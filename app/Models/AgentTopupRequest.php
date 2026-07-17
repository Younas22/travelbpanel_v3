<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentTopupRequest extends Model
{
    protected $fillable = [
        'agent_id', 'amount', 'payment_method', 'payment_proof',
        'note', 'status', 'reviewed_by', 'reviewed_at', 'rejection_note',
    ];

    protected $casts = [
        'amount'      => 'decimal:2',
        'reviewed_at' => 'datetime',
    ];

    // ─── Relations ────────────────────────────────────────────────────────────

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopePending($query)  { return $query->where('status', 'pending'); }
    public function scopeApproved($query) { return $query->where('status', 'approved'); }
    public function scopeRejected($query) { return $query->where('status', 'rejected'); }

    public function scopeByAgent($query, int $agentId)
    {
        return $query->where('agent_id', $agentId);
    }

    // ─── Accessors ────────────────────────────────────────────────────────────

    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'pending'  => 'badge bg-warning text-dark',
            'approved' => 'badge bg-success',
            'rejected' => 'badge bg-danger',
            default    => 'badge bg-secondary',
        };
    }

    public function getProofUrlAttribute(): ?string
    {
        return $this->payment_proof ? asset('storage/' . $this->payment_proof) : null;
    }
}
