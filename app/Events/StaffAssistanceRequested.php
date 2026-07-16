<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * A customer tapped "Call a Staff" on the Welcome (lobby QR) screen — no
 * order/table is necessarily involved, just a live nudge to whichever
 * staff are looking at any admin page right now.
 */
class StaffAssistanceRequested implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public ?string $customerName,
    ) {
        //
    }

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('staff-alerts'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'StaffAssistanceRequested';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'message' => $this->customerName
                ? __(':name is requesting assistance.', ['name' => $this->customerName])
                : __('A guest is requesting assistance.'),
        ];
    }
}
