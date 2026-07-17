<?php
// app/Models/MenuItem.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'url',
        'category',
        'sort_order',
        'is_active',
        'parent_id',
        'icon',
        'target',
        'description'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationships
    public function parent()
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('sort_order');
    }

    public function activeChildren()
    {
        return $this->hasMany(MenuItem::class, 'parent_id')
                    ->where('is_active', true)
                    ->orderBy('sort_order');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeHeader($query)
    {
        return $query->where('category', 'header');
    }

    public function scopeFooterQuickLinks($query)
    {
        return $query->where('category', 'footer_quick_links');
    }

    public function scopeFooterServices($query)
    {
        return $query->where('category', 'footer_services');
    }

    public function scopeFooterSupport($query)
    {
        return $query->where('category', 'footer_support');
    }

    public function scopeParentItems($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc');
    }

    // Accessors
    public function getFullUrlAttribute()
    {
        if (str_starts_with($this->url, 'http')) {
            return $this->url;
        }
        
        if (str_starts_with($this->url, '/')) {
            return url($this->url);
        }
        
        return url('/' . $this->url);
    }

    public function getCategoryLabelAttribute()
    {
        $labels = [
            'header' => 'Header Menu',
            'footer_quick_links' => 'Footer - Quick Links',
            'footer_services' => 'Footer - Services',
            'footer_support' => 'Footer - Support'
        ];

        return $labels[$this->category] ?? $this->category;
    }

    public function getDepthAttribute()
    {
        $depth = 0;
        $parent = $this->parent;
        
        while ($parent) {
            $depth++;
            $parent = $parent->parent;
        }
        
        return $depth;
    }

    // Methods
    public function activate()
    {
        $this->update(['is_active' => true]);
    }

    public function deactivate()
    {
        $this->update(['is_active' => false]);
    }

    public function moveToPosition($newSortOrder)
    {
        $this->update(['sort_order' => $newSortOrder]);
    }

    public function hasChildren()
    {
        return $this->children()->count() > 0;
    }

    public function hasActiveChildren()
    {
        return $this->activeChildren()->count() > 0;
    }

    // Static Methods
    public static function getMenuByCategory($category, $activeOnly = true)
    {
        $query = self::where('category', $category)
                    ->parentItems()
                    ->ordered();
        
        if ($activeOnly) {
            $query->active();
        }
        
        return $query->with(['activeChildren' => function($q) {
            $q->ordered();
        }])->get();
    }

    public static function getHeaderMenu($activeOnly = true)
    {
        return self::getMenuByCategory('header', $activeOnly);
    }

    public static function getFooterQuickLinks($activeOnly = true)
    {
        return self::getMenuByCategory('footer_quick_links', $activeOnly);
    }

    public static function getFooterServices($activeOnly = true)
    {
        return self::getMenuByCategory('footer_services', $activeOnly);
    }

    public static function getFooterSupport($activeOnly = true)
    {
        return self::getMenuByCategory('footer_support', $activeOnly);
    }

    public static function getCategoryOptions()
    {
        return [
            'header' => 'Header Menu',
            'footer_quick_links' => 'Footer - Quick Links',
            'footer_services' => 'Footer - Services',
            'footer_support' => 'Footer - Support'
        ];
    }
}