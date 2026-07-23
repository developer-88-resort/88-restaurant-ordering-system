<?php

namespace App\Enums;

enum TaxRegistrationType: string
{
    case Vat = 'vat';
    case NonVat = 'non_vat';

    public function label(): string
    {
        return match ($this) {
            self::Vat => __('VAT Registered'),
            self::NonVat => __('Non-VAT Registered'),
        };
    }

    public function defaultInvoiceTitle(): string
    {
        return match ($this) {
            self::Vat => __('VAT Invoice'),
            self::NonVat => __('Non-VAT Invoice'),
        };
    }
}
