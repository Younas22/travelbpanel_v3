@extends('admin-nova.layouts.app')

@section('title', 'Contact Messages')

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
    #cmPage, #cmPage *, #cmPage *::before, #cmPage *::after { box-sizing: border-box; }
    #cmPage { font-family: 'Plus Jakarta Sans', sans-serif; }
    #cmPage h1, #cmPage h2, #cmPage p { margin: 0; padding: 0; }
    #cmPage a { text-decoration: none; color: inherit; }
    #cmPage button { font: inherit; color: inherit; background: none; border: none; cursor: pointer; padding: 0; }
    #cmPage svg { display: block; }

    #cmPage .tt-fade-in { animation: cmFadeIn .5s ease both; }
    @keyframes cmFadeIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }

    #cmPage .tt-row { transition: background .2s ease, border-color .2s ease, transform .2s ease; }
    #cmPage .tt-row:hover { background: #F7F8FC; border-color: #DBEAFE; transform: translateX(2px); }

    /* Plain CSS backing for every pill/icon button — Tailwind CDN utility
       classes compile at runtime and have a window where they simply aren't
       generated yet, leaving buttons shapeless/colorless. */
    #cmPage .cm-btn-nova {
        display: inline-flex; align-items: center; justify-content: center; gap: 6px;
        border: 1px solid #E5E7EB; border-radius: 9999px; color: #000; background: #fff;
        white-space: nowrap; transition: background .2s ease, border-color .2s ease;
    }
    #cmPage .cm-btn-nova:hover { background: #F7F8FC; }
    #cmPage .cm-btn-primary { background: #2563EB; color: #fff; border-color: #2563EB; }
    #cmPage .cm-btn-primary:hover { background: #1D4ED8; border-color: #1D4ED8; }
    #cmPage .cm-icon-btn {
        width: 30px; height: 30px; border-radius: 9999px; border: 1px solid #E5E7EB;
        display: inline-flex; align-items: center; justify-content: center; background: #fff;
        transition: background .2s ease, border-color .2s ease;
    }
    #cmPage .cm-icon-btn:hover { background: #F7F8FC; }
    #cmPage .cm-icon-success:hover { background: #ECFDF5; border-color: #22C55E; color: #22C55E; }

    /* Material-style hover tooltip (see agents module for the same pattern). */
    #cmPage [data-tooltip] { position: relative; }
    #cmPage [data-tooltip]::after {
        content: attr(data-tooltip); position: absolute; bottom: calc(100% + 8px); left: 50%;
        transform: translateX(-50%) translateY(4px); background: #1F2937; color: #fff;
        font-size: 11px; font-weight: 600; line-height: 1; padding: 6px 10px; border-radius: 6px;
        white-space: nowrap; box-shadow: 0 6px 16px rgba(0,0,0,.18); opacity: 0; visibility: hidden;
        pointer-events: none; transition: opacity .15s ease, transform .15s ease, visibility .15s ease; z-index: 60;
    }
    #cmPage [data-tooltip]::before {
        content: ''; position: absolute; bottom: calc(100% + 3px); left: 50%; transform: translateX(-50%);
        border: 5px solid transparent; border-top-color: #1F2937; opacity: 0; visibility: hidden;
        pointer-events: none; transition: opacity .15s ease, visibility .15s ease; z-index: 60;
    }
    #cmPage [data-tooltip]:hover::after, #cmPage [data-tooltip]:focus-visible::after { opacity: 1; visibility: visible; transform: translateX(-50%) translateY(0); transition-delay: .25s; }
    #cmPage [data-tooltip]:hover::before, #cmPage [data-tooltip]:focus-visible::before { opacity: 1; visibility: visible; transition-delay: .25s; }

    /* Same Bootstrap-collision fix as the agents index header row. */
    #cmPage .cm-thead { }
</style>
@endpush

@section('content')
@php
    $subjectLabels = [
        'booking' => 'Booking Assistance', 'cancellation' => 'Cancellation/Refund',
        'modification' => 'Flight Modification', 'complaint' => 'Complaint',
        'feedback' => 'Feedback', 'partnership' => 'Business Partnership', 'other' => 'Other',
    ];
    $subjectColor = [
        'booking' => ['bg' => 'bg-blue-50', 'text' => 'text-novablue'],
        'cancellation' => ['bg' => 'bg-red-50', 'text' => 'text-novadanger'],
        'modification' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-600'],
        'complaint' => ['bg' => 'bg-red-50', 'text' => 'text-novadanger'],
        'feedback' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600'],
        'partnership' => ['bg' => 'bg-cyan-50', 'text' => 'text-novacyan'],
        'other' => ['bg' => 'bg-slate-100', 'text' => 'text-novamuted'],
    ];
    $statusStyle = [
        'new' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-600', 'label' => 'New'],
        'read' => ['bg' => 'bg-blue-50', 'text' => 'text-novablue', 'label' => 'Read'],
        'replied' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600', 'label' => 'Replied'],
    ];
    $avatarPalette = [
        ['bg' => '#DBEAFE', 'text' => '#1D4ED8'], ['bg' => '#DCFCE7', 'text' => '#15803D'],
        ['bg' => '#FEF3C7', 'text' => '#B45309'], ['bg' => '#FCE7F3', 'text' => '#BE185D'],
        ['bg' => '#EDE9FE', 'text' => '#6D28D9'],
    ];
@endphp

<div id="cmPage" class="tt-fade-in font-jakarta">

    {{-- ============ HEADER ============ --}}
    <div class="mb-6">
        <h1 class="text-lg font-bold text-novatext">Contact Messages Management</h1>
        <p class="text-xs text-novamuted mt-1">Track and manage all customer inquiries</p>
    </div>

    {{-- ============ KPI CARDS ============ --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
        <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-3">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-medium text-novamuted">Total</p>
                <div class="w-7 h-7 rounded-full bg-novablue text-white flex items-center justify-center flex-shrink-0">
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16v13H7l-3 3V4Z"/></svg>
                </div>
            </div>
            <span class="block text-lg font-bold text-novatext">{{ number_format($stats['total'] ?? 0) }}</span>
        </div>
        <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-3">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-medium text-novamuted">New</p>
                <div class="w-7 h-7 rounded-full bg-novawarning text-white flex items-center justify-center flex-shrink-0">
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
                </div>
            </div>
            <span class="block text-lg font-bold text-novatext">{{ number_format($stats['new'] ?? 0) }}</span>
        </div>
        <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-3">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-medium text-novamuted">Read</p>
                <div class="w-7 h-7 rounded-full bg-novacyan text-white flex items-center justify-center flex-shrink-0">
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M2.5 12S6 5 12 5s9.5 7 9.5 7-3.5 7-9.5 7-9.5-7-9.5-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                </div>
            </div>
            <span class="block text-lg font-bold text-novatext">{{ number_format($stats['read'] ?? 0) }}</span>
        </div>
        <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-3">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-medium text-novamuted">Replied</p>
                <div class="w-7 h-7 rounded-full bg-novasuccess text-white flex items-center justify-center flex-shrink-0">
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12.75 11.25 15 15 9.75"/></svg>
                </div>
            </div>
            <span class="block text-lg font-bold text-novatext">{{ number_format($stats['replied'] ?? 0) }}</span>
        </div>
    </div>

    {{-- ============ FILTERS ============ --}}
    <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 sm:p-5 mb-5">
        <form method="GET" action="{{ route('admin.contact-messages.index') }}">
            <div class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[220px]">
                    <label class="block text-xs font-semibold text-novamuted mb-1.5">Search messages</label>
                    <div class="relative">
                        <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-novamuted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Name, email, booking ref..."
                               class="w-full text-sm border border-novaborder rounded-full pl-10 pr-4 py-2.5">
                    </div>
                </div>
                <div class="w-44">
                    <label class="block text-xs font-semibold text-novamuted mb-1.5">Subject</label>
                    <select name="subject" class="w-full text-sm border border-novaborder rounded-full px-3.5 py-2.5">
                        <option value="">All subjects</option>
                        @foreach($subjectLabels as $key => $label)
                            <option value="{{ $key }}" {{ request('subject') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="w-36">
                    <label class="block text-xs font-semibold text-novamuted mb-1.5">Status</label>
                    <select name="status" class="w-full text-sm border border-novaborder rounded-full px-3.5 py-2.5">
                        <option value="">All status</option>
                        <option value="new" {{ request('status') === 'new' ? 'selected' : '' }}>New</option>
                        <option value="read" {{ request('status') === 'read' ? 'selected' : '' }}>Read</option>
                        <option value="replied" {{ request('status') === 'replied' ? 'selected' : '' }}>Replied</option>
                    </select>
                </div>
                <div class="w-40">
                    <label class="block text-xs font-semibold text-novamuted mb-1.5">Date from</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full text-sm border border-novaborder rounded-full px-3.5 py-2.5">
                </div>
                <div class="flex items-center gap-2">
                    <button type="submit" class="cm-btn-nova cm-btn-primary w-10 h-10" data-tooltip="Apply filters">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 5h16M7 12h10M10 19h4"/></svg>
                    </button>
                    <a href="{{ route('admin.contact-messages.index') }}" class="cm-btn-nova w-10 h-10" data-tooltip="Reset filters">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 1 2.64 6.36M3 12v6m0-6h6"/></svg>
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- ============ MESSAGES LIST ============ --}}
    <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 sm:p-5">
        <div class="flex items-center gap-2 mb-4">
            <h2 class="text-sm font-semibold text-novatext">All Contact Messages</h2>
            <span class="text-xs text-novamuted">{{ number_format($contactMessages->total()) }} total</span>
        </div>

        <div class="hidden lg:flex lg:items-center lg:gap-3 lg:px-4 pb-2 mb-1">
            <span class="w-9 flex-shrink-0"></span>
            <span class="w-48 flex-shrink-0 text-[10px] font-semibold uppercase tracking-wide text-novamuted">Contact</span>
            <span class="w-32 flex-shrink-0 text-[10px] font-semibold uppercase tracking-wide text-novamuted">Subject</span>
            <span class="w-24 flex-shrink-0 text-[10px] font-semibold uppercase tracking-wide text-novamuted">Booking Ref</span>
            <span class="flex-1 min-w-0 text-[10px] font-semibold uppercase tracking-wide text-novamuted">Preview</span>
            <span class="w-20 flex-shrink-0 text-[10px] font-semibold uppercase tracking-wide text-novamuted">Status</span>
            <span class="w-28 flex-shrink-0 text-[10px] font-semibold uppercase tracking-wide text-novamuted">Received</span>
            <span class="flex-shrink-0 text-[10px] font-semibold uppercase tracking-wide text-novamuted" style="width:90px;">Actions</span>
        </div>

        <div class="space-y-3">
            @forelse($contactMessages as $message)
                @php
                    $fullName = $message->full_name;
                    $initials = collect(explode(' ', $fullName))->filter()->map(fn($n) => strtoupper(substr($n, 0, 1)))->take(2)->implode('') ?: '?';
                    $avatar = $avatarPalette[crc32($fullName) % count($avatarPalette)];
                    $subj = $subjectColor[$message->subject] ?? $subjectColor['other'];
                    $subjText = $subjectLabels[$message->subject] ?? ucfirst($message->subject);
                    $st = $statusStyle[$message->status] ?? ['bg' => 'bg-slate-100', 'text' => 'text-novamuted', 'label' => ucfirst($message->status)];
                @endphp
                <div class="tt-row flex flex-col lg:flex-row lg:items-center gap-2.5 lg:gap-3 rounded-2xl border {{ $message->status === 'new' ? 'border-amber-200 bg-amber-50/30' : 'border-novaborder' }} p-3.5 lg:p-4">

                    <div class="flex items-center gap-3 lg:contents">
                        <div class="w-9 h-9 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0"
                             style="background:{{ $avatar['bg'] }}; color:{{ $avatar['text'] }}">{{ $initials }}</div>
                        <div class="min-w-0 lg:w-48 lg:flex-shrink-0">
                            <p class="text-sm font-semibold text-novatext truncate flex items-center gap-1.5">
                                {{ $fullName }}
                                @if($message->status === 'new')
                                    <span class="inline-flex px-1.5 py-0.5 rounded-full text-[9px] font-bold bg-novawarning text-white">NEW</span>
                                @endif
                            </p>
                            <p class="text-[11px] text-novamuted truncate mt-0.5">{{ $message->email }}</p>
                        </div>
                        <span class="lg:hidden ml-auto px-2.5 py-1 rounded-full text-[10px] font-semibold {{ $st['bg'] }} {{ $st['text'] }}">{{ $st['label'] }}</span>
                    </div>

                    <div class="lg:w-32 lg:flex-shrink-0 min-w-0">
                        <p class="text-[9px] text-novamuted lg:hidden">Subject</p>
                        <span class="inline-flex px-2.5 py-1 rounded-full text-[10px] font-semibold {{ $subj['bg'] }} {{ $subj['text'] }} truncate">{{ $subjText }}</span>
                    </div>

                    <div class="lg:w-24 lg:flex-shrink-0">
                        <p class="text-[9px] text-novamuted lg:hidden">Booking Ref</p>
                        @if($message->booking_ref)
                            <span class="text-xs font-mono text-novatext">{{ $message->booking_ref }}</span>
                        @else
                            <span class="text-xs text-novamuted">—</span>
                        @endif
                    </div>

                    <div class="lg:flex-1 min-w-0">
                        <p class="text-[9px] text-novamuted lg:hidden">Preview</p>
                        <p class="text-xs text-novamuted truncate">{{ Str::limit($message->message, 90) }}</p>
                    </div>

                    <div class="hidden lg:block lg:w-20 lg:flex-shrink-0">
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold {{ $st['bg'] }} {{ $st['text'] }}">{{ $st['label'] }}</span>
                    </div>

                    <div class="lg:w-28 lg:flex-shrink-0">
                        <p class="text-[9px] text-novamuted lg:hidden">Received</p>
                        <p class="text-xs text-novatext">{{ $message->created_at->format('d M Y') }}</p>
                        <p class="text-[10px] text-novamuted">{{ $message->created_at->format('h:i A') }}</p>
                    </div>

                    <div class="flex items-center gap-1.5 lg:flex-shrink-0" style="width:90px;">
                        <a href="{{ route('admin.contact-messages.show', $message->id) }}" class="cm-icon-btn" data-tooltip="View Details">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M2.5 12S6 5 12 5s9.5 7 9.5 7-3.5 7-9.5 7-9.5-7-9.5-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                        </a>
                        <a href="mailto:{{ $message->email }}" class="cm-icon-btn cm-icon-success" data-tooltip="Reply via Email">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="m9 10-5 2 5 2M4 12h16M15 7l5 5-5 5"/></svg>
                        </a>
                    </div>
                </div>
            @empty
                <div class="text-center py-16">
                    <svg class="w-10 h-10 text-novaborder mx-auto mb-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16v13H7l-3 3V4Z"/></svg>
                    <p class="text-sm font-semibold text-novatext">No contact messages found</p>
                    <p class="text-xs text-novamuted mt-1">Try adjusting your search criteria or filters.</p>
                </div>
            @endforelse
        </div>

        @if($contactMessages->hasPages())
            <div class="flex flex-wrap items-center justify-between gap-3 mt-6 pt-5 border-t border-novaborder">
                <p class="text-xs text-novamuted">Showing {{ $contactMessages->firstItem() }} to {{ $contactMessages->lastItem() }} of {{ number_format($contactMessages->total()) }} entries</p>
                {{ $contactMessages->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
