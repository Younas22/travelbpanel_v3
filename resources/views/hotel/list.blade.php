@extends('common.layout')
@section('content')
    @php
        use Illuminate\Support\Str;
    @endphp

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



    <!-- Main Content -->
    <div class="hotel-main-content">
        <!-- Sidebar Filter -->
        <div class="hotel-sidebar">

            <!-- Hotel Name Search -->
            <div class="hotel-filter-section">
                <div class="hotel-filter-title">{{ t('hotellist.search_hotel') }}</div>
                <input type="text" id="hotelNameSearch" placeholder="{{ t('hotellist.type_hotel_name') }}"
                    style="width:100%; padding:8px 10px; border:1px solid #ddd; border-radius:6px; font-size:13px; outline:none; box-sizing:border-box;">
            </div>

            <!-- Price Range -->
            <div class="hotel-filter-section">
                <div class="hotel-filter-title">{{ t('hotellist.price_range') }}</div>
                @php
                    $minPrice = !empty($hotels) ? min(array_column($hotels, 'minRate')) : 0;
                    $maxPrice = !empty($hotels) ? max(array_column($hotels, 'minRate')) : 50000;
                    $currency = !empty($hotels) ? $hotels[0]['currency'] : 'PKR';
                @endphp
                <input type="range" id="priceSlider" class="hotel-price-slider"
                    min="{{ $minPrice }}"
                    max="{{ $maxPrice }}"
                    value="{{ $maxPrice }}"
                    step="100">
                <div class="hotel-price-display">
                    <span class="hotel-price-label-sm">{{ t('hotellist.max_price') }}</span>
                    <span class="hotel-price-value-sm" id="priceValue">{{ number_format($maxPrice) }} {{ $currency }}</span>
                </div>
            </div>

            <!-- Star Rating -->
            <div class="hotel-filter-section">
                <div class="hotel-filter-title">{{ t('hotellist.star_rating') }}</div>
                @php
                    $starCounts = [];
                    for ($s = 5; $s >= 1; $s--) {
                        $starCounts[$s] = !empty($hotels)
                            ? count(array_filter($hotels, fn($h) => (int)($h['stars'] ?? 0) === $s))
                            : 0;
                    }
                @endphp
                @for($star = 5; $star >= 1; $star--)
                    <div class="hotel-filter-option" style="display:flex; align-items:center; justify-content:space-between; margin-bottom:6px;">
                        <div style="display:flex; align-items:center; gap:6px;">
                            <input type="checkbox" class="star-filter" id="star{{ $star }}" value="{{ $star }}" {{ $starCounts[$star] > 0 ? 'checked' : 'disabled' }}>
                            <label for="star{{ $star }}" style="cursor:{{ $starCounts[$star] > 0 ? 'pointer' : 'default' }}; color:{{ $starCounts[$star] > 0 ? 'inherit' : '#aaa' }};">
                                {!! str_repeat('★', $star) !!} {{ $star }} {{ t('hotellist.star') }}
                            </label>
                        </div>
                        <span style="background:#f0f0f0; color:#555; font-size:12px; padding:2px 7px; border-radius:10px;">{{ $starCounts[$star] }}</span>
                    </div>
                @endfor
            </div>

            <!-- Amenities (dynamic from DB) -->
            @php
                // Collect all unique amenities from all hotels that have amenity data
                $allAmenities = [];
                if (!empty($hotels)) {
                    foreach ($hotels as $h) {
                        if (!empty($h['amenities']) && is_array($h['amenities'])) {
                            foreach ($h['amenities'] as $am) {
                                $amName = is_array($am) ? ($am['name'] ?? '') : $am;
                                $amIcon = is_array($am) ? ($am['icon'] ?? '') : '';
                                $amKey  = \Illuminate\Support\Str::slug($amName);
                                if ($amKey && !isset($allAmenities[$amKey])) {
                                    $allAmenities[$amKey] = ['label' => $amName, 'icon' => $amIcon];
                                }
                            }
                        }
                    }
                }
            @endphp
            @if(!empty($allAmenities))
            <div class="hotel-filter-section">
                <div class="hotel-filter-title">{{ t('hotellist.amenities') }}</div>
                @foreach($allAmenities as $key => $amenity)
                    <div class="hotel-filter-option" style="display:flex; align-items:center; gap:7px; margin-bottom:6px;">
                        <input type="checkbox" class="amenity-filter" id="amenity_{{ $key }}" value="{{ $key }}">
                        <label for="amenity_{{ $key }}" style="cursor:pointer; display:flex; align-items:center; gap:5px; font-size:13px;">
                            @if($amenity['icon'])
                                <i class="{{ $amenity['icon'] }}" style="width:16px; color:#555;"></i>
                            @else
                                <i class="fas fa-check-circle" style="width:16px; color:#555;"></i>
                            @endif
                            {{ $amenity['label'] }}
                        </label>
                    </div>
                @endforeach
            </div>
            @endif

            <!-- Results Count -->
            <div class="hotel-filter-section">
                <div class="hotel-filter-title">
                    {{ t('hotellist.results') }}
                    <button id="resetFilters" style="float: right; background: none; border: none; color: #007bff; cursor: pointer; font-size: 14px;">{{ t('hotellist.reset') }}</button>
                </div>
                <div style="padding: 10px 0; font-size: 14px;">
                    {{ t('hotellist.showing') }} <span id="visibleCount">{{ !empty($hotels) ? count($hotels) : 0 }}</span> {{ t('hotellist.of') }} <span id="totalCount">{{ !empty($hotels) ? count($hotels) : 0 }}</span> {{ t('hotellist.hotels') }}
                </div>
            </div>
        </div>


        <!-- Hotel Cards -->
        <div class="hotel-cards-container">

            @if(!empty($hotels) && count($hotels))
            @foreach($hotels as $hotel)
                @php
                    // Build amenity slugs from actual hotel amenity data
                    $hotelAmenities = [];
                    if (!empty($hotel['amenities']) && is_array($hotel['amenities'])) {
                        foreach ($hotel['amenities'] as $a) {
                            $name = is_array($a) ? ($a['name'] ?? '') : $a;
                            $slug = \Illuminate\Support\Str::slug($name);
                            if ($slug) $hotelAmenities[] = $slug;
                        }
                    }
                    $hotelAmenities = array_unique($hotelAmenities);
                @endphp
                <div class="hotel-card"
                     data-price="{{ $hotel['minRate'] }}"
                     data-stars="{{ $hotel['stars'] }}"
                     data-hotel-name="{{ strtolower($hotel['name']) }}"
                     data-amenities="{{ implode(',', $hotelAmenities) }}">
                    <div style="position: relative;">
                        <img src="{{$hotel['images']}}" alt="Pearl Continental" class="hotel-card-image">
                        <div class="hotel-card-badge">{{$hotel['supplier_name']}}</div>
                    </div>
                    <div class="hotel-card-body">
                        <div class="hotel-card-header">
                            <div>
                                <div class="hotel-card-title">{{$hotel['name']}}</div>
                                <div class="hotel-card-location">
                                    <i class="fas fa-map-pin hotel-card-location-icon"></i>
                                    <span>{{$hotel['address']}}</span>
                                </div>
                            </div>
                            <div class="hotel-card-rating">
                                <span class="hotel-card-stars"> {!! str_repeat('★', $hotel['stars']) !!}</span>
                                <span class="hotel-card-rating-number">{{$hotel['stars']}}</span>
                            </div>
                        </div>
                        @if(isset($hotel['room_name']) && $hotel['room_name'])
                        <div class="hotel-card-room-type">{{$hotel['room_name']}}</div>
                        @endif

                        <div class="hotel-card-amenities">
                            @if(!empty($hotel['amenities']) && is_array($hotel['amenities']))
                                @foreach(array_slice($hotel['amenities'], 0, 5) as $amenityItem)
                                    @php
                                        $amName = is_array($amenityItem) ? ($amenityItem['name'] ?? '') : $amenityItem;
                                        $amIcon = is_array($amenityItem) ? ($amenityItem['icon'] ?? '') : '';
                                    @endphp
                                    @if($amName)
                                    <div class="hotel-amenity">
                                        @if($amIcon)
                                            <i class="{{ $amIcon }} hotel-amenity-icon"></i>
                                        @else
                                            <i class="fas fa-check hotel-amenity-icon"></i>
                                        @endif
                                        <span>{{ $amName }}</span>
                                    </div>
                                    @endif
                                @endforeach
                            @endif
                        </div>

                        <div class="hotel-card-footer">
                            <div class="hotel-card-price">
                                <div class="hotel-card-price-label">{{ t('hotellist.per_night') }}</div>
                                <div>
                                    <div class="hotel-card-price-value">{{$hotel['minRate']}} {{$hotel['currency']}}</div>
                                    {{--<div class="hotel-card-price-original">10,000 PKR</div>--}}
                                </div>
                            </div>
                            @if(isset($hotel['redirect']) && $hotel['redirect'])
                                <a href="{{$hotel['redirect']}}" target="_blank">
                                    <button class="hotel-select-btn">{{ t('hotellist.select_continue') }}</button></a>
                            @else
                            <a href="{{url("hotel/details")}}/{{$hotel['hotel_id']}}/{{ Str::slug(Str::limit($hotel['name'], 30), '-') }}/{{(isset($hotel_search['checkin']) && $hotel_search['checkin'] ? $hotel_search['checkin'] : "")}}/{{(isset($hotel_search['checkout']) && $hotel_search['checkout'] ? $hotel_search['checkout'] : "")}}/{{isset($hotel_search['adults']) && $hotel_search['adults'] ? $hotel_search['adults'] : ""}}/{{isset($hotel_search['childs']) && $hotel_search['childs'] ? $hotel_search['childs'] : "0"}}/{{isset($hotel_search['rooms']) && $hotel_search['rooms'] ? $hotel_search['rooms'] : ""}}/{{$hotel['supplier_name']}}">
                            <button class="hotel-select-btn">{{ t('hotellist.select_continue') }}</button></a>
                            @endif
                        </div>

                    </div>
                </div>
            @endforeach
            @endif
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
    const priceSlider     = document.getElementById('priceSlider');
    const priceValue      = document.getElementById('priceValue');
    const hotelNameSearch = document.getElementById('hotelNameSearch');
    const starFilters     = document.querySelectorAll('.star-filter');
    const amenityFilters  = document.querySelectorAll('.amenity-filter');
    const hotelCards      = document.querySelectorAll('.hotel-card');
    const visibleCount    = document.getElementById('visibleCount');
    const resetFiltersBtn = document.getElementById('resetFilters');
    const currency        = '{{ !empty($hotels) ? $hotels[0]["currency"] : "PKR" }}';

    // ── Price Slider ─────────────────────────────────────────────────────────
    if (priceSlider) {
        priceSlider.addEventListener('input', function () {
            priceValue.textContent = `${parseInt(this.value).toLocaleString()} ${currency}`;
            filterHotels();
        });
    }

    // ── Hotel Name Search ────────────────────────────────────────────────────
    if (hotelNameSearch) {
        hotelNameSearch.addEventListener('input', filterHotels);
    }

    // ── Star Filters ─────────────────────────────────────────────────────────
    starFilters.forEach(f => f.addEventListener('change', filterHotels));

    // ── Amenity Filters ──────────────────────────────────────────────────────
    amenityFilters.forEach(f => f.addEventListener('change', filterHotels));

    // ── Reset ────────────────────────────────────────────────────────────────
    if (resetFiltersBtn) {
        resetFiltersBtn.addEventListener('click', function () {
            if (priceSlider) {
                priceSlider.value = priceSlider.max;
                priceValue.textContent = `${parseInt(priceSlider.max).toLocaleString()} ${currency}`;
            }
            if (hotelNameSearch) hotelNameSearch.value = '';
            starFilters.forEach(f => { if (!f.disabled) f.checked = true; });
            amenityFilters.forEach(f => f.checked = false);
            filterHotels();
        });
    }

    // ── Main Filter Function ─────────────────────────────────────────────────
    function filterHotels() {
        const maxPrice = priceSlider ? parseFloat(priceSlider.value) : Infinity;

        const nameQuery = hotelNameSearch
            ? hotelNameSearch.value.trim().toLowerCase()
            : '';

        const selectedStars = Array.from(starFilters)
            .filter(f => f.checked && !f.disabled)
            .map(f => parseInt(f.value));

        const selectedAmenities = Array.from(amenityFilters)
            .filter(f => f.checked)
            .map(f => f.value);

        let visibleHotels = 0;

        hotelCards.forEach(card => {
            const cardPrice    = parseFloat(card.dataset.price);
            const cardStars    = parseInt(card.dataset.stars);
            const cardName     = (card.dataset.hotelName || '').toLowerCase();
            const cardAmenities = (card.dataset.amenities || '').split(',').map(a => a.trim());

            const matchesPrice    = cardPrice <= maxPrice;
            const matchesName     = nameQuery === '' || cardName.includes(nameQuery);
            const matchesStars    = selectedStars.length === 0 || selectedStars.includes(cardStars);
            const matchesAmenities = selectedAmenities.length === 0
                || selectedAmenities.every(a => cardAmenities.includes(a));

            if (matchesPrice && matchesName && matchesStars && matchesAmenities) {
                card.style.display = '';
                visibleHotels++;
            } else {
                card.style.display = 'none';
            }
        });

        // Update visible count
        if (visibleCount) visibleCount.textContent = visibleHotels;

        // Update star counts dynamically based on visible (price+name+amenity match, ignore star)
        updateStarCounts(maxPrice, nameQuery, selectedAmenities);

        // No results message
        const container   = document.querySelector('.hotel-cards-container');
        let noResultsMsg  = container.querySelector('.no-results-message');
        if (visibleHotels === 0) {
            if (!noResultsMsg) {
                noResultsMsg = document.createElement('div');
                noResultsMsg.className = 'no-results-message alert alert-info';
                noResultsMsg.style.cssText = 'width:100%; padding:20px; margin:20px 0; text-align:center;';
                noResultsMsg.textContent = '{{ t('hotellist.no_hotels_found') }}';
                container.appendChild(noResultsMsg);
            }
        } else {
            if (noResultsMsg) noResultsMsg.remove();
        }
    }

    // ── Live Star Counts (updates badges as price/name/amenity filters change) ──
    function updateStarCounts(maxPrice, nameQuery, selectedAmenities) {
        const counts = {1:0, 2:0, 3:0, 4:0, 5:0};

        hotelCards.forEach(card => {
            const cardPrice     = parseFloat(card.dataset.price);
            const cardStars     = parseInt(card.dataset.stars);
            const cardName      = (card.dataset.hotelName || '').toLowerCase();
            const cardAmenities = (card.dataset.amenities || '').split(',').map(a => a.trim());

            const matchesPrice    = cardPrice <= maxPrice;
            const matchesName     = nameQuery === '' || cardName.includes(nameQuery);
            const matchesAmenities = selectedAmenities.length === 0
                || selectedAmenities.every(a => cardAmenities.includes(a));

            if (matchesPrice && matchesName && matchesAmenities && counts[cardStars] !== undefined) {
                counts[cardStars]++;
            }
        });

        for (let s = 1; s <= 5; s++) {
            const checkbox = document.getElementById(`star${s}`);
            const badge    = checkbox ? checkbox.closest('.hotel-filter-option').querySelector('span') : null;
            if (badge) badge.textContent = counts[s];
            if (checkbox) {
                checkbox.disabled = counts[s] === 0;
                if (counts[s] === 0) checkbox.checked = false;
                const label = checkbox.nextElementSibling;
                if (label) label.style.color = counts[s] === 0 ? '#aaa' : 'inherit';
            }
        }
    }

    // ── Init ─────────────────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        filterHotels();
    });
</script>


@endsection
