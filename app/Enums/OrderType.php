<?php

namespace App\Enums;

enum OrderType: string
{
    case DineIn = 'dine_in';
    case Takeout = 'takeout';

    public function label(): string
    {
        return match ($this) {
            self::DineIn => 'Dine In',
            self::Takeout => 'Take-out',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::DineIn => 'bg-blue-100 text-blue-800',
            self::Takeout => 'bg-orange-100 text-orange-800',
        };
    }
}
