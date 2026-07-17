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
        // Split static header items: Home comes before modules, rest (Contact) after
        $allHeaderItems  = get_menu_items('header');
        $navItemsBefore  = $allHeaderItems->filter(fn($i) => $i->sort_order <= 1);
        $navItemsAfter   = $allHeaderItems->filter(fn($i) => $i->sort_order > 1);
    @endphp
    <div class="max-w-7xl mx-auto px-4 py-1 md:py-2 flex items-center justify-between">

        <!-- Logo & Name -->
        <!-- <div class="flex items-center gap-2">
            <i class="fas fa-plane text-blue-600 text-2xl"></i>
            <div class="text-xl md:text-2xl font-bold" style="color: #003580;">Travel</div>
        </div> -->

        <a class="navbar-brand" href="{{ url('/') }}">
            <img src="{{ getSettingImage('business_logo','branding') }}"
                alt="TravelBookingPanel Logo"
                class="img-fluid"
                style="max-height: 45px; height: auto; width: auto;">
        </a>

        <!-- Desktop Menu -->
        <div class="hidden md:flex gap-4 items-center flex-1 justify-center">
            {{-- Home (and any items before modules) --}}
            @foreach ($navItemsBefore as $item)
                <a href="{{ $item->full_url }}" class="nav-item {{ Request::url() == $item->full_url ? 'active' : '' }}">
                    <i class="{{ $item->icon }}"></i>
                    <span>{{ t('header.' . $item->name) }}</span>
                </a>
            @endforeach
            {{-- Active modules in sort_order --}}
            @foreach ($navModules as $mod)
                @php $modUrl = url($navModUrlMap[$mod->slug] ?? '/'.$mod->slug); @endphp
                @if($mod->slug === 'visa')
                    <a href="{{ route('visa.create') }}" class="nav-item {{ request()->is('visa*') ? 'active' : '' }}">
                        <i class="{{ $navModIconMap[$mod->slug] ?? 'fas fa-circle' }}"></i>
                        <span>{{ t('header.' . $mod->name) }}</span>
                    </a>
                @else
                    <a href="{{ $modUrl }}" class="nav-item {{ Request::url() == $modUrl ? 'active' : '' }}">
                        <i class="{{ $navModIconMap[$mod->slug] ?? 'fas fa-circle' }}"></i>
                        <span>{{ t('header.' . $mod->name) }}</span>
                    </a>
                @endif
            @endforeach
            {{-- Contact and any remaining items --}}
            @foreach ($navItemsAfter as $item)
                <a href="{{ $item->full_url }}" class="nav-item {{ Request::url() == $item->full_url ? 'active' : '' }}">
                    <i class="{{ $item->icon }}"></i>
                    <span>{{ t('header.' . $item->name) }}</span>
                </a>
            @endforeach
        </div>

        <!-- Desktop: Currency + Language + Auth Buttons -->
        <div class="hidden md:flex gap-3 items-center">

            <!-- Currency Dropdown -->
            <div class="relative">
                <select id="currency-select" class="currency-select border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white">
                    @foreach($currencies as $currency)
                        <option value="{{ $currency->currency_name }}" data-flag="{{ getFlagClass($currency->currency_name) }}"
                            {{ $active_currency->currency_name == $currency->currency_name ? 'selected' : '' }}>
                            {{ $currency->currency_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Language Dropdown -->
            <div class="relative">
                <select id="language-select" class="language-select border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white">
                    @if(isset($activeLanguages))
                        @foreach($activeLanguages as $language)
                            <option value="{{ $language->code }}"
                                    data-flag="{{ getFlagClass(strtoupper($language->code)) }}"
                                    {{ app()->getLocale() == $language->code ? 'selected' : '' }}>
                                {{ $language->name }}
                            </option>
                        @endforeach
                    @else
                        <option value="en" data-flag="{{ getFlagClass('GB') }}" {{ app()->getLocale() == 'en' ? 'selected' : '' }}>English</option>
                        <option value="nl" data-flag="{{ getFlagClass('NL') }}" {{ app()->getLocale() == 'nl' ? 'selected' : '' }}>Dutch</option>
                    @endif
                </select>
            </div>

            @if(auth()->check())
                <!-- Logged-in: Profile Dropdown (hover) -->
                <div class="relative group" id="profile-dropdown-wrapper-desktop">
                    <button class="flex items-center gap-2 px-3 py-1.5 rounded-lg border border-gray-200 bg-white group-hover:bg-gray-50 transition text-sm font-semibold" style="color:#003580;">
                        @if(auth()->user()->profile_image)
                            <img src="{{ auth()->user()->isAdmin() ? url('public/' . auth()->user()->profile_image) : (auth()->user()->isAgent() ? auth()->user()->profile_image_url : url('public/assets/images/avatars/' . auth()->user()->profile_image)) }}" class="w-7 h-7 rounded-full object-cover border border-gray-200">
                        @elseif(auth()->user()->isAgent() && auth()->user()->company_logo)
                            <img src="{{ url('public/assets/images/settings/branding/' . auth()->user()->company_logo) }}" class="w-7 h-7 rounded-full object-contain border border-gray-200 bg-white">
                        @else
                            <div class="w-7 h-7 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0" style="background:#0077BE;">
                                {{ auth()->user()->initials }}
                            </div>
                        @endif
                        <span>{{ auth()->user()->isAdmin() ? auth()->user()->first_name : (auth()->user()->isAgent() ? 'Travel Agent' : auth()->user()->first_name) }}</span>
                        <i class="fas fa-chevron-down text-xs" style="display:inline !important;"></i>
                    </button>
                    <div class="absolute right-0 mt-0 pt-2 w-52 z-50 hidden group-hover:block">
                    <div class="bg-white rounded-xl shadow-lg border border-gray-100 py-2">
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.dashboard.index') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm hover:bg-blue-50 transition" style="color:#003580;">
                                <i class="fas fa-gauge-high w-4 text-center"></i> Dashboard
                            </a>
                            <a href="{{ route('admin.profile.index') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm hover:bg-blue-50 transition" style="color:#003580;">
                                <i class="fas fa-user w-4 text-center"></i> Profile
                            </a>
                            <a href="{{ route('admin.bookings.all', ['type' => 'hotel']) }}" class="flex items-center gap-2 px-4 py-2.5 text-sm hover:bg-blue-50 transition" style="color:#003580;">
                                <i class="fas fa-clock-rotate-left w-4 text-center"></i> Recent Booking
                            </a>
                        @elseif(auth()->user()->isAgent())
                            <a href="{{ route('agent.dashboard') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm hover:bg-blue-50 transition" style="color:#003580;">
                                <i class="fas fa-gauge-high w-4 text-center"></i> Dashboard
                            </a>
                            <a href="{{ route('agent.profile.index') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm hover:bg-blue-50 transition" style="color:#003580;">
                                <i class="fas fa-user w-4 text-center"></i> Profile
                            </a>
                            <a href="{{ route('agent.bookings.index') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm hover:bg-blue-50 transition" style="color:#003580;">
                                <i class="fas fa-clock-rotate-left w-4 text-center"></i> Recent Booking
                            </a>
                        @else
                            <a href="{{ route('user.dashboard') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm hover:bg-blue-50 transition" style="color:#003580;">
                                <i class="fas fa-gauge-high w-4 text-center"></i> Dashboard
                            </a>
                            <a href="{{ route('user.profile.index') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm hover:bg-blue-50 transition" style="color:#003580;">
                                <i class="fas fa-user w-4 text-center"></i> Profile
                            </a>
                            <a href="{{ route('user.bookings.index') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm hover:bg-blue-50 transition" style="color:#003580;">
                                <i class="fas fa-clock-rotate-left w-4 text-center"></i> Recent Booking
                            </a>
                        @endif
                        <hr class="my-2 border-gray-100">
                        <form method="POST" action="{{ auth()->user()->isAdmin() ? route('admin.logout') : (auth()->user()->isAgent() ? route('agent.logout') : route('user.logout')) }}">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-2 px-4 py-2.5 text-sm hover:bg-red-50 transition text-red-500 cursor-pointer">
                                <i class="fas fa-right-from-bracket w-4 text-center"></i> Logout
                            </button>
                        </form>
                    </div></div>
                </div>
            @else
                <!-- Login Button -->
                <a href="{{ route('login') }}" class="btn-signin px-4 py-2 font-semibold rounded-lg transition duration-300 text-sm flex items-center gap-1.5" style="text-decoration:none;">
                    <i class="fas fa-right-to-bracket"></i>
                    <span>Login</span>
                </a>

                @if($showUserSignup && $showAgentSignup)
                <!-- Signup Dropdown (hover) — both B2B + B2C -->
                <div class="relative group" id="signup-dropdown-wrapper-desktop">
                    <button class="flex items-center gap-1.5 px-4 py-2 font-semibold rounded-lg border-2 transition duration-300 text-sm" style="border-color:#0077BE;color:#0077BE;background:white;">
                        <i class="fas fa-user-plus" style="display:inline !important;"></i>
                        <span>Sign Up</span>
                        <i class="fas fa-chevron-down text-xs" style="display:inline !important;"></i>
                    </button>
                    <div class="absolute right-0 mt-0 pt-2 w-48 z-50 hidden group-hover:block">
                    <div class="bg-white rounded-xl shadow-lg border border-gray-100 py-2">
                        <a href="{{ route('user.register') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm hover:bg-blue-50 transition" style="color:#003580;">
                            <i class="fas fa-user w-4 text-center" style="display:inline !important;"></i> Customer Signup
                        </a>
                        <a href="{{ route('agent.register') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm hover:bg-blue-50 transition" style="color:#003580;">
                            <i class="fas fa-user-tie w-4 text-center" style="display:inline !important;"></i> Agent Signup
                        </a>
                    </div></div>
                </div>
                @elseif($showUserSignup)
                <!-- B2C Only — direct Customer Signup button -->
                <a href="{{ route('user.register') }}" class="flex items-center gap-1.5 px-4 py-2 font-semibold rounded-lg border-2 transition duration-300 text-sm" style="border-color:#0077BE;color:#0077BE;background:white;text-decoration:none;">
                    <i class="fas fa-user" style="display:inline !important;"></i>
                    <span>Sign Up</span>
                </a>
                @elseif($showAgentSignup)
                <!-- B2B Only — direct Agent Signup button -->
                <a href="{{ route('agent.register') }}" class="flex items-center gap-1.5 px-4 py-2 font-semibold rounded-lg border-2 transition duration-300 text-sm" style="border-color:#003580;color:#003580;background:white;text-decoration:none;">
                    <i class="fas fa-user-tie" style="display:inline !important;"></i>
                    <span>Agent Sign Up</span>
                </a>
                @endif
            @endif

        </div>


        <!-- Hamburger Menu (Mobile) -->
        <button class="hamburger md:hidden flex flex-col gap-1.5 cursor-pointer" onclick="toggleMobileMenu()">
            <span class="line w-6 h-[3px] bg-black rounded transition-all duration-300"></span>
            <span class="line w-6 h-[3px] bg-black rounded transition-all duration-300"></span>
            <span class="line w-6 h-[3px] bg-black rounded transition-all duration-300"></span>
        </button>
    </div>

    <!-- Mobile Menu -->
    <div class="mobile-menu md:hidden border-t" style="background-color: #F7F9FC; border-color: #E5E7EB;">
        <div class="px-4 py-4 space-y-2">

        {{-- Home (before modules) --}}
        @foreach ($navItemsBefore as $item)
            <a href="{{ $item->full_url }}" class="block font-medium py-3 px-4 rounded-lg hover:bg-blue-50 transition text-sm flex items-center gap-3 {{ Request::url() == $item->full_url ? 'bg-blue-50' : '' }}" style="color: {{ Request::url() == $item->full_url ? '#0077BE' : '#003580' }};">
                <i class="{{ $item->icon }}"></i>
                <span>{{ t('header.' . $item->name) }}</span>
            </a>
        @endforeach
        {{-- Active modules --}}
        @foreach ($navModules as $mod)
            @php $modUrl = url($navModUrlMap[$mod->slug] ?? '/'.$mod->slug); @endphp
            @if($mod->slug === 'visa')
                <a href="{{ route('visa.create') }}" class="block font-medium py-3 px-4 rounded-lg hover:bg-blue-50 transition text-sm flex items-center gap-3" style="color:#003580;">
                    <i class="{{ $navModIconMap[$mod->slug] ?? 'fas fa-circle' }}"></i>
                    <span>{{ t('header.' . $mod->name) }}</span>
                </a>
            @else
                <a href="{{ $modUrl }}" class="block font-medium py-3 px-4 rounded-lg hover:bg-blue-50 transition text-sm flex items-center gap-3 {{ Request::url() == $modUrl ? 'bg-blue-50' : '' }}" style="color: {{ Request::url() == $modUrl ? '#0077BE' : '#003580' }};">
                    <i class="{{ $navModIconMap[$mod->slug] ?? 'fas fa-circle' }}"></i>
                    <span>{{ t('header.' . $mod->name) }}</span>
                </a>
            @endif
        @endforeach
        {{-- Contact and remaining items --}}
        @foreach ($navItemsAfter as $item)
            <a href="{{ $item->full_url }}" class="block font-medium py-3 px-4 rounded-lg hover:bg-blue-50 transition text-sm flex items-center gap-3 {{ Request::url() == $item->full_url ? 'bg-blue-50' : '' }}" style="color: {{ Request::url() == $item->full_url ? '#0077BE' : '#003580' }};">
                <i class="{{ $item->icon }}"></i>
                <span>{{ t('header.' . $item->name) }}</span>
            </a>
        @endforeach

            <hr class="border-gray-300 my-4">
        <!-- Mobile: Currency + Language + Auth -->
        <div class="px-4 py-4 space-y-2">

            <!-- Currency Dropdown (Mobile) -->
            <div class="relative w-full">
                <select id="currency-select-mobile" class="currency-select w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white">
                    @foreach($currencies as $currency)
                        <option value="{{ $currency->currency_name }}" data-flag="{{ getFlagClass($currency->currency_name) }}"
                            {{ $active_currency->currency_name == $currency->currency_name ? 'selected' : '' }}>
                            {{ $currency->currency_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Language Dropdown (Mobile) -->
            <div class="relative w-full">
                <select id="language-select-mobile" class="language-select w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white">
                    @if(isset($activeLanguages))
                        @foreach($activeLanguages as $language)
                            <option value="{{ $language->code }}"
                                    data-flag="{{ getFlagClass(strtoupper($language->code)) }}"
                                    {{ app()->getLocale() == $language->code ? 'selected' : '' }}>
                                {{ $language->name }}
                            </option>
                        @endforeach
                    @else
                        <option value="en" data-flag="{{ getFlagClass('GB') }}" {{ app()->getLocale() == 'en' ? 'selected' : '' }}>English</option>
                        <option value="nl" data-flag="{{ getFlagClass('NL') }}" {{ app()->getLocale() == 'nl' ? 'selected' : '' }}>Dutch</option>
                    @endif
                </select>
            </div>

            @if(auth()->check())
                <!-- Logged-in: Profile info + links (Mobile) -->
                <div class="mt-2 border border-gray-100 rounded-xl overflow-hidden">
                    <div class="flex items-center gap-3 px-4 py-3 bg-blue-50">
                        @if(auth()->user()->profile_image)
                            <img src="{{ auth()->user()->isAdmin() ? url('public/' . auth()->user()->profile_image) : (auth()->user()->isAgent() ? auth()->user()->profile_image_url : url('public/assets/images/avatars/' . auth()->user()->profile_image)) }}" class="w-9 h-9 rounded-full object-cover border border-gray-200">
                        @elseif(auth()->user()->isAgent() && auth()->user()->company_logo)
                            <img src="{{ url('public/assets/images/settings/branding/' . auth()->user()->company_logo) }}" class="w-9 h-9 rounded-full object-contain border border-gray-200 bg-white">
                        @else
                            <div class="w-9 h-9 rounded-full flex items-center justify-center text-white text-sm font-bold flex-shrink-0" style="background:#0077BE;">
                                {{ auth()->user()->initials }}
                            </div>
                        @endif
                        <div>
                            <p class="font-semibold text-sm" style="color:#003580;">{{ auth()->user()->isAdmin() ? auth()->user()->first_name : (auth()->user()->isAgent() ? 'Travel Agent' : auth()->user()->first_name) }}</p>
                            <p class="text-xs text-gray-500">{{ auth()->user()->email }}</p>
                        </div>
                    </div>
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm hover:bg-blue-50 transition border-t border-gray-100" style="color:#003580;">
                            <i class="fas fa-gauge-high w-4 text-center"></i> Dashboard
                        </a>
                        <a href="{{ route('admin.profile.index') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm hover:bg-blue-50 transition border-t border-gray-100" style="color:#003580;">
                            <i class="fas fa-user w-4 text-center"></i> Profile
                        </a>
                        <a href="{{ route('admin.bookings.all', ['type' => 'hotel']) }}" class="flex items-center gap-2 px-4 py-2.5 text-sm hover:bg-blue-50 transition border-t border-gray-100" style="color:#003580;">
                            <i class="fas fa-clock-rotate-left w-4 text-center"></i> Recent Booking
                        </a>
                    @elseif(auth()->user()->isAgent())
                        <a href="{{ route('agent.dashboard') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm hover:bg-blue-50 transition border-t border-gray-100" style="color:#003580;">
                            <i class="fas fa-gauge-high w-4 text-center"></i> Dashboard
                        </a>
                        <a href="{{ route('agent.profile.index') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm hover:bg-blue-50 transition border-t border-gray-100" style="color:#003580;">
                            <i class="fas fa-user w-4 text-center"></i> Profile
                        </a>
                        <a href="{{ route('agent.bookings.index') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm hover:bg-blue-50 transition border-t border-gray-100" style="color:#003580;">
                            <i class="fas fa-clock-rotate-left w-4 text-center"></i> Recent Booking
                        </a>
                    @else
                        <a href="{{ route('user.dashboard') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm hover:bg-blue-50 transition border-t border-gray-100" style="color:#003580;">
                            <i class="fas fa-gauge-high w-4 text-center"></i> Dashboard
                        </a>
                        <a href="{{ route('user.profile.index') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm hover:bg-blue-50 transition border-t border-gray-100" style="color:#003580;">
                            <i class="fas fa-user w-4 text-center"></i> Profile
                        </a>
                        <a href="{{ route('user.bookings.index') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm hover:bg-blue-50 transition border-t border-gray-100" style="color:#003580;">
                            <i class="fas fa-clock-rotate-left w-4 text-center"></i> Recent Booking
                        </a>
                    @endif
                    <form method="POST" action="{{ auth()->user()->isAdmin() ? route('admin.logout') : (auth()->user()->isAgent() ? route('agent.logout') : route('user.logout')) }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-2 px-4 py-2.5 text-sm hover:bg-red-50 transition text-red-500 border-t border-gray-100 cursor-pointer">
                            <i class="fas fa-right-from-bracket w-4 text-center"></i> Logout
                        </button>
                    </form>
                </div>
            @else
                <!-- Login Button (Mobile) -->
                <a href="{{ route('login') }}" class="btn-signin w-full px-4 py-2.5 font-semibold rounded-lg transition text-sm flex items-center justify-center gap-2 mt-2" style="text-decoration:none;">
                    <i class="fas fa-right-to-bracket"></i>
                    <span>Login</span>
                </a>

                @if($showUserSignup)
                <!-- Customer Signup (Mobile) -->
                <a href="{{ route('user.register') }}" class="w-full px-4 py-2.5 font-semibold rounded-lg border-2 transition text-sm flex items-center justify-center gap-2" style="border-color:#0077BE;color:#0077BE;background:white;text-decoration:none;">
                    <i class="fas fa-user"></i>
                    <span>Customer Signup</span>
                </a>
                @endif

                @if($showAgentSignup)
                <!-- Agent Signup (Mobile) -->
                <a href="{{ route('agent.register') }}" class="w-full px-4 py-2.5 font-semibold rounded-lg border-2 transition text-sm flex items-center justify-center gap-2" style="border-color:#003580;color:#003580;background:white;text-decoration:none;">
                    <i class="fas fa-user-tie"></i>
                    <span>Agent Signup</span>
                </a>
                @endif
            @endif

        </div>

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
                    width: '100%'
                });
            });

            // Initialize language selects with Select2
            $('#language-select, #language-select-mobile').each(function() {
                $(this).select2({
                    templateResult: formatState,
                    templateSelection: formatState,
                    minimumResultsForSearch: Infinity,
                    width: '100%'
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
