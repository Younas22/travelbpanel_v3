@extends('agent.layouts.app')
@section('title', 'Flight Results')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-1"><i class="bi bi-airplane"></i> Flight Results</h4>
        @if(isset($searchParams))
        <small class="text-muted">
            {{ $searchParams['origin'] ?? '' }} → {{ $searchParams['destination'] ?? '' }} &bull;
            {{ $searchParams['departureDate'] ?? '' }} &bull;
            {{ $searchParams['adult'] ?? 1 }} Adults
        </small>
        @endif
    </div>
    <a href="{{ route('agent.flights.index') }}" class="ap-btn-outline">
        <i class="bi bi-search"></i> New Search
    </a>
</div>

@if(isset($error))
    <div class="alert alert-danger">{{ $error }}</div>
@endif

@if(empty($flights))
    <div class="ap-card">
        <div class="ap-card-body text-center py-5">
            <i class="bi bi-airplane text-muted ap-icon-fs-lg"></i>
            <p class="text-muted mt-3">No flights found. Try different dates or routes.</p>
            <a href="{{ route('agent.flights.index') }}" class="ap-btn-primary">Search Again</a>
        </div>
    </div>
@else
    <p class="text-muted mb-3">{{ count($flights) }} flights found</p>
    @foreach($flights as $flight)
    <div class="ap-card mb-3">
        <div class="ap-card-body">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 items-center">
                <div>
                    <div class="flex items-center gap-3">
                        <div class="text-center">
                            <div class="fw-bold fs-5">{{ $flight['departure_time'] ?? '--:--' }}</div>
                            <div class="text-muted small">{{ $flight['origin'] ?? ($searchParams['origin'] ?? '') }}</div>
                        </div>
                        <div class="text-center flex-grow-1">
                            <div class="text-muted small">{{ $flight['duration'] ?? '' }}</div>
                            <div class="border-top mx-2"></div>
                            <div class="text-muted small">{{ $flight['stops'] ?? 'Direct' }}</div>
                        </div>
                        <div class="text-center">
                            <div class="fw-bold fs-5">{{ $flight['arrival_time'] ?? '--:--' }}</div>
                            <div class="text-muted small">{{ $flight['destination'] ?? ($searchParams['destination'] ?? '') }}</div>
                        </div>
                    </div>
                </div>
                <div class="text-center">
                    <div class="text-muted small">{{ $flight['airline'] ?? 'Airline' }}</div>
                    <div class="text-muted small">{{ $flight['flight_number'] ?? '' }} &bull; {{ $flight['class'] ?? ($searchParams['flightType'] ?? 'Economy') }}</div>
                </div>
                <div class="text-center md:text-right">
                    <div class="fw-bold text-primary fs-5">
                        {{ $flight['currency'] ?? activeCurrency()->currency_name }} {{ number_format($flight['price'] ?? 0, 0) }}
                    </div>
                    <div class="text-muted small">per person</div>
                    <form method="POST" action="{{ route('agent.flights.booking') }}" class="inline">
                        @csrf
                        <input type="hidden" name="flight_data" value="{{ encrypt(json_encode($flight)) }}">
                        <button type="submit" class="ap-btn-primary mt-1">
                            <i class="bi bi-calendar-check"></i> Book Now
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endforeach
@endif
@endsection
