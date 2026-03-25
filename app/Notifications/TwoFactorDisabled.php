<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TwoFactorDisabled extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Two-Factor Authentication Disabled')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Two-factor authentication has been disabled on your account.')
            ->line('Your account no longer has the additional security layer provided by two-factor authentication.')
            ->action('View Your Security Settings', url('/profile/security'))
            ->line('If you did not disable this feature, please contact our support team immediately.')
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'two_factor_disabled',
            'user_id' => $notifiable->id,
            'message' => 'Two-factor authentication has been disabled on your account.',
        ];
    }
}
