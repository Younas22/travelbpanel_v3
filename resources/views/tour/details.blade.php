@extends('common.layout')
@section('content')
    @php
        $active_currency = activeCurrency();

        $tdImages = $tour->images->map(fn($img) => asset('public/assets/images/' . $img->image))->values()->all();
        $tdLocation = trim((string) ($tour->location_name ?? ''));
        if (!empty($tour->location_country) && stripos($tdLocation, $tour->location_country) === false) {
            $tdLocation = trim($tdLocation . ', ' . $tour->location_country, ', ');
        }
        $tdHasCoords = !empty($tour->location_lat) && !empty($tour->location_lng);
        $tdStars = max(0, min(5, (int) $tour->stars));
        $tdRating = is_numeric($tour->rating) && (float) $tour->rating > 0 ? (float) $tour->rating : null;
        $tdDescription = trim((string) ($tour->desc ?? $tour->description ?? ''));
        $tdType = $tour->packageType->packege_type ?? $tour->packege_type ?? '';

        $adults = (int) ($searchParams['adult'] ?? 2);
        $children = (int) ($searchParams['child'] ?? 0);

        // Same pricing as the tour booking page (tour/booking.blade.php)
        $tdAdultPrice = convertCurrency($tour->price, $tour->currency ?? 'USD', $active_currency->currency_name);
        $tdChildPrice = $tour->child_price ?? ($tdAdultPrice * 0.7);
        $tdTotal = ($tdAdultPrice * $adults) + ($tdChildPrice * $children);

        $tdBookingUrl = route('tour.booking', [
            'slug' => \Str::slug($tour->name),
            'location' => $searchParams['location'],
            'type' => $searchParams['type'],
            'startDate' => $searchParams['original_start_date'],
            'endDate' => $searchParams['original_end_date'],
            'adult' => $searchParams['adult'],
            'child' => $searchParams['child'],
        ]);
    @endphp

<div class="td-page">
    <!-- Breadcrumbs -->
    <nav class="td-breadcrumb" aria-label="Breadcrumb">
        <a href="{{ url('/') }}">{{ t('header.Home') }}</a>
        <i class="fas fa-chevron-right" aria-hidden="true"></i>
        <a href="{{ route('tours.index') }}">{{ t('footer.Tours') }}</a>
        @if(!empty($tour->location_name))
            <i class="fas fa-chevron-right" aria-hidden="true"></i>
            <span>{{ $tour->location_name }}</span>
        @endif
        <i class="fas fa-chevron-right" aria-hidden="true"></i>
        <span aria-current="page">{{ $tour->name }}</span>
    </nav>

    <!-- Header -->
    <div class="td-header">
        <h1 class="td-title" dir="auto">{{ $tour->name }}</h1>
        <div class="td-meta">
            @if($tdLocation)
                <span class="td-meta-item"><i class="fas fa-location-dot"></i> {{ $tdLocation }}</span>
            @endif
            @if($tour->duration)
                <span class="td-meta-item"><i class="fas fa-clock"></i> {{ $tour->duration }}</span>
            @endif
            @if($tdStars > 0)
                <span class="td-stars" aria-label="{{ $tdStars }} stars">
                    @for($i = 1; $i <= 5; $i++)<i class="fas fa-star {{ $i <= $tdStars ? 'is-on' : '' }}"></i>@endfor
                </span>
            @endif
            @if($tdRating !== null)
                <span class="td-score">{{ rtrim(rtrim(number_format($tdRating, 1), '0'), '.') }}</span>
            @endif
        </div>
    </div>

    <div class="td-main">
        <div class="td-main-col">
            <!-- Gallery -->
            <div class="td-gallery">
                <div class="td-gallery-grid {{ count($tdImages) > 1 ? '' : 'is-single' }}">
                    <div class="td-gallery-main">
                        <img src="{{ $tdImages[0] ?? asset('public/assets/images/placeholder/default.jpg') }}" alt="{{ $tour->name }}" id="mainImage">
                        @if(count($tdImages) > 1)
                            <button type="button" class="td-nav-btn prev" onclick="prevImage()" aria-label="Previous photo"><i class="fas fa-chevron-left"></i></button>
                            <button type="button" class="td-nav-btn next" onclick="nextImage()" aria-label="Next photo"><i class="fas fa-chevron-right"></i></button>
                            <span class="td-photo-count"><i class="fas fa-images"></i> <span id="photoIndex">1</span> / {{ count($tdImages) }}</span>
                        @endif
                    </div>
                    @if(count($tdImages) > 1)
                        <div class="td-gallery-side">
                            @foreach(array_slice($tdImages, 1, 4) as $i => $image)
                                @php $idx = $i + 1; $isLast = $i === min(3, count($tdImages) - 2); $more = count($tdImages) - 5; @endphp
                                <button type="button" class="gallery-thumbnail td-thumb" data-index="{{ $idx }}"
                                    onclick="{{ $isLast && $more > 0 ? 'toggleAllImages()' : 'changeImage(' . $idx . ')' }}"
                                    aria-label="Photo {{ $idx + 1 }}">
                                    <img src="{{ $image }}" alt="{{ $tour->name }} {{ $idx + 1 }}">
                                    @if($isLast && $more > 0)
                                        <span class="td-more">+{{ $more }}</span>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                @if(count($tdImages) > 5)
                    <div class="td-all-photos" id="remainingImages" hidden>
                        @foreach($tdImages as $index => $image)
                            <button type="button" class="gallery-thumbnail td-thumb" data-index="{{ $index }}" onclick="changeImage({{ $index }}, true)" aria-label="Photo {{ $index + 1 }}">
                                <img src="{{ $image }}" alt="{{ $tour->name }} {{ $index + 1 }}" loading="lazy">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Facts bar -->
            <div class="td-facts">
                @if($tour->days)
                    <div class="td-fact"><span class="td-fact-icon"><i class="fas fa-sun"></i></span>
                        <span><small>{{ t('tourlist.duration') }}</small>{{ $tour->days }} {{ t('tourlist.days') }}@if($tour->nights) / {{ $tour->nights }} {{ t('tourlist.nights') }}@endif</span></div>
                @elseif($tour->duration)
                    <div class="td-fact"><span class="td-fact-icon"><i class="fas fa-clock"></i></span>
                        <span><small>{{ t('tourlist.duration') }}</small>{{ $tour->duration }}</span></div>
                @endif
                @if($tdType)
                    <div class="td-fact"><span class="td-fact-icon"><i class="fas fa-tag"></i></span>
                        <span><small>{{ t('tourlist.tour_type') }}</small>{{ $tdType }}</span></div>
                @endif
                @if($tour->location_name)
                    <div class="td-fact"><span class="td-fact-icon"><i class="fas fa-location-dot"></i></span>
                        <span><small>{{ t('tourlist.destination') }}</small>{{ $tour->location_name }}</span></div>
                @endif
                <div class="td-fact"><span class="td-fact-icon"><i class="fas fa-users"></i></span>
                    <span><small>{{ t('tourlist.travelers') }}</small>{{ $adults }} {{ t('tourlist.adults') }}@if($children > 0), {{ $children }} {{ t('tourlist.children') }}@endif</span></div>
            </div>

            <!-- Overview -->
            @if($tdDescription !== '')
                <div class="td-card">
                    <h2 class="td-section-title">{{ t('tourlist.overview') }}</h2>
                    <div class="td-clamp" data-td-clamp>
                        <p class="td-text">{!! nl2br(e($tdDescription)) !!}</p>
                    </div>
                    <button type="button" class="td-more-btn" data-td-toggle hidden>{{ t('tour.read_more') }} <i class="fas fa-chevron-down"></i></button>
                </div>
            @endif

            <!-- What's included / not included -->
            @if(count($tour->inclusion_names) || count($tour->exclusion_names))
                <div class="td-card">
                    <div class="td-incl-grid">
                        @if(count($tour->inclusion_names))
                            <div>
                                <h2 class="td-section-title">{{ t('tourlist.whats_included') }}</h2>
                                <ul class="td-list is-included">
                                    @foreach($tour->inclusion_names as $inclusion)
                                        <li><i class="fas fa-circle-check"></i> {{ $inclusion }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        @if(count($tour->exclusion_names))
                            <div>
                                <h2 class="td-section-title">{{ t('tourlist.not_included') }}</h2>
                                <ul class="td-list is-excluded">
                                    @foreach($tour->exclusion_names as $exclusion)
                                        <li><i class="fas fa-circle-xmark"></i> {{ $exclusion }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Cancellation Policy -->
            @if($tour->policy)
                <div class="td-card">
                    <h2 class="td-section-title">{{ t('tourlist.cancellation_policy') }}</h2>
                    <div class="td-policy">
                        <i class="fas fa-file-contract"></i>
                        <div class="td-text">{!! nl2br(e($tour->policy)) !!}</div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <aside class="td-sidebar">
            <!-- Booking summary -->
            <div class="td-card td-summary">
                <h2 class="td-side-title">{{ t('tourbooking.booking_summary') }}</h2>

                <div class="td-price-box">
                    <span class="td-price-label">{{ t('tourlist.starting_from') }}</span>
                    <span class="td-price">{{ $active_currency->currency_name }} {{ number_format($tdAdultPrice) }}</span>
                    <span class="td-price-unit">{{ t('tourlist.per_person') }}</span>
                </div>

                <div class="td-row"><span><i class="fas fa-calendar"></i> {{ t('tourbooking.departure_date') }}</span><b>{{ \Carbon\Carbon::parse($searchParams['start_date'])->format('d M Y') }}</b></div>
                <div class="td-row"><span><i class="fas fa-calendar"></i> {{ t('tourbooking.return_date') }}</span><b>{{ \Carbon\Carbon::parse($searchParams['end_date'])->format('d M Y') }}</b></div>
                @if($tour->duration)
                    <div class="td-row"><span><i class="fas fa-clock"></i> {{ t('tourbooking.duration') }}</span><b>{{ $tour->duration }}</b></div>
                @endif
                @if($tdType)
                    <div class="td-row"><span><i class="fas fa-tag"></i> {{ t('tourbooking.tour_type') }}</span><b>{{ $tdType }}</b></div>
                @endif
                <div class="td-row"><span><i class="fas fa-users"></i> {{ t('tourbooking.travellers') }}</span><b>{{ $adults }} {{ t('tourlist.adults') }}@if($children > 0), {{ $children }} {{ t('tourlist.children') }}@endif</b></div>

                <div class="td-total">
                    <span>{{ t('tourbooking.total_amount') }}</span>
                    <b>{{ $active_currency->currency_name }} {{ number_format($tdTotal) }}</b>
                </div>

                <a href="{{ $tdBookingUrl }}" class="td-book-btn">
                    <i class="fas fa-calendar-check"></i> {{ t('tourlist.book_now') }}
                </a>
                <p class="td-secure"><i class="fas fa-shield-halved"></i> {{ t('tourlist.secure_booking') }}</p>
            </div>

            <!-- Why book -->
            <div class="td-card">
                <h2 class="td-side-title">{{ t('tour.why_book_title') }}</h2>
                <ul class="td-why">
                    <li><i class="fas fa-circle-check"></i> {{ t('tour.curated_tours') }}</li>
                    <li><i class="fas fa-circle-check"></i> {{ t('tour.expert_guides') }}</li>
                    <li><i class="fas fa-circle-check"></i> {{ t('tour.safe_travel') }}</li>
                    <li><i class="fas fa-circle-check"></i> {{ t('tour.support_24_7') }}</li>
                </ul>
            </div>

            <!-- Location & map (from the tour's own location record) -->
            @if($tdLocation)
                @php $tdMapQuery = $tdHasCoords ? $tour->location_lat . ',' . $tour->location_lng : urlencode($tdLocation); @endphp
                <div class="td-card">
                    <h2 class="td-side-title">{{ t('tourlist.destination') }}</h2>
                    <div class="td-address"><i class="fas fa-location-dot"></i> {{ $tdLocation }}</div>
                    <div class="td-map">
                        <iframe src="https://maps.google.com/maps?q={{ $tdMapQuery }}&z=11&output=embed" title="{{ $tdLocation }} map" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                    <a class="td-map-link" href="https://www.google.com/maps/search/?api=1&query={{ $tdMapQuery }}" target="_blank" rel="noopener">
                        <i class="fas fa-up-right-from-square"></i> Open in Google Maps
                    </a>
                </div>
            @endif
        </aside>
    </div>
</div>

<script>
    const tourImages = @json($tdImages);
    let currentImageIndex = 0;

    function changeImage(index, scrollUp) {
        if (!tourImages.length) return;
        currentImageIndex = index;
        document.getElementById('mainImage').src = tourImages[index];
        const counter = document.getElementById('photoIndex');
        if (counter) counter.textContent = index + 1;
        document.querySelectorAll('.gallery-thumbnail').forEach(thumb => {
            thumb.classList.toggle('active', parseInt(thumb.dataset.index, 10) === index);
        });
        if (scrollUp) document.querySelector('.td-gallery').scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function prevImage() {
        currentImageIndex = (currentImageIndex - 1 + tourImages.length) % tourImages.length;
        changeImage(currentImageIndex);
    }

    function nextImage() {
        currentImageIndex = (currentImageIndex + 1) % tourImages.length;
        changeImage(currentImageIndex);
    }

    function toggleAllImages() {
        const all = document.getElementById('remainingImages');
        if (!all) return;
        all.hidden = !all.hidden;
        if (!all.hidden) all.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    // Read more / show less for a long overview
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
