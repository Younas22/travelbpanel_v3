@extends('common.layout')
@section('content')

@php
    $activeModules = getActiveModule(); // All active modules
    $active_currency = activeCurrency();
@endphp

<style>
    /* Hero Background Image - Crystal Clear */
    .hero-section {
        background: url('<?=getSettingImage('cover_image', 'homepage')?>') center/cover no-repeat;
        min-height: 700px;
        position: relative;
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
        padding-top: 60px;
    }

    /* Curved Bottom Shape */
    .hero-section::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 150px;
        /* background: #F5F7FA; */
        background: #fff;
        border-radius: 50% 50% 0 0 / 100% 100% 0 0;
        z-index: 1;
    }

    .form-container {
        background-color: white;
        border-radius: 12px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
        width: 100%;
        max-width: 1000px;
        z-index: 20;
        position: relative;
        margin: 0 auto;
    }

    /* Fix for dropdowns on home page - ensure they appear above form container */
    .form-container .destination-dropdown,
    .form-container .tour-type-dropdown,
    .form-container .traveler-dropdown {
        z-index: 150 !important;
    }

    .form-container .dropdown-overlay {
        z-index: 140 !important;
    }

    /* Tab Styles */
    .tab-buttons {
        display: inline-flex;
        justify-content: center;
        gap: 8px;
        padding: 10px 12px;
        background: #f0f4f8;
        border-radius: 50px;
        flex-wrap: wrap;
        margin: 16px auto 0;
        width: auto;
    }

    .form-container .tab-buttons-wrapper {
        text-align: center;
    }

    .tab-btn {
        padding: 10px 20px;
        background: white;
        border: 2px solid #e2e8f0;
        font-weight: 600;
        font-size: 14px;
        color: #64748b;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        white-space: nowrap;
        border-radius: 50px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    }

    .tab-btn:hover {
        color: #0077BE;
        background: white;
        border-color: #0077BE;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0,119,190,0.15);
    }

    .tab-btn.active {
        color: white;
        background: linear-gradient(135deg, #0077BE, #005a8e);
        border-color: transparent;
        box-shadow: 0 4px 14px rgba(0,119,190,0.35);
        transform: translateY(-1px);
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
    @media (max-width: 768px) {
        .tab-buttons {
            gap: 6px;
            padding: 12px 14px;
        }

        .tab-btn {
            padding: 8px 14px;
            font-size: 13px;
            gap: 5px;
        }

        .tab-btn i {
            font-size: 13px;
        }
    }

    @media (max-width: 640px) {
        .tab-buttons {
            gap: 5px;
            padding: 10px 10px;
        }

        .tab-btn {
            padding: 7px 12px;
            font-size: 12px;
            gap: 4px;
        }

        .tab-btn i {
            font-size: 12px;
        }
    }
</style>

<section class="hero-section">
    <div class="hero-content">
        <h1 style="color: white; text-shadow: 0 2px 10px rgba(0,0,0,0.3);">
            {{ t('home.hero_title') }}
        </h1>
        <p style="color: white; text-shadow: 0 2px 8px rgba(0,0,0,0.2);">
            {{ t('home.hero_subtitle') }}
        </p>
    </div>

    <!-- Search Form Container -->
    <div class="form-container">
        <!-- Tab Buttons -->
        <div class="tab-buttons-wrapper">
        <div class="tab-buttons">
            @php $first = true; @endphp
            @foreach($activeModules as $module)
                @if($module->slug === 'visa')
                    <!-- Visa as a link, not a button -->
                    <a href="{{ route('visa.create') }}" class="tab-btn"
                    style="text-decoration: none;">
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
        </div>
        </div>

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
@endphp
    <!-- FEATURED HOTELS SECTION -->
    <div style="background-color: white; padding: 60px 20px; {{ $hotelModule ? '' : 'display:none;' }}">
        <div style="max-width: 1200px; margin: 0 auto;">

            <!-- Section Header -->
            <div style="text-align: center; margin-bottom: 50px;">
                <h2 style="font-size: 32px; font-weight: bold; color: #003580; margin-bottom: 10px;">{{ t('home.trending_hotels') }}</h2>
                <p style="font-size: 16px; color: #6B7280;">{{ t('home.trending_hotels_sub') }}</p>
            </div>

            <!-- Hotels Grid -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px;">

                @php
                    $checkin  = date('Y-m-d', strtotime('+1 day'));
                    $checkout = date('Y-m-d', strtotime('+2 days'));
                @endphp

                @forelse($featuredHotels as $fHotel)
                @php
                    $fImage = $fHotel->images->first()
                        ? asset('public/assets/images/' . $fHotel->images->first()->image_path)
                        : 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=400&h=300&fit=crop';
                    $fLocation = $fHotel->location ? $fHotel->location->city . ', ' . $fHotel->location->country : $fHotel->address;
                    $fAmenities = $fHotel->amenities->take(3);
                    $fSlug = \Illuminate\Support\Str::slug(\Illuminate\Support\Str::limit($fHotel->name, 30), '-');
                    $fDetailUrl = url("hotel/details/{$fHotel->id}/{$fSlug}/{$checkin}/{$checkout}/2/0/1/manual");
                    $fMinPrice = $fHotel->roomTypes()->where('status', 1)->min('price_per_night') ?? 0;
                @endphp
                <a href="{{ $fDetailUrl }}" style="text-decoration: none;">
                <div style="background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08); transition: all 0.3s ease; cursor: pointer;" onmouseover="this.style.boxShadow='0 12px 32px rgba(0, 119, 190, 0.15)'; this.style.transform='translateY(-8px)'" onmouseout="this.style.boxShadow='0 2px 12px rgba(0, 0, 0, 0.08)'; this.style.transform='translateY(0)'">
                    <div style="position: relative; overflow: hidden; height: 220px;">
                        <img src="{{ $fImage }}" alt="{{ $fHotel->name }}" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;" onmouseover="this.style.transform='scale(1.08)'" onmouseout="this.style.transform='scale(1)'">
                        <div style="position: absolute; top: 12px; left: 12px; background: #FF6B35; color: white; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: bold;">{{ t('home.badge_featured') }}</div>
                    </div>
                    <div style="padding: 20px;">
                        <h3 style="font-size: 18px; font-weight: bold; color: #003580; margin-bottom: 6px;">{{ $fHotel->name }}</h3>
                        <p style="font-size: 13px; color: #6B7280; margin-bottom: 12px;">{{ $fLocation }}</p>

                        <!-- Rating -->
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
                            <div style="display: flex; gap: 2px;">
                                @for($s = 1; $s <= 5; $s++)
                                    <span style="color: {{ $s <= $fHotel->stars ? '#FF6B35' : '#D1D5DB' }};">★</span>
                                @endfor
                            </div>
                            <span style="font-size: 12px; color: #6B7280;">{{ $fHotel->stars }} {{ t('home.stars') }}</span>
                        </div>

                        <!-- Amenities -->
                        <div style="display: flex; gap: 8px; margin-bottom: 14px; flex-wrap: wrap;">
                            @foreach($fAmenities as $fAmenity)
                                <span style="background-color: #F0F4F8; color: #0077BE; padding: 4px 8px; border-radius: 4px; font-size: 11px;">{{ $fAmenity->name }}</span>
                            @endforeach
                        </div>

                        <!-- Price -->
                        <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 12px; border-top: 1px solid #F0F4F8;">
                            <div>
                                <span style="font-size: 12px; color: #6B7280;">{{ t('home.starting_from') }}</span>
                                @if($fMinPrice > 0)
                                    <p style="font-size: 20px; font-weight: bold; color: #0077BE; margin: 0;">{{ $active_currency->currency_name }} {{ number_format($fMinPrice, 0) }}</p>
                                @else
                                    <p style="font-size: 14px; color: #6B7280; margin: 0;">{{ t('home.contact_for_price') }}</p>
                                @endif
                            </div>
                            <button style="background-color: #0077BE; color: white; border: none; padding: 10px 16px; border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: bold; transition: all 0.3s ease;" onmouseover="this.style.backgroundColor='#0066A1'" onmouseout="this.style.backgroundColor='#0077BE'">{{ t('home.view') }}</button>
                        </div>
                    </div>
                </div>
                </a>
                @empty
                    <p style="grid-column: 1/-1; text-align:center; color:#6B7280;">{{ t('home.no_featured_hotels') }}</p>
                @endforelse

            </div>

            <!-- View All Button -->
            <!-- <div style="text-align: center; margin-top: 50px;">
                <button style="background-color: #0077BE; color: white; border: none; padding: 14px 48px; font-size: 15px; font-weight: bold; border-radius: 6px; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 8px rgba(0, 119, 190, 0.15);" onmouseover="this.style.backgroundColor='#0066A1'; this.style.boxShadow='0 4px 12px rgba(0, 119, 190, 0.3)'; this.style.transform='translateY(-2px)'" onmouseout="this.style.backgroundColor='#0077BE'; this.style.boxShadow='0 2px 8px rgba(0, 119, 190, 0.15)'; this.style.transform='translateY(0)'">
                    Explore More Hotels
                </button>
            </div> -->
        </div>
    </div>

@php
    $toursModule = getActiveModule('tours'); // active or null
@endphp
    <!-- FEATURED TOURS SECTION -->
    <section class="responsive-section" style="{{ $toursModule ? '' : 'display:none;' }}">
        <div class="container" style="max-width: 1200px; margin: 0 auto;">
            <div class="section-header">
                <h2>{{ t('tour.find_popular_tours') }}</h2>
                <p>{{ t('tour.find_popular_tours_subtitle') }}</p>
            </div>

            <div class="tour-deals-grid">
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

                    <a href="{{ $detailUrl }}" class="tour-deal-card">
                        <div class="tour-card-image">
                            <img src="{{ $imageUrl }}" alt="{{ $tour->name }}">

                            @if($tour->featured == 1)
                                <div class="popular-badge">{{ t('tour.popular') }}</div>
                            @endif

                            @if($tour->days)
                                <div class="duration-badge">
                                    <i class="fas fa-clock"></i> {{ $tour->days }} {{ $tour->days > 1 ? t('tour.days') : t('tour.day') }}
                                </div>
                            @endif
                        </div>
                        <div class="tour-card-body">
                            <h3 class="tour-name">{{ $tour->name }}</h3>

                            @if($tour->location_name)
                                <div class="tour-location">
                                    <i class="fas fa-map-marker-alt"></i> {{ $tour->location_name }}
                                </div>
                            @endif

                            <div class="tour-features">
                                @if(count($highlights) > 0)
                                    @foreach($highlights as $highlight)
                                        <span class="feature-item">
                                            <i class="fas fa-check-circle"></i> {{ Str::limit($highlight, 15) }}
                                        </span>
                                    @endforeach
                                @else
                                    <span class="feature-item"><i class="fas fa-hotel"></i> {{ t('tour.hotel') }}</span>
                                    <span class="feature-item"><i class="fas fa-utensils"></i> {{ t('tour.meals') }}</span>
                                    <span class="feature-item"><i class="fas fa-bus"></i> {{ t('tour.transport') }}</span>
                                @endif
                            </div>

                            <div class="tour-price-section">
                                <div class="price-info">
                                    <div class="price-label">{{ t('tour.starting_from') }}</div>
                                    <div class="price-amount">
                                        <span class="price-currency">{{$active_currency->currency_name}} </span> {{convertCurrency($tour->price, $tour->currency ?? 'USD', $active_currency->currency_name)}}
                                    </div>
                                </div>
                                <button class="view-deal-btn">{{ t('tour.view_details') }}</button>
                            </div>
                        </div>
                    </a>
                @endforeach

            </div>

            <!-- View All Button -->
            <div class="cta-section">
                <a href="{{ route('tours.index') }}" class="cta-button">
                    <i class="fas fa-globe"></i> {{ t('tour.view_all_tours') }}
                </a>
            </div>
        </div>
    </section>

@php
    $umrahModule = getActiveModule('umrah'); // active or null
@endphp
    <!-- FEATURED UMRAH PACKAGES SECTION -->
    <section class="responsive-section-umrah" style="{{ $umrahModule ? '' : 'display:none;' }}">
        <div class="container" style="max-width: 1200px; margin: 0 auto;">
            <div class="section-header">
                <h2>{{ t('home.umrah_featured_title') }}</h2>
                <p>{{ t('home.umrah_featured_sub') }}</p>
            </div>

            <div class="umrah-deals-grid">
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

                    <a href="{{ $detailUrl }}" class="umrah-deal-card">
                        <div class="umrah-card-image">
                            <img src="{{ $imageUrl }}" alt="{{ $package->name }}">

                            @if($package->featured == 1)
                                <div class="popular-badge-umrah">{{ t('home.badge_featured') }}</div>
                            @endif

                            @if($totalNights > 0)
                                <div class="duration-badge-umrah">
                                    <i class="fas fa-moon"></i> {{ $totalNights }} {{ t('home.nights') }}
                                </div>
                            @endif
                        </div>
                        <div class="umrah-card-body">
                            <h3 class="umrah-name">{{ $package->name }}</h3>

                            @if($package->from_location && $package->to_location)
                                <div class="umrah-location">
                                    <i class="fas fa-plane-departure"></i> {{ $package->from_location }} <i class="fas fa-arrow-right mx-1"></i> {{ $package->to_location }}
                                </div>
                            @endif

                            <div class="umrah-features">
                                @if(count($highlights) > 0)
                                    @foreach($highlights as $highlight)
                                        <span class="feature-item-umrah">
                                            <i class="fas fa-check-circle"></i> {{ Str::limit($highlight, 15) }}
                                        </span>
                                    @endforeach
                                @else
                                    @if($package->makkah_nights > 0)
                                        <span class="feature-item-umrah"><i class="fas fa-kaaba"></i> {{ $package->makkah_nights }} {{ t('home.nights_makkah') }}</span>
                                    @endif
                                    @if($package->madina_nights > 0)
                                        <span class="feature-item-umrah"><i class="fas fa-mosque"></i> {{ $package->madina_nights }} {{ t('home.nights_madinah') }}</span>
                                    @endif
                                    <span class="feature-item-umrah"><i class="fas fa-hotel"></i> {{ t('home.hotel') }}</span>
                                @endif
                            </div>

                            <div class="umrah-price-section">
                                <div class="price-info-umrah">
                                    <div class="price-label-umrah">{{ t('home.starting_from') }}</div>
                                    <div class="price-amount-umrah">
                                        <span class="price-currency-umrah">{{$active_currency->currency_name}} </span> {{convertCurrency($package->price, $package->currency ?? 'USD', $active_currency->currency_name)}}
                                    </div>
                                </div>
                                <button class="view-deal-btn-umrah">{{ t('home.view_details') }}</button>
                            </div>
                        </div>
                    </a>
                @endforeach

            </div>

            <!-- View All Button -->
            <div class="cta-section">
                <a href="{{ route('umrah.index') }}" class="cta-button">
                    <i class="fas fa-kaaba"></i> {{ t('home.explore_all_packages') }}
                </a>
            </div>
        </div>
    </section>


    <section class="how-it-works">
            <div class="section-header">
                <h2>{{ t('home.how_it_work') }}</h2>
                <p>{{ t('home.howitwork_sub_one') }} <span class="accent-text">{{ t('home.howitwork_sub_two') }}</span>. {{ t('home.howitwork_sub_three') }}</p>
            </div>

            <div class="steps-container">
                <!-- Step 1 -->
                <div class="step-card">
                    <div class="step-number">1</div>
                    <div class="step-icon">🔍</div>
                    <h3 class="step-title">{{t('home.step_1_title')}}</h3>
                    <p class="step-description">{{ t('home.step_1_desc') }}</p>
                    <div class="connector"></div>
                </div>

                <!-- Step 2 -->
                <div class="step-card">
                    <div class="step-number">2</div>
                    <div class="step-icon">⭐</div>
                    <h3 class="step-title">{{t('home.step_2_title')}}</h3>
                    <p class="step-description">{{ t('home.step_2_desc') }}</p>
                    <div class="connector"></div>
                </div>

                <!-- Step 3 -->
                <div class="step-card">
                    <div class="step-number">3</div>
                    <div class="step-icon">💳</div>
                    <h3 class="step-title">{{t('home.step_3_title')}}</h3>
                    <p class="step-description">{{ t('home.step_3_desc') }}</p>
                    <div class="connector"></div>
                </div>

                <!-- Step 4 -->
                <div class="step-card">
                    <div class="step-number">4</div>
                    <div class="step-icon">🎉</div>
                    <h3 class="step-title">{{t('home.step_4_title')}}</h3>
                    <p class="step-description">{{ t('home.step_4_desc') }}</p>
                </div>
            </div>

            <div class="cta-section">
                <button class="cta-button" id="start_booking_now">{{t('home.start_booking_now')}} →</button>
            </div>
    </section>

<script>
    // Scroll to hero-section when start_booking_now button is clicked
    document.addEventListener('DOMContentLoaded', function() {
        const startBookingBtn = document.getElementById('start_booking_now');
        if (startBookingBtn) {
            startBookingBtn.addEventListener('click', function() {
                const heroSection = document.querySelector('.hero-section');
                if (heroSection) {
                    heroSection.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        }
    });
</script>

@endsection
