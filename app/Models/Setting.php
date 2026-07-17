<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'group',
        'key',
        'value',
        'type',
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Helper method to get setting value
    public static function getValue($key, $group = null, $default = null)
    {
        $query = self::where('key', $key)->where('is_active', true);
        
        if ($group) {
            $query->where('group', $group);
        }
        
        $setting = $query->first();
        
        return $setting ? $setting->value : $default;
    }

    // Helper method to get boolean value
    public static function getBoolValue($key, $group = null, $default = false)
    {
        $value = self::getValue($key, $group, $default);
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    // Helper method to get settings by group
    public static function getByGroup($group)
    {
        return self::where('group', $group)
                  ->where('is_active', true)
                  ->pluck('value', 'key');
    }

    // Helper method to set value
    public static function setValue($key, $value, $group = null)
    {
        $query = ['key' => $key];
        
        if ($group) {
            $query['group'] = $group;
        }
        
        return self::updateOrCreate($query, ['value' => $value]);
    }


        // Method to get image URL
    public static function getImageUrl($key, $group = 'main', $default = null)
    {
        $imagePath = self::getValue($key, $group);
        
        if ($imagePath && file_exists(public_path('assets/images/' . $imagePath))) {
            return asset('assets/images/' . $imagePath);
        }
        
        return $default ? asset($default) : null;
    }
    
}