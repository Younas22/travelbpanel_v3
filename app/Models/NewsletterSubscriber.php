<?php
// app/Models/NewsletterSubscriber.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewsletterSubscriber extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'status',
        'joined_date'
    ];

    protected $casts = [
        'joined_date' => 'datetime',
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    public function scopeUnsubscribed($query)
    {
        return $query->where('status', 'unsubscribed');
    }

    // Accessors
    public function getJoinedDateFormattedAttribute()
    {
        return $this->joined_date->format('M j, Y');
    }

    public function getJoinedTimeAgoAttribute()
    {
        return $this->joined_date->diffForHumans();
    }

    // Methods
    public function subscribe()
    {
        $this->update([
            'status' => 'active',
            'joined_date' => now()
        ]);
    }

    public function unsubscribe()
    {
        $this->update(['status' => 'unsubscribed']);
    }

    public function activate()
    {
        $this->update(['status' => 'active']);
    }

    public function deactivate()
    {
        $this->update(['status' => 'inactive']);
    }
}