<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class DirectMessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    /** @param array{id: string, sender_id: int, recipient_id: int, body: string, sent_at: string, sent_at_label: string} $message */
    public function __construct(
        public array $message,
        public int $firstUserId,
        public int $secondUserId,
    ) {
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel("conversations.{$this->firstUserId}.{$this->secondUserId}")];
    }

    public function broadcastAs(): string
    {
        return 'direct-message.sent';
    }

    public function broadcastWith(): array
    {
        return ['message' => $this->message];
    }
}
