@extends('admin.layouts.app')
@section('title', 'Top-Up Requests')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Wallet Top-Up Requests</h4>
        @if($pendingCount > 0)
            <span class="badge bg-warning text-dark">{{ $pendingCount }} pending</span>
        @endif
    </div>
</div>

{{-- Filter --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="d-flex gap-2 align-items-center">
            <select name="status" class="form-select form-select-sm w-150px">
                <option value="">All Status</option>
                <option value="pending"  {{ request('status') === 'pending'  ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
            </select>
            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
            <a href="{{ route('admin.topup.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th><th>Agent</th><th>Amount</th><th>Method</th>
                        <th>Proof</th><th>Status</th><th>Submitted</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $req)
                    <tr>
                        <td>{{ $req->id }}</td>
                        <td>
                            <div class="fw-semibold">{{ $req->agent->full_name }}</div>
                            <div class="small text-muted">{{ $req->agent->agent_code }}</div>
                        </td>
                        <td class="fw-semibold">PKR {{ number_format($req->amount, 2) }}</td>
                        <td>{{ $req->payment_method ?? '—' }}</td>
                        <td>
                            @if($req->payment_proof)
                                <a href="{{ asset('storage/' . $req->payment_proof) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-file-earmark"></i> View
                                </a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td><span class="{{ $req->status_badge_class }}">{{ ucfirst($req->status) }}</span></td>
                        <td class="small text-muted">{{ $req->created_at->format('d M Y, h:i A') }}</td>
                        <td>
                            @if($req->status === 'pending')
                            <div class="d-flex gap-1">
                                <form method="POST" action="{{ route('admin.topup.approve', $req) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success"
                                            onclick="return confirm('Approve PKR {{ number_format($req->amount, 0) }} top-up?')">
                                        <i class="bi bi-check-lg"></i> Approve
                                    </button>
                                </form>
                                <button type="button" class="btn btn-sm btn-danger"
                                        data-bs-toggle="modal" data-bs-target="#rejectModal{{ $req->id }}">
                                    <i class="bi bi-x-lg"></i> Reject
                                </button>
                            </div>

                            {{-- Reject Modal --}}
                            <div class="modal fade" id="rejectModal{{ $req->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST" action="{{ route('admin.topup.reject', $req) }}">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title">Reject Top-Up Request</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <label class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                                                <textarea name="rejection_note" class="form-control" rows="3" required
                                                          placeholder="Tell agent why request was rejected..."></textarea>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-danger">Reject Request</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @else
                                <a href="{{ route('admin.topup.show', $req) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-eye"></i> View
                                </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">No requests found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($requests->hasPages())
    <div class="card-footer bg-white">{{ $requests->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
