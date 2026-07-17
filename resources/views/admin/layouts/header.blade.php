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

<style>
    /* ── Header shell ───────────────────────────────────── */
    .adm-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 1.5rem;
        height: 58px;
        background: var(--bs-body-bg);
        border-bottom: 1px solid var(--bs-border-color);
        position: sticky;
        top: 0;
        z-index: 100;
    }

    /* ── Left ───────────────────────────────────────────── */
    .adm-header-left {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .adm-menu-btn {
        width: 34px;
        height: 34px;
        background: var(--bs-secondary-bg);
        border: 1px solid var(--bs-border-color);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 17px;
        color: var(--bs-secondary-color);
        cursor: pointer;
        transition: background .15s;
    }

    .adm-menu-btn:hover {
        background: var(--bs-tertiary-bg);
    }

    .adm-header-title {
        font-size: 14px;
        font-weight: 600;
        color: var(--bs-body-color);
        margin: 0;
        line-height: 1.2;
    }

    .adm-header-sub {
        font-size: 11px;
        color: var(--bs-secondary-color);
        display: block;
        margin-top: 1px;
    }

    /* ── Right ──────────────────────────────────────────── */
    .adm-header-right {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .adm-icon-btn {
        width: 34px;
        height: 34px;
        background: var(--bs-secondary-bg);
        border: 1px solid var(--bs-border-color);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
        color: var(--bs-secondary-color);
        cursor: pointer;
        position: relative;
        transition: background .15s;
    }

    .adm-icon-btn:hover {
        background: var(--bs-tertiary-bg);
        color: var(--bs-body-color);
    }

    .adm-notif-dot {
        position: absolute;
        top: 7px;
        right: 7px;
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #dc3545;
        border: 1.5px solid var(--bs-body-bg);
    }

    .adm-divider-v {
        width: 1px;
        height: 20px;
        background: var(--bs-border-color);
        margin: 0 4px;
    }

    /* ── User button ────────────────────────────────────── */
    .adm-user-btn {
        display: inline-flex;
        align-items: center;
        gap: 9px;
        padding: 4px 10px 4px 4px;
        border: 1px solid var(--bs-border-color);
        border-radius: 10px;
        background: var(--bs-body-bg);
        text-decoration: none;
        color: inherit;
        transition: background .15s, border-color .15s;
    }

    .adm-user-btn:hover {
        background: var(--bs-secondary-bg);
        border-color: var(--bs-border-color);
        text-decoration: none;
        color: inherit;
    }

    .adm-user-btn::after {
        display: none;
    }

    .adm-user-avatar {
        width: 28px;
        height: 28px;
        border-radius: 8px;
        background: #B5D4F4;
        color: #0C447C;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: 600;
        flex-shrink: 0;
        overflow: hidden;
    }

    .adm-user-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .adm-user-info {
        display: flex;
        flex-direction: column;
        gap: 1px;
    }

    .adm-user-name {
        font-size: 12px;
        font-weight: 600;
        color: var(--bs-body-color);
        white-space: nowrap;
        line-height: 1.2;
    }

    .adm-user-role {
        font-size: 10px;
        color: var(--bs-secondary-color);
        line-height: 1.2;
    }

    .adm-user-chevron {
        font-size: 10px;
        color: var(--bs-secondary-color);
        margin-left: 2px;
    }

    /* ── Dropdown ───────────────────────────────────────── */
    .adm-dropdown {
        min-width: 210px;
        border: 1px solid var(--bs-border-color);
        border-radius: 12px;
        padding: 6px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, .06);
        margin-top: 6px !important;
        background: var(--bs-body-bg);
    }

    .adm-dd-header {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 10px 12px;
    }

    .adm-dd-avatar {
        width: 34px;
        height: 34px;
        border-radius: 8px;
        background: #B5D4F4;
        color: #0C447C;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 600;
        flex-shrink: 0;
    }

    .adm-dd-name {
        font-size: 13px;
        font-weight: 600;
        color: var(--bs-body-color);
    }

    .adm-dd-role {
        font-size: 11px;
        color: var(--bs-secondary-color);
        margin-top: 1px;
    }

    .adm-dd-sep {
        height: 1px;
        background: var(--bs-border-color);
        margin: 4px 0;
    }

    .adm-dd-item {
        display: flex;
        align-items: center;
        gap: 9px;
        padding: 8px 10px;
        font-size: 13px;
        font-weight: 500;
        color: var(--bs-body-color);
        border-radius: 8px;
        text-decoration: none;
        transition: background .12s;
    }

    .adm-dd-item:hover {
        background: var(--bs-secondary-bg);
        color: var(--bs-body-color);
        text-decoration: none;
    }

    .adm-dd-item i {
        font-size: 15px;
        color: var(--bs-secondary-color);
    }

    .adm-dd-logout {
        color: #dc3545 !important;
    }

    .adm-dd-logout i {
        color: #dc3545 !important;
    }

    .adm-dd-logout:hover {
        background: #fff1f1 !important;
    }

    [data-bs-theme="dark"] .adm-dd-logout:hover {
        background: #2d1515 !important;
    }

    /* ── Responsive ─────────────────────────────────────── */
    @media (max-width: 576px) {
        .adm-header { padding: 0 1rem; }
        .adm-user-info { display: none; }
        .adm-user-btn { padding: 4px; gap: 6px; }
        .adm-user-chevron { display: none; }
        .adm-icon-btn { width: 32px; height: 32px; }
    }
</style>
