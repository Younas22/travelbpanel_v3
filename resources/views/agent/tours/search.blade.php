@extends('agent.layouts.app')
@section('title', 'Tour Search Results')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-map"></i> Tour Results</h4>
    <a href="{{ route('agent.tours.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-search"></i> New Search
    </a>
</div>

<div class="row g-3">
    @forelse($tours as $tour)
    <div class="col-md-6 col-xl-4">
        <div class="card border-0 shadow-sm h-100">
            @if($tour->images->first())
            <img src="{{ asset('public/assets/images/' . $tour->images->first()->image_path) }}"
                 class="card-img-top" style="height:160px;object-fit:cover" alt="{{ $tour->name }}">
            @endif
            <div class="card-body d-flex flex-column">
                <h6 class="fw-bold">{{ $tour->name }}</h6>
                <p class="text-muted small"><i class="bi bi-geo-alt"></i> {{ $tour->location_name ?? 'N/A' }} &nbsp;<i class="bi bi-clock ms-2"></i> {{ $tour->days ?? '?' }} Days</p>
                <div class="mt-auto d-flex justify-content-between align-items-center">
                    <div class="fw-bold text-primary">{{ $tour->currency ?? '' }} {{ number_format($tour->price ?? 0, 0) }}</div>
                    <a href="{{ route('agent.tours.details', \Str::slug($tour->name)) }}" class="btn btn-primary btn-sm">View</a>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card border-0 bg-light">
            <div class="card-body text-center py-5">
                <i class="bi bi-map text-muted fs-1"></i>
                <p class="text-muted mt-2">No tours found for your search criteria.</p>
            </div>
        </div>
    </div>
    @endforelse
</div>

@if($tours->hasPages())
<div class="mt-3">{{ $tours->links() }}</div>
@endif
@endsection
