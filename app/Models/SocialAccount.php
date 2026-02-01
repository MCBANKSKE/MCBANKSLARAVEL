<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialAccount extends Model
{
    protected $fillable = [
        'user_id',
        'provider',
        'provider_id',
        'provider_token',
        'provider_refresh_token',
        'provider_expires_in',
        'provider_data',
        'nickname',
        'name',
        'email',
        'avatar',
    ];

    protected $casts = [
        'provider_data' => 'array',
        'provider_expires_in' => 'integer',
    ];

    /**
     * Get the user that owns the social account.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the avatar URL from provider or default.
     */
    public function getAvatarUrlAttribute(): string
    {
        return $this->avatar ?: 'https://ui-avatars.com/api/?name=' . urlencode($this->name ?: $this->nickname);
    }

    /**
     * Check if the social account has a valid token.
     */
    public function hasValidToken(): bool
    {
        return !empty($this->provider_token) && 
               (!$this->provider_expires_in || $this->provider_expires_in > now()->timestamp);
    }

    /**
     * Get the provider name in a formatted way.
     */
    public function getFormattedProviderAttribute(): string
    {
        return ucfirst($this->provider);
    }

    /**
     * Scope to get social accounts by provider.
     */
    public function scopeByProvider($query, string $provider)
    {
        return $query->where('provider', $provider);
    }

    /**
     * Scope to get social accounts with valid tokens.
     */
    public function scopeWithValidToken($query)
    {
        return $query->whereNotNull('provider_token')
                   ->where(function ($q) {
                       $q->whereNull('provider_expires_in')
                         ->orWhere('provider_expires_in', '>', now()->timestamp);
                   });
    }
}
