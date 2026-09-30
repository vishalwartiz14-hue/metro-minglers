<?php

namespace App\Livewire\Messages;

use App\Events\DirectMessageSent;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Conversation extends Component
{
    #[Locked]
    public int $memberId;

    #[Locked]
    public array $messages = [];

    public string $body = '';

    public function mount(User $member): void
    {
        $this->memberId = (int) $member->id;
        $this->authorizedMember();
    }

    public function getListeners(): array
    {
        [$firstUserId, $secondUserId] = $this->conversationUsers();

        return [
            "echo-private:conversations.{$firstUserId}.{$secondUserId},.direct-message.sent" => 'receiveMessage',
        ];
    }

    public function send(): void
    {
        $member = $this->authorizedMember();
        $this->body = trim($this->body);
        $validated = $this->validate(['body' => ['required', 'string', 'max:2000']]);
        [$firstUserId, $secondUserId] = $this->conversationUsers();
        $sentAt = now();

        $message = [
            'id' => (string) Str::uuid(),
            'sender_id' => (int) auth()->id(),
            'recipient_id' => (int) $member->id,
            'body' => $validated['body'],
            'sent_at' => $sentAt->toIso8601String(),
            'sent_at_label' => $sentAt->format('M j, g:i A'),
        ];

        event(new DirectMessageSent($message, $firstUserId, $secondUserId));
        $this->appendMessage($message);
        $this->body = '';
        $this->dispatch('message-updated');
    }

    public function receiveMessage(array $event): void
    {
        $message = $event['message'] ?? [];
        $viewerId = (int) auth()->id();
        $senderId = (int) ($message['sender_id'] ?? 0);
        $recipientId = (int) ($message['recipient_id'] ?? 0);

        if (! (($senderId === $viewerId && $recipientId === $this->memberId)
            || ($senderId === $this->memberId && $recipientId === $viewerId))) {
            return;
        }

        $this->authorizedMember();
        $this->appendMessage($message);
        $this->dispatch('message-updated');
    }

    public function render(): View
    {
        return view('livewire.messages.conversation', [
            'member' => $this->authorizedMember(),
            'messages' => $this->messages,
        ]);
    }

    private function authorizedMember(): User
    {
        $viewer = auth()->user();
        $member = User::query()->findOrFail($this->memberId);

        abort_if($member->deactivated_at, 404);
        abort_if($viewer->is($member) || $viewer->hasBlockedOrBeenBlockedBy($member->id), 404);
        abort_unless($viewer->isConnectedWith($member->id), 403, 'Connect with this member before sending a message.');

        return $member;
    }

    /** @return array{int, int} */
    private function conversationUsers(): array
    {
        $ids = [(int) auth()->id(), $this->memberId];
        sort($ids);

        return $ids;
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
