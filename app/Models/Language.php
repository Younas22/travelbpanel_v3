<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'native_name',
        'status',
        'is_default',
        'direction',
        'sort_order',
    ];

    protected $casts = [
        'status' => 'boolean',
        'is_default' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get only active languages
     */
    public static function getActive()
    {
        return self::where('status', 1)
                   ->orderBy('sort_order')
                   ->get();
    }

    /**
     * Get active language codes
     */
    public static function getActiveCodes()
    {
        return self::where('status', 1)
                   ->orderBy('sort_order')
                   ->pluck('code')
                   ->toArray();
    }

    /**
     * Check if language is active
     */
    public function isActive()
    {
        return $this->status == 1;
    }

    /**
     * Get default language
     */
    public static function getDefault()
    {
        return self::where('is_default', 1)->first();
    }

    /**
     * Check if language is default
     */
    public function isDefault()
    {
        return $this->is_default == 1;
    }

    /**
     * Check if language is RTL
     */
    public function isRtl()
    {
        return $this->direction == 'rtl';
    }
}
