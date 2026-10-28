@extends('common.layout')
@section('content')
    @php
        $active_currency = activeCurrency();
        $sortBy = $sortBy ?? 'popular';
        $tourItems = $tours->getCollection();

        // Converted price per person — the same conversion every tour page uses
        $tlPrice = fn($tour) => convertCurrency($tour->price, $tour->currency ?? 'USD', $active_currency->currency_name);
        $prices = $tourItems->map($tlPrice);
        $minPrice = $prices->count() ? (int) floor($prices->min()) : 0;
        $maxPrice = $prices->count() ? (int) ceil($prices->max()) : 10000;
        if ($maxPrice <= $minPrice) $maxPrice = $minPrice + 100;

        $starCounts = [];
        for ($s = 5; $s >= 1; $s--) {
            $starCounts[$s] = $tourItems->filter(fn($t) => (int) $t->stars === $s)->count();
        }
        $durationBuckets = [
            '1-3'  => [t('tourlist.1_3_days'), fn($d) => $d >= 1 && $d <= 3],
            '4-7'  => [t('tourlist.4_7_days'), fn($d) => $d >= 4 && $d <= 7],
            '8-14' => [t('tourlist.8_14_days'), fn($d) => $d >= 8 && $d <= 14],
            '15+'  => [t('tourlist.15_plus_days'), fn($d) => $d >= 15],
        ];
        $durationCounts = collect($durationBuckets)->map(fn($b) => $tourItems->filter(fn($t) => $t->days && $b[1]((int) $t->days))->count());
        $typeCounts = $tourItems->groupBy(fn($t) => $t->packageType->packege_type ?? $t->packege_type ?? '')->filter(fn($g, $k) => $k !== '')->map->count()->sortDesc();
        $inclusionCounts = $tourItems->flatMap(fn($t) => array_unique($t->highlights ?? []))->countBy()->sortDesc();

        $searchCity = $searchParams['location_name'] ?? ucfirst($searchParams['location'] ?? '');
        $mapQuery = urlencode('tours in ' . trim($searchCity . ' ' . ($searchParams['location_country'] ?? '')));
        $tours->appends(request()->query());
    @endphp

<div class="tl-page">
    <!-- Search bar (the shared tour search form) -->
    <div id="siteRedesign">
        <div class="form-container tl-search">
            <div class="tab-content active" id="form-tour">
                @include('forms.tours-form')
            </div>
        </div>
    </div>

    <!-- Results header -->
    <div class="tl-head">
        <div>
            <h1 class="tl-title">@if($searchCity){{ $searchCity }}: @endif<span id="tlTotal">{{ $tours->total() }}</span> {{ t('tourlist.tours') }}</h1>
            <p class="tl-subtitle">
                {{ \Carbon\Carbon::parse($searchParams['start_date'])->format('M d, Y') }} – {{ \Carbon\Carbon::parse($searchParams['end_date'])->format('M d, Y') }}
                · {{ $searchParams['adult'] }} {{ t('tourlist.adults') }}@if($searchParams['child'] > 0), {{ $searchParams['child'] }} {{ t('tourlist.children') }}@endif
                @if(($searchParams['type'] ?? 'all') !== 'all') · {{ $searchParams['type_name'] }} @endif
            </p>
        </div>
        <div class="tl-head-actions">
            <button type="button" class="tl-filter-toggle" id="mobileFilterToggle" aria-expanded="false" aria-controls="filterSidebar">
                <i class="fas fa-sliders"></i> {{ t('tourlist.show_filters') }}
            </button>
            <div class="tl-sort-wrap">
                <label class="tl-sort-label" for="sortSelect"><i class="fas fa-arrow-down-wide-short"></i> {{ t('tourlist.sort_by') }}</label>
                <select class="tl-sort" id="sortSelect" onchange="window.location.href = addSortToUrl(this.value)">
                    <option value="popular" {{ $sortBy == 'popular' ? 'selected' : '' }}>{{ t('tourlist.most_popular') }}</option>
                    <option value="price_low" {{ $sortBy == 'price_low' ? 'selected' : '' }}>{{ t('tourlist.price_low_high') }}</option>
                    <option value="price_high" {{ $sortBy == 'price_high' ? 'selected' : '' }}>{{ t('tourlist.price_high_low') }}</option>
                    <option value="duration" {{ $sortBy == 'duration' ? 'selected' : '' }}>{{ t('tourlist.duration') }}</option>
                    <option value="rating" {{ $sortBy == 'rating' ? 'selected' : '' }}>{{ t('tourlist.highest_rated') }}</option>
                </select>
            </div>
        </div>
    </div>

    <div class="tl-main">
        <!-- Filters -->
        <aside class="tl-sidebar" id="filterSidebar">
            @if($searchCity)
                <div class="tl-map">
                    <iframe src="https://maps.google.com/maps?q={{ urlencode(trim($searchCity . ' ' . ($searchParams['location_country'] ?? ''))) }}&z=10&output=embed" title="{{ $searchCity }} map" loading="lazy" tabindex="-1" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    <a class="tl-map-btn" href="https://www.google.com/maps/search/?api=1&query={{ $mapQuery }}" target="_blank" rel="noopener"><i class="fas fa-map-location-dot"></i> View on Map</a>
                </div>
            @endif

            <div class="tl-sidebar-title"><i class="fas fa-sliders"></i> {{ t('tourlist.apply_filters') }} <span class="tl-badge-count" id="tlActiveCount">0</span></div>

            <!-- Price -->
            <div class="tl-group">
                <div class="tl-group-title">{{ t('tourlist.price_range') }} ({{ $active_currency->currency_name }})</div>
                <div class="tl-price-inputs">
                    <input type="number" class="tl-price-box" id="priceMinLabel" value="{{ $minPrice }}" min="{{ $minPrice }}" max="{{ $maxPrice }}" aria-label="Minimum price">
                    <span>—</span>
                    <input type="number" class="tl-price-box" id="priceMaxLabel" value="{{ $maxPrice }}" min="{{ $minPrice }}" max="{{ $maxPrice }}" aria-label="Maximum price">
                </div>
                <div class="tl-range">
                    <div class="tl-range-track"></div>
                    <div class="tl-range-fill" id="priceRangeFill"></div>
                    <input type="range" id="priceMinRange" class="tl-range-input" min="{{ $minPrice }}" max="{{ $maxPrice }}" value="{{ $minPrice }}" aria-label="Minimum price">
                    <input type="range" id="priceMaxRange" class="tl-range-input" min="{{ $minPrice }}" max="{{ $maxPrice }}" value="{{ $maxPrice }}" aria-label="Maximum price">
                </div>
            </div>

            <!-- Duration -->
            @if($durationCounts->filter()->count())
            <div class="tl-group">
                <div class="tl-group-title">{{ t('tourlist.duration') }}</div>
                @foreach($durationBuckets as $key => [$label, $fn])
                    @if($durationCounts[$key] > 0)
                        <label class="tl-check"><span><input type="checkbox" class="tl-duration" value="{{ $key }}"> {{ $label }}</span><span class="tl-count">{{ $durationCounts[$key] }}</span></label>
                    @endif
                @endforeach
            </div>
            @endif

            <!-- Star rating -->
            @if(array_sum($starCounts) > 0)
            <div class="tl-group">
                <div class="tl-group-title">{{ t('tourbooking.star_tour') }}</div>
                @for($star = 5; $star >= 1; $star--)
                    @if($starCounts[$star] > 0)
                        <label class="tl-check"><span><input type="checkbox" class="tl-star" value="{{ $star }}"> <span class="tl-stars-inline">{!! str_repeat('★', $star) !!}</span></span><span class="tl-count">{{ $starCounts[$star] }}</span></label>
                    @endif
                @endfor
            </div>
            @endif

            <!-- Tour name -->
            <div class="tl-group">
                <div class="tl-input-icon">
                    <i class="fas fa-magnifying-glass"></i>
                    <input type="text" id="tlNameSearch" class="tl-name-search" placeholder="{{ t('tourlist.search_destination') }}">
                </div>
            </div>

            <!-- Tour type -->
            @if($typeCounts->count() > 1)
            <div class="tl-group">
                <div class="tl-group-title">{{ t('tourlist.tour_type') }}</div>
                @foreach($typeCounts as $type => $count)
                    <label class="tl-check"><span><input type="checkbox" class="tl-type" value="{{ strtolower($type) }}"> {{ $type }}</span><span class="tl-count">{{ $count }}</span></label>
                @endforeach
            </div>
            @endif

            <!-- Inclusions -->
            @if($inclusionCounts->count())
            <div class="tl-group" id="tlInclGroup">
                <div class="tl-group-title">{{ t('tourlist.whats_included') }}</div>
                @foreach($inclusionCounts as $name => $count)
                    <label class="tl-check {{ $loop->index >= 5 ? 'is-extra' : '' }}"><span><input type="checkbox" class="tl-incl" value="{{ \Str::slug($name) }}"> {{ $name }}</span><span class="tl-count">{{ $count }}</span></label>
                @endforeach
                @if($inclusionCounts->count() > 5)
                    <button type="button" class="tl-show-more" id="tlInclMore">{{ t('tourlist.more') }} <i class="fas fa-chevron-down"></i></button>
                @endif
            </div>
            @endif

            <button type="button" class="tl-clear" id="clearFiltersBtn">{{ t('tourlist.clear_all') }}</button>
        </aside>

        <!-- Results -->
        <div class="tl-results-col">
            <div class="tl-results" id="tlResults">
                @forelse($tourItems as $tour)
                    @php
                        $price = $tlPrice($tour);
                        $stars = max(0, min(5, (int) $tour->stars));
                        $tourType = $tour->packageType->packege_type ?? $tour->packege_type ?? '';
                        $location = trim(($tour->location_name ?? '') . (!empty($tour->location_country) && stripos((string) $tour->location_name, $tour->location_country) === false ? ', ' . $tour->location_country : ''), ', ');
                        $desc = trim((string) ($tour->desc ?? $tour->description ?? ''));
                        $highlights = $tour->highlights ?? [];
                        $detailUrl = route('tours.details', [
                            'slug' => \Str::slug($tour->name),
                            'location' => $searchParams['location'] ?? 'all',
                            'type' => $searchParams['type'] ?? 'all',
                            'startDate' => $searchParams['original_start_date'] ?? date('d-m-Y'),
                            'endDate' => $searchParams['original_end_date'] ?? date('d-m-Y', strtotime('+7 days')),
                            'adult' => $searchParams['adult'] ?? 2,
                            'child' => $searchParams['child'] ?? 0,
                        ]);
                    @endphp
                    <article class="tl-card tour-list-card"
                        data-price="{{ round($price, 2) }}"
                        data-days="{{ (int) $tour->days }}"
                        data-stars="{{ $stars }}"
                        data-name="{{ strtolower($tour->name . " " . $location) }}"
                        data-type="{{ strtolower($tourType) }}"
                        data-incl="{{ collect($highlights)->map(fn($h) => \Str::slug($h))->implode(' ') }}">
                        <a href="{{ $detailUrl }}" class="tl-card-image">
                            <img src="{{ $tour->images->count() ? asset('public/assets/images/' . $tour->images->first()->image) : asset('public/assets/images/placeholder/default.jpg') }}" alt="{{ $tour->name }}" loading="lazy">
                            @if($tour->featured == 1)
                                <span class="tl-featured"><i class="fas fa-star"></i> {{ t('tourlist.featured') }}</span>
                            @endif
                            @if($tour->images->count() > 1)
                                <span class="tl-photos"><i class="fas fa-images"></i> {{ $tour->images->count() }}</span>
                            @endif
                        </a>
                        <div class="tl-card-body">
                            <h3 class="tl-card-title" dir="auto"><a href="{{ $detailUrl }}">{{ $tour->name }}</a></h3>
                            <div class="tl-card-meta">
                                @if($stars > 0)
                                    <span class="tl-stars">@for($i = 1; $i <= 5; $i++)<i class="fas fa-star {{ $i <= $stars ? 'is-on' : '' }}"></i>@endfor</span>
                                @endif
                                @if(is_numeric($tour->rating) && $tour->rating > 0)
                                    <span class="tl-score">{{ rtrim(rtrim(number_format((float) $tour->rating, 1), '0'), '.') }}</span>
                                @endif
                            </div>
                            <div class="tl-facts">
                                @if($location)<span><i class="fas fa-location-dot"></i> {{ $location }}</span>@endif
                                @if($tour->days)
                                    <span><i class="fas fa-clock"></i> {{ $tour->days }} {{ t('tourlist.days') }}@if($tour->nights) / {{ $tour->nights }} {{ t('tourlist.nights') }}@endif</span>
                                @elseif($tour->duration)
                                    <span><i class="fas fa-clock"></i> {{ $tour->duration }}</span>
                                @endif
                                @if($tourType)<span><i class="fas fa-tag"></i> {{ $tourType }}</span>@endif
                            </div>
                            @if($desc !== '')
                                <p class="tl-desc">{{ \Str::limit($desc, 130) }}</p>
                            @endif
                            @if(count($highlights))
                                <div class="tl-pills">
                                    @foreach(array_slice($highlights, 0, 3) as $highlight)
                                        <span class="tl-pill"><i class="fas fa-check"></i> {{ $highlight }}</span>
                                    @endforeach
                                    @if(count($highlights) > 3)
                                        <span class="tl-pill is-more">+{{ count($highlights) - 3 }} {{ t('tourlist.more') }}</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                        <div class="tl-price-panel">
                            <div>
                                <div class="tl-price-label">{{ t('tourlist.starting_from') }}</div>
                                <div class="tl-price">{{ $active_currency->currency_name }} {{ number_format($price) }}</div>
                                <div class="tl-price-unit">{{ t('tourlist.per_person') }}</div>
                            </div>
                            <a href="{{ $detailUrl }}" class="tl-view-btn">{{ t('tourlist.view_details') }} <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </article>
                @empty
                    <div class="tl-empty">
                        <i class="fas fa-magnifying-glass"></i>
                        <h3>{{ t('tourlist.no_tours_found') }}</h3>
                        <p>{{ t('tourlist.try_different_search') }}</p>
                    </div>
                @endforelse
                <div class="tl-empty" id="tlNoMatch" hidden>
                    <i class="fas fa-magnifying-glass"></i>
                    <h3>{{ t('tourlist.no_tours_found') }}</h3>
                    <p>{{ t('tourlist.try_different_search') }}</p>
                </div>
            </div>

            @if($tours->total() > 0)
                <nav class="tl-pagination" aria-label="Pagination">
                    <div class="tl-pages">
                        @if($tours->onFirstPage())
                            <span class="tl-page-btn is-disabled" aria-hidden="true"><i class="fas fa-chevron-left"></i></span>
                        @else
                            <a class="tl-page-btn" href="{{ $tours->previousPageUrl() }}" aria-label="Previous page"><i class="fas fa-chevron-left"></i></a>
                        @endif
                        @php
                            $last = $tours->lastPage();
                            $cur = $tours->currentPage();
                            $show = collect([1, $last, $cur - 1, $cur, $cur + 1])->filter(fn($n) => $n >= 1 && $n <= $last)->unique()->sort()->values();
                            $prev = 0;
                        @endphp
                        @foreach($show as $n)
                            @if($n - $prev > 1)<span class="tl-page-gap">…</span>@endif
                            @if($n == $cur)
                                <span class="tl-page-btn is-active" aria-current="page">{{ $n }}</span>
                            @else
                                <a class="tl-page-btn" href="{{ $tours->url($n) }}">{{ $n }}</a>
                            @endif
                            @php $prev = $n; @endphp
                        @endforeach
                        @if($tours->hasMorePages())
                            <a class="tl-page-btn" href="{{ $tours->nextPageUrl() }}" aria-label="Next page"><i class="fas fa-chevron-right"></i></a>
                        @else
                            <span class="tl-page-btn is-disabled" aria-hidden="true"><i class="fas fa-chevron-right"></i></span>
                        @endif
                    </div>
                    <div class="tl-page-summary">{{ t('tourlist.showing') }} {{ $tours->firstItem() }}–{{ $tours->lastItem() }} / {{ $tours->total() }} {{ t('tourlist.tours') }}</div>
                </nav>
            @endif
        </div>
    </div>
</div>

<script>
    (function () {
        // Mobile filter toggle
        const mobileFilterToggle = document.getElementById('mobileFilterToggle');
        const filterSidebar = document.getElementById('filterSidebar');
        if (mobileFilterToggle) {
            mobileFilterToggle.addEventListener('click', function () {
                const open = filterSidebar.classList.toggle('active');
                mobileFilterToggle.setAttribute('aria-expanded', String(open));
            });
        }

        const inclMore = document.getElementById('tlInclMore');
        if (inclMore) {
            inclMore.addEventListener('click', function () {
                const open = document.getElementById('tlInclGroup').classList.toggle('is-expanded');
                inclMore.querySelector('i').className = open ? 'fas fa-chevron-up' : 'fas fa-chevron-down';
            });
        }

        const priceMinRange = document.getElementById('priceMinRange');
        const priceMaxRange = document.getElementById('priceMaxRange');
        const priceMinLabel = document.getElementById('priceMinLabel');
        const priceMaxLabel = document.getElementById('priceMaxLabel');
        const priceRangeFill = document.getElementById('priceRangeFill');
        const nameSearch = document.getElementById('tlNameSearch');
        const groups = ['.tl-duration', '.tl-star', '.tl-type', '.tl-incl'].map(s => Array.from(document.querySelectorAll(s)));
        const cards = Array.from(document.querySelectorAll('.tl-card'));
        const totalEl = document.getElementById('tlTotal');
        const activeEl = document.getElementById('tlActiveCount');
        const noMatch = document.getElementById('tlNoMatch');
        const serverTotal = {{ (int) $tours->total() }};

        function updateRangeFill() {
            const min = +priceMinRange.min, max = +priceMinRange.max, range = (max - min) || 1;
            const l = ((+priceMinRange.value - min) / range) * 100;
            const r = ((+priceMaxRange.value - min) / range) * 100;
            priceRangeFill.style.left = l + '%';
            priceRangeFill.style.width = (r - l) + '%';
            priceMinLabel.value = priceMinRange.value;
            priceMaxLabel.value = priceMaxRange.value;
        }

        const values = list => list.filter(c => c.checked).map(c => c.value);

        function applyFrontendFilters() {
            const minP = +priceMinRange.value, maxP = +priceMaxRange.value;
            const q = nameSearch.value.trim().toLowerCase();
            const [dur, stars, types, incl] = groups.map(values);
            let active = (q ? 1 : 0) + ((priceMinRange.value != priceMinRange.min || priceMaxRange.value != priceMaxRange.max) ? 1 : 0);
            active += dur.length + stars.length + types.length + incl.length;
            activeEl.textContent = active;

            let visible = 0;
            cards.forEach(function (card) {
                const days = +card.dataset.days;
                const ok =
                    +card.dataset.price >= minP && +card.dataset.price <= maxP &&
                    (q === '' || card.dataset.name.includes(q)) &&
                    (dur.length === 0 || dur.some(d => d === '15+' ? days >= 15 : (days >= +d.split('-')[0] && days <= +d.split('-')[1]))) &&
                    (stars.length === 0 || stars.includes(card.dataset.stars)) &&
                    (types.length === 0 || types.includes(card.dataset.type)) &&
                    incl.every(i => (' ' + card.dataset.incl + ' ').includes(' ' + i + ' '));
                card.hidden = !ok;
                if (ok) visible++;
            });
            totalEl.textContent = active ? visible : serverTotal;
            if (noMatch) noMatch.hidden = !(cards.length && visible === 0);
        }

        function clampBox(box, range) {
            box.addEventListener('change', function () {
                range.value = Math.min(Math.max(+box.value || 0, +range.min), +range.max);
                if (+priceMinRange.value > +priceMaxRange.value) {
                    if (range === priceMinRange) priceMaxRange.value = range.value; else priceMinRange.value = range.value;
                }
                updateRangeFill(); applyFrontendFilters();
            });
        }

        priceMinRange.addEventListener('input', function () {
            if (+priceMinRange.value > +priceMaxRange.value) priceMinRange.value = priceMaxRange.value;
            updateRangeFill(); applyFrontendFilters();
        });
        priceMaxRange.addEventListener('input', function () {
            if (+priceMaxRange.value < +priceMinRange.value) priceMaxRange.value = priceMinRange.value;
            updateRangeFill(); applyFrontendFilters();
        });
        clampBox(priceMinLabel, priceMinRange);
        clampBox(priceMaxLabel, priceMaxRange);
        nameSearch.addEventListener('input', applyFrontendFilters);
        groups.forEach(list => list.forEach(c => c.addEventListener('change', applyFrontendFilters)));

        document.getElementById('clearFiltersBtn').addEventListener('click', function () {
            priceMinRange.value = priceMinRange.min;
            priceMaxRange.value = priceMaxRange.max;
            nameSearch.value = '';
            groups.forEach(list => list.forEach(c => { c.checked = false; }));
            updateRangeFill(); applyFrontendFilters();
        });

        updateRangeFill();
    })();

    // Sort (server side, keeps the other query parameters)
    function addSortToUrl(sortValue) {
        const url = new URL(window.location.href);
        url.searchParams.set('sort_by', sortValue);
        url.searchParams.delete('page');
        return url.toString();
    }
</script>

@endsection
