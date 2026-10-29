@extends('admin-nova.layouts.app')

@section('title', 'Customers')

@section('content')
@php
    $total = max($stats['total_customers'], 0);
    $pct = fn ($n) => $total ? round($n / $total * 100, 1) : 0;
    $statCards = [
        ['Total Customers', 'bi-people', 'is-blue', number_format($stats['total_customers']), ($stats['new_this_month'] ? '+' . number_format($stats['new_this_month']) : '0') . ' new this month', $stats['new_this_month'] ? 'is-up' : ''],
        ['Active Customers', 'bi-person-check', 'is-green', number_format($stats['active_customers']), $pct($stats['active_customers']) . '% active rate', 'is-up'],
        ['VIP Customers', 'bi-star', 'is-amber', number_format($stats['vip_customers']), $pct($stats['vip_customers']) . '% of total (Platinum tier)', ''],
        ['Avg. Customer Value', 'bi-currency-dollar', 'is-violet', '$' . number_format($stats['avg_customer_value'], 2), 'Average spend per customer', ''],
    ];
    $statusClass = ['active' => 'is-active', 'vip' => 'is-vip', 'inactive' => 'is-inactive', 'suspended' => 'is-suspended'];
@endphp
<div class="tbp-customers-page">

    <div class="tbp-customers-header">
        <h1>Customer Management</h1>
        <p>Manage customer accounts and analyze user behavior</p>
    </div>

    <div class="tbp-customers-stats">
        @foreach($statCards as [$label, $icon, $tone, $value, $note, $trend])
            <div class="tbp-customers-stat">
                <span class="tbp-customers-stat-icon {{ $tone }}"><i class="bi {{ $icon }}"></i></span>
                <div>
                    <small>{{ $label }}</small>
                    <strong>{{ $value }}</strong>
                    <em class="{{ $trend }}">@if($trend)<i class="bi bi-arrow-up"></i>@endif {{ $note }}</em>
                </div>
            </div>
        @endforeach
    </div>

    <form method="GET" action="{{ route('admin.customers.index') }}" class="tbp-customers-filters">
        <label class="tbp-customers-field is-search"><span>Search Customers</span>
            <span class="tbp-customers-control"><i class="bi bi-search"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, email, phone..."></span></label>
        <label class="tbp-customers-field"><span>Status</span>
            <select name="status">
                <option value="">All Status</option>
                @foreach(['active' => 'Active', 'inactive' => 'Inactive', 'suspended' => 'Suspended', 'vip' => 'VIP'] as $v => $l)
                    <option value="{{ $v }}" @selected(request('status') === $v)>{{ $l }}</option>
                @endforeach
            </select></label>
        <label class="tbp-customers-field"><span>Registration Date</span>
            <span class="tbp-customers-control"><i class="bi bi-calendar3"></i>
                <select name="date_filter">
                    @foreach(['' => 'All Time', 'today' => 'Today', 'week' => 'This Week', 'month' => 'This Month', 'year' => 'This Year'] as $v => $l)
                        <option value="{{ $v }}" @selected((string) request('date_filter') === $v)>{{ $l }}</option>
                    @endforeach
                </select></span></label>
        <div class="tbp-customers-filter-actions">
            <button type="submit" class="tbp-customers-btn is-primary"><i class="bi bi-funnel"></i> Filter</button>
            <a href="{{ route('admin.customers.index') }}" class="tbp-customers-btn is-soft"><i class="bi bi-arrow-clockwise"></i> Reset</a>
            <button type="button" class="tbp-customers-btn is-green" id="tbpBulkEmail" title="Select customers in the list first"><i class="bi bi-envelope"></i> Bulk Email <span data-bulk-count></span></button>
        </div>
    </form>

    <section class="tbp-customers-card">
        <header class="tbp-customers-card-head">
            <h2>Customer List ({{ number_format($customers->total()) }})</h2>
            <p>View and manage all registered customers</p>
        </header>

        <div class="tbp-customers-table-wrap">
            <table class="tbp-customers-table">
                <thead>
                    <tr>
                        <th class="is-check"><input type="checkbox" id="tbpCustomerSelectAll" aria-label="Select all customers"></th>
                        <th>Customer</th>
                        <th>Registration</th>
                        <th>Bookings</th>
                        <th>Total Spent</th>
                        <th>Last Activity</th>
                        <th>Status</th>
                        <th class="is-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($customers as $customer)
                    <tr>
                        <td class="is-check"><input type="checkbox" class="tbp-customer-check" value="{{ $customer->email }}" aria-label="Select {{ $customer->full_name }}"></td>
                        <td data-label="Customer">
                            <div class="tbp-customers-person">
                                <span class="tbp-customers-avatar is-tone-{{ crc32($customer->email) % 6 }}">{{ $customer->initials }}</span>
                                <div>
                                    <strong>{{ $customer->full_name }}</strong>
                                    <small>{{ $customer->email }}</small>
                                    @if($customer->phone)<small>{{ $customer->phone }}</small>@endif
                                </div>
                            </div>
                        </td>
                        <td data-label="Registration"><strong>{{ $customer->created_at->format('M d, Y') }}</strong><small>{{ $customer->created_at->diffForHumans() }}</small></td>
                        <td data-label="Bookings"><strong>{{ number_format($customer->total_bookings ?? 0) }}</strong><small>{{ \Illuminate\Support\Str::plural('booking', (int) ($customer->total_bookings ?? 0)) }}</small></td>
                        <td data-label="Total Spent">
                            <strong class="is-money">${{ number_format($customer->total_spent ?? 0, 2) }}</strong>
                            @if(($customer->total_bookings ?? 0) > 0)<small>Avg ${{ number_format($customer->average_spending ?? 0, 2) }}</small>@endif
                        </td>
                        <td data-label="Last Activity"><strong>{{ $customer->last_activity?->format('M d, Y') ?? 'Never' }}</strong><small>{{ $customer->last_activity?->diffForHumans() ?? 'No activity' }}</small></td>
                        <td data-label="Status"><span class="tbp-customers-status {{ $statusClass[$customer->status] ?? '' }}">{{ ucfirst($customer->status ?? 'unknown') }}</span></td>
                        <td class="is-actions">
                            <div class="tbp-customers-actions">
                                <a href="mailto:{{ $customer->email }}" title="Email {{ $customer->full_name }}"><i class="bi bi-envelope"></i></a>
                                <form method="POST" action="{{ route('admin.customers.destroy', $customer) }}" onsubmit="return confirm('Delete {{ addslashes($customer->full_name) }}? This cannot be undone.');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="is-danger" title="Delete customer"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="tbp-customers-empty"><i class="bi bi-people"></i><strong>No customers found</strong><span>No customers match your search or filters.</span></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <footer class="tbp-customers-footer">
            <span>@if($customers->total()) Showing {{ $customers->firstItem() }} to {{ $customers->lastItem() }} of {{ number_format($customers->total()) }} entries @else No entries @endif</span>
            @php $p = $customers->withQueryString(); @endphp
            <nav class="tbp-customers-pages">
                <a class="{{ $p->onFirstPage() ? 'is-disabled' : '' }}" href="{{ $p->previousPageUrl() ?? '#' }}" aria-label="Previous"><i class="bi bi-chevron-left"></i></a>
                @foreach($p->getUrlRange(max(1, $p->currentPage() - 2), min($p->lastPage(), $p->currentPage() + 2)) as $num => $url)
                    <a class="{{ $num === $p->currentPage() ? 'is-active' : '' }}" href="{{ $url }}">{{ $num }}</a>
                @endforeach
                <a class="{{ $p->hasMorePages() ? '' : 'is-disabled' }}" href="{{ $p->nextPageUrl() ?? '#' }}" aria-label="Next"><i class="bi bi-chevron-right"></i></a>
            </nav>
        </footer>
    </section>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var all = document.getElementById('tbpCustomerSelectAll');
    var boxes = document.querySelectorAll('.tbp-customer-check');
    var bulk = document.getElementById('tbpBulkEmail');
    function selected() { return Array.prototype.filter.call(boxes, function (b) { return b.checked; }).map(function (b) { return b.value; }); }
    function sync() {
        var n = selected().length;
        bulk.querySelector('[data-bulk-count]').textContent = n ? '(' + n + ')' : '';
        if (all) { all.checked = n && n === boxes.length; all.indeterminate = n > 0 && n < boxes.length; }
    }
    boxes.forEach(function (b) { b.addEventListener('change', sync); });
    if (all) {
        all.disabled = !boxes.length;
        all.addEventListener('change', function () { boxes.forEach(function (b) { b.checked = all.checked; }); sync(); });
    }
    // Opens the admin's mail app with the selected customers in BCC.
    bulk.addEventListener('click', function () {
        var emails = selected();
        if (!emails.length) { alert('Select at least one customer in the list first.'); return; }
        window.location.href = 'mailto:?bcc=' + encodeURIComponent(emails.join(','));
    });
})();
</script>
@endpush
