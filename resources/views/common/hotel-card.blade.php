{{--
    Hotel card for the .th-section carousels (home.blade.php, hotel/hotel.blade.php).
    Expects: $fHotel (Hotel with images/location/amenities), $fDetailUrl,
    $fPrice (formatted price string, or null when the hotel has no active room price).
--}}
@php
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
    $fImage = $fHotel->images->first()
        ? asset('public/assets/images/' . $fHotel->images->first()->image_path)
        : 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=400&h=300&fit=crop';
    $fCity = $fHotel->location ? $fHotel->location->city : null;
    $fLocation = $fHotel->location ? $fHotel->location->city . ', ' . $fHotel->location->country : $fHotel->address;
    $fAmenities = $fHotel->amenities->take(3);
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
        <h3 class="th-name" dir="auto"><a href="{{ $fDetailUrl }}">{{ $fHotel->name }}</a></h3>
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
                @if($fPrice !== null)
                    <span class="th-price-label">{{ t('home.starting_from') }}</span>
                    <span class="th-price">{{ $fPrice }}</span>
                @else
                    <span class="th-price-none">{{ t('hotel.contactForPrice') }}</span>
                @endif
            </div>
            <a href="{{ $fDetailUrl }}" class="th-view-btn">{{ t('home.view') }} <i class="fas fa-arrow-right"></i></a>
        </div>
    </div>
</article>
