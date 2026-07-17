# Agent B2B — Part 7: Admin Agent Management Views (Actual Blade Code)

---

## View 1: Agents List

**File:** `resources/views/admin/agents/index.blade.php`

```blade
@extends('admin.layouts.app')
@section('title', 'Agents Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Agents Management</h4>
        <p class="text-muted mb-0">Manage B2B travel agents</p>
    </div>
    <a href="{{ route('admin.agents.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Add Agent
    </a>
</div>

{{-- Stats --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-3">
                <div class="fs-3 fw-bold text-primary">{{ $stats['total'] }}</div>
                <div class="small text-muted">Total Agents</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-3">
                <div class="fs-3 fw-bold text-success">{{ $stats['active'] }}</div>
                <div class="small text-muted">Active</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-3">
                <div class="fs-3 fw-bold text-warning">{{ $stats['pending'] }}</div>
                <div class="small text-muted">Pending</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-3">
                <div class="fs-3 fw-bold text-danger">{{ $stats['suspended'] }}</div>
                <div class="small text-muted">Suspended</div>
            </div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control" placeholder="Search by name, email, company, code..."
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="active"    {{ request('status') === 'active'    ? 'selected' : '' }}>Active</option>
                    <option value="pending"   {{ request('status') === 'pending'   ? 'selected' : '' }}>Pending</option>
                    <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('admin.agents.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
            </div>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Agent</th>
                        <th>Code</th>
                        <th>Company</th>
                        <th>Status</th>
                        <th>Wallet Balance</th>
                        <th>Commission</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($agents as $agent)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $agent->full_name }}</div>
                            <div class="small text-muted">{{ $agent->email }}</div>
                        </td>
                        <td><code>{{ $agent->agent_code }}</code></td>
                        <td>{{ $agent->company_name ?? '—' }}</td>
                        <td>{!! $agent->approval_status_badge !!}</td>
                        <td>
                            @if($agent->wallet)
                                <span class="fw-semibold text-success">PKR {{ number_format($agent->wallet->balance, 0) }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>{{ $agent->commission_rate ?? 0 }}%</td>
                        <td class="small text-muted">{{ $agent->created_at->format('d M Y') }}</td>
                        <td>
                            <div class="d-flex gap-1 flex-wrap">
                                <a href="{{ route('admin.agents.show', $agent) }}" class="btn btn-sm btn-outline-primary" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('admin.agents.edit', $agent) }}" class="btn btn-sm btn-outline-secondary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="{{ route('admin.agents.permissions', $agent) }}" class="btn btn-sm btn-outline-warning" title="Permissions">
                                    <i class="bi bi-shield-check"></i>
                                </a>
                                <a href="{{ route('admin.agents.wallet', $agent) }}" class="btn btn-sm btn-outline-success" title="Wallet">
                                    <i class="bi bi-wallet2"></i>
                                </a>
                                @if($agent->approval_status === 'pending')
                                    <form method="POST" action="{{ route('admin.agents.approve', $agent) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" title="Approve">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                    </form>
                                @elseif($agent->approval_status === 'active')
                                    <button type="button" class="btn btn-sm btn-warning" title="Suspend"
                                            data-bs-toggle="modal" data-bs-target="#suspendModal{{ $agent->id }}">
                                        <i class="bi bi-pause-circle"></i>
                                    </button>
                                @elseif($agent->approval_status === 'suspended')
                                    <form method="POST" action="{{ route('admin.agents.activate', $agent) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-info" title="Activate">
                                            <i class="bi bi-play-circle"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>

                    {{-- Suspend Modal --}}
                    <div class="modal fade" id="suspendModal{{ $agent->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="POST" action="{{ route('admin.agents.suspend', $agent) }}">
                                    @csrf
                                    <div class="modal-header">
                                        <h5 class="modal-title">Suspend {{ $agent->full_name }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <label class="form-label">Reason (optional)</label>
                                        <textarea name="reason" class="form-control" rows="3" placeholder="Reason for suspension..."></textarea>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-warning">Suspend Agent</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">No agents found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($agents->hasPages())
    <div class="card-footer bg-white">
        {{ $agents->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
```

---

## View 2: Agent Detail (with tabs)

**File:** `resources/views/admin/agents/show.blade.php`

```blade
@extends('admin.layouts.app')
@section('title', 'Agent: ' . $agent->full_name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">{{ $agent->full_name }}</h4>
        <small class="text-muted">{{ $agent->agent_code }} &bull; {{ $agent->email }}</small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.agents.edit', $agent) }}" class="btn btn-outline-secondary">
            <i class="bi bi-pencil"></i> Edit
        </a>
        <a href="{{ route('admin.agents.index') }}" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

{{-- Tabs --}}
<ul class="nav nav-tabs mb-4" id="agentTabs">
    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#overview">Overview</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#permissions">Permissions</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#wallet">Wallet</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#bookings">Bookings</a></li>
</ul>

<div class="tab-content">

    {{-- Overview Tab --}}
    <div class="tab-pane fade show active" id="overview">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white"><h6 class="mb-0">Agent Information</h6></div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6"><label class="text-muted small">Full Name</label><div class="fw-semibold">{{ $agent->full_name }}</div></div>
                            <div class="col-md-6"><label class="text-muted small">Email</label><div>{{ $agent->email }}</div></div>
                            <div class="col-md-6"><label class="text-muted small">Phone</label><div>{{ $agent->phone ?? '—' }}</div></div>
                            <div class="col-md-6"><label class="text-muted small">Agent Code</label><div><code>{{ $agent->agent_code }}</code></div></div>
                            <div class="col-md-6"><label class="text-muted small">Company</label><div>{{ $agent->company_name ?? '—' }}</div></div>
                            <div class="col-md-6"><label class="text-muted small">Company Phone</label><div>{{ $agent->company_phone ?? '—' }}</div></div>
                            <div class="col-md-6"><label class="text-muted small">CNIC / Reg No.</label><div>{{ $agent->cnic_or_reg_number ?? '—' }}</div></div>
                            <div class="col-md-6"><label class="text-muted small">Commission Rate</label><div>{{ $agent->commission_rate ?? 0 }}%</div></div>
                            <div class="col-12"><label class="text-muted small">Company Address</label><div>{{ $agent->company_address ?? '—' }}</div></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body text-center">
                        <div class="mb-3">{!! $agent->approval_status_badge !!}</div>
                        @if($agent->approval_status === 'pending')
                            <form method="POST" action="{{ route('admin.agents.approve', $agent) }}">
                                @csrf
                                <button type="submit" class="btn btn-success w-100 mb-2">Approve Agent</button>
                            </form>
                        @elseif($agent->approval_status === 'active')
                            <button type="button" class="btn btn-warning w-100" data-bs-toggle="modal" data-bs-target="#suspendModal">Suspend Agent</button>
                        @elseif($agent->approval_status === 'suspended')
                            <form method="POST" action="{{ route('admin.agents.activate', $agent) }}">
                                @csrf
                                <button type="submit" class="btn btn-info w-100">Reactivate Agent</button>
                            </form>
                        @endif
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white"><h6 class="mb-0">Booking Stats</h6></div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between"><span>Hotels</span><strong>{{ $bookingStats['hotels'] }}</strong></li>
                            <li class="list-group-item d-flex justify-content-between"><span>Flights</span><strong>{{ $bookingStats['flights'] }}</strong></li>
                            <li class="list-group-item d-flex justify-content-between"><span>Tours</span><strong>{{ $bookingStats['tours'] }}</strong></li>
                            <li class="list-group-item d-flex justify-content-between"><span>Umrah</span><strong>{{ $bookingStats['umrah'] }}</strong></li>
                            <li class="list-group-item d-flex justify-content-between bg-light"><span class="fw-semibold">Total</span><strong>{{ $bookingStats['total'] }}</strong></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Permissions Tab --}}
    <div class="tab-pane fade" id="permissions">
        @include('admin.agents._tab_permissions')
    </div>

    {{-- Wallet Tab --}}
    <div class="tab-pane fade" id="wallet">
        @include('admin.agents._tab_wallet')
    </div>

    {{-- Bookings Tab --}}
    <div class="tab-pane fade" id="bookings">
        <p class="text-muted">
            <a href="{{ route('admin.bookings.all') }}?agent_id={{ $agent->id }}">View all bookings by this agent →</a>
        </p>
    </div>

</div>

{{-- Suspend Modal --}}
<div class="modal fade" id="suspendModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.agents.suspend', $agent) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Suspend Agent</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label">Reason (optional)</label>
                    <textarea name="reason" class="form-control" rows="3"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Confirm Suspend</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
```

---

## View 3: Permissions Tab (partial)

**File:** `resources/views/admin/agents/_tab_permissions.blade.php`

```blade
<form method="POST" action="{{ route('admin.agents.permissions.save', $agent) }}">
    @csrf
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="bi bi-shield-check"></i> Module Permissions</h6>
            <button type="submit" class="btn btn-primary btn-sm">Save Permissions</button>
        </div>
        <div class="card-body">

            <div class="row g-4">
                {{-- Module Access --}}
                <div class="col-md-6">
                    <h6 class="text-muted text-uppercase small fw-bold mb-3">Module Access</h6>
                    @foreach(['module.hotels' => 'Hotels', 'module.flights' => 'Flights', 'module.tours' => 'Tours', 'module.umrah' => 'Umrah', 'module.visa' => 'Visa'] as $key => $label)
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="permissions[{{ $key }}]" value="1"
                               id="perm_{{ str_replace('.', '_', $key) }}"
                               {{ isset($agentPermissions[$key]) && $agentPermissions[$key] ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="perm_{{ str_replace('.', '_', $key) }}">
                            {{ $label }}
                        </label>
                    </div>
                    @endforeach
                </div>

                {{-- Sub Permissions --}}
                <div class="col-md-6">
                    <h6 class="text-muted text-uppercase small fw-bold mb-3">Source Permissions</h6>
                    @foreach(['hotels.api' => 'API Hotels (Hotelbeds/Agoda)', 'hotels.manual' => 'Manual Hotels', 'flights.api' => 'API Flights (Amadeus/Sabre)', 'flights.manual' => 'Manual Flights', 'tours.manual' => 'Manual Tours', 'umrah.manual' => 'Manual Umrah Packages', 'visa.submit' => 'Submit Visa Requests'] as $key => $label)
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="permissions[{{ $key }}]" value="1"
                               id="perm_{{ str_replace('.', '_', $key) }}"
                               {{ isset($agentPermissions[$key]) && $agentPermissions[$key] ? 'checked' : '' }}>
                        <label class="form-check-label" for="perm_{{ str_replace('.', '_', $key) }}">
                            {{ $label }}
                        </label>
                    </div>
                    @endforeach
                </div>

                {{-- Wallet Permissions --}}
                <div class="col-md-6">
                    <h6 class="text-muted text-uppercase small fw-bold mb-3">Wallet</h6>
                    @foreach(['wallet.view' => 'View Wallet Balance', 'wallet.request' => 'Request Top-Up', 'bookings.view' => 'View Own Bookings'] as $key => $label)
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="permissions[{{ $key }}]" value="1"
                               id="perm_{{ str_replace('.', '_', $key) }}"
                               {{ isset($agentPermissions[$key]) && $agentPermissions[$key] ? 'checked' : '' }}>
                        <label class="form-check-label" for="perm_{{ str_replace('.', '_', $key) }}">
                            {{ $label }}
                        </label>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</form>
```

---

## View 4: Wallet Tab (partial)

**File:** `resources/views/admin/agents/_tab_wallet.blade.php`

```blade
@php $wallet = $agent->wallet; @endphp

{{-- Balance Cards --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-3">
                <div class="text-muted small">Balance</div>
                <div class="fs-4 fw-bold text-success">PKR {{ number_format($wallet?->balance ?? 0, 2) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-3">
                <div class="text-muted small">Total Credited</div>
                <div class="fs-4 fw-bold text-primary">PKR {{ number_format($wallet?->total_credited ?? 0, 2) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-3">
                <div class="text-muted small">Total Debited</div>
                <div class="fs-4 fw-bold text-danger">PKR {{ number_format($wallet?->total_debited ?? 0, 2) }}</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    {{-- Add Balance --}}
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-success text-white"><h6 class="mb-0">Add Balance (Credit)</h6></div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.agents.wallet.credit', $agent) }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Amount (PKR)</label>
                        <input type="number" name="amount" class="form-control" min="1" step="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Method</label>
                        <input type="text" name="payment_method" class="form-control" placeholder="e.g. Bank Transfer, Cash">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Note</label>
                        <textarea name="note" class="form-control" rows="2" placeholder="Optional note..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-success w-100">Add Balance</button>
                </form>
            </div>
        </div>
    </div>

    {{-- Deduct Balance --}}
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-danger text-white"><h6 class="mb-0">Deduct Balance (Debit)</h6></div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.agents.wallet.debit', $agent) }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Amount (PKR)</label>
                        <input type="number" name="amount" class="form-control" min="1" step="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason <span class="text-danger">*</span></label>
                        <textarea name="note" class="form-control" rows="2" placeholder="Reason for deduction..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-danger w-100"
                            onclick="return confirm('Deduct from wallet?')">Deduct Balance</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="mt-3 text-end">
    <a href="{{ route('admin.agents.wallet.transactions', $agent) }}" class="btn btn-outline-primary">
        <i class="bi bi-clock-history"></i> View Full Transaction History
    </a>
</div>
```

---

## View 5: Top-Up Requests List

**File:** `resources/views/admin/topup-requests/index.blade.php`

```blade
@extends('admin.layouts.app')
@section('title', 'Top-Up Requests')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Wallet Top-Up Requests</h4>
        @if($pendingCount > 0)
            <span class="badge bg-warning text-dark">{{ $pendingCount }} pending</span>
        @endif
    </div>
</div>

{{-- Filter --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="d-flex gap-2 align-items-center">
            <select name="status" class="form-select form-select-sm" style="width:150px;">
                <option value="">All Status</option>
                <option value="pending"  {{ request('status') === 'pending'  ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
            </select>
            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
            <a href="{{ route('admin.topup.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Agent</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Proof</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $req)
                    <tr>
                        <td>{{ $req->id }}</td>
                        <td>
                            <div class="fw-semibold">{{ $req->agent->full_name }}</div>
                            <div class="small text-muted">{{ $req->agent->agent_code }}</div>
                        </td>
                        <td class="fw-semibold">PKR {{ number_format($req->amount, 2) }}</td>
                        <td>{{ $req->payment_method ?? '—' }}</td>
                        <td>
                            @if($req->payment_proof)
                                <a href="{{ asset('storage/' . $req->payment_proof) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-file-earmark"></i> View
                                </a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td><span class="{{ $req->status_badge_class }}">{{ ucfirst($req->status) }}</span></td>
                        <td class="small text-muted">{{ $req->created_at->format('d M Y, h:i A') }}</td>
                        <td>
                            @if($req->status === 'pending')
                            <div class="d-flex gap-1">
                                <form method="POST" action="{{ route('admin.topup.approve', $req) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success"
                                            onclick="return confirm('Approve PKR {{ number_format($req->amount, 0) }} top-up?')">
                                        <i class="bi bi-check-lg"></i> Approve
                                    </button>
                                </form>
                                <button type="button" class="btn btn-sm btn-danger"
                                        data-bs-toggle="modal" data-bs-target="#rejectModal{{ $req->id }}">
                                    <i class="bi bi-x-lg"></i> Reject
                                </button>
                            </div>

                            {{-- Reject Modal --}}
                            <div class="modal fade" id="rejectModal{{ $req->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST" action="{{ route('admin.topup.reject', $req) }}">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title">Reject Top-Up Request</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <label class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                                                <textarea name="rejection_note" class="form-control" rows="3" required
                                                          placeholder="Tell agent why request was rejected..."></textarea>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-danger">Reject Request</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @else
                                <a href="{{ route('admin.topup.show', $req) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-eye"></i> View
                                </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">No requests found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($requests->hasPages())
    <div class="card-footer bg-white">{{ $requests->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
```
