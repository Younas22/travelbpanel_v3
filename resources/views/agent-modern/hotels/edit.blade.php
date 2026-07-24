@extends('agent-modern.layouts.app')
@section('title', 'Edit Hotel')

@section('content')

    <div class="ap-page-header">
        <div>
            <h4 class="ap-page-title">Edit Hotel</h4>
            <p class="ap-page-sub">{{ $hotel->name }}</p>
        </div>
        <a href="{{ route('agent.hotels.index') }}" class="ap-btn-outline">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    <div class="ap-tab-bar">
        <button type="button" class="ap-tab-item active" data-tab="hotel-info"><i class="bi bi-building"></i> Hotel Info</button>
        <button type="button" class="ap-tab-item" data-tab="room-types"><i class="bi bi-door-open"></i> Room Types <span class="ap-tab-count">{{ $hotelRoomTypes->count() }}</span></button>
        <button type="button" class="ap-tab-item" data-tab="rooms"><i class="bi bi-key"></i> Rooms <span class="ap-tab-count">{{ $hotelRooms->count() }}</span></button>
    </div>

    {{-- ===== TAB: Hotel Info ===== --}}
    <div class="ap-tab-pane active" id="hotel-info">
    <form action="{{ route('agent.hotels.update', $hotel->id) }}" method="POST" enctype="multipart/form-data">
        @csrf @method('PUT')
        <div class="row g-4">

            <div class="col-lg-8">

                <div class="am-card mb-4">
                    <div class="am-card-header">Basic Information</div>
                    <div class="am-card-body">
                        <div class="mb-3">
                            <label class="form-label">Hotel Name <span style="color: var(--danger-color);">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $hotel->name) }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Location <span style="color: var(--danger-color);">*</span></label>
                                <select name="location_id" id="location_id" class="form-select location-select @error('location_id') is-invalid @enderror" required>
                                    <option value="">Search and select location...</option>
                                    @php
                                        $selectedLocationId = old('location_id', $hotel->location_id);
                                        $selectedLocation   = $selectedLocationId ? \App\Models\Location::find($selectedLocationId) : null;
                                    @endphp
                                    @if($selectedLocation)
                                        <option value="{{ $selectedLocation->id }}" selected>{{ $selectedLocation->city }}, {{ $selectedLocation->country }}</option>
                                    @endif
                                </select>
                                @error('location_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Type <span style="color: var(--danger-color);">*</span></label>
                                <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                                    <option value="">Select Type</option>
                                    <option value="hotel" {{ old('type', $hotel->type) == 'hotel' ? 'selected' : '' }}>Hotel</option>
                                    <option value="guest house" {{ old('type', $hotel->type) == 'guest house' ? 'selected' : '' }}>Guest House</option>
                                    <option value="resort" {{ old('type', $hotel->type) == 'resort' ? 'selected' : '' }}>Resort</option>
                                </select>
                                @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <input type="text" name="address" class="form-control" value="{{ old('address', $hotel->address) }}">
                        </div>
                        <div class="mb-0">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="4">{{ old('description', $hotel->description) }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="am-card mb-4">
                    <div class="am-card-header">Contact Details</div>
                    <div class="am-card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" class="form-control" value="{{ old('phone', $hotel->phone) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">WhatsApp</label>
                                <input type="text" name="whatsapp" class="form-control" value="{{ old('whatsapp', $hotel->whatsapp) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email', $hotel->email) }}">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="am-card mb-4">
                    <div class="am-card-header">Hotel Details</div>
                    <div class="am-card-body">
                        <div class="row g-3">
                            <div class="col-6 col-md-3">
                                <label class="form-label">Check-in Time</label>
                                <input type="time" name="check_in_time" class="form-control" value="{{ old('check_in_time', $hotel->check_in_time ? substr($hotel->check_in_time, 0, 5) : '') }}">
                            </div>
                            <div class="col-6 col-md-3">
                                <label class="form-label">Check-out Time</label>
                                <input type="time" name="check_out_time" class="form-control" value="{{ old('check_out_time', $hotel->check_out_time ? substr($hotel->check_out_time, 0, 5) : '') }}">
                            </div>
                            <div class="col-6 col-md-3">
                                <label class="form-label">Total Rooms</label>
                                <input type="number" name="total_rooms" class="form-control" value="{{ old('total_rooms', $hotel->total_rooms) }}" min="0">
                            </div>
                            <div class="col-6 col-md-3">
                                <label class="form-label">Stars</label>
                                <select name="stars" class="form-select">
                                    <option value="">Select</option>
                                    @for($i = 1; $i <= 5; $i++)
                                        <option value="{{ $i }}" {{ old('stars', $hotel->stars) == $i ? 'selected' : '' }}>{{ $i }} Star{{ $i > 1 ? 's' : '' }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                @if($amenities->count())
                <div class="am-card mb-4">
                    <div class="am-card-header">Amenities</div>
                    <div class="am-card-body">
                        @php $selectedAmenities = old('amenities', $hotel->amenities->pluck('id')->toArray()); @endphp
                        <div class="ap-checkbox-grid">
                            @foreach($amenities as $amenity)
                            <label class="ap-checkbox-item">
                                <input type="checkbox" name="amenities[]" value="{{ $amenity->id }}" id="am{{ $amenity->id }}" class="form-check-input"
                                       {{ in_array($amenity->id, $selectedAmenities) ? 'checked' : '' }}>
                                {{ $amenity->name }}
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                @if($hotel->images->count())
                <div class="am-card mb-4">
                    <div class="am-card-header d-flex align-items-center justify-content-between">
                        <span>Current Images</span>
                        <span style="font-size: 11px; font-weight: 500; color: color-mix(in srgb, var(--text-color) 50%, transparent);"><i class="bi bi-grip-horizontal"></i> Drag to reorder</span>
                    </div>
                    <div class="am-card-body">
                        <div class="ap-img-grid" id="existing-images-grid">
                            @foreach($hotel->images as $image)
                            <div class="ap-img-item" id="image-{{ $image->id }}" data-id="{{ $image->id }}">
                                <div class="ap-img-drag-handle"><i class="bi bi-grip-horizontal"></i></div>
                                <img src="{{ asset('public/assets/images/' . $image->image_path) }}">
                                <button type="button" class="ap-img-delete delete-image-btn" data-id="{{ $image->id }}">
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>
                            @endforeach
                        </div>
                        <div id="reorder-status" class="ap-reorder-status d-none"></div>
                    </div>
                </div>
                @endif

                <div class="am-card">
                    <div class="am-card-header">Add More Images</div>
                    <div class="am-card-body">
                        <label class="ap-upload-dropzone" for="imageInput">
                            <i class="bi bi-cloud-arrow-up"></i>
                            <span class="ap-upload-title">Click to upload images</span>
                            <span class="ap-upload-hint">JPG, PNG — max 2MB each</span>
                            <input type="file" name="images[]" id="imageInput" class="d-none" multiple accept="image/*">
                        </label>
                        <div id="imagePreviewContainer" class="ap-img-grid"></div>
                    </div>
                </div>

            </div>

            <div class="col-lg-4">
                <div class="am-card" style="position: sticky; top: 80px;">
                    <div class="am-card-header">Publish</div>
                    <div class="am-card-body">
                        <div class="mb-3">
                            <label class="form-label">Status <span style="color: var(--danger-color);">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="1" {{ old('status', $hotel->status) == '1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('status', $hotel->status) == '0' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        <button type="submit" class="ap-btn-primary w-100 justify-content-center">
                            <i class="bi bi-check-lg"></i> Update Hotel
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </form>
    </div>{{-- end hotel-info tab --}}

    {{-- ===== TAB: Room Types ===== --}}
    <div class="ap-tab-pane" id="room-types">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <p style="font-size: 13px; color: color-mix(in srgb, var(--text-color) 55%, transparent); margin: 0;">Room types for <strong>{{ $hotel->name }}</strong></p>
            <a href="{{ route('agent.hotels.room-types.create', ['hotel_id' => $hotel->id]) }}" class="ap-btn-primary">
                <i class="bi bi-plus-lg"></i> Add Room Type
            </a>
        </div>

        <div class="am-card">
            @if($hotelRoomTypes->isEmpty())
                <div class="am-empty">
                    <i class="bi bi-door-open"></i>
                    <h6>No room types yet</h6>
                    <p class="mb-3">Define room categories like Deluxe, Standard, Suite etc.</p>
                    <a href="{{ route('agent.hotels.room-types.create', ['hotel_id' => $hotel->id]) }}" class="ap-btn-primary">
                        <i class="bi bi-plus-lg"></i> Add First Room Type
                    </a>
                </div>
            @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Room Type</th>
                            <th>Price / Night</th>
                            <th>Capacity</th>
                            <th>Beds</th>
                            <th>AC</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($hotelRoomTypes as $roomType)
                        <tr>
                            <td>
                                @if($roomType->images->first())
                                    <img src="{{ asset('public/assets/images/' . $roomType->images->first()->image_path) }}" style="width: 46px; height: 46px; object-fit: cover; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
                                @else
                                    <div style="width: 46px; height: 46px; border-radius: var(--radius-md); border: 1px solid var(--border-color); display: flex; align-items: center; justify-content: center; color: color-mix(in srgb, var(--text-color) 30%, transparent);">
                                        <i class="bi bi-door-open"></i>
                                    </div>
                                @endif
                            </td>
                            <td style="font-weight: 650;">{{ $roomType->name }}</td>
                            <td style="font-weight: 650; color: var(--success-color);">{{ number_format($roomType->price_per_night, 2) }}</td>
                            <td style="font-size: 12px;">
                                <i class="bi bi-person"></i> {{ $roomType->max_adults }} Adults
                                @if($roomType->max_children > 0) / {{ $roomType->max_children }} Children @endif
                            </td>
                            <td><span class="badge bg-primary">{{ $roomType->beds }} Bed(s)</span></td>
                            <td>
                                @if($roomType->ac)
                                    <i class="bi bi-snow" style="color: var(--primary-color);"></i>
                                @else
                                    <i class="bi bi-x" style="color: color-mix(in srgb, var(--text-color) 30%, transparent);"></i>
                                @endif
                            </td>
                            <td>
                                @if($roomType->status)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('agent.hotels.room-types.edit', $roomType) }}" class="ap-btn-outline" style="padding: 6px 12px; font-size: 11.5px;">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                    <form method="POST" action="{{ route('agent.hotels.room-types.destroy', $roomType) }}" class="d-inline" onsubmit="return confirm('Delete this room type?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="ap-btn-danger" style="padding: 6px 12px; font-size: 11.5px;">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>{{-- end room-types tab --}}

    {{-- ===== TAB: Rooms ===== --}}
    <div class="ap-tab-pane" id="rooms">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <p style="font-size: 13px; color: color-mix(in srgb, var(--text-color) 55%, transparent); margin: 0;">Individual rooms for <strong>{{ $hotel->name }}</strong></p>
            <a href="{{ route('agent.hotels.rooms.create', ['hotel_id' => $hotel->id]) }}" class="ap-btn-primary">
                <i class="bi bi-plus-lg"></i> Add Room
            </a>
        </div>

        <div class="am-card">
            @if($hotelRooms->isEmpty())
                <div class="am-empty">
                    <i class="bi bi-key"></i>
                    <h6>No rooms yet</h6>
                    <p class="mb-3">Add individual room numbers under each room type.</p>
                    <a href="{{ route('agent.hotels.rooms.create', ['hotel_id' => $hotel->id]) }}" class="ap-btn-primary">
                        <i class="bi bi-plus-lg"></i> Add First Room
                    </a>
                </div>
            @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Room Number</th>
                            <th>Room Type</th>
                            <th>Floor</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($hotelRooms as $room)
                        <tr>
                            <td style="font-weight: 650;">{{ $room->room_number }}</td>
                            <td>
                                @if($room->roomType)
                                    <span class="badge bg-primary">{{ $room->roomType->name }}</span>
                                @else
                                    <span style="color: color-mix(in srgb, var(--text-color) 40%, transparent);">—</span>
                                @endif
                            </td>
                            <td>{{ $room->floor ?? '—' }}</td>
                            <td>
                                @if($room->status == 'available')
                                    <span class="badge bg-success"><i class="bi bi-check-lg"></i> Available</span>
                                @elseif($room->status == 'occupied')
                                    <span class="badge bg-danger"><i class="bi bi-x-lg"></i> Occupied</span>
                                @else
                                    <span class="badge bg-warning"><i class="bi bi-tools"></i> Maintenance</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('agent.hotels.rooms.edit', $room) }}" class="ap-btn-outline" style="padding: 6px 12px; font-size: 11.5px;">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                    <form method="POST" action="{{ route('agent.hotels.rooms.destroy', $room) }}" class="d-inline" onsubmit="return confirm('Delete this room?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="ap-btn-danger" style="padding: 6px 12px; font-size: 11.5px;">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>{{-- end rooms tab --}}

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Tab Switching ──────────────────────────────────
    const tabItems = document.querySelectorAll('.ap-tab-item[data-tab]');
    const tabPanes = document.querySelectorAll('.ap-tab-pane');

    function activateTab(tabId) {
        tabItems.forEach(t => t.classList.toggle('active', t.dataset.tab === tabId));
        tabPanes.forEach(p => p.classList.toggle('active', p.id === tabId));
        history.replaceState(null, '', '#' + tabId);
    }

    tabItems.forEach(btn => btn.addEventListener('click', () => activateTab(btn.dataset.tab)));

    const hash = location.hash.replace('#', '');
    if (hash && document.getElementById(hash)) activateTab(hash);

    // ── Image Drag & Drop Reorder ──────────────────────
    const imageGrid = document.getElementById('existing-images-grid');
    if (imageGrid && typeof Sortable !== 'undefined') {
        Sortable.create(imageGrid, {
            handle: '.ap-img-drag-handle',
            animation: 150,
            onEnd: function () {
                const items = imageGrid.querySelectorAll('.ap-img-item');
                const images = [];
                items.forEach((item, index) => {
                    images.push({ id: parseInt(item.dataset.id), sort_order: index + 1 });
                });
                const statusEl = document.getElementById('reorder-status');
                statusEl.classList.remove('d-none');
                statusEl.innerHTML = '<span style="color: color-mix(in srgb, var(--text-color) 55%, transparent);"><i class="bi bi-arrow-repeat"></i> Saving order...</span>';
                fetch('{{ route("agent.hotels.reorder-images") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({ images })
                })
                .then(r => r.json())
                .then(data => {
                    statusEl.innerHTML = data.success
                        ? '<span style="color: var(--success-color);"><i class="bi bi-check-circle"></i> Order saved!</span>'
                        : '<span style="color: var(--danger-color);"><i class="bi bi-x-circle"></i> Failed to save.</span>';
                    setTimeout(() => statusEl.classList.add('d-none'), 2500);
                })
                .catch(() => {
                    statusEl.innerHTML = '<span style="color: var(--danger-color);"><i class="bi bi-x-circle"></i> Error saving order.</span>';
                    setTimeout(() => statusEl.classList.add('d-none'), 2500);
                });
            }
        });
    }

    // ── Delete Existing Hotel Image ────────────────────
    document.querySelectorAll('.delete-image-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const imageId = this.dataset.id;
            if (!confirm('Delete this image?')) return;
            fetch(`{{ url('agent/hotels/image') }}/${imageId}`, {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) document.getElementById(`image-${imageId}`).remove();
            });
        });
    });

    // ── New Image Upload Preview ───────────────────────
    const imageInput       = document.getElementById('imageInput');
    const previewContainer = document.getElementById('imagePreviewContainer');
    let selectedFiles = [];

    imageInput.addEventListener('change', function (e) {
        Array.from(e.target.files).forEach(file => {
            if (file.type.startsWith('image/')) {
                selectedFiles.push(file);
                const reader = new FileReader();
                reader.onload = function (ev) {
                    const idx = selectedFiles.length - 1;
                    const div = document.createElement('div');
                    div.className = 'ap-img-item';
                    div.innerHTML = `<img src="${ev.target.result}">
                        <button type="button" class="ap-img-delete remove-img" data-idx="${idx}"><i class="bi bi-x"></i></button>`;
                    previewContainer.appendChild(div);
                };
                reader.readAsDataURL(file);
            }
        });
        syncInput();
    });

    previewContainer.addEventListener('click', function (e) {
        const btn = e.target.closest('.remove-img');
        if (btn) { selectedFiles.splice(parseInt(btn.dataset.idx), 1); syncInput(); renderAll(); }
    });

    function syncInput() {
        const dt = new DataTransfer();
        selectedFiles.forEach(f => dt.items.add(f));
        imageInput.files = dt.files;
    }

    function renderAll() {
        previewContainer.innerHTML = '';
        selectedFiles.forEach((file, idx) => {
            const reader = new FileReader();
            reader.onload = ev => {
                const div = document.createElement('div');
                div.className = 'ap-img-item';
                div.innerHTML = `<img src="${ev.target.result}">
                    <button type="button" class="ap-img-delete remove-img" data-idx="${idx}"><i class="bi bi-x"></i></button>`;
                previewContainer.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
    }

});

$(document).ready(function () {
    const fullPath = window.location.pathname.split('/');
    const baseFolder = fullPath[1];
    const API_BASE_URL = window.location.origin + "/" + baseFolder;

    function formatLocation(location) {
        if (location.loading) return location.text;
        return $('<div><strong>' + location.city + '</strong><br><small>' + location.country + '</small></div>');
    }
    function formatLocationSelection(location) {
        return location.city || location.text;
    }

    $('.location-select').select2({
        placeholder: 'Search and select location...',
        allowClear: true,
        minimumInputLength: 3,
        ajax: {
            url: API_BASE_URL + '/api/hotel_destinations',
            dataType: 'json',
            delay: 250,
            data: function(params) { return { search: params.term }; },
            processResults: function(data) {
                return {
                    results: data.data.map(function(item) {
                        return { id: item.id, text: item.city + ', ' + item.country, city: item.city, country: item.country, country_code: item.country_code };
                    })
                };
            },
            cache: true
        },
        templateResult: formatLocation,
        templateSelection: formatLocationSelection
    });
});
</script>
@endpush
