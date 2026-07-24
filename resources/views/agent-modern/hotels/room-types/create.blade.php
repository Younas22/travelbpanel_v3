@extends('agent-modern.layouts.app')
@section('title', 'Create Room Type')

@section('content')

    <div class="ap-page-header">
        <div>
            <h4 class="ap-page-title">Create Room Type</h4>
            <p class="ap-page-sub">Add a new room type to your hotel</p>
        </div>
        <a href="{{ $backUrl ?? route('agent.hotels.index') }}" class="ap-btn-outline">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    @if($errors->any())
    <div class="am-alert am-alert-danger">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <span><ul class="mb-0 ps-3">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></span>
    </div>
    @endif

    <form action="{{ $formAction ?? route('agent.hotels.room-types.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row g-4">

            <div class="col-lg-8">

                <div class="am-card mb-4">
                    <div class="am-card-header">Basic Information</div>
                    <div class="am-card-body">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Hotel <span style="color: var(--danger-color);">*</span></label>
                                <select name="hotel_id" class="form-select @error('hotel_id') is-invalid @enderror" required>
                                    <option value="">Select Hotel</option>
                                    @foreach($hotels as $hotel)
                                        <option value="{{ $hotel->id }}" {{ request()->input('hotel_id', old('hotel_id')) == $hotel->id ? 'selected' : '' }}>{{ $hotel->name }}</option>
                                    @endforeach
                                </select>
                                @error('hotel_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Room Type Name <span style="color: var(--danger-color);">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="e.g. Deluxe, Suite" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="mb-0">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Brief description...">{{ old('description') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="am-card mb-4">
                    <div class="am-card-header">Pricing &amp; Capacity</div>
                    <div class="am-card-body">
                        <div class="row g-3">
                            <div class="col-6 col-md-3">
                                <label class="form-label">Price / Night <span style="color: var(--danger-color);">*</span></label>
                                <input type="number" name="price_per_night" class="form-control @error('price_per_night') is-invalid @enderror" value="{{ old('price_per_night') }}" step="0.01" min="0" placeholder="0.00" required>
                                @error('price_per_night') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-6 col-md-3">
                                <label class="form-label">Beds <span style="color: var(--danger-color);">*</span></label>
                                <input type="number" name="beds" class="form-control @error('beds') is-invalid @enderror" value="{{ old('beds', 1) }}" min="1" required>
                                @error('beds') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-6 col-md-3">
                                <label class="form-label">Max Adults <span style="color: var(--danger-color);">*</span></label>
                                <input type="number" name="max_adults" class="form-control @error('max_adults') is-invalid @enderror" value="{{ old('max_adults', 2) }}" min="1" required>
                                @error('max_adults') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-6 col-md-3">
                                <label class="form-label">Max Children <span style="color: var(--danger-color);">*</span></label>
                                <input type="number" name="max_children" class="form-control @error('max_children') is-invalid @enderror" value="{{ old('max_children', 0) }}" min="0" required>
                                @error('max_children') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                    <div class="am-card-header">Images</div>
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
                    <div class="am-card-header">Settings</div>
                    <div class="am-card-body">
                        <div class="mb-3">
                            <label class="form-label">Air Conditioning <span style="color: var(--danger-color);">*</span></label>
                            <select name="ac" class="form-select" required>
                                <option value="1" {{ old('ac', '1') == '1' ? 'selected' : '' }}>Yes</option>
                                <option value="0" {{ old('ac') == '0' ? 'selected' : '' }}>No</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status <span style="color: var(--danger-color);">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        <button type="submit" class="ap-btn-primary w-100 justify-content-center">
                            <i class="bi bi-plus-lg"></i> Create Room Type
                        </button>
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
                div.innerHTML = `<img src="${ev.target.result}">
                    <button type="button" class="ap-img-delete remove-img" data-idx="${idx}"><i class="bi bi-x"></i></button>`;
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
                div.className = 'ap-img-item';
                div.innerHTML = `<img src="${ev.target.result}">
                    <button type="button" class="ap-img-delete remove-img" data-idx="${idx}"><i class="bi bi-x"></i></button>`;
                previewContainer.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
    }
});
</script>
@endpush
