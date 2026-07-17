<?php
// app/Models/BlogTag.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BlogTag extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'posts_count',
        'color'
    ];

    protected $casts = [
        'posts_count' => 'integer',
    ];

    // Relationships
    public function posts()
    {
        return $this->belongsToMany(BlogPost::class, 'blog_post_tags', 'blog_tag_id', 'blog_post_id');
    }

    // Mutators
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        if (empty($this->attributes['slug'])) {
            $this->attributes['slug'] = Str::slug($value);
        }
    }

    // Methods
    public function updatePostsCount()
    {
        $this->update([
            'posts_count' => $this->posts()->whereHas('posts', function($query) {
                $query->where('status', 'published');
            })->count()
        ]);
    }
}