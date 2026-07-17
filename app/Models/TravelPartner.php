<?php
// app/Models/TravelPartner.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class TravelPartner extends Model
{
    use HasFactory;

    protected $fillable = [
        'module_id',
        'supplier_type',
        'company_name',
        'profile_image',
        'api_type',
        'partner_tier',
        'status',
        'commission_rate',
        'discount_rate',
        'b2c_markup',
        'b2b_markup',
        'monthly_revenue',
        'integration_date',
        'contract_end_date',
        'api_credential_1',
        'api_credential_2',
        'api_credential_3',
        'api_credential_4',
        'api_credential_5',
        'api_credential_6',
        'development_mode',
        'currency_support',
        'payment_integration',
        'custom_pnr_format',
        'api_uptime',
        'total_bookings',
        'total_revenue',
        'revenue_growth',
        'admin_notes',
        'contract_details',
        'contact_email',
        'contact_phone',
        'contact_person',
        'supported_currencies',
        'supported_countries',
        'last_api_call',
        'last_revenue_update',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'integration_date' => 'date',
        'contract_end_date' => 'date',
        'commission_rate' => 'decimal:2',
        'monthly_revenue' => 'decimal:2',
        'total_revenue' => 'decimal:2',
        'revenue_growth' => 'decimal:2',
        'api_uptime' => 'decimal:2',
        'total_bookings' => 'integer',
        'development_mode' => 'boolean',
        'currency_support' => 'boolean',
        'payment_integration' => 'boolean',
        'custom_pnr_format' => 'boolean',
        'supported_currencies' => 'array',
        'supported_countries' => 'array',
        'last_api_call' => 'datetime',
        'last_revenue_update' => 'datetime',
    ];

    protected $hidden = [
        'api_credential_2', // Hide secret keys
        'api_credential_5', // Hide webhook secrets
    ];

        public function module()
    {
        return $this->belongsTo(Module::class, 'module_id');
    }


    // Relationships
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'partner_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeSuspended($query)
    {
        return $query->where('status', 'suspended');
    }

    public function scopeByTier($query, $tier)
    {
        return $query->where('partner_tier', $tier);
    }

    public function scopeEnterprise($query)
    {
        return $query->where('partner_tier', 'enterprise');
    }

    public function scopePremium($query)
    {
        return $query->where('partner_tier', 'premium');
    }

    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->where('contract_end_date', '<=', now()->addDays($days));
    }

    // Accessors
    public function getIntegrationAgeAttribute()
    {
        return $this->integration_date ? $this->integration_date->diffForHumans() : null;
    }

    public function getContractStatusAttribute()
    {
        if (!$this->contract_end_date) return 'No end date';

        if ($this->contract_end_date->isPast()) {
            return 'Expired';
        } elseif ($this->contract_end_date->diffInDays() <= 30) {
            return 'Expiring Soon';
        } else {
            return 'Active';
        }
    }

    public function getRevenueGrowthTextAttribute()
    {
        $growth = $this->revenue_growth;
        if ($growth > 0) {
            return "+{$growth}% this month";
        } elseif ($growth < 0) {
            return "{$growth}% this month";
        } else {
            return "No change";
        }
    }

    public function getApiHealthStatusAttribute()
    {
        $uptime = $this->api_uptime;
        if ($uptime >= 95) return 'excellent';
        if ($uptime >= 85) return 'good';
        return 'poor';
    }

    public function getPartnerLogoAttribute()
    {
        // Generate initials from company name
        $words = explode(' ', $this->company_name);
        $initials = '';
        foreach ($words as $word) {
            $initials .= strtoupper(substr($word, 0, 1));
            if (strlen($initials) >= 2) break;
        }
        return $initials ?: strtoupper(substr($this->company_name, 0, 2));
    }

    public function getMonthlyRevenueFormattedAttribute()
    {
        return '$' . number_format($this->monthly_revenue);
    }

    public function getTotalRevenueFormattedAttribute()
    {
        return '$' . number_format($this->total_revenue);
    }

    // Mutators
    public function setCompanyNameAttribute($value)
    {
        $this->attributes['company_name'] = $value;
    }

    // Methods
    public function activate()
    {
        $this->update(['status' => 'active']);
    }

    public function suspend()
    {
        $this->update(['status' => 'suspended']);
    }

    public function updateRevenue($amount)
    {
        $oldRevenue = $this->monthly_revenue;
        $this->update([
            'monthly_revenue' => $amount,
            'revenue_growth' => $oldRevenue > 0 ? (($amount - $oldRevenue) / $oldRevenue) * 100 : 0,
            'last_revenue_update' => now()
        ]);
    }

    public function incrementBookings()
    {
        $this->increment('total_bookings');
    }

    public function updateApiUptime($uptime)
    {
        $this->update([
            'api_uptime' => $uptime,
            'last_api_call' => now()
        ]);
    }

    public function isContractExpiring($days = 30)
    {
        return $this->contract_end_date &&
               $this->contract_end_date->diffInDays() <= $days &&
               !$this->contract_end_date->isPast();
    }

    public function isContractExpired()
    {
        return $this->contract_end_date && $this->contract_end_date->isPast();
    }

    public function getCommissionAmount($bookingAmount)
    {
        return ($bookingAmount * $this->commission_rate) / 100;
    }

    // Static methods
    public static function getActivePartnersCount()
    {
        return self::active()->count();
    }

    public static function getTotalMonthlyRevenue()
    {
        return self::active()->sum('monthly_revenue');
    }

    public static function getAverageCommission()
    {
        return self::active()->avg('commission_rate');
    }
}
