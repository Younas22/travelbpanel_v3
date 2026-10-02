@extends('common.layout')
@section('content')
    @php
        use Illuminate\Support\Str;
    @endphp

<style>
    :root {
        --hl-green: #0346FA;
        --hl-green-dark: #022ec2;
        --hl-green-light: #e8edff;
        --hl-ink: #1f2430;
        --hl-muted: #6b7280;
        --hl-border: #e5e7eb;
        --hl-bg: #f6f7f9;
    }

    .hlist-page { background: var(--hl-bg); padding: 20px 0 48px; }

    /* ---------- Modify Search collapse (kept, restyled) ---------- */
    .hotel-search-collapse-btn {
        width: 100%; display: flex; align-items: center; justify-content: space-between;
        background: #fff; border: 1px solid var(--hl-border); border-radius: 12px;
        padding: 14px 18px; font-weight: 600; font-size: 14px; color: var(--hl-ink);
        cursor: pointer; box-shadow: 0 1px 2px rgba(16,24,40,.04); margin-bottom: 14px;
        transition: box-shadow .2s ease;
    }
    .hotel-search-collapse-btn:hover { box-shadow: 0 2px 8px rgba(16,24,40,.08); }
    .hotel-search-collapse-btn i.fa-search { color: var(--hl-green); margin-right: 6px; }
    .hotel-search-collapse-content { max-height: 0; overflow: hidden; transition: max-height .3s ease; }
    .hotel-search-collapse-content.active { max-height: 900px; }

    /* ---------- Search info strip ---------- */
    .hotel-search-info { background: #fff; border: 1px solid var(--hl-border); border-radius: 12px; padding: 14px 20px; margin-bottom: 16px; box-shadow: 0 1px 2px rgba(16,24,40,.04); }
    .hotel-search-info-row { display: flex; flex-wrap: wrap; gap: 28px; }
    .hotel-info-item { display: flex; align-items: center; gap: 10px; }
    .hotel-info-icon { color: var(--hl-green); font-size: 16px; }
    .hotel-info-label { font-size: 11px; color: var(--hl-muted); text-transform: uppercase; letter-spacing: .03em; }
    .hotel-info-value { font-size: 14px; font-weight: 600; color: var(--hl-ink); }

    /* ---------- Top bar ---------- */
    .hlist-topbar {
        display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;
        background: #fff; border: 1px solid var(--hl-border); border-radius: 12px;
        padding: 16px 20px; margin-bottom: 16px; box-shadow: 0 1px 2px rgba(16,24,40,.04);
    }
    .hlist-topbar-left { font-size: 15px; color: var(--hl-ink); }
    .hlist-topbar-left strong { font-weight: 700; }
    .hlist-sort {
        border: 1px solid var(--hl-border); border-radius: 8px; padding: 9px 14px; font-size: 13px;
        font-weight: 600; color: var(--hl-ink); background: #fff; cursor: pointer; outline: none;
    }

    /* ---------- Layout ---------- */
    .hlist-main { display: flex; align-items: flex-start; gap: 20px; }
    .hlist-sidebar {
        width: 300px; flex-shrink: 0; background: #fff; border: 1px solid var(--hl-border);
        border-radius: 12px; padding: 18px; box-shadow: 0 1px 2px rgba(16,24,40,.04); position: sticky; top: 14px;
    }
    .hlist-results { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 14px; }

    /* ---------- Sidebar ---------- */
    .hlist-sidebar-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; }
    .hlist-sidebar-title { display: flex; align-items: center; gap: 8px; font-size: 16px; font-weight: 700; color: var(--hl-ink); }
    .hlist-sidebar-title i { color: var(--hl-muted); }
    .hlist-badge-count {
        background: var(--hl-green); color: #fff; font-size: 12px; font-weight: 700;
        min-width: 20px; height: 20px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; padding: 0 6px;
    }
    .hlist-clear-all { color: var(--hl-green); font-weight: 600; font-size: 13px; text-decoration: none; cursor: pointer; }
    .hlist-clear-all:hover { text-decoration: underline; }

    .hlist-filter-group { border-top: 1px solid var(--hl-border); padding: 16px 0; }
    .hlist-filter-group:first-of-type { border-top: none; padding-top: 0; }
    .hlist-filter-title { font-weight: 700; font-size: 14px; color: var(--hl-ink); margin-bottom: 12px; }

    .hlist-name-search {
        width: 100%; padding: 9px 12px; border: 1px solid var(--hl-border); border-radius: 8px;
        font-size: 13px; outline: none; box-sizing: border-box; margin-bottom: 4px;
    }
    .hlist-name-search:focus { border-color: var(--hl-green); }

    .hlist-check-row {
        display: flex; align-items: center; justify-content: space-between; gap: 10px;
        padding: 6px 0; cursor: pointer; font-size: 13px; color: var(--hl-ink); user-select: none;
    }
    .hlist-check-row.is-disabled { color: #b0b4bb; cursor: default; }
    .hlist-check-left { display: flex; align-items: center; gap: 9px; }
    .hlist-check-row input[type="checkbox"] {
        width: 17px; height: 17px; accent-color: var(--hl-green); cursor: pointer; flex-shrink: 0;
    }
    .hlist-check-row.is-disabled input[type="checkbox"] { cursor: default; }
    .hlist-count-pill {
        background: #f0f1f3; color: #555; font-size: 11px; font-weight: 600; padding: 2px 8px; border-radius: 10px; flex-shrink: 0;
    }
    .hlist-stars-inline { color: #f5a623; letter-spacing: 1px; font-size: 13px; }

    /* Dual range price slider */
    .hlist-price-inputs { display: flex; align-items: center; gap: 8px; margin-bottom: 16px; }
    .hlist-price-box {
        flex: 1; border: 1px solid var(--hl-border); border-radius: 8px; padding: 8px 10px;
        font-size: 13px; font-weight: 600; color: var(--hl-ink); text-align: center; background: #fafafa;
    }
    .hlist-price-sep { color: var(--hl-muted); font-size: 13px; }
    .hlist-range-slider { position: relative; height: 30px; margin: 0 2px 4px; }
    .hlist-range-track {
        position: absolute; top: 50%; left: 0; right: 0; height: 4px; background: #e2e4e9; border-radius: 2px; transform: translateY(-50%);
    }
    .hlist-range-selected {
        position: absolute; top: 50%; height: 4px; background: var(--hl-green); border-radius: 2px; transform: translateY(-50%);
    }
    .hlist-range-input {
        position: absolute; top: 0; left: 0; width: 100%; margin: 0; background: transparent;
        -webkit-appearance: none; appearance: none; pointer-events: none; height: 30px;
    }
    .hlist-range-input::-webkit-slider-thumb {
        -webkit-appearance: none; pointer-events: auto; width: 18px; height: 18px; border-radius: 50%;
        background: #fff; border: 3px solid var(--hl-green); cursor: pointer; box-shadow: 0 1px 3px rgba(0,0,0,.25); margin-top: -7px;
    }
    .hlist-range-input::-moz-range-thumb {
        pointer-events: auto; width: 18px; height: 18px; border-radius: 50%;
        background: #fff; border: 3px solid var(--hl-green); cursor: pointer; box-shadow: 0 1px 3px rgba(0,0,0,.25);
    }
    .hlist-range-input::-webkit-slider-runnable-track { -webkit-appearance: none; background: transparent; height: 4px; }
    .hlist-range-input::-moz-range-track { background: transparent; height: 4px; }

    .hlist-reset-note { font-size: 12px; color: var(--hl-muted); padding-top: 4px; }

    /* ---------- Hotel cards ---------- */
    .hlist-card {
        display: flex; background: #fff; border: 1px solid var(--hl-border); border-radius: 14px;
        overflow: hidden; box-shadow: 0 1px 2px rgba(16,24,40,.04); transition: box-shadow .2s ease, transform .2s ease;
    }
    .hlist-card:hover { box-shadow: 0 8px 24px rgba(16,24,40,.10); transform: translateY(-1px); }

    /* Fixed height (not stretched to match each card's body) so every photo
       in the list renders at the exact same size regardless of how much text
       a given hotel's address/room name takes up. */
    .hlist-card-image { width: 280px; height: 200px; flex-shrink: 0; align-self: flex-start; position: relative; background: #eef0f2; }
    .hlist-card-image img { width: 100%; height: 100%; object-fit: cover; object-position: center; display: block; }
    .hlist-card-supplier {
        position: absolute; top: 10px; left: 10px; background: rgba(0,0,0,.6); color: #fff;
        font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; padding: 4px 9px; border-radius: 6px;
    }

    .hlist-card-body { flex: 1; min-width: 0; padding: 18px 20px; display: flex; flex-direction: column; }
    .hlist-card-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 14px; }
    /* min-width: 0 is what lets this flex child actually shrink/truncate
       instead of overflowing into the star rating next to it. */
    .hlist-card-title-wrap { flex: 1; min-width: 0; }
    .hlist-card-title {
        font-size: 19px; font-weight: 700; color: var(--hl-ink); margin: 0 0 6px;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .hlist-card-address { display: flex; align-items: center; gap: 7px; font-size: 13px; color: var(--hl-muted); }
    .hlist-card-address i { color: var(--hl-green); font-size: 12px; }
    .hlist-card-stars { flex-shrink: 0; color: #f5a623; font-size: 15px; letter-spacing: 1px; white-space: nowrap; }

    .hlist-badges { display: flex; flex-wrap: wrap; gap: 8px; margin: 12px 0; }
    .hlist-badge-pill {
        display: inline-flex; align-items: center; gap: 6px; font-size: 12.5px; font-weight: 600;
        padding: 5px 11px; border-radius: 20px; background: var(--hl-green-light); color: var(--hl-green-dark);
    }
    .hlist-badge-pill i { font-size: 11px; }

    .hlist-card-divider { border-top: 1px solid var(--hl-border); margin-top: auto; padding-top: 12px; }
    .hlist-room-line { font-size: 13px; color: var(--hl-ink); }
    .hlist-room-line b { font-weight: 700; }
    .hlist-room-line .sep { color: var(--hl-border); margin: 0 6px; }

    .hlist-price-panel {
        width: 210px; flex-shrink: 0; border-left: 1px solid var(--hl-border); padding: 18px 18px;
        display: flex; flex-direction: column; align-items: flex-end; justify-content: space-between; text-align: right;
    }
    .hlist-price-meta { font-size: 12px; color: var(--hl-muted); margin-bottom: 4px; }
    .hlist-price-total { font-size: 24px; font-weight: 800; color: var(--hl-ink); line-height: 1.2; }
    .hlist-price-per-night { font-size: 12px; color: var(--hl-muted); margin-top: 2px; }
    .hlist-price-taxes { font-size: 11px; color: #9aa0a9; margin-top: 2px; margin-bottom: 14px; }
    .hlist-view-deal-btn {
        display: inline-flex; align-items: center; gap: 8px; background: var(--hl-green); color: #fff;
        font-weight: 700; font-size: 14px; padding: 11px 20px; border-radius: 9px; border: none; cursor: pointer;
        text-decoration: none; white-space: nowrap; transition: background .15s ease;
    }
    .hlist-view-deal-btn:hover { background: var(--hl-green-dark); color: #fff; }

    .hlist-no-results {
        background: #fff; border: 1px solid var(--hl-border); border-radius: 12px; padding: 40px 20px;
        text-align: center; color: var(--hl-muted); font-size: 14px;
    }

    @media (max-width: 900px) {
        .hlist-main { flex-direction: column; }
        .hlist-sidebar { width: 100%; position: static; }
        .hlist-card { flex-direction: column; }
        .hlist-card-image { width: 100%; height: 200px; }
        .hlist-price-panel { width: 100%; border-left: none; border-top: 1px solid var(--hl-border); align-items: stretch; text-align: left; }
        .hlist-view-deal-btn { justify-content: center; }
    }
</style>

<div class="hlist-page">
<div class="hotel-container">
    <!-- Search Collapse -->
    <div class="hotel-search-collapse">
        <button class="hotel-search-collapse-btn" id="searchCollapseBtn">
            <span><i class="fas fa-search"></i> {{ t('hotellist.modify_search') }}</span>
            <i class="fas fa-chevron-down" id="collapseIcon"></i>
        </button>
        <div class="hotel-search-collapse-content" id="searchCollapseContent">
            <style>
            .form-container {
                max-width: 1200px !important;
            }
            </style>
            @include('forms.hotel-form')
        </div>
    </div>

    <!-- Search Info -->
    <div class="hotel-search-info">
        <div class="hotel-search-info-row">
            <div class="hotel-info-item">
                <i class="fas fa-map-pin hotel-info-icon"></i>
                <div>
                    <div class="hotel-info-label">{{ t('hotellist.destination') }}</div>
                    <div class="hotel-info-value">{{ strtoupper(isset($hotel_search['city']) && $hotel_search['city'] ? $hotel_search['city'] : "")}}</div>
                </div>
            </div>
            <div class="hotel-info-item">
                <i class="fas fa-calendar hotel-info-icon"></i>
                <div>
                    <div class="hotel-info-label">{{ t('hotellist.check_in') }}</div>
                    <div class="hotel-info-value">{{ isset($hotel_search['checkin']) && $hotel_search['checkin'] ? \Carbon\Carbon::parse($hotel_search['checkin'])->format('M d, Y') : '' }}</div>
                </div>
            </div>
            <div class="hotel-info-item">
                <i class="fas fa-calendar hotel-info-icon"></i>
                <div>
                    <div class="hotel-info-label">{{ t('hotellist.check_out') }}</div>
                    <div class="hotel-info-value">{{ isset($hotel_search['checkout']) && $hotel_search['checkout'] ? \Carbon\Carbon::parse($hotel_search['checkout'])->format('M d, Y') : '' }}</div>
                </div>
            </div>
            <div class="hotel-info-item">
                <i class="fas fa-users hotel-info-icon"></i>
                <div>
                    <div class="hotel-info-label">{{ t('hotellist.guests') }}</div>
                    <div class="hotel-info-value">{{isset($hotel_search['adults']) && $hotel_search['adults'] ? $hotel_search['adults'] : ""}} {{ t('hotellist.adults') }}@if(isset($hotel_search['childs']) && $hotel_search['childs'] > 0), {{ $hotel_search['childs'] }} {{ t('hotellist.children') }}@endif, {{isset($hotel_search['rooms']) && $hotel_search['rooms'] ? $hotel_search['rooms'] : ""}} {{ t('hotellist.room') }}</div>
                </div>
            </div>
        </div>
    </div>

    @php
        $nights = 1;
        if (!empty($hotel_search['checkin']) && !empty($hotel_search['checkout'])) {
            $nights = max(1, (int) \Carbon\Carbon::parse($hotel_search['checkin'])->diffInDays(\Carbon\Carbon::parse($hotel_search['checkout'])));
        }
        $searchAdults = $hotel_search['adults'] ?? 2;
        $searchRooms  = $hotel_search['rooms'] ?? 1;
        $searchCity   = isset($hotel_search['city']) ? ucwords(str_replace('-', ' ', $hotel_search['city'])) : '';
    @endphp

    <!-- Top bar -->
    <div class="hlist-topbar">
        <div class="hlist-topbar-left">
            {{ t('hotellist.showing') }} <strong>{{ !empty($hotels) ? count($hotels) : 0 }}</strong>
            {{ t('hotellist.properties_in') }} <strong>{{ $searchCity }}</strong>
        </div>
        <div class="hlist-topbar-right">
            <select class="hlist-sort" id="hlistSort">
                <option value="top_picks">{{ t('hotellist.our_top_picks') }}</option>
                <option value="price_asc">{{ t('hotellist.price_low_high') }}</option>
                <option value="price_desc">{{ t('hotellist.price_high_low') }}</option>
                <option value="rating">{{ t('hotellist.star_rating') }}</option>
            </select>
        </div>
    </div>

    <!-- Main Content -->
    <div class="hlist-main">
        <!-- Sidebar Filter -->
        <aside class="hlist-sidebar">
            <div class="hlist-sidebar-header">
                <div class="hlist-sidebar-title">
                    <i class="fas fa-sliders-h"></i>
                    {{ t('hotellist.filter_results') }}
                    <span class="hlist-badge-count" id="activeFilterCount">0</span>
                </div>
                <a class="hlist-clear-all" id="resetFilters">{{ t('hotellist.clear_all') }}</a>
            </div>

            <!-- Hotel Name Search -->
            <div class="hlist-filter-group">
                <input type="text" id="hotelNameSearch" class="hlist-name-search" placeholder="{{ t('hotellist.type_hotel_name') }}">
            </div>

            <!-- Price Range -->
            <div class="hlist-filter-group">
                <div class="hlist-filter-title">{{ t('hotellist.price_per_night') }}</div>
                @php
                    $minPrice = !empty($hotels) ? (int) floor(min(array_column($hotels, 'minRate')) / max($nights,1)) : 0;
                    $maxPrice = !empty($hotels) ? (int) ceil(max(array_column($hotels, 'minRate')) / max($nights,1)) : 50000;
                    if ($maxPrice <= $minPrice) $maxPrice = $minPrice + 100;
                    $currency = !empty($hotels) ? $hotels[0]['currency'] : 'PKR';
                @endphp
                <div class="hlist-price-inputs">
                    <div class="hlist-price-box" id="priceMinBox">{{ $currency }} {{ number_format($minPrice) }}</div>
                    <span class="hlist-price-sep">—</span>
                    <div class="hlist-price-box" id="priceMaxBox">{{ $currency }} {{ number_format($maxPrice) }}</div>
                </div>
                <div class="hlist-range-slider">
                    <div class="hlist-range-track"></div>
                    <div class="hlist-range-selected" id="rangeSelected"></div>
                    <input type="range" id="priceMinSlider" class="hlist-range-input" min="{{ $minPrice }}" max="{{ $maxPrice }}" value="{{ $minPrice }}" step="{{ max(1, (int) (($maxPrice-$minPrice)/100)) }}">
                    <input type="range" id="priceMaxSlider" class="hlist-range-input" min="{{ $minPrice }}" max="{{ $maxPrice }}" value="{{ $maxPrice }}" step="{{ max(1, (int) (($maxPrice-$minPrice)/100)) }}">
                </div>
            </div>

            <!-- Property / Star Rating -->
            <div class="hlist-filter-group">
                <div class="hlist-filter-title">{{ t('hotellist.property') }}</div>
                @php
                    $starCounts = [];
                    for ($s = 5; $s >= 1; $s--) {
                        $starCounts[$s] = !empty($hotels)
                            ? count(array_filter($hotels, fn($h) => (int)($h['stars'] ?? 0) === $s))
                            : 0;
                    }
                @endphp
                @for($star = 5; $star >= 1; $star--)
                    <label class="hlist-check-row {{ $starCounts[$star] === 0 ? 'is-disabled' : '' }}">
                        <span class="hlist-check-left">
                            <input type="checkbox" class="star-filter" id="star{{ $star }}" value="{{ $star }}" {{ $starCounts[$star] > 0 ? 'checked' : 'disabled' }}>
                            <span class="hlist-stars-inline">{!! str_repeat('★', $star) . str_repeat('☆', 5-$star) !!}</span>
                        </span>
                        <span class="hlist-count-pill">{{ $starCounts[$star] }}</span>
                    </label>
                @endfor
            </div>

            <div class="hlist-reset-note">
                {{ t('hotellist.showing') }} <span id="visibleCount">{{ !empty($hotels) ? count($hotels) : 0 }}</span>
                {{ t('hotellist.of') }} <span id="totalCount">{{ !empty($hotels) ? count($hotels) : 0 }}</span> {{ t('hotellist.hotels') }}
            </div>
        </aside>

        <!-- Hotel Cards -->
        <div class="hlist-results" id="hlistResults">
            @if(!empty($hotels) && count($hotels))
            @foreach($hotels as $hotel)
                @php
                    $hotelAmenities = [];
                    $hasBreakfast = false;
                    if (!empty($hotel['amenities']) && is_array($hotel['amenities'])) {
                        foreach ($hotel['amenities'] as $a) {
                            $name = is_array($a) ? ($a['name'] ?? '') : $a;
                            $slug = Str::slug($name);
                            if ($slug) $hotelAmenities[] = $slug;
                            if (str_contains($slug, 'breakfast')) $hasBreakfast = true;
                        }
                    }
                    $hotelAmenities = array_unique($hotelAmenities);
                    $perNight = round(($hotel['minRate'] ?? 0) / max($nights, 1), 2);
                @endphp
                <div class="hlist-card"
                     data-price="{{ $perNight }}"
                     data-stars="{{ $hotel['stars'] }}"
                     data-hotel-name="{{ strtolower($hotel['name']) }}">
                    <div class="hlist-card-image">
                        <img src="{{$hotel['images']}}" alt="{{ $hotel['name'] }}" onerror="this.src='https://placehold.co/400x300?text=Hotel'">
                        <div class="hlist-card-supplier">{{$hotel['supplier_name']}}</div>
                    </div>
                    <div class="hlist-card-body">
                        <div class="hlist-card-top">
                            <div class="hlist-card-title-wrap">
                                <h3 class="hlist-card-title" title="{{$hotel['name']}}">{{$hotel['name']}}</h3>
                                <div class="hlist-card-address">
                                    <i class="fas fa-map-pin"></i>
                                    <span>{{$hotel['address']}}</span>
                                </div>
                            </div>
                            @if((int)$hotel['stars'] > 0)
                            <div class="hlist-card-stars">{!! str_repeat('★', (int)$hotel['stars']) . str_repeat('☆', max(0,5-(int)$hotel['stars'])) !!}</div>
                            @endif
                        </div>

                        <div class="hlist-badges">
                            @if($hasBreakfast)
                                <span class="hlist-badge-pill"><i class="fas fa-mug-hot"></i> {{ t('hotellist.breakfast_included') }}</span>
                            @endif
                            <span class="hlist-badge-pill"><i class="fas fa-check"></i> {{ t('hotellist.instant_confirmation') }}</span>
                        </div>

                        <div class="hlist-card-divider">
                            <div class="hlist-room-line">
                                @if(!empty($hotel['room_name']))
                                    <b>{{$hotel['room_name']}}</b><span class="sep">·</span>
                                @endif
                                {{ $searchRooms }} {{ $searchRooms == 1 ? t('hotellist.room') : t('hotellist.rooms') }}
                                <span class="sep">·</span>
                                {{ $searchAdults }} {{ t('hotellist.adults') }}
                            </div>
                        </div>
                    </div>
                    <div class="hlist-price-panel">
                        <div>
                            <div class="hlist-price-meta">{{ $searchRooms }} {{ t('hotellist.room') }} &middot; {{ $nights }} {{ $nights == 1 ? t('hotellist.night') : t('hotellist.nights') }}, {{ t('hotellist.total') }}</div>
                            <div class="hlist-price-total">{{ number_format($hotel['minRate'], 0) }} {{$hotel['currency']}}</div>
                            <div class="hlist-price-per-night">{{ number_format($perNight, 0) }} {{$hotel['currency']}} / {{ t('hotellist.per_night') }}</div>
                            <div class="hlist-price-taxes">{{ t('hotellist.includes_taxes') }}</div>
                        </div>
                        @if(isset($hotel['redirect']) && $hotel['redirect'])
                            <a href="{{$hotel['redirect']}}" target="_blank" class="hlist-view-deal-btn">{{ t('hotellist.view_deal') }} <i class="fas fa-arrow-right"></i></a>
                        @else
                            <a href="{{url("hotel/details")}}/{{$hotel['hotel_id']}}/{{ Str::slug(Str::limit($hotel['name'], 30), '-') }}/{{(isset($hotel_search['checkin']) && $hotel_search['checkin'] ? $hotel_search['checkin'] : "")}}/{{(isset($hotel_search['checkout']) && $hotel_search['checkout'] ? $hotel_search['checkout'] : "")}}/{{isset($hotel_search['adults']) && $hotel_search['adults'] ? $hotel_search['adults'] : ""}}/{{isset($hotel_search['childs']) && $hotel_search['childs'] ? $hotel_search['childs'] : "0"}}/{{isset($hotel_search['rooms']) && $hotel_search['rooms'] ? $hotel_search['rooms'] : ""}}/{{$hotel['supplier_name']}}" class="hlist-view-deal-btn">
                                {{ t('hotellist.view_deal') }} <i class="fas fa-arrow-right"></i>
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
            @else
                <div class="hlist-no-results">{{ t('hotellist.no_hotels_found') }}</div>
            @endif
        </div>
    </div>
</div>
</div>

<script>
    // ── Search Collapse ──────────────────────────────────────────────────────
    const searchCollapseBtn     = document.getElementById('searchCollapseBtn');
    const searchCollapseContent = document.getElementById('searchCollapseContent');
    const collapseIcon          = document.getElementById('collapseIcon');

    searchCollapseBtn.addEventListener('click', function () {
        searchCollapseBtn.classList.toggle('active');
        searchCollapseContent.classList.toggle('active');
        collapseIcon.style.transform = searchCollapseContent.classList.contains('active')
            ? 'rotate(180deg)' : 'rotate(0deg)';
    });

    // ── Filter Elements ──────────────────────────────────────────────────────
    const priceMinSlider  = document.getElementById('priceMinSlider');
    const priceMaxSlider  = document.getElementById('priceMaxSlider');
    const priceMinBox     = document.getElementById('priceMinBox');
    const priceMaxBox     = document.getElementById('priceMaxBox');
    const rangeSelected   = document.getElementById('rangeSelected');
    const hotelNameSearch = document.getElementById('hotelNameSearch');
    const starFilters     = document.querySelectorAll('.star-filter');
    const hotelCards      = document.querySelectorAll('.hlist-card');
    const visibleCount    = document.getElementById('visibleCount');
    const activeFilterCountEl = document.getElementById('activeFilterCount');
    const resetFiltersBtn = document.getElementById('resetFilters');
    const sortSelect      = document.getElementById('hlistSort');
    const resultsContainer = document.getElementById('hlistResults');
    const currency        = '{{ !empty($hotels) ? $hotels[0]["currency"] : "PKR" }}';

    // ── Dual price range slider ─────────────────────────────────────────────
    function updateRangeUI() {
        if (!priceMinSlider || !priceMaxSlider) return;
        const min = parseFloat(priceMinSlider.min);
        const max = parseFloat(priceMinSlider.max);
        let minVal = parseFloat(priceMinSlider.value);
        let maxVal = parseFloat(priceMaxSlider.value);

        if (minVal > maxVal) { minVal = maxVal; priceMinSlider.value = minVal; }

        const minPct = max > min ? ((minVal - min) / (max - min)) * 100 : 0;
        const maxPct = max > min ? ((maxVal - min) / (max - min)) * 100 : 100;

        rangeSelected.style.left = minPct + '%';
        rangeSelected.style.right = (100 - maxPct) + '%';

        priceMinBox.textContent = `${currency} ${Math.round(minVal).toLocaleString()}`;
        priceMaxBox.textContent = `${currency} ${Math.round(maxVal).toLocaleString()}`;
    }

    if (priceMinSlider && priceMaxSlider) {
        priceMinSlider.addEventListener('input', function () {
            if (parseFloat(priceMinSlider.value) > parseFloat(priceMaxSlider.value)) {
                priceMinSlider.value = priceMaxSlider.value;
            }
            updateRangeUI();
            filterHotels();
        });
        priceMaxSlider.addEventListener('input', function () {
            if (parseFloat(priceMaxSlider.value) < parseFloat(priceMinSlider.value)) {
                priceMaxSlider.value = priceMinSlider.value;
            }
            updateRangeUI();
            filterHotels();
        });
        updateRangeUI();
    }

    // ── Hotel Name Search ────────────────────────────────────────────────────
    if (hotelNameSearch) hotelNameSearch.addEventListener('input', filterHotels);

    // ── Checkbox filters ─────────────────────────────────────────────────────
    starFilters.forEach(f => f.addEventListener('change', filterHotels));

    // ── Sort ─────────────────────────────────────────────────────────────────
    if (sortSelect) {
        sortSelect.addEventListener('change', function () {
            const cards = Array.from(hotelCards);
            const dir = sortSelect.value;
            if (dir === 'price_asc') cards.sort((a,b) => parseFloat(a.dataset.price) - parseFloat(b.dataset.price));
            else if (dir === 'price_desc') cards.sort((a,b) => parseFloat(b.dataset.price) - parseFloat(a.dataset.price));
            else if (dir === 'rating') cards.sort((a,b) => parseFloat(b.dataset.stars) - parseFloat(a.dataset.stars));
            else return; // top picks = original order, leave as-is
            cards.forEach(c => resultsContainer.appendChild(c));
        });
    }

    // ── Reset ────────────────────────────────────────────────────────────────
    if (resetFiltersBtn) {
        resetFiltersBtn.addEventListener('click', function () {
            if (priceMinSlider && priceMaxSlider) {
                priceMinSlider.value = priceMinSlider.min;
                priceMaxSlider.value = priceMaxSlider.max;
                updateRangeUI();
            }
            if (hotelNameSearch) hotelNameSearch.value = '';
            starFilters.forEach(f => { if (!f.disabled) f.checked = true; });
            filterHotels();
        });
    }

    // ── Main Filter Function ─────────────────────────────────────────────────
    function filterHotels() {
        const minPrice = priceMinSlider ? parseFloat(priceMinSlider.value) : -Infinity;
        const maxPrice = priceMaxSlider ? parseFloat(priceMaxSlider.value) : Infinity;

        const nameQuery = hotelNameSearch ? hotelNameSearch.value.trim().toLowerCase() : '';

        const selectedStars = Array.from(starFilters).filter(f => f.checked && !f.disabled).map(f => parseInt(f.value));

        let activeFilters = 0;
        if (nameQuery) activeFilters++;
        if (priceMinSlider && priceMaxSlider && (priceMinSlider.value != priceMinSlider.min || priceMaxSlider.value != priceMaxSlider.max)) activeFilters++;
        if (selectedStars.length > 0 && selectedStars.length < Array.from(starFilters).filter(f => !f.disabled).length) activeFilters++;
        if (activeFilterCountEl) activeFilterCountEl.textContent = activeFilters;

        let visibleHotels = 0;

        hotelCards.forEach(card => {
            const cardPrice = parseFloat(card.dataset.price);
            const cardStars = parseInt(card.dataset.stars);
            const cardName  = (card.dataset.hotelName || '').toLowerCase();

            const matchesPrice = cardPrice >= minPrice && cardPrice <= maxPrice;
            const matchesName  = nameQuery === '' || cardName.includes(nameQuery);
            const matchesStars = selectedStars.length === 0 || selectedStars.includes(cardStars);

            if (matchesPrice && matchesName && matchesStars) {
                card.style.display = '';
                visibleHotels++;
            } else {
                card.style.display = 'none';
            }
        });

        if (visibleCount) visibleCount.textContent = visibleHotels;
        updateStarCounts(minPrice, maxPrice, nameQuery);

        let noResultsMsg = resultsContainer.querySelector('.hlist-no-results.js-filtered');
        if (visibleHotels === 0 && hotelCards.length > 0) {
            if (!noResultsMsg) {
                noResultsMsg = document.createElement('div');
                noResultsMsg.className = 'hlist-no-results js-filtered';
                noResultsMsg.textContent = '{{ t('hotellist.no_hotels_found') }}';
                resultsContainer.appendChild(noResultsMsg);
            }
        } else if (noResultsMsg) {
            noResultsMsg.remove();
        }
    }

    // ── Live Star Counts ─────────────────────────────────────────────────────
    function updateStarCounts(minPrice, maxPrice, nameQuery) {
        const counts = {1:0, 2:0, 3:0, 4:0, 5:0};

        hotelCards.forEach(card => {
            const cardPrice = parseFloat(card.dataset.price);
            const cardStars = parseInt(card.dataset.stars);
            const cardName  = (card.dataset.hotelName || '').toLowerCase();

            const matchesPrice = cardPrice >= minPrice && cardPrice <= maxPrice;
            const matchesName  = nameQuery === '' || cardName.includes(nameQuery);

            if (matchesPrice && matchesName && counts[cardStars] !== undefined) {
                counts[cardStars]++;
            }
        });

        for (let s = 1; s <= 5; s++) {
            const checkbox = document.getElementById(`star${s}`);
            const pill     = checkbox ? checkbox.closest('.hlist-check-row').querySelector('.hlist-count-pill') : null;
            if (pill) pill.textContent = counts[s];
        }
    }

    // ── Init ─────────────────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        filterHotels();
    });
</script>

@endsection
