@extends('common.layout')
@section('page_class', 'view-umrah')
@section('content')
    @php
        // Hero title with its last two words in brand blue, the same treatment
        // as the homepage hero.
        $heroTitleWords = preg_split('/\s+/u', trim(t('umrah.heroTitle')));
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

<div id="siteRedesign">
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-content">
            <h1 class="hero-title">
                {{ $heroTitleLead }}@if($heroTitleAccent !== '') <span class="hero-title-accent">{{ $heroTitleAccent }}</span>@endif
            </h1>
            <p class="hero-subtitle">{{ t('umrah.heroSubtitle') }}</p>
        </div>

        <!-- Search Form -->
        <div class="form-container">
            <div class="tab-content active" id="form-umrah">
                @include('forms.umrah-form')
            </div>
        </div>
    </section>

    <!-- Popular Umrah Packages Section -->
    @php [$thLead, $thAccent] = $thSplitTitle(t('umrah.featuredPackagesTitle')); @endphp
    <section class="th-section" id="featuredUmrahPackages">
        <div class="th-container">
            <div class="th-header">
                <div class="th-header-text">
                    <span class="th-eyebrow">{{ t('umrah.featuredPackagesTitle') }}</span>
                    <h2 class="th-title">{{ $thLead }} @if($thAccent)<span class="th-title-accent">{{ $thAccent }}</span>@endif</h2>
                </div>
                @if($umrahPackages->count() > 1)
                <div class="th-nav">
                    <button type="button" class="th-nav-btn th-nav-prev" data-th-dir="-1" aria-label="Previous"><i class="fas fa-arrow-left"></i></button>
                    <button type="button" class="th-nav-btn th-nav-next" data-th-dir="1" aria-label="Next"><i class="fas fa-arrow-right"></i></button>
                </div>
                @endif
            </div>

            <div class="th-track">
                @php
                    // Default date values - 2 weeks from today
                    $departureDate = date('d-m-Y', strtotime('+14 days'));
                    $returnDate = date('d-m-Y', strtotime('+21 days'));
                    $adult = 2;
                    $child = 0;
                    $infant = 0;
                    $makkahNights = 4;
                    $madinaNights = 3;
                @endphp

                @foreach($umrahPackages as $package)
                    @php
                        // Package slug from name
                        $slug = Str::slug($package->name);

                        // Origin and destination codes
                        $originCode = 'JED';
                        $destinationCode = 'JED';

                        if ($package->leaving_from) {
                            $fromAirport = DB::table('flights_airports')->where('id', $package->leaving_from)->first();
                            $originCode = $fromAirport ? $fromAirport->code : 'JED';
                        }

                        if ($package->going_to) {
                            $toAirport = DB::table('flights_airports')->where('id', $package->going_to)->first();
                            $destinationCode = $toAirport ? $toAirport->code : 'JED';
                        }

                        // Build detail URL
                        $detailUrl = url("umrah/{$slug}/{$originCode}/{$destinationCode}/{$departureDate}/{$returnDate}/{$adult}/{$child}/{$infant}/{$makkahNights}/{$madinaNights}");
                    @endphp

                    @include('common.umrah-card', [
                        'packagePrice' => ($package->currency ?? 'USD') . ' ' . number_format($package->price, 0),
                        'umrahLabels' => ['featured' => 'umrah.featured', 'nights' => 'umrah.nights', 'makkah' => 'umrah.nightsMakkah', 'madinah' => 'umrah.nightsMadinah', 'hotel' => 'umrah.hotel', 'from' => 'umrah.startingFrom'],
                    ])
                @endforeach
            </div>
        </div>
    </section>

    <!-- What's Included Section -->
    @php [$thLead, $thAccent] = $thSplitTitle(t('umrah.whatsIncludedTitle')); @endphp
    <section class="th-section" id="umrahIncluded">
        <div class="th-container">
            <div class="th-header">
                <div class="th-header-text">
                    <span class="th-eyebrow">{{ t('umrah.whatsIncludedTitle') }}</span>
                    <h2 class="th-title">{{ $thLead }} @if($thAccent)<span class="th-title-accent">{{ $thAccent }}</span>@endif</h2>
                </div>
            </div>

            <div class="hw-grid is-3">
                @foreach([
                    ['fa-plane', 'umrah.roundTripFlights', 'umrah.roundTripFlightsDesc'],
                    ['fa-hotel', 'umrah.hotelAccommodation', 'umrah.hotelAccommodationDesc'],
                    ['fa-passport', 'umrah.visaProcessing', 'umrah.visaProcessingDesc'],
                    ['fa-bus', 'umrah.transportation', 'umrah.transportationDesc'],
                    ['fa-user-tie', 'umrah.expertGuidesIncluded', 'umrah.expertGuidesIncludedDesc'],
                    ['fa-utensils', 'umrah.meals', 'umrah.mealsDesc'],
                ] as [$hwIcon, $hwTitle, $hwDesc])
                <div class="hw-card is-inline">
                    <span class="hw-icon"><i class="fas {{ $hwIcon }}"></i></span>
                    <div>
                        <h3 class="hw-title">{{ t($hwTitle) }}</h3>
                        <p class="hw-desc">{{ t($hwDesc) }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Why Choose Us Section -->
    @php [$thLead, $thAccent] = $thSplitTitle(t('umrah.whyBookTitle')); @endphp
    <section class="th-section" id="whyBookUmrah">
        <div class="th-container">
            <div class="th-header">
                <div class="th-header-text">
                    <span class="th-eyebrow">{{ t('umrah.whyBookTitle') }}</span>
                    <h2 class="th-title">{{ $thLead }} @if($thAccent)<span class="th-title-accent">{{ $thAccent }}</span>@endif</h2>
                </div>
            </div>

            <div class="hw-grid">
                @foreach([
                    ['fa-kaaba', 'umrah.sacredAccommodations', 'umrah.sacredAccommodationsDesc'],
                    ['fa-user-tie', 'umrah.expertGuides', 'umrah.expertGuidesDesc'],
                    ['fa-plane-departure', 'umrah.hassleFreeTravel', 'umrah.hassleFreeTravelDesc'],
                    ['fa-headset', 'umrah.support247', 'umrah.support247Desc'],
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

    <script>
        // Smooth scroll to form on page load if hash is present
        document.addEventListener('DOMContentLoaded', function() {
            if (window.location.hash === '#form-umrah') {
                setTimeout(function() {
                    const formElement = document.getElementById('form-umrah');
                    if (formElement) {
                        formElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }, 300);
            }
        });
    </script>

@endsection
