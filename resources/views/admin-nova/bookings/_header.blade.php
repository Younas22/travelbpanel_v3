{{-- Page title, summary cards and filter panel shared by every admin-nova/bookings/* page.
     Expects: $stats, $bookingType, $title, $subtitle, $formRoute (route name the filter form
     submits to), $fullFilters (true on All Bookings, which also supports status/agent/month),
     $agents (when $fullFilters). The active summary card is derived from the current route +
     query string. --}}
@php
    $onAll = request()->routeIs('admin.bookings.all');
    $activeView = match (true) {
        request()->routeIs('admin.bookings.pending-confirmations') => 'pending',
        request()->routeIs('admin.bookings.cancelled-refunds') => 'cancelled',
        $onAll && request('payment_state') === 'refunded' => 'refunded',
        $onAll && request('status') === 'confirmed' => 'confirmed',
        $onAll && request('status') === 'pending' => 'pending',
        $onAll && request('status') === 'cancelled' => 'cancelled',
        default => 'all',
    };
    $typeQuery = ($bookingType ?? 'all') !== 'all' ? ['type' => $bookingType] : [];
    $cards = [
        'all'       => ['All Bookings', 'bi-journal-text', $stats['total'] ?? 0, route('admin.bookings.all', $typeQuery)],
        'confirmed' => ['Confirmed', 'bi-check-circle', $stats['confirmed'] ?? 0, route('admin.bookings.all', $typeQuery + ['status' => 'confirmed'])],
        'pending'   => ['Pending Confirmations', 'bi-hourglass-split', $stats['pending'] ?? 0, route('admin.bookings.pending-confirmations', $typeQuery)],
        'cancelled' => ['Cancelled', 'bi-x-octagon', $stats['cancelled'] ?? 0, route('admin.bookings.all', $typeQuery + ['status' => 'cancelled'])],
        'refunded'  => ['Refunded', 'bi-arrow-counterclockwise', $stats['refunded'] ?? 0, route('admin.bookings.all', $typeQuery + ['payment_state' => 'refunded'])],
    ];
    $fullFilters = $fullFilters ?? false;
@endphp

<div class="tbp-bookings-top">
    <div class="tbp-bookings-title">
        <h1>{{ $title }}</h1>
        <p>{{ $subtitle }}</p>
    </div>
    <nav class="tbp-bookings-summary" aria-label="Booking views">
        @foreach($cards as $key => [$label, $icon, $count, $url])
            <a href="{{ $url }}" class="tbp-bookings-summary-card is-{{ $key }} {{ $activeView === $key ? 'is-active' : '' }}" @if($activeView === $key) aria-current="page" @endif>
                <i class="bi {{ $icon }}"></i>
                <strong>{{ number_format($count) }}</strong>
                <span>{{ $label }}</span>
            </a>
        @endforeach
    </nav>
</div>

<form method="GET" action="{{ route($formRoute) }}" class="tbp-bookings-filters {{ $fullFilters ? 'is-full' : '' }}">
    @if(request('payment_state'))
        <input type="hidden" name="payment_state" value="{{ request('payment_state') }}">
    @endif
    <label class="tbp-bookings-field is-search"><span>Search bookings</span>
        <span class="tbp-bookings-control"><i class="bi bi-search"></i>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Booking ID, customer name, email, phone..."></span></label>
    <label class="tbp-bookings-field"><span>Type</span>
        <span class="tbp-bookings-control"><i class="bi bi-briefcase"></i>
            <select name="type">
                @foreach(['all' => 'All types', 'flight' => 'Flight', 'hotel' => 'Hotel', 'tour' => 'Tour', 'umrah' => 'Umrah'] as $v => $l)
                    <option value="{{ $v }}" @selected(($bookingType ?? 'all') === $v)>{{ $l }}</option>
                @endforeach
            </select></span></label>
    @if($fullFilters)
        <label class="tbp-bookings-field"><span>Status</span>
            <span class="tbp-bookings-control"><i class="bi bi-activity"></i>
                <select name="status">
                    <option value="">All status</option>
                    @foreach(['confirmed', 'pending', 'cancelled'] as $v)
                        <option value="{{ $v }}" @selected(request('status') === $v)>{{ ucfirst($v) }}</option>
                    @endforeach
                </select></span></label>
        <label class="tbp-bookings-field"><span>Agent</span>
            <span class="tbp-bookings-control"><i class="bi bi-person"></i>
                <select name="agent_id">
                    <option value="">All agents</option>
                    @foreach($agents as $agentOption)
                        <option value="{{ $agentOption->id }}" @selected((string) request('agent_id') === (string) $agentOption->id)>{{ trim($agentOption->first_name . ' ' . $agentOption->last_name) }}{{ $agentOption->company_name ? ' — ' . $agentOption->company_name : '' }}</option>
                    @endforeach
                </select></span></label>
        <label class="tbp-bookings-field"><span>Month</span>
            <input type="month" name="month" value="{{ request('month') }}"></label>
    @endif
    <label class="tbp-bookings-field"><span>Date from</span>
        <input type="date" name="date_from" value="{{ request('date_from') }}"></label>
    <label class="tbp-bookings-field"><span>Date to</span>
        <input type="date" name="date_to" value="{{ request('date_to') }}"></label>
    <div class="tbp-bookings-filter-actions">
        <button type="submit" class="tbp-bookings-btn is-primary"><i class="bi bi-funnel"></i> Filter</button>
        <a href="{{ route($formRoute) }}" class="tbp-bookings-btn is-icon" title="Reset filters"><i class="bi bi-arrow-counterclockwise"></i></a>
    </div>
</form>
