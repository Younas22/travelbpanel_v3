<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'status',
        'sort_order',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get all partners for this module
     */
    public function partners()
    {
        return $this->hasMany(TravelPartner::class, 'module_id');
    }

    /**
     * Scope: Get only active modules
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')->orderBy('sort_order');
    }

    /**
     * Scope: Get only inactive modules
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    /**
     * Check if module has partners
     */
    public function hasPartners()
    {
        return $this->partners()->count() > 0;
    }

    /**
     * Get total revenue from all active partners
     */
    public function getTotalRevenue()
    {
        return $this->partners()
                   ->where('status', 'active')
                   ->sum('monthly_revenue');
    }

    /**
     * Get average commission from partners
     */
    public function getAverageCommission()
    {
        return $this->partners()
                   ->where('status', 'active')
                   ->avg('commission_rate') ?? 0;
    }

    /**
     * Get count of active partners
     */
    public function getActivePartnersCount()
    {
        return $this->partners()
                   ->where('status', 'active')
                   ->count();
    }

    /**
     * Toggle module status
     */
    public function toggleStatus()
    {
        $this->status = $this->status === 'active' ? 'inactive' : 'active';
        $this->save();
        return $this;
    }
}