{{-- Room Types / Rooms / Rate calendar share one page: these tabs switch between them, keeping the hotel filter. --}}
@php
    $keep = array_filter(['hotel_id' => request('hotel_id')]);
    $tabs = [
        ['admin.hotels.room-types.index', 'admin.hotels.room-types.*', 'bi-door-open', 'Room Types'],
        ['admin.hotels.rooms.index', 'admin.hotels.rooms.*', 'bi-key', 'Rooms'],
        ['admin.hotels.rate-calendar.index', 'admin.hotels.rate-calendar.*', 'bi-calendar3', 'Rate calendar'],
    ];
@endphp
<nav class="tbp-units-tabs" aria-label="Room sections">
    @foreach($tabs as [$route, $pattern, $icon, $label])
        @php $on = request()->routeIs($pattern); @endphp
        <a href="{{ route($route, $keep) }}" class="{{ $on ? 'is-active' : '' }}" @if($on) aria-current="page" @endif><i class="bi {{ $icon }}"></i> {{ $label }}</a>
    @endforeach
</nav>
