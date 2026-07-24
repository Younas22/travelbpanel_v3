{{-- Disclosure: mirrors agent/tours/details.blade.php, which is dead/unreachable
     code — no route ever calls a controller method that returns this view.
     Kept for file-tree parity with the exhaustive file list this task specifies. --}}
@extends('agent-modern.layouts.app')
@section('title', $tour->name)

@section('content')
<div class="mb-3">
    <a href="{{ route('agent.tours.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Back to Tours
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            @if($tour->images->first())
            <img src="{{ asset('public/assets/images/' . $tour->images->first()->image_path) }}"
                 class="card-img-top ap-img-h-300" alt="{{ $tour->name }}">
            @endif
            <div class="card-body">
                <h4 class="fw-bold mb-1">{{ $tour->name }}</h4>
                <div class="d-flex gap-3 text-muted small mb-3">
                    <span><i class="bi bi-geo-alt"></i> {{ $tour->location_name ?? 'N/A' }}</span>
                    <span><i class="bi bi-clock"></i> {{ $tour->days ?? '?' }} Days / {{ $tour->duration ?? '?' }} Nights</span>
                    @if($tour->packageType)
                    <span><i class="bi bi-tag"></i> {{ $tour->packageType->packege_type }}</span>
                    @endif
                </div>

                @if($tour->description)
                <p class="text-muted">{{ $tour->description }}</p>
                @endif

                @if(!empty($tour->inclusion_names))
                <h6 class="fw-semibold mt-3">Inclusions</h6>
                <div class="d-flex flex-wrap gap-1 mb-3">
                    @foreach($tour->inclusion_names as $inc)
                    <span class="badge bg-success bg-opacity-10 text-success border border-success"><i class="bi bi-check2"></i> {{ $inc }}</span>
                    @endforeach
                </div>
                @endif

                @if(!empty($tour->exclusion_names))
                <h6 class="fw-semibold">Exclusions</h6>
                <div class="d-flex flex-wrap gap-1">
                    @foreach($tour->exclusion_names as $exc)
                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger"><i class="bi bi-x"></i> {{ $exc }}</span>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm sticky-top ap-sticky-80">
            <div class="card-header bg-white"><h6 class="mb-0">Book This Tour</h6></div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="text-muted small">Starting from</div>
                    <div class="fw-bold text-primary fs-4">{{ $tour->currency ?? activeCurrency()->currency_name }} {{ number_format($tour->price ?? 0, 0) }}</div>
                    <div class="text-muted small">per adult</div>
                </div>

                <div class="card bg-light border-0 mb-3 p-2">
                    <div class="text-muted small">Wallet Balance</div>
                    <div class="fw-bold text-success">{{ activeCurrency()->currency_name }} {{ number_format(auth()->user()->wallet?->balance ?? 0, 0) }}</div>
                </div>

                <a href="{{ route('agent.tours.booking', $tour->id) }}" class="btn btn-success w-100">
                    <i class="bi bi-calendar-check"></i> Book Now
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
