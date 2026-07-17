@extends('common.layout')
@section('content')

    <!-- Hero Section -->
    <section class="hero-section tour-page">
        <div class="hero-content">
            <h1>{{ t('tour.page_title') }}</h1>
            <p>{{ t('tour.page_subtitle') }}</p>
        </div>

        <!-- Search Form -->
        <div class="form-container">
            <div class="tab-content active bg-white rounded-lg shadow-lg mb-8 relative z-10" id="form-tour">
                @include('forms.tours-form')
            </div>
        </div>
    </section>

        <!-- Why Choose Us Section -->
    <section class="how-it-works">
        <div class="section-header">
            <h2>{{ t('tour.why_book_title') }}</h2>
            <p>{{ t('tour.why_book_subtitle') }}</p>
        </div>

        <div class="steps-container">
            <div class="step-card">
                <div class="step-number">
                    <i class="fas fa-map-marked-alt step-icon"></i>
                </div>
                <h3 class="step-title">{{ t('tour.curated_tours') }}</h3>
                <p class="step-description">{{ t('tour.curated_tours_desc') }}</p>
            </div>

            <div class="step-card">
                <div class="step-number">
                    <i class="fas fa-users step-icon"></i>
                </div>
                <h3 class="step-title">{{ t('tour.expert_guides') }}</h3>
                <p class="step-description">{{ t('tour.expert_guides_desc') }}</p>
            </div>

            <div class="step-card">
                <div class="step-number">
                    <i class="fas fa-shield-alt step-icon"></i>
                </div>
                <h3 class="step-title">{{ t('tour.safe_travel') }}</h3>
                <p class="step-description">{{ t('tour.safe_travel_desc') }}</p>
            </div>

            <div class="step-card">
                <div class="step-number">
                    <i class="fas fa-headset step-icon"></i>
                </div>
                <h3 class="step-title">{{ t('tour.support_24_7') }}</h3>
                <p class="step-description">{{ t('tour.support_24_7_desc') }}</p>
            </div>
        </div>
    </section>

    <!-- Trending Destinations Section -->
    <section class="bg-white py-16">
        <div class="container mx-auto px-4">
            <!-- Section Header -->
            <div class="text-center mb-12">
                <h2 class="text-4xl font-bold text-gray-800 mb-3">{{ t('tour.trending_destinations') }}</h2>
                <p class="text-gray-600 text-lg">{{ t('tour.trending_destinations_subtitle') }}</p>
            </div>

            <!-- Carousel Wrapper -->
            <div class="relative max-w-7xl mx-auto">
                <!-- Previous Button -->
                <button onclick="scrollCarousel('prev')"
                        class="absolute left-0 top-1/2 -translate-y-1/2 z-10 w-12 h-12 bg-white rounded-full shadow-lg border-2 border-gray-200 flex items-center justify-center text-blue-600 hover:bg-blue-600 hover:text-white hover:border-blue-600 transition-all duration-300 hover:shadow-xl">
                    <i class="fas fa-chevron-left"></i>
                </button>

                <!-- Carousel Container -->
                <div class="overflow-hidden px-14">
                    <div id="destinationsCarousel"
                         class="destinations-carousel flex gap-8 overflow-x-auto scroll-smooth py-6">

                        <!-- Destination 1: Paris -->
                        <a href="#" class="flex-shrink-0 w-40 text-center group">
                            <div class="relative mb-4">
                                <div class="w-40 h-40 rounded-full overflow-hidden shadow-lg border-4 border-white group-hover:border-blue-500 transition-all duration-300 group-hover:shadow-2xl group-hover:-translate-y-2">
                                    <img src="https://images.unsplash.com/photo-1502602898657-3e91760cbb34?w=300&h=300&fit=crop"
                                         alt="Paris"
                                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                </div>
                            </div>
                            <h3 class="text-lg font-bold text-gray-800 mb-1 group-hover:text-blue-600 transition-colors">Paris</h3>
                            <p class="text-sm text-gray-600 flex items-center justify-center gap-1">
                                <i class="fas fa-map-marked-alt text-blue-600 text-xs"></i>
                                120+ Tours
                            </p>
                        </a>

                        <!-- Destination 2: Rome -->
                        <a href="#" class="flex-shrink-0 w-40 text-center group">
                            <div class="relative mb-4">
                                <div class="w-40 h-40 rounded-full overflow-hidden shadow-lg border-4 border-white group-hover:border-blue-500 transition-all duration-300 group-hover:shadow-2xl group-hover:-translate-y-2">
                                    <img src="https://images.unsplash.com/photo-1552832230-c0197dd311b5?w=300&h=300&fit=crop"
                                         alt="Rome"
                                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                </div>
                            </div>
                            <h3 class="text-lg font-bold text-gray-800 mb-1 group-hover:text-blue-600 transition-colors">Rome</h3>
                            <p class="text-sm text-gray-600 flex items-center justify-center gap-1">
                                <i class="fas fa-map-marked-alt text-blue-600 text-xs"></i>
                                95+ Tours
                            </p>
                        </a>

                        <!-- Destination 3: Dubai -->
                        <a href="#" class="flex-shrink-0 w-40 text-center group">
                            <div class="relative mb-4">
                                <div class="w-40 h-40 rounded-full overflow-hidden shadow-lg border-4 border-white group-hover:border-blue-500 transition-all duration-300 group-hover:shadow-2xl group-hover:-translate-y-2">
                                    <img src="https://images.unsplash.com/photo-1512453979798-5ea266f8880c?w=300&h=300&fit=crop"
                                         alt="Dubai"
                                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                </div>
                            </div>
                            <h3 class="text-lg font-bold text-gray-800 mb-1 group-hover:text-blue-600 transition-colors">Dubai</h3>
                            <p class="text-sm text-gray-600 flex items-center justify-center gap-1">
                                <i class="fas fa-map-marked-alt text-blue-600 text-xs"></i>
                                150+ Tours
                            </p>
                        </a>

                        <!-- Destination 4: Bali -->
                        <a href="#" class="flex-shrink-0 w-40 text-center group">
                            <div class="relative mb-4">
                                <div class="w-40 h-40 rounded-full overflow-hidden shadow-lg border-4 border-white group-hover:border-blue-500 transition-all duration-300 group-hover:shadow-2xl group-hover:-translate-y-2">
                                    <img src="https://images.unsplash.com/photo-1537996194471-e657df975ab4?w=300&h=300&fit=crop"
                                         alt="Bali"
                                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                </div>
                            </div>
                            <h3 class="text-lg font-bold text-gray-800 mb-1 group-hover:text-blue-600 transition-colors">Bali</h3>
                            <p class="text-sm text-gray-600 flex items-center justify-center gap-1">
                                <i class="fas fa-map-marked-alt text-blue-600 text-xs"></i>
                                80+ Tours
                            </p>
                        </a>

                        <!-- Destination 5: Maldives -->
                        <a href="#" class="flex-shrink-0 w-40 text-center group">
                            <div class="relative mb-4">
                                <div class="w-40 h-40 rounded-full overflow-hidden shadow-lg border-4 border-white group-hover:border-blue-500 transition-all duration-300 group-hover:shadow-2xl group-hover:-translate-y-2">
                                    <img src="https://images.unsplash.com/photo-1514282401047-d79a71a590e8?w=300&h=300&fit=crop"
                                         alt="Maldives"
                                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                </div>
                            </div>
                            <h3 class="text-lg font-bold text-gray-800 mb-1 group-hover:text-blue-600 transition-colors">Maldives</h3>
                            <p class="text-sm text-gray-600 flex items-center justify-center gap-1">
                                <i class="fas fa-map-marked-alt text-blue-600 text-xs"></i>
                                65+ Tours
                            </p>
                        </a>

                        <!-- Destination 6: Switzerland -->
                        <a href="#" class="flex-shrink-0 w-40 text-center group">
                            <div class="relative mb-4">
                                <div class="w-40 h-40 rounded-full overflow-hidden shadow-lg border-4 border-white group-hover:border-blue-500 transition-all duration-300 group-hover:shadow-2xl group-hover:-translate-y-2">
                                    <img src="https://images.unsplash.com/photo-1527668752968-14dc70a27c95?w=300&h=300&fit=crop"
                                         alt="Switzerland"
                                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                </div>
                            </div>
                            <h3 class="text-lg font-bold text-gray-800 mb-1 group-hover:text-blue-600 transition-colors">Switzerland</h3>
                            <p class="text-sm text-gray-600 flex items-center justify-center gap-1">
                                <i class="fas fa-map-marked-alt text-blue-600 text-xs"></i>
                                100+ Tours
                            </p>
                        </a>

                        <!-- Destination 7: Santorini -->
                        <a href="#" class="flex-shrink-0 w-40 text-center group">
                            <div class="relative mb-4">
                                <div class="w-40 h-40 rounded-full overflow-hidden shadow-lg border-4 border-white group-hover:border-blue-500 transition-all duration-300 group-hover:shadow-2xl group-hover:-translate-y-2">
                                    <img src="https://images.unsplash.com/photo-1570077188670-e3a8d69ac5ff?w=300&h=300&fit=crop"
                                         alt="Santorini"
                                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                </div>
                            </div>
                            <h3 class="text-lg font-bold text-gray-800 mb-1 group-hover:text-blue-600 transition-colors">Santorini</h3>
                            <p class="text-sm text-gray-600 flex items-center justify-center gap-1">
                                <i class="fas fa-map-marked-alt text-blue-600 text-xs"></i>
                                75+ Tours
                            </p>
                        </a>

                        <!-- Destination 8: Thailand -->
                        <a href="#" class="flex-shrink-0 w-40 text-center group">
                            <div class="relative mb-4">
                                <div class="w-40 h-40 rounded-full overflow-hidden shadow-lg border-4 border-white group-hover:border-blue-500 transition-all duration-300 group-hover:shadow-2xl group-hover:-translate-y-2">
                                    <img src="https://images.unsplash.com/photo-1552465011-b4e21bf6e79a?w=300&h=300&fit=crop"
                                         alt="Thailand"
                                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                </div>
                            </div>
                            <h3 class="text-lg font-bold text-gray-800 mb-1 group-hover:text-blue-600 transition-colors">Thailand</h3>
                            <p class="text-sm text-gray-600 flex items-center justify-center gap-1">
                                <i class="fas fa-map-marked-alt text-blue-600 text-xs"></i>
                                110+ Tours
                            </p>
                        </a>

                    </div>
                </div>

                <!-- Next Button -->
                <button onclick="scrollCarousel('next')"
                        class="absolute right-0 top-1/2 -translate-y-1/2 z-10 w-12 h-12 bg-white rounded-full shadow-lg border-2 border-gray-200 flex items-center justify-center text-blue-600 hover:bg-blue-600 hover:text-white hover:border-blue-600 transition-all duration-300 hover:shadow-xl">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>

        </div>
    </section>

    <!-- Find Popular Tours Section -->
    <section class="responsive-section">
        <div class="container">
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
                                        <span class="price-currency">{{ $tour->currency ?? 'USD' }}</span> {{ number_format($tour->price, 0) }}
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
                <a href="#form-tour" class="cta-button">
                    <i class="fas fa-globe"></i> {{ t('tour.view_all_tours') }}
                </a>
            </div>
        </div>
    </section>

    <!-- Promotional Section - Grab up to 35% off -->
    <section class="promo-gradient-bg relative overflow-hidden py-16 md:py-24" style="display: none;">
        <!-- Decorative Background Circle -->
        <div class="absolute -top-1/2 -right-20 w-96 h-96 md:w-[600px] md:h-[600px] bg-blue-100/30 rounded-full blur-3xl"></div>

        <div class="container mx-auto px-4 md:px-6 lg:px-8 relative z-10">
            <div class="max-w-7xl mx-auto">
                <div class="flex flex-col lg:flex-row items-center gap-8 lg:gap-16">

                    <!-- Left Side: Text Content -->
                    <div class="w-full lg:w-1/2 text-center lg:text-left space-y-6">
                        <h2 class="text-4xl md:text-5xl lg:text-6xl font-extrabold text-gray-800 leading-tight">
                            Grab up to
                            <span class="text-custom-blue">35% off</span>
                            <br>
                            on your favorite
                            <br>
                            <span class="text-custom-blue">Destination</span>
                        </h2>

                        <p class="text-lg md:text-xl text-gray-600 font-medium max-w-md mx-auto lg:mx-0">
                            Limited time offer, don't miss the opportunity
                        </p>

                        <div class="pt-4">
                            <a href="#" class="promo-button-gradient inline-flex items-center justify-center gap-3 text-white font-bold text-base md:text-lg px-8 md:px-12 py-4 rounded-full shadow-lg hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300">
                                Book Now
                                <i class="fas fa-arrow-right text-sm"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Right Side: Image -->
                    <div class="w-full lg:w-1/2 mt-8 lg:mt-0">
                        <div class="relative group">
                            <!-- Image Container -->
                            <div class="relative rounded-3xl overflow-hidden shadow-2xl transition-all duration-500 group-hover:shadow-3xl group-hover:scale-105">
                                <img src="https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?w=800&h=600&fit=crop"
                                     alt="Travel Destination"
                                     class="w-full h-auto object-cover">

                                <!-- Discount Badge -->
                                <div class="absolute top-6 right-6 promo-badge-gradient rounded-2xl px-6 py-5 shadow-xl transform rotate-6 group-hover:rotate-0 group-hover:scale-110 transition-all duration-300">
                                    <div class="flex flex-col items-center text-white">
                                        <span class="text-xs font-bold tracking-wider uppercase">UP TO</span>
                                        <span class="text-5xl font-black leading-none my-1">35%</span>
                                        <span class="text-xs font-bold tracking-wider uppercase">OFF</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Decorative Element -->
                            <div class="absolute -bottom-4 -left-4 w-24 h-24 bg-yellow-400 rounded-full opacity-60 blur-2xl"></div>
                            <div class="absolute -top-4 -right-4 w-32 h-32 bg-pink-400 rounded-full opacity-40 blur-2xl"></div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <!-- Popular Things To Do Section -->
    <section class="bg-white py-16 md:py-20" style="display: none;">
        <div class="container mx-auto px-4 md:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">

                <!-- Section Header -->
                <div class="flex justify-between items-center mb-10 md:mb-12">
                    <h2 class="text-3xl md:text-4xl font-bold text-custom-blue">Popular things to do</h2>
                    <a href="#" class="text-custom-blue hover:text-custom-blue font-semibold text-sm md:text-base flex items-center gap-2 transition-colors duration-300 group hover:underline">
                        See all
                        <i class="fas fa-arrow-right text-xs group-hover:translate-x-1 transition-transform duration-300"></i>
                    </a>
                </div>

                <!-- Two Row Masonry Grid Layout -->
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 md:gap-5">

                    <!-- Row 1 -->
                    <!-- Card 1: Cruises - Small Left -->
                    <a href="#" class="group relative overflow-hidden rounded-3xl md:col-span-3 h-64 md:h-72 shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
                        <img src="https://images.unsplash.com/photo-1544551763-46a013bb70d5?w=500&h=600&fit=crop"
                             alt="Cruises"
                             class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/20 to-transparent"></div>
                        <div class="absolute bottom-0 left-0 p-6">
                            <h3 class="text-white font-bold text-2xl drop-shadow-lg">Cruises</h3>
                        </div>
                    </a>

                    <!-- Card 2: Beach Tours - Large Center -->
                    <a href="#" class="group relative overflow-hidden rounded-3xl md:col-span-6 h-64 md:h-72 shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
                        <img src="https://images.unsplash.com/photo-1559827260-dc66d52bef19?w=800&h=600&fit=crop"
                             alt="Beach Tours"
                             class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/20 to-transparent"></div>
                        <div class="absolute bottom-0 left-0 p-6">
                            <h3 class="text-white font-bold text-2xl md:text-3xl drop-shadow-lg">Beach Tours</h3>
                        </div>
                    </a>

                    <!-- Card 3: City Tours - Small Right -->
                    <a href="#" class="group relative overflow-hidden rounded-3xl md:col-span-3 h-64 md:h-72 shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
                        <img src="https://images.unsplash.com/photo-1477959858617-67f85cf4f1df?w=500&h=600&fit=crop"
                             alt="City Tours"
                             class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/20 to-transparent"></div>
                        <div class="absolute bottom-0 left-0 p-6">
                            <h3 class="text-white font-bold text-2xl drop-shadow-lg">City Tours</h3>
                        </div>
                    </a>

                    <!-- Row 2 -->
                    <!-- Card 4: Museum Tour - Small Left -->
                    <a href="#" class="group relative overflow-hidden rounded-3xl md:col-span-3 h-64 md:h-72 shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
                        <img src="https://creativelayers.net/themes/viatours-html/img/features/1/5.png"
                             alt="Museum Tour"
                             class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/20 to-transparent"></div>
                        <div class="absolute bottom-0 left-0 p-6">
                            <h3 class="text-white font-bold text-2xl drop-shadow-lg">Museum Tour</h3>
                        </div>
                    </a>

                    <!-- Card 5: Food - Medium -->
                    <a href="#" class="group relative overflow-hidden rounded-3xl md:col-span-3 h-64 md:h-72 shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
                        <img src="https://images.unsplash.com/photo-1555939594-58d7cb561ad1?w=500&h=600&fit=crop"
                             alt="Food"
                             class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/20 to-transparent"></div>
                        <div class="absolute bottom-0 left-0 p-6">
                            <h3 class="text-white font-bold text-2xl drop-shadow-lg">Food</h3>
                        </div>
                    </a>

                    <!-- Card 6: Hiking - Large Right -->
                    <a href="#" class="group relative overflow-hidden rounded-3xl md:col-span-6 h-64 md:h-72 shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
                        <img src="https://images.unsplash.com/photo-1551632811-561732d1e306?w=800&h=600&fit=crop"
                             alt="Hiking"
                             class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/20 to-transparent"></div>
                        <div class="absolute bottom-0 left-0 p-6">
                            <h3 class="text-white font-bold text-2xl md:text-3xl drop-shadow-lg">Hiking</h3>
                        </div>
                    </a>

                </div>

            </div>
        </div>
    </section>

    <script>
        // Smooth scroll to form on page load if hash is present
        document.addEventListener('DOMContentLoaded', function() {
            if (window.location.hash === '#form-tour') {
                setTimeout(function() {
                    const formElement = document.getElementById('form-tour');
                    if (formElement) {
                        formElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }, 300);
            }
        });

        // Carousel scroll functionality
        function scrollCarousel(direction) {
            const carousel = document.getElementById('destinationsCarousel');
            const scrollAmount = 220; // width of item + gap

            if (direction === 'next') {
                carousel.scrollBy({ left: scrollAmount, behavior: 'smooth' });
            } else {
                carousel.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
            }
        }

        // Optional: Auto-scroll functionality
        let autoScrollInterval;

        function startAutoScroll() {
            autoScrollInterval = setInterval(() => {
                const carousel = document.getElementById('destinationsCarousel');
                const maxScroll = carousel.scrollWidth - carousel.clientWidth;

                if (carousel.scrollLeft >= maxScroll - 10) {
                    // Reset to start
                    carousel.scrollTo({ left: 0, behavior: 'smooth' });
                } else {
                    scrollCarousel('next');
                }
            }, 3000); // Auto-scroll every 3 seconds
        }

        function stopAutoScroll() {
            clearInterval(autoScrollInterval);
        }

        // Start auto-scroll on page load
        document.addEventListener('DOMContentLoaded', function() {
            const carousel = document.getElementById('destinationsCarousel');

            // Stop auto-scroll when user interacts with carousel
            carousel.addEventListener('mouseenter', stopAutoScroll);
            carousel.addEventListener('touchstart', stopAutoScroll);

            // Resume auto-scroll when user stops interacting
            carousel.addEventListener('mouseleave', startAutoScroll);

            // Start auto-scroll
            startAutoScroll();
        });
    </script>

@endsection
