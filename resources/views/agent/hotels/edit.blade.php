@extends('agent.layouts.app')
@section('title', 'Edit Hotel')

@section('content')

{{-- Page Header --}}
<div class="flex items-center justify-between mb-5">
    <div>
        <h4 class="text-lg font-bold text-gray-800">Edit Hotel</h4>
        <p class="text-xs text-gray-400 mt-0.5">{{ $hotel->name }}</p>
    </div>
    <a href="{{ route('agent.hotels.index') }}" class="ap-btn-outline">
        <i class="fas fa-arrow-left text-xs"></i> Back
    </a>
</div>

{{-- Tab Navigation --}}
<div class="ap-tab-bar">
    <button class="ap-tab-item active" data-tab="hotel-info">
        <i class="fas fa-building"></i> Hotel Info
    </button>
    <button class="ap-tab-item" data-tab="room-types">
        <i class="fas fa-door-open"></i> Room Types
        <span class="ap-tab-count">{{ $hotelRoomTypes->count() }}</span>
    </button>
    <button class="ap-tab-item" data-tab="rooms">
        <i class="fas fa-key"></i> Rooms
        <span class="ap-tab-count">{{ $hotelRooms->count() }}</span>
    </button>
</div>

{{-- ===== TAB 1: Hotel Info ===== --}}
<div class="ap-tab-pane active" id="hotel-info">
<form action="{{ route('agent.hotels.update', $hotel->id) }}" method="POST" enctype="multipart/form-data">
    @csrf @method('PUT')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- Left: Main Fields --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Basic Information --}}
            <div class="ap-card">
                <div class="ap-card-header">Basic Information</div>
                <div class="ap-card-body">
                    <div class="grid grid-cols-1 gap-4">

                        <div>
                            <label class="ap-label">Hotel Name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" class="ap-input @error('name') ap-input-error @enderror"
                                   value="{{ old('name', $hotel->name) }}" required>
                            @error('name')<p class="ap-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="ap-label">Location <span class="text-red-500">*</span></label>
                                <select name="location_id" id="location_id" class="ap-input location-select @error('location_id') ap-input-error @enderror" required>
                                    <option value="">Search and select location...</option>
                                    @php
                                        $selectedLocationId = old('location_id', $hotel->location_id);
                                        $selectedLocation   = $selectedLocationId ? \App\Models\Location::find($selectedLocationId) : null;
                                    @endphp
                                    @if($selectedLocation)
                                        <option value="{{ $selectedLocation->id }}" selected>{{ $selectedLocation->city }}, {{ $selectedLocation->country }}</option>
                                    @endif
                                </select>
                                @error('location_id')<p class="ap-error">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="ap-label">Type <span class="text-red-500">*</span></label>
                                <select name="type" class="ap-input @error('type') ap-input-error @enderror" required>
                                    <option value="">Select Type</option>
                                    <option value="hotel"       {{ old('type', $hotel->type) == 'hotel'       ? 'selected' : '' }}>Hotel</option>
                                    <option value="guest house" {{ old('type', $hotel->type) == 'guest house' ? 'selected' : '' }}>Guest House</option>
                                    <option value="resort"      {{ old('type', $hotel->type) == 'resort'      ? 'selected' : '' }}>Resort</option>
                                </select>
                                @error('type')<p class="ap-error">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div>
                            <label class="ap-label">Address</label>
                            <input type="text" name="address" class="ap-input" value="{{ old('address', $hotel->address) }}">
                        </div>

                        <div>
                            <label class="ap-label">Description</label>
                            <textarea name="description" class="ap-input" rows="4">{{ old('description', $hotel->description) }}</textarea>
                        </div>

                    </div>
                </div>
            </div>

            {{-- Contact --}}
            <div class="ap-card">
                <div class="ap-card-header">Contact Details</div>
                <div class="ap-card-body">
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="ap-label">Phone</label>
                            <input type="text" name="phone" class="ap-input" value="{{ old('phone', $hotel->phone) }}">
                        </div>
                        <div>
                            <label class="ap-label">WhatsApp</label>
                            <input type="text" name="whatsapp" class="ap-input" value="{{ old('whatsapp', $hotel->whatsapp) }}">
                        </div>
                        <div>
                            <label class="ap-label">Email</label>
                            <input type="email" name="email" class="ap-input" value="{{ old('email', $hotel->email) }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Hotel Details --}}
            <div class="ap-card">
                <div class="ap-card-header">Hotel Details</div>
                <div class="ap-card-body">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <label class="ap-label">Check-in Time</label>
                            <input type="time" name="check_in_time" class="ap-input" value="{{ old('check_in_time', $hotel->check_in_time ? substr($hotel->check_in_time, 0, 5) : '') }}">
                        </div>
                        <div>
                            <label class="ap-label">Check-out Time</label>
                            <input type="time" name="check_out_time" class="ap-input" value="{{ old('check_out_time', $hotel->check_out_time ? substr($hotel->check_out_time, 0, 5) : '') }}">
                        </div>
                        <div>
                            <label class="ap-label">Total Rooms</label>
                            <input type="number" name="total_rooms" class="ap-input" value="{{ old('total_rooms', $hotel->total_rooms) }}" min="0">
                        </div>
                        <div>
                            <label class="ap-label">Stars</label>
                            <select name="stars" class="ap-input">
                                <option value="">Select</option>
                                @for($i = 1; $i <= 5; $i++)
                                    <option value="{{ $i }}" {{ old('stars', $hotel->stars) == $i ? 'selected' : '' }}>{{ $i }} Star{{ $i > 1 ? 's' : '' }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Amenities --}}
            @if($amenities->count())
            <div class="ap-card">
                <div class="ap-card-header">Amenities</div>
                <div class="ap-card-body">
                    @php $selectedAmenities = old('amenities', $hotel->amenities->pluck('id')->toArray()); @endphp
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        @foreach($amenities as $amenity)
                        <label class="flex items-center gap-2.5 cursor-pointer group">
                            <input type="checkbox" name="amenities[]" value="{{ $amenity->id }}" id="am{{ $amenity->id }}"
                                   class="ap-checkbox"
                                   {{ in_array($amenity->id, $selectedAmenities) ? 'checked' : '' }}>
                            <span class="text-sm text-gray-700 group-hover:text-blue-600 transition">{{ $amenity->name }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            {{-- Existing Images --}}
            @if($hotel->images->count())
            <div class="ap-card">
                <div class="ap-card-header flex items-center justify-between">
                    <span>Current Images</span>
                    <span class="text-xs text-gray-400 font-normal"><i class="fas fa-grip-horizontal"></i> Drag to reorder</span>
                </div>
                <div class="ap-card-body">
                    <div class="grid grid-cols-3 md:grid-cols-4 gap-3" id="existing-images-grid">
                        @foreach($hotel->images as $image)
                        <div class="relative group image-item" id="image-{{ $image->id }}" data-id="{{ $image->id }}">
                            <div class="drag-handle absolute top-0 left-0 right-0 bg-black/40 text-white text-center py-1 text-xs rounded-t-lg opacity-0 group-hover:opacity-100 transition cursor-grab z-10">
                                <i class="fas fa-grip-horizontal"></i>
                            </div>
                            <img src="{{ asset('public/assets/images/' . $image->image_path) }}"
                                 class="w-full h-24 object-cover rounded-lg border border-gray-200">
                            <button type="button"
                                    class="delete-image-btn absolute top-1 right-1 w-6 h-6 bg-red-500 text-white rounded-full text-xs flex items-center justify-center opacity-0 group-hover:opacity-100 transition z-10"
                                    data-id="{{ $image->id }}">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        @endforeach
                    </div>
                    <div id="reorder-status" class="mt-2 text-xs hidden"></div>
                </div>
            </div>
            @endif

            {{-- New Images --}}
            <div class="ap-card">
                <div class="ap-card-header">Add More Images</div>
                <div class="ap-card-body">
                    <label class="ap-file-label" for="imageInput">
                        <i class="fas fa-cloud-upload-alt text-blue-400 text-2xl"></i>
                        <span class="text-sm font-medium text-gray-600 mt-2">Click to upload images</span>
                        <span class="text-xs text-gray-400">JPG, PNG — max 2MB each</span>
                        <input type="file" name="images[]" id="imageInput" class="hidden" multiple accept="image/*">
                    </label>
                    <div id="imagePreviewContainer" class="grid grid-cols-3 md:grid-cols-4 gap-3 mt-3"></div>
                </div>
            </div>

        </div>

        {{-- Right: Publish --}}
        <div class="lg:col-span-1">
            <div class="ap-card sticky top-4">
                <div class="ap-card-header">Publish</div>
                <div class="ap-card-body space-y-4">
                    <div>
                        <label class="ap-label">Status <span class="text-red-500">*</span></label>
                        <select name="status" class="ap-input" required>
                            <option value="1" {{ old('status', $hotel->status) == '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('status', $hotel->status) == '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <button type="submit" class="ap-btn-primary w-full justify-center">
                        <i class="fas fa-check text-xs"></i> Update Hotel
                    </button>
                </div>
            </div>
        </div>

    </div>
</form>
</div>{{-- end hotel-info tab --}}

{{-- ===== TAB 2: Room Types ===== --}}
<div class="ap-tab-pane" id="room-types">
    <div class="flex items-center justify-between mb-4">
        <p class="text-sm text-gray-500">Room types for <strong>{{ $hotel->name }}</strong></p>
        <a href="{{ route('agent.hotels.room-types.create', ['hotel_id' => $hotel->id]) }}" class="ap-btn-primary">
            <i class="fas fa-plus text-xs"></i> Add Room Type
        </a>
    </div>

    <div class="ap-card">
        @if($hotelRoomTypes->isEmpty())
            <div class="text-center py-14">
                <div class="w-14 h-14 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-door-open text-blue-400 text-xl"></i>
                </div>
                <p class="text-gray-500 font-medium text-sm">No room types yet</p>
                <p class="text-gray-400 text-xs mt-1 mb-4">Define room categories like Deluxe, Standard, Suite etc.</p>
                <a href="{{ route('agent.hotels.room-types.create', ['hotel_id' => $hotel->id]) }}" class="ap-btn-primary">
                    <i class="fas fa-plus text-xs"></i> Add First Room Type
                </a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="ap-table">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Room Type</th>
                            <th>Price / Night</th>
                            <th>Capacity</th>
                            <th>Beds</th>
                            <th>AC</th>
                            <th>Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($hotelRoomTypes as $roomType)
                        <tr>
                            <td>
                                @if($roomType->images->first())
                                    <img src="{{ asset('public/assets/images/' . $roomType->images->first()->image_path) }}"
                                         class="w-12 h-12 object-cover rounded-lg border border-gray-100">
                                @else
                                    <div class="w-12 h-12 bg-gray-50 rounded-lg border border-gray-100 flex items-center justify-center">
                                        <i class="fas fa-door-open text-gray-300"></i>
                                    </div>
                                @endif
                            </td>
                            <td class="font-semibold text-gray-800">{{ $roomType->name }}</td>
                            <td>
                                <span class="font-semibold text-green-600">{{ number_format($roomType->price_per_night, 2) }}</span>
                            </td>
                            <td class="text-gray-600 text-xs">
                                <i class="fas fa-user text-gray-400"></i> {{ $roomType->max_adults }} Adults
                                @if($roomType->max_children > 0)
                                    / {{ $roomType->max_children }} Children
                                @endif
                            </td>
                            <td>
                                <span class="ap-badge ap-badge-blue">{{ $roomType->beds }} Bed(s)</span>
                            </td>
                            <td>
                                @if($roomType->ac)
                                    <i class="fas fa-snowflake text-blue-500 text-sm"></i>
                                @else
                                    <i class="fas fa-times text-gray-300 text-sm"></i>
                                @endif
                            </td>
                            <td>
                                @if($roomType->status)
                                    <span class="ap-badge ap-badge-success">Active</span>
                                @else
                                    <span class="ap-badge" style="background:#f1f5f9;color:#64748b;">Inactive</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('agent.hotels.room-types.edit', $roomType) }}" class="ap-btn-outline-sm">
                                        <i class="fas fa-pencil text-xs"></i> Edit
                                    </a>
                                    <form method="POST" action="{{ route('agent.hotels.room-types.destroy', $roomType) }}" class="inline"
                                          onsubmit="return confirm('Delete this room type?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="ap-btn-danger-sm">
                                            <i class="fas fa-trash text-xs"></i>
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

{{-- ===== TAB 3: Rooms ===== --}}
<div class="ap-tab-pane" id="rooms">
    <div class="flex items-center justify-between mb-4">
        <p class="text-sm text-gray-500">Individual rooms for <strong>{{ $hotel->name }}</strong></p>
        <a href="{{ route('agent.hotels.rooms.create', ['hotel_id' => $hotel->id]) }}" class="ap-btn-primary">
            <i class="fas fa-plus text-xs"></i> Add Room
        </a>
    </div>

    <div class="ap-card">
        @if($hotelRooms->isEmpty())
            <div class="text-center py-14">
                <div class="w-14 h-14 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-key text-blue-400 text-xl"></i>
                </div>
                <p class="text-gray-500 font-medium text-sm">No rooms yet</p>
                <p class="text-gray-400 text-xs mt-1 mb-4">Add individual room numbers under each room type.</p>
                <a href="{{ route('agent.hotels.rooms.create', ['hotel_id' => $hotel->id]) }}" class="ap-btn-primary">
                    <i class="fas fa-plus text-xs"></i> Add First Room
                </a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="ap-table">
                    <thead>
                        <tr>
                            <th>Room Number</th>
                            <th>Room Type</th>
                            <th>Floor</th>
                            <th>Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($hotelRooms as $room)
                        <tr>
                            <td class="font-semibold text-gray-800">{{ $room->room_number }}</td>
                            <td>
                                @if($room->roomType)
                                    <span class="ap-badge ap-badge-blue">{{ $room->roomType->name }}</span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="text-gray-600">{{ $room->floor ?? '—' }}</td>
                            <td>
                                @if($room->status == 'available')
                                    <span class="ap-badge ap-badge-success"><i class="fas fa-check text-xs"></i> Available</span>
                                @elseif($room->status == 'occupied')
                                    <span class="ap-badge ap-badge-danger"><i class="fas fa-times text-xs"></i> Occupied</span>
                                @else
                                    <span class="ap-badge ap-badge-warning"><i class="fas fa-tools text-xs"></i> Maintenance</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('agent.hotels.rooms.edit', $room) }}" class="ap-btn-outline-sm">
                                        <i class="fas fa-pencil text-xs"></i> Edit
                                    </a>
                                    <form method="POST" action="{{ route('agent.hotels.rooms.destroy', $room) }}" class="inline"
                                          onsubmit="return confirm('Delete this room?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="ap-btn-danger-sm">
                                            <i class="fas fa-trash text-xs"></i>
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

@push('styles')
<style>
.select2-results__option .location-country-text { color: #9ca3af; }
.select2-results__option--highlighted .location-country-text { color: rgba(255,255,255,.85); }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Tab Switching ──────────────────────────────────
    const tabItems  = document.querySelectorAll('.ap-tab-item');
    const tabPanes  = document.querySelectorAll('.ap-tab-pane');

    function activateTab(tabId) {
        tabItems.forEach(t => t.classList.toggle('active', t.dataset.tab === tabId));
        tabPanes.forEach(p => p.classList.toggle('active', p.id === tabId));
        history.replaceState(null, '', '#' + tabId);
    }

    tabItems.forEach(btn => btn.addEventListener('click', () => activateTab(btn.dataset.tab)));

    // Activate tab from URL hash on load
    const hash = location.hash.replace('#', '');
    if (hash && document.getElementById(hash)) activateTab(hash);

    // ── Image Drag & Drop Reorder ──────────────────────
    const imageGrid = document.getElementById('existing-images-grid');
    if (imageGrid && typeof Sortable !== 'undefined') {
        Sortable.create(imageGrid, {
            handle: '.drag-handle',
            animation: 150,
            onEnd: function () {
                const items = imageGrid.querySelectorAll('.image-item');
                const images = [];
                items.forEach((item, index) => {
                    images.push({ id: parseInt(item.dataset.id), sort_order: index + 1 });
                });
                const statusEl = document.getElementById('reorder-status');
                statusEl.classList.remove('hidden');
                statusEl.innerHTML = '<span class="text-gray-500"><i class="fas fa-circle-notch fa-spin"></i> Saving order...</span>';
                fetch('{{ route("agent.hotels.reorder-images") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({ images })
                })
                .then(r => r.json())
                .then(data => {
                    statusEl.innerHTML = data.success
                        ? '<span class="text-green-600"><i class="fas fa-check-circle"></i> Order saved!</span>'
                        : '<span class="text-red-500"><i class="fas fa-times-circle"></i> Failed to save.</span>';
                    setTimeout(() => statusEl.classList.add('hidden'), 2500);
                })
                .catch(() => {
                    statusEl.innerHTML = '<span class="text-red-500"><i class="fas fa-times-circle"></i> Error saving order.</span>';
                    setTimeout(() => statusEl.classList.add('hidden'), 2500);
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
    const imageInput      = document.getElementById('imageInput');
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
                    div.className = 'relative';
                    div.innerHTML = `<img src="${ev.target.result}" class="w-full h-24 object-cover rounded-lg border border-gray-200">
                        <button type="button" class="absolute top-1 right-1 w-6 h-6 bg-red-500 text-white rounded-full text-xs flex items-center justify-center remove-img" data-idx="${idx}"><i class="fas fa-times"></i></button>`;
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
                div.className = 'relative';
                div.innerHTML = `<img src="${ev.target.result}" class="w-full h-24 object-cover rounded-lg border border-gray-200">
                    <button type="button" class="absolute top-1 right-1 w-6 h-6 bg-red-500 text-white rounded-full text-xs flex items-center justify-center remove-img" data-idx="${idx}"><i class="fas fa-times"></i></button>`;
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
        return $('<div><strong>' + location.city + '</strong><br><small class="location-country-text">' + location.country + '</small></div>');
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

@endsection
