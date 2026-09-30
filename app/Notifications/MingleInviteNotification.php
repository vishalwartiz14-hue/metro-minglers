<?php

namespace App\Notifications;

use App\Models\Mingle;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MingleInviteNotification extends Notification
{
    use Queueable;

    public function __construct(public Mingle $mingle, public string $inviterName)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'You’re invited to '.$this->mingle->title,
            'body' => $this->inviterName.' invited you to join this Mingle.',
            'mingle_id' => $this->mingle->id,
            'kind' => 'mingle-invite',
        ];
    }
}
