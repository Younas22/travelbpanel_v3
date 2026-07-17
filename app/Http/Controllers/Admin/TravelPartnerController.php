<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TravelPartner;
use App\Models\Module;
use Illuminate\Http\Request;

class TravelPartnerController extends Controller
{
    /**
     * Display the travel partners management page
     */
    public function index(Request $request)
    {
        // Get filter values from request
        $search = $request->input('search');
        $status = $request->input('status');
        $tier = $request->input('tier');
        $apiHealth = $request->input('api_health');

        // Base query
        $query = TravelPartner::query();

        // Search functionality
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('company_name', 'like', "%{$search}%")
                  ->orWhere('api_type', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%")
                  ->orWhere('contact_email', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($status) {
            $query->where('status', $status);
        }

        // Tier filter
        if ($tier) {
            $query->where('partner_tier', $tier);
        }

        // API Health filter
        if ($apiHealth) {
            switch ($apiHealth) {
                case 'excellent':
                    $query->where('api_uptime', '>=', 95);
                    break;
                case 'good':
                    $query->whereBetween('api_uptime', [85, 94.99]);
                    break;
                case 'poor':
                    $query->where('api_uptime', '<', 85);
                    break;
            }
        }

        // Get all modules with partner counts and eager load partners
        $modules = Module::with(['partners' => function($query) {
            $query->latest();
        }])->withCount('partners')->orderBy('sort_order')->get();

        // Get all partners with pagination for the table
        $partners = $query->with('module')->latest()->paginate(20);

        // Get overall stats
        $stats = $this->getOverallStats();

        return view('admin.travel-partners.index', compact('modules', 'stats', 'partners'));
    }

    /**
     * Get partners by module (AJAX endpoint)
     */
    public function getByModule($moduleId)
    {
        // Validate module exists
        $module = Module::findOrFail($moduleId);

        // Get partners for this module
        $partners = TravelPartner::where('module_id', $moduleId)
                                 ->latest()
                                 ->get();

        // Calculate stats for this module
        $stats = [
            'total_partners' => $partners->count(),
            'active_partners' => $partners->where('status', 'active')->count(),
            'active_rate' => $partners->count() > 0
                ? round(($partners->where('status', 'active')->count() / $partners->count()) * 100, 1)
                : 0,
            'monthly_revenue' => $partners->sum('monthly_revenue'),
            'revenue_growth' => $partners->avg('revenue_growth') ?? 0,
            'avg_commission' => $partners->avg('commission_rate') ?? 0,
        ];

        return response()->json([
            'success' => true,
            'partners' => $partners,
            'stats' => $stats
        ]);
    }

    /**
     * Get overall statistics
     */
    private function getOverallStats()
    {
        $activePartners = TravelPartner::where('status', 'active')->count();
        $totalPartners = TravelPartner::count();

        return [
            'total_partners' => $totalPartners,
            'active_partners' => $activePartners,
            'monthly_revenue' => TravelPartner::where('status', 'active')->sum('monthly_revenue'),
            'avg_commission' => TravelPartner::where('status', 'active')->avg('commission_rate') ?? 0,
            'new_this_month' => TravelPartner::whereMonth('created_at', now()->month)->count(),
            'revenue_growth' => TravelPartner::where('status', 'active')
                                    ->whereMonth('updated_at', now()->month)
                                    ->avg('revenue_growth') ?? 0
        ];
    }

    /**
     * Show create partner form
     */
    public function create()
    {
        $modules = Module::where('status', 'active')->get();
        return view('admin.travel-partners.create', compact('modules'));
    }

    /**
     * Store a new partner
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'module_id' => 'required|exists:modules,id',
            'contact_person' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'api_type' => 'nullable|string|max:255',
            'commission_rate' => 'required|numeric|min:0|max:100',
            'discount_rate' => 'required|numeric|min:0|max:100',
            'b2c_markup' => 'required|numeric|min:0|max:100',
            'b2b_markup' => 'required|numeric|min:0|max:100',
            'partner_tier' => 'nullable|in:standard,premium,enterprise',
            'status' => 'required|in:active,pending,suspended',
            'supplier_type' => 'nullable|string|max:255',
            'api_credential_1' => 'nullable|string|max:500',
            'api_credential_2' => 'nullable|string|max:500',
            'api_credential_3' => 'nullable|string|max:500',
            'api_credential_4' => 'nullable|string|max:500',
            'api_credential_5' => 'nullable|string|max:500',
            'api_credential_6' => 'nullable|string|max:500',
            'development_mode' => 'nullable|boolean',
            'monthly_revenue' => 'nullable|numeric|min:0',
        ]);

        $validated['development_mode'] = $request->has('development_mode') ? 1 : 0;
        $validated['created_by'] = auth()->id();

        $partner = TravelPartner::create($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Partner created successfully!',
                'partner' => $partner
            ]);
        }

        return redirect()->route('admin.travel-partners.index')
                        ->with('success', 'Travel partner created successfully!');
    }

    /**
     * Show edit form
     */
    public function edit(TravelPartner $partner)
    {
        $modules = Module::get();
        return view('admin.travel-partners.edit', compact('partner', 'modules'));
    }

    /**
     * Show partner details
     */
    public function show(TravelPartner $partner)
    {
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'partner' => $partner
            ]);
        }

        return view('admin.travel-partners.show', compact('partner'));
    }

    /**
     * Update partner
     */
    public function update(Request $request, TravelPartner $partner)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'module_id' => 'nullable|exists:modules,id',
            'api_type' => 'nullable|string|max:255',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'discount_rate' => 'nullable|numeric|min:0|max:100',
            'b2c_markup' => 'nullable|numeric|min:0|max:100',
            'b2b_markup' => 'nullable|numeric|min:0|max:100',
            'partner_tier' => 'nullable|in:standard,premium,enterprise',
            'status' => 'required|in:active,inactive',
            'contract_end_date' => 'nullable|date',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'contact_person' => 'nullable|string|max:255',
            'integration_date' => 'nullable|date',
            'monthly_revenue' => 'nullable|numeric|min:0',
            'api_credential_1' => 'nullable|string|max:500',
            'api_credential_2' => 'nullable|string|max:500',
            'api_credential_3' => 'nullable|string|max:500',
            'api_credential_4' => 'nullable|string|max:500',
            'api_credential_5' => 'nullable|string|max:500',
            'api_credential_6' => 'nullable|string|max:500',
            'development_mode' => 'nullable',
            'currency_support' => 'nullable|boolean',
            'payment_integration' => 'nullable|boolean',
            'custom_pnr_format' => 'nullable|boolean',
            'admin_notes' => 'nullable|string|max:1000',
            'supplier_type' => 'nullable|string|max:255',
        ]);

        // Handle boolean conversions
        $validated['development_mode'] = $request->input('development_mode', 0) == 1 ? 1 : 0;
        $validated['currency_support'] = $request->input('currency_support', 0) == 1 ? 1 : 0;
        $validated['payment_integration'] = $request->input('payment_integration', 0) == 1 ? 1 : 0;
        $validated['custom_pnr_format'] = $request->input('custom_pnr_format', 0) == 1 ? 1 : 0;
        $validated['updated_by'] = auth()->id();

        // Remove empty credentials
        if (empty($validated['api_credential_2'])) unset($validated['api_credential_2']);
        if (empty($validated['api_credential_5'])) unset($validated['api_credential_5']);

        try {
            $partner->update($validated);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Partner updated successfully!',
                    'partner' => $partner
                ]);
            }

            return redirect()->route('admin.travel-partners.index')
                             ->with('success', 'Travel partner updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                             ->withInput()
                             ->with('error', 'Error updating partner: ' . $e->getMessage());
        }
    }

    /**
     * Activate partner
     */
    public function activate(TravelPartner $partner)
    {
        $partner->update(['status' => 'active']);

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Partner activated successfully'
            ]);
        }

        return redirect()->back()->with('success', 'Partner activated successfully');
    }

    /**
     * Suspend partner
     */
    public function suspend(TravelPartner $partner)
    {
        $partner->update(['status' => 'suspended']);

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Partner suspended successfully'
            ]);
        }

        return redirect()->back()->with('success', 'Partner suspended successfully');
    }

    /**
     * Toggle partner status (active/inactive) with auto module toggle logic
     */
    public function toggleStatus(Request $request, TravelPartner $partner)
    {
        $newStatus = $partner->status === 'active' ? 'inactive' : 'active';
        $partner->update(['status' => $newStatus]);

        $moduleStatusChanged = false;
        $newModuleStatus = null;

        // Get module ID from partner or request
        $moduleId = $request->input('module_id') ?? $partner->module_id;

        if ($moduleId) {
            $module = Module::find($moduleId);

            if ($module) {
                // Get all partners for this module
                $allPartners = TravelPartner::where('module_id', $moduleId)->get();
                $activePartnersCount = $allPartners->where('status', 'active')->count();

                // Logic 1: If all partners are inactive, module should be inactive
                if ($activePartnersCount === 0 && $module->status === 'active') {
                    $module->update(['status' => 'inactive']);
                    $moduleStatusChanged = true;
                    $newModuleStatus = 'inactive';
                }

                // Logic 2: If any partner becomes active and module is inactive, activate module
                if ($newStatus === 'active' && $module->status === 'inactive') {
                    $module->update(['status' => 'active']);
                    $moduleStatusChanged = true;
                    $newModuleStatus = 'active';
                }
            }
        }

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Partner status updated successfully',
                'status' => $newStatus,
                'module_status_changed' => $moduleStatusChanged,
                'new_module_status' => $newModuleStatus
            ]);
        }

        return redirect()->back()->with('success', 'Partner status updated successfully');
    }

    /**
     * Delete partner
     */
    public function destroy(TravelPartner $partner)
    {
        $partner->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Partner deleted successfully'
            ]);
        }

        return redirect()->route('admin.travel-partners.index')
                        ->with('success', 'Travel partner deleted successfully!');
    }
}
