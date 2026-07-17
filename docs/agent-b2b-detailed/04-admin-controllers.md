# Agent B2B — Part 4: Admin Controllers (Actual Code)

---

## Controller 1: Admin\AgentController

**File:** `app/Http/Controllers/Admin/AgentController.php`

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AgentPermission;
use App\Models\AgentWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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
            'first_name'   => 'required|string|max:100',
            'last_name'    => 'required|string|max:100',
            'email'        => 'required|email|unique:users,email',
            'phone'        => 'nullable|string|max:30',
            'company_name' => 'nullable|string|max:255',
            'company_phone'=> 'nullable|string|max:30',
            'company_address' => 'nullable|string',
            'cnic_or_reg_number' => 'nullable|string|max:100',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'password'     => 'required|min:8|confirmed',
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
            'cnic_or_reg_number' => $request->cnic_or_reg_number,
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

        $bookingStats = $this->getAgentBookingStats($agent->id);
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
        return back()->with('success', 'Agent approved successfully.');
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
        $allPermissions = AgentPermission::ALL_PERMISSIONS;
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

    private function ensureAgent(User $user): void
    {
        if (!$user->isAgent()) abort(404);
    }

    private function getAgentBookingStats(int $agentId): array
    {
        return [
            'hotels'  => \App\Models\HotelBooking::where('agent_id', $agentId)->count(),
            'flights' => \App\Models\FlightBooking::where('agent_id', $agentId)->count(),
            'tours'   => \App\Models\TourBooking::where('agent_id', $agentId)->count(),
            'umrah'   => \App\Models\UmrahBooking::where('agent_id', $agentId)->count(),
            'total'   => \App\Models\HotelBooking::where('agent_id', $agentId)->count()
                       + \App\Models\FlightBooking::where('agent_id', $agentId)->count()
                       + \App\Models\TourBooking::where('agent_id', $agentId)->count()
                       + \App\Models\UmrahBooking::where('agent_id', $agentId)->count(),
        ];
    }
}
```

---

## Controller 2: Admin\AgentWalletController

**File:** `app/Http/Controllers/Admin/AgentWalletController.php`

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AgentWallet;
use App\Models\AgentWalletTransaction;
use Illuminate\Http\Request;

class AgentWalletController extends Controller
{
    public function index(User $agent)
    {
        $wallet = $agent->wallet ?? AgentWallet::create(['agent_id' => $agent->id]);
        $recentTransactions = AgentWalletTransaction::byAgent($agent->id)->latest()->take(10)->get();

        return view('admin.agents.wallet', compact('agent', 'wallet', 'recentTransactions'));
    }

    public function credit(Request $request, User $agent)
    {
        $request->validate([
            'amount'         => 'required|numeric|min:1',
            'payment_method' => 'nullable|string|max:100',
            'note'           => 'nullable|string|max:500',
        ]);

        $wallet = $agent->wallet ?? AgentWallet::create(['agent_id' => $agent->id]);

        $wallet->credit(
            amount:        $request->amount,
            note:          $request->note ?? 'Manual credit by admin',
            performedBy:   auth()->id(),
            paymentMethod: $request->payment_method,
        );

        return back()->with('success', 'PKR ' . number_format($request->amount, 2) . ' added to wallet.');
    }

    public function debit(Request $request, User $agent)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'note'   => 'required|string|max:500',
        ]);

        $wallet = $agent->wallet;

        if (!$wallet || !$wallet->hasSufficientBalance($request->amount)) {
            return back()->with('error', 'Insufficient wallet balance.');
        }

        $wallet->debit(
            amount:      $request->amount,
            note:        $request->note,
            performedBy: auth()->id(),
        );

        return back()->with('success', 'PKR ' . number_format($request->amount, 2) . ' deducted from wallet.');
    }

    public function transactions(Request $request, User $agent)
    {
        $query = AgentWalletTransaction::byAgent($agent->id)->with('performedBy');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('from') && $request->filled('to')) {
            $query->byDateRange($request->from, $request->to . ' 23:59:59');
        }

        $transactions = $query->latest()->paginate(20);
        $wallet = $agent->wallet;

        return view('admin.agents.transactions', compact('agent', 'wallet', 'transactions'));
    }
}
```

---

## Controller 3: Admin\TopupRequestController

**File:** `app/Http/Controllers/Admin/TopupRequestController.php`

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgentTopupRequest;
use App\Models\AgentWallet;
use Illuminate\Http\Request;

class TopupRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = AgentTopupRequest::with('agent')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->pending(); // default: show pending
        }

        $requests = $query->paginate(20);
        $pendingCount = AgentTopupRequest::pending()->count();

        return view('admin.topup-requests.index', compact('requests', 'pendingCount'));
    }

    public function show(AgentTopupRequest $topupRequest)
    {
        $topupRequest->load('agent', 'reviewedBy');
        return view('admin.topup-requests.show', compact('topupRequest'));
    }

    public function approve(Request $request, AgentTopupRequest $topupRequest)
    {
        if ($topupRequest->status !== 'pending') {
            return back()->with('error', 'This request has already been reviewed.');
        }

        $wallet = AgentWallet::firstOrCreate(
            ['agent_id' => $topupRequest->agent_id],
        );

        // Add balance
        $wallet->credit(
            amount:        $topupRequest->amount,
            note:          'Top-up request #' . $topupRequest->id . ' approved',
            performedBy:   auth()->id(),
            paymentMethod: $topupRequest->payment_method,
            reference:     'TOPUP-' . $topupRequest->id,
        );

        // Update request status
        $topupRequest->update([
            'status'      => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Top-up request approved. PKR ' . number_format($topupRequest->amount, 2) . ' added to agent wallet.');
    }

    public function reject(Request $request, AgentTopupRequest $topupRequest)
    {
        $request->validate([
            'rejection_note' => 'required|string|max:500',
        ]);

        if ($topupRequest->status !== 'pending') {
            return back()->with('error', 'This request has already been reviewed.');
        }

        $topupRequest->update([
            'status'         => 'rejected',
            'reviewed_by'    => auth()->id(),
            'reviewed_at'    => now(),
            'rejection_note' => $request->rejection_note,
        ]);

        return back()->with('success', 'Top-up request rejected.');
    }
}
```
