<?php

namespace App\Enums;

enum TableStatus: string
{
    case Available = 'available';
    case InSession = 'in_session';
    case Occupied = 'occupied';
    case NeedsCleaning = 'needs_cleaning';
    case Disabled = 'disabled';

    public function label(): string
    {
        return match ($this) {
            self::Available => 'Available',
            self::InSession => 'In Session',
            self::Occupied => 'Occupied',
            self::NeedsCleaning => 'Needs Cleaning',
            self::Disabled => 'Disabled',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Available => 'bg-green-100 text-green-800',
            self::InSession => 'bg-blue-100 text-blue-800',
            self::Occupied => 'bg-amber-100 text-amber-800',
            self::NeedsCleaning => 'bg-yellow-100 text-yellow-800',
            self::Disabled => 'bg-gray-200 text-gray-700',
        };
    }
}
