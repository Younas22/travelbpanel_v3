@extends('common.layout')
@section('content')
    <div class="flight-container">
    <!-- Search Collapse -->
    <div class="hotel-search-collapse">
        <button class="hotel-search-collapse-btn" id="searchCollapseBtn">
            <span><i class="fas fa-search"></i> {{t('flightform.modify_search')}}</span>
            <i class="fas fa-chevron-down" id="collapseIcon"></i>
        </button>
        <div class="hotel-search-collapse-content" id="searchCollapseContent">
            <style>
            .form-container {
                max-width: 1200px !important;
            }
            </style>
            @include('forms.flight-form')
        </div>
    </div>

    @if(isset($flights_data) && isset($flight_search))
        @php
            // Suppliers don't reliably honor the requested search currency
            // (sandbox/test credentials especially), so the raw price +
            // currency returned per segment can't be trusted as-is. Every
            // displayed price is converted here into the site's active
            // currency instead, so the list always matches whatever
            // currency is active admin-side, regardless of what the
            // supplier actually returned.
            $activeCurrency = activeCurrency();
            $toActive = function ($price, $fromCurrency) use ($activeCurrency) {
                $clean = floatval(str_replace(',', '', $price));
                $from = $fromCurrency ?: $activeCurrency->currency_name;
                return $from === $activeCurrency->currency_name ? $clean : convertCurrency($clean, $from, $activeCurrency->currency_name);
            };
            // Total duration comes back in whichever shape the active
            // supplier formats it in — "04h:30m" (Amadeus), "4:13" (Duffel),
            // "4h 30m" (Sabre) — so this pulls the first two digit groups
            // (hours, then minutes) regardless of the separator between
            // them, for the Transit Hours filter.
            $toMinutes = function ($duration) {
                if (preg_match('/(\d+)\D+(\d+)/', (string) $duration, $m)) {
                    return ((int) $m[1] * 60) + (int) $m[2];
                }
                return 0;
            };
            // Some suppliers (Duffel) return baggage as a bare piece count
            // (0, 1, 2...) instead of a label — turn that into readable text
            // so the Luggage filter and the card/modal baggage display agree.
            $baggageLabel = function ($raw) {
                if ($raw === null || $raw === '') {
                    return 'No Luggage';
                }
                if (is_numeric($raw)) {
                    $n = (int) $raw;
                    return $n <= 0 ? 'No Luggage' : ($n . ' ' . ($n === 1 ? 'Piece' : 'Pieces'));
                }
                return (string) $raw;
            };
        @endphp
        <!-- Search Info Bar -->
        <div class="bg-white border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 py-3">
                <div class="flex flex-wrap items-center gap-4 text-sm">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-map-marker-alt text-[#0077BE]"></i>
                        <div>
                            <p class="text-xs font-bold text-gray-600">{{t('flightform.from')}}</p>
                            <p class="font-bold text-gray-900">{{ strtoupper($flight_search['origin'] ?? '') }}</p>
                        </div> 
                    </div>
                    <i class="fas fa-arrow-right text-gray-400"></i>
                    <div class="flex items-center gap-2">
                        <i class="fas fa-map-marker-alt text-[#0077BE]"></i>
                        <div>
                            <p class="text-xs font-bold text-gray-600">{{t('flightform.to')}}</p>
                            <p class="font-bold text-gray-900">{{ strtoupper($flight_search['destination'] ?? '') }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <i class="fas fa-calendar text-[#0077BE]"></i>
                        <div>
                            <p class="text-xs font-bold text-gray-600">{{t('flightform.date')}}</p>
                            <p class="font-bold text-gray-900">{{ isset($flight_search['departure_date']) ? \Carbon\Carbon::parse($flight_search['departure_date'])->format('d M Y') : '' }}</p>
                        </div>
                    </div>
                    @if($flight_search['trip_type'] == "round")
                    <div class="flex items-center gap-2">
                        <i class="fas fa-calendar text-[#0077BE]"></i>
                        <div>
                            <p class="text-xs font-bold text-gray-600">{{t('flightform.return')}}</p>
                            <p class="font-bold text-gray-900">{{ isset($flight_search['return_date']) ? \Carbon\Carbon::parse($flight_search['return_date'])->format('d M Y') : '' }}</p>
                        </div>
                    </div>
                    @endif
                    <div class="flex items-center gap-2">
                        <i class="fas fa-users text-[#0077BE]"></i>
                        <div>
                            <p class="text-xs font-bold text-gray-600">{{t('flightform.passengers')}}</p>
                            <p class="font-bold text-gray-900">{{ $flight_search['passenger_count'] ?? 1 }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto px-4 py-6">
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                <!-- Sidebar -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-lg shadow-sm p-5 sticky top-20">
                        <h3 class="text-sm font-bold text-gray-900 mb-4">{{t('flightlist.filters')}}</h3>

                        <!-- Stops (Radio Buttons) -->
                        @php
                            $check_stops = [];
                            foreach ($flights_data as $item) {
                                foreach ($item['segments'] as $segments) {
                                    $stops = count($segments) - 1;
                                    $check_stops[$stops] = true;
                                }
                            }
                            ksort($check_stops);
                        @endphp
                        <div class="mb-6 pb-6 border-b border-gray-200">
                            <p class="text-xs font-bold text-gray-600 mb-3">{{t('flightlist.stops')}}</p>
                            <div class="space-y-2">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="stops" value="" checked class="w-4 h-4 accent-[#0077BE]">
                                    <span class="text-sm text-gray-700">{{t('flightlist.all_flights')}}</span>
                                </label>
                                @foreach ($check_stops as $stop_count => $value)
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="stops" value="{{ $stop_count }}" class="w-4 h-4 accent-[#0077BE]">
                                    <span class="text-sm text-gray-700">
                                        @if ($stop_count === 0)
                                            {{t('flightlist.nonstop')}}
                                        @elseif ($stop_count === 1)
                                            1 {{t('flightlist.stop')}}
                                        @else
                                            {{ $stop_count }} {{t('flightlist.stops')}}
                                        @endif
                                    </span>
                                </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Flight Time -->
                        @php
                            $timeBuckets = [
                                'early_morning' => ['label' => t('flightlist.early_morning'), 'range' => '00-06', 'from' => 0, 'to' => 6, 'icon' => 'fa-cloud-moon', 'tint' => '#ECFDF5'],
                                'morning'       => ['label' => t('flightlist.morning'), 'range' => '06-12', 'from' => 6, 'to' => 12, 'icon' => 'fa-sun', 'tint' => '#ECFDF5'],
                                'afternoon'     => ['label' => t('flightlist.afternoon'), 'range' => '12-18', 'from' => 12, 'to' => 18, 'icon' => 'fa-cloud-sun', 'tint' => '#EFF4FA'],
                                'evening'       => ['label' => t('flightlist.evening'), 'range' => '18-24', 'from' => 18, 'to' => 24, 'icon' => 'fa-moon', 'tint' => '#EFF4FA'],
                            ];
                        @endphp
                        <style>
                            .ft-row { padding: 12px 14px; border-radius: 12px; transition: box-shadow .15s; }
                            .ft-row:has(input:checked) { box-shadow: inset 0 0 0 2px #059669; }
                            .ft-tab-btn { flex: 1; padding: 8px 0; border-radius: 9999px; font-size: 13px; font-weight: 600; transition: all .15s; cursor: pointer; }
                        </style>
                        <div class="mb-6 pb-6 border-b border-gray-200">
                            <button type="button" id="flightTimeToggle" class="w-full flex items-center justify-between text-left">
                                <span class="text-sm font-bold text-gray-900">{{t('flightlist.flight_time')}}</span>
                                <i class="fas fa-chevron-down text-gray-400 text-xs transition-transform" id="flightTimeChevron"></i>
                            </button>

                            <div id="flightTimeBody" class="mt-4">
                                <div class="flex gap-1 p-1 bg-gray-50 rounded-full mb-4">
                                    <button type="button" class="ft-tab-btn ft-tab-active" data-tab="departure" style="background:#ECFDF5; color:#059669;">
                                        {{t('flightlist.departure')}}
                                    </button>
                                    <button type="button" class="ft-tab-btn" data-tab="arrival" style="background:#fff; color:#111827; border:1px solid #E5E7EB;">
                                        {{t('flightlist.arrival')}}
                                    </button>
                                </div>

                                <div id="ft-panel-departure" class="space-y-2.5">
                                    @foreach($timeBuckets as $key => $bucket)
                                    <label class="ft-row flex items-center justify-between cursor-pointer" style="background:{{ $bucket['tint'] }};">
                                        <span class="flex items-center gap-3">
                                            <i class="fas {{ $bucket['icon'] }}" style="color:#475569; width:16px;"></i>
                                            <span class="text-sm font-medium text-gray-800">{{ $bucket['label'] }}</span>
                                        </span>
                                        <span class="flex items-center gap-2">
                                            <span class="text-xs text-gray-500">{{ $bucket['range'] }}</span>
                                            <input type="checkbox" class="dep-time-checkbox" value="{{ $key }}" style="display:none;">
                                        </span>
                                    </label>
                                    @endforeach
                                </div>

                                <div id="ft-panel-arrival" class="space-y-2.5 hidden">
                                    @foreach($timeBuckets as $key => $bucket)
                                    <label class="ft-row flex items-center justify-between cursor-pointer" style="background:{{ $bucket['tint'] }};">
                                        <span class="flex items-center gap-3">
                                            <i class="fas {{ $bucket['icon'] }}" style="color:#475569; width:16px;"></i>
                                            <span class="text-sm font-medium text-gray-800">{{ $bucket['label'] }}</span>
                                        </span>
                                        <span class="flex items-center gap-2">
                                            <span class="text-xs text-gray-500">{{ $bucket['range'] }}</span>
                                            <input type="checkbox" class="arr-time-checkbox" value="{{ $key }}" style="display:none;">
                                        </span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Price -->
                        @php
                            $prices = [];
                            foreach ($flights_data as $item) {
                                if (isset($item['segments'][0][0]['price'])) {
                                    $prices[] = $toActive($item['segments'][0][0]['price'], $item['segments'][0][0]['currency'] ?? null);
                                }
                            }
                            $minPrice = !empty($prices) ? min($prices) : 0;
                            $maxPrice = !empty($prices) ? max($prices) : 0;
                        @endphp
                        <div class="mb-6 pb-6 border-b border-gray-200">
                            <p class="text-xs font-bold text-gray-600 mb-3">{{t('flightlist.price')}}</p>
                            <input type="range" min="{{ $minPrice }}" max="{{ $maxPrice }}" value="{{ $maxPrice }}" id="priceSlider" class="w-full accent-[#0077BE]">
                            <div class="flex justify-between text-xs text-gray-600 mt-2">
                                <span class="inline-flex items-center gap-1"><span class="{{ getFlagClass($activeCurrency->currency_name) }}"></span>{{ $activeCurrency->currency_name }} {{ number_format($minPrice, 2) }}</span>
                                <span class="inline-flex items-center gap-1"><span class="{{ getFlagClass($activeCurrency->currency_name) }}"></span>{{ $activeCurrency->currency_name }} <span id="priceValue">{{ number_format($maxPrice, 2) }}</span></span>
                            </div>
                        </div>

                        <!-- Transit Hours -->
                        @php
                            $durations = [];
                            foreach ($flights_data as $item) {
                                if (isset($item['segments'][0][0]['total_duration'])) {
                                    $durations[] = $toMinutes($item['segments'][0][0]['total_duration']);
                                }
                            }
                            $minDuration = !empty($durations) ? min($durations) : 0;
                            $maxDuration = !empty($durations) ? max($durations) : 0;
                        @endphp
                        <div class="mb-6 pb-6 border-b border-gray-200">
                            <p class="text-xs font-bold text-gray-600 mb-3">{{t('flightlist.transit_hours')}}</p>
                            <input type="range" min="{{ $minDuration }}" max="{{ $maxDuration }}" value="{{ $maxDuration }}" id="durationSlider" class="w-full accent-[#0077BE]">
                            <div class="flex justify-between text-xs text-gray-600 mt-2">
                                <span>{{ floor($minDuration / 60) }}h</span>
                                <span id="durationValue">{{ ceil($maxDuration / 60) }}h</span>
                            </div>
                        </div>

                        <!-- Luggage -->
                        @php
                            $displayed_baggage = [];
                            foreach ($flights_data as $item) {
                                $baggage = $baggageLabel($item['segments'][0][0]['baggage'] ?? null);
                                if (!in_array($baggage, $displayed_baggage)) {
                                    $displayed_baggage[] = $baggage;
                                }
                            }
                        @endphp
                        <div class="mb-6 pb-6 border-b border-gray-200">
                            <p class="text-xs font-bold text-gray-600 mb-3">{{t('flightlist.luggage')}}</p>
                            <div class="space-y-2">
                                @foreach($displayed_baggage as $baggage)
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" class="w-4 h-4 accent-[#0077BE] baggage-checkbox" value="{{ $baggage }}">
                                    <span class="text-sm text-gray-700">{{ $baggage }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Preferred Airlines -->
                        @php
                            $displayed_airlines = [];
                            $airline_map = [];
                            foreach ($flights_data as $item) {
                                foreach ($item['segments'] as $segments) {
                                    $airline_code = $segments[0]['carrier']['operating'];
                                    $airline_name = $segments[0]['airline_name'] ?? '';
                                    if (!in_array($airline_code, $displayed_airlines) && !empty($airline_name)) {
                                        $displayed_airlines[] = $airline_code;
                                        $airline_map[$airline_code] = $airline_name;
                                    }
                                }
                            }
                        @endphp
                        <div>
                            <p class="text-xs font-bold text-gray-600 mb-3">{{t('flightlist.preferred_airlines')}}</p>
                            <div class="space-y-3">
                                @foreach ($airline_map as $code => $name)
                                <label class="flex items-center gap-2 cursor-pointer airline-filter" data-airline="{{ $code }}">
                                    <img
                                        src="https://assets.duffel.com/img/airlines/for-light-background/full-color-logo/{{ $code }}.svg"
                                        alt="{{ $name }}"
                                        class="w-8 h-8 object-contain"
                                        onerror="this.onerror=null;this.style.display='none';this.nextElementSibling.style.display='flex';"
                                    />
                                    <div class="w-8 h-8 bg-gradient-to-br from-[#0077BE] to-blue-800 rounded flex items-center justify-center" style="display:none;">
                                        <span class="text-xs font-bold text-white">{{ strtoupper(substr($code, 0, 2)) }}</span>
                                    </div>
                                    <input type="checkbox" class="w-4 h-4 accent-[#0077BE] airline-checkbox">
                                    <span class="text-xs text-gray-700">{{ $name }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Flight Cards -->
                <div class="lg:col-span-3 space-y-4">
                    <div class="mb-4">
                        <p class="text-gray-600"><span id="flightCount">{{ $flights_data->total() }}</span> {{t('flightlist.flights_found')}}</p>
                    </div>

                    @if(isset($flight_search['trip_type']) && $flight_search['trip_type'] == "oneway")

                        @php $modalCounter = 0; @endphp
                        @foreach($flights_data as $key => $segment)
                        @php $modalCounter++; @endphp
                        <div class="flight-card bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition"
                             data-stops="{{ count($segment['segments'][0]) - 1 }}"
                             data-airline="{{ $segment['segments'][0][0]['carrier']['operating'] }}"
                             data-price="{{ $toActive($segment['segments'][0][0]['price'], $segment['segments'][0][0]['currency'] ?? null) }}"
                             data-dep-hour="{{ $segment['segments'][0][0]['departure']['hour'] ?? '' }}"
                             data-arr-hour="{{ end($segment['segments'][0])['arrival']['hour'] ?? '' }}"
                             data-baggage="{{ $baggageLabel($segment['segments'][0][0]['baggage'] ?? null) }}"
                             data-duration="{{ $toMinutes($segment['segments'][0][0]['total_duration'] ?? '') }}">

                            <div class="flex">
                                <!-- Airline Logo -->
                                <div class="w-32 flex items-center justify-center border-r border-gray-200 p-4">
                                    <img
                                        src="https://assets.duffel.com/img/airlines/for-light-background/full-color-logo/{{ $segment['segments'][0][0]['carrier']['operating'] }}.svg"
                                        alt="Airline Logo"
                                        class="w-20 h-20 object-contain"
                                        onerror="this.onerror=null;this.src='{{ url('public/assets/images/list-plane.png') }}';"
                                    />
                                </div>

                                <!-- Flight Details -->
                                <div class="flex-1 p-6">
                                    <div class="flex justify-between items-start mb-4">
                                        <div>
                                            <p class="text-xs text-gray-600 font-semibold">
                                                {{ $segment['segments'][0][0]['departure']['airport'] }} to {{ end($segment['segments'][0])['arrival']['airport'] }} •
                                                {{ count($segment['segments'][0]) - 1 == 0 ? 'Direct' : (count($segment['segments'][0]) - 1 . ' Stop') }}
                                            </p>
                                            <p class="text-sm text-gray-500">{{ $segment['segments'][0][0]['departure']['date_convert'] }}</p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-xs text-gray-600">{{t('flightlist.total_fare')}}</p>
                                            <p class="text-2xl font-bold text-[#0077BE] inline-flex items-center gap-1.5"><span class="{{ getFlagClass($activeCurrency->currency_name) }}"></span>{{ $activeCurrency->currency_name }} {{ number_format($toActive($segment['segments'][0][0]['price'], $segment['segments'][0][0]['currency'] ?? null), 2) }}</p>
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between mb-4 pb-4 border-b border-gray-200">
                                        <div class="flex-1">
                                            <p class="text-3xl font-bold text-gray-900">{{ $segment['segments'][0][0]['departure']['time'] }}</p>
                                            <p class="text-sm font-semibold text-gray-700">{{ $segment['segments'][0][0]['departure']['airport'] }}</p>
                                        </div>
                                        <div class="flex-1 text-center">
                                            <div class="flex items-center justify-center mb-2">
                                                <div class="flex-1 h-0.5 bg-gray-300"></div>
                                                <i class="fas fa-plane text-gray-400 mx-4"></i>
                                                <div class="flex-1 h-0.5 bg-gray-300"></div>
                                            </div>
                                            <p class="text-xs text-gray-600">{{ $segment['segments'][0][0]['total_duration'] }}</p>
                                            <p class="text-xs font-semibold text-[#0077BE]">
                                                @if(count($segment['segments'][0]) - 1 == 0)
                                                    {{t('flightlist.direct')}}
                                                @else
                                                    {{ count($segment['segments'][0]) - 1 }} {{t('flightlist.stops')}}
                                                @endif
                                            </p>
                                        </div>
                                        <div class="flex-1 text-right">
                                            <p class="text-3xl font-bold text-gray-900">{{ end($segment['segments'][0])['arrival']['time'] }}</p>
                                            <p class="text-sm font-semibold text-gray-700">{{ end($segment['segments'][0])['arrival']['airport'] }}</p>
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-4">
                                            <div class="flex items-center gap-2">
                                                <i class="fas fa-suitcase text-gray-600"></i>
                                                <span class="text-sm text-gray-700">{{ $segment['segments'][0][0]['cabin_baggage'] ?? '7 kg' }}</span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <i class="fas fa-suitcase-rolling text-gray-600"></i>
                                                <span class="text-sm text-gray-700">{{ $baggageLabel($segment['segments'][0][0]['baggage'] ?? null) }}</span>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <button onclick="openModal{{ $modalCounter }}()" class="p-2 hover:bg-gray-100 rounded-lg transition" title="Details">
                                                <i class="fas fa-circle-info text-gray-600"></i>
                                            </button>
                                            <form action="{{ route('flights.booking') }}" method="post" class="inline">
                                                @csrf
                                                <input name="routes" type="hidden" value="{{ encrypt(json_encode($segment))}}">
                                                <input name="booking_data" type="hidden" value="{{ encrypt(json_encode($segment['segments'][0][0]['booking_data'])) }}">
                                                <button type="submit" class="px-6 py-2 bg-[#0077BE] text-white rounded-full font-semibold text-sm hover:bg-blue-700">
                                                    {{t('flightlist.book')}} →
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal for One Way -->
                        <div id="modal{{ $modalCounter }}" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
                            <div class="bg-white rounded-3xl w-full max-w-2xl border-8 border-white shadow-2xl">
                                <div class="bg-[#0077BE] text-white px-6 py-4 rounded-t-2xl flex justify-between items-start">
                                    <div>
                                        <h2 class="text-lg font-bold-">{{ $segment['segments'][0][0]['departure']['airport'] }} to {{ end($segment['segments'][0])['arrival']['airport'] }}</h2>
                                        <p class="text-xs text-blue-200">{{ $segment['segments'][0][0]['departure']['date_convert'] }}</p>
                                    </div>
                                    <button onclick="closeModal{{ $modalCounter }}()" class="p-1 hover:bg-blue-800 rounded-lg transition">
                                        <i class="fas fa-times text-lg"></i>
                                    </button>
                                </div>

                                <div class="bg-white px-6 py-5 rounded-b-2xl border-2 border-gray-200 max-h-96 overflow-y-auto">
                                    @foreach($segment['segments'][0] as $seg)
                                    <div class="mb-6 pb-6 border-b border-gray-200 last:border-b-0">
                                        <!-- Airline Info -->
                                        <div class="flex justify-between items-start mb-4">
                                            <div class="flex gap-3">
                                                <div class="w-12 h-12 flex items-center justify-center">
                                                    <img
                                                        src="https://assets.duffel.com/img/airlines/for-light-background/full-color-logo/{{ $seg['carrier']['operating'] }}.svg"
                                                        alt="Airline Logo"
                                                        class="w-full h-full object-contain"
                                                        onerror="this.onerror=null;this.src='{{ url('public/assets/images/list-plane.png') }}';"
                                                    />
                                                </div>
                                                <div>
                                                    <p class="text-sm font-bold text-gray-900">{{ $seg['airline_name'] }}</p>
                                                    <p class="text-xs text-gray-600">{{ $seg['flight_number'] }} • Economy</p>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <div class="flex gap-2 justify-end mb-1">
                                                    <i class="fas fa-suitcase text-gray-600 text-xs"></i>
                                                    <p class="text-xs text-gray-600">{{ $seg['cabin_baggage'] ?? '7 kg' }}</p>
                                                </div>
                                                <div class="flex gap-2 justify-end">
                                                    <i class="fas fa-suitcase-rolling text-gray-600 text-xs"></i>
                                                    <p class="text-xs text-gray-600">{{ $baggageLabel($seg['baggage'] ?? null) }}</p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Flight Segment -->
                                        <div class="grid grid-cols-3 gap-4">
                                            <div class="text-left">
                                                <p class="text-2xl font-bold- text-gray-900">{{ $seg['departure']['airport'] }} {{ $seg['departure']['time'] }}</p>
                                                <p class="text-xs text-gray-600 mt-1">{{ $seg['departure']['date_convert'] }}</p>
                                                @if(!empty($seg['departure']['terminal']))
                                                <div class="flex items-center gap-1 mt-2">
                                                    <i class="fas fa-location-dot text-gray-400 text-xs"></i>
                                                    <p class="text-xs text-gray-500">Terminal {{ $seg['departure']['terminal'] }}</p>
                                                </div>
                                                @endif
                                                <p class="text-xs text-gray-600">{{ $seg['departure']['city_name'] }}</p>
                                            </div>
                                            <div class="flex flex-col items-center justify-center">
                                                <i class="fas fa-clock text-gray-400 text-lg mb-1"></i>
                                                <p class="text-xs font-bold text-gray-700">{{ $seg['duration'] }}</p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-2xl font-bold- text-gray-900">{{ $seg['arrival']['airport'] }} {{ $seg['arrival']['time'] }}</p>
                                                <p class="text-xs text-gray-600 mt-1">{{ $seg['arrival']['date_convert'] }}</p>
                                                @if(!empty($seg['arrival']['terminal']))
                                                <div class="flex items-center justify-end gap-1 mt-2">
                                                    <i class="fas fa-location-dot text-gray-400 text-xs"></i>
                                                    <p class="text-xs text-gray-500">Terminal {{ $seg['arrival']['terminal'] }}</p>
                                                </div>
                                                @endif
                                                <p class="text-xs text-gray-600">{{ $seg['arrival']['city_name'] }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endforeach
                    @endif

                    @if(isset($flight_search['trip_type']) && $flight_search['trip_type'] == "round")
                        @php $modalCounter = 0; @endphp
                        @foreach($flights_data as $key => $segment)
                        @php $modalCounter++; @endphp
                        <div class="flight-card bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition"
                             data-stops="{{ count($segment['segments'][0]) - 1 }}"
                             data-airline="{{ $segment['segments'][0][0]['carrier']['operating'] }}"
                             data-price="{{ $toActive($segment['segments'][0][0]['price'], $segment['segments'][0][0]['currency'] ?? null) }}"
                             data-dep-hour="{{ $segment['segments'][0][0]['departure']['hour'] ?? '' }}"
                             data-arr-hour="{{ end($segment['segments'][0])['arrival']['hour'] ?? '' }}"
                             data-baggage="{{ $baggageLabel($segment['segments'][0][0]['baggage'] ?? null) }}"
                             data-duration="{{ $toMinutes($segment['segments'][0][0]['total_duration'] ?? '') }}">

                            <div class="flex">
                                <!-- Airline Logo -->
                                <div class="w-32 flex items-center justify-center border-r border-gray-200 p-4">
                                    <img
                                        src="https://assets.duffel.com/img/airlines/for-light-background/full-color-logo/{{ $segment['segments'][0][0]['carrier']['operating'] }}.svg"
                                        alt="Airline Logo"
                                        class="w-20 h-20 object-contain"
                                        onerror="this.onerror=null;this.src='{{ url('public/assets/images/list-plane.png') }}';"
                                    />
                                </div>

                                <!-- Flight Details -->
                                <div class="flex-1 p-6">
                                    <div class="flex justify-between items-start mb-4">
                                        <div>
                                            <p class="text-xs text-gray-600 font-semibold">{{ $segment['segments'][0][0]['departure']['airport'] }} ↔ {{ end($segment['segments'][0])['arrival']['airport'] }} • Round Trip</p>
                                            <p class="text-sm text-gray-500">
                                                {{ $segment['segments'][0][0]['departure']['date_convert'] }} - {{ $segment['segments'][1][0]['departure']['date_convert'] }}
                                            </p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-xs text-gray-600">{{t('flightlist.total_fare')}}</p>
                                            <p class="text-2xl font-bold text-[#0077BE] inline-flex items-center gap-1.5"><span class="{{ getFlagClass($activeCurrency->currency_name) }}"></span>{{ $activeCurrency->currency_name }} {{ number_format($toActive($segment['segments'][0][0]['price'], $segment['segments'][0][0]['currency'] ?? null), 2) }}</p>
                                        </div>
                                    </div>

                                    <!-- Outbound -->
                                    <div class="flex items-center justify-between mb-4 pb-4 border-b border-gray-200">
                                        <div class="flex-1">
                                            <p class="text-3xl font-bold text-gray-900">{{ $segment['segments'][0][0]['departure']['time'] }}</p>
                                            <p class="text-sm font-semibold text-gray-700">{{ $segment['segments'][0][0]['departure']['airport'] }}</p>
                                        </div>
                                        <div class="flex-1 text-center">
                                            <div class="flex items-center justify-center mb-2">
                                                <div class="flex-1 h-0.5 bg-gray-300"></div>
                                                <i class="fas fa-plane text-gray-400 mx-4"></i>
                                                <div class="flex-1 h-0.5 bg-gray-300"></div>
                                            </div>
                                            <p class="text-xs text-gray-600">{{ $segment['segments'][0][0]['total_duration'] }}</p>
                                            <p class="text-xs font-semibold text-[#0077BE]">
                                                @if(count($segment['segments'][0]) - 1 == 0)
                                                    {{t('flightlist.direct')}}
                                                @else
                                                    {{ count($segment['segments'][0]) - 1 }} {{t('flightlist.stops')}}
                                                @endif
                                            </p>
                                        </div>
                                        <div class="flex-1 text-right">
                                            <p class="text-3xl font-bold text-gray-900">{{ end($segment['segments'][0])['arrival']['time'] }}</p>
                                            <p class="text-sm font-semibold text-gray-700">{{ end($segment['segments'][0])['arrival']['airport'] }}</p>
                                        </div>
                                    </div>

                                    <!-- Return -->
                                    <div class="flex items-center justify-between mb-4 pb-4">
                                        <div class="flex-1">
                                            <p class="text-3xl font-bold text-gray-900">{{ $segment['segments'][1][0]['departure']['time'] }}</p>
                                            <p class="text-sm font-semibold text-gray-700">{{ $segment['segments'][1][0]['departure']['airport'] }}</p>
                                        </div>
                                        <div class="flex-1 text-center">
                                            <div class="flex items-center justify-center mb-2">
                                                <div class="flex-1 h-0.5 bg-gray-300"></div>
                                                <i class="fas fa-plane text-gray-400 mx-4"></i>
                                                <div class="flex-1 h-0.5 bg-gray-300"></div>
                                            </div>
                                            <p class="text-xs text-gray-600">{{ $segment['segments'][1][0]['total_duration'] }}</p>
                                            <p class="text-xs font-semibold text-[#0077BE]">
                                                @if(count($segment['segments'][1]) - 1 == 0)
                                                    {{t('flightlist.direct')}}
                                                @else
                                                    {{ count($segment['segments'][1]) - 1 }} {{t('flightlist.stops')}}
                                                @endif
                                            </p>
                                        </div>
                                        <div class="flex-1 text-right">
                                            <p class="text-3xl font-bold text-gray-900">{{ end($segment['segments'][1])['arrival']['time'] }}</p>
                                            <p class="text-sm font-semibold text-gray-700">{{ end($segment['segments'][1])['arrival']['airport'] }}</p>
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                                        <div class="flex items-center gap-4">
                                            <div class="flex items-center gap-2">
                                                <i class="fas fa-suitcase text-gray-600"></i>
                                                <span class="text-sm text-gray-700">{{ $segment['segments'][0][0]['cabin_baggage'] ?? '7 kg' }}</span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <i class="fas fa-suitcase-rolling text-gray-600"></i>
                                                <span class="text-sm text-gray-700">{{ $baggageLabel($segment['segments'][0][0]['baggage'] ?? null) }}</span>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <button onclick="openRoundModal{{ $modalCounter }}()" class="p-2 hover:bg-gray-100 rounded-lg transition" title="Details">
                                                <i class="fas fa-circle-info text-gray-600"></i>
                                            </button>
                                            <form action="{{ route('flights.booking') }}" method="post" class="inline">
                                                @csrf
                                                <input name="routes" type="hidden" value="{{ encrypt(json_encode($segment))}}">
                                                <input name="booking_data" type="hidden" value="{{ encrypt(json_encode($segment['segments'][0][0]['booking_data'])) }}">
                                                <button type="submit" class="px-6 py-2 bg-[#0077BE] text-white rounded-full font-semibold text-sm hover:bg-blue-700">
                                                    {{t('flightlist.book')}} →
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal for Round Trip -->
                        <div id="roundModal{{ $modalCounter }}" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
                            <div class="bg-white rounded-3xl w-full max-w-2xl border-8 border-white shadow-2xl max-h-[90vh] overflow-y-auto">
                                <!-- Outbound Flight -->
                                <div class="bg-[#0077BE] text-white px-6 py-4 rounded-t-2xl flex justify-between items-start">
                                    <div>
                                        <h2 class="text-lg font-bold-">{{t('flightlist.outbound')}}: {{ $segment['segments'][0][0]['departure']['airport'] }} to {{ end($segment['segments'][0])['arrival']['airport'] }}</h2>
                                        <p class="text-xs text-blue-200">{{ $segment['segments'][0][0]['departure']['date_convert'] }}</p>
                                    </div>
                                    <button onclick="closeRoundModal{{ $modalCounter }}()" class="p-1 hover:bg-blue-800 rounded-lg transition">
                                        <i class="fas fa-times text-lg"></i>
                                    </button>
                                </div>

                                <div class="bg-white px-6 py-5 border-2 border-gray-200">
                                    @foreach($segment['segments'][0] as $seg)
                                    <div class="mb-6 pb-6 border-b border-gray-200 last:border-b-0">
                                        <div class="flex justify-between items-start mb-4">
                                            <div class="flex gap-3">
                                                <div class="w-12 h-12 flex items-center justify-center">
                                                    <img
                                                        src="https://assets.duffel.com/img/airlines/for-light-background/full-color-logo/{{ $seg['carrier']['operating'] }}.svg"
                                                        alt="Airline Logo"
                                                        class="w-full h-full object-contain"
                                                        onerror="this.onerror=null;this.src='{{ url('public/assets/images/list-plane.png') }}';"
                                                    />
                                                </div>
                                                <div>
                                                    <p class="text-sm font-bold- text-gray-900">{{ $seg['airline_name'] }}</p>
                                                    <p class="text-xs text-gray-600">{{ $seg['flight_number'] }} • Economy</p>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <div class="flex gap-2 justify-end mb-1">
                                                    <i class="fas fa-suitcase text-gray-600 text-xs"></i>
                                                    <p class="text-xs text-gray-600">{{ $seg['cabin_baggage'] ?? '7 kg' }}</p>
                                                </div>
                                                <div class="flex gap-2 justify-end">
                                                    <i class="fas fa-suitcase-rolling text-gray-600 text-xs"></i>
                                                    <p class="text-xs text-gray-600">{{ $baggageLabel($seg['baggage'] ?? null) }}</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-3 gap-4">
                                            <div class="text-left">
                                                <p class="text-2xl font-bold- text-gray-900">{{ $seg['departure']['airport'] }} {{ $seg['departure']['time'] }}</p>
                                                <p class="text-xs text-gray-600 mt-1">{{ $seg['departure']['date_convert'] }}</p>
                                                @if(!empty($seg['departure']['terminal']))
                                                <div class="flex items-center gap-1 mt-2">
                                                    <i class="fas fa-location-dot text-gray-400 text-xs"></i>
                                                    <p class="text-xs text-gray-500">Terminal {{ $seg['departure']['terminal'] }}</p>
                                                </div>
                                                @endif
                                                <p class="text-xs text-gray-600">{{ $seg['departure']['city_name'] }}</p>
                                            </div>
                                            <div class="flex flex-col items-center justify-center">
                                                <i class="fas fa-clock text-gray-400 text-lg mb-1"></i>
                                                <p class="text-xs font-bold text-gray-700">{{ $seg['duration'] }}</p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-2xl font-bold- text-gray-900">{{ $seg['arrival']['airport'] }} {{ $seg['arrival']['time'] }}</p>
                                                <p class="text-xs text-gray-600 mt-1">{{ $seg['arrival']['date_convert'] }}</p>
                                                @if(!empty($seg['arrival']['terminal']))
                                                <div class="flex items-center justify-end gap-1 mt-2">
                                                    <i class="fas fa-location-dot text-gray-400 text-xs"></i>
                                                    <p class="text-xs text-gray-500">Terminal {{ $seg['arrival']['terminal'] }}</p>
                                                </div>
                                                @endif
                                                <p class="text-xs text-gray-600">{{ $seg['arrival']['city_name'] }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>

                                <!-- Return Flight -->
                                <div class="bg-[#0077BE] text-white px-6 py-4 mt-4">
                                    <div>
                                        <h2 class="text-lg font-bold-">{{t('flightform.return')}}: {{ $segment['segments'][1][0]['departure']['airport'] }} to {{ end($segment['segments'][1])['arrival']['airport'] }}</h2>
                                        <p class="text-xs text-blue-200">{{ $segment['segments'][1][0]['departure']['date_convert'] }}</p>
                                    </div>
                                </div>

                                <div class="bg-white px-6 py-5 rounded-b-2xl border-2 border-gray-200">
                                    @foreach($segment['segments'][1] as $seg)
                                    <div class="mb-6 pb-6 border-b border-gray-200 last:border-b-0">
                                        <div class="flex justify-between items-start mb-4">
                                            <div class="flex gap-3">
                                                <div class="w-12 h-12 flex items-center justify-center">
                                                    <img
                                                        src="https://assets.duffel.com/img/airlines/for-light-background/full-color-logo/{{ $seg['carrier']['operating'] }}.svg"
                                                        alt="Airline Logo"
                                                        class="w-full h-full object-contain"
                                                        onerror="this.onerror=null;this.src='{{ url('public/assets/images/list-plane.png') }}';"
                                                    />
                                                </div>
                                                <div>
                                                    <p class="text-sm font-bold- text-gray-900">{{ $seg['airline_name'] }}</p>
                                                    <p class="text-xs text-gray-600">{{ $seg['flight_number'] }} • Economy</p>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <div class="flex gap-2 justify-end mb-1">
                                                    <i class="fas fa-suitcase text-gray-600 text-xs"></i>
                                                    <p class="text-xs text-gray-600">{{ $seg['cabin_baggage'] ?? '7 kg' }}</p>
                                                </div>
                                                <div class="flex gap-2 justify-end">
                                                    <i class="fas fa-suitcase-rolling text-gray-600 text-xs"></i>
                                                    <p class="text-xs text-gray-600">{{ $baggageLabel($seg['baggage'] ?? null) }}</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-3 gap-4">
                                            <div class="text-left">
                                                <p class="text-2xl font-bold- text-gray-900">{{ $seg['departure']['airport'] }} {{ $seg['departure']['time'] }}</p>
                                                <p class="text-xs text-gray-600 mt-1">{{ $seg['departure']['date_convert'] }}</p>
                                                @if(!empty($seg['departure']['terminal']))
                                                <div class="flex items-center gap-1 mt-2">
                                                    <i class="fas fa-location-dot text-gray-400 text-xs"></i>
                                                    <p class="text-xs text-gray-500">Terminal {{ $seg['departure']['terminal'] }}</p>
                                                </div>
                                                @endif
                                                <p class="text-xs text-gray-600">{{ $seg['departure']['city_name'] }}</p>
                                            </div>
                                            <div class="flex flex-col items-center justify-center">
                                                <i class="fas fa-clock text-gray-400 text-lg mb-1"></i>
                                                <p class="text-xs font-bold text-gray-700">{{ $seg['duration'] }}</p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-2xl font-bold- text-gray-900">{{ $seg['arrival']['airport'] }} {{ $seg['arrival']['time'] }}</p>
                                                <p class="text-xs text-gray-600 mt-1">{{ $seg['arrival']['date_convert'] }}</p>
                                                @if(!empty($seg['arrival']['terminal']))
                                                <div class="flex items-center justify-end gap-1 mt-2">
                                                    <i class="fas fa-location-dot text-gray-400 text-xs"></i>
                                                    <p class="text-xs text-gray-500">Terminal {{ $seg['arrival']['terminal'] }}</p>
                                                </div>
                                                @endif
                                                <p class="text-xs text-gray-600">{{ $seg['arrival']['city_name'] }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endforeach
                    @endif

                    <!-- Pagination -->
                    <div class="mt-6">
                        {{ $flights_data->links() }}
                    </div>
                </div>
            </div>
        </div>
        @else

        <div class="max-w-4xl mx-auto px-5 py-12">
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-8 text-center">
                <div class="flex justify-center mb-4">
                    <div class="bg-red-100 text-red-600 p-4 rounded-full">
                        <i class="fas fa-plane-slash text-3xl"></i>
                    </div>
                </div>

                <h2 class="text-xl font-bold text-gray-800 mb-2">{{t('flightlist.no_flights_found')}}</h2>

                <p class="text-sm text-gray-600 mb-4">
                    {{t('flightlist.no_flights_message')}}
                </p>

                <a href="{{ url('/flights') }}"
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow">
                    <i class="fas fa-search"></i>
                    {{t('flightlist.search_again')}}
                </a>
            </div>
        </div>

    @endif

    </div>

    @if(isset($flights_data) && isset($flight_search))
    <script>
        // Generate modal functions dynamically
        @if(isset($flight_search['trip_type']) && $flight_search['trip_type'] == "oneway")
            @php $modalCounter = 0; @endphp
            @foreach($flights_data as $key => $segment)
                @php $modalCounter++; @endphp
                function openModal{{ $modalCounter }}() {
                    document.getElementById('modal{{ $modalCounter }}').classList.remove('hidden');
                }
                function closeModal{{ $modalCounter }}() {
                    document.getElementById('modal{{ $modalCounter }}').classList.add('hidden');
                }
                // Close on backdrop click
                document.getElementById('modal{{ $modalCounter }}').addEventListener('click', function(e) {
                    if (e.target === this) {
                        closeModal{{ $modalCounter }}();
                    }
                });
            @endforeach
        @endif

        @if(isset($flight_search['trip_type']) && $flight_search['trip_type'] == "round")
            @php $modalCounter = 0; @endphp
            @foreach($flights_data as $key => $segment)
                @php $modalCounter++; @endphp
                function openRoundModal{{ $modalCounter }}() {
                    document.getElementById('roundModal{{ $modalCounter }}').classList.remove('hidden');
                }
                function closeRoundModal{{ $modalCounter }}() {
                    document.getElementById('roundModal{{ $modalCounter }}').classList.add('hidden');
                }
                // Close on backdrop click
                document.getElementById('roundModal{{ $modalCounter }}').addEventListener('click', function(e) {
                    if (e.target === this) {
                        closeRoundModal{{ $modalCounter }}();
                    }
                });
            @endforeach
        @endif

        // Filter functionality
        document.addEventListener('DOMContentLoaded', function() {
            let selectedStops = '';
            let selectedAirlines = [];
            let selectedDepTimes = [];
            let selectedArrTimes = [];
            let selectedBaggage = [];
            let maxPrice = {{ $maxPrice }};
            let maxDuration = {{ $maxDuration }};

            const timeBuckets = {
                early_morning: [0, 6],
                morning: [6, 12],
                afternoon: [12, 18],
                evening: [18, 24],
            };

            const flightCards = document.querySelectorAll('.flight-card');
            const priceSlider = document.getElementById('priceSlider');
            const priceValue = document.getElementById('priceValue');
            const durationSlider = document.getElementById('durationSlider');
            const durationValue = document.getElementById('durationValue');

            // Price filter
            priceSlider.addEventListener('input', function() {
                maxPrice = this.value;
                priceValue.textContent = parseFloat(maxPrice).toFixed(2);
                filterFlights();
            });

            // Transit hours filter
            durationSlider.addEventListener('input', function() {
                maxDuration = this.value;
                durationValue.textContent = Math.ceil(maxDuration / 60) + 'h';
                filterFlights();
            });

            // Stops filter
            document.querySelectorAll('input[name="stops"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    selectedStops = this.value;
                    filterFlights();
                });
            });

            // Airlines filter
            document.querySelectorAll('.airline-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const airlineCode = this.closest('.airline-filter').dataset.airline;
                    if (this.checked) {
                        selectedAirlines.push(airlineCode);
                    } else {
                        selectedAirlines = selectedAirlines.filter(a => a !== airlineCode);
                    }
                    filterFlights();
                });
            });

            // Flight time (departure) filter
            document.querySelectorAll('.dep-time-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    if (this.checked) {
                        selectedDepTimes.push(this.value);
                    } else {
                        selectedDepTimes = selectedDepTimes.filter(v => v !== this.value);
                    }
                    filterFlights();
                });
            });

            // Flight time (arrival) filter
            document.querySelectorAll('.arr-time-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    if (this.checked) {
                        selectedArrTimes.push(this.value);
                    } else {
                        selectedArrTimes = selectedArrTimes.filter(v => v !== this.value);
                    }
                    filterFlights();
                });
            });

            // Luggage filter
            document.querySelectorAll('.baggage-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    if (this.checked) {
                        selectedBaggage.push(this.value);
                    } else {
                        selectedBaggage = selectedBaggage.filter(v => v !== this.value);
                    }
                    filterFlights();
                });
            });

            // Flight Time: collapse toggle
            const flightTimeToggle = document.getElementById('flightTimeToggle');
            const flightTimeBody = document.getElementById('flightTimeBody');
            const flightTimeChevron = document.getElementById('flightTimeChevron');
            if (flightTimeToggle) {
                flightTimeToggle.addEventListener('click', function() {
                    flightTimeBody.classList.toggle('hidden');
                    flightTimeChevron.style.transform = flightTimeBody.classList.contains('hidden') ? 'rotate(-90deg)' : 'rotate(0deg)';
                });
            }

            // Flight Time: Departure / Arrival tab switch
            document.querySelectorAll('.ft-tab-btn').forEach(tabBtn => {
                tabBtn.addEventListener('click', function() {
                    const tab = this.dataset.tab;

                    document.querySelectorAll('.ft-tab-btn').forEach(btn => {
                        const active = btn === this;
                        btn.style.background = active ? '#ECFDF5' : '#fff';
                        btn.style.color = active ? '#059669' : '#111827';
                        btn.style.border = active ? 'none' : '1px solid #E5E7EB';
                    });

                    document.getElementById('ft-panel-departure').classList.toggle('hidden', tab !== 'departure');
                    document.getElementById('ft-panel-arrival').classList.toggle('hidden', tab !== 'arrival');
                });
            });

            function hourMatchesAnyBucket(hour, bucketKeys) {
                if (bucketKeys.length === 0) return true;
                if (hour === '' || hour === null || hour === undefined) return false;
                hour = parseInt(hour, 10);
                return bucketKeys.some(key => {
                    const range = timeBuckets[key];
                    return range && hour >= range[0] && hour < range[1];
                });
            }

            function filterFlights() {
                let visibleCount = 0;

                flightCards.forEach(card => {
                    const cardStops = card.dataset.stops;
                    const cardAirline = card.dataset.airline;
                    const cardPrice = parseFloat(card.dataset.price);
                    const cardDuration = parseFloat(card.dataset.duration);
                    const cardBaggage = card.dataset.baggage;

                    const stopsMatch = selectedStops === '' || selectedStops === cardStops;
                    const airlineMatch = selectedAirlines.length === 0 || selectedAirlines.includes(cardAirline);
                    const priceMatch = cardPrice <= maxPrice;
                    const durationMatch = isNaN(cardDuration) || cardDuration <= maxDuration;
                    const baggageMatch = selectedBaggage.length === 0 || selectedBaggage.includes(cardBaggage);
                    const depTimeMatch = hourMatchesAnyBucket(card.dataset.depHour, selectedDepTimes);
                    const arrTimeMatch = hourMatchesAnyBucket(card.dataset.arrHour, selectedArrTimes);

                    if (stopsMatch && airlineMatch && priceMatch && durationMatch && baggageMatch && depTimeMatch && arrTimeMatch) {
                        card.style.display = 'block';
                        visibleCount++;
                    } else {
                        card.style.display = 'none';
                    }
                });

                document.getElementById('flightCount').textContent = visibleCount;
            }
        });
    </script>
    @endif

    <script>
        const searchCollapseBtn = document.getElementById('searchCollapseBtn');
        const searchCollapseContent = document.getElementById('searchCollapseContent');
        const collapseIcon = document.getElementById('collapseIcon');

        searchCollapseBtn.addEventListener('click', function() {
            searchCollapseBtn.classList.toggle('active');
            searchCollapseContent.classList.toggle('active');
            collapseIcon.style.transform = searchCollapseContent.classList.contains('active')
                ? 'rotate(180deg)'
                : 'rotate(0deg)';
        });
    </script>


@endsection
