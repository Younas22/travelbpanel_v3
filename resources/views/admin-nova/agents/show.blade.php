@extends('admin-nova.layouts.app')

@section('title', 'Agent: ' . $agent->full_name)

@section('content')
@php
    $wallet = $agent->wallet;
    // Same display rule as the wallet page: stored "PKR" wallets show as USD.
    $walletCurrency = ($wallet?->currency && $wallet->currency !== 'PKR') ? $wallet->currency : 'USD';
    $money = fn ($v) => $walletCurrency . ' ' . number_format((float) $v, 2);
    $isFixed = $agent->commission_type === 'fixed';
    $commission = $isFixed ? $money($agent->commission_rate ?? 0) : rtrim(rtrim(number_format((float) ($agent->commission_rate ?? 0), 2), '0'), '.') . '%';

    // Recent activity: latest wallet movements plus the account creation event.
    $activity = \App\Models\AgentWalletTransaction::byAgent($agent->id)->latest()->take(5)->get()
        ->map(fn ($t) => [
            'date'    => $t->created_at,
            'title'   => $t->type === 'credit' ? 'Wallet Credit' : 'Wallet Debit',
            'details' => $t->note ?: ($t->reference ?: '—'),
            'amount'  => ($t->type === 'credit' ? '+' : '−') . $money($t->amount),
            'status'  => ucfirst($t->status ?: 'completed'),
        ])
        ->push([
            'date' => $agent->created_at, 'title' => 'Agent Created', 'details' => 'Agent account was created',
            'amount' => '—', 'status' => 'Completed',
        ])
        ->filter(fn ($a) => $a['date'])->sortByDesc('date')->take(5);

    $infoLeft = [
        ['bi-person', 'Full Name', $agent->full_name],
        ['bi-envelope', 'Email Address', $agent->email],
        ['bi-telephone', 'Phone Number', $agent->phone],
        ['bi-building', 'Company', $agent->company_name],
        ['bi-geo-alt', 'Company Address', $agent->company_address],
    ];
    $infoRight = [
        ['bi-grid', 'Agent Code', $agent->agent_code],
        ['bi-telephone-forward', 'Company Phone', $agent->company_phone],
        ['bi-file-earmark-text', 'Tax Number', $agent->tax_number],
        ['bi-percent', 'Commission Rate', $commission],
    ];
    $statRows = [['bi-building', 'Hotels', 'hotels'], ['bi-airplane', 'Flights', 'flights'], ['bi-map', 'Tours', 'tours'], ['bi-moon-stars', 'Umrah', 'umrah']];
@endphp

<div class="tbp-agent-detail-page" id="agPage">
    @include('admin-nova.agents._detail_header', ['activeTab' => 'overview', 'inPageTabs' => true])

    <div class="tab-content">

        {{-- ===== OVERVIEW ===== --}}
        <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview-tab">
            <div class="tbp-agent-card tbp-agent-summary-row">
                <div class="tbp-agent-summary-card">
                    <span class="tbp-agent-icon-box"><i class="bi bi-calendar3"></i></span>
                    <div><small>Total Bookings</small><strong>{{ number_format($bookingStats['total']) }}</strong><em>Across all services</em></div>
                </div>
                <div class="tbp-agent-summary-card">
                    <span class="tbp-agent-icon-box"><i class="bi bi-wallet2"></i></span>
                    <div><small>Wallet Balance</small><strong>{{ $money($wallet?->balance ?? 0) }}</strong><em>Available balance</em></div>
                </div>
                <div class="tbp-agent-summary-card">
                    <span class="tbp-agent-icon-box"><i class="bi bi-percent"></i></span>
                    <div><small>Commission Rate</small><strong>{{ $commission }}</strong><em>{{ $isFixed ? 'Fixed per booking' : 'Percentage' }}</em></div>
                </div>
                <div class="tbp-agent-summary-card">
                    <span class="tbp-agent-icon-box"><i class="bi bi-calendar-check"></i></span>
                    <div><small>Member Since</small><strong>{{ $agent->created_at?->format('d M Y') ?? '—' }}</strong><em>{{ $agent->created_at?->diffForHumans() }}</em></div>
                </div>
            </div>

            <div class="tbp-agent-overview-grid">
                <div class="tbp-agent-overview-main">
                    <section class="tbp-agent-card tbp-agent-information-card">
                        <header class="tbp-agent-card-head">
                            <h2><i class="bi bi-person"></i> Agent Information</h2>
                            <span class="tbp-agent-status is-{{ $agent->approval_status }}">{{ ucfirst($agent->approval_status ?? 'unknown') }}</span>
                        </header>
                        <div class="tbp-agent-info-columns">
                            @foreach([$infoLeft, $infoRight] as $col)
                                <dl>
                                    @foreach($col as [$icon, $label, $value])
                                        <div class="tbp-agent-info-row">
                                            <i class="bi {{ $icon }}"></i>
                                            <div>
                                                <dt>{{ $label }}</dt>
                                                <dd>
                                                    {{ filled($value) ? $value : '—' }}
                                                    @if($label === 'Agent Code' && filled($value))
                                                        <button type="button" class="tbp-agent-copy" data-copy="{{ $value }}" title="Copy"><i class="bi bi-copy"></i></button>
                                                    @endif
                                                </dd>
                                            </div>
                                        </div>
                                    @endforeach
                                    @if($col === $infoRight)
                                        <div class="tbp-agent-info-row">
                                            <i class="bi bi-shield-check"></i>
                                            <div><dt>Status</dt><dd><span class="tbp-agent-status is-{{ $agent->approval_status }}">{{ ucfirst($agent->approval_status ?? 'unknown') }}</span></dd></div>
                                        </div>
                                    @endif
                                </dl>
                            @endforeach
                        </div>
                        @if($agent->internal_notes)
                            <div class="tbp-agent-notes"><strong>Internal Notes</strong><p>{{ $agent->internal_notes }}</p></div>
                        @endif
                    </section>

                    <section class="tbp-agent-card tbp-agent-recent-activity">
                        <header class="tbp-agent-card-head">
                            <h2><i class="bi bi-clock-history"></i> Recent Activity</h2>
                            <a href="{{ route('admin.agents.wallet.transactions', $agent) }}" class="tbp-agent-btn is-small">View All</a>
                        </header>
                        <div class="tbp-agent-table-wrap">
                            <table>
                                <thead><tr><th>Date</th><th>Activity</th><th>Details</th><th>Amount</th><th>Status</th></tr></thead>
                                <tbody>
                                    @forelse($activity as $row)
                                        <tr>
                                            <td>{{ $row['date']->format('d M Y') }}</td>
                                            <td>{{ $row['title'] }}</td>
                                            <td class="is-details">{{ \Illuminate\Support\Str::limit($row['details'] ?? '', 60) }}</td>
                                            <td>{{ $row['amount'] }}</td>
                                            <td><span class="tbp-agent-status is-{{ strtolower($row['status']) }}">{{ $row['status'] }}</span></td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="tbp-agent-empty">No activity yet.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>

                <aside class="tbp-agent-overview-side">
                    <section class="tbp-agent-card tbp-agent-quick-actions">
                        <h2><i class="bi bi-lightning"></i> Quick Actions</h2>
                        <a href="{{ route('admin.agents.edit', $agent) }}" class="tbp-agent-btn is-outline-blue"><i class="bi bi-pencil"></i> Edit Agent</a>
                        @if($agent->approval_status === 'pending')
                            <form method="POST" action="{{ route('admin.agents.approve', $agent) }}">
                                @csrf
                                <button type="submit" class="tbp-agent-btn is-outline-green"><i class="bi bi-check-lg"></i> Approve Agent</button>
                            </form>
                        @elseif($agent->approval_status === 'active')
                            <button type="button" class="tbp-agent-btn is-outline-red" data-bs-toggle="modal" data-bs-target="#suspendModal"><i class="bi bi-pause-fill"></i> Suspend Agent</button>
                        @elseif($agent->approval_status === 'suspended')
                            <form method="POST" action="{{ route('admin.agents.activate', $agent) }}">
                                @csrf
                                <button type="submit" class="tbp-agent-btn is-outline-green"><i class="bi bi-play-fill"></i> Activate Agent</button>
                            </form>
                        @endif
                        <a href="{{ route('admin.agents.wallet', $agent) }}" class="tbp-agent-btn"><i class="bi bi-wallet2"></i> View Wallet</a>
                        <a href="#bookings" class="tbp-agent-btn" data-open-tab="bookings"><i class="bi bi-calendar3"></i> View Bookings</a>
                    </section>

                    <section class="tbp-agent-card tbp-agent-booking-statistics">
                        <h2><i class="bi bi-bar-chart"></i> Booking Statistics</h2>
                        <ul>
                            @foreach($statRows as [$icon, $label, $key])
                                <li><span><i class="bi {{ $icon }}"></i> {{ $label }}</span><strong>{{ number_format($bookingStats[$key]) }}</strong></li>
                            @endforeach
                        </ul>
                        <div class="tbp-agent-stat-total"><span>Total Bookings</span><strong>{{ number_format($bookingStats['total']) }}</strong></div>
                    </section>
                </aside>
            </div>
        </div>

        {{-- ===== PERMISSIONS ===== --}}
        <div class="tab-pane fade" id="permissions" role="tabpanel" aria-labelledby="permissions-tab">
            @include('admin-nova.agents._tab_permissions')
        </div>

        {{-- ===== BOOKINGS ===== --}}
        <div class="tab-pane fade" id="bookings" role="tabpanel" aria-labelledby="bookings-tab">
            @php
                $bkFiltered = collect(request()->only(['bk_type', 'bk_status', 'bk_payment', 'bk_from', 'bk_to', 'bk_search']))->filter()->isNotEmpty();
                $bkTypeIcons = ['hotel' => 'bi-building', 'flight' => 'bi-airplane', 'tour' => 'bi-map', 'umrah' => 'bi-moon-stars'];
            @endphp
            <section class="tbp-agent-card tbp-agent-bookings-pane">
                <header class="tbp-agent-card-head">
                    <div>
                        <h2><i class="bi bi-calendar3"></i> Bookings</h2>
                        <p>{{ number_format($agentBookings->total()) }} {{ $bkFiltered ? 'matching' : '' }} {{ \Illuminate\Support\Str::plural('booking', $agentBookings->total()) }} made by this agent</p>
                    </div>
                    <a href="{{ request()->fullUrlWithQuery(['print' => 1, 'bk_page' => null]) }}" target="_blank" class="tbp-agent-btn {{ $agentBookings->total() ? '' : 'is-disabled' }}"><i class="bi bi-file-earmark-pdf"></i> Export PDF</a>
                </header>

                <form method="GET" action="{{ route('admin.agents.show', $agent) }}#bookings" class="tbp-agent-bookings-filters">
                    <input type="search" name="bk_search" value="{{ request('bk_search') }}" placeholder="Search reference, PNR or guest…" aria-label="Search bookings">
                    <select name="bk_type" aria-label="Service">
                        <option value="">All services</option>
                        @foreach(['hotel' => 'Hotels', 'flight' => 'Flights', 'tour' => 'Tours', 'umrah' => 'Umrah'] as $v => $l)
                            <option value="{{ $v }}" @selected(request('bk_type') === $v)>{{ $l }}</option>
                        @endforeach
                    </select>
                    <select name="bk_status" aria-label="Status">
                        <option value="">All statuses</option>
                        @foreach(['confirmed', 'pending', 'cancelled'] as $v)
                            <option value="{{ $v }}" @selected(request('bk_status') === $v)>{{ ucfirst($v) }}</option>
                        @endforeach
                    </select>
                    <select name="bk_payment" aria-label="Payment">
                        <option value="">All payments</option>
                        @foreach(['paid', 'unpaid', 'refunded'] as $v)
                            <option value="{{ $v }}" @selected(request('bk_payment') === $v)>{{ ucfirst($v) }}</option>
                        @endforeach
                    </select>
                    <input type="date" name="bk_from" value="{{ request('bk_from') }}" aria-label="From date">
                    <input type="date" name="bk_to" value="{{ request('bk_to') }}" aria-label="To date">
                    <button type="submit" class="tbp-agent-btn is-primary"><i class="bi bi-funnel"></i> Filter</button>
                    @if($bkFiltered)
                        <a href="{{ route('admin.agents.show', $agent) }}#bookings" class="tbp-agent-btn" title="Reset filters"><i class="bi bi-arrow-clockwise"></i></a>
                    @endif
                </form>

                @if($agentBookings->total())
                    <div class="tbp-agent-table-wrap">
                        <table class="tbp-agent-bookings-table">
                            <thead><tr><th>Reference</th><th>Service</th><th>Details</th><th>Customer</th><th>Amount</th><th>Status</th><th>Payment</th><th>Date</th><th></th></tr></thead>
                            <tbody>
                                @foreach($agentBookings as $b)
                                    <tr>
                                        <td><strong>{{ $b['ref'] ?: '#' . $b['id'] }}</strong>@if($b['pnr'])<small>PNR {{ $b['pnr'] }}</small>@endif</td>
                                        <td><span class="tbp-agent-service"><i class="bi {{ $bkTypeIcons[$b['type']] }}"></i> {{ ucfirst($b['type']) }}</span></td>
                                        <td class="is-details">{{ $b['title'] }}@if($b['subtitle'])<small>{{ $b['subtitle'] }}</small>@endif</td>
                                        <td>{{ $b['customer'] ?: '—' }}</td>
                                        <td><strong>{{ $b['amount'] }}</strong></td>
                                        <td><span class="tbp-agent-status is-{{ $b['status'] === 'confirmed' ? 'completed' : ($b['status'] === 'cancelled' ? 'rejected' : 'pending') }}">{{ ucfirst($b['status']) }}</span></td>
                                        <td><span class="tbp-agent-status is-{{ $b['payment'] === 'paid' ? 'completed' : ($b['payment'] === 'refunded' ? 'rejected' : 'pending') }}">{{ ucfirst($b['payment']) }}</span></td>
                                        <td>{{ $b['date']?->format('d M Y') }}</td>
                                        <td><a href="{{ route('admin.bookings.edit', [$b['type'], $b['id']]) }}" class="tbp-agent-icon-link" title="Open booking"><i class="bi bi-box-arrow-up-right"></i></a></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($agentBookings->hasPages())
                        <div class="tbp-agent-bookings-pages">{{ $agentBookings->links() }}</div>
                    @endif
                @else
                    <div class="tbp-agent-bookings-empty">
                        <i class="bi bi-calendar-x"></i>
                        <strong>No bookings</strong>
                        <span>{{ $bkFiltered ? 'No bookings match these filters.' : $agent->full_name . ' has not made any bookings yet.' }}</span>
                    </div>
                @endif
            </section>
        </div>
    </div>

    {{-- ===== SUSPEND MODAL ===== --}}
    <div class="modal fade" id="suspendModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content tbp-agent-modal">
                <form method="POST" action="{{ route('admin.agents.suspend', $agent) }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Suspend Agent</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <label class="form-label" for="suspendReason">Reason (optional)</label>
                        <textarea name="reason" id="suspendReason" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="tbp-agent-btn" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="tbp-agent-btn is-warning">Confirm Suspend</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    var tabs = document.querySelectorAll('#agPage .tbp-agent-tab[data-bs-toggle="tab"]');
    tabs.forEach(function (tab) {
        tab.addEventListener('shown.bs.tab', function (e) {
            tabs.forEach(function (t) { t.classList.remove('is-active'); });
            e.target.classList.add('is-active');
        });
    });
    function openTab(name) {
        var tab = document.querySelector('#agPage .tbp-agent-tab[href="#' + name + '"]');
        if (tab && window.bootstrap) bootstrap.Tab.getOrCreateInstance(tab).show();
    }
    document.querySelectorAll('#agPage [data-open-tab]').forEach(function (el) {
        el.addEventListener('click', function (e) { e.preventDefault(); openTab(el.dataset.openTab); });
    });
    // Wallet lives on its own page; older #wallet links go there.
    if (location.hash === '#wallet') location.replace(@json(route('admin.agents.wallet', $agent)));
    else if (location.hash) openTab(location.hash.slice(1));

    document.querySelectorAll('#agPage [data-copy]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (navigator.clipboard) navigator.clipboard.writeText(btn.dataset.copy);
            btn.innerHTML = '<i class="bi bi-check2"></i>';
            setTimeout(function () { btn.innerHTML = '<i class="bi bi-copy"></i>'; }, 1200);
        });
    });
})();
</script>
@endpush
@endsection
