@extends('common.layout')
@section('content')
    @php

        $active_currency = activeCurrency();
    @endphp
    <style>
        /* Hero Background Image - Crystal Clear */
        .hero-section {
            background: url('<?=url('public/assets/images/flight/flight2.jpg')?>') center/cover no-repeat;
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

        /* Flight Cards Grid - 3 per row */
        .flight-deals-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 24px;
            max-width: 1200px;
            margin: 0 auto;
        }

        @media (min-width: 1024px) {
            .flight-deals-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        /* Clean Minimal Flight Card */
        .flight-deal-card {
            background: white;
            border-radius: 16px;
            padding: 0; /* Changed from 24px to 0 */
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            text-decoration: none;
            display: block;
            border: 1px solid #E5E7EB;
            position: relative;
            overflow: hidden; /* Added */
        }

        .flight-deal-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 24px rgba(0, 119, 190, 0.15);
            border-color: #0077BE;
        }

        /* Card Image Styling */
        .card-image {
            width: 100%;
            height: 180px;
            overflow: hidden;
            border-radius: 16px 16px 0 0;
            margin: 0; /* Changed */
        }

        .card-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .flight-deal-card:hover .card-image img {
            transform: scale(1.1);
        }

        /* Card Layout */
        .card-inner {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 24px; /* Moved padding here */
        }

        /* From City Section */
        .from-section {
            flex: 1;
            text-align: left;
        }

        .city-name {
            font-size: 20px;
            font-weight: 700;
            color: #1F2937;
            margin-bottom: 4px;
            line-height: 1.2;
        }

        .airline-name {
            font-size: 13px;
            color: #6B7280;
            font-weight: 500;
        }

        /* Arrow/Plane Icon */
        .flight-separator {
            flex: 0 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .plane-icon-wrapper {
            width: 40px;
            height: 40px;
            background: #F0F9FF;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .flight-deal-card:hover .plane-icon-wrapper {
            background: #0077BE;
            transform: scale(1.1);
        }

        .plane-icon-wrapper i {
            color: #0077BE;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .flight-deal-card:hover .plane-icon-wrapper i {
            color: white;
        }

        /* To City Section */
        .to-section {
            flex: 1;
            text-align: right;
        }

        .destination-name {
            font-size: 20px;
            font-weight: 700;
            color: #1F2937;
            margin-bottom: 4px;
            line-height: 1.2;
        }

        .price-info {
            font-size: 13px;
            color: #6B7280;
            font-weight: 500;
        }

        .price-amount {
            font-weight: 700;
            color: #0077BE;
            font-size: 16px;
        }

        /* Subtle hover effect on text */
        .flight-deal-card:hover .city-name,
        .flight-deal-card:hover .destination-name {
            color: #0077BE;
        }
    </style>


    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-content">
            <h1 style="color: white; text-shadow: 0 2px 10px rgba(0,0,0,0.3);">{{ t('flight.hero_title') }}</h1>
            <p style="color: white; text-shadow: 0 2px 8px rgba(0,0,0,0.2);">{{ t('flight.hero_subtitle') }}</p>
        </div>

<!-- Search Form -->
    <div class="form-container">
        <div class="tab-content active bg-white rounded-lg shadow-lg p-6 mb-8 relative z-10" id="form-flight">
        @include('forms.flight-form')
        </div>
    </div>

</section>

    <!-- Featured Flights Section -->
    <section style="background: linear-gradient(180deg, #F9FAFB 0%, #F3F4F6 100%); padding: 80px 20px;">
        <div class="container">
            <div class="section-header">
                <h2>{{ t('flight.featured_flight_deals') }}</h2>
                <p>{{ t('flight.featured_flight_subtitle') }}</p>
            </div>

            <div class="flight-deals-grid">
                <!-- Flight Card 1 -->
                <a href="#" class="flight-deal-card">
                    <div class="card-image">
                        <img src="https://images.unsplash.com/photo-1512453979798-5ea266f8880c?w=400&h=200&fit=crop" alt="Dubai">
                    </div>
                    <div class="card-inner">
                        <div class="from-section">
                            <div class="city-name">Lahore</div>
                            <div class="airline-name">Pakistan International</div>
                        </div>

                        <div class="flight-separator">
                            <div class="plane-icon-wrapper">
                                <i class="fas fa-plane"></i>
                            </div>
                        </div>

                        <div class="to-section">
                            <div class="destination-name">Dubai</div>
                            <div class="price-info">From <span class="price-amount">{{$active_currency->currency_name}} {{convertCurrency(100, "USD", $active_currency->currency_name)}}</span></div>
                        </div>
                    </div>
                </a>

                <!-- Flight Card 2 -->
                <a href="#" class="flight-deal-card">
                    <div class="card-image">
                        <img src="https://images.unsplash.com/photo-1596422846543-75c6fc197f07?w=400&h=200&fit=crop" alt="Kuala Lumpur">
                    </div>
                    <div class="card-inner">
                        <div class="from-section">
                            <div class="city-name">Kuala Lumpur</div>
                            <div class="airline-name">Malaysia Airlines</div>
                        </div>

                        <div class="flight-separator">
                            <div class="plane-icon-wrapper">
                                <i class="fas fa-plane"></i>
                            </div>
                        </div>

                        <div class="to-section">
                            <div class="destination-name">Dubai</div>
                            <div class="price-info">From <span class="price-amount">{{$active_currency->currency_name}} {{convertCurrency(620, "USD", $active_currency->currency_name)}}</span></div>
                        </div>
                    </div>
                </a>

                <!-- Flight Card 3 -->
                <a href="#" class="flight-deal-card">
                    <div class="card-image">
                        <img src="https://images.unsplash.com/photo-1527838832700-5059252407fa?w=400&h=200&fit=crop" alt="Berlin to Istanbul">
                    </div>
                    <div class="card-inner">
                        <div class="from-section">
                            <div class="city-name">Berlin</div>
                            <div class="airline-name">Turkish Airlines</div>
                        </div>

                        <div class="flight-separator">
                            <div class="plane-icon-wrapper">
                                <i class="fas fa-plane"></i>
                            </div>
                        </div>

                        <div class="to-section">
                            <div class="destination-name">Istanbul</div>
                            <div class="price-info">From <span class="price-amount">{{$active_currency->currency_name}} {{convertCurrency(600, "USD", $active_currency->currency_name)}}</span></div>
                        </div>
                    </div>
                </a>

                <!-- Flight Card 4 -->
                <a href="#" class="flight-deal-card">
                    <div class="card-image">
                        <img src="https://images.unsplash.com/photo-1582672060674-bc2bd808a8b5?w=400&h=200&fit=crop" alt="Dubai">
                    </div>
                    <div class="card-inner">
                        <div class="from-section">
                            <div class="city-name">Dubai</div>
                            <div class="airline-name">Emirates</div>
                        </div>

                        <div class="flight-separator">
                            <div class="plane-icon-wrapper">
                                <i class="fas fa-plane"></i>
                            </div>
                        </div>

                        <div class="to-section">
                            <div class="destination-name">Sharjah</div>
                            <div class="price-info">From <span class="price-amount">{{$active_currency->currency_name}} {{convertCurrency(460, "USD", $active_currency->currency_name)}}</span></div>
                        </div>
                    </div>
                </a>

                <!-- Flight Card 5 -->
                <a href="#" class="flight-deal-card">
                    <div class="card-image">
                        <img src="https://images.unsplash.com/photo-1591604021695-0c69b7c05981?w=400&h=200&fit=crop" alt="Dhaka to Jeddah">
                    </div>
                    <div class="card-inner">
                        <div class="from-section">
                            <div class="city-name">Dhaka</div>
                            <div class="airline-name">Australian Airlines</div>
                        </div>

                        <div class="flight-separator">
                            <div class="plane-icon-wrapper">
                                <i class="fas fa-plane"></i>
                            </div>
                        </div>

                        <div class="to-section">
                            <div class="destination-name">Jeddah</div>
                            <div class="price-info">From <span class="price-amount">{{$active_currency->currency_name}} {{convertCurrency(385, "USD", $active_currency->currency_name)}}</span></div>
                        </div>
                    </div>
                </a>

                <!-- Flight Card 6 -->
                <a href="#" class="flight-deal-card">
                    <div class="card-image">
                        <img src="https://images.unsplash.com/photo-1513326738677-b964603b136d?w=400&h=200&fit=crop" alt="Delhi to Moscow">
                    </div>
                    <div class="card-inner">
                        <div class="from-section">
                            <div class="city-name">Delhi</div>
                            <div class="airline-name">Air India Limited</div>
                        </div>

                        <div class="flight-separator">
                            <div class="plane-icon-wrapper">
                                <i class="fas fa-plane"></i>
                            </div>
                        </div>

                        <div class="to-section">
                            <div class="destination-name">Moscow</div>
                            <div class="price-info">From <span class="price-amount">{{$active_currency->currency_name}} {{convertCurrency(760, "USD", $active_currency->currency_name)}}</span></div>
                        </div>
                    </div>
                </a>

                <!-- Flight Card 7 -->
                <a href="#" class="flight-deal-card">
                    <div class="card-image">
                        <img src="https://images.unsplash.com/photo-1563789031959-4c02bcb41319?w=400&h=200&fit=crop" alt="Manila">
                    </div>
                    <div class="card-inner">
                        <div class="from-section">
                            <div class="city-name">Manila</div>
                            <div class="airline-name">Air Philippines</div>
                        </div>

                        <div class="flight-separator">
                            <div class="plane-icon-wrapper">
                                <i class="fas fa-plane"></i>
                            </div>
                        </div>

                        <div class="to-section">
                            <div class="destination-name">Dubai</div>
                            <div class="price-info">From <span class="price-amount">{{$active_currency->currency_name}} {{convertCurrency(450, "USD", $active_currency->currency_name)}}</span></div>
                        </div>
                    </div>
                </a>

                <!-- Flight Card 8 -->
                <a href="#" class="flight-deal-card">
                    <div class="card-image">
                        <img src="https://images.unsplash.com/photo-1496442226666-8d4d0e62e6e9?w=400&h=200&fit=crop" alt="New York">
                    </div>
                    <div class="card-inner">
                        <div class="from-section">
                            <div class="city-name">Surabaya</div>
                            <div class="airline-name">American Airlines</div>
                        </div>

                        <div class="flight-separator">
                            <div class="plane-icon-wrapper">
                                <i class="fas fa-plane"></i>
                            </div>
                        </div>

                        <div class="to-section">
                            <div class="destination-name">New York</div>
                            <div class="price-info">From <span class="price-amount">{{$active_currency->currency_name}} {{convertCurrency(800, "USD", $active_currency->currency_name)}}</span></div>
                        </div>
                    </div>
                </a>

                <!-- Flight Card 9 -->
                <a href="#" class="flight-deal-card">
                    <div class="card-image">
                        <img src="https://images.unsplash.com/photo-1518684079-3c830dcef090?w=400&h=200&fit=crop" alt="Berlin to Dubai">
                    </div>
                    <div class="card-inner">
                        <div class="from-section">
                            <div class="city-name">Berlin</div>
                            <div class="airline-name">Air Arabia</div>
                        </div>

                        <div class="flight-separator">
                            <div class="plane-icon-wrapper">
                                <i class="fas fa-plane"></i>
                            </div>
                        </div>

                        <div class="to-section">
                            <div class="destination-name">Dubai</div>
                            <div class="price-info">From <span class="price-amount">{{$active_currency->currency_name}} {{convertCurrency(240, "USD", $active_currency->currency_name)}}</span></div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- View All Button -->
            <div class="cta-section">
                <a href="#" class="cta-button">
                    <i class="fas fa-plane-departure"></i> {{ t('flight.view_all_flights') }}
                </a>
            </div>
        </div>
    </section>

    <!-- Why Choose Us Section -->
    <section class="how-it-works">
        <div class="section-header">
            <h2>{{ t('flight.why_book_with_us') }}</h2>
            <p>{{ t('flight.why_book_subtitle') }}</p>
        </div>

        <div class="steps-container">
            <div class="step-card">
                <div class="step-number">
                    <i class="fas fa-search step-icon"></i>
                </div>
                <h3 class="step-title">{{ t('flight.easy_search') }}</h3>
                <p class="step-description">{{ t('flight.easy_search_desc') }}</p>
            </div>

            <div class="step-card">
                <div class="step-number">
                    <i class="fas fa-tags step-icon"></i>
                </div>
                <h3 class="step-title">{{ t('flight.best_prices') }}</h3>
                <p class="step-description">{{ t('flight.best_prices_desc') }}</p>
            </div>

            <div class="step-card">
                <div class="step-number">
                    <i class="fas fa-lock step-icon"></i>
                </div>
                <h3 class="step-title">{{ t('flight.secure_booking') }}</h3>
                <p class="step-description">{{ t('flight.secure_booking_desc') }}</p>
            </div>

            <div class="step-card">
                <div class="step-number">
                    <i class="fas fa-headset step-icon"></i>
                </div>
                <h3 class="step-title">{{ t('flight.support_24_7') }}</h3>
                <p class="step-description">{{ t('flight.support_24_7_desc') }}</p>
            </div>
        </div>
    </section>

@endsection
