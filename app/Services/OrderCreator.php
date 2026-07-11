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
     * @param  array<int, array{menu_item_id: int, quantity: int, notes?: ?string}>  $items
     * @param  array<string, mixed>  $orderAttributes  Everything except order_number/status/payment_status/total_amount, which this method fills in.
     */
    public static function create(array $items, array $orderAttributes, ?Space $space): Order
    {
        return DB::transaction(function () use ($items, $orderAttributes, $space) {
            $lines = collect($items)->map(function (array $line) {
                $menuItem = MenuItem::findOrFail($line['menu_item_id']);
                $unitPrice = (float) $menuItem->price;
                $quantity = (int) $line['quantity'];

                return [
                    'menu_item_id' => $menuItem->id,
                    'item_name' => $menuItem->name,
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
