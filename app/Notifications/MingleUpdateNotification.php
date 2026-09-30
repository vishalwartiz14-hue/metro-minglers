<?php

namespace App\Notifications;

use App\Models\Mingle;
use App\Models\MingleUpdate;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MingleUpdateNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Mingle $mingle,
        public MingleUpdate $update,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Update from '.$this->mingle->title,
            'body' => $this->update->body,
            'mingle_id' => $this->mingle->id,
            'update_id' => $this->update->id,
            'url' => route('mingles.show', $this->mingle).'#updates',
        ];
    }
}
