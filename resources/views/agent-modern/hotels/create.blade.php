@extends('agent-modern.layouts.app')
@section('title', 'Add Hotel')

@section('content')

    <div class="ap-page-header">
        <div>
            <h4 class="ap-page-title">Add Hotel</h4>
            <p class="ap-page-sub">Fill in the details to list your hotel</p>
        </div>
        <a href="{{ route('agent.hotels.index') }}" class="ap-btn-outline">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    <div class="ap-tab-bar">
        <button type="button" class="ap-tab-item active"><i class="bi bi-building"></i> Hotel Info</button>
        <button type="button" class="ap-tab-item" disabled title="Save hotel first" style="opacity: .4; cursor: not-allowed;"><i class="bi bi-door-open"></i> Room Types</button>
        <button type="button" class="ap-tab-item" disabled title="Save hotel first" style="opacity: .4; cursor: not-allowed;"><i class="bi bi-key"></i> Rooms</button>
    </div>

    <form action="{{ route('agent.hotels.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row g-4">

            <div class="col-lg-8">

                <div class="am-card mb-4">
                    <div class="am-card-header">Basic Information</div>
                    <div class="am-card-body">
                        <div class="mb-3">
                            <label class="form-label">Hotel Name <span style="color: var(--danger-color);">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="e.g. Grand Pearl Hotel" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Location <span style="color: var(--danger-color);">*</span></label>
                                <select name="location_id" id="location_id" class="form-select location-select @error('location_id') is-invalid @enderror" required>
                                    <option value="">Search and select location...</option>
                                </select>
                                @error('location_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Type <span style="color: var(--danger-color);">*</span></label>
                                <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                                    <option value="">Select Type</option>
                                    <option value="hotel" {{ old('type') == 'hotel' ? 'selected' : '' }}>Hotel</option>
                                    <option value="guest house" {{ old('type') == 'guest house' ? 'selected' : '' }}>Guest House</option>
                                    <option value="resort" {{ old('type') == 'resort' ? 'selected' : '' }}>Resort</option>
                                </select>
                                @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <input type="text" name="address" class="form-control" value="{{ old('address') }}" placeholder="Street address, area">
                        </div>
                        <div class="mb-0">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="4" placeholder="Brief description of the hotel...">{{ old('description') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="am-card mb-4">
                    <div class="am-card-header">Contact Details</div>
                    <div class="am-card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="+92...">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">WhatsApp</label>
                                <input type="text" name="whatsapp" class="form-control" value="{{ old('whatsapp') }}" placeholder="+92...">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="hotel@example.com">
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
                                <input type="time" name="check_in_time" class="form-control" value="{{ old('check_in_time') }}">
                            </div>
                            <div class="col-6 col-md-3">
                                <label class="form-label">Check-out Time</label>
                                <input type="time" name="check_out_time" class="form-control" value="{{ old('check_out_time') }}">
                            </div>
                            <div class="col-6 col-md-3">
                                <label class="form-label">Total Rooms</label>
                                <input type="number" name="total_rooms" class="form-control" value="{{ old('total_rooms') }}" min="0" placeholder="0">
                            </div>
                            <div class="col-6 col-md-3">
                                <label class="form-label">Stars</label>
                                <select name="stars" class="form-select">
                                    <option value="">Select</option>
                                    @for($i = 1; $i <= 5; $i++)
                                        <option value="{{ $i }}" {{ old('stars') == $i ? 'selected' : '' }}>{{ $i }} Star{{ $i > 1 ? 's' : '' }}</option>
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
                        <div class="ap-checkbox-grid">
                            @foreach($amenities as $amenity)
                            <label class="ap-checkbox-item">
                                <input type="checkbox" name="amenities[]" value="{{ $amenity->id }}" id="am{{ $amenity->id }}" class="form-check-input"
                                       {{ in_array($amenity->id, old('amenities', [])) ? 'checked' : '' }}>
                                {{ $amenity->name }}
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                <div class="am-card">
                    <div class="am-card-header">Hotel Images</div>
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
                <div class="am-card mb-3" style="position: sticky; top: 80px;">
                    <div class="am-card-header">Publish</div>
                    <div class="am-card-body">
                        <div class="mb-3">
                            <label class="form-label">Status <span style="color: var(--danger-color);">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        <button type="submit" class="ap-btn-primary w-100 justify-content-center">
                            <i class="bi bi-check-lg"></i> Save Hotel
                        </button>
                    </div>
                </div>

                <div class="am-card" style="border-color: color-mix(in srgb, var(--primary-color) 25%, var(--border-color)); background: var(--primary-tint-6);">
                    <div class="am-card-body">
                        <div class="d-flex gap-2">
                            <i class="bi bi-info-circle" style="color: var(--primary-color); margin-top: 2px;"></i>
                            <div style="font-size: 12.5px; color: var(--primary-color);">
                                <p style="font-weight: 650; margin-bottom: 4px;">After saving</p>
                                <p style="margin: 0;">You can add <strong>Room Types</strong> and individual <strong>Rooms</strong> from the hotel edit page.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </form>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
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
                div.className = 'ap-img-item';
                div.innerHTML = `
                    <img src="${ev.target.result}">
                    <button type="button" class="ap-img-delete remove-img" data-idx="${idx}">
                        <i class="bi bi-x"></i>
                    </button>`;
                previewContainer.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
        syncInput();
    });

    previewContainer.addEventListener('click', function (e) {
        const btn = e.target.closest('.remove-img');
        if (btn) {
            selectedFiles.splice(parseInt(btn.dataset.idx), 1);
            syncInput();
            renderAll();
        }
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
                div.innerHTML = `
                    <img src="${ev.target.result}">
                    <button type="button" class="ap-img-delete remove-img" data-idx="${idx}">
                        <i class="bi bi-x"></i>
                    </button>`;
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
