<?php

namespace App\Services;

use App\Enums\DiscountType;
use App\Enums\TaxRegistrationType;

/**
 * Stateless, decimal-safe (bcmath) computation of the full BIR invoice
 * breakdown for one order: VATable/VAT-exempt sales, VAT amount, Senior/
 * PWD/promo discount, optional service charge, and the final amount due.
 * This is the single source of truth for these numbers — the checkout
 * flow calls it to produce the persisted OrderInvoiceSnapshot, and every
 * other place (receipt, PDF, reports) only ever reads that already-computed
 * snapshot rather than recomputing.
 *
 * bcmath is used because decimal:2-cast Eloquent money attributes are
 * already PHP strings, which is exactly what bcmath natively consumes and
 * produces — no cents-conversion boundary needed. Internal scale is 6;
 * every returned figure is rounded to 2 decimals exactly once, at the
 * point it's returned (bcmath truncates rather than rounds, so rounding
 * mid-calculation and reusing the rounded value would let small errors
 * compound — see round2()). Where two figures must sum to a known total
 * (e.g. vat_amount and vatable_sales against the inclusive amount), the
 * second is derived by subtraction from the first rather than rounded
 * independently, so a routine rounding_adjustment is never needed for
 * typical peso amounts.
 */
class InvoiceCalculator
{
    protected const SCALE = 6;

    /**
     * @param  array{
     *     gross_sales: string|float,
     *     tax_registration_type: TaxRegistrationType|string,
     *     tax_rate: string|float,
     *     prices_include_vat: bool,
     *     discount_type?: DiscountType|string|null,
     *     eligible_amount?: string|float|null,
     *     promo_percent?: string|float|null,
     *     service_charge_enabled?: bool,
     *     service_charge_percent?: string|float|null,
     *     service_charge_taxable?: bool,
     * }  $input
     * @return array<string, string>
     */
    public static function compute(array $input): array
    {
        $rate = self::normalize($input['tax_rate'] ?? '12.00');
        $inclusive = (bool) ($input['prices_include_vat'] ?? true);
        $isVat = self::isVat($input['tax_registration_type']);
        $grossSales = self::normalize($input['gross_sales'] ?? '0');

        $discountType = $input['discount_type'] ?? null;
        $eligibleAmount = ($input['eligible_amount'] ?? null) !== null
            ? self::normalize($input['eligible_amount'])
            : null;
        $promoPercent = $input['promo_percent'] ?? null;
        $hasDiscount = $discountType !== null && $eligibleAmount !== null;
        $isStatutory = $hasDiscount && self::isStatutory($discountType);

        $nonEligibleAmount = $hasDiscount
            ? bcsub($grossSales, $eligibleAmount, self::SCALE)
            : $grossSales;

        $vatableSales = '0';
        $vatAmount = '0';
        $vatExemptSales = '0';
        $vatExemption = '0';
        $discountAmount = '0';
        $nonEligibleDue = $nonEligibleAmount;
        $eligibleDue = '0';

        if ($isVat) {
            $split = self::splitTaxable($nonEligibleAmount, $rate, $inclusive);
            $vatableSales = $split['net'];
            $vatAmount = $split['vat'];
            $nonEligibleDue = $split['gross'];
        }

        if ($hasDiscount) {
            if ($isVat && $isStatutory) {
                // Senior/PWD: 20% off the amount NET of VAT (RA 9994 / RA
                // 10754 / BIR RR 8-2010) — strip VAT first, then discount.
                $eligibleSplit = self::splitTaxable($eligibleAmount, $rate, $inclusive);
                $vatExemptSales = $eligibleSplit['net'];
                $vatExemption = $eligibleSplit['vat'];
                $discountAmount = bcmul($vatExemptSales, '0.20', self::SCALE);
                $eligibleDue = bcsub($vatExemptSales, $discountAmount, self::SCALE);
            } elseif ($isVat) {
                // Promo: not statutory, stays fully VATable — simple
                // percentage off, no VAT-exemption treatment.
                $eligibleSplit = self::splitTaxable($eligibleAmount, $rate, $inclusive);
                $vatableSales = bcadd($vatableSales, $eligibleSplit['net'], self::SCALE);
                $vatAmount = bcadd($vatAmount, $eligibleSplit['vat'], self::SCALE);
                $percent = bcdiv(self::normalize($promoPercent ?? '0'), '100', self::SCALE);
                $discountAmount = bcmul($eligibleAmount, $percent, self::SCALE);
                $eligibleDue = bcsub($eligibleSplit['gross'], $discountAmount, self::SCALE);
            } else {
                // Non-VAT business: nothing labeled VAT anywhere.
                $percent = $isStatutory ? '0.20' : bcdiv(self::normalize($promoPercent ?? '0'), '100', self::SCALE);
                $discountAmount = bcmul($eligibleAmount, $percent, self::SCALE);
                $eligibleDue = bcsub($eligibleAmount, $discountAmount, self::SCALE);
            }
        }

        // Service charge: computed off gross_sales before discount, kept
        // as its own additive line so it never interacts with the
        // discount math above. Only contributes its own small VAT
        // component when explicitly marked taxable.
        $serviceChargeEnabled = (bool) ($input['service_charge_enabled'] ?? false);
        $serviceChargeAmount = '0';
        if ($serviceChargeEnabled && ! empty($input['service_charge_percent'])) {
            $scPercent = bcdiv(self::normalize($input['service_charge_percent']), '100', self::SCALE);
            $serviceChargeAmount = bcmul($grossSales, $scPercent, self::SCALE);

            if ($isVat && ($input['service_charge_taxable'] ?? false)) {
                $scSplit = self::splitTaxable($serviceChargeAmount, $rate, true);
                $vatAmount = bcadd($vatAmount, $scSplit['vat'], self::SCALE);
            }
        }

        $totalAmountDue = bcadd(bcadd($nonEligibleDue, $eligibleDue, self::SCALE), $serviceChargeAmount, self::SCALE);

        return [
            'gross_sales' => self::round2($grossSales),
            'vatable_sales' => self::round2($vatableSales),
            'vat_exempt_sales' => self::round2($vatExemptSales),
            'zero_rated_sales' => '0.00',
            'vat_amount' => self::round2($vatAmount),
            'vat_exemption_amount' => self::round2($vatExemption),
            'discount_amount' => self::round2($discountAmount),
            'service_charge_amount' => self::round2($serviceChargeAmount),
            'rounding_adjustment' => '0.00',
            'total_amount_due' => self::round2($totalAmountDue),
        ];
    }

    /**
     * Splits an amount into its net-of-tax and VAT components. When prices
     * are VAT-inclusive (the normal case), $amount already IS the gross
     * charge and is divided down; when they're VAT-exclusive, $amount is
     * the net base and VAT is added on top.
     *
     * @return array{net: string, vat: string, gross: string}
     */
    protected static function splitTaxable(string $amount, string $rate, bool $inclusive): array
    {
        if ($inclusive) {
            $divisor = bcadd('1', bcdiv($rate, '100', self::SCALE), self::SCALE);
            $net = bcdiv($amount, $divisor, self::SCALE);
            $vat = bcsub($amount, $net, self::SCALE);

            return ['net' => $net, 'vat' => $vat, 'gross' => $amount];
        }

        $vat = bcmul($amount, bcdiv($rate, '100', self::SCALE), self::SCALE);
        $gross = bcadd($amount, $vat, self::SCALE);

        return ['net' => $amount, 'vat' => $vat, 'gross' => $gross];
    }

    /**
     * bcmath truncates rather than rounds — this adds half a centavo
     * before truncating to 2 decimals, i.e. standard round-half-up.
     * Every monetary value in this system is non-negative, so this simple
     * form (no sign handling) is sufficient.
     */
    protected static function round2(string $value): string
    {
        return bcadd($value, '0.005', 2);
    }

    protected static function normalize(string|float|int $value): string
    {
        return rtrim(rtrim(sprintf('%.'.self::SCALE.'F', (float) $value), '0'), '.') ?: '0';
    }

    protected static function isVat(TaxRegistrationType|string $type): bool
    {
        return $type instanceof TaxRegistrationType
            ? $type === TaxRegistrationType::Vat
            : $type === TaxRegistrationType::Vat->value;
    }

    protected static function isStatutory(DiscountType|string $type): bool
    {
        return $type instanceof DiscountType
            ? $type->isStatutory()
            : in_array($type, [DiscountType::SeniorCitizen->value, DiscountType::Pwd->value], true);
    }
}
