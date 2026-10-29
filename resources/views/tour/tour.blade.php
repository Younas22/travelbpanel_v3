@extends('common.layout')
@section('content')
    @php
        $active_currency = activeCurrency();

        // Hero title with its last two words in brand blue, the same treatment
        // as the homepage hero.
        $heroTitleWords = preg_split('/\s+/u', trim(t('tour.page_title')));
        $heroAccentCount = count($heroTitleWords) >= 3 ? 2 : (count($heroTitleWords) === 2 ? 1 : 0);
        $heroTitleLead = implode(' ', array_slice($heroTitleWords, 0, count($heroTitleWords) - $heroAccentCount));
        $heroTitleAccent = implode(' ', array_slice($heroTitleWords, count($heroTitleWords) - $heroAccentCount));

        // Section titles: last word gets the brand accent colour
        $thSplitTitle = function ($text) {
            $words = preg_split('/\s+/u', trim($text));
            $accent = count($words) > 1 ? array_pop($words) : '';
            return [implode(' ', $words), $accent];
        };

        // Default search values - 2 days from today and 4 days from today, 2 adults
        $startDate = date('d-m-Y', strtotime('+2 days'));
        $endDate = date('d-m-Y', strtotime('+4 days'));
        $adult = 2;
        $child = 0;

        // Trending destinations (same places and tour counts as before); each
        // opens the tour listing for that city.
        $destinations = [
            ['Paris', '120+', 'https://images.unsplash.com/photo-1502602898657-3e91760cbb34?w=300&h=300&fit=crop'],
            ['Rome', '95+', 'https://images.unsplash.com/photo-1552832230-c0197dd311b5?w=300&h=300&fit=crop'],
            ['Dubai', '150+', 'https://images.unsplash.com/photo-1512453979798-5ea266f8880c?w=300&h=300&fit=crop'],
            ['Bali', '80+', 'https://images.unsplash.com/photo-1537996194471-e657df975ab4?w=300&h=300&fit=crop'],
            ['Maldives', '65+', 'https://images.unsplash.com/photo-1514282401047-d79a71a590e8?w=300&h=300&fit=crop'],
            ['Switzerland', '100+', 'https://images.unsplash.com/photo-1527668752968-14dc70a27c95?w=300&h=300&fit=crop'],
            ['Santorini', '75+', 'https://images.unsplash.com/photo-1570077188670-e3a8d69ac5ff?w=300&h=300&fit=crop'],
            ['Thailand', '110+', 'https://images.unsplash.com/photo-1552465011-b4e21bf6e79a?w=300&h=300&fit=crop'],
        ];
    @endphp
    <style>
        /* Hero layout and the search panel live in style.css (#siteRedesign);
           only this page's cover image is set here. */
        #siteRedesign .hero-section {
            background-image: url('<?=url('public/assets/images/flight/tour-cappadocia-balloons.png')?>');
        }
    </style>

<div id="siteRedesign">
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-content">
            <h1 class="hero-title">
                {{ $heroTitleLead }}@if($heroTitleAccent !== '') <span class="hero-title-accent">{{ $heroTitleAccent }}</span>@endif
            </h1>
            <p class="hero-subtitle">{{ t('tour.page_subtitle') }}</p>
        </div>

        <!-- Search Form -->
        <div class="form-container">
            <div class="tab-content active" id="form-tour">
                @include('forms.tours-form')
            </div>
        </div>
    </section>

    <!-- Trending Destinations Section -->
    @php [$thLead, $thAccent] = $thSplitTitle(t('tour.trending_destinations')); @endphp
    <section class="th-section" id="trendingDestinations">
        <div class="th-container">
            <div class="th-header">
                <div class="th-header-text">
                    <span class="th-eyebrow">{{ t('tour.trending_destinations') }}</span>
                    <h2 class="th-title">{{ $thLead }} @if($thAccent)<span class="th-title-accent">{{ $thAccent }}</span>@endif</h2>
                </div>
                <div class="th-nav">
                    <button type="button" class="th-nav-btn th-nav-prev" data-th-dir="-1" aria-label="Previous"><i class="fas fa-arrow-left"></i></button>
                    <button type="button" class="th-nav-btn th-nav-next" data-th-dir="1" aria-label="Next"><i class="fas fa-arrow-right"></i></button>
                </div>
            </div>

            <div class="th-track is-dense">
                @foreach($destinations as [$destName, $destCount, $destImage])
                    <a href="{{ url('tours/' . \Illuminate\Support\Str::slug($destName) . "/all/{$startDate}/{$endDate}/{$adult}/{$child}") }}" class="th-card th-dest">
                        <span class="th-dest-media">
                            <img src="{{ $destImage }}" alt="{{ $destName }}" loading="lazy">
                        </span>
                        <span class="th-dest-body">
                            <span class="th-dest-name">{{ $destName }}</span>
                            <span class="th-dest-count"><i class="fas fa-map-location-dot"></i> {{ $destCount }} Tours</span>
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Find Popular Tours Section -->
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
                @foreach($tours as $tour)
                    @php
                        // Build detail URL: tours/{slug}/{location}/{type}/{startDate}/{endDate}/{adult}/{child}
                        $slug = Str::slug($tour->name);
                        $locationSlug = $tour->location_name ? strtolower(str_replace(' ', '-', $tour->location_name)) : 'dubai';
                        $type = $tour->packege_type ?? 'all';
                        $detailUrl = url("tours/{$slug}/{$locationSlug}/{$type}/{$startDate}/{$endDate}/{$adult}/{$child}");
                    @endphp
                    @include('common.tour-card', [
                        'tourPrice' => ($tour->currency ?? 'USD') . ' ' . number_format($tour->price, 0),
                    ])
                @endforeach
            </div>
        </div>
    </section>

    <!-- Why Choose Us Section -->
    @php [$thLead, $thAccent] = $thSplitTitle(t('tour.why_book_title')); @endphp
    <section class="th-section" id="whyBookTours">
        <div class="th-container">
            <div class="th-header">
                <div class="th-header-text">
                    <span class="th-eyebrow">{{ t('tour.why_book_title') }}</span>
                    <h2 class="th-title">{{ $thLead }} @if($thAccent)<span class="th-title-accent">{{ $thAccent }}</span>@endif</h2>
                </div>
            </div>

            <div class="hw-grid">
                @foreach([
                    ['fa-map-location-dot', 'tour.curated_tours', 'tour.curated_tours_desc'],
                    ['fa-users', 'tour.expert_guides', 'tour.expert_guides_desc'],
                    ['fa-shield-halved', 'tour.safe_travel', 'tour.safe_travel_desc'],
                    ['fa-headset', 'tour.support_24_7', 'tour.support_24_7_desc'],
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
    </script>

@endsection
