<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // User Types Constants
    const TYPE_USER = 'user';
    const TYPE_ADMIN = 'admin';
    const TYPE_AGENT = 'agent';

    protected $fillable = [
        'user_type',
        'first_name',
        'last_name',
        'email',
        'phone',
        'profile_image',
        'date_of_birth',
        'gender',
        'customer_tier',
        'status',
        'password',
        'address',
        'city',
        'state',
        'zip_code',
        'country',
        'total_bookings',
        'total_spent',
        'average_spending',
        'last_activity',
        'employee_id',
        'hire_date',
        'department',
        'commission_rate',
        'total_sales',
        'total_commission',
        'email_notifications',
        'sms_notifications',
        'marketing_emails',
        'internal_notes',
        'company_name', 'agent_code', 'approval_status',
        'approved_by', 'approved_at', 'rejection_reason',
        'company_address', 'company_phone', 'company_logo', 'cnic_or_reg_number',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'date_of_birth' => 'date',
        'hire_date' => 'date',
        'last_activity' => 'datetime',
        'total_spent' => 'decimal:2',
        'average_spending' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'total_commission' => 'decimal:2',
        'email_notifications' => 'boolean',
        'sms_notifications' => 'boolean',
        'marketing_emails' => 'boolean',
        'approved_at'      => 'datetime',
    ];

    // Accessors
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getInitialsAttribute()
    {
        return strtoupper(substr($this->first_name, 0, 1) . substr($this->last_name, 0, 1));
    }

    public function getProfileImageUrlAttribute()
    {
        if (!$this->profile_image) return null;

        if ($this->isAgent()) {
            return url('public/assets/images/agents/' . $this->profile_image);
        }

        if ($this->isAdmin()) {
            return url('public/' . $this->profile_image);
        }

        return url('public/assets/images/avatars/' . $this->profile_image);
    }

    // User Type Scopes
    public function scopeCustomers($query)
    {
        return $query->where('user_type', self::TYPE_USER);
    }

    public function scopeAdmins($query)
    {
        return $query->where('user_type', self::TYPE_ADMIN);
    }

    public function scopeAgents($query)
    {
        return $query->where('user_type', self::TYPE_AGENT);
    }

    public function scopeStaff($query)
    {
        return $query->whereIn('user_type', [self::TYPE_ADMIN, self::TYPE_AGENT]);
    }

    // Other Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByTier($query, $tier)
    {
        return $query->where('customer_tier', $tier);
    }

    public function scopeVip($query)
    {
        return $query->where('status', 'vip');
    }

    // User Type Check Methods
    public function isCustomer()
    {
        return $this->user_type === self::TYPE_USER;
    }

    public function isAdmin()
    {
        return $this->user_type === self::TYPE_ADMIN;
    }

    public function isAgent()
    {
        return $this->user_type === self::TYPE_AGENT;
    }

    public function isStaff()
    {
        return in_array($this->user_type, [self::TYPE_ADMIN, self::TYPE_AGENT]);
    }

    // app/Models/User.php
    public function blogPosts()
    {
        return $this->hasMany(BlogPost::class, 'author_id');
    }

    // ─── Agent Relations ──────────────────────────────────────────────────────

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

    // ─── Agent Methods ────────────────────────────────────────────────────────

    public function hasPermission(string $key): bool
    {
        if (!$this->isAgent()) return false;
        return AgentPermission::hasPermission($this->id, $key);
    }

    public function isActiveAgent(): bool
    {
        return $this->isAgent() && $this->approval_status === 'active';
    }

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

    public function suspendAgent(string $reason = null): void
    {
        $this->update([
            'approval_status'  => 'suspended',
            'rejection_reason' => $reason,
        ]);
    }

    public function activateAgent(): void
    {
        $this->update(['approval_status' => 'active']);
    }

    public function rejectAgent(string $reason = null): void
    {
        $this->update([
            'approval_status'  => 'rejected',
            'rejection_reason' => $reason,
        ]);
    }

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
            'rejected'  => '<span class="badge bg-dark">Rejected</span>',
            default     => '<span class="badge bg-secondary">Unknown</span>',
        };
    }

    // Methods
    public function updateBookingStats($newBookingAmount = 0)
    {
        if ($this->isCustomer()) {
            $this->increment('total_bookings');
            $this->increment('total_spent', $newBookingAmount);
            
            // Update average spending
            $this->average_spending = $this->total_spent / $this->total_bookings;
            $this->last_activity = now();
            $this->save();
        }
    }

    public function updateAgentSales($saleAmount = 0)
    {
        if ($this->isAgent() && $this->commission_rate) {
            $this->increment('total_sales');
            $commission = $saleAmount * ($this->commission_rate / 100);
            $this->increment('total_commission', $commission);
            $this->save();
        }
    }

    public function getStatusBadgeClass()
    {
        return match($this->status) {
            'active' => 'status-active',
            'inactive' => 'status-inactive',
            'suspended' => 'status-suspended',
            'vip' => 'status-vip',
            default => 'status-active'
        };
    }

    public function getTierBadgeClass()
    {
        return match($this->customer_tier) {
            'bronze' => 'tier-bronze',
            'silver' => 'tier-silver',
            'gold' => 'tier-gold',
            'platinum' => 'tier-platinum',
            default => 'tier-bronze'
        };
    }

    public function getUserTypeBadgeClass()
    {
        return match($this->user_type) {
            'admin' => 'badge bg-danger',
            'agent' => 'badge bg-warning',
            'user' => 'badge bg-primary',
            default => 'badge bg-secondary'
        };
    }

    public function getUserTypeLabel()
    {
        return match($this->user_type) {
            'admin' => 'Administrator',
            'agent' => 'Travel Agent',
            'user' => 'Customer',
            default => 'Unknown'
        };
    }
}