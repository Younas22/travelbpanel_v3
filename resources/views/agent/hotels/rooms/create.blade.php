@extends('agent.layouts.app')
@section('title', 'Create Room')

@section('content')

{{-- Page Header --}}
<div class="flex items-center justify-between mb-5">
    <div>
        <h4 class="text-lg font-bold text-gray-800">Create Room</h4>
        <p class="text-xs text-gray-400 mt-0.5">Add a new room to your hotel</p>
    </div>
    <a href="{{ $backUrl ?? route('agent.hotels.index') }}" class="ap-btn-outline">
        <i class="fas fa-arrow-left text-xs"></i> Back
    </a>
</div>

@if($errors->any())
<div class="mb-4 bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 text-sm">
    <ul class="list-disc list-inside space-y-1">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- Left: Form --}}
    <div class="lg:col-span-2">
        <form action="{{ $formAction ?? route('agent.hotels.rooms.store') }}" method="POST">
            @csrf
            <div class="ap-card">
                <div class="ap-card-header">Room Information</div>
                <div class="ap-card-body">
                    <div class="grid grid-cols-2 gap-4">

                        <div>
                            <label class="ap-label">Hotel <span class="text-red-500">*</span></label>
                            <select name="hotel_id" id="hotel_id" class="ap-input @error('hotel_id') ap-input-error @enderror" required>
                                <option value="">Select Hotel</option>
                                @foreach($hotels as $hotel)
                                    <option value="{{ $hotel->id }}" {{ request()->input('hotel_id', old('hotel_id')) == $hotel->id ? 'selected' : '' }}>
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
                                            {{ old('room_type_id') == $type->id ? 'selected' : '' }}>
                                        {{ $type->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('room_type_id')<p class="ap-error">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="ap-label">Room Number <span class="text-red-500">*</span></label>
                            <input type="text" name="room_number" class="ap-input @error('room_number') ap-input-error @enderror"
                                   value="{{ old('room_number') }}" placeholder="e.g. 101, A-5" required>
                            @error('room_number')<p class="ap-error">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="ap-label">Floor</label>
                            <input type="text" name="floor" class="ap-input"
                                   value="{{ old('floor') }}" placeholder="e.g. Ground, 1st, 2nd">
                        </div>

                        <div class="col-span-2">
                            <label class="ap-label">Status <span class="text-red-500">*</span></label>
                            <select name="status" class="ap-input @error('status') ap-input-error @enderror" required>
                                <option value="available"   {{ old('status', 'available') == 'available'   ? 'selected' : '' }}>Available</option>
                                <option value="occupied"    {{ old('status') == 'occupied'    ? 'selected' : '' }}>Occupied</option>
                                <option value="maintenance" {{ old('status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                            </select>
                            @error('status')<p class="ap-error">{{ $message }}</p>@enderror
                        </div>

                    </div>
                </div>
                <div class="px-5 py-4 bg-gray-50 border-t border-gray-100 flex justify-end">
                    <button type="submit" class="ap-btn-primary">
                        <i class="fas fa-plus text-xs"></i> Create Room
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Right: Info Panel --}}
    <div class="lg:col-span-1">
        <div class="ap-card">
            <div class="ap-card-header">Tips</div>
            <div class="ap-card-body space-y-3">

                <div class="flex items-start gap-3 text-sm text-gray-600">
                    <i class="fas fa-info-circle text-blue-400 mt-0.5 flex-shrink-0"></i>
                    <span>Room numbers must be unique within each hotel.</span>
                </div>

                <div class="flex items-start gap-3 text-sm text-gray-600">
                    <i class="fas fa-lightbulb text-yellow-400 mt-0.5 flex-shrink-0"></i>
                    <span>Select the hotel first to filter available room types.</span>
                </div>

                <div class="border-t border-gray-100 pt-3">
                    <p class="text-xs text-gray-400 font-medium mb-2">Status Guide</p>
                    <div class="space-y-1.5">
                        <div class="flex items-center gap-2 text-xs text-gray-600">
                            <span class="w-2 h-2 rounded-full bg-green-500 flex-shrink-0"></span>
                            <span><strong>Available</strong> — Ready for booking</span>
                        </div>
                        <div class="flex items-center gap-2 text-xs text-gray-600">
                            <span class="w-2 h-2 rounded-full bg-red-500 flex-shrink-0"></span>
                            <span><strong>Occupied</strong> — Currently in use</span>
                        </div>
                        <div class="flex items-center gap-2 text-xs text-gray-600">
                            <span class="w-2 h-2 rounded-full bg-yellow-500 flex-shrink-0"></span>
                            <span><strong>Maintenance</strong> — Under repair</span>
                        </div>
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

    function filterRoomTypes(hotelId, resetValue = true) {
        roomTypeSelect.querySelectorAll('option').forEach(opt => {
            if (!opt.value) return;
            opt.style.display = (!hotelId || opt.dataset.hotelId === hotelId) ? '' : 'none';
        });
        if (resetValue) roomTypeSelect.value = '';
    }

    hotelSelect.addEventListener('change', function () { filterRoomTypes(this.value, true); });

    if (hotelSelect.value) {
        filterRoomTypes(hotelSelect.value, false);
    }
});
</script>
@endpush

@endsection
