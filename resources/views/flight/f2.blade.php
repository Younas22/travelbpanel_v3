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
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-bold text-gray-900">{{t('flightlist.filters')}}</h3>
                            <button type="button" id="resetFiltersBtn" class="text-xs font-semibold text-[#0077BE] hover:underline">
                                {{t('flightlist.reset_filters')}}
                            </button>
                        </div>

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
                            .ft-row { padding: 12px 14px; border-radius: 12px; background: #fff; border: 1px solid #F1F5F9; transition: background .15s, box-shadow .15s, border-color .15s; }
                            .ft-row:has(input:checked) { background: var(--tint); border-color: transparent; box-shadow: inset 0 0 0 2px #059669; }
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
                                    <label class="ft-row flex items-center justify-between cursor-pointer" style="--tint: {{ $bucket['tint'] }};">
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
                                    <label class="ft-row flex items-center justify-between cursor-pointer" style="--tint: {{ $bucket['tint'] }};">
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
                        <style>
                            .dual-range { position: relative; height: 24px; }
                            .dual-range input[type=range] {
                                position: absolute; top: 0; left: 0; width: 100%; height: 24px;
                                margin: 0; background: transparent; pointer-events: none;
                                -webkit-appearance: none; appearance: none;
                            }
                            .dual-range input[type=range]::-webkit-slider-runnable-track { -webkit-appearance: none; background: transparent; }
                            .dual-range input[type=range]::-moz-range-track { background: transparent; border: none; }
                            .dual-range input[type=range]::-webkit-slider-thumb {
                                pointer-events: auto; -webkit-appearance: none; appearance: none;
                                width: 18px; height: 18px; border-radius: 50%;
                                background: #fff; border: 2px solid #0077BE; cursor: pointer;
                                margin-top: 3px; box-shadow: 0 1px 3px rgba(0,0,0,.25); position: relative; z-index: 3;
                            }
                            .dual-range input[type=range]::-moz-range-thumb {
                                pointer-events: auto; width: 18px; height: 18px; border-radius: 50%;
                                background: #fff; border: 2px solid #0077BE; cursor: pointer;
                            }
                            .dual-range .track-bg { position: absolute; top: 10px; left: 0; right: 0; height: 4px; background: #E5E7EB; border-radius: 2px; }
                            .dual-range .track-fill { position: absolute; top: 10px; height: 4px; background: #0077BE; border-radius: 2px; }
                        </style>

                        <div class="mb-6 pb-6 border-b border-gray-200">
                            <p class="text-xs font-bold text-gray-600 mb-3">{{t('flightlist.price')}}</p>
                            <div class="dual-range mb-2">
                                <div class="track-bg"></div>
                                <div class="track-fill" id="priceTrackFill"></div>
                                <input type="range" min="{{ $minPrice }}" max="{{ $maxPrice }}" step="0.01" value="{{ $minPrice }}" id="priceMinSlider">
                                <input type="range" min="{{ $minPrice }}" max="{{ $maxPrice }}" step="0.01" value="{{ $maxPrice }}" id="priceMaxSlider">
                            </div>
                            <div class="flex justify-between text-xs text-gray-600">
                                <span class="inline-flex items-center gap-1"><span class="{{ getFlagClass($activeCurrency->currency_name) }}"></span>{{ $activeCurrency->currency_name }} <span id="priceMinValue">{{ number_format($minPrice, 2) }}</span></span>
                                <span class="inline-flex items-center gap-1"><span class="{{ getFlagClass($activeCurrency->currency_name) }}"></span>{{ $activeCurrency->currency_name }} <span id="priceMaxValue">{{ number_format($maxPrice, 2) }}</span></span>
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
                            <div class="dual-range mb-2">
                                <div class="track-bg"></div>
                                <div class="track-fill" id="durationTrackFill"></div>
                                <input type="range" min="{{ $minDuration }}" max="{{ $maxDuration }}" value="{{ $minDuration }}" id="durationMinSlider">
                                <input type="range" min="{{ $minDuration }}" max="{{ $maxDuration }}" value="{{ $maxDuration }}" id="durationMaxSlider">
                            </div>
                            <div class="flex justify-between text-xs text-gray-600">
                                <span id="durationMinValue">{{ floor($minDuration / 60) }}h</span>
                                <span id="durationMaxValue">{{ ceil($maxDuration / 60) }}h</span>
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

                            <div class="flex flex-col sm:flex-row">
                                <!-- Airline Logo -->
                                <div class="sm:w-16 w-full flex items-center justify-center sm:border-r border-b sm:border-b-0 border-gray-200 p-2">
                                    <img
                                        src="https://assets.duffel.com/img/airlines/for-light-background/full-color-logo/{{ $segment['segments'][0][0]['carrier']['operating'] }}.svg"
                                        alt="Airline Logo"
                                        class="w-9 h-9 object-contain"
                                        onerror="this.onerror=null;this.src='{{ url('public/assets/images/list-plane.png') }}';"
                                    />
                                </div>

                                <!-- Flight Details -->
                                <div class="flex-1 p-3">
                                    <div class="flex justify-between items-start mb-1.5">
                                        <div>
                                            <p class="text-[10px] leading-tight text-gray-600 font-semibold">
                                                {{ $segment['segments'][0][0]['departure']['airport'] }} to {{ end($segment['segments'][0])['arrival']['airport'] }} •
                                                {{ count($segment['segments'][0]) - 1 == 0 ? 'Direct' : (count($segment['segments'][0]) - 1 . ' Stop') }}
                                            </p>
                                            <p class="text-[10px] leading-tight text-gray-500">{{ $segment['segments'][0][0]['departure']['date_convert'] }}</p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-[9px] leading-tight text-gray-600">{{t('flightlist.total_fare')}}</p>
                                            <p class="text-base leading-tight font-bold text-[#0077BE] inline-flex items-center gap-1"><span class="{{ getFlagClass($activeCurrency->currency_name) }}" style="font-size:10px;"></span>{{ $activeCurrency->currency_name }} {{ number_format($toActive($segment['segments'][0][0]['price'], $segment['segments'][0][0]['currency'] ?? null), 2) }}</p>
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between mb-1.5 pb-1.5 border-b border-gray-200">
                                        <div class="flex-1">
                                            <p class="text-lg leading-none font-bold text-gray-900">{{ $segment['segments'][0][0]['departure']['time'] }}</p>
                                            <p class="text-[10px] font-semibold text-gray-700 mt-0.5">{{ $segment['segments'][0][0]['departure']['airport'] }}</p>
                                        </div>
                                        <div class="flex-1 text-center">
                                            <div class="flex items-center justify-center mb-0.5">
                                                <div class="flex-1 h-0.5 bg-gray-300"></div>
                                                <i class="fas fa-plane text-gray-400 mx-2 text-[10px]"></i>
                                                <div class="flex-1 h-0.5 bg-gray-300"></div>
                                            </div>
                                            <p class="text-[9px] leading-tight text-gray-600">{{ $segment['segments'][0][0]['total_duration'] }}</p>
                                            <p class="text-[9px] leading-tight font-semibold text-[#0077BE]">
                                                @if(count($segment['segments'][0]) - 1 == 0)
                                                    {{t('flightlist.direct')}}
                                                @else
                                                    {{ count($segment['segments'][0]) - 1 }} {{t('flightlist.stops')}}
                                                @endif
                                            </p>
                                        </div>
                                        <div class="flex-1 text-right">
                                            <p class="text-lg leading-none font-bold text-gray-900">{{ end($segment['segments'][0])['arrival']['time'] }}</p>
                                            <p class="text-[10px] font-semibold text-gray-700 mt-0.5">{{ end($segment['segments'][0])['arrival']['airport'] }}</p>
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between flex-wrap gap-1.5">
                                        <div class="flex items-center gap-3">
                                            <div class="flex items-center gap-1">
                                                <i class="fas fa-suitcase text-gray-600 text-[10px]"></i>
                                                <span class="text-[10px] text-gray-700">{{ $segment['segments'][0][0]['cabin_baggage'] ?? '7 kg' }}</span>
                                            </div>
                                            <div class="flex items-center gap-1">
                                                <i class="fas fa-suitcase-rolling text-gray-600 text-[10px]"></i>
                                                <span class="text-[10px] text-gray-700">{{ $baggageLabel($segment['segments'][0][0]['baggage'] ?? null) }}</span>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-1.5">
                                            <button onclick="openModal{{ $modalCounter }}()" class="p-1 hover:bg-gray-100 rounded-lg transition" title="Details">
                                                <i class="fas fa-circle-info text-gray-600 text-xs"></i>
                                            </button>
                                            <form action="{{ route('flights.booking') }}" method="post" class="inline">
                                                @csrf
                                                <input name="routes" type="hidden" value="{{ encrypt(json_encode($segment))}}">
                                                <input name="booking_data" type="hidden" value="{{ encrypt(json_encode($segment['segments'][0][0]['booking_data'])) }}">
                                                <button type="submit" class="px-3 py-1.5 bg-[#0077BE] text-white rounded-full font-semibold text-[11px] hover:bg-blue-700">
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

                            <div class="flex flex-col sm:flex-row">
                                <!-- Airline Logo -->
                                <div class="sm:w-16 w-full flex items-center justify-center sm:border-r border-b sm:border-b-0 border-gray-200 p-2">
                                    <img
                                        src="https://assets.duffel.com/img/airlines/for-light-background/full-color-logo/{{ $segment['segments'][0][0]['carrier']['operating'] }}.svg"
                                        alt="Airline Logo"
                                        class="w-9 h-9 object-contain"
                                        onerror="this.onerror=null;this.src='{{ url('public/assets/images/list-plane.png') }}';"
                                    />
                                </div>

                                <!-- Flight Details -->
                                <div class="flex-1 p-3">
                                    <div class="flex justify-between items-start mb-1.5">
                                        <div>
                                            <p class="text-[10px] leading-tight text-gray-600 font-semibold">{{ $segment['segments'][0][0]['departure']['airport'] }} ↔ {{ end($segment['segments'][0])['arrival']['airport'] }} • Round Trip</p>
                                            <p class="text-[10px] leading-tight text-gray-500">
                                                {{ $segment['segments'][0][0]['departure']['date_convert'] }} - {{ $segment['segments'][1][0]['departure']['date_convert'] }}
                                            </p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-[9px] leading-tight text-gray-600">{{t('flightlist.total_fare')}}</p>
                                            <p class="text-base leading-tight font-bold text-[#0077BE] inline-flex items-center gap-1"><span class="{{ getFlagClass($activeCurrency->currency_name) }}" style="font-size:10px;"></span>{{ $activeCurrency->currency_name }} {{ number_format($toActive($segment['segments'][0][0]['price'], $segment['segments'][0][0]['currency'] ?? null), 2) }}</p>
                                        </div>
                                    </div>

                                    <!-- Outbound -->
                                    <div class="flex items-center justify-between mb-1 pb-1 border-b border-gray-200">
                                        <div class="flex-1">
                                            <p class="text-sm leading-none font-bold text-gray-900">{{ $segment['segments'][0][0]['departure']['time'] }}</p>
                                            <p class="text-[9px] font-semibold text-gray-700 mt-0.5">{{ $segment['segments'][0][0]['departure']['airport'] }}</p>
                                        </div>
                                        <div class="flex-1 text-center">
                                            <div class="flex items-center justify-center">
                                                <div class="flex-1 h-0.5 bg-gray-300"></div>
                                                <i class="fas fa-plane text-gray-400 mx-1.5 text-[9px]"></i>
                                                <div class="flex-1 h-0.5 bg-gray-300"></div>
                                            </div>
                                            <p class="text-[8px] leading-tight text-gray-600">{{ $segment['segments'][0][0]['total_duration'] }}</p>
                                            <p class="text-[8px] leading-tight font-semibold text-[#0077BE]">
                                                @if(count($segment['segments'][0]) - 1 == 0)
                                                    {{t('flightlist.direct')}}
                                                @else
                                                    {{ count($segment['segments'][0]) - 1 }} {{t('flightlist.stops')}}
                                                @endif
                                            </p>
                                        </div>
                                        <div class="flex-1 text-right">
                                            <p class="text-sm leading-none font-bold text-gray-900">{{ end($segment['segments'][0])['arrival']['time'] }}</p>
                                            <p class="text-[9px] font-semibold text-gray-700 mt-0.5">{{ end($segment['segments'][0])['arrival']['airport'] }}</p>
                                        </div>
                                    </div>

                                    <!-- Return -->
                                    <div class="flex items-center justify-between mb-1.5 pb-1.5">
                                        <div class="flex-1">
                                            <p class="text-sm leading-none font-bold text-gray-900">{{ $segment['segments'][1][0]['departure']['time'] }}</p>
                                            <p class="text-[9px] font-semibold text-gray-700 mt-0.5">{{ $segment['segments'][1][0]['departure']['airport'] }}</p>
                                        </div>
                                        <div class="flex-1 text-center">
                                            <div class="flex items-center justify-center">
                                                <div class="flex-1 h-0.5 bg-gray-300"></div>
                                                <i class="fas fa-plane text-gray-400 mx-1.5 text-[9px]"></i>
                                                <div class="flex-1 h-0.5 bg-gray-300"></div>
                                            </div>
                                            <p class="text-[8px] leading-tight text-gray-600">{{ $segment['segments'][1][0]['total_duration'] }}</p>
                                            <p class="text-[8px] leading-tight font-semibold text-[#0077BE]">
                                                @if(count($segment['segments'][1]) - 1 == 0)
                                                    {{t('flightlist.direct')}}
                                                @else
                                                    {{ count($segment['segments'][1]) - 1 }} {{t('flightlist.stops')}}
                                                @endif
                                            </p>
                                        </div>
                                        <div class="flex-1 text-right">
                                            <p class="text-sm leading-none font-bold text-gray-900">{{ end($segment['segments'][1])['arrival']['time'] }}</p>
                                            <p class="text-[9px] font-semibold text-gray-700 mt-0.5">{{ end($segment['segments'][1])['arrival']['airport'] }}</p>
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between flex-wrap gap-1.5 pt-1.5 border-t border-gray-200">
                                        <div class="flex items-center gap-3">
                                            <div class="flex items-center gap-1">
                                                <i class="fas fa-suitcase text-gray-600 text-[10px]"></i>
                                                <span class="text-[10px] text-gray-700">{{ $segment['segments'][0][0]['cabin_baggage'] ?? '7 kg' }}</span>
                                            </div>
                                            <div class="flex items-center gap-1">
                                                <i class="fas fa-suitcase-rolling text-gray-600 text-[10px]"></i>
                                                <span class="text-[10px] text-gray-700">{{ $baggageLabel($segment['segments'][0][0]['baggage'] ?? null) }}</span>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-1.5">
                                            <button onclick="openRoundModal{{ $modalCounter }}()" class="p-1 hover:bg-gray-100 rounded-lg transition" title="Details">
                                                <i class="fas fa-circle-info text-gray-600 text-xs"></i>
                                            </button>
                                            <form action="{{ route('flights.booking') }}" method="post" class="inline">
                                                @csrf
                                                <input name="routes" type="hidden" value="{{ encrypt(json_encode($segment))}}">
                                                <input name="booking_data" type="hidden" value="{{ encrypt(json_encode($segment['segments'][0][0]['booking_data'])) }}">
                                                <button type="submit" class="px-3 py-1.5 bg-[#0077BE] text-white rounded-full font-semibold text-[11px] hover:bg-blue-700">
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
            let minPrice = {{ $minPrice }};
            let maxPrice = {{ $maxPrice }};
            let minDuration = {{ $minDuration }};
            let maxDuration = {{ $maxDuration }};

            const timeBuckets = {
                early_morning: [0, 6],
                morning: [6, 12],
                afternoon: [12, 18],
                evening: [18, 24],
            };

            const flightCards = document.querySelectorAll('.flight-card');

            // Wires up a pair of overlapping range inputs as one dual-handle
            // slider: keeps the handles from crossing, positions the filled
            // track between them, and reports the live [min, max] back via
            // onChange on every drag.
            function setupDualRange(minInput, maxInput, trackFill, onChange, formatValue) {
                const rangeMin = parseFloat(minInput.min);
                const rangeMax = parseFloat(minInput.max);
                const span = (rangeMax - rangeMin) || 1;

                function update() {
                    let lo = parseFloat(minInput.value);
                    let hi = parseFloat(maxInput.value);

                    if (lo > hi) {
                        [lo, hi] = [hi, lo];
                        minInput.value = lo;
                        maxInput.value = hi;
                    }

                    const loPct = ((lo - rangeMin) / span) * 100;
                    const hiPct = ((hi - rangeMin) / span) * 100;
                    trackFill.style.left = loPct + '%';
                    trackFill.style.width = Math.max(hiPct - loPct, 0) + '%';

                    onChange(lo, hi, formatValue);
                }

                minInput.addEventListener('input', update);
                maxInput.addEventListener('input', update);
                update();
            }

            setupDualRange(
                document.getElementById('priceMinSlider'),
                document.getElementById('priceMaxSlider'),
                document.getElementById('priceTrackFill'),
                function(lo, hi) {
                    minPrice = lo;
                    maxPrice = hi;
                    document.getElementById('priceMinValue').textContent = lo.toFixed(2);
                    document.getElementById('priceMaxValue').textContent = hi.toFixed(2);
                    filterFlights();
                }
            );

            setupDualRange(
                document.getElementById('durationMinSlider'),
                document.getElementById('durationMaxSlider'),
                document.getElementById('durationTrackFill'),
                function(lo, hi) {
                    minDuration = lo;
                    maxDuration = hi;
                    document.getElementById('durationMinValue').textContent = Math.floor(lo / 60) + 'h';
                    document.getElementById('durationMaxValue').textContent = Math.ceil(hi / 60) + 'h';
                    filterFlights();
                }
            );

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

            // Reset Filters
            const resetFiltersBtn = document.getElementById('resetFiltersBtn');
            if (resetFiltersBtn) {
                resetFiltersBtn.addEventListener('click', function() {
                    selectedStops = '';
                    selectedAirlines = [];
                    selectedDepTimes = [];
                    selectedArrTimes = [];
                    selectedBaggage = [];

                    document.querySelectorAll('input[name="stops"]').forEach(radio => {
                        radio.checked = radio.value === '';
                    });
                    document.querySelectorAll('.airline-checkbox, .dep-time-checkbox, .arr-time-checkbox, .baggage-checkbox').forEach(checkbox => {
                        checkbox.checked = false;
                    });

                    ['priceMinSlider', 'durationMinSlider'].forEach(id => {
                        const el = document.getElementById(id);
                        el.value = el.min;
                        el.dispatchEvent(new Event('input'));
                    });
                    ['priceMaxSlider', 'durationMaxSlider'].forEach(id => {
                        const el = document.getElementById(id);
                        el.value = el.max;
                        el.dispatchEvent(new Event('input'));
                    });

                    filterFlights();
                });
            }

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
                    const priceMatch = isNaN(cardPrice) || (cardPrice >= minPrice && cardPrice <= maxPrice);
                    const durationMatch = isNaN(cardDuration) || (cardDuration >= minDuration && cardDuration <= maxDuration);
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
