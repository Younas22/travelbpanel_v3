@extends('admin-modern.layouts.app')
@section('title', 'Tour Packages')

@section('content')

    <div class="tpk-header">
        <div>
            <h2 class="tpk-title">Tour Packages</h2>
            <p class="tpk-subtitle">Manage all tour packages</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <div class="view-switch" id="tpkViewSwitch">
                <button type="button" data-view="list" class="active"><i class="bi bi-list-ul"></i> List</button>
                <button type="button" data-view="grid"><i class="bi bi-grid-3x3-gap"></i> Grid</button>
            </div>
            <a href="{{ route('admin.tours.packages.create') }}" class="tpk-add-btn">
                <i class="bi bi-plus-circle"></i> Add New Package
            </a>
        </div>
    </div>

    <div class="tpk-stats">
        <div class="tpk-stat">
            <div class="tpk-stat-icon tpk-icon-accent"><i class="bi bi-box-seam"></i></div>
            <div><div class="tpk-stat-value">{{ $stats['total'] }}</div><div class="tpk-stat-label">Total packages</div></div>
        </div>
        <div class="tpk-stat">
            <div class="tpk-stat-icon tpk-icon-accent"><i class="bi bi-check-circle"></i></div>
            <div><div class="tpk-stat-value">{{ $stats['active'] }}</div><div class="tpk-stat-label">Active</div></div>
        </div>
        <div class="tpk-stat">
            <div class="tpk-stat-icon tpk-icon-red"><i class="bi bi-x-circle"></i></div>
            <div><div class="tpk-stat-value">{{ $stats['inactive'] }}</div><div class="tpk-stat-label">Inactive</div></div>
        </div>
        <div class="tpk-stat">
            <div class="tpk-stat-icon tpk-icon-amber"><i class="bi bi-star"></i></div>
            <div><div class="tpk-stat-value">{{ $stats['featured'] }}</div><div class="tpk-stat-label">Featured</div></div>
        </div>
    </div>

    <div class="tpk-filter-card">
        <form action="{{ route('admin.tours.packages.index') }}" method="GET" class="tpk-filter-grid">
            <div class="tpk-filter-search">
                <i class="bi bi-search"></i>
                <input type="text" name="search" class="form-control" placeholder="Search by name or location..." value="{{ $search ?? '' }}">
            </div>
            <select name="status" class="form-select">
                <option value="">All status</option>
                <option value="1" {{ ($status ?? '') == '1' ? 'selected' : '' }}>Active</option>
                <option value="0" {{ ($status ?? '') === '0' ? 'selected' : '' }}>Inactive</option>
            </select>
            <select name="package_type" class="form-select">
                <option value="">All package types</option>
                @foreach($packageTypes as $type)
                    <option value="{{ $type->packege_type }}" {{ ($packageType ?? '') == $type->packege_type ? 'selected' : '' }}>{{ ucfirst($type->packege_type) }}</option>
                @endforeach
            </select>
            <button type="submit" class="tpk-btn tpk-btn-primary"><i class="bi bi-funnel"></i> Filter</button>
        </form>
    </div>

    <div class="tpk-table-card">
        <div class="table-responsive">
            <table class="tpk-table">
                <thead>
                <tr>
                    <th>Image</th><th>Package Name</th><th>Location</th><th>Type</th><th>Price</th>
                    <th>Duration</th><th>Approval</th><th>Status</th><th>Featured</th><th class="text-end">Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse($packages as $package)
                    <tr>
                        <td>
                            @if($package->images->first())
                                <img src="{{ asset('public/assets/images/' . $package->images->first()->image) }}" alt="" class="tpk-thumb">
                            @else
                                <div class="tpk-thumb-placeholder"><i class="bi bi-image"></i></div>
                            @endif
                        </td>
                        <td>
                            <div class="tpk-pkg-name">{{ $package->name }}</div>
                            @if(!empty($package->inclusions) || !empty($package->exclusions))
                                <button class="tpk-details-toggle" data-bs-toggle="collapse" data-bs-target="#details_{{ $package->id }}" type="button"><i class="bi bi-list-check"></i> Details</button>
                                <div class="collapse mt-2" id="details_{{ $package->id }}">
                                    @if(!empty($package->inclusions))
                                        <div class="tpk-detail-group">
                                            <div class="tpk-detail-label tpk-detail-inc"><i class="bi bi-check-circle"></i> Inclusions</div>
                                            <div class="tpk-tags">
                                                @foreach($package->inclusions as $incId)
                                                    @if(isset($allInclusions[$incId]))<span class="tpk-tag tpk-tag-inc">{{ $allInclusions[$incId]->name }}</span>@endif
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                    @if(!empty($package->exclusions))
                                        <div class="tpk-detail-group">
                                            <div class="tpk-detail-label tpk-detail-exc"><i class="bi bi-x-circle"></i> Exclusions</div>
                                            <div class="tpk-tags">
                                                @foreach($package->exclusions as $excId)
                                                    @if(isset($allExclusions[$excId]))<span class="tpk-tag tpk-tag-exc">{{ $allExclusions[$excId]->name }}</span>@endif
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </td>
                        <td>
                            @if($package->location)
                                <span class="tpk-location"><i class="bi bi-geo-alt"></i> {{ $package->location->city }}, {{ $package->location->country }}</span>
                            @else
                                <span class="tpk-meta">—</span>
                            @endif
                        </td>
                        <td><span class="tpk-badge tpk-badge-type">{{ ucfirst($package->packege_type) }}</span></td>
                        <td><span class="tpk-price">{{ $package->currceny }} {{ number_format($package->price) }}</span></td>
                        <td><span class="tpk-meta">{{ $package->duration }}</span></td>
                        <td>
                            @php $approval = $package->approval_status ?? 'approved'; @endphp
                            @if($approval === 'pending')
                                <span class="tpk-badge tpk-badge-pending">Pending</span>
                                <div class="tpk-approve-actions">
                                    <form action="{{ route('admin.tours.packages.approve', $package->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="tpk-micro-btn tpk-micro-approve"><i class="bi bi-check-lg"></i> Approve</button>
                                    </form>
                                    <form action="{{ route('admin.tours.packages.reject', $package->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="tpk-micro-btn tpk-micro-reject"><i class="bi bi-x-lg"></i> Reject</button>
                                    </form>
                                </div>
                            @elseif($approval === 'rejected')
                                <span class="tpk-badge tpk-badge-rejected">Rejected</span>
                            @else
                                <span class="tpk-badge tpk-badge-approved">Approved</span>
                            @endif
                        </td>
                        <td><span class="tpk-badge js-status-badge {{ $package->status == '1' ? 'tpk-badge-active' : 'tpk-badge-inactive' }}" data-id="{{ $package->id }}">{{ $package->status == '1' ? 'Active' : 'Inactive' }}</span></td>
                        <td>
                            <button type="button" class="tpk-action-btn has-label js-toggle-featured {{ $package->featured == '1' ? 'tpk-action-featured-on' : 'tpk-action-featured-off' }}"
                                    data-id="{{ $package->id }}" data-featured="{{ $package->featured == '1' ? '1' : '0' }}">
                                <i class="bi bi-star{{ $package->featured == '1' ? '-fill' : '' }}"></i>
                                <span>{{ $package->featured == '1' ? 'Featured' : 'Not featured' }}</span>
                            </button>
                        </td>
                        <td>
                            <div class="tpk-actions">
                                <a href="{{ route('admin.tours.packages.edit', $package->id) }}" class="tpk-action-btn tpk-action-edit" title="Edit"><i class="bi bi-pencil"></i></a>
                                <button type="button" class="tpk-action-btn has-label js-toggle-status {{ $package->status == '1' ? 'tpk-action-pause' : 'tpk-action-activate' }}"
                                        data-id="{{ $package->id }}" data-status="{{ $package->status == '1' ? '1' : '0' }}">
                                    <i class="bi bi-{{ $package->status == '1' ? 'pause-circle' : 'play-circle' }}"></i>
                                    <span>{{ $package->status == '1' ? 'Deactivate' : 'Activate' }}</span>
                                </button>
                                <form action="{{ route('admin.tours.packages.destroy', $package->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this package?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="tpk-action-btn tpk-action-delete" title="Delete"><i class="bi bi-trash"></i></button>
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
            <div class="tpk-pagination">{{ $packages->withQueryString()->links() }}</div>
        @endif
    </div>

    <div class="pkg-grid" id="tpkGridView" style="display:none;">
        @forelse($packages as $package)
            <div class="pkg-card">
                <div class="pkg-card-img">
                    @if($package->images->first())
                        <img src="{{ asset('public/assets/images/' . $package->images->first()->image) }}" alt="{{ $package->name }}">
                    @else
                        <div class="pkg-card-placeholder"><i class="bi bi-image"></i></div>
                    @endif
                    <div class="pkg-card-badges">
                        <span class="tpk-badge js-status-badge {{ $package->status == '1' ? 'tpk-badge-active' : 'tpk-badge-inactive' }}" data-id="{{ $package->id }}">{{ $package->status == '1' ? 'Active' : 'Inactive' }}</span>
                        <span class="tpk-badge tpk-badge-type js-featured-badge" data-id="{{ $package->id }}" style="{{ $package->featured == '1' ? '' : 'display:none;' }}"><i class="bi bi-star-fill"></i> Featured</span>
                    </div>
                </div>
                <div class="pkg-card-body">
                    <h3 class="pkg-card-title">{{ $package->name }}</h3>
                    @if($package->location)
                        <div class="pkg-card-loc"><i class="bi bi-geo-alt"></i> {{ $package->location->city }}, {{ $package->location->country }}</div>
                    @endif
                    <div class="pkg-card-meta">
                        <span><i class="bi bi-clock"></i> {{ $package->duration }}</span>
                        <span class="tpk-badge tpk-badge-type">{{ ucfirst($package->packege_type) }}</span>
                    </div>
                    <div class="pkg-card-price">{{ $package->currceny }} {{ number_format($package->price) }}</div>
                </div>
                <div class="pkg-card-footer">
                    <a href="{{ route('admin.tours.packages.edit', $package->id) }}" class="tpk-action-btn tpk-action-edit" title="Edit"><i class="bi bi-pencil"></i></a>
                    <button type="button" class="tpk-action-btn has-label js-toggle-featured {{ $package->featured == '1' ? 'tpk-action-featured-on' : 'tpk-action-featured-off' }}"
                            data-id="{{ $package->id }}" data-featured="{{ $package->featured == '1' ? '1' : '0' }}">
                        <i class="bi bi-star{{ $package->featured == '1' ? '-fill' : '' }}"></i>
                    </button>
                    <button type="button" class="tpk-action-btn has-label js-toggle-status {{ $package->status == '1' ? 'tpk-action-pause' : 'tpk-action-activate' }}"
                            data-id="{{ $package->id }}" data-status="{{ $package->status == '1' ? '1' : '0' }}">
                        <i class="bi bi-{{ $package->status == '1' ? 'pause-circle' : 'play-circle' }}"></i>
                        <span>{{ $package->status == '1' ? 'Deactivate' : 'Activate' }}</span>
                    </button>
                    <form action="{{ route('admin.tours.packages.destroy', $package->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this package?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="tpk-action-btn tpk-action-delete" title="Delete"><i class="bi bi-trash"></i></button>
                    </form>
                </div>
            </div>
        @empty
            <div class="tpk-empty">
                <div class="tpk-empty-icon"><i class="bi bi-box-seam"></i></div>
                <h5>No packages found</h5>
                <p>Try adjusting your filters or add a new package.</p>
            </div>
        @endforelse
    </div>
    @if($packages->hasPages())
        <div class="tpk-pagination" id="tpkGridPagination" style="display:none;">{{ $packages->withQueryString()->links() }}</div>
    @endif

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const VIEW_KEY = 'tours_packages_view';
    const listCard = document.querySelector('.tpk-table-card');
    const gridView = document.getElementById('tpkGridView');
    const gridPagination = document.getElementById('tpkGridPagination');
    const switchBtns = document.querySelectorAll('#tpkViewSwitch button');

    function setView(view) {
        switchBtns.forEach(b => b.classList.toggle('active', b.dataset.view === view));
        if (view === 'grid') {
            listCard.style.display = 'none';
            gridView.style.display = 'grid';
            if (gridPagination) gridPagination.style.display = 'block';
        } else {
            listCard.style.display = '';
            gridView.style.display = 'none';
            if (gridPagination) gridPagination.style.display = 'none';
        }
        localStorage.setItem(VIEW_KEY, view);
    }

    switchBtns.forEach(btn => btn.addEventListener('click', () => setView(btn.dataset.view)));
    setView(localStorage.getItem(VIEW_KEY) || 'list');

    function csrfToken() {
        return document.querySelector('meta[name="csrf-token"]').content;
    }

    function toast(message) {
        const el = document.createElement('div');
        el.className = 'alert alert-success alert-dismissible fade show position-fixed top-0 end-0 m-3';
        el.style.zIndex = '9999';
        el.innerHTML = `${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
        document.body.appendChild(el);
        setTimeout(() => el.remove(), 3000);
    }

    document.querySelectorAll('.js-toggle-status').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;
            const url = "{{ route('admin.tours.packages.toggle-status', ['tour' => '__ID__']) }}".replace('__ID__', id);
            fetch(url, { method: 'PATCH', headers: { 'X-CSRF-TOKEN': csrfToken(), 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.json())
                .then(data => {
                    if (!data.success) return;
                    const nowActive = this.dataset.status !== '1';
                    document.querySelectorAll(`.js-toggle-status[data-id="${id}"]`).forEach(b => {
                        b.dataset.status = nowActive ? '1' : '0';
                        b.classList.toggle('tpk-action-pause', nowActive);
                        b.classList.toggle('tpk-action-activate', !nowActive);
                        const icon = b.querySelector('i');
                        if (icon) icon.className = 'bi bi-' + (nowActive ? 'pause-circle' : 'play-circle');
                        const label = b.querySelector('span');
                        if (label) label.textContent = nowActive ? 'Deactivate' : 'Activate';
                    });
                    document.querySelectorAll(`.js-status-badge[data-id="${id}"]`).forEach(badge => {
                        badge.classList.toggle('tpk-badge-active', nowActive);
                        badge.classList.toggle('tpk-badge-inactive', !nowActive);
                        badge.textContent = nowActive ? 'Active' : 'Inactive';
                    });
                    toast(data.message || 'Status updated');
                })
                .catch(() => toast('Something went wrong'));
        });
    });

    document.querySelectorAll('.js-toggle-featured').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;
            const url = "{{ route('admin.tours.packages.toggle-featured', ['tour' => '__ID__']) }}".replace('__ID__', id);
            fetch(url, { method: 'PATCH', headers: { 'X-CSRF-TOKEN': csrfToken(), 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.json())
                .then(data => {
                    if (!data.success) return;
                    const nowFeatured = this.dataset.featured !== '1';
                    document.querySelectorAll(`.js-toggle-featured[data-id="${id}"]`).forEach(b => {
                        b.dataset.featured = nowFeatured ? '1' : '0';
                        b.classList.toggle('tpk-action-featured-on', nowFeatured);
                        b.classList.toggle('tpk-action-featured-off', !nowFeatured);
                        const icon = b.querySelector('i');
                        if (icon) icon.className = 'bi bi-star' + (nowFeatured ? '-fill' : '');
                        const label = b.querySelector('span');
                        if (label) label.textContent = nowFeatured ? 'Featured' : 'Not featured';
                    });
                    document.querySelectorAll(`.js-featured-badge[data-id="${id}"]`).forEach(badge => {
                        badge.style.display = nowFeatured ? '' : 'none';
                    });
                    toast(data.message || 'Featured status updated');
                })
                .catch(() => toast('Something went wrong'));
        });
    });
});
</script>
@endpush
