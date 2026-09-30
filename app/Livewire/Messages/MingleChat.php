<?php

namespace App\Livewire\Messages;

use App\Events\MingleMessageSent;
use App\Models\Mingle;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class MingleChat extends Component
{
    #[Locked]
    public int $mingleId;

    #[Locked]
    public array $messages = [];

    public string $body = '';

    public function mount(Mingle $mingle): void
    {
        $this->mingleId = (int) $mingle->id;
        $this->authorizedMingle();
    }

    public function getListeners(): array
    {
        $userId = auth()->id();

        return [
            "echo-private:users.{$userId},.mingle-message.sent" => 'receiveMessage',
        ];
    }

    public function send(): void
    {
        $mingle = $this->authorizedMingle();
        $this->body = trim($this->body);
        $validated = $this->validate(['body' => ['required', 'string', 'max:2000']]);
        $sender = auth()->user();
        $sentAt = now();
        $blockedIds = $sender->blockedUsers()->pluck('users.id')
            ->merge($sender->blockedByUsers()->pluck('users.id'))
            ->map(fn ($id) => (int) $id)
            ->all();

        $recipientIds = $mingle->attendees()
            ->whereNull('users.deactivated_at')
            ->whereNotIn('users.id', $blockedIds)
            ->pluck('users.id')
            ->map(fn ($id) => (int) $id)
            ->push((int) $mingle->user_id, (int) $sender->id)
            ->unique()
            ->values()
            ->all();

        $message = [
            'id' => (string) Str::uuid(),
            'mingle_id' => (int) $mingle->id,
            'sender_id' => (int) $sender->id,
            'sender_name' => $sender->name,
            'body' => $validated['body'],
            'sent_at' => $sentAt->toIso8601String(),
            'sent_at_label' => $sentAt->format('M j, g:i A'),
        ];

        event(new MingleMessageSent($message, $recipientIds));
        $this->appendMessage($message);
        $this->body = '';
        $this->dispatch('mingle-message-updated');
    }

    public function receiveMessage(array $event): void
    {
        $message = $event['message'] ?? [];
        if ((int) ($message['mingle_id'] ?? 0) !== $this->mingleId) {
            return;
        }

        $this->authorizedMingle();
        abort_if(auth()->user()->hasBlockedOrBeenBlockedBy((int) ($message['sender_id'] ?? 0)), 404);
        $this->appendMessage($message);
        $this->dispatch('mingle-message-updated');
    }

    public function render(): View
    {
        return view('livewire.messages.mingle-chat', [
            'mingle' => $this->authorizedMingle(),
            'messages' => $this->messages,
        ]);
    }

    private function authorizedMingle(): Mingle
    {
        $user = auth()->user();
        $mingle = Mingle::query()->with('host:id,deactivated_at')->findOrFail($this->mingleId);
        $isHost = (int) $mingle->user_id === (int) $user->id;
        $isMember = $mingle->attendees()->whereKey($user->id)->exists();

        abort_if($mingle->host->deactivated_at, 404);
        abort_if(! $isHost && $user->hasBlockedOrBeenBlockedBy((int) $mingle->user_id), 404);
        abort_unless($isHost || $isMember, 403, 'Join this Mingle before using its live chat.');

        return $mingle;
    }

    private function appendMessage(array $message): void
    {
        $messageId = (string) ($message['id'] ?? '');
        if ($messageId === '' || collect($this->messages)->contains('id', $messageId)) {
            return;
        }

        $this->messages[] = $message;
        $this->messages = array_slice($this->messages, -100);
    }
}
