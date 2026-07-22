<?php

namespace App\Http\Requests\Concerns;

use App\Models\MenuItem;
use Illuminate\Validation\Validator;

/**
 * Shared by every order-creation request (staff + both customer flows).
 * Two concerns the plain per-field rules can't express on their own:
 *  - Once an item has variants, a variant must be chosen — can't submit
 *    the bare item.
 *  - Uniqueness has to be checked on the (menu_item_id, menu_item_variant_id)
 *    *pair*, not menu_item_id alone — a customer ordering one Spicy Tuna
 *    and one Oriental Tuna is two legitimate lines for the same item.
 */
trait ValidatesMenuItemVariantSelections
{
    protected function validateVariantSelections(Validator $validator): void
    {
        $seenPairs = [];

        foreach ((array) $this->input('items', []) as $index => $item) {
            $menuItemId = $item['menu_item_id'] ?? null;
            $variantId = $item['menu_item_variant_id'] ?? null;

            if (! $menuItemId) {
                continue;
            }

            $pairKey = $menuItemId.':'.($variantId ?: '');
            if (in_array($pairKey, $seenPairs, true)) {
                $validator->errors()->add("items.{$index}.menu_item_id", __('This item/option combination was submitted more than once.'));
            }
            $seenPairs[] = $pairKey;

            $menuItem = MenuItem::with('variants')->find($menuItemId);

            if (! $menuItem) {
                continue;
            }

            if ($menuItem->hasVariants() && ! $variantId) {
                $validator->errors()->add("items.{$index}.menu_item_variant_id", __('Please choose an option for :name.', ['name' => $menuItem->name]));
            } elseif ($variantId && ! $menuItem->variants->contains('id', (int) $variantId)) {
                $validator->errors()->add("items.{$index}.menu_item_variant_id", __('Invalid option selected for :name.', ['name' => $menuItem->name]));
            }
        }
    }
}
