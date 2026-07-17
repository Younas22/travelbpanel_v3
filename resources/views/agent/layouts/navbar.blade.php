<nav class="nav-bar sticky top-0 z-50">
    @php
        $currencies = allCurrencies();
        $active_currency = activeCurrency();
    @endphp
    <div class="max-w-7xl mx-auto px-4 py-1 md:py-2 flex items-center justify-between">

        <!-- Logo -->
        <a class="navbar-brand" href="{{ url('/') }}">
            <img src="{{ getSettingImage('business_logo','branding') }}"
                alt="Logo"
                class="img-fluid"
                style="max-height: 45px; height: auto; width: auto;">
        </a>

        <!-- Desktop Menu -->
        <div class="hidden md:flex gap-4 items-center flex-1 justify-center">
            @foreach (get_menu_items('header') as $item)
                <a href="{{ $item->full_url }}" class="nav-item {{ Request::url() == $item->full_url ? 'active' : '' }}">
                    <i class="{{ $item->icon }}"></i>
                    <span>{{ t('header.' . $item->name) }}</span>
                </a>
            @endforeach
        </div>

        <!-- Desktop: Currency + Language + Profile -->
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
                    @endif
                </select>
            </div>

            <!-- Profile Dropdown -->
            <div class="relative group" id="profile-dropdown-wrapper-desktop">
                <button class="flex items-center gap-2 px-3 py-1.5 rounded-lg border border-gray-200 bg-white group-hover:bg-gray-50 transition text-sm font-semibold" style="color:#003580;">
                    @if(auth()->user()->company_logo)
                        <img src="{{ asset('public/assets/images/settings/branding/' . auth()->user()->company_logo) }}"
                             class="w-7 h-7 rounded-full object-contain border border-gray-200 bg-white">
                    @else
                        <div class="w-7 h-7 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0" style="background:#0077BE;">
                            {{ auth()->user()->initials }}
                        </div>
                    @endif
                    <span>{{ auth()->user()->first_name }}</span>
                    <i class="fas fa-chevron-down text-xs" style="display:inline !important;"></i>
                </button>
                <div class="absolute right-0 mt-0 pt-2 w-52 z-50 hidden group-hover:block">
                    <div class="bg-white rounded-xl shadow-lg border border-gray-100 py-2">
                        <a href="{{ route('agent.dashboard') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm hover:bg-blue-50 transition" style="color:#003580;">
                            <i class="fas fa-gauge-high w-4 text-center"></i> Dashboard
                        </a>
                        <a href="{{ route('agent.profile.index') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm hover:bg-blue-50 transition" style="color:#003580;">
                            <i class="fas fa-user w-4 text-center"></i> Profile
                        </a>
                        <a href="{{ route('agent.bookings.index') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm hover:bg-blue-50 transition" style="color:#003580;">
                            <i class="fas fa-clock-rotate-left w-4 text-center"></i> My Bookings
                        </a>
                        @if(auth()->user()->hasPermission('wallet.view'))
                        <a href="{{ route('agent.wallet.index') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm hover:bg-blue-50 transition" style="color:#003580;">
                            <i class="fas fa-wallet w-4 text-center"></i> My Wallet
                        </a>
                        @endif
                        <hr class="my-2 border-gray-100">
                        <form method="POST" action="{{ route('agent.logout') }}">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-2 px-4 py-2.5 text-sm hover:bg-red-50 transition text-red-500 cursor-pointer">
                                <i class="fas fa-right-from-bracket w-4 text-center"></i> Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>

        </div>

        <!-- Hamburger (Mobile) — toggles sidebar -->
        <button class="hamburger md:hidden flex flex-col gap-1.5 cursor-pointer" onclick="toggleSidebar()">
            <span class="line w-6 h-[3px] bg-black rounded transition-all duration-300"></span>
            <span class="line w-6 h-[3px] bg-black rounded transition-all duration-300"></span>
            <span class="line w-6 h-[3px] bg-black rounded transition-all duration-300"></span>
        </button>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                if (typeof jQuery !== 'undefined' && jQuery.fn.select2) {
                    initAgentFlagSelects();
                    var $selects = $('#currency-select, #language-select');
                    $selects.each(function() {
                        var $el = $(this);
                        $el.closest('.relative').on('mouseenter', function() {
                            $selects.not($el).select2('close');
                            $el.select2('open');
                        }).on('mouseleave', function() {
                            setTimeout(function() {
                                var $drop = $el.data('select2').$dropdown;
                                if (!$drop || !$drop.is(':hover')) { $el.select2('close'); }
                            }, 150);
                        });
                        $el.on('select2:open', function() {
                            var $drop = $el.data('select2').$dropdown;
                            if ($drop) { $drop.on('mouseleave', function() { $el.select2('close'); }); }
                        });
                    });
                }
            }, 100);
        });

        function initAgentFlagSelects() {
            function formatState(state) {
                if (!state.id) return state.text;
                var flagClass = $(state.element).data('flag');
                if (!flagClass) return state.text;
                var isRTL = document.documentElement.dir === 'rtl';
                var marginStyle = isRTL ? 'margin-left:8px;' : 'margin-right:8px;';
                return $('<span style="display:inline-flex;align-items:center;font-size:11px;white-space:nowrap;"><span class="' + flagClass + '" style="' + marginStyle + 'flex-shrink:0;"></span>' + state.text + '</span>');
            }
            $('#currency-select').select2({ templateResult: formatState, templateSelection: formatState, minimumResultsForSearch: Infinity, width: '100%' });
            $('#language-select').select2({ templateResult: formatState, templateSelection: formatState, minimumResultsForSearch: Infinity, width: '100%' });

            $('#currency-select').on('select2:select', function(e) {
                fetch('{{ url('/set-currency') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ code: e.params.data.id })
                }).then(function(r){ return r.json(); }).then(function(d){ if(d.success) window.location.reload(); });
            });

            $('#language-select').on('select2:select', function(e) {
                var url = new URL(window.location.href);
                url.searchParams.set('lang', e.params.data.id);
                window.location.href = url.toString();
            });
        }
    </script>
</nav>
