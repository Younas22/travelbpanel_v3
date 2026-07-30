@extends('admin-nova.layouts.app')

@section('title', 'Add New Agent')

@push('styles')
@include('admin-nova.agents._styles')
@endpush

@section('content')
<div id="agPage" class="tt-fade-in font-jakarta max-w-3xl mx-auto">

    <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
        <div>
            <h1 class="text-lg font-bold text-novatext">Add New Agent</h1>
            <p class="text-xs text-novamuted mt-1">Create a new B2B agent account</p>
        </div>
        <a href="{{ route('admin.agents.index') }}" class="ag-btn-nova px-4 py-2.5 text-xs font-semibold">
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            Back
        </a>
    </div>

    <form method="POST" action="{{ route('admin.agents.store') }}">
        @csrf

        @include('admin-nova.agents._form', ['agent' => null])

        <button type="submit" class="ag-btn-nova ag-btn-primary w-full sm:w-auto px-6 py-3 text-sm font-semibold">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="3.25"/><path d="M2.75 19c.5-3.2 3-5.25 6.25-5.25S15.25 15.8 15.75 19"/><path d="M18 8v6M15 11h6"/></svg>
            Create Agent
        </button>
    </form>
</div>
@endsection
