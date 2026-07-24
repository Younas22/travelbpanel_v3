@extends('agent.layouts.app')
@section('title', 'Hotel Search Results')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-1"><i class="bi bi-building"></i> Hotel Results</h4>
        @if(session('hotel_search'))
        <small class="text-muted">
            {{ session('hotel_search.city') }} &bull;
            {{ session('hotel_search.checkin') }} → {{ session('hotel_search.checkout') }} &bull;
            {{ session('hotel_search.adults') }} adults
        </small>
        @endif
    </div>
    <a href="{{ route('agent.hotels.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-search"></i> New Search
    </a>
</div>

@if(isset($error))
    <div class="alert alert-danger">{{ $error }}</div>
@endif

@if(empty($hotels))
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="bi bi-building text-muted ap-icon-fs-lg"></i>
            <p class="text-muted mt-3">No hotels found. Try different dates or destination.</p>
            <a href="{{ route('agent.hotels.index') }}" class="btn btn-primary">Search Again</a>
        </div>
    </div>
@else
    <p class="text-muted mb-3">{{ count($hotels) }} hotels found</p>
    <div class="row g-3">
        @foreach($hotels as $hotel)
        <div class="col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                @if(!empty($hotel['images']))
                <img src="{{ is_array($hotel['images']) ? ($hotel['images'][0] ?? 'https://placehold.co/400x200') : $hotel['images'] }}"
                     class="card-img-top" alt="{{ $hotel['name'] }} ap-img-h-180">
                @else
                <div class="bg-light d-flex align-items-center justify-content-center ap-h-180">
                    <i class="bi bi-building text-muted fs-1"></i>
                </div>
                @endif
                <div class="card-body d-flex flex-column">
                    <h6 class="card-title fw-bold">{{ $hotel['name'] }}</h6>
                    <p class="text-muted small mb-1"><i class="bi bi-geo-alt"></i> {{ $hotel['location'] ?? $hotel['address'] ?? '' }}</p>
                    @if(!empty($hotel['stars']))
                    <div class="mb-2">
                        @for($s=1; $s<=$hotel['stars']; $s++)<i class="bi bi-star-fill text-warning ap-icon-fs-sm"></i>@endfor
                    </div>
                    @endif
                    <div class="mt-auto d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small">From</span>
                            <div class="fw-bold text-primary">{{ $hotel['currency'] ?? '' }} {{ number_format($hotel['minRate'] ?? 0, 0) }}</div>
                        </div>
                        <a href="{{ route('agent.hotels.details', [$hotel['hotel_id'], \Str::slug($hotel['name'])]) }}?supplier={{ $hotel['supplier_name'] ?? 'manual' }}"
                           class="btn btn-primary btn-sm">View Rooms</a>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
@endif
@endsection
