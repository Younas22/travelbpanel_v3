@extends('agent.layouts.app')
@section('title', 'Visa Applications')

@section('content')
<div class="flex justify-between items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-passport"></i> Visa Applications</h4>
    <a href="{{ route('agent.visa.apply') }}" class="ap-btn-primary">
        <i class="bi bi-plus-circle"></i> New Application
    </a>
</div>

{{-- Filters --}}
<div class="ap-card mb-3">
    <div class="ap-card-body py-2">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-9 gap-2 items-center">
            <div class="md:col-span-4">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search name, passport..." value="{{ request('search') }}">
            </div>
            <div class="md:col-span-3">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="md:col-span-2">
                <button type="submit" class="ap-btn-primary w-full justify-center"><i class="bi bi-funnel"></i> Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="ap-card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Applicant</th>
                    <th>Visa Type</th>
                    <th>Passport No</th>
                    <th>Nationality</th>
                    <th>Submitted</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($visaRequests as $visa)
                <tr>
                    <td>
                        <div class="fw-semibold">{{ $visa->first_name }} {{ $visa->surname }}</div>
                        <div class="text-muted small">{{ $visa->visa_plan }}</div>
                    </td>
                    <td><span class="badge bg-primary">{{ strtoupper($visa->visa_type ?? '-') }}</span></td>
                    <td><code>{{ $visa->passport_no }}</code></td>
                    <td>{{ ucfirst($visa->nationality) }}</td>
                    <td class="text-muted small">{{ $visa->created_at->format('d M Y') }}</td>
                    <td>
                        @php
                            $status = $visa->status ?? 'pending';
                            $color = match($status) { 'approved' => 'success', 'rejected' => 'danger', default => 'warning' };
                        @endphp
                        <span class="badge bg-{{ $color }}">{{ ucfirst($status) }}</span>
                    </td>
                    <td>
                        <a href="{{ route('agent.visa.status', $visa->id) }}" class="ap-btn-outline-sm">View</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        No visa applications yet.
                        <a href="{{ route('agent.visa.apply') }}">Submit one now</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($visaRequests->hasPages())
    <div class="card-footer">{{ $visaRequests->links() }}</div>
    @endif
</div>
@endsection
