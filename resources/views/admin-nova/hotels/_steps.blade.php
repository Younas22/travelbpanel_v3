{{-- Hotel Info / Room Types steps (rooms and rates are tabs on the Room Types page). Expects $hotel (null on create). --}}
<nav class="tbp-hotel-steps">
    <span class="tbp-hotel-step is-active"><b>1</b><i class="bi bi-building"></i> Hotel Info</span>
    @if($hotel)
        <a class="tbp-hotel-step" href="{{ route('admin.hotels.room-types.index', ['hotel_id' => $hotel->id]) }}"><b>2</b><i class="bi bi-file-earmark-text"></i> Room Types <em>{{ $hotel->roomTypes()->count() }}</em></a>
    @else
        <span class="tbp-hotel-step is-locked" title="Save the hotel first"><b>2</b><i class="bi bi-file-earmark-text"></i> Room Types</span>
        <small class="tbp-hotel-steps-note"><i class="bi bi-info-circle"></i> Create the hotel first, then add its room types, rooms and rates.</small>
    @endif
</nav>
