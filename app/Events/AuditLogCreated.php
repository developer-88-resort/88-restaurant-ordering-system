<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class AuditLogCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('audit-logs'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'AuditLogCreated';
    }
}
