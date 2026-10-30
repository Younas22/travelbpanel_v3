@extends('common.layout')
@section('page_class', 'view-flights')
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

        // Featured routes, shown with the same cards as the homepage
        $flightDeals = \App\Support\FeaturedFlightDeals::all();
    @endphp

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
    @include('common.flight-deals', ['deals' => $flightDeals, 'sectionId' => 'featuredFlights'])

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
