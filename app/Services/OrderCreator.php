<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\SpaceStatus;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Space;
use Illuminate\Support\Facades\DB;

/**
 * The trusted "build an order" core shared by staff order creation
 * (OrderController::store()) and customer self-service ordering
 * (CustomerOrderController::store()). Both callers resolve *which*
 * space/area/category/type an order belongs to differently — this class
 * only handles the part that must never differ between them: re-pricing
 * items from the live MenuItem record (never trusting a submitted price),
 * assigning the order number, and flipping the space to Occupied.
 */
class OrderCreator
{
    /**
     * @param  array<int, array{menu_item_id: int, menu_item_variant_id?: ?int, quantity: int, notes?: ?string}>  $items
     * @param  array<string, mixed>  $orderAttributes  Everything except order_number/status/payment_status/total_amount, which this method fills in.
     */
    public static function create(array $items, array $orderAttributes, ?Space $space): Order
    {
        return DB::transaction(function () use ($items, $orderAttributes, $space) {
            $lines = collect($items)->map(function (array $line) {
                $menuItem = MenuItem::findOrFail($line['menu_item_id']);
                $quantity = (int) $line['quantity'];

                // Once an item has variants, its own price is meaningless —
                // resolve the chosen variant (falling back to the item's
                // default/first one if the caller somehow didn't pass one,
                // so this never silently falls through to the stale base
                // price). Freezing the variant name straight into item_name
                // means every existing display surface (receipt, kitchen
                // board, reports) needs zero changes to show it correctly.
                $variant = null;
                if ($menuItem->hasVariants()) {
                    $variant = ! empty($line['menu_item_variant_id'])
                        ? $menuItem->variants->firstWhere('id', (int) $line['menu_item_variant_id'])
                        : null;
                    $variant ??= $menuItem->variants->firstWhere('is_default', true) ?? $menuItem->variants->first();
                }

                $unitPrice = (float) ($variant->price ?? $menuItem->price);
                $itemName = $variant ? "{$menuItem->name} — {$variant->name}" : $menuItem->name;

                return [
                    'menu_item_id' => $menuItem->id,
                    'menu_item_variant_id' => $variant?->id,
                    'item_name' => $itemName,
                    'unit_price' => $unitPrice,
                    'quantity' => $quantity,
                    'subtotal' => $unitPrice * $quantity,
                    'notes' => $line['notes'] ?? null,
                ];
            });

            $order = Order::create($orderAttributes + [
                'order_number' => OrderNumberGenerator::generate(),
                'status' => OrderStatus::Pending,
                'payment_status' => PaymentStatus::Unpaid,
                'total_amount' => $lines->sum('subtotal'),
            ]);

            $order->items()->createMany($lines->all());

            if ($space && $space->status === SpaceStatus::Available) {
                $space->setStatusWithSharedTables(SpaceStatus::Occupied);
            }

            return $order;
        });
    }
}
