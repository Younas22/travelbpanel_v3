<?php
// app/Models/BlogCategory.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BlogCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
        'parent_id',
        'posts_count',
        'views_count',
        'sort_order',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'posts_count' => 'integer',
        'views_count' => 'integer',
        'sort_order' => 'integer',
    ];

    // Relationships
    public function posts()
    {
        return $this->hasMany(BlogPost::class, 'category_id');
    }

    public function parent()
    {
        return $this->belongsTo(BlogCategory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(BlogCategory::class, 'parent_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeParent($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeOrderBySort($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    // Mutators
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        if (empty($this->attributes['slug'])) {
            $this->attributes['slug'] = Str::slug($value);
        }
    }

    // Accessors
    public function getPostsCountAttribute($value)
    {
        return $this->posts()->where('status', 'published')->count();
    }

    // Methods
    public function updatePostsCount()
    {
        $this->update([
            'posts_count' => $this->posts()->where('status', 'published')->count()
        ]);
    }

    public function incrementViewsCount()
    {
        $this->increment('views_count');
    }
}