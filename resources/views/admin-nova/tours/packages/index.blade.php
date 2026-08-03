@extends('admin-nova.layouts.app')

@section('title', 'Tour Packages')

@push('styles')
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        corePlugins: { preflight: false },
        theme: {
            extend: {
                fontFamily: { jakarta: ['Plus Jakarta Sans', 'sans-serif'] },
                colors: {
                    novabg: '#F7F8FC', novablue: '#2563EB', novacyan: '#06B6D4',
                    novatext: '#000000', novamuted: '#000000', novaborder: '#E5E7EB',
                    novasuccess: '#22C55E', novawarning: '#F59E0B', novadanger: '#EF4444',
                },
            },
        },
    };
</script>
<style>
    #tpPage, #tpPage *, #tpPage *::before, #tpPage *::after { box-sizing: border-box; }
    #tpPage { font-family: 'Plus Jakarta Sans', sans-serif; }
    #tpPage h1, #tpPage h2, #tpPage h3, #tpPage p { margin: 0; padding: 0; }
    #tpPage a { text-decoration: none; color: inherit; }
    #tpPage button { font: inherit; color: inherit; background: none; border: none; cursor: pointer; padding: 0; }
    #tpPage svg { display: block; }
    #tpPage .tt-fade-in { animation: tpFadeIn .5s ease both; }
    @keyframes tpFadeIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }

    #tpPage .tpn-card { transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease; }
    #tpPage .tpn-card:hover { transform: translateY(-4px); box-shadow: 0 20px 40px -14px rgba(37,99,235,.2); border-color: #DBEAFE; }
    #tpPage .tpn-card-img img { transition: transform .4s ease; }
    #tpPage .tpn-card:hover .tpn-card-img img { transform: scale(1.06); }
    #tpPage .tpn-card-img::after {
        content: ''; position: absolute; inset: 0; pointer-events: none;
        background: linear-gradient(180deg, rgba(0,0,0,.28) 0%, rgba(0,0,0,0) 32%, rgba(0,0,0,0) 68%, rgba(0,0,0,.16) 100%);
    }
    #tpPage .tpn-price-row { background: linear-gradient(135deg, #EFF6FF 0%, #F7F8FC 100%); border: 1px solid #DBEAFE; }

    #tpPage .tpn-btn-nova {
        display: inline-flex; align-items: center; justify-content: center; gap: 6px;
        border: 1px solid #E5E7EB; border-radius: 9999px; color: #000; background: #fff;
        white-space: nowrap; transition: background .2s ease, border-color .2s ease;
    }
    #tpPage .tpn-btn-nova:hover { background: #F7F8FC; }
    #tpPage .tpn-btn-primary { background: #2563EB; color: #fff; border-color: #2563EB; }
    #tpPage .tpn-btn-primary:hover { background: #1D4ED8; border-color: #1D4ED8; }
    #tpPage .tpn-icon-btn {
        width: 30px; height: 30px; border-radius: 9999px; border: 1px solid #E5E7EB;
        display: inline-flex; align-items: center; justify-content: center; background: #fff;
        transition: background .2s ease, border-color .2s ease;
    }
    #tpPage .tpn-icon-btn:hover { background: #F7F8FC; }
    #tpPage .tpn-icon-warn:hover { background: #FFFBEB; border-color: #F59E0B; color: #F59E0B; }
    #tpPage .tpn-icon-success:hover { background: #ECFDF5; border-color: #22C55E; color: #22C55E; }
    #tpPage .tpn-icon-danger:hover { background: #FEF2F2; border-color: #EF4444; color: #EF4444; }

    /* Labeled variant: same pill, but wide enough to show text next to the
       icon so status/featured is legible without hovering for the tooltip. */
    #tpPage .tpn-icon-btn.has-label { width: auto; height: 30px; padding: 0 12px; gap: 6px; font-size: 11px; font-weight: 600; }
    #tpPage .tpn-icon-warn.has-label { border-color: #FDE68A; color: #B45309; }
    #tpPage .tpn-icon-success.has-label { border-color: #BBF7D0; color: #15803D; }
    #tpPage .tpn-icon-danger.has-label { border-color: #FECACA; color: #DC2626; }
    #tpPage .tpn-icon-featured.has-label { border-color: #FDE68A; color: #B45309; background: #FFFBEB; }

    #tpPage .tpn-star-btn { color: #E5E7EB; transition: color .2s ease, transform .2s ease; }
    #tpPage .tpn-star-btn:hover { transform: scale(1.15); }
    #tpPage .tpn-star-btn.tpn-star-active { color: #F59E0B; }

    /* List / Grid view switch */
    #tpPage .tpn-view-switch { display: inline-flex; align-items: center; background: #F7F8FC; border: 1px solid #E5E7EB; border-radius: 9999px; padding: 3px; gap: 2px; }
    #tpPage .tpn-view-switch button { display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px; border-radius: 9999px; font-size: 12px; font-weight: 600; color: #000; opacity: .55; transition: background .2s ease, opacity .2s ease; }
    #tpPage .tpn-view-switch button.active { background: #fff; opacity: 1; box-shadow: 0 1px 3px rgba(0,0,0,.08); }

    /* List view row */
    #tpPage .tpn-list-row { transition: background .2s ease, border-color .2s ease; }
    #tpPage .tpn-list-row:hover { background: #F7F8FC; border-color: #DBEAFE; }

    #tpPage .tpn-details-panel { display: none; }
    #tpPage .tpn-details-panel.tpn-open { display: block; }
    #tpPage .tpn-details-toggle svg { transition: transform .2s ease; }
    #tpPage .tpn-details-toggle.tpn-open svg { transform: rotate(180deg); }

    #tpPage [data-tooltip] { position: relative; }
    #tpPage [data-tooltip]::after {
        content: attr(data-tooltip); position: absolute; bottom: calc(100% + 8px); left: 50%;
        transform: translateX(-50%) translateY(4px); background: #1F2937; color: #fff;
        font-size: 11px; font-weight: 600; line-height: 1; padding: 6px 10px; border-radius: 6px;
        white-space: nowrap; box-shadow: 0 6px 16px rgba(0,0,0,.18); opacity: 0; visibility: hidden;
        pointer-events: none; transition: opacity .15s ease, transform .15s ease, visibility .15s ease; z-index: 60;
    }
    #tpPage [data-tooltip]::before {
        content: ''; position: absolute; bottom: calc(100% + 3px); left: 50%; transform: translateX(-50%);
        border: 5px solid transparent; border-top-color: #1F2937; opacity: 0; visibility: hidden;
        pointer-events: none; transition: opacity .15s ease, visibility .15s ease; z-index: 60;
    }
    #tpPage [data-tooltip]:hover::after, #tpPage [data-tooltip]:focus-visible::after { opacity: 1; visibility: visible; transform: translateX(-50%) translateY(0); transition-delay: .25s; }
    #tpPage [data-tooltip]:hover::before, #tpPage [data-tooltip]:focus-visible::before { opacity: 1; visibility: visible; transition-delay: .25s; }
</style>
@endpush

@section('content')
<div id="tpPage" class="tt-fade-in font-jakarta">

    {{-- ============ HEADER ============ --}}
    <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
        <div>
            <h1 class="text-lg font-bold text-novatext">Tour Packages</h1>
            <p class="text-xs text-novamuted mt-1">Manage all tour packages</p>
        </div>
        <div class="flex items-center gap-2">
            <div class="tpn-view-switch" id="tpViewSwitch">
                <button type="button" data-view="grid" class="active">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
                    Grid
                </button>
                <button type="button" data-view="list">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
                    List
                </button>
            </div>
            <a href="{{ route('admin.tours.packages.create') }}" class="tpn-btn-nova tpn-btn-primary px-4 py-2.5 text-xs font-semibold">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                Add New Package
            </a>
        </div>
    </div>

    {{-- ============ KPI CARDS ============ --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 mb-5">
        <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-3">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-medium text-novamuted">Total</p>
                <div class="w-7 h-7 rounded-full bg-novablue text-white flex items-center justify-center flex-shrink-0">
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="14" rx="2"/><path d="M3 9h18M8 4v5"/></svg>
                </div>
            </div>
            <span class="block text-lg font-bold text-novatext">{{ number_format($stats['total']) }}</span>
        </div>
        <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-3">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-medium text-novamuted">Active</p>
                <div class="w-7 h-7 rounded-full bg-novasuccess text-white flex items-center justify-center flex-shrink-0">
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12.75 11.25 15 15 9.75"/></svg>
                </div>
            </div>
            <span class="block text-lg font-bold text-novatext">{{ number_format($stats['active']) }}</span>
        </div>
        <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-3">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-medium text-novamuted">Inactive</p>
                <div class="w-7 h-7 rounded-full bg-novadanger text-white flex items-center justify-center flex-shrink-0">
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                </div>
            </div>
            <span class="block text-lg font-bold text-novatext">{{ number_format($stats['inactive']) }}</span>
        </div>
        <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-3">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-medium text-novamuted">Featured</p>
                <div class="w-7 h-7 rounded-full bg-novawarning text-white flex items-center justify-center flex-shrink-0">
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="M12 2.5 15 9l7 .8-5.2 4.8L18.2 21 12 17.3 5.8 21l1.4-6.4L2 9.8 9 9l3-6.5Z"/></svg>
                </div>
            </div>
            <span class="block text-lg font-bold text-novatext">{{ number_format($stats['featured']) }}</span>
        </div>
        <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-3">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-medium text-novamuted">Pending</p>
                <div class="w-7 h-7 rounded-full bg-novacyan text-white flex items-center justify-center flex-shrink-0">
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
                </div>
            </div>
            <span class="block text-lg font-bold text-novatext">{{ number_format($stats['pending']) }}</span>
        </div>
    </div>

    {{-- ============ FILTERS ============ --}}
    <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 sm:p-5 mb-5">
        <form method="GET" action="{{ route('admin.tours.packages.index') }}">
            <div class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[220px]">
                    <label class="block text-xs font-semibold text-novamuted mb-1.5">Search packages</label>
                    <div class="relative">
                        <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-novamuted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
                        <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Name or location..."
                               class="w-full text-sm border border-novaborder rounded-full pl-10 pr-4 py-2.5">
                    </div>
                </div>
                <div class="w-40">
                    <label class="block text-xs font-semibold text-novamuted mb-1.5">Status</label>
                    <select name="status" class="w-full text-sm border border-novaborder rounded-full px-3.5 py-2.5">
                        <option value="">All status</option>
                        <option value="1" {{ ($status ?? '') == '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ ($status ?? '') === '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="w-48">
                    <label class="block text-xs font-semibold text-novamuted mb-1.5">Package Type</label>
                    <select name="package_type" class="w-full text-sm border border-novaborder rounded-full px-3.5 py-2.5">
                        <option value="">All types</option>
                        @foreach($packageTypes as $type)
                            <option value="{{ $type->packege_type }}" {{ ($packageType ?? '') == $type->packege_type ? 'selected' : '' }}>{{ ucfirst($type->packege_type) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <button type="submit" class="tpn-btn-nova tpn-btn-primary w-10 h-10" data-tooltip="Apply filters">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 5h16M7 12h10M10 19h4"/></svg>
                    </button>
                    <a href="{{ route('admin.tours.packages.index') }}" class="tpn-btn-nova w-10 h-10" data-tooltip="Reset filters">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 1 2.64 6.36M3 12v6m0-6h6"/></svg>
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- ============ PACKAGES GRID ============ --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4" id="tpGridView">
        @forelse($packages as $package)
            @php
                $approval = $package->approval_status ?? 'approved';
                $hasDetails = !empty($package->inclusions) || !empty($package->exclusions);
            @endphp
            <div class="tpn-card bg-white rounded-2xl border border-novaborder shadow-sm overflow-hidden">
                <div class="tpn-card-img relative h-44 bg-novabg overflow-hidden">
                    @if($package->images->first())
                        <img src="{{ asset('public/assets/images/' . $package->images->first()->image) }}" alt="" class="w-full h-full object-cover" style="object-fit:cover; object-position:center; width:100%; height:100%;">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-novaborder">
                            <svg class="w-10 h-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg>
                        </div>
                    @endif
                    <div class="absolute top-2 left-2 flex items-center gap-1.5 z-10">
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold js-status-badge {{ $package->status == '1' ? 'bg-emerald-500 text-white' : 'bg-slate-500 text-white' }}"
                              data-id="{{ $package->id }}" data-active-class="bg-emerald-500 text-white" data-inactive-class="bg-slate-500 text-white">
                            {{ $package->status == '1' ? 'Active' : 'Inactive' }}
                        </span>
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-amber-500 text-white js-featured-badge" data-id="{{ $package->id }}" style="{{ $package->featured == '1' ? '' : 'display:none;' }}">Featured</span>
                        @if($approval === 'pending')
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-amber-500 text-white">Pending</span>
                        @elseif($approval === 'rejected')
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-red-500 text-white">Rejected</span>
                        @endif
                    </div>
                    <button type="button" class="tpn-star-btn js-toggle-featured w-8 h-8 rounded-full bg-white/90 flex items-center justify-center absolute top-2 right-2 z-10 {{ $package->featured == '1' ? 'tpn-star-active' : '' }}"
                            data-id="{{ $package->id }}" data-featured="{{ $package->featured == '1' ? '1' : '0' }}"
                            data-tooltip="{{ $package->featured == '1' ? 'Remove from featured' : 'Mark as featured' }}">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="{{ $package->featured == '1' ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="m12 2.5 3 6.5 7 .8-5.2 4.8L18.2 21 12 17.3 5.8 21l1.4-6.4L2 9.8 9 9l3-6.5Z"/></svg>
                    </button>
                </div>

                <div class="p-4">
                    <p class="text-base font-bold text-novatext truncate">{{ $package->name }}</p>
                    <p class="text-[11px] text-novamuted mt-1 flex items-center gap-1 truncate">
                        <svg class="w-3 h-3 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21s7-6.5 7-11.5A7 7 0 0 0 5 9.5C5 14.5 12 21 12 21Z"/><circle cx="12" cy="9.5" r="2.25"/></svg>
                        {{ $package->location ? $package->location->city . ', ' . $package->location->country : '—' }}
                    </p>

                    <div class="flex items-center flex-wrap gap-1.5 mt-3">
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-blue-50 text-novablue">{{ ucfirst($package->packege_type) }}</span>
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-slate-100 text-novamuted flex items-center gap-1">
                            <svg class="w-2.5 h-2.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
                            {{ $package->duration }}
                        </span>
                    </div>

                    <div class="tpn-price-row flex items-center justify-between rounded-xl px-3 py-2 mt-3">
                        <span class="text-[10px] font-semibold uppercase tracking-wide text-novablue">Price</span>
                        <span class="text-base font-bold text-novablue">{{ $package->currceny }} {{ number_format($package->price) }}</span>
                    </div>

                    @if($approval === 'pending')
                        <div class="flex items-center gap-1.5 mt-3">
                            <form action="{{ route('admin.tours.packages.approve', $package->id) }}" method="POST" class="flex-1">
                                @csrf
                                <button type="submit" class="tpn-btn-nova w-full py-1.5 text-[11px] font-semibold" style="background:#22C55E; color:#fff; border-color:#22C55E;">
                                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                                    Approve
                                </button>
                            </form>
                            <form action="{{ route('admin.tours.packages.reject', $package->id) }}" method="POST" class="flex-1">
                                @csrf
                                <button type="submit" class="tpn-btn-nova w-full py-1.5 text-[11px] font-semibold" style="color:#EF4444; border-color:#EF4444;">
                                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                                    Reject
                                </button>
                            </form>
                        </div>
                    @endif

                    @if($hasDetails)
                        <button type="button" class="tpn-details-toggle mt-3 flex items-center gap-1 text-[11px] font-semibold text-novablue" onclick="tpToggleDetails({{ $package->id }}, this)">
                            <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                            Details
                        </button>
                        <div id="tpDetails{{ $package->id }}" class="tpn-details-panel mt-2 pt-2 border-t border-novaborder space-y-2">
                            @if(!empty($package->inclusions))
                                <div>
                                    <p class="text-[10px] font-semibold text-novasuccess mb-1">Inclusions</p>
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($package->inclusions as $incId)
                                            @if(isset($allInclusions[$incId]))
                                                <span class="px-2 py-0.5 rounded-full text-[9px] font-semibold bg-emerald-50 text-emerald-600">{{ $allInclusions[$incId]->name }}</span>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                            @if(!empty($package->exclusions))
                                <div>
                                    <p class="text-[10px] font-semibold text-novadanger mb-1">Exclusions</p>
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($package->exclusions as $excId)
                                            @if(isset($allExclusions[$excId]))
                                                <span class="px-2 py-0.5 rounded-full text-[9px] font-semibold bg-red-50 text-novadanger">{{ $allExclusions[$excId]->name }}</span>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    <div class="flex items-center gap-1.5 flex-wrap mt-4 pt-3 border-t border-novaborder">
                        <a href="{{ route('admin.tours.packages.edit', $package->id) }}" class="tpn-icon-btn" data-tooltip="Edit">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M16.474 5.408 18.592 7.526M4 20l1.11-3.92a2 2 0 0 1 .53-.9l9.9-9.9a1.5 1.5 0 0 1 2.12 0l1.06 1.06a1.5 1.5 0 0 1 0 2.12l-9.9 9.9a2 2 0 0 1-.9.53L4 20Z"/></svg>
                        </a>
                        <button type="button" class="tpn-icon-btn has-label js-toggle-status {{ $package->status == '1' ? 'tpn-icon-warn' : 'tpn-icon-success' }}"
                                data-id="{{ $package->id }}" data-status="{{ $package->status == '1' ? '1' : '0' }}">
                            @if($package->status == '1')
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="6" y="5" width="4" height="14" rx="1"/><rect x="14" y="5" width="4" height="14" rx="1"/></svg>
                            @else
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M6 4.5v15l13-7.5-13-7.5Z"/></svg>
                            @endif
                            <span>{{ $package->status == '1' ? 'Deactivate' : 'Activate' }}</span>
                        </button>
                        <button type="button" class="tpn-icon-btn has-label js-toggle-featured tpn-icon-featured"
                                data-id="{{ $package->id }}" data-featured="{{ $package->featured == '1' ? '1' : '0' }}">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="{{ $package->featured == '1' ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="m12 2.5 3 6.5 7 .8-5.2 4.8L18.2 21 12 17.3 5.8 21l1.4-6.4L2 9.8 9 9l3-6.5Z"/></svg>
                            <span>{{ $package->featured == '1' ? 'Featured' : 'Not featured' }}</span>
                        </button>
                        <form action="{{ route('admin.tours.packages.destroy', $package->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this package?')" class="ml-auto">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="tpn-icon-btn tpn-icon-danger" data-tooltip="Delete">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0-1 14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2L4 6h16Z"/></svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-16">
                <svg class="w-10 h-10 text-novaborder mx-auto mb-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg>
                <p class="text-sm font-semibold text-novatext">No packages found</p>
                <p class="text-xs text-novamuted mt-1">Try adjusting your search or filters.</p>
            </div>
        @endforelse
    </div>

    {{-- ============ PACKAGES LIST (alternate view) ============ --}}
    <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 sm:p-5" id="tpListView" style="display:none;">
        <div class="hidden lg:flex lg:items-center lg:gap-3 lg:px-4 pb-2 mb-1">
            <span class="w-14 flex-shrink-0"></span>
            <span class="lg:flex-[2] text-[10px] font-semibold uppercase tracking-wide text-novamuted">Package</span>
            <span class="lg:flex-1 text-[10px] font-semibold uppercase tracking-wide text-novamuted">Type</span>
            <span class="lg:flex-1 text-[10px] font-semibold uppercase tracking-wide text-novamuted">Price</span>
            <span class="lg:flex-1 text-[10px] font-semibold uppercase tracking-wide text-novamuted">Duration</span>
            <span class="lg:flex-1 text-[10px] font-semibold uppercase tracking-wide text-novamuted">Status</span>
        </div>
        <div class="space-y-3">
            @forelse($packages as $package)
                <div class="tpn-list-row flex flex-col rounded-2xl border border-novaborder p-3.5 lg:p-4">
                    <div class="flex flex-col lg:flex-row lg:items-center gap-2.5 lg:gap-3">
                        <div class="flex items-center gap-3 lg:contents">
                            <div class="w-14 h-14 rounded-xl bg-novabg flex-shrink-0 overflow-hidden flex items-center justify-center text-novaborder">
                                @if($package->images->first())
                                    <img src="{{ asset('public/assets/images/' . $package->images->first()->image) }}" alt="" class="w-full h-full object-cover" style="object-fit:cover; object-position:center; width:100%; height:100%;">
                                @else
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg>
                                @endif
                            </div>
                            <div class="min-w-0 lg:flex-[2]">
                                <p class="text-sm font-semibold text-novatext truncate">{{ $package->name }}</p>
                                <p class="text-[11px] text-novamuted truncate mt-0.5">{{ $package->location ? $package->location->city . ', ' . $package->location->country : '—' }}</p>
                            </div>
                        </div>

                        <div class="lg:flex-1">
                            <p class="text-[9px] text-novamuted lg:hidden">Type</p>
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-blue-50 text-novablue">{{ ucfirst($package->packege_type) }}</span>
                        </div>

                        <div class="lg:flex-1">
                            <p class="text-[9px] text-novamuted lg:hidden">Price</p>
                            <p class="text-xs font-bold text-novatext">{{ $package->currceny }} {{ number_format($package->price) }}</p>
                        </div>

                        <div class="lg:flex-1">
                            <p class="text-[9px] text-novamuted lg:hidden">Duration</p>
                            <p class="text-xs text-novamuted">{{ $package->duration }}</p>
                        </div>

                        <div class="lg:flex-1">
                            <span class="js-status-badge px-2.5 py-1 rounded-full text-[10px] font-semibold {{ $package->status == '1' ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-novamuted' }}"
                                  data-id="{{ $package->id }}" data-active-class="bg-emerald-50 text-emerald-600" data-inactive-class="bg-slate-100 text-novamuted">{{ $package->status == '1' ? 'Active' : 'Inactive' }}</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-1.5 flex-wrap mt-3 pt-3 border-t border-novaborder">
                        <a href="{{ route('admin.tours.packages.edit', $package->id) }}" class="tpn-icon-btn" data-tooltip="Edit">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M16.474 5.408 18.592 7.526M4 20l1.11-3.92a2 2 0 0 1 .53-.9l9.9-9.9a1.5 1.5 0 0 1 2.12 0l1.06 1.06a1.5 1.5 0 0 1 0 2.12l-9.9 9.9a2 2 0 0 1-.9.53L4 20Z"/></svg>
                        </a>
                        <form action="{{ route('admin.tours.packages.destroy', $package->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this package?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="tpn-icon-btn tpn-icon-danger" data-tooltip="Delete">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0-1 14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2L4 6h16Z"/></svg>
                            </button>
                        </form>
                        <button type="button" class="tpn-icon-btn has-label js-toggle-status {{ $package->status == '1' ? 'tpn-icon-warn' : 'tpn-icon-success' }}"
                                data-id="{{ $package->id }}" data-status="{{ $package->status == '1' ? '1' : '0' }}">
                            @if($package->status == '1')
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="6" y="5" width="4" height="14" rx="1"/><rect x="14" y="5" width="4" height="14" rx="1"/></svg>
                            @else
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M6 4.5v15l13-7.5-13-7.5Z"/></svg>
                            @endif
                            <span>{{ $package->status == '1' ? 'Deactivate' : 'Activate' }}</span>
                        </button>
                        <button type="button" class="tpn-icon-btn has-label js-toggle-featured tpn-icon-featured"
                                data-id="{{ $package->id }}" data-featured="{{ $package->featured == '1' ? '1' : '0' }}">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="{{ $package->featured == '1' ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="m12 2.5 3 6.5 7 .8-5.2 4.8L18.2 21 12 17.3 5.8 21l1.4-6.4L2 9.8 9 9l3-6.5Z"/></svg>
                            <span>{{ $package->featured == '1' ? 'Featured' : 'Not featured' }}</span>
                        </button>
                    </div>
                </div>
            @empty
                <div class="text-center py-16">
                    <p class="text-sm font-semibold text-novatext">No packages found</p>
                    <p class="text-xs text-novamuted mt-1">Try adjusting your search or filters.</p>
                </div>
            @endforelse
        </div>
    </div>

    @if($packages->hasPages())
        <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 sm:p-5 mt-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <p class="text-xs text-novamuted">Showing {{ $packages->firstItem() }} to {{ $packages->lastItem() }} of {{ number_format($packages->total()) }} entries</p>
                {{ $packages->withQueryString()->links() }}
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    function tpToggleDetails(id, btn) {
        var panel = document.getElementById('tpDetails' + id);
        panel.classList.toggle('tpn-open');
        btn.classList.toggle('tpn-open');
    }

    document.addEventListener('DOMContentLoaded', function () {
        const VIEW_KEY = 'tours_packages_view_nova';
        const gridView = document.getElementById('tpGridView');
        const listView = document.getElementById('tpListView');
        const switchBtns = document.querySelectorAll('#tpViewSwitch button');

        function setView(view) {
            switchBtns.forEach(b => b.classList.toggle('active', b.dataset.view === view));
            if (view === 'list') {
                gridView.style.display = 'none';
                listView.style.display = 'block';
            } else {
                gridView.style.display = 'grid';
                listView.style.display = 'none';
            }
            localStorage.setItem(VIEW_KEY, view);
        }

        switchBtns.forEach(btn => btn.addEventListener('click', () => setView(btn.dataset.view)));
        setView(localStorage.getItem(VIEW_KEY) || 'grid');

        function csrfToken() {
            return document.querySelector('meta[name="csrf-token"]').content;
        }

        function toast(message) {
            const el = document.createElement('div');
            el.className = 'alert alert-success alert-dismissible fade show position-fixed top-0 end-0 m-3';
            el.style.zIndex = '9999';
            el.innerHTML = `${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
            document.body.appendChild(el);
            setTimeout(() => el.remove(), 3000);
        }

        document.querySelectorAll('.js-toggle-status').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.dataset.id;
                const url = "{{ route('admin.tours.packages.toggle-status', ['tour' => '__ID__']) }}".replace('__ID__', id);
                fetch(url, { method: 'PATCH', headers: { 'X-CSRF-TOKEN': csrfToken(), 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(r => r.json())
                    .then(data => {
                        if (!data.success) return;
                        const nowActive = this.dataset.status !== '1';
                        document.querySelectorAll(`.js-toggle-status[data-id="${id}"]`).forEach(b => {
                            b.dataset.status = nowActive ? '1' : '0';
                            b.classList.toggle('tpn-icon-warn', nowActive);
                            b.classList.toggle('tpn-icon-success', !nowActive);
                            const svg = b.querySelector('svg');
                            if (svg) svg.innerHTML = nowActive
                                ? '<rect x="6" y="5" width="4" height="14" rx="1"/><rect x="14" y="5" width="4" height="14" rx="1"/>'
                                : '<path d="M6 4.5v15l13-7.5-13-7.5Z"/>';
                            const label = b.querySelector('span');
                            if (label) label.textContent = nowActive ? 'Deactivate' : 'Activate';
                        });
                        document.querySelectorAll(`.js-status-badge[data-id="${id}"]`).forEach(badge => {
                            badge.textContent = nowActive ? 'Active' : 'Inactive';
                            badge.className = 'px-2.5 py-1 rounded-full text-[10px] font-semibold js-status-badge '
                                + (nowActive ? badge.dataset.activeClass : badge.dataset.inactiveClass);
                        });
                        toast(data.message || 'Status updated');
                    })
                    .catch(() => toast('Something went wrong'));
            });
        });

        document.querySelectorAll('.js-toggle-featured').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.dataset.id;
                const url = "{{ route('admin.tours.packages.toggle-featured', ['tour' => '__ID__']) }}".replace('__ID__', id);
                fetch(url, { method: 'PATCH', headers: { 'X-CSRF-TOKEN': csrfToken(), 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(r => r.json())
                    .then(data => {
                        if (!data.success) return;
                        const nowFeatured = this.dataset.featured !== '1';
                        document.querySelectorAll(`.js-toggle-featured[data-id="${id}"]`).forEach(b => {
                            b.dataset.featured = nowFeatured ? '1' : '0';
                            const svg = b.querySelector('svg');
                            if (svg) svg.setAttribute('fill', nowFeatured ? 'currentColor' : 'none');
                            b.classList.toggle('tpn-star-active', nowFeatured);
                            const label = b.querySelector('span');
                            if (label) label.textContent = nowFeatured ? 'Featured' : 'Not featured';
                            if (b.dataset.tooltip !== undefined) b.setAttribute('data-tooltip', nowFeatured ? 'Remove from featured' : 'Mark as featured');
                        });
                        document.querySelectorAll(`.js-featured-badge[data-id="${id}"]`).forEach(badge => {
                            badge.style.display = nowFeatured ? '' : 'none';
                        });
                        toast(data.message || 'Featured status updated');
                    })
                    .catch(() => toast('Something went wrong'));
            });
        });
    });
</script>
@endpush
