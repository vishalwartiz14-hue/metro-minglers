<?php

namespace App\Notifications;

use App\Models\Mingle;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MingleReminder extends Notification
{
    public function __construct(public Mingle $mingle)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)->subject('Reminder: '.$this->mingle->title)->greeting('Hi '.$notifiable->name.',')->line('Your RSVP is coming up soon.');

        if ($this->mingle->starts_at) {
            $message->line($this->mingle->starts_at->format('l, F j').' at '.$this->mingle->starts_at->format('g:i A'));
        }

        if ($this->mingle->venue) {
            $message->line('Location: '.$this->mingle->venue);
        }

        return $message->action('View Mingle', route('mingles.show', $this->mingle))
            ->line('You can turn off reminders for this event from My Mingles.');
    }
}
