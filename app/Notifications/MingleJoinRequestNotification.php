<?php

namespace App\Notifications;

use App\Models\Mingle;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MingleJoinRequestNotification extends Notification
{
    use Queueable;

    public function __construct(public Mingle $mingle, public string $memberName)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => $this->memberName.' wants to join '.$this->mingle->title,
            'body' => 'Review the request on your Mingle page.',
            'mingle_id' => $this->mingle->id,
            'kind' => 'join-request',
        ];
    }
}
