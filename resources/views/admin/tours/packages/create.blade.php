@extends($layout ?? 'admin.layouts.app')
@section('title', 'Create Tour Package')

@section('content')

    <!-- ===== BREADCRUMB ===== -->
    <div class="tex-breadcrumb">
        <a href="{{ route('agent.dashboard') }}">Dashboard</a>
        <i class="bi bi-chevron-right"></i>
        <a href="{{ $backUrl ?? route('agent.tours.index') }}">My Tour Packages</a>
        <i class="bi bi-chevron-right"></i>
        <span>Create Package</span>
    </div>

    <!-- ===== PAGE HEADER ===== -->
    <div class="etp-header">
        <div class="etp-header-left">
            <div class="etp-header-icon"><i class="bi bi-map"></i></div>
            <div>
                <h2 class="etp-title">Create Tour Package</h2>
                <p class="etp-subtitle">Add a new tour package</p>
            </div>
        </div>
        <a href="{{ $backUrl ?? route('agent.tours.index') }}" class="etp-back-btn">
            <i class="bi bi-arrow-left"></i> Back to list
        </a>
    </div>

    @if($errors->any())
        <div class="etp-error-card">
            <div class="etp-error-title"><i class="bi bi-exclamation-triangle"></i> Please fix the following:</div>
            <ul class="etp-error-list">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ $formAction ?? route('admin.tours.packages.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="etp-layout">

            <!-- ===== MAIN COLUMN ===== -->
            <div class="etp-main">

                <!-- Basic Information -->
                <div class="etp-card">
                    <h5 class="etp-section-title"><i class="bi bi-info-circle"></i> Basic Information</h5>
                    <div class="etp-grid-1">
                        <div class="etp-field">
                            <label>Package Name <span class="etp-req">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="etp-grid-2">
                        <div class="etp-field">
                            <label>Package Type <span class="etp-req">*</span></label>
                            <select name="packege_type" class="form-select" required>
                                <option value="">Select type</option>
                                @foreach($packageTypes as $type)
                                    <option value="{{ $type->packege_type }}"
                                        {{ old('packege_type') == $type->packege_type ? 'selected' : '' }}>
                                        {{ ucfirst($type->packege_type) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="etp-field">
                            <label>Location <span class="etp-req">*</span></label>
                            <select name="loaction" class="form-select location-select" id="location" required>
                                <option value="">Search and select location...</option>
                            </select>
                        </div>
                        <div class="etp-field">
                            <label>Leaving From</label>
                            <select name="leaving_from" class="form-select airport-select" id="leaving_from">
                                <option value="">Select airport</option>
                                @foreach($airports as $airport)
                                    <option value="{{ $airport->id }}"
                                            data-airport="{{ $airport->airport }}"
                                            data-city="{{ $airport->city }}"
                                            data-country="{{ $airport->country }}"
                                            data-code="{{ $airport->code }}"
                                        {{ old('leaving_from') == $airport->id ? 'selected' : '' }}>
                                        {{ $airport->airport }} - {{ $airport->city }}, {{ $airport->country }} ({{ $airport->code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="etp-field">
                            <label>Going To</label>
                            <select name="going_to" class="form-select airport-select" id="going_to">
                                <option value="">Select airport</option>
                                @foreach($airports as $airport)
                                    <option value="{{ $airport->id }}"
                                            data-airport="{{ $airport->airport }}"
                                            data-city="{{ $airport->city }}"
                                            data-country="{{ $airport->country }}"
                                            data-code="{{ $airport->code }}"
                                        {{ old('going_to') == $airport->id ? 'selected' : '' }}>
                                        {{ $airport->airport }} - {{ $airport->city }}, {{ $airport->country }} ({{ $airport->code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="etp-field">
                            <label>Currency <span class="etp-req">*</span></label>
                            <select name="currceny" class="form-select" required>
                                <option value="PKR" {{ old('currceny') == 'PKR' ? 'selected' : '' }}>PKR</option>
                                <option value="USD" {{ old('currceny') == 'USD' ? 'selected' : '' }}>USD</option>
                                <option value="SAR" {{ old('currceny') == 'SAR' ? 'selected' : '' }}>SAR</option>
                                <option value="GBP" {{ old('currceny') == 'GBP' ? 'selected' : '' }}>GBP</option>
                            </select>
                        </div>
                        <div class="etp-field">
                            <label>Price <span class="etp-req">*</span></label>
                            <input type="number" name="price" class="form-control"
                                   value="{{ old('price') }}" required>
                        </div>
                        <div class="etp-field etp-span-2">
                            <label>Duration <span class="etp-req">*</span></label>
                            <input type="text" name="duration" class="form-control"
                                   placeholder="e.g., 7 Days / 6 Nights"
                                   value="{{ old('duration') }}" required>
                        </div>
                    </div>
                </div>

                <!-- Stay Details -->
                <div class="etp-card">
                    <h5 class="etp-section-title"><i class="bi bi-calendar3"></i> Stay Details</h5>
                    <div class="etp-grid-2">
                        <div class="etp-field">
                            <label>Check-in Date</label>
                            <input type="date" name="checkin_date" class="form-control" value="{{ old('checkin_date') }}">
                        </div>
                        <div class="etp-field">
                            <label>Check-out Date</label>
                            <input type="date" name="checkout_date" class="form-control" value="{{ old('checkout_date') }}">
                        </div>
                        <div class="etp-field">
                            <label>Days</label>
                            <input type="number" name="days" class="form-control" value="{{ old('days') }}">
                        </div>
                        <div class="etp-field">
                            <label>Nights</label>
                            <input type="number" name="nights" class="form-control" value="{{ old('nights') }}">
                        </div>
                        <div class="etp-field">
                            <label>Class</label>
                            <select name="class" class="form-select">
                                <option value="">Select class</option>
                                <option value="economy"  {{ old('class') == 'economy'  ? 'selected' : '' }}>Economy</option>
                                <option value="business" {{ old('class') == 'business' ? 'selected' : '' }}>Business</option>
                                <option value="first"    {{ old('class') == 'first'    ? 'selected' : '' }}>First Class</option>
                            </select>
                        </div>
                        <div class="etp-field">
                            <label>Hotel Stars</label>
                            <select name="stars" class="form-select">
                                <option value="">Select stars</option>
                                <option value="3" {{ old('stars') == '3' ? 'selected' : '' }}>3 Star</option>
                                <option value="4" {{ old('stars') == '4' ? 'selected' : '' }}>4 Star</option>
                                <option value="5" {{ old('stars') == '5' ? 'selected' : '' }}>5 Star</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Guest Details -->
                <div class="etp-card">
                    <h5 class="etp-section-title"><i class="bi bi-people"></i> Guest Details</h5>
                    <div class="etp-grid-3">
                        <div class="etp-field">
                            <label>Adults</label>
                            <input type="number" name="adults" class="form-control" value="{{ old('adults', '1') }}">
                        </div>
                        <div class="etp-field">
                            <label>Children</label>
                            <input type="number" name="childs" class="form-control" value="{{ old('childs', '0') }}">
                        </div>
                        <div class="etp-field">
                            <label>Infants</label>
                            <input type="number" name="infants" class="form-control" value="{{ old('infants', '0') }}">
                        </div>
                    </div>
                </div>

                <!-- Description & Policy -->
                <div class="etp-card">
                    <h5 class="etp-section-title"><i class="bi bi-file-text"></i> Description & Policy</h5>
                    <div class="etp-field">
                        <label>Description <span class="etp-req">*</span></label>
                        <textarea name="desc" class="form-control" rows="5" required>{{ old('desc') }}</textarea>
                    </div>
                    <div class="etp-field etp-field-spaced">
                        <label>Policy</label>
                        <textarea name="policy" class="form-control" rows="4">{{ old('policy') }}</textarea>
                    </div>
                </div>

                <!-- Inclusions & Exclusions -->
                <div class="etp-card">
                    <h5 class="etp-section-title"><i class="bi bi-list-check"></i> Inclusions & Exclusions</h5>
                    <div class="etp-grid-2">
                        <div>
                            <div class="etp-check-label etp-check-inc"><i class="bi bi-check-circle"></i> Inclusions</div>
                            <div class="etp-check-box">
                                @php $selectedInclusions = old('inclusions', []); @endphp
                                @foreach($inclusions as $inclusion)
                                    <div class="etp-check-row">
                                        <input class="form-check-input" type="checkbox"
                                               name="inclusions[]" value="{{ $inclusion->id }}"
                                               id="inclusion_{{ $inclusion->id }}"
                                            {{ in_array($inclusion->id, $selectedInclusions) ? 'checked' : '' }}>
                                        <label for="inclusion_{{ $inclusion->id }}">{{ $inclusion->name }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div>
                            <div class="etp-check-label etp-check-exc"><i class="bi bi-x-circle"></i> Exclusions</div>
                            <div class="etp-check-box">
                                @php $selectedExclusions = old('exclusions', []); @endphp
                                @foreach($exclusions as $exclusion)
                                    <div class="etp-check-row">
                                        <input class="form-check-input" type="checkbox"
                                               name="exclusions[]" value="{{ $exclusion->id }}"
                                               id="exclusion_{{ $exclusion->id }}"
                                            {{ in_array($exclusion->id, $selectedExclusions) ? 'checked' : '' }}>
                                        <label for="exclusion_{{ $exclusion->id }}">{{ $exclusion->name }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

            </div><!-- /.etp-main -->

            <!-- ===== SIDEBAR ===== -->
            <div class="etp-sidebar">

                <!-- Status & Visibility -->
                <div class="etp-card">
                    <h5 class="etp-section-title"><i class="bi bi-toggle-on"></i> Status & Visibility</h5>
                    <div class="etp-field">
                        <label>Status</label>
                        <select name="status" class="form-select">
                            <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="etp-field etp-field-spaced">
                        <label>Featured</label>
                        <select name="featured" class="form-select">
                            <option value="0" {{ old('featured', '0') == '0' ? 'selected' : '' }}>No</option>
                            <option value="1" {{ old('featured') == '1' ? 'selected' : '' }}>Yes</option>
                        </select>
                    </div>
                    <div class="etp-field etp-field-spaced">
                        <label>Rating</label>
                        <input type="text" name="rating" class="form-control"
                               placeholder="e.g., 4.5" value="{{ old('rating') }}">
                    </div>
                </div>

                <!-- Images -->
                <div class="etp-card">
                    <h5 class="etp-section-title"><i class="bi bi-cloud-upload"></i> Images</h5>
                    <div class="etp-field">
                        <label>Upload Images</label>
                        <input type="file" name="images[]" class="form-control" multiple accept="image/*">
                        <div class="etp-hint">You can select multiple images</div>
                    </div>
                </div>

                <!-- Submit -->
                <div class="etp-submit-group">
                    <button type="submit" class="etp-btn etp-btn-primary">
                        <i class="bi bi-check-circle"></i> Create Package
                    </button>
                    <a href="{{ $backUrl ?? route('agent.tours.index') }}" class="etp-btn etp-btn-outline">
                        Cancel
                    </a>
                </div>

            </div><!-- /.etp-sidebar -->
        </div><!-- /.etp-layout -->
    </form>

@endsection

@push('styles')
    <link href="{{ url('public/assets/libs/select2/css/select2.min.css') }}" rel="stylesheet" />
@endpush


@push('scripts')
    <script src="{{ url('public/assets/js/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ url('public/assets/libs/select2/js/select2.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            const fullPath = window.location.pathname.split('/');
            const baseFolder = fullPath[1];
            const API_BASE_URL = window.location.origin + "/" + baseFolder;

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
                                return { id: item.id, text: item.city + ', ' + item.country, city: item.city, country: item.country };
                            })
                        };
                    },
                    cache: true
                }
            });
        });
    </script>
@endpush
