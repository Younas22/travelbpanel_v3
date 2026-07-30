@extends('admin-nova.layouts.app')

@section('title', 'Edit Agent: ' . $agent->full_name)

@push('styles')
@include('admin-nova.agents._styles')
@endpush

@section('content')
<div id="agPage" class="tt-fade-in font-jakarta max-w-3xl mx-auto">

    <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
        <div>
            <h1 class="text-lg font-bold text-novatext">Edit Agent</h1>
            <p class="text-xs text-novamuted mt-1">{{ $agent->full_name }}</p>
        </div>
        <a href="{{ route('admin.agents.show', $agent) }}" class="ag-btn-nova px-4 py-2.5 text-xs font-semibold">
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            Back
        </a>
    </div>

    <form method="POST" action="{{ route('admin.agents.update', $agent) }}">
        @csrf
        @method('PUT')

        @include('admin-nova.agents._form', ['agent' => $agent])

        <div class="flex items-center justify-end gap-2">
            <a href="{{ route('admin.agents.show', $agent) }}" class="ag-btn-nova px-5 py-2.5 text-xs font-semibold">Cancel</a>
            <button type="submit" class="ag-btn-nova ag-btn-primary px-5 py-2.5 text-xs font-semibold">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12.75 11.25 15 15 9.75"/><circle cx="12" cy="12" r="9"/></svg>
                Save Changes
            </button>
        </div>
    </form>
</div>
@endsection
