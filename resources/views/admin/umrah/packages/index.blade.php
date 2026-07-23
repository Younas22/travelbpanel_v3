@extends('admin.layouts.app')

@section('title', 'Umrah Packages')

@section('content')
<div class="content-area p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Umrah Packages</h4>
            <p class="text-muted mb-0">Manage all Umrah packages</p>
        </div>
        <a href="{{ route('admin.umrah.packages.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add New Package
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="mb-0">Total Packages</h6>
                            <h3 class="mb-0">{{ $stats['total'] }}</h3>
                        </div>
                        <i class="bi bi-box-seam fs-1 opacity-50"></i>
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
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="mb-0">Featured</h6>
                            <h3 class="mb-0">{{ $stats['featured'] }}</h3>
                        </div>
                        <i class="bi bi-star fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.umrah.packages.index') }}" method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search by name or location..." value="{{ $search ?? '' }}">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="1" {{ ($status ?? '') == '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ ($status ?? '') === '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="package_type" class="form-select">
                        <option value="">All Package Types</option>
                        @foreach($packageTypes as $type)
                            <option value="{{ $type->packege_type }}" {{ ($packageType ?? '') == $type->packege_type ? 'selected' : '' }}>{{ ucfirst($type->packege_type) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Packages Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Image</th>
                            <th>Package Name</th>
                            <th>Type</th>
                            <th>Price</th>
                            <th>Duration</th>
                            <th>Leaving From</th>
                            <th>Going To</th>
                            <th>Approval</th>
                            <th>Status</th>
                            <th>Featured</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($packages as $package)
                        <tr>
                            <td>
                                @if($package->images->first())
                                    <img src="{{ asset('public/assets/images/' . $package->images->first()->image) }}" alt="" class="rounded hotel-thumb-img">
                                @else
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center hotel-thumb-placeholder">
                                        <i class="bi bi-image text-muted"></i>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $package->name }}</strong>
                                <br><small class="text-muted"><i class="bi bi-geo-alt"></i> {{ $package->loaction }}</small>
                                @if(!empty($package->inclusions) || !empty($package->exclusions))
                                <div class="mt-2">
                                    <a class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" href="#details_{{ $package->id }}" role="button">
                                        <i class="bi bi-list-check"></i> Details
                                    </a>
                                    <div class="collapse mt-2" id="details_{{ $package->id }}">
                                        @if(!empty($package->inclusions))
                                        <div class="mb-2">
                                            <small class="text-success fw-bold"><i class="bi bi-check-circle"></i> Inclusions:</small>
                                            <div class="d-flex flex-wrap gap-1 mt-1">
                                                @foreach($package->inclusions as $incId)
                                                    @if(isset($allInclusions[$incId]))
                                                        <span class="badge bg-success-subtle text-success">{{ $allInclusions[$incId]->name }}</span>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                        @endif
                                        @if(!empty($package->exclusions))
                                        <div>
                                            <small class="text-danger fw-bold"><i class="bi bi-x-circle"></i> Exclusions:</small>
                                            <div class="d-flex flex-wrap gap-1 mt-1">
                                                @foreach($package->exclusions as $excId)
                                                    @if(isset($allExclusions[$excId]))
                                                        <span class="badge bg-danger-subtle text-danger">{{ $allExclusions[$excId]->name }}</span>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                @endif
                            </td>
                            <td><span class="badge bg-info">{{ ucfirst($package->packege_type) }}</span></td>
                            <td><strong>{{ $package->currceny }} {{ number_format($package->price) }}</strong></td>
                            <td>{{ $package->duration }}</td>
                            <td>
                                @if($package->leaving_from && isset($airports[$package->leaving_from]))
                                    <small>{{ $airports[$package->leaving_from]->airport }} - {{ $airports[$package->leaving_from]->city }}, {{ $airports[$package->leaving_from]->country }}</small>
                                @else
                                    <small class="text-muted">-</small>
                                @endif
                            </td>
                            <td>
                                @if($package->going_to && isset($airports[$package->going_to]))
                                    <small>{{ $airports[$package->going_to]->airport }} - {{ $airports[$package->going_to]->city }}, {{ $airports[$package->going_to]->country }}</small>
                                @else
                                    <small class="text-muted">-</small>
                                @endif
                            </td>
                            <td>
                                @if(($package->approval_status ?? 'approved') === 'pending')
                                    <span class="badge bg-warning text-dark">Pending</span>
                                    <div class="mt-1 d-flex gap-1">
                                        <form action="{{ route('admin.umrah.packages.approve', $package->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-xs btn-success hotel-approval-btn">
                                                <i class="bi bi-check-lg"></i> Approve
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.umrah.packages.reject', $package->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-xs btn-danger hotel-approval-btn">
                                                <i class="bi bi-x-lg"></i> Reject
                                            </button>
                                        </form>
                                    </div>
                                @elseif(($package->approval_status ?? 'approved') === 'rejected')
                                    <span class="badge bg-danger">Rejected</span>
                                @else
                                    <span class="badge bg-success">Approved</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $package->status == '1' ? 'bg-success' : 'bg-danger' }}">
                                    {{ $package->status == '1' ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <form action="{{ route('admin.umrah.packages.toggle-featured', $package->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm {{ $package->featured == '1' ? 'btn-warning' : 'btn-outline-warning' }}">
                                        <i class="bi bi-star{{ $package->featured == '1' ? '-fill' : '' }}"></i>
                                    </button>
                                </form>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('admin.umrah.packages.edit', $package->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('admin.umrah.packages.toggle-status', $package->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-outline-{{ $package->status == '1' ? 'warning' : 'success' }}">
                                            <i class="bi bi-{{ $package->status == '1' ? 'pause' : 'play' }}"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.umrah.packages.destroy', $package->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this package?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-4">
                                <i class="bi bi-inbox fs-1 text-muted"></i>
                                <p class="mt-2 mb-0">No packages found</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $packages->links() }}
        </div>
    </div>
</div>
@endsection
