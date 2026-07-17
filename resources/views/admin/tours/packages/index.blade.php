@extends('admin.layouts.app')
@section('title', 'Tour Packages')

@section('content')

    <!-- ===== PAGE HEADER ===== -->
    <div class="tp-header">
        <div>
            <h2 class="tp-title">Tour Packages</h2>
            <p class="tp-subtitle">Manage all tour packages</p>
        </div>
        <a href="{{ route('admin.tours.packages.create') }}" class="tp-add-btn">
            <i class="bi bi-plus-circle"></i> Add New Package
        </a>
    </div>

    <!-- ===== STATS ===== -->
    <div class="tp-stats">
        <div class="tp-stat">
            <div class="tp-stat-icon tp-icon-accent"><i class="bi bi-box-seam"></i></div>
            <div>
                <div class="tp-stat-value">{{ $stats['total'] }}</div>
                <div class="tp-stat-label">Total packages</div>
            </div>
        </div>
        <div class="tp-stat">
            <div class="tp-stat-icon tp-icon-accent"><i class="bi bi-check-circle"></i></div>
            <div>
                <div class="tp-stat-value">{{ $stats['active'] }}</div>
                <div class="tp-stat-label">Active</div>
            </div>
        </div>
        <div class="tp-stat">
            <div class="tp-stat-icon tp-icon-red"><i class="bi bi-x-circle"></i></div>
            <div>
                <div class="tp-stat-value">{{ $stats['inactive'] }}</div>
                <div class="tp-stat-label">Inactive</div>
            </div>
        </div>
        <div class="tp-stat">
            <div class="tp-stat-icon tp-icon-amber"><i class="bi bi-star"></i></div>
            <div>
                <div class="tp-stat-value">{{ $stats['featured'] }}</div>
                <div class="tp-stat-label">Featured</div>
            </div>
        </div>
    </div>

    <!-- ===== FILTERS ===== -->
    <div class="tp-filter-card">
        <form action="{{ route('admin.tours.packages.index') }}" method="GET" class="tp-filter-grid">
            <div class="tp-filter-search">
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
            <button type="submit" class="tp-btn tp-btn-primary">
                <i class="bi bi-funnel"></i> Filter
            </button>
        </form>
    </div>

    <!-- ===== TABLE CARD ===== -->
    <div class="tp-table-card">
        <div class="table-responsive">
            <table class="tp-table">
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
                    <th style="text-align:right">Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse($packages as $package)
                    <tr>
                        <!-- Image -->
                        <td>
                            @if($package->images->first())
                                <img src="{{ asset('public/assets/images/' . $package->images->first()->image) }}"
                                     alt="" class="tp-thumb">
                            @else
                                <div class="tp-thumb-placeholder">
                                    <i class="bi bi-image"></i>
                                </div>
                            @endif
                        </td>

                        <!-- Package Name + Details -->
                        <td>
                            <div class="tp-pkg-name">{{ $package->name }}</div>
                            @if(!empty($package->inclusions) || !empty($package->exclusions))
                                <button class="tp-details-toggle"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#details_{{ $package->id }}"
                                        type="button">
                                    <i class="bi bi-list-check"></i> Details
                                </button>
                                <div class="collapse mt-2" id="details_{{ $package->id }}">
                                    @if(!empty($package->inclusions))
                                        <div class="tp-detail-group">
                                            <div class="tp-detail-label tp-detail-inc">
                                                <i class="bi bi-check-circle"></i> Inclusions
                                            </div>
                                            <div class="tp-tags">
                                                @foreach($package->inclusions as $incId)
                                                    @if(isset($allInclusions[$incId]))
                                                        <span class="tp-tag tp-tag-inc">{{ $allInclusions[$incId]->name }}</span>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                    @if(!empty($package->exclusions))
                                        <div class="tp-detail-group">
                                            <div class="tp-detail-label tp-detail-exc">
                                                <i class="bi bi-x-circle"></i> Exclusions
                                            </div>
                                            <div class="tp-tags">
                                                @foreach($package->exclusions as $excId)
                                                    @if(isset($allExclusions[$excId]))
                                                        <span class="tp-tag tp-tag-exc">{{ $allExclusions[$excId]->name }}</span>
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
                                <span class="tp-location">
                                        <i class="bi bi-geo-alt"></i>
                                        {{ $package->location->city }}, {{ $package->location->country }}
                                    </span>
                            @else
                                <span class="tp-meta">—</span>
                            @endif
                        </td>

                        <!-- Type -->
                        <td><span class="tp-badge tp-badge-type">{{ ucfirst($package->packege_type) }}</span></td>

                        <!-- Price -->
                        <td><span class="tp-price">{{ $package->currceny }} {{ number_format($package->price) }}</span></td>

                        <!-- Duration -->
                        <td><span class="tp-meta">{{ $package->duration }}</span></td>

                        <!-- Approval -->
                        <td>
                            @php $approval = $package->approval_status ?? 'approved'; @endphp
                            @if($approval === 'pending')
                                <span class="tp-badge tp-badge-pending">Pending</span>
                                <div class="tp-approve-actions">
                                    <form action="{{ route('admin.tours.packages.approve', $package->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="tp-micro-btn tp-micro-approve">
                                            <i class="bi bi-check-lg"></i> Approve
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.tours.packages.reject', $package->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="tp-micro-btn tp-micro-reject">
                                            <i class="bi bi-x-lg"></i> Reject
                                        </button>
                                    </form>
                                </div>
                            @elseif($approval === 'rejected')
                                <span class="tp-badge tp-badge-rejected">Rejected</span>
                            @else
                                <span class="tp-badge tp-badge-approved">Approved</span>
                            @endif
                        </td>

                        <!-- Status -->
                        <td>
                                <span class="tp-badge {{ $package->status == '1' ? 'tp-badge-active' : 'tp-badge-inactive' }}">
                                    {{ $package->status == '1' ? 'Active' : 'Inactive' }}
                                </span>
                        </td>

                        <!-- Featured -->
                        <td>
                            <form action="{{ route('admin.tours.packages.toggle-featured', $package->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="tp-action-btn {{ $package->featured == '1' ? 'tp-action-featured-on' : 'tp-action-featured-off' }}"
                                        title="{{ $package->featured == '1' ? 'Remove featured' : 'Mark featured' }}">
                                    <i class="bi bi-star{{ $package->featured == '1' ? '-fill' : '' }}"></i>
                                </button>
                            </form>
                        </td>

                        <!-- Actions -->
                        <td>
                            <div class="tp-actions">
                                <a href="{{ route('admin.tours.packages.edit', $package->id) }}"
                                   class="tp-action-btn tp-action-edit" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.tours.packages.toggle-status', $package->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                            class="tp-action-btn {{ $package->status == '1' ? 'tp-action-pause' : 'tp-action-activate' }}"
                                            title="{{ $package->status == '1' ? 'Deactivate' : 'Activate' }}">
                                        <i class="bi bi-{{ $package->status == '1' ? 'pause-circle' : 'play-circle' }}"></i>
                                    </button>
                                </form>
                                <form action="{{ route('admin.tours.packages.destroy', $package->id) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Are you sure you want to delete this package?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="tp-action-btn tp-action-delete" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10">
                            <div class="tp-empty">
                                <div class="tp-empty-icon"><i class="bi bi-box-seam"></i></div>
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
            <div class="tp-pagination">
                {{ $packages->withQueryString()->links() }}
            </div>
        @endif
    </div>

@endsection

@push('styles')
    <style>
        /* ===== PAGE HEADER ===== */
        .tp-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 1.25rem;
        }
        .tp-title {
            font-size: 20px; font-weight: 600;
            color: var(--bs-body-color); margin: 0 0 4px;
        }
        .tp-subtitle { font-size: 13px; color: var(--bs-secondary-color); margin: 0; }
        .tp-add-btn {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 9px 18px; border-radius: 8px;
            font-size: 13px; font-weight: 500;
            background: #0C6DFD; border: 1px solid #0C6DFD; color: #fff;
            text-decoration: none; transition: opacity .15s;
        }
        .tp-add-btn:hover { opacity: .9; color: #fff; text-decoration: none; }

        /* ===== STATS ===== */
        .tp-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 1.25rem;
        }
        .tp-stat {
            background: var(--bs-body-bg);
            border: 1px solid var(--bs-border-color);
            border-radius: 14px;
            padding: 14px 16px;
            display: flex; align-items: center; gap: 12px;
        }
        .tp-stat-icon {
            width: 42px; height: 42px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 19px; flex-shrink: 0;
        }
        .tp-icon-accent { background: #E3F0FF; color: #0C6DFD; }
        .tp-icon-red    { background: #FCEBEB; color: #A32D2D; }
        .tp-icon-amber  { background: #FAEEDA; color: #633806; }
        [data-bs-theme="dark"] .tp-icon-accent { background: #0a2a4d; color: #6ba8ff; }
        [data-bs-theme="dark"] .tp-icon-red    { background: #2e0a0a; color: #f08080; }
        [data-bs-theme="dark"] .tp-icon-amber  { background: #2e1e05; color: #f0b054; }
        .tp-stat-value { font-size: 22px; font-weight: 600; color: var(--bs-body-color); line-height: 1; }
        .tp-stat-label { font-size: 12px; color: var(--bs-secondary-color); margin-top: 3px; }

        /* ===== FILTER CARD ===== */
        .tp-filter-card {
            background: var(--bs-body-bg);
            border: 1px solid var(--bs-border-color);
            border-radius: 14px;
            padding: .85rem 1.1rem;
            margin-bottom: 1.25rem;
        }
        .tp-filter-grid {
            display: grid;
            grid-template-columns: 1fr 180px 200px auto;
            gap: 10px; align-items: center;
        }
        .tp-filter-search { position: relative; }
        .tp-filter-search i {
            position: absolute; left: 12px; top: 50%;
            transform: translateY(-50%);
            font-size: 13px; color: var(--bs-secondary-color); pointer-events: none;
        }
        .tp-filter-search .form-control { padding-left: 34px; }
        .tp-filter-card .form-control,
        .tp-filter-card .form-select {
            border-radius: 8px; border-color: var(--bs-border-color); font-size: 13px;
        }
        .tp-btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 6px;
            padding: 9px 18px; border-radius: 8px;
            font-size: 13px; font-weight: 500;
            cursor: pointer; white-space: nowrap;
            border: 1px solid var(--bs-border-color);
            transition: opacity .15s;
        }
        .tp-btn-primary { background: #0C6DFD; border-color: #0C6DFD; color: #fff; }
        .tp-btn-primary:hover { opacity: .9; color: #fff; }

        /* ===== TABLE CARD ===== */
        .tp-table-card {
            background: var(--bs-body-bg);
            border: 1px solid var(--bs-border-color);
            border-radius: 14px; overflow: hidden;
        }
        .tp-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .tp-table thead th {
            padding: 10px 14px;
            font-size: 11px; font-weight: 600;
            text-transform: uppercase; letter-spacing: .4px;
            color: var(--bs-secondary-color);
            border-bottom: 1px solid var(--bs-border-color);
            white-space: nowrap; text-align: left;
            background: var(--bs-secondary-bg);
        }
        .tp-table tbody td {
            padding: 11px 14px;
            border-bottom: 1px solid var(--bs-border-color);
            vertical-align: middle; color: var(--bs-body-color);
        }
        .tp-table tbody tr:last-child td { border-bottom: none; }
        .tp-table tbody tr:hover td { background: var(--bs-tertiary-bg); }

        /* ===== THUMBNAIL ===== */
        .tp-thumb {
            width: 52px; height: 52px;
            border-radius: 8px; object-fit: cover;
            border: 1px solid var(--bs-border-color);
        }
        .tp-thumb-placeholder {
            width: 52px; height: 52px;
            border-radius: 8px;
            background: var(--bs-secondary-bg);
            border: 1px solid var(--bs-border-color);
            display: flex; align-items: center; justify-content: center;
            font-size: 18px; color: var(--bs-secondary-color);
        }

        /* ===== PACKAGE NAME / DETAILS ===== */
        .tp-pkg-name { font-size: 13px; font-weight: 600; color: var(--bs-body-color); }
        .tp-details-toggle {
            display: inline-flex; align-items: center; gap: 5px;
            margin-top: 5px; padding: 3px 9px;
            border-radius: 6px; font-size: 11px; font-weight: 500;
            background: var(--bs-secondary-bg);
            border: 1px solid var(--bs-border-color);
            color: var(--bs-secondary-color); cursor: pointer;
            transition: background .12s;
        }
        .tp-details-toggle:hover { background: var(--bs-tertiary-bg); color: var(--bs-body-color); }
        .tp-detail-group { margin-bottom: 8px; }
        .tp-detail-label {
            font-size: 11px; font-weight: 600; margin-bottom: 4px;
            display: flex; align-items: center; gap: 4px;
        }
        .tp-detail-inc { color: #0C6DFD; }
        .tp-detail-exc { color: #A32D2D; }
        .tp-tags { display: flex; flex-wrap: wrap; gap: 4px; }
        .tp-tag {
            font-size: 11px; font-weight: 500;
            padding: 2px 8px; border-radius: 20px;
        }
        .tp-tag-inc { background: #E3F0FF; color: #0C6DFD; }
        .tp-tag-exc { background: #FCEBEB; color: #A32D2D; }
        [data-bs-theme="dark"] .tp-tag-inc { background: #0a2a4d; color: #6ba8ff; }
        [data-bs-theme="dark"] .tp-tag-exc { background: #2e0a0a; color: #f08080; }

        /* ===== MISC CELLS ===== */
        .tp-meta { font-size: 12px; color: var(--bs-secondary-color); }
        .tp-location { font-size: 12px; color: var(--bs-secondary-color); display: flex; align-items: center; gap: 4px; }
        .tp-location i { color: #0C6DFD; font-size: 13px; }
        .tp-price { font-size: 13px; font-weight: 600; color: var(--bs-body-color); }

        /* ===== BADGES ===== */
        .tp-badge {
            display: inline-block; font-size: 11px; font-weight: 600;
            padding: 3px 10px; border-radius: 20px; white-space: nowrap;
        }
        .tp-badge-type     { background: #E3F0FF; color: #0C6DFD; }
        .tp-badge-active   { background: #E3F0FF; color: #0C6DFD; }
        .tp-badge-inactive { background: #FCEBEB; color: #A32D2D; }
        .tp-badge-approved { background: #E3F0FF; color: #0C6DFD; }
        .tp-badge-pending  { background: #FAEEDA; color: #633806; }
        .tp-badge-rejected { background: #FCEBEB; color: #A32D2D; }
        [data-bs-theme="dark"] .tp-badge-type,
        [data-bs-theme="dark"] .tp-badge-active,
        [data-bs-theme="dark"] .tp-badge-approved { background: #0a2a4d; color: #6ba8ff; }
        [data-bs-theme="dark"] .tp-badge-inactive,
        [data-bs-theme="dark"] .tp-badge-rejected { background: #2e0a0a; color: #f08080; }
        [data-bs-theme="dark"] .tp-badge-pending  { background: #2e1e05; color: #f0b054; }

        /* ===== APPROVE / REJECT MICRO BUTTONS ===== */
        .tp-approve-actions { display: flex; gap: 4px; margin-top: 5px; flex-wrap: wrap; }
        .tp-micro-btn {
            display: inline-flex; align-items: center; gap: 3px;
            padding: 3px 8px; border-radius: 6px;
            font-size: 11px; font-weight: 500; cursor: pointer; border: none;
            transition: opacity .12s;
        }
        .tp-micro-approve { background: #0C6DFD; color: #fff; }
        .tp-micro-approve:hover { opacity: .85; color: #fff; }
        .tp-micro-reject  { background: #E24B4A; color: #fff; }
        .tp-micro-reject:hover { opacity: .85; color: #fff; }

        /* ===== ACTION BUTTONS ===== */
        .tp-actions { display: flex; align-items: center; justify-content: flex-end; gap: 5px; flex-wrap: wrap; }
        .tp-action-btn {
            width: 30px; height: 30px;
            display: inline-flex; align-items: center; justify-content: center;
            border-radius: 7px; font-size: 13px; cursor: pointer;
            border: 1px solid var(--bs-border-color);
            transition: background .12s, color .12s, opacity .12s;
            text-decoration: none;
        }
        .tp-action-edit {
            background: var(--bs-secondary-bg); color: var(--bs-body-color);
        }
        .tp-action-edit:hover { background: var(--bs-tertiary-bg); color: var(--bs-body-color); text-decoration: none; }

        .tp-action-pause {
            background: #FAEEDA; color: #633806; border-color: transparent;
        }
        .tp-action-pause:hover { opacity: .8; }
        [data-bs-theme="dark"] .tp-action-pause { background: #2e1e05; color: #f0b054; }

        .tp-action-activate {
            background: #E3F0FF; color: #0C6DFD; border-color: transparent;
        }
        .tp-action-activate:hover { opacity: .8; }
        [data-bs-theme="dark"] .tp-action-activate { background: #0a2a4d; color: #6ba8ff; }

        .tp-action-delete {
            background: #FCEBEB; color: #A32D2D; border-color: transparent;
        }
        .tp-action-delete:hover { background: #f7c1c1; }
        [data-bs-theme="dark"] .tp-action-delete { background: #2e0a0a; color: #f08080; }

        /* Featured toggle */
        .tp-action-featured-on {
            background: #FAEEDA; color: #633806; border-color: transparent;
        }
        .tp-action-featured-on:hover { opacity: .8; }
        .tp-action-featured-off {
            background: var(--bs-secondary-bg); color: var(--bs-secondary-color);
        }
        .tp-action-featured-off:hover { background: #FAEEDA; color: #633806; border-color: transparent; }
        [data-bs-theme="dark"] .tp-action-featured-on { background: #2e1e05; color: #f0b054; }

        /* ===== EMPTY STATE ===== */
        .tp-empty { text-align: center; padding: 3rem 1rem; }
        .tp-empty-icon {
            width: 52px; height: 52px; border-radius: 50%;
            background: #E3F0FF; color: #0C6DFD;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px; margin: 0 auto 12px;
        }
        [data-bs-theme="dark"] .tp-empty-icon { background: #0a2a4d; color: #6ba8ff; }
        .tp-empty h5 { font-size: 14px; font-weight: 600; color: var(--bs-body-color); margin: 0 0 4px; }
        .tp-empty p { font-size: 12px; color: var(--bs-secondary-color); margin: 0; }

        /* ===== PAGINATION ===== */
        .tp-pagination { padding: 14px 20px; border-top: 1px solid var(--bs-border-color); }
        .tp-pagination .pagination { margin: 0; }
        .tp-pagination .page-link {
            border-radius: 7px; border-color: var(--bs-border-color);
            color: var(--bs-body-color); font-size: 13px;
            margin: 0 2px; background: var(--bs-body-bg);
        }
        .tp-pagination .page-item.active .page-link { background: #0C6DFD; border-color: #0C6DFD; color: #fff; }
        .tp-pagination .page-item.disabled .page-link { color: var(--bs-secondary-color); background: var(--bs-secondary-bg); }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 992px) {
            .tp-stats { grid-template-columns: repeat(2, 1fr); }
            .tp-filter-grid { grid-template-columns: 1fr 1fr; }
            .tp-filter-search { grid-column: 1 / -1; }
        }
        @media (max-width: 576px) {
            .tp-stats { grid-template-columns: 1fr 1fr; }
            .tp-filter-grid { grid-template-columns: 1fr; }
            .tp-filter-search { grid-column: auto; }
            .tp-header { align-items: flex-start; }
            .tp-add-btn { width: 100%; justify-content: center; }
        }
    </style>
@endpush
