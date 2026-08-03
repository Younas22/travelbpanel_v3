@extends('admin-nova.layouts.app')

@section('title', 'Hotels')

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
    #htPage, #htPage *, #htPage *::before, #htPage *::after { box-sizing: border-box; }
    #htPage { font-family: 'Plus Jakarta Sans', sans-serif; }
    #htPage h1, #htPage h2, #htPage h3, #htPage p { margin: 0; padding: 0; }
    #htPage a { text-decoration: none; color: inherit; }
    #htPage button { font: inherit; color: inherit; background: none; border: none; cursor: pointer; padding: 0; }
    #htPage svg { display: block; }
    #htPage .tt-fade-in { animation: htFadeIn .5s ease both; }
    @keyframes htFadeIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }

    #htPage .ht-card { transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease; }
    #htPage .ht-card:hover { transform: translateY(-4px); box-shadow: 0 20px 40px -14px rgba(37,99,235,.2); border-color: #DBEAFE; }
    #htPage .ht-card-img img { transition: transform .4s ease; }
    #htPage .ht-card:hover .ht-card-img img { transform: scale(1.06); }
    #htPage .ht-card-img::after {
        content: ''; position: absolute; inset: 0; pointer-events: none;
        background: linear-gradient(180deg, rgba(0,0,0,.28) 0%, rgba(0,0,0,0) 32%, rgba(0,0,0,0) 68%, rgba(0,0,0,.16) 100%);
    }
    #htPage .ht-rooms-row { background: linear-gradient(135deg, #EFF6FF 0%, #F7F8FC 100%); border: 1px solid #DBEAFE; }

    #htPage .ht-btn-nova {
        display: inline-flex; align-items: center; justify-content: center; gap: 6px;
        border: 1px solid #E5E7EB; border-radius: 9999px; color: #000; background: #fff;
        white-space: nowrap; transition: background .2s ease, border-color .2s ease;
    }
    #htPage .ht-btn-nova:hover { background: #F7F8FC; }
    #htPage .ht-btn-primary { background: #2563EB; color: #fff; border-color: #2563EB; }
    #htPage .ht-btn-primary:hover { background: #1D4ED8; border-color: #1D4ED8; }
    #htPage .ht-icon-btn {
        width: 30px; height: 30px; border-radius: 9999px; border: 1px solid #E5E7EB;
        display: inline-flex; align-items: center; justify-content: center; background: #fff;
        transition: background .2s ease, border-color .2s ease;
    }
    #htPage .ht-icon-btn:hover { background: #F7F8FC; }
    #htPage .ht-icon-warn:hover { background: #FFFBEB; border-color: #F59E0B; color: #F59E0B; }
    #htPage .ht-icon-success:hover { background: #ECFDF5; border-color: #22C55E; color: #22C55E; }
    #htPage .ht-icon-danger:hover { background: #FEF2F2; border-color: #EF4444; color: #EF4444; }

    /* Labeled variant: same pill, but wide enough to show text next to the
       icon so status/featured is legible without hovering for the tooltip. */
    #htPage .ht-icon-btn.has-label { width: auto; height: 30px; padding: 0 12px; gap: 6px; font-size: 11px; font-weight: 600; }
    #htPage .ht-icon-warn.has-label { border-color: #FDE68A; color: #B45309; }
    #htPage .ht-icon-success.has-label { border-color: #BBF7D0; color: #15803D; }
    #htPage .ht-icon-danger.has-label { border-color: #FECACA; color: #DC2626; }
    #htPage .ht-icon-featured.has-label { border-color: #FDE68A; color: #B45309; background: #FFFBEB; }

    #htPage .ht-star-btn { color: #E5E7EB; transition: color .2s ease, transform .2s ease; }
    #htPage .ht-star-btn:hover { transform: scale(1.15); }
    #htPage .ht-star-btn.ht-star-active { color: #F59E0B; }

    /* List / Grid view switch */
    #htPage .ht-view-switch { display: inline-flex; align-items: center; background: #F7F8FC; border: 1px solid #E5E7EB; border-radius: 9999px; padding: 3px; gap: 2px; }
    #htPage .ht-view-switch button { display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px; border-radius: 9999px; font-size: 12px; font-weight: 600; color: #000; opacity: .55; transition: background .2s ease, opacity .2s ease; }
    #htPage .ht-view-switch button.active { background: #fff; opacity: 1; box-shadow: 0 1px 3px rgba(0,0,0,.08); }

    /* List view row */
    #htPage .ht-list-row { transition: background .2s ease, border-color .2s ease; }
    #htPage .ht-list-row:hover { background: #F7F8FC; border-color: #DBEAFE; }

    #htPage [data-tooltip] { position: relative; }
    #htPage [data-tooltip]::after {
        content: attr(data-tooltip); position: absolute; bottom: calc(100% + 8px); left: 50%;
        transform: translateX(-50%) translateY(4px); background: #1F2937; color: #fff;
        font-size: 11px; font-weight: 600; line-height: 1; padding: 6px 10px; border-radius: 6px;
        white-space: nowrap; box-shadow: 0 6px 16px rgba(0,0,0,.18); opacity: 0; visibility: hidden;
        pointer-events: none; transition: opacity .15s ease, transform .15s ease, visibility .15s ease; z-index: 60;
    }
    #htPage [data-tooltip]::before {
        content: ''; position: absolute; bottom: calc(100% + 3px); left: 50%; transform: translateX(-50%);
        border: 5px solid transparent; border-top-color: #1F2937; opacity: 0; visibility: hidden;
        pointer-events: none; transition: opacity .15s ease, visibility .15s ease; z-index: 60;
    }
    #htPage [data-tooltip]:hover::after, #htPage [data-tooltip]:focus-visible::after { opacity: 1; visibility: visible; transform: translateX(-50%) translateY(0); transition-delay: .25s; }
    #htPage [data-tooltip]:hover::before, #htPage [data-tooltip]:focus-visible::before { opacity: 1; visibility: visible; transition-delay: .25s; }
</style>
@endpush

@section('content')
<div id="htPage" class="tt-fade-in font-jakarta">

    {{-- ============ HEADER ============ --}}
    <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
        <div>
            <h1 class="text-lg font-bold text-novatext">Hotels</h1>
            <p class="text-xs text-novamuted mt-1">Manage all hotels</p>
        </div>
        <div class="flex items-center gap-2">
            <div class="ht-view-switch" id="htViewSwitch">
                <button type="button" data-view="grid" class="active">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
                    Grid
                </button>
                <button type="button" data-view="list">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
                    List
                </button>
            </div>
            <a href="{{ route('admin.hotels.create') }}" class="ht-btn-nova ht-btn-primary px-4 py-2.5 text-xs font-semibold">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                Add New Hotel
            </a>
        </div>
    </div>

    {{-- ============ KPI CARDS ============ --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 mb-5">
        <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-3">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-medium text-novamuted">Total</p>
                <div class="w-7 h-7 rounded-full bg-novablue text-white flex items-center justify-center flex-shrink-0">
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21V7a1 1 0 0 1 1-1h6v15M14 21V11a1 1 0 0 1 1-1h5a1 1 0 0 1 1 1v10"/><path d="M7 9h.01M7 12h.01M7 15h.01M17 14h.01M17 17h.01"/></svg>
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
                <p class="text-xs font-medium text-novamuted">Hotels (type)</p>
                <div class="w-7 h-7 rounded-full bg-novacyan text-white flex items-center justify-center flex-shrink-0">
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="3" width="16" height="18" rx="1.5"/><path d="M9 21v-4h6v4"/></svg>
                </div>
            </div>
            <span class="block text-lg font-bold text-novatext">{{ number_format($stats['hotel']) }}</span>
        </div>
        <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-3">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-medium text-novamuted">Pending</p>
                <div class="w-7 h-7 rounded-full bg-novawarning text-white flex items-center justify-center flex-shrink-0">
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
                </div>
            </div>
            <span class="block text-lg font-bold text-novatext">{{ number_format($stats['pending']) }}</span>
        </div>
    </div>

    {{-- ============ FILTERS ============ --}}
    <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 sm:p-5 mb-5">
        <form method="GET" action="{{ route('admin.hotels.index') }}">
            <div class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[220px]">
                    <label class="block text-xs font-semibold text-novamuted mb-1.5">Search hotels</label>
                    <div class="relative">
                        <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-novamuted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
                        <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Name, address or location..."
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
                    <label class="block text-xs font-semibold text-novamuted mb-1.5">Type</label>
                    <select name="type" class="w-full text-sm border border-novaborder rounded-full px-3.5 py-2.5">
                        <option value="">All types</option>
                        <option value="hotel" {{ ($type ?? '') == 'hotel' ? 'selected' : '' }}>Hotel</option>
                        <option value="guest house" {{ ($type ?? '') == 'guest house' ? 'selected' : '' }}>Guest House</option>
                        <option value="resort" {{ ($type ?? '') == 'resort' ? 'selected' : '' }}>Resort</option>
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <button type="submit" class="ht-btn-nova ht-btn-primary w-10 h-10" data-tooltip="Apply filters">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 5h16M7 12h10M10 19h4"/></svg>
                    </button>
                    <a href="{{ route('admin.hotels.index') }}" class="ht-btn-nova w-10 h-10" data-tooltip="Reset filters">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 1 2.64 6.36M3 12v6m0-6h6"/></svg>
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- ============ HOTELS GRID ============ --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4" id="htGridView">
        @forelse($hotels as $hotel)
            @php $approval = $hotel->approval_status ?? 'approved'; @endphp
            <div class="ht-card bg-white rounded-2xl border border-novaborder shadow-sm overflow-hidden">
                <div class="ht-card-img relative h-44 bg-novabg overflow-hidden">
                    @if($hotel->images->first())
                        <img src="{{ asset('public/assets/images/' . $hotel->images->first()->image_path) }}" alt="" class="w-full h-full object-cover" style="object-fit:cover; object-position:center; width:100%; height:100%;">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-novaborder">
                            <svg class="w-10 h-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21V7a1 1 0 0 1 1-1h6v15M14 21V11a1 1 0 0 1 1-1h5a1 1 0 0 1 1 1v10"/></svg>
                        </div>
                    @endif
                    <div class="absolute top-2 left-2 flex items-center gap-1.5 z-10">
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold js-status-badge {{ $hotel->status ? 'bg-emerald-500 text-white' : 'bg-slate-500 text-white' }}"
                              data-id="{{ $hotel->id }}" data-active-class="bg-emerald-500 text-white" data-inactive-class="bg-slate-500 text-white">
                            {{ $hotel->status ? 'Active' : 'Inactive' }}
                        </span>
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-amber-500 text-white js-featured-badge" data-id="{{ $hotel->id }}" style="{{ $hotel->featured == '1' ? '' : 'display:none;' }}">Featured</span>
                        @if($approval === 'pending')
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-amber-500 text-white">Pending</span>
                        @elseif($approval === 'rejected')
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-red-500 text-white">Rejected</span>
                        @endif
                    </div>
                    <button type="button" class="ht-star-btn js-toggle-featured w-8 h-8 rounded-full bg-white/90 flex items-center justify-center absolute top-2 right-2 z-10 {{ $hotel->featured == '1' ? 'ht-star-active' : '' }}"
                            data-id="{{ $hotel->id }}" data-featured="{{ $hotel->featured == '1' ? '1' : '0' }}"
                            data-tooltip="{{ $hotel->featured == '1' ? 'Remove from featured' : 'Mark as featured' }}">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="{{ $hotel->featured == '1' ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="m12 2.5 3 6.5 7 .8-5.2 4.8L18.2 21 12 17.3 5.8 21l1.4-6.4L2 9.8 9 9l3-6.5Z"/></svg>
                    </button>
                </div>

                <div class="p-4">
                    <p class="text-base font-bold text-novatext truncate">{{ $hotel->name }}</p>
                    <p class="text-[11px] text-novamuted mt-1 flex items-center gap-1 truncate">
                        <svg class="w-3 h-3 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21s7-6.5 7-11.5A7 7 0 0 0 5 9.5C5 14.5 12 21 12 21Z"/><circle cx="12" cy="9.5" r="2.25"/></svg>
                        {{ $hotel->location ? $hotel->location->city . ', ' . $hotel->location->country : ($hotel->address ?? '—') }}
                    </p>

                    <div class="flex items-center flex-wrap gap-1.5 mt-3">
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-blue-50 text-novablue">{{ ucfirst($hotel->type) }}</span>
                        @if($hotel->phone || $hotel->email)
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-slate-100 text-novamuted truncate max-w-[140px]">{{ $hotel->phone ?? $hotel->email }}</span>
                        @endif
                    </div>

                    <div class="ht-rooms-row flex items-center justify-between rounded-xl px-3 py-2 mt-3">
                        <span class="text-[10px] font-semibold uppercase tracking-wide text-novablue flex items-center gap-1">
                            <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="3" width="16" height="18" rx="1.5"/><path d="M9 21v-4h6v4"/></svg>
                            Total Rooms
                        </span>
                        <span class="text-base font-bold text-novablue">{{ $hotel->total_rooms ?? 0 }}</span>
                    </div>

                    @if($approval === 'pending')
                        <div class="flex items-center gap-1.5 mt-3">
                            <form action="{{ route('admin.hotels.approve', $hotel) }}" method="POST" class="flex-1">
                                @csrf
                                <button type="submit" class="ht-btn-nova w-full py-1.5 text-[11px] font-semibold" style="background:#22C55E; color:#fff; border-color:#22C55E;">
                                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                                    Approve
                                </button>
                            </form>
                            <form action="{{ route('admin.hotels.reject', $hotel) }}" method="POST" class="flex-1">
                                @csrf
                                <button type="submit" class="ht-btn-nova w-full py-1.5 text-[11px] font-semibold" style="color:#EF4444; border-color:#EF4444;">
                                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                                    Reject
                                </button>
                            </form>
                        </div>
                    @endif

                    <div class="flex items-center gap-1.5 flex-wrap mt-4 pt-3 border-t border-novaborder">
                        <a href="{{ route('admin.hotels.edit', $hotel) }}" class="ht-icon-btn" data-tooltip="Edit">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M16.474 5.408 18.592 7.526M4 20l1.11-3.92a2 2 0 0 1 .53-.9l9.9-9.9a1.5 1.5 0 0 1 2.12 0l1.06 1.06a1.5 1.5 0 0 1 0 2.12l-9.9 9.9a2 2 0 0 1-.9.53L4 20Z"/></svg>
                        </a>
                        <button type="button" class="ht-icon-btn has-label js-toggle-status {{ $hotel->status ? 'ht-icon-warn' : 'ht-icon-success' }}"
                                data-id="{{ $hotel->id }}" data-status="{{ $hotel->status ? '1' : '0' }}">
                            @if($hotel->status)
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="6" y="5" width="4" height="14" rx="1"/><rect x="14" y="5" width="4" height="14" rx="1"/></svg>
                            @else
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M6 4.5v15l13-7.5-13-7.5Z"/></svg>
                            @endif
                            <span>{{ $hotel->status ? 'Deactivate' : 'Activate' }}</span>
                        </button>
                        <button type="button" class="ht-icon-btn has-label js-toggle-featured ht-icon-featured"
                                data-id="{{ $hotel->id }}" data-featured="{{ $hotel->featured == '1' ? '1' : '0' }}">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="{{ $hotel->featured == '1' ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="m12 2.5 3 6.5 7 .8-5.2 4.8L18.2 21 12 17.3 5.8 21l1.4-6.4L2 9.8 9 9l3-6.5Z"/></svg>
                            <span>{{ $hotel->featured == '1' ? 'Featured' : 'Not featured' }}</span>
                        </button>
                        <button type="button" class="ht-icon-btn ht-icon-danger js-delete-hotel ml-auto" data-id="{{ $hotel->id }}" data-tooltip="Delete">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0-1 14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2L4 6h16Z"/></svg>
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-16">
                <svg class="w-10 h-10 text-novaborder mx-auto mb-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21V7a1 1 0 0 1 1-1h6v15M14 21V11a1 1 0 0 1 1-1h5a1 1 0 0 1 1 1v10"/></svg>
                <p class="text-sm font-semibold text-novatext">No hotels found</p>
                <p class="text-xs text-novamuted mt-1">Try adjusting your search or filters.</p>
            </div>
        @endforelse
    </div>

    {{-- ============ HOTELS LIST (alternate view) ============ --}}
    <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 sm:p-5" id="htListView" style="display:none;">
        <div class="hidden lg:flex lg:items-center lg:gap-3 lg:px-4 pb-2 mb-1">
            <span class="w-14 flex-shrink-0"></span>
            <span class="lg:flex-[2] text-[10px] font-semibold uppercase tracking-wide text-novamuted">Hotel</span>
            <span class="lg:flex-1 text-[10px] font-semibold uppercase tracking-wide text-novamuted">Type</span>
            <span class="lg:flex-1 text-[10px] font-semibold uppercase tracking-wide text-novamuted">Rooms</span>
            <span class="lg:flex-1 text-[10px] font-semibold uppercase tracking-wide text-novamuted">Contact</span>
            <span class="lg:flex-1 text-[10px] font-semibold uppercase tracking-wide text-novamuted">Status</span>
        </div>
        <div class="space-y-3">
            @forelse($hotels as $hotel)
                <div class="ht-list-row flex flex-col rounded-2xl border border-novaborder p-3.5 lg:p-4">
                    <div class="flex flex-col lg:flex-row lg:items-center gap-2.5 lg:gap-3">
                        <div class="flex items-center gap-3 lg:contents">
                            <div class="w-14 h-14 rounded-xl bg-novabg flex-shrink-0 overflow-hidden flex items-center justify-center text-novaborder">
                                @if($hotel->images->first())
                                    <img src="{{ asset('public/assets/images/' . $hotel->images->first()->image_path) }}" alt="" class="w-full h-full object-cover" style="object-fit:cover; object-position:center; width:100%; height:100%;">
                                @else
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21V7a1 1 0 0 1 1-1h6v15M14 21V11a1 1 0 0 1 1-1h5a1 1 0 0 1 1 1v10"/></svg>
                                @endif
                            </div>
                            <div class="min-w-0 lg:flex-[2]">
                                <p class="text-sm font-semibold text-novatext truncate">{{ $hotel->name }}</p>
                                <p class="text-[11px] text-novamuted truncate mt-0.5">{{ $hotel->location ? $hotel->location->city . ', ' . $hotel->location->country : ($hotel->address ?? '—') }}</p>
                            </div>
                        </div>

                        <div class="lg:flex-1">
                            <p class="text-[9px] text-novamuted lg:hidden">Type</p>
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-blue-50 text-novablue">{{ ucfirst($hotel->type) }}</span>
                        </div>

                        <div class="lg:flex-1">
                            <p class="text-[9px] text-novamuted lg:hidden">Rooms</p>
                            <p class="text-xs font-semibold text-novatext">{{ $hotel->total_rooms ?? 0 }}</p>
                        </div>

                        <div class="lg:flex-1 min-w-0">
                            <p class="text-[9px] text-novamuted lg:hidden">Contact</p>
                            <p class="text-xs text-novamuted truncate">{{ $hotel->phone ?? '—' }}</p>
                        </div>

                        <div class="lg:flex-1">
                            <span class="js-status-badge px-2.5 py-1 rounded-full text-[10px] font-semibold {{ $hotel->status ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-novamuted' }}"
                                  data-id="{{ $hotel->id }}" data-active-class="bg-emerald-50 text-emerald-600" data-inactive-class="bg-slate-100 text-novamuted">{{ $hotel->status ? 'Active' : 'Inactive' }}</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-1.5 flex-wrap mt-3 pt-3 border-t border-novaborder">
                        <a href="{{ route('admin.hotels.edit', $hotel) }}" class="ht-icon-btn" data-tooltip="Edit">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M16.474 5.408 18.592 7.526M4 20l1.11-3.92a2 2 0 0 1 .53-.9l9.9-9.9a1.5 1.5 0 0 1 2.12 0l1.06 1.06a1.5 1.5 0 0 1 0 2.12l-9.9 9.9a2 2 0 0 1-.9.53L4 20Z"/></svg>
                        </a>
                        <button type="button" class="ht-icon-btn has-label js-toggle-status {{ $hotel->status ? 'ht-icon-warn' : 'ht-icon-success' }}"
                                data-id="{{ $hotel->id }}" data-status="{{ $hotel->status ? '1' : '0' }}">
                            @if($hotel->status)
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="6" y="5" width="4" height="14" rx="1"/><rect x="14" y="5" width="4" height="14" rx="1"/></svg>
                            @else
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M6 4.5v15l13-7.5-13-7.5Z"/></svg>
                            @endif
                            <span>{{ $hotel->status ? 'Deactivate' : 'Activate' }}</span>
                        </button>
                        <button type="button" class="ht-icon-btn has-label js-toggle-featured ht-icon-featured"
                                data-id="{{ $hotel->id }}" data-featured="{{ $hotel->featured == '1' ? '1' : '0' }}">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="{{ $hotel->featured == '1' ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="m12 2.5 3 6.5 7 .8-5.2 4.8L18.2 21 12 17.3 5.8 21l1.4-6.4L2 9.8 9 9l3-6.5Z"/></svg>
                            <span>{{ $hotel->featured == '1' ? 'Featured' : 'Not featured' }}</span>
                        </button>
                        <button type="button" class="ht-icon-btn ht-icon-danger js-delete-hotel ml-auto" data-id="{{ $hotel->id }}" data-tooltip="Delete">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0-1 14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2L4 6h16Z"/></svg>
                        </button>
                    </div>
                </div>
            @empty
                <div class="text-center py-16">
                    <p class="text-sm font-semibold text-novatext">No hotels found</p>
                    <p class="text-xs text-novamuted mt-1">Try adjusting your search or filters.</p>
                </div>
            @endforelse
        </div>
    </div>

    @if($hotels->hasPages())
        <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 sm:p-5 mt-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <p class="text-xs text-novamuted">Showing {{ $hotels->firstItem() }} to {{ $hotels->lastItem() }} of {{ number_format($hotels->total()) }} entries</p>
                {{ $hotels->withQueryString()->links() }}
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const VIEW_KEY = 'hotels_view_nova';
        const gridView = document.getElementById('htGridView');
        const listView = document.getElementById('htListView');
        const switchBtns = document.querySelectorAll('#htViewSwitch button');

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
                fetch(`{{ url('admin/hotels') }}/${id}/toggle-status`, {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken(), 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(r => r.json())
                .then(data => {
                    if (!data.success) return;
                    const nowActive = this.dataset.status !== '1';
                    document.querySelectorAll(`.js-toggle-status[data-id="${id}"]`).forEach(b => {
                        b.dataset.status = nowActive ? '1' : '0';
                        b.classList.toggle('ht-icon-warn', nowActive);
                        b.classList.toggle('ht-icon-success', !nowActive);
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
                fetch(`{{ url('admin/hotels') }}/${id}/toggle-featured`, {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken(), 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(r => r.json())
                .then(data => {
                    if (!data.success) return;
                    const nowFeatured = this.dataset.featured !== '1';
                    document.querySelectorAll(`.js-toggle-featured[data-id="${id}"]`).forEach(b => {
                        b.dataset.featured = nowFeatured ? '1' : '0';
                        const svg = b.querySelector('svg');
                        if (svg) svg.setAttribute('fill', nowFeatured ? 'currentColor' : 'none');
                        b.classList.toggle('ht-star-active', nowFeatured);
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

        document.querySelectorAll('.js-delete-hotel').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.dataset.id;
                if (!confirm('Are you sure you want to delete this hotel? All related data will be removed.')) return;
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `{{ url('admin/hotels') }}/${id}`;
                form.innerHTML = `
                    <input type="hidden" name="_token" value="${csrfToken()}">
                    <input type="hidden" name="_method" value="DELETE">
                `;
                document.body.appendChild(form);
                form.submit();
            });
        });
    });
</script>
@endpush
