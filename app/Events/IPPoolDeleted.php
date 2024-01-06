<?php

namespace App\Events;

use App\Models\IPPool;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class IPPoolDeleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public IPPool $ipPool;
    /**
     * Create a new event instance.
     */
    public function __construct(IPPool $ipPool)
    {
        $this->ipPool = $ipPool;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [];
//        return [
//            new PrivateChannel('channel-name'),
//        ];
    }
}
