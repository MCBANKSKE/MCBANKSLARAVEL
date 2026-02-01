<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
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
}
