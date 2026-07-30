@extends('admin-nova.layouts.app')

@section('title', 'Agents Management')

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
    #agPage, #agPage *, #agPage *::before, #agPage *::after { box-sizing: border-box; }
    #agPage { font-family: 'Plus Jakarta Sans', sans-serif; }
    #agPage h1, #agPage h2, #agPage p { margin: 0; padding: 0; }
    #agPage a { text-decoration: none; color: inherit; }
    #agPage button { font: inherit; color: inherit; background: none; border: none; cursor: pointer; padding: 0; }
    #agPage svg { display: block; }

    #agPage .tt-fade-in { animation: agFadeIn .5s ease both; }
    @keyframes agFadeIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }

    #agPage .tt-row { transition: background .2s ease, border-color .2s ease, transform .2s ease; }
    #agPage .tt-row:hover { background: #F7F8FC; border-color: #DBEAFE; transform: translateX(2px); }

    /* Plain CSS backing for every pill button — same reasoning as the
       bookings/suppliers pages: Tailwind CDN utility classes compile at
       runtime and have a window where they simply aren't generated yet. */
    #agPage .ag-btn-nova {
        display: inline-flex; align-items: center; justify-content: center; gap: 6px;
        border: 1px solid #E5E7EB; border-radius: 9999px; color: #000; background: #fff;
        white-space: nowrap; transition: background .2s ease, border-color .2s ease;
    }
    #agPage .ag-btn-nova:hover { background: #F7F8FC; }
    #agPage .ag-btn-primary { background: #2563EB; color: #fff; border-color: #2563EB; }
    #agPage .ag-btn-primary:hover { background: #1D4ED8; border-color: #1D4ED8; }
    #agPage .ag-icon-btn {
        width: 30px; height: 30px; border-radius: 9999px; border: 1px solid #E5E7EB;
        display: inline-flex; align-items: center; justify-content: center; background: #fff;
        transition: background .2s ease, border-color .2s ease;
    }
    #agPage .ag-icon-btn:hover { background: #F7F8FC; }
    #agPage .ag-icon-approve:hover { background: #ECFDF5; border-color: #22C55E; color: #22C55E; }
    #agPage .ag-icon-suspend:hover { background: #FFFBEB; border-color: #F59E0B; color: #F59E0B; }
    #agPage .ag-icon-danger:hover { background: #FEF2F2; border-color: #EF4444; color: #EF4444; }

    /* Material-style hover tooltip — replaces the native browser title=""
       tooltip (unstyled, slow, inconsistent across browsers). Label goes in
       data-tooltip instead of title so only this custom tooltip shows. Small
       dark pill above the trigger with a short show-delay and a fade +
       rise-in transition. */
    #agPage [data-tooltip] { position: relative; }
    #agPage [data-tooltip]::after {
        content: attr(data-tooltip);
        position: absolute;
        bottom: calc(100% + 8px);
        left: 50%;
        transform: translateX(-50%) translateY(4px);
        background: #1F2937;
        color: #fff;
        font-size: 11px;
        font-weight: 600;
        line-height: 1;
        padding: 6px 10px;
        border-radius: 6px;
        white-space: nowrap;
        box-shadow: 0 6px 16px rgba(0,0,0,.18);
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
        transition: opacity .15s ease, transform .15s ease, visibility .15s ease;
        z-index: 60;
    }
    #agPage [data-tooltip]::before {
        content: '';
        position: absolute;
        bottom: calc(100% + 3px);
        left: 50%;
        transform: translateX(-50%);
        border: 5px solid transparent;
        border-top-color: #1F2937;
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
        transition: opacity .15s ease, visibility .15s ease;
        z-index: 60;
    }
    #agPage [data-tooltip]:hover::after, #agPage [data-tooltip]:focus-visible::after {
        opacity: 1; visibility: visible; transform: translateX(-50%) translateY(0); transition-delay: .25s;
    }
    #agPage [data-tooltip]:hover::before, #agPage [data-tooltip]:focus-visible::before {
        opacity: 1; visibility: visible; transition-delay: .25s;
    }
</style>
@endpush

@section('content')
@php
    $avatarPalette = [
        ['bg' => '#DBEAFE', 'text' => '#1D4ED8'],
        ['bg' => '#DCFCE7', 'text' => '#15803D'],
        ['bg' => '#FEF3C7', 'text' => '#B45309'],
        ['bg' => '#FCE7F3', 'text' => '#BE185D'],
        ['bg' => '#EDE9FE', 'text' => '#6D28D9'],
    ];
    $statusStyle = [
        'active'    => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600', 'label' => 'Active'],
        'pending'   => ['bg' => 'bg-amber-50',   'text' => 'text-amber-600',   'label' => 'Pending'],
        'suspended' => ['bg' => 'bg-red-50',     'text' => 'text-novadanger', 'label' => 'Suspended'],
    ];
@endphp

<div id="agPage" class="tt-fade-in font-jakarta">

    {{-- ============ HEADER ============ --}}
    <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
        <div>
            <h1 class="text-lg font-bold text-novatext">Agents Management</h1>
            <p class="text-xs text-novamuted mt-1">Manage B2B travel agents</p>
        </div>
        <a href="{{ route('admin.agents.create') }}" class="ag-btn-nova ag-btn-primary px-4 py-2.5 text-xs font-semibold">
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
            Add Agent
        </a>
    </div>

    {{-- ============ KPI CARDS ============ --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
        <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-3">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-medium text-novamuted">Total Agents</p>
                <div class="w-7 h-7 rounded-full bg-novablue text-white flex items-center justify-center flex-shrink-0">
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="3.25"/><path d="M2.75 19c.5-3.2 3-5.25 6.25-5.25S15.25 15.8 15.75 19"/><circle cx="17" cy="9" r="2.5"/><path d="M15 13.75c2.4.2 4.2 1.9 4.75 5.25"/></svg>
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
                <p class="text-xs font-medium text-novamuted">Pending</p>
                <div class="w-7 h-7 rounded-full bg-novawarning text-white flex items-center justify-center flex-shrink-0">
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
                </div>
            </div>
            <span class="block text-lg font-bold text-novatext">{{ number_format($stats['pending']) }}</span>
        </div>
        <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-3">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-medium text-novamuted">Suspended</p>
                <div class="w-7 h-7 rounded-full bg-novadanger text-white flex items-center justify-center flex-shrink-0">
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                </div>
            </div>
            <span class="block text-lg font-bold text-novatext">{{ number_format($stats['suspended']) }}</span>
        </div>
    </div>

    {{-- ============ FILTERS (real GET filters, server-side) ============ --}}
    <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 sm:p-5 mb-5">
        <form method="GET" action="{{ route('admin.agents.index') }}">
            <div class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[220px]">
                    <label class="block text-xs font-semibold text-novamuted mb-1.5">Search agents</label>
                    <div class="relative">
                        <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-novamuted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Name, email, company, code..."
                               class="w-full text-sm border border-novaborder rounded-full pl-10 pr-4 py-2.5">
                    </div>
                </div>
                <div class="w-40">
                    <label class="block text-xs font-semibold text-novamuted mb-1.5">Status</label>
                    <select name="status" class="w-full text-sm border border-novaborder rounded-full px-3.5 py-2.5">
                        <option value="">All status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <button type="submit" class="ag-btn-nova ag-btn-primary w-10 h-10" data-tooltip="Apply filters">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 5h16M7 12h10M10 19h4"/></svg>
                    </button>
                    <a href="{{ route('admin.agents.index') }}" class="ag-btn-nova w-10 h-10" data-tooltip="Reset filters">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 1 2.64 6.36M3 12v6m0-6h6"/></svg>
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- ============ AGENT LIST ============ --}}
    <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 sm:p-5">
        <div class="flex items-center gap-2 mb-4">
            <h2 class="text-sm font-semibold text-novatext">Agents</h2>
            <span class="text-xs text-novamuted">{{ number_format($agents->total()) }} total</span>
        </div>

        {{-- Plain "gap-3"/"px-4" would collide with Bootstrap's own
             .gap-3{gap:1rem!important} (16px vs Tailwind's 12px) and
             .px-4{padding:1.5rem!important} (24px vs Tailwind's 16px) —
             Bootstrap's !important wins on the unprefixed class names, which
             was quietly shifting every column right of "Agent" out of
             alignment with the data rows below. Using the responsive-
             prefixed "lg:gap-3"/"lg:px-4" instead sidesteps the collision
             entirely (Bootstrap has no such classes) and matches the data
             rows, which already use "lg:gap-3"/"lg:p-4" for the same reason. --}}
        <div class="hidden lg:flex lg:items-center lg:gap-3 lg:px-4 pb-2 mb-1">
            <span class="w-9 flex-shrink-0"></span>
            <span class="w-48 flex-shrink-0 text-[10px] font-semibold uppercase tracking-wide text-novamuted">Agent</span>
            <span class="w-32 flex-shrink-0 text-[10px] font-semibold uppercase tracking-wide text-novamuted">Company</span>
            <span class="w-24 flex-shrink-0 text-[10px] font-semibold uppercase tracking-wide text-novamuted">Status</span>
            <span class="w-28 flex-shrink-0 text-[10px] font-semibold uppercase tracking-wide text-novamuted">Wallet</span>
            <span class="w-20 flex-shrink-0 text-[10px] font-semibold uppercase tracking-wide text-novamuted">Commission</span>
            <span class="w-24 flex-shrink-0 text-[10px] font-semibold uppercase tracking-wide text-novamuted">Joined</span>
            <span class="flex-shrink-0 text-[10px] font-semibold uppercase tracking-wide text-novamuted" style="width:220px;">Actions</span>
        </div>

        <div class="space-y-3">
            @forelse($agents as $agent)
                @php
                    $initials = collect(explode(' ', $agent->full_name))->filter()->map(fn($n) => strtoupper(substr($n, 0, 1)))->take(2)->implode('') ?: '?';
                    $avatar = $avatarPalette[crc32($agent->full_name) % count($avatarPalette)];
                    $st = $statusStyle[$agent->approval_status] ?? ['bg' => 'bg-slate-100', 'text' => 'text-novamuted', 'label' => ucfirst($agent->approval_status)];
                @endphp
                <div class="tt-row flex flex-col lg:flex-row lg:items-center gap-2.5 lg:gap-3 rounded-2xl border border-novaborder p-3.5 lg:p-4">

                    {{-- lg:contents "dissolves" this wrapper at desktop width so its
                         three children (avatar / name-block / mobile-status) become
                         direct flex items of the row — lining up 1:1 with the
                         header's own [avatar-spacer][Agent][...] columns instead of
                         being trapped as one combined column with its own internal
                         gap that doesn't match the header's gap-3 spacing. --}}
                    <div class="flex items-center gap-3 lg:contents">
                        <div class="w-9 h-9 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0"
                             style="background:{{ $avatar['bg'] }}; color:{{ $avatar['text'] }}">{{ $initials }}</div>
                        <div class="min-w-0 lg:w-48 lg:flex-shrink-0">
                            <p class="text-sm font-semibold text-novatext truncate">{{ $agent->full_name }}</p>
                            <p class="text-[11px] text-novamuted truncate mt-0.5">{{ $agent->email }}</p>
                            <span class="inline-flex mt-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-novamuted">#{{ $agent->agent_code }}</span>
                        </div>
                        <span class="lg:hidden ml-auto px-2.5 py-1 rounded-full text-[10px] font-semibold {{ $st['bg'] }} {{ $st['text'] }}">{{ $st['label'] }}</span>
                    </div>

                    <div class="lg:w-32 lg:flex-shrink-0 min-w-0">
                        <p class="text-[9px] text-novamuted lg:hidden">Company</p>
                        <p class="text-xs text-novatext truncate">{{ $agent->company_name ?? '—' }}</p>
                    </div>

                    <div class="hidden lg:block lg:w-24 lg:flex-shrink-0">
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold {{ $st['bg'] }} {{ $st['text'] }}">{{ $st['label'] }}</span>
                    </div>

                    <div class="lg:w-28 lg:flex-shrink-0">
                        <p class="text-[9px] text-novamuted lg:hidden">Wallet</p>
                        <p class="text-xs font-semibold text-novatext">
                            @if($agent->wallet)
                                PKR {{ number_format($agent->wallet->balance, 0) }}
                            @else
                                —
                            @endif
                        </p>
                    </div>

                    <div class="lg:w-20 lg:flex-shrink-0">
                        <p class="text-[9px] text-novamuted lg:hidden">Commission</p>
                        <p class="text-xs font-semibold text-novatext">{{ $agent->commission_rate ?? 0 }}%</p>
                    </div>

                    <div class="lg:w-24 lg:flex-shrink-0">
                        <p class="text-[9px] text-novamuted lg:hidden">Joined</p>
                        <p class="text-xs text-novamuted">{{ $agent->created_at->format('d M Y') }}</p>
                    </div>

                    <div class="flex items-center gap-1.5 flex-wrap lg:flex-nowrap lg:w-[220px] lg:flex-shrink-0">
                        <a href="{{ route('admin.agents.show', $agent) }}" class="ag-icon-btn" data-tooltip="View">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M2.5 12S6 5 12 5s9.5 7 9.5 7-3.5 7-9.5 7-9.5-7-9.5-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                        </a>
                        <a href="{{ route('admin.agents.edit', $agent) }}" class="ag-icon-btn" data-tooltip="Edit">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M16.474 5.408 18.592 7.526M4 20l1.11-3.92a2 2 0 0 1 .53-.9l9.9-9.9a1.5 1.5 0 0 1 2.12 0l1.06 1.06a1.5 1.5 0 0 1 0 2.12l-9.9 9.9a2 2 0 0 1-.9.53L4 20Z"/></svg>
                        </a>
                        <a href="{{ route('admin.agents.permissions', $agent) }}" class="ag-icon-btn" data-tooltip="Permissions">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3 4 6v6c0 5 3.5 8 8 9 4.5-1 8-4 8-9V6l-8-3Z"/></svg>
                        </a>
                        <a href="{{ route('admin.agents.wallet', $agent) }}" class="ag-icon-btn" data-tooltip="Wallet">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="6" width="18" height="13" rx="2"/><path d="M3 10h18M16 14.5h1.5"/></svg>
                        </a>

                        @if($agent->approval_status === 'pending')
                            <form method="POST" action="{{ route('admin.agents.approve', $agent) }}" class="inline">
                                @csrf
                                <button type="submit" class="ag-icon-btn ag-icon-approve" data-tooltip="Approve">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                                </button>
                            </form>
                        @elseif($agent->approval_status === 'active')
                            <button type="button" class="ag-icon-btn ag-icon-suspend" data-tooltip="Suspend"
                                    data-bs-toggle="modal" data-bs-target="#suspendModal{{ $agent->id }}">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="6" y="5" width="4" height="14" rx="1"/><rect x="14" y="5" width="4" height="14" rx="1"/></svg>
                            </button>
                        @elseif($agent->approval_status === 'suspended')
                            <form method="POST" action="{{ route('admin.agents.activate', $agent) }}" class="inline">
                                @csrf
                                <button type="submit" class="ag-icon-btn ag-icon-approve" data-tooltip="Activate">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M6 4.5v15l13-7.5-13-7.5Z"/></svg>
                                </button>
                            </form>
                        @endif

                        <button type="button" class="ag-icon-btn ag-icon-danger" data-tooltip="Delete"
                                data-bs-toggle="modal" data-bs-target="#deleteModal{{ $agent->id }}">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0-1 14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2L4 6h16Z"/></svg>
                        </button>
                    </div>
                </div>

                {{-- Delete modal — content restyled, Bootstrap modal shell kept
                     for real functionality (same DELETE form/route as Classic). --}}
                <div class="modal fade" id="deleteModal{{ $agent->id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content" style="border-radius:1.5rem; font-family:'Plus Jakarta Sans',sans-serif;">
                            <form method="POST" action="{{ route('admin.agents.destroy', $agent) }}">
                                @csrf
                                @method('DELETE')
                                <div class="modal-header" style="border-bottom:1px solid #E5E7EB;">
                                    <h5 class="modal-title" style="font-weight:700; color:#EF4444;">Delete Agent</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body" style="font-size:.875rem;">
                                    <p>Are you sure you want to delete <strong>{{ $agent->full_name }}</strong>?</p>
                                    <p style="color:#EF4444; font-size:.8rem;">This action cannot be undone. The agent and all associated data will be permanently deleted.</p>
                                </div>
                                <div class="modal-footer" style="border-top:1px solid #E5E7EB;">
                                    <button type="button" data-bs-dismiss="modal"
                                            style="display:inline-flex; align-items:center; border-radius:9999px; padding:10px 20px; font-size:13px; font-weight:600; font-family:'Plus Jakarta Sans',sans-serif; background:#F7F8FC; color:#000; border:1px solid #E5E7EB; cursor:pointer;">Cancel</button>
                                    <button type="submit"
                                            style="display:inline-flex; align-items:center; border-radius:9999px; padding:10px 20px; font-size:13px; font-weight:600; font-family:'Plus Jakarta Sans',sans-serif; background:#EF4444; color:#fff; border:1px solid #EF4444; cursor:pointer;">Delete Agent</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Suspend modal --}}
                <div class="modal fade" id="suspendModal{{ $agent->id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content" style="border-radius:1.5rem; font-family:'Plus Jakarta Sans',sans-serif;">
                            <form method="POST" action="{{ route('admin.agents.suspend', $agent) }}">
                                @csrf
                                <div class="modal-header" style="border-bottom:1px solid #E5E7EB;">
                                    <h5 class="modal-title" style="font-weight:700;">Suspend {{ $agent->full_name }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <label class="form-label" style="font-size:.8rem; font-weight:600;">Reason (optional)</label>
                                    <textarea name="reason" class="form-control" style="border-radius:1rem;" rows="3" placeholder="Reason for suspension..."></textarea>
                                </div>
                                <div class="modal-footer" style="border-top:1px solid #E5E7EB;">
                                    <button type="button" data-bs-dismiss="modal"
                                            style="display:inline-flex; align-items:center; border-radius:9999px; padding:10px 20px; font-size:13px; font-weight:600; font-family:'Plus Jakarta Sans',sans-serif; background:#F7F8FC; color:#000; border:1px solid #E5E7EB; cursor:pointer;">Cancel</button>
                                    <button type="submit"
                                            style="display:inline-flex; align-items:center; border-radius:9999px; padding:10px 20px; font-size:13px; font-weight:600; font-family:'Plus Jakarta Sans',sans-serif; background:#F59E0B; color:#fff; border:1px solid #F59E0B; cursor:pointer;">Suspend Agent</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-16">
                    <svg class="w-10 h-10 text-novaborder mx-auto mb-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="3.25"/><path d="M2.75 19c.5-3.2 3-5.25 6.25-5.25S15.25 15.8 15.75 19"/><circle cx="17" cy="9" r="2.5"/><path d="M15 13.75c2.4.2 4.2 1.9 4.75 5.25"/></svg>
                    <p class="text-sm font-semibold text-novatext">No agents found</p>
                    <p class="text-xs text-novamuted mt-1">Try adjusting your search or filters.</p>
                </div>
            @endforelse
        </div>

        @if($agents->hasPages())
            <div class="flex flex-wrap items-center justify-between gap-3 mt-6 pt-5 border-t border-novaborder">
                <p class="text-xs text-novamuted">Showing {{ $agents->firstItem() }} to {{ $agents->lastItem() }} of {{ number_format($agents->total()) }} entries</p>
                {{ $agents->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
