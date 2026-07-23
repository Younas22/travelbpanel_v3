@extends($layout ?? 'admin.layouts.app')
@section('title', 'Create Umrah Package')

@section('content')

<div class="flex items-center gap-2 text-xs text-gray-400 mb-4">
    <a href="{{ route('agent.dashboard') }}" class="umr-link">Dashboard</a>
    <i class="fas fa-chevron-right text-gray-300"></i>
    <a href="{{ $backUrl ?? route('agent.umrah.index') }}" class="umr-link">My Umrah Packages</a>
    <i class="fas fa-chevron-right text-gray-300"></i>
    <span class="text-gray-600">Create Package</span>
</div>

<div class="flex items-center justify-between mb-5">
    <div class="flex items-center gap-3">
        <div class="w-9 h-9 rounded-lg flex items-center justify-center umr-icon-bg">
            <i class="fas fa-moon umr-accent"></i>
        </div>
        <div>
            <h4 class="text-lg font-bold text-gray-800">Create Umrah Package</h4>
            <p class="text-xs text-gray-400">Add a new Umrah package</p>
        </div>
    </div>
    <a href="{{ $backUrl ?? route('agent.umrah.index') }}" class="px-4 py-2 rounded-lg text-sm font-semibold border border-gray-200 text-gray-600 hover:bg-gray-50 transition umr-no-underline">
        <i class="fas fa-arrow-left text-xs"></i> Back to List
    </a>
</div>

@if($errors->any())
    <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ $formAction ?? route('admin.umrah.packages.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        <div class="lg:col-span-2 space-y-5">

            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 font-semibold text-sm text-gray-700">Basic Information</div>
                <div class="p-5 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Package Name <span class="umr-accent">*</span></label>
                        <input type="text" name="name" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-400 bg-gray-50" value="{{ old('name') }}" required>
                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Package Type <span class="umr-accent">*</span></label>
                            <select name="packege_type" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-400 bg-gray-50" required>
                                <option value="">Select Type</option>
                                @foreach($packageTypes as $type)
                                    <option value="{{ $type->packege_type }}" {{ old('packege_type') == $type->packege_type ? 'selected' : '' }}>{{ ucfirst($type->packege_type) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Location <span class="umr-accent">*</span></label>
                            <input type="text" name="loaction" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-400 bg-gray-50" value="{{ old('loaction') }}" required>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Leaving From</label>
                            <select name="leaving_from" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-400 bg-gray-50 airport-select" id="leaving_from">
                                <option value="">Select Airport</option>
                                @foreach($airports as $airport)
                                    <option value="{{ $airport->id }}" data-airport="{{ $airport->airport }}" data-city="{{ $airport->city }}" data-country="{{ $airport->country }}" data-code="{{ $airport->code }}" {{ old('leaving_from') == $airport->id ? 'selected' : '' }}>{{ $airport->airport }} - {{ $airport->city }}, {{ $airport->country }} ({{ $airport->code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Going To</label>
                            <select name="going_to" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-400 bg-gray-50 airport-select" id="going_to">
                                <option value="">Select Airport</option>
                                @foreach($airports as $airport)
                                    <option value="{{ $airport->id }}" data-airport="{{ $airport->airport }}" data-city="{{ $airport->city }}" data-country="{{ $airport->country }}" data-code="{{ $airport->code }}" {{ old('going_to') == $airport->id ? 'selected' : '' }}>{{ $airport->airport }} - {{ $airport->city }}, {{ $airport->country }} ({{ $airport->code }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Currency <span class="umr-accent">*</span></label>
                            <select name="currceny" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-400 bg-gray-50" required>
                                <option value="PKR" {{ old('currceny') == 'PKR' ? 'selected' : '' }}>PKR</option>
                                <option value="USD" {{ old('currceny') == 'USD' ? 'selected' : '' }}>USD</option>
                                <option value="SAR" {{ old('currceny') == 'SAR' ? 'selected' : '' }}>SAR</option>
                                <option value="GBP" {{ old('currceny') == 'GBP' ? 'selected' : '' }}>GBP</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Price <span class="umr-accent">*</span></label>
                            <input type="number" name="price" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-400 bg-gray-50" value="{{ old('price') }}" required>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Duration <span class="umr-accent">*</span></label>
                            <input type="text" name="duration" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-400 bg-gray-50" placeholder="e.g., 7 Days / 6 Nights" value="{{ old('duration') }}" required>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 font-semibold text-sm text-gray-700">Stay Details</div>
                <div class="p-5 space-y-4">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Check-in Date</label>
                            <input type="date" name="checkin_date" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-400 bg-gray-50" value="{{ old('checkin_date') }}">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Check-out Date</label>
                            <input type="date" name="checkout_date" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-400 bg-gray-50" value="{{ old('checkout_date') }}">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nights in Makkah</label>
                            <input type="number" name="night_in_mekkah" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-400 bg-gray-50" value="{{ old('night_in_mekkah') }}">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nights in Madinah</label>
                            <input type="number" name="night_in_madina" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-400 bg-gray-50" value="{{ old('night_in_madina') }}">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Class</label>
                            <select name="class" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-400 bg-gray-50">
                                <option value="">Select Class</option>
                                <option value="economy" {{ old('class') == 'economy' ? 'selected' : '' }}>Economy</option>
                                <option value="business" {{ old('class') == 'business' ? 'selected' : '' }}>Business</option>
                                <option value="first" {{ old('class') == 'first' ? 'selected' : '' }}>First Class</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Hotel Stars</label>
                            <select name="stars" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-400 bg-gray-50">
                                <option value="">Select Stars</option>
                                <option value="3" {{ old('stars') == '3' ? 'selected' : '' }}>3 Star</option>
                                <option value="4" {{ old('stars') == '4' ? 'selected' : '' }}>4 Star</option>
                                <option value="5" {{ old('stars') == '5' ? 'selected' : '' }}>5 Star</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 font-semibold text-sm text-gray-700">Guest Details</div>
                <div class="p-5">
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Adults</label>
                            <input type="number" name="adults" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-400 bg-gray-50" value="{{ old('adults', '1') }}">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Children</label>
                            <input type="number" name="childs" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-400 bg-gray-50" value="{{ old('childs', '0') }}">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Infants</label>
                            <input type="number" name="infants" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-400 bg-gray-50" value="{{ old('infants', '0') }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 font-semibold text-sm text-gray-700">Description & Policy</div>
                <div class="p-5 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Description <span class="umr-accent">*</span></label>
                        <textarea name="desc" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-400 bg-gray-50" rows="5" required>{{ old('desc') }}</textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Policy</label>
                        <textarea name="policy" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-400 bg-gray-50" rows="4">{{ old('policy') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 font-semibold text-sm text-gray-700">Inclusions & Exclusions</div>
                <div class="p-5 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Inclusions</label>
                        <div class="border border-gray-200 rounded-lg p-3 bg-gray-50 max-h-48 overflow-y-auto space-y-2">
                            @php $selectedInclusions = old('inclusions', []); @endphp
                            @foreach($inclusions as $inclusion)
                                <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                                    <input type="checkbox" name="inclusions[]" value="{{ $inclusion->id }}" class="rounded border-gray-300" {{ in_array($inclusion->id, $selectedInclusions) ? 'checked' : '' }}>
                                    {{ $inclusion->name }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Exclusions</label>
                        <div class="border border-gray-200 rounded-lg p-3 bg-gray-50 max-h-48 overflow-y-auto space-y-2">
                            @php $selectedExclusions = old('exclusions', []); @endphp
                            @foreach($exclusions as $exclusion)
                                <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                                    <input type="checkbox" name="exclusions[]" value="{{ $exclusion->id }}" class="rounded border-gray-300" {{ in_array($exclusion->id, $selectedExclusions) ? 'checked' : '' }}>
                                    {{ $exclusion->name }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="space-y-5">

            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 font-semibold text-sm text-gray-700">Status & Visibility</div>
                <div class="p-5 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Status</label>
                        <select name="status" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-400 bg-gray-50">
                            <option value="1" {{ old('status') == '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Featured</label>
                        <select name="featured" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-400 bg-gray-50">
                            <option value="0" {{ old('featured') == '0' ? 'selected' : '' }}>No</option>
                            <option value="1" {{ old('featured') == '1' ? 'selected' : '' }}>Yes</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Rating</label>
                        <input type="text" name="rating" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-400 bg-gray-50" placeholder="e.g., 4.5" value="{{ old('rating') }}">
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 font-semibold text-sm text-gray-700">Images</div>
                <div class="p-5">
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Upload Images</label>
                    <input type="file" name="images[]" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm bg-gray-50" multiple accept="image/*">
                    <p class="text-xs text-gray-400 mt-1.5">You can select multiple images</p>
                </div>
            </div>

            <div class="space-y-2">
                <button type="submit" class="w-full px-5 py-2.5 rounded-lg text-sm font-semibold text-white umr-btn-solid">
                    <i class="fas fa-check text-xs"></i> Create Package
                </button>
                <a href="{{ $backUrl ?? route('agent.umrah.index') }}" class="w-full px-5 py-2.5 rounded-lg text-sm font-semibold text-center block border border-gray-200 text-gray-600 hover:bg-gray-50 transition umr-no-underline">
                    Cancel
                </a>
            </div>

        </div>
    </div>
</form>

@endsection

@push('scripts')
<script src="{{ url('public/assets/js/jquery-3.7.1.min.js') }}"></script>
<script src="{{ url('public/assets/libs/select2/js/select2.min.js') }}"></script>
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
