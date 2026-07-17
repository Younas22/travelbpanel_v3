@extends('user.layouts.app')
@section('title', 'Dashboard')

@section('content')

{{-- Page Header --}}
<div class="flex items-center justify-between mb-5">
    <div>
        <h4 class="text-xl font-bold text-gray-800">Dashboard</h4>
        <p class="text-sm text-gray-400 mt-0.5">Your Travel Overview</p>
    </div>
    <a href="{{ route('user.profile.index') }}"
       class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold transition"
       style="background:#e8f4fd; color:#0077BE; text-decoration:none;">
        <i class="fas fa-user-pen"></i> Edit Profile
    </a>
</div>

{{-- Stats --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0" style="background:#e8f4fd;">
            <i class="fas fa-calendar-check" style="color:#0077BE;"></i>
        </div>
        <div>
            <p class="text-xs text-gray-400">Total Bookings</p>
            <p class="text-xl font-bold text-gray-800">{{ $totalBookings }}</p>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 bg-green-50">
            <i class="fas fa-circle-check text-green-500"></i>
        </div>
        <div>
            <p class="text-xs text-gray-400">Confirmed</p>
            <p class="text-xl font-bold text-gray-800">{{ $recentBookings->where('status','confirmed')->count() }}</p>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 bg-yellow-50">
            <i class="fas fa-clock text-yellow-500"></i>
        </div>
        <div>
            <p class="text-xs text-gray-400">Pending</p>
            <p class="text-xl font-bold text-gray-800">{{ $recentBookings->where('status','pending')->count() }}</p>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 bg-red-50">
            <i class="fas fa-circle-xmark text-red-400"></i>
        </div>
        <div>
            <p class="text-xs text-gray-400">Cancelled</p>
            <p class="text-xl font-bold text-gray-800">{{ $recentBookings->where('status','cancelled')->count() }}</p>
        </div>
    </div>
</div>

{{-- Recent Bookings --}}
<div class="bg-white rounded-xl border border-gray-100 shadow-sm">
    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
        <div>
            <h5 class="text-sm font-bold text-gray-800">Recent Bookings</h5>
            <p class="text-xs text-gray-400 mt-0.5">Total: {{ $totalBookings }} records</p>
        </div>
        <a href="{{ route('user.bookings.index') }}"
           class="text-xs font-semibold px-3 py-1.5 rounded-lg transition"
           style="background:#e8f4fd; color:#0077BE; text-decoration:none;">
            View All
        </a>
    </div>

    @if($recentBookings->isEmpty())
        <div class="py-14 text-center text-gray-400">
            <i class="fas fa-calendar-xmark text-3xl mb-3 block"></i>
            <p class="text-sm font-medium">No bookings yet</p>
            <p class="text-xs mt-1">Your recent bookings will appear here</p>
        </div>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wide">
                    <th class="px-5 py-3 text-left font-semibold">#</th>
                    <th class="px-5 py-3 text-left font-semibold">Invoice</th>
                    <th class="px-5 py-3 text-left font-semibold">Module</th>
                    <th class="px-5 py-3 text-left font-semibold">Status</th>
                    <th class="px-5 py-3 text-left font-semibold">Payment</th>
                    <th class="px-5 py-3 text-left font-semibold">Price</th>
                    <th class="px-5 py-3 text-left font-semibold">Date</th>
                    <th class="px-5 py-3 text-left font-semibold">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($recentBookings as $i => $b)
                @php
                    $statusColors = ['confirmed'=>'bg-green-100 text-green-700','pending'=>'bg-yellow-100 text-yellow-700','cancelled'=>'bg-red-100 text-red-700'];
                    $payColors    = ['paid'=>'bg-green-100 text-green-700','unpaid'=>'bg-orange-100 text-orange-700','refunded'=>'bg-blue-100 text-blue-700'];
                    $typeIcons    = ['hotel'=>'fa-hotel','flight'=>'fa-plane','tour'=>'fa-map-location-dot','umrah'=>'fa-moon','visa'=>'fa-file-alt'];
                @endphp
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-5 py-3 text-gray-500">{{ $i+1 }}</td>
                    <td class="px-5 py-3 font-mono font-semibold text-gray-700">{{ $b['booking_code'] }}</td>
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
                    <td class="px-5 py-3 font-semibold text-gray-700">{{ $b['price'] }}</td>
                    <td class="px-5 py-3 text-gray-500 text-xs">{{ \Carbon\Carbon::parse($b['created_at'])->format('M d, Y') }}</td>
                    <td class="px-5 py-3">
                        <a href="{{ route('user.bookings.index') }}" class="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-1 rounded-lg hover:bg-blue-50 transition" style="color:#0077BE; text-decoration:none;">
                            <i class="fas fa-eye"></i>
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
