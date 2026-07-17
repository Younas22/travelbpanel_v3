@extends('agent.layouts.app')
@section('title', 'Add Hotel')

@section('content')

{{-- Page Header --}}
<div class="flex items-center justify-between mb-5">
    <div>
        <h4 class="text-lg font-bold text-gray-800">Add Hotel</h4>
        <p class="text-xs text-gray-400 mt-0.5">Fill in the details to list your hotel</p>
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
    <button class="ap-tab-item opacity-40 cursor-not-allowed" disabled title="Save hotel first">
        <i class="fas fa-door-open"></i> Room Types
    </button>
    <button class="ap-tab-item opacity-40 cursor-not-allowed" disabled title="Save hotel first">
        <i class="fas fa-key"></i> Rooms
    </button>
</div>

{{-- ===== TAB: Hotel Info ===== --}}
<div id="hotel-info">
<form action="{{ route('agent.hotels.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
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
                            <input type="text" name="name"
                                   class="ap-input @error('name') ap-input-error @enderror"
                                   value="{{ old('name') }}" placeholder="e.g. Grand Pearl Hotel" required>
                            @error('name')<p class="ap-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="ap-label">Location <span class="text-red-500">*</span></label>
                                <select name="location_id" id="location_id"
                                        class="ap-input location-select @error('location_id') ap-input-error @enderror" required>
                                    <option value="">Search and select location...</option>
                                </select>
                                @error('location_id')<p class="ap-error">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="ap-label">Type <span class="text-red-500">*</span></label>
                                <select name="type"
                                        class="ap-input @error('type') ap-input-error @enderror" required>
                                    <option value="">Select Type</option>
                                    <option value="hotel"       {{ old('type') == 'hotel'       ? 'selected' : '' }}>Hotel</option>
                                    <option value="guest house" {{ old('type') == 'guest house' ? 'selected' : '' }}>Guest House</option>
                                    <option value="resort"      {{ old('type') == 'resort'      ? 'selected' : '' }}>Resort</option>
                                </select>
                                @error('type')<p class="ap-error">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div>
                            <label class="ap-label">Address</label>
                            <input type="text" name="address" class="ap-input"
                                   value="{{ old('address') }}" placeholder="Street address, area">
                        </div>

                        <div>
                            <label class="ap-label">Description</label>
                            <textarea name="description" class="ap-input" rows="4"
                                      placeholder="Brief description of the hotel...">{{ old('description') }}</textarea>
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
                            <input type="text" name="phone" class="ap-input"
                                   value="{{ old('phone') }}" placeholder="+92...">
                        </div>
                        <div>
                            <label class="ap-label">WhatsApp</label>
                            <input type="text" name="whatsapp" class="ap-input"
                                   value="{{ old('whatsapp') }}" placeholder="+92...">
                        </div>
                        <div>
                            <label class="ap-label">Email</label>
                            <input type="email" name="email" class="ap-input"
                                   value="{{ old('email') }}" placeholder="hotel@example.com">
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
                            <input type="time" name="check_in_time" class="ap-input"
                                   value="{{ old('check_in_time') }}">
                        </div>
                        <div>
                            <label class="ap-label">Check-out Time</label>
                            <input type="time" name="check_out_time" class="ap-input"
                                   value="{{ old('check_out_time') }}">
                        </div>
                        <div>
                            <label class="ap-label">Total Rooms</label>
                            <input type="number" name="total_rooms" class="ap-input"
                                   value="{{ old('total_rooms') }}" min="0" placeholder="0">
                        </div>
                        <div>
                            <label class="ap-label">Stars</label>
                            <select name="stars" class="ap-input">
                                <option value="">Select</option>
                                @for($i = 1; $i <= 5; $i++)
                                    <option value="{{ $i }}" {{ old('stars') == $i ? 'selected' : '' }}>
                                        {{ $i }} Star{{ $i > 1 ? 's' : '' }}
                                    </option>
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
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        @foreach($amenities as $amenity)
                        <label class="flex items-center gap-2.5 cursor-pointer group">
                            <input type="checkbox" name="amenities[]" value="{{ $amenity->id }}"
                                   id="am{{ $amenity->id }}" class="ap-checkbox"
                                   {{ in_array($amenity->id, old('amenities', [])) ? 'checked' : '' }}>
                            <span class="text-sm text-gray-700 group-hover:text-blue-600 transition">
                                {{ $amenity->name }}
                            </span>
                        </label>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            {{-- Images --}}
            <div class="ap-card">
                <div class="ap-card-header">Hotel Images</div>
                <div class="ap-card-body">
                    <label class="ap-file-label" for="imageInput">
                        <i class="fas fa-cloud-upload-alt text-blue-400 text-2xl"></i>
                        <span class="text-sm font-medium text-gray-600 mt-2">Click to upload images</span>
                        <span class="text-xs text-gray-400">JPG, PNG — max 2MB each</span>
                        <input type="file" name="images[]" id="imageInput"
                               class="hidden" multiple accept="image/*">
                    </label>
                    <div id="imagePreviewContainer" class="grid grid-cols-3 md:grid-cols-4 gap-3 mt-3"></div>
                </div>
            </div>

        </div>

        {{-- Right: Publish + Notice --}}
        <div class="lg:col-span-1 space-y-4">

            {{-- Publish --}}
            <div class="ap-card sticky top-4">
                <div class="ap-card-header">Publish</div>
                <div class="ap-card-body space-y-4">
                    <div>
                        <label class="ap-label">Status <span class="text-red-500">*</span></label>
                        <select name="status" class="ap-input" required>
                            <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('status') == '0'       ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <button type="submit" class="ap-btn-primary w-full justify-center">
                        <i class="fas fa-check text-xs"></i> Save Hotel
                    </button>
                </div>
            </div>

            {{-- Info notice --}}
            <div class="ap-card border-blue-100" style="background:#f0f9ff; border-color:#bfdbfe;">
                <div class="ap-card-body">
                    <div class="flex gap-3">
                        <i class="fas fa-info-circle text-blue-400 mt-0.5 flex-shrink-0"></i>
                        <div class="text-xs text-blue-700 space-y-1.5">
                            <p class="font-semibold">After saving</p>
                            <p>You can add <strong>Room Types</strong> and individual <strong>Rooms</strong> from the hotel edit page.</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>
</form>
</div>

@push('styles')
<style>
.select2-results__option .location-country-text { color: #9ca3af; }
.select2-results__option--highlighted .location-country-text { color: rgba(255,255,255,.85); }
</style>
@endpush

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
                div.className = 'relative';
                div.innerHTML = `
                    <img src="${ev.target.result}" class="w-full h-24 object-cover rounded-lg border border-gray-200">
                    <button type="button" class="absolute top-1 right-1 w-6 h-6 bg-red-500 text-white rounded-full text-xs flex items-center justify-center remove-img" data-idx="${idx}">
                        <i class="fas fa-times"></i>
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
                div.className = 'relative';
                div.innerHTML = `
                    <img src="${ev.target.result}" class="w-full h-24 object-cover rounded-lg border border-gray-200">
                    <button type="button" class="absolute top-1 right-1 w-6 h-6 bg-red-500 text-white rounded-full text-xs flex items-center justify-center remove-img" data-idx="${idx}">
                        <i class="fas fa-times"></i>
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
