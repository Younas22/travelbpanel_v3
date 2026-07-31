@extends('admin-nova.layouts.app')

@section('title', 'Edit Page')

@push('styles')
@include('admin-nova.pages._styles')
@endpush

@section('content')
<div id="pgPage" class="tt-fade-in font-jakarta">
    <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
        <div>
            <h1 class="text-lg font-bold text-novatext">Edit Page: {{ Str::limit($page->name, 30) }}</h1>
            <p class="text-xs text-novamuted mt-1">Update page content and settings</p>
        </div>
        <div class="flex items-center gap-2">
            @if($page->status === 'published')
                <a href="{{ $page->url }}" target="_blank" class="pg-btn-nova px-4 py-2.5 text-xs font-semibold" style="color:#06B6D4; border-color:#06B6D4;">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h6v6M10 14 21 3M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/></svg>
                    View Live
                </a>
            @endif
            <a href="{{ route('admin.pages.index') }}" class="pg-btn-nova px-4 py-2.5 text-xs font-semibold">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                Back to Pages
            </a>
        </div>
    </div>

    @include('admin-nova.pages._form', ['page' => $page])
</div>
@endsection
