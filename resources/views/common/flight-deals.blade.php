{{-- Featured flight deals grid (home + /flights). Pass $deals (rows from
     FeaturedFlightDeals::all()) and optionally $sectionId. Each card opens a
     one-way economy search for 1 adult, a week from today. --}}
@php
    $flightDealDate = date('d-m-Y', strtotime('+7 days'));
    $dealCurrency = activeCurrency();
    // Airport names/cities for the cards, from the airports table
    $flightDealAirports = empty($deals) ? collect() : DB::table('flights_airports')
        ->whereIn('code', collect($deals)->flatMap(fn($d) => [$d[0], $d[2]])->unique()->values())
        ->get(['code', 'airport', 'city'])
        ->keyBy('code');
    $dealTitleWords = preg_split('/\s+/u', trim(t('flight.featured_flight_deals')));
    $dealTitleAccent = count($dealTitleWords) > 1 ? array_pop($dealTitleWords) : '';
    $dealTitleLead = implode(' ', $dealTitleWords);
@endphp
<section class="th-section" id="{{ $sectionId ?? 'featuredFlightDeals' }}">
    <div class="th-container">
        <div class="th-header">
            <div class="th-header-text">
                <span class="th-eyebrow">{{ t('flight.featured_flight_deals') }}</span>
                <h2 class="th-title">{{ $dealTitleLead }} @if($dealTitleAccent)<span class="th-title-accent">{{ $dealTitleAccent }}</span>@endif</h2>
            </div>
        </div>

        <div class="home-flight-grid">
            @foreach($deals as [$fromCode, $fromCity, $toCode, $toCity, $airline, $usdPrice, $dealImage, $airlineCode])
                @php
                    $dealUrl = url('flights/' . strtolower($fromCode) . '/' . strtolower($toCode) . "/oneway/economy/{$flightDealDate}/1/0/0");
                    $fromAirport = $flightDealAirports[$fromCode] ?? null;
                    $toAirport = $flightDealAirports[$toCode] ?? null;
                @endphp
                <article class="home-flight-card">
                    <div class="hfc-main">
                        <div class="hfc-airline">
                            <span class="hfc-logo">
                                <img src="https://assets.duffel.com/img/airlines/for-light-background/full-color-logo/{{ $airlineCode }}.svg" alt="{{ $airline }}" loading="lazy"
                                     onerror="this.onerror=null;this.src='{{ url('public/assets/images/list-plane.png') }}';">
                            </span>
                            <span class="hfc-airline-text">
                                <b>{{ $airline }}</b>
                                <small>{{ $airlineCode }}</small>
                            </span>
                            <span class="hfc-badge">{{ t('flightform.one_way') }}</span>
                        </div>

                        <div class="hfc-route">
                            <div class="hfc-point">
                                <b class="hfc-code">{{ $fromCode }}</b>
                                <span class="hfc-city">{{ $fromAirport->city ?? $fromCity }}</span>
                                @if($fromAirport)<small class="hfc-airport">{{ $fromAirport->airport }}</small>@endif
                            </div>
                            <div class="hfc-path" aria-hidden="true">
                                <span class="hfc-track"><i class="fas fa-plane"></i></span>
                            </div>
                            <div class="hfc-point is-arrival">
                                <b class="hfc-code">{{ $toCode }}</b>
                                <span class="hfc-city">{{ $toAirport->city ?? $toCity }}</span>
                                @if($toAirport)<small class="hfc-airport">{{ $toAirport->airport }}</small>@endif
                            </div>
                        </div>

                        <div class="hfc-meta">
                            <span><i class="far fa-calendar"></i> {{ date('D, d M Y', strtotime($flightDealDate)) }}</span>
                        </div>
                    </div>

                    <div class="hfc-fare">
                        <div class="hfc-fare-info">
                            <span class="hfc-fare-type">{{ t('flightform.economy') }}</span>
                            <span class="hfc-fare-label">{{ t('home.starting_from') }}</span>
                            <span class="hfc-price"><small>{{ $dealCurrency->currency_name }}</small> {{ convertCurrency($usdPrice, 'USD', $dealCurrency->currency_name) }}</span>
                            <span class="hfc-per">{{ t('home.per_adult') }}</span>
                        </div>
                        <a href="{{ $dealUrl }}" class="hfc-view-btn">{{ t('home.view') }} <i class="fas fa-arrow-right"></i></a>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
