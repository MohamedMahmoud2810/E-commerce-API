<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Log;

class VerifyApiEmail extends Notification implements ShouldQueue
{
    use Queueable;

    public $verificationUrl;

    public function __construct($verificationUrl)
    {
        $this->verificationUrl = $verificationUrl;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        Log::info('Sending email to: ' . $notifiable->email);
        Log::info('Verification URL: ' . $this->verificationUrl);

        return (new MailMessage)
            ->subject('Verify Your Email Address')
            ->line('Please click the button below to verify your email address.')
            ->action('Verify Email', url('/api/email/verify/' . $notifiable->id . '?expires=' . time() . '&signature=' . sha1($notifiable->id)))
            ->line('If you did not create an account, no further action is required.');
    }
}

