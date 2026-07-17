@extends('agent.layouts.app')
@section('title', 'Edit Room Type')

@section('content')

{{-- Page Header --}}
<div class="flex items-center justify-between mb-5">
    <div>
        <h4 class="text-lg font-bold text-gray-800">Edit Room Type</h4>
        <p class="text-xs text-gray-400 mt-0.5">{{ $roomType->name }} — {{ $roomType->hotel->name ?? '' }}</p>
    </div>
    <a href="{{ route('agent.hotels.edit', $roomType->hotel_id) }}#room-types" class="ap-btn-outline">
        <i class="fas fa-arrow-left text-xs"></i> Back
    </a>
</div>

<form action="{{ route('agent.hotels.room-types.update', $roomType) }}" method="POST" enctype="multipart/form-data">
    @csrf @method('PATCH')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- Left: Main Fields --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Basic Information --}}
            <div class="ap-card">
                <div class="ap-card-header">Basic Information</div>
                <div class="ap-card-body">
                    <div class="grid grid-cols-1 gap-4">

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="ap-label">Hotel <span class="text-red-500">*</span></label>
                                <select name="hotel_id" class="ap-input @error('hotel_id') ap-input-error @enderror" required>
                                    <option value="">Select Hotel</option>
                                    @foreach($hotels as $hotel)
                                        <option value="{{ $hotel->id }}" {{ old('hotel_id', $roomType->hotel_id) == $hotel->id ? 'selected' : '' }}>
                                            {{ $hotel->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('hotel_id')<p class="ap-error">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="ap-label">Room Type Name <span class="text-red-500">*</span></label>
                                <input type="text" name="name" class="ap-input @error('name') ap-input-error @enderror"
                                       value="{{ old('name', $roomType->name) }}" placeholder="e.g. Deluxe, Suite" required>
                                @error('name')<p class="ap-error">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div>
                            <label class="ap-label">Description</label>
                            <textarea name="description" class="ap-input" rows="3" placeholder="Brief description...">{{ old('description', $roomType->description) }}</textarea>
                        </div>

                    </div>
                </div>
            </div>

            {{-- Pricing & Capacity --}}
            <div class="ap-card">
                <div class="ap-card-header">Pricing & Capacity</div>
                <div class="ap-card-body">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <label class="ap-label">Price / Night <span class="text-red-500">*</span></label>
                            <input type="number" name="price_per_night" class="ap-input @error('price_per_night') ap-input-error @enderror"
                                   value="{{ old('price_per_night', $roomType->price_per_night) }}"
                                   step="0.01" min="0" placeholder="0.00" required>
                            @error('price_per_night')<p class="ap-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="ap-label">Beds <span class="text-red-500">*</span></label>
                            <input type="number" name="beds" class="ap-input @error('beds') ap-input-error @enderror"
                                   value="{{ old('beds', $roomType->beds) }}" min="1" required>
                            @error('beds')<p class="ap-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="ap-label">Max Adults <span class="text-red-500">*</span></label>
                            <input type="number" name="max_adults" class="ap-input @error('max_adults') ap-input-error @enderror"
                                   value="{{ old('max_adults', $roomType->max_adults) }}" min="1" required>
                            @error('max_adults')<p class="ap-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="ap-label">Max Children <span class="text-red-500">*</span></label>
                            <input type="number" name="max_children" class="ap-input @error('max_children') ap-input-error @enderror"
                                   value="{{ old('max_children', $roomType->max_children) }}" min="0" required>
                            @error('max_children')<p class="ap-error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Amenities --}}
            @if($amenities->count())
            <div class="ap-card">
                <div class="ap-card-header">Amenities</div>
                <div class="ap-card-body">
                    @php $selectedAmenities = old('amenities', $roomType->amenities->pluck('id')->toArray()); @endphp
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
            @if($roomType->images->count())
            <div class="ap-card">
                <div class="ap-card-header flex items-center justify-between">
                    <span>Current Images</span>
                    <span class="text-xs text-gray-400 font-normal"><i class="fas fa-grip-horizontal"></i> Drag to reorder</span>
                </div>
                <div class="ap-card-body">
                    <div class="grid grid-cols-3 md:grid-cols-4 gap-3" id="existing-images-grid">
                        @foreach($roomType->images as $image)
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
                <div class="ap-card-header">Add Images</div>
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

        {{-- Right: Settings --}}
        <div class="lg:col-span-1">
            <div class="ap-card sticky top-4">
                <div class="ap-card-header">Settings</div>
                <div class="ap-card-body space-y-4">
                    <div>
                        <label class="ap-label">Air Conditioning <span class="text-red-500">*</span></label>
                        <select name="ac" class="ap-input" required>
                            <option value="1" {{ old('ac', $roomType->ac) == '1' ? 'selected' : '' }}>Yes</option>
                            <option value="0" {{ old('ac', $roomType->ac) == '0' ? 'selected' : '' }}>No</option>
                        </select>
                    </div>
                    <div>
                        <label class="ap-label">Status <span class="text-red-500">*</span></label>
                        <select name="status" class="ap-input" required>
                            <option value="1" {{ old('status', $roomType->status) == '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('status', $roomType->status) == '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <button type="submit" class="ap-btn-primary w-full justify-center">
                        <i class="fas fa-check text-xs"></i> Update Room Type
                    </button>
                </div>
            </div>
        </div>

    </div>
</form>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Drag & Drop Image Reorder ──────────────────────
    const imageGrid = document.getElementById('existing-images-grid');
    if (imageGrid && typeof Sortable !== 'undefined') {
        Sortable.create(imageGrid, {
            handle: '.drag-handle',
            animation: 150,
            onEnd: function () {
                const images = [];
                imageGrid.querySelectorAll('.image-item').forEach((item, index) => {
                    images.push({ id: parseInt(item.dataset.id), sort_order: index + 1 });
                });
                const statusEl = document.getElementById('reorder-status');
                statusEl.classList.remove('hidden');
                statusEl.innerHTML = '<span class="text-gray-500"><i class="fas fa-circle-notch fa-spin"></i> Saving order...</span>';
                fetch('{{ route("agent.hotels.room-types.reorder-images") }}', {
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
                });
            }
        });
    }

    // ── Delete Image ───────────────────────────────────
    document.querySelectorAll('.delete-image-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            if (!confirm('Delete this image?')) return;
            const imageId = this.dataset.id;
            fetch(`{{ url('agent/hotels/room-types/image') }}/${imageId}`, {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
            })
            .then(r => r.json())
            .then(data => { if (data.success) document.getElementById(`image-${imageId}`).remove(); });
        });
    });

    // ── New Image Preview ──────────────────────────────
    const imageInput       = document.getElementById('imageInput');
    const previewContainer = document.getElementById('imagePreviewContainer');
    let selectedFiles = [];

    imageInput.addEventListener('change', function (e) {
        Array.from(e.target.files).forEach(file => {
            if (!file.type.startsWith('image/')) return;
            selectedFiles.push(file);
            const reader = new FileReader();
            reader.onload = ev => {
                const idx = selectedFiles.length - 1;
                const div = document.createElement('div');
                div.className = 'relative';
                div.innerHTML = `<img src="${ev.target.result}" class="w-full h-24 object-cover rounded-lg border border-gray-200">
                    <button type="button" class="absolute top-1 right-1 w-6 h-6 bg-red-500 text-white rounded-full text-xs flex items-center justify-center remove-img" data-idx="${idx}"><i class="fas fa-times"></i></button>`;
                previewContainer.appendChild(div);
            };
            reader.readAsDataURL(file);
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
</script>
@endpush

@endsection
