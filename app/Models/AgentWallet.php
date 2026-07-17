<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AgentWallet extends Model
{
    protected $fillable = [
        'agent_id', 'balance', 'currency', 'total_credited', 'total_debited',
    ];

    protected $casts = [
        'balance'        => 'decimal:2',
        'total_credited' => 'decimal:2',
        'total_debited'  => 'decimal:2',
    ];

    // ─── Relations ────────────────────────────────────────────────────────────

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function transactions()
    {
        return $this->hasMany(AgentWalletTransaction::class, 'agent_id', 'agent_id')
                    ->latest();
    }

    // ─── Business Logic ───────────────────────────────────────────────────────

    public function hasSufficientBalance(float $amount): bool
    {
        return $this->balance >= $amount;
    }

    /**
     * Add balance to wallet (credit).
     */
    public function credit(
        float $amount,
        string $note,
        int $performedBy,
        string $paymentMethod = null,
        string $reference = null,
        string $bookingType = null,
        int $bookingId = null
    ): AgentWalletTransaction {
        return DB::transaction(function () use ($amount, $note, $performedBy, $paymentMethod, $reference, $bookingType, $bookingId) {
            $balanceBefore = $this->balance;
            $balanceAfter  = $balanceBefore + $amount;

            $this->update([
                'balance'        => $balanceAfter,
                'total_credited' => $this->total_credited + $amount,
            ]);

            return AgentWalletTransaction::create([
                'agent_id'       => $this->agent_id,
                'type'           => 'credit',
                'amount'         => $amount,
                'balance_before' => $balanceBefore,
                'balance_after'  => $balanceAfter,
                'reference'      => $reference,
                'booking_type'   => $bookingType,
                'booking_id'     => $bookingId,
                'note'           => $note,
                'payment_method' => $paymentMethod,
                'performed_by'   => $performedBy,
                'status'         => 'completed',
            ]);
        });
    }

    /**
     * Deduct balance from wallet (debit).
     * Throws exception if insufficient balance.
     */
    public function debit(
        float $amount,
        string $note,
        int $performedBy,
        string $bookingType = null,
        int $bookingId = null,
        string $reference = null
    ): AgentWalletTransaction {
        if (!$this->hasSufficientBalance($amount)) {
            throw new \Exception('Insufficient wallet balance. Available: ' . $this->balance . ', Required: ' . $amount);
        }

        return DB::transaction(function () use ($amount, $note, $performedBy, $bookingType, $bookingId, $reference) {
            $balanceBefore = $this->balance;
            $balanceAfter  = $balanceBefore - $amount;

            $this->update([
                'balance'       => $balanceAfter,
                'total_debited' => $this->total_debited + $amount,
            ]);

            return AgentWalletTransaction::create([
                'agent_id'       => $this->agent_id,
                'type'           => 'debit',
                'amount'         => $amount,
                'balance_before' => $balanceBefore,
                'balance_after'  => $balanceAfter,
                'reference'      => $reference,
                'booking_type'   => $bookingType,
                'booking_id'     => $bookingId,
                'note'           => $note,
                'performed_by'   => $performedBy,
                'status'         => 'completed',
            ]);
        });
    }
}
