<?php

namespace App\Enums;

enum DiscountType: string
{
    case SeniorCitizen = 'senior_citizen';
    case Pwd = 'pwd';
    case Promo = 'promo';

    public function label(): string
    {
        return match ($this) {
            self::SeniorCitizen => __('Senior Citizen'),
            self::Pwd => __('PWD'),
            self::Promo => __('Regular/Promotional Discount'),
        };
    }

    /**
     * Only statutory discounts (Senior/PWD) get the VAT-exemption
     * treatment under RA 9994/RA 10754 — a promo is just a plain
     * percentage off, still fully VATable.
     */
    public function isStatutory(): bool
    {
        return $this !== self::Promo;
    }
}
