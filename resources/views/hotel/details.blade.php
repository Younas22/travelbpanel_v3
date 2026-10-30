@extends('common.layout')
@section('page_class', 'view-hotel-details')

@section('content')

    @foreach($details as $hotel)
        @php
            $hdImages   = $hotel['imgs'] ?? [];
            $hdStars    = (int) filter_var($hotel['stars'] ?? 0, FILTER_SANITIZE_NUMBER_INT);
            $hdNights   = max(1, (int) \Carbon\Carbon::parse($hotel['checkin'])->diffInDays(\Carbon\Carbon::parse($hotel['checkout'])));
            // review score is only shown when the supplier sends one on a 0–100 scale
            $hdRating   = (float) ($hotel['rating'] ?? 0);
            $hdScore    = ($hdRating >= 10 && $hdRating <= 100) ? round($hdRating / 10, 1) : null;
            $hdAmenities = $hotel['amenities'] ?? [];
            $hdHasCoords = !empty($hotel['lat']) && !empty($hotel['lng']);
            $hdLocation  = trim(($hotel['city'] ?? '') . (!empty($hotel['country']) ? ', ' . $hotel['country'] : ''), ', ');
            $hdRefundableOption = collect($hotel['rooms'] ?? [])->flatMap(fn($r) => $r['options'] ?? [])->contains(fn($o) => empty($o['non_refundable']));
            $hdSearch = $hotel_search ?? [];
            $hdListUrl = null;
            if (!empty($hdSearch['city']) && !empty($hdSearch['checkin']) && !empty($hdSearch['checkout'])) {
                $hdListUrl = url('hotels/' . $hdSearch['city'] . '/' . date('d-m-Y', strtotime($hdSearch['checkin'])) . '/' . date('d-m-Y', strtotime($hdSearch['checkout']))
                    . '/' . ($hdSearch['adults'] ?? 2) . '/' . ($hdSearch['childs'] ?? 0) . '/' . ($hdSearch['rooms'] ?? 1) . '/' . ($hdSearch['nationality'] ?? 'PK'));
            }
        @endphp
        <div class="hotel-details-container">

            <!-- Breadcrumbs -->
            <nav class="hd-breadcrumb" aria-label="Breadcrumb">
                <a href="{{ url('/') }}">{{ t('header.Home') }}</a>
                <i class="fas fa-chevron-right" aria-hidden="true"></i>
                <a href="{{ route('hotels.index') }}">{{ t('footer.Stay') }}</a>
                @if(!empty($hotel['city']))
                    <i class="fas fa-chevron-right" aria-hidden="true"></i>
                    @if($hdListUrl)
                        <a href="{{ $hdListUrl }}">{{ ucwords(strtolower($hotel['city'])) }}</a>
                    @else
                        <span>{{ ucwords(strtolower($hotel['city'])) }}</span>
                    @endif
                @endif
                <i class="fas fa-chevron-right" aria-hidden="true"></i>
                <span aria-current="page">{{ $hotel['h_name'] }}</span>
            </nav>

            <!-- Header -->
            <div class="hotel-details-header">
                <h1 class="hotel-details-title" dir="auto">{{ $hotel['h_name'] }}</h1>
                <div class="hotel-details-meta">
                    @if(!empty($hotel['address']))
                    <div class="hotel-meta-item">
                        <i class="fas fa-location-dot hotel-meta-icon"></i>
                        <span>{{ $hotel['address'] }}</span>
                    </div>
                    @endif
                    @if($hdStars > 0)
                    <div class="hotel-rating-badge" title="{{ $hdStars }} {{ t('hotel.stars') }}">
                        <span class="hotel-rating-stars">
                            @for($i = 1; $i <= 5; $i++)<i class="fas fa-star {{ $i <= $hdStars ? 'is-on' : '' }}"></i>@endfor
                        </span>
                        <span class="hotel-rating-score">{{ $hdStars }} {{ t('hotel.stars') }}</span>
                    </div>
                    @endif
                    @if($hdScore !== null)
                    <div class="hd-score">{{ $hdScore }}<small>/10</small></div>
                    @endif
                </div>
            </div>

            <!-- Main Content -->
            <div class="hotel-details-main">
                <div class="hd-main-col">
                    <!-- Image Gallery -->
                    <div class="hotel-gallery">
                        <div class="hd-gallery-grid {{ count($hdImages) > 1 ? '' : 'is-single' }}">
                            <div class="hotel-gallery-main">
                                <img src="{{ $hdImages[0] ?? 'https://via.placeholder.com/800x400' }}" alt="{{ $hotel['h_name'] }}" id="mainImage">
                                @if(count($hdImages) > 1)
                                <button type="button" class="gallery-nav-btn prev" onclick="prevImage()" aria-label="Previous photo"><i class="fas fa-chevron-left"></i></button>
                                <button type="button" class="gallery-nav-btn next" onclick="nextImage()" aria-label="Next photo"><i class="fas fa-chevron-right"></i></button>
                                <span class="hd-photo-count"><i class="fas fa-images"></i> <span id="photoIndex">1</span> / {{ count($hdImages) }}</span>
                                @endif
                            </div>
                            @if(count($hdImages) > 1)
                            <div class="hd-gallery-side">
                                @foreach(array_slice($hdImages, 1, 4) as $i => $image)
                                    @php $idx = $i + 1; $isLast = $i === min(3, count($hdImages) - 2); $more = count($hdImages) - 5; @endphp
                                    <button type="button" class="gallery-thumbnail" data-index="{{ $idx }}"
                                        onclick="{{ $isLast && $more > 0 ? 'toggleAllImages()' : 'changeImage(' . $idx . ')' }}"
                                        aria-label="{{ $isLast && $more > 0 ? str_replace(':count', count($hdImages), t('hoteldetails.view_all_images')) : 'Photo ' . ($idx + 1) }}">
                                        <img src="{{ $image }}" alt="Hotel Image {{ $idx + 1 }}">
                                        @if($isLast && $more > 0)
                                            <span class="hd-more">+{{ $more }}</span>
                                        @endif
                                    </button>
                                @endforeach
                            </div>
                            @endif
                        </div>

                        @if(count($hdImages) > 5)
                            <button type="button" class="view-all-images-btn" onclick="toggleAllImages()">
                                <span id="viewAllText">{{ str_replace(':count', count($hdImages), t('hoteldetails.view_all_images')) }}</span>
                                <i class="fas fa-chevron-down" id="viewAllIcon"></i>
                            </button>
                            <div class="remaining-images" id="remainingImages">
                                @foreach($hdImages as $index => $image)
                                    <button type="button" class="gallery-thumbnail" data-index="{{ $index }}" onclick="changeImage({{ $index }}, true)" aria-label="Photo {{ $index + 1 }}">
                                        <img src="{{ $image }}" alt="Hotel Image {{ $index + 1 }}" loading="lazy">
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- Highlights bar (from the hotel's own data) -->
                    <div class="hd-facts">
                        @if($hdStars > 0)
                            <div class="hd-fact"><span class="hd-fact-icon"><i class="fas fa-star"></i></span> {{ $hdStars }} {{ t('hotel.stars') }} {{ t('hoteldetails.hotel_label') }}</div>
                        @endif
                        @if($hdLocation)
                            <div class="hd-fact"><span class="hd-fact-icon"><i class="fas fa-location-dot"></i></span> {{ ucwords(strtolower($hdLocation)) }}</div>
                        @endif
                        @if($hdRefundableOption)
                            <div class="hd-fact"><span class="hd-fact-icon"><i class="fas fa-rotate-left"></i></span> {{ t('hoteldetails.refundable') }}</div>
                        @endif
                        @if(count($hdImages))
                            <div class="hd-fact"><span class="hd-fact-icon"><i class="fas fa-images"></i></span> {{ count($hdImages) }} Photos</div>
                        @endif
                        @foreach(array_slice($hdAmenities, 0, 2) as $amenity)
                            <div class="hd-fact"><span class="hd-fact-icon"><i class="fas fa-check"></i></span> {{ $amenity }}</div>
                        @endforeach
                    </div>

                    <!-- Description -->
                    @if(trim((string) ($hotel['desc'] ?? '')) !== '')
                    <div class="hotel-description">
                        <div class="section-title">{{ t('hoteldetails.about_hotel') }}</div>
                        <div class="hd-clamp" data-hd-clamp>
                            <div class="hotel-description-text">{!! richText($hotel['desc']) !!}</div>
                        </div>
                        <button type="button" class="hd-more-btn" data-hd-toggle hidden>Read more <i class="fas fa-chevron-down"></i></button>
                    </div>
                    @endif

                    <!-- Highlights -->
                    <div class="hotel-description">
                        <div class="section-title">{{ t('hoteldetails.highlights') }}</div>
                        <div class="hotel-highlights">
                            @if(isset($hotel['refundable']) && $hotel['refundable'])
                                <div class="highlight-item">
                                    <i class="fas fa-check"></i>
                                    {{ t('hoteldetails.free_cancellation') }}
                                </div>
                            @endif
                            <div class="highlight-item">
                                <i class="fas fa-check"></i>
                                {{ t('hoteldetails.prime_location') }}
                            </div>
                            <div class="highlight-item">
                                <i class="fas fa-check"></i>
                                {{ $hotel['stars'] }} {{ t('hoteldetails.hotel_label') }}
                            </div>
                            @foreach(array_slice($hdAmenities, 0, 6) as $amenity)
                                <div class="highlight-item">
                                    <i class="fas fa-check"></i>
                                    {{ $amenity }}
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Rooms -->
                    <div class="hotel-rooms">
                        <div class="section-title">{{ str_replace(':count', count($hotel['rooms']), t('hoteldetails.available_rooms')) }}</div>

                        @foreach($hotel['rooms'] as $roomIndex => $room)
                            @php
                                $firstOption = $room['options'][0] ?? [];
                                $roomAmenities = $room['amenities'] ?? [];
                                $roomImages = $room['images'] ?? [];
                            @endphp
                            <div class="hd-room">
                                <!-- Room Header -->
                                <div class="room-card">
                                    <div class="room-card-header">
                                        <img src="{{ $roomImages[0] ?? $hdImages[0] ?? asset('public/assets/images/placeholder/default.jpg') }}" alt="{{ $room['name'] }}" class="room-card-image" loading="lazy">
                                        <div class="room-card-info">
                                            <div class="room-name" dir="auto">{{ $room['name'] }}</div>
                                            <div class="room-features">
                                                @if(!empty($firstOption))
                                                    <span><i class="fas fa-user-group"></i> {{ $firstOption['adults'] }} {{ $firstOption['adults'] > 1 ? t('hoteldetails.adults') : t('hoteldetails.adult') }}@if(($firstOption['child'] ?? 0) > 0), {{ $firstOption['child'] }} {{ $firstOption['child'] > 1 ? t('hoteldetails.children') : t('hoteldetails.child') }}@endif</span>
                                                @endif
                                                <span><i class="fas fa-bed"></i> {{ $room['name'] }}</span>
                                                @foreach(array_slice($roomAmenities, 0, 3) as $amenity)
                                                    <span><i class="fas fa-check"></i> {{ $amenity }}</span>
                                                @endforeach
                                            </div>
                                            @if(count($roomAmenities) > 3 || count($roomImages) > 1)
                                                <button type="button" class="hd-room-toggle" data-hd-room-toggle aria-expanded="false">Room details <i class="fas fa-chevron-down"></i></button>
                                            @endif
                                        </div>
                                    </div>
                                    @if(count($roomAmenities) > 3 || count($roomImages) > 1)
                                    <div class="hd-room-details" hidden>
                                        @if(count($roomAmenities))
                                            <div class="hd-room-amenities">
                                                @foreach($roomAmenities as $amenity)
                                                    <span><i class="fas fa-check"></i> {{ $amenity }}</span>
                                                @endforeach
                                            </div>
                                        @endif
                                        @if(count($roomImages) > 1)
                                            <div class="hd-room-photos">
                                                @foreach(array_slice($roomImages, 0, 6) as $roomImage)
                                                    <img src="{{ $roomImage }}" alt="{{ $room['name'] }}" loading="lazy">
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                    @endif
                                </div>

                                <!-- Booking options / rate plans -->
                                <div class="hd-options-title">{{ str_replace(':count', count($room['options']), t('hoteldetails.booking_options')) }}</div>
                                <div class="room-options-grid">
                                    @foreach($room['options'] as $optionIndex => $option)
                                        <div class="room-option-card">
                                            <div class="hd-option-main">
                                                <div class="option-header">
                                                    <div class="option-title">{{ !empty($option['description']) ? $option['description'] : t('hoteldetails.option') . ' ' . ($optionIndex + 1) }}</div>
                                                    @if(!empty($option['non_refundable']))
                                                        <span class="option-badge non-refundable">
                                                            <i class="fas fa-times-circle"></i> {{ t('hoteldetails.non_refundable') }}
                                                        </span>
                                                    @else
                                                        <span class="option-badge refundable">
                                                            <i class="fas fa-check-circle"></i> {{ t('hoteldetails.refundable') }}
                                                        </span>
                                                    @endif
                                                </div>

                                                @if(!empty($option['cancel_restricted']))
                                                    <div class="option-notice restricted">
                                                        <i class="fas fa-ban"></i>
                                                        <span>{{ $option['cancel_restricted_note'] ?? 'Cancellation not allowed' }}</span>
                                                    </div>
                                                @endif

                                                @if(!empty($option['cancellation_rules']))
                                                    <div class="option-taxes">
                                                        <div class="option-taxes-title"><i class="fas fa-calendar-xmark"></i> {{ t('hoteldetails.cancellation_policy') }}</div>
                                                        @foreach($option['cancellation_rules'] as $rule)
                                                            <div class="option-taxes-row">
                                                                <span>
                                                                    @if($rule['type'] === 'no_show')
                                                                        {{ $rule['description'] }}
                                                                    @elseif($rule['from_date'] || $rule['to_date'])
                                                                        {{ $rule['from_date'] ? \Carbon\Carbon::parse($rule['from_date'])->format('d M Y H:i') : t('hoteldetails.booking_date') }}
                                                                        &ndash;
                                                                        {{ $rule['to_date'] ? \Carbon\Carbon::parse($rule['to_date'])->format('d M Y H:i') : t('hoteldetails.checkin_label') }}
                                                                    @else
                                                                        {{ $rule['description'] }}
                                                                    @endif
                                                                </span>
                                                                <span>
                                                                    @if($rule['type'] === 'restricted')
                                                                        <span class="tag at-property">{{ t('hoteldetails.not_allowed') }}</span>
                                                                    @elseif($rule['cancel_charge'] > 0)
                                                                        {{ number_format($rule['cancel_charge'], 2) }} {{ $room['currency'] }}
                                                                    @else
                                                                        <span class="tag included">{{ t('hoteldetails.free') }}</span>
                                                                    @endif
                                                                </span>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif

                                                @if(!empty($option['specials']))
                                                    @foreach($option['specials'] as $special)
                                                        @if(!empty($special['name']))
                                                            <div class="option-notice promotion">
                                                                <i class="fas fa-tag"></i>
                                                                <span>{{ $special['name'] }}{{ !empty($special['discount']) ? ' (' . $special['discount'] . '% off)' : '' }}</span>
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                @endif

                                                @if(!empty($option['changed_occupancy']))
                                                    <div class="option-notice occupancy">
                                                        <i class="fas fa-exclamation-triangle"></i>
                                                        <span>{{ t('hoteldetails.changed_occupancy_note') }}</span>
                                                    </div>
                                                @endif

                                                @if(!empty($option['min_stay']) && (int) $option['min_stay'] > 0)
                                                    <div class="option-notice min-stay">
                                                        <i class="fas fa-calendar-day"></i>
                                                        <span>{{ str_replace(':nights', $option['min_stay'], t('hoteldetails.min_stay_note')) }}</span>
                                                    </div>
                                                @endif

                                                @if(!empty($option['tariff_notes']))
                                                    <div class="option-notice rate-notes">
                                                        <i class="fas fa-info-circle"></i>
                                                        <div>
                                                            <div class="hd-clamp is-short" data-hd-clamp><span>{{ $option['tariff_notes'] }}</span></div>
                                                            <button type="button" class="hd-more-btn" data-hd-toggle hidden>Read more <i class="fas fa-chevron-down"></i></button>
                                                        </div>
                                                    </div>
                                                @endif

                                                @if(!empty($option['taxes_included']) || !empty($option['taxes_at_property']))
                                                    <div class="option-taxes">
                                                        <div class="option-taxes-title"><i class="fas fa-receipt"></i> {{ t('hoteldetails.taxes_and_fees') }}</div>
                                                        @foreach(($option['taxes_included'] ?? []) as $tax)
                                                            <div class="option-taxes-row">
                                                                <span>{{ $tax['description'] ?: $tax['type'] }} <span class="tag included">{{ t('hoteldetails.included_in_price') }}</span></span>
                                                                <span>{{ number_format($tax['amount'], 2) }} {{ $tax['currency'] }}</span>
                                                            </div>
                                                        @endforeach
                                                        @foreach(($option['taxes_at_property'] ?? []) as $tax)
                                                            <div class="option-taxes-row">
                                                                <span>{{ $tax['description'] ?: $tax['type'] }} <span class="tag at-property">{{ t('hoteldetails.payable_at_property') }}</span></span>
                                                                <span>{{ number_format($tax['amount'], 2) }} {{ $tax['currency'] }}</span>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif

                                                <div class="option-details-list">
                                                    <div class="option-detail-item">
                                                        <i class="fas fa-users"></i>
                                                        <span>{{ $option['adults'] }} {{ $option['adults'] > 1 ? t('hoteldetails.adults') : t('hoteldetails.adult') }}</span>
                                                    </div>
                                                    @if($option['child'] > 0)
                                                        <div class="option-detail-item">
                                                            <i class="fas fa-child"></i>
                                                            <span>{{ $option['child'] }} {{ $option['child'] > 1 ? t('hoteldetails.children') : t('hoteldetails.child') }}</span>
                                                        </div>
                                                    @endif
                                                    <div class="option-detail-item">
                                                        <i class="fas fa-bed"></i>
                                                        <span>{{ $room['name'] }}</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="hd-option-price">
                                                <div class="option-price-section">
                                                    <div class="hd-price-night">
                                                        <span class="option-price">{{ $room['currency'] }} {{ number_format($option['per_day'], 2) }}</span>
                                                        <span class="option-per-night">/ {{ t('hoteldetails.per_night') }}</span>
                                                    </div>
                                                    <div class="hd-price-total">
                                                        {{ t('hoteldetails.currency_total') }} ({{ $hdNights }} {{ t('hoteldetails.nights') }}):
                                                        <strong>{{ $room['currency'] }} {{ number_format($option['price'], 2) }}</strong>
                                                    </div>
                                                    @if(!empty($option['taxes_at_property']))
                                                        <div class="option-per-night is-warning">
                                                            + {{ number_format(collect($option['taxes_at_property'])->sum('amount'), 2) }} {{ $room['currency'] }} {{ t('hoteldetails.payable_at_property') }}
                                                        </div>
                                                    @endif
                                                </div>

                                                <form action="{{url('hotel/hotel_booking')}}" method="POST" >
                                                    @csrf
                                                    <input type="hidden" name="room_data" value="{{ encrypt(json_encode($room['room_data'])) }}">
                                                    <input type="hidden" name="room" value="{{ encrypt(json_encode($room)) }}">
                                                    <input type="hidden" name="option" value="{{ encrypt(json_encode($option)) }}">
                                                    <input type="hidden" name="booking_data" value="{{ encrypt(json_encode([
                                    'hotel_name' => $hotel['h_name'],
                                    'supplier_name' => $hotel['supplier_name'],
                                    'address' => $hotel['address'],
                                    'agent_id' => $hotel['agent_id'],
                                    'stars' => $hotel['stars'],
                                    'checkin' => (isset($hotel_search['checkin']) && $hotel_search['checkin'] ? $hotel_search['checkin'] : ""),
                                    'checkout' => (isset($hotel_search['checkout']) && $hotel_search['checkout'] ? $hotel_search['checkout'] : ""),
                                    'adults' => isset($hotel_search['adults']) && $hotel_search['adults'] ? $hotel_search['adults'] : "0",
                                    'child' => isset($hotel_search['childs']) && $hotel_search['childs'] ? $hotel_search['childs'] : "0",
                                    ])) }}">

                                                    <button class="option-select-btn" onclick="selectOption('{{ $room['id'] }}', '{{ $option['id'] }}', {{ $option['price'] }})">
                                                        <i class="fas fa-calendar-check"></i> {{ t('hoteldetails.book_now') }}
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>

                </div>

                <!-- Sidebar -->
                <aside class="hd-sidebar">
                    <!-- Booking Summary -->
                    <div class="hotel-booking-sidebar">
                        <div class="booking-summary-title">{{ t('hoteldetails.booking_summary') }}</div>

                        <div class="booking-summary-item">
                            <span class="booking-label"><i class="fas fa-calendar"></i> {{ t('hoteldetails.check_in') }}</span>
                            <span class="booking-value">{{ date('d M Y', strtotime($hotel['checkin'])) }}</span>
                        </div>

                        <div class="booking-summary-item">
                            <span class="booking-label"><i class="fas fa-calendar"></i> {{ t('hoteldetails.check_out') }}</span>
                            <span class="booking-value">{{ date('d M Y', strtotime($hotel['checkout'])) }}</span>
                        </div>

                        <div class="booking-summary-item">
                            <span class="booking-label"><i class="fas fa-moon"></i> {{ t('hoteldetails.nights') }}</span>
                            <span class="booking-value">{{ $hdNights }}</span>
                        </div>

                        @if($hdLocation)
                        <div class="booking-summary-item">
                            <span class="booking-label"><i class="fas fa-location-dot"></i> {{ t('hoteldetails.location') }}</span>
                            <span class="booking-value">{{ ucwords(strtolower($hdLocation)) }}</span>
                        </div>
                        @endif

                        @if($hdStars > 0)
                        <div class="booking-summary-item">
                            <span class="booking-label"><i class="fas fa-star"></i> {{ t('hoteldetails.hotel_rating') }}</span>
                            <span class="booking-value">{{ $hdStars }} {{ t('hotel.stars') }}</span>
                        </div>
                        @endif

                        <div class="booking-total">
                            <span class="booking-total-label">{{ t('hoteldetails.starting_from') }}</span>
                            <span class="booking-total-value hotel-price">
                                {{ number_format($hotel['rooms'][0]['price'] ?? 0, 2) }} {{ $hotel['rooms'][0]['currency'] ?? 'USD' }}
                            </span>
                            <span class="hd-total-note">{{ t('hoteldetails.currency_total') }} · {{ $hdNights }} {{ t('hoteldetails.nights') }}</span>
                        </div>

                        {{--<button class="booking-btn" onclick="proceedToBooking()">
                            <i class="fas fa-check"></i> Proceed to Booking
                        </button>--}}
                    </div>

                    <!-- Why book -->
                    <div class="hd-side-card">
                        <div class="hd-side-title">{{ t('hotel.whyBookTitle') }}</div>
                        <ul class="hd-why">
                            <li><i class="fas fa-circle-check"></i> {{ t('hotel.bestPricesTitle') }}</li>
                            <li><i class="fas fa-circle-check"></i> {{ t('hotellist.instant_confirmation') }}</li>
                            <li><i class="fas fa-circle-check"></i> {{ t('hotel.secureBookingTitle') }}</li>
                            <li><i class="fas fa-circle-check"></i> {{ t('hotel.supportTitle') }}</li>
                        </ul>
                    </div>

                    <!-- Location -->
                    <div class="hotel-location">
                        <div class="section-title">{{ t('hoteldetails.location_map') }}</div>
                        <div class="hotel-address">
                            <i class="fas fa-location-dot hotel-address-icon"></i>
                            <div class="hotel-address-text">{{ $hotel['address'] }}@if($hdLocation), {{ ucwords(strtolower($hdLocation)) }}@endif</div>
                        </div>
                        @php
                            $hdMapQuery = $hdHasCoords
                                ? $hotel['lat'] . ',' . $hotel['lng']
                                : urlencode(trim($hotel['h_name'] . ', ' . $hotel['address'] . ', ' . $hdLocation, ', '));
                        @endphp
                        <div class="hd-map">
                            <iframe src="https://maps.google.com/maps?q={{ $hdMapQuery }}&z=15&output=embed" title="{{ $hotel['h_name'] }} map" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                        </div>
                        <a class="hd-map-link" target="_blank" rel="noopener"
                           href="https://www.google.com/maps/search/?api=1&query={{ $hdMapQuery }}">
                            <i class="fas fa-up-right-from-square"></i> Open in Google Maps
                        </a>
                    </div>

                    <!-- Amenities -->
                    @if(count($hdAmenities))
                    <div class="hd-side-card hotel-amenities">
                        <div class="hd-side-title">{{ t('hoteldetails.amenities') }}</div>
                        <div class="amenities-grid">
                            @foreach($hdAmenities as $amenity)
                                <div class="amenity-item">
                                    <i class="fas fa-check amenity-icon"></i>
                                    <span class="amenity-text">{{ $amenity }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Hotel Policies -->
                    <div class="hotel-policies">
                        <div class="section-title">{{ t('hoteldetails.hotel_policies') }}</div>

                        <div class="policy-item">
                            <div class="policy-title">
                                <i class="fas fa-clock"></i>
                                {{ t('hoteldetails.checkin_checkout') }}
                            </div>
                            <div class="policy-content">
                                <strong>{{ t('hoteldetails.checkin_label') }}</strong> {{ t('hoteldetails.checkin_from') }}<br>
                                <strong>{{ t('hoteldetails.checkout_label') }}</strong> {{ t('hoteldetails.checkout_until') }}<br>
                                {{ t('hoteldetails.early_late_policy') }}
                            </div>
                        </div>

                        <div class="policy-item">
                            <div class="policy-title">
                                <i class="fas fa-times-circle"></i>
                                {{ t('hoteldetails.cancellation_policy') }}
                            </div>
                            <div class="policy-content">
                                @if(isset($room['refundable']) && $room['refundable'])
                                    {{ str_replace(':date', date('M d, Y', strtotime($hotel['checkin'] . ' -2 days')), t('hoteldetails.free_cancellation_until')) }}
                                @else
                                    {{ t('hoteldetails.cancellation_varies') }}
                                @endif
                            </div>
                        </div>

                        <div class="policy-item">
                            <div class="policy-title">
                                <i class="fas fa-child"></i>
                                {{ t('hoteldetails.children_extra_beds') }}
                            </div>
                            <div class="policy-content">
                                {{ t('hoteldetails.children_policy') }}
                            </div>
                        </div>

                        <div class="policy-item">
                            <div class="policy-title">
                                <i class="fas fa-paw"></i>
                                {{ t('hoteldetails.pets') }}
                            </div>
                            <div class="policy-content">
                                {{ t('hoteldetails.pets_policy') }}
                            </div>
                        </div>

                        <div class="policy-item">
                            <div class="policy-title">
                                <i class="fas fa-credit-card"></i>
                                {{ t('hoteldetails.payment_methods') }}
                            </div>
                            <div class="policy-content">
                                {{ t('hoteldetails.payment_policy') }}
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </div>

        <script>
            const hotelImages = @json($hdImages);
            let currentImageIndex = 0;

            function changeImage(index, scrollUp) {
                if (!hotelImages.length) return;
                currentImageIndex = index;
                document.getElementById('mainImage').src = hotelImages[index];
                const counter = document.getElementById('photoIndex');
                if (counter) counter.textContent = index + 1;

                // Update active thumbnail
                document.querySelectorAll('.gallery-thumbnail').forEach(thumb => {
                    thumb.classList.toggle('active', parseInt(thumb.dataset.index, 10) === index);
                });
                if (scrollUp) document.querySelector('.hotel-gallery').scrollIntoView({ behavior: 'smooth', block: 'center' });
            }

            function prevImage() {
                currentImageIndex = (currentImageIndex - 1 + hotelImages.length) % hotelImages.length;
                changeImage(currentImageIndex);
            }

            function nextImage() {
                currentImageIndex = (currentImageIndex + 1) % hotelImages.length;
                changeImage(currentImageIndex);
            }

            function toggleAllImages() {
                const remainingImages = document.getElementById('remainingImages');
                const viewAllText = document.getElementById('viewAllText');
                const viewAllIcon = document.getElementById('viewAllIcon');
                const viewAllBtn = document.querySelector('.view-all-images-btn');
                if (!remainingImages) return;

                remainingImages.classList.toggle('show');
                viewAllBtn.classList.toggle('active');

                if (remainingImages.classList.contains('show')) {
                    viewAllText.textContent = '{{ t('hoteldetails.hide_images') }}';
                    viewAllIcon.classList.remove('fa-chevron-down');
                    viewAllIcon.classList.add('fa-chevron-up');
                    viewAllBtn.scrollIntoView({ behavior: 'smooth', block: 'start' });
                } else {
                    viewAllText.textContent = '{{ t('hoteldetails.view_all_images') }}'.replace(':count', hotelImages.length);
                    viewAllIcon.classList.remove('fa-chevron-up');
                    viewAllIcon.classList.add('fa-chevron-down');
                }
            }

            // Read more / show less for long description and tariff notes
            document.querySelectorAll('[data-hd-clamp]').forEach(function (box) {
                const btn = box.parentElement.querySelector('[data-hd-toggle]');
                if (!btn || box.scrollHeight <= box.clientHeight + 2) return;
                btn.hidden = false;
                btn.addEventListener('click', function () {
                    const open = box.classList.toggle('is-open');
                    btn.innerHTML = open ? 'Show less <i class="fas fa-chevron-up"></i>' : 'Read more <i class="fas fa-chevron-down"></i>';
                });
            });

            // Room details expand / collapse
            document.querySelectorAll('[data-hd-room-toggle]').forEach(function (btn) {
                const panel = btn.closest('.room-card').querySelector('.hd-room-details');
                btn.addEventListener('click', function () {
                    panel.hidden = !panel.hidden;
                    btn.setAttribute('aria-expanded', String(!panel.hidden));
                    btn.querySelector('i').className = panel.hidden ? 'fas fa-chevron-down' : 'fas fa-chevron-up';
                });
            });

            function toggleOptions(roomId) {
                const optionsList = document.getElementById(`options-${roomId}`);
                optionsList.classList.toggle('active');
            }

            function selectRoom(roomId, optionId) {
                // Add your room selection logic here
                console.log('Selected Room:', roomId, 'Option:', optionId);
                alert('Room selected! Proceeding to booking...');
            }

            function proceedToBooking() {
                // Add your booking logic here
                alert('Please select a room first!');
            }
        </script>
    @endforeach

@endsection
