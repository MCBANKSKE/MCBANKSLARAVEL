<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_super_admin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_super_admin' => 'boolean',
        ];
    }

    /**
     * Get the user's profile.
     */
    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    /**
     * Get the user's social accounts.
     */
    public function socialAccounts(): HasMany
    {
        return $this->hasMany(SocialAccount::class);
    }

    /**
     * Get the user's avatar through their profile.
     */
    public function getAvatarAttribute()
    {
        return $this->profile?->avatar;
    }

    /**
     * Get the user's avatar URL.
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->profile && $this->profile->avatar) {
            return $this->profile->avatar->url;
        }

        return app(\App\Services\AvatarUploadService::class)->getDefaultAvatarUrl();
    }

    /**
     * Get the user's thumbnail URL.
     */
    public function getThumbnailUrlAttribute(): string
    {
        if ($this->profile && $this->profile->avatar) {
            return $this->profile->avatar->thumbnail_url;
        }

        return app(\App\Services\AvatarUploadService::class)->getDefaultAvatarUrl();
    }

    /**
     * Check if user has a complete profile.
     */
    public function hasCompleteProfile(): bool
    {
        return $this->profile && $this->profile->completion_percentage >= 80;
    }

    /**
     * Get profile completion percentage.
     */
    public function getProfileCompletionPercentage(): int
    {
        return $this->profile?->completion_percentage ?? 0;
    }

    /**
     * Create or get user profile.
     */
    public function getOrCreateProfile(): Profile
    {
        if (!$this->profile) {
            $this->profile = Profile::create(['user_id' => $this->id]);
        }

        return $this->profile;
    }

    /**
     * Check if user can view another user's profile.
     */
    public function canViewProfile(User $targetUser): bool
    {
        // Users can always view their own profile
        if ($this->id === $targetUser->id) {
            return true;
        }

        // Check if target profile is public
        if ($targetUser->profile) {
            $privacy = $targetUser->profile->privacy_settings ?? $targetUser->profile->getDefaultPrivacySettings();
            return $privacy['profile_public'] ?? true;
        }

        return false;
    }

    /**
     * Get display name with fallback.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->name ?? $this->email ?? 'Unknown User';
    }

    /**
     * Get full location from profile.
     */
    public function getLocationAttribute(): string
    {
        return $this->profile?->full_location ?? '';
    }

    /**
     * Check if user has social account for specific provider.
     */
    public function hasSocialAccount(string $provider): bool
    {
        return $this->socialAccounts()->byProvider($provider)->exists();
    }

    /**
     * Get social account for specific provider.
     */
    public function getSocialAccount(string $provider): ?SocialAccount
    {
        return $this->socialAccounts()->byProvider($provider)->first();
    }

    /**
     * Get all connected social providers.
     */
    public function getConnectedProvidersAttribute(): array
    {
        return $this->socialAccounts()->pluck('provider')->toArray();
    }

    /**
     * Check if user has any social accounts.
     */
    public function hasSocialAccounts(): bool
    {
        return $this->socialAccounts()->exists();
    }

    /**
     * Find user by social account.
     */
    public static function findBySocialAccount(string $provider, string $providerId): ?self
    {
        return static::whereHas('socialAccounts', function ($query) use ($provider, $providerId) {
            $query->where('provider', $provider)->where('provider_id', $providerId);
        })->first();
    }

    /**
     * Create user from social account data.
     */
    public static function createFromSocialAccount(array $socialData): self
    {
        $user = static::create([
            'name' => $socialData['name'] ?? $socialData['nickname'] ?? 'Unknown User',
            'email' => $socialData['email'] ?? null,
            'password' => bcrypt(\Str::random(32)), // Random password
            'email_verified_at' => $socialData['email'] ? now() : null,
        ]);

        // Create social account
        $user->socialAccounts()->create([
            'provider' => $socialData['provider'],
            'provider_id' => $socialData['provider_id'],
            'provider_token' => $socialData['token'] ?? null,
            'provider_refresh_token' => $socialData['refresh_token'] ?? null,
            'provider_expires_in' => $socialData['expires_in'] ?? null,
            'provider_data' => $socialData['user'] ?? null,
            'nickname' => $socialData['nickname'] ?? null,
            'name' => $socialData['name'] ?? null,
            'email' => $socialData['email'] ?? null,
            'avatar' => $socialData['avatar'] ?? null,
        ]);

        // Create profile
        $user->getOrCreateProfile();

        return $user;
    }
}
