@extends($layout ?? 'admin-nova.layouts.app')
@section('title', 'Umrah Exclusions')

@push('styles')
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        corePlugins: { preflight: false },
        theme: {
            extend: {
                fontFamily: { jakarta: ['var(--font-family)'] },
                colors: {
                    novabg: 'var(--body-bg)', novablue: 'var(--primary-color)', novacyan: 'var(--info-color)',
                    novatext: 'var(--text-color)', novamuted: 'var(--text-color)', novaborder: 'var(--border-color)', novacard: 'var(--card-bg)',
                    novasuccess: 'var(--success-color)', novawarning: 'var(--warning-color)', novadanger: 'var(--danger-color)',
                },
            },
        },
    };
</script>
@endpush

@section('content')
<div id="uxPage" class="tt-fade-in font-jakarta">

    <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
        <div>
            <h1 class="text-lg font-bold text-novatext">Umrah Exclusions</h1>
            <p class="text-xs text-novamuted mt-1">Manage exclusions for Umrah packages</p>
        </div>
        <button onclick="uxOpenModal('addModal')" class="ux-btn-nova ux-btn-primary px-4 py-2.5 text-xs font-semibold">
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
            Add Exclusion
        </button>
    </div>

    <div class="tt-card bg-novacard rounded-2xl border border-novaborder shadow-sm p-4 sm:p-5">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                <tr class="border-b border-novaborder">
                    <th class="text-left text-[10px] font-semibold uppercase tracking-wide text-novamuted py-2.5 px-3">#</th>
                    <th class="text-left text-[10px] font-semibold uppercase tracking-wide text-novamuted py-2.5 px-3">Name</th>
                    <th class="text-left text-[10px] font-semibold uppercase tracking-wide text-novamuted py-2.5 px-3">Created At</th>
                    <th class="text-right text-[10px] font-semibold uppercase tracking-wide text-novamuted py-2.5 px-3">Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse($exclusions as $exclusion)
                    <tr class="tt-row border-b border-novaborder last:border-0">
                        <td class="py-3 px-3 text-novamuted">{{ $exclusion->id }}</td>
                        <td class="py-3 px-3 font-semibold text-novatext">
                            <span class="inline-flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-novadanger" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="m9.5 9.5 5 5m0-5-5 5"/></svg>
                                {{ $exclusion->name }}
                            </span>
                        </td>
                        <td class="py-3 px-3 text-novamuted">{{ $exclusion->created_at->format('d M Y') }}</td>
                        <td class="py-3 px-3">
                            <div class="flex items-center justify-end gap-1.5">
                                <button onclick="uxOpenModal('editModal{{ $exclusion->id }}')" class="ux-icon-btn" data-tooltip="Edit">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M16.474 5.408 18.592 7.526M4 20l1.11-3.92a2 2 0 0 1 .53-.9l9.9-9.9a1.5 1.5 0 0 1 2.12 0l1.06 1.06a1.5 1.5 0 0 1 0 2.12l-9.9 9.9a2 2 0 0 1-.9.53L4 20Z"/></svg>
                                </button>
                                <form action="{{ route(($routePrefix ?? 'admin.umrah.exclusions') . '.destroy', $exclusion->id) }}" method="POST" onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="ux-icon-btn ux-icon-danger" data-tooltip="Delete">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0-1 14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2L4 6h16Z"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center py-16">
                            <svg class="w-10 h-10 text-novaborder mx-auto mb-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12.75 11.25 15 15 9.75"/><circle cx="12" cy="12" r="9"/></svg>
                            <p class="text-sm font-semibold text-novatext">No exclusions found</p>
                            <p class="text-xs text-novamuted mt-1">Add your first exclusion to get started.</p>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($exclusions->hasPages())
            <div class="mt-6 pt-5 border-t border-novaborder">
                {{ $exclusions->links() }}
            </div>
        @endif
    </div>

    {{-- ===== ADD MODAL ===== --}}
    <div id="addModal" class="ux-modal-overlay hidden">
        <div class="ux-modal">
            <form action="{{ route(($routePrefix ?? 'admin.umrah.exclusions') . '.store') }}" method="POST">
                @csrf
                <div class="ux-modal-header">
                    <h5 class="text-sm font-bold text-novatext">Add Exclusion</h5>
                    <button type="button" onclick="uxCloseModal('addModal')" class="ux-modal-close">&times;</button>
                </div>
                <div class="ux-modal-body">
                    <label class="ux-label">Exclusion Name</label>
                    <input type="text" name="name" class="ux-input" placeholder="e.g., Additional Meals, Room Extras" required>
                </div>
                <div class="ux-modal-footer">
                    <button type="button" onclick="uxCloseModal('addModal')" class="ux-btn-nova px-4 py-2.5 text-xs font-semibold">Cancel</button>
                    <button type="submit" class="ux-btn-nova ux-btn-primary px-4 py-2.5 text-xs font-semibold">Add Exclusion</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ===== EDIT MODALS ===== --}}
    @foreach($exclusions as $exclusion)
        <div id="editModal{{ $exclusion->id }}" class="ux-modal-overlay hidden">
            <div class="ux-modal">
                <form action="{{ route(($routePrefix ?? 'admin.umrah.exclusions') . '.update', $exclusion->id) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="ux-modal-header">
                        <h5 class="text-sm font-bold text-novatext">Edit Exclusion</h5>
                        <button type="button" onclick="uxCloseModal('editModal{{ $exclusion->id }}')" class="ux-modal-close">&times;</button>
                    </div>
                    <div class="ux-modal-body">
                        <label class="ux-label">Exclusion Name</label>
                        <input type="text" name="name" class="ux-input" value="{{ $exclusion->name }}" required>
                    </div>
                    <div class="ux-modal-footer">
                        <button type="button" onclick="uxCloseModal('editModal{{ $exclusion->id }}')" class="ux-btn-nova px-4 py-2.5 text-xs font-semibold">Cancel</button>
                        <button type="submit" class="ux-btn-nova ux-btn-primary px-4 py-2.5 text-xs font-semibold">Update</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach
</div>
@endsection

@push('scripts')
    <script>
        function uxOpenModal(id) { document.getElementById(id).classList.remove('hidden'); }
        function uxCloseModal(id) { document.getElementById(id).classList.add('hidden'); }
        document.addEventListener('click', function (e) {
            if (e.target.classList.contains('ux-modal-overlay')) uxCloseModal(e.target.id);
        });
    </script>
@endpush
