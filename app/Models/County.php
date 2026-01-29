<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\SubCounty;

class County extends Model
{
    protected $fillable = [
        'county_name',
    ];
    
    public $timestamps = false;
    
    public function subcounties(): HasMany
    {
        return $this->hasMany(SubCounty::class, 'county_id', 'id');
    }

    protected static function booted()
    {
        static::addGlobalScope('ordered', function ($query) {
            $query->orderBy('county_name');
        });
    }
}