@extends('admin-modern.layouts.app')

@section('title', 'Hotels')

@section('content')

    <div class="tpk-header">
        <div>
            <h2 class="tpk-title">Hotels</h2>
            <p class="tpk-subtitle">Manage all hotels</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <div class="view-switch" id="htlViewSwitch">
                <button type="button" data-view="list" class="active"><i class="bi bi-list-ul"></i> List</button>
                <button type="button" data-view="grid"><i class="bi bi-grid-3x3-gap"></i> Grid</button>
            </div>
            <a href="{{ route('admin.hotels.create') }}" class="tpk-add-btn">
                <i class="bi bi-plus-circle"></i> Add New Hotel
            </a>
        </div>
    </div>

    <div class="tpk-stats">
        <div class="tpk-stat">
            <div class="tpk-stat-icon tpk-icon-accent"><i class="bi bi-building"></i></div>
            <div><div class="tpk-stat-value">{{ $stats['total'] }}</div><div class="tpk-stat-label">Total hotels</div></div>
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
            <div class="tpk-stat-icon tpk-icon-amber"><i class="bi bi-building-fill"></i></div>
            <div><div class="tpk-stat-value">{{ $stats['hotel'] }}</div><div class="tpk-stat-label">Hotels (type)</div></div>
        </div>
    </div>

    <div class="tpk-filter-card">
        <form action="{{ route('admin.hotels.index') }}" method="GET" class="tpk-filter-grid">
            <div class="tpk-filter-search">
                <i class="bi bi-search"></i>
                <input type="text" name="search" class="form-control" placeholder="Search by name, address or location..." value="{{ $search ?? '' }}">
            </div>
            <select name="status" class="form-select">
                <option value="">All status</option>
                <option value="1" {{ ($status ?? '') == '1' ? 'selected' : '' }}>Active</option>
                <option value="0" {{ ($status ?? '') === '0' ? 'selected' : '' }}>Inactive</option>
            </select>
            <select name="type" class="form-select">
                <option value="">All types</option>
                <option value="hotel" {{ ($type ?? '') == 'hotel' ? 'selected' : '' }}>Hotel</option>
                <option value="guest house" {{ ($type ?? '') == 'guest house' ? 'selected' : '' }}>Guest House</option>
                <option value="resort" {{ ($type ?? '') == 'resort' ? 'selected' : '' }}>Resort</option>
            </select>
            <button type="submit" class="tpk-btn tpk-btn-primary"><i class="bi bi-funnel"></i> Filter</button>
        </form>
    </div>

    <div class="tpk-table-card">
        <div class="table-responsive">
            <table class="tpk-table">
                <thead>
                <tr>
                    <th>Image</th><th>Hotel Name</th><th>Location</th><th>Type</th><th>Total Rooms</th>
                    <th>Contact</th><th>Approval</th><th>Status</th><th>Featured</th><th class="text-end">Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse($hotels as $hotel)
                    <tr>
                        <td>
                            @if($hotel->images->first())
                                <img src="{{ asset('public/assets/images/' . $hotel->images->first()->image_path) }}" alt="" class="tpk-thumb">
                            @else
                                <div class="tpk-thumb-placeholder"><i class="bi bi-building"></i></div>
                            @endif
                        </td>
                        <td>
                            <div class="tpk-pkg-name">{{ $hotel->name }}</div>
                            <div class="tpk-meta">{{ $hotel->address }}</div>
                        </td>
                        <td>
                            @if($hotel->location)
                                <span class="tpk-location"><i class="bi bi-geo-alt"></i> {{ $hotel->location->city }}, {{ $hotel->location->country }}</span>
                            @else
                                <span class="tpk-meta">—</span>
                            @endif
                        </td>
                        <td><span class="tpk-badge tpk-badge-type">{{ ucfirst($hotel->type) }}</span></td>
                        <td><span class="tpk-meta">{{ $hotel->total_rooms ?? 0 }}</span></td>
                        <td>
                            @if($hotel->phone)<div class="tpk-meta"><i class="bi bi-telephone"></i> {{ $hotel->phone }}</div>@endif
                            @if($hotel->email)<div class="tpk-meta"><i class="bi bi-envelope"></i> {{ $hotel->email }}</div>@endif
                        </td>
                        <td>
                            @php $approval = $hotel->approval_status ?? 'approved'; @endphp
                            @if($approval === 'pending')
                                <span class="tpk-badge tpk-badge-pending">Pending</span>
                                <div class="tpk-approve-actions">
                                    <form action="{{ route('admin.hotels.approve', $hotel) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="tpk-micro-btn tpk-micro-approve"><i class="bi bi-check-lg"></i> Approve</button>
                                    </form>
                                    <button type="button" class="tpk-micro-btn tpk-micro-reject reject-btn"
                                            data-hotel-id="{{ $hotel->id }}" data-hotel-name="{{ $hotel->name }}"
                                            data-reject-url="{{ route('admin.hotels.reject', $hotel) }}">
                                        <i class="bi bi-x-lg"></i> Reject
                                    </button>
                                </div>
                            @elseif($approval === 'rejected')
                                <span class="tpk-badge tpk-badge-rejected">Rejected</span>
                            @else
                                <span class="tpk-badge tpk-badge-approved">Approved</span>
                            @endif
                        </td>
                        <td><span class="tpk-badge js-status-badge {{ $hotel->status ? 'tpk-badge-active' : 'tpk-badge-inactive' }}" data-id="{{ $hotel->id }}">{{ $hotel->status ? 'Active' : 'Inactive' }}</span></td>
                        <td>
                            <button type="button" class="tpk-action-btn has-label js-toggle-featured {{ $hotel->featured == '1' ? 'tpk-action-featured-on' : 'tpk-action-featured-off' }}"
                                    data-id="{{ $hotel->id }}" data-featured="{{ $hotel->featured == '1' ? '1' : '0' }}">
                                <i class="bi bi-star{{ $hotel->featured == '1' ? '-fill' : '' }}"></i>
                                <span>{{ $hotel->featured == '1' ? 'Featured' : 'Not featured' }}</span>
                            </button>
                        </td>
                        <td>
                            <div class="tpk-actions">
                                <a href="{{ route('admin.hotels.edit', $hotel) }}" class="tpk-action-btn tpk-action-edit" title="Edit"><i class="bi bi-pencil"></i></a>
                                <button type="button" class="tpk-action-btn has-label js-toggle-status {{ $hotel->status ? 'tpk-action-pause' : 'tpk-action-activate' }}"
                                        data-id="{{ $hotel->id }}" data-status="{{ $hotel->status ? '1' : '0' }}">
                                    <i class="bi bi-{{ $hotel->status ? 'pause-circle' : 'play-circle' }}"></i>
                                    <span>{{ $hotel->status ? 'Deactivate' : 'Activate' }}</span>
                                </button>
                                <button type="button" class="tpk-action-btn tpk-action-delete delete-btn" data-id="{{ $hotel->id }}" title="Delete"><i class="bi bi-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10">
                            <div class="tpk-empty">
                                <div class="tpk-empty-icon"><i class="bi bi-building"></i></div>
                                <h5>No hotels found</h5>
                                <p>Try adjusting your filters or add a new hotel.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($hotels->hasPages())
            <div class="tpk-pagination">{{ $hotels->withQueryString()->links() }}</div>
        @endif
    </div>

    <div class="pkg-grid" id="htlGridView" style="display:none;">
        @forelse($hotels as $hotel)
            <div class="pkg-card">
                <div class="pkg-card-img">
                    @if($hotel->images->first())
                        <img src="{{ asset('public/assets/images/' . $hotel->images->first()->image_path) }}" alt="{{ $hotel->name }}">
                    @else
                        <div class="pkg-card-placeholder"><i class="bi bi-building"></i></div>
                    @endif
                    <div class="pkg-card-badges">
                        <span class="tpk-badge js-status-badge {{ $hotel->status ? 'tpk-badge-active' : 'tpk-badge-inactive' }}" data-id="{{ $hotel->id }}">{{ $hotel->status ? 'Active' : 'Inactive' }}</span>
                        <span class="tpk-badge tpk-badge-type js-featured-badge" data-id="{{ $hotel->id }}" style="{{ $hotel->featured == '1' ? '' : 'display:none;' }}"><i class="bi bi-star-fill"></i> Featured</span>
                    </div>
                </div>
                <div class="pkg-card-body">
                    <h3 class="pkg-card-title">{{ $hotel->name }}</h3>
                    @if($hotel->location)
                        <div class="pkg-card-loc"><i class="bi bi-geo-alt"></i> {{ $hotel->location->city }}, {{ $hotel->location->country }}</div>
                    @endif
                    <div class="pkg-card-meta">
                        <span><i class="bi bi-door-open"></i> {{ $hotel->total_rooms ?? 0 }} rooms</span>
                        <span class="tpk-badge tpk-badge-type">{{ ucfirst($hotel->type) }}</span>
                    </div>
                </div>
                <div class="pkg-card-footer">
                    <a href="{{ route('admin.hotels.edit', $hotel) }}" class="tpk-action-btn tpk-action-edit" title="Edit"><i class="bi bi-pencil"></i></a>
                    <button type="button" class="tpk-action-btn has-label js-toggle-featured {{ $hotel->featured == '1' ? 'tpk-action-featured-on' : 'tpk-action-featured-off' }}"
                            data-id="{{ $hotel->id }}" data-featured="{{ $hotel->featured == '1' ? '1' : '0' }}">
                        <i class="bi bi-star{{ $hotel->featured == '1' ? '-fill' : '' }}"></i>
                    </button>
                    <button type="button" class="tpk-action-btn has-label js-toggle-status {{ $hotel->status ? 'tpk-action-pause' : 'tpk-action-activate' }}"
                            data-id="{{ $hotel->id }}" data-status="{{ $hotel->status ? '1' : '0' }}">
                        <i class="bi bi-{{ $hotel->status ? 'pause-circle' : 'play-circle' }}"></i>
                        <span>{{ $hotel->status ? 'Deactivate' : 'Activate' }}</span>
                    </button>
                    <button type="button" class="tpk-action-btn tpk-action-delete delete-btn" data-id="{{ $hotel->id }}" title="Delete"><i class="bi bi-trash"></i></button>
                </div>
            </div>
        @empty
            <div class="tpk-empty">
                <div class="tpk-empty-icon"><i class="bi bi-building"></i></div>
                <h5>No hotels found</h5>
                <p>Try adjusting your filters or add a new hotel.</p>
            </div>
        @endforelse
    </div>
    @if($hotels->hasPages())
        <div class="tpk-pagination" id="htlGridPagination" style="display:none;">{{ $hotels->withQueryString()->links() }}</div>
    @endif

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
                        <textarea id="rejectReason" name="reason" class="form-control" rows="4" placeholder="Please provide a reason for rejection..." required></textarea>
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
    const VIEW_KEY = 'hotels_view';
    const listCard = document.querySelector('.tpk-table-card');
    const gridView = document.getElementById('htlGridView');
    const gridPagination = document.getElementById('htlGridPagination');
    const switchBtns = document.querySelectorAll('#htlViewSwitch button');

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
            fetch(`{{ url('admin/hotels') }}/${id}/toggle-status`, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken(), 'X-Requested-With': 'XMLHttpRequest' }
            })
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
            fetch(`{{ url('admin/hotels') }}/${id}/toggle-featured`, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken(), 'X-Requested-With': 'XMLHttpRequest' }
            })
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

    document.querySelectorAll('.reject-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('rejectHotelName').textContent = this.dataset.hotelName;
            document.getElementById('rejectReason').value = '';
            document.getElementById('rejectForm').action = this.dataset.rejectUrl;
            new bootstrap.Modal(document.getElementById('rejectModal')).show();
        });
    });

    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const hotelId = this.dataset.id;
            if (confirm('Are you sure you want to delete this hotel? All related data will be removed.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `{{ url('admin/hotels') }}/${hotelId}`;
                form.innerHTML = `
                    <input type="hidden" name="_token" value="${csrfToken()}">
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
