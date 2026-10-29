@extends('common.layout')
@section('content')
@php
    // Same layout and styles as the tour listing (.tl-* in style.css)
    $active_currency = activeCurrency();
    $sortBy = $sortBy ?? 'popular';
    $items = $packages->getCollection();

    $ulPrice = fn($p) => convertCurrency($p->price, $p->currency ?? 'USD', $active_currency->currency_name);
    $ulNights = fn($p) => (int) ($p->night_in_mekkah ?? 0) + (int) ($p->night_in_madina ?? 0);
    $prices = $items->map($ulPrice);
    $minPrice = $prices->count() ? (int) floor($prices->min()) : 0;
    $maxPrice = $prices->count() ? (int) ceil($prices->max()) : 10000;
    if ($maxPrice <= $minPrice) $maxPrice = $minPrice + 100;

    $starCounts = [];
    for ($s = 5; $s >= 1; $s--) {
        $starCounts[$s] = $items->filter(fn($p) => (int) $p->stars === $s)->count();
    }
    $nightBuckets = [
        '1-3'  => t('umrahlist.nights_1_3'),
        '4-7'  => t('umrahlist.nights_4_7'),
        '8-14' => t('umrahlist.nights_8_14'),
        '15+'  => t('umrahlist.nights_15_plus'),
    ];
    $inBucket = fn($n, $key) => $key === '15+' ? $n >= 15 : ($n >= (int) explode('-', $key)[0] && $n <= (int) explode('-', $key)[1]);
    $nightCounts = collect($nightBuckets)->map(fn($label, $key) => $items->filter(fn($p) => $ulNights($p) > 0 && $inBucket($ulNights($p), $key))->count());
    $typeCounts = $items->groupBy(fn($p) => $p->packageType->packege_type ?? $p->packege_type ?? '')->filter(fn($g, $k) => $k !== '')->map->count()->sortDesc();
    $inclusionCounts = $items->flatMap(fn($p) => array_unique($p->inclusion_names ?? []))->countBy()->sortDesc();

    $fromCity = $originAirport->city ?? $searchParams['origin'];
    $toCity = $destinationAirport->city ?? $searchParams['destination'];
    $mapQuery = urlencode(trim($toCity . ' ' . ($destinationAirport->country ?? '')));
@endphp

<div class="tl-page">
    <!-- Search bar (the shared Umrah search form) -->
    <div id="siteRedesign">
        <div class="form-container tl-search">
            <div class="tab-content active" id="form-umrah">
                @include('forms.umrah-form')
            </div>
        </div>
    </div>

    <!-- Results header -->
    <div class="tl-head">
        <div>
            <h1 class="tl-title">{{ $fromCity }} <i class="fas fa-arrow-right tl-title-arrow"></i> {{ $toCity }}: <span id="tlTotal">{{ $packages->total() }}</span> {{ t('umrahlist.packages') }}</h1>
            <p class="tl-subtitle">
                {{ $searchParams['departure_date'] }} – {{ $searchParams['return_date'] }}
                · {{ $searchParams['adult'] }} {{ t('umrahlist.adults') }}@if($searchParams['child'] > 0), {{ $searchParams['child'] }} {{ t('umrahlist.children') }}@endif @if($searchParams['infant'] > 0), {{ $searchParams['infant'] }} {{ t('umrahlist.infants') }}@endif
            </p>
        </div>
        <div class="tl-head-actions">
            <button type="button" class="tl-filter-toggle" id="mobileFilterToggle" aria-expanded="false" aria-controls="filterSidebar">
                <i class="fas fa-sliders"></i> {{ t('umrahlist.show_filters') }}
            </button>
            <div class="tl-sort-wrap">
                <label class="tl-sort-label" for="sortSelect"><i class="fas fa-arrow-down-wide-short"></i> {{ t('umrahlist.sort_by') }}</label>
                <select class="tl-sort" id="sortSelect" onchange="window.location.href = addSortToUrl(this.value)">
                    <option value="popular" {{ $sortBy == 'popular' ? 'selected' : '' }}>{{ t('umrahlist.most_popular') }}</option>
                    <option value="price_low" {{ $sortBy == 'price_low' ? 'selected' : '' }}>{{ t('umrahlist.price_low_high') }}</option>
                    <option value="price_high" {{ $sortBy == 'price_high' ? 'selected' : '' }}>{{ t('umrahlist.price_high_low') }}</option>
                    <option value="duration" {{ $sortBy == 'duration' ? 'selected' : '' }}>{{ t('umrahlist.duration') }}</option>
                    <option value="rating" {{ $sortBy == 'rating' ? 'selected' : '' }}>{{ t('umrahlist.highest_rated') }}</option>
                </select>
            </div>
        </div>
    </div>

    <div class="tl-main">
        <!-- Filters -->
        <aside class="tl-sidebar" id="filterSidebar">
            <div class="tl-map">
                <iframe src="https://maps.google.com/maps?q={{ $mapQuery }}&z=11&output=embed" title="{{ $toCity }} map" loading="lazy" tabindex="-1" referrerpolicy="no-referrer-when-downgrade"></iframe>
                <a class="tl-map-btn" href="https://www.google.com/maps/search/?api=1&query={{ $mapQuery }}" target="_blank" rel="noopener"><i class="fas fa-map-location-dot"></i> View on Map</a>
            </div>

            <div class="tl-sidebar-title"><i class="fas fa-sliders"></i> {{ t('umrahlist.apply_filters') }} <span class="tl-badge-count" id="tlActiveCount">0</span></div>

            <!-- Price -->
            <div class="tl-group">
                <div class="tl-group-title">{{ t('umrahlist.price_range') }} ({{ $active_currency->currency_name }})</div>
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

            <!-- Nights -->
            @if($nightCounts->filter()->count())
            <div class="tl-group">
                <div class="tl-group-title">{{ t('umrahlist.duration') }}</div>
                @foreach($nightBuckets as $key => $label)
                    @if($nightCounts[$key] > 0)
                        <label class="tl-check"><span><input type="checkbox" class="tl-duration" value="{{ $key }}"> {{ $label }}</span><span class="tl-count">{{ $nightCounts[$key] }}</span></label>
                    @endif
                @endforeach
            </div>
            @endif

            <!-- Star rating -->
            @if(array_sum($starCounts) > 0)
            <div class="tl-group">
                <div class="tl-group-title">{{ t('umrahlist.star_rating') }}</div>
                @for($star = 5; $star >= 1; $star--)
                    @if($starCounts[$star] > 0)
                        <label class="tl-check"><span><input type="checkbox" class="tl-star" value="{{ $star }}"> <span class="tl-stars-inline">{!! str_repeat('★', $star) !!}</span></span><span class="tl-count">{{ $starCounts[$star] }}</span></label>
                    @endif
                @endfor
            </div>
            @endif

            <!-- Package name -->
            <div class="tl-group">
                <div class="tl-input-icon">
                    <i class="fas fa-magnifying-glass"></i>
                    <input type="text" id="tlNameSearch" class="tl-name-search" placeholder="{{ t('umrahlist.umrah_packages') }}…">
                </div>
            </div>

            <!-- Package type -->
            @if($typeCounts->count() > 1)
            <div class="tl-group">
                <div class="tl-group-title">{{ t('umrahlist.package_type') }}</div>
                @foreach($typeCounts as $type => $count)
                    <label class="tl-check"><span><input type="checkbox" class="tl-type" value="{{ strtolower($type) }}"> {{ ucfirst($type) }}</span><span class="tl-count">{{ $count }}</span></label>
                @endforeach
            </div>
            @endif

            <!-- Inclusions -->
            @if($inclusionCounts->count())
            <div class="tl-group" id="tlInclGroup">
                <div class="tl-group-title">{{ t('umrahlist.whats_included') }}</div>
                @foreach($inclusionCounts as $name => $count)
                    <label class="tl-check {{ $loop->index >= 5 ? 'is-extra' : '' }}"><span><input type="checkbox" class="tl-incl" value="{{ \Str::slug($name) }}"> {{ $name }}</span><span class="tl-count">{{ $count }}</span></label>
                @endforeach
                @if($inclusionCounts->count() > 5)
                    <button type="button" class="tl-show-more" id="tlInclMore">{{ t('umrahlist.more') }} <i class="fas fa-chevron-down"></i></button>
                @endif
            </div>
            @endif

            <button type="button" class="tl-clear" id="clearFiltersBtn">{{ t('umrahlist.clear_all') }}</button>
        </aside>

        <!-- Results -->
        <div class="tl-results-col">
            <div class="tl-results" id="tlResults">
                @forelse($items as $package)
                    @php
                        $price = $ulPrice($package);
                        $nights = $ulNights($package);
                        $stars = max(0, min(5, (int) $package->stars));
                        $type = $package->packageType->packege_type ?? $package->packege_type ?? '';
                        $inclusions = $package->inclusion_names ?? [];
                        $detailUrl = route('umrah.details', [
                            'slug' => \Str::slug($package->name),
                            'origin' => strtolower($searchParams['origin']),
                            'destination' => strtolower($searchParams['destination']),
                            'departure_date' => $searchParams['departure_date'],
                            'return_date' => $searchParams['return_date'],
                            'adult' => $searchParams['adult'],
                            'child' => $searchParams['child'],
                            'infant' => $searchParams['infant'],
                            'makkah_nights' => $searchParams['makkah_nights'],
                            'madina_nights' => $searchParams['madina_nights'],
                        ]);
                    @endphp
                    <article class="tl-card umrah-list-card"
                        data-price="{{ round($price, 2) }}"
                        data-days="{{ $nights }}"
                        data-stars="{{ $stars }}"
                        data-name="{{ strtolower($package->name . ' ' . $package->from_location . ' ' . $package->to_location) }}"
                        data-type="{{ strtolower($type) }}"
                        data-incl="{{ collect($inclusions)->map(fn($i) => \Str::slug($i))->implode(' ') }}">
                        <a href="{{ $detailUrl }}" class="tl-card-image">
                            <img src="{{ $package->images->count() ? asset('public/assets/images/' . $package->images->first()->image) : asset('public/assets/images/placeholder/default.jpg') }}" alt="{{ $package->name }}" loading="lazy">
                            @if($package->featured == 1)
                                <span class="tl-featured"><i class="fas fa-star"></i> {{ t('umrahlist.featured') }}</span>
                            @endif
                            @if($package->images->count() > 1)
                                <span class="tl-photos"><i class="fas fa-images"></i> {{ $package->images->count() }}</span>
                            @endif
                        </a>
                        <div class="tl-card-body">
                            <h3 class="tl-card-title" dir="auto"><a href="{{ $detailUrl }}">{{ $package->name }}</a></h3>
                            <div class="tl-card-meta">
                                @if($stars > 0)
                                    <span class="tl-stars">@for($i = 1; $i <= 5; $i++)<i class="fas fa-star {{ $i <= $stars ? 'is-on' : '' }}"></i>@endfor</span>
                                @endif
                                @if(is_numeric($package->rating) && $package->rating > 0)
                                    <span class="tl-score">{{ rtrim(rtrim(number_format((float) $package->rating, 1), '0'), '.') }}</span>
                                @endif
                            </div>
                            <div class="tl-facts">
                                @if($package->from_location && $package->to_location)
                                    <span><i class="fas fa-plane-departure"></i> {{ $package->from_location }} <i class="fas fa-arrow-right"></i> {{ $package->to_location }}</span>
                                @endif
                                @if($package->duration)<span><i class="fas fa-clock"></i> {{ $package->duration }}</span>@endif
                                @if($package->night_in_mekkah)<span><i class="fas fa-kaaba"></i> {{ $package->night_in_mekkah }} {{ t('umrahlist.nights_makkah') }}</span>@endif
                                @if($package->night_in_madina)<span><i class="fas fa-mosque"></i> {{ $package->night_in_madina }} {{ t('umrahlist.nights_madina') }}</span>@endif
                                @if($package->class)<span><i class="fas fa-plane"></i> {{ ucfirst($package->class) }}</span>@endif
                            </div>
                            @if(trim((string) $package->desc) !== '')
                                <p class="tl-desc">{{ \Str::limit(strip_tags($package->desc), 130) }}</p>
                            @endif
                            @if(count($inclusions))
                                <div class="tl-pills">
                                    @foreach(array_slice($inclusions, 0, 3) as $inclusion)
                                        <span class="tl-pill"><i class="fas fa-check"></i> {{ $inclusion }}</span>
                                    @endforeach
                                    @if(count($inclusions) > 3)
                                        <span class="tl-pill is-more">+{{ count($inclusions) - 3 }} {{ t('umrahlist.more') }}</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                        <div class="tl-price-panel">
                            <div>
                                <div class="tl-price-label">{{ t('umrahlist.starting_from') }}</div>
                                <div class="tl-price">{{ $active_currency->currency_name }} {{ number_format($price) }}</div>
                                <div class="tl-price-unit">{{ t('umrahlist.per_person') }}</div>
                            </div>
                            <a href="{{ $detailUrl }}" class="tl-view-btn">{{ t('umrahlist.view_details') }} <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </article>
                @empty
                    <div class="tl-empty">
                        <i class="fas fa-magnifying-glass"></i>
                        <h3>{{ t('umrahlist.no_packages_found') }}</h3>
                        <p>{{ t('umrahlist.try_different_search') }}</p>
                    </div>
                @endforelse
                <div class="tl-empty" id="tlNoMatch" hidden>
                    <i class="fas fa-magnifying-glass"></i>
                    <h3>{{ t('umrahlist.no_packages_found') }}</h3>
                    <p>{{ t('umrahlist.try_different_search') }}</p>
                </div>
            </div>

            @if($packages->total() > 0)
                <nav class="tl-pagination" aria-label="Pagination">
                    <div class="tl-pages">
                        @if($packages->onFirstPage())
                            <span class="tl-page-btn is-disabled" aria-hidden="true"><i class="fas fa-chevron-left"></i></span>
                        @else
                            <a class="tl-page-btn" href="{{ $packages->previousPageUrl() }}" aria-label="Previous page"><i class="fas fa-chevron-left"></i></a>
                        @endif
                        @php
                            $last = $packages->lastPage();
                            $cur = $packages->currentPage();
                            $show = collect([1, $last, $cur - 1, $cur, $cur + 1])->filter(fn($n) => $n >= 1 && $n <= $last)->unique()->sort()->values();
                            $prev = 0;
                        @endphp
                        @foreach($show as $n)
                            @if($n - $prev > 1)<span class="tl-page-gap">…</span>@endif
                            @if($n == $cur)
                                <span class="tl-page-btn is-active" aria-current="page">{{ $n }}</span>
                            @else
                                <a class="tl-page-btn" href="{{ $packages->url($n) }}">{{ $n }}</a>
                            @endif
                            @php $prev = $n; @endphp
                        @endforeach
                        @if($packages->hasMorePages())
                            <a class="tl-page-btn" href="{{ $packages->nextPageUrl() }}" aria-label="Next page"><i class="fas fa-chevron-right"></i></a>
                        @else
                            <span class="tl-page-btn is-disabled" aria-hidden="true"><i class="fas fa-chevron-right"></i></span>
                        @endif
                    </div>
                    <div class="tl-page-summary">{{ t('umrahlist.showing') }} {{ $packages->firstItem() }}–{{ $packages->lastItem() }} / {{ $packages->total() }} {{ t('umrahlist.packages') }}</div>
                </nav>
            @endif
        </div>
    </div>
</div>

<script>
    (function () {
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
        const serverTotal = {{ (int) $packages->total() }};

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
                const nights = +card.dataset.days;
                const ok =
                    +card.dataset.price >= minP && +card.dataset.price <= maxP &&
                    (q === '' || card.dataset.name.includes(q)) &&
                    (dur.length === 0 || dur.some(d => d === '15+' ? nights >= 15 : (nights >= +d.split('-')[0] && nights <= +d.split('-')[1]))) &&
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
