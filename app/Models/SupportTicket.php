<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportTicket extends Model
{
    protected $fillable = [
        'ticket_number',
        'user_id',
        'agent_id',
        'subject',
        'description',
        'priority',
        'status',
        'attachment',
        'last_activity_at',
    ];

    protected $casts = [
        'last_activity_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function replies()
    {
        return $this->hasMany(SupportTicketReply::class)->orderBy('created_at');
    }

    /**
     * Whichever side actually raised this ticket — a customer or an agent.
     */
    public function getSubmitterAttribute(): ?User
    {
        return $this->user ?? $this->agent;
    }

    public function getSubmitterTypeAttribute(): string
    {
        return $this->agent_id ? 'agent' : 'user';
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'open' => 'open',
            'in_progress' => 'in-progress',
            'resolved' => 'resolved',
            'closed' => 'closed',
            default => 'open',
        };
    }
}
