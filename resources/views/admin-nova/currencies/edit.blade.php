@extends('admin-nova.layouts.app')

@section('title', 'Edit Currency')

@push('styles')
@include('admin-nova.currencies._styles')
@endpush

@section('content')
<div id="crPage" class="tt-fade-in font-jakarta">
    <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
        <div>
            <h1 class="text-lg font-bold text-novatext">Edit Currency</h1>
            <p class="text-xs text-novamuted mt-1">Update currency details and exchange rate</p>
        </div>
        <a href="{{ route('admin.currencies.index') }}" class="cr-btn-nova px-4 py-2.5 text-xs font-semibold">
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            Back to Currencies
        </a>
    </div>

    @include('admin-nova.currencies._form', ['currency' => $currency])
</div>
@endsection
