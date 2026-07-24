{{-- Disclosure: mirrors agent/umrah/search.blade.php, which is dead/unreachable
     code — no route ever calls a controller method that returns this view.
     Kept for file-tree parity with the exhaustive file list this task specifies. --}}
@extends('agent-modern.layouts.app')
@section('title', 'Umrah Search Results')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-moon-stars"></i> Umrah Results</h4>
    <a href="{{ route('agent.umrah.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-search"></i> New Search
    </a>
</div>

<div class="row g-3">
    @forelse($packages as $package)
    <div class="col-md-6 col-xl-4">
        <div class="card border-0 shadow-sm h-100">
            @if($package->images->first())
            <img src="{{ asset('public/assets/images/' . $package->images->first()->image_path) }}"
                 class="card-img-top ap-img-h-160" alt="{{ $package->name }}">
            @endif
            <div class="card-body d-flex flex-column">
                <h6 class="fw-bold">{{ $package->name }}</h6>
                <p class="text-muted small">{{ $package->from_location ?? '' }}{{ !empty($package->to_location) ? ' → ' . $package->to_location : '' }}
                    @if($package->days)<span class="ms-2"><i class="bi bi-clock"></i> {{ $package->days }} Days</span>@endif
                </p>
                <div class="mt-auto d-flex justify-content-between align-items-center">
                    <div class="fw-bold text-primary">{{ $package->currency ?? '' }} {{ number_format($package->price ?? 0, 0) }}</div>
                    <a href="{{ route('agent.umrah.details', \Str::slug($package->name)) }}" class="btn btn-primary btn-sm">View</a>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card border-0 bg-light">
            <div class="card-body text-center py-5">
                <i class="bi bi-moon-stars text-muted fs-1"></i>
                <p class="text-muted mt-2">No Umrah packages found.</p>
            </div>
        </div>
    </div>
    @endforelse
</div>

@if($packages->hasPages())
<div class="mt-3">{{ $packages->links() }}</div>
@endif
@endsection
