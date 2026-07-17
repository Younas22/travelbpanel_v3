@php $user = auth()->user(); @endphp

<aside id="agentSidebar" class="agent-sidebar">

    {{-- User Info --}}
    <div class="p-4 border-b border-gray-100">
        <div class="flex items-center justify-between gap-2 mb-0">
        <div class="flex items-center gap-3 min-w-0 flex-1">
            @if($user->profile_image)
                <img src="{{ url('public/assets/images/avatars/' . $user->profile_image) }}"
                     class="w-10 h-10 rounded-full object-cover border-2 flex-shrink-0" style="border-color:#0077BE;">
            @else
                <div class="w-10 h-10 rounded-full flex items-center justify-center text-white text-sm font-bold flex-shrink-0" style="background:#0077BE;">
                    {{ $user->initials ?? strtoupper(substr($user->first_name,0,1).substr($user->last_name,0,1)) }}
                </div>
            @endif
            <div class="min-w-0">
                <p class="text-sm font-semibold text-gray-800 truncate">{{ $user->first_name }} {{ $user->last_name }}</p>
                <p class="text-xs text-gray-400 truncate">{{ $user->email }}</p>
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

    <nav class="p-3 space-y-0.5">

        {{-- Dashboard --}}
        <a href="{{ route('user.dashboard') }}"
           class="agent-nav-link {{ request()->routeIs('user.dashboard') ? 'active' : '' }}">
            <i class="fas fa-gauge-high w-5 text-center"></i>
            <span>Dashboard</span>
        </a>

        {{-- My Bookings --}}
        <div>
            <button onclick="toggleNavGroup(this)"
                    class="agent-nav-link {{ request()->routeIs('user.bookings*') ? 'active' : '' }}">
                <i class="fas fa-calendar-check w-5 text-center"></i>
                <span>My Bookings</span>
                <i class="fas fa-chevron-down text-xs ml-auto transition-transform duration-200 {{ request()->routeIs('user.bookings*') ? 'rotate-180' : '' }}"></i>
            </button>
            <div class="pl-7 space-y-0.5 mt-0.5 {{ request()->routeIs('user.bookings*') ? '' : 'hidden' }}">
                <a href="{{ route('user.bookings.index') }}"
                   class="agent-nav-link text-xs {{ request()->routeIs('user.bookings.index') && !request('type') ? 'active' : '' }}">
                    <i class="fas fa-list w-4 text-center"></i> All Bookings
                </a>
                <a href="{{ route('user.bookings.index', ['type' => 'hotel']) }}"
                   class="agent-nav-link text-xs {{ request('type') === 'hotel' ? 'active' : '' }}">
                    <i class="fas fa-hotel w-4 text-center"></i> Stays
                </a>
                <a href="{{ route('user.bookings.index', ['type' => 'flight']) }}"
                   class="agent-nav-link text-xs {{ request('type') === 'flight' ? 'active' : '' }}">
                    <i class="fas fa-plane-departure w-4 text-center"></i> Flights
                </a>
                <a href="{{ route('user.bookings.index', ['type' => 'tour']) }}"
                   class="agent-nav-link text-xs {{ request('type') === 'tour' ? 'active' : '' }}">
                    <i class="fas fa-map-location-dot w-4 text-center"></i> Tours
                </a>
                <a href="{{ route('user.bookings.index', ['type' => 'visa']) }}"
                   class="agent-nav-link text-xs {{ request('type') === 'visa' ? 'active' : '' }}">
                    <i class="fas fa-file-alt w-4 text-center"></i> Visa
                </a>
            </div>
        </div>

        {{-- Support --}}
        <div>
            <button onclick="toggleNavGroup(this)"
                    class="agent-nav-link {{ request()->routeIs('user.support*') ? 'active' : '' }}">
                <i class="fas fa-headset w-5 text-center"></i>
                <span>Support</span>
                <i class="fas fa-chevron-down text-xs ml-auto transition-transform duration-200 {{ request()->routeIs('user.support*') ? 'rotate-180' : '' }}"></i>
            </button>
            <div class="pl-7 space-y-0.5 mt-0.5 {{ request()->routeIs('user.support*') ? '' : 'hidden' }}">
                <a href="{{ route('user.support.index') }}"
                   class="agent-nav-link text-xs {{ request()->routeIs('user.support.index') ? 'active' : '' }}">
                    <i class="fas fa-ticket w-4 text-center"></i> Support Tickets
                </a>
            </div>
        </div>

        {{-- Account --}}
        <span class="agent-nav-section-title">Account</span>

        <a href="{{ route('user.profile.index') }}"
           class="agent-nav-link {{ request()->routeIs('user.profile*') ? 'active' : '' }}">
            @if($user->profile_image)
                <img src="{{ url('public/assets/images/avatars/' . $user->profile_image) }}"
                     class="w-5 h-5 rounded-full object-cover flex-shrink-0">
            @else
                <i class="fas fa-user-circle w-5 text-center"></i>
            @endif
            <span>Profile</span>
        </a>

        <form method="POST" action="{{ route('user.logout') }}">
            @csrf
            <button type="submit" class="agent-nav-link text-red-500 hover:!bg-red-50 hover:!text-red-600">
                <i class="fas fa-right-from-bracket w-5 text-center"></i>
                <span>Logout</span>
            </button>
        </form>

        {{-- Quick Actions --}}
        <span class="agent-nav-section-title">Quick Actions</span>

        <a href="{{ route('user.profile.index') }}"
           class="agent-nav-link">
            <i class="fas fa-user-pen w-5 text-center"></i>
            <span>Edit Profile</span>
        </a>

    </nav>
</aside>
