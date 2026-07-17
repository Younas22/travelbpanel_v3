@extends($layout ?? 'admin.layouts.app')
@section('title', 'Umrah Inclusions')

@section('content')

<div class="flex items-center gap-2 text-xs text-gray-400 mb-4">
    <a href="{{ route('agent.dashboard') }}" style="color:#0077BE; text-decoration:none;">Dashboard</a>
    <i class="fas fa-chevron-right text-gray-300"></i>
    <a href="{{ route('agent.umrah.index') }}" style="color:#0077BE; text-decoration:none;">My Umrah</a>
    <i class="fas fa-chevron-right text-gray-300"></i>
    <span class="text-gray-600">Inclusions</span>
</div>

<div class="flex items-center justify-between mb-5">
    <div class="flex items-center gap-3">
        <div class="w-9 h-9 rounded-lg flex items-center justify-center" style="background:#e8f4fd;">
            <i class="fas fa-check-circle" style="color:#0077BE;"></i>
        </div>
        <div>
            <h4 class="text-lg font-bold text-gray-800">Umrah Inclusions</h4>
            <p class="text-xs text-gray-400">Manage inclusions for Umrah packages</p>
        </div>
    </div>
    <button onclick="openModal('addModal')" class="px-5 py-2.5 rounded-lg text-sm font-semibold text-white" style="background:#0077BE; border:none; cursor:pointer;">
        <i class="fas fa-plus text-xs"></i> Add Inclusion
    </button>
</div>

<div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50">
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">#</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Name</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Created At</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($inclusions as $inclusion)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3 text-xs text-gray-400">{{ $inclusion->id }}</td>
                    <td class="px-4 py-3 font-semibold text-gray-800">{{ $inclusion->name }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $inclusion->created_at->format('d M Y') }}</td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-2">
                            <button onclick="openModal('editModal{{ $inclusion->id }}')" class="px-3 py-1.5 rounded-lg text-xs font-semibold border border-gray-200 text-gray-600 hover:bg-gray-50 transition">
                                <i class="fas fa-pencil text-xs"></i>
                            </button>
                            <form action="{{ route(($routePrefix ?? 'admin.umrah.inclusions') . '.destroy', $inclusion->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-red-50 text-red-600 hover:bg-red-100 transition border-none cursor-pointer">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4">
                        <div class="text-center py-12">
                            <div class="w-14 h-14 rounded-full flex items-center justify-center mx-auto mb-3" style="background:#e8f4fd;">
                                <i class="fas fa-check-circle text-xl" style="color:#0077BE;"></i>
                            </div>
                            <p class="text-sm font-semibold text-gray-600 mb-1">No inclusions found</p>
                            <p class="text-xs text-gray-400">Add your first inclusion to get started.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($inclusions->hasPages())
    <div class="px-5 py-3 border-t border-gray-100">
        {{ $inclusions->links() }}
    </div>
    @endif
</div>

{{-- Add Modal --}}
<div id="addModal" class="fixed inset-0 hidden flex items-center justify-center" style="z-index:9999; background:rgba(0,0,0,0.4);">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4">
        <form action="{{ route(($routePrefix ?? 'admin.umrah.inclusions') . '.store') }}" method="POST">
            @csrf
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <h5 class="font-semibold text-gray-800 text-sm">Add Inclusion</h5>
                <button type="button" onclick="closeModal('addModal')" class="text-gray-400 hover:text-gray-600 border-none bg-transparent cursor-pointer text-lg leading-none">&times;</button>
            </div>
            <div class="p-5">
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Inclusion Name</label>
                <input type="text" name="name" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-400 bg-gray-50" placeholder="e.g., Flight Tickets, Hotel Service" required>
            </div>
            <div class="flex items-center justify-end gap-3 px-5 py-4 border-t border-gray-100">
                <button type="button" onclick="closeModal('addModal')" class="px-4 py-2 rounded-lg text-sm font-semibold border border-gray-200 text-gray-600 hover:bg-gray-50 transition bg-transparent cursor-pointer">Cancel</button>
                <button type="submit" class="px-5 py-2 rounded-lg text-sm font-semibold text-white border-none cursor-pointer" style="background:#0077BE;">Add Inclusion</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Modals --}}
@foreach($inclusions as $inclusion)
<div id="editModal{{ $inclusion->id }}" class="fixed inset-0 hidden flex items-center justify-center" style="z-index:9999; background:rgba(0,0,0,0.4);">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4">
        <form action="{{ route(($routePrefix ?? 'admin.umrah.inclusions') . '.update', $inclusion->id) }}" method="POST">
            @csrf @method('PATCH')
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <h5 class="font-semibold text-gray-800 text-sm">Edit Inclusion</h5>
                <button type="button" onclick="closeModal('editModal{{ $inclusion->id }}')" class="text-gray-400 hover:text-gray-600 border-none bg-transparent cursor-pointer text-lg leading-none">&times;</button>
            </div>
            <div class="p-5">
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Inclusion Name</label>
                <input type="text" name="name" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-400 bg-gray-50" value="{{ $inclusion->name }}" required>
            </div>
            <div class="flex items-center justify-end gap-3 px-5 py-4 border-t border-gray-100">
                <button type="button" onclick="closeModal('editModal{{ $inclusion->id }}')" class="px-4 py-2 rounded-lg text-sm font-semibold border border-gray-200 text-gray-600 hover:bg-gray-50 transition bg-transparent cursor-pointer">Cancel</button>
                <button type="submit" class="px-5 py-2 rounded-lg text-sm font-semibold text-white border-none cursor-pointer" style="background:#0077BE;">Update</button>
            </div>
        </form>
    </div>
</div>
@endforeach

@endsection

@push('scripts')
<script>
function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
function closeModal(id) { document.getElementById(id).classList.add('hidden'); }
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('fixed')) closeModal(e.target.id);
});
</script>
@endpush
