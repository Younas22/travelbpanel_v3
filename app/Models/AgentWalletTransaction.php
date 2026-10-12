<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentWalletTransaction extends Model
{
    protected $fillable = [
        'agent_id', 'type', 'amount', 'currency', 'balance_before', 'balance_after',
        'reference', 'booking_type', 'booking_id', 'note',
        'payment_method', 'performed_by', 'status', 'seen_by_agent',
    ];

    protected $casts = [
        'amount'         => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after'  => 'decimal:2',
        'seen_by_agent'  => 'datetime',
    ];

    // ─── Relations ────────────────────────────────────────────────────────────

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function performedBy()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeCredits($query)
    {
        return $query->where('type', 'credit');
    }

    public function scopeDebits($query)
    {
        return $query->where('type', 'debit');
    }

    public function scopeByAgent($query, int $agentId)
    {
        return $query->where('agent_id', $agentId);
    }

    public function scopeByDateRange($query, $from, $to)
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }

    /**
     * Admin "Zero Out Balance" actions, not yet acknowledged by the agent —
     * surfaced as a one-time dashboard banner so the agent sees why their
     * balance changed, not silently discovered later in the history list.
     */
    public function scopeUnseenZeroOutNotices($query)
    {
        return $query->where('note', 'like', 'Balance zeroed out by admin%')
                     ->whereNull('seen_by_agent');
    }

    // ─── Accessors ────────────────────────────────────────────────────────────

    public function getTypeBadgeClassAttribute(): string
    {
        return $this->type === 'credit' ? 'badge bg-success' : 'badge bg-danger';
    }
}
