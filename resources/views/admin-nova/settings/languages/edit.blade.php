@extends('admin-nova.layouts.app')

@section('title', 'Edit Language')

@push('styles')
@include('admin-nova.settings.languages._styles')
@endpush

@section('content')
<div id="lgPage" class="tt-fade-in font-jakarta">
    <div class="mb-6">
        <h1 class="text-lg font-bold text-novatext">Edit Language</h1>
        <p class="text-xs text-novamuted mt-1">Update language information</p>
    </div>

    @include('admin-nova.settings.languages._form', ['language' => $language])
</div>
@endsection
