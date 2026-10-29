<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AgentPermission;
use App\Models\AgentWallet;
use App\Models\HotelBooking;
use App\Models\FlightBooking;
use App\Models\TourBooking;
use App\Models\UmrahBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Setting;
use Resend\Laravel\Facades\Resend;

class AgentController extends Controller
{
    public const SORTS = [
        'newest' => 'Date Joined (Newest)',
        'oldest' => 'Date Joined (Oldest)',
        'name_asc' => 'Name (A–Z)',
        'name_desc' => 'Name (Z–A)',
        'commission_desc' => 'Commission (High–Low)',
    ];

    /** Search / company / joined filters shared by the list, the tab counts and the export. */
    private function filteredAgents(Request $request)
    {
        $query = User::agents();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('first_name', 'like', "%{$s}%")
                  ->orWhere('last_name', 'like', "%{$s}%")
                  ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$s}%"])
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('company_name', 'like', "%{$s}%")
                  ->orWhere('agent_code', 'like', "%{$s}%");
            });
        }
        if ($request->filled('company')) {
            $query->where('company_name', $request->company);
        }
        if ($request->filled('joined') && strtotime($request->joined)) {
            $query->whereDate('created_at', '>=', $request->joined);
        }

        return $query;
    }

    private function applySort($query, ?string $sort)
    {
        return match ($sort) {
            'oldest' => $query->oldest(),
            'name_asc' => $query->orderBy('first_name')->orderBy('last_name'),
            'name_desc' => $query->orderByDesc('first_name')->orderByDesc('last_name'),
            'commission_desc' => $query->orderByRaw('COALESCE(commission_rate, 0) DESC'),
            default => $query->latest(),
        };
    }

    public function index(Request $request)
    {
        $query = $this->filteredAgents($request)->with('wallet');
        if ($request->filled('status')) {
            $query->where('approval_status', $request->status);
        }
        $agents = $this->applySort($query, $request->sort)->paginate(20)->withQueryString();

        $stats = [
            'total'     => User::agents()->count(),
            'active'    => User::agents()->where('approval_status', 'active')->count(),
            'pending'   => User::agents()->where('approval_status', 'pending')->count(),
            'suspended' => User::agents()->where('approval_status', 'suspended')->count(),
        ];

        // Tab counts follow the search/company/joined filters (not the status itself).
        $base = $this->filteredAgents($request);
        $tabCounts = [
            ''          => (clone $base)->count(),
            'active'    => (clone $base)->where('approval_status', 'active')->count(),
            'pending'   => (clone $base)->where('approval_status', 'pending')->count(),
            'suspended' => (clone $base)->where('approval_status', 'suspended')->count(),
        ];

        // KPI trends: cumulative agents per month (last 8 months) and new agents
        // in the last 30 days vs the 30 days before, per status.
        $all = User::agents()->get(['approval_status', 'created_at']);
        $trends = [];
        foreach (['total' => null, 'active' => 'active', 'pending' => 'pending', 'suspended' => 'suspended'] as $key => $status) {
            $set = $status ? $all->where('approval_status', $status) : $all;
            $spark = [];
            for ($i = 7; $i >= 0; $i--) {
                $end = now()->subMonthsNoOverflow($i)->endOfMonth();
                $spark[] = $set->filter(fn ($a) => $a->created_at <= $end)->count();
            }
            $recent = $set->filter(fn ($a) => $a->created_at >= now()->subDays(30))->count();
            $before = $set->filter(fn ($a) => $a->created_at < now()->subDays(30) && $a->created_at >= now()->subDays(60))->count();
            $trends[$key] = [
                'spark' => $spark,
                'change' => $before ? round(($recent - $before) / $before * 100, 1) : ($recent ? null : 0.0),
                'new' => $recent,
            ];
        }

        $companies = User::agents()->whereNotNull('company_name')->where('company_name', '!=', '')
            ->distinct()->orderBy('company_name')->pluck('company_name');
        $sorts = self::SORTS;
        $pendingTopups = \App\Models\AgentTopupRequest::pending()->count();

        return view('admin.agents.index', compact('agents', 'stats', 'pendingTopups', 'tabCounts', 'trends', 'companies', 'sorts'));
    }

    /** CSV of the current filtered list, or only the selected agents (ids[]). */
    public function export(Request $request)
    {
        $query = $this->filteredAgents($request)->with('wallet');
        if ($request->filled('status')) {
            $query->where('approval_status', $request->status);
        }
        if ($ids = array_filter((array) $request->input('ids', []), 'is_numeric')) {
            $query->whereIn('id', $ids);
        }
        $agents = $this->applySort($query, $request->sort)->get();

        return response()->streamDownload(function () use ($agents) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Agent Code', 'Name', 'Email', 'Phone', 'Company', 'Status', 'Wallet Balance', 'Wallet Currency', 'Commission Rate', 'Joined']);
            foreach ($agents as $a) {
                fputcsv($out, [
                    $a->agent_code, $a->full_name, $a->email, $a->phone, $a->company_name, $a->approval_status,
                    // Same currency display rule as the agents list (stored "PKR" is shown as USD).
                    $a->wallet->balance ?? '', $a->wallet ? (($a->wallet->currency && $a->wallet->currency !== 'PKR') ? $a->wallet->currency : 'USD') : '',
                    $a->commission_rate ?? 0, $a->created_at?->format('Y-m-d'),
                ]);
            }
            fclose($out);
        }, 'agents-' . now()->format('Y-m-d') . '.csv', ['Content-Type' => 'text/csv']);
    }

    public function create()
    {
        return view('admin.agents.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name'      => 'required|string|max:100',
            'last_name'       => 'required|string|max:100',
            'email'           => 'required|email|unique:users,email',
            'phone'           => 'nullable|string|max:30',
            'company_name'    => 'nullable|string|max:255',
            'company_phone'   => 'nullable|string|max:30',
            'company_address' => 'nullable|string',
            'tax_number'      => 'nullable|string|max:100',
            'commission_type' => 'required|in:fixed,percentage',
            'commission_rate' => 'nullable|numeric|min:0' . ($request->commission_type === 'percentage' ? '|max:100' : ''),
            'password'        => 'required|min:8|confirmed',
        ]);

        $agent = User::create([
            'user_type'       => 'agent',
            'first_name'      => $request->first_name,
            'last_name'       => $request->last_name,
            'email'           => $request->email,
            'phone'           => $request->phone,
            'company_name'    => $request->company_name,
            'company_phone'   => $request->company_phone,
            'company_address' => $request->company_address,
            'tax_number'      => $request->tax_number,
            'internal_notes'  => $request->internal_notes,
            'commission_type' => $request->commission_type,
            'commission_rate' => $request->commission_rate ?? 0,
            'agent_code'      => User::generateAgentCode(),
            'approval_status' => 'active',
            'approved_by'     => auth()->id(),
            'approved_at'     => now(),
            'password'        => Hash::make($request->password),
        ]);

        // Create wallet
        AgentWallet::create(['agent_id' => $agent->id]);

        return redirect()->route('admin.agents.show', $agent)
                         ->with('success', 'Agent created successfully! Code: ' . $agent->agent_code);
    }

    public function show(User $agent)
    {
        $this->ensureAgent($agent);
        $agent->load(['wallet', 'agentPermissions', 'topupRequests' => fn($q) => $q->latest()->take(5)]);

        $bookingStats   = $this->getAgentBookingStats($agent->id);
        $allPermissions = AgentPermission::ALL_PERMISSIONS;
        $agentPermissions = AgentPermission::getAgentPermissions($agent->id);

        return view('admin.agents.show', compact('agent', 'bookingStats', 'allPermissions', 'agentPermissions'));
    }

    public function edit(User $agent)
    {
        $this->ensureAgent($agent);
        return view('admin.agents.edit', compact('agent'));
    }

    public function update(Request $request, User $agent)
    {
        $this->ensureAgent($agent);

        $request->validate([
            'first_name'      => 'required|string|max:100',
            'last_name'       => 'required|string|max:100',
            'email'           => 'required|email|unique:users,email,' . $agent->id,
            'phone'           => 'nullable|string|max:30',
            'company_name'    => 'nullable|string|max:255',
            'tax_number'      => 'nullable|string|max:100',
            'commission_type' => 'required|in:fixed,percentage',
            'commission_rate' => 'nullable|numeric|min:0' . ($request->commission_type === 'percentage' ? '|max:100' : ''),
        ]);

        $agent->update($request->only([
            'first_name', 'last_name', 'email', 'phone',
            'company_name', 'company_phone', 'company_address',
            'tax_number', 'commission_type', 'commission_rate', 'internal_notes',
        ]));

        return redirect()->route('admin.agents.show', $agent)->with('success', 'Agent updated.');
    }

    public function destroy(User $agent)
    {
        $this->ensureAgent($agent);
        $agent->delete();
        return redirect()->route('admin.agents.index')->with('success', 'Agent deleted.');
    }

    public function approve(User $agent)
    {
        $this->ensureAgent($agent);
        $agent->approveAgent(auth()->id());

        // Issue a fresh, known password so the credentials we email are
        // guaranteed to work, rather than assuming the agent still
        // remembers whatever they typed at self-registration.
        $plainPassword = Str::password(12, symbols: false);
        $agent->update(['password' => Hash::make($plainPassword)]);

        $this->sendAgentApprovalEmail($agent, $plainPassword);
        return back()->with('success', 'Agent approved successfully. Login credentials emailed to ' . $agent->email . '.');
    }

    public function reject(Request $request, User $agent)
    {
        $this->ensureAgent($agent);
        $request->validate(['reason' => 'required|string|max:1000']);
        $agent->rejectAgent($request->reason);
        $this->sendAgentRejectionEmail($agent, $request->reason);
        return back()->with('success', 'Agent rejected.');
    }

    public function suspend(Request $request, User $agent)
    {
        $this->ensureAgent($agent);
        $agent->suspendAgent($request->reason);
        return back()->with('success', 'Agent suspended.');
    }

    public function activate(User $agent)
    {
        $this->ensureAgent($agent);
        $agent->activateAgent();
        return back()->with('success', 'Agent reactivated.');
    }

    public function permissions(User $agent)
    {
        $this->ensureAgent($agent);
        $allPermissions   = AgentPermission::ALL_PERMISSIONS;
        $agentPermissions = AgentPermission::getAgentPermissions($agent->id);
        return view('admin.agents.permissions', compact('agent', 'allPermissions', 'agentPermissions'));
    }

    public function savePermissions(Request $request, User $agent)
    {
        $this->ensureAgent($agent);
        AgentPermission::savePermissions($agent->id, $request->input('permissions', []));
        return back()->with('success', 'Permissions saved.');
    }

    // ─── Private Helpers ──────────────────────────────────────────────────────

    private function sendAgentApprovalEmail(User $agent, string $plainPassword): void
    {
        try {
            $senderEmail  = getSetting('sender_email', 'email', 'contact@travelbookingpanel.com');
            $senderName   = getSetting('sender_name',  'email', 'Travel Booking Panel');
            $businessName = getSetting('business_name', 'main', 'Travel Booking Panel');
            $businessLogo = getSettingImage('business_logo_white', 'branding');
            $loginUrl     = url('/login');

            $logoHtml = $businessLogo
                ? "<img src='{$businessLogo}' alt='{$businessName}' style='max-height:40px;margin-bottom:10px;'><br>"
                : '';

            $html = "
            <div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;background:#fff;border:1px solid #e0e0e0;border-radius:8px;overflow:hidden;'>
                <div style='background:#0077BE;padding:24px 32px;text-align:center;'>
                    {$logoHtml}
                    <h1 style='color:#fff;margin:0;font-size:22px;'>Account Approved</h1>
                </div>
                <div style='padding:32px;'>
                    <p style='font-size:15px;color:#333;'>Dear <strong>{$agent->full_name}</strong>,</p>
                    <p style='font-size:15px;color:#333;'>We are pleased to inform you that your agent account with <strong>{$businessName}</strong> has been <strong style='color:#28a745;'>approved</strong>.</p>
                    <p style='font-size:15px;color:#333;'>Your agent code is: <strong style='font-size:18px;color:#0077BE;'>{$agent->agent_code}</strong></p>
                    <div style='background:#f6fbff;border:1px solid #cfe9fb;border-radius:6px;padding:18px 20px;margin:20px 0;'>
                        <p style='font-size:14px;color:#333;margin:0 0 8px;'><strong>Your login credentials:</strong></p>
                        <p style='font-size:14px;color:#333;margin:4px 0;'>Email: <strong>{$agent->email}</strong></p>
                        <p style='font-size:14px;color:#333;margin:4px 0;'>Password: <strong style='font-family:monospace;font-size:15px;'>{$plainPassword}</strong></p>
                        <p style='font-size:12px;color:#888;margin:10px 0 0;'>For your security, please log in and change this password as soon as possible.</p>
                    </div>
                    <div style='text-align:center;margin:32px 0;'>
                        <a href='{$loginUrl}' style='background:#0077BE;color:#fff;padding:12px 32px;border-radius:6px;text-decoration:none;font-weight:bold;font-size:15px;'>Login to Agent Portal</a>
                    </div>
                    <p style='font-size:13px;color:#777;'>If you have any questions, please contact our support team.</p>
                </div>
                <div style='background:#f5f5f5;padding:16px 32px;text-align:center;'>
                    <p style='font-size:12px;color:#999;margin:0;'>© " . date('Y') . " {$businessName}. All rights reserved.</p>
                </div>
            </div>";

            $apiKey = Setting::getValue('resend_api_key', 'email');
            if ($apiKey) {
                config(['services.resend.key' => $apiKey]);
            }

            \Log::info('Agent approval email sending', [
                'from' => $senderEmail . ' (' . $senderName . ')',
                'to'   => $agent->email . ' (' . $agent->full_name . ')',
            ]);

            Resend::emails()->send([
                'from'    => "{$senderName} <{$senderEmail}>",
                'to'      => [$agent->email],
                'subject' => "Your Agent Account Has Been Approved – {$businessName}",
                'html'    => $html,
            ]);

            \Log::info('Agent approval email sent successfully', [
                'from' => $senderEmail,
                'to'   => $agent->email,
            ]);
        } catch (\Exception $e) {
            \Log::error('Agent approval email failed: ' . $e->getMessage(), [
                'from' => $senderEmail ?? 'unknown',
                'to'   => $agent->email,
            ]);
        }
    }

    private function sendAgentRejectionEmail(User $agent, string $reason): void
    {
        try {
            $senderEmail  = getSetting('sender_email', 'email', 'contact@travelbookingpanel.com');
            $senderName   = getSetting('sender_name',  'email', 'Travel Booking Panel');
            $businessName = getSetting('business_name', 'main', 'Travel Booking Panel');
            $businessLogo = getSettingImage('business_logo_white', 'branding');
            $contactEmail = getSetting('contact_email', 'contact', 'support@travelbookingpanel.com');

            $logoHtml = $businessLogo
                ? "<img src='{$businessLogo}' alt='{$businessName}' style='max-height:40px;margin-bottom:10px;'><br>"
                : '';

            $html = "
            <div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;background:#fff;border:1px solid #e0e0e0;border-radius:8px;overflow:hidden;'>
                <div style='background:#dc3545;padding:24px 32px;text-align:center;'>
                    {$logoHtml}
                    <h1 style='color:#fff;margin:0;font-size:22px;'>Application Update</h1>
                </div>
                <div style='padding:32px;'>
                    <p style='font-size:15px;color:#333;'>Dear <strong>{$agent->full_name}</strong>,</p>
                    <p style='font-size:15px;color:#333;'>We regret to inform you that your agent account application with <strong>{$businessName}</strong> has been <strong style='color:#dc3545;'>rejected</strong>.</p>
                    <div style='background:#fff3f3;border-left:4px solid #dc3545;padding:16px 20px;border-radius:4px;margin:20px 0;'>
                        <p style='font-size:14px;color:#333;margin:0;'><strong>Reason:</strong></p>
                        <p style='font-size:14px;color:#555;margin:8px 0 0;'>{$reason}</p>
                    </div>
                    <p style='font-size:15px;color:#333;'>If you believe this is a mistake or would like to reapply, please contact us at <a href='mailto:{$contactEmail}' style='color:#0077BE;'>{$contactEmail}</a>.</p>
                </div>
                <div style='background:#f5f5f5;padding:16px 32px;text-align:center;'>
                    <p style='font-size:12px;color:#999;margin:0;'>© " . date('Y') . " {$businessName}. All rights reserved.</p>
                </div>
            </div>";

            $apiKey = Setting::getValue('resend_api_key', 'email');
            if ($apiKey) {
                config(['services.resend.key' => $apiKey]);
            }

            \Log::info('Agent rejection email sending', [
                'from' => $senderEmail . ' (' . $senderName . ')',
                'to'   => $agent->email . ' (' . $agent->full_name . ')',
            ]);

            Resend::emails()->send([
                'from'    => "{$senderName} <{$senderEmail}>",
                'to'      => [$agent->email],
                'subject' => "Your Agent Account Application – {$businessName}",
                'html'    => $html,
            ]);

            \Log::info('Agent rejection email sent successfully', [
                'from' => $senderEmail,
                'to'   => $agent->email,
            ]);
        } catch (\Exception $e) {
            \Log::error('Agent rejection email failed: ' . $e->getMessage(), [
                'from' => $senderEmail ?? 'unknown',
                'to'   => $agent->email,
            ]);
        }
    }

    private function ensureAgent(User $user): void
    {
        if (!$user->isAgent()) abort(404);
    }

    private function getAgentBookingStats(int $agentId): array
    {
        $hotels  = HotelBooking::where('agent_id', $agentId)->count();
        $flights = FlightBooking::where('agent_id', $agentId)->count();
        $tours   = TourBooking::where('agent_id', $agentId)->count();
        $umrah   = UmrahBooking::where('agent_id', $agentId)->count();

        return [
            'hotels'  => $hotels,
            'flights' => $flights,
            'tours'   => $tours,
            'umrah'   => $umrah,
            'total'   => $hotels + $flights + $tours + $umrah,
        ];
    }
}
