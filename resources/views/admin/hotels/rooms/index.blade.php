@extends('admin.layouts.app')

@section('title', 'Rooms')

@section('content')
@php
    $cards = [
        ['Total Rooms', 'bi-key', $stats['total']],
        ['Available', 'bi-check-circle', $stats['available']],
        ['Occupied', 'bi-person', $stats['occupied']],
        ['Maintenance', 'bi-tools', $stats['maintenance']],
    ];
    $statuses = ['available' => ['Available', 'is-green'], 'occupied' => ['Occupied', 'is-amber'], 'maintenance' => ['Maintenance', 'is-red']];
    $filtered = filled($search ?? null) || filled($hotelId ?? null) || filled($roomTypeId ?? null) || filled($status ?? null);
@endphp
<div class="tbp-units-page">
    @include('admin.hotels.room-types._tabs')
    <header class="tbp-units-header">
        <div><h1>Rooms</h1><p>Manage all rooms</p></div>
        <a href="{{ route('admin.hotels.rooms.create', array_filter(['hotel_id' => request('hotel_id'), 'room_type_id' => request('room_type_id')])) }}" class="tbp-units-btn is-primary"><i class="bi bi-plus-circle"></i> Add New Room</a>
    </header>

    <div class="tbp-units-stats is-4">
        @foreach($cards as [$label, $icon, $value])
            <div class="tbp-units-stat"><span class="tbp-units-stat-icon"><i class="bi {{ $icon }}"></i></span><div><small>{{ $label }}</small><strong>{{ number_format($value) }}</strong></div></div>
        @endforeach
    </div>

    <form action="{{ route('admin.hotels.rooms.index') }}" method="GET" class="tbp-units-toolbar is-rooms">
        <label class="tbp-units-input"><i class="bi bi-search"></i>
            <input type="text" name="search" placeholder="Search room number or name..." value="{{ $search ?? '' }}" aria-label="Search room number"></label>
        <label class="tbp-units-input"><i class="bi bi-building"></i>
            <select name="hotel_id" id="tbpRoomsHotel" aria-label="Hotel">
                <option value="">All Hotels</option>
                @foreach($hotels as $hotel)
                    <option value="{{ $hotel->id }}" @selected(($hotelId ?? '') == $hotel->id)>{{ $hotel->name }}</option>
                @endforeach
            </select></label>
        <label class="tbp-units-input"><i class="bi bi-tag"></i>
            <select name="room_type_id" id="tbpRoomsType" aria-label="Room type">
                <option value="">All Room Types</option>
                @foreach($roomTypes as $type)
                    <option value="{{ $type->id }}" data-hotel="{{ $type->hotel_id }}" @selected(($roomTypeId ?? '') == $type->id)>{{ $type->name }}</option>
                @endforeach
            </select></label>
        <label class="tbp-units-input"><i class="bi bi-circle"></i>
            <select name="status" aria-label="Status">
                <option value="">All Status</option>
                @foreach($statuses as $v => [$l])
                    <option value="{{ $v }}" @selected(($status ?? '') == $v)>{{ $l }}</option>
                @endforeach
            </select></label>
        <button type="submit" class="tbp-units-btn is-primary"><i class="bi bi-funnel"></i> Filter</button>
        <a href="{{ route('admin.hotels.rooms.index') }}" class="tbp-units-btn is-icon" title="Reset filters" aria-label="Reset filters"><i class="bi bi-arrow-clockwise"></i></a>
    </form>

    <section class="tbp-units-card">
        <div class="tbp-units-scroll">
            <table class="tbp-units-table">
                <thead><tr><th>Room Number</th><th>Room Name</th><th>Hotel</th><th>Room Type</th><th>Floor</th><th>Status</th><th class="is-actions">Actions</th></tr></thead>
                <tbody>
                @forelse($rooms as $room)
                    @php [$sLabel, $sTone] = $statuses[$room->status] ?? [ucfirst($room->status), '']; @endphp
                    <tr>
                        <td class="tbp-units-strong">{{ $room->room_number }}</td>
                        <td>{{ filled($room->name) ? $room->name : '—' }}</td>
                        <td>{{ optional($room->hotel)->name ?? '—' }}</td>
                        <td>@if($room->roomType)<span class="tbp-units-chip is-blue">{{ $room->roomType->name }}</span>@else — @endif</td>
                        <td>{{ filled($room->floor) ? $room->floor : '—' }}</td>
                        <td><span class="tbp-units-badge has-dot {{ $sTone }}">{{ $sLabel }}</span></td>
                        <td class="is-actions">
                            <div class="tbp-units-actions">
                                <a href="{{ route('admin.hotels.rooms.edit', $room) }}" class="tbp-units-icon-btn" title="Edit" aria-label="Edit room {{ $room->room_number }}"><i class="bi bi-pencil"></i></a>
                                <form method="POST" action="{{ route('admin.hotels.rooms.destroy', $room) }}" class="js-units-delete"
                                      data-confirm="Delete room {{ $room->room_number }}{{ $room->hotel ? ' at ' . $room->hotel->name : '' }}? This action cannot be undone.">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="tbp-units-icon-btn" title="Delete" aria-label="Delete room {{ $room->room_number }}"><i class="bi bi-trash3"></i></button>
                                </form>
                                <details class="tbp-units-menu">
                                    <summary class="tbp-units-icon-btn" title="More actions" aria-label="More actions"><i class="bi bi-three-dots-vertical"></i></summary>
                                    <div>
                                        <span class="tbp-units-menu-label">Set status</span>
                                        @foreach($statuses as $v => [$l])
                                            <button type="button" class="js-room-status" data-url="{{ route('admin.hotels.rooms.update-status', $room) }}" data-status="{{ $v }}" @disabled($room->status === $v)><i class="bi bi-circle"></i> {{ $l }}</button>
                                        @endforeach
                                    </div>
                                </details>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="tbp-units-empty"><i class="bi bi-inbox"></i><strong>No rooms found</strong>
                        <span>{{ $filtered ? 'No rooms match your search or filters.' : 'Add your first room to get started.' }}</span></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @include('admin-nova.partials._tbp-pagination', ['paginator' => $rooms])
    </section>
</div>
@include('admin.hotels.rooms._units-scripts')

@push('scripts')
<script>
(function () {
    // Room types follow the selected hotel
    var hotel = document.getElementById('tbpRoomsHotel'), type = document.getElementById('tbpRoomsType');
    function sync() {
        Array.prototype.forEach.call(type.options, function (o) {
            if (!o.value) return;
            o.hidden = o.disabled = !!hotel.value && o.dataset.hotel !== hotel.value;
        });
        if (type.selectedOptions[0] && type.selectedOptions[0].disabled) type.value = '';
    }
    hotel.addEventListener('change', sync);
    sync();

    // Quick status change from the "more actions" menu
    var tones = { available: ['Available', 'is-green'], occupied: ['Occupied', 'is-amber'], maintenance: ['Maintenance', 'is-red'] };
    document.querySelectorAll('.js-room-status').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var row = btn.closest('tr'), menu = btn.closest('details');
            fetch(btn.dataset.url, {
                method: 'PATCH',
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ status: btn.dataset.status })
            })
                .then(function (r) { if (!r.ok) throw new Error(); return r.json(); })
                .then(function (data) {
                    var t = tones[data.status], badge = row.querySelector('.tbp-units-badge');
                    badge.textContent = t[0]; badge.className = 'tbp-units-badge has-dot ' + t[1];
                    menu.querySelectorAll('.js-room-status').forEach(function (b) { b.disabled = b.dataset.status === data.status; });
                    menu.open = false;
                    tbpUnitsToast(data.message);
                })
                .catch(function () { tbpUnitsToast('Could not update the room status.', true); });
        });
    });
})();
</script>
@endpush
@endsection
