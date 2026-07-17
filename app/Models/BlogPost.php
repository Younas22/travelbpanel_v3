<?php
// app/Models/BlogPost.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

class BlogPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'excerpt',
        'featured_image',
        'author_id',
        'category_id',
        'status',
        'views_count',
        'likes_count',
        'comments_count',
        'shares_count',
        'seo_title',
        'meta_description',
        'reading_time',
        'seo_score',
        'scheduled_at',
        'published_at',
        'is_featured',
        'allow_comments',
        'social_shares'
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'published_at' => 'datetime',
        'is_featured' => 'boolean',
        'allow_comments' => 'boolean',
        'social_shares' => 'array',
        'views_count' => 'integer',
        'likes_count' => 'integer',
        'comments_count' => 'integer',
        'shares_count' => 'integer',
        'reading_time' => 'integer',
        'seo_score' => 'integer',
    ];

    // Relationships
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function category()
    {
        return $this->belongsTo(BlogCategory::class, 'category_id');
    }

    public function tags()
    {
        return $this->belongsToMany(BlogTag::class, 'blog_post_tags', 'blog_post_id', 'blog_tag_id');
    }

    public function comments()
    {
        return $this->hasMany(BlogComment::class);
    }

    public function approvedComments()
    {
        return $this->hasMany(BlogComment::class)->where('status', 'approved');
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published')->whereNotNull('published_at');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled')->whereNotNull('scheduled_at');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('published_at', 'desc');
    }

    public function scopePopular($query)
    {
        return $query->orderBy('views_count', 'desc');
    }

    // Mutators
    public function setTitleAttribute($value)
    {
        $this->attributes['title'] = $value;
        if (empty($this->attributes['slug'])) {
            $this->attributes['slug'] = Str::slug($value);
        }
        if (empty($this->attributes['seo_title'])) {
            $this->attributes['seo_title'] = $value;
        }
    }

    public function setContentAttribute($value)
    {
        $this->attributes['content'] = $value;
        if (empty($this->attributes['reading_time'])) {
            $this->attributes['reading_time'] = $this->calculateReadingTime($value);
        }
    }

    // Accessors
    public function getExcerptAttribute($value)
    {
        return $value ?: Str::limit(strip_tags($this->content), 160);
    }

    public function getReadingTimeTextAttribute()
    {
        return $this->reading_time . ' min read';
    }

    public function getPublishedDateAttribute()
    {
        return $this->published_at ? $this->published_at->format('M j, Y') : null;
    }

    public function getTimeAgoAttribute()
    {
        return $this->published_at ? $this->published_at->diffForHumans() : null;
    }

    public function getFeaturedImageUrlAttribute()
    {
        if (!$this->featured_image) {
            return asset('images/default-blog.jpg');
        }

        // Check if path already starts with 'assets/' (new format)
        if (str_starts_with($this->featured_image, 'assets/')) {
            return asset($this->featured_image);
        }

        // Handle old storage paths (blog_images/ or blog/images/)
        // Old paths were stored in storage/app/public/, so use storage/ prefix
        return asset('storage/' . $this->featured_image);
    }

    // Methods
    public function publish()
    {
        $this->update([
            'status' => 'published',
            'published_at' => now()
        ]);
        
        $this->category->updatePostsCount();
    }

    public function incrementViews()
    {
        $this->increment('views_count');
        $this->category->incrementViewsCount();
    }

    public function updateCommentsCount()
    {
        $this->update([
            'comments_count' => $this->approvedComments()->count()
        ]);
    }

    private function calculateReadingTime($content)
    {
        $wordCount = str_word_count(strip_tags($content));
        return max(1, ceil($wordCount / 200)); // 200 words per minute
    }

    public function calculateSeoScore()
    {
        $score = 0;
        
        // Title length (50-60 chars)
        if (strlen($this->seo_title) >= 50 && strlen($this->seo_title) <= 60) {
            $score += 20;
        }
        
        // Meta description (150-160 chars)
        if (strlen($this->meta_description) >= 150 && strlen($this->meta_description) <= 160) {
            $score += 20;
        }
        
        // Has featured image
        if ($this->featured_image) {
            $score += 15;
        }
        
        // Has excerpt
        if ($this->excerpt) {
            $score += 10;
        }
        
        // Has tags
        if ($this->tags()->count() > 0) {
            $score += 15;
        }
        
        // Content length (min 300 words)
        if (str_word_count(strip_tags($this->content)) >= 300) {
            $score += 20;
        }
        
        $this->update(['seo_score' => $score]);
        return $score;
    }

        public function syncTags($tags)
    {
        $tagIds = [];
        
        foreach ($tags as $tagName) {
            $tag = BlogTag::firstOrCreate(['name' => trim($tagName)]);
            $tagIds[] = $tag->id;
        }
        
        $this->tags()->sync($tagIds);
    }
}