@extends('admin-nova.layouts.app')

@section('title', 'Visa Requests')

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
    #vrPage, #vrPage *, #vrPage *::before, #vrPage *::after { box-sizing: border-box; }
    #vrPage { font-family: 'Plus Jakarta Sans', sans-serif; }
    #vrPage h1, #vrPage h2, #vrPage p { margin: 0; padding: 0; }
    #vrPage a { text-decoration: none; color: inherit; }
    #vrPage button { font: inherit; color: inherit; background: none; border: none; cursor: pointer; padding: 0; }
    #vrPage svg { display: block; }

    #vrPage .tt-fade-in { animation: vrFadeIn .5s ease both; }
    @keyframes vrFadeIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }

    #vrPage .tt-row { transition: background .2s ease, border-color .2s ease, transform .2s ease; }
    #vrPage .tt-row:hover { background: #F7F8FC; border-color: #DBEAFE; transform: translateX(2px); }

    #vrPage .vr-btn-nova {
        display: inline-flex; align-items: center; justify-content: center; gap: 6px;
        border: 1px solid #E5E7EB; border-radius: 9999px; color: #000; background: #fff;
        white-space: nowrap; transition: background .2s ease, border-color .2s ease;
    }
    #vrPage .vr-btn-nova:hover { background: #F7F8FC; }
    #vrPage .vr-btn-primary { background: #2563EB; color: #fff; border-color: #2563EB; }
    #vrPage .vr-btn-primary:hover { background: #1D4ED8; border-color: #1D4ED8; }
    #vrPage .vr-icon-btn {
        width: 30px; height: 30px; border-radius: 9999px; border: 1px solid #E5E7EB;
        display: inline-flex; align-items: center; justify-content: center; background: #fff;
        transition: background .2s ease, border-color .2s ease;
    }
    #vrPage .vr-icon-btn.has-label { width: auto; height: 30px; padding: 0 12px; gap: 6px; font-size: 11px; font-weight: 600; }
    #vrPage .vr-icon-btn:hover { background: #F7F8FC; }

    #vrPage [data-tooltip] { position: relative; }
    #vrPage [data-tooltip]::after {
        content: attr(data-tooltip); position: absolute; bottom: calc(100% + 8px); left: 50%;
        transform: translateX(-50%) translateY(4px); background: #1F2937; color: #fff;
        font-size: 11px; font-weight: 600; line-height: 1; padding: 6px 10px; border-radius: 6px;
        white-space: nowrap; box-shadow: 0 6px 16px rgba(0,0,0,.18); opacity: 0; visibility: hidden;
        pointer-events: none; transition: opacity .15s ease, transform .15s ease, visibility .15s ease; z-index: 60;
    }
    #vrPage [data-tooltip]::before {
        content: ''; position: absolute; bottom: calc(100% + 3px); left: 50%; transform: translateX(-50%);
        border: 5px solid transparent; border-top-color: #1F2937; opacity: 0; visibility: hidden;
        pointer-events: none; transition: opacity .15s ease, visibility .15s ease; z-index: 60;
    }
    #vrPage [data-tooltip]:hover::after, #vrPage [data-tooltip]:focus-visible::after { opacity: 1; visibility: visible; transform: translateX(-50%) translateY(0); transition-delay: .25s; }
    #vrPage [data-tooltip]:hover::before, #vrPage [data-tooltip]:focus-visible::before { opacity: 1; visibility: visible; transition-delay: .25s; }
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
@endphp

<div id="vrPage" class="tt-fade-in font-jakarta">

    {{-- ============ HEADER ============ --}}
    <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
        <div>
            <h1 class="text-lg font-bold text-novatext">Visa Requests Management</h1>
            <p class="text-xs text-novamuted mt-1">Track and manage all visa applications</p>
        </div>
    </div>

    {{-- ============ KPI CARDS ============ --}}
    <div class="grid grid-cols-3 gap-3 mb-5">
        <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-3">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-medium text-novamuted">Total</p>
                <div class="w-7 h-7 rounded-full bg-novablue text-white flex items-center justify-center flex-shrink-0">
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M7 2v4M17 2v4M3 10h18"/></svg>
                </div>
            </div>
            <span class="block text-lg font-bold text-novatext">{{ number_format($stats['total'] ?? 0) }}</span>
        </div>
        <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-3">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-medium text-novamuted">UAE Visas</p>
                <div class="w-7 h-7 rounded-full bg-novasuccess text-white flex items-center justify-center flex-shrink-0">
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M8 12h8M12 8v8"/></svg>
                </div>
            </div>
            <span class="block text-lg font-bold text-novatext">{{ number_format($stats['uae'] ?? 0) }}</span>
        </div>
        <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-3">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-medium text-novamuted">Other Visas</p>
                <div class="w-7 h-7 rounded-full bg-novawarning text-white flex items-center justify-center flex-shrink-0">
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 8v4l2.5 2.5"/></svg>
                </div>
            </div>
            <span class="block text-lg font-bold text-novatext">{{ number_format($stats['other'] ?? 0) }}</span>
        </div>
    </div>

    {{-- ============ FILTERS ============ --}}
    <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 sm:p-5 mb-5">
        <form method="GET" action="{{ route('admin.visa-requests.visaindex') }}">
            <div class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[220px]">
                    <label class="block text-xs font-semibold text-novamuted mb-1.5">Search requests</label>
                    <div class="relative">
                        <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-novamuted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Name, passport no, nationality..."
                               class="w-full text-sm border border-novaborder rounded-full pl-10 pr-4 py-2.5">
                    </div>
                </div>
                <div class="w-40">
                    <label class="block text-xs font-semibold text-novamuted mb-1.5">Visa Category</label>
                    <select name="visa_category" class="w-full text-sm border border-novaborder rounded-full px-3.5 py-2.5">
                        <option value="">All categories</option>
                        <option value="uae" {{ request('visa_category') === 'uae' ? 'selected' : '' }}>UAE</option>
                        <option value="other" {{ request('visa_category') === 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>
                <div class="w-40">
                    <label class="block text-xs font-semibold text-novamuted mb-1.5">Date From</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full text-sm border border-novaborder rounded-full px-3.5 py-2.5">
                </div>
                <div class="w-40">
                    <label class="block text-xs font-semibold text-novamuted mb-1.5">Date To</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full text-sm border border-novaborder rounded-full px-3.5 py-2.5">
                </div>
                <div class="flex items-center gap-2">
                    <button type="submit" class="vr-btn-nova vr-btn-primary w-10 h-10" data-tooltip="Apply filters">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 5h16M7 12h10M10 19h4"/></svg>
                    </button>
                    <a href="{{ route('admin.visa-requests.visaindex') }}" class="vr-btn-nova w-10 h-10" data-tooltip="Reset filters">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 1 2.64 6.36M3 12v6m0-6h6"/></svg>
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- ============ VISA REQUEST LIST ============ --}}
    <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 sm:p-5">
        <div class="flex items-center gap-2 mb-4">
            <h2 class="text-sm font-semibold text-novatext">Visa Requests</h2>
            <span class="text-xs text-novamuted">{{ number_format($visaRequests->total()) }} total</span>
        </div>

        <div class="hidden lg:flex lg:items-center lg:gap-3 lg:px-4 pb-2 mb-1">
            <span class="w-9 flex-shrink-0"></span>
            <span class="w-44 flex-shrink-0 text-[10px] font-semibold uppercase tracking-wide text-novamuted">Applicant</span>
            <span class="w-32 flex-shrink-0 text-[10px] font-semibold uppercase tracking-wide text-novamuted">Visa Details</span>
            <span class="w-32 flex-shrink-0 text-[10px] font-semibold uppercase tracking-wide text-novamuted">Passport</span>
            <span class="w-24 flex-shrink-0 text-[10px] font-semibold uppercase tracking-wide text-novamuted">Nationality</span>
            <span class="w-32 flex-shrink-0 text-[10px] font-semibold uppercase tracking-wide text-novamuted">Documents</span>
            <span class="w-24 flex-shrink-0 text-[10px] font-semibold uppercase tracking-wide text-novamuted">Applied</span>
        </div>

        <div class="space-y-3">
            @forelse($visaRequests as $request)
                @php
                    $fullName = trim($request->first_name . ' ' . ($request->middle_name ?? '') . ' ' . $request->surname);
                    $initials = collect(explode(' ', $fullName))->filter()->map(fn($n) => strtoupper(substr($n, 0, 1)))->take(2)->implode('') ?: '?';
                    $avatar = $avatarPalette[crc32($fullName) % count($avatarPalette)];
                @endphp
                <div class="tt-row flex flex-col lg:flex-row lg:items-center gap-2.5 lg:gap-3 rounded-2xl border border-novaborder p-3.5 lg:p-4">
                    <div class="flex items-center gap-3 lg:contents">
                        <div class="w-9 h-9 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0"
                             style="background:{{ $avatar['bg'] }}; color:{{ $avatar['text'] }}">{{ $initials }}</div>
                        <div class="min-w-0 lg:w-44 lg:flex-shrink-0">
                            <p class="text-sm font-semibold text-novatext truncate">{{ $fullName }}</p>
                            <p class="text-[11px] text-novamuted truncate mt-0.5">{{ $request->gender }} &middot; {{ $request->marital_status }}</p>
                            <span class="inline-flex mt-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-novamuted">#VR{{ str_pad($request->id, 6, '0', STR_PAD_LEFT) }}</span>
                        </div>
                    </div>

                    <div class="lg:w-32 lg:flex-shrink-0 min-w-0">
                        <p class="text-[9px] text-novamuted lg:hidden">Visa Details</p>
                        <p class="text-xs font-semibold text-novatext capitalize">{{ $request->visa_category }} Visa</p>
                        <p class="text-[11px] text-novamuted truncate">
                            {{ $request->visa_type ? ucfirst($request->visa_type) : '' }}
                            {{ $request->visa_plan ? ' · ' . ucfirst($request->visa_plan) : '' }}
                        </p>
                    </div>

                    <div class="lg:w-32 lg:flex-shrink-0 min-w-0">
                        <p class="text-[9px] text-novamuted lg:hidden">Passport</p>
                        <p class="text-xs text-novatext truncate">{{ $request->passport_no }}</p>
                        <p class="text-[11px] text-novamuted">Exp: {{ $request->passport_expiry_date ? $request->passport_expiry_date->format('M j, Y') : 'N/A' }}</p>
                    </div>

                    <div class="lg:w-24 lg:flex-shrink-0">
                        <p class="text-[9px] text-novamuted lg:hidden">Nationality</p>
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-slate-100 text-novamuted">{{ ucfirst($request->nationality) }}</span>
                    </div>

                    <div class="lg:w-32 lg:flex-shrink-0">
                        <p class="text-[9px] text-novamuted lg:hidden">Documents</p>
                        <div class="flex items-center flex-wrap gap-1">
                            @if($request->passport_front)<span class="px-2 py-0.5 rounded-full text-[9px] font-semibold bg-emerald-50 text-emerald-600">Passport</span>@endif
                            @if($request->passport_photo)<span class="px-2 py-0.5 rounded-full text-[9px] font-semibold bg-blue-50 text-novablue">Photo</span>@endif
                            @if($request->other_document)<span class="px-2 py-0.5 rounded-full text-[9px] font-semibold bg-slate-100 text-novamuted">Other</span>@endif
                            @if(!$request->passport_front && !$request->passport_photo && !$request->other_document)<span class="text-[11px] text-novamuted">—</span>@endif
                        </div>
                    </div>

                    <div class="lg:w-24 lg:flex-shrink-0">
                        <p class="text-[9px] text-novamuted lg:hidden">Applied</p>
                        <p class="text-xs text-novamuted">{{ $request->created_at->format('M j, Y') }}</p>
                    </div>

                    <div class="flex items-center gap-1.5 lg:ml-auto">
                        <a href="{{ route('admin.visa-requests.show', $request->id) }}" class="vr-icon-btn has-label">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M2.5 12S6 5 12 5s9.5 7 9.5 7-3.5 7-9.5 7-9.5-7-9.5-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                            View
                        </a>
                    </div>
                </div>
            @empty
                <div class="text-center py-16">
                    <svg class="w-10 h-10 text-novaborder mx-auto mb-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M7 2v4M17 2v4M3 10h18"/></svg>
                    <p class="text-sm font-semibold text-novatext">No visa requests found</p>
                    <p class="text-xs text-novamuted mt-1">Try adjusting your search criteria or filters.</p>
                </div>
            @endforelse
        </div>

        @if($visaRequests->hasPages())
            <div class="flex flex-wrap items-center justify-between gap-3 mt-6 pt-5 border-t border-novaborder">
                <p class="text-xs text-novamuted">Showing {{ $visaRequests->firstItem() }} to {{ $visaRequests->lastItem() }} of {{ number_format($visaRequests->total()) }} entries</p>
                {{ $visaRequests->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
