@extends('common.layout')
@section('content')
    @php
        $active_currency = activeCurrency();

        // Hero title with its last two words in brand blue ("Find Your | Perfect Hotel"),
        // the same treatment as the homepage hero.
        $heroTitleWords = preg_split('/\s+/u', trim(t('hotel.heroTitle')));
        $heroAccentCount = count($heroTitleWords) >= 3 ? 2 : (count($heroTitleWords) === 2 ? 1 : 0);
        $heroTitleLead = implode(' ', array_slice($heroTitleWords, 0, count($heroTitleWords) - $heroAccentCount));
        $heroTitleAccent = implode(' ', array_slice($heroTitleWords, count($heroTitleWords) - $heroAccentCount));

        // Section titles: last word gets the brand accent colour
        $thSplitTitle = function ($text) {
            $words = preg_split('/\s+/u', trim($text));
            $accent = count($words) > 1 ? array_pop($words) : '';
            return [implode(' ', $words), $accent];
        };
    @endphp
    <style>
        /* Hero layout and the search panel live in style.css (#siteRedesign);
           only this page's cover image is set here. */
        #siteRedesign .hero-section {
            background-image: url('<?=url('public/assets/images/flight/hotel-twilight-caldera.png')?>');
        }
    </style>

<div id="siteRedesign">
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-content">
            <h1 class="hero-title">
                {{ $heroTitleLead }}@if($heroTitleAccent !== '') <span class="hero-title-accent">{{ $heroTitleAccent }}</span>@endif
            </h1>
            <p class="hero-subtitle">{{ t('hotel.heroSubtitle') }}</p>
        </div>

        <!-- Search Form -->
        <div class="form-container">
            <div class="tab-content active" id="form-hotel">
                @include('forms.hotel-form')
            </div>
        </div>
    </section>

    <!-- Featured Hotels Section -->
    @php [$thLead, $thAccent] = $thSplitTitle(t('hotel.featuredTitle')); @endphp
    <section class="th-section" id="featuredHotels">
        <div class="th-container">
            <div class="th-header">
                <div class="th-header-text">
                    <span class="th-eyebrow">{{ t('hotel.featuredTitle') }}</span>
                    <h2 class="th-title">{{ $thLead }} @if($thAccent)<span class="th-title-accent">{{ $thAccent }}</span>@endif</h2>
                </div>
                @if($featuredHotels->count() > 1)
                <div class="th-nav">
                    <button type="button" class="th-nav-btn th-nav-prev" data-th-dir="-1" aria-label="Previous"><i class="fas fa-arrow-left"></i></button>
                    <button type="button" class="th-nav-btn th-nav-next" data-th-dir="1" aria-label="Next"><i class="fas fa-arrow-right"></i></button>
                </div>
                @endif
            </div>

            <div class="th-track">
                @php
                    $checkin  = date('Y-m-d', strtotime('+1 day'));
                    $checkout = date('Y-m-d', strtotime('+2 days'));
                @endphp

                @forelse($featuredHotels as $fHotel)
                    @php
                        $fSlug = \Illuminate\Support\Str::slug(\Illuminate\Support\Str::limit($fHotel->name, 30), '-');
                        $fMinPrice = $fHotel->roomTypes()->where('status', 1)->min('price_per_night') ?? 0;
                    @endphp
                    @include('common.hotel-card', [
                        'fDetailUrl' => url("hotel/details/{$fHotel->id}/{$fSlug}/{$checkin}/{$checkout}/2/0/1/manual"),
                        'fPrice' => $fMinPrice > 0 ? $active_currency->currency_name . ' ' . number_format(convertCurrency($fMinPrice, "USD", $active_currency->currency_name), 0) : null,
                    ])
                @empty
                    <p class="th-empty">{{ t('hotel.noFeaturedHotels') }}</p>
                @endforelse
            </div>
        </div>
    </section>

    <!-- Why Choose Us Section -->
    @php [$thLead, $thAccent] = $thSplitTitle(t('hotel.whyBookTitle')); @endphp
    <section class="th-section" id="whyBookHotels">
        <div class="th-container">
            <div class="th-header">
                <div class="th-header-text">
                    <span class="th-eyebrow">{{ t('hotel.whyBookTitle') }}</span>
                    <h2 class="th-title">{{ $thLead }} @if($thAccent)<span class="th-title-accent">{{ $thAccent }}</span>@endif</h2>
                </div>
            </div>

            <div class="hw-grid">
                @foreach([
                    ['fa-magnifying-glass', 'hotel.easySearchTitle', 'hotel.easySearchDesc'],
                    ['fa-tags', 'hotel.bestPricesTitle', 'hotel.bestPricesDesc'],
                    ['fa-shield-halved', 'hotel.secureBookingTitle', 'hotel.secureBookingDesc'],
                    ['fa-headset', 'hotel.supportTitle', 'hotel.supportDesc'],
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
