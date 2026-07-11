<?php

namespace App\Notifications;

use App\Mail\UserInvitationMail;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class UserInvitationNotification extends Notification
{
    use Queueable;

    public function __construct(public string $token)
    {
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): UserInvitationMail
    {
        return (new UserInvitationMail($this->token, $notifiable))
            ->to($notifiable->email);
    }
}
