@extends('agent-modern.layouts.app')
@section('title', 'Request Top-Up')

@section('content')

    <div class="ap-page-header">
        <div class="ap-page-header-left">
            <div class="ap-icon-badge"><i class="bi bi-plus-circle"></i></div>
            <div>
                <h4 class="ap-page-title">Request Wallet Top-Up</h4>
                <p class="ap-page-sub">Submit payment details for admin verification</p>
            </div>
        </div>
    </div>

    <div class="ap-breadcrumb">
        <a href="{{ route('agent.dashboard') }}">Dashboard</a>
        <i class="bi bi-chevron-right"></i>
        <a href="{{ route('agent.wallet.index') }}">My Wallet</a>
        <i class="bi bi-chevron-right"></i>
        <span>Request Top-Up</span>
    </div>

    <div style="max-width: 560px; margin: 0 auto;">
        <div class="am-card">
            <div class="am-card-header">Top-Up Request Form</div>
            <div class="am-card-body">

                @if($pendingRequest)
                <div class="wal-pending-notice">
                    <div class="wal-pending-icon"><i class="bi bi-clock-history"></i></div>
                    <div>
                        <p style="font-weight: 650;">Pending Request</p>
                        <p>You have a pending top-up request of <strong>PKR {{ number_format($pendingRequest->amount, 2) }}</strong>
                           submitted on {{ $pendingRequest->created_at->format('d M Y') }}. Please wait for admin review.</p>
                    </div>
                </div>
                @else
                <p class="form-text mb-4">
                    Submit your payment details below. Admin will verify and credit balance to your wallet.
                </p>

                <form method="POST" action="{{ route('agent.wallet.topup.submit') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Amount (PKR) <span style="color: var(--primary-color);">*</span></label>
                        <input type="number" name="amount" class="form-control @error('amount') is-invalid @enderror"
                               placeholder="e.g. 50000" min="100" step="1" value="{{ old('amount') }}" required>
                        @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Payment Method <span style="color: var(--primary-color);">*</span></label>
                        <select name="payment_method" class="form-select @error('payment_method') is-invalid @enderror" required>
                            <option value="">Select method</option>
                            <option value="Bank Transfer" {{ old('payment_method') === 'Bank Transfer' ? 'selected' : '' }}>Bank Transfer</option>
                            <option value="Cash"          {{ old('payment_method') === 'Cash'          ? 'selected' : '' }}>Cash</option>
                            <option value="Easypaisa"     {{ old('payment_method') === 'Easypaisa'     ? 'selected' : '' }}>Easypaisa</option>
                            <option value="JazzCash"      {{ old('payment_method') === 'JazzCash'      ? 'selected' : '' }}>JazzCash</option>
                            <option value="Other"         {{ old('payment_method') === 'Other'         ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('payment_method') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Payment Proof (optional)</label>
                        <input type="file" name="payment_proof" class="form-control @error('payment_proof') is-invalid @enderror" accept=".jpg,.jpeg,.png,.pdf">
                        <div class="form-text">Upload screenshot or receipt. Max 2MB. (JPG, PNG, PDF)</div>
                        @error('payment_proof') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Note (optional)</label>
                        <textarea name="note" rows="3" class="form-control" placeholder="Transaction ID, bank name, etc.">{{ old('note') }}</textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="ap-btn-primary" style="flex: 1; justify-content: center;">
                            <i class="bi bi-send"></i> Submit Request
                        </button>
                        <a href="{{ route('agent.wallet.index') }}" class="ap-btn-outline">
                            <i class="bi bi-arrow-left"></i> Back
                        </a>
                    </div>
                </form>
                @endif

            </div>
        </div>
    </div>

@endsection
