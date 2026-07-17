<?php
// app/Models/Page.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Page extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'status',
        'en_content',
        'nl_content',
        'en_meta_title',
        'nl_meta_title',
        'en_meta_description',
        'nl_meta_description',
        'en_meta_keywords',
        'nl_meta_keywords',
        'sort_order',
        'is_homepage',
        'show_in_menu',
        'published_at'
    ];

    protected $casts = [
        'is_homepage' => 'boolean',
        'show_in_menu' => 'boolean',
        'published_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($page) {
            if (empty($page->slug)) {
                $page->slug = Str::slug($page->name);
            }

            // Set published_at when status is published
            if ($page->status === 'published' && !$page->published_at) {
                $page->published_at = now();
            }
        });

        static::updating(function ($page) {
            // Update published_at when status changes to published
            if ($page->status === 'published' && !$page->published_at) {
                $page->published_at = now();
            }

            // Clear published_at when status is not published
            if ($page->status !== 'published') {
                $page->published_at = null;
            }
        });
    }

    // Get content for current language
    public function getContentAttribute()
    {
        $lang = app()->getLocale();
        $field = "{$lang}_content";
        return $this->attributes[$field] ?? $this->attributes['en_content'] ?? '';
    }

    // Get meta title for current language
    public function getMetaTitleAttribute()
    {
        $lang = app()->getLocale();
        $field = "{$lang}_meta_title";
        return $this->attributes[$field] ?? $this->attributes['en_meta_title'] ?? $this->name;
    }

    // Get meta description for current language
    public function getMetaDescriptionAttribute()
    {
        $lang = app()->getLocale();
        $field = "{$lang}_meta_description";
        return $this->attributes[$field] ?? $this->attributes['en_meta_description'] ?? '';
    }

    // Get meta keywords for current language
    public function getMetaKeywordsAttribute()
    {
        $lang = app()->getLocale();
        $field = "{$lang}_meta_keywords";
        return $this->attributes[$field] ?? $this->attributes['en_meta_keywords'] ?? '';
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now());
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopePrivate($query)
    {
        return $query->where('status', 'private');
    }

    public function scopeInMenu($query)
    {
        return $query->where('show_in_menu', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('name', 'asc');
    }

    // Accessors
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'draft' => 'secondary',
            'published' => 'success',
            'private' => 'warning'
        ];

        return $badges[$this->status] ?? 'secondary';
    }

    public function getExcerptAttribute($limit = 150)
    {
        $plainText = strip_tags($this->content);
        return Str::limit($plainText, $limit);
    }

    public function getWordCountAttribute()
    {
        return str_word_count(strip_tags($this->content));
    }

    // Methods
    public function publish()
    {
        $this->update([
            'status' => 'published',
            'published_at' => now()
        ]);
    }

    public function unpublish()
    {
        $this->update([
            'status' => 'draft',
            'published_at' => null
        ]);
    }

    public function makePrivate()
    {
        $this->update([
            'status' => 'private',
            'published_at' => null
        ]);
    }

    public function setAsHomepage()
    {
        // First, remove homepage status from all other pages
        static::where('id', '!=', $this->id)->update(['is_homepage' => false]);
        
        // Then set this page as homepage
        $this->update(['is_homepage' => true, 'status' => 'published']);
    }

    // Route binding
    public function getRouteKeyName()
    {
        return 'slug';
    }

    // URL generation
    public function getUrlAttribute()
    {
        if ($this->is_homepage) {
            return url('/');
        }
        
        return url('/' . $this->slug);
    }
}