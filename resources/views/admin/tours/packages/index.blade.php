@extends('admin.layouts.app')
@section('title', 'Tour Packages')

@section('content')

    <!-- ===== PAGE HEADER ===== -->
    <div class="tpk-header">
        <div>
            <h2 class="tpk-title">Tour Packages</h2>
            <p class="tpk-subtitle">Manage all tour packages</p>
        </div>
        <a href="{{ route('admin.tours.packages.create') }}" class="tpk-add-btn">
            <i class="bi bi-plus-circle"></i> Add New Package
        </a>
    </div>

    <!-- ===== STATS ===== -->
    <div class="tpk-stats">
        <div class="tpk-stat">
            <div class="tpk-stat-icon tpk-icon-accent"><i class="bi bi-box-seam"></i></div>
            <div>
                <div class="tpk-stat-value">{{ $stats['total'] }}</div>
                <div class="tpk-stat-label">Total packages</div>
            </div>
        </div>
        <div class="tpk-stat">
            <div class="tpk-stat-icon tpk-icon-accent"><i class="bi bi-check-circle"></i></div>
            <div>
                <div class="tpk-stat-value">{{ $stats['active'] }}</div>
                <div class="tpk-stat-label">Active</div>
            </div>
        </div>
        <div class="tpk-stat">
            <div class="tpk-stat-icon tpk-icon-red"><i class="bi bi-x-circle"></i></div>
            <div>
                <div class="tpk-stat-value">{{ $stats['inactive'] }}</div>
                <div class="tpk-stat-label">Inactive</div>
            </div>
        </div>
        <div class="tpk-stat">
            <div class="tpk-stat-icon tpk-icon-amber"><i class="bi bi-star"></i></div>
            <div>
                <div class="tpk-stat-value">{{ $stats['featured'] }}</div>
                <div class="tpk-stat-label">Featured</div>
            </div>
        </div>
    </div>

    <!-- ===== FILTERS ===== -->
    <div class="tpk-filter-card">
        <form action="{{ route('admin.tours.packages.index') }}" method="GET" class="tpk-filter-grid">
            <div class="tpk-filter-search">
                <i class="bi bi-search"></i>
                <input type="text" name="search" class="form-control"
                       placeholder="Search by name or location..."
                       value="{{ $search ?? '' }}">
            </div>
            <select name="status" class="form-select">
                <option value="">All status</option>
                <option value="1" {{ ($status ?? '') == '1' ? 'selected' : '' }}>Active</option>
                <option value="0" {{ ($status ?? '') === '0' ? 'selected' : '' }}>Inactive</option>
            </select>
            <select name="package_type" class="form-select">
                <option value="">All package types</option>
                @foreach($packageTypes as $type)
                    <option value="{{ $type->packege_type }}"
                        {{ ($packageType ?? '') == $type->packege_type ? 'selected' : '' }}>
                        {{ ucfirst($type->packege_type) }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="tpk-btn tpk-btn-primary">
                <i class="bi bi-funnel"></i> Filter
            </button>
        </form>
    </div>

    <!-- ===== TABLE CARD ===== -->
    <div class="tpk-table-card">
        <div class="table-responsive">
            <table class="tpk-table">
                <thead>
                <tr>
                    <th>Image</th>
                    <th>Package Name</th>
                    <th>Location</th>
                    <th>Type</th>
                    <th>Price</th>
                    <th>Duration</th>
                    <th>Approval</th>
                    <th>Status</th>
                    <th>Featured</th>
                    <th class="text-end">Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse($packages as $package)
                    <tr>
                        <!-- Image -->
                        <td>
                            @if($package->images->first())
                                <img src="{{ asset('public/assets/images/' . $package->images->first()->image) }}"
                                     alt="" class="tpk-thumb">
                            @else
                                <div class="tpk-thumb-placeholder">
                                    <i class="bi bi-image"></i>
                                </div>
                            @endif
                        </td>

                        <!-- Package Name + Details -->
                        <td>
                            <div class="tpk-pkg-name">{{ $package->name }}</div>
                            @if(!empty($package->inclusions) || !empty($package->exclusions))
                                <button class="tpk-details-toggle"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#details_{{ $package->id }}"
                                        type="button">
                                    <i class="bi bi-list-check"></i> Details
                                </button>
                                <div class="collapse mt-2" id="details_{{ $package->id }}">
                                    @if(!empty($package->inclusions))
                                        <div class="tpk-detail-group">
                                            <div class="tpk-detail-label tpk-detail-inc">
                                                <i class="bi bi-check-circle"></i> Inclusions
                                            </div>
                                            <div class="tpk-tags">
                                                @foreach($package->inclusions as $incId)
                                                    @if(isset($allInclusions[$incId]))
                                                        <span class="tpk-tag tpk-tag-inc">{{ $allInclusions[$incId]->name }}</span>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                    @if(!empty($package->exclusions))
                                        <div class="tpk-detail-group">
                                            <div class="tpk-detail-label tpk-detail-exc">
                                                <i class="bi bi-x-circle"></i> Exclusions
                                            </div>
                                            <div class="tpk-tags">
                                                @foreach($package->exclusions as $excId)
                                                    @if(isset($allExclusions[$excId]))
                                                        <span class="tpk-tag tpk-tag-exc">{{ $allExclusions[$excId]->name }}</span>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </td>

                        <!-- Location -->
                        <td>
                            @if($package->location)
                                <span class="tpk-location">
                                        <i class="bi bi-geo-alt"></i>
                                        {{ $package->location->city }}, {{ $package->location->country }}
                                    </span>
                            @else
                                <span class="tpk-meta">—</span>
                            @endif
                        </td>

                        <!-- Type -->
                        <td><span class="tpk-badge tpk-badge-type">{{ ucfirst($package->packege_type) }}</span></td>

                        <!-- Price -->
                        <td><span class="tpk-price">{{ $package->currceny }} {{ number_format($package->price) }}</span></td>

                        <!-- Duration -->
                        <td><span class="tpk-meta">{{ $package->duration }}</span></td>

                        <!-- Approval -->
                        <td>
                            @php $approval = $package->approval_status ?? 'approved'; @endphp
                            @if($approval === 'pending')
                                <span class="tpk-badge tpk-badge-pending">Pending</span>
                                <div class="tpk-approve-actions">
                                    <form action="{{ route('admin.tours.packages.approve', $package->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="tpk-micro-btn tpk-micro-approve">
                                            <i class="bi bi-check-lg"></i> Approve
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.tours.packages.reject', $package->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="tpk-micro-btn tpk-micro-reject">
                                            <i class="bi bi-x-lg"></i> Reject
                                        </button>
                                    </form>
                                </div>
                            @elseif($approval === 'rejected')
                                <span class="tpk-badge tpk-badge-rejected">Rejected</span>
                            @else
                                <span class="tpk-badge tpk-badge-approved">Approved</span>
                            @endif
                        </td>

                        <!-- Status -->
                        <td>
                                <span class="tpk-badge {{ $package->status == '1' ? 'tpk-badge-active' : 'tpk-badge-inactive' }}">
                                    {{ $package->status == '1' ? 'Active' : 'Inactive' }}
                                </span>
                        </td>

                        <!-- Featured -->
                        <td>
                            <form action="{{ route('admin.tours.packages.toggle-featured', $package->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="tpk-action-btn {{ $package->featured == '1' ? 'tpk-action-featured-on' : 'tpk-action-featured-off' }}"
                                        title="{{ $package->featured == '1' ? 'Remove featured' : 'Mark featured' }}">
                                    <i class="bi bi-star{{ $package->featured == '1' ? '-fill' : '' }}"></i>
                                </button>
                            </form>
                        </td>

                        <!-- Actions -->
                        <td>
                            <div class="tpk-actions">
                                <a href="{{ route('admin.tours.packages.edit', $package->id) }}"
                                   class="tpk-action-btn tpk-action-edit" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.tours.packages.toggle-status', $package->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                            class="tpk-action-btn {{ $package->status == '1' ? 'tpk-action-pause' : 'tpk-action-activate' }}"
                                            title="{{ $package->status == '1' ? 'Deactivate' : 'Activate' }}">
                                        <i class="bi bi-{{ $package->status == '1' ? 'pause-circle' : 'play-circle' }}"></i>
                                    </button>
                                </form>
                                <form action="{{ route('admin.tours.packages.destroy', $package->id) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Are you sure you want to delete this package?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="tpk-action-btn tpk-action-delete" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10">
                            <div class="tpk-empty">
                                <div class="tpk-empty-icon"><i class="bi bi-box-seam"></i></div>
                                <h5>No packages found</h5>
                                <p>Try adjusting your filters or add a new package.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($packages->hasPages())
            <div class="tpk-pagination">
                {{ $packages->withQueryString()->links() }}
            </div>
        @endif
    </div>

@endsection

