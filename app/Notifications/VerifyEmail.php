<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmail extends BaseVerifyEmail implements ShouldQueue
{
    use Queueable;

    /**
     * Get the verify email notification mail message for the given URL.
     *
     * @param  string  $url
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    protected function buildMailMessage($url)
    {
        return (new MailMessage)
            ->subject('Verify Your Email Address - ' . config('app.name'))
            ->greeting('Hello!')
            ->line('Thank you for signing up with ' . config('app.name') . '!')
            ->line('Please click the button below to verify your email address and activate your account.')
            ->action('Verify Email Address', $url)
            ->line('This verification link will expire in 60 minutes.')
            ->line('If you did not create an account, no further action is required.')
            ->line('If you\'re having trouble clicking the verification button, copy and paste the URL below into your web browser:')
            ->line($url)
            ->salutation('Best regards, The ' . config('app.name') . ' Team');
    }
}
