<?php

namespace App\Notifications;

use App\Models\Mingle;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MingleJoinDecisionNotification extends Notification
{
    use Queueable;

    public function __construct(public Mingle $mingle, public bool $accepted)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => $this->accepted ? 'You joined '.$this->mingle->title : 'Your request was declined',
            'body' => $this->accepted ? 'The host approved your request to join.' : 'The host declined your request to join this Mingle.',
            'mingle_id' => $this->mingle->id,
            'kind' => 'join-decision',
        ];
    }
}
