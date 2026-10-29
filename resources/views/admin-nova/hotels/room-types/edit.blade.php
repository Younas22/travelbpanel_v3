@extends('admin-nova.layouts.app')

@section('title', 'Edit Room Type')

@section('content')
<div class="tbp-hotel-form-page">
    <header class="tbp-hotel-form-header">
        <div><h1>Edit Room Type</h1><p>{{ $roomType->name }} · {{ optional($roomType->hotel)->name }}</p></div>
        <a href="{{ route('admin.hotels.room-types.index', ['hotel_id' => $roomType->hotel_id]) }}" class="tbp-hotel-btn"><i class="bi bi-arrow-left"></i> Back to List</a>
    </header>
    @include('admin-nova.hotels.room-types._form', ['roomType' => $roomType])
</div>
@endsection
