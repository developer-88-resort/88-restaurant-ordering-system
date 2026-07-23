<?php

namespace Tests\Feature;

use App\Services\InvoiceCalculator;
use Tests\TestCase;

/**
 * Pure computation tests for InvoiceCalculator — no HTTP/DB involved,
 * since the calculator is fully stateless. See OrderPaymentFinalizationTest
 * for the HTTP-level checkout flow that actually persists these numbers.
 */
class InvoiceComputationTest extends TestCase
{
    public function test_regular_vat_inclusive_order_with_no_discount(): void
    {
        $result = InvoiceCalculator::compute([
            'gross_sales' => '1120.00',
            'tax_registration_type' => 'vat',
            'tax_rate' => '12.00',
            'prices_include_vat' => true,
        ]);

        $this->assertSame('1000.00', $result['vatable_sales']);
        $this->assertSame('120.00', $result['vat_amount']);
        $this->assertSame('0.00', $result['discount_amount']);
        $this->assertSame('1120.00', $result['total_amount_due']);
    }

    public function test_regular_non_vat_order_has_no_vat_lines(): void
    {
        $result = InvoiceCalculator::compute([
            'gross_sales' => '1000.00',
            'tax_registration_type' => 'non_vat',
            'tax_rate' => '12.00',
            'prices_include_vat' => true,
        ]);

        $this->assertSame('0.00', $result['vatable_sales']);
        $this->assertSame('0.00', $result['vat_amount']);
        $this->assertSame('0.00', $result['vat_exempt_sales']);
        $this->assertSame('1000.00', $result['total_amount_due']);
    }

    /**
     * The owner's own worked example: a ₱1,120 VAT-inclusive order fully
     * covered by a Senior Citizen discount.
     */
    public function test_full_order_senior_citizen_discount_matches_the_worked_example(): void
    {
        $result = InvoiceCalculator::compute([
            'gross_sales' => '1120.00',
            'tax_registration_type' => 'vat',
            'tax_rate' => '12.00',
            'prices_include_vat' => true,
            'discount_type' => 'senior_citizen',
            'eligible_amount' => '1120.00',
        ]);

        $this->assertSame('1000.00', $result['vat_exempt_sales']);
        $this->assertSame('120.00', $result['vat_exemption_amount']);
        $this->assertSame('200.00', $result['discount_amount']);
        $this->assertSame('800.00', $result['total_amount_due']);
    }

    public function test_full_order_pwd_discount_matches_senior_citizen_math(): void
    {
        $result = InvoiceCalculator::compute([
            'gross_sales' => '1120.00',
            'tax_registration_type' => 'vat',
            'tax_rate' => '12.00',
            'prices_include_vat' => true,
            'discount_type' => 'pwd',
            'eligible_amount' => '1120.00',
        ]);

        $this->assertSame('1000.00', $result['vat_exempt_sales']);
        $this->assertSame('200.00', $result['discount_amount']);
        $this->assertSame('800.00', $result['total_amount_due']);
    }

    /**
     * A mixed table: only part of a ₱2,000 order is the qualified
     * customer's own consumption — the rest stays fully VATable.
     */
    public function test_mixed_table_partial_eligible_amount(): void
    {
        $result = InvoiceCalculator::compute([
            'gross_sales' => '2000.00',
            'tax_registration_type' => 'vat',
            'tax_rate' => '12.00',
            'prices_include_vat' => true,
            'discount_type' => 'senior_citizen',
            'eligible_amount' => '1120.00',
        ]);

        // Non-eligible portion (880) stays VATable.
        $this->assertSame('785.71', $result['vatable_sales']);
        $this->assertSame('94.29', $result['vat_amount']);
        // Eligible portion (1120) gets the statutory treatment.
        $this->assertSame('1000.00', $result['vat_exempt_sales']);
        $this->assertSame('200.00', $result['discount_amount']);
        $this->assertSame('1680.00', $result['total_amount_due']);
    }

    public function test_promo_discount_does_not_get_vat_exemption_treatment(): void
    {
        $result = InvoiceCalculator::compute([
            'gross_sales' => '1120.00',
            'tax_registration_type' => 'vat',
            'tax_rate' => '12.00',
            'prices_include_vat' => true,
            'discount_type' => 'promo',
            'eligible_amount' => '1120.00',
            'promo_percent' => '10',
        ]);

        // A promo stays fully VATable — no vat_exempt_sales, no vat_exemption_amount.
        $this->assertSame('0.00', $result['vat_exempt_sales']);
        $this->assertSame('0.00', $result['vat_exemption_amount']);
        $this->assertSame('1000.00', $result['vatable_sales']);
        $this->assertSame('112.00', $result['discount_amount']);
        $this->assertSame('1008.00', $result['total_amount_due']);
    }

    public function test_non_vat_business_discount_is_a_straight_twenty_percent_off(): void
    {
        $result = InvoiceCalculator::compute([
            'gross_sales' => '1000.00',
            'tax_registration_type' => 'non_vat',
            'tax_rate' => '12.00',
            'prices_include_vat' => true,
            'discount_type' => 'pwd',
            'eligible_amount' => '1000.00',
        ]);

        $this->assertSame('0.00', $result['vat_exempt_sales'], 'Non-VAT businesses never report a VAT-exempt-sales figure.');
        $this->assertSame('200.00', $result['discount_amount']);
        $this->assertSame('800.00', $result['total_amount_due']);
    }

    public function test_service_charge_is_additive_and_independent_of_discount(): void
    {
        $result = InvoiceCalculator::compute([
            'gross_sales' => '1000.00',
            'tax_registration_type' => 'non_vat',
            'tax_rate' => '12.00',
            'prices_include_vat' => true,
            'service_charge_enabled' => true,
            'service_charge_percent' => '10',
        ]);

        $this->assertSame('100.00', $result['service_charge_amount']);
        $this->assertSame('1100.00', $result['total_amount_due']);
    }

    public function test_two_decimal_rounding_is_applied_consistently(): void
    {
        // 100/3 = 33.333... repeating — must round to exactly 2 decimals.
        $result = InvoiceCalculator::compute([
            'gross_sales' => '100.00',
            'tax_registration_type' => 'vat',
            'tax_rate' => '12.00',
            'prices_include_vat' => true,
            'discount_type' => 'senior_citizen',
            'eligible_amount' => '33.33',
        ]);

        foreach ($result as $key => $value) {
            $this->assertMatchesRegularExpression('/^\d+\.\d{2}$/', $value, "Field {$key} must have exactly 2 decimal places, got {$value}.");
        }
    }

    /**
     * Real-world mixed-table scenario: Budae Jjigae (₱399.00, personal
     * consumption of the Senior Citizen) + Bottled Water (₱90.00, the
     * non-qualified diner's own item). Only the Budae Jjigae is eligible.
     */
    public function test_mixed_table_senior_citizen_scenario_matches_exact_expected_figures(): void
    {
        $result = InvoiceCalculator::compute([
            'gross_sales' => '489.00',
            'tax_registration_type' => 'vat',
            'tax_rate' => '12.00',
            'prices_include_vat' => true,
            'discount_type' => 'senior_citizen',
            'eligible_amount' => '399.00',
        ]);

        $this->assertSame('489.00', $result['gross_sales']);
        $this->assertSame('80.36', $result['vatable_sales']);
        $this->assertSame('356.25', $result['vat_exempt_sales']);
        $this->assertSame('9.64', $result['vat_amount']);
        $this->assertSame('42.75', $result['vat_exemption_amount']);
        $this->assertSame('71.25', $result['discount_amount']);
        $this->assertSame('375.00', $result['total_amount_due']);
    }

    public function test_zero_rated_sales_is_always_reserved_at_zero(): void
    {
        $result = InvoiceCalculator::compute([
            'gross_sales' => '1000.00',
            'tax_registration_type' => 'vat',
            'tax_rate' => '12.00',
            'prices_include_vat' => true,
        ]);

        $this->assertSame('0.00', $result['zero_rated_sales']);
    }
}
