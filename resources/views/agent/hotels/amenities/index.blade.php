@extends('agent.layouts.app')
@section('title', 'Hotel Amenities')

@section('content')

{{-- Page Header --}}
<div class="flex items-center justify-between mb-5">
    <div>
        <h4 class="text-lg font-bold text-gray-800">Hotel Amenities</h4>
        <p class="text-xs text-gray-400 mt-0.5">Manage amenities used in your hotels</p>
    </div>
    <button type="button" class="ap-btn-primary" onclick="openModal('addModal')">
        <i class="fas fa-plus text-xs"></i> Add Amenity
    </button>
</div>

{{-- Stats --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-5">
    <div class="ap-card">
        <div class="ap-card-body flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-blue-50 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-star text-blue-500"></i>
            </div>
            <div>
                <p class="text-xs text-gray-400">Total Amenities</p>
                <p class="text-2xl font-bold text-gray-800 leading-none mt-0.5">{{ $stats['total'] }}</p>
            </div>
        </div>
    </div>
</div>

{{-- Search --}}
<div class="ap-card mb-5">
    <div class="ap-card-body">
        <form action="{{ route('agent.hotels.amenities.index') }}" method="GET" class="flex gap-3">
            <input type="text" name="search" class="ap-input" placeholder="Search amenities..." value="{{ $search ?? '' }}">
            <button type="submit" class="ap-btn-primary flex-shrink-0">
                <i class="fas fa-search text-xs"></i> Search
            </button>
            @if($search)
                <a href="{{ route('agent.hotels.amenities.index') }}" class="ap-btn-outline flex-shrink-0">Clear</a>
            @endif
        </form>
    </div>
</div>

{{-- Table --}}
<div class="ap-card">
    @if($amenities->isEmpty())
        <div class="text-center py-16">
            <div class="w-16 h-16 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-star text-blue-400 text-2xl"></i>
            </div>
            <p class="text-gray-500 font-medium">No amenities yet</p>
            <p class="text-gray-400 text-sm mt-1 mb-4">Add amenities like WiFi, Pool, Parking etc.</p>
            <button type="button" class="ap-btn-primary" onclick="openModal('addModal')">
                <i class="fas fa-plus text-xs"></i> Add First Amenity
            </button>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="ap-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Icon Preview</th>
                        <th>Icon Class</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($amenities as $amenity)
                    <tr>
                        <td class="text-gray-400 text-xs">{{ $amenity->id }}</td>
                        <td class="font-semibold text-gray-800">{{ $amenity->name }}</td>
                        <td>
                            @if($amenity->icon)
                                <div class="w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center">
                                    <i class="{{ $amenity->icon }} text-blue-500 text-sm"></i>
                                </div>
                            @else
                                <span class="text-gray-300">—</span>
                            @endif
                        </td>
                        <td>
                            @if($amenity->icon)
                                <code class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded">{{ $amenity->icon }}</code>
                            @else
                                <span class="text-gray-300 text-xs">No icon</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button type="button" class="ap-btn-outline-sm edit-btn"
                                        data-id="{{ $amenity->id }}"
                                        data-name="{{ $amenity->name }}"
                                        data-icon="{{ $amenity->icon }}">
                                    <i class="fas fa-pencil text-xs"></i> Edit
                                </button>
                                <button type="button" class="ap-btn-danger-sm delete-btn" data-id="{{ $amenity->id }}">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($amenities->hasPages())
        <div class="px-5 py-3 border-t border-gray-100">
            {{ $amenities->links() }}
        </div>
        @endif
    @endif
</div>

{{-- ===== ADD MODAL ===== --}}
<div class="ap-modal-overlay" id="addModal" onclick="closeOnBackdrop(event, 'addModal')">
    <div class="ap-modal" onclick="event.stopPropagation()">
        <div class="ap-modal-header">
            <span>Add New Amenity</span>
            <button type="button" class="ap-modal-close" onclick="closeModal('addModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="addAmenityForm">
            @csrf
            <div class="ap-modal-body space-y-4">
                <div>
                    <label class="ap-label">Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="add_name" class="ap-input" placeholder="e.g. WiFi, Swimming Pool" required>
                </div>
                <div>
                    <label class="ap-label">Icon Class <span class="text-xs font-normal text-gray-400">(FontAwesome)</span></label>
                    <div class="flex gap-2">
                        <input type="text" name="icon" id="add_icon" class="ap-input" placeholder="e.g. fas fa-wifi">
                        <div class="w-10 h-10 flex-shrink-0 bg-gray-50 border border-gray-200 rounded-lg flex items-center justify-center" id="add_icon_preview">
                            <i class="fas fa-question text-gray-300 text-sm"></i>
                        </div>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">Use FontAwesome classes like <code class="bg-gray-100 px-1 rounded">fas fa-wifi</code></p>
                </div>
            </div>
            <div class="ap-modal-footer">
                <button type="button" class="ap-btn-outline" onclick="closeModal('addModal')">Cancel</button>
                <button type="submit" class="ap-btn-primary">
                    <i class="fas fa-plus text-xs"></i> Add Amenity
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ===== EDIT MODAL ===== --}}
<div class="ap-modal-overlay" id="editModal" onclick="closeOnBackdrop(event, 'editModal')">
    <div class="ap-modal" onclick="event.stopPropagation()">
        <div class="ap-modal-header">
            <span>Edit Amenity</span>
            <button type="button" class="ap-modal-close" onclick="closeModal('editModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="editAmenityForm">
            @csrf
            <input type="hidden" id="edit_amenity_id">
            <div class="ap-modal-body space-y-4">
                <div>
                    <label class="ap-label">Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="edit_name" class="ap-input" required>
                </div>
                <div>
                    <label class="ap-label">Icon Class <span class="text-xs font-normal text-gray-400">(FontAwesome)</span></label>
                    <div class="flex gap-2">
                        <input type="text" name="icon" id="edit_icon" class="ap-input" placeholder="e.g. fas fa-wifi">
                        <div class="w-10 h-10 flex-shrink-0 bg-gray-50 border border-gray-200 rounded-lg flex items-center justify-center" id="edit_icon_preview">
                            <i class="fas fa-question text-gray-300 text-sm"></i>
                        </div>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">Use FontAwesome classes like <code class="bg-gray-100 px-1 rounded">fas fa-wifi</code></p>
                </div>
            </div>
            <div class="ap-modal-footer">
                <button type="button" class="ap-btn-outline" onclick="closeModal('editModal')">Cancel</button>
                <button type="submit" class="ap-btn-primary">
                    <i class="fas fa-check text-xs"></i> Update Amenity
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
const baseUrl   = '{{ url("agent/hotels/amenities") }}';

// ── Modal Helpers ──────────────────────────────────
function openModal(id)  { document.getElementById(id).classList.add('active'); }
function closeModal(id) { document.getElementById(id).classList.remove('active'); }
function closeOnBackdrop(e, id) { if (e.target === document.getElementById(id)) closeModal(id); }

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') ['addModal', 'editModal'].forEach(closeModal);
});

// ── Icon live preview ──────────────────────────────
function bindIconPreview(inputId, previewId) {
    document.getElementById(inputId).addEventListener('input', function () {
        const preview = document.getElementById(previewId);
        preview.innerHTML = this.value
            ? `<i class="${this.value} text-blue-500 text-sm"></i>`
            : `<i class="fas fa-question text-gray-300 text-sm"></i>`;
    });
}
bindIconPreview('add_icon', 'add_icon_preview');
bindIconPreview('edit_icon', 'edit_icon_preview');

// ── Add Amenity ────────────────────────────────────
document.getElementById('addAmenityForm').addEventListener('submit', function (e) {
    e.preventDefault();
    const formData = new FormData(this);
    fetch(baseUrl, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken }, body: formData })
        .then(r => r.json())
        .then(data => { if (data.success) location.reload(); });
});

// ── Edit Amenity ───────────────────────────────────
document.querySelectorAll('.edit-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        const icon = this.dataset.icon || '';
        document.getElementById('edit_amenity_id').value = this.dataset.id;
        document.getElementById('edit_name').value       = this.dataset.name;
        document.getElementById('edit_icon').value       = icon;
        document.getElementById('edit_icon_preview').innerHTML = icon
            ? `<i class="${icon} text-blue-500 text-sm"></i>`
            : `<i class="fas fa-question text-gray-300 text-sm"></i>`;
        openModal('editModal');
    });
});

document.getElementById('editAmenityForm').addEventListener('submit', function (e) {
    e.preventDefault();
    const id       = document.getElementById('edit_amenity_id').value;
    const formData = new FormData(this);
    fetch(`${baseUrl}/${id}`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'X-HTTP-Method-Override': 'PATCH' },
        body: formData
    })
    .then(r => r.json())
    .then(data => { if (data.success) location.reload(); });
});

// ── Delete Amenity ─────────────────────────────────
document.querySelectorAll('.delete-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        if (!confirm('Delete this amenity?')) return;
        fetch(`${baseUrl}/${this.dataset.id}`, {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) location.reload();
            else alert(data.message);
        });
    });
});
</script>
@endpush

@endsection
