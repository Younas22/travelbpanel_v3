{{-- Expects: $umrah (Umrah|null — null on create, the model on edit),
     $packageTypes, $inclusions, $exclusions, $airports. Shared by
     create.blade.php / edit.blade.php so both stay pixel-identical except
     for the current-images gallery, which only makes sense on edit. --}}

@if($errors->any())
    <div class="bg-red-50 border border-red-200 rounded-2xl p-4 mb-5">
        <ul class="text-xs text-novadanger list-disc pl-5 space-y-0.5">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    <div class="lg:col-span-2 space-y-5">

        {{-- ===== BASIC INFORMATION ===== --}}
        <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 sm:p-5">
            <h2 class="text-sm font-semibold text-novatext mb-4">Basic Information</h2>
            <div class="space-y-4">
                <div>
                    <label class="umf-label">Package Name <span class="umf-required">*</span></label>
                    <input type="text" name="name" class="umf-input" value="{{ old('name', $umrah->name ?? '') }}" required>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="umf-label">Package Type <span class="umf-required">*</span></label>
                        <select name="packege_type" class="umf-input" required>
                            <option value="">Select Type</option>
                            @foreach($packageTypes as $type)
                                <option value="{{ $type->packege_type }}" {{ old('packege_type', $umrah->packege_type ?? '') == $type->packege_type ? 'selected' : '' }}>{{ ucfirst($type->packege_type) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="umf-label">Location <span class="umf-required">*</span></label>
                        <input type="text" name="loaction" class="umf-input" value="{{ old('loaction', $umrah->loaction ?? '') }}" required>
                    </div>
                    <div>
                        <label class="umf-label">Leaving From</label>
                        <select name="leaving_from" class="umf-input airport-select" id="leaving_from">
                            <option value="">Select Airport</option>
                            @foreach($airports as $airport)
                                <option value="{{ $airport->id }}" data-airport="{{ $airport->airport }}" data-city="{{ $airport->city }}" data-country="{{ $airport->country }}" data-code="{{ $airport->code }}" {{ old('leaving_from', $umrah->leaving_from ?? '') == $airport->id ? 'selected' : '' }}>{{ $airport->airport }} - {{ $airport->city }}, {{ $airport->country }} ({{ $airport->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="umf-label">Going To</label>
                        <select name="going_to" class="umf-input airport-select" id="going_to">
                            <option value="">Select Airport</option>
                            @foreach($airports as $airport)
                                <option value="{{ $airport->id }}" data-airport="{{ $airport->airport }}" data-city="{{ $airport->city }}" data-country="{{ $airport->country }}" data-code="{{ $airport->code }}" {{ old('going_to', $umrah->going_to ?? '') == $airport->id ? 'selected' : '' }}>{{ $airport->airport }} - {{ $airport->city }}, {{ $airport->country }} ({{ $airport->code }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="umf-label">Currency <span class="umf-required">*</span></label>
                        <select name="currceny" class="umf-input" required>
                            @foreach(['PKR','USD','SAR','GBP'] as $cur)
                                <option value="{{ $cur }}" {{ old('currceny', $umrah->currceny ?? 'PKR') == $cur ? 'selected' : '' }}>{{ $cur }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="umf-label">Price <span class="umf-required">*</span></label>
                        <input type="number" name="price" class="umf-input" value="{{ old('price', $umrah->price ?? '') }}" required>
                    </div>
                    <div>
                        <label class="umf-label">Duration <span class="umf-required">*</span></label>
                        <input type="text" name="duration" class="umf-input" placeholder="e.g., 7 Days / 6 Nights" value="{{ old('duration', $umrah->duration ?? '') }}" required>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== STAY DETAILS ===== --}}
        <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 sm:p-5">
            <h2 class="text-sm font-semibold text-novatext mb-4">Stay Details</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="umf-label">Check-in Date</label>
                    <input type="date" name="checkin_date" class="umf-input" value="{{ old('checkin_date', optional($umrah->checkin_date ?? null)->format('Y-m-d')) }}">
                </div>
                <div>
                    <label class="umf-label">Check-out Date</label>
                    <input type="date" name="checkout_date" class="umf-input" value="{{ old('checkout_date', optional($umrah->checkout_date ?? null)->format('Y-m-d')) }}">
                </div>
                <div>
                    <label class="umf-label">Nights in Makkah</label>
                    <input type="number" name="night_in_mekkah" class="umf-input" value="{{ old('night_in_mekkah', $umrah->night_in_mekkah ?? '') }}">
                </div>
                <div>
                    <label class="umf-label">Nights in Madinah</label>
                    <input type="number" name="night_in_madina" class="umf-input" value="{{ old('night_in_madina', $umrah->night_in_madina ?? '') }}">
                </div>
                <div>
                    <label class="umf-label">Class</label>
                    <select name="class" class="umf-input">
                        <option value="">Select Class</option>
                        @foreach(['economy' => 'Economy', 'business' => 'Business', 'first' => 'First Class'] as $val => $label)
                            <option value="{{ $val }}" {{ old('class', $umrah->class ?? '') == $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="umf-label">Hotel Stars</label>
                    <select name="stars" class="umf-input">
                        <option value="">Select Stars</option>
                        @foreach(['3','4','5'] as $s)
                            <option value="{{ $s }}" {{ old('stars', $umrah->stars ?? '') == $s ? 'selected' : '' }}>{{ $s }} Star</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- ===== GUEST DETAILS ===== --}}
        <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 sm:p-5">
            <h2 class="text-sm font-semibold text-novatext mb-4">Guest Details</h2>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="umf-label">Adults</label>
                    <input type="number" name="adults" class="umf-input" value="{{ old('adults', $umrah->adults ?? '1') }}">
                </div>
                <div>
                    <label class="umf-label">Children</label>
                    <input type="number" name="childs" class="umf-input" value="{{ old('childs', $umrah->childs ?? '0') }}">
                </div>
                <div>
                    <label class="umf-label">Infants</label>
                    <input type="number" name="infants" class="umf-input" value="{{ old('infants', $umrah->infants ?? '0') }}">
                </div>
            </div>
        </div>

        {{-- ===== DESCRIPTION & POLICY ===== --}}
        <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 sm:p-5">
            <h2 class="text-sm font-semibold text-novatext mb-4">Description &amp; Policy</h2>
            <div class="space-y-4">
                <div>
                    <label class="umf-label">Description <span class="umf-required">*</span></label>
                    <textarea name="desc" class="umf-input" rows="5" required>{{ old('desc', $umrah->desc ?? '') }}</textarea>
                </div>
                <div>
                    <label class="umf-label">Policy</label>
                    <textarea name="policy" class="umf-input" rows="4">{{ old('policy', $umrah->policy ?? '') }}</textarea>
                </div>
            </div>
        </div>

        {{-- ===== INCLUSIONS & EXCLUSIONS ===== --}}
        <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 sm:p-5">
            <h2 class="text-sm font-semibold text-novatext mb-4">Inclusions &amp; Exclusions</h2>
            <div class="space-y-4">
                <div>
                    <label class="umf-label">Inclusions</label>
                    <div class="umf-checklist">
                        @php $selectedInclusions = old('inclusions', $umrah->inclusions ?? []); @endphp
                        @foreach($inclusions as $inclusion)
                            <label>
                                <input type="checkbox" name="inclusions[]" value="{{ $inclusion->id }}" {{ in_array($inclusion->id, $selectedInclusions) ? 'checked' : '' }}>
                                {{ $inclusion->name }}
                            </label>
                        @endforeach
                    </div>
                </div>
                <div>
                    <label class="umf-label">Exclusions</label>
                    <div class="umf-checklist">
                        @php $selectedExclusions = old('exclusions', $umrah->exclusions ?? []); @endphp
                        @foreach($exclusions as $exclusion)
                            <label>
                                <input type="checkbox" name="exclusions[]" value="{{ $exclusion->id }}" {{ in_array($exclusion->id, $selectedExclusions) ? 'checked' : '' }}>
                                {{ $exclusion->name }}
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== SIDEBAR ===== --}}
    <div class="space-y-5">
        <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 sm:p-5">
            <h2 class="text-sm font-semibold text-novatext mb-4">Status &amp; Visibility</h2>
            <div class="space-y-4">
                <div>
                    <label class="umf-label">Status</label>
                    <select name="status" class="umf-input">
                        <option value="1" {{ old('status', $umrah->status ?? '1') == '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ old('status', $umrah->status ?? '1') == '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div>
                    <label class="umf-label">Featured</label>
                    <select name="featured" class="umf-input">
                        <option value="0" {{ old('featured', $umrah->featured ?? '0') == '0' ? 'selected' : '' }}>No</option>
                        <option value="1" {{ old('featured', $umrah->featured ?? '0') == '1' ? 'selected' : '' }}>Yes</option>
                    </select>
                </div>
                <div>
                    <label class="umf-label">Rating</label>
                    <input type="text" name="rating" class="umf-input" placeholder="e.g., 4.5" value="{{ old('rating', $umrah->rating ?? '') }}">
                </div>
            </div>
        </div>

        @if($umrah && $umrah->images->count() > 0)
            <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 sm:p-5">
                <h2 class="text-sm font-semibold text-novatext mb-4">Current Images</h2>
                <div class="grid grid-cols-2 gap-2">
                    @foreach($umrah->images as $image)
                        <div class="relative">
                            <img src="{{ asset('public/assets/images/' . $image->image) }}" alt="" class="w-full h-20 object-cover rounded-xl">
                            <a href="{{ route($deleteImageRouteName ?? 'admin.umrah.packages.delete-image', $image->id) }}"
                               class="absolute top-1 right-1 w-6 h-6 rounded-full bg-novadanger text-white flex items-center justify-center"
                               onclick="event.preventDefault(); if(confirm('Delete this image?')) document.getElementById('delete-image-{{ $image->id }}').submit();">
                                <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 sm:p-5">
            <h2 class="text-sm font-semibold text-novatext mb-4">{{ $umrah ? 'Add More Images' : 'Images' }}</h2>
            <label class="umf-label">Upload Images</label>
            <input type="file" name="images[]" class="umf-input" multiple accept="image/*">
            <p class="text-[11px] text-novamuted mt-1.5">You can select multiple images</p>
        </div>

        <div class="space-y-2">
            <button type="submit" class="umf-btn-nova umf-btn-primary w-full py-3 text-sm font-semibold">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                {{ $umrah ? 'Update Package' : 'Create Package' }}
            </button>
            <a href="{{ $backUrl ?? route('admin.umrah.packages.index') }}" class="umf-btn-nova w-full py-3 text-sm font-semibold text-center">
                Cancel
            </a>
        </div>
    </div>
</div>

@if($umrah && $umrah->images->count() > 0)
    @foreach($umrah->images as $image)
        <form id="delete-image-{{ $image->id }}" action="{{ route($deleteImageRouteName ?? 'admin.umrah.packages.delete-image', $image->id) }}" method="POST" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    @endforeach
@endif

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    function formatAirport(option) {
        if (!option.id) return option.text;
        var $o = $(option.element);
        return $('<div><strong>' + $o.data('airport') + ' (' + $o.data('code') + ')</strong><br><small>' + $o.data('city') + ', ' + $o.data('country') + '</small></div>');
    }
    function formatAirportSelection(option) {
        if (!option.id) return option.text;
        var $o = $(option.element);
        return $o.data('airport') + ' (' + $o.data('code') + ')';
    }
    $('.airport-select').select2({
        placeholder: 'Select Airport',
        allowClear: true,
        templateResult: formatAirport,
        templateSelection: formatAirportSelection
    });
});
</script>
@endpush
