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

<style>
    /* Shell */
    .sidebar {
        width: 234px;
        height: 100vh;
        background: var(--bs-body-bg);
        border-right: 1px solid var(--bs-border-color);
        display: flex;
        flex-direction: column;
        position: fixed;
        top: 0; left: 0;
        z-index: 200;
        overflow: hidden;
    }

    /* Header */
    .sidebar-header {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 0 14px;
        height: 58px;
        border-bottom: 1px solid var(--bs-border-color);
        flex-shrink: 0;
    }

    .sidebar-toggle {
        width: 32px; height: 32px;
        background: var(--bs-secondary-bg);
        border: 1px solid var(--bs-border-color);
        border-radius: 7px;
        display: flex; align-items: center; justify-content: center;
        font-size: 17px; color: var(--bs-body-color);
        cursor: pointer; transition: background .12s;
    }
    .sidebar-toggle:hover { background: var(--bs-tertiary-bg); }

    .sidebar-brand {
        display: flex; align-items: center;
        text-decoration: none; color: inherit;
    }
    .sidebar-logo { height: 40px; width: auto; object-fit: contain; }
    .sidebar-logo-icon {
        width: 30px; height: 30px;
        border-radius: 8px;
        background: #E3F0FF; color: #0C6DFD;
        display: flex; align-items: center; justify-content: center;
        font-size: 16px;
    }
    [data-bs-theme="dark"] .sidebar-logo-icon { background: #0a2a4d; color: #6ba8ff; }

    /* View Website */
    .sb-view-web {
        padding: 8px 10px;
        border-bottom: 1px solid var(--bs-border-color);
        flex-shrink: 0;
    }
    .sb-web-btn {
        display: flex; align-items: center; gap: 7px;
        padding: 7px 10px;
        border: 1px solid #247BFD;
        border-radius: 8px;
        font-size: 12px; font-weight: 500;
        background: #247BFD;
        color: #fff;
        text-decoration: none;
        transition: opacity .15s;
    }
    .sb-web-btn:hover {
        opacity: .9;
        color: #fff;
        text-decoration: none;
    }
    .sb-web-btn span { flex: 1; }
    .sb-web-btn i { font-size: 13px; }

    /* Nav */
    .sidebar-nav {
        flex: 1; overflow-y: auto;
        padding: 8px 8px 20px;
        scrollbar-width: thin;
        scrollbar-color: var(--bs-border-color) transparent;
    }
    .sidebar-nav::-webkit-scrollbar { width: 3px; }
    .sidebar-nav::-webkit-scrollbar-thumb { background: var(--bs-border-color); border-radius: 4px; }

    /* Universal row — links, group buttons */
    .sb-row {
        display: flex; align-items: center; gap: 8px;
        padding: 7px 8px;
        font-size: 12px; font-weight: 500;
        color: var(--bs-secondary-color);
        border-radius: 8px;
        text-decoration: none;
        transition: background .12s, color .12s;
        margin-bottom: 1px; white-space: nowrap;
        width: 100%; background: transparent; border: none;
        cursor: pointer; text-align: left; line-height: 1.3;
    }
    .sb-row .sb-ico {
        font-size: 15px; width: 16px; text-align: center;
        flex-shrink: 0; color: var(--bs-body-color);
    }
    .sb-row:hover, .sb-row:focus-visible {
        background: var(--bs-secondary-bg);
        color: var(--bs-body-color);
        text-decoration: none;
    }

    /* Active state */
    .sb-row.sb-on { background: #E3F0FF; color: #0C6DFD; }
    .sb-row.sb-on .sb-ico { color: #0C6DFD; }
    [data-bs-theme="dark"] .sb-row.sb-on { background: #0a2a4d; color: #6ba8ff; }
    [data-bs-theme="dark"] .sb-row.sb-on .sb-ico { color: #6ba8ff; }

    /* Right slot */
    .sb-right { margin-left: auto; display: flex; align-items: center; gap: 4px; }
    .sb-chev {
        font-size: 10px; color: var(--bs-secondary-color);
        transition: transform .2s; flex-shrink: 0;
    }
    .sb-group-open > .sb-row .sb-chev { transform: rotate(90deg); }

    /* Badges */
    .sb-pill {
        display: inline-flex; align-items: center; justify-content: center;
        min-width: 16px; height: 16px; padding: 0 5px;
        border-radius: 20px; font-size: 10px; font-weight: 600;
        background: #E3F0FF; color: #0C6DFD;
    }
    .sb-pill-warn { background: #FAEEDA; color: #633806; }
    [data-bs-theme="dark"] .sb-pill { background: #0a2a4d; color: #6ba8ff; }
    [data-bs-theme="dark"] .sb-pill-warn { background: #2e1e05; color: #f0b054; }

    /* Children */
    .sb-group { margin-bottom: 1px; }
    .sb-kids { display: none; padding: 2px 0 4px 24px; }
    .sb-group-open > .sb-kids { display: block; }

    .sb-kid {
        display: flex; align-items: center; gap: 7px;
        padding: 6px 8px;
        font-size: 12px; font-weight: 400;
        color: var(--bs-secondary-color);
        border-radius: 7px; text-decoration: none;
        transition: background .12s, color .12s;
        margin-bottom: 1px;
    }
    .sb-kid i { font-size: 13px; color: var(--bs-body-color); width: 13px; text-align: center; flex-shrink: 0; }
    .sb-kid:hover { background: var(--bs-secondary-bg); color: var(--bs-body-color); text-decoration: none; }

    .sb-kid.sb-kid-on { color: #0C6DFD; background: #E3F0FF; font-weight: 500; }
    .sb-kid.sb-kid-on i { color: #0C6DFD; }
    [data-bs-theme="dark"] .sb-kid.sb-kid-on { background: #0a2a4d; color: #6ba8ff; }
    [data-bs-theme="dark"] .sb-kid.sb-kid-on i { color: #6ba8ff; }

    /* Separator + section label */
    .sb-sep { height: 1px; background: var(--bs-border-color); margin: 6px 4px; }
    .sb-section-label {
        display: block;
        font-size: 10px; font-weight: 600;
        color: var(--bs-secondary-color);
        text-transform: uppercase; letter-spacing: .07em;
        padding: 8px 8px 4px;
    }

    /* Quick action rows — slightly dimmer icon */
    .sb-quick .sb-ico { font-size: 14px; }
</style>

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
