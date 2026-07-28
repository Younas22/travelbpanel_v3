@extends('admin-nova.layouts.app')

@section('title', 'Supplier Integrations')

@push('styles')
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        corePlugins: { preflight: false },
        theme: {
            extend: {
                fontFamily: { jakarta: ['Plus Jakarta Sans', 'sans-serif'] },
                colors: {
                    novabg: '#F7F8FC',
                    novablue: '#2563EB',
                    novacyan: '#06B6D4',
                    novatext: '#000000',
                    novamuted: '#000000',
                    novaborder: '#E5E7EB',
                    novasuccess: '#22C55E',
                    novawarning: '#F59E0B',
                    novadanger: '#EF4444',
                },
            },
        },
    };
</script>
<style>
    #tpPage, #tpPage *, #tpPage *::before, #tpPage *::after { box-sizing: border-box; }
    #tpPage { font-family: 'Plus Jakarta Sans', sans-serif; }
    #tpPage h1, #tpPage h2, #tpPage h3, #tpPage p, #tpPage ul, #tpPage dl, #tpPage dd { margin: 0; padding: 0; }
    #tpPage ul { list-style: none; }
    #tpPage a { text-decoration: none; color: inherit; }
    #tpPage button { font: inherit; color: inherit; background: none; border: none; cursor: pointer; padding: 0; }
    #tpPage svg { display: block; }

    #tpPage .tt-fade-in { animation: tpFadeIn .5s ease both; }
    @keyframes tpFadeIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }

    /* Supplier card hover: lift, stronger shadow, border turns blue, logo scales — per spec. */
    #tpPage .tp-card-nova { transition: transform .3s ease, box-shadow .3s ease, border-color .3s ease; }
    #tpPage .tp-card-nova:hover { transform: translateY(-4px); box-shadow: 0 20px 40px -14px rgba(37,99,235,.18); border-color: #2563EB; }
    #tpPage .tp-card-nova:hover .tp-logo-nova { transform: scale(1.06); }
    #tpPage .tp-logo-nova { transition: transform .3s ease; }

    /* iOS-style toggle switch. */
    #tpPage .tp-switch-nova { position: relative; display: inline-block; width: 40px; height: 22px; flex-shrink: 0; }
    #tpPage .tp-switch-nova input { opacity: 0; width: 0; height: 0; }
    #tpPage .tp-switch-nova .tp-slider-nova {
        position: absolute; inset: 0; background: #E5E7EB; border-radius: 9999px; cursor: pointer;
        transition: background .25s ease;
    }
    #tpPage .tp-switch-nova .tp-slider-nova::before {
        content: ''; position: absolute; width: 16px; height: 16px; left: 3px; top: 3px;
        background: #fff; border-radius: 50%; transition: transform .25s ease; box-shadow: 0 1px 3px rgba(0,0,0,.25);
    }
    #tpPage .tp-switch-nova input:checked + .tp-slider-nova { background: #2563EB; }
    #tpPage .tp-switch-nova input:checked + .tp-slider-nova::before { transform: translateX(18px); }

    /* Smaller variant used on the compact supplier cards. */
    #tpPage .tp-switch-nova.tp-switch-sm { width: 30px; height: 17px; }
    #tpPage .tp-switch-nova.tp-switch-sm .tp-slider-nova::before { width: 12px; height: 12px; left: 2.5px; top: 2.5px; }
    #tpPage .tp-switch-nova.tp-switch-sm input:checked + .tp-slider-nova::before { transform: translateX(13px); }

    /* Plain CSS backing for every pill button on this page (module/filter
       chips, "Add Module"/"Add Integration", card Edit/View Details) —
       Tailwind CDN compiles utility classes at runtime, so on a slow
       connection (or cdn.tailwindcss.com being blocked) there's a window
       where rounded-full/border/bg-novablue simply haven't been generated
       yet and the button has no shape or color at all. These classes give a
       correct baseline appearance immediately, Tailwind or not; Tailwind's
       own utilities (hover states, spacing) still layer on top harmlessly
       once compiled. */
    #tpPage .tp-chip, #tpPage .tp-filter-chip, #tpPage .tp-btn-nova {
        display: inline-flex; align-items: center; justify-content: center; gap: 6px;
        border: 1px solid #E5E7EB; border-radius: 9999px; color: #000;
        background: #fff; white-space: nowrap;
        transition: background .2s ease, border-color .2s ease, color .2s ease;
    }
    #tpPage .tp-chip { padding: 8px 16px; font-size: 12px; font-weight: 600; }
    #tpPage .tp-filter-chip { padding: 6px 14px; font-size: 12px; font-weight: 600; }
    #tpPage .tp-btn-nova { padding: 10px 16px; font-size: 12px; font-weight: 600; }
    #tpPage .tp-chip:hover, #tpPage .tp-filter-chip:hover, #tpPage .tp-btn-nova:hover { background: #F7F8FC; }
    #tpPage .tp-chip.active, #tpPage .tp-filter-chip.tp-filter-active {
        background: #2563EB; color: #fff; border-color: #2563EB;
    }
    #tpPage .tp-card-nova .tp-btn-nova { flex: 1; padding: 8px 12px; }

    /* .tp-btn-primary always comes after the block above in source order, so
       it correctly wins the background/color tie (same specificity, later
       wins) for the one button that combines both classes ("Add Integration"). */
    #tpPage .tp-btn-primary { background: #2563EB; color: #fff; border-color: #2563EB; }
    #tpPage .tp-btn-primary:hover { background: #1D4ED8; border-color: #1D4ED8; }

    /* Tab/pane visibility — same #tpane-all / #tpane-mod-{id} ids and .tp-pane
       class the existing switchTPTab() JS already targets, just Tailwind-less
       plain CSS instead of the Classic .tp-pane/.active rules. */
    #tpPage .tp-pane { display: none; }
    #tpPage .tp-pane.active { display: block; }
    #tpPage .tp-chip.active { background: #2563EB !important; color: #fff !important; border-color: #2563EB !important; }
    #tpPage .tp-card-nova.tp-hidden-by-filter { display: none; }

    #tpPage .sortable-ghost { opacity: .4; }
    #tpPage .sortable-chosen { box-shadow: 0 0 0 2px #2563EB inset; }
</style>
@endpush

@section('content')
@php
    $icMap = [
        'flights'=>'bi-airplane','flight'=>'bi-airplane',
        'hotels'=>'bi-building','hotel'=>'bi-building','stay'=>'bi-building',
        'visa'=>'bi-passport','visas'=>'bi-passport',
        'transfers'=>'bi-car-front','transfer'=>'bi-car-front',
        'tours'=>'bi-map','tour'=>'bi-map',
        'umrah'=>'bi-moon-stars',
        'insurance'=>'bi-shield-check',
        'packages'=>'bi-box-seam','package'=>'bi-box-seam',
    ];
    $moduleColor = [
        'bi-airplane'     => ['bg' => 'bg-blue-50', 'text' => 'text-novablue'],
        'bi-building'     => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600'],
        'bi-passport'     => ['bg' => 'bg-amber-50', 'text' => 'text-amber-600'],
        'bi-car-front'    => ['bg' => 'bg-violet-50', 'text' => 'text-violet-600'],
        'bi-map'          => ['bg' => 'bg-pink-50', 'text' => 'text-pink-600'],
        'bi-moon-stars'   => ['bg' => 'bg-indigo-50', 'text' => 'text-indigo-600'],
        'bi-shield-check' => ['bg' => 'bg-teal-50', 'text' => 'text-teal-600'],
        'bi-box-seam'     => ['bg' => 'bg-orange-50', 'text' => 'text-orange-600'],
        'bi-puzzle'       => ['bg' => 'bg-slate-100', 'text' => 'text-novatext'],
    ];
    $avatarPalette = [
        ['bg' => '#DBEAFE', 'text' => '#1D4ED8'],
        ['bg' => '#DCFCE7', 'text' => '#15803D'],
        ['bg' => '#FEF3C7', 'text' => '#B45309'],
        ['bg' => '#FCE7F3', 'text' => '#BE185D'],
        ['bg' => '#EDE9FE', 'text' => '#6D28D9'],
    ];

    // Every number below is aggregated straight from $modules->partners (the
    // exact same eager-loaded collection the Classic page already receives
    // from TravelPartnerController::index) — nothing here is invented.
    $tpAllPartners = $modules->flatMap(fn($m) => $m->partners);
    $tpTotalSuppliers = $tpAllPartners->count();
    $tpConnectedApis = $tpAllPartners->where('supplier_type', '!=', 'manual')->where('status', 'active')->count();
    $tpManualProviders = $tpAllPartners->where('supplier_type', 'manual')->count();
    $tpInactiveProviders = $tpAllPartners->where('status', '!=', 'active')->count();
@endphp

<div id="tpPage" class="tt-fade-in font-jakarta">

    {{-- ============ HEADER ============ --}}
    <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
        <div>
            <h1 class="text-lg font-bold text-novatext">Supplier Integrations</h1>
            <p class="text-xs text-novamuted mt-1">Manage all travel APIs and manual booking providers from one place.</p>
        </div>
        <div class="flex items-center gap-2">
            <div class="relative">
                <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-novamuted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
                <input type="text" id="tpSearch" placeholder="Search supplier..."
                       class="w-48 sm:w-64 text-sm border border-novaborder rounded-full pl-10 pr-4 py-2.5">
            </div>
            <button type="button" data-bs-toggle="modal" data-bs-target="#addPartnerModal"
                    class="tp-btn-nova tp-btn-primary inline-flex items-center gap-1.5 rounded-full px-4 py-2.5 text-xs font-semibold whitespace-nowrap">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                Add Integration
            </button>
            <button type="button" data-bs-toggle="modal" data-bs-target="#addModuleModal"
                    class="tp-btn-nova inline-flex items-center gap-1.5 rounded-full border border-novaborder hover:bg-novabg px-4 py-2.5 text-xs font-semibold whitespace-nowrap" title="Create a new integration category">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
                Add Module
            </button>
        </div>
    </div>

    {{-- ============ KPI CARDS (real aggregated counts) ============ --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
        <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-3">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-medium text-novamuted">Total Suppliers</p>
                <div class="w-7 h-7 rounded-full bg-novablue text-white flex items-center justify-center flex-shrink-0">
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="14" rx="2"/><path d="M3 9h18M8 4v5"/></svg>
                </div>
            </div>
            <span class="block text-lg font-bold text-novatext">{{ number_format($tpTotalSuppliers) }}</span>
        </div>
        <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-3">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-medium text-novamuted">Connected APIs</p>
                <div class="w-7 h-7 rounded-full bg-novasuccess text-white flex items-center justify-center flex-shrink-0">
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12.75 11.25 15 15 9.75"/></svg>
                </div>
            </div>
            <span class="block text-lg font-bold text-novatext">{{ number_format($tpConnectedApis) }}</span>
        </div>
        <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-3">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-medium text-novamuted">Manual Providers</p>
                <div class="w-7 h-7 rounded-full bg-novacyan text-white flex items-center justify-center flex-shrink-0">
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M16.474 5.408 18.592 7.526M4 20l1.11-3.92a2 2 0 0 1 .53-.9l9.9-9.9a1.5 1.5 0 0 1 2.12 0l1.06 1.06a1.5 1.5 0 0 1 0 2.12l-9.9 9.9a2 2 0 0 1-.9.53L4 20Z"/></svg>
                </div>
            </div>
            <span class="block text-lg font-bold text-novatext">{{ number_format($tpManualProviders) }}</span>
        </div>
        <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-3">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-medium text-novamuted">Inactive Providers</p>
                <div class="w-7 h-7 rounded-full bg-novadanger text-white flex items-center justify-center flex-shrink-0">
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                </div>
            </div>
            <span class="block text-lg font-bold text-novatext">{{ number_format($tpInactiveProviders) }}</span>
        </div>
    </div>

    {{-- ============ FILTER CHIPS ============ --}}
    {{-- Module chips are real + draggable/reorderable exactly like the Classic
         page (same #tp-sortable-tabs id + Sortable.js + admin.modules.reorder
         endpoint). Manual/API/Connected/Disconnected are additional client-
         side filters layered on top, over the cards already in the DOM. --}}
    <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 sm:p-5 mb-6">
        <div class="flex flex-wrap items-center gap-2 mb-3" id="tp-sortable-tabs">
            <button class="tp-chip active tp-tab tp-tab-fixed inline-flex items-center gap-1.5 rounded-full border border-novaborder px-4 py-2 text-xs font-semibold hover:bg-novabg" onclick="switchTPTab('all', this)">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
                All
                <span class="tp-tab-count inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 rounded-full bg-black/10 text-[10px] font-bold">{{ $modules->count() }}</span>
            </button>
            @foreach($modules as $module)
                @php $tabIcon = $icMap[strtolower($module->name)] ?? 'bi-puzzle'; @endphp
                <button class="tp-chip tp-tab inline-flex items-center gap-1.5 rounded-full border border-novaborder px-4 py-2 text-xs font-semibold hover:bg-novabg {{ $module->status !== 'active' ? 'opacity-50' : '' }}"
                        data-module-id="{{ $module->id }}"
                        onclick="switchTPTab('mod-{{ $module->id }}', this)">
                    <span class="tp-drag-grip cursor-grab text-novaborder">⠿</span>
                    <i class="bi {{ $tabIcon }}"></i>
                    {{ $module->name }}
                    <span class="tp-tab-count inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 rounded-full bg-black/10 text-[10px] font-bold">{{ $module->partners_count }}</span>
                </button>
            @endforeach
        </div>
        <div class="flex flex-wrap items-center gap-2 pt-3 border-t border-novaborder">
            <span class="text-[10px] font-semibold uppercase tracking-wide text-novamuted mr-1">Filter</span>
            <button type="button" class="tp-filter-chip tp-filter-active inline-flex items-center rounded-full border border-novaborder px-3.5 py-1.5 text-xs font-semibold hover:bg-novabg" data-filter="all" onclick="tpApplyTypeFilter('all', this)">All</button>
            <button type="button" class="tp-filter-chip inline-flex items-center rounded-full border border-novaborder px-3.5 py-1.5 text-xs font-semibold hover:bg-novabg" data-filter="manual" onclick="tpApplyTypeFilter('manual', this)">Manual</button>
            <button type="button" class="tp-filter-chip inline-flex items-center rounded-full border border-novaborder px-3.5 py-1.5 text-xs font-semibold hover:bg-novabg" data-filter="api" onclick="tpApplyTypeFilter('api', this)">API</button>
            <button type="button" class="tp-filter-chip inline-flex items-center rounded-full border border-novaborder px-3.5 py-1.5 text-xs font-semibold hover:bg-novabg" data-filter="connected" onclick="tpApplyTypeFilter('connected', this)">Connected</button>
            <button type="button" class="tp-filter-chip inline-flex items-center rounded-full border border-novaborder px-3.5 py-1.5 text-xs font-semibold hover:bg-novabg" data-filter="disconnected" onclick="tpApplyTypeFilter('disconnected', this)">Disconnected</button>
        </div>
    </div>

    @php
        // Renders one premium supplier card — used for both the "All" pane
        // and each individual module pane below, so the two stay pixel-
        // identical (same partial logic the Classic page duplicates too).
        $renderPartnerCard = function ($partner, $module) use ($icMap, $moduleColor, $avatarPalette) {
            $isManual = $partner->supplier_type == 'manual';
            $avatar = $avatarPalette[crc32($partner->company_name) % count($avatarPalette)];
            $modIcon = $icMap[strtolower($module->name)] ?? 'bi-puzzle';
            $modColor = $moduleColor[$modIcon] ?? $moduleColor['bi-puzzle'];
            $displayName = $isManual ? $module->name : ucwords(str_replace('_', ' ', $partner->company_name));
            $isConnected = !$isManual && $partner->status === 'active';
            return compact('partner', 'module', 'isManual', 'avatar', 'modIcon', 'modColor', 'displayName', 'isConnected');
        };
    @endphp

    {{-- ============ ALL MODULES PANE ============ --}}
    <div id="tpane-all" class="tp-pane active">
        @if($modules->isEmpty())
            <div class="tt-card bg-white rounded-2xl border border-novaborder p-14 text-center">
                <svg class="w-12 h-12 text-novaborder mx-auto mb-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18M9 16l2 2 4-4"/></svg>
                <p class="text-sm font-semibold text-novatext">No modules found</p>
                <button type="button" data-bs-toggle="modal" data-bs-target="#addModuleModal" class="mt-4 inline-flex items-center gap-1.5 rounded-full bg-novablue text-white px-4 py-2 text-xs font-semibold">Create one now</button>
            </div>
        @else
            @php $tpHasAnyPartner = false; @endphp
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-3" id="tpGridAll">
                @foreach($modules as $module)
                    @foreach($module->partners as $partner)
                        @php $tpHasAnyPartner = true; extract($renderPartnerCard($partner, $module)); @endphp
                        @include('admin-nova.travel-partners._card')
                    @endforeach
                @endforeach
            </div>
            @if(!$tpHasAnyPartner)
                <div class="tt-card bg-white rounded-2xl border border-novaborder p-14 text-center">
                    <svg class="w-12 h-12 text-novaborder mx-auto mb-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18M9 16l2 2 4-4"/></svg>
                    <p class="text-sm font-semibold text-novatext">No partners added yet</p>
                    <button type="button" data-bs-toggle="modal" data-bs-target="#addPartnerModal" class="mt-4 inline-flex items-center gap-1.5 rounded-full bg-novablue text-white px-4 py-2 text-xs font-semibold">Add Supplier</button>
                </div>
            @endif
        @endif
    </div>

    {{-- ============ PER-MODULE PANES ============ --}}
    @foreach($modules as $module)
        @php
            $iconClass = $icMap[strtolower($module->name)] ?? 'bi-puzzle';
            $modColorHeader = $moduleColor[$iconClass] ?? $moduleColor['bi-puzzle'];
        @endphp
        <div id="tpane-mod-{{ $module->id }}" class="tp-pane">
            <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 sm:p-5 mb-4 flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full {{ $modColorHeader['bg'] }} {{ $modColorHeader['text'] }} flex items-center justify-center flex-shrink-0">
                        <i class="bi {{ $iconClass }}"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-novatext">{{ $module->name }}</p>
                        <p class="text-xs text-novamuted mt-0.5">{{ $module->partners_count }} {{ Str::plural('partner', $module->partners_count) }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold {{ $module->status === 'active' ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-novamuted' }}">
                        {{ $module->status === 'active' ? 'Active' : 'Inactive' }}
                    </span>
                    <label class="tp-switch-nova">
                        <input type="checkbox" class="module-switch-input" data-module-id="{{ $module->id }}"
                               {{ $module->status === 'active' ? 'checked' : '' }}
                               onchange="toggleModuleStatus(event, {{ $module->id }})">
                        <span class="tp-slider-nova"></span>
                    </label>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-3">
                @forelse($module->partners as $partner)
                    @php extract($renderPartnerCard($partner, $module)); @endphp
                    @include('admin-nova.travel-partners._card')
                @empty
                    <div class="col-span-full tt-card bg-white rounded-2xl border border-novaborder p-14 text-center">
                        <svg class="w-12 h-12 text-novaborder mx-auto mb-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18M9 16l2 2 4-4"/></svg>
                        <p class="text-sm font-semibold text-novatext">No partners added yet</p>
                        <button type="button" data-bs-toggle="modal" data-bs-target="#addPartnerModal" class="mt-4 inline-flex items-center gap-1.5 rounded-full bg-novablue text-white px-4 py-2 text-xs font-semibold">Add Supplier</button>
                    </div>
                @endforelse
            </div>
        </div>
    @endforeach

    {{-- ============ ADD MODULE MODAL (Bootstrap modal shell kept for
         real functionality, content restyled) ============ --}}
    <div class="modal fade" id="addModuleModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" style="border-radius:1.5rem; font-family:'Plus Jakarta Sans', sans-serif;">
                <div class="modal-header" style="border-bottom:1px solid #E5E7EB;">
                    <h5 class="modal-title" style="font-weight:700;">Create New Module</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addModuleForm">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" style="font-size:.8rem; font-weight:600;">Module Name</label>
                            <input type="text" name="name" class="form-control" style="border-radius:9999px;" placeholder="e.g., Flight, Hotel, Visa" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" style="font-size:.8rem; font-weight:600;">Description</label>
                            <textarea name="description" class="form-control" style="border-radius:1rem;" rows="3" placeholder="Module description..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" style="font-size:.8rem; font-weight:600;">Status</label>
                            <select name="status" class="form-select" style="border-radius:9999px;">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer" style="border-top:1px solid #E5E7EB;">
                    <button type="button" data-bs-dismiss="modal"
                            style="display:inline-flex; align-items:center; justify-content:center; border-radius:9999px; padding:10px 20px; font-size:13px; font-weight:600; font-family:'Plus Jakarta Sans',sans-serif; background:#F7F8FC; color:#000; border:1px solid #E5E7EB; cursor:pointer;">Cancel</button>
                    <button type="submit" form="addModuleForm"
                            style="display:inline-flex; align-items:center; justify-content:center; border-radius:9999px; padding:10px 20px; font-size:13px; font-weight:600; font-family:'Plus Jakarta Sans',sans-serif; background:#2563EB; color:#fff; border:1px solid #2563EB; cursor:pointer;">Create Module</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ============ ADD PARTNER MODAL ============ --}}
    <div class="modal fade" id="addPartnerModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content" style="border-radius:1.5rem; font-family:'Plus Jakarta Sans', sans-serif;">
                <div class="modal-header" style="border-bottom:1px solid #E5E7EB;">
                    <h5 class="modal-title" style="font-weight:700;">Add New Integration</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addPartnerForm">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label" style="font-size:.8rem; font-weight:600;">Company Name</label>
                                    <input type="text" name="company_name" class="form-control" style="border-radius:9999px;" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label" style="font-size:.8rem; font-weight:600;">Module</label>
                                    <select name="module_id" class="form-select" style="border-radius:9999px;" required>
                                        <option value="">Select a module</option>
                                        @foreach($modules as $module)
                                            <option value="{{ $module->id }}">{{ $module->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label" style="font-size:.8rem; font-weight:600;">Commission Rate (%)</label>
                                    <input type="number" name="commission_rate" class="form-control" style="border-radius:9999px;" min="0" max="100" step="0.1" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label" style="font-size:.8rem; font-weight:600;">Status</label>
                                    <select name="status" class="form-select" style="border-radius:9999px;" required>
                                        <option value="active">Active</option>
                                        <option value="pending">Pending</option>
                                        <option value="suspended">Suspended</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer" style="border-top:1px solid #E5E7EB;">
                    <button type="button" data-bs-dismiss="modal"
                            style="display:inline-flex; align-items:center; justify-content:center; border-radius:9999px; padding:10px 20px; font-size:13px; font-weight:600; font-family:'Plus Jakarta Sans',sans-serif; background:#F7F8FC; color:#000; border:1px solid #E5E7EB; cursor:pointer;">Cancel</button>
                    <button type="submit" form="addPartnerForm"
                            style="display:inline-flex; align-items:center; justify-content:center; border-radius:9999px; padding:10px 20px; font-size:13px; font-weight:600; font-family:'Plus Jakarta Sans',sans-serif; background:#2563EB; color:#fff; border:1px solid #2563EB; cursor:pointer;">Add Integration</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const BASE_URL = "{{ url('') }}";
    var _tpJustDragged = false;

    function switchTPTab(id, btn) {
        if (_tpJustDragged) return;
        document.querySelectorAll('#tpPage .tp-pane').forEach(function (p) { p.classList.remove('active'); });
        document.querySelectorAll('#tpPage .tp-tab').forEach(function (b) { b.classList.remove('active'); });
        document.getElementById('tpane-' + id).classList.add('active');
        btn.classList.add('active');
    }

    // Manual/API/Connected/Disconnected — client-side filter layered on top
    // of whichever module pane is currently visible (no server round-trip,
    // same "no filter endpoint exists for this combination yet" reasoning
    // as the booking pages' client-side filters).
    var _tpTypeFilter = 'all';
    function tpApplyTypeFilter(filter, btn) {
        _tpTypeFilter = filter;
        document.querySelectorAll('#tpPage .tp-filter-chip').forEach(function (c) { c.classList.remove('tp-filter-active'); });
        btn.classList.add('tp-filter-active');
        tpRefilterCards();
    }

    function tpRefilterCards() {
        var query = (document.getElementById('tpSearch').value || '').toLowerCase().trim();
        document.querySelectorAll('#tpPage .tp-card-nova').forEach(function (card) {
            var matchesType = _tpTypeFilter === 'all'
                || (_tpTypeFilter === 'manual' && card.dataset.supplierType === 'manual')
                || (_tpTypeFilter === 'api' && card.dataset.supplierType === 'api')
                || (_tpTypeFilter === 'connected' && card.dataset.connected === '1')
                || (_tpTypeFilter === 'disconnected' && card.dataset.connected === '0');
            var matchesSearch = !query || (card.dataset.name || '').includes(query);
            card.classList.toggle('tp-hidden-by-filter', !(matchesType && matchesSearch));
        });
    }

    document.getElementById('tpSearch').addEventListener('input', tpRefilterCards);

    function toggleModuleStatus(event, moduleId) {
        event.stopPropagation();
        fetch(`${BASE_URL}/admin/modules/${moduleId}/update-status`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({})
        }).then(r => r.json()).then(data => { if (data.success) location.reload(); }).catch(e => console.error('Error:', e));
    }

    function togglePartnerStatus(event, partnerId, moduleId) {
        event.stopPropagation();
        fetch(`${BASE_URL}/admin/travel-partners/${partnerId}/toggle-status`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ module_id: moduleId })
        }).then(r => r.json()).then(data => {
            if (data.success) {
                if (data.module_status_changed) {
                    const ms = document.querySelector(`.module-switch-input[data-module-id="${moduleId}"]`);
                    if (ms) ms.checked = data.new_module_status === 'active';
                }
                location.reload();
            }
        }).catch(error => { console.error('Error:', error); event.target.checked = !event.target.checked; });
    }

    document.getElementById('addModuleForm')?.addEventListener('submit', function (e) {
        e.preventDefault();
        fetch('{{ route("admin.modules.store") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'X-Requested-With': 'XMLHttpRequest' },
            body: new FormData(this)
        }).then(r => r.json()).then(data => { if (data.success) location.reload(); }).catch(e => console.error('Error:', e));
    });

    document.getElementById('addPartnerForm')?.addEventListener('submit', function (e) {
        e.preventDefault();
        fetch('{{ route("admin.travel-partners.store") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'X-Requested-With': 'XMLHttpRequest' },
            body: new FormData(this)
        }).then(r => r.json()).then(data => { if (data.success) location.reload(); }).catch(e => console.error('Error:', e));
    });

    document.addEventListener('DOMContentLoaded', function () {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('#tpPage [title]'));
        tooltipTriggerList.map(function (el) { return new bootstrap.Tooltip(el); });

        const container = document.getElementById('tp-sortable-tabs');
        if (container && window.Sortable) {
            Sortable.create(container, {
                animation: 150,
                filter: '.tp-tab-fixed',
                draggable: '.tp-tab:not(.tp-tab-fixed)',
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                onStart: function () { _tpJustDragged = false; },
                onMove: function () { _tpJustDragged = true; },
                onEnd: function () {
                    setTimeout(function () { _tpJustDragged = false; }, 50);
                    const items = [];
                    container.querySelectorAll('.tp-tab[data-module-id]').forEach(function (btn, index) {
                        items.push({ id: parseInt(btn.dataset.moduleId), sort_order: index + 1 });
                    });
                    fetch('{{ route("admin.modules.reorder") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({ items: items }),
                    }).then(r => r.json()).then(data => {
                        showTPToast(data.success ? 'Module order saved!' : 'Error saving order', data.success ? 'success' : 'error');
                    }).catch(() => showTPToast('Error saving order', 'error'));
                }
            });
        }
    });

    function showTPToast(msg, type) {
        const t = document.createElement('div');
        t.textContent = msg;
        t.style.cssText = 'position:fixed;bottom:20px;right:20px;z-index:9999;padding:10px 18px;border-radius:9999px;font-size:13px;font-weight:600;color:#fff;font-family:"Plus Jakarta Sans",sans-serif;background:' + (type === 'success' ? '#22C55E' : '#EF4444');
        document.body.appendChild(t);
        setTimeout(() => t.remove(), 2500);
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
@endpush
