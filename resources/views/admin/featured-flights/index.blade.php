@extends('admin.layouts.app')

@section('title', 'Featured Flights')

@section('content')
@php
    $cards = [
        ['Total Deals', 'bi-airplane', $stats['total']],
        ['Active', 'bi-check-circle', $stats['active']],
        ['Inactive', 'bi-x-circle', $stats['inactive']],
    ];
    $filtered = $search !== '' || in_array($status, ['0', '1'], true);
@endphp
<div class="tbp-units-page">
    <header class="tbp-units-header">
        <div><h1>Featured Flights</h1><p>Deals shown in "Featured Flight Deals" on the home and flights pages</p></div>
        <a href="{{ route('admin.featured-flights.create') }}" class="tbp-units-btn is-primary"><i class="bi bi-plus-circle"></i> Add Featured Flight</a>
    </header>

    @unless($enabled)
        <div class="alert alert-warning d-flex align-items-center gap-2">
            <i class="bi bi-eye-slash"></i>
            <span>Hidden on the website: the <strong>Flight</strong> module or the <strong>Featured Flights</strong> supplier is switched off.
                <a href="{{ route('admin.travel-partners.index') }}">Turn it on in Suppliers</a>.</span>
        </div>
    @endunless

    <div class="tbp-units-stats is-3">
        @foreach($cards as [$label, $icon, $value])
            <div class="tbp-units-stat"><span class="tbp-units-stat-icon"><i class="bi {{ $icon }}"></i></span><div><small>{{ $label }}</small><strong>{{ number_format($value) }}</strong></div></div>
        @endforeach
    </div>

    <form action="{{ route('admin.featured-flights.index') }}" method="GET" class="tbp-units-toolbar is-rt">
        <label class="tbp-units-input"><i class="bi bi-search"></i>
            <input type="text" name="search" placeholder="Search route, city or airline..." value="{{ $search }}" aria-label="Search featured flights"></label>
        <label class="tbp-units-input"><i class="bi bi-tag"></i>
            <select name="status" aria-label="Status">
                <option value="">All Status</option>
                <option value="1" @selected($status === '1')>Active</option>
                <option value="0" @selected($status === '0')>Inactive</option>
            </select></label>
        <span></span>
        <button type="submit" class="tbp-units-btn is-primary"><i class="bi bi-funnel"></i> Filter</button>
        <a href="{{ route('admin.featured-flights.index') }}" class="tbp-units-btn is-icon" title="Reset filters" aria-label="Reset filters"><i class="bi bi-arrow-clockwise"></i></a>
    </form>

    <section class="tbp-units-card">
        <div class="tbp-units-scroll">
            <table class="tbp-units-table">
                <thead><tr><th>Order</th><th>Route</th><th>Airline</th><th>Cabin</th><th>Departure</th><th>Price From</th><th>Status</th><th class="is-actions">Actions</th></tr></thead>
                <tbody>
                @forelse($flights as $flight)
                    <tr>
                        <td>{{ $flight->sort_order }}</td>
                        <td>
                            <span class="tbp-units-strong">{{ $flight->from_code }} <i class="bi bi-arrow-right"></i> {{ $flight->to_code }}</span>
                            <span class="tbp-units-meta">{{ $flight->from_city }} – {{ $flight->to_city }}</span>
                        </td>
                        <td>
                            <span class="tbp-units-meta">
                                <img src="https://assets.duffel.com/img/airlines/for-light-background/full-color-logo/{{ $flight->airline_code }}.svg" alt="" width="20" height="20" loading="lazy" onerror="this.remove()">
                                {{ $flight->airline }}
                            </span>
                            <span class="tbp-units-chip">{{ $flight->airline_code }}</span>
                        </td>
                        <td>{{ \App\Models\FeaturedFlight::CABINS[$flight->cabin] ?? ucfirst($flight->cabin) }}</td>
                        <td>{{ $flight->days_ahead == 0 ? 'Today' : '+' . $flight->days_ahead . ' ' . Str::plural('day', $flight->days_ahead) }}</td>
                        <td class="tbp-units-price">USD {{ number_format($flight->price, 2) }}</td>
                        <td>
                            <div class="tbp-units-status">
                                <button type="button" class="tbp-units-switch js-ff-toggle" role="switch" aria-checked="{{ $flight->status ? 'true' : 'false' }}"
                                        aria-label="Active: {{ $flight->from_code }} to {{ $flight->to_code }}" data-url="{{ route('admin.featured-flights.toggle-status', $flight) }}"><i></i></button>
                                <span class="tbp-units-badge {{ $flight->status ? 'is-green' : 'is-red' }}">{{ $flight->status ? 'Active' : 'Inactive' }}</span>
                            </div>
                        </td>
                        <td class="is-actions">
                            <div class="tbp-units-actions">
                                <a href="{{ route('admin.featured-flights.edit', $flight) }}" class="tbp-units-icon-btn" title="Edit" aria-label="Edit {{ $flight->from_code }} to {{ $flight->to_code }}"><i class="bi bi-pencil"></i></a>
                                <form method="POST" action="{{ route('admin.featured-flights.destroy', $flight) }}" class="js-units-delete"
                                      data-confirm="Delete the featured flight {{ $flight->from_code }} → {{ $flight->to_code }} ({{ $flight->airline }})? This action cannot be undone.">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="tbp-units-icon-btn" title="Delete" aria-label="Delete {{ $flight->from_code }} to {{ $flight->to_code }}"><i class="bi bi-trash3"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="tbp-units-empty"><i class="bi bi-inbox"></i><strong>No featured flights found</strong>
                        <span>{{ $filtered ? 'No featured flights match your search or filters.' : 'Add your first featured flight to show it on the website.' }}</span></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @include('admin-nova.partials._tbp-pagination', ['paginator' => $flights])
    </section>
</div>
@include('admin.hotels.rooms._units-scripts')

@push('scripts')
<script>
document.querySelectorAll('.js-ff-toggle').forEach(function (btn) {
    btn.addEventListener('click', function () {
        if (btn.disabled) return;
        btn.disabled = true;
        fetch(btn.dataset.url, { method: 'PATCH', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.success) throw new Error(data.message || 'Failed');
                var on = !!data.status;
                btn.setAttribute('aria-checked', on ? 'true' : 'false');
                var badge = btn.nextElementSibling;
                badge.textContent = on ? 'Active' : 'Inactive';
                badge.className = 'tbp-units-badge ' + (on ? 'is-green' : 'is-red');
                tbpUnitsToast(data.message);
            })
            .catch(function () { tbpUnitsToast('Could not update the status.', true); })
            .finally(function () { btn.disabled = false; });
    });
});
</script>
@endpush
@endsection
