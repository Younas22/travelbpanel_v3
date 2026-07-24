@extends('admin-modern.layouts.app')

@section('title', 'Room Types')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Room Types</h4>
            <p class="text-muted mb-0">Manage all room types</p>
        </div>
        <a href="{{ route('admin.hotels.room-types.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Add New Room Type</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body"><div class="d-flex justify-content-between"><div><h6 class="mb-0">Total Room Types</h6><h3 class="mb-0">{{ $stats['total'] }}</h3></div><i class="bi bi-door-open fs-1 opacity-50"></i></div></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body"><div class="d-flex justify-content-between"><div><h6 class="mb-0">Active</h6><h3 class="mb-0">{{ $stats['active'] }}</h3></div><i class="bi bi-check-circle fs-1 opacity-50"></i></div></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-danger text-white">
                <div class="card-body"><div class="d-flex justify-content-between"><div><h6 class="mb-0">Inactive</h6><h3 class="mb-0">{{ $stats['inactive'] }}</h3></div><i class="bi bi-x-circle fs-1 opacity-50"></i></div></div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.hotels.room-types.index') }}" method="GET" class="row g-3">
                <div class="col-md-4"><input type="text" name="search" class="form-control" placeholder="Search by name..." value="{{ $search ?? '' }}"></div>
                <div class="col-md-3">
                    <select name="hotel_id" class="form-select">
                        <option value="">All Hotels</option>
                        @foreach($hotels as $hotel)
                            <option value="{{ $hotel->id }}" {{ ($hotelId ?? '') == $hotel->id ? 'selected' : '' }}>{{ $hotel->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="1" {{ ($status ?? '') == '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ ($status ?? '') === '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2"><button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Filter</button></div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr><th>Image</th><th>Room Type</th><th>Hotel</th><th>Price/Night</th><th>Capacity</th><th>Beds</th><th>AC</th><th>Status</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        @forelse($roomTypes as $roomType)
                        <tr>
                            <td>
                                @if($roomType->images->first())
                                    <img src="{{ asset('public/assets/images/' . $roomType->images->first()->image_path) }}" alt="" class="rounded hotel-thumb-img">
                                @else
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center hotel-thumb-placeholder"><i class="bi bi-door-open text-muted"></i></div>
                                @endif
                            </td>
                            <td><strong>{{ $roomType->name }}</strong></td>
                            <td>@if($roomType->hotel)<small>{{ $roomType->hotel->name }}</small>@endif</td>
                            <td><strong class="text-success">{{ number_format($roomType->price_per_night, 2) }}</strong></td>
                            <td><small><i class="bi bi-people"></i> {{ $roomType->max_adults }} Adults @if($roomType->max_children > 0)<br><i class="bi bi-person"></i> {{ $roomType->max_children }} Children @endif</small></td>
                            <td><span class="badge bg-secondary">{{ $roomType->beds }} Bed(s)</span></td>
                            <td>@if($roomType->ac)<i class="bi bi-snow text-primary" title="AC Available"></i>@else<i class="bi bi-x-circle text-muted" title="No AC"></i>@endif</td>
                            <td>
                                <button type="button" class="status-toggle-btn {{ $roomType->status ? 'active' : 'inactive' }}" data-id="{{ $roomType->id }}" data-status="{{ $roomType->status }}">
                                    <span class="toggle-track"><span class="toggle-thumb"></span></span>
                                </button>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.hotels.room-types.edit', $roomType) }}" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                                    <button type="button" class="btn btn-sm btn-outline-danger delete-btn" data-id="{{ $roomType->id }}" title="Delete"><i class="bi bi-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="9" class="text-center py-4"><i class="bi bi-inbox fs-1 text-muted"></i><p class="text-muted mb-0">No room types found</p></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $roomTypes->links('pagination::bootstrap-4') }}</div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.status-toggle-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const roomTypeId = this.dataset.id;
            fetch(`{{ url('admin/hotels/room-types') }}/${roomTypeId}/toggle-status`, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.classList.toggle('active');
                    this.classList.toggle('inactive');
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

    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const roomTypeId = this.dataset.id;
            if (confirm('Are you sure you want to delete this room type?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/admin/hotels/room-types/${roomTypeId}`;
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
