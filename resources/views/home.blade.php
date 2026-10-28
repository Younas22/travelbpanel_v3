@extends('common.layout')
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

<style>
    /* Hero Background Image - Crystal Clear */
    /* Pulled up under the floating header (--nav-offset, style.css) so the
       scenery fills the area around the rounded nav bar. */
    .hero-section {
        background: url('<?=getSettingImage('cover_image', 'homepage')?>') center 35%/cover no-repeat;
        min-height: 720px;
        position: relative;
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
        margin-top: calc(-1 * var(--nav-offset));
        padding-top: calc(var(--nav-offset) + 64px);
        padding-bottom: 88px;
    }

    .form-container {
        background-color: #fff;
        border-radius: 24px;
        box-shadow: 0 24px 64px rgba(0, 0, 0, 0.14);
        width: calc(100% - 32px);
        max-width: 1100px;
        padding: 20px 24px 24px;
        z-index: 20;
        position: relative;
        margin: 0 auto;
    }

    /* Fix for dropdowns on home page - ensure they appear above form container.
       Every form partial's dropdown is listed: the last partial on the page
       (Umrah) sets the shared .dropdown-overlay to z-index 999, which would
       otherwise cover any dropdown below it. */
    .form-container .airport-dropdown,
    .form-container .passenger-dropdown,
    .form-container .class-dropdown,
    .form-container .country-dropdown,
    .form-container .destination-dropdown,
    .form-container .tour-type-dropdown,
    .form-container .traveler-dropdown {
        z-index: 150 !important;
    }

    .form-container .dropdown-overlay {
        z-index: 140 !important;
    }

    /* Tab Styles — a glass pill bar above the search panel (outside the
       white card). Centering uses auto margins on the first/last tab (not
       justify-content) so an overflowing row still scrolls to its first tab
       on small screens. */
    .tab-buttons-wrapper {
        display: flex;
        justify-content: center;
        width: calc(100% - 32px);
        max-width: 1100px;
        margin: 0 auto 10px;
        position: relative;
        z-index: 21;
    }

    .tab-buttons {
        display: flex;
        justify-content: flex-start;
        gap: 4px;
        max-width: 100%;
        padding: 6px;
        border-radius: 9999px;
        border: 1px solid rgba(255, 255, 255, 0.55);
        background: rgba(37, 99, 235, 0.32);
        backdrop-filter: blur(14px) saturate(140%);
        -webkit-backdrop-filter: blur(14px) saturate(140%);
        box-shadow: 0 8px 24px rgba(37, 99, 235, 0.18);
        overflow-x: auto;
        scrollbar-width: none;
    }
    .tab-buttons::-webkit-scrollbar { display: none; }
    .tab-buttons > .tab-btn:first-child { margin-left: auto; }
    .tab-buttons > .tab-btn:last-child { margin-right: auto; }

    .tab-btn {
        flex: 0 0 auto;
        height: 46px;
        padding: 0 22px;
        background: transparent;
        border: 0;
        font-weight: 600;
        font-size: 15px;
        letter-spacing: 0;
        text-transform: none;
        color: #fff;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.18);
        text-decoration: none;
        cursor: pointer;
        transition: background-color .2s ease, color .2s ease, box-shadow .2s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        white-space: nowrap;
        border-radius: 9999px;
        box-shadow: none;
    }

    .tab-btn i {
        color: #fff;
        font-size: 16px;
    }

    .tab-btn:hover {
        color: #fff;
        background: rgba(255, 255, 255, 0.2);
    }

    .tab-btn.active {
        color: #000;
        text-shadow: none;
        background: #fff;
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.14);
    }

    .tab-btn.active i {
        color: #2563EB;
    }

    /* Primary-action color override, scoped to the homepage only — .cta-button,
       .view-deal-btn, .view-deal-btn-umrah and .step-number are shared global
       classes also used on the Tours/Umrah listing pages. */
    #homeRedesign .cta-button,
    #homeRedesign .view-deal-btn,
    #homeRedesign .view-deal-btn-umrah {
        background: #0346FA;
    }

    #homeRedesign .view-deal-btn:hover,
    #homeRedesign .view-deal-btn-umrah:hover {
        box-shadow: 0 4px 12px rgba(3,70,250,0.3);
    }

    #homeRedesign .step-number {
        background: #0346FA;
    }

    #homeRedesign .step-card:hover .step-number {
        box-shadow: 0 8px 20px rgba(3,70,250,0.3);
    }

    #homeRedesign .connector {
        background: linear-gradient(90deg, transparent, #0346FA 50%, transparent);
    }

    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
        animation: fadeIn 0.3s ease-in;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Responsive */
    @media (max-width: 1023px) {
        .hero-section {
            min-height: 0;
            padding-top: calc(var(--nav-offset) + 40px);
            padding-bottom: 64px;
        }

        .form-container {
            padding: 22px 22px 24px;
            border-radius: 24px;
        }
    }

    @media (max-width: 640px) {
        .hero-section {
            padding-top: calc(var(--nav-offset) + 28px);
            padding-bottom: 48px;
        }

        .form-container {
            width: calc(100% - 24px);
            padding: 16px 14px 18px;
            border-radius: 20px;
        }

        .tab-buttons-wrapper {
            width: calc(100% - 24px);
            margin-bottom: 8px;
        }

        .tab-buttons {
            padding: 4px;
        }

        .tab-btn {
            height: 40px;
            padding: 0 14px;
            font-size: 13px;
            gap: 6px;
        }

        .tab-btn i {
            font-size: 14px;
        }
    }
</style>

@php
    // Hero title from the existing translation, with its last two words in
    // brand blue ("Find Your | Perfect Flight"). Short titles highlight the
    // last word; languages written without spaces render in one color.
    $heroTitleWords = preg_split('/\s+/u', trim(t('home.hero_title')));
    $heroAccentCount = count($heroTitleWords) >= 3 ? 2 : (count($heroTitleWords) === 2 ? 1 : 0);
    $heroTitleLead = implode(' ', array_slice($heroTitleWords, 0, count($heroTitleWords) - $heroAccentCount));
    $heroTitleAccent = implode(' ', array_slice($heroTitleWords, count($heroTitleWords) - $heroAccentCount));
@endphp

<div id="homeRedesign">
<section class="hero-section">
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
                <div style="background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); transition: all 0.3s ease; cursor: pointer;" onmouseover="this.style.boxShadow='0 8px 24px rgba(0, 119, 190, 0.15)'; this.style.transform='translateY(-4px)'" onmouseout="this.style.boxShadow='0 2px 8px rgba(0, 0, 0, 0.08)'; this.style.transform='translateY(0)'">
                    <div style="position: relative; overflow: hidden; height: 200px;">
                        <img src="https://images.unsplash.com/photo-1502602898657-3e91760cbb34?w=400&h=300&fit=crop" alt="New York" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                        <div style="position: absolute; top: 12px; right: 12px; background: #FF6B35; color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: bold;">{{ t('home.badge_popular') }}</div>
                    </div>
                    <div style="padding: 16px;">
                        <h3 style="font-size: 18px; font-weight: bold; color: #003580; margin-bottom: 8px;">New York</h3>
                        <p style="font-size: 14px; color: #6B7280; margin-bottom: 12px;">2,450 {{ t('home.hotels_available') }}</p>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 14px; color: #0077BE; font-weight: bold;">{{ t('home.from') }} {{$active_currency->currency_name}} {{convertCurrency(85, "USD", $active_currency->currency_name)}}{{ t('home.per_night') }}</span>
                            <span style="font-size: 12px; color: #FF6B35;">→</span>
                        </div>
                    </div>
                </div>

                <!-- Destination Card 2 -->
                <div style="background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); transition: all 0.3s ease; cursor: pointer;" onmouseover="this.style.boxShadow='0 8px 24px rgba(0, 119, 190, 0.15)'; this.style.transform='translateY(-4px)'" onmouseout="this.style.boxShadow='0 2px 8px rgba(0, 0, 0, 0.08)'; this.style.transform='translateY(0)'">
                    <div style="position: relative; overflow: hidden; height: 200px;">
                        <img src="https://images.unsplash.com/photo-1502602898657-3e91760cbb34?w=400&h=300&fit=crop" alt="Paris" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                        <div style="position: absolute; top: 12px; right: 12px; background: #FF6B35; color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: bold;">{{ t('home.badge_popular') }}</div>
                    </div>
                    <div style="padding: 16px;">
                        <h3 style="font-size: 18px; font-weight: bold; color: #003580; margin-bottom: 8px;">Paris</h3>
                        <p style="font-size: 14px; color: #6B7280; margin-bottom: 12px;">1,890 {{ t('home.hotels_available') }}</p>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 14px; color: #0077BE; font-weight: bold;">{{ t('home.from') }} {{$active_currency->currency_name}} {{convertCurrency(92, "USD", $active_currency->currency_name)}}{{ t('home.per_night') }}</span>
                            <span style="font-size: 12px; color: #FF6B35;">→</span>
                        </div>
                    </div>
                </div>

                <!-- Destination Card 3 -->
                <div style="background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); transition: all 0.3s ease; cursor: pointer;" onmouseover="this.style.boxShadow='0 8px 24px rgba(0, 119, 190, 0.15)'; this.style.transform='translateY(-4px)'" onmouseout="this.style.boxShadow='0 2px 8px rgba(0, 0, 0, 0.08)'; this.style.transform='translateY(0)'">
                    <div style="position: relative; overflow: hidden; height: 200px;">
                        <img src="https://images.unsplash.com/photo-1480714378408-67cf0d13bc1b?w=400&h=300&fit=crop" alt="Dubai" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                        <div style="position: absolute; top: 12px; right: 12px; background: #FF6B35; color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: bold;">{{ t('home.badge_sale') }}</div>
                    </div>
                    <div style="padding: 16px;">
                        <h3 style="font-size: 18px; font-weight: bold; color: #003580; margin-bottom: 8px;">Dubai</h3>
                        <p style="font-size: 14px; color: #6B7280; margin-bottom: 12px;">1,650 {{ t('home.hotels_available') }}</p>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 14px; color: #0077BE; font-weight: bold;">{{ t('home.from') }} {{$active_currency->currency_name}} {{convertCurrency(75, "USD", $active_currency->currency_name)}}{{ t('home.per_night') }}</span>
                            <span style="font-size: 12px; color: #FF6B35;">→</span>
                        </div>
                    </div>
                </div>

                <!-- Destination Card 4 -->
                <div style="background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); transition: all 0.3s ease; cursor: pointer;" onmouseover="this.style.boxShadow='0 8px 24px rgba(0, 119, 190, 0.15)'; this.style.transform='translateY(-4px)'" onmouseout="this.style.boxShadow='0 2px 8px rgba(0, 0, 0, 0.08)'; this.style.transform='translateY(0)'">
                    <div style="position: relative; overflow: hidden; height: 200px;">
                        <img src="https://images.unsplash.com/photo-1488646953014-85cb44e25828?w=400&h=300&fit=crop" alt="Tokyo" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                    </div>
                    <div style="padding: 16px;">
                        <h3 style="font-size: 18px; font-weight: bold; color: #003580; margin-bottom: 8px;">Tokyo</h3>
                        <p style="font-size: 14px; color: #6B7280; margin-bottom: 12px;">2,120 {{ t('home.hotels_available') }}</p>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 14px; color: #0077BE; font-weight: bold;">{{ t('home.from') }} {{$active_currency->currency_name}} {{convertCurrency(96, "USD", $active_currency->currency_name)}}{{ t('home.per_night') }}</span>
                            <span style="font-size: 12px; color: #FF6B35;">→</span>
                        </div>
                    </div>
                </div>

                <!-- Destination Card 5 -->
                <div style="background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); transition: all 0.3s ease; cursor: pointer;" onmouseover="this.style.boxShadow='0 8px 24px rgba(0, 119, 190, 0.15)'; this.style.transform='translateY(-4px)'" onmouseout="this.style.boxShadow='0 2px 8px rgba(0, 0, 0, 0.08)'; this.style.transform='translateY(0)'">
                    <div style="position: relative; overflow: hidden; height: 200px;">
                        <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400&h=300&fit=crop" alt="London" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                    </div>
                    <div style="padding: 16px;">
                        <h3 style="font-size: 18px; font-weight: bold; color: #003580; margin-bottom: 8px;">London</h3>
                        <p style="font-size: 14px; color: #6B7280; margin-bottom: 12px;">1,750 {{ t('home.hotels_available') }}</p>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 14px; color: #0077BE; font-weight: bold;">{{ t('home.from') }} {{$active_currency->currency_name}} {{convertCurrency(88, "USD", $active_currency->currency_name)}}{{ t('home.per_night') }}</span>
                            <span style="font-size: 12px; color: #FF6B35;">→</span>
                        </div>
                    </div>
                </div>

                <!-- Destination Card 6 -->
                <div style="background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); transition: all 0.3s ease; cursor: pointer;" onmouseover="this.style.boxShadow='0 8px 24px rgba(0, 119, 190, 0.15)'; this.style.transform='translateY(-4px)'" onmouseout="this.style.boxShadow='0 2px 8px rgba(0, 0, 0, 0.08)'; this.style.transform='translateY(0)'">
                    <div style="position: relative; overflow: hidden; height: 200px;">
                        <img src="https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=400&h=300&fit=crop" alt="Sydney" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                    </div>
                    <div style="padding: 16px;">
                        <h3 style="font-size: 18px; font-weight: bold; color: #003580; margin-bottom: 8px;">Sydney</h3>
                        <p style="font-size: 14px; color: #6B7280; margin-bottom: 12px;">980 {{ t('home.hotels_available') }}</p>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 14px; color: #0077BE; font-weight: bold;">{{ t('home.from') }} {{$active_currency->currency_name}} {{convertCurrency(105, "USD", $active_currency->currency_name)}}{{ t('home.per_night') }}</span>
                            <span style="font-size: 12px; color: #FF6B35;">→</span>
                        </div>
                    </div>
                </div>

                <!-- Destination Card 7 -->
                <div style="background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); transition: all 0.3s ease; cursor: pointer;" onmouseover="this.style.boxShadow='0 8px 24px rgba(0, 119, 190, 0.15)'; this.style.transform='translateY(-4px)'" onmouseout="this.style.boxShadow='0 2px 8px rgba(0, 0, 0, 0.08)'; this.style.transform='translateY(0)'">
                    <div style="position: relative; overflow: hidden; height: 200px;">
                        <img src="https://images.unsplash.com/photo-1502602898657-3e91760cbb34?w=400&h=300&fit=crop" alt="Bangkok" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                    </div>
                    <div style="padding: 16px;">
                        <h3 style="font-size: 18px; font-weight: bold; color: #003580; margin-bottom: 8px;">Bangkok</h3>
                        <p style="font-size: 14px; color: #6B7280; margin-bottom: 12px;">1,340 {{ t('home.hotels_available') }}</p>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 14px; color: #0077BE; font-weight: bold;">{{ t('home.from') }} {{$active_currency->currency_name}} {{convertCurrency(45, "USD", $active_currency->currency_name)}}{{ t('home.per_night') }}</span>
                            <span style="font-size: 12px; color: #FF6B35;">→</span>
                        </div>
                    </div>
                </div>

                <!-- Destination Card 8 -->
                <div style="background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); transition: all 0.3s ease; cursor: pointer;" onmouseover="this.style.boxShadow='0 8px 24px rgba(0, 119, 190, 0.15)'; this.style.transform='translateY(-4px)'" onmouseout="this.style.boxShadow='0 2px 8px rgba(0, 0, 0, 0.08)'; this.style.transform='translateY(0)'">
                    <div style="position: relative; overflow: hidden; height: 200px;">
                        <img src="https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=400&h=300&fit=crop" alt="Bali" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                    </div>
                    <div style="padding: 16px;">
                        <h3 style="font-size: 18px; font-weight: bold; color: #003580; margin-bottom: 8px;">Bali</h3>
                        <p style="font-size: 14px; color: #6B7280; margin-bottom: 12px;">1,200 {{ t('home.hotels_available') }}</p>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 14px; color: #0077BE; font-weight: bold;">{{ t('home.from') }} {{$active_currency->currency_name}} {{convertCurrency(35, "USD", $active_currency->currency_name)}}{{ t('home.per_night') }}</span>
                            <span style="font-size: 12px; color: #FF6B35;">→</span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- View All Button -->
            <!-- <div style="text-align: center; margin-top: 40px;">
                <button style="background-color: #0077BE; color: white; border: none; padding: 12px 40px; font-size: 14px; font-weight: bold; border-radius: 6px; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 8px rgba(0, 119, 190, 0.15);" onmouseover="this.style.backgroundColor='#0066A1'; this.style.boxShadow='0 4px 12px rgba(0, 119, 190, 0.3)'; this.style.transform='translateY(-2px)'" onmouseout="this.style.backgroundColor='#0077BE'; this.style.boxShadow='0 2px 8px rgba(0, 119, 190, 0.15)'; this.style.transform='translateY(0)'">
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
                    // amenities store Bootstrap Icons classes, which are not loaded on public pages
                    $thAmenityIcons = [
                        'bi-wifi' => 'fa-wifi', 'bi-car-front' => 'fa-car', 'bi-snow' => 'fa-snowflake',
                        'bi-tv' => 'fa-tv', 'bi-water' => 'fa-water-ladder', 'bi-bicycle' => 'fa-dumbbell',
                        'bi-shop' => 'fa-utensils', 'bi-bell' => 'fa-bell-concierge', 'bi-droplet' => 'fa-shirt',
                        'bi-arrow-up-square' => 'fa-elevator', 'bi-cup-straw' => 'fa-martini-glass',
                        'bi-heart-pulse' => 'fa-spa', 'bi-people' => 'fa-users', 'bi-clock' => 'fa-clock',
                        'bi-egg-fried' => 'fa-egg', 'bi-cup-hot' => 'fa-mug-hot', 'bi-shield-lock' => 'fa-lock',
                        'bi-door-open' => 'fa-door-open', 'bi-bus-front' => 'fa-van-shuttle', 'bi-person-badge' => 'fa-id-badge',
                    ];
                @endphp

                @forelse($featuredHotels as $fHotel)
                @php
                    $fImage = $fHotel->images->first()
                        ? asset('public/assets/images/' . $fHotel->images->first()->image_path)
                        : 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=400&h=300&fit=crop';
                    $fCity = $fHotel->location ? $fHotel->location->city : null;
                    $fLocation = $fHotel->location ? $fHotel->location->city . ', ' . $fHotel->location->country : $fHotel->address;
                    $fAmenities = $fHotel->amenities->take(3);
                    $fSlug = \Illuminate\Support\Str::slug(\Illuminate\Support\Str::limit($fHotel->name, 30), '-');
                    $fDetailUrl = url("hotel/details/{$fHotel->id}/{$fSlug}/{$checkin}/{$checkout}/2/0/1/manual");
                    $fMinPrice = $fHotel->roomTypes()->where('status', 1)->min('price_per_night') ?? 0;
                    $fStars = max(0, min(5, (int) $fHotel->stars));
                @endphp
                <article class="th-card">
                    <a href="{{ $fDetailUrl }}" class="th-card-media">
                        <img src="{{ $fImage }}" alt="{{ $fHotel->name }}" loading="lazy">
                        @if($fHotel->featured == 1)
                            <span class="th-badge"><i class="fas fa-star"></i> {{ t('home.badge_featured') }}</span>
                        @endif
                        @if($fCity)
                            <span class="th-chip"><i class="fas fa-location-dot"></i> {{ $fCity }}</span>
                        @endif
                    </a>
                    <div class="th-card-body">
                        <h3 class="th-name"><a href="{{ $fDetailUrl }}">{{ $fHotel->name }}</a></h3>
                        @if($fLocation)
                            <p class="th-location"><i class="fas fa-location-dot"></i> <span>{{ $fLocation }}</span></p>
                        @endif

                        @if($fStars > 0)
                        <div class="th-rating">
                            <span class="th-stars">
                                @for($s = 1; $s <= 5; $s++)
                                    <i class="fas fa-star {{ $s <= $fStars ? 'is-on' : '' }}"></i>
                                @endfor
                            </span>
                            <span class="th-rating-text">{{ $fStars }} {{ t('hotel.stars') }}</span>
                        </div>
                        @endif

                        @if($fAmenities->count())
                        <div class="th-pills">
                            @foreach($fAmenities as $fAmenity)
                                @php
                                    $fBi = collect(explode(' ', (string) $fAmenity->icon))->first(fn($c) => isset($thAmenityIcons[$c]));
                                    $fFa = $fBi ? $thAmenityIcons[$fBi] : 'fa-check';
                                @endphp
                                <span class="th-pill"><i class="fas {{ $fFa }}"></i> {{ $fAmenity->name }}</span>
                            @endforeach
                        </div>
                        @endif

                        <div class="th-footer">
                            <div class="th-price-wrap">
                                @if($fMinPrice > 0)
                                    <span class="th-price-label">{{ t('home.starting_from') }}</span>
                                    <span class="th-price">{{ $active_currency->currency_name }} {{ number_format($fMinPrice, 0) }}</span>
                                @else
                                    <span class="th-price-none">{{ t('hotel.contactForPrice') }}</span>
                                @endif
                            </div>
                            <a href="{{ $fDetailUrl }}" class="th-view-btn">{{ t('home.view') }} <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                </article>
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

                        // Get first image or use placeholder
                        $imageUrl = $tour->images && $tour->images->first()
                            ? asset('public/assets/images/' . $tour->images->first()->image)
                            : 'https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?w=400&h=300&fit=crop';

                        // Get first 3 highlights
                        $highlights = $tour->highlights ? array_slice($tour->highlights, 0, 3) : [];
                    @endphp

                    <article class="th-card">
                        <a href="{{ $detailUrl }}" class="th-card-media">
                            <img src="{{ $imageUrl }}" alt="{{ $tour->name }}" loading="lazy">
                            @if($tour->featured == 1)
                                <span class="th-badge"><i class="fas fa-star"></i> {{ t('tour.popular') }}</span>
                            @endif
                            @if($tour->days)
                                <span class="th-chip"><i class="fas fa-clock"></i> {{ $tour->days }} {{ $tour->days > 1 ? t('tour.days') : t('tour.day') }}</span>
                            @endif
                        </a>
                        <div class="th-card-body">
                            <h3 class="th-name"><a href="{{ $detailUrl }}">{{ $tour->name }}</a></h3>
                            @if($tour->location_name)
                                <p class="th-location"><i class="fas fa-location-dot"></i> <span>{{ $tour->location_name }}</span></p>
                            @endif

                            <div class="th-pills">
                                @if(count($highlights) > 0)
                                    @foreach($highlights as $highlight)
                                        <span class="th-pill"><i class="fas fa-check-circle"></i> {{ Str::limit($highlight, 15) }}</span>
                                    @endforeach
                                @else
                                    <span class="th-pill"><i class="fas fa-hotel"></i> {{ t('tour.hotel') }}</span>
                                    <span class="th-pill"><i class="fas fa-utensils"></i> {{ t('tour.meals') }}</span>
                                    <span class="th-pill"><i class="fas fa-bus"></i> {{ t('tour.transport') }}</span>
                                @endif
                            </div>

                            <div class="th-footer">
                                <div class="th-price-wrap">
                                    <span class="th-price-label">{{ t('tour.starting_from') }}</span>
                                    <span class="th-price">{{ $active_currency->currency_name }} {{ convertCurrency($tour->price, $tour->currency ?? 'USD', $active_currency->currency_name) }}</span>
                                </div>
                                <a href="{{ $detailUrl }}" class="th-view-btn">{{ t('home.view') }} <i class="fas fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    @if($umrahModule)
    <!-- FEATURED UMRAH PACKAGES SECTION -->
    @php [$thLead, $thAccent] = $thSplitTitle(t('home.umrah_featured_title')); @endphp
    <section class="th-section" id="umrahPackages">
        <div class="th-container">
            <div class="th-header">
                <div class="th-header-text">
                    <span class="th-eyebrow">{{ t('home.umrah_featured_title') }}</span>
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

                        // Get first image or use placeholder
                        $imageUrl = $package->images && $package->images->first()
                            ? asset('public/assets/images/' . $package->images->first()->image)
                            : 'https://images.unsplash.com/photo-1591604129939-f1efa4d9f7fa?w=400&h=300&fit=crop';

                        // Get first 3 highlights
                        $highlights = $package->highlights ? array_slice($package->highlights, 0, 3) : [];

                        // Calculate total nights
                        $totalNights = ($package->makkah_nights ?? 0) + ($package->madina_nights ?? 0);
                    @endphp

                    <article class="th-card">
                        <a href="{{ $detailUrl }}" class="th-card-media">
                            <img src="{{ $imageUrl }}" alt="{{ $package->name }}" loading="lazy">
                            @if($package->featured == 1)
                                <span class="th-badge"><i class="fas fa-star"></i> {{ t('home.badge_featured') }}</span>
                            @endif
                            @if($totalNights > 0)
                                <span class="th-chip"><i class="fas fa-moon"></i> {{ $totalNights }} {{ t('home.nights') }}</span>
                            @endif
                        </a>
                        <div class="th-card-body">
                            <h3 class="th-name"><a href="{{ $detailUrl }}">{{ $package->name }}</a></h3>
                            @if($package->from_location && $package->to_location)
                                <p class="th-location"><i class="fas fa-plane-departure"></i> <span>{{ $package->from_location }} <i class="fas fa-arrow-right th-route-arrow"></i> {{ $package->to_location }}</span></p>
                            @endif

                            <div class="th-pills">
                                @if(count($highlights) > 0)
                                    @foreach($highlights as $highlight)
                                        <span class="th-pill"><i class="fas fa-check-circle"></i> {{ Str::limit($highlight, 15) }}</span>
                                    @endforeach
                                @else
                                    @if($package->makkah_nights > 0)
                                        <span class="th-pill"><i class="fas fa-kaaba"></i> {{ $package->makkah_nights }} {{ t('home.nights_makkah') }}</span>
                                    @endif
                                    @if($package->madina_nights > 0)
                                        <span class="th-pill"><i class="fas fa-mosque"></i> {{ $package->madina_nights }} {{ t('home.nights_madinah') }}</span>
                                    @endif
                                    <span class="th-pill"><i class="fas fa-hotel"></i> {{ t('home.hotel') }}</span>
                                @endif
                            </div>

                            <div class="th-footer">
                                <div class="th-price-wrap">
                                    <span class="th-price-label">{{ t('home.starting_from') }}</span>
                                    <span class="th-price">{{ $active_currency->currency_name }} {{ convertCurrency($package->price, $package->currency ?? 'USD', $active_currency->currency_name) }}</span>
                                </div>
                                <a href="{{ $detailUrl }}" class="th-view-btn">{{ t('home.view') }} <i class="fas fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
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

    // Homepage card carousels: prev/next buttons scroll one card at a time
    document.querySelectorAll('.th-section').forEach(function (section) {
        var track = section.querySelector('.th-track');
        var prev = section.querySelector('.th-nav-prev');
        var next = section.querySelector('.th-nav-next');
        if (!track || !prev || !next) return;
        var rtl = getComputedStyle(track).direction === 'rtl';
        function step() {
            var card = track.querySelector('.th-card');
            var gap = parseFloat(getComputedStyle(track).columnGap) || 0;
            return card ? card.getBoundingClientRect().width + gap : track.clientWidth;
        }
        function update() {
            var max = track.scrollWidth - track.clientWidth;
            var pos = Math.abs(track.scrollLeft);
            prev.disabled = pos <= 2;
            next.disabled = pos >= max - 2;
        }
        [prev, next].forEach(function (btn) {
            btn.addEventListener('click', function () {
                var dir = parseInt(btn.getAttribute('data-th-dir'), 10) * (rtl ? -1 : 1);
                track.scrollBy({ left: dir * step(), behavior: 'smooth' });
            });
        });
        track.addEventListener('scroll', update, { passive: true });
        window.addEventListener('resize', update);
        update();
    });
</script>

@endsection
