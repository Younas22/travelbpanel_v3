@php($__activeTheme = app(\App\Services\ThemeService::class)->active(auth()->id()))
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ in_array(app()->getLocale(), ['ar']) ? 'rtl' : 'ltr' }}" data-bs-theme="{{ $__activeTheme->isDark() ? 'dark' : 'light' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Agent Panel') - {{ getSetting('business_name', 'main', 'TravelPanel') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="{{ url('public/assets/css/bootstrap-5.3.6-dist/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ url('public/assets/libs/bootstrap-icons/font/bootstrap-icons.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ url('public/assets/libs/font-awesome/css/all.min.css') }}">
    <link href="{{ url('public/assets/libs/select2/css/select2.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ url('public/assets/libs/flag-icons/css/flag-icons.min.css') }}">
    <link rel="stylesheet" href="{{ url('public/assets/libs/toastify/toastify.min.css') }}">

    {{-- Modern always loads agent-modern.css — this whole view tree only
         ever renders when the active design is Modern (see
         App\View\ModernAgentViewFinder), so there's no href-swapping to
         do here the way agent.layouts.app has to for its live preview. --}}
    <link id="agent-design-css" href="{{ asset('public/assets/css/agent-modern.css') }}?v={{ @filemtime(public_path('assets/css/agent-modern.css')) ?: 1 }}" rel="stylesheet">

    @if($__activeTheme->font_family && !in_array(strtolower($__activeTheme->font_family), ['system font', 'system', 'system-ui']))
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family={{ str_replace(' ', '+', $__activeTheme->font_family) }}:wght@400;500;600;700&display=swap" rel="stylesheet">
    @endif
    <style id="theme-vars">{!! app(\App\Services\ThemeService::class)->inlineStyleTag($__activeTheme) !!}</style>

    @stack('styles')
</head>
<body class="theme-{{ $__activeTheme->theme_name }} design-modern {{ $__activeTheme->layoutBodyClasses() }}">

    <div class="am-shell">

        <div id="amSidebarOverlay" class="am-sidebar-overlay" onclick="closeAmSidebar()"></div>

        @include('agent-modern.layouts.sidebar')

        <button id="amSidebarReopenBtn" class="am-sidebar-reopen-btn" onclick="toggleAmSidebar()" title="Open sidebar">
            <i class="bi bi-chevron-right"></i>
        </button>

        <div class="am-main">

            @include('agent-modern.layouts.navbar')

            <div class="content-area">

                @if(session('success'))
                    <div class="am-alert am-alert-success">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>{{ session('success') }}</span>
                        <button type="button" class="am-alert-close" onclick="this.closest('.am-alert').remove()"><i class="bi bi-x-lg"></i></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="am-alert am-alert-danger">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        <span>{{ session('error') }}</span>
                        <button type="button" class="am-alert-close" onclick="this.closest('.am-alert').remove()"><i class="bi bi-x-lg"></i></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="am-alert am-alert-danger">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        <span>{{ $errors->first() }}</span>
                        <button type="button" class="am-alert-close" onclick="this.closest('.am-alert').remove()"><i class="bi bi-x-lg"></i></button>
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
    </div>

    <script src="{{ url('public/assets/js/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ url('public/assets/libs/select2/js/select2.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script src="{{ url('public/assets/libs/bootstrap-5.3.6-dist/js/bootstrap.bundle.min.js') }}"></script>

    <script>
        function toggleAmSidebar() {
            const sidebar = document.getElementById('amSidebar');
            const overlay = document.getElementById('amSidebarOverlay');
            const reopenBtn = document.getElementById('amSidebarReopenBtn');
            const isDesktop = window.innerWidth >= 992;
            if (isDesktop) {
                sidebar.classList.toggle('am-sidebar-collapsed');
                const collapsed = sidebar.classList.contains('am-sidebar-collapsed');
                document.querySelector('.am-main').style.marginLeft = collapsed ? '0' : '';
                if (reopenBtn) reopenBtn.classList.toggle('show', collapsed);
            } else {
                sidebar.classList.toggle('am-sidebar-open');
                overlay.classList.toggle('active');
            }
        }
        function closeAmSidebar() {
            const sidebar = document.getElementById('amSidebar');
            const overlay = document.getElementById('amSidebarOverlay');
            sidebar.classList.remove('am-sidebar-open');
            overlay.classList.remove('active');
        }
        function toggleAmNavGroup(btn) {
            btn.closest('.am-nav-group').classList.toggle('am-nav-group-open');
        }
        document.addEventListener('click', function (e) {
            document.querySelectorAll('.am-dropdown.open').forEach(function (dd) {
                if (!dd.contains(e.target)) dd.classList.remove('open');
            });
        });
        function toggleAmDropdown(el) {
            const dd = el.closest('.am-dropdown');
            const wasOpen = dd.classList.contains('open');
            document.querySelectorAll('.am-dropdown.open').forEach(function (d) { d.classList.remove('open'); });
            if (!wasOpen) dd.classList.add('open');
        }
    </script>
    @auth
        @include('admin.layouts.partials.theme-quick-switcher', ['themeRoutePrefix' => 'agent'])
    @endauth
    @stack('scripts')
</body>
</html>
