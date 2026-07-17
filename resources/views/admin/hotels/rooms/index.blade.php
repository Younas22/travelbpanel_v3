@extends('admin.layouts.app')

@section('title', 'Rooms')

@section('content')
<div class="content-area p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Rooms</h4>
            <p class="text-muted mb-0">Manage all rooms</p>
        </div>
        <a href="{{ route('admin.hotels.rooms.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add New Room
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="mb-0">Total Rooms</h6>
                            <h3 class="mb-0">{{ $stats['total'] }}</h3>
                        </div>
                        <i class="bi bi-key fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="mb-0">Available</h6>
                            <h3 class="mb-0">{{ $stats['available'] }}</h3>
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
                            <h6 class="mb-0">Occupied</h6>
                            <h3 class="mb-0">{{ $stats['occupied'] }}</h3>
                        </div>
                        <i class="bi bi-person-fill fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="mb-0">Maintenance</h6>
                            <h3 class="mb-0">{{ $stats['maintenance'] }}</h3>
                        </div>
                        <i class="bi bi-tools fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.hotels.rooms.index') }}" method="GET" class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Search room number..." value="{{ $search ?? '' }}">
                </div>
                <div class="col-md-3">
                    <select name="hotel_id" class="form-select">
                        <option value="">All Hotels</option>
                        @foreach($hotels as $hotel)
                            <option value="{{ $hotel->id }}" {{ ($hotelId ?? '') == $hotel->id ? 'selected' : '' }}>{{ $hotel->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="room_type_id" class="form-select">
                        <option value="">All Room Types</option>
                        @foreach($roomTypes as $type)
                            <option value="{{ $type->id }}" {{ ($roomTypeId ?? '') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="available" {{ ($status ?? '') == 'available' ? 'selected' : '' }}>Available</option>
                        <option value="occupied" {{ ($status ?? '') == 'occupied' ? 'selected' : '' }}>Occupied</option>
                        <option value="maintenance" {{ ($status ?? '') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Rooms Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Room Number</th>
                            <th>Hotel</th>
                            <th>Room Type</th>
                            <th>Floor</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rooms as $room)
                        <tr>
                            <td><strong>{{ $room->room_number }}</strong></td>
                            <td>
                                @if($room->hotel)
                                    <small>{{ $room->hotel->name }}</small>
                                @endif
                            </td>
                            <td>
                                @if($room->roomType)
                                    <span class="badge bg-info">{{ $room->roomType->name }}</span>
                                @endif
                            </td>
                            <td>{{ $room->floor ?? '-' }}</td>
                            <td>
                                @if($room->status == 'available')
                                    <span class="badge bg-success">Available</span>
                                @elseif($room->status == 'occupied')
                                    <span class="badge bg-danger">Occupied</span>
                                @else
                                    <span class="badge bg-warning text-dark">Maintenance</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.hotels.rooms.edit', $room) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger delete-btn" data-id="{{ $room->id }}" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <i class="bi bi-inbox fs-1 text-muted"></i>
                                <p class="text-muted mb-0">No rooms found</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $rooms->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const roomId = this.dataset.id;

            if (confirm('Are you sure you want to delete this room?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `{{ url('admin/hotels/rooms') }}/${roomId}`;
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
