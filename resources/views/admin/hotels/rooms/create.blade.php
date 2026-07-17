@extends($layout ?? 'admin.layouts.app')

@section('title', 'Create Room')

@section('content')
<div class="content-area p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Create Room</h4>
            <p class="text-muted mb-0">Add a new room</p>
        </div>
        <a href="{{ $backUrl ?? route('admin.hotels.rooms.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <form action="{{ $formAction ?? route('admin.hotels.rooms.store') }}" method="POST">
                @csrf
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Room Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Hotel <span class="text-danger">*</span></label>
                                <select name="hotel_id" id="hotel_id" class="form-select" required>
                                    <option value="">Select Hotel</option>
                                    @foreach($hotels as $hotel)
                                        <option value="{{ $hotel->id }}" {{ request()->input('hotel_id', old('hotel_id')) == $hotel->id ? 'selected' : '' }}>
                                            {{ $hotel->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Room Type <span class="text-danger">*</span></label>
                                <select name="room_type_id" id="room_type_id" class="form-select" required>
                                    <option value="">Select Room Type</option>
                                    @foreach($roomTypes as $type)
                                        <option value="{{ $type->id }}" data-hotel-id="{{ $type->hotel_id }}" {{ old('room_type_id') == $type->id ? 'selected' : '' }}>
                                            {{ $type->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Room Number <span class="text-danger">*</span></label>
                                <input type="text" name="room_number" class="form-control" value="{{ old('room_number') }}" placeholder="e.g., 101" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Floor</label>
                                <input type="text" name="floor" class="form-control" value="{{ old('floor') }}" placeholder="e.g., Ground Floor, 1st Floor">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-select" required>
                                    <option value="available" {{ old('status', 'available') == 'available' ? 'selected' : '' }}>Available</option>
                                    <option value="occupied" {{ old('status') == 'occupied' ? 'selected' : '' }}>Occupied</option>
                                    <option value="maintenance" {{ old('status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Create Room
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Information</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-2"><i class="bi bi-info-circle"></i> Room numbers must be unique within each hotel.</p>
                    <p class="text-muted mb-0"><i class="bi bi-lightbulb"></i> Select the hotel first to filter available room types.</p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const hotelSelect = document.getElementById('hotel_id');
    const roomTypeSelect = document.getElementById('room_type_id');

    function filterRoomTypes(hotelId, resetValue = true) {
        const options = roomTypeSelect.querySelectorAll('option');
        options.forEach(option => {
            if (option.value === '') {
                option.style.display = 'block';
            } else {
                const optionHotelId = option.dataset.hotelId;
                if (hotelId === '' || optionHotelId === hotelId) {
                    option.style.display = 'block';
                } else {
                    option.style.display = 'none';
                }
            }
        });
        if (resetValue) roomTypeSelect.value = '';
    }

    hotelSelect.addEventListener('change', function() {
        filterRoomTypes(this.value, true);
    });

    // Page load par agar hotel pre-selected ho to filter apply karo
    if (hotelSelect.value) {
        filterRoomTypes(hotelSelect.value, false);
    }
});
</script>
@endpush
@endsection
