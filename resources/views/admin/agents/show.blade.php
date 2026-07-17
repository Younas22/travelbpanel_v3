@extends('admin.layouts.app')
@section('title', 'Agent: ' . $agent->full_name)

@section('content')

    @php
        $initials = collect(explode(' ', $agent->full_name))
            ->map(fn($n) => strtoupper(substr($n, 0, 1)))
            ->take(2)->implode('');
    @endphp

        <!-- ===== PAGE HEADER ===== -->
    <div class="as-header">
        <div class="as-header-left">
            <div class="as-avatar">{{ $initials }}</div>
            <div>
                <h2 class="as-title">{{ $agent->full_name }}</h2>
                <p class="as-subtitle">{{ $agent->agent_code }} &middot; {{ $agent->email }}</p>
            </div>
        </div>
        <div class="as-header-actions">
            <a href="{{ route('admin.agents.edit', $agent) }}" class="as-btn">
                <i class="bi bi-pencil"></i> Edit
            </a>
            <a href="{{ route('admin.agents.index') }}" class="as-btn">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <!-- ===== TABS ===== -->
    <div class="as-tabs nav" id="agentTabs" role="tablist">
        <a class="as-tab nav-link active" id="overview-tab" data-bs-toggle="tab" href="#overview" role="tab" aria-controls="overview" aria-selected="true"><i class="bi bi-person"></i> Overview</a>
        <a class="as-tab nav-link" id="permissions-tab" data-bs-toggle="tab" href="#permissions" role="tab" aria-controls="permissions" aria-selected="false"><i class="bi bi-shield-check"></i> Permissions</a>
        <a class="as-tab nav-link" id="wallet-tab" data-bs-toggle="tab" href="#wallet" role="tab" aria-controls="wallet" aria-selected="false"><i class="bi bi-wallet2"></i> Wallet</a>
        <a class="as-tab nav-link" id="bookings-tab" data-bs-toggle="tab" href="#bookings" role="tab" aria-controls="bookings" aria-selected="false"><i class="bi bi-calendar-check"></i> Bookings</a>
    </div>

    <div class="tab-content">

        <!-- ===== OVERVIEW TAB ===== -->
        <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview-tab" tabindex="0">
            <div class="as-overview-grid">

                <!-- Agent Information -->
                <div class="as-card">
                    <h5 class="as-card-title">Agent Information</h5>
                    <div class="as-info-grid">
                        <div class="as-info-item">
                            <div class="as-info-label">Full Name</div>
                            <div class="as-info-value">{{ $agent->full_name }}</div>
                        </div>
                        <div class="as-info-item">
                            <div class="as-info-label">Email</div>
                            <div class="as-info-value">{{ $agent->email }}</div>
                        </div>
                        <div class="as-info-item">
                            <div class="as-info-label">Phone</div>
                            <div class="as-info-value">{{ $agent->phone ?? '—' }}</div>
                        </div>
                        <div class="as-info-item">
                            <div class="as-info-label">Agent Code</div>
                            <div class="as-info-value"><span class="as-code">{{ $agent->agent_code }}</span></div>
                        </div>
                        <div class="as-info-item">
                            <div class="as-info-label">Company</div>
                            <div class="as-info-value">{{ $agent->company_name ?? '—' }}</div>
                        </div>
                        <div class="as-info-item">
                            <div class="as-info-label">Company Phone</div>
                            <div class="as-info-value">{{ $agent->company_phone ?? '—' }}</div>
                        </div>
                        <div class="as-info-item">
                            <div class="as-info-label">CNIC / Reg No.</div>
                            <div class="as-info-value">{{ $agent->cnic_or_reg_number ?? '—' }}</div>
                        </div>
                        <div class="as-info-item">
                            <div class="as-info-label">Commission Rate</div>
                            <div class="as-info-value">{{ $agent->commission_rate ?? 0 }}%</div>
                        </div>
                        <div class="as-info-item as-info-full">
                            <div class="as-info-label">Company Address</div>
                            <div class="as-info-value">{{ $agent->company_address ?? '—' }}</div>
                        </div>
                        @if($agent->internal_notes)
                            <div class="as-info-item as-info-full">
                                <div class="as-info-label">Internal Notes</div>
                                <div class="as-info-value as-info-muted">{{ $agent->internal_notes }}</div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Sidebar -->
                <div>
                    <div class="as-card as-status-card">
                        {!! $agent->approval_status_badge !!}

                        @if($agent->approval_status === 'pending')
                            <form method="POST" action="{{ route('admin.agents.approve', $agent) }}">
                                @csrf
                                <button type="submit" class="as-status-btn as-status-btn-approve">
                                    <i class="bi bi-check-circle"></i> Approve Agent
                                </button>
                            </form>
                        @elseif($agent->approval_status === 'active')
                            <button type="button" class="as-status-btn as-status-btn-suspend" data-bs-toggle="modal" data-bs-target="#suspendModal">
                                <i class="bi bi-pause-circle"></i> Suspend Agent
                            </button>
                        @elseif($agent->approval_status === 'suspended')
                            <form method="POST" action="{{ route('admin.agents.activate', $agent) }}">
                                @csrf
                                <button type="submit" class="as-status-btn as-status-btn-activate">
                                    <i class="bi bi-play-circle"></i> Reactivate Agent
                                </button>
                            </form>
                        @endif
                    </div>

                    <div class="as-card">
                        <h5 class="as-card-title">Booking Stats</h5>
                        <div class="as-stats-list">
                            <div class="as-stats-row">
                                <span>Hotels</span><strong>{{ $bookingStats['hotels'] }}</strong>
                            </div>
                            <div class="as-stats-row">
                                <span>Flights</span><strong>{{ $bookingStats['flights'] }}</strong>
                            </div>
                            <div class="as-stats-row">
                                <span>Tours</span><strong>{{ $bookingStats['tours'] }}</strong>
                            </div>
                            <div class="as-stats-row">
                                <span>Umrah</span><strong>{{ $bookingStats['umrah'] }}</strong>
                            </div>
                            <div class="as-stats-row as-stats-total">
                                <span>Total</span><strong>{{ $bookingStats['total'] }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===== PERMISSIONS TAB ===== -->
        <div class="tab-pane fade" id="permissions" role="tabpanel" aria-labelledby="permissions-tab" tabindex="0">
            @include('admin.agents._tab_permissions')
        </div>

        <!-- ===== WALLET TAB ===== -->
        <div class="tab-pane fade" id="wallet" role="tabpanel" aria-labelledby="wallet-tab" tabindex="0">
            @include('admin.agents._tab_wallet')
        </div>

        <!-- ===== BOOKINGS TAB ===== -->
        <div class="tab-pane fade" id="bookings" role="tabpanel" aria-labelledby="bookings-tab" tabindex="0">
            <div class="as-card">
                <p class="as-card-text">All bookings made by this agent.</p>
                <a href="{{ route('admin.bookings.all') }}?agent_id={{ $agent->id }}" class="as-btn as-btn-primary">
                    <i class="bi bi-list-ul"></i> View all bookings by {{ $agent->full_name }}
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>

    </div>

    <!-- ===== SUSPEND MODAL ===== -->
    <div class="modal fade" id="suspendModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content as-modal">
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
                        <button type="button" class="as-btn as-btn-outline" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="as-btn as-btn-warning">Confirm Suspend</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('styles')
    <style>
        /* ===== PAGE HEADER ===== */
        .as-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 1.25rem;
        }
        .as-header-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .as-avatar {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            background: #B5D4F4;
            color: #0C447C;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            font-weight: 600;
            flex-shrink: 0;
        }
        [data-bs-theme="dark"] .as-avatar { background: #0c2f4d; color: #7db8f0; }

        .as-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--bs-body-color);
            margin: 0;
        }
        .as-subtitle {
            font-size: 12px;
            color: var(--bs-secondary-color);
            margin: 2px 0 0;
        }
        .as-header-actions { display: flex; gap: 8px; }

        /* ===== TABS ===== */
        .as-tabs {
            display: flex;
            gap: 2px;
            background: var(--bs-secondary-bg);
            border: 1px solid var(--bs-border-color);
            border-radius: 10px;
            padding: 4px;
            margin-bottom: 1.25rem;
            overflow-x: auto;
        }
        .as-tab {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 8px 16px;
            font-size: 13px;
            font-weight: 500;
            color: var(--bs-secondary-color);
            border-radius: 8px;
            text-decoration: none;
            white-space: nowrap;
            transition: background .15s, color .15s;
        }
        .as-tab:hover { color: var(--bs-body-color); text-decoration: none; }
        .as-tab.active {
            background: var(--bs-body-bg);
            color: var(--bs-body-color);
            border: 1px solid var(--bs-border-color);
        }

        /* ===== LAYOUT ===== */
        .as-overview-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 16px;
            align-items: start;
        }

        /* ===== CARD ===== */
        .as-card {
            background: var(--bs-body-bg);
            border: 1px solid var(--bs-border-color);
            border-radius: 14px;
            padding: 1.25rem 1.5rem;
            margin-bottom: 1.25rem;
        }
        .as-card-title {
            font-size: 14px;
            font-weight: 600;
            color: var(--bs-body-color);
            margin: 0 0 1.1rem;
            padding-bottom: .85rem;
            border-bottom: 1px solid var(--bs-border-color);
        }
        .as-card-text {
            font-size: 13px;
            color: var(--bs-secondary-color);
            margin: 0 0 1rem;
        }

        /* ===== INFO GRID ===== */
        .as-info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.1rem;
        }
        .as-info-full { grid-column: 1 / -1; }
        .as-info-label {
            font-size: 11px;
            font-weight: 500;
            color: var(--bs-secondary-color);
            text-transform: uppercase;
            letter-spacing: .05em;
            margin-bottom: 4px;
        }
        .as-info-value {
            font-size: 13px;
            font-weight: 500;
            color: var(--bs-body-color);
            word-break: break-word;
        }
        .as-info-muted {
            font-weight: 400;
            color: var(--bs-secondary-color);
        }
        .as-code {
            font-family: var(--bs-font-monospace, monospace);
            font-size: 12px;
            background: var(--bs-secondary-bg);
            padding: 2px 8px;
            border-radius: 6px;
        }

        /* ===== STATUS CARD ===== */
        .as-status-card {
            text-align: center;
        }
        .as-status-card .badge {
            font-size: 12px;
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 20px;
            margin-bottom: 14px;
            display: inline-block;
            letter-spacing: .02em;
            text-transform: capitalize;
        }
        .as-status-card .badge.bg-success { background: #2F82FD !important; color: #fff !important; }
        .as-status-card .badge.bg-warning { background: #FAEEDA !important; color: #633806 !important; }
        .as-status-card .badge.bg-danger  { background: #FCEBEB !important; color: #A32D2D !important; }
        .as-status-card .badge.bg-info,
        .as-status-card .badge.bg-primary { background: #E6F1FB !important; color: #0C447C !important; }
        .as-status-card .badge.bg-secondary { background: var(--bs-tertiary-bg) !important; color: var(--bs-secondary-color) !important; }

        [data-bs-theme="dark"] .as-status-card .badge.bg-success { background: #0a2e1a !important; color: #6dd499 !important; }
        [data-bs-theme="dark"] .as-status-card .badge.bg-warning { background: #2e1e05 !important; color: #f0b054 !important; }
        [data-bs-theme="dark"] .as-status-card .badge.bg-danger  { background: #2e0a0a !important; color: #f08080 !important; }
        [data-bs-theme="dark"] .as-status-card .badge.bg-info,
        [data-bs-theme="dark"] .as-status-card .badge.bg-primary { background: #0c2f4d !important; color: #7db8f0 !important; }

        .as-status-btn {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 10px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: opacity .15s;
        }
        .as-status-btn:hover { opacity: .85; }
        .as-status-btn-approve  { background: #0C6DFD; color: #fff; }
        .as-status-btn-suspend  { background: #0C6DFD; color: #fff; }
        .as-status-btn-activate { background: #0C6DFD; color: #fff; }

        /* ===== BOOKING STATS ===== */
        .as-stats-list { display: flex; flex-direction: column; }
        .as-stats-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            font-size: 13px;
            color: var(--bs-body-color);
            border-bottom: 1px solid var(--bs-border-color);
        }
        .as-stats-row:last-of-type { border-bottom: none; }
        .as-stats-row span:first-child { color: var(--bs-secondary-color); }
        .as-stats-total {
            font-weight: 600;
            background: var(--bs-secondary-bg);
            margin: 4px -10px -10px;
            padding: 10px 10px;
            border-radius: 0 0 10px 10px;
            border-bottom: none;
        }
        .as-stats-total span:first-child { color: var(--bs-body-color); }

        /* ===== BUTTONS ===== */
        .as-btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 8px 16px;
            border: 1px solid var(--bs-border-color);
            border-radius: 8px;
            background: var(--bs-secondary-bg);
            color: var(--bs-secondary-color);
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: background .15s, color .15s, opacity .15s;
        }
        .as-btn:hover {
            background: var(--bs-tertiary-bg);
            color: var(--bs-body-color);
            text-decoration: none;
        }
        .as-btn-outline {
            background: var(--bs-secondary-bg);
            color: var(--bs-secondary-color);
        }
        .as-btn-primary {
            background: #0C6DFD;
            border-color: #0C6DFD;
            color: #fff;
        }
        .as-btn-primary:hover { opacity: .9; color: #fff; }
        .as-btn-warning {
            background: #0C6DFD;
            border-color: #0C6DFD;
            color: #fff;
        }
        .as-btn-warning:hover { opacity: .9; color: #fff; }

        /* ===== MODAL ===== */
        .as-modal {
            border: 1px solid var(--bs-border-color);
            border-radius: 14px;
        }
        .as-modal .modal-header,
        .as-modal .modal-footer {
            border-color: var(--bs-border-color);
        }
        .as-modal .form-control {
            border-radius: 8px;
            border-color: var(--bs-border-color);
            font-size: 13px;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 992px) {
            .as-overview-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 768px) {
            .as-info-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 480px) {
            .as-header { align-items: flex-start; }
            .as-header-actions { width: 100%; }
            .as-btn { flex: 1; justify-content: center; }
        }
    </style>
@endpush
