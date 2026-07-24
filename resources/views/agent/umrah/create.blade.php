@extends('agent.layouts.app')
@section('title', 'Add Umrah Package')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-moon-stars"></i> Add Umrah Package</h4>
    <a href="{{ route('agent.umrah.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Back
    </a>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif

<form action="{{ route('agent.umrah.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="row g-4">
        <div class="col-lg-8">

            {{-- Basic Info --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white"><h6 class="mb-0">Basic Information</h6></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Package Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Package Type <span class="text-danger">*</span></label>
                            <select name="packege_type" class="form-select @error('packege_type') is-invalid @enderror" required>
                                <option value="">Select Type</option>
                                @foreach($packageTypes as $type)
                                    <option value="{{ $type->packege_type }}" {{ old('packege_type') == $type->packege_type ? 'selected' : '' }}>
                                        {{ ucfirst($type->packege_type) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('packege_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Location <span class="text-danger">*</span></label>
                            <input type="text" name="loaction" class="form-control @error('loaction') is-invalid @enderror"
                                   value="{{ old('loaction') }}" placeholder="e.g. Makkah, Saudi Arabia" required>
                            @error('loaction')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Leaving From</label>
                            <select name="leaving_from" class="form-select airport-select">
                                <option value="">Select Airport</option>
                                @foreach($airports as $airport)
                                    <option value="{{ $airport->id }}"
                                        data-airport="{{ $airport->airport }}" data-city="{{ $airport->city }}"
                                        data-country="{{ $airport->country }}" data-code="{{ $airport->code }}"
                                        {{ old('leaving_from') == $airport->id ? 'selected' : '' }}>
                                        {{ $airport->airport }} - {{ $airport->city }}, {{ $airport->country }} ({{ $airport->code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Going To</label>
                            <select name="going_to" class="form-select airport-select">
                                <option value="">Select Airport</option>
                                @foreach($airports as $airport)
                                    <option value="{{ $airport->id }}"
                                        data-airport="{{ $airport->airport }}" data-city="{{ $airport->city }}"
                                        data-country="{{ $airport->country }}" data-code="{{ $airport->code }}"
                                        {{ old('going_to') == $airport->id ? 'selected' : '' }}>
                                        {{ $airport->airport }} - {{ $airport->city }}, {{ $airport->country }} ({{ $airport->code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Currency <span class="text-danger">*</span></label>
                            <select name="currceny" class="form-select" required>
                                <option value="PKR" {{ old('currceny') == 'PKR' ? 'selected' : '' }}>PKR</option>
                                <option value="USD" {{ old('currceny') == 'USD' ? 'selected' : '' }}>USD</option>
                                <option value="SAR" {{ old('currceny') == 'SAR' ? 'selected' : '' }}>SAR</option>
                                <option value="GBP" {{ old('currceny') == 'GBP' ? 'selected' : '' }}>GBP</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Price <span class="text-danger">*</span></label>
                            <input type="number" name="price" class="form-control @error('price') is-invalid @enderror"
                                   value="{{ old('price') }}" required>
                            @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Duration <span class="text-danger">*</span></label>
                            <input type="text" name="duration" class="form-control @error('duration') is-invalid @enderror"
                                   placeholder="e.g. 7 Days / 6 Nights" value="{{ old('duration') }}" required>
                            @error('duration')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Stay Details --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white"><h6 class="mb-0">Stay Details</h6></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Check-in Date</label>
                            <input type="date" name="checkin_date" class="form-control" value="{{ old('checkin_date') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Check-out Date</label>
                            <input type="date" name="checkout_date" class="form-control" value="{{ old('checkout_date') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nights in Makkah</label>
                            <input type="number" name="night_in_mekkah" class="form-control" value="{{ old('night_in_mekkah') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nights in Madinah</label>
                            <input type="number" name="night_in_madina" class="form-control" value="{{ old('night_in_madina') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Class</label>
                            <select name="class" class="form-select">
                                <option value="">Select Class</option>
                                <option value="economy"  {{ old('class') == 'economy'  ? 'selected' : '' }}>Economy</option>
                                <option value="business" {{ old('class') == 'business' ? 'selected' : '' }}>Business</option>
                                <option value="first"    {{ old('class') == 'first'    ? 'selected' : '' }}>First Class</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Hotel Stars</label>
                            <select name="stars" class="form-select">
                                <option value="">Select Stars</option>
                                <option value="3" {{ old('stars') == '3' ? 'selected' : '' }}>3 Star</option>
                                <option value="4" {{ old('stars') == '4' ? 'selected' : '' }}>4 Star</option>
                                <option value="5" {{ old('stars') == '5' ? 'selected' : '' }}>5 Star</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Guest Details --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white"><h6 class="mb-0">Guest Details</h6></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Adults</label>
                            <input type="number" name="adults" class="form-control" value="{{ old('adults', 1) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Children</label>
                            <input type="number" name="childs" class="form-control" value="{{ old('childs', 0) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Infants</label>
                            <input type="number" name="infants" class="form-control" value="{{ old('infants', 0) }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Description & Policy --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white"><h6 class="mb-0">Description & Policy</h6></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Description <span class="text-danger">*</span></label>
                        <textarea name="desc" class="form-control @error('desc') is-invalid @enderror" rows="5" required>{{ old('desc') }}</textarea>
                        @error('desc')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Policy</label>
                        <textarea name="policy" class="form-control" rows="4">{{ old('policy') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Inclusions & Exclusions --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white"><h6 class="mb-0">Inclusions & Exclusions</h6></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Inclusions</label>
                        <div class="border rounded p-2 ap-scroll-200">
                            @php $selInc = old('inclusions', []); @endphp
                            @foreach($inclusions as $inc)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="inclusions[]"
                                           value="{{ $inc->id }}" id="inc_{{ $inc->id }}"
                                           {{ in_array($inc->id, $selInc) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="inc_{{ $inc->id }}">{{ $inc->name }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Exclusions</label>
                        <div class="border rounded p-2 ap-scroll-200">
                            @php $selExc = old('exclusions', []); @endphp
                            @foreach($exclusions as $exc)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="exclusions[]"
                                           value="{{ $exc->id }}" id="exc_{{ $exc->id }}"
                                           {{ in_array($exc->id, $selExc) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="exc_{{ $exc->id }}">{{ $exc->name }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white"><h6 class="mb-0">Status & Visibility</h6></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Featured</label>
                        <select name="featured" class="form-select">
                            <option value="0" {{ old('featured', '0') == '0' ? 'selected' : '' }}>No</option>
                            <option value="1" {{ old('featured') == '1' ? 'selected' : '' }}>Yes</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Rating</label>
                        <input type="text" name="rating" class="form-control" placeholder="e.g. 4.5" value="{{ old('rating') }}">
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white"><h6 class="mb-0">Images</h6></div>
                <div class="card-body">
                    <input type="file" name="images[]" id="imageInput" class="form-control" multiple accept="image/*">
                    <small class="text-muted">JPG, PNG, GIF — max 2MB each</small>
                    <div id="imagePreviewContainer" class="row g-2 mt-2"></div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-check-circle"></i> Save Package
            </button>
        </div>
    </div>
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const imageInput = document.getElementById('imageInput');
    const previewContainer = document.getElementById('imagePreviewContainer');
    let selectedFiles = [];

    imageInput.addEventListener('change', function (e) {
        Array.from(e.target.files).forEach(file => {
            if (file.type.startsWith('image/')) {
                selectedFiles.push(file);
                const reader = new FileReader();
                reader.onload = e => {
                    const idx = selectedFiles.length - 1;
                    const div = document.createElement('div');
                    div.className = 'col-md-4';
                    div.innerHTML = `<div class="position-relative">
                        <img src="${e.target.result}" class="img-fluid rounded ap-img-h-100">
                        <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1 remove-img" data-idx="${idx}"><i class="bi bi-x"></i></button>
                    </div>`;
                    previewContainer.appendChild(div);
                };
                reader.readAsDataURL(file);
            }
        });
        syncInput();
    });

    previewContainer.addEventListener('click', function (e) {
        const btn = e.target.closest('.remove-img');
        if (btn) {
            selectedFiles.splice(parseInt(btn.dataset.idx), 1);
            syncInput(); renderAll();
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
            reader.onload = e => {
                const div = document.createElement('div');
                div.className = 'col-md-4';
                div.innerHTML = `<div class="position-relative">
                    <img src="${e.target.result}" class="img-fluid rounded ap-img-h-100">
                    <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1 remove-img" data-idx="${idx}"><i class="bi bi-x"></i></button>
                </div>`;
                previewContainer.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
    }
});
</script>
@endpush
@endsection
