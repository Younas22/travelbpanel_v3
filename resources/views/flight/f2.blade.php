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

                        <!-- Price -->
                        @php
                            $prices = [];
                            foreach ($flights_data as $item) {
                                if (isset($item['segments'][0][0]['price'])) {
                                    $cleanPrice = floatval(str_replace(',', '', $item['segments'][0][0]['price']));
                                    $prices[] = $cleanPrice;
                                }
                            }
                            $minPrice = !empty($prices) ? min($prices) : 0;
                            $maxPrice = !empty($prices) ? max($prices) : 0;
                        @endphp
                        <div class="mb-6 pb-6 border-b border-gray-200">
                            <p class="text-xs font-bold text-gray-600 mb-3">{{t('flightlist.price')}}</p>
                            <input type="range" min="{{ $minPrice }}" max="{{ $maxPrice }}" value="{{ $maxPrice }}" id="priceSlider" class="w-full accent-[#0077BE]">
                            <div class="flex justify-between text-xs text-gray-600 mt-2">
                                <span>{{ $minPrice }}</span>
                                <span id="priceValue">{{ $maxPrice }}</span>
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
                             data-price="{{ floatval(str_replace(',', '', $segment['segments'][0][0]['price'])) }}">

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
                                            <p class="text-2xl font-bold text-[#0077BE]">{{ $segment['segments'][0][0]['currency'] }} {{ $segment['segments'][0][0]['price'] }}</p>
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
                                                <span class="text-sm text-gray-700">{{ $segment['segments'][0][0]['baggage'] ?? 'No Luggage' }}</span>
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
                                                    <p class="text-xs text-gray-600">{{ $seg['baggage'] ?? 'No Luggage' }}</p>
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
                             data-price="{{ floatval(str_replace(',', '', $segment['segments'][0][0]['price'])) }}">

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
                                            <p class="text-2xl font-bold text-[#0077BE]">{{ $segment['segments'][0][0]['currency'] }} {{ $segment['segments'][0][0]['price'] }}</p>
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
                                                <span class="text-sm text-gray-700">{{ $segment['segments'][0][0]['baggage'] ?? 'No Luggage' }}</span>
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
                                                    <p class="text-xs text-gray-600">{{ $seg['baggage'] ?? 'No Luggage' }}</p>
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
                                                    <p class="text-xs text-gray-600">{{ $seg['baggage'] ?? 'No Luggage' }}</p>
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
            let maxPrice = {{ $maxPrice }};

            const flightCards = document.querySelectorAll('.flight-card');
            const priceSlider = document.getElementById('priceSlider');
            const priceValue = document.getElementById('priceValue');

            // Price filter
            priceSlider.addEventListener('input', function() {
                maxPrice = this.value;
                priceValue.textContent = maxPrice;
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

            function filterFlights() {
                let visibleCount = 0;

                flightCards.forEach(card => {
                    const cardStops = card.dataset.stops;
                    const cardAirline = card.dataset.airline;
                    const cardPrice = parseFloat(card.dataset.price);

                    const stopsMatch = selectedStops === '' || selectedStops === cardStops;
                    const airlineMatch = selectedAirlines.length === 0 || selectedAirlines.includes(cardAirline);
                    const priceMatch = cardPrice <= maxPrice;

                    if (stopsMatch && airlineMatch && priceMatch) {
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
