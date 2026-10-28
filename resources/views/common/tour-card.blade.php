{{--
    Tour card for the .th-section carousels (home.blade.php, tour/tour.blade.php).
    Expects: $tour (Tour with images, location_name, highlights), $detailUrl,
    $tourPrice (formatted price string).
--}}
@php
    $imageUrl = $tour->images && $tour->images->first()
        ? asset('public/assets/images/' . $tour->images->first()->image)
        : 'https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?w=400&h=300&fit=crop';
    $highlights = $tour->highlights ? array_slice($tour->highlights, 0, 3) : [];
@endphp
<article class="th-card">
    <a href="{{ $detailUrl }}" class="th-card-media">
        <img src="{{ $imageUrl }}" alt="{{ $tour->name }}" loading="lazy">
        @if($tour->featured == 1)
            <span class="th-badge"><i class="fas fa-star"></i> {{ t('tour.popular') }}</span>
        @endif
        @if($tour->days)
            <span class="th-chip"><i class="fas fa-clock"></i> {{ $tour->days }} {{ $tour->days > 1 ? t('tour.days') : t('tour.day') }}</span>
        @endif
    </a>
    <div class="th-card-body">
        <h3 class="th-name" dir="auto"><a href="{{ $detailUrl }}">{{ $tour->name }}</a></h3>
        @if($tour->location_name)
            <p class="th-location"><i class="fas fa-location-dot"></i> <span>{{ $tour->location_name }}</span></p>
        @endif

        <div class="th-pills">
            @if(count($highlights) > 0)
                @foreach($highlights as $highlight)
                    <span class="th-pill"><i class="fas fa-check-circle"></i> {{ Str::limit($highlight, 15) }}</span>
                @endforeach
            @else
                <span class="th-pill"><i class="fas fa-hotel"></i> {{ t('tour.hotel') }}</span>
                <span class="th-pill"><i class="fas fa-utensils"></i> {{ t('tour.meals') }}</span>
                <span class="th-pill"><i class="fas fa-bus"></i> {{ t('tour.transport') }}</span>
            @endif
        </div>

        <div class="th-footer">
            <div class="th-price-wrap">
                <span class="th-price-label">{{ t('tour.starting_from') }}</span>
                <span class="th-price">{{ $tourPrice }}</span>
            </div>
            <a href="{{ $detailUrl }}" class="th-view-btn">{{ t('home.view') }} <i class="fas fa-arrow-right"></i></a>
        </div>
    </div>
</article>
