{{-- Expects: $partner, $module, $isManual, $avatar, $modIcon, $modColor,
     $displayName, $isConnected — extracted by index.blade.php's
     $renderPartnerCard closure so the "All" pane and each per-module pane
     render an identical card from one place. --}}
<div class="tp-card-nova bg-white rounded-2xl border border-novaborder shadow-sm p-3.5"
     data-supplier-type="{{ $isManual ? 'manual' : 'api' }}"
     data-connected="{{ $isConnected ? '1' : '0' }}"
     data-name="{{ strtolower($displayName) }}">

    <div class="flex items-start justify-between gap-2 mb-2.5">
        <div class="flex items-center gap-2 min-w-0">
            @if($isManual)
                <div class="tp-logo-nova w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 font-bold text-[11px]"
                     style="background:{{ $avatar['bg'] }}; color:{{ $avatar['text'] }}"
                     title="Added manually — no third-party API connected">
                    {{ strtoupper(substr($displayName, 0, 2)) }}
                </div>
            @else
                <div class="tp-logo-nova w-9 h-9 rounded-xl bg-slate-50 border border-novaborder flex items-center justify-center flex-shrink-0 overflow-hidden">
                    <img src="{{ asset('public/assets/images/partners/'.$partner->company_name.'.png') }}"
                         alt="{{ $partner->company_name }}" class="max-w-full max-h-full object-contain"
                         onerror="this.style.display='none'">
                </div>
            @endif
            <div class="min-w-0">
                <p class="text-xs font-semibold text-novatext truncate">{{ $displayName }}</p>
                <p class="text-[10px] text-novamuted truncate mt-0.5">{{ $isManual ? 'Manual entry' : ucwords(str_replace('_',' ',$partner->company_name)).' API' }}</p>
            </div>
        </div>
        <label class="tp-switch-nova tp-switch-sm flex-shrink-0">
            <input type="checkbox" class="partner-switch-input" data-partner-id="{{ $partner->id }}" data-module-id="{{ $module->id }}"
                   {{ $partner->status === 'active' ? 'checked' : '' }}
                   onchange="togglePartnerStatus(event, {{ $partner->id }}, {{ $module->id }})">
            <span class="tp-slider-nova"></span>
        </label>
    </div>

    <div class="flex flex-wrap items-center gap-1 mb-2.5">
        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-semibold {{ $modColor['bg'] }} {{ $modColor['text'] }}">
            <i class="bi {{ $modIcon }}"></i> {{ $module->name }}
        </span>
        @if($isManual)
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-semibold bg-slate-100 text-novamuted">Manual</span>
        @elseif($isConnected)
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-semibold bg-emerald-50 text-emerald-600">Connected</span>
        @else
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-semibold bg-slate-100 text-novamuted">Disconnected</span>
        @endif
        @if(!$isManual)
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-semibold {{ $partner->development_mode ? 'bg-amber-50 text-amber-600' : 'bg-blue-50 text-novablue' }}">
                {{ $partner->development_mode ? 'Sandbox' : 'Live' }}
            </span>
        @endif
    </div>

    @if(!$isManual && $partner->commission_rate !== null)
        <div class="flex items-center justify-between text-[11px] mb-2.5 pb-2.5 border-b border-novaborder">
            <span class="text-novamuted">Commission</span>
            <span class="font-semibold text-novatext">{{ number_format($partner->commission_rate, 1) }}%</span>
        </div>
    @endif

    @if($module->id != 3)
        <a href="{{ route('admin.travel-partners.edit', $partner->id) }}"
           class="tp-btn-nova w-full inline-flex items-center justify-center gap-1.5 rounded-full border border-novaborder px-3 py-1.5 text-[11px] font-semibold hover:bg-novabg">
            <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M16.474 5.408 18.592 7.526M4 20l1.11-3.92a2 2 0 0 1 .53-.9l9.9-9.9a1.5 1.5 0 0 1 2.12 0l1.06 1.06a1.5 1.5 0 0 1 0 2.12l-9.9 9.9a2 2 0 0 1-.9.53L4 20Z"/></svg>
            Edit
        </a>
    @endif
</div>
