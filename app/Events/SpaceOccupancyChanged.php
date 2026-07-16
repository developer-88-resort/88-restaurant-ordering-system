<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class SpaceOccupancyChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    /**
     * @param  array<int, int>  $spaceIds
     */
    public function __construct(
        public string $message,
        public array $spaceIds,
        public string $status,
    ) {
        //
    }

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('spaces'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'SpaceOccupancyChanged';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'message' => $this->message,
            'space_ids' => $this->spaceIds,
            'status' => $this->status,
        ];
    }
}
