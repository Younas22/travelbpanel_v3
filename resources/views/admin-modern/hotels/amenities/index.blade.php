@extends('admin-modern.layouts.app')

@section('title', 'Hotel Amenities')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Hotel Amenities</h4>
            <p class="text-muted mb-0">Manage all hotel amenities</p>
        </div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAmenityModal"><i class="bi bi-plus-circle"></i> Add New Amenity</button>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white"><div class="card-body"><div class="d-flex justify-content-between"><div><h6 class="mb-0">Total Amenities</h6><h3 class="mb-0">{{ $stats['total'] }}</h3></div><i class="bi bi-stars fs-1 opacity-50"></i></div></div></div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route(($routePrefix ?? 'admin.hotels.amenities') . '.index') }}" method="GET" class="row g-3">
                <div class="col-md-10"><input type="text" name="search" class="form-control" placeholder="Search amenities..." value="{{ $search ?? '' }}"></div>
                <div class="col-md-2"><button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Search</button></div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light"><tr><th>ID</th><th>Name</th><th>Icon</th><th>Actions</th></tr></thead>
                    <tbody>
                        @forelse($amenities as $amenity)
                        <tr>
                            <td>{{ $amenity->id }}</td>
                            <td><strong>{{ $amenity->name }}</strong></td>
                            <td>@if($amenity->icon)<i class="{{ $amenity->icon }}"></i> {{ $amenity->icon }}@else<span class="text-muted">-</span>@endif</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary edit-btn" data-id="{{ $amenity->id }}" data-name="{{ $amenity->name }}" data-icon="{{ $amenity->icon }}" data-bs-toggle="modal" data-bs-target="#editAmenityModal"><i class="bi bi-pencil"></i></button>
                                    <button type="button" class="btn btn-sm btn-outline-danger delete-btn" data-id="{{ $amenity->id }}"><i class="bi bi-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center py-4"><i class="bi bi-inbox fs-1 text-muted"></i><p class="text-muted mb-0">No amenities found</p></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $amenities->links('pagination::bootstrap-4') }}</div>
        </div>
    </div>

<div class="modal fade" id="addAmenityModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Amenity</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addAmenityForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Name <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" required></div>
                    <div class="mb-3">
                        <label class="form-label">Icon (Bootstrap Icons class)</label>
                        <input type="text" name="icon" class="form-control" placeholder="e.g., bi bi-wifi">
                        <small class="text-muted">Use Bootstrap Icons: <a href="https://icons.getbootstrap.com/" target="_blank">Browse Icons</a></small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Add Amenity</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editAmenityModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Amenity</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editAmenityForm">
                @csrf
                @method('PATCH')
                <input type="hidden" id="edit_amenity_id">
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Name <span class="text-danger">*</span></label><input type="text" name="name" id="edit_name" class="form-control" required></div>
                    <div class="mb-3">
                        <label class="form-label">Icon (Bootstrap Icons class)</label>
                        <input type="text" name="icon" id="edit_icon" class="form-control" placeholder="e.g., bi bi-wifi">
                        <small class="text-muted">Use Bootstrap Icons: <a href="https://icons.getbootstrap.com/" target="_blank">Browse Icons</a></small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update Amenity</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    document.getElementById('addAmenityForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        fetch('{{ $baseUrl ?? url('admin/hotels/amenities') }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            body: formData
        })
        .then(response => response.json())
        .then(data => { if (data.success) location.reload(); })
        .catch(error => console.error('Error:', error));
    });

    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('edit_amenity_id').value = this.dataset.id;
            document.getElementById('edit_name').value = this.dataset.name;
            document.getElementById('edit_icon').value = this.dataset.icon || '';
        });
    });

    document.getElementById('editAmenityForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const amenityId = document.getElementById('edit_amenity_id').value;
        const formData = new FormData(this);
        fetch(`{{ $baseUrl ?? url('admin/hotels/amenities') }}/${amenityId}`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'X-HTTP-Method-Override': 'PATCH' },
            body: formData
        })
        .then(response => response.json())
        .then(data => { if (data.success) location.reload(); })
        .catch(error => console.error('Error:', error));
    });

    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const amenityId = this.dataset.id;
            if (confirm('Are you sure you want to delete this amenity?')) {
                fetch(`{{ $baseUrl ?? url('admin/hotels/amenities') }}/${amenityId}`, {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken }
                })
                .then(response => response.json())
                .then(data => { if (data.success) location.reload(); else alert(data.message); })
                .catch(error => console.error('Error:', error));
            }
        });
    });
});
</script>
@endpush
