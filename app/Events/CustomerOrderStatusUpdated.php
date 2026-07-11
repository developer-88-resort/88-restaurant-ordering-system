<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Lets an anonymous customer's order-status page update live. Broadcasts on
 * a *public* channel (no routes/channels.php entry needed) scoped by the
 * order's public_token rather than its raw id, so the token protects the
 * WebSocket side the same way it protects the status-page URL — otherwise
 * a stranger could still enumerate `order.1`, `order.2`, ... over Echo even
 * with an unguessable HTTP link.
 */
class CustomerOrderStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(public Order $order) {}

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('order.'.$this->order->public_token),
        ];
    }

    public function broadcastAs(): string
    {
        return 'CustomerOrderStatusUpdated';
    }

    /**
     * @return array<string, string>
     */
    public function broadcastWith(): array
    {
        return [
            'status' => $this->order->status->value,
            'label' => $this->order->status->label(),
        ];
    }
}
