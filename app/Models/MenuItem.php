<?php
// app/Models/MenuItem.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class MenuItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'translations',
        'url',
        'category',
        'sort_order',
        'is_active',
        'parent_id',
        'page_id',
        'icon',
        'target',
        'description'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'translations' => 'array',
    ];

    /**
     * Attributes for saving a menu item from a form. Link: a page (address
     * follows the page's slug), a custom URL, or nothing for a dropdown that
     * only holds sub-items. Names: one per active language; names of
     * languages switched off right now are kept.
     */
    public static function linkAndNameAttributes(array $validated, ?self $menu = null): array
    {
        $type = $validated['link_type'];
        if ($type === 'page') {
            $validated['url'] = '/page/' . Page::whereKey($validated['page_id'])->value('slug');
        } else {
            $validated['page_id'] = null;
            if ($type === 'dropdown') {
                $validated['url'] = null;
            }
        }

        if (array_key_exists('translations', $validated) || !$menu) {
            $active = Language::getActiveCodes();
            $names = array_filter(Arr::only($validated['translations'] ?? [], $active), 'filled');
            $names += Arr::except($menu?->translations ?? [], $active);
            $validated['translations'] = $names ?: null;
        }

        unset($validated['link_type']);

        return $validated;
    }

    // Relationships
    public function page()
    {
        return $this->belongsTo(Page::class);
    }

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
        // linked page: always its current address, even after the slug changed
        if ($this->page_id && $this->page) {
            return url('/page/' . $this->page->slug);
        }

        if (str_starts_with($this->url, 'http')) {
            return $this->url;
        }
        
        if (str_starts_with($this->url, '/')) {
            return url($this->url);
        }
        
        return url('/' . $this->url);
    }

    /**
     * Text shown in the website menu for the current language: the name typed
     * for that language in admin → Menus, else the linked page's title in
     * that language, else the old "{group}.{name}" translation key, else the
     * default language's name / page title, else the item name.
     */
    public function label(string $group): string
    {
        $locale = app()->getLocale();
        $own = $this->translations ?? [];

        if (filled($own[$locale] ?? null)) {
            return $own[$locale];
        }

        $pageTitle = $this->page?->translation('title', $locale, false);
        if (filled($pageTitle)) {
            return $pageTitle;
        }

        $key = "{$group}.{$this->name}";
        $text = t($key);
        if ($text !== $key) {
            return $text;
        }

        $default = Language::getDefault()?->code ?? 'en';

        return $own[$default] ?? $this->page?->title ?? $this->name;
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