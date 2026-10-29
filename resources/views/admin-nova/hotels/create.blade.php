@extends('admin-nova.layouts.app')

@section('title', 'Create Hotel')

@section('content')
<div class="tbp-hotel-form-page">
    <header class="tbp-hotel-form-header">
        <div><h1>Create Hotel</h1><p>Add a new hotel to your system</p></div>
        <a href="{{ route('admin.hotels.index') }}" class="tbp-hotel-btn"><i class="bi bi-arrow-left"></i> Back to List</a>
    </header>
    @include('admin-nova.hotels._steps', ['hotel' => null])
    @include('admin-nova.hotels._form', ['hotel' => null])
</div>
@endsection
