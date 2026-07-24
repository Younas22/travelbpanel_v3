@php($__activeTheme = app(\App\Services\ThemeService::class)->active(auth()->id()))
<!DOCTYPE html>
<html lang="en" data-bs-theme="{{ $__activeTheme->isDark() ? 'dark' : 'light' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Panel') - {{getSetting('meta_title', 'seo', 'Default Title')}}</title>
    <meta name="description" content="{{ getSetting('meta_description', 'seo', 'Default description here...') }}">
    <meta name="keywords" content="{{ getSetting('keywords', 'seo', '') }}">

    <meta property="og:title" content="{{ getSetting('meta_title', 'seo', 'Default OG Title') }}">
    <meta property="og:description" content="{{ getSetting('meta_description', 'seo', 'Default OG Description') }}">
    <meta property="og:image" content="{{ getSettingImage('business_logo', 'branding') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ getSetting('meta_title', 'seo', 'Default Twitter Title') }}">
    <meta name="twitter:description" content="{{ getSetting('meta_description', 'seo', 'Default Twitter Description') }}">
    <meta name="twitter:image" content="{{ getSettingImage('business_logo', 'branding') }}">

    <link rel="icon" href="{{ getSettingImage('favicon', 'branding') }}" type="image/png">

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="{{ url('public/assets/css/bootstrap-5.3.6-dist/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ url('public/assets/libs/bootstrap-icons/font/bootstrap-icons.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ url('public/assets/libs/toastify/toastify.min.css') }}">

    {{-- Modern always loads admin-modern.css — this whole view tree only
         ever renders when the active design is Modern (see
         App\View\ModernAdminViewFinder), so there's no href-swapping to
         do here the way admin.layouts.app has to for its live preview. --}}
    <link id="admin-design-css" href="{{ asset('public/assets/css/admin-modern.css') }}?v={{ @filemtime(public_path('assets/css/admin-modern.css')) ?: 1 }}" rel="stylesheet">

    @if($__activeTheme->font_family && !in_array(strtolower($__activeTheme->font_family), ['system font', 'system', 'system-ui']))
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family={{ str_replace(' ', '+', $__activeTheme->font_family) }}:wght@400;500;600;700&display=swap" rel="stylesheet">
    @endif
    <style id="theme-vars">{!! app(\App\Services\ThemeService::class)->inlineStyleTag($__activeTheme) !!}</style>
    @stack('styles')
</head>
<body class="theme-{{ $__activeTheme->theme_name }} design-modern {{ $__activeTheme->layoutBodyClasses() }}">
    @include('admin-modern.layouts.sidebar')

    <div class="main-content">
        @include('admin-modern.layouts.header')

        <div class="content-area p-4">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ $errors->first() }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </div>

        @include('admin-modern.layouts.footer')
    </div>

    @include('admin-modern.layouts.partials.scripts')
    @include('admin.layouts.partials.theme-quick-switcher')
    @stack('scripts')
</body>
</html>
