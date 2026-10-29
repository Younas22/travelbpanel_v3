@extends('admin-nova.layouts.app')

@section('title', 'Create Room Type')

@section('content')
<div class="tbp-hotel-form-page">
    <header class="tbp-hotel-form-header">
        <div><h1>Create Room Type</h1><p>Add a new room type to this hotel</p></div>
        <a href="{{ route('admin.hotels.room-types.index', request('hotel_id') ? ['hotel_id' => request('hotel_id')] : []) }}" class="tbp-hotel-btn"><i class="bi bi-arrow-left"></i> Back to List</a>
    </header>
    @include('admin-nova.hotels.room-types._form', ['roomType' => null])
</div>
@endsection
