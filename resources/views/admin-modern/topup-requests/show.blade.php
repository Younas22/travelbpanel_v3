@extends('admin-modern.layouts.app')
@section('title', 'Top-Up Request #' . $topupRequest->id)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4>Top-Up Request #{{ $topupRequest->id }}</h4>
    <a href="{{ route('admin.topup.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white"><h6 class="mb-0">Request Details</h6></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="text-muted small">Agent</label>
                        <div class="fw-semibold">{{ $topupRequest->agent->full_name }}</div>
                        <div class="small text-muted">{{ $topupRequest->agent->agent_code }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Amount Requested</label>
                        <div class="fs-5 fw-bold text-primary">PKR {{ number_format($topupRequest->amount, 2) }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Payment Method</label>
                        <div>{{ $topupRequest->payment_method ?? '—' }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Status</label>
                        <div><span class="{{ $topupRequest->status_badge_class }}">{{ ucfirst($topupRequest->status) }}</span></div>
                    </div>
                    <div class="col-12">
                        <label class="text-muted small">Note from Agent</label>
                        <div>{{ $topupRequest->note ?? '—' }}</div>
                    </div>
                    @if($topupRequest->payment_proof)
                    <div class="col-12">
                        <label class="text-muted small">Payment Proof</label>
                        <div>
                            <a href="{{ asset('storage/' . $topupRequest->payment_proof) }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-file-earmark-image"></i> View Proof
                            </a>
                        </div>
                    </div>
                    @endif
                    <div class="col-md-6">
                        <label class="text-muted small">Submitted</label>
                        <div>{{ $topupRequest->created_at->format('d M Y, h:i A') }}</div>
                    </div>
                    @if($topupRequest->reviewed_by)
                    <div class="col-md-6">
                        <label class="text-muted small">Reviewed By</label>
                        <div>{{ $topupRequest->reviewedBy?->full_name }} — {{ $topupRequest->reviewed_at?->format('d M Y, h:i A') }}</div>
                    </div>
                    @endif
                    @if($topupRequest->rejection_note)
                    <div class="col-12">
                        <label class="text-muted small">Rejection Reason</label>
                        <div class="text-danger">{{ $topupRequest->rejection_note }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($topupRequest->status === 'pending')
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-success text-white"><h6 class="mb-0">Approve Request</h6></div>
            <div class="card-body">
                <p class="small text-muted">PKR {{ number_format($topupRequest->amount, 2) }} will be added to agent's wallet.</p>
                <form method="POST" action="{{ route('admin.topup.approve', $topupRequest) }}">
                    @csrf
                    <button type="submit" class="btn btn-success w-100"
                            onclick="return confirm('Approve this top-up request?')">
                        <i class="bi bi-check-circle"></i> Approve
                    </button>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-danger text-white"><h6 class="mb-0">Reject Request</h6></div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.topup.reject', $topupRequest) }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea name="rejection_note" class="form-control" rows="3" required
                                  placeholder="Tell agent why..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-danger w-100">Reject Request</button>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
