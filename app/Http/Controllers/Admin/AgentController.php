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
    public function index(Request $request)
    {
        $query = User::agents()->with('wallet');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('first_name', 'like', "%{$s}%")
                  ->orWhere('last_name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('company_name', 'like', "%{$s}%")
                  ->orWhere('agent_code', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('approval_status', $request->status);
        }

        $agents = $query->latest()->paginate(20);

        $stats = [
            'total'     => User::agents()->count(),
            'active'    => User::agents()->where('approval_status', 'active')->count(),
            'pending'   => User::agents()->where('approval_status', 'pending')->count(),
            'suspended' => User::agents()->where('approval_status', 'suspended')->count(),
        ];

        $pendingTopups = \App\Models\AgentTopupRequest::pending()->count();

        return view('admin.agents.index', compact('agents', 'stats', 'pendingTopups'));
    }

    public function create()
    {
        return view('admin.agents.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name'         => 'required|string|max:100',
            'last_name'          => 'required|string|max:100',
            'email'              => 'required|email|unique:users,email',
            'phone'              => 'nullable|string|max:30',
            'company_name'       => 'nullable|string|max:255',
            'company_phone'      => 'nullable|string|max:30',
            'company_address'    => 'nullable|string',
            'cnic_or_reg_number' => 'nullable|string|max:100',
            'commission_rate'    => 'nullable|numeric|min:0|max:100',
            'password'           => 'required|min:8|confirmed',
        ]);

        $agent = User::create([
            'user_type'          => 'agent',
            'first_name'         => $request->first_name,
            'last_name'          => $request->last_name,
            'email'              => $request->email,
            'phone'              => $request->phone,
            'company_name'       => $request->company_name,
            'company_phone'      => $request->company_phone,
            'company_address'    => $request->company_address,
            'cnic_or_reg_number' => $request->cnic_or_reg_number,
            'commission_rate'    => $request->commission_rate ?? 0,
            'agent_code'         => User::generateAgentCode(),
            'approval_status'    => 'active',
            'approved_by'        => auth()->id(),
            'approved_at'        => now(),
            'password'           => Hash::make($request->password),
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
            'commission_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        $agent->update($request->only([
            'first_name', 'last_name', 'email', 'phone',
            'company_name', 'company_phone', 'company_address',
            'cnic_or_reg_number', 'commission_rate', 'internal_notes',
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
