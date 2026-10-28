{{--
    Umrah package card for the .th-section carousels (home.blade.php, umrah/umrah.blade.php).
    Expects: $package (Umrah with images, highlights, from_location, to_location),
    $detailUrl, $packagePrice (formatted price string) and $umrahLabels — the
    translation keys each page already uses: featured, nights, makkah, madinah,
    hotel, from.
--}}
@php
    $imageUrl = $package->images && $package->images->first()
        ? asset('public/assets/images/' . $package->images->first()->image)
        : 'https://images.unsplash.com/photo-1591604129939-f1efa4d9f7fa?w=400&h=300&fit=crop';
    $highlights = $package->highlights ? array_slice($package->highlights, 0, 3) : [];
    $totalNights = ($package->makkah_nights ?? 0) + ($package->madina_nights ?? 0);
@endphp
<article class="th-card">
    <a href="{{ $detailUrl }}" class="th-card-media">
        <img src="{{ $imageUrl }}" alt="{{ $package->name }}" loading="lazy">
        @if($package->featured == 1)
            <span class="th-badge"><i class="fas fa-star"></i> {{ t($umrahLabels['featured']) }}</span>
        @endif
        @if($totalNights > 0)
            <span class="th-chip"><i class="fas fa-moon"></i> {{ $totalNights }} {{ t($umrahLabels['nights']) }}</span>
        @endif
    </a>
    <div class="th-card-body">
        <h3 class="th-name" dir="auto"><a href="{{ $detailUrl }}">{{ $package->name }}</a></h3>
        @if($package->from_location && $package->to_location)
            <p class="th-location"><i class="fas fa-plane-departure"></i> <span>{{ $package->from_location }} <i class="fas fa-arrow-right th-route-arrow"></i> {{ $package->to_location }}</span></p>
        @endif

        <div class="th-pills">
            @if(count($highlights) > 0)
                @foreach($highlights as $highlight)
                    <span class="th-pill"><i class="fas fa-check-circle"></i> {{ Str::limit($highlight, 15) }}</span>
                @endforeach
            @else
                @if($package->makkah_nights > 0)
                    <span class="th-pill"><i class="fas fa-kaaba"></i> {{ $package->makkah_nights }} {{ t($umrahLabels['makkah']) }}</span>
                @endif
                @if($package->madina_nights > 0)
                    <span class="th-pill"><i class="fas fa-mosque"></i> {{ $package->madina_nights }} {{ t($umrahLabels['madinah']) }}</span>
                @endif
                <span class="th-pill"><i class="fas fa-hotel"></i> {{ t($umrahLabels['hotel']) }}</span>
            @endif
        </div>

        <div class="th-footer">
            <div class="th-price-wrap">
                <span class="th-price-label">{{ t($umrahLabels['from']) }}</span>
                <span class="th-price">{{ $packagePrice }}</span>
            </div>
            <a href="{{ $detailUrl }}" class="th-view-btn">{{ t('home.view') }} <i class="fas fa-arrow-right"></i></a>
        </div>
    </div>
</article>
