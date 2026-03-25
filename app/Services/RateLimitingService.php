<?php

namespace App\Services;

use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class RateLimitingService
{
    protected RateLimiter $limiter;

    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    /**
     * Check if the request is rate limited.
     */
    public function checkRateLimit(Request $request, string $key, int $maxAttempts, int $decayMinutes): bool
    {
        return $this->limiter->tooManyAttempts(
            $this->resolveRequestSignature($request, $key),
            $maxAttempts,
            $decayMinutes * 60
        );
    }

    /**
     * Get the rate limit headers.
     */
    public function getRateLimitHeaders(Request $request, string $key, int $maxAttempts, int $decayMinutes): array
    {
        $signature = $this->resolveRequestSignature($request, $key);
        $remaining = $this->limiter->retriesLeft($signature, $maxAttempts);
        $reset = $this->limiter->availableIn($signature);

        return [
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => max(0, $remaining),
            'X-RateLimit-Reset' => $reset,
        ];
    }

    /**
     * Increment the rate limit attempts.
     */
    public function incrementRateLimit(Request $request, string $key): void
    {
        $this->limiter->hit($this->resolveRequestSignature($request, $key));
    }

    /**
     * Clear the rate limit for the request.
     */
    public function clearRateLimit(Request $request, string $key): void
    {
        $this->limiter->clear($this->resolveRequestSignature($request, $key));
    }

    /**
     * Get the rate limit key for the request.
     */
    protected function resolveRequestSignature(Request $request, string $key): string
    {
        if ($user = $request->user()) {
            return sha1($key . ':' . $user->id . ':' . $request->ip());
        }

        return sha1($key . ':' . $request->ip());
    }

    /**
     * Authentication rate limiting.
     */
    public function checkAuthRateLimit(Request $request): bool
    {
        return $this->checkRateLimit($request, 'auth', 5, 1); // 5 attempts per minute
    }

    /**
     * Login rate limiting.
     */
    public function checkLoginRateLimit(Request $request): bool
    {
        return $this->checkRateLimit($request, 'login', 5, 15); // 5 attempts per 15 minutes
    }

    /**
     * Registration rate limiting.
     */
    public function checkRegistrationRateLimit(Request $request): bool
    {
        return $this->checkRateLimit($request, 'registration', 3, 60); // 3 attempts per hour
    }

    /**
     * Password reset rate limiting.
     */
    public function checkPasswordResetRateLimit(Request $request): bool
    {
        return $this->checkRateLimit($request, 'password_reset', 3, 60); // 3 attempts per hour
    }

    /**
     * Two-factor authentication rate limiting.
     */
    public function checkTwoFactorRateLimit(Request $request): bool
    {
        return $this->checkRateLimit($request, '2fa', 5, 15); // 5 attempts per 15 minutes
    }

    /**
     * Profile update rate limiting.
     */
    public function checkProfileUpdateRateLimit(Request $request): bool
    {
        return $this->checkRateLimit($request, 'profile_update', 10, 60); // 10 attempts per hour
    }

    /**
     * Avatar upload rate limiting.
     */
    public function checkAvatarUploadRateLimit(Request $request): bool
    {
        return $this->checkRateLimit($request, 'avatar_upload', 5, 60); // 5 attempts per hour
    }

    /**
     * API rate limiting for authenticated users.
     */
    public function checkApiRateLimit(Request $request): bool
    {
        $maxAttempts = $request->user() ? 1000 : 100; // Authenticated users get more requests
        return $this->checkRateLimit($request, 'api', $maxAttempts, 60); // Per hour
    }

    /**
     * API rate limiting for specific endpoints.
     */
    public function checkApiEndpointRateLimit(Request $request, string $endpoint): bool
    {
        $limits = $this->getApiEndpointLimits();
        
        $limit = $limits[$endpoint] ?? [
            'max_attempts' => 60,
            'decay_minutes' => 60
        ];

        return $this->checkRateLimit($request, 'api_' . $endpoint, $limit['max_attempts'], $limit['decay_minutes']);
    }

    /**
     * Get rate limits for specific API endpoints.
     */
    protected function getApiEndpointLimits(): array
    {
        return [
            'auth.login' => ['max_attempts' => 5, 'decay_minutes' => 15],
            'auth.register' => ['max_attempts' => 3, 'decay_minutes' => 60],
            'auth.logout' => ['max_attempts' => 20, 'decay_minutes' => 60],
            'auth.refresh' => ['max_attempts' => 10, 'decay_minutes' => 60],
            'profile.show' => ['max_attempts' => 200, 'decay_minutes' => 60],
            'profile.update' => ['max_attempts' => 10, 'decay_minutes' => 60],
            'profile.avatar' => ['max_attempts' => 5, 'decay_minutes' => 60],
            'users.public' => ['max_attempts' => 100, 'decay_minutes' => 60],
            'geo.countries' => ['max_attempts' => 1000, 'decay_minutes' => 60],
            'geo.states' => ['max_attempts' => 500, 'decay_minutes' => 60],
            'geo.cities' => ['max_attempts' => 300, 'decay_minutes' => 60],
            'social.connect' => ['max_attempts' => 5, 'decay_minutes' => 60],
            'social.disconnect' => ['max_attempts' => 10, 'decay_minutes' => 60],
        ];
    }

    /**
     * Email sending rate limiting.
     */
    public function checkEmailRateLimit(Request $request): bool
    {
        return $this->checkRateLimit($request, 'email', 10, 60); // 10 emails per hour
    }

    /**
     * Verification email rate limiting.
     */
    public function checkVerificationEmailRateLimit(Request $request): bool
    {
        return $this->checkRateLimit($request, 'verification_email', 3, 300); // 3 per 5 minutes
    }

    /**
     * Social authentication rate limiting.
     */
    public function checkSocialAuthRateLimit(Request $request): bool
    {
        return $this->checkRateLimit($request, 'social_auth', 10, 60); // 10 attempts per hour
    }

    /**
     * Get rate limit status for a specific key.
     */
    public function getRateLimitStatus(Request $request, string $key, int $maxAttempts): array
    {
        $signature = $this->resolveRequestSignature($request, $key);
        $remaining = $this->limiter->retriesLeft($signature, $maxAttempts);
        $availableIn = $this->limiter->availableIn($signature);

        return [
            'remaining' => max(0, $remaining),
            'available_in' => $availableIn,
            'max_attempts' => $maxAttempts,
            'is_limited' => $remaining <= 0,
        ];
    }

    /**
     * Get all rate limit statuses for the current request.
     */
    public function getAllRateLimitStatuses(Request $request): array
    {
        $statuses = [];

        $rateLimits = [
            'auth' => 5,
            'login' => 5,
            'registration' => 3,
            'password_reset' => 3,
            '2fa' => 5,
            'profile_update' => 10,
            'avatar_upload' => 5,
            'api' => $request->user() ? 1000 : 100,
            'email' => 10,
            'verification_email' => 3,
            'social_auth' => 10,
        ];

        foreach ($rateLimits as $key => $maxAttempts) {
            $statuses[$key] = $this->getRateLimitStatus($request, $key, $maxAttempts);
        }

        return $statuses;
    }

    /**
     * Check if IP is blacklisted.
     */
    public function isIpBlacklisted(string $ip): bool
    {
        return Cache::has('blacklisted_ip:' . $ip);
    }

    /**
     * Blacklist an IP address.
     */
    public function blacklistIp(string $ip, int $durationInMinutes = 60): void
    {
        Cache::put('blacklisted_ip:' . $ip, true, $durationInMinutes * 60);
    }

    /**
     * Remove IP from blacklist.
     */
    public function unblacklistIp(string $ip): void
    {
        Cache::forget('blacklisted_ip:' . $ip);
    }

    /**
     * Check if user is rate limited based on suspicious activity.
     */
    public function checkSuspiciousActivityRateLimit(Request $request): bool
    {
        $ip = $request->ip();
        $key = 'suspicious_activity:' . $ip;
        
        $attempts = Cache::get($key, 0);
        
        if ($attempts >= 20) { // 20 suspicious activities trigger rate limit
            return true;
        }
        
        Cache::put($key, $attempts + 1, 3600); // Reset after 1 hour
        
        return false;
    }

    /**
     * Log suspicious activity.
     */
    public function logSuspiciousActivity(Request $request, string $activity): void
    {
        $data = [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'activity' => $activity,
            'user_id' => $request->user()?->id,
            'timestamp' => now(),
        ];

        Cache::put('suspicious_activity_log:' . Str::random(10), $data, 86400); // Keep for 24 hours
        
        // Increment suspicious activity counter
        $this->checkSuspiciousActivityRateLimit($request);
    }

    /**
     * Get rate limit statistics.
     */
    public function getRateLimitStatistics(): array
    {
        return [
            'total_rate_limited_requests' => Cache::get('stats.rate_limited', 0),
            'total_blacklisted_ips' => $this->getBlacklistedIpCount(),
            'total_suspicious_activities' => $this->getSuspiciousActivityCount(),
            'most_common_rate_limits' => $this->getMostCommonRateLimits(),
        ];
    }

    /**
     * Get count of blacklisted IPs.
     */
    protected function getBlacklistedIpCount(): int
    {
        // This is a simplified implementation
        // In production, you might want to use a more efficient method
        return Cache::get('stats.blacklisted_ips', 0);
    }

    /**
     * Get count of suspicious activities.
     */
    protected function getSuspiciousActivityCount(): int
    {
        return Cache::get('stats.suspicious_activities', 0);
    }

    /**
     * Get most common rate limit triggers.
     */
    protected function getMostCommonRateLimits(): array
    {
        return Cache::get('stats.most_common_limits', [
            'auth' => 0,
            'login' => 0,
            'api' => 0,
        ]);
    }

    /**
     * Increment rate limit statistics.
     */
    public function incrementRateLimitStats(string $key): void
    {
        Cache::increment('stats.rate_limited');
        Cache::increment('stats.most_common_limits.' . $key, 1);
    }
}
