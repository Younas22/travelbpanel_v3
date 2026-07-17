@extends('common.layout')
@section('content')
@php
    $active_currency = activeCurrency();
@endphp
<div class="umrah-details-container">
    <!-- Header -->
    <div class="umrah-details-header">
        <h1 class="umrah-details-title">{{ $package->name }}</h1>
        <div class="umrah-details-meta">
            <div class="umrah-meta-item">
                <i class="fas fa-map-marker-alt umrah-meta-icon"></i>
                <span>{{ $package->loaction }}</span>
            </div>
            <div class="umrah-meta-item">
                <i class="fas fa-clock umrah-meta-icon"></i>
                <span>{{ $package->duration }}</span>
            </div>
            <div class="umrah-meta-item">
                <i class="fas fa-kaaba umrah-meta-icon"></i>
                <span>{{ $package->night_in_mekkah }} {{ t('umrahlist.nights_makkah') }}</span>
            </div>
            <div class="umrah-meta-item">
                <i class="fas fa-mosque umrah-meta-icon"></i>
                <span>{{ $package->night_in_madina }} {{ t('umrahlist.nights_madina') }}</span>
            </div>
            @if($package->rating)
                <div class="umrah-rating-badge">
                    <span class="umrah-rating-stars">
                        @for($i = 1; $i <= 5; $i++)
                            @if($i <= $package->stars)★@else☆@endif
                        @endfor
                    </span>
                    <span class="umrah-rating-score">{{ $package->rating }}</span>
                </div>
            @endif
        </div>
    </div>

    <!-- Main Content -->
    <div class="umrah-details-main">
        <div>
            <!-- Image Gallery -->
            <div class="umrah-gallery">
                <div class="umrah-gallery-main">
                    @if($package->images->count() > 0)
                        <img src="{{ asset('public/assets/images/' . $package->images->first()->image) }}" alt="{{ $package->name }}" id="mainImage">
                    @else
                        <img src="{{ asset('assets/images/placeholder/default.jpg') }}" alt="{{ $package->name }}" id="mainImage">
                    @endif
                    @if($package->images->count() > 1)
                        <button class="gallery-nav-btn prev" onclick="prevImage()"><i class="fas fa-chevron-left"></i></button>
                        <button class="gallery-nav-btn next" onclick="nextImage()"><i class="fas fa-chevron-right"></i></button>
                    @endif
                </div>
                @if($package->images->count() > 1)
                    <div class="umrah-gallery-thumbnails">
                        @foreach($package->images as $index => $image)
                            <div class="gallery-thumbnail {{ $index === 0 ? 'active' : '' }}" onclick="changeImage({{ $index }})">
                                <img src="{{ asset('public/assets/images/' . $image->image) }}" alt="{{ $package->name }} {{ $index + 1 }}">
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Overview -->
            <div class="umrah-section">
                <h2 class="section-title">
                    <i class="fas fa-info-circle"></i>
                    {{ t('umrahlist.overview') ?? 'Overview' }}
                </h2>
                <div class="section-content">
                    {!! $package->desc !!}
                </div>
                <div class="umrah-highlights-grid">
                    <div class="highlight-card">
                        <i class="fas fa-kaaba highlight-icon"></i>
                        <div class="highlight-content">
                            <h4>{{ t('umrahlist.makkah') ?? 'Makkah' }}</h4>
                            <p>{{ $package->night_in_mekkah }} {{ t('umrahlist.nights') ?? 'Nights' }}</p>
                        </div>
                    </div>
                    <div class="highlight-card">
                        <i class="fas fa-mosque highlight-icon"></i>
                        <div class="highlight-content">
                            <h4>{{ t('umrahlist.madina') ?? 'Madina' }}</h4>
                            <p>{{ $package->night_in_madina }} {{ t('umrahlist.nights') ?? 'Nights' }}</p>
                        </div>
                    </div>
                    @if($package->class)
                        <div class="highlight-card">
                            <i class="fas fa-plane highlight-icon"></i>
                            <div class="highlight-content">
                                <h4>{{ t('umrahlist.flight_class') ?? 'Flight Class' }}</h4>
                                <p>{{ $package->class }}</p>
                            </div>
                        </div>
                    @endif
                    <div class="highlight-card">
                        <i class="fas fa-users highlight-icon"></i>
                        <div class="highlight-content">
                            <h4>{{ t('umrahlist.group_size') ?? 'Group Size' }}</h4>
                            <p>
                                {{ $package->adults }} {{ t('umrahlist.adults') ?? 'Adults' }}
                                @if($package->childs > 0), {{ $package->childs }} {{ t('umrahlist.children') ?? 'Children' }}@endif
                                @if($package->infants > 0), {{ $package->infants }} {{ t('umrahlist.infants') ?? 'Infants' }}@endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- What's Included -->
            @if(count($package->inclusion_names) > 0)
                <div class="umrah-section">
                    <h2 class="section-title">
                        <i class="fas fa-check-circle"></i>
                        {{ t('umrahlist.whats_included') ?? "What's Included" }}
                    </h2>
                    <div class="included-list">
                        @foreach($package->inclusion_names as $inclusion)
                            <div class="included-item">
                                <i class="fas fa-check-circle"></i>
                                <span>{{ $inclusion }}</span>
                            </div>
                        @endforeach
                    </div>

                    @if(count($package->exclusion_names) > 0)
                        <h3 style="margin-top: 28px; margin-bottom: 16px; font-size: 18px; color: #111827;">{{ t('umrahlist.not_included') ?? 'Not Included' }}</h3>
                        <div class="excluded-list">
                            @foreach($package->exclusion_names as $exclusion)
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
            <div class="umrah-section">
                <h2 class="section-title">
                    <i class="fas fa-plane-departure"></i>
                    {{ t('umrahlist.travel_info') ?? 'Travel Information' }}
                </h2>
                <div class="meeting-point">
                    <h4><i class="fas fa-plane-departure"></i> {{ t('umrahlist.departure') ?? 'Departure' }}</h4>
                    <p><strong>{{ $originAirport->city ?? $searchParams['origin'] }}</strong> ({{ $searchParams['origin'] }})<br>
                    {{ $searchParams['departure_date'] }}</p>
                </div>
                <div class="meeting-point">
                    <h4><i class="fas fa-plane-arrival"></i> {{ t('umrahlist.destination') ?? 'Destination' }}</h4>
                    <p><strong>{{ $destinationAirport->city ?? $searchParams['destination'] }}</strong> ({{ $searchParams['destination'] }})</p>
                </div>
                <div class="meeting-point">
                    <h4><i class="fas fa-calendar-alt"></i> {{ t('umrahlist.return') ?? 'Return' }}</h4>
                    <p>{{ $searchParams['return_date'] }}</p>
                </div>
            </div>

            <!-- Cancellation Policy -->
            @if($package->policy)
                <div class="umrah-section">
                    <h2 class="section-title">
                        <i class="fas fa-file-contract"></i>
                        {{ t('umrahlist.cancellation_policy') ?? 'Cancellation Policy' }}
                    </h2>
                    <div class="section-content">
                        {!! $package->policy !!}
                    </div>
                </div>
            @endif
        </div>

        <!-- Booking Sidebar -->
        <div class="umrah-booking-sidebar">
            <div class="booking-price-section">
                <div class="booking-price-label">{{ t('umrahlist.starting_from') ?? 'From' }}</div>
                <div class="booking-price">
                    <span class="booking-price-currency">{{$active_currency->currency_name}}</span>{{convertCurrency($package->price, $package->currency ?? 'USD', $active_currency->currency_name)}}
                </div>
                <div class="booking-price-person">{{ t('umrahlist.per_person') ?? 'per person' }}</div>
            </div>

            <div class="booking-details">
                <div class="booking-detail-item">
                    <span class="detail-label">{{ t('umrahlist.duration') ?? 'Duration' }}</span>
                    <span class="detail-value">{{ $package->duration }}</span>
                </div>
                <div class="booking-detail-item">
                    <span class="detail-label">{{ t('umrahlist.makkah') ?? 'Makkah' }}</span>
                    <span class="detail-value">{{ $package->night_in_mekkah }} {{ t('umrahlist.nights') ?? 'Nights' }}</span>
                </div>
                <div class="booking-detail-item">
                    <span class="detail-label">{{ t('umrahlist.madina') ?? 'Madina' }}</span>
                    <span class="detail-value">{{ $package->night_in_madina }} {{ t('umrahlist.nights') ?? 'Nights' }}</span>
                </div>
                @if($package->class)
                    <div class="booking-detail-item">
                        <span class="detail-label">{{ t('umrahlist.flight_class') ?? 'Flight Class' }}</span>
                        <span class="detail-value">{{ $package->class }}</span>
                    </div>
                @endif
                <div class="booking-detail-item">
                    <span class="detail-label">{{ t('umrahlist.passengers') ?? 'Passengers' }}</span>
                    <span class="detail-value">{{ $searchParams['adult'] + $searchParams['child'] + $searchParams['infant'] }}</span>
                </div>
                @if($package->packageType)
                    <div class="booking-detail-item">
                        <span class="detail-label">{{ t('umrahlist.package_type') ?? 'Package Type' }}</span>
                        <span class="detail-value">{{ $package->packageType->packege_type }}</span>
                    </div>
                @endif
            </div>

            <a href="{{ route('umrah.booking', [
                'slug' => \Str::slug($package->name),
                'origin' => $searchParams['origin'],
                'destination' => $searchParams['destination'],
                'departure_date' => $searchParams['departure_date'],
                'return_date' => $searchParams['return_date'],
                'adult' => $searchParams['adult'],
                'child' => $searchParams['child'],
                'infant' => $searchParams['infant'],
                'makkah_nights' => $searchParams['makkah_nights'],
                'madina_nights' => $searchParams['madina_nights']
            ]) }}" class="booking-btn">
                <i class="fas fa-calendar-check"></i>
                {{ t('umrahlist.book_now') ?? 'Book This Package' }}
            </a>

            <div class="safety-badge">
                <i class="fas fa-shield-alt"></i>
                <p>{{ t('umrahlist.secure_booking') ?? 'Secure Booking - Your information is protected' }}</p>
            </div>
        </div>
    </div>
</div>

<script>
    @if($package->images->count() > 0)
        const umrahImages = [
            @foreach($package->images as $image)
                '{{ asset('public/assets/images/' . $image->image) }}',
            @endforeach
        ];
        let currentImageIndex = 0;

        function changeImage(index) {
            currentImageIndex = index;
            document.getElementById('mainImage').src = umrahImages[index];

            document.querySelectorAll('.gallery-thumbnail').forEach((thumb, i) => {
                thumb.classList.toggle('active', i === index);
            });
        }

        function prevImage() {
            currentImageIndex = (currentImageIndex - 1 + umrahImages.length) % umrahImages.length;
            changeImage(currentImageIndex);
        }

        function nextImage() {
            currentImageIndex = (currentImageIndex + 1) % umrahImages.length;
            changeImage(currentImageIndex);
        }
    @endif
</script>

@endsection
