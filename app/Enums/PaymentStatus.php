<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Unpaid = 'unpaid';
    case Paid = 'paid';
    case Voided = 'voided';

    public function label(): string
    {
        return match ($this) {
            self::Unpaid => 'Unpaid',
            self::Paid => 'Paid',
            self::Voided => 'Voided',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Unpaid => 'bg-red-100 text-red-800',
            self::Paid => 'bg-green-100 text-green-800',
            self::Voided => 'bg-gray-200 text-gray-600',
        };
    }

    public function dotClasses(): string
    {
        return match ($this) {
            self::Unpaid => 'bg-red-500',
            self::Paid => 'bg-green-500',
            self::Voided => 'bg-gray-500',
        };
    }
}
