@extends('admin-nova.layouts.app')

@section('title', 'Contact Messages')

@section('content')
@php
    $subjectLabels = [
        'booking' => 'Booking Assistance', 'cancellation' => 'Cancellation/Refund',
        'modification' => 'Flight Modification', 'complaint' => 'Complaint',
        'feedback' => 'Feedback', 'partnership' => 'Business Partnership', 'other' => 'Other',
    ];
    $subjectTone = ['booking' => 'is-blue', 'cancellation' => 'is-red', 'modification' => 'is-amber', 'complaint' => 'is-red', 'feedback' => 'is-green', 'partnership' => 'is-blue', 'other' => ''];
    $statusTone = ['new' => 'is-amber', 'read' => 'is-blue', 'replied' => 'is-green'];
    $cards = [
        ['Total Messages', 'bi-chat-left-text', 'is-blue', $stats['total'] ?? 0],
        ['New Messages', 'bi-clock', 'is-amber', $stats['new'] ?? 0],
        ['Read Messages', 'bi-eye', 'is-cyan', $stats['read'] ?? 0],
        ['Replied Messages', 'bi-check2-circle', 'is-green', $stats['replied'] ?? 0],
    ];
@endphp
<div class="tbp-admin-page">
    <header class="tbp-admin-header">
        <div><h1>Contact Messages Management</h1><p>Track and manage all customer inquiries</p></div>
    </header>

    <div class="tbp-admin-stats">
        @foreach($cards as [$label, $icon, $tone, $value])
            <div class="tbp-admin-stat"><span class="tbp-admin-stat-icon {{ $tone }}"><i class="bi {{ $icon }}"></i></span><div><small>{{ $label }}</small><strong>{{ number_format($value) }}</strong></div></div>
        @endforeach
    </div>

    <form method="GET" action="{{ route('admin.contact-messages.index') }}" class="tbp-admin-filters">
        <label class="tbp-admin-field is-wide"><span>Search messages</span>
            <span class="tbp-admin-control"><i class="bi bi-search"></i><input type="text" name="search" value="{{ request('search') }}" placeholder="Name, email, subject or booking reference..."></span></label>
        <label class="tbp-admin-field"><span>Subject</span>
            <select name="subject"><option value="">All subjects</option>
                @foreach($subjectLabels as $key => $label)<option value="{{ $key }}" @selected(request('subject') === $key)>{{ $label }}</option>@endforeach
            </select></label>
        <label class="tbp-admin-field"><span>Status</span>
            <select name="status"><option value="">All status</option>
                @foreach(['new' => 'New', 'read' => 'Read', 'replied' => 'Replied'] as $v => $l)<option value="{{ $v }}" @selected(request('status') === $v)>{{ $l }}</option>@endforeach
            </select></label>
        <label class="tbp-admin-field"><span>Date from</span><input type="date" name="date_from" value="{{ request('date_from') }}"></label>
        <label class="tbp-admin-field"><span>Date to</span><input type="date" name="date_to" value="{{ request('date_to') }}"></label>
        <div class="tbp-admin-filter-actions">
            <button type="submit" class="tbp-admin-btn is-primary"><i class="bi bi-funnel"></i> Filter</button>
            <a href="{{ route('admin.contact-messages.index') }}" class="tbp-admin-btn is-icon" title="Reset filters"><i class="bi bi-arrow-clockwise"></i></a>
        </div>
    </form>

    <section class="tbp-admin-card">
        <header class="tbp-admin-card-head"><h2>All Contact Messages <span>{{ number_format($contactMessages->total()) }} total</span></h2></header>
        <table class="tbp-admin-table">
            <thead><tr><th>Contact</th><th>Subject</th><th>Booking Ref</th><th>Message Preview</th><th>Status</th><th>Received</th><th class="is-actions">Actions</th></tr></thead>
            <tbody>
            @forelse($contactMessages as $message)
                @php
                    $fullName = $message->full_name;
                    $initials = collect(explode(' ', $fullName))->filter()->map(fn ($n) => strtoupper(substr($n, 0, 1)))->take(2)->implode('') ?: '?';
                @endphp
                <tr class="{{ $message->status === 'new' ? 'is-unread' : '' }}">
                    <td data-label="Contact">
                        <div class="tbp-admin-person"><span class="tbp-admin-avatar is-tone-{{ crc32($fullName) % 5 }}">{{ $initials }}</span>
                            <div><strong>{{ $fullName }}</strong><small>{{ $message->email }}</small></div></div>
                    </td>
                    <td data-label="Subject"><span class="tbp-admin-pill {{ $subjectTone[$message->subject] ?? '' }}">{{ $subjectLabels[$message->subject] ?? ucfirst($message->subject) }}</span></td>
                    <td data-label="Booking Ref">{{ $message->booking_ref ?: '—' }}</td>
                    <td data-label="Preview" class="is-preview">{{ \Illuminate\Support\Str::limit($message->message, 80) }}</td>
                    <td data-label="Status"><span class="tbp-admin-pill {{ $statusTone[$message->status] ?? '' }}">{{ ucfirst($message->status) }}</span></td>
                    <td data-label="Received"><strong>{{ $message->created_at->copy()->timezone(siteTimezone())->format('d M Y') }}</strong><small>{{ displayTime($message->created_at) }}</small></td>
                    <td class="is-actions">
                        <div class="tbp-admin-actions">
                            <a href="{{ route('admin.contact-messages.show', $message->id) }}" title="View details"><i class="bi bi-eye"></i></a>
                            <a href="mailto:{{ $message->email }}" title="Reply via email"><i class="bi bi-reply"></i></a>
                            <form method="POST" action="{{ route('admin.contact-messages.destroy', $message->id) }}" onsubmit="return confirm('Delete this message? This cannot be undone.');">
                                @csrf @method('DELETE')
                                <button type="submit" class="is-danger" title="Delete message"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="tbp-admin-empty"><i class="bi bi-chat-left"></i><strong>No contact messages found</strong><span>Try adjusting your search criteria or filters.</span></td></tr>
            @endforelse
            </tbody>
        </table>
        @include('admin-nova.partials._tbp-pagination', ['paginator' => $contactMessages])
    </section>
</div>
@endsection
