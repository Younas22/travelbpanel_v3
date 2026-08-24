<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TravelPartner;
use App\Models\TravelPartnerImport;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

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
        $imports = $partner->imports()->latest()->get();
        $steps = $this->buildStepStatuses($partner, $imports);
        $defaultStep = collect(array_keys($steps))->first(fn($key) => !$steps[$key]['complete']) ?? array_key_last($steps);

        return view('admin.travel-partners.edit', compact('partner', 'modules', 'imports', 'steps', 'defaultStep'));
    }

    /**
     * Compute completed/pending status for each stepper step, used to render
     * the step indicator and to pick a sensible default active step. Keys
     * match the frontend's data-step values exactly.
     */
    private function buildStepStatuses(TravelPartner $partner, $imports)
    {
        $hasCredential = false;
        for ($i = 1; $i <= 6; $i++) {
            if (!empty($partner->getAttribute("api_credential_{$i}"))) {
                $hasCredential = true;
                break;
            }
        }

        $hasFinancials = $partner->commission_rate !== null
            && $partner->discount_rate !== null
            && $partner->b2b_markup !== null
            && $partner->b2c_markup !== null;

        return [
            'credentials' => [
                'complete' => $hasCredential,
            ],
            'test-api' => [
                'complete' => $partner->last_api_test_status === 'success',
                'status' => $partner->last_api_test_status,
            ],
            'financial' => [
                'complete' => $hasFinancials,
            ],
        ] + ($this->partnerSupportsImport($partner) ? [
            'import' => [
                'complete' => $imports->where('status', 'imported')->isNotEmpty(),
            ],
        ] : []);
    }

    /**
     * Import Data is only relevant for partners under the "Stay" module —
     * other modules (Flight, Visa, Tours, Umrah) don't use this import flow.
     */
    private function partnerSupportsImport(TravelPartner $partner): bool
    {
        return strtolower(optional($partner->module)->name ?? '') === 'stay';
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
     * A financial value can only be capped at 100 when its type is
     * "percentage" — a "fixed" amount has no such ceiling.
     */
    private function percentageCapRule(Request $request, string $typeField)
    {
        return function ($attribute, $value, $fail) use ($request, $typeField) {
            $type = $request->input($typeField, 'percentage');
            if ($type === 'percentage' && $value > 100) {
                $fail('The ' . str_replace('_', ' ', $attribute) . ' must not exceed 100 when type is Percentage.');
            }
        };
    }

    /**
     * Update partner
     */
    public function update(Request $request, TravelPartner $partner)
    {
        $validator = Validator::make($request->all(), [
            'company_name' => 'required|string|max:255',
            'module_id' => 'nullable|exists:modules,id',
            'api_type' => 'nullable|string|max:255',
            'commission_rate' => ['nullable', 'numeric', 'min:0', $this->percentageCapRule($request, 'commission_type')],
            'commission_type' => 'nullable|in:percentage,fixed',
            'discount_rate' => ['nullable', 'numeric', 'min:0', $this->percentageCapRule($request, 'discount_type')],
            'discount_type' => 'nullable|in:percentage,fixed',
            'b2c_markup' => ['nullable', 'numeric', 'min:0', $this->percentageCapRule($request, 'b2c_markup_type')],
            'b2c_markup_type' => 'nullable|in:percentage,fixed',
            'b2b_markup' => ['nullable', 'numeric', 'min:0', $this->percentageCapRule($request, 'b2b_markup_type')],
            'b2b_markup_type' => 'nullable|in:percentage,fixed',
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
            'db_host' => 'nullable|string|max:255',
            'db_port' => 'nullable|string|max:10',
            'db_database' => 'nullable|string|max:255',
            'db_username' => 'nullable|string|max:255',
            'db_password' => 'nullable|string|max:500',
        ]);

        $validated = $validator->validate();

        // Handle boolean conversions
        $validated['development_mode'] = $request->input('development_mode', 0) == 1 ? 1 : 0;
        $validated['currency_support'] = $request->input('currency_support', 0) == 1 ? 1 : 0;
        $validated['payment_integration'] = $request->input('payment_integration', 0) == 1 ? 1 : 0;
        $validated['custom_pnr_format'] = $request->input('custom_pnr_format', 0) == 1 ? 1 : 0;
        $validated['updated_by'] = auth()->id();

        // Remove empty credentials
        if (empty($validated['api_credential_2'])) unset($validated['api_credential_2']);
        if (empty($validated['api_credential_5'])) unset($validated['api_credential_5']);

        // Password field: only overwrite the stored value when the admin actually typed a new one
        if (!$request->filled('db_password')) {
            unset($validated['db_password']);
        }

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

    /**
     * Persist the outcome of a credential-check run performed directly by the
     * browser against the partner's own dynamic /api/{supplier}/test_credentials
     * endpoint. This does not itself call any external API — it only records
     * the real result the frontend already received, so the stepper and
     * result panel can show the last known status after a page reload.
     */
    public function recordApiTestResult(Request $request, TravelPartner $partner)
    {
        $validated = $request->validate([
            'success' => 'required|boolean',
            'message' => 'required|string|max:1000',
        ]);

        $this->recordTestResult($partner, $validated['success'], $validated['message']);

        return response()->json(['success' => true]);
    }

    /**
     * Persist the outcome of a credential-check run so the stepper and
     * result panel can show the last known status after a page reload.
     */
    private function recordTestResult(TravelPartner $partner, bool $success, string $message)
    {
        $partner->update([
            'last_api_test_status' => $success ? 'success' : 'failed',
            'last_api_test_message' => $message,
            'last_api_test_at' => now(),
        ]);
    }

    /**
     * Upload a partner content file and create an import record. The
     * upload/storage step and the row-count/preview parsing below are real
     * (CSV/JSON/XML are actually read) — this does not map the file into any
     * partner-specific business data, it only stores it and previews it.
     */
    public function importContent(Request $request, TravelPartner $partner)
    {
        abort_unless($this->partnerSupportsImport($partner), 403, 'Content import is only available for partners under the Stay module.');

        $validated = $request->validate([
            'import_type' => 'required|string|max:100',
            'file_format' => 'required|string|max:20',
            'file' => 'required|file|max:20480|mimes:csv,txt,json,xml,xlsx,xls',
        ]);

        $file = $request->file('file');
        $path = $file->store("travel-partners/{$partner->id}/imports", 'local');
        $format = strtolower($validated['file_format']);

        [$recordsCount, $previewData, $errorMessage] = $this->parseImportFileForPreview(
            Storage::disk('local')->path($path),
            $format
        );

        $import = TravelPartnerImport::create([
            'travel_partner_id' => $partner->id,
            'import_type' => $validated['import_type'],
            'file_format' => $format,
            'original_filename' => $file->getClientOriginalName(),
            'stored_path' => $path,
            'status' => $errorMessage ? 'failed' : 'imported',
            'records_count' => $recordsCount,
            'preview_data' => $previewData,
            'error_message' => $errorMessage,
            'created_by' => auth()->id(),
        ]);

        return response()->json([
            'success' => !$errorMessage,
            'message' => $errorMessage
                ? "File uploaded but could not be parsed: {$errorMessage}"
                : ($recordsCount !== null
                    ? "File imported successfully — {$recordsCount} record(s) found."
                    : 'File uploaded successfully. A read-only preview is not available for this file format.'),
            'import' => $this->formatImportForResponse($import),
        ]);
    }

    /**
     * View a single import's read-only preview data.
     */
    public function showImport(TravelPartner $partner, TravelPartnerImport $import)
    {
        abort_if($import->travel_partner_id !== $partner->id, 404);

        return response()->json([
            'success' => true,
            'import' => $this->formatImportForResponse($import, true),
        ]);
    }

    /**
     * Permanently delete an import record and its stored file.
     */
    public function destroyImport(TravelPartner $partner, TravelPartnerImport $import)
    {
        abort_if($import->travel_partner_id !== $partner->id, 404);

        Storage::disk('local')->delete($import->stored_path);
        $import->delete();

        return response()->json([
            'success' => true,
            'message' => 'Import deleted successfully',
        ]);
    }

    private function formatImportForResponse(TravelPartnerImport $import, bool $withPreview = false)
    {
        $data = [
            'id' => $import->id,
            'original_filename' => $import->original_filename,
            'import_type' => $import->import_type,
            'file_format' => $import->file_format,
            'status' => $import->status,
            'records_count' => $import->records_count,
            'error_message' => $import->error_message,
            'created_at' => $import->created_at->toDateTimeString(),
            'created_at_human' => $import->created_at->diffForHumans(),
        ];

        if ($withPreview) {
            $data['preview_data'] = $import->preview_data;
        }

        return $data;
    }

    /**
     * Best-effort, read-only parse of an uploaded file for CSV/JSON/XML so we
     * can show a real record count and preview rows. XLS/XLSX are stored but
     * not parsed (no spreadsheet library in this project) — returns a null
     * count/preview rather than a fabricated one.
     */
    private function parseImportFileForPreview(string $absolutePath, string $format): array
    {
        $previewLimit = 20;

        try {
            switch ($format) {
                case 'csv':
                case 'txt':
                    $handle = fopen($absolutePath, 'r');
                    if (!$handle) {
                        return [null, null, 'Could not open file'];
                    }

                    $header = fgetcsv($handle);
                    if ($header === false) {
                        fclose($handle);
                        return [0, [], null];
                    }

                    $rows = [];
                    $count = 0;
                    while (($row = fgetcsv($handle)) !== false) {
                        $count++;
                        if (count($rows) < $previewLimit) {
                            $rows[] = array_combine(
                                $header,
                                array_pad(array_slice($row, 0, count($header)), count($header), null)
                            );
                        }
                    }
                    fclose($handle);

                    return [$count, $rows, null];

                case 'json':
                    $decoded = json_decode(file_get_contents($absolutePath), true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        return [null, null, 'Invalid JSON: ' . json_last_error_msg()];
                    }
                    $items = array_is_list($decoded) ? $decoded : [$decoded];

                    return [count($items), array_slice($items, 0, $previewLimit), null];

                case 'xml':
                    libxml_use_internal_errors(true);
                    $xml = simplexml_load_file($absolutePath);
                    if ($xml === false) {
                        $error = libxml_get_errors();
                        libxml_clear_errors();
                        return [null, null, 'Invalid XML' . ($error ? ': ' . trim($error[0]->message) : '')];
                    }

                    $items = [];
                    foreach ($xml->children() as $child) {
                        $items[] = json_decode(json_encode($child), true);
                    }

                    return [count($items), array_slice($items, 0, $previewLimit), null];

                default:
                    // xlsx/xls — no spreadsheet parser available in this project
                    return [null, null, null];
            }
        } catch (\Throwable $e) {
            return [null, null, 'Could not parse file: ' . $e->getMessage()];
        }
    }
}
