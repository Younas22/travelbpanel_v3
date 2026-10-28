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
    /* Hero layout and the search panel live in style.css (#siteRedesign);
       only this page's cover image is set here. */
    #siteRedesign .hero-section {
        background-image: url('<?=getSettingImage('cover_image', 'homepage')?>');
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
        font-weight: 400;
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
        font-size: 14px;
    }

    .tab-btn:hover {
        color: #fff;
        background: rgba(255, 255, 255, 0.2);
    }

    /* the selected module's name stands out from the others */
    .tab-btn.active {
        color: #000;
        font-weight: 600;
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
    #siteRedesign .cta-button,
    #siteRedesign .view-deal-btn,
    #siteRedesign .view-deal-btn-umrah {
        background: #0346FA;
    }

    #siteRedesign .view-deal-btn:hover,
    #siteRedesign .view-deal-btn-umrah:hover {
        box-shadow: 0 4px 12px rgba(3,70,250,0.3);
    }

    #siteRedesign .step-number {
        background: #0346FA;
    }

    #siteRedesign .step-card:hover .step-number {
        box-shadow: 0 8px 20px rgba(3,70,250,0.3);
    }

    #siteRedesign .connector {
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
    @media (max-width: 640px) {
        .tab-buttons-wrapper {
            width: calc(100% - 24px);
            margin-bottom: 8px;
        }

        /* phones: wrap the tabs onto rows so every module is visible and
           tappable, instead of hiding some off-screen in a sideways scroll */
        .tab-buttons {
            flex-wrap: wrap;
            justify-content: center;
            padding: 4px;
            border-radius: 22px;
            overflow: visible;
        }

        .tab-buttons > .tab-btn:first-child,
        .tab-buttons > .tab-btn:last-child {
            margin-left: 0;
            margin-right: 0;
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

<div id="siteRedesign" class="has-ai-search">
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
                    @endphp

                    @include('common.umrah-card', [
                        'packagePrice' => $active_currency->currency_name . ' ' . convertCurrency($package->price, $package->currency ?? 'USD', $active_currency->currency_name),
                        'umrahLabels' => ['featured' => 'home.badge_featured', 'nights' => 'home.nights', 'makkah' => 'home.nights_makkah', 'madinah' => 'home.nights_madinah', 'hotel' => 'home.hotel', 'from' => 'home.starting_from'],
                    ])
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

    <!-- MOBILE APP SHOWCASE SECTION -->
    <section class="app-showcase" id="appShowcase">
        <div class="th-container app-showcase-inner">
            <div class="app-showcase-text">
                <span class="th-eyebrow">TravelBookingPanel Mobile App</span>
                <h2 class="app-showcase-title" dir="auto">Your Complete Travel Booking Solution, Now <span class="app-showcase-accent">in Your Pocket</span></h2>
                <p class="app-showcase-desc" dir="auto">Book flights, hotels, tours, visas, and more with the TravelBookingPanel mobile experience. Explore destinations, manage your bookings, and access essential travel services from one convenient place.</p>
                <ul class="app-showcase-features">
                    <li><span class="app-showcase-icon"><i class="fas fa-plane"></i></span> Flights &amp; Hotel Booking</li>
                    <li><span class="app-showcase-icon"><i class="fas fa-kaaba"></i></span> Tours, Visa &amp; Umrah</li>
                    <li><span class="app-showcase-icon"><i class="fas fa-ticket"></i></span> Manage Your Bookings</li>
                    <li><span class="app-showcase-icon"><i class="fas fa-mobile-screen-button"></i></span> Travel Services in One App</li>
                </ul>
            </div>
            <div class="app-showcase-media">
                <img src="{{ asset('public/assets/images/app/travel-booking-app-screens.png') }}" alt="TravelBookingPanel mobile app: hotel search, home and flight search screens" width="1536" height="1024" loading="lazy">
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

</script>

@endsection
