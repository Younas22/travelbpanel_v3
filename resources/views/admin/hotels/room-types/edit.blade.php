@extends($layout ?? 'admin.layouts.app')

@section('title', 'Edit Room Type')

@section('content')
<div class="content-area p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Edit Room Type</h4>
            <p class="text-muted mb-0">Update room type information</p>
        </div>
        <a href="{{ $backUrl ?? route('admin.hotels.room-types.index') }}" class="btn btn-secondary">
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

    <form action="{{ $formAction ?? route('admin.hotels.room-types.update', $roomType) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PATCH')
        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Basic Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Hotel <span class="text-danger">*</span></label>
                                <select name="hotel_id" class="form-select" required>
                                    <option value="">Select Hotel</option>
                                    @foreach($hotels as $hotel)
                                        <option value="{{ $hotel->id }}" {{ old('hotel_id', $roomType->hotel_id) == $hotel->id ? 'selected' : '' }}>
                                            {{ $hotel->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Room Type Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" value="{{ old('name', $roomType->name) }}" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="4">{{ old('description', $roomType->description) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Pricing & Capacity</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Price Per Night <span class="text-danger">*</span></label>
                                <input type="number" name="price_per_night" class="form-control" value="{{ old('price_per_night', $roomType->price_per_night) }}" step="0.01" min="0" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Number of Beds <span class="text-danger">*</span></label>
                                <input type="number" name="beds" class="form-control" value="{{ old('beds', $roomType->beds) }}" min="1" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Max Adults <span class="text-danger">*</span></label>
                                <input type="number" name="max_adults" class="form-control" value="{{ old('max_adults', $roomType->max_adults) }}" min="1" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Max Children <span class="text-danger">*</span></label>
                                <input type="number" name="max_children" class="form-control" value="{{ old('max_children', $roomType->max_children) }}" min="0" required>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Amenities</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            @php
                                $selectedAmenities = old('amenities', $roomType->amenities->pluck('id')->toArray());
                            @endphp
                            @foreach($amenities as $amenity)
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="amenities[]" value="{{ $amenity->id }}" id="amenity{{ $amenity->id }}" {{ in_array($amenity->id, $selectedAmenities) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="amenity{{ $amenity->id }}">
                                        {{ $amenity->name }}
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                @if($roomType->images->count() > 0)
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Existing Images</h5>
                        <small class="text-muted"><i class="bi bi-arrows-move"></i> Drag to reorder</small>
                    </div>
                    <div class="card-body">
                        <div class="row g-3" id="existing-images-grid">
                            @foreach($roomType->images as $image)
                            <div class="col-md-3 image-item" id="image-{{ $image->id }}" data-id="{{ $image->id }}">
                                <div class="position-relative border rounded overflow-hidden img-drag-wrap">
                                    <div class="drag-handle bg-dark bg-opacity-50 text-white text-center py-1 img-drag-handle">
                                        <i class="bi bi-grip-horizontal"></i> Drag
                                    </div>
                                    <img src="{{ asset('public/assets/images/' . $image->image_path) }}" class="img-fluid img-drag-thumb" alt="">
                                    <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2 delete-image-btn img-delete-btn" data-id="{{ $image->id }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <div id="reorder-status" class="mt-2 d-none"></div>
                    </div>
                </div>
                @endif

                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Add New Images</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Upload Images</label>
                            <input type="file" name="images[]" id="imageInput" class="form-control" multiple accept="image/*">
                            <small class="text-muted">Max 2MB each</small>
                        </div>
                        <div id="imagePreviewContainer" class="row g-3"></div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Settings</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Air Conditioning <span class="text-danger">*</span></label>
                            <select name="ac" class="form-select" required>
                                <option value="1" {{ old('ac', $roomType->ac) == '1' ? 'selected' : '' }}>Yes</option>
                                <option value="0" {{ old('ac', $roomType->ac) == '0' ? 'selected' : '' }}>No</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="1" {{ old('status', $roomType->status) == '1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('status', $roomType->status) == '0' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Update Room Type
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {

    // Existing Images Drag & Drop Reorder
    const imageGrid = document.getElementById('existing-images-grid');
    if (imageGrid) {
        Sortable.create(imageGrid, {
            handle: '.drag-handle',
            animation: 150,
            ghostClass: 'opacity-50',
            onEnd: function() {
                const items = imageGrid.querySelectorAll('.image-item');
                const images = [];
                items.forEach((item, index) => {
                    images.push({ id: parseInt(item.dataset.id), sort_order: index + 1 });
                });

                const statusEl = document.getElementById('reorder-status');
                statusEl.style.display = 'block';
                statusEl.innerHTML = '<span class="text-muted"><i class="bi bi-hourglass-split"></i> Saving order...</span>';

                fetch('{{ route("admin.hotels.room-types.reorder-images") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ images })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        statusEl.innerHTML = '<span class="text-success"><i class="bi bi-check-circle"></i> Order saved!</span>';
                    } else {
                        statusEl.innerHTML = '<span class="text-danger"><i class="bi bi-x-circle"></i> Failed to save order.</span>';
                    }
                    setTimeout(() => { statusEl.style.display = 'none'; }, 2500);
                })
                .catch(() => {
                    statusEl.innerHTML = '<span class="text-danger"><i class="bi bi-x-circle"></i> Error saving order.</span>';
                    setTimeout(() => { statusEl.style.display = 'none'; }, 2500);
                });
            }
        });
    }
    const imageInput = document.getElementById('imageInput');
    const previewContainer = document.getElementById('imagePreviewContainer');
    let selectedFiles = [];

    imageInput.addEventListener('change', function(e) {
        const files = Array.from(e.target.files);
        files.forEach((file, index) => {
            if (file.type.startsWith('image/')) {
                selectedFiles.push(file);
                const reader = new FileReader();
                reader.onload = function(e) {
                    const fileIndex = selectedFiles.length - 1;
                    const previewDiv = document.createElement('div');
                    previewDiv.className = 'col-md-4';
                    previewDiv.id = `preview-${fileIndex}`;
                    previewDiv.innerHTML = `
                        <div class="position-relative">
                            <img src="${e.target.result}" class="img-fluid rounded preview-thumb">
                            <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2 remove-image" data-index="${fileIndex}">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                    `;
                    previewContainer.appendChild(previewDiv);
                };
                reader.readAsDataURL(file);
            }
        });
        updateFileInput();
    });

    previewContainer.addEventListener('click', function(e) {
        if (e.target.closest('.remove-image')) {
            const index = parseInt(e.target.closest('.remove-image').dataset.index);
            selectedFiles.splice(index, 1);
            updateFileInput();
            renderPreviews();
        }
    });

    function updateFileInput() {
        const dt = new DataTransfer();
        selectedFiles.forEach(file => dt.items.add(file));
        imageInput.files = dt.files;
    }

    function renderPreviews() {
        previewContainer.innerHTML = '';
        selectedFiles.forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const previewDiv = document.createElement('div');
                previewDiv.className = 'col-md-4';
                previewDiv.innerHTML = `
                    <div class="position-relative">
                        <img src="${e.target.result}" class="img-fluid rounded preview-thumb">
                        <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2 remove-image" data-index="${index}">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                `;
                previewContainer.appendChild(previewDiv);
            };
            reader.readAsDataURL(file);
        });
    }

    // Delete existing images
    document.querySelectorAll('.delete-image-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const imageId = this.dataset.id;
            if (confirm('Are you sure you want to delete this image?')) {
                fetch(`{{ url('admin/hotels/room-types/image') }}/${imageId}`, {
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
                    }
                });
            }
        });
    });
});
</script>
@endpush
@endsection
