@extends('admin.layouts.app')

@section('title', 'Hotels')

@section('content')
<div class="content-area p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Hotels</h4>
            <p class="text-muted mb-0">Manage all hotels</p>
        </div>
        <a href="{{ route('admin.hotels.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add New Hotel
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="mb-0">Total Hotels</h6>
                            <h3 class="mb-0">{{ $stats['total'] }}</h3>
                        </div>
                        <i class="bi bi-building fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="mb-0">Active</h6>
                            <h3 class="mb-0">{{ $stats['active'] }}</h3>
                        </div>
                        <i class="bi bi-check-circle fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="mb-0">Inactive</h6>
                            <h3 class="mb-0">{{ $stats['inactive'] }}</h3>
                        </div>
                        <i class="bi bi-x-circle fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="mb-0">Hotels</h6>
                            <h3 class="mb-0">{{ $stats['hotel'] }}</h3>
                        </div>
                        <i class="bi bi-building-fill fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.hotels.index') }}" method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search by name, address or location..." value="{{ $search ?? '' }}">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="1" {{ ($status ?? '') == '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ ($status ?? '') === '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="type" class="form-select">
                        <option value="">All Types</option>
                        <option value="hotel" {{ ($type ?? '') == 'hotel' ? 'selected' : '' }}>Hotel</option>
                        <option value="guest house" {{ ($type ?? '') == 'guest house' ? 'selected' : '' }}>Guest House</option>
                        <option value="resort" {{ ($type ?? '') == 'resort' ? 'selected' : '' }}>Resort</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Hotels Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Image</th>
                            <th>Hotel Name</th>
                            <th>Location</th>
                            <th>Type</th>
                            <th>Total Rooms</th>
                            <th>Contact</th>
                            <th>Approval</th>
                            <th>Status</th>
                            <th>Featured</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($hotels as $hotel)
                        <tr>
                            <td>
                                @if($hotel->images->first())
                                    <img src="{{ asset('public/assets/images/' . $hotel->images->first()->image_path) }}" alt="" class="rounded hotel-thumb-img">
                                @else
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center hotel-thumb-placeholder">
                                        <i class="bi bi-building text-muted"></i>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $hotel->name }}</strong>
                                <br>
                                <small class="text-muted">{{ $hotel->address }}</small>
                            </td>
                            <td>
                                @if($hotel->location)
                                    <i class="bi bi-geo-alt text-primary"></i>
                                    {{ $hotel->location->city }}, {{ $hotel->location->country }}
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-info">{{ ucfirst($hotel->type) }}</span>
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $hotel->total_rooms ?? 0 }}</span>
                            </td>
                            <td>
                                @if($hotel->phone)
                                    <small><i class="bi bi-telephone"></i> {{ $hotel->phone }}</small><br>
                                @endif
                                @if($hotel->email)
                                    <small><i class="bi bi-envelope"></i> {{ $hotel->email }}</small>
                                @endif
                            </td>
                            <td>
                                @if(($hotel->approval_status ?? 'approved') === 'pending')
                                    <span class="badge bg-warning text-dark">Pending</span>
                                    <div class="mt-1 d-flex gap-1">
                                        <form action="{{ route('admin.hotels.approve', $hotel) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-xs btn-success hotel-approval-btn" title="Approve">
                                                <i class="bi bi-check-lg"></i> Approve
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-xs btn-danger reject-btn hotel-approval-btn"
                                            data-hotel-id="{{ $hotel->id }}"
                                            data-hotel-name="{{ $hotel->name }}"
                                            data-reject-url="{{ route('admin.hotels.reject', $hotel) }}">
                                            <i class="bi bi-x-lg"></i> Reject
                                        </button>
                                    </div>
                                @elseif(($hotel->approval_status ?? 'approved') === 'rejected')
                                    <span class="badge bg-danger">Rejected</span>
                                @else
                                    <span class="badge bg-success">Approved</span>
                                @endif
                            </td>
                            <td>
                                <button type="button"
                                    class="status-toggle-btn {{ $hotel->status ? 'active' : 'inactive' }}"
                                    data-id="{{ $hotel->id }}"
                                    data-status="{{ $hotel->status }}">
                                    <span class="toggle-track">
                                        <span class="toggle-thumb"></span>
                                    </span>
                                </button>
                            </td>
                            <td>
                                <form action="{{ route('admin.hotels.toggle-featured', $hotel->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm {{ $hotel->featured == '1' ? 'btn-warning' : 'btn-outline-warning' }}" title="{{ $hotel->featured == '1' ? 'Featured' : 'Not Featured' }}">
                                        <i class="bi bi-star{{ $hotel->featured == '1' ? '-fill' : '' }}"></i>
                                    </button>
                                </form>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.hotels.edit', $hotel) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger delete-btn" data-id="{{ $hotel->id }}" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-4">
                                <i class="bi bi-inbox fs-1 text-muted"></i>
                                <p class="text-muted mb-0">No hotels found</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $hotels->links() }}
            </div>
        </div>
    </div>
</div>


<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="rejectModalLabel"><i class="bi bi-x-circle me-2"></i>Reject Hotel</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="modal-body">
                    <p class="mb-3">You are rejecting: <strong id="rejectHotelName"></strong></p>
                    <div class="mb-3">
                        <label for="rejectReason" class="form-label fw-semibold">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea id="rejectReason" name="reason" class="form-control" rows="4"
                            placeholder="Please provide a reason for rejection..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger"><i class="bi bi-x-lg me-1"></i>Reject Hotel</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Status toggle
    document.querySelectorAll('.status-toggle-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const hotelId = this.dataset.id;
            const isActive = this.classList.contains('active');

            fetch(`{{ url('admin/hotels') }}/${hotelId}/toggle-status`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Toggle button state
                    this.classList.toggle('active');
                    this.classList.toggle('inactive');
                    // Show success message
                    const alert = document.createElement('div');
                    alert.className = 'alert alert-success alert-dismissible fade show position-fixed top-0 end-0 m-3';
                    alert.style.zIndex = '9999';
                    alert.innerHTML = `${data.message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
                    document.body.appendChild(alert);
                    setTimeout(() => alert.remove(), 3000);
                }
            })
            .catch(error => console.error('Error:', error));
        });
    });

    // Reject modal
    document.querySelectorAll('.reject-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('rejectHotelName').textContent = this.dataset.hotelName;
            document.getElementById('rejectReason').value = '';
            document.getElementById('rejectForm').action = this.dataset.rejectUrl;
            new bootstrap.Modal(document.getElementById('rejectModal')).show();
        });
    });

    // Delete button
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const hotelId = this.dataset.id;

            if (confirm('Are you sure you want to delete this hotel? All related data will be removed.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `{{ url('admin/hotels') }}/${hotelId}`;
                form.innerHTML = `
                    <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').content}">
                    <input type="hidden" name="_method" value="DELETE">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        });
    });
});
</script>
@endpush
@endsection
