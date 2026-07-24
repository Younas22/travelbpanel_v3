@extends('agent.layouts.app')
@section('title', 'Hotel Details')

@section('content')
<div class="mb-3">
    <a href="{{ route('agent.hotels.search') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Back to Results
    </a>
</div>

@if(isset($error))
    <div class="alert alert-danger">{{ $error }}</div>
@elseif(!empty($details))
    @php $hotel = $details[0]; @endphp

    {{-- Hotel Header --}}
    <div class="card border-0 shadow-sm mb-4">
        @if(!empty($hotel['imgs']))
        <div class="ap-hero-wrap-280">
            <img src="{{ is_array($hotel['imgs']) ? $hotel['imgs'][0] : $hotel['imgs'] }}"
                 class="w-100 h-100 ap-object-cover" alt="{{ $hotel['h_name'] }}">
        </div>
        @endif
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h4 class="fw-bold mb-1">{{ $hotel['h_name'] }}</h4>
                    <p class="text-muted mb-1"><i class="bi bi-geo-alt"></i> {{ $hotel['city'] }}, {{ $hotel['country'] }}</p>
                    @if(!empty($hotel['stars']))
                    <div>@for($s=1;$s<=$hotel['stars'];$s++)<i class="bi bi-star-fill text-warning"></i>@endfor</div>
                    @endif
                </div>
                <div class="text-end">
                    <div class="text-muted small">Check-in: <strong>{{ $hotel['checkin'] }}</strong></div>
                    <div class="text-muted small">Check-out: <strong>{{ $hotel['checkout'] }}</strong></div>
                </div>
            </div>
            @if(!empty($hotel['desc']))
            <p class="text-muted mt-3 small">{{ $hotel['desc'] }}</p>
            @endif
            @if(!empty($hotel['amenities']))
            <div class="d-flex flex-wrap gap-1 mt-2">
                @foreach(array_slice($hotel['amenities'], 0, 8) as $amenity)
                <span class="badge bg-light text-dark border"><i class="bi bi-check2"></i> {{ $amenity }}</span>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    {{-- Rooms --}}
    <h5 class="fw-bold mb-3">Available Rooms</h5>
    @forelse($hotel['rooms'] ?? [] as $room)
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-3">
                    @if(!empty($room['images'][0]))
                    <img src="{{ $room['images'][0] }}" class="img-fluid rounded ap-img-h-100" alt="{{ $room['name'] }}">
                    @else
                    <div class="bg-light rounded d-flex align-items-center justify-content-center ap-h-100"><i class="bi bi-door-open text-muted fs-2"></i></div>
                    @endif
                </div>
                <div class="col-md-6">
                    <h6 class="fw-bold mb-1">{{ $room['name'] }}</h6>
                    @if(!empty($room['amenities']))
                    <div class="d-flex flex-wrap gap-1 mb-2">
                        @foreach(array_slice($room['amenities'], 0, 5) as $a)
                        <span class="badge bg-light text-dark border small">{{ $a }}</span>
                        @endforeach
                    </div>
                    @endif
                    <span class="badge {{ $room['refundable'] ? 'bg-success' : 'bg-secondary' }}">
                        {{ $room['refundable'] ? 'Refundable' : 'Non-refundable' }}
                    </span>
                </div>
                <div class="col-md-3 text-end">
                    @foreach($room['options'] ?? [] as $option)
                    <div class="mb-2">
                        <div class="fw-bold text-primary fs-5">{{ $room['currency'] ?? '' }} {{ number_format($option['price'], 0) }}</div>
                        <div class="text-muted small">{{ $option['adults'] }} Adults, {{ $option['child'] }} Children</div>
                        <a href="{{ route('agent.hotels.booking', $room['id']) }}?supplier={{ request('supplier', 'manual') }}&room={{ encrypt(json_encode($room)) }}&option={{ encrypt(json_encode($option)) }}&booking_data={{ encrypt(json_encode($hotel_search ?? session('hotel_search') ?? [])) }}"
                           class="btn btn-primary btn-sm mt-1">
                            <i class="bi bi-calendar-check"></i> Book Now
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="alert alert-info">No rooms available for the selected dates.</div>
    @endforelse
@else
    <div class="alert alert-warning">Hotel details not available.</div>
@endif
@endsection
