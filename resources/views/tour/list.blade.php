@extends('common.layout')
@section('content')
    @php
        $active_currency = activeCurrency();
    @endphp
    <!-- Search Collapse -->
    <div class="tour-list-container">
        <div class="hotel-search-collapse">
            <button class="hotel-search-collapse-btn" id="searchCollapseBtn">
                <span><i class="fas fa-search"></i> {{t('tourlist.modify_search')}}</span>
                <i class="fas fa-chevron-down" id="collapseIcon"></i>
            </button>
            <div class="hotel-search-collapse-content" id="searchCollapseContent">
                <style>
                .form-container {
                    max-width: 1200px !important;
                }
                </style>
                @include('forms.tours-form')
            </div>
        </div>
    </div>

    <!-- Page Header -->
    <div class="bg-white border-b border-gray-200">
        <div class="tour-page-header-inner">
            <div class="flex flex-wrap items-center gap-4 text-sm">
                <div class="flex items-center gap-2">
                    <i class="fas fa-map-marker-alt text-[#0077BE]"></i>
                    <div>
                        <p class="text-xs font-bold text-gray-600">{{ t('tourlist.destination') }}</p>
                        <p class="font-bold text-gray-900">{{ ucfirst($searchParams['location'] ?? 'All Destinations') }}</p>
                    </div>
                </div>
                @if(isset($searchParams) && isset($searchParams['start_date']))
                <i class="fas fa-arrow-right text-gray-400"></i>
                <div class="flex items-center gap-2">
                    <i class="fas fa-calendar text-[#0077BE]"></i>
                    <div>
                        <p class="text-xs font-bold text-gray-600">{{ t('tourlist.dates') }}</p>
                        <p class="font-bold text-gray-900">{{ $searchParams['original_start_date'] }} - {{ $searchParams['original_end_date'] }}</p>
                    </div>
                </div>
                @endif
                @if(isset($searchParams) && isset($searchParams['adult']))
                <div class="flex items-center gap-2">
                    <i class="fas fa-users text-[#0077BE]"></i>
                    <div>
                        <p class="text-xs font-bold text-gray-600">{{ t('tourlist.travelers') }}</p>
                        <p class="font-bold text-gray-900">{{ $searchParams['adult'] }} {{ t('tourlist.adults') }}, {{ $searchParams['child'] }} {{ t('tourlist.children') }}</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Main Container -->
    <div class="tour-list-container">
        <section class="tour-main-container">
        <div class="tour-content-wrapper">
            <!-- Filter Sidebar -->
            <aside class="tour-filter-sidebar" id="filterSidebar">

                    <!-- Price Range Filter -->
                    <div class="tour-filter-section">
                        <h3 class="tour-filter-title">{{ t('tourlist.price_range') }}</h3>
                        <div class="price-range-wrapper" style="padding: 5px 0;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 13px; color: #555;">
                                <span id="priceMinLabel">{{ $tours->min('price') ? number_format($tours->min('price')) : 0 }}</span>
                                <span id="priceMaxLabel">{{ $tours->max('price') ? number_format($tours->max('price')) : 0 }}</span>
                            </div>
                            <div style="position: relative; height: 30px;">
                                <div id="priceRangeTrack" style="position: absolute; top: 30%; transform: translateY(-50%); height: 5px; width: 100%; border-radius: 3px; background: #ddd; z-index: 0;">
                                    <div id="priceRangeFill" style="position: absolute; height: 100%; background: #0077BE; border-radius: 3px;"></div>
                                </div>
                                <input type="range" id="priceMinRange" min="{{ $tours->min('price') ?? 0 }}" max="{{ $tours->max('price') ?? 10000 }}" value="{{ $tours->min('price') ?? 0 }}" style="position: absolute; width: 100%; pointer-events: none; -webkit-appearance: none; appearance: none; background: transparent; z-index: 2;">
                                <input type="range" id="priceMaxRange" min="{{ $tours->min('price') ?? 0 }}" max="{{ $tours->max('price') ?? 10000 }}" value="{{ $tours->max('price') ?? 10000 }}" style="position: absolute; width: 100%; pointer-events: none; -webkit-appearance: none; appearance: none; background: transparent; z-index: 2;">
                            </div>
                        </div>
                        <style>
                            #priceMinRange::-webkit-slider-runnable-track, #priceMaxRange::-webkit-slider-runnable-track {
                                -webkit-appearance: none; appearance: none; height: 0; background: transparent;
                            }
                            #priceMinRange::-webkit-slider-thumb, #priceMaxRange::-webkit-slider-thumb {
                                pointer-events: all; -webkit-appearance: none; appearance: none;
                                width: 18px; height: 18px; border-radius: 50%; background: #0077BE;
                                cursor: pointer; border: 2px solid #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.3);
                                margin-top: -2px;
                            }
                            #priceMinRange::-moz-range-track, #priceMaxRange::-moz-range-track {
                                height: 0; background: transparent; border: none;
                            }
                            #priceMinRange::-moz-range-thumb, #priceMaxRange::-moz-range-thumb {
                                pointer-events: all; width: 18px; height: 18px; border-radius: 50%;
                                background: #0077BE; cursor: pointer; border: 2px solid #fff;
                                box-shadow: 0 1px 3px rgba(0,0,0,0.3);
                            }
                        </style>
                    </div>

                    <!-- Duration Filter -->
                    <div class="tour-filter-section">
                        <h3 class="tour-filter-title">{{ t('tourlist.duration') }}</h3>
                        <select id="durationFilter" class="tour-filter-input">
                            <option value="">{{ t('tourlist.any_duration') }}</option>
                            @php
                                $allDays = $tours->pluck('days')->filter()->values();
                                $has1to3 = $allDays->filter(fn($d) => $d >= 1 && $d <= 3)->count() > 0;
                                $has4to7 = $allDays->filter(fn($d) => $d >= 4 && $d <= 7)->count() > 0;
                                $has8to14 = $allDays->filter(fn($d) => $d >= 8 && $d <= 14)->count() > 0;
                                $has15plus = $allDays->filter(fn($d) => $d >= 15)->count() > 0;
                            @endphp
                            @if($has1to3)<option value="1-3">{{ t('tourlist.1_3_days') }}</option>@endif
                            @if($has4to7)<option value="4-7">{{ t('tourlist.4_7_days') }}</option>@endif
                            @if($has8to14)<option value="8-14">{{ t('tourlist.8_14_days') }}</option>@endif
                            @if($has15plus)<option value="15+">{{ t('tourlist.15_plus_days') }}</option>@endif
                        </select>
                    </div>

                    <!-- Action Buttons -->
                    <button type="button" class="tour-clear-filters-btn" id="clearFiltersBtn" style="display: block; width: 100%; text-align: center; cursor: pointer; border: none; background: none;">
                        {{ t('tourlist.clear_all') }}
                    </button>
            </aside>

            <!-- Results Section -->
            <main class="tour-results-section">
                <!-- Mobile Filter Toggle -->
                <button class="tour-mobile-filter-toggle" id="mobileFilterToggle">
                    <i class="fas fa-filter"></i> {{ t('tourlist.show_filters') }}
                </button>

                <!-- Results Header -->
                <div class="tour-results-header">
                    <div class="tour-results-count">
                        {{ t('tourlist.showing') }} <strong>{{ $tours->total() ?? 0 }} {{ t('tourlist.tours') }}</strong>
                    </div>
                    <div class="tour-sort-section">
                        <label class="tour-sort-label">{{ t('tourlist.sort_by') }}:</label>
                        <select class="tour-sort-select" id="sortSelect" onchange="window.location.href = addSortToUrl(this.value)">
                            <option value="popular" {{ ($sortBy ?? 'popular') == 'popular' ? 'selected' : '' }}>{{ t('tourlist.most_popular') }}</option>
                            <option value="price_low" {{ ($sortBy ?? '') == 'price_low' ? 'selected' : '' }}>{{ t('tourlist.price_low_high') }}</option>
                            <option value="price_high" {{ ($sortBy ?? '') == 'price_high' ? 'selected' : '' }}>{{ t('tourlist.price_high_low') }}</option>
                            <option value="duration" {{ ($sortBy ?? '') == 'duration' ? 'selected' : '' }}>{{ t('tourlist.duration') }}</option>
                            <option value="rating" {{ ($sortBy ?? '') == 'rating' ? 'selected' : '' }}>{{ t('tourlist.highest_rated') }}</option>
                        </select>
                    </div>
                </div>

                <!-- Tour List Grid -->
                <div class="tour-list-grid">
                    @forelse($tours ?? [] as $tour)
                        <div class="tour-list-card {{ $tour->featured == 1 ? 'featured-card' : '' }}" data-price="{{ $tour->price }}" data-days="{{ $tour->days }}">
                            <div class="tour-list-image">
                                @if($tour->images->count() > 0)
                                    <img src="{{ asset('public/assets/images/' . $tour->images->first()->image) }}" alt="{{ $tour->name }}">
                                @else
                                    <img src="{{ asset('assets/images/placeholder/default.jpg') }}" alt="{{ $tour->name }}">
                                @endif
                                @if($tour->featured == 1)
                                    <div class="tour-badge featured-badge">{{ t('tourlist.featured') }}</div>
                                @endif
                                <div class="tour-duration-badge">
                                    <i class="fas fa-clock"></i> {{ $tour->duration }}
                                </div>
                            </div>
                            <div class="tour-list-content">
                                <div class="tour-header">
                                    <h3 class="tour-title">{{ $tour->name }}</h3>
                                    <div class="tour-location">
                                        <i class="fas fa-map-marker-alt"></i> {{ $tour->location }}
                                    </div>
                                </div>

                                <!-- Rating -->
                                @if($tour->rating)
                                    <div class="tour-rating">
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= $tour->stars)
                                                <i class="fas fa-star"></i>
                                            @else
                                                <i class="far fa-star"></i>
                                            @endif
                                        @endfor
                                        <span>({{ $tour->rating }})</span>
                                    </div>
                                @endif

                                <!-- Description -->
                                @if($tour->description)
                                    <p class="tour-description">
                                        {{ Str::limit($tour->description, 120) }}
                                    </p>
                                @endif

                                <!-- Highlights -->
                                @if(isset($tour->highlights) && count($tour->highlights) > 0)
                                    <div class="tour-highlights">
                                        @foreach(array_slice($tour->highlights, 0, 4) as $highlight)
                                            <span class="tour-highlight-item">
                                                <i class="fas fa-check"></i> {{ $highlight }}
                                            </span>
                                        @endforeach
                                        @if(count($tour->highlights) > 4)
                                            <span class="tour-highlight-item">
                                                +{{ count($tour->highlights) - 4 }} {{ t('tourlist.more') }}
                                            </span>
                                        @endif
                                    </div>
                                @endif

                                <div class="tour-footer">
                                    <div class="tour-price-section">
                                        <span class="tour-price-label">{{ t('tourlist.starting_from') }}</span>
                                        <div class="tour-price">
                                            <span class="tour-price-currency">{{$active_currency->currency_name}}</span> {{ number_format(convertCurrency($tour->price,  $tour->currency ?? 'USD', $active_currency->currency_name)) }}
                                        </div>
                                        <span class="tour-per-person">{{ t('tourlist.per_person') }}</span>
                                    </div>
                                    <a href="{{ route('tours.details', [
                                        'slug' => \Str::slug($tour->name),
                                        'location' => $searchParams['location'] ?? 'all',
                                        'type' => $searchParams['type'] ?? 'all',
                                        'startDate' => $searchParams['original_start_date'] ?? date('d-m-Y'),
                                        'endDate' => $searchParams['original_end_date'] ?? date('d-m-Y', strtotime('+7 days')),
                                        'adult' => $searchParams['adult'] ?? 2,
                                        'child' => $searchParams['child'] ?? 0
                                    ]) }}" class="tour-view-details-btn">
                                        {{ t('tourlist.view_details') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="tour-no-results">
                            <i class="fas fa-search"></i>
                            <h3>{{ t('tourlist.no_tours_found') }}</h3>
                            <p>{{ t('tourlist.try_different_search') }}</p>
                        </div>
                    @endforelse
                </div>

                <!-- Pagination -->
                @if(isset($tours) && $tours->hasPages())
                    <div class="tour-pagination-wrapper">
                        {{ $tours->links() }}
                    </div>
                @endif
            </main>
        </div>
        </section>
    </div>

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

        // Mobile filter toggle
        const mobileFilterToggle = document.getElementById('mobileFilterToggle');
        const filterSidebar = document.getElementById('filterSidebar');

        if (mobileFilterToggle) {
            mobileFilterToggle.addEventListener('click', function() {
                filterSidebar.classList.toggle('active');
            });
        }

        // Sort functionality
        function addSortToUrl(sortValue) {
            const url = new URL(window.location.href);
            url.searchParams.set('sort_by', sortValue);
            return url.toString();
        }

        // Frontend filtering
        const priceMinRange = document.getElementById('priceMinRange');
        const priceMaxRange = document.getElementById('priceMaxRange');
        const priceMinLabel = document.getElementById('priceMinLabel');
        const priceMaxLabel = document.getElementById('priceMaxLabel');
        const priceRangeFill = document.getElementById('priceRangeFill');
        const durationFilter = document.getElementById('durationFilter');
        const clearFiltersBtn = document.getElementById('clearFiltersBtn');

        function formatNumber(n) {
            return Number(n).toLocaleString();
        }

        function updateRangeFill() {
            const min = parseInt(priceMinRange.min);
            const max = parseInt(priceMinRange.max);
            const range = max - min || 1;
            const leftPercent = ((parseInt(priceMinRange.value) - min) / range) * 100;
            const rightPercent = ((parseInt(priceMaxRange.value) - min) / range) * 100;
            priceRangeFill.style.left = leftPercent + '%';
            priceRangeFill.style.width = (rightPercent - leftPercent) + '%';
        }

        function applyFrontendFilters() {
            const minPrice = parseInt(priceMinRange.value);
            const maxPrice = parseInt(priceMaxRange.value);
            const durationVal = durationFilter.value;
            const cards = document.querySelectorAll('.tour-list-card');
            let visibleCount = 0;

            cards.forEach(function(card) {
                const price = parseFloat(card.getAttribute('data-price'));
                const days = parseInt(card.getAttribute('data-days'));
                let show = true;

                if (price < minPrice || price > maxPrice) show = false;

                if (durationVal) {
                    if (durationVal === '1-3' && (days < 1 || days > 3)) show = false;
                    else if (durationVal === '4-7' && (days < 4 || days > 7)) show = false;
                    else if (durationVal === '8-14' && (days < 8 || days > 14)) show = false;
                    else if (durationVal === '15+' && days < 15) show = false;
                }

                card.style.display = show ? '' : 'none';
                if (show) visibleCount++;
            });

            const countEl = document.querySelector('.tour-results-count strong');
            if (countEl) {
                countEl.textContent = visibleCount + ' {{ t("tourlist.tours") }}';
            }
        }

        priceMinRange.addEventListener('input', function() {
            if (parseInt(priceMinRange.value) > parseInt(priceMaxRange.value)) {
                priceMinRange.value = priceMaxRange.value;
            }
            priceMinLabel.textContent = formatNumber(priceMinRange.value);
            updateRangeFill();
            applyFrontendFilters();
        });

        priceMaxRange.addEventListener('input', function() {
            if (parseInt(priceMaxRange.value) < parseInt(priceMinRange.value)) {
                priceMaxRange.value = priceMinRange.value;
            }
            priceMaxLabel.textContent = formatNumber(priceMaxRange.value);
            updateRangeFill();
            applyFrontendFilters();
        });

        durationFilter.addEventListener('change', applyFrontendFilters);

        clearFiltersBtn.addEventListener('click', function() {
            priceMinRange.value = priceMinRange.min;
            priceMaxRange.value = priceMaxRange.max;
            priceMinLabel.textContent = formatNumber(priceMinRange.min);
            priceMaxLabel.textContent = formatNumber(priceMaxRange.max);
            durationFilter.value = '';
            updateRangeFill();
            document.querySelectorAll('.tour-list-card').forEach(function(card) {
                card.style.display = '';
            });
            const countEl = document.querySelector('.tour-results-count strong');
            if (countEl) {
                countEl.textContent = '{{ $tours->total() ?? 0 }} {{ t("tourlist.tours") }}';
            }
        });

        // Initialize fill on load
        updateRangeFill();
    </script>

@endsection
