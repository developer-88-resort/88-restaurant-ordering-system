<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Cash = 'cash';
    case Card = 'card';
    case Gcash = 'gcash';
    case Maya = 'maya';
    case BankTransfer = 'bank_transfer';
    case RoomCharge = 'room_charge';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Cash => __('Cash'),
            self::Card => __('Card'),
            self::Gcash => __('GCash'),
            self::Maya => __('Maya'),
            self::BankTransfer => __('Bank Transfer'),
            self::RoomCharge => __('Room Charge'),
            self::Other => __('Other'),
        };
    }

    /**
     * Cash is the only method where "amount tendered" naturally differs
     * from the total (making change) — every other method is assumed to
     * be paid exactly, and a reference number is required instead.
     */
    public function requiresReference(): bool
    {
        return $this !== self::Cash;
    }
}
