<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TwoFactorAuthentication extends Model
{
    protected $fillable = [
        'user_id',
        'secret_key',
        'recovery_codes',
        'enabled',
        'confirmed_at',
        'last_used_at',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'recovery_codes' => 'array',
        'confirmed_at' => 'datetime',
        'last_used_at' => 'datetime',
    ];

    /**
     * Get the user that owns the two factor authentication.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Generate a new secret key.
     */
    public static function generateSecretKey(): string
    {
        return app('pragmarx.google2fa')->generateSecretKey();
    }

    /**
     * Generate QR code URL.
     */
    public function getQrCodeUrl(): string
    {
        $google2fa = app('pragmarx.google2fa');
        
        return $google2fa->getQRCodeUrl(
            config('app.name'),
            $this->user->email,
            $this->secret_key
        );
    }

    /**
     * Generate recovery codes.
     */
    public static function generateRecoveryCodes(int $count = 8): array
    {
        $codes = [];
        
        for ($i = 0; $i < $count; $i++) {
            $codes[] = strtoupper(str()->random(4) . '-' . str()->random(4));
        }
        
        return $codes;
    }

    /**
     * Verify the OTP code.
     */
    public function verifyCode(string $code): bool
    {
        if (!$this->secret_key) {
            return false;
        }

        $google2fa = app('pragmarx.google2fa');
        
        return $google2fa->verifyKey($this->secret_key, $code);
    }

    /**
     * Verify recovery code.
     */
    public function verifyRecoveryCode(string $code): bool
    {
        if (!$this->recovery_codes) {
            return false;
        }

        $code = strtoupper($code);
        
        if (in_array($code, $this->recovery_codes)) {
            // Remove the used recovery code
            $this->recovery_codes = array_values(array_diff($this->recovery_codes, [$code]));
            $this->save();
            
            return true;
        }

        return false;
    }

    /**
     * Enable two factor authentication.
     */
    public function enable(): void
    {
        $this->secret_key = static::generateSecretKey();
        $this->recovery_codes = static::generateRecoveryCodes();
        $this->enabled = true;
        $this->confirmed_at = now();
        $this->save();
    }

    /**
     * Disable two factor authentication.
     */
    public function disable(): void
    {
        $this->secret_key = null;
        $this->recovery_codes = null;
        $this->enabled = false;
        $this->confirmed_at = null;
        $this->last_used_at = null;
        $this->save();
    }

    /**
     * Regenerate recovery codes.
     */
    public function regenerateRecoveryCodes(): void
    {
        $this->recovery_codes = static::generateRecoveryCodes();
        $this->save();
    }

    /**
     * Update last used timestamp.
     */
    public function updateLastUsed(): void
    {
        $this->last_used_at = now();
        $this->save();
    }

    /**
     * Check if user has remaining recovery codes.
     */
    public function hasRecoveryCodes(): bool
    {
        return !empty($this->recovery_codes) && count($this->recovery_codes) > 0;
    }

    /**
     * Get remaining recovery codes count.
     */
    public function getRemainingRecoveryCodesCount(): int
    {
        return $this->recovery_codes ? count($this->recovery_codes) : 0;
    }
}
