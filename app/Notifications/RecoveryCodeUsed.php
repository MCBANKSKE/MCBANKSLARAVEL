<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RecoveryCodeUsed extends Notification implements ShouldQueue
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
            ->subject('Recovery Code Used')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('A recovery code was used to access your account.')
            ->line('If this was you, no action is needed. If you did not use a recovery code, please secure your account immediately.')
            ->action('Secure Your Account', url('/profile/security'))
            ->line('Remaining recovery codes: ' . $notifiable->twoFactorAuthentication->getRemainingRecoveryCodesCount())
            ->line('If you suspect unauthorized access, consider regenerating your recovery codes.')
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
            'type' => 'recovery_code_used',
            'user_id' => $notifiable->id,
            'message' => 'A recovery code was used to access your account.',
            'remaining_codes' => $notifiable->twoFactorAuthentication->getRemainingRecoveryCodesCount(),
        ];
    }
}
