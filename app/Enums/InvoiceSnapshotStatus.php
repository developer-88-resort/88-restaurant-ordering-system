<?php

namespace App\Enums;

enum InvoiceSnapshotStatus: string
{
    case Active = 'active';
    case Voided = 'voided';

    public function label(): string
    {
        return match ($this) {
            self::Active => __('Active'),
            self::Voided => __('Voided'),
        };
    }
}
