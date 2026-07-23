<?php

namespace App\Enums;

enum MenuItemAvailability: string
{
    case Available = 'available';
    case OutOfStock = 'out_of_stock';
    case Seasonal = 'seasonal';
    case Hidden = 'hidden';

    public function label(): string
    {
        return match ($this) {
            self::Available => __('Available'),
            self::OutOfStock => __('Out of Stock'),
            self::Seasonal => __('Seasonal'),
            self::Hidden => __('Hidden'),
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Available => 'bg-green-100 text-green-800',
            self::OutOfStock => 'bg-gray-200 text-gray-700',
            self::Seasonal => 'bg-amber-100 text-amber-800',
            self::Hidden => 'bg-slate-700 text-white',
        };
    }

    /**
     * Whether customers can add this item to their cart. Seasonal items are
     * orderable while in season (that's what flipping them back to
     * Available already covers) — a still-Seasonal item stays orderable
     * with just a badge, only Out of Stock/Hidden block adding.
     */
    public function isOrderable(): bool
    {
        return match ($this) {
            self::Available, self::Seasonal => true,
            self::OutOfStock, self::Hidden => false,
        };
    }

    /**
     * Whether customer-facing menu pages should list this item at all.
     * Hidden items are only ever visible in the Superadmin/Admin management
     * grid, never on a customer's QR menu.
     */
    public function isCustomerVisible(): bool
    {
        return $this !== self::Hidden;
    }
}
