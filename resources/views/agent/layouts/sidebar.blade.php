@php $agent = auth()->user(); @endphp

<aside id="agentSidebar" class="agent-sidebar">

    {{-- Agent Info --}}
    <div class="p-4 border-b border-gray-100">
        <div class="flex items-center justify-between gap-2 mb-0">
        <div class="flex items-center gap-3 min-w-0 flex-1">
            @if($agent->profile_image)
                <img src="{{ url('public/assets/images/agents/' . $agent->profile_image) }}"
                     class="w-10 h-10 rounded-full object-cover border-2 flex-shrink-0 ap-accent-border">
            @elseif($agent->company_logo)
                <img src="{{ url('public/assets/images/settings/branding/' . $agent->company_logo) }}"
                     class="w-10 h-10 rounded-full object-contain border border-gray-200 bg-white flex-shrink-0">
            @else
                <div class="w-10 h-10 rounded-full flex items-center justify-center text-white text-sm font-bold flex-shrink-0 ap-solid-accent">
                    {{ $agent->initials ?? strtoupper(substr($agent->first_name,0,1).substr($agent->last_name,0,1)) }}
                </div>
            @endif
            <div class="min-w-0">
                <p class="text-sm font-semibold text-gray-800 truncate">{{ $agent->first_name }} {{ $agent->last_name }}</p>
                <p class="text-xs text-gray-400 truncate">Travel Agent</p>
            </div>
        </div>
        <button onclick="toggleSidebar()"
                class="flex flex-col gap-1 justify-center items-center w-8 h-8 rounded-lg hover:bg-blue-50 transition flex-shrink-0"
                title="Toggle Sidebar">
            <span class="w-3.5 h-px rounded bg-gray-500"></span>
            <span class="w-3.5 h-px rounded bg-gray-500"></span>
            <span class="w-3.5 h-px rounded bg-gray-500"></span>
        </button>
        </div>
    </div>

    <!-- Nav -->
    <nav class="p-3 space-y-0.5">

        {{-- Dashboard --}}
        <a href="{{ route('agent.dashboard') }}"
           class="agent-nav-link {{ request()->routeIs('agent.dashboard') ? 'active' : '' }}">
            <i class="fas fa-gauge-high w-5 text-center"></i>
            <span>Dashboard</span>
        </a>

        {{-- Wallet --}}
        @if($agent->hasPermission('wallet.view'))
        <a href="{{ route('agent.wallet.index') }}"
           class="agent-nav-link {{ request()->routeIs('agent.wallet*') ? 'active' : '' }}">
            <i class="fas fa-wallet w-5 text-center"></i>
            <span>My Wallet</span>
            @if($agent->wallet)
                <span class="ml-auto text-xs px-2 py-0.5 bg-green-100 text-green-700 rounded-full font-semibold whitespace-nowrap">
                    PKR {{ number_format($agent->wallet->balance, 0) }}
                </span>
            @endif
        </a>
        @endif

        {{-- Bookings --}}
        @if($agent->hasPermission('bookings.view'))
        <a href="{{ route('agent.bookings.index') }}"
           class="agent-nav-link {{ request()->routeIs('agent.bookings*') ? 'active' : '' }}">
            <i class="fas fa-calendar-check w-5 text-center"></i>
            <span>My Bookings</span>
        </a>
        @endif

        {{-- Support --}}
        <a href="{{ route('agent.support.index') }}"
           class="agent-nav-link {{ request()->routeIs('agent.support*') ? 'active' : '' }}">
            <i class="fas fa-headset w-5 text-center"></i>
            <span>Support</span>
        </a>

        {{-- My Properties --}}
        @if($agent->hasPermission('hotels.add') || $agent->hasPermission('tours.add') || $agent->hasPermission('umrah.add'))
        <span class="agent-nav-section-title">My Properties</span>

        {{-- Hotel Management --}}
        @if($agent->hasPermission('hotels.add'))
        <div>
            <button onclick="toggleNavGroup(this)"
                    class="agent-nav-link {{ request()->routeIs('agent.hotels*') ? 'active' : '' }}">
                <i class="fas fa-building w-5 text-center"></i>
                <span>Hotel Management</span>
                <i class="fas fa-chevron-down text-xs ml-auto transition-transform duration-200 {{ request()->routeIs('agent.hotels*') ? 'rotate-180' : '' }}"></i>
            </button>
            <div class="pl-7 space-y-0.5 mt-0.5 {{ request()->routeIs('agent.hotels*') ? '' : 'hidden' }}">
                <a href="{{ route('agent.hotels.index') }}"
                   class="agent-nav-link text-xs {{ request()->routeIs('agent.hotels.index') || request()->routeIs('agent.hotels.create') || request()->routeIs('agent.hotels.edit') ? 'active' : '' }}">
                    <i class="fas fa-hotel w-4 text-center"></i> Hotels
                </a>
                <a href="{{ route('agent.hotels.amenities.index') }}"
                   class="agent-nav-link text-xs {{ request()->routeIs('agent.hotels.amenities*') ? 'active' : '' }}">
                    <i class="fas fa-star w-4 text-center"></i> Amenities
                </a>
            </div>
        </div>
        @endif

        {{-- Tours Management --}}
        @if($agent->hasPermission('tours.add'))
        <div>
            <button onclick="toggleNavGroup(this)"
                    class="agent-nav-link {{ request()->routeIs('agent.tours*') ? 'active' : '' }}">
                <i class="fas fa-map-location-dot w-5 text-center"></i>
                <span>Tours Management</span>
                <i class="fas fa-chevron-down text-xs ml-auto transition-transform duration-200 {{ request()->routeIs('agent.tours*') ? 'rotate-180' : '' }}"></i>
            </button>
            <div class="pl-7 space-y-0.5 mt-0.5 {{ request()->routeIs('agent.tours*') ? '' : 'hidden' }}">
                <a href="{{ route('agent.tours.index') }}"
                   class="agent-nav-link text-xs {{ request()->routeIs('agent.tours.index') || request()->routeIs('agent.tours.create') || request()->routeIs('agent.tours.edit') ? 'active' : '' }}">
                    <i class="fas fa-box w-4 text-center"></i> Tour Packages
                </a>
                <a href="{{ route('agent.tours.package-types.index') }}"
                   class="agent-nav-link text-xs {{ request()->routeIs('agent.tours.package-types*') ? 'active' : '' }}">
                    <i class="fas fa-tags w-4 text-center"></i> Package Types
                </a>
                <a href="{{ route('agent.tours.inclusions.index') }}"
                   class="agent-nav-link text-xs {{ request()->routeIs('agent.tours.inclusions*') ? 'active' : '' }}">
                    <i class="fas fa-circle-check w-4 text-center"></i> Inclusions
                </a>
                <a href="{{ route('agent.tours.exclusions.index') }}"
                   class="agent-nav-link text-xs {{ request()->routeIs('agent.tours.exclusions*') ? 'active' : '' }}">
                    <i class="fas fa-circle-xmark w-4 text-center"></i> Exclusions
                </a>
            </div>
        </div>
        @endif

        {{-- Umrah Management --}}
        @if($agent->hasPermission('umrah.add'))
        <div>
            <button onclick="toggleNavGroup(this)"
                    class="agent-nav-link {{ request()->routeIs('agent.umrah*') ? 'active' : '' }}">
                <i class="fas fa-moon w-5 text-center"></i>
                <span>Umrah Management</span>
                <i class="fas fa-chevron-down text-xs ml-auto transition-transform duration-200 {{ request()->routeIs('agent.umrah*') ? 'rotate-180' : '' }}"></i>
            </button>
            <div class="pl-7 space-y-0.5 mt-0.5 {{ request()->routeIs('agent.umrah*') ? '' : 'hidden' }}">
                <a href="{{ route('agent.umrah.index') }}"
                   class="agent-nav-link text-xs {{ request()->routeIs('agent.umrah.index') || request()->routeIs('agent.umrah.create') || request()->routeIs('agent.umrah.edit') ? 'active' : '' }}">
                    <i class="fas fa-box w-4 text-center"></i> Umrah Packages
                </a>
                <a href="{{ route('agent.umrah.package-types.index') }}"
                   class="agent-nav-link text-xs {{ request()->routeIs('agent.umrah.package-types*') ? 'active' : '' }}">
                    <i class="fas fa-tags w-4 text-center"></i> Package Types
                </a>
                <a href="{{ route('agent.umrah.inclusions.index') }}"
                   class="agent-nav-link text-xs {{ request()->routeIs('agent.umrah.inclusions*') ? 'active' : '' }}">
                    <i class="fas fa-circle-check w-4 text-center"></i> Inclusions
                </a>
                <a href="{{ route('agent.umrah.exclusions.index') }}"
                   class="agent-nav-link text-xs {{ request()->routeIs('agent.umrah.exclusions*') ? 'active' : '' }}">
                    <i class="fas fa-circle-xmark w-4 text-center"></i> Exclusions
                </a>
            </div>
        </div>
        @endif

        @endif {{-- end My Properties --}}

        {{-- Account --}}
        <span class="agent-nav-section-title">Account</span>

        @if($agent->hasPermission('wallet.request'))
        <a href="{{ route('agent.wallet.topup') }}" class="agent-nav-link">
            <i class="fas fa-plus-circle w-5 text-center"></i>
            <span>Request Top-Up</span>
        </a>
        @endif

        <a href="{{ route('agent.settings.theme') }}"
           class="agent-nav-link {{ request()->routeIs('agent.settings.theme*') ? 'active' : '' }}">
            <i class="fas fa-palette w-5 text-center"></i>
            <span>Theme</span>
        </a>

        <a href="{{ route('agent.profile.index') }}"
           class="agent-nav-link {{ request()->routeIs('agent.profile*') ? 'active' : '' }}">
            @if($agent->profile_image)
                <img src="{{ url('public/assets/images/agents/' . $agent->profile_image) }}"
                     class="w-5 h-5 rounded-full object-cover flex-shrink-0">
            @elseif($agent->company_logo)
                <img src="{{ url('public/assets/images/settings/branding/' . $agent->company_logo) }}"
                     class="w-5 h-5 rounded-full object-contain bg-white flex-shrink-0">
            @else
                <i class="fas fa-user-circle w-5 text-center"></i>
            @endif
            <span>My Profile</span>
        </a>

        <form method="POST" action="{{ route('agent.logout') }}">
            @csrf
            <button type="submit" class="agent-nav-link text-red-500 hover:!bg-red-50 hover:!text-red-600">
                <i class="fas fa-right-from-bracket w-5 text-center"></i>
                <span>Logout</span>
            </button>
        </form>

    </nav>
</aside>

<script>
function toggleNavGroup(btn) {
    const submenu = btn.nextElementSibling;
    const icon = btn.querySelector('.fa-chevron-down');
    submenu.classList.toggle('hidden');
    if (icon) icon.classList.toggle('rotate-180');
}
</script>
