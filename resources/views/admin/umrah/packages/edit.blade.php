@extends($layout ?? 'admin.layouts.app')

@section('title', 'Edit Umrah Package')

@section('content')
@php $umrah = $umrah ?? $package ?? null; @endphp
<div class="{{ isset($layout) ? '' : 'content-area p-4' }}">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Edit Umrah Package</h4>
            <p class="text-muted mb-0">Update package: {{ $umrah->name }}</p>
        </div>
        <a href="{{ $backUrl ?? route('admin.umrah.packages.index') }}" class="btn btn-secondary">
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

    <form action="{{ $formAction ?? route('admin.umrah.packages.update', $umrah->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method($formMethod ?? 'PATCH')
        <div class="row">
            <!-- Main Info -->
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Basic Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Package Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" value="{{ old('name', $umrah->name) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Package Type <span class="text-danger">*</span></label>
                                <select name="packege_type" class="form-select" required>
                                    <option value="">Select Type</option>
                                    @foreach($packageTypes as $type)
                                        <option value="{{ $type->packege_type }}" {{ old('packege_type', $umrah->packege_type) == $type->packege_type ? 'selected' : '' }}>{{ ucfirst($type->packege_type) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Location <span class="text-danger">*</span></label>
                                <input type="text" name="loaction" class="form-control" value="{{ old('loaction', $umrah->loaction) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Leaving From</label>
                                <select name="leaving_from" class="form-select airport-select" id="leaving_from">
                                    <option value="">Select Airport</option>
                                    @foreach($airports as $airport)
                                        <option value="{{ $airport->id }}" data-airport="{{ $airport->airport }}" data-city="{{ $airport->city }}" data-country="{{ $airport->country }}" data-code="{{ $airport->code }}" {{ old('leaving_from', $umrah->leaving_from) == $airport->id ? 'selected' : '' }}>{{ $airport->airport }} - {{ $airport->city }}, {{ $airport->country }} ({{ $airport->code }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Going To</label>
                                <select name="going_to" class="form-select airport-select" id="going_to">
                                    <option value="">Select Airport</option>
                                    @foreach($airports as $airport)
                                        <option value="{{ $airport->id }}" data-airport="{{ $airport->airport }}" data-city="{{ $airport->city }}" data-country="{{ $airport->country }}" data-code="{{ $airport->code }}" {{ old('going_to', $umrah->going_to) == $airport->id ? 'selected' : '' }}>{{ $airport->airport }} - {{ $airport->city }}, {{ $airport->country }} ({{ $airport->code }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Currency <span class="text-danger">*</span></label>
                                <select name="currceny" class="form-select" required>
                                    <option value="PKR" {{ old('currceny', $umrah->currceny) == 'PKR' ? 'selected' : '' }}>PKR</option>
                                    <option value="USD" {{ old('currceny', $umrah->currceny) == 'USD' ? 'selected' : '' }}>USD</option>
                                    <option value="SAR" {{ old('currceny', $umrah->currceny) == 'SAR' ? 'selected' : '' }}>SAR</option>
                                    <option value="GBP" {{ old('currceny', $umrah->currceny) == 'GBP' ? 'selected' : '' }}>GBP</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Price <span class="text-danger">*</span></label>
                                <input type="number" name="price" class="form-control" value="{{ old('price', $umrah->price) }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Duration <span class="text-danger">*</span></label>
                                <input type="text" name="duration" class="form-control" placeholder="e.g., 7 Days / 6 Nights" value="{{ old('duration', $umrah->duration) }}" required>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Stay Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Check-in Date</label>
                                <input type="date" name="checkin_date" class="form-control" value="{{ old('checkin_date', $umrah->checkin_date?->format('Y-m-d')) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Check-out Date</label>
                                <input type="date" name="checkout_date" class="form-control" value="{{ old('checkout_date', $umrah->checkout_date?->format('Y-m-d')) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nights in Makkah</label>
                                <input type="number" name="night_in_mekkah" class="form-control" value="{{ old('night_in_mekkah', $umrah->night_in_mekkah) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nights in Madinah</label>
                                <input type="number" name="night_in_madina" class="form-control" value="{{ old('night_in_madina', $umrah->night_in_madina) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Class</label>
                                <select name="class" class="form-select">
                                    <option value="">Select Class</option>
                                    <option value="economy" {{ old('class', $umrah->class) == 'economy' ? 'selected' : '' }}>Economy</option>
                                    <option value="business" {{ old('class', $umrah->class) == 'business' ? 'selected' : '' }}>Business</option>
                                    <option value="first" {{ old('class', $umrah->class) == 'first' ? 'selected' : '' }}>First Class</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Hotel Stars</label>
                                <select name="stars" class="form-select">
                                    <option value="">Select Stars</option>
                                    <option value="3" {{ old('stars', $umrah->stars) == '3' ? 'selected' : '' }}>3 Star</option>
                                    <option value="4" {{ old('stars', $umrah->stars) == '4' ? 'selected' : '' }}>4 Star</option>
                                    <option value="5" {{ old('stars', $umrah->stars) == '5' ? 'selected' : '' }}>5 Star</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Guest Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Adults</label>
                                <input type="number" name="adults" class="form-control" value="{{ old('adults', $umrah->adults) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Children</label>
                                <input type="number" name="childs" class="form-control" value="{{ old('childs', $umrah->childs) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Infants</label>
                                <input type="number" name="infants" class="form-control" value="{{ old('infants', $umrah->infants) }}">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Description & Policy</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Description <span class="text-danger">*</span></label>
                            <textarea name="desc" class="form-control" rows="5" required>{{ old('desc', $umrah->desc) }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Policy</label>
                            <textarea name="policy" class="form-control" rows="4">{{ old('policy', $umrah->policy) }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Inclusions & Exclusions</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Inclusions</label>
                            <div class="border rounded p-2" style="max-height: 200px; overflow-y: auto;">
                                @php $selectedInclusions = old('inclusions', $umrah->inclusions ?? []); @endphp
                                @foreach($inclusions as $inclusion)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="inclusions[]" value="{{ $inclusion->id }}" id="inclusion_{{ $inclusion->id }}"
                                            {{ in_array($inclusion->id, $selectedInclusions) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="inclusion_{{ $inclusion->id }}">{{ $inclusion->name }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Exclusions</label>
                            <div class="border rounded p-2" style="max-height: 200px; overflow-y: auto;">
                                @php $selectedExclusions = old('exclusions', $umrah->exclusions ?? []); @endphp
                                @foreach($exclusions as $exclusion)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="exclusions[]" value="{{ $exclusion->id }}" id="exclusion_{{ $exclusion->id }}"
                                            {{ in_array($exclusion->id, $selectedExclusions) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="exclusion_{{ $exclusion->id }}">{{ $exclusion->name }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Status & Visibility</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="1" {{ old('status', $umrah->status) == '1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('status', $umrah->status) == '0' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Featured</label>
                            <select name="featured" class="form-select">
                                <option value="0" {{ old('featured', $umrah->featured) == '0' ? 'selected' : '' }}>No</option>
                                <option value="1" {{ old('featured', $umrah->featured) == '1' ? 'selected' : '' }}>Yes</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Rating</label>
                            <input type="text" name="rating" class="form-control" placeholder="e.g., 4.5" value="{{ old('rating', $umrah->rating) }}">
                        </div>
                    </div>
                </div>

                <!-- Current Images -->
                @if($umrah->images->count() > 0)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Current Images</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            @foreach($umrah->images as $image)
                            <div class="col-6">
                                <div class="position-relative">
                                    <img src="{{ asset('public/assets/images/' . $image->image) }}" alt="" class="img-fluid rounded" style="height: 100px; width: 100%; object-fit: cover;">
                                    <a href="{{ route($deleteImageRouteName ?? 'admin.umrah.packages.delete-image', $image->id) }}"
                                       class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1 delete-image-btn"
                                       onclick="event.preventDefault(); if(confirm('Delete this image?')) document.getElementById('delete-image-{{ $image->id }}').submit();">
                                        <i class="bi bi-x"></i>
                                    </a>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Add More Images</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Upload Images</label>
                            <input type="file" name="images[]" class="form-control" multiple accept="image/*">
                            <small class="text-muted">You can select multiple images</small>
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-check-lg"></i> Update Package
                    </button>
                    <a href="{{ $backUrl ?? route('admin.umrah.packages.index') }}" class="btn btn-secondary">
                        Cancel
                    </a>
                </div>
            </div>
        </div>
    </form>

    @if($umrah->images->count() > 0)
        @foreach($umrah->images as $image)
        <form id="delete-image-{{ $image->id }}" action="{{ route($deleteImageRouteName ?? 'admin.umrah.packages.delete-image', $image->id) }}" method="POST" style="display: none;">
            @csrf
            @method('DELETE')
        </form>
        @endforeach
    @endif
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    function formatAirport(option) {
        if (!option.id) return option.text;
        var $option = $(option.element);
        var airport = $option.data('airport');
        var city = $option.data('city');
        var country = $option.data('country');
        var code = $option.data('code');
        return $('<div>' +
            '<strong>' + airport + ' (' + code + ')</strong>' +
            '<br><small class="text-muted">' + city + ', ' + country + '</small>' +
        '</div>');
    }

    function formatAirportSelection(option) {
        if (!option.id) return option.text;
        var $option = $(option.element);
        var airport = $option.data('airport');
        var code = $option.data('code');
        return airport + ' (' + code + ')';
    }

    $('.airport-select').select2({
        theme: 'bootstrap-5',
        placeholder: 'Select Airport',
        allowClear: true,
        templateResult: formatAirport,
        templateSelection: formatAirportSelection
    });
});
</script>
@endpush
