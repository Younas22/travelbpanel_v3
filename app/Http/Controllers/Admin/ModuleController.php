<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ModuleController extends Controller
{
    /**
     * Display all modules
     */
    public function index()
    {
        $modules = Module::withCount('partners')->get();
        return view('admin.modules.index', compact('modules'));
    }

    /**
     * Show create module form
     */
    public function create()
    {
        return view('admin.modules.create');
    }

    /**
     * Store a new module
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:modules',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:active,inactive',
        ]);

        // Generate slug from name
        $validated['slug'] = Str::slug($validated['name']);

        $module = Module::create($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Module created successfully!',
                'module' => $module
            ]);
        }

        return redirect()->route('admin.modules.index')
                        ->with('success', 'Module created successfully!');
    }

    /**
     * Show edit module form
     */
    public function edit(Module $module)
    {
        return view('admin.modules.edit', compact('module'));
    }

    /**
     * Update module
     */
    public function update(Request $request, Module $module)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:modules,name,' . $module->id,
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:active,inactive',
        ]);

        // Generate slug from name if name changed
        if ($validated['name'] !== $module->name) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $module->update($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Module updated successfully!',
                'module' => $module
            ]);
        }

        return redirect()->route('admin.modules.index')
                        ->with('success', 'Module updated successfully!');
    }

    /**
     * Toggle module status (Active/Inactive)
     * Route: PATCH /modules/{id}/update-status
     *
     * Logic: When module is toggled, all its partners follow the same status
     */
    public function toggleStatus(Module $module)
    {
        $newStatus = $module->status === 'active' ? 'inactive' : 'active';

        // Update module status
        $module->update(['status' => $newStatus]);

        // Update ALL partners of this module to follow module status
        $module->partners()->update(['status' => $newStatus]);


        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Module and all partners status changed to {$newStatus}",
                'status' => $newStatus,
                'module' => $module
            ]);
        }

        return redirect()->back()->with('success', "Module and all partners status changed to {$newStatus}");
    }


    /**
     * Show module details
     */
    public function show(Module $module)
    {
        $module->load('partners');

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'module' => $module,
                'partners_count' => $module->partners->count(),
                'active_partners' => $module->partners->where('status', 'active')->count(),
            ]);
        }

        return view('admin.modules.show', compact('module'));
    }

    /**
     * Delete module
     */
    public function destroy(Module $module)
    {
        // Check if module has partners
        if ($module->partners()->count() > 0) {
            return redirect()->back()
                            ->with('error', 'Cannot delete module with existing partners. Please move or delete partners first.');
        }

        $module->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Module deleted successfully'
            ]);
        }

        return redirect()->route('admin.modules.index')
                        ->with('success', 'Module deleted successfully!');
    }

    /**
     * Get partners for a module (used in AJAX)
     */
    public function getPartners(Module $module)
    {
        $partners = $module->partners()->latest()->get();

        return response()->json([
            'success' => true,
            'module' => $module,
            'partners' => $partners,
            'count' => $partners->count(),
        ]);
    }

    /**
     * Bulk update partners status
     */
    public function bulkUpdateStatus(Request $request, Module $module)
    {
        $validated = $request->validate([
            'partner_ids' => 'required|array',
            'partner_ids.*' => 'integer|exists:travel_partners,id',
            'status' => 'required|in:active,pending,suspended',
        ]);

        $module->partners()
               ->whereIn('id', $validated['partner_ids'])
               ->update(['status' => $validated['status']]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => count($validated['partner_ids']) . ' partners updated successfully',
                'updated_count' => count($validated['partner_ids'])
            ]);
        }

        return redirect()->back()->with('success', 'Partners updated successfully!');
    }

    /**
     * Get module statistics
     */
    public function getStats(Module $module)
    {
        $partners = $module->partners;
        $activePartners = $partners->where('status', 'active');

        $stats = [
            'total_partners' => $partners->count(),
            'active_partners' => $activePartners->count(),
            'pending_partners' => $partners->where('status', 'pending')->count(),
            'suspended_partners' => $partners->where('status', 'suspended')->count(),
            'total_revenue' => $activePartners->sum('monthly_revenue'),
            'avg_commission' => $activePartners->avg('commission_rate') ?? 0,
            'avg_uptime' => $activePartners->avg('api_uptime') ?? 0,
        ];

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);
        }

        return $stats;
    }

    // Module slug → header menu item name
    private const SLUG_TO_MENU = [
        'hotel'  => 'Stay',
        'flight' => 'Flights',
        'visa'   => 'Visa',
        'tours'  => 'Tours',
        'umrah'  => 'Umrah',
    ];

    public function updateSortOrder(Request $request)
    {
        $request->validate([
            'items'             => 'required|array',
            'items.*.id'        => 'required|exists:modules,id',
            'items.*.sort_order'=> 'required|integer|min:1',
        ]);

        foreach ($request->items as $item) {
            Module::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json(['success' => true, 'message' => 'Module order updated!']);
    }
}