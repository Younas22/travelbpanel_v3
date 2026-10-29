@extends('common.layout')

@section('content')
    <style>
        /* Hotel details page — follows the site redesign (brand #2563EB,
           black headings/prices, gold stars, 16px cards). */
        .hotel-details-container {
            max-width: 1328px;
            margin: 0 auto;
            padding: 20px 24px 64px;
            color: #000000;
        }

        /* Neutralise the older global hotel-details boxes in style.css so
           the cards below are the only containers. */
        .hotel-details-container .hotel-details-header,
        .hotel-details-container .hotel-gallery,
        .hotel-details-container .room-card-header,
        .hotel-details-container .hotel-address,
        .hotel-details-container .hotel-rating-badge {
            padding: 0;
            border: 0;
            border-radius: 0;
            background: transparent;
            box-shadow: none;
        }

        /* ---------- Breadcrumbs ---------- */
        .hd-breadcrumb {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 6px 10px;
            margin-bottom: 14px;
            color: rgba(0, 0, 0, 0.55);
            font-size: 13px;
        }

        .hd-breadcrumb a {
            color: rgba(0, 0, 0, 0.55);
            text-decoration: none;
        }

        .hd-breadcrumb a:hover {
            color: #2563EB;
        }

        .hd-breadcrumb i {
            font-size: 9px;
            color: rgba(0, 0, 0, 0.35);
        }

        .hd-breadcrumb [aria-current] {
            color: #000000;
            font-weight: 500;
        }

        /* ---------- Header ---------- */
        .hotel-details-header {
            margin-bottom: 18px;
        }

        .hotel-details-title {
            margin: 0 0 8px;
            color: #000000;
            font-size: clamp(24px, 2.6vw, 32px);
            font-weight: 700;
            line-height: 1.2;
        }

        .hotel-details-meta {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px 18px;
        }

        .hotel-meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #000000;
            font-size: 14px;
        }

        .hotel-meta-icon {
            color: #000000;
        }

        .hotel-rating-badge {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .hotel-rating-stars {
            display: inline-flex;
            gap: 2px;
            color: rgba(0, 0, 0, 0.15);
            font-size: 13px;
        }

        .hotel-rating-stars .is-on {
            color: #F5B301;
        }

        .hotel-rating-score {
            color: #000000;
            font-size: 13px;
            font-weight: 500;
        }

        .hd-score {
            padding: 4px 10px;
            border-radius: 8px;
            background: #2563EB;
            color: #FFFFFF;
            font-size: 13px;
            font-weight: 600;
        }

        .hd-score small {
            opacity: 0.8;
            font-size: 11px;
        }

        /* ---------- Layout ---------- */
        .hotel-details-main {
            display: grid;
            grid-template-columns: minmax(0, 2fr) minmax(0, 1fr);
            gap: 24px;
            align-items: start;
        }

        .hd-main-col {
            min-width: 0;
        }

        .hd-sidebar {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        /* ---------- Gallery ---------- */
        .hotel-gallery {
            margin-bottom: 16px;
        }

        .hd-gallery-grid {
            display: grid;
            grid-template-columns: minmax(0, 3fr) minmax(0, 1fr);
            gap: 8px;
            height: 440px;
        }

        .hd-gallery-grid.is-single {
            grid-template-columns: minmax(0, 1fr);
        }

        .hotel-gallery-main {
            position: relative;
            min-height: 0;
            overflow: hidden;
            border-radius: 16px;
            background: rgba(37, 99, 235, 0.06);
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
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.92);
            color: #000000;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        .gallery-nav-btn:hover {
            background: #FFFFFF;
            color: #2563EB;
        }

        .gallery-nav-btn.prev {
            left: 14px;
        }

        .gallery-nav-btn.next {
            right: 14px;
        }

        .hd-photo-count {
            position: absolute;
            left: 14px;
            bottom: 14px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            border-radius: 9999px;
            background: rgba(0, 0, 0, 0.62);
            color: #FFFFFF;
            font-size: 12px;
        }

        .hd-gallery-side {
            display: grid;
            grid-template-rows: repeat(4, minmax(0, 1fr));
            gap: 8px;
            min-height: 0;
        }

        .gallery-thumbnail {
            position: relative;
            display: block;
            width: 100%;
            min-height: 0;
            padding: 0;
            overflow: hidden;
            border: 2px solid transparent;
            border-radius: 12px;
            background: rgba(37, 99, 235, 0.06);
            cursor: pointer;
            transition: border-color 0.2s ease;
        }

        .gallery-thumbnail:hover,
        .gallery-thumbnail.active {
            border-color: #2563EB;
        }

        .gallery-thumbnail img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .hd-gallery-side .gallery-thumbnail {
            height: 100%;
        }

        .hd-more {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(0, 0, 0, 0.5);
            color: #FFFFFF;
            font-size: 18px;
            font-weight: 600;
        }

        .view-all-images-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 10px;
            padding: 8px 14px;
            border: 1px solid rgba(37, 99, 235, 0.3);
            border-radius: 10px;
            background: #FFFFFF;
            color: #2563EB;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
        }

        .view-all-images-btn:hover {
            border-color: #2563EB;
        }

        .view-all-images-btn i {
            font-size: 11px;
        }

        .remaining-images {
            display: none;
            grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
            gap: 8px;
            margin-top: 10px;
        }

        .remaining-images.show {
            display: grid;
        }

        .remaining-images .gallery-thumbnail {
            height: 80px;
        }

        /* ---------- Highlights bar ---------- */
        .hd-facts {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            margin-bottom: 20px;
            padding: 12px 8px;
            border: 1px solid rgba(0, 0, 0, 0.08);
            border-radius: 16px;
            background: #FFFFFF;
        }

        .hd-facts:empty {
            display: none;
        }

        .hd-fact {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 4px 16px;
            border-left: 1px solid rgba(0, 0, 0, 0.08);
            color: #000000;
            font-size: 13px;
            font-weight: 500;
        }

        .hd-fact:first-child {
            border-left: 0;
        }

        .hd-fact-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            border-radius: 9999px;
            background: rgba(37, 99, 235, 0.1);
            color: #2563EB;
            font-size: 12px;
        }

        /* ---------- Cards ---------- */
        .section-title {
            position: relative;
            margin-bottom: 16px;
            padding: 0 0 0 12px;
            border-bottom: 0;
            color: #000000;
            font-size: 18px;
            font-weight: 700;
        }

        .hotel-details-container .section-title::before {
            content: "";
            position: absolute;
            left: 0;
            top: 3px;
            bottom: 3px;
            width: 4px;
            height: auto;
            border-radius: 2px;
            background-color: #2563EB;
        }

        .hotel-description,
        .hotel-rooms,
        .hotel-location,
        .hotel-policies,
        .hotel-booking-sidebar,
        .hd-side-card {
            margin-bottom: 20px;
            padding: 22px;
            border: 1px solid rgba(0, 0, 0, 0.08);
            border-radius: 16px;
            background: #FFFFFF;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
        }

        .hd-sidebar .hotel-booking-sidebar,
        .hd-sidebar .hd-side-card,
        .hd-sidebar .hotel-location,
        .hd-sidebar .hotel-policies {
            position: static; /* style.css makes the booking summary sticky */
            margin-bottom: 0;
        }

        /* Location & Map and Hotel Policies in the sidebar */
        .hd-sidebar .section-title {
            font-size: 17px;
        }

        .hd-sidebar .hotel-address {
            padding: 10px 12px;
            border-radius: 10px;
            background: rgba(37, 99, 235, 0.06);
            font-size: 13px;
        }

        .hd-sidebar .hotel-address-icon {
            color: #2563EB;
        }

        .hd-sidebar .hd-map iframe {
            height: 220px;
        }

        .hd-sidebar .policy-item {
            display: grid;
            grid-template-columns: 22px minmax(0, 1fr);
            column-gap: 10px;
            padding: 12px;
        }

        .hd-sidebar .policy-title {
            display: contents;
            font-size: 13.5px;
        }

        .hd-sidebar .policy-title i {
            grid-row: span 2;
            margin-top: 2px;
            font-size: 16px;
        }

        .hd-sidebar .policy-content {
            grid-column: 2;
            margin-top: 4px;
            font-size: 12.5px;
        }

        .hotel-description-text {
            margin: 0;
            color: rgba(0, 0, 0, 0.75);
            font-size: 14px;
            line-height: 1.7;
            white-space: pre-line;
        }

        /* Read more */
        .hd-clamp {
            max-height: 150px;
            overflow: hidden;
        }

        .hd-clamp.is-short {
            max-height: 84px;
        }

        .hd-clamp.is-open {
            max-height: none;
        }

        .hd-more-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 8px;
            padding: 0;
            border: 0;
            background: none;
            color: #2563EB;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
        }

        .hd-more-btn i {
            font-size: 10px;
        }

        .hotel-highlights {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .highlight-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 7px 14px;
            border: 0;
            border-radius: 9999px;
            background: rgba(0, 0, 0, 0.05);
            color: #000000;
            font-size: 13px;
            font-weight: 500;
        }

        .highlight-item i {
            color: #2563EB;
            font-size: 11px;
        }

        /* ---------- Rooms ---------- */
        .hd-room {
            margin-bottom: 24px;
        }

        .hd-room:last-child {
            margin-bottom: 0;
        }

        .room-card {
            padding: 14px;
            border: 1px solid rgba(0, 0, 0, 0.08);
            border-radius: 16px;
            background: #FFFFFF;
        }

        .room-card-header {
            display: flex;
            gap: 16px;
        }

        .room-card-image {
            flex-shrink: 0;
            width: 200px;
            height: 130px;
            border-radius: 12px;
            object-fit: cover;
        }

        .room-card-info {
            flex: 1;
            min-width: 0;
        }

        .room-name {
            margin-bottom: 10px;
            color: #000000;
            font-size: 17px;
            font-weight: 600;
        }

        .room-features {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            color: #000000;
            font-size: 13px;
        }

        .room-features span {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 10px;
            border-radius: 9999px;
            background: rgba(0, 0, 0, 0.05);
        }

        .room-features i {
            color: #2563EB;
            font-size: 11px;
        }

        .hd-room-toggle {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 12px;
            padding: 0;
            border: 0;
            background: none;
            color: #2563EB;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
        }

        .hd-room-toggle i {
            font-size: 10px;
        }

        .hd-room-details {
            margin-top: 14px;
            padding-top: 14px;
            border-top: 1px solid rgba(0, 0, 0, 0.08);
        }

        .hd-room-amenities {
            display: flex;
            flex-wrap: wrap;
            gap: 8px 16px;
            font-size: 13px;
        }

        .hd-room-amenities i {
            margin-right: 4px;
            color: #2563EB;
            font-size: 11px;
        }

        .hd-room-photos {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
            gap: 8px;
            margin-top: 12px;
        }

        .hd-room-photos img {
            width: 100%;
            height: 76px;
            border-radius: 10px;
            object-fit: cover;
        }

        .hd-options-title {
            margin: 18px 0 10px;
            color: #000000;
            font-size: 15px;
            font-weight: 600;
        }

        /* ---------- Rate plans ---------- */
        .room-options-grid {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .room-option-card {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 240px;
            overflow: hidden;
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 16px;
            background: #FFFFFF;
        }

        .hd-option-main {
            min-width: 0;
            padding: 16px 18px;
        }

        .option-header {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            margin-bottom: 12px;
        }

        .option-title {
            color: #000000;
            font-size: 15px;
            font-weight: 600;
        }

        .option-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 600;
        }

        .option-badge.refundable {
            background: rgba(37, 99, 235, 0.1);
            color: #2563EB;
        }

        .option-badge.non-refundable {
            background: #fee2e2;
            color: #991b1b;
        }

        .option-details-list {
            display: flex;
            flex-wrap: wrap;
            gap: 6px 18px;
            margin-top: 4px;
        }

        .option-detail-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #000000;
            font-size: 13px;
        }

        .option-detail-item i {
            width: 14px;
            color: #2563EB;
        }

        .option-notice {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 10px;
            padding: 10px 12px;
            border-radius: 12px;
            font-size: 12.5px;
            line-height: 1.55;
        }

        .option-notice > i {
            margin-top: 3px;
        }

        .option-notice > div {
            min-width: 0;
        }

        .option-notice.restricted {
            background: #fee2e2;
            color: #991b1b;
            font-weight: 600;
        }

        .option-notice.min-stay,
        .option-notice.occupancy {
            background: #fef3c7;
            color: #92400e;
        }

        .option-notice.rate-notes {
            background: rgba(37, 99, 235, 0.06);
            color: #000000;
        }

        .option-notice.rate-notes > i {
            color: #2563EB;
        }

        .option-notice.promotion {
            background: rgba(37, 99, 235, 0.1);
            color: #2563EB;
            font-weight: 600;
        }

        .option-taxes {
            margin-bottom: 10px;
            padding: 10px 12px;
            border: 1px solid rgba(0, 0, 0, 0.08);
            border-radius: 12px;
            font-size: 12.5px;
        }

        .option-taxes-title {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 6px;
            color: #000000;
            font-weight: 600;
        }

        .option-taxes-title i {
            color: #2563EB;
        }

        .option-taxes-row {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 3px 0;
            color: rgba(0, 0, 0, 0.7);
        }

        .option-taxes-row > span:last-child {
            flex-shrink: 0;
            color: #000000;
            font-weight: 500;
        }

        .option-taxes-row .tag {
            margin-left: 6px;
            padding: 1px 6px;
            border-radius: 4px;
            font-size: 10px;
        }

        .option-taxes-row .tag.included { background: rgba(37, 99, 235, 0.1); color: #2563EB; }
        .option-taxes-row .tag.at-property { background: #fef3c7; color: #92400e; }

        .hd-option-price {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 14px;
            padding: 16px 18px;
            border-left: 1px solid rgba(0, 0, 0, 0.08);
            background: rgba(37, 99, 235, 0.03);
        }

        .option-price-section {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .hd-price-night {
            display: flex;
            flex-wrap: wrap;
            align-items: baseline;
            gap: 4px;
        }

        .option-price {
            color: #000000;
            font-size: 22px;
            font-weight: 700;
            line-height: 1.2;
        }

        .option-per-night {
            color: rgba(0, 0, 0, 0.55);
            font-size: 12px;
        }

        .option-per-night.is-warning {
            color: #92400e;
        }

        .hd-price-total {
            color: rgba(0, 0, 0, 0.65);
            font-size: 12.5px;
        }

        .hd-price-total strong {
            color: #000000;
            font-weight: 600;
        }

        .option-select-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            height: 46px;
            border: 0;
            border-radius: 12px;
            background: #2563EB;
            color: #FFFFFF;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        .option-select-btn:hover {
            background: #1D4ED8;
        }

        /* ---------- Location ---------- */
        .hotel-address {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            color: #000000;
            font-size: 14px;
        }

        .hotel-address-icon {
            margin-top: 3px;
            color: #000000;
        }

        .hd-map {
            margin-top: 14px;
            overflow: hidden;
            border-radius: 12px;
            background: rgba(37, 99, 235, 0.06);
        }

        .hd-map iframe {
            display: block;
            width: 100%;
            height: 300px;
            border: 0;
        }

        .hd-map-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 12px;
            color: #2563EB;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
        }

        .hd-map-link:hover {
            text-decoration: underline;
        }

        /* ---------- Policies ---------- */
        .policy-item {
            margin-bottom: 10px;
            padding: 14px 16px;
            border-radius: 12px;
            background: rgba(0, 0, 0, 0.03);
        }

        .policy-item:last-child {
            margin-bottom: 0;
        }

        .policy-title {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 6px;
            color: #000000;
            font-size: 14px;
            font-weight: 600;
        }

        .policy-title i {
            color: #2563EB;
        }

        .policy-content {
            color: rgba(0, 0, 0, 0.7);
            font-size: 13.5px;
            line-height: 1.6;
        }

        /* ---------- Sidebar ---------- */
        .booking-summary-title,
        .hd-side-title {
            margin-bottom: 12px;
            color: #000000;
            font-size: 17px;
            font-weight: 700;
        }

        .booking-summary-item {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 11px 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.06);
        }

        .booking-label {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: rgba(0, 0, 0, 0.6);
            font-size: 14px;
        }

        .booking-label i {
            width: 14px;
            color: #2563EB;
            font-size: 13px;
            text-align: center;
        }

        .booking-value {
            color: #000000;
            font-size: 14px;
            font-weight: 500;
            text-align: right;
        }

        .booking-total {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 2px 12px;
            margin-top: 16px;
            padding: 14px 16px;
            border: 1px solid rgba(37, 99, 235, 0.2);
            border-radius: 12px;
            background: rgba(37, 99, 235, 0.06);
        }

        .booking-total-label {
            color: #000000;
            font-size: 14px;
            font-weight: 600;
        }

        .booking-total-value {
            color: #000000;
            font-size: 22px;
            font-weight: 700;
        }

        .hd-total-note {
            width: 100%;
            color: rgba(0, 0, 0, 0.55);
            font-size: 12px;
        }

        .hd-why {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .hd-why li {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #000000;
            font-size: 14px;
        }

        .hd-why i {
            color: #2563EB;
        }

        .amenities-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;
        }

        .amenity-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 10px;
            border-radius: 10px;
            background: rgba(0, 0, 0, 0.04);
        }

        .amenity-icon {
            color: #2563EB;
            font-size: 12px;
        }

        .amenity-text {
            min-width: 0;
            color: #000000;
            font-size: 13px;
        }

        .hotel-details-container :focus-visible {
            outline: 2px solid #2563EB;
            outline-offset: 2px;
        }

        /* ---------- Responsive ---------- */
        @media (max-width: 1023px) {
            .hotel-details-main {
                grid-template-columns: minmax(0, 1fr);
            }

            .hd-sidebar {
                position: static;
            }

            .hd-gallery-grid {
                height: 360px;
            }
        }

        @media (max-width: 767px) {
            .room-option-card {
                grid-template-columns: minmax(0, 1fr);
            }

            .hd-option-price {
                border-left: 0;
                border-top: 1px solid rgba(0, 0, 0, 0.08);
            }
        }

        @media (max-width: 639px) {
            .hotel-details-container {
                padding: 16px 16px 48px;
            }

            .hd-gallery-grid {
                grid-template-columns: minmax(0, 1fr);
                height: auto;
            }

            .hotel-gallery-main {
                height: 240px;
            }

            .hd-gallery-side {
                grid-template-columns: repeat(4, minmax(0, 1fr));
                grid-template-rows: none;
            }

            .hd-gallery-side .gallery-thumbnail {
                height: 64px;
            }

            .hd-facts {
                flex-wrap: nowrap;
                overflow-x: auto;
                scrollbar-width: none;
            }

            .hd-fact {
                flex-shrink: 0;
                white-space: nowrap;
            }

            .hotel-description,
            .hotel-rooms,
            .hotel-location,
            .hotel-policies,
            .hotel-booking-sidebar,
            .hd-side-card {
                padding: 16px;
            }

            .room-card-header {
                flex-direction: column;
            }

            .room-card-image {
                width: 100%;
                height: 170px;
            }
        }

        /* Right-to-left */
        [dir="rtl"] .hd-fact { border-left: 0; border-right: 1px solid rgba(0, 0, 0, 0.08); }
        [dir="rtl"] .hd-fact:first-child { border-right: 0; }
        [dir="rtl"] .section-title { padding: 0 12px 0 0; }
        [dir="rtl"] .hotel-details-container .section-title::before { left: auto; right: 0; }
        [dir="rtl"] .hd-option-price { border-left: 0; border-right: 1px solid rgba(0, 0, 0, 0.08); }
        [dir="rtl"] .booking-value { text-align: left; }
        [dir="rtl"] .hd-breadcrumb i,
        [dir="rtl"] .gallery-nav-btn i { transform: scaleX(-1); }
        @media (max-width: 767px) {
            [dir="rtl"] .hd-option-price { border-right: 0; }
        }
    </style>

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
