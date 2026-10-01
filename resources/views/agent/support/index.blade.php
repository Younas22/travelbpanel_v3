@extends('agent.layouts.app')
@section('title', 'Support Tickets')

@section('content')

<div class="flex items-center justify-between mb-5">
    <div class="flex items-center gap-3">
        <div class="w-9 h-9 rounded-lg flex items-center justify-center ap-tint-bg">
            <i class="fas fa-headset ap-accent"></i>
        </div>
        <div>
            <h4 class="text-lg font-bold text-gray-800">Support Tickets</h4>
            <p class="text-xs text-gray-400">Get help from our support team</p>
        </div>
    </div>
    <button onclick="openTicketModal()"
            class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold text-white ap-solid-accent-btn" style="border:none; cursor:pointer;">
        <i class="fas fa-plus"></i> Create Ticket
    </button>
</div>

<div class="flex items-center gap-2 text-xs text-gray-400 mb-4">
    <a href="{{ route('agent.dashboard') }}" class="ap-accent-link">Dashboard</a>
    <i class="fas fa-chevron-right text-gray-300"></i>
    <span class="text-gray-600">Support Tickets</span>
</div>

@if(session('success'))
<div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-green-700 text-sm flex items-center gap-2">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
</div>
@endif

<div class="bg-white rounded-xl border border-gray-100 shadow-sm">
    @if($tickets->isEmpty())
        <div class="py-16 text-center text-gray-400">
            <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 ap-tint-bg">
                <i class="fas fa-ticket text-2xl ap-accent"></i>
            </div>
            <p class="text-sm font-semibold text-gray-600 mb-1">No tickets found</p>
            <p class="text-xs mb-4">Create your first support ticket</p>
            <button onclick="openTicketModal()" class="px-5 py-2.5 rounded-lg text-sm font-semibold text-white ap-solid-accent-btn" style="border:none; cursor:pointer;">
                <i class="fas fa-plus mr-1"></i> Create Ticket
            </button>
        </div>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wide">
                    <th class="px-5 py-3 text-left font-semibold">#</th>
                    <th class="px-5 py-3 text-left font-semibold">Subject</th>
                    <th class="px-5 py-3 text-left font-semibold">Priority</th>
                    <th class="px-5 py-3 text-left font-semibold">Status</th>
                    <th class="px-5 py-3 text-left font-semibold">Date</th>
                    <th class="px-5 py-3 text-left font-semibold">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @php
                    $statusColors = [
                        'open' => 'bg-yellow-100 text-yellow-700',
                        'in_progress' => 'bg-blue-100 text-blue-700',
                        'resolved' => 'bg-green-100 text-green-700',
                        'closed' => 'bg-gray-100 text-gray-600',
                    ];
                @endphp
                @foreach($tickets as $i => $ticket)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-5 py-3 text-gray-500 text-xs">{{ $i+1 }}</td>
                    <td class="px-5 py-3 font-medium text-gray-700">
                        <a href="{{ route('agent.support.show', $ticket) }}" class="ap-accent-link hover:underline">{{ $ticket->subject }}</a>
                        <div class="text-xs text-gray-400 font-mono mt-0.5">{{ $ticket->ticket_number }}</div>
                    </td>
                    <td class="px-5 py-3">
                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-blue-100 text-blue-700">{{ ucfirst($ticket->priority) }}</span>
                    </td>
                    <td class="px-5 py-3">
                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $statusColors[$ticket->status] ?? 'bg-gray-100 text-gray-600' }}">{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</span>
                    </td>
                    <td class="px-5 py-3 text-xs text-gray-500">{{ $ticket->created_at->format('M d, Y') }}</td>
                    <td class="px-5 py-3">
                        <a href="{{ route('agent.support.show', $ticket) }}" class="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-1.5 rounded-lg ap-chip-link">
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

{{-- Create Ticket Modal --}}
<div id="ticketModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:9999; align-items:center; justify-content:center; padding:16px; backdrop-filter:blur(4px);">
    <div style="background:#fff; border-radius:16px; width:100%; max-width:520px; max-height:90vh; overflow-y:auto; box-shadow:0 24px 64px rgba(0,0,0,.18); animation:mIn .25s ease;">

        <div style="background:linear-gradient(135deg,#0077BE,#005a8f); padding:20px 24px; border-radius:16px 16px 0 0; display:flex; align-items:center; justify-content:space-between;">
            <h5 style="color:#fff; font-size:.95rem; font-weight:800; margin:0;">Create New Ticket</h5>
            <button onclick="closeTicketModal()" style="background:rgba(255,255,255,.15); border:none; color:#fff; width:28px; height:28px; border-radius:50%; cursor:pointer; font-size:.8rem; display:flex; align-items:center; justify-content:center;">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div style="padding:24px;">
            <form method="POST" action="{{ route('agent.support.store') }}" enctype="multipart/form-data">
                @csrf
                <div style="margin-bottom:16px;">
                    <label style="display:block; font-size:.78rem; font-weight:600; color:#374151; margin-bottom:4px;">Subject <span style="color:#0077BE;">*</span></label>
                    <input type="text" name="subject" required placeholder="Enter ticket subject" value="{{ old('subject') }}"
                           style="width:100%; border:1.5px solid #d1d5db; border-radius:8px; padding:9px 12px; font-size:.85rem; font-family:inherit; background:#fafafa; box-sizing:border-box;">
                    @error('subject')<span style="font-size:.73rem; color:#ef4444;">{{ $message }}</span>@enderror
                </div>

                <div style="margin-bottom:16px;">
                    <label style="display:block; font-size:.78rem; font-weight:600; color:#374151; margin-bottom:4px;">Priority</label>
                    <select name="priority" style="width:100%; border:1.5px solid #d1d5db; border-radius:8px; padding:9px 12px; font-size:.85rem; font-family:inherit; background:#fafafa; box-sizing:border-box;">
                        <option value="low">Low</option>
                        <option value="normal" selected>Normal</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>

                <div style="margin-bottom:16px;">
                    <label style="display:block; font-size:.78rem; font-weight:600; color:#374151; margin-bottom:4px;">Description <span style="color:#0077BE;">*</span></label>
                    <textarea name="description" required rows="4" placeholder="Describe your issue..."
                              style="width:100%; border:1.5px solid #d1d5db; border-radius:8px; padding:9px 12px; font-size:.85rem; font-family:inherit; background:#fafafa; box-sizing:border-box; resize:vertical;">{{ old('description') }}</textarea>
                    @error('description')<span style="font-size:.73rem; color:#ef4444;">{{ $message }}</span>@enderror
                </div>

                <div style="margin-bottom:20px;">
                    <label style="display:block; font-size:.78rem; font-weight:600; color:#374151; margin-bottom:4px;">Attachment <span style="font-size:.72rem; color:#9ca3af;">(Optional)</span></label>
                    <input type="file" name="attachment" accept=".jpg,.jpeg,.png,.gif,.pdf,.zip"
                           style="width:100%; border:1.5px solid #d1d5db; border-radius:8px; padding:8px 12px; font-size:.82rem; font-family:inherit; background:#fafafa; box-sizing:border-box;">
                    <p style="font-size:.7rem; color:#9ca3af; margin-top:4px;">Max 5MB. Allowed: JPG, PNG, GIF, PDF, ZIP</p>
                </div>

                <div style="display:flex; gap:10px;">
                    <button type="submit" style="flex:1; background:#0077BE; color:#fff; border:none; border-radius:9px; padding:11px; font-size:.88rem; font-weight:700; cursor:pointer; font-family:inherit;">
                        <i class="fas fa-paper-plane mr-1"></i> Create Ticket
                    </button>
                    <button type="button" onclick="closeTicketModal()" style="flex:1; background:#f1f5f9; color:#64748b; border:none; border-radius:9px; padding:11px; font-size:.88rem; font-weight:600; cursor:pointer; font-family:inherit;">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<style>
    @keyframes mIn { from { opacity:0; transform:translateY(16px); } to { opacity:1; transform:translateY(0); } }
</style>
<script>
    function openTicketModal()  { const m = document.getElementById('ticketModal'); m.style.display = 'flex'; document.body.style.overflow = 'hidden'; }
    function closeTicketModal() { const m = document.getElementById('ticketModal'); m.style.display = 'none';  document.body.style.overflow = ''; }
    document.getElementById('ticketModal').addEventListener('click', function(e) { if (e.target === this) closeTicketModal(); });
    @if($errors->any()) document.addEventListener('DOMContentLoaded', openTicketModal); @endif
</script>
@endpush
