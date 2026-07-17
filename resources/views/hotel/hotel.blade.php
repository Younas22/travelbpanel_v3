@extends('common.layout')
@section('content')
    @php

        $active_currency = activeCurrency();
    @endphp
    <style>
        /* Hero Background Image - Crystal Clear */
        .hero-section {
            background: url('<?=url('public/assets/images/flight/h4.jpg')?>') center/cover no-repeat;
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
            background: #F9FAFB;
            border-radius: 50% 50% 0 0 / 100% 100% 0 0;
            z-index: 1;
        }

        /* Hero Content */
        .hero-content {
            position: relative;
            z-index: 2;
            margin-bottom: 40px;
        }

        /* Search Form Container */
        .form-container-wrapper {
            position: relative;
            z-index: 2;
        }

        /* Hotel Cards Grid - 3 per row */
        .hotel-deals-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 24px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-section {
                min-height: 650px;
                padding-top: 40px;
            }

            .hero-section::after {
                height: 80px;
            }
        }


        .responsive-section {
    background-color: #F7F9FC;
    padding: 60px 8px; /* default for mobile */
}

@media (min-width: 1025px) {
    .responsive-section {
        padding: 60px 20px; /* for larger screens */
    }
}
    </style>


    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-content">
            <h1 style="color: white; text-shadow: 0 2px 10px rgba(0,0,0,0.3);">{{t('hotel.heroTitle')}}</h1>
            <p style="color: white; text-shadow: 0 2px 8px rgba(0,0,0,0.2);">{{t('hotel.heroSubtitle')}}</p>
        </div>

        <!-- Search Form -->
        <div class="form-container">
            <div class="tab-content active bg-white rounded-lg shadow-lg p-6 mb-8 relative z-10" id="form-flight">
            @include('forms.hotel-form')
            </div>
        </div>
    </section>
 
    <!-- Featured Hotels Section -->
    <section class="responsive-section">
        <div class="container">
            <div class="section-header">
                <h2>{{t('hotel.featuredTitle')}}</h2>
                <p>{{t('hotel.featuredSubtitle')}}</p>
            </div>

            <div class="hotel-deals-grid">
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
                        <div style="position: absolute; top: 12px; left: 12px; background: #FF6B35; color: white; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: bold;">{{ t('hotel.badgeFeatured') }}</div>
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
                            <span style="font-size: 12px; color: #6B7280;">{{ $fHotel->stars }} {{ t('hotel.stars') }}</span>
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
                                <span style="font-size: 12px; color: #6B7280;">{{ t('hotel.startingFrom') }}</span>
                                @if($fMinPrice > 0)
                                    <p style="font-size: 20px; font-weight: bold; color: #0077BE; margin: 0;">{{ $active_currency->currency_name }} {{ number_format(convertCurrency($fMinPrice, "USD", $active_currency->currency_name), 0) }}</p>
                                @else
                                    <p style="font-size: 14px; color: #6B7280; margin: 0;">{{ t('hotel.contactForPrice') }}</p>
                                @endif
                            </div>
                            <button style="background-color: #0077BE; color: white; border: none; padding: 10px 16px; border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: bold; transition: all 0.3s ease;" onmouseover="this.style.backgroundColor='#0066A1'" onmouseout="this.style.backgroundColor='#0077BE'">{{ t('hotel.viewDeal') }}</button>
                        </div>
                    </div>
                </div>
                </a>
                @empty
                <p style="grid-column: 1/-1; text-align:center; color:#6B7280;">{{ t('hotel.noFeaturedHotels') }}</p>
                @endforelse
            </div>

            <!-- View All Button -->
            <div class="cta-section">
                <a href="#" class="cta-button">
                    <i class="fas fa-hotel"></i> {{t('hotel.viewAllHotels')}}
                </a>
            </div>
        </div>
    </section>

    <!-- Why Choose Us Section -->
    <section class="how-it-works">
        <div class="section-header">
            <h2>{{t('hotel.whyBookTitle')}}</h2>
            <p>{{t('hotel.whyBookSubtitle')}}</p>
        </div>

        <div class="steps-container">
            <div class="step-card">
                <div class="step-number">
                    <i class="fas fa-search step-icon"></i>
                </div>
                <h3 class="step-title">{{t('hotel.easySearchTitle')}}</h3>
                <p class="step-description">{{t('hotel.easySearchDesc')}}</p>
            </div>

            <div class="step-card">
                <div class="step-number">
                    <i class="fas fa-tags step-icon"></i>
                </div>
                <h3 class="step-title">{{t('hotel.bestPricesTitle')}}</h3>
                <p class="step-description">{{t('hotel.bestPricesDesc')}}</p>
            </div>

            <div class="step-card">
                <div class="step-number">
                    <i class="fas fa-shield-alt step-icon"></i>
                </div>
                <h3 class="step-title">{{t('hotel.secureBookingTitle')}}</h3>
                <p class="step-description">{{t('hotel.secureBookingDesc')}}</p>
            </div>

            <div class="step-card">
                <div class="step-number">
                    <i class="fas fa-headset step-icon"></i>
                </div>
                <h3 class="step-title">{{t('hotel.supportTitle')}}</h3>
                <p class="step-description">{{t('hotel.supportDesc')}}</p>
            </div>
        </div>
    </section>

@endsection
