{{-- Warning for hotels without rooms yet (rooms_count comes from HotelController::index withCount). --}}
@if(!$hotel->rooms_count)
    <span class="tbp-hotels-norooms">
        <i class="bi bi-exclamation-triangle"></i> No rooms added to this hotel
        <a href="{{ $hotel->room_types_count ? route('admin.hotels.rooms.create', ['hotel_id' => $hotel->id]) : route('admin.hotels.room-types.create', ['hotel_id' => $hotel->id]) }}">{{ $hotel->room_types_count ? 'Add room' : 'Add room type' }}</a>
    </span>
@endif
