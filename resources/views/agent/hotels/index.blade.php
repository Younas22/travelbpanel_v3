@extends('agent.layouts.app')
@section('title', 'My Hotels')

@section('content')

<div class="flex items-center gap-2 text-xs text-gray-400 mb-4">
    <a href="{{ route('agent.dashboard') }}" class="ap-accent-link">Dashboard</a>
    <i class="fas fa-chevron-right text-gray-300"></i>
    <span class="text-gray-600">My Hotels</span>
</div>

<div class="flex items-center justify-between mb-5">
    <div class="flex items-center gap-3">
        <div class="w-9 h-9 rounded-lg flex items-center justify-center ap-tint-bg">
            <i class="fas fa-building ap-accent"></i>
        </div>
        <div>
            <h4 class="text-lg font-bold text-gray-800">My Hotels</h4>
            <p class="text-xs text-gray-400">Manage your hotel listings</p>
        </div>
    </div>
    <a href="{{ route('agent.hotels.create') }}" class="px-5 py-2.5 rounded-lg text-sm font-semibold text-white ap-solid-accent-btn">
        <i class="fas fa-plus text-xs"></i> Add Hotel
    </a>
</div>

<div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
    @if($hotels->isEmpty())
        <div class="text-center py-12">
            <div class="w-14 h-14 rounded-full flex items-center justify-center mx-auto mb-3 ap-tint-bg">
                <i class="fas fa-building text-xl ap-accent"></i>
            </div>
            <p class="text-sm font-semibold text-gray-600 mb-1">No hotels yet</p>
            <p class="text-xs text-gray-400 mb-4">Start by adding your first hotel listing.</p>
            <a href="{{ route('agent.hotels.create') }}" class="px-5 py-2.5 rounded-lg text-sm font-semibold text-white ap-solid-accent-btn">
                <i class="fas fa-plus text-xs"></i> Add Hotel
            </a>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">#</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Hotel Name</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Location</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Type</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Stars</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($hotels as $hotel)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 text-xs text-gray-400">{{ $hotel->id }}</td>
                        <td class="px-4 py-3 font-semibold text-gray-800">{{ $hotel->name }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $hotel->location?->city ?? '—' }}@if($hotel->location?->country), {{ $hotel->location->country }}@endif</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ ucfirst($hotel->type) }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-0.5">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star text-xs {{ $i <= ($hotel->stars ?? 0) ? 'text-yellow-400' : 'text-gray-200' }}"></i>
                                @endfor
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            @php $approval = $hotel->approval_status ?? 'approved'; @endphp
                            @if($approval === 'pending')
                                <span class="px-2 py-0.5 bg-yellow-100 text-yellow-700 rounded-full text-xs font-medium"><i class="fas fa-clock text-xs"></i> Pending</span>
                            @elseif($approval === 'rejected')
                                <span class="px-2 py-0.5 bg-red-100 text-red-700 rounded-full text-xs font-medium"><i class="fas fa-times text-xs"></i> Rejected</span>
                            @else
                                <span class="px-2 py-0.5 bg-green-100 text-green-700 rounded-full text-xs font-medium"><i class="fas fa-check text-xs"></i> Approved</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('agent.hotels.edit', $hotel->id) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold border border-gray-200 text-gray-600 hover:bg-gray-50 transition ap-no-underline">
                                    <i class="fas fa-pencil text-xs"></i> Edit
                                </a>
                                <form method="POST" action="{{ route('agent.hotels.destroy', $hotel->id) }}" class="inline"
                                      onsubmit="return confirm('Delete this hotel?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-red-50 text-red-600 hover:bg-red-100 transition border-none cursor-pointer">
                                        <i class="fas fa-trash text-xs"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($hotels->hasPages())
        <div class="px-5 py-3 border-t border-gray-100">
            {{ $hotels->links() }}
        </div>
        @endif
    @endif
</div>

@endsection
