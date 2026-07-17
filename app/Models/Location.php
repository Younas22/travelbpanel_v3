<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $table = 'locations';

    protected $fillable = [
        'country',
        'country_code',
        'city',
        'latitude',
        'longitude',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
        'latitude' => 'string',
        'longitude' => 'string',
    ];

    public $timestamps = true;

    // Scope for active locations
    public function scopeActive($query)
    {
        return $query->where('status', '1');
    }

    // Scope for filtering by country
    public function scopeByCountry($query, $country)
    {
        return $query->where('country', $country);
    }

    // Scope for filtering by city
    public function scopeByCity($query, $city)
    {
        return $query->where('city', $city);
    }

    // Get full location name
    public function getFullLocationAttribute()
    {
        return $this->city . ', ' . $this->country;
    }
}
