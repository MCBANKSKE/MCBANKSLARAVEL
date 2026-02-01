<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Profile extends Model
{
    protected $fillable = [
        'user_id',
        'bio',
        'phone',
        'website',
        'country_id',
        'state_id',
        'city_id',
        'address',
        'privacy_settings',
        'completion_percentage',
    ];

    protected $casts = [
        'privacy_settings' => 'array',
        'completion_percentage' => 'integer',
    ];

    /**
     * Get the user that owns the profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the avatar associated with the profile.
     */
    public function avatar(): HasOne
    {
        return $this->hasOne(Avatar::class);
    }

    /**
     * Get the country for the profile.
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Get the state for the profile.
     */
    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    /**
     * Get the city for the profile.
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Get the default privacy settings.
     */
    public function getDefaultPrivacySettings(): array
    {
        return [
            'show_email' => false,
            'show_phone' => true,
            'show_location' => true,
            'show_website' => true,
            'allow_messages' => true,
            'profile_public' => true,
        ];
    }

    /**
     * Get a specific privacy setting value.
     */
    public function getPrivacySetting(string $key, mixed $default = null): mixed
    {
        $settings = $this->privacy_settings ?? $this->getDefaultPrivacySettings();
        return $settings[$key] ?? $default;
    }

    /**
     * Set a specific privacy setting.
     */
    public function setPrivacySetting(string $key, mixed $value): void
    {
        $settings = $this->privacy_settings ?? $this->getDefaultPrivacySettings();
        $settings[$key] = $value;
        $this->privacy_settings = $settings;
    }

    /**
     * Calculate profile completion percentage.
     */
    public function calculateCompletionPercentage(): int
    {
        $fields = [
            'bio' => $this->bio,
            'phone' => $this->phone,
            'website' => $this->website,
            'country_id' => $this->country_id,
            'state_id' => $this->state_id,
            'city_id' => $this->city_id,
            'address' => $this->address,
        ];

        $filledFields = collect($fields)->filter(fn($field) => !empty($field))->count();
        $totalFields = count($fields);
        
        return (int) round(($filledFields / $totalFields) * 100);
    }

    /**
     * Update completion percentage and save.
     */
    public function updateCompletionPercentage(): bool
    {
        $this->completion_percentage = $this->calculateCompletionPercentage();
        return $this->save();
    }

    /**
     * Get full location string.
     */
    public function getFullLocationAttribute(): string
    {
        $parts = array_filter([
            $this->city?->name,
            $this->state?->name,
            $this->country?->name,
        ]);

        return implode(', ', $parts);
    }

    /**
     * Get formatted website URL.
     */
    public function getFormattedWebsiteAttribute(): string
    {
        if (empty($this->website)) {
            return '';
        }

        $website = $this->website;
        if (!str_starts_with($website, 'http://') && !str_starts_with($website, 'https://')) {
            $website = 'https://' . $website;
        }

        return $website;
    }
}
