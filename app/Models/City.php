<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class City extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'state_id', 'state_code', 'country_id', 'country_code',
        'latitude', 'longitude', 'flag', 'wikiDataId'
    ];

    protected $casts = [
        'flag' => 'boolean',
    ];

    /**
     * A city belongs to a state.
     */
    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    /**
     * A city belongs to a country.
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
}
