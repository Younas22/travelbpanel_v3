
<!-- NAVIGATION BAR -->
<nav class="nav-bar sticky top-0 z-50">
    @php
        $currencies = allCurrencies();
        $active_currency = activeCurrency();
        $businessModel   = getSetting('business_model', 'system', 'both');
        $showUserSignup  = in_array($businessModel, ['both', 'b2c']);
        $showAgentSignup = in_array($businessModel, ['both', 'b2b']);

        // Module links — driven directly from module status (not menu_items table)
        $navModules = \App\Models\Module::active()->orderBy('sort_order')->get();

        // Logged-in agents only see the modules admin has allowed for them
        // (AgentPermission module.* keys) — guests, customers and admins
        // keep seeing every active module, unfiltered.
        if (auth()->check() && auth()->user()->isAgent()) {
            $navModulePermissionMap = [
                'hotel'  => 'module.hotels',
                'flight' => 'module.flights',
                'tours'  => 'module.tours',
                'umrah'  => 'module.umrah',
                'visa'   => 'module.visa',
            ];
            $navModules = $navModules->filter(
                fn($mod) => auth()->user()->hasPermission($navModulePermissionMap[$mod->slug] ?? 'module.' . $mod->slug)
            );
        }
        $navModIconMap = [
            'hotel'  => 'fas fa-hotel',
            'flight' => 'fas fa-plane',
            'visa'   => 'fas fa-passport',
            'tours'  => 'fas fa-suitcase',
            'umrah'  => 'fas fa-mosque',
        ];
        $navModUrlMap = [
            'hotel'  => '/hotels',
            'flight' => '/flights',
            'visa'   => '/visa',
            'tours'  => '/tours',
            'umrah'  => '/umrah',
        ];
        // Resolve each module's link and active state once, for both menus.
        $navModules = $navModules->map(function ($mod) use ($navModUrlMap) {
            $mod->nav_url    = $mod->slug === 'visa' ? route('visa.create') : url($navModUrlMap[$mod->slug] ?? '/' . $mod->slug);
            $mod->nav_active = $mod->slug === 'visa' ? request()->is('visa*') : Request::url() == $mod->nav_url;
            return $mod;
        });
        $navModulesActive = $navModules->contains('nav_active', true);

        // Split static header items: Home comes before modules, rest (Contact) after.
        // Home is left out of the bar — the logo already links home.
        $allHeaderItems  = get_menu_items('header')->reject(fn($i) => $i->name === 'Home');
        $navItemsBefore  = $allHeaderItems->filter(fn($i) => $i->sort_order <= 1);
        $navItemsAfter   = $allHeaderItems->filter(fn($i) => $i->sort_order > 1);

        // About and Blog sit after Services in the bar (and mobile menu).
        $navPageLinks = [
            [url('/page/about'),    'fas fa-circle-info', t('footer.About')],
            [route('blog.index'),   'fas fa-newspaper',   t('footer.Blog')],
        ];

        // Flag for a currency option: mapped currencies via getFlagClass, others
        // via the ISO 4217 rule that the first two letters are the country (KWD → kw).
        $navCurrencyFlag = function ($code) {
            $flag = getFlagClass($code);
            return ($flag === 'fi fi-' . strtolower($code) && strlen($code) === 3)
                ? 'fi fi-' . strtolower(substr($code, 0, 2))
                : $flag;
        };

        // Avatar + label for the logged-in account button (same rules as before).
        if (auth()->check()) {
            $navUser = auth()->user();
            $navAvatar = $navUser->profile_image
                ? ($navUser->isAdmin() ? url('public/' . $navUser->profile_image) : ($navUser->isAgent() ? $navUser->profile_image_url : url('public/assets/images/avatars/' . $navUser->profile_image)))
                : null;
            $navAvatarIsLogo = false;
            if (! $navAvatar && $navUser->isAgent() && $navUser->company_logo) {
                $navAvatar = url('public/assets/images/settings/branding/' . $navUser->company_logo);
                $navAvatarIsLogo = true;
            }
            $navUserLabel = $navUser->isAdmin() ? $navUser->first_name : ($navUser->isAgent() ? 'Travel Agent' : $navUser->first_name);
        }
    @endphp

    <div class="nav-shell max-w-7xl mx-auto flex items-center justify-between gap-4 xl:gap-6">

        <!-- Logo & Name -->
        <a class="navbar-brand flex items-center gap-2 shrink-0" href="{{ url('/') }}">
            @if(auth()->check() && auth()->user()->isAgent() && auth()->user()->company_logo)
                <img src="{{ url('public/assets/images/settings/branding/' . auth()->user()->company_logo) }}"
                    alt="{{ auth()->user()->company_name ?? 'Company Logo' }}"
                    class="nav-logo">
                @if(auth()->user()->company_name)
                    <span class="nav-brand-name">{{ auth()->user()->company_name }}</span>
                @endif
            @else
                <img src="{{ getSettingImage('business_logo','branding') }}"
                    alt="TravelBookingPanel Logo"
                    class="nav-logo">
                @if(showBusinessNameWithLogo())
                    <span class="nav-brand-name">{{ getSetting('business_name', 'main', 'TravelBookingPanel') }}</span>
                @endif
            @endif
        </a>

        <!-- Desktop Menu -->
        <div class="hidden lg:flex items-center gap-1 flex-1 justify-center">
            {{-- Home (and any items before modules) --}}
            @foreach ($navItemsBefore as $item)
                <a href="{{ $item->full_url }}" class="nav-item {{ Request::url() == $item->full_url ? 'active' : '' }}">
                    <span>{{ t('header.' . $item->name) }}</span>
                </a>
            @endforeach

            {{-- Active modules, grouped under one Services dropdown --}}
            @if($navModules->isNotEmpty())
                <div class="relative group nav-dd">
                    <button type="button" class="nav-item {{ $navModulesActive ? 'active' : '' }}" aria-haspopup="true">
                        <i class="fas fa-table-cells-large nav-ico"></i>
                        <span>{{ t('footer.our_services') }}</span>
                        <i class="fas fa-chevron-down nav-ico nav-chevron"></i>
                    </button>
                    <div class="absolute left-1/2 -translate-x-1/2 top-full pt-3 w-52 z-50 hidden group-hover:block group-focus-within:block">
                        <div class="nav-dd-panel">
                            @foreach ($navModules as $mod)
                                <a href="{{ $mod->nav_url }}" class="nav-dd-item {{ $mod->nav_active ? 'active' : '' }}">
                                    <span class="nav-dd-icon"><i class="{{ $navModIconMap[$mod->slug] ?? 'fas fa-circle' }}"></i></span>
                                    <span>{{ t('header.' . $mod->name) }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            {{-- About and Blog (same pages as the footer's quick links) --}}
            @foreach ($navPageLinks as [$linkUrl, $linkIcon, $linkLabel])
                <a href="{{ $linkUrl }}" class="nav-item {{ Request::url() == $linkUrl ? 'active' : '' }}">
                    <i class="{{ $linkIcon }} nav-ico"></i>
                    <span>{{ $linkLabel }}</span>
                </a>
            @endforeach
        </div>

        <!-- Desktop: Language + Currency + Auth Buttons -->
        <div class="hidden lg:flex gap-3 items-center shrink-0">

            <!-- Language + Currency: two round pills, each led by a round flag -->
            <div class="nav-util-pill-group">
                <div class="nav-util-pill-item relative">
                    <select id="language-select" class="language-select">
                        @if(isset($activeLanguages))
                            @foreach($activeLanguages as $language)
                                <option value="{{ $language->code }}" data-flag="{{ getFlagClass($language->code) }}"
                                        {{ app()->getLocale() == $language->code ? 'selected' : '' }}>
                                    {{ $language->name }}
                                </option>
                            @endforeach
                        @else
                            <option value="en" data-flag="{{ getFlagClass('en') }}" {{ app()->getLocale() == 'en' ? 'selected' : '' }}>English</option>
                            <option value="nl" data-flag="{{ getFlagClass('nl') }}" {{ app()->getLocale() == 'nl' ? 'selected' : '' }}>Dutch</option>
                        @endif
                    </select>
                </div>
                <div class="nav-util-pill-item relative">
                    <select id="currency-select" class="currency-select">
                        @foreach($currencies as $currency)
                            <option value="{{ $currency->currency_name }}" data-flag="{{ $navCurrencyFlag($currency->currency_name) }}"
                                {{ $active_currency->currency_name == $currency->currency_name ? 'selected' : '' }}>
                                {{ $currency->currency_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Support (Contact) and any remaining items, after currency --}}
            @foreach ($navItemsAfter as $item)
                <a href="{{ $item->full_url }}" class="nav-util-link {{ Request::url() == $item->full_url ? 'active' : '' }}">
                    @if($item->icon)<i class="{{ $item->icon }}"></i>@endif
                    <span>{{ t('header.' . $item->name) }}</span>
                </a>
            @endforeach

            <span class="nav-sep" aria-hidden="true"></span>

            @if(auth()->check())
                <!-- Logged-in: Profile Dropdown (hover) -->
                <div class="relative group" id="profile-dropdown-wrapper-desktop">
                    <button type="button" class="nav-account-btn">
                        @if($navAvatar)
                            <img src="{{ $navAvatar }}" alt="" class="nav-avatar {{ $navAvatarIsLogo ? 'nav-avatar-logo' : '' }}">
                        @else
                            <span class="nav-avatar">{{ $navUser->initials }}</span>
                        @endif
                        <span>{{ $navUserLabel }}</span>
                        <i class="fas fa-chevron-down text-xs"></i>
                    </button>
                    <div class="nav-dd-under absolute left-1/2 -translate-x-1/2 top-full pt-2 w-56 z-50 hidden group-hover:block group-focus-within:block">
                        <div class="nav-dd-panel">
                            @if($navUser->isAdmin())
                                <a href="{{ route('admin.dashboard.index') }}" class="nav-menu-link"><i class="fas fa-gauge-high"></i> Dashboard</a>
                                <a href="{{ route('admin.profile.index') }}" class="nav-menu-link"><i class="fas fa-user"></i> Profile</a>
                                <a href="{{ route('admin.bookings.all', ['type' => 'hotel']) }}" class="nav-menu-link"><i class="fas fa-clock-rotate-left"></i> Recent Booking</a>
                            @elseif($navUser->isAgent())
                                <a href="{{ route('agent.dashboard') }}" class="nav-menu-link"><i class="fas fa-gauge-high"></i> Dashboard</a>
                                <a href="{{ route('agent.profile.index') }}" class="nav-menu-link"><i class="fas fa-user"></i> Profile</a>
                                <a href="{{ route('agent.bookings.index') }}" class="nav-menu-link"><i class="fas fa-clock-rotate-left"></i> Recent Booking</a>
                            @else
                                <a href="{{ route('user.dashboard') }}" class="nav-menu-link"><i class="fas fa-gauge-high"></i> Dashboard</a>
                                <a href="{{ route('user.profile.index') }}" class="nav-menu-link"><i class="fas fa-user"></i> Profile</a>
                                <a href="{{ route('user.bookings.index') }}" class="nav-menu-link"><i class="fas fa-clock-rotate-left"></i> Recent Booking</a>
                            @endif
                            <hr class="nav-menu-sep">
                            <form method="POST" action="{{ $navUser->isAdmin() ? route('admin.logout') : ($navUser->isAgent() ? route('agent.logout') : route('user.logout')) }}">
                                @csrf
                                <button type="submit" class="nav-menu-link nav-menu-link-danger"><i class="fas fa-right-from-bracket"></i> Logout</button>
                            </form>
                        </div>
                    </div>
                </div>
            @else
                <!-- Login Button -->
                <a href="{{ route('login') }}" class="btn-signin-pill">
                    <i class="fas fa-right-to-bracket"></i>
                    <span>{{ t('header.Sign In') }}</span>
                </a>

                @if($showUserSignup && $showAgentSignup)
                <!-- Signup Dropdown (hover) — both B2B + B2C -->
                <div class="relative group" id="signup-dropdown-wrapper-desktop">
                    <button type="button" class="btn-signup-solid">
                        <i class="fas fa-user-plus"></i>
                        <span>Sign Up</span>
                        <i class="fas fa-chevron-down text-xs"></i>
                    </button>
                    <div class="nav-dd-under absolute left-1/2 -translate-x-1/2 top-full pt-2 w-72 z-50 hidden group-hover:block group-focus-within:block">
                        <div class="nav-dd-panel">
                            <a href="{{ route('user.register') }}" class="nav-dd-item nav-signup-item">
                                <span class="nav-dd-icon"><i class="fas fa-user"></i></span>
                                <span><b>Customer Signup</b><small>Book trips for yourself and family</small></span>
                            </a>
                            <a href="{{ agentSignupUrl() }}" class="nav-dd-item nav-signup-item">
                                <span class="nav-dd-icon"><i class="fas fa-user-tie"></i></span>
                                <span><b>Agent Signup</b><small>Sell travel and earn commission</small></span>
                            </a>
                        </div>
                    </div>
                </div>
                @elseif($showUserSignup)
                <!-- B2C Only — direct Customer Signup button -->
                <a href="{{ route('user.register') }}" class="btn-signup-solid">
                    <i class="fas fa-user"></i>
                    <span>Sign Up</span>
                </a>
                @elseif($showAgentSignup)
                <!-- B2B Only — direct Agent Signup button -->
                <a href="{{ agentSignupUrl() }}" class="btn-signup-solid">
                    <i class="fas fa-user-tie"></i>
                    <span>Agent Sign Up</span>
                </a>
                @endif
            @endif

        </div>


        <!-- Mobile / Tablet: hamburger -->
        <div class="flex items-center gap-3 lg:hidden">
            <button type="button" class="hamburger flex flex-col gap-1.5 cursor-pointer" onclick="toggleMobileMenu()" aria-label="Menu">
                <span class="line w-6 h-[3px] bg-black rounded transition-all duration-300"></span>
                <span class="line w-6 h-[3px] bg-black rounded transition-all duration-300"></span>
                <span class="line w-6 h-[3px] bg-black rounded transition-all duration-300"></span>
            </button>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div class="mobile-menu lg:hidden">
        <div class="nav-mobile-card max-w-7xl mx-auto">

        {{-- Home (before modules) --}}
        @foreach ($navItemsBefore as $item)
            <a href="{{ $item->full_url }}" class="nav-m-link {{ Request::url() == $item->full_url ? 'active' : '' }}">
                <i class="{{ $item->icon }}"></i>
                <span>{{ t('header.' . $item->name) }}</span>
            </a>
        @endforeach

        {{-- Active modules --}}
        @if($navModules->isNotEmpty())
            <p class="nav-m-label">{{ t('footer.our_services') }}</p>
            @foreach ($navModules as $mod)
                <a href="{{ $mod->nav_url }}" class="nav-m-link {{ $mod->nav_active ? 'active' : '' }}">
                    <i class="{{ $navModIconMap[$mod->slug] ?? 'fas fa-circle' }}"></i>
                    <span>{{ t('header.' . $mod->name) }}</span>
                </a>
            @endforeach
        @endif

        @foreach ($navPageLinks as [$linkUrl, $linkIcon, $linkLabel])
            <a href="{{ $linkUrl }}" class="nav-m-link {{ Request::url() == $linkUrl ? 'active' : '' }}">
                <i class="{{ $linkIcon }}"></i>
                <span>{{ $linkLabel }}</span>
            </a>
        @endforeach

        {{-- Contact and remaining items --}}
        @foreach ($navItemsAfter as $item)
            <a href="{{ $item->full_url }}" class="nav-m-link {{ Request::url() == $item->full_url ? 'active' : '' }}">
                <i class="{{ $item->icon }}"></i>
                <span>{{ t('header.' . $item->name) }}</span>
            </a>
        @endforeach

        <hr class="nav-menu-sep">

        <!-- Mobile: Currency + Language + Auth -->
        <div class="grid grid-cols-2 gap-2">

            <!-- Currency Dropdown (Mobile) -->
            <div class="relative w-full">
                <select id="currency-select-mobile" class="currency-select w-full">
                    @foreach($currencies as $currency)
                        <option value="{{ $currency->currency_name }}" data-flag="{{ $navCurrencyFlag($currency->currency_name) }}"
                            {{ $active_currency->currency_name == $currency->currency_name ? 'selected' : '' }}>
                            {{ $currency->currency_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Language Dropdown (Mobile) -->
            <div class="relative w-full">
                <select id="language-select-mobile" class="language-select w-full">
                    @if(isset($activeLanguages))
                        @foreach($activeLanguages as $language)
                            <option value="{{ $language->code }}" data-flag="{{ getFlagClass($language->code) }}"
                                    {{ app()->getLocale() == $language->code ? 'selected' : '' }}>
                                {{ $language->name }}
                            </option>
                        @endforeach
                    @else
                        <option value="en" data-flag="{{ getFlagClass('en') }}" {{ app()->getLocale() == 'en' ? 'selected' : '' }}>English</option>
                        <option value="nl" data-flag="{{ getFlagClass('nl') }}" {{ app()->getLocale() == 'nl' ? 'selected' : '' }}>Dutch</option>
                    @endif
                </select>
            </div>
        </div>

        @if(auth()->check())
            <!-- Logged-in: Profile info + links (Mobile) -->
            <div class="nav-m-user">
                @if($navAvatar)
                    <img src="{{ $navAvatar }}" alt="" class="nav-avatar nav-avatar-lg {{ $navAvatarIsLogo ? 'nav-avatar-logo' : '' }}">
                @else
                    <span class="nav-avatar nav-avatar-lg">{{ $navUser->initials }}</span>
                @endif
                <div>
                    <p class="nav-m-user-name">{{ $navUserLabel }}</p>
                    <p class="nav-m-user-email">{{ $navUser->email }}</p>
                </div>
            </div>
            @if($navUser->isAdmin())
                <a href="{{ route('admin.dashboard') }}" class="nav-m-link"><i class="fas fa-gauge-high"></i> Dashboard</a>
                <a href="{{ route('admin.profile.index') }}" class="nav-m-link"><i class="fas fa-user"></i> Profile</a>
                <a href="{{ route('admin.bookings.all', ['type' => 'hotel']) }}" class="nav-m-link"><i class="fas fa-clock-rotate-left"></i> Recent Booking</a>
            @elseif($navUser->isAgent())
                <a href="{{ route('agent.dashboard') }}" class="nav-m-link"><i class="fas fa-gauge-high"></i> Dashboard</a>
                <a href="{{ route('agent.profile.index') }}" class="nav-m-link"><i class="fas fa-user"></i> Profile</a>
                <a href="{{ route('agent.bookings.index') }}" class="nav-m-link"><i class="fas fa-clock-rotate-left"></i> Recent Booking</a>
            @else
                <a href="{{ route('user.dashboard') }}" class="nav-m-link"><i class="fas fa-gauge-high"></i> Dashboard</a>
                <a href="{{ route('user.profile.index') }}" class="nav-m-link"><i class="fas fa-user"></i> Profile</a>
                <a href="{{ route('user.bookings.index') }}" class="nav-m-link"><i class="fas fa-clock-rotate-left"></i> Recent Booking</a>
            @endif
            <form method="POST" action="{{ $navUser->isAdmin() ? route('admin.logout') : ($navUser->isAgent() ? route('agent.logout') : route('user.logout')) }}">
                @csrf
                <button type="submit" class="nav-m-link nav-menu-link-danger w-full"><i class="fas fa-right-from-bracket"></i> Logout</button>
            </form>
        @else
            <div class="flex flex-col gap-2 mt-3">
                <!-- Login Button (Mobile) -->
                <a href="{{ route('login') }}" class="btn-signin-pill w-full">
                    <i class="fas fa-right-to-bracket"></i>
                    <span>Login</span>
                </a>

                @if($showUserSignup)
                <!-- Customer Signup (Mobile) -->
                <a href="{{ route('user.register') }}" class="btn-signup-solid w-full">
                    <i class="fas fa-user"></i>
                    <span>Customer Signup</span>
                </a>
                @endif

                @if($showAgentSignup)
                <!-- Agent Signup (Mobile) -->
                <a href="{{ agentSignupUrl() }}" class="{{ $showUserSignup ? 'btn-signin-pill' : 'btn-signup-solid' }} w-full">
                    <i class="fas fa-user-tie"></i>
                    <span>Agent Signup</span>
                </a>
                @endif
            </div>
        @endif

        </div>
    </div>

    <script>
        // Wait for jQuery and Select2 to load (they're in footer)
        document.addEventListener('DOMContentLoaded', function() {
            // Wait a bit for jQuery and Select2 to be available
            setTimeout(function() {
                if (typeof jQuery !== 'undefined' && jQuery.fn.select2) {
                    initializeFlagSelects();

                    // Hover to open Select2 (Currency & Language)
                    var $selects = $('#currency-select, #language-select');

                    $selects.each(function() {
                        var $el = $(this);
                        $el.closest('.relative').on('mouseenter', function() {
                            // Close all other selects first
                            $selects.not($el).select2('close');
                            $el.select2('open');
                        }).on('mouseleave', function() {
                            setTimeout(function() {
                                // Check if mouse is over this select2 dropdown
                                var $drop = $el.data('select2').$dropdown;
                                if (!$drop || !$drop.is(':hover')) {
                                    $el.select2('close');
                                }
                            }, 150);
                        });

                        // Also close when mouse leaves the Select2 dropdown itself
                        $el.on('select2:open', function() {
                            var $drop = $el.data('select2').$dropdown;
                            if ($drop) {
                                $drop.on('mouseleave', function() {
                                    $el.select2('close');
                                });
                            }
                        });
                    });
                }
            }, 100);
        });

        function initializeFlagSelects() {
            // Custom template for Select2 with flags
            function formatState(state) {
                if (!state.id) return state.text;

                const flagClass = $(state.element).data('flag');
                if (!flagClass) return state.text;

                // Check if RTL mode is active
                const isRTL = document.documentElement.dir === 'rtl';
                const marginStyle = isRTL ? 'margin-left: 8px;' : 'margin-right: 8px;';

                return $('<span style="display: inline-flex; align-items: center; font-size: 11px; white-space: nowrap;"><span class="' + flagClass + '" style="' + marginStyle + ' flex-shrink: 0;"></span>' + state.text + '</span>');
            }

            // Initialize currency selects with Select2
            $('#currency-select, #currency-select-mobile').each(function() {
                $(this).select2({
                    templateResult: formatState,
                    templateSelection: formatState,
                    minimumResultsForSearch: Infinity,
                    width: '100%',
                    // the desktop pills are small; let the open list fit its option names
                    dropdownAutoWidth: true
                });
            });

            // Desktop pill shows just the language code (EN, AR…); the open list keeps full names
            function formatCode(state) {
                if (!state.id) return state.text;
                const flagClass = $(state.element).data('flag');
                const isRTL = document.documentElement.dir === 'rtl';
                const marginStyle = isRTL ? 'margin-left: 6px;' : 'margin-right: 6px;';
                return $('<span style="display: inline-flex; align-items: center; white-space: nowrap;"><span class="' + (flagClass || '') + '" style="' + marginStyle + ' flex-shrink: 0;"></span>' + String(state.id).toUpperCase() + '</span>');
            }

            // Initialize language selects with Select2
            $('#language-select, #language-select-mobile').each(function() {
                $(this).select2({
                    templateResult: formatState,
                    templateSelection: this.id === 'language-select' ? formatCode : formatState,
                    minimumResultsForSearch: Infinity,
                    width: '100%',
                    // the desktop pills are small; let the open list fit its option names
                    dropdownAutoWidth: true
                });
            });

            // Currency change handler - attached AFTER Select2 initialization
            $('#currency-select, #currency-select-mobile').on('select2:select', function(e) {
                const currencyCode = e.params.data.id;
                console.log('Currency changed to:', currencyCode);
                handleCurrencyChange(currencyCode);
            });

            // Language change handler - attached AFTER Select2 initialization
            $('#language-select, #language-select-mobile').on('select2:select', function(e) {
                const langCode = e.params.data.id;
                console.log('Language changed to:', langCode);
                changeLanguage(langCode);
            });
        }

        function toggleMobileMenu() {
            const menu = document.querySelector('.mobile-menu');
            const hamburger = document.querySelector('.hamburger');
            if (menu) menu.classList.toggle('active');
            if (hamburger) hamburger.classList.toggle('active');
        }

        // Elevate the header with a shadow once the page scrolls past the hero.
        (function() {
            const navBar = document.querySelector('.nav-bar');
            if (!navBar) return;
            const toggle = () => navBar.classList.toggle('is-scrolled', window.scrollY > 12);
            toggle();
            window.addEventListener('scroll', toggle, { passive: true });
        })();

        // Currency change helper function
        function handleCurrencyChange(currencyCode) {
            fetch('<?= url('/set-currency') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '<?= csrf_token() ?>'
                },
                body: JSON.stringify({ code: currencyCode })
            })
            .then(response => response.json())
            .then(data => {
                if(data.success){
                    window.location.reload();
                }
            })
            .catch(error => console.error('Currency change error:', error));
        }

        // Language change helper function
        function changeLanguage(langCode) {
            console.log('Changing language to:', langCode);

            // Show page loader
            const loader = document.getElementById('pageLoader');
            if (loader) {
                loader.classList.remove('hidden');
            }

            // Construct URL with lang parameter
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('lang', langCode);
            const newUrl = currentUrl.toString();

            console.log('Redirecting to:', newUrl);
            window.location.href = newUrl;
        }
    </script>
</nav>
