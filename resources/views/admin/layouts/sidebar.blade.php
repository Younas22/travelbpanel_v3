<div class="sidebar">

    {{-- Header --}}
    <div class="sidebar-header">
        <button class="sidebar-toggle d-lg-none" type="button" onclick="toggleSidebar()" aria-label="Toggle sidebar">
            <i class="bi bi-list"></i>
        </button>
        <a href="{{ route('admin.dashboard.index') }}" class="sidebar-brand">
            @if(getSettingImage('favicon','branding'))
                <img src="{{ getSettingImage('business_logo','branding') }}"
                     alt="{{ getSetting('business_name', 'main', 'Default Title') }}"
                     class="sidebar-logo">
            @else
                <div class="sidebar-logo-icon">
                    <i class="bi bi-airplane"></i>
                </div>
            @endif
        </a>

        {{-- Desktop sidebar collapse button — Nova only (see .sidebar-collapse-btn
             in admin-modern.css, display:none by default, shown under
             body.design-nova). Classic/Modern leave it display:none, so
             it's inert dead markup for them. This file is Classic's own
             separate sidebar (not the shared admin-modern one), but Nova
             still renders through it on every admin.* page it hasn't
             converted yet, so it needs the same button as admin-modern's
             sidebar.blade.php for a consistent Nova sidebar everywhere. --}}
        <button class="sidebar-collapse-btn" type="button" onclick="toggleNovaSidebar()" aria-label="Collapse sidebar">
            <i class="bi bi-layout-sidebar-inset"></i>
        </button>
    </div>

    {{-- View Website --}}
    <div class="sb-view-web">
        <a href="{{ url('/') }}" target="_blank" class="sb-web-btn">
            <i class="bi bi-globe2"></i>
            <span>View website</span>
            <i class="bi bi-arrow-up-right"></i>
        </a>
    </div>

    {{-- Navigation --}}
    <nav class="sidebar-nav">

        {{-- Dashboard --}}
        <a href="{{ route('admin.dashboard.index') }}"
           class="sb-row {{ request()->routeIs('admin.dashboard*') ? 'sb-on' : '' }}">
            <i class="bi bi-speedometer2 sb-ico"></i>
            <span>Dashboard</span>
        </a>

        {{-- Bookings --}}
        <div class="sb-group {{ request()->routeIs('admin.bookings*') ? 'sb-group-open' : '' }}">
            <button class="sb-row {{ request()->routeIs('admin.bookings*') ? 'sb-on' : '' }}"
                    onclick="toggleSbGroup(this)" type="button">
                <i class="bi bi-calendar-check sb-ico"></i>
                <span>Bookings</span>
                <div class="sb-right">
                    <i class="bi bi-chevron-right sb-chev"></i>
                </div>
            </button>
            <div class="sb-kids">
                <a href="{{ route('admin.bookings.all') }}"
                   class="sb-kid {{ request()->routeIs('admin.bookings.all') && !request('type') ? 'sb-kid-on' : '' }}">
                    <i class="bi bi-list-ul"></i> All bookings
                </a>
                <a href="{{ route('admin.bookings.cancelled-refunds') }}"
                   class="sb-kid {{ request()->routeIs('admin.bookings.cancelled-refunds') ? 'sb-kid-on' : '' }}">
                    <i class="bi bi-x-circle"></i> Cancelled / refunds
                </a>
                <a href="{{ route('admin.bookings.pending-confirmations') }}"
                   class="sb-kid {{ request()->routeIs('admin.bookings.pending-confirmations') ? 'sb-kid-on' : '' }}">
                    <i class="bi bi-clock"></i> Pending confirmations
                </a>
            </div>
        </div>

        {{-- Suppliers --}}
        <a href="{{ route('admin.travel-partners.index') }}"
           class="sb-row {{ request()->routeIs('admin.travel-partners*') ? 'sb-on' : '' }}">
            <i class="bi bi-building sb-ico"></i>
            <span>Suppliers</span>
        </a>

        {{-- Agents — hidden in B2C-only mode --}}
        @if((\App\Models\Setting::getValue('business_model', 'system') ?? 'both') !== 'b2c')
            @php $pendingAgents = \App\Models\User::agents()->where('approval_status', 'pending')->count(); @endphp
            <div class="sb-group {{ request()->routeIs('admin.agents*') || request()->routeIs('admin.topup*') ? 'sb-group-open' : '' }}">
                <button class="sb-row {{ request()->routeIs('admin.agents*') || request()->routeIs('admin.topup*') ? 'sb-on' : '' }}"
                        onclick="toggleSbGroup(this)" type="button">
                    <i class="bi bi-person-badge sb-ico"></i>
                    <span>Agents</span>
                    <div class="sb-right">
                        @if($pendingAgents > 0)
                            <span class="sb-pill">{{ $pendingAgents }}</span>
                        @endif
                        <i class="bi bi-chevron-right sb-chev"></i>
                    </div>
                </button>
                <div class="sb-kids">
                    <a href="{{ route('admin.agents.index') }}"
                       class="sb-kid {{ request()->routeIs('admin.agents.index') ? 'sb-kid-on' : '' }}">
                        <i class="bi bi-people"></i> All agents
                    </a>
                    <a href="{{ route('admin.agents.create') }}"
                       class="sb-kid {{ request()->routeIs('admin.agents.create') ? 'sb-kid-on' : '' }}">
                        <i class="bi bi-person-plus"></i> Add new agent
                    </a>
                </div>
            </div>
        @endif

        @php
            // Show module in sidebar only when it has at least one active manual partner
            $sidebarModule = function(string $slug) {
                return \App\Models\Module::where('slug', $slug)
                    ->whereHas('partners', fn($p) => $p->where('status', 'active')->where('supplier_type', 'manual'))
                    ->first();
            };
            $visaModule   = $sidebarModule('visa');
            $umrahModule  = $sidebarModule('umrah');
            $toursModule  = $sidebarModule('tours');
            $hotelsModule = $sidebarModule('hotel');
        @endphp

        {{-- Visa --}}
        @if($visaModule)
            <a href="{{ route('admin.visa-requests.visaindex') }}"
               class="sb-row {{ request()->routeIs('admin.visa-requests*') ? 'sb-on' : '' }}">
                <i class="bi bi-passport sb-ico"></i>
                <span>Visa requests</span>
            </a>
        @endif

        {{-- Umrah --}}
        @if($umrahModule)
            <div class="sb-group {{ request()->routeIs('admin.umrah*') ? 'sb-group-open' : '' }}">
                <button class="sb-row {{ request()->routeIs('admin.umrah*') ? 'sb-on' : '' }}"
                        onclick="toggleSbGroup(this)" type="button">
                    <i class="bi bi-moon-stars sb-ico"></i>
                    <span>Umrah</span>
                    <div class="sb-right"><i class="bi bi-chevron-right sb-chev"></i></div>
                </button>
                <div class="sb-kids">
                    <a href="{{ route('admin.umrah.packages.index') }}" class="sb-kid {{ request()->routeIs('admin.umrah.packages*') ? 'sb-kid-on' : '' }}"><i class="bi bi-box-seam"></i> Umrah packages</a>
                    <a href="{{ route('admin.umrah.package-types.index') }}" class="sb-kid {{ request()->routeIs('admin.umrah.package-types*') ? 'sb-kid-on' : '' }}"><i class="bi bi-tags"></i> Package types</a>
                    <a href="{{ route('admin.umrah.inclusions.index') }}" class="sb-kid {{ request()->routeIs('admin.umrah.inclusions*') ? 'sb-kid-on' : '' }}"><i class="bi bi-check-circle"></i> Inclusions</a>
                    <a href="{{ route('admin.umrah.exclusions.index') }}" class="sb-kid {{ request()->routeIs('admin.umrah.exclusions*') ? 'sb-kid-on' : '' }}"><i class="bi bi-x-circle"></i> Exclusions</a>
                    <a href="{{ route('admin.bookings.all') }}?type=umrah" class="sb-kid {{ request()->routeIs('admin.bookings.all') && request('type') === 'umrah' ? 'sb-kid-on' : '' }}"><i class="bi bi-calendar-check"></i> Umrah bookings</a>
                </div>
            </div>
        @endif

        {{-- Tours --}}
        @if($toursModule)
            <div class="sb-group {{ request()->routeIs('admin.tours*') ? 'sb-group-open' : '' }}">
                <button class="sb-row {{ request()->routeIs('admin.tours*') ? 'sb-on' : '' }}"
                        onclick="toggleSbGroup(this)" type="button">
                    <i class="bi bi-geo-alt sb-ico"></i>
                    <span>Tours</span>
                    <div class="sb-right"><i class="bi bi-chevron-right sb-chev"></i></div>
                </button>
                <div class="sb-kids">
                    <a href="{{ route('admin.tours.packages.index') }}" class="sb-kid {{ request()->routeIs('admin.tours.packages*') ? 'sb-kid-on' : '' }}"><i class="bi bi-box-seam"></i> Tour packages</a>
                    <a href="{{ route('admin.tours.package-types.index') }}" class="sb-kid {{ request()->routeIs('admin.tours.package-types*') ? 'sb-kid-on' : '' }}"><i class="bi bi-tags"></i> Package types</a>
                    <a href="{{ route('admin.tours.inclusions.index') }}" class="sb-kid {{ request()->routeIs('admin.tours.inclusions*') ? 'sb-kid-on' : '' }}"><i class="bi bi-check-circle"></i> Inclusions</a>
                    <a href="{{ route('admin.tours.exclusions.index') }}" class="sb-kid {{ request()->routeIs('admin.tours.exclusions*') ? 'sb-kid-on' : '' }}"><i class="bi bi-x-circle"></i> Exclusions</a>
                    <a href="{{ route('admin.bookings.all') }}?type=tour" class="sb-kid {{ request()->routeIs('admin.bookings.all') && request('type') === 'tour' ? 'sb-kid-on' : '' }}"><i class="bi bi-calendar-check"></i> Tour bookings</a>
                </div>
            </div>
        @endif

        {{-- Hotels --}}
        @if($hotelsModule)
            <div class="sb-group {{ request()->routeIs('admin.hotels*') ? 'sb-group-open' : '' }}">
                <button class="sb-row {{ request()->routeIs('admin.hotels*') ? 'sb-on' : '' }}"
                        onclick="toggleSbGroup(this)" type="button">
                    <i class="bi bi-building sb-ico"></i>
                    <span>Hotels</span>
                    <div class="sb-right"><i class="bi bi-chevron-right sb-chev"></i></div>
                </button>
                <div class="sb-kids">
                    <a href="{{ route('admin.hotels.index') }}" class="sb-kid {{ request()->routeIs('admin.hotels.index*') || request()->routeIs('admin.hotels.edit*') || request()->routeIs('admin.hotels.create*') ? 'sb-kid-on' : '' }}"><i class="bi bi-building-fill"></i> Hotels</a>
                    <a href="{{ route('admin.hotels.amenities.index') }}" class="sb-kid {{ request()->routeIs('admin.hotels.amenities*') ? 'sb-kid-on' : '' }}"><i class="bi bi-stars"></i> Amenities</a>
                    <a href="{{ route('admin.bookings.all') }}?type=hotel" class="sb-kid {{ request()->routeIs('admin.bookings.all') && request('type') === 'hotel' ? 'sb-kid-on' : '' }}"><i class="bi bi-calendar-check"></i> Hotel bookings</a>
                </div>
            </div>
        @endif

        {{-- Messages --}}
        @php $newMessages = \App\Models\ContactMessage::where('status', 'new')->count(); @endphp
        <a href="{{ route('admin.contact-messages.index') }}"
           class="sb-row {{ request()->routeIs('admin.contact-messages.*') ? 'sb-on' : '' }}">
            <i class="bi bi-envelope sb-ico"></i>
            <span>Messages</span>
            @if($newMessages > 0)
                <div class="sb-right">
                    <span class="sb-pill sb-pill-warn">{{ $newMessages }}</span>
                </div>
            @endif
        </a>

        {{-- Currencies --}}
        <a href="{{ route('admin.currencies.index') }}"
           class="sb-row {{ request()->routeIs('admin.currencies*') ? 'sb-on' : '' }}">
            <i class="bi bi-currency-exchange sb-ico"></i>
            <span>Currencies</span>
        </a>

        {{-- Content --}}
        <div class="sb-group {{ request()->routeIs('admin.content*') || request()->routeIs('admin.pages*') || request()->routeIs('admin.menus*') ? 'sb-group-open' : '' }}">
            <button class="sb-row {{ request()->routeIs('admin.content*') || request()->routeIs('admin.pages*') || request()->routeIs('admin.menus*') ? 'sb-on' : '' }}"
                    onclick="toggleSbGroup(this)" type="button">
                <i class="bi bi-file-earmark-text sb-ico"></i>
                <span>Content</span>
                <div class="sb-right"><i class="bi bi-chevron-right sb-chev"></i></div>
            </button>
            <div class="sb-kids">
                <a href="{{ route('admin.pages.index') }}" class="sb-kid {{ request()->routeIs('admin.pages*') ? 'sb-kid-on' : '' }}"><i class="bi bi-file-earmark-richtext"></i> Pages</a>
                <a href="{{ route('admin.content.blog.index') }}" class="sb-kid {{ request()->routeIs('admin.content.blog*') ? 'sb-kid-on' : '' }}"><i class="bi bi-journal-text"></i> Blog</a>
                <a href="{{ route('admin.menus.index') }}" class="sb-kid {{ request()->routeIs('admin.menus*') ? 'sb-kid-on' : '' }}"><i class="bi bi-menu-app"></i> Menus</a>
                <a href="{{ route('admin.content.newsletter.subscribers') }}" class="sb-kid {{ request()->routeIs('admin.content.newsletter.subscribers*') ? 'sb-kid-on' : '' }}"><i class="bi bi-people-fill"></i> Newsletter</a>
                <a href="{{ route('admin.settings.languages.index') }}" class="sb-kid {{ request()->routeIs('admin.settings.languages*') ? 'sb-kid-on' : '' }}"><i class="bi bi-globe"></i> Languages</a>
                <a href="{{ route('admin.content.translations.index') }}" class="sb-kid {{ request()->routeIs('admin.content.translations*') ? 'sb-kid-on' : '' }}"><i class="bi bi-translate"></i> Translations</a>
            </div>
        </div>

        {{-- Settings --}}
        <div class="sb-group {{ request()->routeIs('admin.settings*') ? 'sb-group-open' : '' }}">
            <button class="sb-row {{ request()->routeIs('admin.settings*') ? 'sb-on' : '' }}"
                    onclick="toggleSbGroup(this)" type="button">
                <i class="bi bi-gear sb-ico"></i>
                <span>Settings</span>
                <div class="sb-right"><i class="bi bi-chevron-right sb-chev"></i></div>
            </button>
            <div class="sb-kids">
                <a href="{{ route('admin.settings.website') }}" class="sb-kid {{ request()->routeIs('admin.settings.website*') ? 'sb-kid-on' : '' }}"><i class="bi bi-globe"></i> Website</a>
                <a href="{{ route('admin.settings.email') }}" class="sb-kid {{ request()->routeIs('admin.settings.email*') ? 'sb-kid-on' : '' }}"><i class="bi bi-envelope-at"></i> Email</a>
                <a href="{{ route('admin.settings.payment') }}" class="sb-kid {{ request()->routeIs('admin.settings.payment*') ? 'sb-kid-on' : '' }}"><i class="bi bi-wallet2"></i> Payment</a>
                <a href="{{ route('admin.settings.languages.index') }}" class="sb-kid {{ request()->routeIs('admin.settings.languages*') ? 'sb-kid-on' : '' }}"><i class="bi bi-translate"></i> Languages</a>
                <a href="{{ route('admin.settings.theme') }}" class="sb-kid {{ request()->routeIs('admin.settings.theme*') ? 'sb-kid-on' : '' }}"><i class="bi bi-palette"></i> Theme</a>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="sb-sep"></div>
        <span class="sb-section-label">Quick actions</span>

        <a href="{{ route('admin.pages.create') }}" class="sb-row sb-quick">
            <i class="bi bi-plus-circle sb-ico"></i><span>Add new page</span>
        </a>
        <a href="{{ route('admin.content.blog.create') }}" class="sb-row sb-quick">
            <i class="bi bi-pencil-square sb-ico"></i><span>Write new post</span>
        </a>
        <a href="{{ route('admin.menus.create') }}" class="sb-row sb-quick">
            <i class="bi bi-menu-button-wide sb-ico"></i><span>Add menu item</span>
        </a>

    </nav>
</div>

<script>
    function toggleSbGroup(btn) {
        btn.closest('.sb-group').classList.toggle('sb-group-open');
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.sidebar .sb-row, .sidebar .sb-kid').forEach(function (el) {
            el.addEventListener('click', function () {
                if (window.innerWidth < 992) {
                    const sidebar = document.querySelector('.sidebar');
                    const overlay = document.querySelector('.sidebar-overlay');
                    if (sidebar && sidebar.classList.contains('open')) {
                        sidebar.classList.remove('open');
                        if (overlay) overlay.classList.remove('active');
                    }
                }
            });
        });
    });
</script>
