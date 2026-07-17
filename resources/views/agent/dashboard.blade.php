@extends('agent.layouts.app')
@section('title', 'Dashboard')

@section('content')

{{-- Welcome Header --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h4 class="text-xl font-bold text-gray-800">Welcome back, {{ auth()->user()->first_name }}!</h4>
        <p class="text-sm text-gray-500 mt-0.5">{{ auth()->user()->company_name }} &bull; {{ auth()->user()->agent_code }}</p>
    </div>
    @if(auth()->user()->hasPermission('wallet.view'))
    <a href="{{ route('agent.wallet.index') }}"
       class="hidden sm:flex items-center gap-2 px-4 py-2 border border-green-300 rounded-lg text-green-700 bg-green-50 hover:bg-green-100 transition text-sm font-semibold"
       style="text-decoration:none;">
        <i class="fas fa-wallet"></i>
        Wallet: <strong>PKR {{ number_format($wallet?->balance ?? 0, 0) }}</strong>
    </a>
    @endif
</div>

{{-- Stats Cards — all 6 in 1 row --}}
<div class="grid grid-cols-6 gap-3 mb-5">

    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-3 flex items-center gap-2">
        <div class="w-8 h-8 rounded-full bg-blue-50 flex items-center justify-center flex-shrink-0">
            <i class="fas fa-building text-blue-500 text-sm"></i>
        </div>
        <div>
            <div class="text-xs text-gray-400 leading-tight">Hotels</div>
            <div class="text-lg font-bold text-gray-800 leading-tight">{{ $stats['total_hotels'] }}</div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-3 flex items-center gap-2">
        <div class="w-8 h-8 rounded-full bg-green-50 flex items-center justify-center flex-shrink-0">
            <i class="fas fa-map-location-dot text-green-500 text-sm"></i>
        </div>
        <div>
            <div class="text-xs text-gray-400 leading-tight">Tours</div>
            <div class="text-lg font-bold text-gray-800 leading-tight">{{ $stats['total_tours'] }}</div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-3 flex items-center gap-2">
        <div class="w-8 h-8 rounded-full bg-yellow-50 flex items-center justify-center flex-shrink-0">
            <i class="fas fa-moon text-yellow-500 text-sm"></i>
        </div>
        <div>
            <div class="text-xs text-gray-400 leading-tight">Umrah</div>
            <div class="text-lg font-bold text-gray-800 leading-tight">{{ $stats['total_umrah'] }}</div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-3 flex items-center gap-2">
        <div class="w-8 h-8 rounded-full bg-blue-50 flex items-center justify-center flex-shrink-0">
            <i class="fas fa-calendar-check text-blue-500 text-sm"></i>
        </div>
        <div>
            <div class="text-xs text-gray-400 leading-tight">Bookings</div>
            <div class="text-lg font-bold text-gray-800 leading-tight">{{ $stats['total'] }}</div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-3 flex items-center gap-2">
        <div class="w-8 h-8 rounded-full bg-yellow-50 flex items-center justify-center flex-shrink-0">
            <i class="fas fa-wallet text-yellow-500 text-sm"></i>
        </div>
        <div>
            <div class="text-xs text-gray-400 leading-tight">Wallet</div>
            <div class="text-sm font-bold text-gray-800 leading-tight">{{ number_format($wallet?->balance ?? 0, 0) }}</div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-3 flex items-center gap-2">
        <div class="w-8 h-8 rounded-full bg-cyan-50 flex items-center justify-center flex-shrink-0">
            <i class="fas fa-chart-line text-cyan-500 text-sm"></i>
        </div>
        <div>
            <div class="text-xs text-gray-400 leading-tight">Spent</div>
            <div class="text-sm font-bold text-gray-800 leading-tight">{{ number_format($wallet?->total_debited ?? 0, 0) }}</div>
        </div>
    </div>

</div>

{{-- Quick Actions --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">

    @if(auth()->user()->hasPermission('module.flights'))
    <a href="{{ route('agent.flights.index') }}"
       class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 text-center hover:shadow-md hover:border-green-200 transition group"
       style="text-decoration:none;">
        <i class="fas fa-plane text-green-500 text-3xl group-hover:scale-110 transition-transform inline-block"></i>
        <div class="mt-2 text-sm font-semibold text-gray-700">Book Flight</div>
    </a>
    @endif

    @if(auth()->user()->hasPermission('module.tours'))
    <a href="{{ route('agent.tours.index') }}"
       class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 text-center hover:shadow-md hover:border-yellow-200 transition group"
       style="text-decoration:none;">
        <i class="fas fa-map-location-dot text-yellow-500 text-3xl group-hover:scale-110 transition-transform inline-block"></i>
        <div class="mt-2 text-sm font-semibold text-gray-700">Book Tour</div>
    </a>
    @endif

    @if(auth()->user()->hasPermission('module.umrah'))
    <a href="{{ route('agent.umrah.index') }}"
       class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 text-center hover:shadow-md hover:border-cyan-200 transition group"
       style="text-decoration:none;">
        <i class="fas fa-moon text-cyan-500 text-3xl group-hover:scale-110 transition-transform inline-block"></i>
        <div class="mt-2 text-sm font-semibold text-gray-700">Book Umrah</div>
    </a>
    @endif

</div>

{{-- Recent Bookings — Full Width --}}
<div class="grid grid-cols-1 gap-4">

    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h6 class="font-semibold text-gray-800">Recent Bookings</h6>
            <a href="{{ route('agent.bookings.index') }}"
               class="text-xs px-3 py-1.5 border border-blue-200 text-blue-600 rounded-lg hover:bg-blue-50 transition"
               style="text-decoration:none;">View All</a>
        </div>
        @if($recentBookings->isEmpty())
            <div class="text-center py-10 text-gray-400 text-sm">
                <i class="fas fa-calendar-xmark text-3xl mb-2 block text-gray-300"></i>
                No bookings yet.
            </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wide">
                        <th class="px-5 py-3 text-left font-semibold">Reference</th>
                        <th class="px-5 py-3 text-left font-semibold">Type</th>
                        <th class="px-5 py-3 text-left font-semibold">Amount</th>
                        <th class="px-5 py-3 text-left font-semibold">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($recentBookings as $booking)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-5 py-3 font-mono text-xs text-blue-600 font-semibold">{{ $booking['booking_code'] ?? 'N/A' }}</td>
                        <td class="px-5 py-3">
                            <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded text-xs font-medium">{{ ucfirst($booking['booking_type']) }}</span>
                        </td>
                        <td class="px-5 py-3 font-semibold text-gray-700">PKR {{ number_format($booking['total_fare'] ?? 0, 0) }}</td>
                        <td class="px-5 py-3 text-gray-500 text-xs">{{ \Carbon\Carbon::parse($booking['created_at'])->format('d M Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    {{-- Wallet Activity — Full Width --}}
    @if(auth()->user()->hasPermission('wallet.view'))
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h6 class="font-semibold text-gray-800">Wallet Activity</h6>
            <a href="{{ route('agent.wallet.transactions') }}"
               class="text-xs px-3 py-1.5 border border-green-200 text-green-600 rounded-lg hover:bg-green-50 transition"
               style="text-decoration:none;">View All</a>
        </div>
        @if($recentTransactions->isEmpty())
            <div class="text-center py-10 text-gray-400 text-sm">
                <i class="fas fa-wallet text-3xl mb-2 block text-gray-300"></i>
                No transactions yet.
            </div>
        @else
        <ul class="divide-y divide-gray-50">
            @foreach($recentTransactions as $txn)
            <li class="px-4 py-3 flex items-center justify-between gap-2">
                <div class="min-w-0">
                    <div class="text-sm font-medium text-gray-700 truncate">{{ Str::limit($txn->note, 30) }}</div>
                    <div class="text-xs text-gray-400 mt-0.5">{{ $txn->created_at->format('d M Y') }}</div>
                </div>
                <span class="{{ $txn->type === 'credit' ? 'text-green-600 bg-green-50' : 'text-red-500 bg-red-50' }} text-xs font-semibold px-2 py-1 rounded-lg whitespace-nowrap flex-shrink-0">
                    {{ $txn->type === 'credit' ? '+' : '-' }} {{ number_format($txn->amount, 0) }}
                </span>
            </li>
            @endforeach
        </ul>
        @endif
    </div>
    @endif

</div>

@endsection
