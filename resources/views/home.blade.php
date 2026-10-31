@extends('common.layout')
@section('page_class', 'view-home')
@section('content')

@php
    $activeModules = getActiveModule(); // All active modules
    $active_currency = activeCurrency();

    // Logged-in agents only get a booking tab for modules admin has allowed
    // for them (same AgentPermission module.* keys as the top navbar) —
    // guests, customers and admins keep seeing every active module.
    if (auth()->check() && auth()->user()->isAgent()) {
        $homeModulePermissionMap = [
            'hotel'  => 'module.hotels',
            'flight' => 'module.flights',
            'tours'  => 'module.tours',
            'umrah'  => 'module.umrah',
            'visa'   => 'module.visa',
        ];
        $activeModules = $activeModules->filter(
            fn($mod) => auth()->user()->hasPermission($homeModulePermissionMap[$mod->slug] ?? 'module.' . $mod->slug)
        );
    }
@endphp


@php
    // Hero title from the existing translation, with its last two words in
    // brand blue ("Find Your | Perfect Flight"). Short titles highlight the
    // last word; languages written without spaces render in one color.
    $heroTitleWords = preg_split('/\s+/u', trim(t('home.hero_title')));
    $heroAccentCount = count($heroTitleWords) >= 3 ? 2 : (count($heroTitleWords) === 2 ? 1 : 0);
    $heroTitleLead = implode(' ', array_slice($heroTitleWords, 0, count($heroTitleWords) - $heroAccentCount));
    $heroTitleAccent = implode(' ', array_slice($heroTitleWords, count($heroTitleWords) - $heroAccentCount));
@endphp

<div id="siteRedesign" class="has-ai-search">
<section class="hero-section has-trust">
    <div class="hero-content">
        <h1 class="hero-title">
            {{ $heroTitleLead }}@if($heroTitleAccent !== '') <span class="hero-title-accent">{{ $heroTitleAccent }}</span>@endif
        </h1>
        <p class="hero-subtitle">
            {{ t('home.hero_subtitle') }}
        </p>
    </div>

    <!-- Tab Buttons -->
    <div class="tab-buttons-wrapper">
    <div class="tab-buttons">
        @php $first = true; @endphp
        @foreach($activeModules as $module)
            @if($module->slug === 'visa')
                <!-- Visa as a link, not a button -->
                <a href="{{ route('visa.create') }}" class="tab-btn">
                    <i class="fas fa-passport"></i>
                    {{ t('header.' . $module->name) }}
                </a>
            @else
                <button type="button" class="tab-btn {{ $first ? 'active' : '' }}" data-tab="{{ $module->slug }}">
                    <i class="fas
                        @if($module->slug === 'hotel') fa-hotel
                        @elseif($module->slug === 'flight') fa-plane
                        @elseif($module->slug === 'tours') fa-suitcase
                        @else fa-mosque
                        @endif
                    "></i>
                    {{ t('header.' . $module->name) }}
                </button>

                @php $first = false; @endphp
            @endif
        @endforeach
        <a href="{{ route('ticket.track') }}" class="tab-btn">
            <i class="fas fa-ticket"></i>
            {{ t('footer.Track Booking') }}
        </a>
    </div>
    </div>

    <!-- Search Form Container -->
    <div class="form-container">

        <!-- Tab Contents - Include separate files -->
        @php $firstContent = true; @endphp
        @foreach($activeModules as $module)
            @if($module->slug === 'visa') @continue @endif
            @php $isActive = $firstContent; $firstContent = false; @endphp
            <div class="tab-content {{ $isActive ? 'active' : '' }}" id="form-{{ $module->slug }}">
                @if($module->slug === 'hotel')
                    @include('forms.hotel-form')
                @elseif($module->slug === 'flight')
                    @include('forms.flight-form')
                @elseif($module->slug === 'tours')
                    @include('forms.tours-form')
                @elseif($module->slug === 'umrah')
                    @include('forms.umrah-form')
                @endif
            </div>
        @endforeach
    </div>

    <!-- Trust cards along the bottom edge of the cover image -->
    @include('common.hero-trust', ['items' => [
        ['fa-tags', 'home.best_price_guarantee', 'home.best_price_guarantee_desc'],
        ['fa-lock', 'home.secure_payment', 'home.secure_payment_desc'],
        ['fa-star', 'home.trusted_by_millions', 'home.trusted_by_millions_desc'],
        ['fa-headset', 'hotel.supportTitle', 'hotel.supportDesc'],
    ]])
</section>

    <!-- TRUST & FEATURES SECTION -->
    <div class="bg-white py-12 md:py-16 px-4 mt-10" style="display: none;">
        <div class="max-w-6xl mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-8">
                <div class="feature-card">
                    <div class="feature-icon">🏆</div>
                    <h3 style="color: #003580;" class="text-lg md:text-xl font-bold mb-2">{{ t('home.best_price_guarantee') }}</h3>
                    <p style="color: #6B7280;" class="text-sm leading-relaxed">{{ t('home.best_price_guarantee_desc') }}</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🔒</div>
                    <h3 style="color: #003580;" class="text-lg md:text-xl font-bold mb-2">{{ t('home.secure_payment') }}</h3>
                    <p style="color: #6B7280;" class="text-sm leading-relaxed">{{ t('home.secure_payment_desc') }}</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">⭐</div>
                    <h3 style="color: #003580;" class="text-lg md:text-xl font-bold mb-2">{{ t('home.trusted_by_millions') }}</h3>
                    <p style="color: #6B7280;" class="text-sm leading-relaxed">{{ t('home.trusted_by_millions_desc') }}</p>
                </div>
            </div>
        </div>
    </div>
 
@php
    $flightModule = getActiveModule('flight'); // active or null
@endphp
        <!-- POPULAR DESTINATIONS SECTION -->
    <div style="background-color: #F7F9FC; display:none; padding: 60px 20px; {{ $flightModule ? '' : 'display:none;' }}">
        <div style="max-width: 1200px; margin: 0 auto;">

            <!-- Section Header -->
            <div style="text-align: center; margin-bottom: 50px;">
                <h2 style="font-size: 32px; font-weight: bold; color: #003580; margin-bottom: 10px;">{{ t('home.popular_destination') }}</h2>
                <p style="font-size: 16px; color: #6B7280;">{{ t('home.popular_destination_sub') }}</p>
            </div>

            <!-- Destinations Grid -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">

                <!-- Destination Card 1 -->
                <div style="background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); transition: all 0.3s ease; cursor: pointer;" onmouseover="this.style.boxShadow='0 8px 24px color-mix(in srgb, var(--site-primary) 15%, transparent)'; this.style.transform='translateY(-4px)'" onmouseout="this.style.boxShadow='0 2px 8px rgba(0, 0, 0, 0.08)'; this.style.transform='translateY(0)'">
                    <div style="position: relative; overflow: hidden; height: 200px;">
                        <img src="https://images.unsplash.com/photo-1502602898657-3e91760cbb34?w=400&h=300&fit=crop" alt="New York" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                        <div style="position: absolute; top: 12px; right: 12px; background: #FF6B35; color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: bold;">{{ t('home.badge_popular') }}</div>
                    </div>
                    <div style="padding: 16px;">
                        <h3 style="font-size: 18px; font-weight: bold; color: #003580; margin-bottom: 8px;">New York</h3>
                        <p style="font-size: 14px; color: #6B7280; margin-bottom: 12px;">2,450 {{ t('home.hotels_available') }}</p>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 14px; color: var(--site-primary); font-weight: bold;">{{ t('home.from') }} {{$active_currency->currency_name}} {{convertCurrency(85, "USD", $active_currency->currency_name)}}{{ t('home.per_night') }}</span>
                            <span style="font-size: 12px; color: #FF6B35;">→</span>
                        </div>
                    </div>
                </div>

                <!-- Destination Card 2 -->
                <div style="background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); transition: all 0.3s ease; cursor: pointer;" onmouseover="this.style.boxShadow='0 8px 24px color-mix(in srgb, var(--site-primary) 15%, transparent)'; this.style.transform='translateY(-4px)'" onmouseout="this.style.boxShadow='0 2px 8px rgba(0, 0, 0, 0.08)'; this.style.transform='translateY(0)'">
                    <div style="position: relative; overflow: hidden; height: 200px;">
                        <img src="https://images.unsplash.com/photo-1502602898657-3e91760cbb34?w=400&h=300&fit=crop" alt="Paris" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                        <div style="position: absolute; top: 12px; right: 12px; background: #FF6B35; color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: bold;">{{ t('home.badge_popular') }}</div>
                    </div>
                    <div style="padding: 16px;">
                        <h3 style="font-size: 18px; font-weight: bold; color: #003580; margin-bottom: 8px;">Paris</h3>
                        <p style="font-size: 14px; color: #6B7280; margin-bottom: 12px;">1,890 {{ t('home.hotels_available') }}</p>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 14px; color: var(--site-primary); font-weight: bold;">{{ t('home.from') }} {{$active_currency->currency_name}} {{convertCurrency(92, "USD", $active_currency->currency_name)}}{{ t('home.per_night') }}</span>
                            <span style="font-size: 12px; color: #FF6B35;">→</span>
                        </div>
                    </div>
                </div>

                <!-- Destination Card 3 -->
                <div style="background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); transition: all 0.3s ease; cursor: pointer;" onmouseover="this.style.boxShadow='0 8px 24px color-mix(in srgb, var(--site-primary) 15%, transparent)'; this.style.transform='translateY(-4px)'" onmouseout="this.style.boxShadow='0 2px 8px rgba(0, 0, 0, 0.08)'; this.style.transform='translateY(0)'">
                    <div style="position: relative; overflow: hidden; height: 200px;">
                        <img src="https://images.unsplash.com/photo-1480714378408-67cf0d13bc1b?w=400&h=300&fit=crop" alt="Dubai" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                        <div style="position: absolute; top: 12px; right: 12px; background: #FF6B35; color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: bold;">{{ t('home.badge_sale') }}</div>
                    </div>
                    <div style="padding: 16px;">
                        <h3 style="font-size: 18px; font-weight: bold; color: #003580; margin-bottom: 8px;">Dubai</h3>
                        <p style="font-size: 14px; color: #6B7280; margin-bottom: 12px;">1,650 {{ t('home.hotels_available') }}</p>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 14px; color: var(--site-primary); font-weight: bold;">{{ t('home.from') }} {{$active_currency->currency_name}} {{convertCurrency(75, "USD", $active_currency->currency_name)}}{{ t('home.per_night') }}</span>
                            <span style="font-size: 12px; color: #FF6B35;">→</span>
                        </div>
                    </div>
                </div>

                <!-- Destination Card 4 -->
                <div style="background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); transition: all 0.3s ease; cursor: pointer;" onmouseover="this.style.boxShadow='0 8px 24px color-mix(in srgb, var(--site-primary) 15%, transparent)'; this.style.transform='translateY(-4px)'" onmouseout="this.style.boxShadow='0 2px 8px rgba(0, 0, 0, 0.08)'; this.style.transform='translateY(0)'">
                    <div style="position: relative; overflow: hidden; height: 200px;">
                        <img src="https://images.unsplash.com/photo-1488646953014-85cb44e25828?w=400&h=300&fit=crop" alt="Tokyo" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                    </div>
                    <div style="padding: 16px;">
                        <h3 style="font-size: 18px; font-weight: bold; color: #003580; margin-bottom: 8px;">Tokyo</h3>
                        <p style="font-size: 14px; color: #6B7280; margin-bottom: 12px;">2,120 {{ t('home.hotels_available') }}</p>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 14px; color: var(--site-primary); font-weight: bold;">{{ t('home.from') }} {{$active_currency->currency_name}} {{convertCurrency(96, "USD", $active_currency->currency_name)}}{{ t('home.per_night') }}</span>
                            <span style="font-size: 12px; color: #FF6B35;">→</span>
                        </div>
                    </div>
                </div>

                <!-- Destination Card 5 -->
                <div style="background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); transition: all 0.3s ease; cursor: pointer;" onmouseover="this.style.boxShadow='0 8px 24px color-mix(in srgb, var(--site-primary) 15%, transparent)'; this.style.transform='translateY(-4px)'" onmouseout="this.style.boxShadow='0 2px 8px rgba(0, 0, 0, 0.08)'; this.style.transform='translateY(0)'">
                    <div style="position: relative; overflow: hidden; height: 200px;">
                        <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400&h=300&fit=crop" alt="London" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                    </div>
                    <div style="padding: 16px;">
                        <h3 style="font-size: 18px; font-weight: bold; color: #003580; margin-bottom: 8px;">London</h3>
                        <p style="font-size: 14px; color: #6B7280; margin-bottom: 12px;">1,750 {{ t('home.hotels_available') }}</p>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 14px; color: var(--site-primary); font-weight: bold;">{{ t('home.from') }} {{$active_currency->currency_name}} {{convertCurrency(88, "USD", $active_currency->currency_name)}}{{ t('home.per_night') }}</span>
                            <span style="font-size: 12px; color: #FF6B35;">→</span>
                        </div>
                    </div>
                </div>

                <!-- Destination Card 6 -->
                <div style="background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); transition: all 0.3s ease; cursor: pointer;" onmouseover="this.style.boxShadow='0 8px 24px color-mix(in srgb, var(--site-primary) 15%, transparent)'; this.style.transform='translateY(-4px)'" onmouseout="this.style.boxShadow='0 2px 8px rgba(0, 0, 0, 0.08)'; this.style.transform='translateY(0)'">
                    <div style="position: relative; overflow: hidden; height: 200px;">
                        <img src="https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=400&h=300&fit=crop" alt="Sydney" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                    </div>
                    <div style="padding: 16px;">
                        <h3 style="font-size: 18px; font-weight: bold; color: #003580; margin-bottom: 8px;">Sydney</h3>
                        <p style="font-size: 14px; color: #6B7280; margin-bottom: 12px;">980 {{ t('home.hotels_available') }}</p>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 14px; color: var(--site-primary); font-weight: bold;">{{ t('home.from') }} {{$active_currency->currency_name}} {{convertCurrency(105, "USD", $active_currency->currency_name)}}{{ t('home.per_night') }}</span>
                            <span style="font-size: 12px; color: #FF6B35;">→</span>
                        </div>
                    </div>
                </div>

                <!-- Destination Card 7 -->
                <div style="background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); transition: all 0.3s ease; cursor: pointer;" onmouseover="this.style.boxShadow='0 8px 24px color-mix(in srgb, var(--site-primary) 15%, transparent)'; this.style.transform='translateY(-4px)'" onmouseout="this.style.boxShadow='0 2px 8px rgba(0, 0, 0, 0.08)'; this.style.transform='translateY(0)'">
                    <div style="position: relative; overflow: hidden; height: 200px;">
                        <img src="https://images.unsplash.com/photo-1502602898657-3e91760cbb34?w=400&h=300&fit=crop" alt="Bangkok" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                    </div>
                    <div style="padding: 16px;">
                        <h3 style="font-size: 18px; font-weight: bold; color: #003580; margin-bottom: 8px;">Bangkok</h3>
                        <p style="font-size: 14px; color: #6B7280; margin-bottom: 12px;">1,340 {{ t('home.hotels_available') }}</p>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 14px; color: var(--site-primary); font-weight: bold;">{{ t('home.from') }} {{$active_currency->currency_name}} {{convertCurrency(45, "USD", $active_currency->currency_name)}}{{ t('home.per_night') }}</span>
                            <span style="font-size: 12px; color: #FF6B35;">→</span>
                        </div>
                    </div>
                </div>

                <!-- Destination Card 8 -->
                <div style="background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); transition: all 0.3s ease; cursor: pointer;" onmouseover="this.style.boxShadow='0 8px 24px color-mix(in srgb, var(--site-primary) 15%, transparent)'; this.style.transform='translateY(-4px)'" onmouseout="this.style.boxShadow='0 2px 8px rgba(0, 0, 0, 0.08)'; this.style.transform='translateY(0)'">
                    <div style="position: relative; overflow: hidden; height: 200px;">
                        <img src="https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=400&h=300&fit=crop" alt="Bali" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                    </div>
                    <div style="padding: 16px;">
                        <h3 style="font-size: 18px; font-weight: bold; color: #003580; margin-bottom: 8px;">Bali</h3>
                        <p style="font-size: 14px; color: #6B7280; margin-bottom: 12px;">1,200 {{ t('home.hotels_available') }}</p>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 14px; color: var(--site-primary); font-weight: bold;">{{ t('home.from') }} {{$active_currency->currency_name}} {{convertCurrency(35, "USD", $active_currency->currency_name)}}{{ t('home.per_night') }}</span>
                            <span style="font-size: 12px; color: #FF6B35;">→</span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- View All Button -->
            <!-- <div style="text-align: center; margin-top: 40px;">
                <button style="background-color: var(--site-primary); color: white; border: none; padding: 12px 40px; font-size: 14px; font-weight: bold; border-radius: 6px; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 8px color-mix(in srgb, var(--site-primary) 15%, transparent);" onmouseover="this.style.backgroundColor='#0066A1'; this.style.boxShadow='0 4px 12px color-mix(in srgb, var(--site-primary) 30%, transparent)'; this.style.transform='translateY(-2px)'" onmouseout="this.style.backgroundColor='var(--site-primary)'; this.style.boxShadow='0 2px 8px color-mix(in srgb, var(--site-primary) 15%, transparent)'; this.style.transform='translateY(0)'">
                    View All Destinations
                </button>
            </div> -->
        </div>
    </div>

@php
    $hotelModule = getActiveModule('hotel'); // active or null
    $toursModule = getActiveModule('tours'); // active or null
    $umrahModule = getActiveModule('umrah'); // active or null

    // Section titles: last word gets the brand accent colour
    $thSplitTitle = function ($text) {
        $words = preg_split('/\s+/u', trim($text));
        $accent = count($words) > 1 ? array_pop($words) : '';
        return [implode(' ', $words), $accent];
    };
@endphp

    @if($flightModule)
    <!-- FEATURED FLIGHTS SECTION -->
    @include('common.flight-deals', ['deals' => $featuredFlightDeals, 'sectionId' => 'featuredHomeFlights'])
    @endif

    @if($hotelModule)
    <!-- FEATURED HOTELS SECTION -->
    @php [$thLead, $thAccent] = $thSplitTitle(t('home.trending_hotels')); @endphp
    <section class="th-section" id="trendingHotels">
        <div class="th-container">
            <div class="th-header">
                <div class="th-header-text">
                    <span class="th-eyebrow">{{ t('home.trending_hotels') }}</span>
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
                        'fPrice' => $fMinPrice > 0 ? $active_currency->currency_name . ' ' . number_format($fMinPrice, 0) : null,
                    ])
                @empty
                    <p class="th-empty">{{ t('home.no_featured_hotels') }}</p>
                @endforelse
            </div>
        </div>
    </section>
    @endif

    @if($toursModule)
    <!-- FEATURED TOURS SECTION -->
    @php [$thLead, $thAccent] = $thSplitTitle(t('tour.find_popular_tours')); @endphp
    <section class="th-section" id="popularTours">
        <div class="th-container">
            <div class="th-header">
                <div class="th-header-text">
                    <span class="th-eyebrow">{{ t('tour.find_popular_tours') }}</span>
                    <h2 class="th-title">{{ $thLead }} @if($thAccent)<span class="th-title-accent">{{ $thAccent }}</span>@endif</h2>
                </div>
                @if($tours->count() > 1)
                <div class="th-nav">
                    <button type="button" class="th-nav-btn th-nav-prev" data-th-dir="-1" aria-label="Previous"><i class="fas fa-arrow-left"></i></button>
                    <button type="button" class="th-nav-btn th-nav-next" data-th-dir="1" aria-label="Next"><i class="fas fa-arrow-right"></i></button>
                </div>
                @endif
            </div>

            <div class="th-track">
                @php
                    // Default date values - 2 days from today and 4 days from today
                    $startDate = date('d-m-Y', strtotime('+2 days'));
                    $endDate = date('d-m-Y', strtotime('+4 days'));
                    $adult = 2;
                    $child = 0;
                @endphp

                @foreach($tours as $index => $tour)
                    @php
                        // Tour slug from name
                        $slug = Str::slug($tour->name);

                        // Location slug (use location name or default 'dubai')
                        $locationSlug = $tour->location_name ? strtolower(str_replace(' ', '-', $tour->location_name)) : 'dubai';

                        // Package type (use type ID or 'all')
                        $type = $tour->packege_type ?? 'all';

                        // Build detail URL: tours/{slug}/{location}/{type}/{startDate}/{endDate}/{adult}/{child}
                        $detailUrl = url("tours/{$slug}/{$locationSlug}/{$type}/{$startDate}/{$endDate}/{$adult}/{$child}");
                    @endphp

                    @include('common.tour-card', [
                        'tourPrice' => $active_currency->currency_name . ' ' . convertCurrency($tour->price, $tour->currency ?? 'USD', $active_currency->currency_name),
                    ])
                @endforeach
            </div>
        </div>
    </section>
    @endif

    @if($umrahModule)
    <!-- FEATURED UMRAH PACKAGES SECTION -->
    @php
        // Highlight the last two words of the heading ("Umrah Packages")
        $umTitleWords = preg_split('/\s+/u', trim(t('home.umrah_featured_title')));
        $umAccent = count($umTitleWords) > 2 ? implode(' ', array_splice($umTitleWords, -2)) : '';
        $umLead = implode(' ', $umTitleWords);

        // Default search values for the package detail links - 2 weeks from today
        $departureDate = date('d-m-Y', strtotime('+14 days'));
        $returnDate = date('d-m-Y', strtotime('+21 days'));
        $adult = 2;
        $child = 0;
        $infant = 0;
        $makkahNights = 4;
        $madinaNights = 3;

        // Icon per included service, matched on its name
        $umServiceIcon = fn($name) => match (true) {
            str_contains(strtolower($name), 'flight') => 'fa-plane',
            str_contains(strtolower($name), 'visa') => 'fa-passport',
            str_contains(strtolower($name), 'hotel') => 'fa-hotel',
            str_contains(strtolower($name), 'bus'), str_contains(strtolower($name), 'transfer') => 'fa-bus',
            default => 'fa-check-circle',
        };

        $umItems = $umrahPackages->map(function ($package) use ($departureDate, $returnDate, $adult, $child, $infant, $makkahNights, $madinaNights, $active_currency) {
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
            $slug = Str::slug($package->name);

            return (object) [
                'package' => $package,
                'url' => url("umrah/{$slug}/{$originCode}/{$destinationCode}/{$departureDate}/{$returnDate}/{$adult}/{$child}/{$infant}/{$makkahNights}/{$madinaNights}"),
                'image' => $package->images && $package->images->first()
                    ? asset('public/assets/images/' . $package->images->first()->image)
                    : 'https://images.unsplash.com/photo-1591604129939-f1efa4d9f7fa?w=400&h=300&fit=crop',
                'price' => $active_currency->currency_name . ' ' . convertCurrency($package->price, $package->currency ?? 'USD', $active_currency->currency_name),
                'services' => array_slice($package->highlights ?? [], 0, 3),
            ];
        });
        $umLeadItem = $umItems->first();
        $umOthers = $umItems->slice(1, 3);
    @endphp
    <section class="th-section" id="umrahPackages">
        <div class="th-container">
            <div class="th-header">
                <div class="th-header-text">
                    <span class="th-eyebrow">{{ t('home.umrah_featured_title') }}</span>
                    <h2 class="th-title">{{ $umLead }} @if($umAccent)<span class="th-title-accent">{{ $umAccent }}</span>@endif</h2>
                    <p class="um-intro">{{ t('umrah.featuredPackagesSubtitle') }}</p>
                </div>
                <a href="{{ route('umrah.index') }}" class="um-all-link">{{ t('umrah.exploreAllPackages') }} <i class="fas fa-arrow-right"></i></a>
            </div>

            @if($umLeadItem)
            {{-- every package shows at once (one large + up to three compact), so no prev/next controls --}}
            <div class="um-layout {{ $umOthers->isEmpty() ? 'is-single' : '' }}">
                <div class="um-lead">
                    <article class="um-feature">
                        <a href="{{ $umLeadItem->url }}" class="um-feature-media">
                            <img src="{{ $umLeadItem->image }}" alt="{{ $umLeadItem->package->name }}" loading="lazy">
                            @if($umLeadItem->package->featured == 1)
                                <span class="um-badge"><i class="fas fa-star"></i> {{ t('home.badge_featured') }}</span>
                            @endif
                        </a>
                        <div class="um-body">
                            <h3 class="um-name" dir="auto"><a href="{{ $umLeadItem->url }}">{{ $umLeadItem->package->name }}</a></h3>
                            @if($umLeadItem->package->from_location && $umLeadItem->package->to_location)
                                <p class="um-route"><i class="fas fa-plane-departure"></i> <span>{{ $umLeadItem->package->from_location }} <i class="fas fa-arrow-right um-route-arrow"></i> {{ $umLeadItem->package->to_location }}</span></p>
                            @endif
                            @if($umLeadItem->services)
                                <div class="um-services">
                                    @foreach($umLeadItem->services as $service)
                                        <span><i class="fas {{ $umServiceIcon($service) }}"></i> {{ $service }}</span>
                                    @endforeach
                                </div>
                            @endif
                            <div class="um-foot">
                                <div class="um-price">
                                    <small>{{ t('home.starting_from') }}</small>
                                    <b>{{ $umLeadItem->price }}</b>
                                    <small>{{ t('home.per_adult') }}</small>
                                </div>
                                <a href="{{ $umLeadItem->url }}" class="um-btn">{{ t('home.view_details') }} <i class="fas fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </article>

                    <ul class="um-trust">
                        <li><i class="fas fa-kaaba"></i><div><b>{{ t('umrah.hassleFreeTravel') }}</b><small>{{ t('umrah.hassleFreeTravelDesc') }}</small></div></li>
                        <li><i class="fas fa-shield-halved"></i><div><b>{{ t('home.secure_payment') }}</b><small>{{ t('home.secure_payment_desc') }}</small></div></li>
                        <li><i class="fas fa-headset"></i><div><b>{{ t('umrah.support247') }}</b><small>{{ t('umrah.support247Desc') }}</small></div></li>
                        <li><i class="fas fa-tag"></i><div><b>{{ t('home.best_price_guarantee') }}</b><small>{{ t('home.best_price_guarantee_desc') }}</small></div></li>
                    </ul>
                </div>

                @if($umOthers->isNotEmpty())
                <div class="um-list">
                    @foreach($umOthers as $item)
                        <article class="um-item">
                            <a href="{{ $item->url }}" class="um-item-media">
                                <img src="{{ $item->image }}" alt="{{ $item->package->name }}" loading="lazy">
                                @if($item->package->featured == 1)
                                    <span class="um-badge"><i class="fas fa-star"></i> {{ t('home.badge_featured') }}</span>
                                @endif
                            </a>
                            <div class="um-body">
                                <h3 class="um-name" dir="auto"><a href="{{ $item->url }}">{{ $item->package->name }}</a></h3>
                                @if($item->package->from_location && $item->package->to_location)
                                    <p class="um-route"><i class="fas fa-plane-departure"></i> <span>{{ $item->package->from_location }} <i class="fas fa-arrow-right um-route-arrow"></i> {{ $item->package->to_location }}</span></p>
                                @endif
                                @if($item->services)
                                    <div class="um-services">
                                        @foreach($item->services as $service)
                                            <span><i class="fas {{ $umServiceIcon($service) }}"></i> {{ $service }}</span>
                                        @endforeach
                                    </div>
                                @endif
                                <div class="um-foot">
                                    <div class="um-price">
                                        <small>{{ t('home.starting_from') }}</small>
                                        <b>{{ $item->price }}</b>
                                        <small>{{ t('home.per_adult') }}</small>
                                    </div>
                                    <a href="{{ $item->url }}" class="um-btn">{{ t('home.view_details') }} <i class="fas fa-arrow-right"></i></a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
                @endif
            </div>
            @endif
        </div>
    </section>
    @endif

    <!-- HOW IT WORKS SECTION -->
    @php [$thLead, $thAccent] = $thSplitTitle(t('home.how_it_work')); @endphp
    <section class="th-section" id="howItWorks">
        <div class="th-container">
            <div class="th-header">
                <div class="th-header-text">
                    <span class="th-eyebrow">{{ t('home.how_it_work') }}</span>
                    <h2 class="th-title">{{ $thLead }} @if($thAccent)<span class="th-title-accent">{{ $thAccent }}</span>@endif</h2>
                </div>
            </div>

            <div class="hw-grid">
                @foreach([
                    ['fa-magnifying-glass', 'home.step_1_title', 'home.step_1_desc'],
                    ['fa-star', 'home.step_2_title', 'home.step_2_desc'],
                    ['fa-credit-card', 'home.step_3_title', 'home.step_3_desc'],
                    ['fa-circle-check', 'home.step_4_title', 'home.step_4_desc'],
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

    <!-- Search with AI dialog (opened from the flight form's #aiSearchBtn) -->
    <div class="ai-modal" id="aiSearchModal" hidden>
        <div class="ai-modal-backdrop" data-ai-close></div>
        <div class="ai-modal-panel" role="dialog" aria-modal="true" aria-labelledby="aiSearchTitle">
            <button type="button" class="ai-modal-close" data-ai-close aria-label="Close"><i class="fas fa-xmark"></i></button>
            <div class="ai-modal-head">
                <span class="ai-modal-icon"><i class="fas fa-wand-magic-sparkles"></i></span>
                <div>
                    <h3 id="aiSearchTitle">Search with AI</h3>
                    <p>Describe your trip in your own words and we'll find the flights.</p>
                </div>
            </div>
            <form id="aiSearchForm" class="ai-modal-form">
                @csrf
                <textarea id="aiSearchQuery" name="query" rows="3" maxlength="500" required
                    placeholder="e.g. Lahore to Dubai next Friday, back on 20 December, 2 adults in business"></textarea>
                <div class="ai-modal-examples">
                    <button type="button" class="ai-example">Karachi to Jeddah on 15 December, 2 adults</button>
                    <button type="button" class="ai-example">Islamabad to London next Monday, one way</button>
                    <button type="button" class="ai-example">Lahore to Dubai tomorrow for 5 days, business</button>
                </div>
                <div class="ai-modal-status" id="aiSearchStatus" role="status" aria-live="polite"></div>
                <button type="submit" class="ai-modal-submit" id="aiSearchSubmit">
                    <i class="fas fa-magnifying-glass"></i> <span>Search Flights</span>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    // Search with AI: free-text request -> /ai-search/flights -> flight results page
    (function () {
        var openBtn = document.getElementById('aiSearchBtn');
        var modal = document.getElementById('aiSearchModal');
        if (!openBtn || !modal) return;
        var form = document.getElementById('aiSearchForm');
        var input = document.getElementById('aiSearchQuery');
        var status = document.getElementById('aiSearchStatus');
        var submit = document.getElementById('aiSearchSubmit');

        function setStatus(text, type) {
            status.textContent = text || '';
            status.className = 'ai-modal-status' + (type ? ' is-' + type : '');
        }
        function open() {
            modal.hidden = false;
            document.body.classList.add('overflow-hidden');
            setStatus('');
            setTimeout(function () { input.focus(); }, 30);
        }
        function close() {
            modal.hidden = true;
            document.body.classList.remove('overflow-hidden');
            openBtn.focus();
        }

        openBtn.addEventListener('click', open);
        modal.querySelectorAll('[data-ai-close]').forEach(function (el) { el.addEventListener('click', close); });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !modal.hidden) close();
        });
        modal.querySelectorAll('.ai-example').forEach(function (chip) {
            chip.addEventListener('click', function () { input.value = chip.textContent.trim(); input.focus(); });
        });
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); form.requestSubmit(); }
        });

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var query = input.value.trim();
            if (query.length < 3) { setStatus('Please describe your trip.', 'error'); return; }

            submit.disabled = true;
            setStatus('Understanding your request…', 'loading');

            fetch('{{ route('ai-search.flights') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value
                },
                body: JSON.stringify({ query: query })
            })
            .then(function (r) {
                if (r.status === 429) throw new Error('Too many searches. Please wait a minute and try again.');
                return r.json();
            })
            .then(function (data) {
                if (!data.success) {
                    submit.disabled = false;
                    setStatus(data.message || (data.errors && data.errors.query && data.errors.query[0]) || 'Sorry, we could not understand that request.', 'error');
                    return;
                }
                var s = data.summary;
                setStatus(s.from + ' → ' + s.to + ' · ' + s.departure + (s.return ? ' – ' + s.return : '') + ' · ' + s.travellers + ' traveller' + (s.travellers > 1 ? 's' : '') + ' · ' + s.cabin + '. Searching…', 'success');
                if (typeof showPageLoader === 'function') showPageLoader('flight');
                window.location.href = data.url;
            })
            .catch(function (err) {
                submit.disabled = false;
                setStatus(err.message && err.message.indexOf('Too many') === 0 ? err.message : 'Something went wrong. Please try again.', 'error');
            });
        });
    })();

</script>

@endsection
