@extends('common.layout')
@section('content')

    <!-- Hero Section -->
    <section class="hero-section umrah-page">
        <div class="hero-content">
            <h1>{{t('umrah.heroTitle')}}</h1>
            <p>{{t('umrah.heroSubtitle')}}</p>
        </div>

        <!-- Search Form -->
        <div class="form-container">
            <div class="tab-content active bg-white rounded-lg shadow-lg mb-8 relative z-10" id="form-umrah">
                @include('forms.umrah-form')
            </div>
        </div>
    </section>

    <!-- Why Choose Us Section -->
    <section class="how-it-works">
        <div class="section-header">
            <h2>{{t('umrah.whyBookTitle')}}</h2>
            <p>{{t('umrah.whyBookSubtitle')}}</p>
        </div>

        <div class="steps-container">
            <div class="step-card">
                <div class="step-number">
                    <i class="fas fa-kaaba step-icon"></i>
                </div>
                <h3 class="step-title">{{t('umrah.sacredAccommodations')}}</h3>
                <p class="step-description">{{t('umrah.sacredAccommodationsDesc')}}</p>
            </div>

            <div class="step-card">
                <div class="step-number">
                    <i class="fas fa-user-tie step-icon"></i>
                </div>
                <h3 class="step-title">{{t('umrah.expertGuides')}}</h3>
                <p class="step-description">{{t('umrah.expertGuidesDesc')}}</p>
            </div>

            <div class="step-card">
                <div class="step-number">
                    <i class="fas fa-plane-departure step-icon"></i>
                </div>
                <h3 class="step-title">{{t('umrah.hassleFreeTravel')}}</h3>
                <p class="step-description">{{t('umrah.hassleFreeTravelDesc')}}</p>
            </div>

            <div class="step-card">
                <div class="step-number">
                    <i class="fas fa-headset step-icon"></i>
                </div>
                <h3 class="step-title">{{t('umrah.support247')}}</h3>
                <p class="step-description">{{t('umrah.support247Desc')}}</p>
            </div>
        </div>
    </section>

    <!-- Popular Umrah Packages Section -->
    <section class="responsive-section-umrah">
        <div class="container">
            <div class="section-header">
                <h2>{{t('umrah.featuredPackagesTitle')}}</h2>
                <p>{{t('umrah.featuredPackagesSubtitle')}}</p>
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
                                <div class="popular-badge-umrah">{{t('umrah.featured')}}</div>
                            @endif

                            @if($totalNights > 0)
                                <div class="duration-badge-umrah">
                                    <i class="fas fa-moon"></i> {{ $totalNights }} {{t('umrah.nights')}}
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
                                        <span class="feature-item-umrah"><i class="fas fa-kaaba"></i> {{ $package->makkah_nights }} {{t('umrah.nightsMakkah')}}</span>
                                    @endif
                                    @if($package->madina_nights > 0)
                                        <span class="feature-item-umrah"><i class="fas fa-mosque"></i> {{ $package->madina_nights }} {{t('umrah.nightsMadinah')}}</span>
                                    @endif
                                    <span class="feature-item-umrah"><i class="fas fa-hotel"></i> {{t('umrah.hotel')}}</span>
                                @endif
                            </div>

                            <div class="umrah-price-section">
                                <div class="price-info-umrah">
                                    <div class="price-label-umrah">{{t('umrah.startingFrom')}}</div>
                                    <div class="price-amount-umrah">
                                        <span class="price-currency-umrah">{{ $package->currency ?? 'USD' }}</span> {{ number_format($package->price, 0) }}
                                    </div>
                                </div>
                                <button class="view-deal-btn-umrah">{{t('umrah.viewDetails')}}</button>
                            </div>
                        </div>
                    </a>
                @endforeach

            </div>

            <!-- View All Button -->
            <div class="cta-section">
                <a href="#form-umrah" class="cta-button">
                    <i class="fas fa-kaaba"></i> {{t('umrah.exploreAllPackages')}}
                </a>
            </div>
        </div>
    </section>

    <!-- What's Included Section -->
    <section class="bg-white py-16">
        <div class="container mx-auto px-4">
            <div class="max-w-6xl mx-auto">
                <!-- Section Header -->
                <div class="text-center mb-12">
                    <h2 class="text-4xl font-bold text-gray-800 mb-3">{{t('umrah.whatsIncludedTitle')}}</h2>
                    <p class="text-gray-600 text-lg">{{t('umrah.whatsIncludedSubtitle')}}</p>
                </div>

                <!-- Grid of Inclusions -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Inclusion 1 -->
                    <div class="flex items-start gap-4 p-6 bg-blue-50 rounded-xl hover:bg-blue-100 transition-colors">
                        <div class="flex-shrink-0 w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center">
                            <i class="fas fa-plane text-white text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800 mb-1">{{t('umrah.roundTripFlights')}}</h3>
                            <p class="text-sm text-gray-600">{{t('umrah.roundTripFlightsDesc')}}</p>
                        </div>
                    </div>

                    <!-- Inclusion 2 -->
                    <div class="flex items-start gap-4 p-6 bg-blue-50 rounded-xl hover:bg-blue-100 transition-colors">
                        <div class="flex-shrink-0 w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center">
                            <i class="fas fa-hotel text-white text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800 mb-1">{{t('umrah.hotelAccommodation')}}</h3>
                            <p class="text-sm text-gray-600">{{t('umrah.hotelAccommodationDesc')}}</p>
                        </div>
                    </div>

                    <!-- Inclusion 3 -->
                    <div class="flex items-start gap-4 p-6 bg-blue-50 rounded-xl hover:bg-blue-100 transition-colors">
                        <div class="flex-shrink-0 w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center">
                            <i class="fas fa-passport text-white text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800 mb-1">{{t('umrah.visaProcessing')}}</h3>
                            <p class="text-sm text-gray-600">{{t('umrah.visaProcessingDesc')}}</p>
                        </div>
                    </div>

                    <!-- Inclusion 4 -->
                    <div class="flex items-start gap-4 p-6 bg-blue-50 rounded-xl hover:bg-blue-100 transition-colors">
                        <div class="flex-shrink-0 w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center">
                            <i class="fas fa-bus text-white text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800 mb-1">{{t('umrah.transportation')}}</h3>
                            <p class="text-sm text-gray-600">{{t('umrah.transportationDesc')}}</p>
                        </div>
                    </div>

                    <!-- Inclusion 5 -->
                    <div class="flex items-start gap-4 p-6 bg-blue-50 rounded-xl hover:bg-blue-100 transition-colors">
                        <div class="flex-shrink-0 w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center">
                            <i class="fas fa-user-tie text-white text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800 mb-1">{{t('umrah.expertGuidesIncluded')}}</h3>
                            <p class="text-sm text-gray-600">{{t('umrah.expertGuidesIncludedDesc')}}</p>
                        </div>
                    </div>

                    <!-- Inclusion 6 -->
                    <div class="flex items-start gap-4 p-6 bg-blue-50 rounded-xl hover:bg-blue-100 transition-colors">
                        <div class="flex-shrink-0 w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center">
                            <i class="fas fa-utensils text-white text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800 mb-1">{{t('umrah.meals')}}</h3>
                            <p class="text-sm text-gray-600">{{t('umrah.mealsDesc')}}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

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
