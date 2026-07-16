<?php

namespace App\Enums;

enum DiscountEligibilityMethod: string
{
    case ItemBased = 'item_based';
    case AmountBased = 'amount_based';

    public function label(): string
    {
        return match ($this) {
            self::ItemBased => __('Select eligible items'),
            self::AmountBased => __('Enter eligible amount'),
        };
    }
}
