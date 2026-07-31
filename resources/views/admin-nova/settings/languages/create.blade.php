@extends('admin-nova.layouts.app')

@section('title', 'Add Language')

@push('styles')
@include('admin-nova.settings.languages._styles')
@endpush

@section('content')
<div id="lgPage" class="tt-fade-in font-jakarta">
    <div class="mb-6">
        <h1 class="text-lg font-bold text-novatext">Add New Language</h1>
        <p class="text-xs text-novamuted mt-1">Add a new language to your application</p>
    </div>

    @include('admin-nova.settings.languages._form', ['language' => null])
</div>
@endsection
