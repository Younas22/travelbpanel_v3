@extends('admin-nova.layouts.app')

@section('title', 'Contact Message Details')

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
    #cmsPage, #cmsPage *, #cmsPage *::before, #cmsPage *::after { box-sizing: border-box; }
    #cmsPage { font-family: 'Plus Jakarta Sans', sans-serif; }
    #cmsPage h1, #cmsPage h2, #cmsPage h3, #cmsPage p { margin: 0; padding: 0; }
    #cmsPage a { text-decoration: none; color: inherit; }
    #cmsPage button { font: inherit; color: inherit; background: none; border: none; cursor: pointer; padding: 0; }
    #cmsPage svg { display: block; }
    #cmsPage .tt-fade-in { animation: cmsFadeIn .5s ease both; }
    @keyframes cmsFadeIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }

    #cmsPage .cm-btn-nova {
        display: inline-flex; align-items: center; justify-content: center; gap: 6px;
        border: 1px solid #E5E7EB; border-radius: 9999px; color: #000; background: #fff;
        white-space: nowrap; transition: background .2s ease, border-color .2s ease;
    }
    #cmsPage .cm-btn-nova:hover { background: #F7F8FC; }
    #cmsPage select.cm-status-select {
        border: 1px solid #E5E7EB; border-radius: 9999px; padding: 8px 14px; font-size: 12px;
        font-weight: 600; font-family: 'Plus Jakarta Sans', sans-serif; background: #fff; color: #000;
    }
</style>
@endpush

@section('content')
@php
    $subjectLabels = [
        'booking' => 'Booking Assistance', 'cancellation' => 'Cancellation/Refund',
        'modification' => 'Flight Modification', 'complaint' => 'Complaint',
        'feedback' => 'Feedback', 'partnership' => 'Business Partnership', 'other' => 'Other',
    ];
    $subjectText = $subjectLabels[$contactMessage->subject] ?? ucfirst($contactMessage->subject);
    $statusStyle = [
        'new' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-600', 'label' => 'New'],
        'read' => ['bg' => 'bg-blue-50', 'text' => 'text-novablue', 'label' => 'Read'],
        'replied' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600', 'label' => 'Replied'],
    ];
    $st = $statusStyle[$contactMessage->status] ?? ['bg' => 'bg-slate-100', 'text' => 'text-novamuted', 'label' => ucfirst($contactMessage->status)];
@endphp

<div id="cmsPage" class="tt-fade-in font-jakarta max-w-4xl mx-auto">

    <div class="mb-5">
        <a href="{{ route('admin.contact-messages.index') }}" class="cm-btn-nova px-4 py-2.5 text-xs font-semibold">
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            Back to Messages
        </a>
    </div>

    {{-- ===== HEADER CARD ===== --}}
    <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 sm:p-5 mb-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-lg font-bold text-novatext">Message #CM{{ str_pad($contactMessage->id, 6, '0', STR_PAD_LEFT) }}</h1>
                <p class="text-xs text-novamuted mt-1">
                    Received: {{ $contactMessage->created_at->format('F j, Y \a\t g:i A') }}
                    ({{ $contactMessage->created_at->diffForHumans() }})
                </p>
            </div>
            <form method="POST" action="{{ route('admin.contact-messages.update-status', $contactMessage->id) }}">
                @csrf
                @method('PATCH')
                <select name="status" class="cm-status-select" onchange="this.form.submit()">
                    <option value="new" {{ $contactMessage->status === 'new' ? 'selected' : '' }}>New</option>
                    <option value="read" {{ $contactMessage->status === 'read' ? 'selected' : '' }}>Read</option>
                    <option value="replied" {{ $contactMessage->status === 'replied' ? 'selected' : '' }}>Replied</option>
                </select>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
        {{-- Contact Information --}}
        <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 sm:p-5">
            <h2 class="text-sm font-semibold text-novatext flex items-center gap-2 mb-4">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="3.25"/><path d="M4.75 19c.6-3.7 3.4-6 7.25-6s6.65 2.3 7.25 6"/></svg>
                Contact Information
            </h2>
            <div class="space-y-3">
                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-novamuted mb-1">Full Name</p>
                    <p class="text-xs font-semibold text-novatext">{{ $contactMessage->full_name }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-novamuted mb-1">Email</p>
                    <a href="mailto:{{ $contactMessage->email }}" class="text-xs text-novablue font-semibold">{{ $contactMessage->email }}</a>
                </div>
                @if($contactMessage->phone)
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-novamuted mb-1">Phone</p>
                        <a href="tel:{{ $contactMessage->phone }}" class="text-xs text-novablue font-semibold">{{ $contactMessage->phone }}</a>
                    </div>
                @endif
            </div>
        </div>

        {{-- Message Details --}}
        <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 sm:p-5">
            <h2 class="text-sm font-semibold text-novatext flex items-center gap-2 mb-4">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 8v4M12 16h.01"/></svg>
                Message Details
            </h2>
            <div class="space-y-3">
                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-novamuted mb-1">Subject</p>
                    <span class="inline-flex px-2.5 py-1 rounded-full text-[10px] font-semibold bg-blue-50 text-novablue">{{ $subjectText }}</span>
                </div>
                @if($contactMessage->booking_ref)
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-novamuted mb-1">Booking Reference</p>
                        <span class="inline-flex px-2.5 py-1 rounded-full text-[10px] font-semibold bg-slate-100 text-novatext font-mono">{{ $contactMessage->booking_ref }}</span>
                    </div>
                @endif
                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-novamuted mb-1">Status</p>
                    <span class="inline-flex px-2.5 py-1 rounded-full text-[10px] font-semibold {{ $st['bg'] }} {{ $st['text'] }}">{{ $st['label'] }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== MESSAGE CONTENT ===== --}}
    <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 sm:p-5 mb-5">
        <h2 class="text-sm font-semibold text-novatext flex items-center gap-2 mb-4">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16v13H7l-3 3V4Z"/></svg>
            Message Content
        </h2>
        <div class="bg-novabg rounded-xl p-4">
            <p class="text-sm text-novatext whitespace-pre-wrap leading-relaxed">{{ $contactMessage->message }}</p>
        </div>
    </div>

    {{-- ===== ACTIONS ===== --}}
    <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 sm:p-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <a href="mailto:{{ $contactMessage->email }}?subject={{ rawurlencode('Re: ' . $subjectText) }}&body={{ rawurlencode('Dear ' . $contactMessage->first_name . ",\n\n") }}"
                   class="cm-btn-nova px-4 py-2.5 text-xs font-semibold" style="background:#22C55E; color:#fff; border-color:#22C55E;">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="m9 10-5 2 5 2M4 12h16M15 7l5 5-5 5"/></svg>
                    Reply via Email
                </a>
                @if($contactMessage->phone)
                    <a href="tel:{{ $contactMessage->phone }}" class="cm-btn-nova px-4 py-2.5 text-xs font-semibold" style="background:#06B6D4; color:#fff; border-color:#06B6D4;">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M4.5 4h3l1.5 4-2 1.5a11 11 0 0 0 5.5 5.5L14 13l4 1.5v3a1.5 1.5 0 0 1-1.6 1.5A16 16 0 0 1 3 5.6 1.5 1.5 0 0 1 4.5 4Z"/></svg>
                        Call Customer
                    </a>
                @endif
            </div>
            <form method="POST" action="{{ route('admin.contact-messages.destroy', $contactMessage->id) }}"
                  onsubmit="return confirm('Are you sure you want to delete this message?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="cm-btn-nova px-4 py-2.5 text-xs font-semibold" style="background:#EF4444; color:#fff; border-color:#EF4444;">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0-1 14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2L4 6h16Z"/></svg>
                    Delete Message
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
