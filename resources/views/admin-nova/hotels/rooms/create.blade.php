@extends('admin-nova.layouts.app')

@section('title', 'Create Room')

@section('content')
<div class="tbp-hotel-form-page tbp-room-page">
    <header class="tbp-hotel-form-header">
        <div><h1>Create Room</h1><p>Add a new room to this hotel</p></div>
        <a href="{{ route('admin.hotels.rooms.index', request('hotel_id') ? ['hotel_id' => request('hotel_id')] : []) }}" class="tbp-hotel-btn"><i class="bi bi-arrow-left"></i> Back to List</a>
    </header>
    @include('admin-nova.hotels.rooms._form', ['room' => null])
</div>
@endsection
