@extends('admin-nova.layouts.app')

@section('title', 'Edit Menu Item')

@push('styles')
@include('admin-nova.menus._styles')
@endpush

@section('content')
<div id="mnPage" class="tt-fade-in font-jakarta">
    <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
        <div>
            <h1 class="text-lg font-bold text-novatext">Edit Menu Item</h1>
            <p class="text-xs text-novamuted mt-1">Update: {{ $menu->name }}</p>
        </div>
        <a href="{{ route('admin.menus.index', ['category' => $menu->category]) }}" class="mn-btn-nova px-4 py-2.5 text-xs font-semibold">
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            Back to Menu
        </a>
    </div>

    @include('admin-nova.menus._form', ['menu' => $menu, 'category' => $menu->category])
</div>
@endsection
