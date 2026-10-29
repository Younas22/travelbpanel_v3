@extends('common.layout')
@section('content')
    @php
        $active_currency = activeCurrency();

        // Hero title with its last two words in brand blue ("Find Your | Perfect Flight"),
        // the same treatment as the homepage hero.
        $heroTitleWords = preg_split('/\s+/u', trim(t('flight.hero_title')));
        $heroAccentCount = count($heroTitleWords) >= 3 ? 2 : (count($heroTitleWords) === 2 ? 1 : 0);
        $heroTitleLead = implode(' ', array_slice($heroTitleWords, 0, count($heroTitleWords) - $heroAccentCount));
        $heroTitleAccent = implode(' ', array_slice($heroTitleWords, count($heroTitleWords) - $heroAccentCount));

        // Section titles: last word gets the brand accent colour
        $thSplitTitle = function ($text) {
            $words = preg_split('/\s+/u', trim($text));
            $accent = count($words) > 1 ? array_pop($words) : '';
            return [implode(' ', $words), $accent];
        };

        // Featured routes (same routes, airlines and prices as before); each card
        // opens a one-way economy search for 1 adult, a week from today.
        $dealDate = date('d-m-Y', strtotime('+7 days'));
        $flightDeals = \App\Support\FeaturedFlightDeals::all();
    @endphp
    <style>
        /* Hero layout and the search panel live in style.css (#siteRedesign);
           only this page's cover image is set here. */
        #siteRedesign .hero-section {
            background-image: url('<?=url('public/assets/images/flight/flight-sunset-islands.png')?>');
        }
    </style>

<div id="siteRedesign">
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-content">
            <h1 class="hero-title">
                {{ $heroTitleLead }}@if($heroTitleAccent !== '') <span class="hero-title-accent">{{ $heroTitleAccent }}</span>@endif
            </h1>
            <p class="hero-subtitle">{{ t('flight.hero_subtitle') }}</p>
        </div>

        <!-- Search Form -->
        <div class="form-container">
            <div class="tab-content active" id="form-flight">
                @include('forms.flight-form')
            </div>
        </div>
    </section>

    <!-- Featured Flights Section -->
    @php [$thLead, $thAccent] = $thSplitTitle(t('flight.featured_flight_deals')); @endphp
    <section class="th-section" id="featuredFlights">
        <div class="th-container">
            <div class="th-header">
                <div class="th-header-text">
                    <span class="th-eyebrow">{{ t('flight.featured_flight_deals') }}</span>
                    <h2 class="th-title">{{ $thLead }} @if($thAccent)<span class="th-title-accent">{{ $thAccent }}</span>@endif</h2>
                </div>
                <div class="th-nav">
                    <button type="button" class="th-nav-btn th-nav-prev" data-th-dir="-1" aria-label="Previous"><i class="fas fa-arrow-left"></i></button>
                    <button type="button" class="th-nav-btn th-nav-next" data-th-dir="1" aria-label="Next"><i class="fas fa-arrow-right"></i></button>
                </div>
            </div>

            <div class="th-track">
                @foreach($flightDeals as [$fromCode, $fromCity, $toCode, $toCity, $airline, $usdPrice, $image])
                    @php $dealUrl = url('flights/' . strtolower($fromCode) . '/' . strtolower($toCode) . "/oneway/economy/{$dealDate}/1/0/0"); @endphp
                    <article class="th-card is-compact">
                        <a href="{{ $dealUrl }}" class="th-card-media">
                            <img src="{{ $image }}" alt="{{ $fromCity }} - {{ $toCity }}" loading="lazy">
                            <span class="th-chip"><i class="fas fa-plane"></i> {{ $fromCode }} - {{ $toCode }}</span>
                        </a>
                        <div class="th-card-body">
                            <div class="th-route">
                                <span class="th-route-city">{{ $fromCity }}</span>
                                <span class="th-route-icon"><i class="fas fa-plane"></i></span>
                                <span class="th-route-city">{{ $toCity }}</span>
                            </div>
                            <p class="th-location"><i class="fas fa-plane-departure"></i> <span>{{ $airline }}</span></p>

                            <div class="th-footer">
                                <div class="th-price-wrap">
                                    <span class="th-price-label">{{ t('home.starting_from') }}</span>
                                    <span class="th-price">{{ $active_currency->currency_name }} {{ convertCurrency($usdPrice, "USD", $active_currency->currency_name) }}</span>
                                </div>
                                <a href="{{ $dealUrl }}" class="th-view-btn">{{ t('home.view') }} <i class="fas fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Why Choose Us Section -->
    @php [$thLead, $thAccent] = $thSplitTitle(t('flight.why_book_with_us')); @endphp
    <section class="th-section" id="whyBookFlights">
        <div class="th-container">
            <div class="th-header">
                <div class="th-header-text">
                    <span class="th-eyebrow">{{ t('flight.why_book_with_us') }}</span>
                    <h2 class="th-title">{{ $thLead }} @if($thAccent)<span class="th-title-accent">{{ $thAccent }}</span>@endif</h2>
                </div>
            </div>

            <div class="hw-grid">
                @foreach([
                    ['fa-magnifying-glass', 'flight.easy_search', 'flight.easy_search_desc'],
                    ['fa-tags', 'flight.best_prices', 'flight.best_prices_desc'],
                    ['fa-lock', 'flight.secure_booking', 'flight.secure_booking_desc'],
                    ['fa-headset', 'flight.support_24_7', 'flight.support_24_7_desc'],
                ] as $hwIndex => [$hwIcon, $hwTitle, $hwDesc])
                <div class="hw-card">
                    <div class="hw-card-top">
                        <span class="hw-icon"><i class="fas {{ $hwIcon }}"></i></span>
                        <span class="hw-number">{{ str_pad($hwIndex + 1, 2, '0', STR_PAD_LEFT) }}</span>
                    </div>
                    <h3 class="hw-title">{{ t($hwTitle) }}</h3>
                    <p class="hw-desc">{{ t($hwDesc) }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @include('common.mobile-app-showcase')
</div>

@endsection
