<?php

namespace App\Enums;

enum SpaceStatus: string
{
    case Available = 'available';
    case Occupied = 'occupied';
    case Reserved = 'reserved';
    case Maintenance = 'maintenance';
    case Disabled = 'disabled';

    public function label(): string
    {
        return match ($this) {
            self::Available => 'Available',
            self::Occupied => 'Occupied',
            self::Reserved => 'Reserved',
            self::Maintenance => 'Maintenance',
            self::Disabled => 'Disabled',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Available => 'bg-green-600 text-white',
            self::Occupied => 'bg-red-600 text-white',
            self::Reserved => 'bg-orange-500 text-white',
            self::Maintenance => 'bg-yellow-500 text-white',
            self::Disabled => 'bg-blue-600 text-white',
        };
    }

    /**
     * Softer, easier-on-the-eyes styling for dense grids (e.g. the New
     * Order location picker) — white card with a colored left accent
     * border and matching status text, instead of a full solid fill.
     */
    public function pickerAccentClasses(): string
    {
        return match ($this) {
            self::Available => 'border-l-green-500 text-green-700',
            self::Occupied => 'border-l-red-500 text-red-700',
            self::Reserved => 'border-l-orange-500 text-orange-700',
            self::Maintenance => 'border-l-yellow-600 text-yellow-700',
            self::Disabled => 'border-l-blue-500 text-blue-700',
        };
    }
}
