@extends('common.layout')
@section('content')
    @php
        $active_currency = activeCurrency();
    @endphp
<div class="tour-details-container">
    <!-- Header -->
    <div class="tour-details-header">
        <h1 class="tour-details-title">{{ $tour->name }}</h1>
        <div class="tour-details-meta">
            <div class="tour-meta-item">
                <i class="fas fa-map-marker-alt tour-meta-icon"></i>
                <span>{{ $tour->location_name ?? $tour->loaction }}</span>
            </div>
            <div class="tour-meta-item">
                <i class="fas fa-clock tour-meta-icon"></i>
                <span>{{ $tour->duration }}</span>
            </div>
            @if($tour->packageType)
                <div class="tour-meta-item">
                    <i class="fas fa-tag tour-meta-icon"></i>
                    <span>{{ $tour->packageType->packege_type }}</span>
                </div>
            @endif
            @if($tour->rating)
                <div class="tour-rating-badge">
                    <span class="tour-rating-stars">
                        @for($i = 1; $i <= 5; $i++)
                            @if($i <= $tour->stars)★@else☆@endif
                        @endfor
                    </span>
                    <span class="tour-rating-score">{{ $tour->rating }}</span>
                </div>
            @endif
        </div>
    </div>

    <!-- Main Content -->
    <div class="tour-details-main">
        <div>
            <!-- Image Gallery -->
            <div class="tour-gallery">
                <div class="tour-gallery-main">

                    @if($tour->images->count() > 0)
                        <img src="{{ asset('public/assets/images/' . $tour->images->first()->image) }}" alt="{{ $tour->name }}" id="mainImage">
                    @else
                        <img src="{{ asset('assets/images/placeholder/default.jpg') }}" alt="{{ $tour->name }}" id="mainImage">
                    @endif
                    @if($tour->images->count() > 1)
                        <button class="gallery-nav-btn prev" onclick="prevImage()"><i class="fas fa-chevron-left"></i></button>
                        <button class="gallery-nav-btn next" onclick="nextImage()"><i class="fas fa-chevron-right"></i></button>
                    @endif
                </div>
                @if($tour->images->count() > 1)
                    <div class="tour-gallery-thumbnails">
                        @foreach($tour->images as $index => $image)
                            <div class="gallery-thumbnail {{ $index === 0 ? 'active' : '' }}" onclick="changeImage({{ $index }})">
                                <img src="{{ asset('public/assets/images/' . $image->image) }}" alt="{{ $tour->name }} {{ $index + 1 }}">
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Overview -->
            <div class="tour-section">
                <h2 class="section-title">
                    <i class="fas fa-info-circle"></i>
                    {{ t('tourlist.overview') ?? 'Overview' }}
                </h2>
                <div class="section-content">
                    {!! $tour->description !!}
                </div>
                <div class="tour-highlights-grid">
                    <div class="highlight-card">
                        <i class="fas fa-clock highlight-icon"></i>
                        <div class="highlight-content">
                            <h4>{{ t('tourlist.duration') ?? 'Duration' }}</h4>
                            <p>{{ $tour->duration }}</p>
                        </div>
                    </div>
                    @if($tour->days)
                        <div class="highlight-card">
                            <i class="fas fa-calendar-alt highlight-icon"></i>
                            <div class="highlight-content">
                                <h4>{{ t('tourlist.days') ?? 'Days' }}</h4>
                                <p>{{ $tour->days }} {{ t('tourlist.days') ?? 'Days' }}</p>
                            </div>
                        </div>
                    @endif
                    @if($tour->packageType)
                        <div class="highlight-card">
                            <i class="fas fa-tag highlight-icon"></i>
                            <div class="highlight-content">
                                <h4>{{ t('tourlist.tour_type') ?? 'Tour Type' }}</h4>
                                <p>{{ $tour->packageType->packege_type }}</p>
                            </div>
                        </div>
                    @endif
                    <div class="highlight-card">
                        <i class="fas fa-users highlight-icon"></i>
                        <div class="highlight-content">
                            <h4>{{ t('tourlist.travelers') ?? 'Travelers' }}</h4>
                            <p>
                                {{ $searchParams['adult'] ?? 2 }} {{ t('tourlist.adults') ?? 'Adults' }}
                                @if(isset($searchParams['child']) && $searchParams['child'] > 0), {{ $searchParams['child'] }} {{ t('tourlist.children') ?? 'Children' }}@endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- What's Included -->
            @if(count($tour->inclusion_names) > 0)
                <div class="tour-section">
                    <h2 class="section-title">
                        <i class="fas fa-check-circle"></i>
                        {{ t('tourlist.whats_included') ?? "What's Included" }}
                    </h2>
                    <div class="included-list">
                        @foreach($tour->inclusion_names as $inclusion)
                            <div class="included-item">
                                <i class="fas fa-check-circle"></i>
                                <span>{{ $inclusion }}</span>
                            </div>
                        @endforeach
                    </div>

                    @if(count($tour->exclusion_names) > 0)
                        <h3 style="margin-top: 28px; margin-bottom: 16px; font-size: 18px; color: #111827;">{{ t('tourlist.not_included') ?? 'Not Included' }}</h3>
                        <div class="excluded-list">
                            @foreach($tour->exclusion_names as $exclusion)
                                <div class="excluded-item">
                                    <i class="fas fa-times-circle"></i>
                                    <span>{{ $exclusion }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif

            <!-- Travel Information -->
            <div class="tour-section">
                <h2 class="section-title">
                    <i class="fas fa-map-pin"></i>
                    {{ t('tourlist.travel_info') ?? 'Travel Information' }}
                </h2>
                <div class="meeting-point">
                    <h4><i class="fas fa-map-marker-alt"></i> {{ t('tourlist.destination') ?? 'Destination' }}</h4>
                    <p><strong>{{ $tour->location_name ?? $tour->loaction }}</strong></p>
                </div>
                @if(isset($searchParams['start_date']))
                    <div class="meeting-point">
                        <h4><i class="fas fa-calendar-alt"></i> {{ t('tourlist.dates') ?? 'Travel Dates' }}</h4>
                        <p>{{ t('tourlist.departure') ?? 'Departure' }}: {{ $searchParams['original_start_date'] }}<br>
                        {{ t('tourlist.return') ?? 'Return' }}: {{ $searchParams['original_end_date'] }}</p>
                    </div>
                @endif
                <div class="meeting-point">
                    <h4><i class="fas fa-users"></i> {{ t('tourlist.travelers') ?? 'Travelers' }}</h4>
                    <p>{{ $searchParams['adult'] ?? 2 }} {{ t('tourlist.adults') ?? 'Adults' }}
                    @if(isset($searchParams['child']) && $searchParams['child'] > 0), {{ $searchParams['child'] }} {{ t('tourlist.children') ?? 'Children' }}@endif
                    </p>
                </div>
            </div>

            <!-- Cancellation Policy -->
            @if($tour->policy)
                <div class="tour-section">
                    <h2 class="section-title">
                        <i class="fas fa-file-contract"></i>
                        {{ t('tourlist.cancellation_policy') ?? 'Cancellation Policy' }}
                    </h2>
                    <div class="section-content">
                        {!! $tour->policy !!}
                    </div>
                </div>
            @endif
        </div>

        <!-- Booking Sidebar -->
        <div class="tour-booking-sidebar">
            <div class="booking-price-section">
                <div class="booking-price-label">{{ t('tourlist.starting_from') ?? 'From' }}</div>
                <div class="booking-price">
                    <span class="booking-price-currency">{{$active_currency->currency_name}}</span>{{ number_format(convertCurrency($tour->price,  $tour->currency ?? 'USD', $active_currency->currency_name)) }}
                </div>
                <div class="booking-price-person">{{ t('tourlist.per_person') ?? 'per person' }}</div>
            </div>

            <div class="booking-details">
                <div class="booking-detail-item">
                    <span class="detail-label">{{ t('tourlist.duration') ?? 'Duration' }}</span>
                    <span class="detail-value">{{ $tour->duration }}</span>
                </div>
                @if($tour->days)
                    <div class="booking-detail-item">
                        <span class="detail-label">{{ t('tourlist.days') ?? 'Days' }}</span>
                        <span class="detail-value">{{ $tour->days }} {{ t('tourlist.days') ?? 'Days' }}</span>
                    </div>
                @endif
                <div class="booking-detail-item">
                    <span class="detail-label">{{ t('tourlist.destination') ?? 'Destination' }}</span>
                    <span class="detail-value">{{ $tour->location_name ?? $tour->loaction }}</span>
                </div>
                @if($tour->packageType)
                    <div class="booking-detail-item">
                        <span class="detail-label">{{ t('tourlist.tour_type') ?? 'Tour Type' }}</span>
                        <span class="detail-value">{{ $tour->packageType->packege_type }}</span>
                    </div>
                @endif
                <div class="booking-detail-item">
                    <span class="detail-label">{{ t('tourlist.travelers') ?? 'Travelers' }}</span>
                    <span class="detail-value">{{ ($searchParams['adult'] ?? 2) + ($searchParams['child'] ?? 0) }}</span>
                </div>
            </div>

            <a href="{{ route('tour.booking', [
                'slug' => \Str::slug($tour->name),
                'location' => $searchParams['location'],
                'type' => $searchParams['type'],
                'startDate' => $searchParams['original_start_date'],
                'endDate' => $searchParams['original_end_date'],
                'adult' => $searchParams['adult'],
                'child' => $searchParams['child']
            ]) }}" class="booking-btn">
                <i class="fas fa-calendar-check"></i>
                {{ t('tourlist.book_now') ?? 'Book This Tour' }}
            </a>

            <div class="safety-badge">
                <i class="fas fa-shield-alt"></i>
                <p>{{ t('tourlist.secure_booking') ?? 'Secure Booking - Your information is protected' }}</p>
            </div>
        </div>
    </div>
</div>

<script>
    @if($tour->images->count() > 0)
        const tourImages = [
            @foreach($tour->images as $image)
                '{{ asset('public/assets/images/' . $image->image) }}',
            @endforeach
        ];
        let currentImageIndex = 0;

        function changeImage(index) {
            currentImageIndex = index;
            document.getElementById('mainImage').src = tourImages[index];

            document.querySelectorAll('.gallery-thumbnail').forEach((thumb, i) => {
                thumb.classList.toggle('active', i === index);
            });
        }

        function prevImage() {
            currentImageIndex = (currentImageIndex - 1 + tourImages.length) % tourImages.length;
            changeImage(currentImageIndex);
        }

        function nextImage() {
            currentImageIndex = (currentImageIndex + 1) % tourImages.length;
            changeImage(currentImageIndex);
        }
    @endif
</script>

@endsection
