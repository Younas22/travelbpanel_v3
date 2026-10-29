@extends('admin-nova.layouts.app')

@section('title', 'Edit Room')

@section('content')
<div class="tbp-hotel-form-page tbp-room-page">
    <header class="tbp-hotel-form-header">
        <div><h1>Edit Room</h1><p>Room {{ $room->room_number }} · {{ optional($room->hotel)->name }}</p></div>
        <a href="{{ route('admin.hotels.rooms.index', ['hotel_id' => $room->hotel_id]) }}" class="tbp-hotel-btn"><i class="bi bi-arrow-left"></i> Back to List</a>
    </header>
    @include('admin-nova.hotels.rooms._form', ['room' => $room])
</div>
@endsection
