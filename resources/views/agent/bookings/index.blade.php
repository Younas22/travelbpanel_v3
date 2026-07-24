@extends('agent.layouts.app')
@section('title', 'My Bookings')

@section('content')

<div class="flex items-center gap-3 mb-5">
    <div class="w-9 h-9 rounded-lg flex items-center justify-center ap-tint-bg">
        <i class="fas fa-calendar-check ap-accent"></i>
    </div>
    <div>
        <h4 class="text-lg font-bold text-gray-800">My Bookings</h4>
        <p class="text-xs text-gray-400">All your bookings in one place</p>
    </div>
</div>

<div class="flex items-center gap-2 text-xs text-gray-400 mb-4">
    <a href="{{ route('agent.dashboard') }}" class="ap-accent-link">Dashboard</a>
    <i class="fas fa-chevron-right text-gray-300"></i>
    <span class="text-gray-600">My Bookings</span>
</div>

<div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden mb-4">
    <div class="flex items-center gap-1 px-4 py-3 border-b border-gray-100 overflow-x-auto">
        <a href="{{ route('agent.bookings.index') }}"
           class="px-4 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition {{ $type === 'all' ? 'ap-tab-pill-active' : 'ap-tab-pill-inactive' }}">
            <i class="fas fa-th-large mr-1"></i> All
        </a>
        <a href="{{ route('agent.bookings.index', ['type' => 'hotel']) }}"
           class="px-4 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition {{ $type === 'hotel' ? 'ap-tab-pill-active' : 'ap-tab-pill-inactive' }}">
            <i class="fas fa-hotel mr-1"></i> Hotels
        </a>
        <a href="{{ route('agent.bookings.index', ['type' => 'flight']) }}"
           class="px-4 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition {{ $type === 'flight' ? 'ap-tab-pill-active' : 'ap-tab-pill-inactive' }}">
            <i class="fas fa-plane mr-1"></i> Flights
        </a>
        <a href="{{ route('agent.bookings.index', ['type' => 'tour']) }}"
           class="px-4 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition {{ $type === 'tour' ? 'ap-tab-pill-active' : 'ap-tab-pill-inactive' }}">
            <i class="fas fa-map-marker-alt mr-1"></i> Tours
        </a>
        <a href="{{ route('agent.bookings.index', ['type' => 'umrah']) }}"
           class="px-4 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition {{ $type === 'umrah' ? 'ap-tab-pill-active' : 'ap-tab-pill-inactive' }}">
            <i class="fas fa-moon mr-1"></i> Umrah
        </a>
    </div>

    @if($bookings->isEmpty())
    <div class="text-center py-12">
        <div class="w-14 h-14 rounded-full flex items-center justify-center mx-auto mb-3 ap-tint-bg">
            <i class="fas fa-calendar-times text-xl ap-accent"></i>
        </div>
        <p class="text-sm font-semibold text-gray-600 mb-1">No bookings found</p>
        <p class="text-xs text-gray-400">Your bookings will appear here once made.</p>
    </div>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50">
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Reference</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Type</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Details</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Amount</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Date</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($bookings as $booking)
                @php
                    $typeLabel = ucfirst($booking['booking_type']);
                    $typeColors = ['hotel' => 'primary', 'flight' => 'success', 'tour' => 'warning', 'umrah' => 'info', 'visa' => 'secondary'];
                    $color = $typeColors[$booking['booking_type']] ?? 'secondary';
                    $statusColors = ['confirmed' => 'success', 'pending' => 'warning', 'cancelled' => 'danger'];
                    $statusColor = $statusColors[$booking['status'] ?? ''] ?? 'secondary';
                @endphp
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3 text-xs font-mono">
                        @php
                            $agentInvoiceRoute = match($booking['booking_type'] ?? 'flight') {
                                'flight' => url('flight/invoice', $booking['booking_code']),
                                'hotel'  => url('hotel/invoice', $booking['booking_code']),
                                'tour'   => route('tour.invoice', ['booking_ref' => $booking['booking_code']]),
                                'umrah'  => route('umrah.invoice', ['booking_ref' => $booking['booking_code']]),
                                default  => url('flight/invoice', $booking['booking_code']),
                            };
                        @endphp
                        <a href="{{ $agentInvoiceRoute }}" target="_blank" class="ap-accent-link">
                            <span class="font-semibold">#{{ $booking['booking_code'] ?? 'N/A' }}</span>
                            <i class="fas fa-arrow-up-right-from-square text-xs ml-1"></i>
                        </a>
                    </td>
                    <td class="px-4 py-3">
                        @php
                            $typeBadge = match($booking['booking_type']) {
                                'hotel'  => ['bg-blue-100',   'text-blue-700'],
                                'flight' => ['bg-green-100',  'text-green-700'],
                                'tour'   => ['bg-yellow-100', 'text-yellow-700'],
                                'umrah'  => ['bg-purple-100', 'text-purple-700'],
                                'visa'   => ['bg-gray-100',   'text-gray-700'],
                                default  => ['bg-gray-100',   'text-gray-700'],
                            };
                        @endphp
                        <span class="px-2 py-0.5 {{ $typeBadge[0] }} {{ $typeBadge[1] }} rounded-full text-xs font-medium">
                            {{ $typeLabel }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700">
                        @if($booking['booking_type'] === 'hotel')
                            <span class="font-medium">{{ $booking['hotel_name'] ?? '—' }}</span><br>
                            <span class="text-xs text-gray-400">{{ $booking['check_in'] ?? '' }} → {{ $booking['check_out'] ?? '' }}</span>
                        @elseif($booking['booking_type'] === 'flight')
                            <span class="font-medium">{{ $booking['origin'] ?? '' }} → {{ $booking['destination'] ?? '' }}</span><br>
                            <span class="text-xs text-gray-400">{{ $booking['departure_date'] ?? '' }}</span>
                        @else
                            {{ Str::limit($booking['title'] ?? ($booking['tour_name'] ?? ($booking['package_name'] ?? '—')), 30) }}
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm font-semibold text-gray-800">PKR {{ number_format($booking['total_fare'] ?? 0, 0) }}</td>
                    <td class="px-4 py-3">
                        @php $s = $booking['status'] ?? ''; @endphp
                        @if($s === 'confirmed')
                            <span class="px-2 py-0.5 bg-green-100 text-green-700 rounded-full text-xs font-medium">Confirmed</span>
                        @elseif($s === 'pending')
                            <span class="px-2 py-0.5 bg-yellow-100 text-yellow-700 rounded-full text-xs font-medium">Pending</span>
                        @elseif($s === 'cancelled')
                            <span class="px-2 py-0.5 bg-red-100 text-red-700 rounded-full text-xs font-medium">Cancelled</span>
                        @else
                            <span class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded-full text-xs font-medium">{{ ucfirst($s) ?: 'N/A' }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-400 whitespace-nowrap">{{ \Carbon\Carbon::parse($booking['created_at'])->format('d M Y') }}</td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('agent.bookings.show', [$booking['booking_type'], $booking['id']]) }}"
                               class="px-3 py-1.5 rounded-lg text-xs font-semibold ap-chip-link">
                                View
                            </a>
                            <a href="{{ $agentInvoiceRoute }}" target="_blank"
                               class="px-3 py-1.5 rounded-lg text-xs font-semibold ap-chip-link">
                                <i class="fas fa-file-invoice"></i> Invoice
                            </a>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

@endsection
