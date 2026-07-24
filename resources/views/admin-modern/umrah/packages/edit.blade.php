@extends('admin-modern.layouts.app')
@section('title', 'Edit Umrah Package')

@section('content')
    @php $umrah = $umrah ?? $package ?? null; @endphp

    <div class="etp-header etp-header-edit">
        <div>
            <h2 class="etp-title">Edit Umrah Package</h2>
            <p class="etp-subtitle">{{ $umrah->name }}</p>
        </div>
        <a href="{{ $backUrl ?? route('admin.umrah.packages.index') }}" class="etp-back-btn"><i class="bi bi-arrow-left"></i> Back to list</a>
    </div>

    @if($errors->any())
        <div class="etp-error-card">
            <div class="etp-error-title"><i class="bi bi-exclamation-triangle"></i> Please fix the following:</div>
            <ul class="etp-error-list">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form action="{{ $formAction ?? route('admin.umrah.packages.update', $umrah->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method($formMethod ?? 'PATCH')

        <div class="etp-layout">

            <div class="etp-main">

                <div class="etp-card">
                    <h5 class="etp-section-title"><i class="bi bi-info-circle"></i> Basic Information</h5>
                    <div class="etp-grid-1">
                        <div class="etp-field">
                            <label>Package Name <span class="etp-req">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $umrah->name) }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="etp-grid-2">
                        <div class="etp-field">
                            <label>Package Type <span class="etp-req">*</span></label>
                            <select name="packege_type" class="form-select" required>
                                <option value="">Select type</option>
                                @foreach($packageTypes as $type)
                                    <option value="{{ $type->packege_type }}" {{ old('packege_type', $umrah->packege_type) == $type->packege_type ? 'selected' : '' }}>{{ ucfirst($type->packege_type) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="etp-field">
                            <label>Location <span class="etp-req">*</span></label>
                            <input type="text" name="loaction" class="form-control" value="{{ old('loaction', $umrah->loaction) }}" required>
                        </div>
                        <div class="etp-field">
                            <label>Leaving From</label>
                            <select name="leaving_from" class="form-select airport-select" id="leaving_from">
                                <option value="">Select airport</option>
                                @foreach($airports as $airport)
                                    <option value="{{ $airport->id }}" data-airport="{{ $airport->airport }}" data-city="{{ $airport->city }}" data-country="{{ $airport->country }}" data-code="{{ $airport->code }}" {{ old('leaving_from', $umrah->leaving_from) == $airport->id ? 'selected' : '' }}>{{ $airport->airport }} - {{ $airport->city }}, {{ $airport->country }} ({{ $airport->code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="etp-field">
                            <label>Going To</label>
                            <select name="going_to" class="form-select airport-select" id="going_to">
                                <option value="">Select airport</option>
                                @foreach($airports as $airport)
                                    <option value="{{ $airport->id }}" data-airport="{{ $airport->airport }}" data-city="{{ $airport->city }}" data-country="{{ $airport->country }}" data-code="{{ $airport->code }}" {{ old('going_to', $umrah->going_to) == $airport->id ? 'selected' : '' }}>{{ $airport->airport }} - {{ $airport->city }}, {{ $airport->country }} ({{ $airport->code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="etp-field">
                            <label>Currency <span class="etp-req">*</span></label>
                            <select name="currceny" class="form-select" required>
                                <option value="PKR" {{ old('currceny', $umrah->currceny) == 'PKR' ? 'selected' : '' }}>PKR</option>
                                <option value="USD" {{ old('currceny', $umrah->currceny) == 'USD' ? 'selected' : '' }}>USD</option>
                                <option value="SAR" {{ old('currceny', $umrah->currceny) == 'SAR' ? 'selected' : '' }}>SAR</option>
                                <option value="GBP" {{ old('currceny', $umrah->currceny) == 'GBP' ? 'selected' : '' }}>GBP</option>
                            </select>
                        </div>
                        <div class="etp-field"><label>Price <span class="etp-req">*</span></label><input type="number" name="price" class="form-control" value="{{ old('price', $umrah->price) }}" required></div>
                        <div class="etp-field etp-span-2"><label>Duration <span class="etp-req">*</span></label><input type="text" name="duration" class="form-control" placeholder="e.g., 7 Days / 6 Nights" value="{{ old('duration', $umrah->duration) }}" required></div>
                    </div>
                </div>

                <div class="etp-card">
                    <h5 class="etp-section-title"><i class="bi bi-calendar3"></i> Stay Details</h5>
                    <div class="etp-grid-2">
                        <div class="etp-field"><label>Check-in Date</label><input type="date" name="checkin_date" class="form-control" value="{{ old('checkin_date', $umrah->checkin_date?->format('Y-m-d')) }}"></div>
                        <div class="etp-field"><label>Check-out Date</label><input type="date" name="checkout_date" class="form-control" value="{{ old('checkout_date', $umrah->checkout_date?->format('Y-m-d')) }}"></div>
                        <div class="etp-field"><label>Nights in Makkah</label><input type="number" name="night_in_mekkah" class="form-control" value="{{ old('night_in_mekkah', $umrah->night_in_mekkah) }}"></div>
                        <div class="etp-field"><label>Nights in Madinah</label><input type="number" name="night_in_madina" class="form-control" value="{{ old('night_in_madina', $umrah->night_in_madina) }}"></div>
                        <div class="etp-field">
                            <label>Class</label>
                            <select name="class" class="form-select">
                                <option value="">Select class</option>
                                <option value="economy"  {{ old('class', $umrah->class) == 'economy'  ? 'selected' : '' }}>Economy</option>
                                <option value="business" {{ old('class', $umrah->class) == 'business' ? 'selected' : '' }}>Business</option>
                                <option value="first"    {{ old('class', $umrah->class) == 'first'    ? 'selected' : '' }}>First Class</option>
                            </select>
                        </div>
                        <div class="etp-field">
                            <label>Hotel Stars</label>
                            <select name="stars" class="form-select">
                                <option value="">Select stars</option>
                                <option value="3" {{ old('stars', $umrah->stars) == '3' ? 'selected' : '' }}>3 Star</option>
                                <option value="4" {{ old('stars', $umrah->stars) == '4' ? 'selected' : '' }}>4 Star</option>
                                <option value="5" {{ old('stars', $umrah->stars) == '5' ? 'selected' : '' }}>5 Star</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="etp-card">
                    <h5 class="etp-section-title"><i class="bi bi-people"></i> Guest Details</h5>
                    <div class="etp-grid-3">
                        <div class="etp-field"><label>Adults</label><input type="number" name="adults" class="form-control" value="{{ old('adults', $umrah->adults) }}"></div>
                        <div class="etp-field"><label>Children</label><input type="number" name="childs" class="form-control" value="{{ old('childs', $umrah->childs) }}"></div>
                        <div class="etp-field"><label>Infants</label><input type="number" name="infants" class="form-control" value="{{ old('infants', $umrah->infants) }}"></div>
                    </div>
                </div>

                <div class="etp-card">
                    <h5 class="etp-section-title"><i class="bi bi-file-text"></i> Description & Policy</h5>
                    <div class="etp-field"><label>Description <span class="etp-req">*</span></label><textarea name="desc" class="form-control" rows="5" required>{{ old('desc', $umrah->desc) }}</textarea></div>
                    <div class="etp-field etp-field-spaced"><label>Policy</label><textarea name="policy" class="form-control" rows="4">{{ old('policy', $umrah->policy) }}</textarea></div>
                </div>

                <div class="etp-card">
                    <h5 class="etp-section-title"><i class="bi bi-list-check"></i> Inclusions & Exclusions</h5>
                    <div class="etp-grid-2">
                        <div>
                            <div class="etp-check-label etp-check-inc"><i class="bi bi-check-circle"></i> Inclusions</div>
                            <div class="etp-check-box">
                                @php $selectedInclusions = old('inclusions', $umrah->inclusions ?? []); @endphp
                                @foreach($inclusions as $inclusion)
                                    <div class="etp-check-row">
                                        <input class="form-check-input" type="checkbox" name="inclusions[]" value="{{ $inclusion->id }}" id="inclusion_{{ $inclusion->id }}" {{ in_array($inclusion->id, $selectedInclusions) ? 'checked' : '' }}>
                                        <label for="inclusion_{{ $inclusion->id }}">{{ $inclusion->name }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div>
                            <div class="etp-check-label etp-check-exc"><i class="bi bi-x-circle"></i> Exclusions</div>
                            <div class="etp-check-box">
                                @php $selectedExclusions = old('exclusions', $umrah->exclusions ?? []); @endphp
                                @foreach($exclusions as $exclusion)
                                    <div class="etp-check-row">
                                        <input class="form-check-input" type="checkbox" name="exclusions[]" value="{{ $exclusion->id }}" id="exclusion_{{ $exclusion->id }}" {{ in_array($exclusion->id, $selectedExclusions) ? 'checked' : '' }}>
                                        <label for="exclusion_{{ $exclusion->id }}">{{ $exclusion->name }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="etp-sidebar">

                <div class="etp-card">
                    <h5 class="etp-section-title"><i class="bi bi-toggle-on"></i> Status & Visibility</h5>
                    <div class="etp-field">
                        <label>Status</label>
                        <select name="status" class="form-select">
                            <option value="1" {{ old('status', $umrah->status) == '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('status', $umrah->status) == '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="etp-field etp-field-spaced">
                        <label>Featured</label>
                        <select name="featured" class="form-select">
                            <option value="0" {{ old('featured', $umrah->featured) == '0' ? 'selected' : '' }}>No</option>
                            <option value="1" {{ old('featured', $umrah->featured) == '1' ? 'selected' : '' }}>Yes</option>
                        </select>
                    </div>
                    <div class="etp-field etp-field-spaced"><label>Rating</label><input type="text" name="rating" class="form-control" placeholder="e.g., 4.5" value="{{ old('rating', $umrah->rating) }}"></div>
                </div>

                @if($umrah->images->count() > 0)
                    <div class="etp-card">
                        <h5 class="etp-section-title"><i class="bi bi-images"></i> Current Images</h5>
                        <div class="etp-img-grid">
                            @foreach($umrah->images as $image)
                                <div class="etp-img-wrap">
                                    <img src="{{ asset('public/assets/images/' . $image->image) }}" alt="" class="etp-img">
                                    <button type="button" class="etp-img-delete" onclick="if(confirm('Delete this image?')) document.getElementById('delete-image-{{ $image->id }}').submit();" title="Delete image"><i class="bi bi-x"></i></button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="etp-card">
                    <h5 class="etp-section-title"><i class="bi bi-cloud-upload"></i> Add Images</h5>
                    <div class="etp-field">
                        <label>Upload Images</label>
                        <input type="file" name="images[]" class="form-control" multiple accept="image/*">
                        <div class="etp-hint">You can select multiple images</div>
                    </div>
                </div>

                <div class="etp-submit-group">
                    <button type="submit" class="etp-btn etp-btn-primary"><i class="bi bi-check-circle"></i> Update Package</button>
                    <a href="{{ $backUrl ?? route('admin.umrah.packages.index') }}" class="etp-btn etp-btn-outline">Cancel</a>
                </div>

            </div>
        </div>
    </form>

    @if($umrah->images->count() > 0)
        @foreach($umrah->images as $image)
            <form id="delete-image-{{ $image->id }}" action="{{ route($deleteImageRouteName ?? 'admin.umrah.packages.delete-image', $image->id) }}" method="POST" class="d-none">
                @csrf
                @method('DELETE')
            </form>
        @endforeach
    @endif

@endsection

@push('styles')
    <link href="{{ url('public/assets/libs/select2/css/select2.min.css') }}" rel="stylesheet" />
@endpush

@push('scripts')
    <script src="{{ url('public/assets/js/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ url('public/assets/libs/select2/js/select2.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            function formatAirport(option) {
                if (!option.id) return option.text;
                var $o = $(option.element);
                return $('<div><strong>' + $o.data('airport') + ' (' + $o.data('code') + ')</strong><br><small class="text-muted">' + $o.data('city') + ', ' + $o.data('country') + '</small></div>');
            }
            function formatAirportSelection(option) {
                if (!option.id) return option.text;
                var $o = $(option.element);
                return $o.data('airport') + ' (' + $o.data('code') + ')';
            }

            $('.airport-select').select2({
                placeholder: 'Select airport',
                allowClear: true,
                templateResult: formatAirport,
                templateSelection: formatAirportSelection
            });
        });
    </script>
@endpush
