<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
        'iso3',
        'iso2',
        'phone_code',
        'capital',
        'currency',
        'currency_symbol',
        'latitude',
        'longitude',
        'region',
        'subregion',
    ];

    /**
     * The phone_code column is inconsistent in the seed data — most rows are
     * bare digits ("92"), some already have a leading "+" with an area-code
     * suffix ("+1-684"). Always returns a single leading "+".
     */
    public function getDialCodeAttribute(): ?string
    {
        if (!$this->phone_code) {
            return null;
        }

        return str_starts_with($this->phone_code, '+') ? $this->phone_code : '+' . $this->phone_code;
    }
}
