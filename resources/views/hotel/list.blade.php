@extends('common.layout')
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

<style>
    /* Hotel listing — follows the site redesign (brand #2563EB, black
       headings/prices, gold stars, 16px cards). Wrapped in #siteRedesign so
       the search panel uses the shared hero/search styles in style.css. */
    .hlist-page { --hl-blue: #2563EB; --hl-blue-dark: #1D4ED8; --hl-border: rgba(0,0,0,.08); --hl-muted: rgba(0,0,0,.6); --hl-gold: #F5B301;
        background: #fff; padding: 24px 0 64px; color: #000; }
    .hlist-page .hotel-container { max-width: 1328px; margin: 0 auto; padding: 0 24px; }

    /* ---------- Search bar ---------- */
    #siteRedesign .hlist-page .form-container { width: 100%; max-width: none; margin: 0 0 24px; padding: 4px 20px 20px;
        border: 1px solid var(--hl-border); box-shadow: 0 8px 24px rgba(0,0,0,.06); }

    /* ---------- Results header ---------- */
    .hlist-head { display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 12px 24px; margin-bottom: 20px; }
    .hlist-title { margin: 0 0 4px; font-size: clamp(22px, 2.4vw, 28px); font-weight: 700; line-height: 1.2; }
    .hlist-subtitle { margin: 0; color: var(--hl-muted); font-size: 14px; }
    .hlist-head-actions { display: flex; align-items: center; gap: 10px; }
    .hlist-sort-wrap { display: flex; align-items: center; overflow: hidden; border: 1px solid rgba(0,0,0,.12); border-radius: 12px; background: #fff; }
    .hlist-sort-label { display: inline-flex; align-items: center; gap: 8px; padding: 0 12px; border-right: 1px solid var(--hl-border); color: #000; font-size: 13px; white-space: nowrap; }
    .hlist-sort-label i { color: var(--hl-blue); }
    .hlist-sort { height: 42px; padding: 0 12px; border: 0; background: #fff; color: #000; font-size: 13px; font-weight: 500; cursor: pointer; outline: none; }
    .hlist-filter-toggle { display: none; align-items: center; gap: 8px; height: 44px; padding: 0 16px; border: 1px solid rgba(0,0,0,.12); border-radius: 12px; background: #fff; color: #000; font-size: 13px; font-weight: 500; cursor: pointer; }
    .hlist-filter-toggle i { color: var(--hl-blue); }

    /* ---------- Layout ---------- */
    .hlist-main { display: grid; grid-template-columns: minmax(0, 27fr) minmax(0, 73fr); gap: 24px; align-items: start; }
    .hlist-sidebar { display: flex; flex-direction: column; padding: 16px; border: 1px solid var(--hl-border); border-radius: 16px; background: #fff; box-shadow: 0 4px 16px rgba(0,0,0,.04); }

    /* ---------- Sidebar ---------- */
    .hlist-map { position: relative; overflow: hidden; margin-bottom: 4px; border-radius: 12px; background: rgba(37,99,235,.06); }
    .hlist-map iframe { display: block; width: 100%; height: 150px; border: 0; pointer-events: none; }
    .hlist-map-btn { position: absolute; left: 50%; bottom: 12px; transform: translateX(-50%); display: inline-flex; align-items: center; gap: 8px;
        padding: 7px 16px; border: 1px solid var(--hl-blue); border-radius: 9999px; background: #fff; color: var(--hl-blue); font-size: 13px; font-weight: 500; text-decoration: none; white-space: nowrap; }
    .hlist-map-btn:hover { background: var(--hl-blue); color: #fff; }

    .hlist-sidebar-header { display: flex; align-items: center; justify-content: space-between; padding: 14px 0 4px; }
    .hlist-sidebar-title { display: flex; align-items: center; gap: 8px; font-size: 15px; font-weight: 600; }
    .hlist-sidebar-title i { color: var(--hl-blue); }
    .hlist-badge-count { display: inline-flex; align-items: center; justify-content: center; min-width: 20px; height: 20px; padding: 0 6px; border-radius: 10px; background: var(--hl-blue); color: #fff; font-size: 12px; font-weight: 600; }

    .hlist-filter-group { padding: 16px 0; border-top: 1px solid var(--hl-border); }
    .hlist-filter-group:first-of-type { border-top: 0; }
    .hlist-filter-title { margin-bottom: 12px; font-size: 14px; font-weight: 600; }

    .hlist-input-icon { position: relative; }
    .hlist-input-icon i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: rgba(0,0,0,.4); font-size: 13px; }
    .hlist-name-search { width: 100%; height: 42px; padding: 0 12px 0 36px; border: 1px solid rgba(0,0,0,.12); border-radius: 10px; font-size: 13px; outline: none; }
    .hlist-name-search:focus, .hlist-price-box:focus { border-color: var(--hl-blue); box-shadow: 0 0 0 3px rgba(37,99,235,.12); }

    .hlist-check-row { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 6px 0; color: #000; font-size: 13px; cursor: pointer; user-select: none; }
    .hlist-check-row.is-disabled { color: rgba(0,0,0,.35); cursor: default; }
    .hlist-check-row.is-extra { display: none; }
    .hlist-filter-group.is-expanded .hlist-check-row.is-extra { display: flex; }
    .hlist-check-left { display: flex; align-items: center; gap: 10px; min-width: 0; }
    .hlist-check-left i { width: 14px; color: var(--hl-blue); font-size: 12px; text-align: center; }
    .hlist-check-row input[type="checkbox"] { flex-shrink: 0; width: 17px; height: 17px; accent-color: var(--hl-blue); cursor: pointer; }
    .hlist-count { flex-shrink: 0; color: var(--hl-muted); font-size: 12px; }
    .hlist-stars-inline { color: var(--hl-gold); font-size: 12px; letter-spacing: 1px; }
    .hlist-show-more { display: inline-flex; align-items: center; gap: 6px; margin-top: 6px; padding: 0; border: 0; background: none; color: var(--hl-blue); font-size: 13px; font-weight: 500; cursor: pointer; }
    .hlist-show-more i { font-size: 10px; }

    /* Dual range price slider */
    .hlist-price-inputs { display: flex; align-items: center; gap: 8px; margin-bottom: 14px; }
    .hlist-price-box { flex: 1; min-width: 0; height: 40px; padding: 0 10px; border: 1px solid rgba(0,0,0,.12); border-radius: 10px; color: #000; font-size: 13px; outline: none; }
    .hlist-price-sep { color: var(--hl-muted); font-size: 12px; }
    .hlist-range-slider { position: relative; height: 30px; margin: 0 2px; }
    .hlist-range-track { position: absolute; top: 50%; left: 0; right: 0; height: 4px; border-radius: 2px; background: rgba(0,0,0,.1); transform: translateY(-50%); }
    .hlist-range-selected { position: absolute; top: 50%; height: 4px; border-radius: 2px; background: var(--hl-blue); transform: translateY(-50%); }
    .hlist-range-input { position: absolute; top: 0; left: 0; width: 100%; height: 30px; margin: 0; background: transparent; -webkit-appearance: none; appearance: none; pointer-events: none; }
    .hlist-range-input::-webkit-slider-thumb { -webkit-appearance: none; pointer-events: auto; width: 18px; height: 18px; margin-top: -7px; border: 3px solid var(--hl-blue); border-radius: 50%; background: #fff; cursor: pointer; box-shadow: 0 1px 3px rgba(0,0,0,.25); }
    .hlist-range-input::-moz-range-thumb { pointer-events: auto; width: 18px; height: 18px; border: 3px solid var(--hl-blue); border-radius: 50%; background: #fff; cursor: pointer; }
    .hlist-range-input::-webkit-slider-runnable-track { -webkit-appearance: none; height: 4px; background: transparent; }
    .hlist-range-input::-moz-range-track { height: 4px; background: transparent; }

    .hlist-clear-all { width: 100%; height: 44px; margin-top: 8px; border: 1px solid var(--hl-blue); border-radius: 12px; background: #fff; color: var(--hl-blue); font-size: 14px; font-weight: 600; cursor: pointer; transition: background-color .2s ease, color .2s ease; }
    .hlist-clear-all:hover { background: var(--hl-blue); color: #fff; }

    /* ---------- Hotel cards ---------- */
    .hlist-results-col { min-width: 0; }
    .hlist-results { display: flex; flex-direction: column; gap: 14px; }
    .hlist-card { display: grid; grid-template-columns: 240px minmax(0, 1fr) 200px; overflow: hidden; border: 1px solid var(--hl-border); border-radius: 16px; background: #fff; box-shadow: 0 4px 16px rgba(0,0,0,.04); transition: box-shadow .2s ease; }
    .hlist-card:hover { box-shadow: 0 12px 28px rgba(37,99,235,.12); }
    .hlist-card[hidden] { display: none; }

    .hlist-card-image { position: relative; display: block; height: 100%; min-height: 190px; margin: 10px 0 10px 10px; overflow: hidden; border-radius: 12px; background: rgba(37,99,235,.06); }
    .hlist-card-image img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
    .hlist-card-supplier { position: absolute; top: 10px; left: 10px; padding: 4px 9px; border-radius: 6px; background: rgba(0,0,0,.6); color: #fff; font-size: 10px; font-weight: 600; letter-spacing: .04em; text-transform: uppercase; }
    .hlist-card-photos { position: absolute; left: 10px; bottom: 10px; display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 9999px; background: rgba(0,0,0,.62); color: #fff; font-size: 12px; }

    .hlist-card-body { display: flex; flex-direction: column; gap: 8px; min-width: 0; padding: 16px 18px; }
    .hlist-card-title { margin: 0; font-size: 17px; font-weight: 600; line-height: 1.3; }
    .hlist-card-title a { color: #000; text-decoration: none; }
    .hlist-card-title a:hover { color: var(--hl-blue); }
    .hlist-card-stars { display: inline-flex; gap: 2px; color: rgba(0,0,0,.15); font-size: 13px; }
    .hlist-card-stars .is-on { color: var(--hl-gold); }
    .hlist-card-address { display: flex; align-items: flex-start; gap: 8px; color: #000; font-size: 13px; line-height: 1.45; }
    .hlist-card-address i { margin-top: 2px; color: #000; font-size: 12px; }
    .hlist-pills { display: flex; flex-wrap: wrap; gap: 6px; }
    .hlist-pill { display: inline-flex; align-items: center; gap: 6px; padding: 5px 10px; border-radius: 9999px; background: rgba(37,99,235,.06); color: #000; font-size: 12px; }
    .hlist-pill i { color: var(--hl-blue); font-size: 11px; }
    .hlist-room-line { margin-top: auto; padding-top: 10px; border-top: 1px solid var(--hl-border); color: var(--hl-muted); font-size: 12.5px; }
    .hlist-room-line b { color: #000; font-weight: 600; }

    .hlist-price-panel { display: flex; flex-direction: column; align-items: flex-end; justify-content: space-between; gap: 12px; padding: 16px 18px; border-left: 1px solid var(--hl-border); text-align: right; }
    .hlist-price-total { color: #000; font-size: 24px; font-weight: 700; line-height: 1.15; }
    .hlist-price-unit { color: var(--hl-muted); font-size: 12px; }
    .hlist-price-meta { margin-top: 6px; color: var(--hl-muted); font-size: 11.5px; line-height: 1.5; }
    .hlist-view-deal-btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; width: 100%; height: 42px; border-radius: 10px; background: var(--hl-blue); color: #fff; font-size: 14px; font-weight: 600; text-decoration: none; white-space: nowrap; transition: background-color .15s ease; }
    .hlist-view-deal-btn:hover { background: var(--hl-blue-dark); color: #fff; }

    .hlist-no-results { padding: 40px 20px; border: 1px solid var(--hl-border); border-radius: 16px; background: #fff; color: var(--hl-muted); font-size: 14px; text-align: center; }

    /* ---------- Pagination ---------- */
    .hlist-pagination { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; margin-top: 20px; }
    .hlist-pages { display: flex; flex-wrap: wrap; align-items: center; gap: 6px; }
    .hlist-page-btn { display: inline-flex; align-items: center; justify-content: center; min-width: 38px; height: 38px; padding: 0 10px; border: 1px solid rgba(0,0,0,.12); border-radius: 10px; background: #fff; color: #000; font-size: 13px; cursor: pointer; }
    .hlist-page-btn:hover:not(:disabled):not(.is-active) { border-color: var(--hl-blue); color: var(--hl-blue); }
    .hlist-page-btn.is-active { border-color: var(--hl-blue); background: var(--hl-blue); color: #fff; font-weight: 600; }
    .hlist-page-btn:disabled { opacity: .4; cursor: default; }
    .hlist-page-gap { padding: 0 4px; color: var(--hl-muted); }
    .hlist-page-summary { color: var(--hl-muted); font-size: 13px; }

    .hlist-page :focus-visible { outline: 2px solid var(--hl-blue); outline-offset: 2px; }

    /* ---------- Responsive ---------- */
    @media (max-width: 1279px) {
        .hlist-card { grid-template-columns: 200px minmax(0, 1fr) 180px; }
    }
    @media (max-width: 1023px) {
        .hlist-main { grid-template-columns: minmax(0, 1fr); }
        .hlist-filter-toggle { display: inline-flex; }
        .hlist-sidebar { display: none; }
        .hlist-sidebar.is-open { display: flex; }
    }
    @media (max-width: 767px) {
        .hlist-page .hotel-container { padding: 0 16px; }
        .hlist-card { grid-template-columns: minmax(0, 1fr); }
        .hlist-card-image { height: 200px; min-height: 0; margin: 10px 10px 0; }
        .hlist-price-panel { flex-direction: row; align-items: center; border-left: 0; border-top: 1px solid var(--hl-border); text-align: left; }
        .hlist-view-deal-btn { width: auto; padding: 0 18px; }
        .hlist-head-actions { width: 100%; }
        .hlist-sort-wrap { flex: 1; }
        .hlist-sort { flex: 1; min-width: 0; }
        .hlist-pagination { justify-content: center; }
    }

    [dir="rtl"] .hlist-sort-label { border-right: 0; border-left: 1px solid var(--hl-border); }
    [dir="rtl"] .hlist-input-icon i { left: auto; right: 12px; }
    [dir="rtl"] .hlist-name-search { padding: 0 36px 0 12px; }
    [dir="rtl"] .hlist-card-image { margin: 10px 10px 10px 0; }
    [dir="rtl"] .hlist-price-panel { border-left: 0; border-right: 1px solid var(--hl-border); align-items: flex-start; text-align: left; }
    [dir="rtl"] .hlist-card-supplier { left: auto; right: 10px; }
    [dir="rtl"] .hlist-view-deal-btn i, [dir="rtl"] .hlist-page-btn i { transform: scaleX(-1); }
    @media (max-width: 767px) {
        [dir="rtl"] .hlist-card-image { margin: 10px 10px 0; }
        [dir="rtl"] .hlist-price-panel { border-right: 0; text-align: right; }
    }
</style>

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
