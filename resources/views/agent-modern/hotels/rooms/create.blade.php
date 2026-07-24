@extends('agent-modern.layouts.app')
@section('title', 'Create Room')

@section('content')

    <div class="ap-page-header">
        <div>
            <h4 class="ap-page-title">Create Room</h4>
            <p class="ap-page-sub">Add a new room to your hotel</p>
        </div>
        <a href="{{ $backUrl ?? route('agent.hotels.index') }}" class="ap-btn-outline">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    @if($errors->any())
    <div class="am-alert am-alert-danger">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <span><ul class="mb-0 ps-3">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></span>
    </div>
    @endif

    <div class="row g-4">

        <div class="col-lg-8">
            <form action="{{ $formAction ?? route('agent.hotels.rooms.store') }}" method="POST">
                @csrf
                <div class="am-card">
                    <div class="am-card-header">Room Information</div>
                    <div class="am-card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Hotel <span style="color: var(--danger-color);">*</span></label>
                                <select name="hotel_id" id="hotel_id" class="form-select @error('hotel_id') is-invalid @enderror" required>
                                    <option value="">Select Hotel</option>
                                    @foreach($hotels as $hotel)
                                        <option value="{{ $hotel->id }}" {{ request()->input('hotel_id', old('hotel_id')) == $hotel->id ? 'selected' : '' }}>{{ $hotel->name }}</option>
                                    @endforeach
                                </select>
                                @error('hotel_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Room Type <span style="color: var(--danger-color);">*</span></label>
                                <select name="room_type_id" id="room_type_id" class="form-select @error('room_type_id') is-invalid @enderror" required>
                                    <option value="">Select Room Type</option>
                                    @foreach($roomTypes as $type)
                                        <option value="{{ $type->id }}" data-hotel-id="{{ $type->hotel_id }}" {{ old('room_type_id') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                                    @endforeach
                                </select>
                                @error('room_type_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Room Number <span style="color: var(--danger-color);">*</span></label>
                                <input type="text" name="room_number" class="form-control @error('room_number') is-invalid @enderror" value="{{ old('room_number') }}" placeholder="e.g. 101, A-5" required>
                                @error('room_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Floor</label>
                                <input type="text" name="floor" class="form-control" value="{{ old('floor') }}" placeholder="e.g. Ground, 1st, 2nd">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Status <span style="color: var(--danger-color);">*</span></label>
                                <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                    <option value="available" {{ old('status', 'available') == 'available' ? 'selected' : '' }}>Available</option>
                                    <option value="occupied" {{ old('status') == 'occupied' ? 'selected' : '' }}>Occupied</option>
                                    <option value="maintenance" {{ old('status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                </select>
                                @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                    <div style="padding: var(--am-space-4) var(--am-space-5); border-top: 1px solid var(--border-color); display: flex; justify-content: flex-end;">
                        <button type="submit" class="ap-btn-primary">
                            <i class="bi bi-plus-lg"></i> Create Room
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-lg-4">
            <div class="am-card">
                <div class="am-card-header">Tips</div>
                <div class="am-card-body">
                    <div class="ap-info-tip"><i class="bi bi-info-circle" style="color: var(--primary-color);"></i> <span>Room numbers must be unique within each hotel.</span></div>
                    <div class="ap-info-tip"><i class="bi bi-lightbulb" style="color: var(--warning-color);"></i> <span>Select the hotel first to filter available room types.</span></div>
                    <div style="border-top: 1px solid var(--border-color); padding-top: var(--am-space-3);">
                        <p style="font-size: 11px; color: color-mix(in srgb, var(--text-color) 50%, transparent); font-weight: 650; margin-bottom: 8px;">Status Guide</p>
                        <div style="display: flex; align-items: center; gap: 8px; font-size: 12px; margin-bottom: 6px;"><span class="ap-status-dot dot-available"></span> <strong>Available</strong> — Ready for booking</div>
                        <div style="display: flex; align-items: center; gap: 8px; font-size: 12px; margin-bottom: 6px;"><span class="ap-status-dot dot-occupied"></span> <strong>Occupied</strong> — Currently in use</div>
                        <div style="display: flex; align-items: center; gap: 8px; font-size: 12px;"><span class="ap-status-dot dot-maintenance"></span> <strong>Maintenance</strong> — Under repair</div>
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
