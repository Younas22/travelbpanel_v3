@extends('common.layout')
@section('content')
@php
    // Same layout and styles as the tour details page (.td-* in style.css)
    $active_currency = activeCurrency();

    $udImages = $package->images->map(fn($img) => asset('public/assets/images/' . $img->image))->values()->all();
    $udStars = max(0, min(5, (int) $package->stars));
    $udRating = is_numeric($package->rating) && (float) $package->rating > 0 ? (float) $package->rating : null;
    $udType = $package->packageType->packege_type ?? $package->packege_type ?? '';
    $udDesc = trim((string) $package->desc);
    $udFrom = $packageFrom ?? $originAirport;
    $udTo = $packageTo ?? $destinationAirport;

    $adults = (int) $searchParams['adult'];
    $children = (int) $searchParams['child'];
    $infants = (int) $searchParams['infant'];

    // Same pricing as the Umrah booking page (umrah/booking.blade.php)
    $udCurrency = $package->currency ?? 'USD';
    $udAdultPrice = convertCurrency($package->price, $udCurrency, $active_currency->currency_name);
    $childPrice = $package->child_price ?? ($package->price * 0.7);
    $infantPrice = $package->infant_price ?? ($package->price * 0.1);
    $udTotal = convertCurrency(($package->price * $adults) + ($childPrice * $children) + ($infantPrice * $infants), $udCurrency, $active_currency->currency_name);

    $udBookingUrl = route('umrah.booking', [
        'slug' => \Str::slug($package->name),
        'origin' => $searchParams['origin'],
        'destination' => $searchParams['destination'],
        'departure_date' => $searchParams['departure_date'],
        'return_date' => $searchParams['return_date'],
        'adult' => $searchParams['adult'],
        'child' => $searchParams['child'],
        'infant' => $searchParams['infant'],
        'makkah_nights' => $searchParams['makkah_nights'],
        'madina_nights' => $searchParams['madina_nights'],
    ]);
    $udListUrl = route('umrah.search', [
        'origin' => strtolower($searchParams['origin']),
        'destination' => strtolower($searchParams['destination']),
        'departure_date' => $searchParams['departure_date'],
        'return_date' => $searchParams['return_date'],
        'adult' => $searchParams['adult'],
        'child' => $searchParams['child'],
        'infant' => $searchParams['infant'],
        'makkah_nights' => $searchParams['makkah_nights'],
        'madina_nights' => $searchParams['madina_nights'],
    ]);
    $udMapQuery = $udTo && !empty($udTo->late) && !empty($udTo->long)
        ? $udTo->late . ',' . $udTo->long
        : urlencode(trim(($udTo->city ?? $searchParams['destination']) . ' ' . ($udTo->country ?? '')));
@endphp

<div class="td-page">
    <!-- Breadcrumbs -->
    <nav class="td-breadcrumb" aria-label="Breadcrumb">
        <a href="{{ url('/') }}">{{ t('header.Home') }}</a>
        <i class="fas fa-chevron-right" aria-hidden="true"></i>
        <a href="{{ route('umrah.index') }}">{{ t('footer.Umrah') }}</a>
        <i class="fas fa-chevron-right" aria-hidden="true"></i>
        <a href="{{ $udListUrl }}">{{ $originAirport->city ?? $searchParams['origin'] }} – {{ $destinationAirport->city ?? $searchParams['destination'] }}</a>
        <i class="fas fa-chevron-right" aria-hidden="true"></i>
        <span aria-current="page">{{ $package->name }}</span>
    </nav>

    <!-- Header -->
    <div class="td-header">
        <h1 class="td-title" dir="auto">{{ $package->name }}</h1>
        <div class="td-meta">
            @if($udFrom && $udTo)
                <span class="td-meta-item"><i class="fas fa-plane-departure"></i> {{ $udFrom->city }} ({{ $udFrom->code }}) <i class="fas fa-arrow-right"></i> {{ $udTo->city }} ({{ $udTo->code }})</span>
            @endif
            @if($package->duration)
                <span class="td-meta-item"><i class="fas fa-clock"></i> {{ $package->duration }}</span>
            @endif
            @if($udStars > 0)
                <span class="td-stars" aria-label="{{ $udStars }} stars">
                    @for($i = 1; $i <= 5; $i++)<i class="fas fa-star {{ $i <= $udStars ? 'is-on' : '' }}"></i>@endfor
                </span>
            @endif
            @if($udRating !== null)
                <span class="td-score">{{ rtrim(rtrim(number_format($udRating, 1), '0'), '.') }}</span>
            @endif
        </div>
    </div>

    <div class="td-main">
        <div class="td-main-col">
            <!-- Gallery -->
            <div class="td-gallery">
                <div class="td-gallery-grid {{ count($udImages) > 1 ? '' : 'is-single' }}">
                    <div class="td-gallery-main">
                        <img src="{{ $udImages[0] ?? asset('public/assets/images/placeholder/default.jpg') }}" alt="{{ $package->name }}" id="mainImage">
                        @if(count($udImages) > 1)
                            <button type="button" class="td-nav-btn prev" onclick="prevImage()" aria-label="Previous photo"><i class="fas fa-chevron-left"></i></button>
                            <button type="button" class="td-nav-btn next" onclick="nextImage()" aria-label="Next photo"><i class="fas fa-chevron-right"></i></button>
                            <span class="td-photo-count"><i class="fas fa-images"></i> <span id="photoIndex">1</span> / {{ count($udImages) }}</span>
                        @endif
                    </div>
                    @if(count($udImages) > 1)
                        <div class="td-gallery-side">
                            @foreach(array_slice($udImages, 1, 4) as $i => $image)
                                @php $idx = $i + 1; $isLast = $i === min(3, count($udImages) - 2); $more = count($udImages) - 5; @endphp
                                <button type="button" class="gallery-thumbnail td-thumb" data-index="{{ $idx }}"
                                    onclick="{{ $isLast && $more > 0 ? 'toggleAllImages()' : 'changeImage(' . $idx . ')' }}"
                                    aria-label="Photo {{ $idx + 1 }}">
                                    <img src="{{ $image }}" alt="{{ $package->name }} {{ $idx + 1 }}">
                                    @if($isLast && $more > 0)
                                        <span class="td-more">+{{ $more }}</span>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                @if(count($udImages) > 5)
                    <div class="td-all-photos" id="remainingImages" hidden>
                        @foreach($udImages as $index => $image)
                            <button type="button" class="gallery-thumbnail td-thumb" data-index="{{ $index }}" onclick="changeImage({{ $index }}, true)" aria-label="Photo {{ $index + 1 }}">
                                <img src="{{ $image }}" alt="{{ $package->name }} {{ $index + 1 }}" loading="lazy">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Facts bar -->
            <div class="td-facts">
                @if($package->night_in_mekkah)
                    <div class="td-fact"><span class="td-fact-icon"><i class="fas fa-kaaba"></i></span>
                        <span><small>{{ t('umrahlist.makkah') }}</small>{{ $package->night_in_mekkah }} {{ t('umrahlist.nights') }}</span></div>
                @endif
                @if($package->night_in_madina)
                    <div class="td-fact"><span class="td-fact-icon"><i class="fas fa-mosque"></i></span>
                        <span><small>{{ t('umrahlist.madina') }}</small>{{ $package->night_in_madina }} {{ t('umrahlist.nights') }}</span></div>
                @endif
                @if($package->class)
                    <div class="td-fact"><span class="td-fact-icon"><i class="fas fa-plane"></i></span>
                        <span><small>{{ t('umrahlist.flight_class') }}</small>{{ ucfirst($package->class) }}</span></div>
                @endif
                @if($udType)
                    <div class="td-fact"><span class="td-fact-icon"><i class="fas fa-tag"></i></span>
                        <span><small>{{ t('umrahlist.package_type') }}</small>{{ ucfirst($udType) }}</span></div>
                @endif
                <div class="td-fact"><span class="td-fact-icon"><i class="fas fa-users"></i></span>
                    <span><small>{{ t('umrahlist.passengers') }}</small>{{ $adults }} {{ t('umrahlist.adults') }}@if($children > 0), {{ $children }} {{ t('umrahlist.children') }}@endif @if($infants > 0), {{ $infants }} {{ t('umrahlist.infants') }}@endif</span></div>
            </div>

            <!-- Overview -->
            @if($udDesc !== '')
                <div class="td-card">
                    <h2 class="td-section-title">{{ t('umrahlist.overview') }}</h2>
                    <div class="td-clamp" data-td-clamp>
                        <p class="td-text">{!! nl2br(e(strip_tags($udDesc))) !!}</p>
                    </div>
                    <button type="button" class="td-more-btn" data-td-toggle hidden>{{ t('tour.read_more') }} <i class="fas fa-chevron-down"></i></button>
                </div>
            @endif

            <!-- What's included / not included -->
            @if(count($package->inclusion_names) || count($package->exclusion_names))
                <div class="td-card">
                    <div class="td-incl-grid">
                        @if(count($package->inclusion_names))
                            <div>
                                <h2 class="td-section-title">{{ t('umrahlist.whats_included') }}</h2>
                                <ul class="td-list is-included">
                                    @foreach($package->inclusion_names as $inclusion)
                                        <li><i class="fas fa-circle-check"></i> {{ $inclusion }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        @if(count($package->exclusion_names))
                            <div>
                                <h2 class="td-section-title">{{ t('umrahlist.not_included') }}</h2>
                                <ul class="td-list is-excluded">
                                    @foreach($package->exclusion_names as $exclusion)
                                        <li><i class="fas fa-circle-xmark"></i> {{ $exclusion }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Travel information -->
            <div class="td-card">
                <h2 class="td-section-title">{{ t('umrahlist.travel_info') }}</h2>
                <div class="ud-journey">
                    <div class="ud-stop">
                        <span class="td-fact-icon"><i class="fas fa-plane-departure"></i></span>
                        <div>
                            <small>{{ t('umrahlist.departure') }}</small>
                            <b>{{ $originAirport->city ?? $searchParams['origin'] }} ({{ $searchParams['origin'] }})</b>
                            <span>{{ $searchParams['departure_date'] }}</span>
                        </div>
                    </div>
                    <i class="fas fa-arrow-right ud-journey-arrow" aria-hidden="true"></i>
                    <div class="ud-stop">
                        <span class="td-fact-icon"><i class="fas fa-plane-arrival"></i></span>
                        <div>
                            <small>{{ t('umrahlist.destination') }}</small>
                            <b>{{ $destinationAirport->city ?? $searchParams['destination'] }} ({{ $searchParams['destination'] }})</b>
                            <span>{{ t('umrahlist.return') }}: {{ $searchParams['return_date'] }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cancellation Policy -->
            @if($package->policy)
                <div class="td-card">
                    <h2 class="td-section-title">{{ t('umrahlist.cancellation_policy') }}</h2>
                    <div class="td-policy">
                        <i class="fas fa-file-contract"></i>
                        <div class="td-text">{!! nl2br(e(strip_tags($package->policy))) !!}</div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <aside class="td-sidebar">
            <div class="td-card td-summary">
                <h2 class="td-side-title">{{ t('umrahbooking.booking_summary') }}</h2>

                <div class="td-price-box">
                    <span class="td-price-label">{{ t('umrahlist.starting_from') }}</span>
                    <span class="td-price">{{ $active_currency->currency_name }} {{ number_format($udAdultPrice) }}</span>
                    <span class="td-price-unit">{{ t('umrahlist.per_person') }}</span>
                </div>

                <div class="td-row"><span><i class="fas fa-calendar"></i> {{ t('umrahlist.departure') }}</span><b>{{ $searchParams['departure_date'] }}</b></div>
                <div class="td-row"><span><i class="fas fa-calendar"></i> {{ t('umrahlist.return') }}</span><b>{{ $searchParams['return_date'] }}</b></div>
                @if($package->duration)
                    <div class="td-row"><span><i class="fas fa-clock"></i> {{ t('umrahlist.duration') }}</span><b>{{ $package->duration }}</b></div>
                @endif
                @if($package->night_in_mekkah)
                    <div class="td-row"><span><i class="fas fa-kaaba"></i> {{ t('umrahlist.makkah') }}</span><b>{{ $package->night_in_mekkah }} {{ t('umrahlist.nights') }}</b></div>
                @endif
                @if($package->night_in_madina)
                    <div class="td-row"><span><i class="fas fa-mosque"></i> {{ t('umrahlist.madina') }}</span><b>{{ $package->night_in_madina }} {{ t('umrahlist.nights') }}</b></div>
                @endif
                <div class="td-row"><span><i class="fas fa-users"></i> {{ t('umrahlist.passengers') }}</span><b>{{ $adults + $children + $infants }}</b></div>

                <div class="td-total">
                    <span>{{ t('umrahbooking.total_amount') }}</span>
                    <b>{{ $active_currency->currency_name }} {{ number_format($udTotal) }}</b>
                </div>

                <a href="{{ $udBookingUrl }}" class="td-book-btn">
                    <i class="fas fa-calendar-check"></i> {{ t('umrahlist.book_now') }}
                </a>
                <p class="td-secure"><i class="fas fa-shield-halved"></i> {{ t('umrahlist.secure_booking') }}</p>
            </div>

            <div class="td-card">
                <h2 class="td-side-title">{{ t('umrah.whyBookTitle') }}</h2>
                <ul class="td-why">
                    <li><i class="fas fa-circle-check"></i> {{ t('umrah.sacredAccommodations') }}</li>
                    <li><i class="fas fa-circle-check"></i> {{ t('umrah.expertGuides') }}</li>
                    <li><i class="fas fa-circle-check"></i> {{ t('umrah.hassleFreeTravel') }}</li>
                    <li><i class="fas fa-circle-check"></i> {{ t('umrah.support247') }}</li>
                </ul>
            </div>

            <div class="td-card">
                <h2 class="td-side-title">{{ t('umrahlist.destination') }}</h2>
                <div class="td-address"><i class="fas fa-location-dot"></i> {{ $udTo->airport ?? ($udTo->city ?? $searchParams['destination']) }}@if(!empty($udTo->city)), {{ $udTo->city }}@endif</div>
                <div class="td-map">
                    <iframe src="https://maps.google.com/maps?q={{ $udMapQuery }}&z=11&output=embed" title="{{ $udTo->city ?? 'Destination' }} map" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
                <a class="td-map-link" href="https://www.google.com/maps/search/?api=1&query={{ $udMapQuery }}" target="_blank" rel="noopener">
                    <i class="fas fa-up-right-from-square"></i> Open in Google Maps
                </a>
            </div>
        </aside>
    </div>
</div>

<script>
    const umrahImages = @json($udImages);
    let currentImageIndex = 0;

    function changeImage(index, scrollUp) {
        if (!umrahImages.length) return;
        currentImageIndex = index;
        document.getElementById('mainImage').src = umrahImages[index];
        const counter = document.getElementById('photoIndex');
        if (counter) counter.textContent = index + 1;
        document.querySelectorAll('.gallery-thumbnail').forEach(thumb => {
            thumb.classList.toggle('active', parseInt(thumb.dataset.index, 10) === index);
        });
        if (scrollUp) document.querySelector('.td-gallery').scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function prevImage() {
        currentImageIndex = (currentImageIndex - 1 + umrahImages.length) % umrahImages.length;
        changeImage(currentImageIndex);
    }

    function nextImage() {
        currentImageIndex = (currentImageIndex + 1) % umrahImages.length;
        changeImage(currentImageIndex);
    }

    function toggleAllImages() {
        const all = document.getElementById('remainingImages');
        if (!all) return;
        all.hidden = !all.hidden;
        if (!all.hidden) all.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    document.querySelectorAll('[data-td-clamp]').forEach(function (box) {
        const btn = box.parentElement.querySelector('[data-td-toggle]');
        if (!btn || box.scrollHeight <= box.clientHeight + 2) return;
        btn.hidden = false;
        const more = btn.innerHTML;
        btn.addEventListener('click', function () {
            const open = box.classList.toggle('is-open');
            btn.innerHTML = open ? '<i class="fas fa-chevron-up"></i>' : more;
        });
    });
</script>

@endsection
