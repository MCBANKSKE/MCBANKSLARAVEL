<?php

namespace App\Services;

use App\Models\User;
use App\Models\TwoFactorAuthentication;
use Illuminate\Support\Facades\Cache;
use PragmaRX\Google2FA\Google2FA;
use SimpleSoftwareIO\QrCode\Generator as QrCodeGenerator;

class TwoFactorService
{
    protected Google2FA $google2fa;
    protected QrCodeGenerator $qrCode;

    public function __construct(Google2FA $google2fa, QrCodeGenerator $qrCode)
    {
        $this->google2fa = $google2fa;
        $this->qrCode = $qrCode;
    }

    /**
     * Generate new secret key for user.
     */
    public function generateSecretKey(User $user): string
    {
        $secret = $this->google2fa->generateSecretKey();
        
        // Store temporary secret in cache for verification
        Cache::put("2fa_secret_{$user->id}", $secret, now()->addMinutes(10));
        
        return $secret;
    }

    /**
     * Generate QR code for user.
     */
    public function generateQrCode(User $user, string $secret): string
    {
        $appName = config('app.name');
        $email = $user->email;
        
        $qrCodeUrl = $this->google2fa->getQRCodeUrl($appName, $email, $secret);
        
        return $this->qrCode->format('svg')
            ->size(200)
            ->generate($qrCodeUrl);
    }

    /**
     * Enable two factor authentication for user.
     */
    public function enable(User $user, string $code): bool
    {
        $secret = Cache::get("2fa_secret_{$user->id}");
        
        if (!$secret || !$this->google2fa->verifyKey($secret, $code)) {
            return false;
        }

        $twoFactor = $user->getOrCreateTwoFactor();
        $twoFactor->secret_key = $secret;
        $twoFactor->recovery_codes = TwoFactorAuthentication::generateRecoveryCodes();
        $twoFactor->enabled = true;
        $twoFactor->confirmed_at = now();
        $twoFactor->save();

        // Clear cache
        Cache::forget("2fa_secret_{$user->id}");

        return true;
    }

    /**
     * Disable two factor authentication for user.
     */
    public function disable(User $user): void
    {
        $user->disableTwoFactor();
    }

    /**
     * Verify two factor authentication code.
     */
    public function verify(User $user, string $code): bool
    {
        if (!$user->hasTwoFactorEnabled()) {
            return false;
        }

        return $user->verifyTwoFactorCode($code);
    }

    /**
     * Generate new recovery codes.
     */
    public function regenerateRecoveryCodes(User $user): array
    {
        $twoFactor = $user->getOrCreateTwoFactor();
        $twoFactor->regenerateRecoveryCodes();
        
        return $twoFactor->recovery_codes;
    }

    /**
     * Check if user has valid two factor setup.
     */
    public function hasValidSetup(User $user): bool
    {
        return $user->hasTwoFactorEnabled() && $user->twoFactorAuthentication->secret_key;
    }

    /**
     * Generate backup codes for user.
     */
    public function generateBackupCodes(int $count = 8): array
    {
        $codes = [];
        
        for ($i = 0; $i < $count; $i++) {
            $codes[] = strtoupper(str()->random(4) . '-' . str()->random(4));
        }
        
        return $codes;
    }

    /**
     * Verify backup code.
     */
    public function verifyBackupCode(User $user, string $code): bool
    {
        if (!$user->hasTwoFactorEnabled()) {
            return false;
        }

        return $user->twoFactorAuthentication->verifyRecoveryCode($code);
    }

    /**
     * Get user's two factor status.
     */
    public function getStatus(User $user): array
    {
        $twoFactor = $user->twoFactorAuthentication;
        
        return [
            'enabled' => $twoFactor?->enabled ?? false,
            'confirmed' => !is_null($twoFactor?->confirmed_at),
            'has_recovery_codes' => $twoFactor?->hasRecoveryCodes() ?? false,
            'recovery_codes_count' => $twoFactor?->getRemainingRecoveryCodesCount() ?? 0,
            'last_used_at' => $twoFactor?->last_used_at,
        ];
    }

    /**
     * Send two factor notification to user.
     */
    public function sendNotification(User $user, string $type): void
    {
        switch ($type) {
            case 'enabled':
                $user->notify(new \App\Notifications\TwoFactorEnabled());
                break;
            case 'disabled':
                $user->notify(new \App\Notifications\TwoFactorDisabled());
                break;
            case 'recovery_codes_used':
                $user->notify(new \App\Notifications\RecoveryCodeUsed());
                break;
        }
    }

    /**
     * Validate two factor setup before enabling.
     */
    public function validateSetup(User $user, string $code): array
    {
        $secret = Cache::get("2fa_secret_{$user->id}");
        
        if (!$secret) {
            return [
                'valid' => false,
                'message' => 'Two factor setup expired. Please start over.',
            ];
        }

        if (!$this->google2fa->verifyKey($secret, $code)) {
            return [
                'valid' => false,
                'message' => 'Invalid authentication code. Please try again.',
            ];
        }

        return [
            'valid' => true,
            'message' => 'Two factor authentication verified successfully.',
        ];
    }

    /**
     * Get current secret key from cache.
     */
    public function getCurrentSecret(User $user): ?string
    {
        return Cache::get("2fa_secret_{$user->id}");
    }

    /**
     * Clear temporary secret from cache.
     */
    public function clearSecret(User $user): void
    {
        Cache::forget("2fa_secret_{$user->id}");
    }

    /**
     * Check rate limiting for two factor attempts.
     */
    public function checkRateLimit(User $user): bool
    {
        $key = "2fa_attempts_{$user->id}";
        $attempts = Cache::get($key, 0);
        
        if ($attempts >= 5) {
            return false;
        }

        Cache::put($key, $attempts + 1, now()->addMinutes(15));
        
        return true;
    }

    /**
     * Clear rate limit for user.
     */
    public function clearRateLimit(User $user): void
    {
        Cache::forget("2fa_attempts_{$user->id}");
    }

    /**
     * Get two factor statistics.
     */
    public function getStatistics(): array
    {
        $totalUsers = User::count();
        $enabledUsers = User::whereHas('twoFactorAuthentication', function ($query) {
            $query->where('enabled', true);
        })->count();
        
        $percentage = $totalUsers > 0 ? ($enabledUsers / $totalUsers) * 100 : 0;
        
        return [
            'total_users' => $totalUsers,
            'enabled_users' => $enabledUsers,
            'adoption_rate' => round($percentage, 2),
        ];
    }
}
