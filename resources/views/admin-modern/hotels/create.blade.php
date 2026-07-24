@extends('admin-modern.layouts.app')

@section('title', 'Create Hotel')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Create Hotel</h4>
            <p class="text-muted mb-0">Add a new hotel</p>
        </div>
        <a href="{{ route('admin.hotels.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back to List</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <ul class="nav hotel-tabs mb-4">
        <li class="nav-item"><a class="nav-link active" href="#hotel-info" data-bs-toggle="tab"><i class="bi bi-building"></i> Hotel Info</a></li>
        <li class="nav-item"><a class="nav-link tab-locked" href="#room-types" data-bs-toggle="tab"><i class="bi bi-door-open"></i> Room Types</a></li>
        <li class="nav-item"><a class="nav-link tab-locked" href="#rooms" data-bs-toggle="tab"><i class="bi bi-key"></i> Rooms</a></li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="hotel-info">
            <form action="{{ route('admin.hotels.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-md-8">
                        <div class="card mb-4">
                            <div class="card-header"><h5 class="mb-0">Basic Information</h5></div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label class="form-label">Hotel Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Location <span class="text-danger">*</span></label>
                                        <select name="location_id" class="form-select location-select" id="location_id" required>
                                            <option value="">Search and select location...</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Type <span class="text-danger">*</span></label>
                                        <select name="type" class="form-select" required>
                                            <option value="">Select Type</option>
                                            <option value="hotel" {{ old('type') == 'hotel' ? 'selected' : '' }}>Hotel</option>
                                            <option value="guest house" {{ old('type') == 'guest house' ? 'selected' : '' }}>Guest House</option>
                                            <option value="resort" {{ old('type') == 'resort' ? 'selected' : '' }}>Resort</option>
                                        </select>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">Address</label>
                                        <input type="text" name="address" class="form-control" value="{{ old('address') }}" placeholder="Street / Area">
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">Description</label>
                                        <textarea name="description" class="form-control" rows="4">{{ old('description') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header"><h5 class="mb-0">Contact Information</h5></div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-4"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control" value="{{ old('phone') }}"></div>
                                    <div class="col-md-4"><label class="form-label">WhatsApp</label><input type="text" name="whatsapp" class="form-control" value="{{ old('whatsapp') }}"></div>
                                    <div class="col-md-4"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="{{ old('email') }}"></div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header"><h5 class="mb-0">Hotel Details</h5></div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-4"><label class="form-label">Check-in Time</label><input type="time" name="check_in_time" class="form-control" value="{{ old('check_in_time') }}"></div>
                                    <div class="col-md-4"><label class="form-label">Check-out Time</label><input type="time" name="check_out_time" class="form-control" value="{{ old('check_out_time') }}"></div>
                                    <div class="col-md-4"><label class="form-label">Total Rooms</label><input type="number" name="total_rooms" class="form-control" value="{{ old('total_rooms') }}" min="0"></div>
                                    <div class="col-md-4">
                                        <label class="form-label">Stars</label>
                                        <select name="stars" class="form-select">
                                            <option value="">Select Stars</option>
                                            @for($i = 1; $i <= 5; $i++)
                                                <option value="{{ $i }}" {{ old('stars') == $i ? 'selected' : '' }}>{{ $i }} Star{{ $i > 1 ? 's' : '' }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                    <div class="col-md-4"><label class="form-label">Total Reviews</label><input type="number" name="total_rating" class="form-control" value="{{ old('total_rating') }}" min="0" step="1" placeholder="e.g. 2500"></div>
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
                                        <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Create Hotel</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="tab-pane fade" id="room-types">
            <div class="text-center py-5">
                <i class="bi bi-lock fs-1 text-muted d-block mb-3"></i>
                <h5 class="text-muted">Room Types</h5>
                <p class="text-muted mb-4">Please save the hotel first, then you can add Room Types.</p>
                <button type="button" onclick="document.querySelector('[href=\'#hotel-info\']').click()" class="btn btn-primary"><i class="bi bi-arrow-left"></i> Go to Hotel Info</button>
            </div>
        </div>

        <div class="tab-pane fade" id="rooms">
            <div class="text-center py-5">
                <i class="bi bi-lock fs-1 text-muted d-block mb-3"></i>
                <h5 class="text-muted">Rooms</h5>
                <p class="text-muted mb-4">Please save the hotel first, then you can add Rooms.</p>
                <button type="button" onclick="document.querySelector('[href=\'#hotel-info\']').click()" class="btn btn-primary"><i class="bi bi-arrow-left"></i> Go to Hotel Info</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const imageInput = document.getElementById('imageInput');
    const previewContainer = document.getElementById('imagePreviewContainer');
    let selectedFiles = [];

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
                    previewDiv.innerHTML = `
                        <div class="position-relative">
                            <img src="${e.target.result}" class="img-fluid rounded preview-thumb">
                            <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2 remove-image" data-index="${fileIndex}"><i class="bi bi-x"></i></button>
                        </div>`;
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
                previewDiv.className = 'col-md-3';
                previewDiv.innerHTML = `
                    <div class="position-relative">
                        <img src="${e.target.result}" class="img-fluid rounded preview-thumb">
                        <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2 remove-image" data-index="${index}"><i class="bi bi-x"></i></button>
                    </div>`;
                previewContainer.appendChild(previewDiv);
            };
            reader.readAsDataURL(file);
        });
    }
});
</script>
@endpush

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    const fullPath = window.location.pathname.split('/');
    const baseFolder = fullPath[1];
    const API_BASE_URL = window.location.origin + "/" + baseFolder;

    function formatLocation(location) {
        if (location.loading) return location.text;
        return $('<div><strong>' + location.city + '</strong><br><small class="text-muted">' + location.country + '</small></div>');
    }
    function formatLocationSelection(location) { return location.city || location.text; }

    $('.location-select').select2({
        theme: 'bootstrap-5',
        placeholder: 'Search and select location...',
        allowClear: true,
        minimumInputLength: 3,
        ajax: {
            url: API_BASE_URL + '/api/hotel_destinations',
            dataType: 'json',
            delay: 250,
            data: function(params) { return { search: params.term }; },
            processResults: function(data) {
                return { results: data.data.map(function(item) {
                    return { id: item.id, text: item.city + ', ' + item.country, city: item.city, country: item.country, country_code: item.country_code };
                }) };
            },
            cache: true
        },
        templateResult: formatLocation,
        templateSelection: formatLocationSelection
    });
});
</script>
@endpush
