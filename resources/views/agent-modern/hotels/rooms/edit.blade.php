@extends('agent-modern.layouts.app')
@section('title', 'Edit Room')

@section('content')

    <div class="ap-page-header">
        <div>
            <h4 class="ap-page-title">Edit Room</h4>
            <p class="ap-page-sub">Room {{ $room->room_number }}{{ $room->roomType ? ' — ' . $room->roomType->name : '' }}</p>
        </div>
        <a href="{{ route('agent.hotels.edit', $room->hotel_id) }}#rooms" class="ap-btn-outline">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    <div class="row g-4">

        <div class="col-lg-8">
            <form action="{{ route('agent.hotels.rooms.update', $room) }}" method="POST">
                @csrf @method('PATCH')
                <div class="am-card">
                    <div class="am-card-header">Room Information</div>
                    <div class="am-card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Hotel <span style="color: var(--danger-color);">*</span></label>
                                <select name="hotel_id" id="hotel_id" class="form-select @error('hotel_id') is-invalid @enderror" required>
                                    <option value="">Select Hotel</option>
                                    @foreach($hotels as $hotel)
                                        <option value="{{ $hotel->id }}" {{ old('hotel_id', $room->hotel_id) == $hotel->id ? 'selected' : '' }}>{{ $hotel->name }}</option>
                                    @endforeach
                                </select>
                                @error('hotel_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Room Type <span style="color: var(--danger-color);">*</span></label>
                                <select name="room_type_id" id="room_type_id" class="form-select @error('room_type_id') is-invalid @enderror" required>
                                    <option value="">Select Room Type</option>
                                    @foreach($roomTypes as $type)
                                        <option value="{{ $type->id }}" data-hotel-id="{{ $type->hotel_id }}" {{ old('room_type_id', $room->room_type_id) == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                                    @endforeach
                                </select>
                                @error('room_type_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Room Number <span style="color: var(--danger-color);">*</span></label>
                                <input type="text" name="room_number" class="form-control @error('room_number') is-invalid @enderror" value="{{ old('room_number', $room->room_number) }}" placeholder="e.g. 101, A-5" required>
                                @error('room_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Floor</label>
                                <input type="text" name="floor" class="form-control" value="{{ old('floor', $room->floor) }}" placeholder="e.g. Ground, 1st, 2nd">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Status <span style="color: var(--danger-color);">*</span></label>
                                <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                    <option value="available" {{ old('status', $room->status) == 'available' ? 'selected' : '' }}>Available</option>
                                    <option value="occupied" {{ old('status', $room->status) == 'occupied' ? 'selected' : '' }}>Occupied</option>
                                    <option value="maintenance" {{ old('status', $room->status) == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                </select>
                                @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                    <div style="padding: var(--am-space-4) var(--am-space-5); border-top: 1px solid var(--border-color); display: flex; justify-content: flex-end;">
                        <button type="submit" class="ap-btn-primary">
                            <i class="bi bi-check-lg"></i> Update Room
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-lg-4">
            <div class="am-card">
                <div class="am-card-header">Room Details</div>
                <div class="am-card-body">
                    <div class="ap-kv-row">
                        <span class="label">Current Status</span>
                        @if($room->status == 'available')
                            <span class="badge bg-success"><i class="bi bi-check-lg"></i> Available</span>
                        @elseif($room->status == 'occupied')
                            <span class="badge bg-danger"><i class="bi bi-x-lg"></i> Occupied</span>
                        @else
                            <span class="badge bg-warning"><i class="bi bi-tools"></i> Maintenance</span>
                        @endif
                    </div>

                    @if($room->roomType)
                    <div class="ap-kv-row"><span class="label">Room Type</span><span class="badge bg-primary">{{ $room->roomType->name }}</span></div>
                    <div class="ap-kv-row"><span class="label">Price / Night</span><span style="font-weight: 650; color: var(--success-color);">{{ number_format($room->roomType->price_per_night, 2) }}</span></div>
                    <div class="ap-kv-row"><span class="label">Capacity</span><span>{{ $room->roomType->max_adults }} Adults{{ $room->roomType->max_children ? ' / ' . $room->roomType->max_children . ' Children' : '' }}</span></div>
                    <div class="ap-kv-row"><span class="label">Beds</span><span>{{ $room->roomType->beds }}</span></div>
                    <div class="ap-kv-row">
                        <span class="label">AC</span>
                        @if($room->roomType->ac)
                            <i class="bi bi-snow" style="color: var(--primary-color);"></i>
                        @else
                            <i class="bi bi-x" style="color: color-mix(in srgb, var(--text-color) 30%, transparent);"></i>
                        @endif
                    </div>
                    @endif

                    <div style="border-top: 1px solid var(--border-color); padding-top: var(--am-space-3); margin-top: 8px;">
                        <div class="ap-kv-row" style="font-size: 11px;"><span class="label">Created</span><span>{{ $room->created_at->format('d M Y') }}</span></div>
                        <div class="ap-kv-row" style="font-size: 11px;"><span class="label">Updated</span><span>{{ $room->updated_at->format('d M Y') }}</span></div>
                    </div>
                </div>
            </div>
        </div>

    </div>

@endsection

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
