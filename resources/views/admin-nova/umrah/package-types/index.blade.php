@extends($layout ?? 'admin-nova.layouts.app')
@section('title', 'Umrah Package Types')

@push('styles')
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        corePlugins: { preflight: false },
        theme: {
            extend: {
                fontFamily: { jakarta: ['Plus Jakarta Sans', 'sans-serif'] },
                colors: {
                    novabg: '#F7F8FC', novablue: '#2563EB', novacyan: '#06B6D4',
                    novatext: '#000000', novamuted: '#000000', novaborder: '#E5E7EB',
                    novasuccess: '#22C55E', novawarning: '#F59E0B', novadanger: '#EF4444',
                },
            },
        },
    };
</script>
<style>
    #uxPage, #uxPage *, #uxPage *::before, #uxPage *::after { box-sizing: border-box; }
    #uxPage { font-family: 'Plus Jakarta Sans', sans-serif; }
    #uxPage h1, #uxPage h2, #uxPage h5, #uxPage p { margin: 0; padding: 0; }
    #uxPage a { text-decoration: none; color: inherit; }
    #uxPage button { font: inherit; color: inherit; background: none; border: none; cursor: pointer; padding: 0; }
    #uxPage svg { display: block; }
    #uxPage .tt-fade-in { animation: uxFadeIn .5s ease both; }
    @keyframes uxFadeIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }
    #uxPage .tt-row:hover { background: #F7F8FC; }

    #uxPage .ux-btn-nova {
        display: inline-flex; align-items: center; justify-content: center; gap: 6px;
        border: 1px solid #E5E7EB; border-radius: 9999px; color: #000; background: #fff;
        white-space: nowrap; transition: background .2s ease, border-color .2s ease;
    }
    #uxPage .ux-btn-nova:hover { background: #F7F8FC; }
    #uxPage .ux-btn-primary { background: #2563EB; color: #fff; border-color: #2563EB; }
    #uxPage .ux-btn-primary:hover { background: #1D4ED8; border-color: #1D4ED8; }
    #uxPage .ux-icon-btn {
        width: 30px; height: 30px; border-radius: 9999px; border: 1px solid #E5E7EB;
        display: inline-flex; align-items: center; justify-content: center; background: #fff;
        transition: background .2s ease, border-color .2s ease;
    }
    #uxPage .ux-icon-btn:hover { background: #F7F8FC; }
    #uxPage .ux-icon-warn:hover { background: #FFFBEB; border-color: #F59E0B; color: #F59E0B; }
    #uxPage .ux-icon-success:hover { background: #ECFDF5; border-color: #22C55E; color: #22C55E; }
    #uxPage .ux-icon-danger:hover { background: #FEF2F2; border-color: #EF4444; color: #EF4444; }

    #uxPage .ux-input, #uxPage select.ux-input {
        width: 100%; border: 1px solid #E5E7EB; border-radius: 14px; padding: 10px 14px;
        font-size: 13px; font-family: 'Plus Jakarta Sans', sans-serif; color: #000; background: #fff;
    }
    #uxPage .ux-input:focus { outline: none; border-color: #2563EB; }
    #uxPage .ux-label { display: block; font-size: 11px; font-weight: 600; color: #000; margin-bottom: 6px; }

    /* Modal overlay — plain CSS, not Bootstrap (matches Classic's own
       openModal()/closeModal() JS exactly; only the visuals are Nova-ified). */
    #uxPage .ux-modal-overlay {
        position: fixed; inset: 0; background: rgba(15,23,42,.5); z-index: 1000;
        display: flex; align-items: center; justify-content: center; padding: 20px;
    }
    #uxPage .ux-modal-overlay.hidden { display: none; }
    #uxPage .ux-modal { background: #fff; border-radius: 1.5rem; width: 100%; max-width: 420px; overflow: hidden; }
    #uxPage .ux-modal-header { display: flex; align-items: center; justify-content: space-between; padding: 18px 22px; border-bottom: 1px solid #E5E7EB; }
    #uxPage .ux-modal-body { padding: 22px; }
    #uxPage .ux-modal-footer { display: flex; justify-content: flex-end; gap: 8px; padding: 18px 22px; border-top: 1px solid #E5E7EB; }
    #uxPage .ux-modal-close { font-size: 22px; line-height: 1; color: #6B7280; cursor: pointer; }

    /* Material-style hover tooltip (see agents module for the same pattern). */
    #uxPage [data-tooltip] { position: relative; }
    #uxPage [data-tooltip]::after {
        content: attr(data-tooltip); position: absolute; bottom: calc(100% + 8px); left: 50%;
        transform: translateX(-50%) translateY(4px); background: #1F2937; color: #fff;
        font-size: 11px; font-weight: 600; line-height: 1; padding: 6px 10px; border-radius: 6px;
        white-space: nowrap; box-shadow: 0 6px 16px rgba(0,0,0,.18); opacity: 0; visibility: hidden;
        pointer-events: none; transition: opacity .15s ease, transform .15s ease, visibility .15s ease; z-index: 60;
    }
    #uxPage [data-tooltip]::before {
        content: ''; position: absolute; bottom: calc(100% + 3px); left: 50%; transform: translateX(-50%);
        border: 5px solid transparent; border-top-color: #1F2937; opacity: 0; visibility: hidden;
        pointer-events: none; transition: opacity .15s ease, visibility .15s ease; z-index: 60;
    }
    #uxPage [data-tooltip]:hover::after, #uxPage [data-tooltip]:focus-visible::after { opacity: 1; visibility: visible; transform: translateX(-50%) translateY(0); transition-delay: .25s; }
    #uxPage [data-tooltip]:hover::before, #uxPage [data-tooltip]:focus-visible::before { opacity: 1; visibility: visible; transition-delay: .25s; }
</style>
@endpush

@section('content')
<div id="uxPage" class="tt-fade-in font-jakarta">

    <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
        <div>
            <h1 class="text-lg font-bold text-novatext">Umrah Package Types</h1>
            <p class="text-xs text-novamuted mt-1">Manage package types for Umrah packages</p>
        </div>
        <button onclick="uxOpenModal('addModal')" class="ux-btn-nova ux-btn-primary px-4 py-2.5 text-xs font-semibold">
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
            Add Package Type
        </button>
    </div>

    <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 sm:p-5">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                <tr class="border-b border-novaborder">
                    <th class="text-left text-[10px] font-semibold uppercase tracking-wide text-novamuted py-2.5 px-3">#</th>
                    <th class="text-left text-[10px] font-semibold uppercase tracking-wide text-novamuted py-2.5 px-3">Package Type</th>
                    <th class="text-left text-[10px] font-semibold uppercase tracking-wide text-novamuted py-2.5 px-3">Status</th>
                    <th class="text-left text-[10px] font-semibold uppercase tracking-wide text-novamuted py-2.5 px-3">Created At</th>
                    <th class="text-right text-[10px] font-semibold uppercase tracking-wide text-novamuted py-2.5 px-3">Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse($packageTypes as $type)
                    <tr class="tt-row border-b border-novaborder last:border-0">
                        <td class="py-3 px-3 text-novamuted">{{ $type->id }}</td>
                        <td class="py-3 px-3 font-semibold text-novatext">{{ ucfirst($type->packege_type) }}</td>
                        <td class="py-3 px-3">
                            @if($type->status == '1')
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-600">Active</span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-slate-100 text-novamuted">Inactive</span>
                            @endif
                        </td>
                        <td class="py-3 px-3 text-novamuted">{{ $type->created_at->format('d M Y') }}</td>
                        <td class="py-3 px-3">
                            <div class="flex items-center justify-end gap-1.5">
                                <button onclick="uxOpenModal('editModal{{ $type->id }}')" class="ux-icon-btn" data-tooltip="Edit">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M16.474 5.408 18.592 7.526M4 20l1.11-3.92a2 2 0 0 1 .53-.9l9.9-9.9a1.5 1.5 0 0 1 2.12 0l1.06 1.06a1.5 1.5 0 0 1 0 2.12l-9.9 9.9a2 2 0 0 1-.9.53L4 20Z"/></svg>
                                </button>
                                <form action="{{ route(($routePrefix ?? 'admin.umrah.package-types') . '.toggle-status', $type->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="ux-icon-btn {{ $type->status == '1' ? 'ux-icon-warn' : 'ux-icon-success' }}" data-tooltip="{{ $type->status == '1' ? 'Deactivate' : 'Activate' }}">
                                        @if($type->status == '1')
                                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="6" y="5" width="4" height="14" rx="1"/><rect x="14" y="5" width="4" height="14" rx="1"/></svg>
                                        @else
                                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M6 4.5v15l13-7.5-13-7.5Z"/></svg>
                                        @endif
                                    </button>
                                </form>
                                <form action="{{ route(($routePrefix ?? 'admin.umrah.package-types') . '.destroy', $type->id) }}" method="POST" onsubmit="return confirm('Are you sure?')">
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
                        <td colspan="5" class="text-center py-16">
                            <svg class="w-10 h-10 text-novaborder mx-auto mb-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41 11 3.83A2 2 0 0 0 9.59 3.24H4a1 1 0 0 0-1 1v5.59a2 2 0 0 0 .59 1.41l9.58 9.59a2 2 0 0 0 2.83 0l6.59-6.59a2 2 0 0 0 0-2.83Z"/><circle cx="7.5" cy="7.5" r="1.5"/></svg>
                            <p class="text-sm font-semibold text-novatext">No package types found</p>
                            <p class="text-xs text-novamuted mt-1">Add your first package type to get started.</p>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($packageTypes->hasPages())
            <div class="mt-6 pt-5 border-t border-novaborder">
                {{ $packageTypes->links() }}
            </div>
        @endif
    </div>

    {{-- ===== ADD MODAL ===== --}}
    <div id="addModal" class="ux-modal-overlay hidden">
        <div class="ux-modal">
            <form action="{{ route(($routePrefix ?? 'admin.umrah.package-types') . '.store') }}" method="POST">
                @csrf
                <div class="ux-modal-header">
                    <h5 class="text-sm font-bold text-novatext">Add Package Type</h5>
                    <button type="button" onclick="uxCloseModal('addModal')" class="ux-modal-close">&times;</button>
                </div>
                <div class="ux-modal-body space-y-4">
                    <div>
                        <label class="ux-label">Package Type Name</label>
                        <input type="text" name="packege_type" class="ux-input" placeholder="e.g., basic, standard, premium" required>
                    </div>
                    <div>
                        <label class="ux-label">Status</label>
                        <select name="status" class="ux-input">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="ux-modal-footer">
                    <button type="button" onclick="uxCloseModal('addModal')" class="ux-btn-nova px-4 py-2.5 text-xs font-semibold">Cancel</button>
                    <button type="submit" class="ux-btn-nova ux-btn-primary px-4 py-2.5 text-xs font-semibold">Add Package Type</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ===== EDIT MODALS ===== --}}
    @foreach($packageTypes as $type)
        <div id="editModal{{ $type->id }}" class="ux-modal-overlay hidden">
            <div class="ux-modal">
                <form action="{{ route(($routePrefix ?? 'admin.umrah.package-types') . '.update', $type->id) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="ux-modal-header">
                        <h5 class="text-sm font-bold text-novatext">Edit Package Type</h5>
                        <button type="button" onclick="uxCloseModal('editModal{{ $type->id }}')" class="ux-modal-close">&times;</button>
                    </div>
                    <div class="ux-modal-body space-y-4">
                        <div>
                            <label class="ux-label">Package Type Name</label>
                            <input type="text" name="packege_type" class="ux-input" value="{{ $type->packege_type }}" required>
                        </div>
                        <div>
                            <label class="ux-label">Status</label>
                            <select name="status" class="ux-input">
                                <option value="1" {{ $type->status == '1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ $type->status == '0' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="ux-modal-footer">
                        <button type="button" onclick="uxCloseModal('editModal{{ $type->id }}')" class="ux-btn-nova px-4 py-2.5 text-xs font-semibold">Cancel</button>
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
