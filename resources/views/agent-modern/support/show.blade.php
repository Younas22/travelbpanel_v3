@extends('agent-modern.layouts.app')
@section('title', 'Ticket #' . $ticket->ticket_number)

@section('content')

    <div class="ap-breadcrumb">
        <a href="{{ route('agent.dashboard') }}">Dashboard</a>
        <i class="bi bi-chevron-right"></i>
        <a href="{{ route('agent.support.index') }}">Support Tickets</a>
        <i class="bi bi-chevron-right"></i>
        <span>{{ $ticket->ticket_number }}</span>
    </div>

    @php
        $statusBadge = [
            'open' => 'bg-warning',
            'in_progress' => 'bg-info',
            'resolved' => 'bg-success',
            'closed' => 'bg-secondary',
        ];
    @endphp

    @if(session('success'))
        <div class="alert alert-success d-flex align-items-center gap-2 mb-4">
            <i class="bi bi-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    <div class="am-card mb-4">
        <div class="am-card-body d-flex align-items-start justify-content-between flex-wrap gap-3">
            <div>
                <h4 class="fw-bold mb-1">{{ $ticket->subject }}</h4>
                <p class="text-muted small font-monospace mb-0">{{ $ticket->ticket_number }} &bull; Opened {{ $ticket->created_at->format('M d, Y \a\t h:i A') }}</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary">{{ ucfirst($ticket->priority) }} priority</span>
                <span class="badge {{ $statusBadge[$ticket->status] ?? 'bg-secondary' }}">{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</span>
            </div>
        </div>
    </div>

    <div class="am-card mb-0">
        <div class="am-card-header">Conversation</div>
        <div class="am-card-body d-flex flex-column gap-3">

            <div class="d-flex gap-3">
                <div class="ap-icon-badge flex-shrink-0" style="width:36px; height:36px; font-size:.8rem;">
                    {{ strtoupper(substr(auth()->user()->first_name, 0, 1)) }}
                </div>
                <div class="flex-grow-1 min-w-0">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="fw-semibold">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</span>
                        <span class="text-muted small">{{ $ticket->created_at->diffForHumans() }}</span>
                    </div>
                    <div class="p-3 rounded" style="background: var(--am-input-fill); white-space: pre-line;">{{ $ticket->description }}</div>
                    @if($ticket->attachment)
                        <a href="{{ url('public/assets/uploads/support/' . $ticket->attachment) }}" target="_blank" class="d-inline-flex align-items-center gap-1 mt-2 small">
                            <i class="bi bi-paperclip"></i> View attachment
                        </a>
                    @endif
                </div>
            </div>

            @foreach($ticket->replies as $reply)
                @php $isAdmin = $reply->sender && $reply->sender->isAdmin(); @endphp
                <div class="d-flex gap-3">
                    <div class="ap-icon-badge flex-shrink-0 {{ $isAdmin ? 'bg-primary text-white' : '' }}" style="width:36px; height:36px; font-size:.8rem;">
                        {{ strtoupper(substr($reply->sender->first_name ?? '?', 0, 1)) }}
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="fw-semibold">{{ $reply->sender->first_name ?? 'Unknown' }} {{ $reply->sender->last_name ?? '' }}</span>
                            @if($isAdmin)
                                <span class="badge bg-primary">Support Team</span>
                            @endif
                            <span class="text-muted small">{{ $reply->created_at->diffForHumans() }}</span>
                        </div>
                        <div class="p-3 rounded {{ $isAdmin ? 'bg-primary bg-opacity-10' : '' }}" style="{{ $isAdmin ? '' : 'background: var(--am-input-fill);' }} white-space: pre-line;">{{ $reply->message }}</div>
                    </div>
                </div>
            @endforeach

        </div>

        @if(!in_array($ticket->status, ['closed']))
        <div class="am-card-body border-top">
            <form method="POST" action="{{ route('agent.support.reply', $ticket) }}">
                @csrf
                <label class="form-label">Add a reply</label>
                <textarea name="message" rows="3" required placeholder="Type your message..." class="form-control @error('message') is-invalid @enderror"></textarea>
                @error('message')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <div class="d-flex justify-content-end mt-3">
                    <button type="submit" class="ap-btn-primary"><i class="bi bi-send"></i> Send Reply</button>
                </div>
            </form>
        </div>
        @else
        <div class="am-card-body border-top text-center text-muted small">
            <i class="bi bi-lock"></i> This ticket is closed.
        </div>
        @endif
    </div>

@endsection
