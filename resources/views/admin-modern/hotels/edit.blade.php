@extends('admin-modern.layouts.app')

@section('title', 'Edit Hotel')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Edit Hotel</h4>
            <p class="text-muted mb-0">{{ $hotel->name }}</p>
        </div>
        <a href="{{ route('admin.hotels.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back to List</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <ul class="nav hotel-tabs mb-4">
        <li class="nav-item"><a class="nav-link {{ !request('tab') || request('tab') == 'info' ? 'active' : '' }}" href="#hotel-info" data-bs-toggle="tab"><i class="bi bi-building"></i> Hotel Info</a></li>
        <li class="nav-item"><a class="nav-link {{ request('tab') == 'room-types' ? 'active' : '' }}" href="#room-types" data-bs-toggle="tab"><i class="bi bi-door-open"></i> Room Types <span class="badge">{{ $hotelRoomTypes->count() }}</span></a></li>
        <li class="nav-item"><a class="nav-link {{ request('tab') == 'rooms' ? 'active' : '' }}" href="#rooms" data-bs-toggle="tab"><i class="bi bi-key"></i> Rooms <span class="badge">{{ $hotelRooms->count() }}</span></a></li>
    </ul>

    <div class="tab-content">

        <div class="tab-pane fade {{ !request('tab') || request('tab') == 'info' ? 'show active' : '' }}" id="hotel-info">
            <form action="{{ route('admin.hotels.update', $hotel) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PATCH')
                <div class="row">
                    <div class="col-md-8">
                        <div class="card mb-4">
                            <div class="card-header"><h5 class="mb-0">Basic Information</h5></div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-12"><label class="form-label">Hotel Name <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" value="{{ old('name', $hotel->name) }}" required></div>
                                    <div class="col-md-6">
                                        <label class="form-label">Location <span class="text-danger">*</span></label>
                                        <select name="location_id" class="form-select location-select" id="location_id" required>
                                            <option value="">Search and select location...</option>
                                            @php
                                                $selectedLocationId = old('location_id', $hotel->location_id);
                                                $selectedLocation   = $selectedLocationId ? \App\Models\Location::find($selectedLocationId) : null;
                                            @endphp
                                            @if($selectedLocation)
                                                <option value="{{ $selectedLocation->id }}" selected>{{ $selectedLocation->city }}, {{ $selectedLocation->country }}</option>
                                            @endif
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Type <span class="text-danger">*</span></label>
                                        <select name="type" class="form-select" required>
                                            <option value="">Select Type</option>
                                            <option value="hotel" {{ old('type', $hotel->type) == 'hotel' ? 'selected' : '' }}>Hotel</option>
                                            <option value="guest house" {{ old('type', $hotel->type) == 'guest house' ? 'selected' : '' }}>Guest House</option>
                                            <option value="resort" {{ old('type', $hotel->type) == 'resort' ? 'selected' : '' }}>Resort</option>
                                        </select>
                                    </div>
                                    <div class="col-md-12"><label class="form-label">Address</label><input type="text" name="address" class="form-control" value="{{ old('address', $hotel->address) }}" placeholder="Street / Area"></div>
                                    <div class="col-md-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="4">{{ old('description', $hotel->description) }}</textarea></div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header"><h5 class="mb-0">Contact Information</h5></div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-4"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control" value="{{ old('phone', $hotel->phone) }}"></div>
                                    <div class="col-md-4"><label class="form-label">WhatsApp</label><input type="text" name="whatsapp" class="form-control" value="{{ old('whatsapp', $hotel->whatsapp) }}"></div>
                                    <div class="col-md-4"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="{{ old('email', $hotel->email) }}"></div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header"><h5 class="mb-0">Hotel Details</h5></div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-4"><label class="form-label">Check-in Time</label><input type="time" name="check_in_time" class="form-control" value="{{ old('check_in_time', $hotel->check_in_time ? substr($hotel->check_in_time, 0, 5) : '') }}"></div>
                                    <div class="col-md-4"><label class="form-label">Check-out Time</label><input type="time" name="check_out_time" class="form-control" value="{{ old('check_out_time', $hotel->check_out_time ? substr($hotel->check_out_time, 0, 5) : '') }}"></div>
                                    <div class="col-md-4"><label class="form-label">Total Rooms</label><input type="number" name="total_rooms" class="form-control" value="{{ old('total_rooms', $hotel->total_rooms) }}" min="0"></div>
                                    <div class="col-md-4">
                                        <label class="form-label">Stars</label>
                                        <select name="stars" class="form-select">
                                            <option value="">Select Stars</option>
                                            @for($i = 1; $i <= 5; $i++)
                                                <option value="{{ $i }}" {{ old('stars', $hotel->stars) == $i ? 'selected' : '' }}>{{ $i }} Star{{ $i > 1 ? 's' : '' }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                    <div class="col-md-4"><label class="form-label">Total Reviews</label><input type="number" name="total_rating" class="form-control" value="{{ old('total_rating', $hotel->total_rating) }}" min="0" step="1" placeholder="e.g. 2500"></div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header"><h5 class="mb-0">Amenities</h5></div>
                            <div class="card-body">
                                <div class="row g-2">
                                    @php $selectedAmenities = old('amenities', $hotel->amenities->pluck('id')->toArray()); @endphp
                                    @foreach($amenities as $amenity)
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="amenities[]" value="{{ $amenity->id }}" id="amenity{{ $amenity->id }}" {{ in_array($amenity->id, $selectedAmenities) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="amenity{{ $amenity->id }}">{{ $amenity->name }}</label>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        @if($hotel->images->count() > 0)
                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Existing Images</h5>
                                <small class="text-muted"><i class="bi bi-arrows-move"></i> Drag to reorder</small>
                            </div>
                            <div class="card-body">
                                <div class="row g-3" id="existing-images-grid">
                                    @foreach($hotel->images as $image)
                                    <div class="col-md-3 image-item" id="image-{{ $image->id }}" data-id="{{ $image->id }}">
                                        <div class="position-relative border rounded overflow-hidden img-drag-wrap">
                                            <div class="drag-handle bg-dark bg-opacity-50 text-white text-center py-1 img-drag-handle"><i class="bi bi-grip-horizontal"></i> Drag</div>
                                            <img src="{{ asset('public/assets/images/' . $image->image_path) }}" class="img-fluid img-drag-thumb" alt="">
                                            <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2 delete-image-btn img-delete-btn" data-id="{{ $image->id }}"><i class="bi bi-trash"></i></button>
                                            <div class="p-1"><span class="badge bg-info">{{ ucfirst($image->image_type) }}</span></div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                <div id="reorder-status" class="mt-2 d-none"></div>
                            </div>
                        </div>
                        @endif

                        <div class="card mb-4">
                            <div class="card-header"><h5 class="mb-0">Add New Images</h5></div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Upload Images</label>
                                    <input type="file" name="images[]" id="imageInput" class="form-control" multiple accept="image/*">
                                    <small class="text-muted">You can select multiple images. Accepted formats: JPG, PNG, GIF (Max 2MB each)</small>
                                </div>
                                <div id="imagePreviewContainer" class="row g-3"></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card mb-4">
                            <div class="card-header"><h5 class="mb-0">Publish</h5></div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Status <span class="text-danger">*</span></label>
                                    <select name="status" class="form-select" required>
                                        <option value="1" {{ old('status', $hotel->status) == '1' ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ old('status', $hotel->status) == '0' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>
                                <div class="d-grid"><button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Update Hotel</button></div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        @php
            $rtCreateUrl   = route('admin.hotels.room-types.create', ['hotel_id' => $hotel->id]);
            $rtEditRoute   = 'admin.hotels.room-types.edit';
            $rmCreateUrl   = route('admin.hotels.rooms.create', ['hotel_id' => $hotel->id]);
            $rmEditRoute   = 'admin.hotels.rooms.edit';
        @endphp

        <div class="tab-pane fade {{ request('tab') == 'room-types' ? 'show active' : '' }}" id="room-types">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Room Types — {{ $hotel->name }}</h5>
                <a href="{{ $rtCreateUrl }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle"></i> Add Room Type</a>
            </div>

            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr><th>Image</th><th>Room Type</th><th>Price/Night</th><th>Capacity</th><th>Beds</th><th>AC</th><th>Status</th><th>Actions</th></tr>
                            </thead>
                            <tbody>
                                @forelse($hotelRoomTypes as $roomType)
                                <tr>
                                    <td>
                                        @if($roomType->images->first())
                                            <img src="{{ asset('public/assets/images/' . $roomType->images->first()->image_path) }}" alt="" class="rounded roomtype-thumb-img">
                                        @else
                                            <div class="bg-light rounded d-flex align-items-center justify-content-center roomtype-thumb-placeholder"><i class="bi bi-door-open text-muted"></i></div>
                                        @endif
                                    </td>
                                    <td><strong>{{ $roomType->name }}</strong></td>
                                    <td><strong class="text-success">{{ number_format($roomType->price_per_night, 2) }}</strong></td>
                                    <td><small><i class="bi bi-people"></i> {{ $roomType->max_adults }} Adults @if($roomType->max_children > 0) / {{ $roomType->max_children }} Children @endif</small></td>
                                    <td><span class="badge bg-secondary">{{ $roomType->beds }} Bed(s)</span></td>
                                    <td>
                                        @if($roomType->ac)<i class="bi bi-snow text-primary"></i>@else<i class="bi bi-x-circle text-muted"></i>@endif
                                    </td>
                                    <td>
                                        @if($roomType->status)<span class="badge bg-success">Active</span>@else<span class="badge bg-secondary">Inactive</span>@endif
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route($rtEditRoute, $roomType) }}" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                                            <button type="button" class="btn btn-sm btn-outline-danger delete-rt-btn" data-id="{{ $roomType->id }}" title="Delete"><i class="bi bi-trash"></i></button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <i class="bi bi-door-open fs-1 text-muted d-block mb-2"></i>
                                        <p class="text-muted mb-3">No room types added yet</p>
                                        <a href="{{ $rtCreateUrl }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle"></i> Add First Room Type</a>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade {{ request('tab') == 'rooms' ? 'show active' : '' }}" id="rooms">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Rooms — {{ $hotel->name }}</h5>
                <a href="{{ $rmCreateUrl }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle"></i> Add Room</a>
            </div>

            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light"><tr><th>Room Number</th><th>Room Type</th><th>Floor</th><th>Status</th><th>Actions</th></tr></thead>
                            <tbody>
                                @forelse($hotelRooms as $room)
                                <tr>
                                    <td><strong>{{ $room->room_number }}</strong></td>
                                    <td>
                                        @if($room->roomType)<span class="badge bg-info">{{ $room->roomType->name }}</span>@else<span class="text-muted">-</span>@endif
                                    </td>
                                    <td>{{ $room->floor ?? '-' }}</td>
                                    <td>
                                        @if($room->status == 'available')<span class="badge bg-success">Available</span>
                                        @elseif($room->status == 'occupied')<span class="badge bg-danger">Occupied</span>
                                        @else<span class="badge bg-warning text-dark">Maintenance</span>@endif
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route($rmEditRoute, $room) }}" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                                            <button type="button" class="btn btn-sm btn-outline-danger delete-room-btn" data-id="{{ $room->id }}" title="Delete"><i class="bi bi-trash"></i></button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <i class="bi bi-key fs-1 text-muted d-block mb-2"></i>
                                        <p class="text-muted mb-3">No rooms added yet</p>
                                        <a href="{{ $rmCreateUrl }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle"></i> Add First Room</a>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const imageGrid = document.getElementById('existing-images-grid');
    if (imageGrid) {
        Sortable.create(imageGrid, {
            handle: '.drag-handle',
            animation: 150,
            ghostClass: 'opacity-50',
            onEnd: function() {
                const items = imageGrid.querySelectorAll('.image-item');
                const images = [];
                items.forEach((item, index) => { images.push({ id: parseInt(item.dataset.id), sort_order: index + 1 }); });

                const statusEl = document.getElementById('reorder-status');
                statusEl.style.display = 'block';
                statusEl.innerHTML = '<span class="text-muted"><i class="bi bi-hourglass-split"></i> Saving order...</span>';

                fetch('{{ route("admin.hotels.reorder-images") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({ images })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) statusEl.innerHTML = '<span class="text-success"><i class="bi bi-check-circle"></i> Order saved!</span>';
                    else statusEl.innerHTML = '<span class="text-danger"><i class="bi bi-x-circle"></i> Failed to save order.</span>';
                });
            }
        });
    }

    const imageInput = document.getElementById('imageInput');
    const previewContainer = document.getElementById('imagePreviewContainer');
    let selectedFiles = [];
    if (imageInput) {
        imageInput.addEventListener('change', function(e) {
            const files = Array.from(e.target.files);
            files.forEach((file) => {
                if (file.type.startsWith('image/')) {
                    selectedFiles.push(file);
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const fileIndex = selectedFiles.length - 1;
                        const previewDiv = document.createElement('div');
                        previewDiv.className = 'col-md-3';
                        previewDiv.id = `preview-${fileIndex}`;
                        previewDiv.innerHTML = `<div class="position-relative"><img src="${e.target.result}" class="img-fluid rounded preview-thumb"><button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2 remove-image" data-index="${fileIndex}"><i class="bi bi-x"></i></button></div>`;
                        previewContainer.appendChild(previewDiv);
                    };
                    reader.readAsDataURL(file);
                }
            });
            const dt = new DataTransfer();
            selectedFiles.forEach(file => dt.items.add(file));
            imageInput.files = dt.files;
        });
    }

    document.querySelectorAll('.delete-image-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const imageId = this.dataset.id;
            if (confirm('Are you sure you want to delete this image?')) {
                fetch(`{{ url('admin/hotels/image') }}/${imageId}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById(`image-${imageId}`).remove();
                        const alert = document.createElement('div');
                        alert.className = 'alert alert-success alert-dismissible fade show';
                        alert.innerHTML = `${data.message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
                        document.querySelector('.content-area').prepend(alert);
                        setTimeout(() => alert.remove(), 3000);
                    }
                })
                .catch(error => console.error('Error:', error));
            }
        });
    });

    document.querySelectorAll('.delete-rt-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            if (!confirm('Delete this room type?')) return;
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `{{ url('admin/hotels/room-types') }}/${this.dataset.id}`;
            form.innerHTML = `<input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').content}"><input type="hidden" name="_method" value="DELETE">`;
            document.body.appendChild(form);
            form.submit();
        });
    });

    document.querySelectorAll('.delete-room-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            if (!confirm('Delete this room?')) return;
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `{{ url('admin/hotels/rooms') }}/${this.dataset.id}`;
            form.innerHTML = `<input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').content}"><input type="hidden" name="_method" value="DELETE">`;
            document.body.appendChild(form);
            form.submit();
        });
    });
});
</script>
@endpush
