@extends('admin.layouts.app')

@section('title', 'Ticket #' . $ticket->ticket_number)

@section('content')
    <div class="content-area">

        <div class="d-flex align-items-center gap-2 mb-3">
            <a href="{{ route('admin.support.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Tickets
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success d-flex align-items-center gap-2 mb-3">
                <i class="bi bi-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        @php
            $submitter = $ticket->user ?? $ticket->agent;
            $statusBadge = ['open' => 'bg-warning', 'in_progress' => 'bg-info', 'resolved' => 'bg-success', 'closed' => 'bg-secondary'];
        @endphp

        <div class="row g-3">
            <div class="col-lg-8">
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between flex-wrap gap-2">
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
                </div>

                <div class="card">
                    <div class="card-header fw-semibold">Conversation</div>
                    <div class="card-body d-flex flex-column gap-3">

                        <div class="d-flex gap-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 fw-bold" style="width:36px; height:36px; background:#e8f4fd; color:#0077BE; font-size:.8rem;">
                                {{ strtoupper(substr($submitter->first_name ?? '?', 0, 1)) }}
                            </div>
                            <div class="flex-grow-1 min-w-0">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="fw-semibold">{{ $submitter ? trim($submitter->first_name . ' ' . $submitter->last_name) : 'Unknown' }}</span>
                                    <span class="badge bg-light text-dark border">{{ $ticket->agent_id ? 'Agent' : 'Customer' }}</span>
                                    <span class="text-muted small">{{ $ticket->created_at->diffForHumans() }}</span>
                                </div>
                                <div class="p-3 rounded bg-light" style="white-space: pre-line;">{{ $ticket->description }}</div>
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
                                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 fw-bold {{ $isAdmin ? 'bg-primary text-white' : '' }}" style="width:36px; height:36px; {{ $isAdmin ? '' : 'background:#e8f4fd; color:#0077BE;' }} font-size:.8rem;">
                                    {{ strtoupper(substr($reply->sender->first_name ?? '?', 0, 1)) }}
                                </div>
                                <div class="flex-grow-1 min-w-0">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <span class="fw-semibold">{{ $reply->sender->first_name ?? 'Unknown' }} {{ $reply->sender->last_name ?? '' }}</span>
                                        @if($isAdmin)
                                            <span class="badge bg-primary">Support Team (You)</span>
                                        @endif
                                        <span class="text-muted small">{{ $reply->created_at->diffForHumans() }}</span>
                                    </div>
                                    <div class="p-3 rounded {{ $isAdmin ? 'bg-primary bg-opacity-10' : 'bg-light' }}" style="white-space: pre-line;">{{ $reply->message }}</div>
                                </div>
                            </div>
                        @endforeach

                    </div>

                    @if($ticket->status !== 'closed')
                    <div class="card-body border-top">
                        <form method="POST" action="{{ route('admin.support.reply', $ticket) }}">
                            @csrf
                            <label class="form-label">Reply as Support Team</label>
                            <textarea name="message" rows="3" required placeholder="Type your reply..." class="form-control @error('message') is-invalid @enderror"></textarea>
                            @error('message')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="d-flex justify-content-end mt-3">
                                <button type="submit" class="btn btn-primary"><i class="bi bi-send"></i> Send Reply</button>
                            </div>
                        </form>
                    </div>
                    @else
                    <div class="card-body border-top text-center text-muted small">
                        <i class="bi bi-lock"></i> This ticket is closed.
                    </div>
                    @endif
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card mb-3">
                    <div class="card-header fw-semibold">Ticket Status</div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.support.update-status', $ticket) }}">
                            @csrf
                            @method('PATCH')
                            <select name="status" class="form-select mb-2" onchange="this.form.submit()">
                                <option value="open" {{ $ticket->status === 'open' ? 'selected' : '' }}>Open</option>
                                <option value="in_progress" {{ $ticket->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="resolved" {{ $ticket->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                                <option value="closed" {{ $ticket->status === 'closed' ? 'selected' : '' }}>Closed</option>
                            </select>
                            <div class="form-text">Changing this updates immediately.</div>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header fw-semibold">{{ $ticket->agent_id ? 'Agent' : 'Customer' }} Details</div>
                    <div class="card-body small">
                        @if($submitter)
                            <div class="mb-2"><span class="text-muted">Name:</span> {{ trim($submitter->first_name . ' ' . $submitter->last_name) }}</div>
                            <div class="mb-2"><span class="text-muted">Email:</span> {{ $submitter->email }}</div>
                            @if($submitter->phone)
                                <div class="mb-2"><span class="text-muted">Phone:</span> {{ $submitter->phone }}</div>
                            @endif
                            @if($ticket->agent_id && $submitter->company_name)
                                <div class="mb-0"><span class="text-muted">Company:</span> {{ $submitter->company_name }}</div>
                            @endif
                        @else
                            <p class="text-muted mb-0">Account no longer exists.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
