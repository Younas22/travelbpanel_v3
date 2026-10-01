@extends('agent-modern.layouts.app')
@section('title', 'Support Tickets')

@section('content')

    <div class="ap-page-header">
        <div class="ap-page-header-left">
            <div class="ap-icon-badge"><i class="bi bi-headset"></i></div>
            <div>
                <h4 class="ap-page-title">Support Tickets</h4>
                <p class="ap-page-sub">Get help from our support team</p>
            </div>
        </div>
        <button type="button" class="ap-btn-primary" data-bs-toggle="modal" data-bs-target="#ticketModal">
            <i class="bi bi-plus-lg"></i> Create Ticket
        </button>
    </div>

    <div class="ap-breadcrumb">
        <a href="{{ route('agent.dashboard') }}">Dashboard</a>
        <i class="bi bi-chevron-right"></i>
        <span>Support Tickets</span>
    </div>

    @if(session('success'))
        <div class="alert alert-success d-flex align-items-center gap-2 mb-4">
            <i class="bi bi-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    <div class="am-card mb-0">
        @if($tickets->isEmpty())
            <div class="text-center py-5">
                <div class="ap-icon-badge mx-auto mb-3" style="width:64px; height:64px; font-size:1.5rem;"><i class="bi bi-ticket-perforated"></i></div>
                <h6>No tickets found</h6>
                <p class="text-muted small mb-3">Create your first support ticket</p>
                <button type="button" class="ap-btn-primary" data-bs-toggle="modal" data-bs-target="#ticketModal">
                    <i class="bi bi-plus-lg"></i> Create Ticket
                </button>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Subject</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $statusBadge = [
                                'open' => 'bg-warning',
                                'in_progress' => 'bg-info',
                                'resolved' => 'bg-success',
                                'closed' => 'bg-secondary',
                            ];
                        @endphp
                        @foreach($tickets as $i => $ticket)
                        <tr>
                            <td class="text-muted small">{{ $i+1 }}</td>
                            <td>
                                <a href="{{ route('agent.support.show', $ticket) }}" class="fw-semibold text-decoration-none">{{ $ticket->subject }}</a>
                                <div class="text-muted small font-monospace">{{ $ticket->ticket_number }}</div>
                            </td>
                            <td><span class="badge bg-primary">{{ ucfirst($ticket->priority) }}</span></td>
                            <td><span class="badge {{ $statusBadge[$ticket->status] ?? 'bg-secondary' }}">{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</span></td>
                            <td class="text-muted small">{{ $ticket->created_at->format('M d, Y') }}</td>
                            <td>
                                <a href="{{ route('agent.support.show', $ticket) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Create Ticket Modal --}}
    <div class="modal fade" id="ticketModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('agent.support.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Create New Ticket</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Subject <span class="text-danger">*</span></label>
                            <input type="text" name="subject" class="form-control @error('subject') is-invalid @enderror" required placeholder="Enter ticket subject" value="{{ old('subject') }}">
                            @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Priority</label>
                            <select name="priority" class="form-select">
                                <option value="low">Low</option>
                                <option value="normal" selected>Normal</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description <span class="text-danger">*</span></label>
                            <textarea name="description" rows="4" class="form-control @error('description') is-invalid @enderror" required placeholder="Describe your issue...">{{ old('description') }}</textarea>
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-1">
                            <label class="form-label">Attachment <span class="text-muted small">(Optional)</span></label>
                            <input type="file" name="attachment" class="form-control" accept=".jpg,.jpeg,.png,.gif,.pdf,.zip">
                            <div class="form-text">Max 5MB. Allowed: JPG, PNG, GIF, PDF, ZIP</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="ap-btn-primary"><i class="bi bi-send"></i> Create Ticket</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    @if($errors->any())
        document.addEventListener('DOMContentLoaded', function () {
            new bootstrap.Modal(document.getElementById('ticketModal')).show();
        });
    @endif
</script>
@endpush
