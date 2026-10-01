@extends('user.layouts.app')
@section('title', 'Ticket #' . $ticket->ticket_number)

@section('content')

{{-- Breadcrumb --}}
<div class="flex items-center gap-2 text-xs text-gray-400 mb-4">
    <a href="{{ route('user.dashboard') }}" style="color:#0077BE; text-decoration:none;">Dashboard</a>
    <i class="fas fa-chevron-right text-gray-300"></i>
    <a href="{{ route('user.support.index') }}" style="color:#0077BE; text-decoration:none;">Support Tickets</a>
    <i class="fas fa-chevron-right text-gray-300"></i>
    <span class="text-gray-600">{{ $ticket->ticket_number }}</span>
</div>

@php
    $statusColors = [
        'open' => 'bg-yellow-100 text-yellow-700',
        'in_progress' => 'bg-blue-100 text-blue-700',
        'resolved' => 'bg-green-100 text-green-700',
        'closed' => 'bg-gray-100 text-gray-600',
    ];
@endphp

@if(session('success'))
<div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-green-700 text-sm flex items-center gap-2">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
</div>
@endif

{{-- Ticket header --}}
<div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 mb-5">
    <div class="flex items-start justify-between flex-wrap gap-3">
        <div>
            <h4 class="text-lg font-bold text-gray-800">{{ $ticket->subject }}</h4>
            <p class="text-xs text-gray-400 font-mono mt-1">{{ $ticket->ticket_number }} &bull; Opened {{ $ticket->created_at->format('M d, Y \a\t h:i A') }}</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-blue-100 text-blue-700">{{ ucfirst($ticket->priority) }} priority</span>
            <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $statusColors[$ticket->status] ?? 'bg-gray-100 text-gray-600' }}">{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</span>
        </div>
    </div>
</div>

{{-- Conversation thread --}}
<div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden mb-5">
    <div class="px-5 py-4 border-b border-gray-100 font-semibold text-sm text-gray-700">Conversation</div>
    <div class="p-5 space-y-4">

        {{-- Original message --}}
        <div class="flex gap-3">
            <div class="w-9 h-9 rounded-full flex items-center justify-center flex-shrink-0 text-xs font-bold" style="background:#e8f4fd; color:#0077BE;">
                {{ strtoupper(substr(auth()->user()->first_name, 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-sm font-semibold text-gray-800">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</span>
                    <span class="text-xs text-gray-400">{{ $ticket->created_at->diffForHumans() }}</span>
                </div>
                <div class="bg-gray-50 rounded-lg p-3 text-sm text-gray-700 whitespace-pre-line">{{ $ticket->description }}</div>
                @if($ticket->attachment)
                    <a href="{{ url('public/assets/uploads/support/' . $ticket->attachment) }}" target="_blank" class="inline-flex items-center gap-1.5 mt-2 text-xs font-medium hover:underline" style="color:#0077BE;">
                        <i class="fas fa-paperclip"></i> View attachment
                    </a>
                @endif
            </div>
        </div>

        {{-- Replies --}}
        @foreach($ticket->replies as $reply)
            @php $isAdmin = $reply->sender && $reply->sender->isAdmin(); @endphp
            <div class="flex gap-3">
                <div class="w-9 h-9 rounded-full flex items-center justify-center flex-shrink-0 text-xs font-bold {{ $isAdmin ? 'bg-indigo-100 text-indigo-700' : '' }}" style="{{ $isAdmin ? '' : 'background:#e8f4fd; color:#0077BE;' }}">
                    {{ strtoupper(substr($reply->sender->first_name ?? '?', 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-sm font-semibold text-gray-800">{{ $reply->sender->first_name ?? 'Unknown' }} {{ $reply->sender->last_name ?? '' }}</span>
                        @if($isAdmin)
                            <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded bg-indigo-100 text-indigo-700">Support Team</span>
                        @endif
                        <span class="text-xs text-gray-400">{{ $reply->created_at->diffForHumans() }}</span>
                    </div>
                    <div class="rounded-lg p-3 text-sm text-gray-700 whitespace-pre-line {{ $isAdmin ? 'bg-indigo-50' : 'bg-gray-50' }}">{{ $reply->message }}</div>
                </div>
            </div>
        @endforeach

    </div>

    {{-- Reply form --}}
    @if(!in_array($ticket->status, ['closed']))
    <div class="border-t border-gray-100 p-5">
        <form method="POST" action="{{ route('user.support.reply', $ticket) }}">
            @csrf
            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Add a reply</label>
            <textarea name="message" rows="3" required placeholder="Type your message..."
                      class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-400 bg-gray-50"></textarea>
            @error('message')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            <div class="flex justify-end mt-3">
                <button type="submit" class="px-5 py-2.5 rounded-lg text-sm font-semibold text-white" style="background:#0077BE; border:none; cursor:pointer;">
                    <i class="fas fa-paper-plane mr-1"></i> Send Reply
                </button>
            </div>
        </form>
    </div>
    @else
    <div class="border-t border-gray-100 p-5 text-center text-sm text-gray-400">
        <i class="fas fa-lock mr-1"></i> This ticket is closed.
    </div>
    @endif
</div>

@endsection
