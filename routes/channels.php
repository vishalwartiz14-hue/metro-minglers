<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('users.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('conversations.{first}.{second}', function (User $user, string $first, string $second): bool {
    $firstId = (int) $first;
    $secondId = (int) $second;

    if ($firstId >= $secondId || ! in_array((int) $user->id, [$firstId, $secondId], true)) {
        return false;
    }

    $otherId = (int) $user->id === $firstId ? $secondId : $firstId;
    $other = User::query()->find($otherId);

    return $other !== null
        && ! $other->deactivated_at
        && ! $user->hasBlockedOrBeenBlockedBy($otherId)
        && $user->isConnectedWith($otherId);
});
