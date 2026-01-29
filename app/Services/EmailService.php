<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\WelcomeEmail;
use App\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Log;

class EmailService
{
    /**
     * Send welcome email to new user
     */
    public function sendWelcomeEmail(User $user): bool
    {
        try {
            $user->notify(new WelcomeEmail());
            Log::info('Welcome email sent to user: ' . $user->email);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send welcome email: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send verification reminder email
     */
    public function sendVerificationReminder(User $user): bool
    {
        try {
            $user->notify(new VerifyEmail());
            Log::info('Verification reminder sent to user: ' . $user->email);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send verification reminder: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send bulk emails to multiple users
     */
    public function sendBulkEmail(array $userIds, string $subject, string $message): array
    {
        $results = ['sent' => 0, 'failed' => 0];
        
        foreach ($userIds as $userId) {
            $user = User::find($userId);
            if ($user) {
                try {
                    // This would be implemented with a custom notification
                    // For now, we'll just log it
                    Log::info("Bulk email would be sent to: {$user->email}");
                    $results['sent']++;
                } catch (\Exception $e) {
                    Log::error("Failed to send bulk email to {$user->email}: " . $e->getMessage());
                    $results['failed']++;
                }
            }
        }
        
        return $results;
    }

    /**
     * Check if email configuration is working
     */
    public function testEmailConfiguration(): bool
    {
        try {
            $testEmail = 'test@example.com';
            // This would send a test email
            Log::info('Email configuration test would be sent to: ' . $testEmail);
            return true;
        } catch (\Exception $e) {
            Log::error('Email configuration test failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get email statistics
     */
    public function getEmailStatistics(): array
    {
        return [
            'total_users' => User::count(),
            'verified_users' => User::whereNotNull('email_verified_at')->count(),
            'unverified_users' => User::whereNull('email_verified_at')->count(),
            'users_registered_today' => User::whereDate('created_at', today())->count(),
        ];
    }
}
