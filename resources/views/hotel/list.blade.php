@extends('common.layout')
@section('page_class', 'view-hotel-list')
@section('content')
    @php
        use Illuminate\Support\Str;

        $hotels = $hotels ?? [];
        $nights = 1;
        if (!empty($hotel_search['checkin']) && !empty($hotel_search['checkout'])) {
            $nights = max(1, (int) \Carbon\Carbon::parse($hotel_search['checkin'])->diffInDays(\Carbon\Carbon::parse($hotel_search['checkout'])));
        }
        $searchAdults = $hotel_search['adults'] ?? 2;
        $searchRooms  = $hotel_search['rooms'] ?? 1;
        $searchCity   = isset($hotel_search['city']) ? ucwords(str_replace('-', ' ', $hotel_search['city'])) : '';
        $currency     = !empty($hotels) ? $hotels[0]['currency'] : 'PKR';

        // amenities store Bootstrap Icons classes, which are not loaded on public pages
        $hlAmenityIcons = [
            'bi-wifi' => 'fa-wifi', 'bi-car-front' => 'fa-car', 'bi-snow' => 'fa-snowflake',
            'bi-tv' => 'fa-tv', 'bi-water' => 'fa-water-ladder', 'bi-bicycle' => 'fa-dumbbell',
            'bi-shop' => 'fa-utensils', 'bi-bell' => 'fa-bell-concierge', 'bi-droplet' => 'fa-shirt',
            'bi-arrow-up-square' => 'fa-elevator', 'bi-cup-straw' => 'fa-martini-glass',
            'bi-heart-pulse' => 'fa-spa', 'bi-people' => 'fa-users', 'bi-clock' => 'fa-clock',
            'bi-egg-fried' => 'fa-egg', 'bi-cup-hot' => 'fa-mug-hot', 'bi-shield-lock' => 'fa-lock',
            'bi-door-open' => 'fa-door-open', 'bi-bus-front' => 'fa-van-shuttle', 'bi-person-badge' => 'fa-id-badge',
        ];
        $hlIcon = function ($icon) use ($hlAmenityIcons) {
            $bi = collect(explode(' ', (string) $icon))->first(fn($c) => isset($hlAmenityIcons[$c]));
            return $bi ? $hlAmenityIcons[$bi] : 'fa-check';
        };

        // Normalise each row once: amenities, breakfast flag, property type, per-night price
        $rows = [];
        $amenityCounts = [];
        $typeCounts = [];
        $breakfastCount = 0;
        foreach ($hotels as $i => $hotel) {
            $amen = [];
            $hasBreakfast = false;
            foreach (($hotel['amenities'] ?? []) as $a) {
                $name = is_array($a) ? ($a['name'] ?? '') : $a;
                $slug = Str::slug($name);
                if (!$slug) continue;
                $amen[$slug] = ['name' => $name, 'icon' => $hlIcon(is_array($a) ? ($a['icon'] ?? '') : '')];
                if (str_contains($slug, 'breakfast')) $hasBreakfast = true;
            }
            foreach ($amen as $slug => $a) {
                $amenityCounts[$slug] = ['name' => $a['name'], 'icon' => $a['icon'], 'count' => ($amenityCounts[$slug]['count'] ?? 0) + 1];
            }
            if ($hasBreakfast) $breakfastCount++;
            $type = trim((string) ($hotel['categoryName'] ?? ''));
            if ($type !== '') $typeCounts[$type] = ($typeCounts[$type] ?? 0) + 1;

            $rows[] = $hotel + [
                '_index' => $i,
                '_amenities' => $amen,
                '_breakfast' => $hasBreakfast,
                '_type' => $type,
                '_perNight' => round(($hotel['minRate'] ?? 0) / max($nights, 1), 2),
                '_photos' => !empty($hotel['all_images']) ? count($hotel['all_images']) : (!empty($hotel['images']) ? 1 : 0),
            ];
        }
        uasort($amenityCounts, fn($a, $b) => $b['count'] <=> $a['count']);
        arsort($typeCounts);

        $minPrice = !empty($rows) ? (int) floor(min(array_column($rows, '_perNight'))) : 0;
        $maxPrice = !empty($rows) ? (int) ceil(max(array_column($rows, '_perNight'))) : 50000;
        if ($maxPrice <= $minPrice) $maxPrice = $minPrice + 100;

        $starCounts = [];
        for ($s = 5; $s >= 1; $s--) {
            $starCounts[$s] = count(array_filter($rows, fn($h) => (int) ($h['stars'] ?? 0) === $s));
        }

        $mapQuery = urlencode('hotels in ' . $searchCity);
    @endphp


<div id="siteRedesign">
<div class="hlist-page">
<div class="hotel-container">

    <!-- Search bar (the shared hotel search form) -->
    <div class="form-container">
        <div class="tab-content active" id="form-hotel">
            @include('forms.hotel-form')
        </div>
    </div>

    <!-- Results header -->
    <div class="hlist-head">
        <div>
            <h1 class="hlist-title">{{ $searchCity }}@if($searchCity): @endif<span id="totalCount">{{ count($rows) }}</span> {{ t('hotellist.hotels') }}</h1>
            <p class="hlist-subtitle">
                {{ isset($hotel_search['checkin']) && $hotel_search['checkin'] ? \Carbon\Carbon::parse($hotel_search['checkin'])->format('M d, Y') : '' }}
                @if(!empty($hotel_search['checkout'])) – {{ \Carbon\Carbon::parse($hotel_search['checkout'])->format('M d, Y') }} @endif
                · {{ $searchAdults }} {{ t('hotellist.adults') }}@if(!empty($hotel_search['childs']) && $hotel_search['childs'] > 0), {{ $hotel_search['childs'] }} {{ t('hotellist.children') }}@endif
                · {{ $searchRooms }} {{ $searchRooms == 1 ? t('hotellist.room') : t('hotellist.rooms') }}
            </p>
        </div>
        <div class="hlist-head-actions">
            <button type="button" class="hlist-filter-toggle" id="hlistFilterToggle" aria-expanded="false" aria-controls="hlistSidebar">
                <i class="fas fa-sliders"></i> {{ t('hotellist.filter_results') }}
            </button>
            <div class="hlist-sort-wrap">
                <label class="hlist-sort-label" for="hlistSort"><i class="fas fa-arrow-down-wide-short"></i></label>
                <select class="hlist-sort" id="hlistSort">
                    <option value="top_picks">{{ t('hotellist.our_top_picks') }}</option>
                    <option value="price_asc">{{ t('hotellist.price_low_high') }}</option>
                    <option value="price_desc">{{ t('hotellist.price_high_low') }}</option>
                    <option value="rating">{{ t('hotellist.star_rating') }}</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="hlist-main">
        <!-- Sidebar Filter -->
        <aside class="hlist-sidebar" id="hlistSidebar">
            @if($searchCity)
            <div class="hlist-map">
                <iframe src="https://maps.google.com/maps?q={{ $mapQuery }}&z=11&output=embed" title="{{ $searchCity }} map" loading="lazy" tabindex="-1" referrerpolicy="no-referrer-when-downgrade"></iframe>
                <a class="hlist-map-btn" href="https://www.google.com/maps/search/?api=1&query={{ $mapQuery }}" target="_blank" rel="noopener"><i class="fas fa-map-location-dot"></i> View on Map</a>
            </div>
            @endif

            <div class="hlist-sidebar-header">
                <div class="hlist-sidebar-title">
                    <i class="fas fa-sliders"></i>
                    {{ t('hotellist.filter_results') }}
                    <span class="hlist-badge-count" id="activeFilterCount">0</span>
                </div>
            </div>

            <!-- Price Range -->
            <div class="hlist-filter-group">
                <div class="hlist-filter-title">{{ t('hotellist.price_per_night') }} ({{ $currency }})</div>
                <div class="hlist-price-inputs">
                    <input type="number" class="hlist-price-box" id="priceMinBox" value="{{ $minPrice }}" min="{{ $minPrice }}" max="{{ $maxPrice }}" aria-label="Minimum price">
                    <span class="hlist-price-sep">—</span>
                    <input type="number" class="hlist-price-box" id="priceMaxBox" value="{{ $maxPrice }}" min="{{ $minPrice }}" max="{{ $maxPrice }}" aria-label="Maximum price">
                </div>
                <div class="hlist-range-slider">
                    <div class="hlist-range-track"></div>
                    <div class="hlist-range-selected" id="rangeSelected"></div>
                    <input type="range" id="priceMinSlider" class="hlist-range-input" min="{{ $minPrice }}" max="{{ $maxPrice }}" value="{{ $minPrice }}" step="1" aria-label="Minimum price">
                    <input type="range" id="priceMaxSlider" class="hlist-range-input" min="{{ $minPrice }}" max="{{ $maxPrice }}" value="{{ $maxPrice }}" step="1" aria-label="Maximum price">
                </div>
            </div>

            <!-- Star Rating -->
            <div class="hlist-filter-group">
                <div class="hlist-filter-title">{{ t('hotellist.property') }}</div>
                @for($star = 5; $star >= 1; $star--)
                    <label class="hlist-check-row {{ $starCounts[$star] === 0 ? 'is-disabled' : '' }}">
                        <span class="hlist-check-left">
                            <input type="checkbox" class="star-filter" id="star{{ $star }}" value="{{ $star }}" {{ $starCounts[$star] === 0 ? 'disabled' : '' }}>
                            <span class="hlist-stars-inline">{!! str_repeat('★', $star) !!}</span>
                        </span>
                        <span class="hlist-count" data-star-count="{{ $star }}">{{ $starCounts[$star] }}</span>
                    </label>
                @endfor
            </div>

            <!-- Hotel Name -->
            <div class="hlist-filter-group">
                <div class="hlist-input-icon">
                    <i class="fas fa-magnifying-glass"></i>
                    <input type="text" id="hotelNameSearch" class="hlist-name-search" placeholder="{{ t('hotellist.type_hotel_name') }}">
                </div>
            </div>

            <!-- Popular: breakfast (from the hotels' own amenities) -->
            @if($breakfastCount > 0)
            <div class="hlist-filter-group">
                <label class="hlist-check-row">
                    <span class="hlist-check-left">
                        <input type="checkbox" class="feature-filter" value="breakfast">
                        <i class="fas fa-mug-hot"></i> {{ t('hotellist.breakfast_included') }}
                    </span>
                    <span class="hlist-count">{{ $breakfastCount }}</span>
                </label>
            </div>
            @endif

            <!-- Property type (only when the results carry one) -->
            @if(count($typeCounts) > 1)
            <div class="hlist-filter-group">
                @foreach($typeCounts as $type => $count)
                    <label class="hlist-check-row">
                        <span class="hlist-check-left">
                            <input type="checkbox" class="type-filter" value="{{ strtolower($type) }}"> {{ $type }}
                        </span>
                        <span class="hlist-count">{{ $count }}</span>
                    </label>
                @endforeach
            </div>
            @endif

            <!-- Amenities (only when the results carry them) -->
            @if(count($amenityCounts))
            <div class="hlist-filter-group" id="amenityGroup">
                <div class="hlist-filter-title">{{ t('hoteldetails.amenities') }}</div>
                @foreach($amenityCounts as $slug => $a)
                    <label class="hlist-check-row {{ $loop->index >= 5 ? 'is-extra' : '' }}">
                        <span class="hlist-check-left">
                            <input type="checkbox" class="amenity-filter" value="{{ $slug }}">
                            <i class="fas {{ $a['icon'] }}"></i> {{ $a['name'] }}
                        </span>
                        <span class="hlist-count">{{ $a['count'] }}</span>
                    </label>
                @endforeach
                @if(count($amenityCounts) > 5)
                    <button type="button" class="hlist-show-more" id="amenityMore">Show More <i class="fas fa-chevron-down"></i></button>
                @endif
            </div>
            @endif

            <button type="button" class="hlist-clear-all" id="resetFilters">{{ t('hotellist.clear_all') }}</button>
        </aside>

        <!-- Hotel Cards -->
        <div class="hlist-results-col">
            <div class="hlist-results" id="hlistResults">
                @forelse($rows as $hotel)
                    @php
                        $stars = max(0, min(5, (int) ($hotel['stars'] ?? 0)));
                        if (!empty($hotel['redirect'])) {
                            $detailUrl = $hotel['redirect'];
                        } else {
                            $detailUrl = url('hotel/details') . '/' . $hotel['hotel_id'] . '/' . Str::slug(Str::limit($hotel['name'], 30), '-')
                                . '/' . ($hotel_search['checkin'] ?? '') . '/' . ($hotel_search['checkout'] ?? '')
                                . '/' . ($hotel_search['adults'] ?? '') . '/' . (!empty($hotel_search['childs']) ? $hotel_search['childs'] : '0')
                                . '/' . ($hotel_search['rooms'] ?? '') . '/' . $hotel['supplier_name'];
                        }
                        $external = !empty($hotel['redirect']);
                    @endphp
                    <article class="hlist-card"
                         data-index="{{ $hotel['_index'] }}"
                         data-price="{{ $hotel['_perNight'] }}"
                         data-stars="{{ $stars }}"
                         data-hotel-name="{{ strtolower($hotel['name']) }}"
                         data-breakfast="{{ $hotel['_breakfast'] ? 1 : 0 }}"
                         data-type="{{ strtolower($hotel['_type']) }}"
                         data-amenities="{{ implode(' ', array_keys($hotel['_amenities'])) }}">
                        <a href="{{ $detailUrl }}" class="hlist-card-image" @if($external) target="_blank" rel="noopener" @endif>
                            <img src="{{ $hotel['images'] }}" alt="{{ $hotel['name'] }}" loading="lazy" onerror="this.src='https://placehold.co/400x300?text=Hotel'">
                            <span class="hlist-card-supplier">{{ $hotel['supplier_name'] }}</span>
                            @if($hotel['_photos'] > 1)
                                <span class="hlist-card-photos"><i class="fas fa-images"></i> {{ $hotel['_photos'] }}</span>
                            @endif
                        </a>
                        <div class="hlist-card-body">
                            <h3 class="hlist-card-title" dir="auto"><a href="{{ $detailUrl }}" @if($external) target="_blank" rel="noopener" @endif>{{ $hotel['name'] }}</a></h3>
                            @if($stars > 0)
                                <span class="hlist-card-stars" aria-label="{{ $stars }} stars">
                                    @for($s = 1; $s <= 5; $s++)<i class="fas fa-star {{ $s <= $stars ? 'is-on' : '' }}"></i>@endfor
                                </span>
                            @endif
                            @if(!empty($hotel['address']))
                                <div class="hlist-card-address"><i class="fas fa-location-dot"></i> <span>{{ $hotel['address'] }}</span></div>
                            @endif
                            <div class="hlist-pills">
                                @foreach(array_slice($hotel['_amenities'], 0, 4) as $a)
                                    <span class="hlist-pill"><i class="fas {{ $a['icon'] }}"></i> {{ $a['name'] }}</span>
                                @endforeach
                                @if($hotel['_breakfast'] && empty(array_filter(array_keys(array_slice($hotel['_amenities'], 0, 4)), fn($k) => str_contains($k, 'breakfast'))))
                                    <span class="hlist-pill"><i class="fas fa-mug-hot"></i> {{ t('hotellist.breakfast_included') }}</span>
                                @endif
                                <span class="hlist-pill"><i class="fas fa-check"></i> {{ t('hotellist.instant_confirmation') }}</span>
                            </div>
                            @if(!empty($hotel['room_name']))
                                <div class="hlist-room-line"><b>{{ $hotel['room_name'] }}</b> · {{ $searchRooms }} {{ $searchRooms == 1 ? t('hotellist.room') : t('hotellist.rooms') }} · {{ $searchAdults }} {{ t('hotellist.adults') }}</div>
                            @endif
                        </div>
                        <div class="hlist-price-panel">
                            <div>
                                <div class="hlist-price-total">{{ number_format($hotel['_perNight'], 0) }} {{ $hotel['currency'] }}</div>
                                <div class="hlist-price-unit">/ {{ t('hotellist.per_night') }}</div>
                                <div class="hlist-price-meta">
                                    {{ number_format($hotel['minRate'], 0) }} {{ $hotel['currency'] }} {{ t('hotellist.total') }} · {{ $nights }} {{ $nights == 1 ? t('hotellist.night') : t('hotellist.nights') }}<br>
                                    {{ t('hotellist.includes_taxes') }}
                                </div>
                            </div>
                            <a href="{{ $detailUrl }}" class="hlist-view-deal-btn" @if($external) target="_blank" rel="noopener" @endif>
                                {{ t('hotellist.view_deal') }} <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </article>
                @empty
                    <div class="hlist-no-results">{{ t('hotellist.no_hotels_found') }}</div>
                @endforelse
            </div>

            @if(count($rows))
            <nav class="hlist-pagination" aria-label="Pagination">
                <div class="hlist-pages" id="hlistPages"></div>
                <div class="hlist-page-summary">
                    {{ t('hotellist.showing') }} <span id="pageFrom">1</span>–<span id="pageTo">1</span> {{ t('hotellist.of') }} <span id="visibleCount">{{ count($rows) }}</span> {{ t('hotellist.hotels') }}
                </div>
            </nav>
            @endif
        </div>
    </div>
</div>
</div>
</div>

<script>
    (function () {
        const PAGE_SIZE = 10;
        const priceMinSlider  = document.getElementById('priceMinSlider');
        const priceMaxSlider  = document.getElementById('priceMaxSlider');
        const priceMinBox     = document.getElementById('priceMinBox');
        const priceMaxBox     = document.getElementById('priceMaxBox');
        const rangeSelected   = document.getElementById('rangeSelected');
        const hotelNameSearch = document.getElementById('hotelNameSearch');
        const starFilters     = document.querySelectorAll('.star-filter');
        const featureFilters  = document.querySelectorAll('.feature-filter');
        const typeFilters     = document.querySelectorAll('.type-filter');
        const amenityFilters  = document.querySelectorAll('.amenity-filter');
        const allCards        = Array.from(document.querySelectorAll('.hlist-card'));
        const resultsContainer = document.getElementById('hlistResults');
        const activeFilterCountEl = document.getElementById('activeFilterCount');
        const visibleCount    = document.getElementById('visibleCount');
        const pagesEl         = document.getElementById('hlistPages');
        const sortSelect      = document.getElementById('hlistSort');
        let page = 1;
        let matching = allCards.slice();

        // ── Mobile: filters panel toggle ─────────────────────────────────────
        const toggle = document.getElementById('hlistFilterToggle');
        const sidebar = document.getElementById('hlistSidebar');
        if (toggle && sidebar) {
            toggle.addEventListener('click', function () {
                const open = sidebar.classList.toggle('is-open');
                toggle.setAttribute('aria-expanded', String(open));
            });
        }

        // ── Amenities show more ──────────────────────────────────────────────
        const amenityMore = document.getElementById('amenityMore');
        if (amenityMore) {
            amenityMore.addEventListener('click', function () {
                const group = document.getElementById('amenityGroup');
                const open = group.classList.toggle('is-expanded');
                amenityMore.innerHTML = open ? 'Show Less <i class="fas fa-chevron-up"></i>' : 'Show More <i class="fas fa-chevron-down"></i>';
            });
        }

        // ── Dual price range slider + editable boxes ─────────────────────────
        function updateRangeUI() {
            if (!priceMinSlider) return;
            const min = parseFloat(priceMinSlider.min), max = parseFloat(priceMinSlider.max);
            let minVal = parseFloat(priceMinSlider.value), maxVal = parseFloat(priceMaxSlider.value);
            if (minVal > maxVal) { minVal = maxVal; priceMinSlider.value = minVal; }
            const minPct = max > min ? ((minVal - min) / (max - min)) * 100 : 0;
            const maxPct = max > min ? ((maxVal - min) / (max - min)) * 100 : 100;
            rangeSelected.style.left = minPct + '%';
            rangeSelected.style.right = (100 - maxPct) + '%';
            priceMinBox.value = Math.round(minVal);
            priceMaxBox.value = Math.round(maxVal);
        }
        if (priceMinSlider && priceMaxSlider) {
            priceMinSlider.addEventListener('input', function () {
                if (+priceMinSlider.value > +priceMaxSlider.value) priceMinSlider.value = priceMaxSlider.value;
                updateRangeUI(); applyFilters();
            });
            priceMaxSlider.addEventListener('input', function () {
                if (+priceMaxSlider.value < +priceMinSlider.value) priceMaxSlider.value = priceMinSlider.value;
                updateRangeUI(); applyFilters();
            });
            [[priceMinBox, priceMinSlider], [priceMaxBox, priceMaxSlider]].forEach(function (pair) {
                pair[0].addEventListener('change', function () {
                    const v = Math.min(Math.max(parseFloat(pair[0].value) || 0, +pair[1].min), +pair[1].max);
                    pair[1].value = v;
                    if (+priceMinSlider.value > +priceMaxSlider.value) {
                        if (pair[1] === priceMinSlider) priceMaxSlider.value = v; else priceMinSlider.value = v;
                    }
                    updateRangeUI(); applyFilters();
                });
            });
            updateRangeUI();
        }

        // ── Filter inputs ────────────────────────────────────────────────────
        if (hotelNameSearch) hotelNameSearch.addEventListener('input', applyFilters);
        [starFilters, featureFilters, typeFilters, amenityFilters].forEach(list => list.forEach(f => f.addEventListener('change', applyFilters)));
        if (sortSelect) sortSelect.addEventListener('change', applyFilters);

        // ── Clear all ────────────────────────────────────────────────────────
        const resetBtn = document.getElementById('resetFilters');
        if (resetBtn) {
            resetBtn.addEventListener('click', function () {
                if (priceMinSlider) { priceMinSlider.value = priceMinSlider.min; priceMaxSlider.value = priceMaxSlider.max; updateRangeUI(); }
                if (hotelNameSearch) hotelNameSearch.value = '';
                [starFilters, featureFilters, typeFilters, amenityFilters].forEach(list => list.forEach(f => { f.checked = false; }));
                if (sortSelect) sortSelect.value = 'top_picks';
                applyFilters();
            });
        }

        function checkedValues(list) {
            return Array.from(list).filter(f => f.checked && !f.disabled).map(f => f.value);
        }

        // ── Filter + sort + paginate ─────────────────────────────────────────
        function applyFilters() {
            const minPrice = priceMinSlider ? parseFloat(priceMinSlider.value) : -Infinity;
            const maxPrice = priceMaxSlider ? parseFloat(priceMaxSlider.value) : Infinity;
            const nameQuery = hotelNameSearch ? hotelNameSearch.value.trim().toLowerCase() : '';
            const stars = checkedValues(starFilters).map(Number);
            const features = checkedValues(featureFilters);
            const types = checkedValues(typeFilters);
            const amenities = checkedValues(amenityFilters);

            let active = 0;
            if (nameQuery) active++;
            if (priceMinSlider && (priceMinSlider.value != priceMinSlider.min || priceMaxSlider.value != priceMaxSlider.max)) active++;
            active += stars.length + features.length + types.length + amenities.length;
            if (activeFilterCountEl) activeFilterCountEl.textContent = active;

            const priceOk = c => +c.dataset.price >= minPrice && +c.dataset.price <= maxPrice;
            const nameOk = c => nameQuery === '' || (c.dataset.hotelName || '').includes(nameQuery);
            const otherOk = c =>
                (!features.includes('breakfast') || c.dataset.breakfast === '1') &&
                (types.length === 0 || types.includes(c.dataset.type)) &&
                amenities.every(a => (' ' + c.dataset.amenities + ' ').includes(' ' + a + ' '));

            matching = allCards.filter(c => priceOk(c) && nameOk(c) && otherOk(c) && (stars.length === 0 || stars.includes(+c.dataset.stars)));

            // live star counts, ignoring the star filter itself
            const counts = {1: 0, 2: 0, 3: 0, 4: 0, 5: 0};
            allCards.forEach(c => { if (priceOk(c) && nameOk(c) && otherOk(c) && counts[c.dataset.stars] !== undefined) counts[c.dataset.stars]++; });
            document.querySelectorAll('[data-star-count]').forEach(el => { el.textContent = counts[el.dataset.starCount]; });

            const dir = sortSelect ? sortSelect.value : 'top_picks';
            matching.sort(function (a, b) {
                if (dir === 'price_asc') return a.dataset.price - b.dataset.price;
                if (dir === 'price_desc') return b.dataset.price - a.dataset.price;
                if (dir === 'rating') return b.dataset.stars - a.dataset.stars || a.dataset.index - b.dataset.index;
                return a.dataset.index - b.dataset.index;
            });
            matching.forEach(c => resultsContainer.appendChild(c));

            let empty = resultsContainer.querySelector('.hlist-no-results.js-filtered');
            if (matching.length === 0 && allCards.length > 0) {
                if (!empty) {
                    empty = document.createElement('div');
                    empty.className = 'hlist-no-results js-filtered';
                    empty.textContent = @json(t('hotellist.no_hotels_found'));
                    resultsContainer.appendChild(empty);
                }
            } else if (empty) {
                empty.remove();
            }

            page = 1;
            renderPage();
        }

        function renderPage() {
            const total = matching.length;
            const pages = Math.max(1, Math.ceil(total / PAGE_SIZE));
            page = Math.min(Math.max(1, page), pages);
            const from = (page - 1) * PAGE_SIZE;
            allCards.forEach(c => { c.hidden = true; });
            matching.slice(from, from + PAGE_SIZE).forEach(c => { c.hidden = false; });

            if (visibleCount) visibleCount.textContent = total;
            const pf = document.getElementById('pageFrom'), pt = document.getElementById('pageTo');
            if (pf) pf.textContent = total ? from + 1 : 0;
            if (pt) pt.textContent = Math.min(from + PAGE_SIZE, total);
            if (!pagesEl) return;

            pagesEl.innerHTML = '';
            const btn = (label, target, opts) => {
                const b = document.createElement('button');
                b.type = 'button';
                b.className = 'hlist-page-btn' + (opts && opts.active ? ' is-active' : '');
                b.innerHTML = label;
                if (opts && opts.aria) b.setAttribute('aria-label', opts.aria);
                if (opts && opts.active) b.setAttribute('aria-current', 'page');
                b.disabled = !!(opts && opts.disabled);
                b.addEventListener('click', () => { page = target; renderPage(); resultsContainer.scrollIntoView({ behavior: 'smooth', block: 'start' }); });
                pagesEl.appendChild(b);
            };
            const gap = () => { const s = document.createElement('span'); s.className = 'hlist-page-gap'; s.textContent = '…'; pagesEl.appendChild(s); };

            btn('<i class="fas fa-chevron-left"></i>', page - 1, { disabled: page === 1, aria: 'Previous page' });
            const show = new Set([1, pages, page - 1, page, page + 1].filter(n => n >= 1 && n <= pages));
            let last = 0;
            Array.from(show).sort((a, b) => a - b).forEach(n => {
                if (n - last > 1) gap();
                btn(String(n), n, { active: n === page });
                last = n;
            });
            btn('<i class="fas fa-chevron-right"></i>', page + 1, { disabled: page === pages, aria: 'Next page' });
        }

        applyFilters();
    })();
</script>

@endsection
