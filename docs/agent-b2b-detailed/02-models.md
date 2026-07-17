# Agent B2B — Part 2: Models (Actual Code)

---

## Model 1: AgentWallet

**File:** `app/Models/AgentWallet.php`

```php
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
```

---

## Model 2: AgentWalletTransaction

**File:** `app/Models/AgentWalletTransaction.php`

```php
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
```

---

## Model 3: AgentPermission

**File:** `app/Models/AgentPermission.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AgentPermission extends Model
{
    protected $fillable = ['agent_id', 'permission_key', 'is_enabled'];

    protected $casts = ['is_enabled' => 'boolean'];

    // All valid permission keys
    public const ALL_PERMISSIONS = [
        // Module access
        'module.hotels'   => 'Hotels Module',
        'module.flights'  => 'Flights Module',
        'module.tours'    => 'Tours Module',
        'module.umrah'    => 'Umrah Module',
        'module.visa'     => 'Visa Module',
        // Hotel sub-permissions
        'hotels.api'      => 'API Hotels (Hotelbeds/Agoda)',
        'hotels.manual'   => 'Manual Hotels',
        // Flight sub-permissions
        'flights.api'     => 'API Flights (Amadeus/Sabre)',
        'flights.manual'  => 'Manual Flights',
        // Tour sub-permissions
        'tours.manual'    => 'Manual Tours',
        // Umrah sub-permissions
        'umrah.manual'    => 'Manual Umrah Packages',
        // Visa sub-permissions
        'visa.submit'     => 'Submit Visa Requests',
        // Wallet
        'wallet.view'     => 'View Wallet Balance',
        'wallet.request'  => 'Request Wallet Top-up',
        // Bookings
        'bookings.view'   => 'View Own Bookings',
    ];

    // ─── Relations ────────────────────────────────────────────────────────────

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    // ─── Static Helpers ───────────────────────────────────────────────────────

    /**
     * Check if agent has a specific permission.
     */
    public static function hasPermission(int $agentId, string $key): bool
    {
        $permissions = static::getAgentPermissions($agentId);
        return isset($permissions[$key]) && $permissions[$key] === true;
    }

    /**
     * Get all permissions for an agent as key => bool array.
     * Cached for 60 minutes.
     */
    public static function getAgentPermissions(int $agentId): array
    {
        return Cache::remember("agent_permissions_{$agentId}", 3600, function () use ($agentId) {
            return static::where('agent_id', $agentId)
                ->pluck('is_enabled', 'permission_key')
                ->toArray();
        });
    }

    /**
     * Save permissions for agent (upsert). Clears cache after save.
     */
    public static function savePermissions(int $agentId, array $permissions): void
    {
        foreach (array_keys(static::ALL_PERMISSIONS) as $key) {
            static::updateOrCreate(
                ['agent_id' => $agentId, 'permission_key' => $key],
                ['is_enabled' => isset($permissions[$key]) && $permissions[$key] == '1']
            );
        }
        Cache::forget("agent_permissions_{$agentId}");
    }
}
```

---

## Model 4: AgentTopupRequest

**File:** `app/Models/AgentTopupRequest.php`

```php
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
```

---

## Model 5: Update User Model

**File:** `app/Models/User.php` — Add these fields and methods to existing model.

**Add to `$fillable` array:**
```php
'company_name', 'agent_code', 'approval_status',
'approved_by', 'approved_at', 'rejection_reason',
'company_address', 'company_phone', 'company_logo', 'cnic_or_reg_number',
```

**Add to `$casts` array:**
```php
'approved_at' => 'datetime',
```

**Add these relations:**
```php
// ── Agent relations (user_type = 'agent') ────────────────────────────────

public function wallet()
{
    return $this->hasOne(AgentWallet::class, 'agent_id');
}

public function agentPermissions()
{
    return $this->hasMany(AgentPermission::class, 'agent_id');
}

public function topupRequests()
{
    return $this->hasMany(AgentTopupRequest::class, 'agent_id');
}

// Agent k registered customers (user_type='user' + parent_agent_id = this.id)
public function agentCustomers()
{
    return $this->hasMany(User::class, 'parent_agent_id');
}

// ── Customer relation (user_type = 'user' with parent_agent_id) ──────────

// Yeh user jis agent ka client hai
public function parentAgent()
{
    return $this->belongsTo(User::class, 'parent_agent_id');
}

public function isAgentCustomer(): bool
{
    return !$this->isAgent() && !$this->isAdmin() && !is_null($this->parent_agent_id);
}
```

**Add these methods:**
```php
/**
 * Check if agent has a specific permission.
 */
public function hasPermission(string $key): bool
{
    if (!$this->isAgent()) return false;
    return AgentPermission::hasPermission($this->id, $key);
}

/**
 * Check if agent account is active (approved).
 */
public function isActiveAgent(): bool
{
    return $this->isAgent() && $this->approval_status === 'active';
}

/**
 * Approve this agent.
 */
public function approveAgent(int $adminId): void
{
    $this->update([
        'approval_status' => 'active',
        'approved_by'     => $adminId,
        'approved_at'     => now(),
    ]);

    // Create wallet if not exists
    $this->wallet()->firstOrCreate(['agent_id' => $this->id]);
}

/**
 * Suspend this agent.
 */
public function suspendAgent(string $reason = null): void
{
    $this->update([
        'approval_status'  => 'suspended',
        'rejection_reason' => $reason,
    ]);
}

/**
 * Re-activate a suspended agent.
 */
public function activateAgent(): void
{
    $this->update(['approval_status' => 'active']);
}

/**
 * Generate unique agent code like AGT-0001.
 */
public static function generateAgentCode(): string
{
    $last = static::agents()
        ->whereNotNull('agent_code')
        ->orderByDesc('id')
        ->value('agent_code');

    $number = $last ? (int) substr($last, 4) + 1 : 1;
    return 'AGT-' . str_pad($number, 4, '0', STR_PAD_LEFT);
}

public function getApprovalStatusBadgeAttribute(): string
{
    return match($this->approval_status) {
        'active'    => '<span class="badge bg-success">Active</span>',
        'pending'   => '<span class="badge bg-warning text-dark">Pending</span>',
        'suspended' => '<span class="badge bg-danger">Suspended</span>',
        default     => '<span class="badge bg-secondary">Unknown</span>',
    };
}
```
