<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ in_array(app()->getLocale(), ['ar']) ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- Dynamic Meta Title --}}
    <title>{{ getSetting('meta_title', 'seo', 'Default Title') }}</title>

    {{-- Dynamic Meta Description --}}
    <meta name="description" content="{{ getSetting('meta_description', 'seo', 'Default description here...') }}">

    {{-- Dynamic Meta Keywords --}}
    <meta name="keywords" content="{{ getSetting('keywords', 'seo', '') }}">

    {{-- Open Graph (for social sharing) --}}
    <meta property="og:title" content="{{ getSetting('meta_title', 'seo', 'Default OG Title') }}">
    <meta property="og:description" content="{{ getSetting('meta_description', 'seo', 'Default OG Description') }}">
    <meta property="og:image" content="{{ getSettingImage('business_logo', 'branding') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ getSetting('meta_title', 'seo', 'Default Twitter Title') }}">
    <meta name="twitter:description" content="{{ getSetting('meta_description', 'seo', 'Default Twitter Description') }}">
    <meta name="twitter:image" content="{{ getSettingImage('business_logo', 'branding') }}">

    {{-- Favicon --}}
    <link rel="icon" href="{{ getSettingImage('favicon', 'branding') }}" type="image/png">

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="{{ url('public/assets/libs/tailwind/tailwind.min.js') }}"></script>
    <link rel="stylesheet" href="{{ url('public/assets/libs/font-awesome/css/all.min.css') }}">
    <link href="{{ url('public/assets/libs/select2/css/select2.min.css') }}" rel="stylesheet" />
    <link rel="stylesheet" href="{{ url('public/assets/libs/flatpickr/flatpickr.min.css') }}">
    <!-- Flag Icons CSS -->
    <link rel="stylesheet" href="{{ url('public/assets/libs/flag-icons/css/flag-icons.min.css') }}" />
    

    <!-- style.css -->
    @if(in_array(app()->getLocale(), ['ar']))
        <link rel="stylesheet" type="text/css" href="{{ url('public/assets/css/rtlstyle.css') }}">
    @else
        <link rel="stylesheet" type="text/css" href="{{ url('public/assets/css/style.css') }}">
    @endif

     <style>
        .top-border{
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
        }
        @media (min-width: 1025px) {
            .top-border {
                border-top-left-radius: 0px;
                border-top-right-radius: 0px;
            }
        }

        /* Select2 custom styles */
        .select2-container--default .select2-selection--single {
            border: 1px solid #d1d5db !important;
            border-radius: 0.5rem !important;
            height: 34px !important;
            min-width: 80px !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 32px !important;
            padding-left: 8px !important;
            font-size: 11px !important;
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered span {
            display: inline-flex !important;
            align-items: center !important;
            white-space: nowrap !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 32px !important;
        }

        .select2-dropdown {
            border: 1px solid #d1d5db !important;
            border-radius: 0.5rem !important;
            min-width: 80px !important;
        }

        .select2-results__option {
            padding: 6px 8px !important;
            font-size: 11px !important;
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }

        .select2-container--default .select2-results>.select2-results__options {
    max-height: 200px;
    overflow-y: auto !important;
}

        /* Custom thin scrollbar for language/currency dropdown */
        .select2-results__options::-webkit-scrollbar {
            width: 4px;
        }

        .select2-results__options::-webkit-scrollbar-track {
            background: transparent;
        }

        .select2-results__options::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 2px;
        }

        .select2-results__options::-webkit-scrollbar-thumb:hover {
            background: #9ca3af;
        }

        .select2-results__option span {
            display: inline-flex !important;
            align-items: center !important;
            white-space: nowrap !important;
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #3b82f6 !important;
        }

        /* Ensure flag icons display properly */
        .fi {
            width: 12px;
            height: 9px;
            display: inline-block;
            background-size: contain;
            background-position: center;
            background-repeat: no-repeat;
            vertical-align: middle;
        }

        /* RTL support for flags */
        [dir="ltr"] .fi {
            margin-right: 8px;
            margin-left: 0;
        }

        [dir="rtl"] .fi {
            margin-left: 8px;
            margin-right: 0;
        }
    </style>

    <!-- Loader Styles -->
    <style>
        #pageLoader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.95);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 99999;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }

        #pageLoader.hidden {
            opacity: 0;
            visibility: hidden;
        }

        #pageLoader img {
            max-width: 150px;
            height: auto;
        }

        .loader-text {
            position: absolute;
            bottom: 30%;
            font-size: 14px;
            color: #0077BE;
            font-weight: 600;
            animation: pulse 1.5s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
    </style>
</head>
<body>
    <!-- Page Loader -->
    <div id="pageLoader">
        <div style="text-align: center;">
            <img id="loaderImage" src="{{ url('public/assets/images/loader/global.gif') }}" alt="Loading...">
            <!-- <p class="loader-text">Loading...</p> -->
        </div>
    </div>

    <!-- <div id="dropdownOverlay" class="dropdown-overlay"></div> -->
    @include('common.navbar')
