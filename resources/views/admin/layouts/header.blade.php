<!-- Header -->
<header class="adm-header">
    <div class="adm-header-left">
        <button class="adm-menu-btn d-lg-none" type="button" onclick="toggleSidebar()" aria-label="Toggle sidebar">
            <i class="bi bi-list"></i>
        </button>
        <div>
            <h5 class="adm-header-title">Dashboard</h5>
            <span class="adm-header-sub">Welcome back, {{ auth()->user()->first_name }}!</span>
        </div>
    </div>

    <div class="adm-header-right">




        <!-- User Menu -->
        <div class="dropdown">
            <a href="#" class="adm-user-btn dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                @php
                    $initials = collect(explode(' ', auth()->user()->full_name))
                        ->map(fn($n) => strtoupper(substr($n, 0, 1)))
                        ->take(2)->implode('');
                @endphp
                <div class="adm-user-avatar">
                    @if(auth()->user()->profile_image)
                        <img src="{{ url('public/'.auth()->user()->profile_image) }}"
                             alt="{{ auth()->user()->full_name }}"
                             class="adm-user-img">
                    @else
                        {{ $initials }}
                    @endif
                </div>
                <div class="adm-user-info">
                    <span class="adm-user-name">{{ auth()->user()->full_name }}</span>
                    <span class="adm-user-role">Administrator</span>
                </div>
                <i class="bi bi-chevron-down adm-user-chevron"></i>
            </a>

            <ul class="dropdown-menu dropdown-menu-end adm-dropdown">
                <li class="adm-dd-header">
                    <div class="adm-dd-avatar">{{ $initials }}</div>
                    <div>
                        <div class="adm-dd-name">{{ auth()->user()->full_name }}</div>
                        <div class="adm-dd-role">Administrator</div>
                    </div>
                </li>
                <li><div class="adm-dd-sep"></div></li>
                <li>
                    <a class="adm-dd-item" href="{{ route('admin.profile.index') }}">
                        <i class="bi bi-person"></i> My profile
                    </a>
                </li>
                <li>
                    <a class="adm-dd-item" href="{{ route('admin.settings.website') }}">
                        <i class="bi bi-gear"></i> Settings
                    </a>
                </li>
                <li><div class="adm-dd-sep"></div></li>
                <li>
                    <a class="adm-dd-item adm-dd-logout" href="#"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                    <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>
