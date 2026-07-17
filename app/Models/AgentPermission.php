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
        // Wallet
        'wallet.view'     => 'View Wallet Balance',
        'wallet.request'  => 'Request Wallet Top-up',

        // Booking permissions
        'bookings.make'   => 'Make Bookings (via Website)',
        'bookings.view'   => 'View Own Bookings',

        // Add manual property permissions
        'hotels.add'      => 'Add Hotels',
        'tours.add'       => 'Add Tour Packages',
        'umrah.add'       => 'Add Umrah Packages',
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
