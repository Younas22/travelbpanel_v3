@extends('user.layouts.app')
@section('title', 'My Bookings')

@section('content')

{{-- Breadcrumb --}}
<div class="flex items-center gap-2 text-xs text-gray-400 mb-4">
    <a href="{{ route('user.dashboard') }}" style="color:#0077BE; text-decoration:none;">Dashboard</a>
    <i class="fas fa-chevron-right text-gray-300"></i>
    <span class="text-gray-600">My Bookings</span>
</div>

{{-- Page Header --}}
<div class="flex items-center gap-3 mb-5">
    <div class="w-9 h-9 rounded-lg flex items-center justify-center" style="background:#e8f4fd;">
        <i class="fas fa-calendar-check" style="color:#0077BE;"></i>
    </div>
    <div>
        <h4 class="text-lg font-bold text-gray-800">My Bookings</h4>
        <p class="text-xs text-gray-400">Manage Your Bookings</p>
    </div>
</div>

{{-- Stat Cards --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-5">

    {{-- Total Bookings --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        <div class="flex items-center justify-between mb-4">
            <p class="text-sm font-semibold text-gray-500">Total Bookings</p>
            <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background:#e8f4fd;">
                <i class="fas fa-calendar-check" style="color:#0077BE;"></i>
            </div>
        </div>
        <p class="text-3xl font-bold text-gray-800 mb-1">{{ $stats['total'] }}</p>
        <p class="text-xs text-gray-400">All time bookings</p>
    </div>

    {{-- Booking Status --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        <div class="flex items-center justify-between mb-4">
            <p class="text-sm font-semibold text-gray-500">Booking Status</p>
            <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background:#e8f4fd;">
                <i class="fas fa-circle-dot" style="color:#0077BE;"></i>
            </div>
        </div>
        <div class="space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-xs text-gray-500 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-green-500 inline-block"></span> Confirmed
                </span>
                <span class="text-xs font-bold text-green-600">{{ $stats['confirmed'] }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-xs text-gray-500 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-yellow-400 inline-block"></span> Pending
                </span>
                <span class="text-xs font-bold text-yellow-600">{{ $stats['pending'] }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-xs text-gray-500 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-red-500 inline-block"></span> Cancelled
                </span>
                <span class="text-xs font-bold text-red-600">{{ $stats['cancelled'] }}</span>
            </div>
        </div>
    </div>

    {{-- Payment Status --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        <div class="flex items-center justify-between mb-4">
            <p class="text-sm font-semibold text-gray-500">Payment Status</p>
            <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background:#e8f4fd;">
                <i class="fas fa-wallet" style="color:#0077BE;"></i>
            </div>
        </div>
        <div class="space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-xs text-gray-500 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-green-500 inline-block"></span> Paid
                </span>
                <span class="text-xs font-bold text-green-600">{{ $stats['paid'] }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-xs text-gray-500 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-orange-400 inline-block"></span> Unpaid
                </span>
                <span class="text-xs font-bold text-orange-600">{{ $stats['unpaid'] }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-xs text-gray-500 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-blue-500 inline-block"></span> Refunded
                </span>
                <span class="text-xs font-bold text-blue-600">{{ $stats['refunded'] }}</span>
            </div>
        </div>
    </div>

</div>

{{-- Type Tabs --}}
<div class="bg-white rounded-xl border border-gray-100 shadow-sm">
    <div class="flex items-center gap-1 p-3 border-b border-gray-100 overflow-x-auto">
        @php
            $tabs = [
                'all'    => ['label' => 'All Bookings',  'icon' => 'fa-list'],
                'hotel'  => ['label' => 'Stays',         'icon' => 'fa-hotel'],
                'flight' => ['label' => 'Flights',       'icon' => 'fa-plane-departure'],
                'tour'   => ['label' => 'Tours',         'icon' => 'fa-map-location-dot'],
                'visa'   => ['label' => 'Visa',          'icon' => 'fa-file-alt'],
            ];
        @endphp
        @foreach($tabs as $key => $tab)
        <a href="{{ route('user.bookings.index', $key === 'all' ? [] : ['type' => $key]) }}"
           class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold whitespace-nowrap transition"
           style="text-decoration:none; {{ $type === $key || ($key === 'all' && $type === 'all') ? 'background:#e8f4fd; color:#0077BE;' : 'color:#6b7280;' }}">
            <i class="fas {{ $tab['icon'] }}"></i> {{ $tab['label'] }}
        </a>
        @endforeach
    </div>

    {{-- Table --}}
    <div class="px-4 py-3 border-b border-gray-50 flex items-center justify-between">
        <p class="text-xs text-gray-500">Total: {{ $all->count() }} records</p>
        <div class="relative">
            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-300 text-xs"></i>
            <input type="text" placeholder="Search records..." id="searchInput"
                   class="pl-8 pr-3 py-1.5 text-xs border border-gray-200 rounded-lg focus:outline-none focus:border-blue-400"
                   onkeyup="filterTable()">
        </div>
    </div>

    @if($all->isEmpty())
        <div class="py-14 text-center text-gray-400">
            <i class="fas fa-calendar-xmark text-3xl mb-3 block"></i>
            <p class="text-sm font-medium">No bookings found</p>
        </div>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-sm" id="bookingTable">
            <thead>
                <tr class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wide">
                    <th class="px-5 py-3 text-left font-semibold">#</th>
                    <th class="px-5 py-3 text-left font-semibold">Invoice</th>
                    <th class="px-5 py-3 text-left font-semibold">Module</th>
                    <th class="px-5 py-3 text-left font-semibold">Booking</th>
                    <th class="px-5 py-3 text-left font-semibold">Payment</th>
                    <th class="px-5 py-3 text-left font-semibold">Price</th>
                    <th class="px-5 py-3 text-left font-semibold">PNR</th>
                    <th class="px-5 py-3 text-left font-semibold">Date</th>
                    <th class="px-5 py-3 text-left font-semibold">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($all as $i => $b)
                @php
                    $statusColors = ['confirmed'=>'bg-green-100 text-green-700','pending'=>'bg-yellow-100 text-yellow-700','cancelled'=>'bg-red-100 text-red-700'];
                    $payColors    = ['paid'=>'bg-green-100 text-green-700','unpaid'=>'bg-orange-100 text-orange-700','refunded'=>'bg-blue-100 text-blue-700'];
                    $typeIcons    = ['hotel'=>'fa-hotel','flight'=>'fa-plane','tour'=>'fa-map-location-dot','umrah'=>'fa-moon','visa'=>'fa-file-alt'];
                @endphp
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-5 py-3 text-gray-500 text-xs">{{ $i+1 }}</td>
                    <td class="px-5 py-3">
                        @php
                            $userInvoiceRoute = match($b['booking_type'] ?? 'flight') {
                                'flight' => url('flight/invoice', $b['booking_code']),
                                'hotel'  => url('hotel/invoice', $b['booking_code']),
                                'tour'   => route('tour.invoice', ['booking_ref' => $b['booking_code']]),
                                'umrah'  => route('umrah.invoice', ['booking_ref' => $b['booking_code']]),
                                default  => url('flight/invoice', $b['booking_code']),
                            };
                        @endphp
                        <a href="{{ $userInvoiceRoute }}" target="_blank" style="text-decoration:none; color:#0077BE;">
                            <span class="font-mono font-semibold text-xs">#{{ $b['booking_code'] }}</span>
                            <i class="fas fa-arrow-up-right-from-square text-xs ml-1"></i>
                        </a>
                    </td>
                    <td class="px-5 py-3">
                        <span class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-600 bg-gray-100 px-2.5 py-1 rounded-full">
                            <i class="fas {{ $typeIcons[$b['booking_type']] ?? 'fa-circle' }} text-xs" style="color:#0077BE;"></i>
                            {{ ucfirst($b['booking_type']) }}
                        </span>
                    </td>
                    <td class="px-5 py-3">
                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $statusColors[$b['status']] ?? 'bg-gray-100 text-gray-600' }}">
                            {{ ucfirst($b['status']) }}
                        </span>
                    </td>
                    <td class="px-5 py-3">
                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $payColors[$b['payment']] ?? 'bg-gray-100 text-gray-600' }}">
                            {{ ucfirst($b['payment']) }}
                        </span>
                    </td>
                    <td class="px-5 py-3 font-semibold text-gray-700 text-xs">{{ $b['price'] }}</td>
                    <td class="px-5 py-3 text-xs text-gray-500">{{ $b['pnr'] ?? 'No PNR' }}</td>
                    <td class="px-5 py-3 text-xs text-gray-500">{{ \Carbon\Carbon::parse($b['created_at'])->format('M d, Y') }}</td>
                    <td class="px-5 py-3">
                        <a href="{{ $userInvoiceRoute }}" target="_blank"
                           class="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-1.5 rounded-lg hover:bg-blue-50 transition"
                           style="color:#0077BE; text-decoration:none;">
                            <i class="fas fa-file-invoice"></i> Invoice
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

@endsection

@push('scripts')
<script>
function filterTable() {
    const q = document.getElementById('searchInput').value.toLowerCase();
    document.querySelectorAll('#bookingTable tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
}
</script>
@endpush
