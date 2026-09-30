<?php

namespace App\Livewire\Messages;

use App\Models\User;
use App\Models\UserConnection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Component;

class Inbox extends Component
{
    public string $search = '';

    public function render(): View
    {
        $user = auth()->user();
        $connections = UserConnection::query()
            ->where('status', UserConnection::ACCEPTED)
            ->where(fn (Builder $query) => $query
                ->where('sender_id', $user->id)
                ->orWhere('recipient_id', $user->id))
            ->with([
                'sender:id,name,profile_photo_path,deactivated_at',
                'recipient:id,name,profile_photo_path,deactivated_at',
            ])
            ->get();

        $blockedIds = $user->blockedUsers()->pluck('users.id')
            ->merge($user->blockedByUsers()->pluck('users.id'))
            ->map(fn ($id) => (int) $id)
            ->all();

        $search = mb_strtolower(trim($this->search));
        $partners = $connections
            ->map(fn (UserConnection $connection) => (int) $connection->sender_id === (int) $user->id
                ? $connection->recipient
                : $connection->sender)
            ->filter(fn (?User $partner) => $partner
                && ! $partner->deactivated_at
                && ! in_array((int) $partner->id, $blockedIds, true)
                && ($search === '' || str_contains(mb_strtolower($partner->name), $search)))
            ->unique('id')
            ->sortBy('name')
            ->values();

        return view('livewire.messages.inbox', ['partners' => $partners]);
    }
}
