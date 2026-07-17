@extends($layout ?? 'admin.layouts.app')
@section('title', 'Edit Tour Package')

@section('content')
    @php $tour = $tour ?? $package ?? null; @endphp

        <!-- ===== PAGE HEADER ===== -->
    <div class="etp-header">
        <div>
            <h2 class="etp-title">Edit Tour Package</h2>
            <p class="etp-subtitle">{{ $tour->name }}</p>
        </div>
        <a href="{{ $backUrl ?? route('admin.tours.packages.index') }}" class="etp-back-btn">
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

    <form action="{{ $formAction ?? route('admin.tours.packages.update', $tour->id) }}"
          method="POST" enctype="multipart/form-data">
        @csrf
        @method($formMethod ?? 'PATCH')

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
                                   value="{{ old('name', $tour->name) }}" required>
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
                                        {{ old('packege_type', $tour->packege_type) == $type->packege_type ? 'selected' : '' }}>
                                        {{ ucfirst($type->packege_type) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="etp-field">
                            <label>Location <span class="etp-req">*</span></label>
                            <select name="loaction" class="form-select location-select" id="location" required>
                                <option value="">Search and select location...</option>
                                @if(old('loaction', $tour->loaction))
                                    @php $location = \DB::table('locations')->find(old('loaction', $tour->loaction)); @endphp
                                    @if($location)
                                        <option value="{{ $location->id }}" selected>{{ $location->city }}, {{ $location->country }}</option>
                                    @endif
                                @endif
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
                                        {{ old('leaving_from', $tour->leaving_from) == $airport->id ? 'selected' : '' }}>
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
                                        {{ old('going_to', $tour->going_to) == $airport->id ? 'selected' : '' }}>
                                        {{ $airport->airport }} - {{ $airport->city }}, {{ $airport->country }} ({{ $airport->code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="etp-field">
                            <label>Currency <span class="etp-req">*</span></label>
                            <select name="currceny" class="form-select" required>
                                <option value="PKR" {{ old('currceny', $tour->currceny) == 'PKR' ? 'selected' : '' }}>PKR</option>
                                <option value="USD" {{ old('currceny', $tour->currceny) == 'USD' ? 'selected' : '' }}>USD</option>
                                <option value="SAR" {{ old('currceny', $tour->currceny) == 'SAR' ? 'selected' : '' }}>SAR</option>
                                <option value="GBP" {{ old('currceny', $tour->currceny) == 'GBP' ? 'selected' : '' }}>GBP</option>
                            </select>
                        </div>
                        <div class="etp-field">
                            <label>Price <span class="etp-req">*</span></label>
                            <input type="number" name="price" class="form-control"
                                   value="{{ old('price', $tour->price) }}" required>
                        </div>
                        <div class="etp-field etp-span-2">
                            <label>Duration <span class="etp-req">*</span></label>
                            <input type="text" name="duration" class="form-control"
                                   placeholder="e.g., 7 Days / 6 Nights"
                                   value="{{ old('duration', $tour->duration) }}" required>
                        </div>
                    </div>
                </div>

                <!-- Stay Details -->
                <div class="etp-card">
                    <h5 class="etp-section-title"><i class="bi bi-calendar3"></i> Stay Details</h5>
                    <div class="etp-grid-2">
                        <div class="etp-field">
                            <label>Check-in Date</label>
                            <input type="date" name="checkin_date" class="form-control"
                                   value="{{ old('checkin_date', $tour->checkin_date?->format('Y-m-d')) }}">
                        </div>
                        <div class="etp-field">
                            <label>Check-out Date</label>
                            <input type="date" name="checkout_date" class="form-control"
                                   value="{{ old('checkout_date', $tour->checkout_date?->format('Y-m-d')) }}">
                        </div>
                        <div class="etp-field">
                            <label>Days</label>
                            <input type="number" name="days" class="form-control" value="{{ old('days', $tour->days) }}">
                        </div>
                        <div class="etp-field">
                            <label>Nights</label>
                            <input type="number" name="nights" class="form-control" value="{{ old('nights', $tour->nights) }}">
                        </div>
                        <div class="etp-field">
                            <label>Class</label>
                            <select name="class" class="form-select">
                                <option value="">Select class</option>
                                <option value="economy"  {{ old('class', $tour->class) == 'economy'  ? 'selected' : '' }}>Economy</option>
                                <option value="business" {{ old('class', $tour->class) == 'business' ? 'selected' : '' }}>Business</option>
                                <option value="first"    {{ old('class', $tour->class) == 'first'    ? 'selected' : '' }}>First Class</option>
                            </select>
                        </div>
                        <div class="etp-field">
                            <label>Hotel Stars</label>
                            <select name="stars" class="form-select">
                                <option value="">Select stars</option>
                                <option value="3" {{ old('stars', $tour->stars) == '3' ? 'selected' : '' }}>3 Star</option>
                                <option value="4" {{ old('stars', $tour->stars) == '4' ? 'selected' : '' }}>4 Star</option>
                                <option value="5" {{ old('stars', $tour->stars) == '5' ? 'selected' : '' }}>5 Star</option>
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
                            <input type="number" name="adults" class="form-control" value="{{ old('adults', $tour->adults) }}">
                        </div>
                        <div class="etp-field">
                            <label>Children</label>
                            <input type="number" name="childs" class="form-control" value="{{ old('childs', $tour->childs) }}">
                        </div>
                        <div class="etp-field">
                            <label>Infants</label>
                            <input type="number" name="infants" class="form-control" value="{{ old('infants', $tour->infants) }}">
                        </div>
                    </div>
                </div>

                <!-- Description & Policy -->
                <div class="etp-card">
                    <h5 class="etp-section-title"><i class="bi bi-file-text"></i> Description & Policy</h5>
                    <div class="etp-field">
                        <label>Description <span class="etp-req">*</span></label>
                        <textarea name="desc" class="form-control" rows="5" required>{{ old('desc', $tour->desc) }}</textarea>
                    </div>
                    <div class="etp-field etp-field-spaced">
                        <label>Policy</label>
                        <textarea name="policy" class="form-control" rows="4">{{ old('policy', $tour->policy) }}</textarea>
                    </div>
                </div>

                <!-- Inclusions & Exclusions -->
                <div class="etp-card">
                    <h5 class="etp-section-title"><i class="bi bi-list-check"></i> Inclusions & Exclusions</h5>
                    <div class="etp-grid-2">
                        <div>
                            <div class="etp-check-label etp-check-inc"><i class="bi bi-check-circle"></i> Inclusions</div>
                            <div class="etp-check-box">
                                @php $selectedInclusions = old('inclusions', $tour->inclusions ?? []); @endphp
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
                                @php $selectedExclusions = old('exclusions', $tour->exclusions ?? []); @endphp
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
                            <option value="1" {{ old('status', $tour->status) == '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('status', $tour->status) == '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="etp-field etp-field-spaced">
                        <label>Featured</label>
                        <select name="featured" class="form-select">
                            <option value="0" {{ old('featured', $tour->featured) == '0' ? 'selected' : '' }}>No</option>
                            <option value="1" {{ old('featured', $tour->featured) == '1' ? 'selected' : '' }}>Yes</option>
                        </select>
                    </div>
                    <div class="etp-field etp-field-spaced">
                        <label>Rating</label>
                        <input type="text" name="rating" class="form-control"
                               placeholder="e.g., 4.5"
                               value="{{ old('rating', $tour->rating) }}">
                    </div>
                </div>

                <!-- Current Images -->
                @if($tour->images->count() > 0)
                    <div class="etp-card">
                        <h5 class="etp-section-title"><i class="bi bi-images"></i> Current Images</h5>
                        <div class="etp-img-grid">
                            @foreach($tour->images as $image)
                                <div class="etp-img-wrap">
                                    <img src="{{ asset('public/assets/images/' . $image->image) }}"
                                         alt="" class="etp-img">
                                    <button type="button" class="etp-img-delete"
                                            onclick="if(confirm('Delete this image?')) document.getElementById('delete-image-{{ $image->id }}').submit();"
                                            title="Delete image">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Add More Images -->
                <div class="etp-card">
                    <h5 class="etp-section-title"><i class="bi bi-cloud-upload"></i> Add Images</h5>
                    <div class="etp-field">
                        <label>Upload Images</label>
                        <input type="file" name="images[]" class="form-control" multiple accept="image/*">
                        <div class="etp-hint">You can select multiple images</div>
                    </div>
                </div>

                <!-- Submit -->
                <div class="etp-submit-group">
                    <button type="submit" class="etp-btn etp-btn-primary">
                        <i class="bi bi-check-circle"></i> Update Package
                    </button>
                    <a href="{{ $backUrl ?? route('admin.tours.packages.index') }}" class="etp-btn etp-btn-outline">
                        Cancel
                    </a>
                </div>

            </div><!-- /.etp-sidebar -->
        </div><!-- /.etp-layout -->
    </form>

    <!-- Hidden delete image forms -->
    @if($tour->images->count() > 0)
        @foreach($tour->images as $image)
            <form id="delete-image-{{ $image->id }}"
                  action="{{ route($deleteImageRouteName ?? 'admin.tours.packages.delete-image', $image->id) }}"
                  method="POST" style="display:none;">
                @csrf
                @method('DELETE')
            </form>
        @endforeach
    @endif

@endsection

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <style>
        /* ===== PAGE HEADER ===== */
        .etp-header {
            display: flex; align-items: center; justify-content: space-between;
            flex-wrap: wrap; gap: 12px; margin-bottom: 1.25rem;
        }
        .etp-title { font-size: 20px; font-weight: 600; color: var(--bs-body-color); margin: 0 0 4px; }
        .etp-subtitle { font-size: 13px; color: var(--bs-secondary-color); margin: 0; }
        .etp-back-btn {
            display: inline-flex; align-items: center; gap: 7px;
            padding: 8px 16px; border-radius: 8px;
            border: 1px solid var(--bs-border-color);
            background: var(--bs-secondary-bg); color: var(--bs-secondary-color);
            font-size: 13px; font-weight: 500; text-decoration: none;
            transition: background .15s, color .15s;
        }
        .etp-back-btn:hover { background: var(--bs-tertiary-bg); color: var(--bs-body-color); text-decoration: none; }

        /* ===== ERROR CARD ===== */
        .etp-error-card {
            background: #FCEBEB; border-radius: 12px;
            padding: 1rem 1.25rem; margin-bottom: 1.25rem;
        }
        [data-bs-theme="dark"] .etp-error-card { background: #2e0a0a; }
        .etp-error-title {
            display: flex; align-items: center; gap: 8px;
            font-size: 13px; font-weight: 600; color: #A32D2D; margin-bottom: 6px;
        }
        [data-bs-theme="dark"] .etp-error-title { color: #f08080; }
        .etp-error-list { margin: 0; padding-left: 1.4rem; font-size: 12px; color: #A32D2D; }
        [data-bs-theme="dark"] .etp-error-list { color: #f08080; }

        /* ===== LAYOUT ===== */
        .etp-layout {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 1.25rem;
            align-items: start;
        }

        /* ===== CARD ===== */
        .etp-card {
            background: var(--bs-body-bg);
            border: 1px solid var(--bs-border-color);
            border-radius: 14px;
            padding: 1.25rem 1.5rem;
            margin-bottom: 1.25rem;
        }
        .etp-sidebar .etp-card { margin-bottom: 1.25rem; }

        /* ===== SECTION TITLE ===== */
        .etp-section-title {
            display: flex; align-items: center; gap: 8px;
            font-size: 14px; font-weight: 600; color: var(--bs-body-color);
            margin: 0 0 1.1rem; padding-bottom: .85rem;
            border-bottom: 1px solid var(--bs-border-color);
        }
        .etp-section-title i { font-size: 15px; color: #0C6DFD; }

        /* ===== GRIDS ===== */
        .etp-grid-1 { display: grid; grid-template-columns: 1fr; gap: 1rem; }
        .etp-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        .etp-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; }
        .etp-span-2 { grid-column: 1 / -1; }

        /* ===== FIELDS ===== */
        .etp-field label {
            font-size: 12px; font-weight: 500;
            color: var(--bs-secondary-color); margin-bottom: 6px; display: block;
        }
        .etp-req { color: #A32D2D; }
        .etp-field-spaced { margin-top: 1rem; }
        .etp-hint { font-size: 11px; color: var(--bs-secondary-color); margin-top: 5px; }

        .etp-card .form-control,
        .etp-card .form-select {
            border-radius: 8px; border-color: var(--bs-border-color); font-size: 13px;
        }
        .etp-card .form-control:focus,
        .etp-card .form-select:focus {
            border-color: #0C6DFD;
            box-shadow: 0 0 0 3px rgba(12,109,253,.12);
        }

        /* ===== INCLUSIONS / EXCLUSIONS ===== */
        .etp-check-label {
            font-size: 11px; font-weight: 600; margin-bottom: 8px;
            display: flex; align-items: center; gap: 5px;
            text-transform: uppercase; letter-spacing: .05em;
        }
        .etp-check-inc { color: #0C6DFD; }
        .etp-check-exc { color: #A32D2D; }
        .etp-check-box {
            border: 1px solid var(--bs-border-color);
            border-radius: 10px;
            padding: 10px 12px;
            max-height: 200px;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: var(--bs-border-color) transparent;
        }
        .etp-check-row {
            display: flex; align-items: center; gap: 8px;
            padding: 4px 0; font-size: 13px; color: var(--bs-body-color);
        }
        .etp-check-row:not(:last-child) { border-bottom: 1px solid var(--bs-border-color); }
        .etp-check-row label { margin: 0; cursor: pointer; }
        .etp-check-row .form-check-input:checked { background-color: #0C6DFD; border-color: #0C6DFD; }

        /* ===== IMAGES ===== */
        .etp-img-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
        .etp-img-wrap { position: relative; border-radius: 8px; overflow: hidden; }
        .etp-img {
            width: 100%; height: 90px;
            object-fit: cover; display: block;
            border: 1px solid var(--bs-border-color);
            border-radius: 8px;
        }
        .etp-img-delete {
            position: absolute; top: 4px; right: 4px;
            width: 22px; height: 22px;
            background: #A32D2D; color: #fff;
            border: none; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 13px; cursor: pointer; line-height: 1;
            transition: opacity .12s;
        }
        .etp-img-delete:hover { opacity: .85; }

        /* ===== SUBMIT GROUP ===== */
        .etp-submit-group { display: flex; flex-direction: column; gap: 8px; }
        .etp-btn {
            display: flex; align-items: center; justify-content: center; gap: 7px;
            padding: 10px 18px; border-radius: 8px;
            font-size: 13px; font-weight: 500;
            cursor: pointer; text-decoration: none;
            transition: opacity .15s, background .15s;
            border: 1px solid var(--bs-border-color);
        }
        .etp-btn-primary { background: #0C6DFD; border-color: #0C6DFD; color: #fff; }
        .etp-btn-primary:hover { opacity: .9; color: #fff; }
        .etp-btn-outline { background: var(--bs-secondary-bg); color: var(--bs-secondary-color); }
        .etp-btn-outline:hover { background: var(--bs-tertiary-bg); color: var(--bs-body-color); text-decoration: none; }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 992px) {
            .etp-layout { grid-template-columns: 1fr; }
            .etp-sidebar { order: -1; }
        }
        @media (max-width: 768px) {
            .etp-grid-2 { grid-template-columns: 1fr; }
            .etp-grid-3 { grid-template-columns: 1fr 1fr; }
            .etp-span-2 { grid-column: auto; }
        }
        @media (max-width: 480px) {
            .etp-grid-3 { grid-template-columns: 1fr; }
            .etp-header { align-items: flex-start; }
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
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
                theme: 'bootstrap-5',
                placeholder: 'Select airport',
                allowClear: true,
                templateResult: formatAirport,
                templateSelection: formatAirportSelection
            });

            function formatLocation(location) {
                if (location.loading) return location.text;
                return $('<div><strong>' + location.city + '</strong><br><small class="text-muted">' + location.country + '</small></div>');
            }
            function formatLocationSelection(location) {
                return location.city || location.text;
            }

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
                        return {
                            results: data.data.map(function(item) {
                                return { id: item.id, text: item.city + ', ' + item.country, city: item.city, country: item.country };
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
