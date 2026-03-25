<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class AuditService
{
    /**
     * Log an audit event.
     */
    public function log(array $data): AuditLog
    {
        // Add default values
        $data = array_merge([
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'level' => 'info',
        ], $data);

        // Add user ID if not provided and user is authenticated
        if (!isset($data['user_id']) && Auth::check()) {
            $data['user_id'] = Auth::id();
        }

        $auditLog = AuditLog::log($data);

        // Update statistics
        $this->updateStatistics($auditLog);

        return $auditLog;
    }

    /**
     * Log user authentication events.
     */
    public function logAuth(string $action, array $data = []): AuditLog
    {
        $description = match($action) {
            'login' => 'User logged in successfully',
            'logout' => 'User logged out',
            'login_failed' => 'Failed login attempt',
            'password_reset_request' => 'Password reset requested',
            'password_reset_completed' => 'Password reset completed',
            default => 'Authentication event: ' . $action,
        };

        return $this->log(array_merge([
            'action' => $action,
            'description' => $description,
            'level' => $action === 'login_failed' ? 'warning' : 'info',
        ], $data));
    }

    /**
     * Log user profile changes.
     */
    public function logProfileChange(string $action, User $user, array $oldValues = null, array $newValues = null): AuditLog
    {
        $description = match($action) {
            'profile_updated' => 'User profile updated',
            'avatar_uploaded' => 'User avatar uploaded',
            'avatar_deleted' => 'User avatar deleted',
            'privacy_settings_updated' => 'Privacy settings updated',
            default => 'Profile action: ' . $action,
        };

        return $this->log([
            'user_id' => $user->id,
            'action' => $action,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'model_type' => \App\Models\Profile::class,
            'model_id' => $user->profile?->id,
            'level' => 'info',
        ]);
    }

    /**
     * Log two-factor authentication events.
     */
    public function logTwoFactor(string $action, User $user, array $data = []): AuditLog
    {
        $description = match($action) {
            'two_factor_enabled' => 'Two-factor authentication enabled',
            'two_factor_disabled' => 'Two-factor authentication disabled',
            'two_factor_failed' => 'Two-factor authentication failed',
            'recovery_code_used' => 'Recovery code used',
            'recovery_codes_regenerated' => 'Recovery codes regenerated',
            default => 'Two-factor event: ' . $action,
        };

        $level = match($action) {
            'two_factor_failed', 'recovery_code_used' => 'warning',
            'two_factor_disabled' => 'warning',
            default => 'info',
        };

        return $this->log(array_merge([
            'user_id' => $user->id,
            'action' => $action,
            'description' => $description,
            'level' => $level,
        ], $data));
    }

    /**
     * Log social authentication events.
     */
    public function logSocialAuth(string $action, User $user, string $provider, array $data = []): AuditLog
    {
        $description = match($action) {
            'social_account_connected' => "Connected {$provider} account",
            'social_account_disconnected' => "Disconnected {$provider} account",
            'social_login' => "Logged in with {$provider}",
            'social_login_failed' => "Failed {$provider} login",
            default => 'Social auth event: ' . $action,
        };

        return $this->log(array_merge([
            'user_id' => $user->id,
            'action' => $action,
            'description' => $description,
            'level' => str_contains($action, 'failed') ? 'warning' : 'info',
            'metadata' => array_merge(['provider' => $provider], $data),
        ]));
    }

    /**
     * Log security-related events.
     */
    public function logSecurity(string $action, string $description, array $data = [], string $level = 'warning'): AuditLog
    {
        return $this->log(array_merge([
            'action' => $action,
            'description' => $description,
            'level' => $level,
        ], $data));
    }

    /**
     * Log API events.
     */
    public function logApi(string $action, array $data = []): AuditLog
    {
        $description = match($action) {
            'api_request' => 'API request made',
            'api_error' => 'API error occurred',
            'api_rate_limit' => 'API rate limit exceeded',
            'api_unauthorized' => 'Unauthorized API access',
            default => 'API event: ' . $action,
        };

        $level = match($action) {
            'api_error', 'api_rate_limit', 'api_unauthorized' => 'warning',
            default => 'info',
        };

        return $this->log(array_merge([
            'action' => $action,
            'description' => $description,
            'level' => $level,
        ], $data));
    }

    /**
     * Get audit logs for a user.
     */
    public function getUserLogs(User $user, int $limit = 50, array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        $query = AuditLog::forUser($user->id)
            ->with('model')
            ->latest('created_at')
            ->limit($limit);

        // Apply filters
        if (isset($filters['action'])) {
            $query->forAction($filters['action']);
        }

        if (isset($filters['level'])) {
            $query->forLevel($filters['level']);
        }

        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $query->betweenDates($filters['start_date'], $filters['end_date']);
        }

        return $query->get();
    }

    /**
     * Get security-related logs.
     */
    public function getSecurityLogs(int $limit = 100): \Illuminate\Database\Eloquent\Collection
    {
        return AuditLog::security()
            ->with('user')
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get error logs.
     */
    public function getErrorLogs(int $limit = 100): \Illuminate\Database\Eloquent\Collection
    {
        return AuditLog::errors()
            ->with('user')
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get audit statistics.
     */
    public function getStatistics(): array
    {
        return Cache::remember('audit_statistics', 300, function () {
            $now = now();
            $last24Hours = $now->subDay();
            $last7Days = $now->subDays(7);
            $last30Days = $now->subDays(30);

            return [
                'total_logs' => AuditLog::count(),
                'last_24_hours' => AuditLog::where('created_at', '>=', $last24Hours)->count(),
                'last_7_days' => AuditLog::where('created_at', '>=', $last7Days)->count(),
                'last_30_days' => AuditLog::where('created_at', '>=', $last30Days)->count(),
                'security_events' => AuditLog::security()->count(),
                'error_events' => AuditLog::errors()->count(),
                'unique_users' => AuditLog::distinct('user_id')->whereNotNull('user_id')->count('user_id'),
                'top_actions' => $this->getTopActions(10),
                'top_ips' => $this->getTopIPs(10),
                'activity_by_level' => $this->getActivityByLevel(),
            ];
        });
    }

    /**
     * Get most common actions.
     */
    protected function getTopActions(int $limit = 10): array
    {
        return AuditLog::select('action', \DB::raw('count(*) as count'))
            ->groupBy('action')
            ->orderByDesc('count')
            ->limit($limit)
            ->pluck('count', 'action')
            ->toArray();
    }

    /**
     * Get most active IP addresses.
     */
    protected function getTopIPs(int $limit = 10): array
    {
        return AuditLog::select('ip_address', \DB::raw('count(*) as count'))
            ->whereNotNull('ip_address')
            ->groupBy('ip_address')
            ->orderByDesc('count')
            ->limit($limit)
            ->pluck('count', 'ip_address')
            ->toArray();
    }

    /**
     * Get activity breakdown by level.
     */
    protected function getActivityByLevel(): array
    {
        return AuditLog::select('level', \DB::raw('count(*) as count'))
            ->groupBy('level')
            ->orderByDesc('count')
            ->pluck('count', 'level')
            ->toArray();
    }

    /**
     * Update audit statistics.
     */
    protected function updateStatistics(AuditLog $auditLog): void
    {
        // Update real-time statistics
        Cache::increment('audit_stats.total_logs');
        Cache::increment('audit_stats.last_24_hours');
        Cache::increment('audit_stats.action_' . $auditLog->action);
        Cache::increment('audit_stats.level_' . $auditLog->level);

        if ($auditLog->ip_address) {
            Cache::increment('audit_stats.ip_' . $auditLog->ip_address);
        }

        // Clear cached statistics
        Cache::forget('audit_statistics');
    }

    /**
     * Clean up old audit logs.
     */
    public function cleanupOldLogs(int $daysToKeep = 90): int
    {
        $cutoffDate = now()->subDays($daysToKeep);
        
        $deleted = AuditLog::where('created_at', '<', $cutoffDate)->delete();

        // Update statistics
        Cache::forget('audit_statistics');

        return $deleted;
    }

    /**
     * Export audit logs to CSV.
     */
    public function exportToCSV(array $filters = []): string
    {
        $query = AuditLog::with('user', 'model');

        // Apply filters
        if (isset($filters['user_id'])) {
            $query->forUser($filters['user_id']);
        }

        if (isset($filters['action'])) {
            $query->forAction($filters['action']);
        }

        if (isset($filters['level'])) {
            $query->forLevel($filters['level']);
        }

        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $query->betweenDates($filters['start_date'], $filters['end_date']);
        }

        $logs = $query->orderBy('created_at', 'desc')->get();

        $csv = "ID,User,Action,Description,IP Address,User Agent,Level,Created At\n";

        foreach ($logs as $log) {
            $csv .= sprintf(
                "%d,%s,%s,\"%s\",%s,\"%s\",%s,%s\n",
                $log->id,
                $log->user?->name ?? 'System',
                $log->action,
                str_replace('"', '""', $log->formatted_description),
                $log->ip_address ?? '',
                str_replace('"', '""', $log->user_agent ?? ''),
                $log->level,
                $log->created_at->toDateTimeString()
            );
        }

        return $csv;
    }

    /**
     * Get suspicious activity patterns.
     */
    public function getSuspiciousActivity(): array
    {
        $patterns = [];

        // Check for multiple failed logins from same IP
        $failedLogins = AuditLog::forAction('login_failed')
            ->where('created_at', '>=', now()->subHour())
            ->select('ip_address', \DB::raw('count(*) as count'))
            ->groupBy('ip_address')
            ->having('count', '>', 5)
            ->get();

        if ($failedLogins->isNotEmpty()) {
            $patterns['multiple_failed_logins'] = $failedLogins->toArray();
        }

        // Check for rapid profile updates
        $rapidUpdates = AuditLog::forAction('profile_updated')
            ->where('created_at', '>=', now()->subMinutes(10))
            ->select('user_id', \DB::raw('count(*) as count'))
            ->groupBy('user_id')
            ->having('count', '>', 3)
            ->get();

        if ($rapidUpdates->isNotEmpty()) {
            $patterns['rapid_profile_updates'] = $rapidUpdates->toArray();
        }

        // Check for unusual IP addresses for users
        $unusualIPs = AuditLog::where('created_at', '>=', now()->subDay())
            ->select('user_id', 'ip_address', \DB::raw('count(*) as count'))
            ->whereNotNull('user_id')
            ->whereNotNull('ip_address')
            ->groupBy('user_id', 'ip_address')
            ->having('count', '>', 10)
            ->get();

        if ($unusualIPs->isNotEmpty()) {
            $patterns['unusual_ip_activity'] = $unusualIPs->toArray();
        }

        return $patterns;
    }

    /**
     * Check for security anomalies.
     */
    public function checkSecurityAnomalies(): array
    {
        $anomalies = [];

        // Check for concurrent logins from different IPs
        $concurrentLogins = AuditLog::forAction('login')
            ->where('created_at', '>=', now()->subMinutes(5))
            ->select('user_id', 'ip_address')
            ->distinct()
            ->get()
            ->groupBy('user_id')
            ->filter(function ($ips) {
                return $ips->count() > 1;
            });

        if ($concurrentLogins->isNotEmpty()) {
            $anomalies['concurrent_logins'] = $concurrentLogins->toArray();
        }

        // Check for password reset anomalies
        $passwordResets = AuditLog::forAction('password_reset_request')
            ->where('created_at', '>=', now()->subHour())
            ->select('ip_address', \DB::raw('count(*) as count'))
            ->groupBy('ip_address')
            ->having('count', '>', 3)
            ->get();

        if ($passwordResets->isNotEmpty()) {
            $anomalies['excessive_password_resets'] = $passwordResets->toArray();
        }

        return $anomalies;
    }
}
