@extends('admin-nova.layouts.app')

@section('title', 'Agent: ' . $agent->full_name)

@push('styles')
@include('admin-nova.agents._styles')
@endpush

@section('content')
@php
    $initials = collect(explode(' ', $agent->full_name))
        ->map(fn($n) => strtoupper(substr($n, 0, 1)))
        ->take(2)->implode('');

    // show() eager-loads $agent->wallet and $agent->topupRequests (latest 5)
    // but doesn't hand the Wallet tab its own $wallet/$recentTransactions/
    // $topupRequests variables the way AgentWalletController::index() does —
    // computed here from the same real, already-authorized data/query scope
    // so the Wallet tab renders instead of hitting an undefined variable.
    $wallet = $agent->wallet;
    $recentTransactions = \App\Models\AgentWalletTransaction::byAgent($agent->id)->latest()->take(10)->get();
    $topupRequests = $agent->topupRequests;

    $avatarPalette = [
        ['bg' => '#DBEAFE', 'text' => '#1D4ED8'],
        ['bg' => '#DCFCE7', 'text' => '#15803D'],
        ['bg' => '#FEF3C7', 'text' => '#B45309'],
        ['bg' => '#FCE7F3', 'text' => '#BE185D'],
        ['bg' => '#EDE9FE', 'text' => '#6D28D9'],
    ];
    $avatar = $avatarPalette[crc32($agent->full_name) % count($avatarPalette)];
@endphp

<div id="agPage" class="tt-fade-in font-jakarta">

    {{-- ===== HEADER ===== --}}
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div class="flex items-center gap-3 min-w-0">
            <div class="w-12 h-12 rounded-full flex items-center justify-center text-sm font-bold flex-shrink-0"
                 style="background:{{ $avatar['bg'] }}; color:{{ $avatar['text'] }}">{{ $initials }}</div>
            <div class="min-w-0">
                <h1 class="text-lg font-bold text-novatext truncate">{{ $agent->full_name }}</h1>
                <p class="text-xs text-novamuted mt-0.5 truncate">{{ $agent->agent_code }} &middot; {{ $agent->email }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2 flex-shrink-0">
            <a href="{{ route('admin.agents.edit', $agent) }}" class="ag-btn-nova px-4 py-2.5 text-xs font-semibold">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M16.474 5.408 18.592 7.526M4 20l1.11-3.92a2 2 0 0 1 .53-.9l9.9-9.9a1.5 1.5 0 0 1 2.12 0l1.06 1.06a1.5 1.5 0 0 1 0 2.12l-9.9 9.9a2 2 0 0 1-.9.53L4 20Z"/></svg>
                Edit
            </a>
            <a href="{{ route('admin.agents.index') }}" class="ag-btn-nova px-4 py-2.5 text-xs font-semibold">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                Back
            </a>
        </div>
    </div>

    {{-- ===== TABS (Bootstrap tab-pane machinery kept, pills restyled) ===== --}}
    <div class="ag-tabs" role="tablist">
        <a class="ag-tab active" id="overview-tab" data-bs-toggle="tab" href="#overview" role="tab" aria-controls="overview" aria-selected="true">
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="3.25"/><path d="M4.75 19c.6-3.7 3.4-6 7.25-6s6.65 2.3 7.25 6"/></svg>
            Overview
        </a>
        <a class="ag-tab" id="permissions-tab" data-bs-toggle="tab" href="#permissions" role="tab" aria-controls="permissions" aria-selected="false">
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3 4 6v6c0 5 3.5 8 8 9 4.5-1 8-4 8-9V6l-8-3Z"/></svg>
            Permissions
        </a>
        <a class="ag-tab" id="wallet-tab" data-bs-toggle="tab" href="#wallet" role="tab" aria-controls="wallet" aria-selected="false">
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="6" width="18" height="13" rx="2"/><path d="M3 10h18M16 14.5h1.5"/></svg>
            Wallet
        </a>
        <a class="ag-tab" id="bookings-tab" data-bs-toggle="tab" href="#bookings" role="tab" aria-controls="bookings" aria-selected="false">
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="17" rx="2"/><path d="M3 9h18M8 2v4M16 2v4"/></svg>
            Bookings
        </a>
    </div>

    <div class="tab-content">

        {{-- ===== OVERVIEW TAB ===== --}}
        <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview-tab">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

                {{-- Agent Information --}}
                <div class="lg:col-span-2 tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 sm:p-5">
                    <h2 class="text-sm font-semibold text-novatext mb-4">Agent Information</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-novamuted mb-1">Full Name</p>
                            <p class="text-xs text-novatext">{{ $agent->full_name }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-novamuted mb-1">Email</p>
                            <p class="text-xs text-novatext">{{ $agent->email }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-novamuted mb-1">Phone</p>
                            <p class="text-xs text-novatext">{{ $agent->phone ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-novamuted mb-1">Agent Code</p>
                            <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-novatext">{{ $agent->agent_code }}</span>
                        </div>
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-novamuted mb-1">Company</p>
                            <p class="text-xs text-novatext">{{ $agent->company_name ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-novamuted mb-1">Company Phone</p>
                            <p class="text-xs text-novatext">{{ $agent->company_phone ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-novamuted mb-1">CNIC / Reg No.</p>
                            <p class="text-xs text-novatext">{{ $agent->cnic_or_reg_number ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-novamuted mb-1">Commission Rate</p>
                            <p class="text-xs text-novatext">{{ $agent->commission_rate ?? 0 }}%</p>
                        </div>
                        <div class="sm:col-span-2">
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-novamuted mb-1">Company Address</p>
                            <p class="text-xs text-novatext">{{ $agent->company_address ?? '—' }}</p>
                        </div>
                        @if($agent->internal_notes)
                            <div class="sm:col-span-2">
                                <p class="text-[10px] font-semibold uppercase tracking-wide text-novamuted mb-1">Internal Notes</p>
                                <p class="text-xs text-novamuted">{{ $agent->internal_notes }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Sidebar --}}
                <div class="space-y-4">
                    <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 sm:p-5">
                        <div class="mb-3">{!! $agent->approval_status_badge !!}</div>

                        @if($agent->approval_status === 'pending')
                            <form method="POST" action="{{ route('admin.agents.approve', $agent) }}">
                                @csrf
                                <button type="submit" class="ag-btn-nova w-full py-2.5 text-xs font-semibold" style="background:#22C55E; color:#fff; border-color:#22C55E;">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                                    Approve Agent
                                </button>
                            </form>
                        @elseif($agent->approval_status === 'active')
                            <button type="button" class="ag-btn-nova w-full py-2.5 text-xs font-semibold" style="color:#F59E0B; border-color:#F59E0B;" data-bs-toggle="modal" data-bs-target="#suspendModal">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="6" y="5" width="4" height="14" rx="1"/><rect x="14" y="5" width="4" height="14" rx="1"/></svg>
                                Suspend Agent
                            </button>
                        @elseif($agent->approval_status === 'suspended')
                            <form method="POST" action="{{ route('admin.agents.activate', $agent) }}">
                                @csrf
                                <button type="submit" class="ag-btn-nova w-full py-2.5 text-xs font-semibold" style="background:#22C55E; color:#fff; border-color:#22C55E;">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M6 4.5v15l13-7.5-13-7.5Z"/></svg>
                                    Reactivate Agent
                                </button>
                            </form>
                        @endif
                    </div>

                    <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 sm:p-5">
                        <h3 class="text-sm font-semibold text-novatext mb-3">Booking Stats</h3>
                        <div class="space-y-2.5">
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-novamuted">Hotels</span><strong class="text-novatext">{{ $bookingStats['hotels'] }}</strong>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-novamuted">Flights</span><strong class="text-novatext">{{ $bookingStats['flights'] }}</strong>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-novamuted">Tours</span><strong class="text-novatext">{{ $bookingStats['tours'] }}</strong>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-novamuted">Umrah</span><strong class="text-novatext">{{ $bookingStats['umrah'] }}</strong>
                            </div>
                            <div class="flex items-center justify-between text-xs pt-2.5 border-t border-novaborder">
                                <span class="font-semibold text-novatext">Total</span><strong class="text-novablue">{{ $bookingStats['total'] }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== PERMISSIONS TAB ===== --}}
        <div class="tab-pane fade" id="permissions" role="tabpanel" aria-labelledby="permissions-tab">
            @include('admin-nova.agents._tab_permissions')
        </div>

        {{-- ===== WALLET TAB ===== --}}
        <div class="tab-pane fade" id="wallet" role="tabpanel" aria-labelledby="wallet-tab">
            @include('admin-nova.agents._tab_wallet')
        </div>

        {{-- ===== BOOKINGS TAB ===== --}}
        <div class="tab-pane fade" id="bookings" role="tabpanel" aria-labelledby="bookings-tab">
            <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 sm:p-5">
                <p class="text-xs text-novamuted mb-4">All bookings made by this agent.</p>
                <a href="{{ route('admin.bookings.all') }}?agent_id={{ $agent->id }}" class="ag-btn-nova ag-btn-primary px-4 py-2.5 text-xs font-semibold">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="17" rx="2"/><path d="M3 9h18M8 2v4M16 2v4"/></svg>
                    View all bookings by {{ $agent->full_name }}
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                </a>
            </div>
        </div>
    </div>

    {{-- ===== SUSPEND MODAL ===== --}}
    <div class="modal fade" id="suspendModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" style="border-radius:1.5rem; font-family:'Plus Jakarta Sans',sans-serif;">
                <form method="POST" action="{{ route('admin.agents.suspend', $agent) }}">
                    @csrf
                    <div class="modal-header" style="border-bottom:1px solid #E5E7EB;">
                        <h5 class="modal-title" style="font-weight:700;">Suspend Agent</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <label class="form-label" style="font-size:.8rem; font-weight:600;">Reason (optional)</label>
                        <textarea name="reason" class="form-control" style="border-radius:1rem;" rows="3"></textarea>
                    </div>
                    <div class="modal-footer" style="border-top:1px solid #E5E7EB;">
                        <button type="button" data-bs-dismiss="modal"
                                style="display:inline-flex; align-items:center; border-radius:9999px; padding:10px 20px; font-size:13px; font-weight:600; font-family:'Plus Jakarta Sans',sans-serif; background:#F7F8FC; color:#000; border:1px solid #E5E7EB; cursor:pointer;">Cancel</button>
                        <button type="submit"
                                style="display:inline-flex; align-items:center; border-radius:9999px; padding:10px 20px; font-size:13px; font-weight:600; font-family:'Plus Jakarta Sans',sans-serif; background:#F59E0B; color:#fff; border:1px solid #F59E0B; cursor:pointer;">Confirm Suspend</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Bootstrap's tab JS toggles .show/.active on .tab-pane already; this just
    // mirrors the same active state onto our .ag-tab pills since they're <a>
    // elements (Bootstrap's tab plugin manages .nav-link internally, but we
    // dropped the .nav/.nav-link classes in favor of .ag-tab for the Nova look).
    document.querySelectorAll('#agPage .ag-tab').forEach(function (tab) {
        tab.addEventListener('shown.bs.tab', function (e) {
            document.querySelectorAll('#agPage .ag-tab').forEach(function (t) { t.classList.remove('active'); });
            e.target.classList.add('active');
        });
    });
</script>
@endpush
@endsection
