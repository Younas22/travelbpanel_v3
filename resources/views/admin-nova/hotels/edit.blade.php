@extends('admin-nova.layouts.app')

@section('title', 'Edit Hotel')

@section('content')
<div class="tbp-hotel-form-page">
    <header class="tbp-hotel-form-header">
        <div><h1>Edit Hotel</h1><p>{{ $hotel->name }}</p></div>
        <a href="{{ route('admin.hotels.index') }}" class="tbp-hotel-btn"><i class="bi bi-arrow-left"></i> Back to List</a>
    </header>
    @include('admin-nova.hotels._steps', ['hotel' => $hotel])
    @include('admin-nova.hotels._form', ['hotel' => $hotel])
</div>
@endsection
