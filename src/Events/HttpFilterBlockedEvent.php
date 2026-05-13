<?php

namespace Dasayapov\LaravelHttpFilter\Events;

use Dasayapov\LaravelHttpFilter\Models\HttpFilterIp;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class HttpFilterBlockedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $httpFilterIpId;

    public int $type;

    public array $data;

    const TYPE_STOP_WORDS = 1;

    const TYPE_NOT_FOUND = 2;

    const TYPE_RATE_LIMIT = 3;

    /**
     * Create a new event instance.
     */
    public function __construct($ipId, $type, $data = [])
    {
        $this->httpFilterIpId = $ipId;
        $this->type = $type;
        $this->data = $data;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
