<?php

namespace App\Events;

use App\Models\MenuItem;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Broadcasts on a public channel (no auth) — the customer-facing menu
 * pages are reached anonymously via QR code, so there's no user to
 * authorize a private channel against, and stock status isn't sensitive.
 * Listened to by every open customer menu/takeout/preview page so a
 * Superadmin/Admin changing an item's availability in Menu Management
 * reflects live, with no refresh needed.
 */
class MenuItemAvailabilityChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(public MenuItem $menuItem) {}

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('menu'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'MenuItemAvailabilityChanged';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'menu_item_id' => $this->menuItem->id,
            'availability_status' => $this->menuItem->availability_status->value,
        ];
    }
}
