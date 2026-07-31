@extends('admin-nova.layouts.app')

@section('title', 'Newsletter Subscribers')

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
    #nlPage, #nlPage *, #nlPage *::before, #nlPage *::after { box-sizing: border-box; }
    #nlPage { font-family: 'Plus Jakarta Sans', sans-serif; }
    #nlPage h1, #nlPage h2, #nlPage h5, #nlPage p { margin: 0; padding: 0; }
    #nlPage a { text-decoration: none; color: inherit; }
    #nlPage button { font: inherit; color: inherit; background: none; border: none; cursor: pointer; padding: 0; }
    #nlPage svg { display: block; }
    #nlPage .tt-fade-in { animation: nlFadeIn .5s ease both; }
    @keyframes nlFadeIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }
    #nlPage .tt-row:hover { background: #F7F8FC; }

    #nlPage .nl-btn-nova {
        display: inline-flex; align-items: center; justify-content: center; gap: 6px;
        border: 1px solid #E5E7EB; border-radius: 9999px; color: #000; background: #fff;
        white-space: nowrap; transition: background .2s ease, border-color .2s ease;
    }
    #nlPage .nl-btn-nova:hover { background: #F7F8FC; }
    #nlPage .nl-btn-primary { background: #2563EB; color: #fff; border-color: #2563EB; }
    #nlPage .nl-btn-primary:hover { background: #1D4ED8; border-color: #1D4ED8; }
    #nlPage .nl-btn-warning { background: #F59E0B; color: #fff; border-color: #F59E0B; }
    #nlPage .nl-icon-btn {
        width: 30px; height: 30px; border-radius: 9999px; border: 1px solid #E5E7EB;
        display: inline-flex; align-items: center; justify-content: center; background: #fff;
        transition: background .2s ease, border-color .2s ease;
    }
    #nlPage .nl-icon-btn:hover { background: #F7F8FC; }
    #nlPage .nl-icon-warn:hover { background: #FFFBEB; border-color: #F59E0B; color: #F59E0B; }
    #nlPage .nl-icon-success:hover { background: #ECFDF5; border-color: #22C55E; color: #22C55E; }
    #nlPage .nl-icon-danger:hover { background: #FEF2F2; border-color: #EF4444; color: #EF4444; }

    #nlPage .nl-bulk-bar { display: none; }
    #nlPage .nl-bulk-bar.nl-visible { display: flex; }

    #nlPage [data-tooltip] { position: relative; }
    #nlPage [data-tooltip]::after {
        content: attr(data-tooltip); position: absolute; bottom: calc(100% + 8px); left: 50%;
        transform: translateX(-50%) translateY(4px); background: #1F2937; color: #fff;
        font-size: 11px; font-weight: 600; line-height: 1; padding: 6px 10px; border-radius: 6px;
        white-space: nowrap; box-shadow: 0 6px 16px rgba(0,0,0,.18); opacity: 0; visibility: hidden;
        pointer-events: none; transition: opacity .15s ease, transform .15s ease, visibility .15s ease; z-index: 60;
    }
    #nlPage [data-tooltip]::before {
        content: ''; position: absolute; bottom: calc(100% + 3px); left: 50%; transform: translateX(-50%);
        border: 5px solid transparent; border-top-color: #1F2937; opacity: 0; visibility: hidden;
        pointer-events: none; transition: opacity .15s ease, visibility .15s ease; z-index: 60;
    }
    #nlPage [data-tooltip]:hover::after, #nlPage [data-tooltip]:focus-visible::after { opacity: 1; visibility: visible; transform: translateX(-50%) translateY(0); transition-delay: .25s; }
    #nlPage [data-tooltip]:hover::before, #nlPage [data-tooltip]:focus-visible::before { opacity: 1; visibility: visible; transition-delay: .25s; }

    #nlPage .pagination { gap: 4px; }
    #nlPage .page-link { border-radius: 9999px !important; }
</style>
@endpush

@section('content')
@php
    $avatarPalette = [
        ['bg' => '#DBEAFE', 'text' => '#1D4ED8'], ['bg' => '#DCFCE7', 'text' => '#15803D'],
        ['bg' => '#FEF3C7', 'text' => '#B45309'], ['bg' => '#FCE7F3', 'text' => '#BE185D'],
        ['bg' => '#EDE9FE', 'text' => '#6D28D9'],
    ];
    $statusStyle = [
        'active' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600', 'label' => 'Active'],
        'inactive' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-600', 'label' => 'Inactive'],
        'unsubscribed' => ['bg' => 'bg-red-50', 'text' => 'text-novadanger', 'label' => 'Unsubscribed'],
    ];
@endphp

<div id="nlPage" class="tt-fade-in font-jakarta">

    {{-- ============ HEADER ============ --}}
    <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
        <div>
            <h1 class="text-lg font-bold text-novatext">Newsletter Subscribers</h1>
            <p class="text-xs text-novamuted mt-1">Manage your email subscribers and mailing lists</p>
        </div>
        <div class="flex items-center gap-2">
            <button class="nl-btn-nova px-4 py-2.5 text-xs font-semibold" data-bs-toggle="modal" data-bs-target="#bulkImportModal">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 15V3m0 0 4 4m-4-4-4 4M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"/></svg>
                Bulk Import
            </button>
            <button class="nl-btn-nova nl-btn-primary px-4 py-2.5 text-xs font-semibold" data-bs-toggle="modal" data-bs-target="#addSubscriberModal">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="3.25"/><path d="M2.75 19c.5-3.2 3-5.25 6.25-5.25S15.25 15.8 15.75 19"/><path d="M18 8v6M15 11h6"/></svg>
                Add Subscriber
            </button>
        </div>
    </div>

    {{-- ============ KPI CARDS ============ --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
        <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-3">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-medium text-novamuted">Total Subscribers</p>
                <div class="w-7 h-7 rounded-full bg-novablue text-white flex items-center justify-center flex-shrink-0">
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="3.25"/><path d="M2.75 19c.5-3.2 3-5.25 6.25-5.25S15.25 15.8 15.75 19"/><circle cx="17" cy="9" r="2.5"/><path d="M15 13.75c2.4.2 4.2 1.9 4.75 5.25"/></svg>
                </div>
            </div>
            <span class="block text-lg font-bold text-novatext">{{ number_format($stats['total_subscribers']) }}</span>
            <span class="text-[10px] text-novasuccess font-semibold">+{{ $stats['new_this_week'] }} this week</span>
        </div>
        <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-3">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-medium text-novamuted">Active</p>
                <div class="w-7 h-7 rounded-full bg-novasuccess text-white flex items-center justify-center flex-shrink-0">
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12.75 11.25 15 15 9.75"/></svg>
                </div>
            </div>
            <span class="block text-lg font-bold text-novatext">{{ number_format($stats['active_subscribers']) }}</span>
            <span class="text-[10px] text-novasuccess font-semibold">{{ $stats['total_subscribers'] > 0 ? round(($stats['active_subscribers']/$stats['total_subscribers'])*100, 1) : 0 }}% active rate</span>
        </div>
        <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-3">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-medium text-novamuted">New This Month</p>
                <div class="w-7 h-7 rounded-full bg-novawarning text-white flex items-center justify-center flex-shrink-0">
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="3.25"/><path d="M2.75 19c.5-3.2 3-5.25 6.25-5.25S15.25 15.8 15.75 19"/><path d="M18 8v6M15 11h6"/></svg>
                </div>
            </div>
            <span class="block text-lg font-bold text-novatext">{{ number_format($stats['new_this_month']) }}</span>
            <span class="text-[10px] text-novasuccess font-semibold">{{ $stats['growth_percentage'] }}% growth</span>
        </div>
        <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-3">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-medium text-novamuted">Unsubscribed</p>
                <div class="w-7 h-7 rounded-full bg-novadanger text-white flex items-center justify-center flex-shrink-0">
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="3.25"/><path d="M2.75 19c.5-3.2 3-5.25 6.25-5.25S15.25 15.8 15.75 19"/><path d="m14.5 8.5 5 5m0-5-5 5"/></svg>
                </div>
            </div>
            <span class="block text-lg font-bold text-novatext">{{ number_format($stats['unsubscribed']) }}</span>
            <span class="text-[10px] text-novawarning font-semibold">{{ $stats['total_subscribers'] > 0 ? round(($stats['unsubscribed']/$stats['total_subscribers'])*100, 1) : 0 }}% unsubscribe rate</span>
        </div>
    </div>

    {{-- ============ FILTERS ============ --}}
    <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 sm:p-5 mb-5">
        <form method="GET" action="{{ route('admin.content.newsletter.subscribers') }}">
            <div class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[220px]">
                    <label class="block text-xs font-semibold text-novamuted mb-1.5">Search subscribers</label>
                    <div class="relative">
                        <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-novamuted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by email..."
                               class="w-full text-sm border border-novaborder rounded-full pl-10 pr-4 py-2.5">
                    </div>
                </div>
                <div class="w-40">
                    <label class="block text-xs font-semibold text-novamuted mb-1.5">Status</label>
                    <select name="status" class="w-full text-sm border border-novaborder rounded-full px-3.5 py-2.5">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="unsubscribed" {{ request('status') == 'unsubscribed' ? 'selected' : '' }}>Unsubscribed</option>
                    </select>
                </div>
                <div class="w-40">
                    <label class="block text-xs font-semibold text-novamuted mb-1.5">Join Date</label>
                    <select name="join_date" class="w-full text-sm border border-novaborder rounded-full px-3.5 py-2.5">
                        <option value="">All Time</option>
                        <option value="today" {{ request('join_date') == 'today' ? 'selected' : '' }}>Today</option>
                        <option value="week" {{ request('join_date') == 'week' ? 'selected' : '' }}>This Week</option>
                        <option value="month" {{ request('join_date') == 'month' ? 'selected' : '' }}>This Month</option>
                        <option value="year" {{ request('join_date') == 'year' ? 'selected' : '' }}>This Year</option>
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <button type="submit" class="nl-btn-nova nl-btn-primary w-10 h-10" data-tooltip="Apply filters">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 5h16M7 12h10M10 19h4"/></svg>
                    </button>
                    <a href="{{ route('admin.content.newsletter.subscribers') }}" class="nl-btn-nova w-10 h-10" data-tooltip="Reset filters">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 1 2.64 6.36M3 12v6m0-6h6"/></svg>
                    </a>
                    <button type="button" class="nl-btn-nova nl-btn-warning w-10 h-10" onclick="toggleBulkActions()" data-tooltip="Bulk actions">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M10.3 2.9 8 5.2 5.2 8l-2.3 2.3a2 2 0 0 0 0 2.8l6 6a2 2 0 0 0 2.8 0L14 16.8M13 4l3 3m2-5 3 3-11 11H7v-3Z"/></svg>
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- ============ BULK ACTIONS BAR ============ --}}
    <div class="nl-bulk-bar tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 mb-4 items-center justify-between" id="bulkActionsBar">
        <span class="text-xs text-novatext"><strong><span id="selectedCount">0</span></strong> subscribers selected</span>
        <div class="flex flex-wrap items-center gap-2">
            <button class="nl-btn-nova px-3 py-2 text-xs font-semibold" style="color:#22C55E; border-color:#22C55E;" onclick="bulkAction('activate')">Activate</button>
            <button class="nl-btn-nova px-3 py-2 text-xs font-semibold" style="color:#F59E0B; border-color:#F59E0B;" onclick="bulkAction('deactivate')">Deactivate</button>
            <button class="nl-btn-nova px-3 py-2 text-xs font-semibold" onclick="bulkAction('unsubscribe')">Unsubscribe</button>
            <button class="nl-btn-nova px-3 py-2 text-xs font-semibold" style="background:#EF4444; color:#fff; border-color:#EF4444;" onclick="bulkAction('delete')">Delete</button>
            <button class="nl-btn-nova px-3 py-2 text-xs font-semibold" onclick="clearSelection()">Cancel</button>
        </div>
    </div>

    {{-- ============ SUBSCRIBERS LIST ============ --}}
    <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 sm:p-5">
        <div class="flex items-center gap-2 mb-4">
            <h2 class="text-sm font-semibold text-novatext">Subscribers List</h2>
            <span class="text-xs text-novamuted">{{ number_format($subscribers->total()) }} total</span>
        </div>

        <div class="hidden lg:flex lg:items-center lg:gap-3 lg:px-4 pb-2 mb-1">
            <span class="w-6 flex-shrink-0"><input type="checkbox" id="selectAll" onchange="toggleSelectAll()"></span>
            <span class="w-64 flex-shrink-0 text-[10px] font-semibold uppercase tracking-wide text-novamuted">Email</span>
            <span class="w-28 flex-shrink-0 text-[10px] font-semibold uppercase tracking-wide text-novamuted">Status</span>
            <span class="w-32 flex-shrink-0 text-[10px] font-semibold uppercase tracking-wide text-novamuted">Joined</span>
            <span class="flex-1 min-w-0 text-[10px] font-semibold uppercase tracking-wide text-novamuted">Time Since</span>
            <span class="flex-shrink-0 text-[10px] font-semibold uppercase tracking-wide text-novamuted" style="width:170px;">Actions</span>
        </div>

        <div class="space-y-3">
            @forelse ($subscribers as $subscriber)
                @php
                    $avatar = $avatarPalette[crc32($subscriber->email) % count($avatarPalette)];
                    $st = $statusStyle[$subscriber->status] ?? ['bg' => 'bg-slate-100', 'text' => 'text-novamuted', 'label' => ucfirst($subscriber->status)];
                @endphp
                <div class="tt-row flex flex-col lg:flex-row lg:items-center gap-2.5 lg:gap-3 rounded-2xl border border-novaborder p-3.5 lg:p-4">
                    <div class="flex items-center gap-3 lg:contents">
                        <span class="lg:w-6 lg:flex-shrink-0"><input type="checkbox" class="subscriber-checkbox" value="{{ $subscriber->id }}" onchange="updateSelection()"></span>
                        <div class="min-w-0 lg:w-64 lg:flex-shrink-0 flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-full flex items-center justify-center text-[10px] font-bold flex-shrink-0" style="background:{{ $avatar['bg'] }}; color:{{ $avatar['text'] }}">
                                {{ strtoupper(substr($subscriber->email, 0, 2)) }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-novatext truncate">{{ $subscriber->email }}</p>
                                <p class="text-[11px] text-novamuted">ID: {{ $subscriber->id }}</p>
                            </div>
                        </div>
                        <span class="lg:hidden ml-auto px-2.5 py-1 rounded-full text-[10px] font-semibold {{ $st['bg'] }} {{ $st['text'] }}">{{ $st['label'] }}</span>
                    </div>

                    <div class="hidden lg:block lg:w-28 lg:flex-shrink-0">
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold {{ $st['bg'] }} {{ $st['text'] }}">{{ $st['label'] }}</span>
                    </div>

                    <div class="lg:w-32 lg:flex-shrink-0">
                        <p class="text-[9px] text-novamuted lg:hidden">Joined</p>
                        <p class="text-xs text-novatext">{{ $subscriber->joined_date_formatted }}</p>
                    </div>

                    <div class="lg:flex-1 min-w-0">
                        <p class="text-[9px] text-novamuted lg:hidden">Time Since</p>
                        <p class="text-xs text-novamuted">{{ $subscriber->joined_time_ago }}</p>
                    </div>

                    <div class="flex items-center gap-1.5 lg:flex-shrink-0" style="width:170px;">
                        <button class="nl-icon-btn" data-tooltip="View Details" data-bs-toggle="modal" data-bs-target="#viewSubscriberModal" onclick="loadSubscriberData({{ $subscriber->id }}, 'view')">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M2.5 12S6 5 12 5s9.5 7 9.5 7-3.5 7-9.5 7-9.5-7-9.5-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                        <button class="nl-icon-btn" data-tooltip="Edit" data-bs-toggle="modal" data-bs-target="#editSubscriberModal" onclick="loadSubscriberData({{ $subscriber->id }}, 'edit')">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M16.474 5.408 18.592 7.526M4 20l1.11-3.92a2 2 0 0 1 .53-.9l9.9-9.9a1.5 1.5 0 0 1 2.12 0l1.06 1.06a1.5 1.5 0 0 1 0 2.12l-9.9 9.9a2 2 0 0 1-.9.53L4 20Z"/></svg>
                        </button>
                        @if($subscriber->status == 'active')
                            <button class="nl-icon-btn nl-icon-warn" data-tooltip="Unsubscribe" onclick="changeSubscriberStatus({{ $subscriber->id }}, 'unsubscribe')">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="3.25"/><path d="M2.75 19c.5-3.2 3-5.25 6.25-5.25S15.25 15.8 15.75 19"/><path d="m14.5 8.5 5 5m0-5-5 5"/></svg>
                            </button>
                        @elseif($subscriber->status == 'inactive')
                            <button class="nl-icon-btn nl-icon-success" data-tooltip="Activate" onclick="changeSubscriberStatus({{ $subscriber->id }}, 'activate')">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="3.25"/><path d="M2.75 19c.5-3.2 3-5.25 6.25-5.25S15.25 15.8 15.75 19"/><path d="m14 9 2 2 3.5-4"/></svg>
                            </button>
                        @else
                            <button class="nl-icon-btn nl-icon-success" data-tooltip="Resubscribe" onclick="changeSubscriberStatus({{ $subscriber->id }}, 'subscribe')">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="3.25"/><path d="M2.75 19c.5-3.2 3-5.25 6.25-5.25S15.25 15.8 15.75 19"/><path d="m14 9 2 2 3.5-4"/></svg>
                            </button>
                        @endif
                        <button class="nl-icon-btn nl-icon-danger" data-tooltip="Delete" onclick="deleteSubscriber({{ $subscriber->id }})">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0-1 14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2L4 6h16Z"/></svg>
                        </button>
                    </div>
                </div>
            @empty
                <div class="text-center py-16">
                    <svg class="w-10 h-10 text-novaborder mx-auto mb-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="3.25"/><path d="M2.75 19c.5-3.2 3-5.25 6.25-5.25S15.25 15.8 15.75 19"/></svg>
                    <p class="text-sm font-semibold text-novatext">No subscribers found</p>
                </div>
            @endforelse
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 mt-6 pt-5 border-t border-novaborder">
            <p class="text-xs text-novamuted">Showing {{ $subscribers->firstItem() }} to {{ $subscribers->lastItem() }} of {{ $subscribers->total() }} entries</p>
            {{ $subscribers->withQueryString()->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

{{-- ============ ADD SUBSCRIBER MODAL ============ --}}
<div class="modal fade" id="addSubscriberModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:1.5rem; font-family:'Plus Jakarta Sans',sans-serif;">
            <form id="addSubscriberForm" method="POST" action="{{ route('admin.content.newsletter.store-subscriber') }}">
                @csrf
                <div class="modal-header" style="border-bottom:1px solid #E5E7EB;">
                    <h5 class="modal-title" style="font-weight:700;">Add New Subscriber</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" style="font-size:.8rem; font-weight:600;">Email Address <span style="color:#EF4444;">*</span></label>
                        <input type="email" class="form-control" style="border-radius:9999px;" name="email" placeholder="john.doe@email.com" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-size:.8rem; font-weight:600;">Status</label>
                        <select class="form-select" style="border-radius:9999px;" name="status">
                            <option value="active" selected>Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="unsubscribed">Unsubscribed</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-size:.8rem; font-weight:600;">Join Date</label>
                        <input type="date" class="form-control" style="border-radius:9999px;" name="joined_date" value="{{ date('Y-m-d') }}">
                    </div>
                </div>
                <div class="modal-footer" style="border-top:1px solid #E5E7EB;">
                    <button type="button" data-bs-dismiss="modal"
                            style="display:inline-flex; align-items:center; border-radius:9999px; padding:10px 20px; font-size:13px; font-weight:600; font-family:'Plus Jakarta Sans',sans-serif; background:#F7F8FC; color:#000; border:1px solid #E5E7EB; cursor:pointer;">Cancel</button>
                    <button type="submit"
                            style="display:inline-flex; align-items:center; border-radius:9999px; padding:10px 20px; font-size:13px; font-weight:600; font-family:'Plus Jakarta Sans',sans-serif; background:#2563EB; color:#fff; border:1px solid #2563EB; cursor:pointer;">Add Subscriber</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ============ EDIT SUBSCRIBER MODAL ============ --}}
<div class="modal fade" id="editSubscriberModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:1.5rem; font-family:'Plus Jakarta Sans',sans-serif;">
            <div class="modal-header" style="border-bottom:1px solid #E5E7EB;">
                <h5 class="modal-title" style="font-weight:700;">Edit Subscriber: <span id="editSubscriberEmail"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editSubscriberForm" method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" id="editSubscriberId" name="subscriber_id">
                    <div class="mb-3">
                        <label class="form-label" style="font-size:.8rem; font-weight:600;">Email Address <span style="color:#EF4444;">*</span></label>
                        <input type="email" class="form-control" style="border-radius:9999px;" id="editEmailInput" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-size:.8rem; font-weight:600;">Status</label>
                        <select class="form-select" style="border-radius:9999px;" id="editStatusInput" name="status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="unsubscribed">Unsubscribed</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-size:.8rem; font-weight:600;">Join Date</label>
                        <input type="date" class="form-control" style="border-radius:9999px;" id="editJoinedDateInput" name="joined_date">
                    </div>
                </form>
            </div>
            <div class="modal-footer" style="border-top:1px solid #E5E7EB;">
                <button type="button" data-bs-dismiss="modal"
                        style="display:inline-flex; align-items:center; border-radius:9999px; padding:10px 20px; font-size:13px; font-weight:600; font-family:'Plus Jakarta Sans',sans-serif; background:#F7F8FC; color:#000; border:1px solid #E5E7EB; cursor:pointer;">Cancel</button>
                <button type="submit" form="editSubscriberForm"
                        style="display:inline-flex; align-items:center; border-radius:9999px; padding:10px 20px; font-size:13px; font-weight:600; font-family:'Plus Jakarta Sans',sans-serif; background:#2563EB; color:#fff; border:1px solid #2563EB; cursor:pointer;">Update Subscriber</button>
            </div>
        </div>
    </div>
</div>

{{-- ============ VIEW SUBSCRIBER MODAL ============ --}}
<div class="modal fade" id="viewSubscriberModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:1.5rem; font-family:'Plus Jakarta Sans',sans-serif;">
            <div class="modal-header" style="border-bottom:1px solid #E5E7EB;">
                <h5 class="modal-title" style="font-weight:700;">Subscriber Details: <span id="viewSubscriberEmail"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="font-size:.875rem;">
                <div class="mb-3"><label style="font-size:.75rem; font-weight:700; display:block; margin-bottom:2px;">Email Address</label><div id="viewEmail"></div></div>
                <div class="mb-3"><label style="font-size:.75rem; font-weight:700; display:block; margin-bottom:2px;">Status</label><div id="viewStatus"></div></div>
                <div class="mb-3"><label style="font-size:.75rem; font-weight:700; display:block; margin-bottom:2px;">Joined Date</label><div id="viewJoinedDate"></div></div>
                <div class="mb-3"><label style="font-size:.75rem; font-weight:700; display:block; margin-bottom:2px;">Time Since Join</label><div id="viewTimeSince"></div></div>
                <div class="mb-3"><label style="font-size:.75rem; font-weight:700; display:block; margin-bottom:2px;">Created At</label><div id="viewCreatedAt"></div></div>
                <div class="mb-3"><label style="font-size:.75rem; font-weight:700; display:block; margin-bottom:2px;">Last Updated</label><div id="viewUpdatedAt"></div></div>
            </div>
            <div class="modal-footer" style="border-top:1px solid #E5E7EB;">
                <button type="button" data-bs-dismiss="modal"
                        style="display:inline-flex; align-items:center; border-radius:9999px; padding:10px 20px; font-size:13px; font-weight:600; font-family:'Plus Jakarta Sans',sans-serif; background:#F7F8FC; color:#000; border:1px solid #E5E7EB; cursor:pointer;">Close</button>
                <button type="button" onclick="openEditFromView()"
                        style="display:inline-flex; align-items:center; border-radius:9999px; padding:10px 20px; font-size:13px; font-weight:600; font-family:'Plus Jakarta Sans',sans-serif; background:#2563EB; color:#fff; border:1px solid #2563EB; cursor:pointer;">Edit Subscriber</button>
            </div>
        </div>
    </div>
</div>

{{-- ============ BULK IMPORT MODAL ============ --}}
<div class="modal fade" id="bulkImportModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius:1.5rem; font-family:'Plus Jakarta Sans',sans-serif;">
            <div class="modal-header" style="border-bottom:1px solid #E5E7EB;">
                <h5 class="modal-title" style="font-weight:700;">Bulk Import Subscribers</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="bulkImportForm" method="POST" action="{{ route('admin.content.newsletter.bulk-import') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" style="font-size:.8rem; font-weight:600;">Upload CSV File</label>
                        <input type="file" class="form-control" style="border-radius:14px;" name="csv_file" accept=".csv" required>
                        <div class="form-text">Upload a CSV file with columns: email, status, joined_date</div>
                    </div>
                    <div class="mb-3">
                        <h6 style="font-size:.85rem; font-weight:700;">CSV Format Example:</h6>
                        <pre style="background:#F7F8FC; border-radius:14px; padding:14px; font-size:12px;"><code>email,status,joined_date
john@email.com,active,2025-01-15
sarah@email.com,active,2025-01-16</code></pre>
                    </div>
                    <div class="mb-3 form-check">
                        <input class="form-check-input" type="checkbox" id="skipDuplicates" name="skip_duplicates" checked>
                        <label class="form-check-label" style="font-size:.8rem;" for="skipDuplicates">Skip duplicate email addresses</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer" style="border-top:1px solid #E5E7EB;">
                <button type="button" data-bs-dismiss="modal"
                        style="display:inline-flex; align-items:center; border-radius:9999px; padding:10px 20px; font-size:13px; font-weight:600; font-family:'Plus Jakarta Sans',sans-serif; background:#F7F8FC; color:#000; border:1px solid #E5E7EB; cursor:pointer;">Cancel</button>
                <button type="submit" form="bulkImportForm"
                        style="display:inline-flex; align-items:center; border-radius:9999px; padding:10px 20px; font-size:13px; font-weight:600; font-family:'Plus Jakarta Sans',sans-serif; background:#2563EB; color:#fff; border:1px solid #2563EB; cursor:pointer;">Import Subscribers</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function loadSubscriberData(subscriberId, mode) {
    if (mode === 'view') {
        document.getElementById('viewSubscriberEmail').textContent = 'Loading...';
    } else {
        document.getElementById('editSubscriberEmail').textContent = 'Loading...';
    }
    fetch(`{{ url('admin/content/newsletter/subscribers') }}/${subscriberId}`, {
        method: 'GET',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (mode === 'view') { populateViewModal(data.subscriber); }
            else { populateEditModal(data.subscriber); }
        } else { alert('Error loading subscriber details'); }
    })
    .catch(error => { console.error('Error:', error); alert('Error loading subscriber details'); });
}

function populateViewModal(subscriber) {
    document.getElementById('viewSubscriberEmail').textContent = subscriber.email;
    document.getElementById('viewSubscriberEmail').dataset.subscriberId = subscriber.id;
    document.getElementById('viewEmail').textContent = subscriber.email;
    document.getElementById('viewStatus').innerHTML = `<span style="display:inline-flex; padding:4px 10px; border-radius:9999px; font-size:10px; font-weight:600; background:#F7F8FC;">${subscriber.status}</span>`;
    document.getElementById('viewJoinedDate').textContent = subscriber.joined_date || 'N/A';
    document.getElementById('viewTimeSince').textContent = subscriber.joined_time_ago || 'N/A';
    document.getElementById('viewCreatedAt').textContent = subscriber.created_at || 'N/A';
    document.getElementById('viewUpdatedAt').textContent = subscriber.updated_at || 'N/A';
}

function populateEditModal(subscriber) {
    document.getElementById('editSubscriberEmail').textContent = subscriber.email;
    document.getElementById('editSubscriberId').value = subscriber.id;
    document.getElementById('editEmailInput').value = subscriber.email;
    document.getElementById('editStatusInput').value = subscriber.status;
    document.getElementById('editJoinedDateInput').value = subscriber.joined_date ? subscriber.joined_date.split(' ')[0] : '';
    document.getElementById('editSubscriberForm').action = `{{ url('admin/content/newsletter/subscribers') }}/${subscriber.id}`;
}

function openEditFromView() {
    const subscriberId = document.getElementById('viewSubscriberEmail').dataset.subscriberId;
    if (subscriberId) {
        const viewModal = bootstrap.Modal.getInstance(document.getElementById('viewSubscriberModal'));
        viewModal.hide();
        setTimeout(() => {
            loadSubscriberData(subscriberId, 'edit');
            const editModal = new bootstrap.Modal(document.getElementById('editSubscriberModal'));
            editModal.show();
        }, 300);
    }
}

function changeSubscriberStatus(subscriberId, action) {
    if (confirm(`Are you sure you want to ${action} this subscriber?`)) {
        fetch(`{{ url('admin/content/newsletter/subscribers') }}/${subscriberId}/${action}`, {
            method: 'PATCH',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(data => { if (data.success) { alert(data.message); location.reload(); } else { alert('Error: ' + data.message); } })
        .catch(error => { console.error('Error:', error); alert('An error occurred'); });
    }
}

function deleteSubscriber(subscriberId) {
    if (confirm('Are you sure you want to delete this subscriber? This action cannot be undone.')) {
        fetch(`{{ url('admin/content/newsletter/subscribers') }}/${subscriberId}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(data => { if (data.success) { alert('Subscriber deleted successfully'); location.reload(); } else { alert('Error: ' + data.message); } })
        .catch(error => { console.error('Error:', error); alert('An error occurred'); });
    }
}

function toggleBulkActions() {
    document.getElementById('bulkActionsBar').classList.toggle('nl-visible');
    if (!document.getElementById('bulkActionsBar').classList.contains('nl-visible')) { clearSelection(); }
}

function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    document.querySelectorAll('.subscriber-checkbox').forEach(cb => { cb.checked = selectAll.checked; });
    updateSelection();
}

function updateSelection() {
    const checkboxes = document.querySelectorAll('.subscriber-checkbox:checked');
    document.getElementById('selectedCount').textContent = checkboxes.length;
    document.getElementById('bulkActionsBar').classList.toggle('nl-visible', checkboxes.length > 0);
}

function clearSelection() {
    document.querySelectorAll('.subscriber-checkbox').forEach(cb => { cb.checked = false; });
    document.getElementById('selectAll').checked = false;
    document.getElementById('bulkActionsBar').classList.remove('nl-visible');
}

function bulkAction(action) {
    const checkboxes = document.querySelectorAll('.subscriber-checkbox:checked');
    if (checkboxes.length === 0) { alert('Please select subscribers first'); return; }
    const subscriberIds = Array.from(checkboxes).map(cb => cb.value);
    if (confirm(`Are you sure you want to ${action} ${subscriberIds.length} subscriber(s)?`)) {
        fetch(`{{ url('admin/content/newsletter/subscribers/bulk') }}/${action}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'Content-Type': 'application/json'
            },
            body: JSON.stringify({ subscriber_ids: subscriberIds })
        })
        .then(response => response.json())
        .then(data => { if (data.success) { alert(data.message); location.reload(); } else { alert('Error: ' + data.message); } })
        .catch(error => { console.error('Error:', error); alert('An error occurred'); });
    }
}

document.getElementById('editSubscriberForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const subscriberId = document.getElementById('editSubscriberId').value;
    fetch(`{{ url('admin/content/newsletter/subscribers') }}/${subscriberId}`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('editSubscriberModal'));
            modal.hide();
            alert('Subscriber updated successfully!');
            location.reload();
        } else { alert('Error: ' + (data.message || 'Unknown error')); }
    })
    .catch(error => { console.error('Error:', error); alert('An error occurred while updating the subscriber'); });
});
</script>
@endpush
@endsection
