@extends('agent.layouts.app')
@section('title', 'Create Room Type')

@section('content')

{{-- Page Header --}}
<div class="flex items-center justify-between mb-5">
    <div>
        <h4 class="text-lg font-bold text-gray-800">Create Room Type</h4>
        <p class="text-xs text-gray-400 mt-0.5">Add a new room type to your hotel</p>
    </div>
    <a href="{{ $backUrl ?? route('agent.hotels.index') }}" class="ap-btn-outline">
        <i class="fas fa-arrow-left text-xs"></i> Back
    </a>
</div>

@if($errors->any())
<div class="mb-4 bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 text-sm">
    <ul class="list-disc list-inside space-y-1">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form action="{{ $formAction ?? route('agent.hotels.room-types.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
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
                                        <option value="{{ $hotel->id }}" {{ request()->input('hotel_id', old('hotel_id')) == $hotel->id ? 'selected' : '' }}>
                                            {{ $hotel->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('hotel_id')<p class="ap-error">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="ap-label">Room Type Name <span class="text-red-500">*</span></label>
                                <input type="text" name="name" class="ap-input @error('name') ap-input-error @enderror"
                                       value="{{ old('name') }}" placeholder="e.g. Deluxe, Suite" required>
                                @error('name')<p class="ap-error">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div>
                            <label class="ap-label">Description</label>
                            <textarea name="description" class="ap-input" rows="3" placeholder="Brief description...">{{ old('description') }}</textarea>
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
                                   value="{{ old('price_per_night') }}"
                                   step="0.01" min="0" placeholder="0.00" required>
                            @error('price_per_night')<p class="ap-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="ap-label">Beds <span class="text-red-500">*</span></label>
                            <input type="number" name="beds" class="ap-input @error('beds') ap-input-error @enderror"
                                   value="{{ old('beds', 1) }}" min="1" required>
                            @error('beds')<p class="ap-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="ap-label">Max Adults <span class="text-red-500">*</span></label>
                            <input type="number" name="max_adults" class="ap-input @error('max_adults') ap-input-error @enderror"
                                   value="{{ old('max_adults', 2) }}" min="1" required>
                            @error('max_adults')<p class="ap-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="ap-label">Max Children <span class="text-red-500">*</span></label>
                            <input type="number" name="max_children" class="ap-input @error('max_children') ap-input-error @enderror"
                                   value="{{ old('max_children', 0) }}" min="0" required>
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
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        @foreach($amenities as $amenity)
                        <label class="flex items-center gap-2.5 cursor-pointer group">
                            <input type="checkbox" name="amenities[]" value="{{ $amenity->id }}" id="am{{ $amenity->id }}"
                                   class="ap-checkbox"
                                   {{ in_array($amenity->id, old('amenities', [])) ? 'checked' : '' }}>
                            <span class="text-sm text-gray-700 group-hover:text-blue-600 transition">{{ $amenity->name }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            {{-- Images --}}
            <div class="ap-card">
                <div class="ap-card-header">Images</div>
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
                            <option value="1" {{ old('ac', '1') == '1' ? 'selected' : '' }}>Yes</option>
                            <option value="0" {{ old('ac') == '0' ? 'selected' : '' }}>No</option>
                        </select>
                    </div>
                    <div>
                        <label class="ap-label">Status <span class="text-red-500">*</span></label>
                        <select name="status" class="ap-input" required>
                            <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <button type="submit" class="ap-btn-primary w-full justify-center">
                        <i class="fas fa-plus text-xs"></i> Create Room Type
                    </button>
                </div>
            </div>
        </div>

    </div>
</form>

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
