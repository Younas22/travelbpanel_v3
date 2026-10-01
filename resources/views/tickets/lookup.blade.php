@extends('common.layout')
@section('content')

<div class="max-w-xl mx-auto px-4 py-16">
    <div class="text-center mb-8">
        <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4" style="background:#e8f4fd;">
            <i class="fas fa-ticket text-2xl" style="color:#0077BE;"></i>
        </div>
        <h1 class="text-2xl font-bold text-gray-800">Track Your Ticket or Booking</h1>
        <p class="text-sm text-gray-500 mt-1">Enter your booking/invoice reference or support ticket number to view it.</p>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 sm:p-8">
        @if(session('error'))
            <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm flex items-center gap-2">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('ticket.lookup') }}">
            @csrf

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Booking/Invoice Reference or Ticket Number</label>
                <input type="text" name="ticket_number" required placeholder="e.g. 20260202025457 or TKT-20261001-04ADC"
                       value="{{ old('ticket_number') }}"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition font-mono">
                @error('ticket_number')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>

            <button type="submit" class="w-full px-5 py-3 rounded-lg text-sm font-semibold text-white mt-2" style="background:#0077BE; border:none; cursor:pointer;">
                <i class="fas fa-search mr-1"></i> Find It
            </button>
        </form>

        <p class="text-xs text-gray-400 text-center mt-5">
            A booking reference opens your invoice; a ticket number (TKT-...) opens your support conversation. Logged-in customers and agents can also see all of these from their own dashboard.
        </p>
    </div>
</div>

@endsection
