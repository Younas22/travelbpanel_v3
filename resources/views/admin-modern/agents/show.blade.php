@extends('admin-modern.layouts.app')
@section('title', 'Agent: ' . $agent->full_name)

@section('content')

    @php
        $initials = collect(explode(' ', $agent->full_name))
            ->map(fn($n) => strtoupper(substr($n, 0, 1)))
            ->take(2)->implode('');
    @endphp

    <div class="as-header">
        <div class="as-header-left">
            <div class="as-avatar">{{ $initials }}</div>
            <div>
                <h2 class="as-title">{{ $agent->full_name }}</h2>
                <p class="as-subtitle">{{ $agent->agent_code }} &middot; {{ $agent->email }}</p>
            </div>
        </div>
        <div class="as-header-actions">
            <a href="{{ route('admin.agents.edit', $agent) }}" class="as-btn"><i class="bi bi-pencil"></i> Edit</a>
            <a href="{{ route('admin.agents.index') }}" class="as-btn"><i class="bi bi-arrow-left"></i> Back</a>
        </div>
    </div>

    <div class="as-tabs nav" id="agentTabs" role="tablist">
        <a class="as-tab nav-link active" id="overview-tab" data-bs-toggle="tab" href="#overview" role="tab"><i class="bi bi-person"></i> Overview</a>
        <a class="as-tab nav-link" id="permissions-tab" data-bs-toggle="tab" href="#permissions" role="tab"><i class="bi bi-shield-check"></i> Permissions</a>
        <a class="as-tab nav-link" id="wallet-tab" data-bs-toggle="tab" href="#wallet" role="tab"><i class="bi bi-wallet2"></i> Wallet</a>
        <a class="as-tab nav-link" id="bookings-tab" data-bs-toggle="tab" href="#bookings" role="tab"><i class="bi bi-calendar-check"></i> Bookings</a>
    </div>

    <div class="tab-content">

        <div class="tab-pane fade show active" id="overview" role="tabpanel">
            <div class="as-overview-grid">

                <div class="as-card">
                    <h5 class="as-card-title">Agent Information</h5>
                    <div class="as-info-grid">
                        <div class="as-info-item"><div class="as-info-label">Full Name</div><div class="as-info-value">{{ $agent->full_name }}</div></div>
                        <div class="as-info-item"><div class="as-info-label">Email</div><div class="as-info-value">{{ $agent->email }}</div></div>
                        <div class="as-info-item"><div class="as-info-label">Phone</div><div class="as-info-value">{{ $agent->phone ?? '—' }}</div></div>
                        <div class="as-info-item"><div class="as-info-label">Agent Code</div><div class="as-info-value"><span class="as-code">{{ $agent->agent_code }}</span></div></div>
                        <div class="as-info-item"><div class="as-info-label">Company</div><div class="as-info-value">{{ $agent->company_name ?? '—' }}</div></div>
                        <div class="as-info-item"><div class="as-info-label">Company Phone</div><div class="as-info-value">{{ $agent->company_phone ?? '—' }}</div></div>
                        <div class="as-info-item"><div class="as-info-label">CNIC / Reg No.</div><div class="as-info-value">{{ $agent->cnic_or_reg_number ?? '—' }}</div></div>
                        <div class="as-info-item"><div class="as-info-label">Commission Rate</div><div class="as-info-value">{{ $agent->commission_rate ?? 0 }}%</div></div>
                        <div class="as-info-item as-info-full"><div class="as-info-label">Company Address</div><div class="as-info-value">{{ $agent->company_address ?? '—' }}</div></div>
                        @if($agent->internal_notes)
                            <div class="as-info-item as-info-full"><div class="as-info-label">Internal Notes</div><div class="as-info-value as-info-muted">{{ $agent->internal_notes }}</div></div>
                        @endif
                    </div>
                </div>

                <div>
                    <div class="as-card as-status-card">
                        {!! $agent->approval_status_badge !!}

                        @if($agent->approval_status === 'pending')
                            <form method="POST" action="{{ route('admin.agents.approve', $agent) }}">
                                @csrf
                                <button type="submit" class="as-status-btn as-status-btn-approve"><i class="bi bi-check-circle"></i> Approve Agent</button>
                            </form>
                        @elseif($agent->approval_status === 'active')
                            <button type="button" class="as-status-btn as-status-btn-suspend" data-bs-toggle="modal" data-bs-target="#suspendModal"><i class="bi bi-pause-circle"></i> Suspend Agent</button>
                        @elseif($agent->approval_status === 'suspended')
                            <form method="POST" action="{{ route('admin.agents.activate', $agent) }}">
                                @csrf
                                <button type="submit" class="as-status-btn as-status-btn-activate"><i class="bi bi-play-circle"></i> Reactivate Agent</button>
                            </form>
                        @endif
                    </div>

                    <div class="as-card">
                        <h5 class="as-card-title">Booking Stats</h5>
                        <div class="as-stats-list">
                            <div class="as-stats-row"><span>Hotels</span><strong>{{ $bookingStats['hotels'] }}</strong></div>
                            <div class="as-stats-row"><span>Flights</span><strong>{{ $bookingStats['flights'] }}</strong></div>
                            <div class="as-stats-row"><span>Tours</span><strong>{{ $bookingStats['tours'] }}</strong></div>
                            <div class="as-stats-row"><span>Umrah</span><strong>{{ $bookingStats['umrah'] }}</strong></div>
                            <div class="as-stats-row as-stats-total"><span>Total</span><strong>{{ $bookingStats['total'] }}</strong></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="permissions" role="tabpanel">
            @include('admin-modern.agents._tab_permissions')
        </div>

        <div class="tab-pane fade" id="wallet" role="tabpanel">
            @include('admin-modern.agents._tab_wallet')
        </div>

        <div class="tab-pane fade" id="bookings" role="tabpanel">
            <div class="as-card">
                <p class="as-card-text">All bookings made by this agent.</p>
                <a href="{{ route('admin.bookings.all') }}?agent_id={{ $agent->id }}" class="as-btn as-btn-primary">
                    <i class="bi bi-list-ul"></i> View all bookings by {{ $agent->full_name }}
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>

    </div>

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
