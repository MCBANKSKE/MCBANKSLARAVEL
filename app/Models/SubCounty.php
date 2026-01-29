<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * SubCounty Model - Kenyan Administrative Divisions
 * 
 * This model represents the sub-county administrative structure in Kenya,
 * which includes constituencies and wards. The data is automatically
 * seeded when you run the database migrations.
 * 
 * @package App\Models
 */
class SubCounty extends Model
{
    protected $fillable = ['county_id', 'constituency_name', 'ward', 'alias'];
    
    public $timestamps = false;
    
    public function county(): BelongsTo
    {
        return $this->belongsTo(County::class, 'county_id', 'id');
    }

    /**
     * Get unique constituency names for a given county
     * 
     * @param int $countyId The ID of the county
     * @return \Illuminate\Support\Collection Sorted list of unique constituency names
     * 
     * @example
     * // Get all constituencies in Mombasa County (ID: 1)
     * $constituencies = SubCounty::getUniqueConstituencies(1);
     * // Result: ['changamwe', 'jomvu', 'kisauni', 'nyali', 'likoni', 'mvita']
     */
    public static function getUniqueConstituencies($countyId)
    {
        return self::where('county_id', $countyId)
            ->distinct()
            ->pluck('constituency_name')
            ->filter(function ($constituency) {
                return !empty($constituency) && strtolower($constituency) !== 'reception';
            })
            ->sort()
            ->values();
    }

    /**
     * Get unique ward names for a given county
     * 
     * @param int $countyId The ID of the county
     * @return \Illuminate\Support\Collection Sorted list of unique ward names
     * 
     * @example
     * // Get all wards in Mombasa County (ID: 1)
     * $wards = SubCounty::getUniqueWards(1);
     * // Result: ['airport', 'bamburi', 'changamwe', 'chaani', ...]
     */
    public static function getUniqueWards($countyId)
    {
        return self::where('county_id', $countyId)
            ->distinct()
            ->pluck('ward')
            ->filter(function ($ward) {
                return !empty($ward) && strtolower($ward) !== 'reception';
            })
            ->sort()
            ->values();
    }

    /**
     * Get unique ward names for a specific constituency
     * 
     * @param int $countyId The ID of the county
     * @param string $constituencyName The name of the constituency
     * @return \Illuminate\Support\Collection Sorted list of unique ward names
     * 
     * @example
     * // Get wards in Changamwe constituency, Mombasa County
     * $wards = SubCounty::getWardsByConstituency(1, 'changamwe');
     * // Result: ['airport', 'chaani', 'changamwe', 'kipevu', 'port reitz']
     */
    public static function getWardsByConstituency($countyId, $constituencyName)
    {
        return self::where('county_id', $countyId)
            ->where('constituency_name', $constituencyName)
            ->distinct()
            ->pluck('ward')
            ->filter(function ($ward) {
                return !empty($ward) && strtolower($ward) !== 'reception';
            })
            ->sort()
            ->values();
    }

    /**
     * Get all unique constituency names across all counties
     * 
     * @return \Illuminate\Support\Collection Sorted list of all unique constituency names
     * 
     * @example
     * // Get all constituencies in Kenya
     * $allConstituencies = SubCounty::getAllUniqueConstituencies();
     * // Result: ['balambala', 'banissa', 'bura', 'changamwe', ...]
     */
    public static function getAllUniqueConstituencies()
    {
        return self::distinct()
            ->pluck('constituency_name')
            ->filter(function ($constituency) {
                return !empty($constituency) && strtolower($constituency) !== 'reception';
            })
            ->sort()
            ->values();
    }

    /**
     * Get all unique ward names across all counties
     * 
     * @return \Illuminate\Support\Collection Sorted list of all unique ward names
     * 
     * @example
     * // Get all wards in Kenya
     * $allWards = SubCounty::getAllUniqueWards();
     * // Result: ['abakaile', 'abothuguchi central', 'abothuguchi west', ...]
     */
    public static function getAllUniqueWards()
    {
        return self::distinct()
            ->pluck('ward')
            ->filter(function ($ward) {
                return !empty($ward) && strtolower($ward) !== 'reception';
            })
            ->sort()
            ->values();
    }

    /**
     * Get sub-counties by county with relationships
     * 
     * @param int $countyId The ID of the county
     * @return \Illuminate\Database\Eloquent\Collection
     * 
     * @example
     * // Get all sub-counties with their county relationship
     * $subCounties = SubCounty::with('county')->where('county_id', 1)->get();
     */
    public static function getByCounty($countyId)
    {
        return self::with('county')->where('county_id', $countyId)->get();
    }
}
