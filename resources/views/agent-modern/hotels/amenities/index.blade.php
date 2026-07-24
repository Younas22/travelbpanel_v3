@extends('agent-modern.layouts.app')
@section('title', 'Hotel Amenities')

@section('content')

    <div class="ap-page-header">
        <div>
            <h4 class="ap-page-title">Hotel Amenities</h4>
            <p class="ap-page-sub">Manage amenities used in your hotels</p>
        </div>
        <button type="button" class="ap-btn-primary" data-bs-toggle="modal" data-bs-target="#addAmenityModal">
            <i class="bi bi-plus-lg"></i> Add Amenity
        </button>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-4">
            <div class="dash-stat-card">
                <div class="dash-stat-icon icon-blue"><i class="bi bi-star"></i></div>
                <div>
                    <div class="dash-stat-label">Total Amenities</div>
                    <div class="dash-stat-value">{{ $stats['total'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="am-card mb-4">
        <div class="am-card-body">
            <form action="{{ route('agent.hotels.amenities.index') }}" method="GET" class="d-flex gap-3">
                <input type="text" name="search" class="form-control" placeholder="Search amenities..." value="{{ $search ?? '' }}">
                <button type="submit" class="ap-btn-primary" style="flex-shrink: 0;">
                    <i class="bi bi-search"></i> Search
                </button>
                @if($search)
                    <a href="{{ route('agent.hotels.amenities.index') }}" class="ap-btn-outline" style="flex-shrink: 0;">Clear</a>
                @endif
            </form>
        </div>
    </div>

    <div class="am-card">
        @if($amenities->isEmpty())
            <div class="am-empty">
                <i class="bi bi-star"></i>
                <h6>No amenities yet</h6>
                <p class="mb-3">Add amenities like WiFi, Pool, Parking etc.</p>
                <button type="button" class="ap-btn-primary" data-bs-toggle="modal" data-bs-target="#addAmenityModal">
                    <i class="bi bi-plus-lg"></i> Add First Amenity
                </button>
            </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Icon Preview</th>
                        <th>Icon Class</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($amenities as $amenity)
                    <tr>
                        <td style="color: color-mix(in srgb, var(--text-color) 50%, transparent); font-size: 12px;">{{ $amenity->id }}</td>
                        <td style="font-weight: 650;">{{ $amenity->name }}</td>
                        <td>
                            @if($amenity->icon)
                                <div style="width: 32px; height: 32px; border-radius: var(--radius-md); background: var(--primary-tint-10); display: flex; align-items: center; justify-content: center;">
                                    <i class="{{ $amenity->icon }}" style="color: var(--primary-color); font-size: 13px;"></i>
                                </div>
                            @else
                                <span style="color: color-mix(in srgb, var(--text-color) 30%, transparent);">—</span>
                            @endif
                        </td>
                        <td>
                            @if($amenity->icon)
                                <code style="font-size: 11px; background: color-mix(in srgb, var(--text-color) 8%, transparent); color: var(--text-color); padding: 2px 6px; border-radius: 4px;">{{ $amenity->icon }}</code>
                            @else
                                <span style="color: color-mix(in srgb, var(--text-color) 30%, transparent); font-size: 12px;">No icon</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" class="ap-btn-outline edit-btn" style="padding: 6px 12px; font-size: 11.5px;"
                                        data-id="{{ $amenity->id }}" data-name="{{ $amenity->name }}" data-icon="{{ $amenity->icon }}"
                                        data-bs-toggle="modal" data-bs-target="#editAmenityModal">
                                    <i class="bi bi-pencil"></i> Edit
                                </button>
                                <button type="button" class="ap-btn-danger delete-btn" style="padding: 6px 12px; font-size: 11.5px;" data-id="{{ $amenity->id }}">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($amenities->hasPages())
        <div style="padding: var(--am-space-4) var(--am-space-5); border-top: 1px solid var(--border-color);">
            {{ $amenities->links() }}
        </div>
        @endif
        @endif
    </div>

    {{-- ===== ADD MODAL ===== --}}
    <div class="modal fade" id="addAmenityModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="addAmenityForm">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Amenity</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Name <span style="color: var(--danger-color);">*</span></label>
                            <input type="text" name="name" id="add_name" class="form-control" placeholder="e.g. WiFi, Swimming Pool" required>
                        </div>
                        <div class="mb-0">
                            <label class="form-label">Icon Class <span style="font-weight: 400; font-size: 11px; color: color-mix(in srgb, var(--text-color) 50%, transparent);">(FontAwesome)</span></label>
                            <div class="d-flex gap-2">
                                <input type="text" name="icon" id="add_icon" class="form-control" placeholder="e.g. fas fa-wifi">
                                <div style="width: 40px; height: 40px; flex-shrink: 0; background: var(--am-input-fill); border: 1px solid var(--border-color); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center;" id="add_icon_preview">
                                    <i class="bi bi-question" style="color: color-mix(in srgb, var(--text-color) 30%, transparent);"></i>
                                </div>
                            </div>
                            <div class="form-text">Use FontAwesome classes like <code>fas fa-wifi</code></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="ap-btn-outline" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="ap-btn-primary">
                            <i class="bi bi-plus-lg"></i> Add Amenity
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ===== EDIT MODAL ===== --}}
    <div class="modal fade" id="editAmenityModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editAmenityForm">
                    @csrf
                    <input type="hidden" id="edit_amenity_id">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Amenity</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Name <span style="color: var(--danger-color);">*</span></label>
                            <input type="text" name="name" id="edit_name" class="form-control" required>
                        </div>
                        <div class="mb-0">
                            <label class="form-label">Icon Class <span style="font-weight: 400; font-size: 11px; color: color-mix(in srgb, var(--text-color) 50%, transparent);">(FontAwesome)</span></label>
                            <div class="d-flex gap-2">
                                <input type="text" name="icon" id="edit_icon" class="form-control" placeholder="e.g. fas fa-wifi">
                                <div style="width: 40px; height: 40px; flex-shrink: 0; background: var(--am-input-fill); border: 1px solid var(--border-color); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center;" id="edit_icon_preview">
                                    <i class="bi bi-question" style="color: color-mix(in srgb, var(--text-color) 30%, transparent);"></i>
                                </div>
                            </div>
                            <div class="form-text">Use FontAwesome classes like <code>fas fa-wifi</code></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="ap-btn-outline" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="ap-btn-primary">
                            <i class="bi bi-check-lg"></i> Update Amenity
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
const baseUrl   = '{{ url("agent/hotels/amenities") }}';

function bindIconPreview(inputId, previewId) {
    document.getElementById(inputId).addEventListener('input', function () {
        const preview = document.getElementById(previewId);
        preview.innerHTML = this.value
            ? `<i class="${this.value}" style="color: var(--primary-color); font-size: 13px;"></i>`
            : `<i class="bi bi-question" style="color: color-mix(in srgb, var(--text-color) 30%, transparent);"></i>`;
    });
}
bindIconPreview('add_icon', 'add_icon_preview');
bindIconPreview('edit_icon', 'edit_icon_preview');

document.getElementById('addAmenityForm').addEventListener('submit', function (e) {
    e.preventDefault();
    const formData = new FormData(this);
    fetch(baseUrl, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken }, body: formData })
        .then(r => r.json())
        .then(data => { if (data.success) location.reload(); });
});

document.querySelectorAll('.edit-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        const icon = this.dataset.icon || '';
        document.getElementById('edit_amenity_id').value = this.dataset.id;
        document.getElementById('edit_name').value       = this.dataset.name;
        document.getElementById('edit_icon').value       = icon;
        document.getElementById('edit_icon_preview').innerHTML = icon
            ? `<i class="${icon}" style="color: var(--primary-color); font-size: 13px;"></i>`
            : `<i class="bi bi-question" style="color: color-mix(in srgb, var(--text-color) 30%, transparent);"></i>`;
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
