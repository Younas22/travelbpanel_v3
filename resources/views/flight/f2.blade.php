@extends('common.layout')
@section('content')
<div class="tl-page fl-page">
    <!-- Search bar (the shared flight search form) -->
    <div id="siteRedesign">
        <div class="form-container tl-search">
            <div class="tab-content active" id="form-flight">
                @include('forms.flight-form')
            </div>
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
            $logoUrl = fn($code) => 'https://assets.duffel.com/img/airlines/for-light-background/full-color-logo/' . $code . '.svg';
            $isRound = ($flight_search['trip_type'] ?? '') === 'round';
            $stopsLabel = fn($n) => $n == 0 ? t('flightlist.direct') : ($n . ' ' . ($n == 1 ? t('flightlist.stop') : t('flightlist.stops')));

            // Filter data
            $check_stops = [];
            $prices = [];
            $durations = [];
            $displayed_baggage = [];
            $airline_map = [];
            foreach ($flights_data as $item) {
                foreach ($item['segments'] as $segments) {
                    $check_stops[count($segments) - 1] = true;
                    $code = $segments[0]['carrier']['operating'];
                    if (!isset($airline_map[$code]) && !empty($segments[0]['airline_name'])) {
                        $airline_map[$code] = $segments[0]['airline_name'];
                    }
                }
                if (isset($item['segments'][0][0]['price'])) {
                    $prices[] = $toActive($item['segments'][0][0]['price'], $item['segments'][0][0]['currency'] ?? null);
                }
                if (isset($item['segments'][0][0]['total_duration'])) {
                    $durations[] = $toMinutes($item['segments'][0][0]['total_duration']);
                }
                $bag = $baggageLabel($item['segments'][0][0]['baggage'] ?? null);
                if (!in_array($bag, $displayed_baggage)) $displayed_baggage[] = $bag;
            }
            ksort($check_stops);
            $minPrice = !empty($prices) ? min($prices) : 0;
            $maxPrice = !empty($prices) ? max($prices) : 0;
            $minDuration = !empty($durations) ? min($durations) : 0;
            $maxDuration = !empty($durations) ? max($durations) : 0;

            $timeBuckets = [
                'early_morning' => ['label' => t('flightlist.early_morning'), 'range' => '00-06', 'icon' => 'fa-cloud-moon'],
                'morning'       => ['label' => t('flightlist.morning'), 'range' => '06-12', 'icon' => 'fa-sun'],
                'afternoon'     => ['label' => t('flightlist.afternoon'), 'range' => '12-18', 'icon' => 'fa-cloud-sun'],
                'evening'       => ['label' => t('flightlist.evening'), 'range' => '18-24', 'icon' => 'fa-moon'],
            ];
        @endphp

        <!-- Results header -->
        <div class="tl-head">
            <div>
                <h1 class="tl-title">
                    {{ strtoupper($flight_search['origin'] ?? '') }}
                    <i class="fas {{ $isRound ? 'fa-arrow-right-arrow-left' : 'fa-arrow-right' }} tl-title-arrow"></i>
                    {{ strtoupper($flight_search['destination'] ?? '') }}:
                    <span id="flightCount">{{ $flights_data->total() }}</span> {{ t('flightlist.flights_found') }}
                </h1>
                <p class="tl-subtitle">
                    {{ isset($flight_search['departure_date']) ? \Carbon\Carbon::parse($flight_search['departure_date'])->format('D, d M Y') : '' }}
                    @if($isRound && !empty($flight_search['return_date'])) – {{ \Carbon\Carbon::parse($flight_search['return_date'])->format('D, d M Y') }} @endif
                    · {{ $flight_search['passenger_count'] ?? 1 }} {{ t('flightform.passengers') }}
                </p>
            </div>
            <div class="tl-head-actions">
                <button type="button" class="tl-filter-toggle" id="mobileFilterToggle" aria-expanded="false" aria-controls="filterSidebar">
                    <i class="fas fa-sliders"></i> {{ t('flightlist.filters') }}
                </button>
                <div class="tl-sort-wrap">
                    <label class="tl-sort-label" for="flSort"><i class="fas fa-arrow-down-wide-short"></i></label>
                    <select class="tl-sort" id="flSort">
                        <option value="default">{{ t('flightlist.price') }} ↑</option>
                        <option value="price_desc">{{ t('flightlist.price') }} ↓</option>
                        <option value="duration">{{ t('flightbooking.duration') }}</option>
                        <option value="departure">{{ t('flightlist.departure') }}</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="tl-main">
            <!-- Filters -->
            <aside class="tl-sidebar" id="filterSidebar">
                <div class="tl-sidebar-title fl-sidebar-head">
                    <span><i class="fas fa-sliders"></i> {{ t('flightlist.filters') }}</span>
                    <button type="button" id="resetFiltersBtn" class="fl-reset">{{ t('flightlist.reset_filters') }}</button>
                </div>

                <!-- Stops -->
                <div class="tl-group">
                    <div class="tl-group-title">{{ t('flightlist.stops') }}</div>
                    <label class="tl-check"><span><input type="radio" name="stops" value="" checked> {{ t('flightlist.all_flights') }}</span></label>
                    @foreach ($check_stops as $stop_count => $value)
                        <label class="tl-check"><span><input type="radio" name="stops" value="{{ $stop_count }}">
                            @if ($stop_count === 0) {{ t('flightlist.nonstop') }} @elseif ($stop_count === 1) 1 {{ t('flightlist.stop') }} @else {{ $stop_count }} {{ t('flightlist.stops') }} @endif
                        </span></label>
                    @endforeach
                </div>

                <!-- Flight time -->
                <div class="tl-group">
                    <button type="button" id="flightTimeToggle" class="fl-collapse">
                        <span class="tl-group-title">{{ t('flightlist.flight_time') }}</span>
                        <i class="fas fa-chevron-down" id="flightTimeChevron"></i>
                    </button>
                    <div id="flightTimeBody">
                        <div class="fl-tabs" role="tablist">
                            <button type="button" class="ft-tab-btn is-active" data-tab="departure">{{ t('flightlist.departure') }}</button>
                            <button type="button" class="ft-tab-btn" data-tab="arrival">{{ t('flightlist.arrival') }}</button>
                        </div>
                        @foreach(['departure' => 'dep-time-checkbox', 'arrival' => 'arr-time-checkbox'] as $panel => $cls)
                            <div id="ft-panel-{{ $panel }}" class="fl-time-grid {{ $panel === 'arrival' ? 'hidden' : '' }}">
                                @foreach($timeBuckets as $key => $bucket)
                                    <label class="fl-time">
                                        <input type="checkbox" class="{{ $cls }}" value="{{ $key }}">
                                        <i class="fas {{ $bucket['icon'] }}"></i>
                                        <span>{{ $bucket['label'] }}</span>
                                        <small>{{ $bucket['range'] }}</small>
                                    </label>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Price -->
                <div class="tl-group">
                    <div class="tl-group-title">{{ t('flightlist.price') }} ({{ $activeCurrency->currency_name }})</div>
                    <div class="tl-range">
                        <div class="tl-range-track"></div>
                        <div class="tl-range-fill" id="priceTrackFill"></div>
                        <input type="range" class="tl-range-input" min="{{ $minPrice }}" max="{{ $maxPrice }}" step="0.01" value="{{ $minPrice }}" id="priceMinSlider" aria-label="Minimum price">
                        <input type="range" class="tl-range-input" min="{{ $minPrice }}" max="{{ $maxPrice }}" step="0.01" value="{{ $maxPrice }}" id="priceMaxSlider" aria-label="Maximum price">
                    </div>
                    <div class="fl-range-values">
                        <span>{{ $activeCurrency->currency_name }} <span id="priceMinValue">{{ number_format($minPrice, 2) }}</span></span>
                        <span>{{ $activeCurrency->currency_name }} <span id="priceMaxValue">{{ number_format($maxPrice, 2) }}</span></span>
                    </div>
                </div>

                <!-- Transit hours -->
                <div class="tl-group">
                    <div class="tl-group-title">{{ t('flightlist.transit_hours') }}</div>
                    <div class="tl-range">
                        <div class="tl-range-track"></div>
                        <div class="tl-range-fill" id="durationTrackFill"></div>
                        <input type="range" class="tl-range-input" min="{{ $minDuration }}" max="{{ $maxDuration }}" value="{{ $minDuration }}" id="durationMinSlider" aria-label="Minimum duration">
                        <input type="range" class="tl-range-input" min="{{ $minDuration }}" max="{{ $maxDuration }}" value="{{ $maxDuration }}" id="durationMaxSlider" aria-label="Maximum duration">
                    </div>
                    <div class="fl-range-values">
                        <span id="durationMinValue">{{ floor($minDuration / 60) }}h</span>
                        <span id="durationMaxValue">{{ ceil($maxDuration / 60) }}h</span>
                    </div>
                </div>

                <!-- Luggage -->
                <div class="tl-group">
                    <div class="tl-group-title">{{ t('flightlist.luggage') }}</div>
                    @foreach($displayed_baggage as $baggage)
                        <label class="tl-check"><span><input type="checkbox" class="baggage-checkbox" value="{{ $baggage }}"> {{ $baggage }}</span></label>
                    @endforeach
                </div>

                <!-- Airlines -->
                <div class="tl-group">
                    <div class="tl-group-title">{{ t('flightlist.preferred_airlines') }}</div>
                    @foreach ($airline_map as $code => $name)
                        <label class="tl-check airline-filter" data-airline="{{ $code }}">
                            <span>
                                <input type="checkbox" class="airline-checkbox">
                                <img src="{{ $logoUrl($code) }}" alt="" class="fl-airline-mini" onerror="this.style.visibility='hidden'">
                                {{ $name }}
                            </span>
                        </label>
                    @endforeach
                </div>
            </aside>

            <!-- Results -->
            <div class="tl-results-col">
                <div class="fl-results" id="flResults">
                    @foreach($flights_data as $key => $segment)
                        @php
                            $first = $segment['segments'][0][0];
                            $legs = $isRound ? array_slice($segment['segments'], 0, 2) : [$segment['segments'][0]];
                            $price = $toActive($first['price'], $first['currency'] ?? null);
                            $modalId = 'flModal' . $loop->iteration;
                        @endphp
                        <article class="fl-card flight-card"
                             data-index="{{ $loop->index }}"
                             data-stops="{{ count($segment['segments'][0]) - 1 }}"
                             data-airline="{{ $first['carrier']['operating'] }}"
                             data-price="{{ $price }}"
                             data-dep-hour="{{ $first['departure']['hour'] ?? '' }}"
                             data-arr-hour="{{ end($segment['segments'][0])['arrival']['hour'] ?? '' }}"
                             data-baggage="{{ $baggageLabel($first['baggage'] ?? null) }}"
                             data-duration="{{ $toMinutes($first['total_duration'] ?? '') }}"
                             data-dep-time="{{ $first['departure']['time'] ?? '' }}">

                            @php
                                $cabinBag = isset($first['cabin_baggage']) && $first['cabin_baggage'] !== '' ? $baggageLabel($first['cabin_baggage']) : null;
                                $outStops = count($segment['segments'][0]) - 1;
                            @endphp
                            <div class="fl-main">
                                <div class="fl-airline">
                                    <span class="fl-airline-logo">
                                        <img src="{{ $logoUrl($first['carrier']['operating']) }}" alt="" onerror="this.onerror=null;this.src='{{ url('public/assets/images/list-plane.png') }}';">
                                    </span>
                                    <div>
                                        <b>{{ $first['airline_name'] ?? $first['carrier']['operating'] }}</b>
                                        <small>{{ $first['carrier']['operating'] }} {{ $first['flight_number'] ?? '' }}</small>
                                    </div>
                                    <span class="fl-stops-badge {{ $outStops == 0 ? 'is-direct' : '' }}">{{ $stopsLabel($outStops) }}</span>
                                </div>

                                <div class="fl-legs">
                                    @foreach($legs as $legIndex => $leg)
                                        @php $legStops = count($leg) - 1; $legLast = end($leg); @endphp
                                        <div class="fl-leg">
                                            @if($isRound)
                                                <span class="fl-leg-label">{{ $legIndex === 0 ? t('flightlist.outbound') : t('flightform.return') }}@if($legIndex > 0) · {{ $stopsLabel($legStops) }}@endif</span>
                                            @endif
                                            <div class="fl-point">
                                                <span class="fl-point-top"><b>{{ $leg[0]['departure']['airport'] }}</b> <span class="fl-clock">{{ $leg[0]['departure']['time'] }}</span></span>
                                                <span class="fl-place">{{ collect([$leg[0]['departure']['city_name'] ?? null, $leg[0]['departure']['airport_name'] ?? null])->filter()->unique()->implode(' · ') }}</span>
                                            </div>
                                            <div class="fl-line" aria-hidden="true">
                                                @if(!empty($leg[0]['total_duration']))
                                                    <small class="fl-duration"><i class="far fa-clock"></i> {{ $leg[0]['total_duration'] }}</small>
                                                @endif
                                                <span class="fl-track"><i class="fas fa-plane"></i></span>
                                            </div>
                                            <div class="fl-point is-end">
                                                <span class="fl-point-top"><span class="fl-clock">{{ $legLast['arrival']['time'] }}</span> <b>{{ $legLast['arrival']['airport'] }}</b></span>
                                                <span class="fl-place">{{ collect([$legLast['arrival']['city_name'] ?? null, $legLast['arrival']['airport_name'] ?? null])->filter()->unique()->implode(' · ') }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="fl-extras">
                                    @foreach($legs as $leg)
                                        @if(!empty($leg[0]['departure']['date_convert']))
                                            <span><i class="far fa-calendar"></i> {{ $leg[0]['departure']['date_convert'] }}</span>
                                        @endif
                                    @endforeach
                                    @if($cabinBag)
                                        <span><i class="fas fa-suitcase"></i> {{ t('flightlist.cabin_bag') }}: {{ $cabinBag }}</span>
                                    @endif
                                    <span><i class="fas fa-suitcase-rolling"></i> {{ t('flightlist.checked_bag') }}: {{ $baggageLabel($first['baggage'] ?? null) }}</span>
                                    <button type="button" class="fl-details-btn" data-fl-open="{{ $modalId }}"><i class="fas fa-circle-info"></i> Details</button>
                                </div>
                            </div>

                            <div class="fl-price">
                                <div>
                                    @if(!empty($first['class']))
                                        <span class="fl-fare-type">{{ ucwords(str_replace('_', ' ', $first['class'])) }}</span>
                                    @endif
                                    <small>{{ t('flightlist.total_fare') }}</small>
                                    <b>{{ $activeCurrency->currency_name }} {{ number_format($price, 2) }}</b>
                                    <small>{{ $flight_search['passenger_count'] ?? 1 }} {{ ($flight_search['passenger_count'] ?? 1) == 1 ? t('flightform.passenger') : t('flightform.passengers') }}</small>
                                </div>
                                <form action="{{ route('flights.booking') }}" method="post">
                                    @csrf
                                    <input name="routes" type="hidden" value="{{ encrypt(json_encode($segment)) }}">
                                    <input name="booking_data" type="hidden" value="{{ encrypt(json_encode($first['booking_data'])) }}">
                                    <button type="submit" class="fl-book-btn">{{ t('flightlist.book') }} <i class="fas fa-arrow-right"></i></button>
                                </form>
                            </div>

                            <!-- Details modal -->
                            <div class="fl-modal" id="{{ $modalId }}" hidden>
                                <div class="fl-modal-panel" role="dialog" aria-modal="true">
                                    <button type="button" class="fl-modal-close" data-fl-close aria-label="Close"><i class="fas fa-xmark"></i></button>
                                    @foreach($legs as $legIndex => $leg)
                                        <div class="fl-modal-head">
                                            <span>{{ $legIndex === 0 ? t('flightlist.outbound') : t('flightform.return') }}</span>
                                            <b>{{ $leg[0]['departure']['airport'] }} <i class="fas fa-arrow-right"></i> {{ end($leg)['arrival']['airport'] }}</b>
                                            <small>{{ $leg[0]['departure']['date_convert'] ?? '' }}</small>
                                        </div>
                                        @foreach($leg as $seg)
                                            <div class="fl-seg">
                                                <div class="fl-seg-airline">
                                                    <img src="{{ $logoUrl($seg['carrier']['operating']) }}" alt="" onerror="this.onerror=null;this.src='{{ url('public/assets/images/list-plane.png') }}';">
                                                    <div><b>{{ $seg['airline_name'] }}</b><small>{{ $seg['flight_number'] }}</small></div>
                                                    <div class="fl-seg-bags">
                                                        @if(isset($seg['cabin_baggage']) && $seg['cabin_baggage'] !== '')<span><i class="fas fa-suitcase"></i> {{ $baggageLabel($seg['cabin_baggage']) }}</span>@endif
                                                        <span><i class="fas fa-suitcase-rolling"></i> {{ $baggageLabel($seg['baggage'] ?? null) }}</span>
                                                    </div>
                                                </div>
                                                <div class="fl-seg-route">
                                                    <div>
                                                        <b>{{ $seg['departure']['time'] }} {{ $seg['departure']['airport'] }}</b>
                                                        <small>{{ $seg['departure']['date_convert'] }}</small>
                                                        <small>{{ $seg['departure']['city_name'] }}@if(!empty($seg['departure']['terminal'])) · Terminal {{ $seg['departure']['terminal'] }}@endif</small>
                                                    </div>
                                                    <div class="fl-seg-dur"><i class="fas fa-clock"></i> {{ $seg['duration'] }}</div>
                                                    <div class="is-end">
                                                        <b>{{ $seg['arrival']['time'] }} {{ $seg['arrival']['airport'] }}</b>
                                                        <small>{{ $seg['arrival']['date_convert'] }}</small>
                                                        <small>{{ $seg['arrival']['city_name'] }}@if(!empty($seg['arrival']['terminal'])) · Terminal {{ $seg['arrival']['terminal'] }}@endif</small>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endforeach
                                </div>
                            </div>
                        </article>
                    @endforeach
                    <div class="tl-empty" id="flNoMatch" hidden>
                        <i class="fas fa-plane-slash"></i>
                        <h3>{{ t('flightlist.no_flights_found') }}</h3>
                    </div>
                </div>

                @if($flights_data->hasPages())
                    <nav class="tl-pagination" aria-label="Pagination">
                        <div class="tl-pages">
                            @if($flights_data->onFirstPage())
                                <span class="tl-page-btn is-disabled" aria-hidden="true"><i class="fas fa-chevron-left"></i></span>
                            @else
                                <a class="tl-page-btn" href="{{ $flights_data->previousPageUrl() }}" aria-label="Previous page"><i class="fas fa-chevron-left"></i></a>
                            @endif
                            @for($n = 1; $n <= $flights_data->lastPage(); $n++)
                                @if($n == $flights_data->currentPage())
                                    <span class="tl-page-btn is-active" aria-current="page">{{ $n }}</span>
                                @else
                                    <a class="tl-page-btn" href="{{ $flights_data->url($n) }}">{{ $n }}</a>
                                @endif
                            @endfor
                            @if($flights_data->hasMorePages())
                                <a class="tl-page-btn" href="{{ $flights_data->nextPageUrl() }}" aria-label="Next page"><i class="fas fa-chevron-right"></i></a>
                            @else
                                <span class="tl-page-btn is-disabled" aria-hidden="true"><i class="fas fa-chevron-right"></i></span>
                            @endif
                        </div>
                        <div class="tl-page-summary">{{ $flights_data->firstItem() }}–{{ $flights_data->lastItem() }} / {{ $flights_data->total() }}</div>
                    </nav>
                @endif
            </div>
        </div>
    @else
        <div class="tl-empty fl-empty">
            <i class="fas fa-plane-slash"></i>
            <h3>{{ t('flightlist.no_flights_found') }}</h3>
            <p>{{ t('flightlist.no_flights_message') }}</p>
            <a href="{{ url('/flights') }}" class="tl-view-btn fl-empty-btn"><i class="fas fa-magnifying-glass"></i> {{ t('flightlist.search_again') }}</a>
        </div>
    @endif
</div>

@if(isset($flights_data) && isset($flight_search))
<script>
    document.addEventListener('DOMContentLoaded', function () {
        let selectedStops = '';
        let selectedAirlines = [];
        let selectedDepTimes = [];
        let selectedArrTimes = [];
        let selectedBaggage = [];
        let minPrice = {{ $minPrice }};
        let maxPrice = {{ $maxPrice }};
        let minDuration = {{ $minDuration }};
        let maxDuration = {{ $maxDuration }};

        const timeBuckets = { early_morning: [0, 6], morning: [6, 12], afternoon: [12, 18], evening: [18, 24] };
        const flightCards = Array.from(document.querySelectorAll('.flight-card'));
        const results = document.getElementById('flResults');
        const noMatch = document.getElementById('flNoMatch');

        // Details modals
        document.querySelectorAll('[data-fl-open]').forEach(btn => btn.addEventListener('click', function () {
            document.getElementById(btn.dataset.flOpen).hidden = false;
            document.body.classList.add('overflow-hidden');
        }));
        document.querySelectorAll('.fl-modal').forEach(modal => modal.addEventListener('click', function (e) {
            if (e.target === modal || e.target.closest('[data-fl-close]')) {
                modal.hidden = true;
                document.body.classList.remove('overflow-hidden');
            }
        }));
        document.addEventListener('keydown', function (e) {
            if (e.key !== 'Escape') return;
            document.querySelectorAll('.fl-modal:not([hidden])').forEach(m => { m.hidden = true; });
            document.body.classList.remove('overflow-hidden');
        });

        // Mobile filters
        const toggle = document.getElementById('mobileFilterToggle');
        const sidebar = document.getElementById('filterSidebar');
        toggle.addEventListener('click', function () {
            toggle.setAttribute('aria-expanded', String(sidebar.classList.toggle('active')));
        });

        // Wires up a pair of overlapping range inputs as one dual-handle slider
        function setupDualRange(minInput, maxInput, trackFill, onChange) {
            const rangeMin = parseFloat(minInput.min);
            const span = (parseFloat(minInput.max) - rangeMin) || 1;
            function update() {
                let lo = parseFloat(minInput.value), hi = parseFloat(maxInput.value);
                if (lo > hi) { [lo, hi] = [hi, lo]; minInput.value = lo; maxInput.value = hi; }
                const loPct = ((lo - rangeMin) / span) * 100, hiPct = ((hi - rangeMin) / span) * 100;
                trackFill.style.left = loPct + '%';
                trackFill.style.width = Math.max(hiPct - loPct, 0) + '%';
                onChange(lo, hi);
            }
            minInput.addEventListener('input', update);
            maxInput.addEventListener('input', update);
            update();
        }

        setupDualRange(document.getElementById('priceMinSlider'), document.getElementById('priceMaxSlider'), document.getElementById('priceTrackFill'), function (lo, hi) {
            minPrice = lo; maxPrice = hi;
            document.getElementById('priceMinValue').textContent = lo.toFixed(2);
            document.getElementById('priceMaxValue').textContent = hi.toFixed(2);
            filterFlights();
        });
        setupDualRange(document.getElementById('durationMinSlider'), document.getElementById('durationMaxSlider'), document.getElementById('durationTrackFill'), function (lo, hi) {
            minDuration = lo; maxDuration = hi;
            document.getElementById('durationMinValue').textContent = Math.floor(lo / 60) + 'h';
            document.getElementById('durationMaxValue').textContent = Math.ceil(hi / 60) + 'h';
            filterFlights();
        });

        document.querySelectorAll('input[name="stops"]').forEach(radio => radio.addEventListener('change', function () {
            selectedStops = this.value; filterFlights();
        }));

        function listToggle(selector, getList, setList, valueOf) {
            document.querySelectorAll(selector).forEach(cb => cb.addEventListener('change', function () {
                const v = valueOf(this);
                setList(this.checked ? getList().concat(v) : getList().filter(x => x !== v));
                filterFlights();
            }));
        }
        listToggle('.airline-checkbox', () => selectedAirlines, l => { selectedAirlines = l; }, el => el.closest('.airline-filter').dataset.airline);
        listToggle('.dep-time-checkbox', () => selectedDepTimes, l => { selectedDepTimes = l; }, el => el.value);
        listToggle('.arr-time-checkbox', () => selectedArrTimes, l => { selectedArrTimes = l; }, el => el.value);
        listToggle('.baggage-checkbox', () => selectedBaggage, l => { selectedBaggage = l; }, el => el.value);

        // Flight time: collapse + departure/arrival tabs
        document.getElementById('flightTimeToggle').addEventListener('click', function () {
            const body = document.getElementById('flightTimeBody');
            body.classList.toggle('hidden');
            document.getElementById('flightTimeChevron').style.transform = body.classList.contains('hidden') ? 'rotate(-90deg)' : '';
        });
        document.querySelectorAll('.ft-tab-btn').forEach(tabBtn => tabBtn.addEventListener('click', function () {
            document.querySelectorAll('.ft-tab-btn').forEach(btn => btn.classList.toggle('is-active', btn === this));
            document.getElementById('ft-panel-departure').classList.toggle('hidden', this.dataset.tab !== 'departure');
            document.getElementById('ft-panel-arrival').classList.toggle('hidden', this.dataset.tab !== 'arrival');
        }));

        // Sort (within the current page of results)
        const sortSelect = document.getElementById('flSort');
        const toMin = t => { const m = /(\d+):(\d+)/.exec(t || ''); return m ? (+m[1]) * 60 + (+m[2]) : 0; };
        sortSelect.addEventListener('change', function () {
            const v = sortSelect.value;
            flightCards.slice().sort(function (a, b) {
                if (v === 'price_desc') return b.dataset.price - a.dataset.price;
                if (v === 'duration') return a.dataset.duration - b.dataset.duration;
                if (v === 'departure') return toMin(a.dataset.depTime) - toMin(b.dataset.depTime);
                return a.dataset.index - b.dataset.index;
            }).forEach(c => results.insertBefore(c, noMatch));
        });

        // Reset filters
        document.getElementById('resetFiltersBtn').addEventListener('click', function () {
            selectedStops = ''; selectedAirlines = []; selectedDepTimes = []; selectedArrTimes = []; selectedBaggage = [];
            document.querySelectorAll('input[name="stops"]').forEach(r => { r.checked = r.value === ''; });
            document.querySelectorAll('.airline-checkbox, .dep-time-checkbox, .arr-time-checkbox, .baggage-checkbox').forEach(c => { c.checked = false; });
            ['priceMinSlider', 'durationMinSlider'].forEach(id => { const el = document.getElementById(id); el.value = el.min; el.dispatchEvent(new Event('input')); });
            ['priceMaxSlider', 'durationMaxSlider'].forEach(id => { const el = document.getElementById(id); el.value = el.max; el.dispatchEvent(new Event('input')); });
            filterFlights();
        });

        function hourMatchesAnyBucket(hour, keys) {
            if (keys.length === 0) return true;
            if (hour === '' || hour === null || hour === undefined) return false;
            hour = parseInt(hour, 10);
            return keys.some(k => timeBuckets[k] && hour >= timeBuckets[k][0] && hour < timeBuckets[k][1]);
        }

        function filterFlights() {
            let visible = 0;
            flightCards.forEach(card => {
                const price = parseFloat(card.dataset.price);
                const duration = parseFloat(card.dataset.duration);
                const ok =
                    (selectedStops === '' || selectedStops === card.dataset.stops) &&
                    (selectedAirlines.length === 0 || selectedAirlines.includes(card.dataset.airline)) &&
                    (isNaN(price) || (price >= minPrice && price <= maxPrice)) &&
                    (isNaN(duration) || (duration >= minDuration && duration <= maxDuration)) &&
                    (selectedBaggage.length === 0 || selectedBaggage.includes(card.dataset.baggage)) &&
                    hourMatchesAnyBucket(card.dataset.depHour, selectedDepTimes) &&
                    hourMatchesAnyBucket(card.dataset.arrHour, selectedArrTimes);
                card.hidden = !ok;
                if (ok) visible++;
            });
            document.getElementById('flightCount').textContent = visible;
            noMatch.hidden = !(flightCards.length && visible === 0);
        }
    });
</script>
@endif

@endsection
