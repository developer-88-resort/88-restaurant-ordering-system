<?php

namespace App\Events;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class KitchenUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('kitchen'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'KitchenUpdated';
    }

    /**
     * Computed fresh at broadcast time so the sidebar's "pending orders"
     * badge can update live without a separate fetch — every call site
     * that dispatches this event already means "something about the order
     * queue changed", so a fresh count is always cheap and correct here.
     *
     * @return array<string, int>
     */
    public function broadcastWith(): array
    {
        return [
            'pending_orders_count' => Order::where('status', OrderStatus::Pending)->count(),
        ];
    }
}
