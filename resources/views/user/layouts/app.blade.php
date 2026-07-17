<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ in_array(app()->getLocale(), ['ar']) ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') - {{ getSetting('business_name', 'main', 'TravelPanel') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script src="{{ url('public/assets/libs/tailwind/tailwind.min.js') }}"></script>
    <link rel="stylesheet" href="{{ url('public/assets/libs/font-awesome/css/all.min.css') }}">
    <link href="{{ url('public/assets/libs/select2/css/select2.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ url('public/assets/libs/flag-icons/css/flag-icons.min.css') }}">
    @if(in_array(app()->getLocale(), ['ar']))
        <link rel="stylesheet" href="{{ url('public/assets/css/rtlstyle.css') }}">
    @else
        <link rel="stylesheet" href="{{ url('public/assets/css/style.css') }}">
    @endif

    <style>
        /* Sidebar desktop toggle */
        @media (min-width: 1024px) {
            .agent-sidebar {
                transition: width 0.3s ease, min-width 0.3s ease, transform 0.3s ease;
            }
            .agent-sidebar.sidebar-collapsed {
                width: 0 !important;
                min-width: 0 !important;
                overflow: hidden;
                border-right: none;
            }
        }
        /* Reopen button: visible on mobile by default, hidden on desktop */
        @media (max-width: 1023px) {
            #sidebarReopenBtn { display: flex !important; }
            #sidebarReopenBtn.sidebar-is-open { display: none !important; }
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
        }
        .fi {
            width: 12px;
            height: 9px;
            display: inline-block;
            background-size: contain;
            background-position: center;
            background-repeat: no-repeat;
            vertical-align: middle;
        }
    </style>

    @stack('styles')
</head>
<body class="bg-gray-50">

    @include('common.navbar')

    <div class="agent-layout-wrapper max-w-7xl mx-auto">

        <div id="agentSidebarOverlay" class="agent-sidebar-overlay" onclick="closeSidebar()"></div>

        @include('user.layouts.sidebar')

        {{-- Floating reopen button - shown when sidebar is closed/collapsed --}}
        <button id="sidebarReopenBtn" onclick="toggleSidebar()"
                class="fixed left-0 z-40 flex-col gap-1 justify-center items-center w-6 h-14 rounded-r-lg shadow-md transition"
                style="display:none; top: 50%; transform: translateY(-50%); background:#0077BE;" title="Open Sidebar">
            <span class="w-3 h-px rounded bg-white"></span>
            <span class="w-3 h-px rounded bg-white"></span>
            <span class="w-3 h-px rounded bg-white"></span>
        </button>

        <main class="agent-main-content">
            <div class="p-4 lg:p-5">

                @if(session('success'))
                    <div class="mb-4 flex items-center gap-3 p-4 bg-green-50 border border-green-200 rounded-lg text-green-700 text-sm">
                        <i class="fas fa-check-circle flex-shrink-0"></i>
                        <span>{{ session('success') }}</span>
                        <button onclick="this.parentElement.remove()" class="ml-auto text-green-500 hover:text-green-700"><i class="fas fa-times"></i></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-4 flex items-center gap-3 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
                        <i class="fas fa-exclamation-circle flex-shrink-0"></i>
                        <span>{{ session('error') }}</span>
                        <button onclick="this.parentElement.remove()" class="ml-auto text-red-500 hover:text-red-700"><i class="fas fa-times"></i></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-4 flex items-center gap-3 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
                        <i class="fas fa-exclamation-circle flex-shrink-0"></i>
                        <span>{{ $errors->first() }}</span>
                        <button onclick="this.parentElement.remove()" class="ml-auto text-red-500 hover:text-red-700"><i class="fas fa-times"></i></button>
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    <script src="{{ url('public/assets/js/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ url('public/assets/libs/select2/js/select2.min.js') }}"></script>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('agentSidebar');
            const overlay = document.getElementById('agentSidebarOverlay');
            const reopenBtn = document.getElementById('sidebarReopenBtn');
            const isDesktop = window.innerWidth >= 1024;
            if (isDesktop) {
                sidebar.classList.toggle('sidebar-collapsed');
                const collapsed = sidebar.classList.contains('sidebar-collapsed');
                if (reopenBtn) reopenBtn.style.display = collapsed ? 'flex' : 'none';
            } else {
                sidebar.classList.toggle('open');
                overlay.classList.toggle('active');
                const isOpen = sidebar.classList.contains('open');
                if (reopenBtn) reopenBtn.classList.toggle('sidebar-is-open', isOpen);
            }
        }
        function closeSidebar() {
            const sidebar = document.getElementById('agentSidebar');
            const overlay = document.getElementById('agentSidebarOverlay');
            const reopenBtn = document.getElementById('sidebarReopenBtn');
            sidebar.classList.remove('open');
            overlay.classList.remove('active');
            if (reopenBtn) reopenBtn.classList.remove('sidebar-is-open');
        }
        function toggleNavGroup(btn) {
            const submenu = btn.nextElementSibling;
            const icon = btn.querySelector('.fa-chevron-down');
            submenu.classList.toggle('hidden');
            if (icon) icon.classList.toggle('rotate-180');
        }
    </script>
    @stack('scripts')
</body>
</html>
