@extends('admin.layouts.app')

@section('title', 'Room Types')

@section('content')
@php
    $cards = [
        ['Total Room Types', 'bi-door-open', $stats['total']],
        ['Active', 'bi-check-circle', $stats['active']],
        ['Inactive', 'bi-x-circle', $stats['inactive']],
    ];
    $filtered = ($search ?? '') !== '' || ($hotelId ?? '') !== '' && $hotelId !== null || ($status ?? '') !== '' && $status !== null;
@endphp
<div class="tbp-units-page">
    @include('admin.hotels.room-types._tabs')
    <header class="tbp-units-header">
        <div><h1>Room Types</h1><p>Manage all room types</p></div>
        <a href="{{ route('admin.hotels.room-types.create', request('hotel_id') ? ['hotel_id' => request('hotel_id')] : []) }}" class="tbp-units-btn is-primary"><i class="bi bi-plus-circle"></i> Add New Room Type</a>
    </header>

    <div class="tbp-units-stats is-3">
        @foreach($cards as [$label, $icon, $value])
            <div class="tbp-units-stat"><span class="tbp-units-stat-icon"><i class="bi {{ $icon }}"></i></span><div><small>{{ $label }}</small><strong>{{ number_format($value) }}</strong></div></div>
        @endforeach
    </div>

    <form action="{{ route('admin.hotels.room-types.index') }}" method="GET" class="tbp-units-toolbar is-rt">
        <label class="tbp-units-input"><i class="bi bi-search"></i>
            <input type="text" name="search" placeholder="Search room type name..." value="{{ $search ?? '' }}" aria-label="Search room type name"></label>
        <label class="tbp-units-input"><i class="bi bi-building"></i>
            <select name="hotel_id" aria-label="Hotel">
                <option value="">All Hotels</option>
                @foreach($hotels as $hotel)
                    <option value="{{ $hotel->id }}" @selected(($hotelId ?? '') == $hotel->id)>{{ $hotel->name }}</option>
                @endforeach
            </select></label>
        <label class="tbp-units-input"><i class="bi bi-tag"></i>
            <select name="status" aria-label="Status">
                <option value="">All Status</option>
                <option value="1" @selected(($status ?? '') == '1')>Active</option>
                <option value="0" @selected(($status ?? '') === '0')>Inactive</option>
            </select></label>
        <button type="submit" class="tbp-units-btn is-primary"><i class="bi bi-funnel"></i> Filter</button>
        <a href="{{ route('admin.hotels.room-types.index') }}" class="tbp-units-btn is-icon" title="Reset filters" aria-label="Reset filters"><i class="bi bi-arrow-clockwise"></i></a>
    </form>

    <section class="tbp-units-card">
        <div class="tbp-units-scroll">
            <table class="tbp-units-table">
                <thead><tr><th>Image</th><th>Room Type</th><th>Hotel</th><th>Price / Night</th><th>Capacity</th><th>Beds</th><th>AC</th><th>Status</th><th class="is-actions">Actions</th></tr></thead>
                <tbody>
                @forelse($roomTypes as $roomType)
                    @php $img = $roomType->images->first(); @endphp
                    <tr>
                        <td>
                            <span class="tbp-units-thumb">@if($img)<img src="{{ asset('public/assets/images/' . $img->image_path) }}" alt="" loading="lazy">@else<i class="bi bi-door-open"></i>@endif</span>
                        </td>
                        <td class="tbp-units-strong">{{ $roomType->name }}</td>
                        <td>{{ optional($roomType->hotel)->name ?? '—' }}</td>
                        <td class="tbp-units-price">USD {{ number_format($roomType->price_per_night, 2) }}</td>
                        <td>
                            <span class="tbp-units-meta"><i class="bi bi-people"></i> {{ $roomType->max_adults }} {{ Str::plural('Adult', $roomType->max_adults) }}</span>
                            @if($roomType->max_children > 0)
                                <span class="tbp-units-meta"><i class="bi bi-person"></i> {{ $roomType->max_children }} {{ Str::plural('Child', $roomType->max_children) }}</span>
                            @endif
                        </td>
                        <td><span class="tbp-units-chip"><i class="bi bi-hospital"></i> {{ $roomType->beds }} Bed(s)</span></td>
                        <td>
                            @if($roomType->ac)
                                <span class="tbp-units-ac" title="AC available"><i class="bi bi-snow"></i><span class="visually-hidden">AC available</span></span>
                            @else
                                <span class="tbp-units-ac is-off" title="No AC"><i class="bi bi-dash-circle"></i><span class="visually-hidden">No AC</span></span>
                            @endif
                        </td>
                        <td>
                            <div class="tbp-units-status">
                                <button type="button" class="tbp-units-switch js-rt-toggle" role="switch" aria-checked="{{ $roomType->status ? 'true' : 'false' }}"
                                        aria-label="Active: {{ $roomType->name }}" data-url="{{ route('admin.hotels.room-types.toggle-status', $roomType) }}"><i></i></button>
                                <span class="tbp-units-badge {{ $roomType->status ? 'is-green' : 'is-red' }}">{{ $roomType->status ? 'Active' : 'Inactive' }}</span>
                            </div>
                        </td>
                        <td class="is-actions">
                            <div class="tbp-units-actions">
                                <a href="{{ route('admin.hotels.room-types.edit', $roomType) }}" class="tbp-units-icon-btn" title="Edit" aria-label="Edit {{ $roomType->name }}"><i class="bi bi-pencil"></i></a>
                                <form method="POST" action="{{ route('admin.hotels.room-types.destroy', $roomType) }}" class="js-units-delete"
                                      data-confirm="Delete room type &quot;{{ $roomType->name }}&quot;{{ $roomType->hotel ? ' at ' . $roomType->hotel->name : '' }}? Its images are deleted too. This action cannot be undone.">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="tbp-units-icon-btn" title="Delete" aria-label="Delete {{ $roomType->name }}"><i class="bi bi-trash3"></i></button>
                                </form>
                                <details class="tbp-units-menu">
                                    <summary class="tbp-units-icon-btn" title="More actions" aria-label="More actions"><i class="bi bi-three-dots-vertical"></i></summary>
                                    <div>
                                        <a href="{{ route('admin.hotels.rooms.index', ['hotel_id' => $roomType->hotel_id, 'room_type_id' => $roomType->id]) }}"><i class="bi bi-door-closed"></i> View rooms</a>
                                        <a href="{{ route('admin.hotels.rooms.create', ['hotel_id' => $roomType->hotel_id, 'room_type_id' => $roomType->id]) }}"><i class="bi bi-plus-circle"></i> Add room</a>
                                        <a href="{{ route('admin.hotels.rate-calendar.index', ['hotel_id' => $roomType->hotel_id, 'room_type_id' => $roomType->id]) }}"><i class="bi bi-calendar3"></i> Rate calendar</a>
                                    </div>
                                </details>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="tbp-units-empty"><i class="bi bi-inbox"></i><strong>No room types found</strong>
                        <span>{{ $filtered ? 'No room types match your search or filters.' : 'Add your first room type to get started.' }}</span></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @include('admin-nova.partials._tbp-pagination', ['paginator' => $roomTypes])
    </section>
</div>
@include('admin.hotels.rooms._units-scripts')

@push('scripts')
<script>
document.querySelectorAll('.js-rt-toggle').forEach(function (btn) {
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
