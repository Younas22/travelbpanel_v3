@php $agent = auth()->user(); @endphp

<aside id="amSidebar" class="am-sidebar">

    <div class="am-sidebar-header">
        <a href="{{ route('agent.dashboard') }}" class="am-sidebar-brand">
            @if(getSettingImage('favicon','branding'))
                <img src="{{ getSettingImage('business_logo','branding') }}"
                     alt="{{ getSetting('business_name', 'main', 'TravelPanel') }}"
                     class="am-sidebar-logo">
            @else
                <div class="am-sidebar-logo-icon"><i class="bi bi-airplane"></i></div>
            @endif
        </a>
        <button class="am-sidebar-close" type="button" onclick="toggleAmSidebar()" aria-label="Toggle sidebar">
            <i class="bi bi-list"></i>
        </button>
    </div>

    <div class="am-sidebar-agent">
        @if($agent->profile_image)
            <img src="{{ url('public/assets/images/agents/' . $agent->profile_image) }}" class="am-sidebar-avatar">
        @elseif($agent->company_logo)
            <img src="{{ url('public/assets/images/settings/branding/' . $agent->company_logo) }}" class="am-sidebar-avatar">
        @else
            <div class="am-sidebar-avatar">{{ $agent->initials ?? strtoupper(substr($agent->first_name,0,1).substr($agent->last_name,0,1)) }}</div>
        @endif
        <div class="min-w-0">
            <div class="am-sidebar-agent-name">{{ $agent->first_name }} {{ $agent->last_name }}</div>
            <div class="am-sidebar-agent-role">Travel Agent</div>
        </div>
    </div>

    <nav class="am-sidebar-nav">

        <a href="{{ route('agent.dashboard') }}" class="am-nav-row {{ request()->routeIs('agent.dashboard') ? 'am-nav-on' : '' }}">
            <i class="bi bi-speedometer2 am-nav-ico"></i>
            <span>Dashboard</span>
        </a>

        @if($agent->hasPermission('wallet.view'))
        <a href="{{ route('agent.wallet.index') }}" class="am-nav-row {{ request()->routeIs('agent.wallet*') ? 'am-nav-on' : '' }}">
            <i class="bi bi-wallet2 am-nav-ico"></i>
            <span>My Wallet</span>
            @if($agent->wallet)
                <span class="am-nav-badge">PKR {{ number_format($agent->wallet->balance, 0) }}</span>
            @endif
        </a>
        @endif

        @if($agent->hasPermission('bookings.view'))
        <a href="{{ route('agent.bookings.index') }}" class="am-nav-row {{ request()->routeIs('agent.bookings*') ? 'am-nav-on' : '' }}">
            <i class="bi bi-calendar-check am-nav-ico"></i>
            <span>My Bookings</span>
        </a>
        @endif

        @if($agent->hasPermission('hotels.add') || $agent->hasPermission('tours.add') || $agent->hasPermission('umrah.add'))
        <span class="am-nav-section-label">My Properties</span>

        @if($agent->hasPermission('hotels.add'))
        <div class="am-nav-group {{ request()->routeIs('agent.hotels*') ? 'am-nav-group-open' : '' }}">
            <button type="button" onclick="toggleAmNavGroup(this)" class="am-nav-row {{ request()->routeIs('agent.hotels*') ? 'am-nav-on' : '' }}">
                <i class="bi bi-building am-nav-ico"></i>
                <span>Hotel Management</span>
                <i class="bi bi-chevron-down am-nav-chev"></i>
            </button>
            <div class="am-nav-kids">
                <a href="{{ route('agent.hotels.index') }}" class="am-nav-kid {{ request()->routeIs('agent.hotels.index') || request()->routeIs('agent.hotels.create') || request()->routeIs('agent.hotels.edit') ? 'am-nav-kid-on' : '' }}">
                    <i class="bi bi-building"></i> Hotels
                </a>
                <a href="{{ route('agent.hotels.amenities.index') }}" class="am-nav-kid {{ request()->routeIs('agent.hotels.amenities*') ? 'am-nav-kid-on' : '' }}">
                    <i class="bi bi-stars"></i> Amenities
                </a>
            </div>
        </div>
        @endif

        @if($agent->hasPermission('tours.add'))
        <div class="am-nav-group {{ request()->routeIs('agent.tours*') ? 'am-nav-group-open' : '' }}">
            <button type="button" onclick="toggleAmNavGroup(this)" class="am-nav-row {{ request()->routeIs('agent.tours*') ? 'am-nav-on' : '' }}">
                <i class="bi bi-map am-nav-ico"></i>
                <span>Tours Management</span>
                <i class="bi bi-chevron-down am-nav-chev"></i>
            </button>
            <div class="am-nav-kids">
                <a href="{{ route('agent.tours.index') }}" class="am-nav-kid {{ request()->routeIs('agent.tours.index') || request()->routeIs('agent.tours.create') || request()->routeIs('agent.tours.edit') ? 'am-nav-kid-on' : '' }}">
                    <i class="bi bi-box-seam"></i> Tour Packages
                </a>
                <a href="{{ route('agent.tours.package-types.index') }}" class="am-nav-kid {{ request()->routeIs('agent.tours.package-types*') ? 'am-nav-kid-on' : '' }}">
                    <i class="bi bi-tags"></i> Package Types
                </a>
                <a href="{{ route('agent.tours.inclusions.index') }}" class="am-nav-kid {{ request()->routeIs('agent.tours.inclusions*') ? 'am-nav-kid-on' : '' }}">
                    <i class="bi bi-check-circle"></i> Inclusions
                </a>
                <a href="{{ route('agent.tours.exclusions.index') }}" class="am-nav-kid {{ request()->routeIs('agent.tours.exclusions*') ? 'am-nav-kid-on' : '' }}">
                    <i class="bi bi-x-circle"></i> Exclusions
                </a>
            </div>
        </div>
        @endif

        @if($agent->hasPermission('umrah.add'))
        <div class="am-nav-group {{ request()->routeIs('agent.umrah*') ? 'am-nav-group-open' : '' }}">
            <button type="button" onclick="toggleAmNavGroup(this)" class="am-nav-row {{ request()->routeIs('agent.umrah*') ? 'am-nav-on' : '' }}">
                <i class="bi bi-moon-stars am-nav-ico"></i>
                <span>Umrah Management</span>
                <i class="bi bi-chevron-down am-nav-chev"></i>
            </button>
            <div class="am-nav-kids">
                <a href="{{ route('agent.umrah.index') }}" class="am-nav-kid {{ request()->routeIs('agent.umrah.index') || request()->routeIs('agent.umrah.create') || request()->routeIs('agent.umrah.edit') ? 'am-nav-kid-on' : '' }}">
                    <i class="bi bi-box-seam"></i> Umrah Packages
                </a>
                <a href="{{ route('agent.umrah.package-types.index') }}" class="am-nav-kid {{ request()->routeIs('agent.umrah.package-types*') ? 'am-nav-kid-on' : '' }}">
                    <i class="bi bi-tags"></i> Package Types
                </a>
                <a href="{{ route('agent.umrah.inclusions.index') }}" class="am-nav-kid {{ request()->routeIs('agent.umrah.inclusions*') ? 'am-nav-kid-on' : '' }}">
                    <i class="bi bi-check-circle"></i> Inclusions
                </a>
                <a href="{{ route('agent.umrah.exclusions.index') }}" class="am-nav-kid {{ request()->routeIs('agent.umrah.exclusions*') ? 'am-nav-kid-on' : '' }}">
                    <i class="bi bi-x-circle"></i> Exclusions
                </a>
            </div>
        </div>
        @endif

        @endif {{-- end My Properties --}}

        <span class="am-nav-section-label">Account</span>

        @if($agent->hasPermission('wallet.request'))
        <a href="{{ route('agent.wallet.topup') }}" class="am-nav-row">
            <i class="bi bi-plus-circle am-nav-ico"></i>
            <span>Request Top-Up</span>
        </a>
        @endif

        <a href="{{ route('agent.settings.theme') }}" class="am-nav-row {{ request()->routeIs('agent.settings.theme*') ? 'am-nav-on' : '' }}">
            <i class="bi bi-palette am-nav-ico"></i>
            <span>Theme</span>
        </a>

        <a href="{{ route('agent.profile.index') }}" class="am-nav-row {{ request()->routeIs('agent.profile*') ? 'am-nav-on' : '' }}">
            <i class="bi bi-person-circle am-nav-ico"></i>
            <span>My Profile</span>
        </a>

        <form method="POST" action="{{ route('agent.logout') }}">
            @csrf
            <button type="submit" class="am-nav-row" style="color: var(--danger-color);">
                <i class="bi bi-box-arrow-right am-nav-ico" style="color: var(--danger-color);"></i>
                <span>Logout</span>
            </button>
        </form>

    </nav>
</aside>
