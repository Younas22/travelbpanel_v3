@extends('agent.layouts.app')
@section('title', 'Request Top-Up')

@section('content')

<div class="flex items-center gap-3 mb-5">
    <div class="w-9 h-9 rounded-lg flex items-center justify-center ap-tint-bg">
        <i class="fas fa-plus-circle ap-accent"></i>
    </div>
    <div>
        <h4 class="text-lg font-bold text-gray-800">Request Wallet Top-Up</h4>
        <p class="text-xs text-gray-400">Submit payment details for admin verification</p>
    </div>
</div>

<div class="flex items-center gap-2 text-xs text-gray-400 mb-4">
    <a href="{{ route('agent.dashboard') }}" class="ap-accent-link">Dashboard</a>
    <i class="fas fa-chevron-right text-gray-300"></i>
    <a href="{{ route('agent.wallet.index') }}" class="ap-accent-link">My Wallet</a>
    <i class="fas fa-chevron-right text-gray-300"></i>
    <span class="text-gray-600">Request Top-Up</span>
</div>

<div class="max-w-xl mx-auto">
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 font-semibold text-sm text-gray-700">
            Top-Up Request Form
        </div>
        <div class="p-5">

            @if($pendingRequest)
            <div class="flex items-start gap-3 p-4 rounded-xl border border-yellow-200 bg-yellow-50 mb-4">
                <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 ap-warning-icon-bg">
                    <i class="fas fa-clock text-yellow-600 text-sm"></i>
                </div>
                <div>
                    <p class="text-sm font-semibold text-yellow-800 mb-0.5">Pending Request</p>
                    <p class="text-xs text-yellow-700">
                        You have a pending top-up request of <strong>PKR {{ number_format($pendingRequest->amount, 2) }}</strong>
                        submitted on {{ $pendingRequest->created_at->format('d M Y') }}. Please wait for admin review.
                    </p>
                </div>
            </div>
            @else
            <p class="text-xs text-gray-400 mb-5">
                Submit your payment details below. Admin will verify and credit balance to your wallet.
            </p>

            <form method="POST" action="{{ route('agent.wallet.topup.submit') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                        Amount (PKR) <span class="ap-accent">*</span>
                    </label>
                    <input type="number" name="amount"
                           class="w-full border rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-400 bg-gray-50 @error('amount') border-red-400 @else border-gray-200 @enderror"
                           placeholder="e.g. 50000" min="100" step="1" value="{{ old('amount') }}" required>
                    @error('amount')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                        Payment Method <span class="ap-accent">*</span>
                    </label>
                    <select name="payment_method"
                            class="w-full border rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-400 bg-gray-50 @error('payment_method') border-red-400 @else border-gray-200 @enderror"
                            required>
                        <option value="">Select method</option>
                        <option value="Bank Transfer" {{ old('payment_method') === 'Bank Transfer' ? 'selected' : '' }}>Bank Transfer</option>
                        <option value="Cash"          {{ old('payment_method') === 'Cash'          ? 'selected' : '' }}>Cash</option>
                        <option value="Easypaisa"     {{ old('payment_method') === 'Easypaisa'     ? 'selected' : '' }}>Easypaisa</option>
                        <option value="JazzCash"      {{ old('payment_method') === 'JazzCash'      ? 'selected' : '' }}>JazzCash</option>
                        <option value="Other"         {{ old('payment_method') === 'Other'         ? 'selected' : '' }}>Other</option>
                    </select>
                    @error('payment_method')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Payment Proof (optional)</label>
                    <input type="file" name="payment_proof"
                           class="w-full border rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-400 bg-gray-50 @error('payment_proof') border-red-400 @else border-gray-200 @enderror"
                           accept=".jpg,.jpeg,.png,.pdf">
                    <p class="text-xs text-gray-400 mt-1">Upload screenshot or receipt. Max 2MB. (JPG, PNG, PDF)</p>
                    @error('payment_proof')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Note (optional)</label>
                    <textarea name="note" rows="3"
                              class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-400 bg-gray-50"
                              placeholder="Transaction ID, bank name, etc.">{{ old('note') }}</textarea>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit"
                            class="flex-1 px-5 py-2.5 rounded-lg text-sm font-semibold text-white flex items-center justify-center gap-2 ap-solid-accent-btn">
                        <i class="fas fa-paper-plane"></i> Submit Request
                    </button>
                    <a href="{{ route('agent.wallet.index') }}"
                       class="px-4 py-2.5 rounded-lg text-sm font-semibold flex items-center gap-2 ap-chip-link">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
            </form>
            @endif

        </div>
    </div>
</div>

@endsection
