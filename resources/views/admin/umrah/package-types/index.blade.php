@extends($layout ?? 'admin.layouts.app')
@section('title', 'Umrah Package Types')

@section('content')

<div class="flex items-center gap-2 text-xs text-gray-400 mb-4">
    <a href="{{ route('agent.dashboard') }}" class="umr-link">Dashboard</a>
    <i class="fas fa-chevron-right text-gray-300"></i>
    <a href="{{ route('agent.umrah.index') }}" class="umr-link">My Umrah</a>
    <i class="fas fa-chevron-right text-gray-300"></i>
    <span class="text-gray-600">Package Types</span>
</div>

<div class="flex items-center justify-between mb-5">
    <div class="flex items-center gap-3">
        <div class="w-9 h-9 rounded-lg flex items-center justify-center umr-icon-bg">
            <i class="fas fa-tags umr-accent"></i>
        </div>
        <div>
            <h4 class="text-lg font-bold text-gray-800">Umrah Package Types</h4>
            <p class="text-xs text-gray-400">Manage package types for Umrah packages</p>
        </div>
    </div>
    <button onclick="openModal('addModal')" class="px-5 py-2.5 rounded-lg text-sm font-semibold text-white umr-btn-solid">
        <i class="fas fa-plus text-xs"></i> Add Package Type
    </button>
</div>

<div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50">
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">#</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Package Type</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Created At</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($packageTypes as $type)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3 text-xs text-gray-400">{{ $type->id }}</td>
                    <td class="px-4 py-3 font-semibold text-gray-800">{{ ucfirst($type->packege_type) }}</td>
                    <td class="px-4 py-3">
                        @if($type->status == '1')
                            <span class="px-2 py-0.5 bg-green-100 text-green-700 rounded-full text-xs font-medium">Active</span>
                        @else
                            <span class="px-2 py-0.5 bg-red-100 text-red-700 rounded-full text-xs font-medium">Inactive</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $type->created_at->format('d M Y') }}</td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-2">
                            <button onclick="openModal('editModal{{ $type->id }}')" class="px-3 py-1.5 rounded-lg text-xs font-semibold border border-gray-200 text-gray-600 hover:bg-gray-50 transition">
                                <i class="fas fa-pencil text-xs"></i>
                            </button>
                            <form action="{{ route(($routePrefix ?? 'admin.umrah.package-types') . '.toggle-status', $type->id) }}" method="POST" class="inline">
                                @csrf @method('PATCH')
                                <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-semibold border transition {{ $type->status == '1' ? 'border-yellow-200 bg-yellow-50 text-yellow-600 hover:bg-yellow-100' : 'border-green-200 bg-green-50 text-green-600 hover:bg-green-100' }}">
                                    <i class="fas fa-{{ $type->status == '1' ? 'pause' : 'play' }} text-xs"></i>
                                </button>
                            </form>
                            <form action="{{ route(($routePrefix ?? 'admin.umrah.package-types') . '.destroy', $type->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure?')">
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
                    <td colspan="5">
                        <div class="text-center py-12">
                            <div class="w-14 h-14 rounded-full flex items-center justify-center mx-auto mb-3 umr-icon-bg">
                                <i class="fas fa-tags text-xl umr-accent"></i>
                            </div>
                            <p class="text-sm font-semibold text-gray-600 mb-1">No package types found</p>
                            <p class="text-xs text-gray-400">Add your first package type to get started.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($packageTypes->hasPages())
    <div class="px-5 py-3 border-t border-gray-100">
        {{ $packageTypes->links() }}
    </div>
    @endif
</div>

{{-- Add Modal --}}
<div id="addModal" class="fixed inset-0 hidden flex items-center justify-center umr-modal-overlay">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4">
        <form action="{{ route(($routePrefix ?? 'admin.umrah.package-types') . '.store') }}" method="POST">
            @csrf
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <h5 class="font-semibold text-gray-800 text-sm">Add Package Type</h5>
                <button type="button" onclick="closeModal('addModal')" class="text-gray-400 hover:text-gray-600 border-none bg-transparent cursor-pointer text-lg leading-none">&times;</button>
            </div>
            <div class="p-5 space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Package Type Name</label>
                    <input type="text" name="packege_type" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-400 bg-gray-50" placeholder="e.g., basic, standard, premium" required>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Status</label>
                    <select name="status" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-400 bg-gray-50">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="flex items-center justify-end gap-3 px-5 py-4 border-t border-gray-100">
                <button type="button" onclick="closeModal('addModal')" class="px-4 py-2 rounded-lg text-sm font-semibold border border-gray-200 text-gray-600 hover:bg-gray-50 transition bg-transparent cursor-pointer">Cancel</button>
                <button type="submit" class="px-5 py-2 rounded-lg text-sm font-semibold text-white border-none cursor-pointer umr-btn-fill">Add Package Type</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Modals --}}
@foreach($packageTypes as $type)
<div id="editModal{{ $type->id }}" class="fixed inset-0 hidden flex items-center justify-center umr-modal-overlay">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4">
        <form action="{{ route(($routePrefix ?? 'admin.umrah.package-types') . '.update', $type->id) }}" method="POST">
            @csrf @method('PATCH')
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <h5 class="font-semibold text-gray-800 text-sm">Edit Package Type</h5>
                <button type="button" onclick="closeModal('editModal{{ $type->id }}')" class="text-gray-400 hover:text-gray-600 border-none bg-transparent cursor-pointer text-lg leading-none">&times;</button>
            </div>
            <div class="p-5 space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Package Type Name</label>
                    <input type="text" name="packege_type" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-400 bg-gray-50" value="{{ $type->packege_type }}" required>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Status</label>
                    <select name="status" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-400 bg-gray-50">
                        <option value="1" {{ $type->status == '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ $type->status == '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>
            <div class="flex items-center justify-end gap-3 px-5 py-4 border-t border-gray-100">
                <button type="button" onclick="closeModal('editModal{{ $type->id }}')" class="px-4 py-2 rounded-lg text-sm font-semibold border border-gray-200 text-gray-600 hover:bg-gray-50 transition bg-transparent cursor-pointer">Cancel</button>
                <button type="submit" class="px-5 py-2 rounded-lg text-sm font-semibold text-white border-none cursor-pointer umr-btn-fill">Update</button>
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
