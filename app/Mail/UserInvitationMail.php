<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $token, public User $user)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __("You're invited to join :app", ['app' => config('app.name')]),
        );
    }

    public function content(): Content
    {
        $url = url(route('invitation.accept', [
            'token' => $this->token,
            'email' => $this->user->email,
        ], false));

        return new Content(
            view: 'emails.user-invitation',
            with: [
                'url' => $url,
                'user' => $this->user,
            ],
        );
    }
}
