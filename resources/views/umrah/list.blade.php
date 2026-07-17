@extends('common.layout')
@section('content')
@php
    $active_currency = activeCurrency();
@endphp
    <!-- Search Collapse -->
    <div class="umrah-list-container">
        <div class="hotel-search-collapse">
            <button class="hotel-search-collapse-btn" id="searchCollapseBtn">
                <span><i class="fas fa-search"></i> {{t('umrahlist.modify_search')}}</span>
                <i class="fas fa-chevron-down" id="collapseIcon"></i>
            </button>
            <div class="hotel-search-collapse-content" id="searchCollapseContent">
                <style>
                .form-container {
                    max-width: 1200px !important;
                }
                </style>
                @include('forms.umrah-form')
            </div>
        </div>
    </div>

    <!-- Page Header -->
    <div class="bg-white border-b border-gray-200">
        <div class="umrah-page-header-inner">
            <div class="flex flex-wrap items-center gap-4 text-sm">
                <div class="flex items-center gap-2">
                    <i class="fas fa-plane-departure text-[#0077BE]"></i>
                    <div>
                        <p class="text-xs font-bold text-gray-600">{{ t('umrahlist.from') }}</p>
                        <p class="font-bold text-gray-900">{{ strtoupper($originAirport->city ?? $searchParams['origin']) }}</p>
                    </div>
                </div>
                <i class="fas fa-arrow-right text-gray-400"></i>
                <div class="flex items-center gap-2">
                    <i class="fas fa-plane-arrival text-[#0077BE]"></i>
                    <div>
                        <p class="text-xs font-bold text-gray-600">{{ t('umrahlist.to') }}</p>
                        <p class="font-bold text-gray-900">{{ strtoupper($destinationAirport->city ?? $searchParams['destination']) }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <i class="fas fa-calendar text-[#0077BE]"></i>
                    <div>
                        <p class="text-xs font-bold text-gray-600">{{ t('umrahlist.departure') }}</p>
                        <p class="font-bold text-gray-900">{{ $searchParams['departure_date'] }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <i class="fas fa-calendar text-[#0077BE]"></i>
                    <div>
                        <p class="text-xs font-bold text-gray-600">{{ t('umrahlist.return') }}</p>
                        <p class="font-bold text-gray-900">{{ $searchParams['return_date'] }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <i class="fas fa-users text-[#0077BE]"></i>
                    <div>
                        <p class="text-xs font-bold text-gray-600">{{ t('umrahlist.passengers') }}</p>
                        <p class="font-bold text-gray-900">{{ $searchParams['adult'] + $searchParams['child'] + $searchParams['infant'] }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <i class="fas fa-moon text-[#0077BE]"></i>
                    <div>
                        <p class="text-xs font-bold text-gray-600">{{ t('umrahlist.nights') }}</p>
                        <p class="font-bold text-gray-900">{{ $searchParams['makkah_nights'] }} {{ t('umrahlist.makkah') }}, {{ $searchParams['madina_nights'] }} {{ t('umrahlist.madina') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Container -->
    <div class="umrah-list-container">
        <section class="umrah-main-container">
        <div class="umrah-content-wrapper">
            <!-- Filter Sidebar -->
            <aside class="umrah-filter-sidebar" id="filterSidebar">
                    <!-- Price Range Filter -->
                    <div class="umrah-filter-section">
                        <h3 class="umrah-filter-title">{{ t('umrahlist.price_range') }}</h3>
                        <div class="price-range-wrapper" style="padding: 5px 0;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 13px; color: #555;">
                                <span id="priceMinLabel">{{ $packages->min('price') ? number_format($packages->min('price')) : 0 }}</span>
                                <span id="priceMaxLabel">{{ $packages->max('price') ? number_format($packages->max('price')) : 0 }}</span>
                            </div>
                            <div style="position: relative; height: 30px;">
                                <div id="priceRangeTrack" style="position: absolute; top: 30%; transform: translateY(-50%); height: 5px; width: 100%; border-radius: 3px; background: #ddd; z-index: 0;">
                                    <div id="priceRangeFill" style="position: absolute; height: 100%; background: #0077BE; border-radius: 3px;"></div>
                                </div>
                                <input type="range" id="priceMinRange" min="{{ $packages->min('price') ?? 0 }}" max="{{ $packages->max('price') ?? 10000 }}" value="{{ $packages->min('price') ?? 0 }}" style="position: absolute; width: 100%; pointer-events: none; -webkit-appearance: none; appearance: none; background: transparent; z-index: 2;">
                                <input type="range" id="priceMaxRange" min="{{ $packages->min('price') ?? 0 }}" max="{{ $packages->max('price') ?? 10000 }}" value="{{ $packages->max('price') ?? 10000 }}" style="position: absolute; width: 100%; pointer-events: none; -webkit-appearance: none; appearance: none; background: transparent; z-index: 2;">
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
                    <div class="umrah-filter-section">
                        <h3 class="umrah-filter-title">{{ t('umrahlist.duration') ?? 'Duration' }}</h3>
                        <select id="durationFilter" class="umrah-filter-input">
                            <option value="">{{ t('umrahlist.any_duration') ?? 'Any Duration' }}</option>
                            @php
                                $allNights = $packages->map(fn($p) => ($p->night_in_mekkah ?? 0) + ($p->night_in_madina ?? 0))->filter()->values();
                                $has1to3 = $allNights->filter(fn($d) => $d >= 1 && $d <= 3)->count() > 0;
                                $has4to7 = $allNights->filter(fn($d) => $d >= 4 && $d <= 7)->count() > 0;
                                $has8to14 = $allNights->filter(fn($d) => $d >= 8 && $d <= 14)->count() > 0;
                                $has15plus = $allNights->filter(fn($d) => $d >= 15)->count() > 0;
                            @endphp
                            @if($has1to3)<option value="1-3">{{ t('umrahlist.nights_1_3') ?? '1 - 3 Nights' }}</option>@endif
                            @if($has4to7)<option value="4-7">{{ t('umrahlist.nights_4_7') ?? '4 - 7 Nights' }}</option>@endif
                            @if($has8to14)<option value="8-14">{{ t('umrahlist.nights_8_14') ?? '8 - 14 Nights' }}</option>@endif
                            @if($has15plus)<option value="15+">{{ t('umrahlist.nights_15_plus') ?? '15+ Nights' }}</option>@endif
                        </select>
                    </div>

                    <!-- Clear Filters -->
                    <button type="button" class="umrah-clear-filters-btn" id="clearFiltersBtn" style="display: block; width: 100%; text-align: center; cursor: pointer;">
                        {{ t('umrahlist.clear_all') }}
                    </button>
            </aside>

            <!-- Results Section -->
            <main class="umrah-results-section">
                <!-- Mobile Filter Toggle -->
                <button class="umrah-mobile-filter-toggle" onclick="document.getElementById('filterSidebar').classList.toggle('active')">
                    <i class="fas fa-filter"></i> {{ t('umrahlist.show_filters') }}
                </button>

                <!-- Results Header -->
                <div class="umrah-results-header">
                    <div class="umrah-results-count">
                        {{ t('umrahlist.showing') }} <strong>{{ $packages->total() }} {{ t('umrahlist.packages') }}</strong>
                    </div>
                    <div class="umrah-sort-section">
                        <label class="umrah-sort-label">{{ t('umrahlist.sort_by') }}:</label>
                        <select class="umrah-sort-select" id="sortSelect">
                            <option value="popular">{{ t('umrahlist.most_popular') }}</option>
                            <option value="price_low">{{ t('umrahlist.price_low_high') }}</option>
                            <option value="price_high">{{ t('umrahlist.price_high_low') }}</option>
                            <option value="rating">{{ t('umrahlist.highest_rated') }}</option>
                        </select>
                    </div>
                </div>

                <!-- Umrah List Grid -->
                <div class="umrah-list-grid">
                    @forelse($packages as $package)
                        <div class="umrah-list-card {{ $package->featured == 1 ? 'featured-card' : '' }}" data-price="{{ $package->price }}" data-nights="{{ ($package->night_in_mekkah ?? 0) + ($package->night_in_madina ?? 0) }}">
                            <div class="umrah-list-image">
                                @if($package->images->count() > 0)
                                    <img src="{{ asset('public/assets/images/' . $package->images->first()->image) }}" alt="{{ $package->name }}">
                                @else
                                    <img src="{{ asset('assets/images/placeholder/default.jpg') }}" alt="{{ $package->name }}">
                                @endif
                                @if($package->featured == 1)
                                    <div class="umrah-badge featured-badge">{{ t('umrahlist.featured') }}</div>
                                @endif
                                <div class="umrah-duration-badge">
                                    <i class="fas fa-clock"></i> {{ $package->duration }}
                                </div>
                            </div>
                            <div class="umrah-list-content">
                                <div class="umrah-header">
                                    <h3 class="umrah-title">{{ $package->name }}</h3>
                                    <div class="umrah-location">
                                        <i class="fas fa-map-marker-alt"></i> {{ $package->from_location }} - {{ $package->to_location }}
                                    </div>
                                </div>

                                <!-- Rating -->
                                @if($package->rating)
                                    <div class="umrah-rating">
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= $package->stars)
                                                <i class="fas fa-star"></i>
                                            @else
                                                <i class="far fa-star"></i>
                                            @endif
                                        @endfor
                                        <span>({{ $package->rating }})</span>
                                    </div>
                                @endif

                                <!-- Meta Info -->
                                <div class="umrah-meta">
                                    <span class="umrah-meta-item">
                                        <i class="fas fa-kaaba"></i> {{ $package->night_in_mekkah }} {{ t('umrahlist.nights_makkah') }}
                                    </span>
                                    <span class="umrah-meta-item">
                                        <i class="fas fa-mosque"></i> {{ $package->night_in_madina }} {{ t('umrahlist.nights_madina') }}
                                    </span>
                                </div>

                                <!-- Inclusions -->
                                @if(count($package->inclusion_names) > 0)
                                    <div class="umrah-highlights">
                                        @foreach(array_slice($package->inclusion_names, 0, 3) as $inclusion)
                                            <span class="umrah-highlight-item">
                                                <i class="fas fa-check"></i> {{ $inclusion }}
                                            </span>
                                        @endforeach
                                        @if(count($package->inclusion_names) > 3)
                                            <span class="umrah-highlight-item">
                                                +{{ count($package->inclusion_names) - 3 }} {{ t('umrahlist.more') }}
                                            </span>
                                        @endif
                                    </div>
                                @endif

                                <div class="umrah-footer">
                                    <div class="umrah-price-section">
                                        <span class="umrah-price-label">{{ t('umrahlist.starting_from') }}</span>
                                        <div class="umrah-price">
                                            <span class="umrah-price-currency">{{$active_currency->currency_name}}</span> {{convertCurrency($package->price, $package->currency ?? 'USD', $active_currency->currency_name)}}
                                        </div>
                                        <span class="umrah-per-person">{{ t('umrahlist.per_person') }}</span>
                                    </div>
                                    <a href="{{ route('umrah.details', [
                                        'slug' => \Str::slug($package->name),
                                        'origin' => strtolower($searchParams['origin']),
                                        'destination' => strtolower($searchParams['destination']),
                                        'departure_date' => $searchParams['departure_date'],
                                        'return_date' => $searchParams['return_date'],
                                        'adult' => $searchParams['adult'],
                                        'child' => $searchParams['child'],
                                        'infant' => $searchParams['infant'],
                                        'makkah_nights' => $searchParams['makkah_nights'],
                                        'madina_nights' => $searchParams['madina_nights']
                                    ]) }}" class="umrah-view-details-btn">{{ t('umrahlist.view_details') }}</a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="umrah-no-results">
                            <i class="fas fa-search"></i>
                            <h3>{{ t('umrahlist.no_packages_found') }}</h3>
                            <p>{{ t('umrahlist.try_different_search') }}</p>
                        </div>
                    @endforelse
                </div>

                <!-- Pagination -->
                @if($packages->hasPages())
                    <div class="umrah-pagination-wrapper">
                        {{ $packages->links() }}
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
            const cards = document.querySelectorAll('.umrah-list-card');
            let visibleCount = 0;

            cards.forEach(function(card) {
                const price = parseFloat(card.getAttribute('data-price'));
                const days = parseInt(card.getAttribute('data-nights'));
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

            const countEl = document.querySelector('.umrah-results-count strong');
            if (countEl) {
                countEl.textContent = visibleCount + ' {{ t("umrahlist.packages") }}';
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
            document.querySelectorAll('.umrah-list-card').forEach(function(card) {
                card.style.display = '';
            });
            const countEl = document.querySelector('.umrah-results-count strong');
            if (countEl) {
                countEl.textContent = '{{ $packages->total() }} {{ t("umrahlist.packages") }}';
            }
        });

        // Initialize fill on load
        updateRangeFill();
    </script>

@endsection
