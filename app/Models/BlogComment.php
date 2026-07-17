<?php
// app/Models/BlogComment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlogComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'blog_post_id',
        'user_id',
        'guest_name',
        'guest_email',
        'content',
        'status',
        'parent_id',
        'likes_count',
        'ip_address',
        'user_agent'
    ];

    protected $casts = [
        'likes_count' => 'integer',
    ];

    // Relationships
    public function post()
    {
        return $this->belongsTo(BlogPost::class, 'blog_post_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parent()
    {
        return $this->belongsTo(BlogComment::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(BlogComment::class, 'parent_id');
    }

    // Scopes
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id');
    }

    // Accessors
    public function getAuthorNameAttribute()
    {
        return $this->user ? $this->user->full_name : $this->guest_name;
    }

    public function getAuthorEmailAttribute()
    {
        return $this->user ? $this->user->email : $this->guest_email;
    }

    // Methods
    public function approve()
    {
        $this->update(['status' => 'approved']);
        $this->post->updateCommentsCount();
    }

    public function reject()
    {
        $this->update(['status' => 'rejected']);
        $this->post->updateCommentsCount();
    }
}