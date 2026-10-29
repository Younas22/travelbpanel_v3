@extends('admin-nova.layouts.app')

@section('title', 'Support Tickets')

@section('content')
@php
    $cards = [
        ['Total Tickets', 'bi-ticket-perforated', 'is-blue', $stats['total'] ?? 0],
        ['Open Tickets', 'bi-envelope-open', 'is-amber', $stats['open'] ?? 0],
        ['In Progress', 'bi-hourglass-split', 'is-violet', $stats['in_progress'] ?? 0],
        ['Resolved', 'bi-check2-circle', 'is-green', $stats['resolved'] ?? 0],
    ];
    $statusTone = ['open' => 'is-amber', 'in_progress' => 'is-blue', 'resolved' => 'is-green', 'closed' => ''];
    $priorityTone = ['low' => '', 'normal' => 'is-blue', 'high' => 'is-amber', 'urgent' => 'is-red'];
@endphp
<div class="tbp-admin-page">
    <header class="tbp-admin-header">
        <div><h1>Support Tickets</h1><p>Tickets raised by customers and agents</p></div>
    </header>

    <div class="tbp-admin-stats">
        @foreach($cards as [$label, $icon, $tone, $value])
            <div class="tbp-admin-stat"><span class="tbp-admin-stat-icon {{ $tone }}"><i class="bi {{ $icon }}"></i></span><div><small>{{ $label }}</small><strong>{{ number_format($value) }}</strong></div></div>
        @endforeach
    </div>

    <form method="GET" action="{{ route('admin.support.index') }}" class="tbp-admin-filters">
        <label class="tbp-admin-field is-wide"><span>Search tickets</span>
            <span class="tbp-admin-control"><i class="bi bi-search"></i><input type="text" name="search" value="{{ request('search') }}" placeholder="Ticket #, subject..."></span></label>
        <label class="tbp-admin-field"><span>Source</span>
            <select name="source"><option value="">All</option>
                @foreach(['user' => 'Customers', 'agent' => 'Agents'] as $v => $l)<option value="{{ $v }}" @selected(request('source') === $v)>{{ $l }}</option>@endforeach
            </select></label>
        <label class="tbp-admin-field"><span>Status</span>
            <select name="status"><option value="">All status</option>
                @foreach(['open' => 'Open', 'in_progress' => 'In Progress', 'resolved' => 'Resolved', 'closed' => 'Closed'] as $v => $l)<option value="{{ $v }}" @selected(request('status') === $v)>{{ $l }}</option>@endforeach
            </select></label>
        <label class="tbp-admin-field"><span>Priority</span>
            <select name="priority"><option value="">All priorities</option>
                @foreach(['low' => 'Low', 'normal' => 'Normal', 'high' => 'High', 'urgent' => 'Urgent'] as $v => $l)<option value="{{ $v }}" @selected(request('priority') === $v)>{{ $l }}</option>@endforeach
            </select></label>
        <div class="tbp-admin-filter-actions">
            <button type="submit" class="tbp-admin-btn is-primary"><i class="bi bi-funnel"></i> Filter</button>
            <a href="{{ route('admin.support.index') }}" class="tbp-admin-btn is-icon" title="Reset filters"><i class="bi bi-arrow-clockwise"></i></a>
        </div>
    </form>

    <section class="tbp-admin-card">
        <header class="tbp-admin-card-head"><h2>All tickets <span>{{ number_format($tickets->total()) }} total</span></h2></header>
        <table class="tbp-admin-table">
            <thead><tr><th>Ticket</th><th>Raised By</th><th>Subject</th><th>Priority</th><th>Status</th><th>Last Activity</th><th class="is-actions">Actions</th></tr></thead>
            <tbody>
            @forelse($tickets as $ticket)
                @php $submitter = $ticket->user ?? $ticket->agent; $name = $submitter ? trim($submitter->first_name . ' ' . $submitter->last_name) : 'Unknown'; @endphp
                <tr>
                    <td data-label="Ticket"><a href="{{ route('admin.support.show', $ticket) }}" class="tbp-admin-link">#{{ $ticket->ticket_number }}</a><small>{{ $ticket->created_at->format('M j, Y') }}</small></td>
                    <td data-label="Raised By"><strong>{{ $name }}</strong><small>{{ $ticket->agent_id ? 'Agent' : 'Customer' }}</small></td>
                    <td data-label="Subject" class="is-preview">{{ \Illuminate\Support\Str::limit($ticket->subject, 60) }}</td>
                    <td data-label="Priority"><span class="tbp-admin-pill {{ $priorityTone[$ticket->priority] ?? '' }}">{{ ucfirst($ticket->priority) }}</span></td>
                    <td data-label="Status"><span class="tbp-admin-pill {{ $statusTone[$ticket->status] ?? '' }}">{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</span></td>
                    <td data-label="Last Activity">{{ ($ticket->last_activity_at ?? $ticket->created_at)->diffForHumans() }}</td>
                    <td class="is-actions"><div class="tbp-admin-actions"><a href="{{ route('admin.support.show', $ticket) }}" title="View ticket"><i class="bi bi-eye"></i></a></div></td>
                </tr>
            @empty
                <tr><td colspan="7" class="tbp-admin-empty"><i class="bi bi-inbox"></i><strong>No tickets found</strong><span>{{ request()->hasAny(['search', 'source', 'status', 'priority']) ? 'No support tickets match these filters.' : 'No support tickets have been raised yet.' }}</span></td></tr>
            @endforelse
            </tbody>
        </table>
        @include('admin-nova.partials._tbp-pagination', ['paginator' => $tickets])
    </section>
</div>
@endsection
