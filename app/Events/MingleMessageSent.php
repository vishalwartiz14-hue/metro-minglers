<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class MingleMessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    /** @param array{id: string, mingle_id: int, sender_id: int, sender_name: string, body: string, sent_at: string, sent_at_label: string} $message
     *  @param list<int> $recipientIds
     */
    public function __construct(
        public array $message,
        public array $recipientIds,
    ) {
    }

    public function broadcastOn(): array
    {
        return collect($this->recipientIds)
            ->unique()
            ->map(fn (int $recipientId) => new PrivateChannel('users.'.$recipientId))
            ->all();
    }

    public function broadcastAs(): string
    {
        return 'mingle-message.sent';
    }

    public function broadcastWith(): array
    {
        return ['message' => $this->message];
    }
}
