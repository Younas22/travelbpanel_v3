@php($__activeTheme = app(\App\Services\ThemeService::class)->active())
<!DOCTYPE html>
<html lang="en" data-bs-theme="{{ $__activeTheme->isDark() ? 'dark' : 'light' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Panel') - {{getSetting('meta_title', 'seo', 'Default Title')}}</title>
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
    <link href="{{ url('public/assets/css/bootstrap-5.3.6-dist/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ url('public/assets/libs/bootstrap-icons/font/bootstrap-icons.min.css') }}" rel="stylesheet">
    <!-- In your head section -->
    <link rel="stylesheet" href="{{ url('public/assets/libs/toastify/toastify.min.css') }}">
    <link id="admin-design-css" href="{{ asset($__activeTheme->designStylesheet()) }}?v={{ @filemtime(public_path(str_replace('public/', '', $__activeTheme->designStylesheet()))) }}" rel="stylesheet">
    @if($__activeTheme->design_style === 'nova')
        {{-- Nova's typeface is fixed (see admin-nova/layouts/app.blade.php),
             not the admin's Theme Settings font choice — this page is only
             here because Nova hasn't converted this specific route yet and
             fell back to Classic's own layout (see NovaAdminViewFinder), so
             it still needs Nova's font loaded the same way Nova's own
             layout does, instead of the conditional block below. --}}
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @elseif($__activeTheme->font_family && !in_array(strtolower($__activeTheme->font_family), ['system font', 'system', 'system-ui']))
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family={{ str_replace(' ', '+', $__activeTheme->font_family) }}:wght@400;500;600;700&display=swap" rel="stylesheet">
    @endif
    <style id="theme-vars">{!! app(\App\Services\ThemeService::class)->inlineStyleTag($__activeTheme) !!}</style>
    @stack('styles')

</head>
<body class="theme-{{ $__activeTheme->theme_name }} design-{{ $__activeTheme->design_style }} {{ $__activeTheme->layoutBodyClasses() }}">
    <script>
        // Same collapsed-sidebar preference check as admin-nova/layouts/app
        // .blade.php — applied synchronously before the sidebar paints, so
        // it doesn't flash open first. Harmless on Classic/Modern since
        // .nova-sidebar-collapsed only has any CSS effect under body.design-nova.
        if (localStorage.getItem('novaSidebarCollapsed') === '1') {
            document.body.classList.add('nova-sidebar-collapsed');
        }
    </script>
    <button type="button" class="sidebar-reopen-btn" onclick="toggleNovaSidebar()" aria-label="Open sidebar">
        <i class="bi bi-layout-sidebar-inset"></i>
    </button>

    @include('admin.layouts.sidebar')

    <div class="main-content">
        @include('admin.layouts.header')

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

        @include('admin.layouts.footer')
    </div>

    @include('admin.layouts.partials.scripts')
    @include('admin.layouts.partials.theme-quick-switcher')
    <script>
        function toggleNovaSidebar() {
            var collapsed = document.body.classList.toggle('nova-sidebar-collapsed');
            localStorage.setItem('novaSidebarCollapsed', collapsed ? '1' : '0');
        }
    </script>
    @stack('scripts')
</body>
</html>
