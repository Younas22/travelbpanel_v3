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

    {{-- Nova is a new, phased-in third design (see App\View\NovaAdminViewFinder):
         for now it only has its own Dashboard page (built in Tailwind, loaded
         per-page via @push('styles')); every other admin.* page still falls
         back to Classic. Until Nova grows its own sidebar/header, it reuses
         admin-modern's chrome + stylesheet as a shell (read-only includes
         below), same as admin-modern briefly reused Classic's quick-switcher
         partial during its own early rollout. --}}
    <link id="admin-design-css" href="{{ asset('public/assets/css/admin-modern.css') }}?v={{ @filemtime(public_path('assets/css/admin-modern.css')) ?: 1 }}" rel="stylesheet">

    {{-- Nova's typography is a fixed part of its own design language (Plus
         Jakarta Sans, see body.design-nova in admin-modern.css) rather than
         the admin's per-account Theme Settings font choice — so it always
         loads its own font here instead of the conditional $__activeTheme
         ->font_family block the other designs use. --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style id="theme-vars">{!! app(\App\Services\ThemeService::class)->inlineStyleTag($__activeTheme) !!}</style>
    @stack('styles')
</head>
<body class="theme-{{ $__activeTheme->theme_name }} design-nova {{ $__activeTheme->layoutBodyClasses() }}">
    <script>
        // Applied synchronously, before the sidebar paints, so a collapsed
        // preference from a previous visit doesn't flash open first.
        if (localStorage.getItem('novaSidebarCollapsed') === '1') {
            document.body.classList.add('nova-sidebar-collapsed');
        }
    </script>
    <button type="button" class="sidebar-reopen-btn" onclick="toggleNovaSidebar()" aria-label="Open sidebar">
        <i class="bi bi-layout-sidebar-inset"></i>
    </button>

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
    <script>
        function toggleNovaSidebar() {
            var collapsed = document.body.classList.toggle('nova-sidebar-collapsed');
            localStorage.setItem('novaSidebarCollapsed', collapsed ? '1' : '0');
        }
    </script>
    @stack('scripts')
</body>
</html>
