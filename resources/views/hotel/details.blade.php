@extends('common.layout')

@section('content')
    <style>
        .hotel-details-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        .hotel-details-header {
            margin-bottom: 24px;
        }

        .hotel-details-title {
            font-size: 28px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 12px;
        }

        .hotel-details-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            align-items: center;
        }

        .hotel-meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #6b7280;
        }

        .hotel-meta-icon {
            color: #0077BE;
        }

        .hotel-rating-badge {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px 12px;
            background: #fef3c7;
            border-radius: 8px;
        }

        .hotel-rating-stars {
            color: #f59e0b;
            font-weight: 700;
        }

        .hotel-rating-score {
            font-weight: 700;
            color: #111827;
        }

        .hotel-review-count {
            color: #6b7280;
            font-size: 14px;
        }

        .hotel-details-main {
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 24px;
        }

        /* Image Gallery */
        .hotel-gallery {
            margin-bottom: 24px;
            border-radius: 12px;
            overflow: hidden;
        }

        .hotel-gallery-main {
            position: relative;
            width: 100%;
            height: 450px;
            background: #f3f4f6;
        }

        .hotel-gallery-main img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .gallery-nav-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255, 255, 255, 0.9);
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }

        .gallery-nav-btn:hover {
            background: white;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .gallery-nav-btn.prev {
            left: 16px;
        }

        .gallery-nav-btn.next {
            right: 16px;
        }

        .hotel-gallery-thumbnails {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 8px;
            margin-top: 8px;
        }

        .gallery-thumbnail {
            height: 80px;
            border-radius: 8px;
            overflow: hidden;
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.3s;
        }

        .gallery-thumbnail:hover,
        .gallery-thumbnail.active {
            border-color: #0077BE;
        }

        .gallery-thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .view-all-images-btn {
            grid-column: 1 / -1;
            padding: 12px 24px;
            background: #0077BE;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .view-all-images-btn:hover {
            background: #005f99;
        }

        .view-all-images-btn i {
            transition: transform 0.3s;
        }

        .view-all-images-btn.active i {
            transform: rotate(180deg);
        }

        .remaining-images {
            display: none;
            grid-column: 1 / -1;
        }

        .remaining-images.show {
            display: contents;
        }

        /* Section Styles */
        .section-title {
            font-size: 20px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 2px solid #e5e7eb;
        }

        .hotel-description,
        .hotel-rooms,
        .hotel-amenities,
        .hotel-location,
        .hotel-reviews,
        .hotel-policies {
            background: white;
            padding: 24px;
            border-radius: 12px;
            margin-bottom: 24px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .hotel-description-text {
            color: #4b5563;
            line-height: 1.7;
            white-space: pre-line;
        }

        .hotel-highlights {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 12px;
        }

        .highlight-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: #dcfce7;
            color: #166534;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
        }

        /* Room Card */
        .room-card {
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 16px;
            transition: all 0.3s;
        }

        .room-card:hover {
            border-color: #0077BE;
            box-shadow: 0 4px 12px rgba(0, 119, 190, 0.1);
        }

        .room-card-header {
            display: flex;
            gap: 16px;
            margin-bottom: 16px;
        }

        .room-card-image {
            width: 150px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
        }

        .room-card-info {
            flex: 1;
        }

        .room-name {
            font-size: 18px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 8px;
        }

        .room-features {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            color: #6b7280;
            font-size: 14px;
        }

        .room-features span {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .room-card-body {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 16px;
            border-top: 1px solid #e5e7eb;
        }

        .room-price-label {
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 4px;
        }

        .room-price-value {
            font-size: 24px;
            font-weight: 700;
            color: #0077BE;
        }

        .room-select-btn {
            padding: 12px 32px;
            background: #0077BE;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .room-select-btn:hover {
            background: #005f99;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 119, 190, 0.3);
        }

        /* Room Options as Cards */
        .room-options-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 16px;
            margin-top: 16px;
        }

        .room-option-card {
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 16px;
            background: white;
            transition: all 0.3s;
        }

        .room-option-card:hover {
            border-color: #0077BE;
            box-shadow: 0 4px 12px rgba(0, 119, 190, 0.1);
        }

        .option-header {
            margin-bottom: 12px;
            padding-bottom: 12px;
            border-bottom: 1px solid #e5e7eb;
        }

        .option-title {
            font-weight: 700;
            color: #111827;
            font-size: 16px;
            margin-bottom: 8px;
        }

        .option-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
        }

        .option-badge.refundable {
            background: #dcfce7;
            color: #166534;
        }

        .option-badge.non-refundable {
            background: #fee2e2;
            color: #991b1b;
        }

        .option-details-list {
            margin-bottom: 16px;
        }

        .option-detail-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px 0;
            color: #6b7280;
            font-size: 14px;
        }

        .option-detail-item i {
            color: #0077BE;
            width: 16px;
        }

        .option-price-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .option-price {
            font-size: 22px;
            font-weight: 700;
            color: #0077BE;
        }

        .option-per-night {
            font-size: 12px;
            color: #6b7280;
        }

        .option-select-btn {
            width: 100%;
            padding: 10px;
            background: #0077BE;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 14px;
        }

        .option-select-btn:hover {
            background: #005f99;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 119, 190, 0.3);
        }


        /* Amenities Grid */
        .amenities-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 16px;
        }

        .amenity-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background: #f9fafb;
            border-radius: 8px;
        }

        .amenity-icon {
            color: #0077BE;
            font-size: 20px;
        }

        .amenity-text {
            color: #374151;
            font-size: 14px;
        }

        /* Hotel Policies */
        .policy-item {
            padding: 16px;
            background: #f9fafb;
            border-radius: 8px;
            margin-bottom: 12px;
        }

        .policy-title {
            font-weight: 700;
            color: #111827;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .policy-content {
            color: #6b7280;
            line-height: 1.6;
            font-size: 14px;
        }

        /* Booking Sidebar */
        .hotel-booking-sidebar {
            background: white;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 24px;
            height: fit-content;
        }

        .booking-summary-title {
            font-size: 20px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 2px solid #e5e7eb;
        }

        .booking-summary-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #f3f4f6;
        }

        .booking-label {
            color: #6b7280;
            font-size: 14px;
        }

        .booking-value {
            color: #111827;
            font-weight: 600;
            font-size: 14px;
        }

        .booking-total {
            display: flex;
            justify-content: space-between;
            /* padding: 20px 0; */
            margin-top: 16px;
            border-top: 2px solid #e5e7eb;
        }

        .booking-total-label {
            font-size: 16px;
            font-weight: 700;
            color: #111827;
        }

        .booking-total-value {
            font-size: 24px;
            font-weight: 700;
            color: #0077BE;
        }

        .booking-btn {
            width: 100%;
            padding: 16px;
            background: #0077BE;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 700;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .booking-btn:hover {
            background: #005f99;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 119, 190, 0.3);
        }

        @media (max-width: 1024px) {
            .hotel-details-main {
                grid-template-columns: 1fr;
            }

            .hotel-booking-sidebar {
                position: static;
            }

            .hotel-gallery-main {
                height: 300px;
            }
        }

        @media (max-width: 640px) {
            .hotel-details-title {
                font-size: 22px;
            }

            .hotel-gallery-main {
                height: 250px;
            }

            .gallery-thumbnail {
                height: 60px;
            }

            .room-card-header {
                flex-direction: column;
            }

            .room-card-image {
                width: 100%;
                height: 180px;
            }
        }
    </style>

    @foreach($details as $hotel)
        <div class="hotel-details-container">
            <!-- Header -->
            <div class="hotel-details-header">
                <h1 class="hotel-details-title">{{ $hotel['h_name'] }}</h1>
                <div class="hotel-details-meta">
                    <div class="hotel-meta-item">
                        <i class="fas fa-map-pin hotel-meta-icon"></i>
                        <span>{{ $hotel['address'] }}</span>
                    </div>
                    <div class="hotel-rating-badge">
                <span class="hotel-rating-stars">
                    @for($i = 0; $i < (int)filter_var($hotel['stars'], FILTER_SANITIZE_NUMBER_INT); $i++)
                        ★
                    @endfor
                </span>
                        <span class="hotel-rating-score">{{ $hotel['rating'] / 10 }}</span>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="hotel-details-main">
                <div>
                    <!-- Image Gallery -->
                    <div class="hotel-gallery">
                        <div class="hotel-gallery-main">
                            <img src="{{ $hotel['imgs'][0] ?? 'https://via.placeholder.com/800x400' }}" alt="{{ $hotel['h_name'] }}" id="mainImage">
                            <button class="gallery-nav-btn prev" onclick="prevImage()"><i class="fas fa-chevron-left"></i></button>
                            <button class="gallery-nav-btn next" onclick="nextImage()"><i class="fas fa-chevron-right"></i></button>
                        </div>
                        <div class="hotel-gallery-thumbnails">
                            @foreach(array_slice($hotel['imgs'], 0, 8) as $index => $image)
                                <div class="gallery-thumbnail {{ $index === 0 ? 'active' : '' }}" onclick="changeImage({{ $index }})">
                                    <img src="{{ $image }}" alt="Hotel Image {{ $index + 1 }}">
                                </div>
                            @endforeach

                            @if(count($hotel['imgs']) > 8)
                                <button class="view-all-images-btn" onclick="toggleAllImages()">
                                    <span id="viewAllText">{{ str_replace(':count', count($hotel['imgs']), t('hoteldetails.view_all_images')) }}</span>
                                    <i class="fas fa-chevron-down" id="viewAllIcon"></i>
                                </button>

                                <div class="remaining-images" id="remainingImages">
                                    @foreach(array_slice($hotel['imgs'], 8) as $index => $image)
                                        <div class="gallery-thumbnail" onclick="changeImage({{ $index + 8 }})">
                                            <img src="{{ $image }}" alt="Hotel Image {{ $index + 9 }}">
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="hotel-description">
                        <div class="section-title">{{ t('hoteldetails.about_hotel') }}</div>
                        <p class="hotel-description-text">{{ $hotel['desc'] }}</p>

                        <div class="section-title" style="margin-top: 16px;">{{ t('hoteldetails.highlights') }}</div>
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
                        </div>
                    </div>

                    <!-- Rooms -->
                    <div class="hotel-rooms">
                        <div class="section-title">{{ str_replace(':count', count($hotel['rooms']), t('hoteldetails.available_rooms')) }}</div>

                        @foreach($hotel['rooms'] as $roomIndex => $room)
                            <div style="margin-bottom: 32px;">
                                <!-- Room Header -->
                                <div class="room-card" style="margin-bottom: 0;">
                                    <div class="room-card-header">
                                        <img src="{{ $room['images'][0] ?? $hotel['imgs'][0] }}" alt="{{ $room['name'] }}" class="room-card-image">
                                        <div class="room-card-info">
                                            <div class="room-name">{{ $room['name'] }}</div>
                                            <div class="room-features">
                                                @foreach(array_slice($room['amenities'], 0, 5) as $amenity)
                                                    <span><i class="fas fa-check-circle"></i> {{ $amenity }}</span>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Room Options as Cards -->
                                <div style="padding-left: 16px; padding-right: 16px;">
                                    <h3 style="font-size: 16px; font-weight: 600; color: #374151; margin: 16px 0 12px 0;">
                                        {{ str_replace(':count', count($room['options']), t('hoteldetails.booking_options')) }}
                                    </h3>
                                    <div class="room-options-grid">
                                        @foreach($room['options'] as $optionIndex => $option)
                                            <div class="room-option-card">
                                                <div class="option-header">
                                                    <div class="option-title">{{ t('hoteldetails.option') }} {{ $optionIndex + 1 }}</div>
                                                    @if(strpos($option['id'], 'NRF') !== false)
                                                        <span class="option-badge non-refundable">
                                        <i class="fas fa-times-circle"></i> {{ t('hoteldetails.non_refundable') }}
                                    </span>
                                                    @else
                                                        <span class="option-badge refundable">
                                        <i class="fas fa-check-circle"></i> {{ t('hoteldetails.refundable') }}
                                    </span>
                                                    @endif
                                                </div>

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

                                                <div class="option-price-section">
                                                    <div>
                                                        <div class="option-price">{{ number_format($option['price'], 2) }}</div>
                                                        <div class="option-per-night">{{ $room['currency'] }} {{ t('hoteldetails.currency_total') }}</div>
                                                    </div>
                                                    <div style="text-align: right;">
                                                        <div style="font-size: 14px; font-weight: 600; color: #374151;">
                                                            {{ number_format($option['per_day'], 2) }}
                                                        </div>
                                                        <div class="option-per-night">{{ t('hoteldetails.per_night') }}</div>
                                                    </div>
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
                                                        <i class="fas fa-check"></i> {{ t('hoteldetails.book_now') }}
                                                    </button>
                                                </form>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Amenities -->
                    <div class="hotel-amenities">
                        <div class="section-title">{{ t('hoteldetails.amenities') }}</div>
                        <div class="amenities-grid">
                            @foreach($hotel['amenities'] as $amenity)
                                <div class="amenity-item">
                                    <i class="fas fa-check-circle amenity-icon"></i>
                                    <span class="amenity-text">{{ $amenity }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Hotel Policies -->
                    <div class="hotel-policies">
                        <div class="section-title">{{ t('hoteldetails.hotel_policies') }}</div>

                        <div class="policy-item">
                            <div class="policy-title">
                                <i class="fas fa-clock" style="color: #0077BE;"></i>
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
                                <i class="fas fa-times-circle" style="color: #0077BE;"></i>
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
                                <i class="fas fa-child" style="color: #0077BE;"></i>
                                {{ t('hoteldetails.children_extra_beds') }}
                            </div>
                            <div class="policy-content">
                                {{ t('hoteldetails.children_policy') }}
                            </div>
                        </div>

                        <div class="policy-item">
                            <div class="policy-title">
                                <i class="fas fa-paw" style="color: #0077BE;"></i>
                                {{ t('hoteldetails.pets') }}
                            </div>
                            <div class="policy-content">
                                {{ t('hoteldetails.pets_policy') }}
                            </div>
                        </div>

                        <div class="policy-item">
                            <div class="policy-title">
                                <i class="fas fa-credit-card" style="color: #0077BE;"></i>
                                {{ t('hoteldetails.payment_methods') }}
                            </div>
                            <div class="policy-content">
                                {{ t('hoteldetails.payment_policy') }}
                            </div>
                        </div>
                    </div>

                    <!-- Location -->
                    <div class="hotel-location">
                        <div class="section-title">{{ t('hoteldetails.location_map') }}</div>
                        <div class="hotel-address">
                            <i class="fas fa-map-pin hotel-address-icon"></i>
                            <div class="hotel-address-text">{{ $hotel['address'] }}</div>
                        </div>
                        <div class="hotel-map-placeholder" style="margin-top: 16px; padding: 60px; background: #f3f4f6; border-radius: 12px; text-align: center; color: #6b7280;">
                            <i class="fas fa-map" style="font-size: 32px; margin-right: 8px;"></i>
                            <div>Latitude: {{ $hotel['lat'] }}, Longitude: {{ $hotel['lng'] }}</div>
                            <small>{{ t('hoteldetails.map_placeholder') }}</small>
                        </div>
                    </div>
                </div>

                <!-- Sidebar - Booking Summary -->
                <div class="hotel-booking-sidebar">
                    <div class="booking-summary-title">{{ t('hoteldetails.booking_summary') }}</div>

                    <div class="booking-summary-item">
                        <span class="booking-label">{{ t('hoteldetails.check_in') }}</span>
                        <span class="booking-value">{{ date('d M Y', strtotime($hotel['checkin'])) }}</span>
                    </div>

                    <div class="booking-summary-item">
                        <span class="booking-label">{{ t('hoteldetails.check_out') }}</span>
                        <span class="booking-value">{{ date('d M Y', strtotime($hotel['checkout'])) }}</span>
                    </div>

                    <div class="booking-summary-item">
                        <span class="booking-label">{{ t('hoteldetails.nights') }}</span>
                        <span class="booking-value">
                    {{ \Carbon\Carbon::parse($hotel['checkin'])->diffInDays(\Carbon\Carbon::parse($hotel['checkout'])) }}
                </span>
                    </div>

                    <div class="booking-summary-item">
                        <span class="booking-label">{{ t('hoteldetails.location') }}</span>
                        <span class="booking-value">{{ $hotel['city'] }}, {{ $hotel['country'] }}</span>
                    </div>

                    <div class="booking-summary-item">
                        <span class="booking-label">{{ t('hoteldetails.hotel_rating') }}</span>
                        <span class="booking-value">{{ $hotel['stars'] }}</span>
                    </div>

                    <div class="booking-total">
                        <span class="booking-total-label">{{ t('hoteldetails.starting_from') }}</span>
                        <span class="booking-total-value hotel-price">
                    {{ number_format($hotel['rooms'][0]['price'] ?? 0, 2) }} {{ $hotel['rooms'][0]['currency'] ?? 'USD' }}
                </span>
                    </div>

                    {{--<button class="booking-btn" onclick="proceedToBooking()">
                        <i class="fas fa-check"></i> Proceed to Booking
                    </button>--}}
                </div>
            </div>
        </div>

        <script>
            const hotelImages = @json($hotel['imgs']);
            let currentImageIndex = 0;

            function changeImage(index) {
                currentImageIndex = index;
                document.getElementById('mainImage').src = hotelImages[index];

                // Update active thumbnail
                document.querySelectorAll('.gallery-thumbnail').forEach((thumb, i) => {
                    thumb.classList.toggle('active', i === index);
                });
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

                remainingImages.classList.toggle('show');
                viewAllBtn.classList.toggle('active');

                if (remainingImages.classList.contains('show')) {
                    viewAllText.textContent = '{{ t('hoteldetails.hide_images') }}';
                    viewAllIcon.classList.remove('fa-chevron-down');
                    viewAllIcon.classList.add('fa-chevron-up');
                } else {
                    viewAllText.textContent = '{{ t('hoteldetails.view_all_images') }}'.replace(':count', hotelImages.length);
                    viewAllIcon.classList.remove('fa-chevron-up');
                    viewAllIcon.classList.add('fa-chevron-down');
                }
            }

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
