<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentWalletTransaction extends Model
{
    protected $fillable = [
        'agent_id', 'type', 'amount', 'balance_before', 'balance_after',
        'reference', 'booking_type', 'booking_id', 'note',
        'payment_method', 'performed_by', 'status',
    ];

    protected $casts = [
        'amount'         => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after'  => 'decimal:2',
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

    // ─── Accessors ────────────────────────────────────────────────────────────

    public function getTypeBadgeClassAttribute(): string
    {
        return $this->type === 'credit' ? 'badge bg-success' : 'badge bg-danger';
    }

    public function getFormattedAmountAttribute(): string
    {
        $sign = $this->type === 'credit' ? '+' : '-';
        return $sign . ' PKR ' . number_format($this->amount, 2);
    }
}
