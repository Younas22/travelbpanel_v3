@extends('agent.layouts.app')
@section('title', $package->name)

@section('content')
<div class="mb-3">
    <a href="{{ route('agent.umrah.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Back to Umrah
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            @if($package->images->first())
            <img src="{{ asset('public/assets/images/' . $package->images->first()->image_path) }}"
                 class="card-img-top" style="height:280px;object-fit:cover" alt="{{ $package->name }}">
            @endif
            <div class="card-body">
                <h4 class="fw-bold mb-1">{{ $package->name }}</h4>
                <div class="d-flex gap-3 text-muted small mb-3">
                    @if(!empty($package->from_location))
                    <span><i class="bi bi-geo-alt"></i> {{ $package->from_location }} → {{ $package->to_location ?? 'Makkah' }}</span>
                    @endif
                    @if($package->days)
                    <span><i class="bi bi-clock"></i> {{ $package->days }} Days</span>
                    @endif
                    @if($package->packageType)
                    <span><i class="bi bi-tag"></i> {{ $package->packageType->packege_type ?? '' }}</span>
                    @endif
                </div>

                @if($package->description)
                <p class="text-muted">{{ $package->description }}</p>
                @endif

                @if(!empty($package->inclusion_names))
                <h6 class="fw-semibold mt-3">Inclusions</h6>
                <div class="d-flex flex-wrap gap-1 mb-3">
                    @foreach($package->inclusion_names as $inc)
                    <span class="badge bg-success bg-opacity-10 text-success border border-success"><i class="bi bi-check2"></i> {{ $inc }}</span>
                    @endforeach
                </div>
                @endif

                @if(!empty($package->exclusion_names))
                <h6 class="fw-semibold">Exclusions</h6>
                <div class="d-flex flex-wrap gap-1">
                    @foreach($package->exclusion_names as $exc)
                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger"><i class="bi bi-x"></i> {{ $exc }}</span>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm sticky-top" style="top:80px">
            <div class="card-header bg-white"><h6 class="mb-0">Book This Package</h6></div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="text-muted small">Price per adult</div>
                    <div class="fw-bold text-primary fs-4">{{ $package->currency ?? activeCurrency()->currency_name }} {{ number_format($package->price ?? 0, 0) }}</div>
                </div>

                <div class="card bg-light border-0 mb-3 p-2">
                    <div class="text-muted small">Wallet Balance</div>
                    <div class="fw-bold text-success">{{ activeCurrency()->currency_name }} {{ number_format(auth()->user()->wallet?->balance ?? 0, 0) }}</div>
                </div>

                <a href="{{ route('agent.umrah.booking', $package->id) }}" class="btn btn-success w-100">
                    <i class="bi bi-calendar-check"></i> Book Now
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
