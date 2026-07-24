@php
    $currencies = allCurrencies();
    $active_currency = activeCurrency();
    $agent = auth()->user();
@endphp

<div class="am-topbar">
    <div class="am-topbar-left">
        <button type="button" class="am-topbar-menu-btn" onclick="toggleAmSidebar()" aria-label="Toggle sidebar">
            <i class="bi bi-list"></i>
        </button>
        <div class="am-topbar-title">@yield('title', 'Agent Panel')</div>
    </div>

    <div class="am-topbar-right">

        <select id="am-currency-select" class="am-topbar-select">
            @foreach($currencies as $currency)
                <option value="{{ $currency->currency_name }}" data-flag="{{ getFlagClass($currency->currency_name) }}"
                    {{ $active_currency->currency_name == $currency->currency_name ? 'selected' : '' }}>
                    {{ $currency->currency_name }}
                </option>
            @endforeach
        </select>

        <select id="am-language-select" class="am-topbar-select">
            @if(isset($activeLanguages))
                @foreach($activeLanguages as $language)
                    <option value="{{ $language->code }}" data-flag="{{ getFlagClass(strtoupper($language->code)) }}"
                            {{ app()->getLocale() == $language->code ? 'selected' : '' }}>
                        {{ $language->name }}
                    </option>
                @endforeach
            @else
                <option value="en" data-flag="{{ getFlagClass('GB') }}" selected>English</option>
            @endif
        </select>

        @if($agent->hasPermission('wallet.view') && $agent->wallet)
            <a href="{{ route('agent.wallet.index') }}" class="am-topbar-wallet">
                <i class="bi bi-wallet2"></i> PKR {{ number_format($agent->wallet->balance, 0) }}
            </a>
        @endif

        <div class="am-dropdown">
            <button type="button" class="am-topbar-user" onclick="toggleAmDropdown(this)">
                @if($agent->company_logo)
                    <img src="{{ asset('public/assets/images/settings/branding/' . $agent->company_logo) }}" class="am-topbar-user-avatar">
                @else
                    <span class="am-topbar-user-avatar">{{ $agent->initials }}</span>
                @endif
                <span>{{ $agent->first_name }}</span>
                <i class="bi bi-chevron-down" style="font-size:10px;"></i>
            </button>
            <div class="am-dropdown-menu">
                <a href="{{ route('agent.dashboard') }}" class="am-dropdown-item"><i class="bi bi-speedometer2"></i> Dashboard</a>
                <a href="{{ route('agent.profile.index') }}" class="am-dropdown-item"><i class="bi bi-person"></i> Profile</a>
                <a href="{{ route('agent.bookings.index') }}" class="am-dropdown-item"><i class="bi bi-clock-history"></i> My Bookings</a>
                @if($agent->hasPermission('wallet.view'))
                    <a href="{{ route('agent.wallet.index') }}" class="am-dropdown-item"><i class="bi bi-wallet2"></i> My Wallet</a>
                @endif
                <div class="am-dropdown-sep"></div>
                <form method="POST" action="{{ route('agent.logout') }}">
                    @csrf
                    <button type="submit" class="am-dropdown-item am-dropdown-danger">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(function() {
            if (typeof jQuery !== 'undefined' && jQuery.fn.select2) {
                initAmFlagSelects();
            }
        }, 100);
    });

    function initAmFlagSelects() {
        function formatState(state) {
            if (!state.id) return state.text;
            var flagClass = $(state.element).data('flag');
            if (!flagClass) return state.text;
            var isRTL = document.documentElement.dir === 'rtl';
            var marginStyle = isRTL ? 'margin-left:8px;' : 'margin-right:8px;';
            return $('<span style="display:inline-flex;align-items:center;font-size:11px;white-space:nowrap;"><span class="' + flagClass + '" style="' + marginStyle + 'flex-shrink:0;"></span>' + state.text + '</span>');
        }
        $('#am-currency-select').select2({ templateResult: formatState, templateSelection: formatState, minimumResultsForSearch: Infinity, width: 'auto' });
        $('#am-language-select').select2({ templateResult: formatState, templateSelection: formatState, minimumResultsForSearch: Infinity, width: 'auto' });

        $('#am-currency-select').on('select2:select', function(e) {
            fetch('{{ url('/set-currency') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ code: e.params.data.id })
            }).then(function(r){ return r.json(); }).then(function(d){ if(d.success) window.location.reload(); });
        });

        $('#am-language-select').on('select2:select', function(e) {
            var url = new URL(window.location.href);
            url.searchParams.set('lang', e.params.data.id);
            window.location.href = url.toString();
        });
    }
</script>
