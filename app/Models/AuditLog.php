<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'description',
        'ip_address',
        'user_agent',
        'old_values',
        'new_values',
        'model_type',
        'model_id',
        'level',
        'metadata',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    public $timestamps = false;

    /**
     * Get the user that performed the action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the related model (polymorphic).
     */
    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope a query to only include logs for a specific user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to only include logs with a specific action.
     */
    public function scopeForAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope a query to only include logs with a specific level.
     */
    public function scopeForLevel($query, string $level)
    {
        return $query->where('level', $level);
    }

    /**
     * Scope a query to only include logs within a date range.
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope a query to only include logs from the last N days.
     */
    public function scopeLastDays($query, int $days)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope a query to only include security-related logs.
     */
    public function scopeSecurity($query)
    {
        $securityActions = [
            'login',
            'logout',
            'login_failed',
            'password_reset_request',
            'password_reset_completed',
            'two_factor_enabled',
            'two_factor_disabled',
            'two_factor_failed',
            'recovery_code_used',
            'social_account_connected',
            'social_account_disconnected',
            'account_locked',
            'account_unlocked',
        ];

        return $query->whereIn('action', $securityActions);
    }

    /**
     * Scope a query to only include error-level logs.
     */
    public function scopeErrors($query)
    {
        return $query->whereIn('level', ['error', 'critical']);
    }

    /**
     * Create a new audit log entry.
     */
    public static function log(array $data): self
    {
        $log = new static([
            'user_id' => $data['user_id'] ?? auth()->id(),
            'action' => $data['action'],
            'description' => $data['description'] ?? null,
            'ip_address' => $data['ip_address'] ?? request()->ip(),
            'user_agent' => $data['user_agent'] ?? request()->userAgent(),
            'old_values' => $data['old_values'] ?? null,
            'new_values' => $data['new_values'] ?? null,
            'model_type' => $data['model_type'] ?? null,
            'model_id' => $data['model_id'] ?? null,
            'level' => $data['level'] ?? 'info',
            'metadata' => $data['metadata'] ?? null,
        ]);

        $log->save();

        return $log;
    }

    /**
     * Log a user login.
     */
    public static function logLogin(User $user, string $ipAddress = null, string $userAgent = null): self
    {
        return static::log([
            'user_id' => $user->id,
            'action' => 'login',
            'description' => 'User logged in successfully',
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'level' => 'info',
            'metadata' => [
                'method' => 'web',
                'remember_me' => false,
            ],
        ]);
    }

    /**
     * Log a failed login attempt.
     */
    public static function logLoginFailed(string $email, string $ipAddress = null, string $userAgent = null): self
    {
        return static::log([
            'user_id' => null,
            'action' => 'login_failed',
            'description' => 'Failed login attempt for email: ' . $email,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'level' => 'warning',
            'metadata' => [
                'email' => $email,
                'method' => 'web',
            ],
        ]);
    }

    /**
     * Log a user logout.
     */
    public static function logLogout(User $user, string $ipAddress = null, string $userAgent = null): self
    {
        return static::log([
            'user_id' => $user->id,
            'action' => 'logout',
            'description' => 'User logged out',
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'level' => 'info',
        ]);
    }

    /**
     * Log a profile update.
     */
    public static function logProfileUpdate(User $user, array $oldValues, array $newValues): self
    {
        return static::log([
            'user_id' => $user->id,
            'action' => 'profile_updated',
            'description' => 'User profile was updated',
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'model_type' => Profile::class,
            'model_id' => $user->profile?->id,
            'level' => 'info',
            'metadata' => [
                'fields_changed' => array_keys(array_diff_assoc($newValues, $oldValues)),
            ],
        ]);
    }

    /**
     * Log a password reset request.
     */
    public static function logPasswordResetRequest(User $user, string $ipAddress = null): self
    {
        return static::log([
            'user_id' => $user->id,
            'action' => 'password_reset_request',
            'description' => 'User requested password reset',
            'ip_address' => $ipAddress,
            'level' => 'info',
            'metadata' => [
                'email' => $user->email,
            ],
        ]);
    }

    /**
     * Log a password reset completion.
     */
    public static function logPasswordResetCompleted(User $user, string $ipAddress = null): self
    {
        return static::log([
            'user_id' => $user->id,
            'action' => 'password_reset_completed',
            'description' => 'User completed password reset',
            'ip_address' => $ipAddress,
            'level' => 'info',
            'metadata' => [
                'email' => $user->email,
            ],
        ]);
    }

    /**
     * Log two-factor authentication enabled.
     */
    public static function logTwoFactorEnabled(User $user): self
    {
        return static::log([
            'user_id' => $user->id,
            'action' => 'two_factor_enabled',
            'description' => 'User enabled two-factor authentication',
            'level' => 'info',
            'metadata' => [
                'method' => 'totp',
            ],
        ]);
    }

    /**
     * Log two-factor authentication disabled.
     */
    public static function logTwoFactorDisabled(User $user): self
    {
        return static::log([
            'user_id' => $user->id,
            'action' => 'two_factor_disabled',
            'description' => 'User disabled two-factor authentication',
            'level' => 'warning',
            'metadata' => [
                'method' => 'totp',
            ],
        ]);
    }

    /**
     * Log two-factor authentication failure.
     */
    public static function logTwoFactorFailed(User $user, string $ipAddress = null): self
    {
        return static::log([
            'user_id' => $user->id,
            'action' => 'two_factor_failed',
            'description' => 'Two-factor authentication failed',
            'ip_address' => $ipAddress,
            'level' => 'warning',
            'metadata' => [
                'method' => 'totp',
            ],
        ]);
    }

    /**
     * Log recovery code usage.
     */
    public static function logRecoveryCodeUsed(User $user, string $ipAddress = null): self
    {
        return static::log([
            'user_id' => $user->id,
            'action' => 'recovery_code_used',
            'description' => 'User used a recovery code',
            'ip_address' => $ipAddress,
            'level' => 'warning',
            'metadata' => [
                'remaining_codes' => $user->twoFactorAuthentication?->getRemainingRecoveryCodesCount() ?? 0,
            ],
        ]);
    }

    /**
     * Log social account connection.
     */
    public static function logSocialAccountConnected(User $user, string $provider, string $ipAddress = null): self
    {
        return static::log([
            'user_id' => $user->id,
            'action' => 'social_account_connected',
            'description' => "User connected {$provider} account",
            'ip_address' => $ipAddress,
            'level' => 'info',
            'metadata' => [
                'provider' => $provider,
            ],
        ]);
    }

    /**
     * Log social account disconnection.
     */
    public static function logSocialAccountDisconnected(User $user, string $provider, string $ipAddress = null): self
    {
        return static::log([
            'user_id' => $user->id,
            'action' => 'social_account_disconnected',
            'description' => "User disconnected {$provider} account",
            'ip_address' => $ipAddress,
            'level' => 'info',
            'metadata' => [
                'provider' => $provider,
            ],
        ]);
    }

    /**
     * Log security-related actions.
     */
    public static function logSecurity(string $action, string $description, array $metadata = [], string $level = 'warning'): self
    {
        return static::log([
            'action' => $action,
            'description' => $description,
            'level' => $level,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Get formatted description with context.
     */
    public function getFormattedDescriptionAttribute(): string
    {
        if ($this->description) {
            return $this->description;
        }

        // Generate description from action if not provided
        return match($this->action) {
            'login' => 'User logged in',
            'logout' => 'User logged out',
            'login_failed' => 'Failed login attempt',
            'profile_updated' => 'Profile updated',
            'password_reset_request' => 'Password reset requested',
            'password_reset_completed' => 'Password reset completed',
            'two_factor_enabled' => 'Two-factor authentication enabled',
            'two_factor_disabled' => 'Two-factor authentication disabled',
            'two_factor_failed' => 'Two-factor authentication failed',
            'recovery_code_used' => 'Recovery code used',
            'social_account_connected' => 'Social account connected',
            'social_account_disconnected' => 'Social account disconnected',
            default => ucfirst(str_replace('_', ' ', $this->action)),
        };
    }

    /**
     * Get the level color for UI display.
     */
    public function getLevelColorAttribute(): string
    {
        return match($this->level) {
            'info' => 'blue',
            'warning' => 'yellow',
            'error' => 'red',
            'critical' => 'red',
            default => 'gray',
        };
    }

    /**
     * Get the icon for the action.
     */
    public function getIconAttribute(): string
    {
        return match($this->action) {
            'login' => 'sign-in-alt',
            'logout' => 'sign-out-alt',
            'login_failed' => 'exclamation-triangle',
            'profile_updated' => 'user-edit',
            'password_reset_request' => 'key',
            'password_reset_completed' => 'check-circle',
            'two_factor_enabled' => 'shield-alt',
            'two_factor_disabled' => 'shield-alt',
            'two_factor_failed' => 'times-circle',
            'recovery_code_used' => 'key',
            'social_account_connected' => 'link',
            'social_account_disconnected' => 'unlink',
            default => 'info-circle',
        };
    }
}
