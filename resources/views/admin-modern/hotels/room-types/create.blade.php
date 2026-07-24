@extends('admin-modern.layouts.app')

@section('title', 'Create Room Type')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Create Room Type</h4>
            <p class="text-muted mb-0">Add a new room type</p>
        </div>
        <a href="{{ route('admin.hotels.room-types.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back to List</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <form action="{{ route('admin.hotels.room-types.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header"><h5 class="mb-0">Basic Information</h5></div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Hotel <span class="text-danger">*</span></label>
                                <select name="hotel_id" class="form-select" required>
                                    <option value="">Select Hotel</option>
                                    @foreach($hotels as $hotel)
                                        <option value="{{ $hotel->id }}" {{ request()->input('hotel_id', old('hotel_id')) == $hotel->id ? 'selected' : '' }}>{{ $hotel->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6"><label class="form-label">Room Type Name <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="e.g., Deluxe Room" required></div>
                            <div class="col-md-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="4">{{ old('description') }}</textarea></div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header"><h5 class="mb-0">Pricing & Capacity</h5></div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6"><label class="form-label">Price Per Night <span class="text-danger">*</span></label><input type="number" name="price_per_night" class="form-control" value="{{ old('price_per_night') }}" step="0.01" min="0" required></div>
                            <div class="col-md-6"><label class="form-label">Number of Beds <span class="text-danger">*</span></label><input type="number" name="beds" class="form-control" value="{{ old('beds', 1) }}" min="1" required></div>
                            <div class="col-md-6"><label class="form-label">Max Adults <span class="text-danger">*</span></label><input type="number" name="max_adults" class="form-control" value="{{ old('max_adults', 2) }}" min="1" required></div>
                            <div class="col-md-6"><label class="form-label">Max Children <span class="text-danger">*</span></label><input type="number" name="max_children" class="form-control" value="{{ old('max_children', 0) }}" min="0" required></div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header"><h5 class="mb-0">Amenities</h5></div>
                    <div class="card-body">
                        <div class="row g-2">
                            @foreach($amenities as $amenity)
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="amenities[]" value="{{ $amenity->id }}" id="amenity{{ $amenity->id }}" {{ in_array($amenity->id, old('amenities', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="amenity{{ $amenity->id }}">{{ $amenity->name }}</label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header"><h5 class="mb-0">Images</h5></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Upload Images</label>
                            <input type="file" name="images[]" id="imageInput" class="form-control" multiple accept="image/*">
                            <small class="text-muted">You can select multiple images. Max 2MB each</small>
                        </div>
                        <div id="imagePreviewContainer" class="row g-3"></div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header"><h5 class="mb-0">Settings</h5></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Air Conditioning <span class="text-danger">*</span></label>
                            <select name="ac" class="form-select" required>
                                <option value="1" {{ old('ac', '1') == '1' ? 'selected' : '' }}>Yes</option>
                                <option value="0" {{ old('ac') == '0' ? 'selected' : '' }}>No</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        <div class="d-grid"><button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Create Room Type</button></div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
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
                            <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2 remove-image" data-index="${fileIndex}"><i class="bi bi-x"></i></button>
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
                previewDiv.id = `preview-${index}`;
                previewDiv.innerHTML = `
                    <div class="position-relative">
                        <img src="${e.target.result}" class="img-fluid rounded preview-thumb">
                        <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2 remove-image" data-index="${index}"><i class="bi bi-x"></i></button>
                    </div>
                `;
                previewContainer.appendChild(previewDiv);
            };
            reader.readAsDataURL(file);
        });
    }
});
</script>
@endpush
