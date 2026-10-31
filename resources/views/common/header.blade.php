<!DOCTYPE html>
{{-- style="…" carries the website theme colors/font (Admin > Settings > Theme > Website) --}}
<html lang="{{ app()->getLocale() }}" dir="{{ in_array(app()->getLocale(), ['ar']) ? 'rtl' : 'ltr' }}" style="{{ \App\Support\WebsiteTheme::inlineVars() }}">

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
    

    <!-- style.css (all site CSS, incl. the page styles; ?v= busts the cache on change) -->
    @php $siteCss = in_array(app()->getLocale(), ['ar']) ? 'rtlstyle.css' : 'style.css'; @endphp
    <link rel="stylesheet" type="text/css" href="{{ url('public/assets/css/' . $siteCss) }}?v={{ filemtime(public_path('assets/css/' . $siteCss)) }}">
</head>
{{-- page_class / $pageClass scopes the page's styles in style.css; --cover-image
     is the homepage cover from settings, used by several page backgrounds. --}}
<body class="@yield('page_class', $pageClass ?? '')" style="--cover-image: url('{{ getSettingImage('cover_image', 'homepage') }}')">
    <!-- Page Loader -->
    <div id="pageLoader">
        <div style="text-align: center;">
            <img id="loaderImage" src="{{ url('public/assets/images/loader/travel-compass-loader.gif') }}" alt="Loading...">
            <!-- <p class="loader-text">Loading...</p> -->
        </div>
    </div>

    <!-- <div id="dropdownOverlay" class="dropdown-overlay"></div> -->
    @unless($hideNavbar ?? false)
        @include('common.navbar')
    @endunless
