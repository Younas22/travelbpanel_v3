@extends('agent.layouts.app')
@section('title', 'Edit Room')

@section('content')

{{-- Page Header --}}
<div class="flex items-center justify-between mb-5">
    <div>
        <h4 class="text-lg font-bold text-gray-800">Edit Room</h4>
        <p class="text-xs text-gray-400 mt-0.5">Room {{ $room->room_number }}{{ $room->roomType ? ' — ' . $room->roomType->name : '' }}</p>
    </div>
    <a href="{{ route('agent.hotels.edit', $room->hotel_id) }}#rooms" class="ap-btn-outline">
        <i class="fas fa-arrow-left text-xs"></i> Back
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- Left: Form --}}
    <div class="lg:col-span-2">
        <form action="{{ route('agent.hotels.rooms.update', $room) }}" method="POST">
            @csrf @method('PATCH')
            <div class="ap-card">
                <div class="ap-card-header">Room Information</div>
                <div class="ap-card-body">
                    <div class="grid grid-cols-2 gap-4">

                        <div>
                            <label class="ap-label">Hotel <span class="text-red-500">*</span></label>
                            <select name="hotel_id" id="hotel_id" class="ap-input @error('hotel_id') ap-input-error @enderror" required>
                                <option value="">Select Hotel</option>
                                @foreach($hotels as $hotel)
                                    <option value="{{ $hotel->id }}" {{ old('hotel_id', $room->hotel_id) == $hotel->id ? 'selected' : '' }}>
                                        {{ $hotel->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('hotel_id')<p class="ap-error">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="ap-label">Room Type <span class="text-red-500">*</span></label>
                            <select name="room_type_id" id="room_type_id" class="ap-input @error('room_type_id') ap-input-error @enderror" required>
                                <option value="">Select Room Type</option>
                                @foreach($roomTypes as $type)
                                    <option value="{{ $type->id }}" data-hotel-id="{{ $type->hotel_id }}"
                                            {{ old('room_type_id', $room->room_type_id) == $type->id ? 'selected' : '' }}>
                                        {{ $type->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('room_type_id')<p class="ap-error">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="ap-label">Room Number <span class="text-red-500">*</span></label>
                            <input type="text" name="room_number" class="ap-input @error('room_number') ap-input-error @enderror"
                                   value="{{ old('room_number', $room->room_number) }}" placeholder="e.g. 101, A-5" required>
                            @error('room_number')<p class="ap-error">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="ap-label">Floor</label>
                            <input type="text" name="floor" class="ap-input"
                                   value="{{ old('floor', $room->floor) }}" placeholder="e.g. Ground, 1st, 2nd">
                        </div>

                        <div class="col-span-2">
                            <label class="ap-label">Status <span class="text-red-500">*</span></label>
                            <select name="status" class="ap-input @error('status') ap-input-error @enderror" required>
                                <option value="available"    {{ old('status', $room->status) == 'available'    ? 'selected' : '' }}>Available</option>
                                <option value="occupied"     {{ old('status', $room->status) == 'occupied'     ? 'selected' : '' }}>Occupied</option>
                                <option value="maintenance"  {{ old('status', $room->status) == 'maintenance'  ? 'selected' : '' }}>Maintenance</option>
                            </select>
                            @error('status')<p class="ap-error">{{ $message }}</p>@enderror
                        </div>

                    </div>
                </div>
                <div class="px-5 py-4 bg-gray-50 border-t border-gray-100 flex justify-end">
                    <button type="submit" class="ap-btn-primary">
                        <i class="fas fa-check text-xs"></i> Update Room
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Right: Info --}}
    <div class="lg:col-span-1">
        <div class="ap-card">
            <div class="ap-card-header">Room Details</div>
            <div class="ap-card-body space-y-3">

                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-500">Current Status</span>
                    @if($room->status == 'available')
                        <span class="ap-badge ap-badge-success"><i class="fas fa-check text-xs"></i> Available</span>
                    @elseif($room->status == 'occupied')
                        <span class="ap-badge ap-badge-danger"><i class="fas fa-times text-xs"></i> Occupied</span>
                    @else
                        <span class="ap-badge ap-badge-warning"><i class="fas fa-tools text-xs"></i> Maintenance</span>
                    @endif
                </div>

                @if($room->roomType)
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-500">Room Type</span>
                    <span class="ap-badge ap-badge-blue">{{ $room->roomType->name }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-500">Price / Night</span>
                    <span class="font-semibold text-green-600">{{ number_format($room->roomType->price_per_night, 2) }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-500">Capacity</span>
                    <span class="text-gray-700">{{ $room->roomType->max_adults }} Adults{{ $room->roomType->max_children ? ' / ' . $room->roomType->max_children . ' Children' : '' }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-500">Beds</span>
                    <span class="text-gray-700">{{ $room->roomType->beds }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-500">AC</span>
                    @if($room->roomType->ac)
                        <i class="fas fa-snowflake text-blue-500"></i>
                    @else
                        <i class="fas fa-times text-gray-300"></i>
                    @endif
                </div>
                @endif

                <div class="border-t border-gray-100 pt-3 space-y-2">
                    <div class="flex items-center justify-between text-xs text-gray-400">
                        <span>Created</span>
                        <span>{{ $room->created_at->format('d M Y') }}</span>
                    </div>
                    <div class="flex items-center justify-between text-xs text-gray-400">
                        <span>Updated</span>
                        <span>{{ $room->updated_at->format('d M Y') }}</span>
                    </div>
                </div>

            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const hotelSelect    = document.getElementById('hotel_id');
    const roomTypeSelect = document.getElementById('room_type_id');

    function filterRoomTypes(hotelId) {
        roomTypeSelect.querySelectorAll('option').forEach(opt => {
            if (!opt.value) return;
            opt.style.display = (!hotelId || opt.dataset.hotelId === hotelId) ? '' : 'none';
        });
    }

    hotelSelect.addEventListener('change', function () { filterRoomTypes(this.value); });
    filterRoomTypes(hotelSelect.value);
});
</script>
@endpush

@endsection
