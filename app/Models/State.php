<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class State extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'country_id', 'country_code', 'fips_code', 'iso2', 
        'latitude', 'longitude', 'flag', 'wikiDataId'
    ];

    protected $casts = [
        'flag' => 'boolean',
    ];

    /**
     * A state belongs to a country.
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * A state has many cities.
     */
    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }
}
