@extends($layout ?? 'admin-nova.layouts.app')
@section('title', 'Edit Tour Package')

@push('styles')
@include('admin-nova.umrah.packages._styles')
@endpush

@section('content')
@php $tour = $tour ?? $package ?? null; @endphp
<div id="umfPage" class="tt-fade-in font-jakarta">

    <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
        <div>
            <h1 class="text-lg font-bold text-novatext">Edit Tour Package</h1>
            <p class="text-xs text-novamuted mt-1">Update package: {{ $tour->name }}</p>
        </div>
        <a href="{{ $backUrl ?? route('admin.tours.packages.index') }}" class="umf-btn-nova px-4 py-2.5 text-xs font-semibold">
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            Back to List
        </a>
    </div>

    <form action="{{ $formAction ?? route('admin.tours.packages.update', $tour->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method($formMethod ?? 'PATCH')
        @include('admin-nova.tours.packages._form', ['tour' => $tour])
    </form>
</div>
@endsection
