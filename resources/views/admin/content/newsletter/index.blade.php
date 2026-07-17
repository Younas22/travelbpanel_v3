@extends('admin.layouts.app')

@section('title', 'Newsletter Subscribers')

@section('content')
<div class="content-area p-4">
    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h2 class="mb-1">Newsletter Subscribers</h2>
                <p class="text-muted mb-0">Manage your email subscribers and mailing lists</p>
            </div>
            <div class="col-md-6">
                <div class="text-end">
                    <button class="btn btn-outline-primary modern-btn me-2" data-bs-toggle="modal" data-bs-target="#bulkImportModal">
                        <i class="bi bi-upload"></i> Bulk Import
                    </button>
                    <button class="btn btn-primary modern-btn" data-bs-toggle="modal" data-bs-target="#addSubscriberModal">
                        <i class="bi bi-person-plus"></i> Add Subscriber
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="stats-card">
                <div class="d-flex align-items-center">
                    <div class="stats-icon icon-blue">
                        <i class="bi bi-people"></i>
                    </div>
                    <div class="ms-3">
                        <div class="small text-muted">Total Subscribers</div>
                        <div class="h4 mb-0">{{ number_format($stats['total_subscribers']) }}</div>
                        <div class="small text-success">
                            <i class="bi bi-arrow-up"></i> {{ $stats['new_this_week'] }} this week
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="stats-card">
                <div class="d-flex align-items-center">
                    <div class="stats-icon icon-green">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div class="ms-3">
                        <div class="small text-muted">Active Subscribers</div>
                        <div class="h4 mb-0">{{ number_format($stats['active_subscribers']) }}</div>
                        <div class="small text-success">
                            <i class="bi bi-arrow-up"></i> 
                            @if($stats['total_subscribers'] > 0)
                                {{ round(($stats['active_subscribers']/$stats['total_subscribers'])*100, 1) }}% active rate
                            @else
                                0% active rate
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="stats-card">
                <div class="d-flex align-items-center">
                    <div class="stats-icon icon-orange">
                        <i class="bi bi-person-plus"></i>
                    </div>
                    <div class="ms-3">
                        <div class="small text-muted">New This Month</div>
                        <div class="h4 mb-0">{{ number_format($stats['new_this_month']) }}</div>
                        <div class="small text-success">
                            <i class="bi bi-arrow-up"></i> {{ $stats['growth_percentage'] }}% growth
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="stats-card">
                <div class="d-flex align-items-center">
                    <div class="stats-icon icon-purple">
                        <i class="bi bi-person-x"></i>
                    </div>
                    <div class="ms-3">
                        <div class="small text-muted">Unsubscribed</div>
                        <div class="h4 mb-0">{{ number_format($stats['unsubscribed']) }}</div>
                        <div class="small text-warning">
                            <i class="bi bi-arrow-down"></i> 
                            @if($stats['total_subscribers'] > 0)
                                {{ round(($stats['unsubscribed']/$stats['total_subscribers'])*100, 1) }}% unsubscribe rate
                            @else
                                0% unsubscribe rate
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Filters -->
        <div class="col-12">
            <div class="filter-card">
                <form method="GET" action="{{ route('admin.content.newsletter.subscribers') }}">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label">Search Subscribers</label>
                            <div class="search-container">
                                <i class="bi bi-search"></i>
                                <input type="text" name="search" class="form-control search-input" 
                                       placeholder="Search by email..." 
                                       value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="unsubscribed" {{ request('status') == 'unsubscribed' ? 'selected' : '' }}>Unsubscribed</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Join Date</label>
                            <select name="join_date" class="form-select">
                                <option value="">All Time</option>
                                <option value="today" {{ request('join_date') == 'today' ? 'selected' : '' }}>Today</option>
                                <option value="week" {{ request('join_date') == 'week' ? 'selected' : '' }}>This Week</option>
                                <option value="month" {{ request('join_date') == 'month' ? 'selected' : '' }}>This Month</option>
                                <option value="year" {{ request('join_date') == 'year' ? 'selected' : '' }}>This Year</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary modern-btn">
                                    <i class="bi bi-funnel"></i> Filter
                                </button>
                                <a href="{{ route('admin.content.newsletter.subscribers') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </a>
                                <button type="button" class="btn btn-warning modern-btn" onclick="toggleBulkActions()">
                                    <i class="bi bi-gear"></i> Bulk Actions
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Bulk Actions Bar (Hidden by default) -->
        <div class="col-12" id="bulkActionsBar" style="display: none;">
            <div class="alert alert-info d-flex justify-content-between align-items-center">
                <div>
                    <strong><span id="selectedCount">0</span></strong> subscribers selected
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-success btn-sm" onclick="bulkAction('activate')">
                        <i class="bi bi-check-circle"></i> Activate
                    </button>
                    <button class="btn btn-warning btn-sm" onclick="bulkAction('deactivate')">
                        <i class="bi bi-pause-circle"></i> Deactivate
                    </button>
                    <button class="btn btn-secondary btn-sm" onclick="bulkAction('unsubscribe')">
                        <i class="bi bi-person-x"></i> Unsubscribe
                    </button>
                    <button class="btn btn-danger btn-sm" onclick="bulkAction('delete')">
                        <i class="bi bi-trash"></i> Delete
                    </button>
                    <button class="btn btn-outline-secondary btn-sm" onclick="clearSelection()">
                        Cancel
                    </button>
                </div>
            </div>
        </div>

        <!-- Subscribers Table -->
        <div class="col-xl-12">
            <div class="subscribers-table">
                <div class="table-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Subscribers List ({{ $subscribers->total() }})</h5>
                        <div class="d-flex gap-2 d-none">
                            <div class="dropdown export-dropdown">
                                <button class="btn btn-outline-primary modern-btn dropdown-toggle" data-bs-toggle="dropdown">
                                    <i class="bi bi-download"></i> Export
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#"><i class="bi bi-file-earmark-excel me-2"></i>Export as Excel</a></li>
                                    <li><a class="dropdown-item" href="#"><i class="bi bi-file-earmark-text me-2"></i>Export as CSV</a></li>
                                    <li><a class="dropdown-item" href="#"><i class="bi bi-file-earmark-pdf me-2"></i>Export as PDF</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>
                                    <input type="checkbox" class="form-check-input" id="selectAll" onchange="toggleSelectAll()">
                                </th>
                                <th>Email Address</th>
                                <th>Status</th>
                                <th>Joined Date</th>
                                <th>Time Since</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($subscribers as $subscriber)
                            <tr>
                                <td>
                                    <input type="checkbox" class="form-check-input subscriber-checkbox" 
                                           value="{{ $subscriber->id }}" onchange="updateSelection()">
                                </td>
                                <td>
                                    <div class="subscriber-profile">
                                        <div class="subscriber-avatar" style="background: {{ ['#28a745', '#dc3545', '#6f42c1', '#fd7e14', '#20c997'][array_rand(['#28a745', '#dc3545', '#6f42c1', '#fd7e14', '#20c997'])] }};">
                                            {{ strtoupper(substr($subscriber->email, 0, 2)) }}
                                        </div>
                                        <div class="subscriber-details">
                                            <div class="subscriber-email">{{ $subscriber->email }}</div>
                                            <div class="small text-muted">ID: {{ $subscriber->id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge-status status-{{ $subscriber->status }}">
                                        {{ ucfirst($subscriber->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="subscription-date">{{ $subscriber->joined_date_formatted }}</div>
                                </td>
                                <td>
                                    <div class="small text-muted">{{ $subscriber->joined_time_ago }}</div>
                                </td>
                                <td>
                                    <div class="action-buttons d-flex gap-1">
                                        <!-- View Button -->
                                        <button class="btn btn-outline-primary btn-sm" 
                                                title="View Details"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#viewSubscriberModal"
                                                onclick="loadSubscriberData({{ $subscriber->id }}, 'view')">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        
                                        <!-- Edit Button -->
                                        <button class="btn btn-outline-secondary btn-sm" 
                                                title="Edit"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editSubscriberModal"
                                                onclick="loadSubscriberData({{ $subscriber->id }}, 'edit')">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        
                                        <!-- Status-specific actions -->
                                        @if($subscriber->status == 'active')
                                            <button class="btn btn-outline-warning btn-sm" 
                                                    title="Unsubscribe"
                                                    onclick="changeSubscriberStatus({{ $subscriber->id }}, 'unsubscribe')">
                                                <i class="bi bi-person-x"></i>
                                            </button>
                                        @elseif($subscriber->status == 'inactive')
                                            <button class="btn btn-outline-success btn-sm" 
                                                    title="Activate"
                                                    onclick="changeSubscriberStatus({{ $subscriber->id }}, 'activate')">
                                                <i class="bi bi-person-check"></i>
                                            </button>
                                        @else
                                            <button class="btn btn-outline-success btn-sm" 
                                                    title="Resubscribe"
                                                    onclick="changeSubscriberStatus({{ $subscriber->id }}, 'subscribe')">
                                                <i class="bi bi-person-check"></i>
                                            </button>
                                        @endif
                                        
                                        <!-- Delete Button -->
                                        <button class="btn btn-outline-danger btn-sm" 
                                                title="Delete"
                                                onclick="deleteSubscriber({{ $subscriber->id }})">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-people fs-1"></i>
                                        <p class="mt-2">No subscribers found</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="pagination-container">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted">
                            Showing {{ $subscribers->firstItem() }} to {{ $subscribers->lastItem() }} of {{ $subscribers->total() }} entries
                        </div>
                        <nav>
                            {{ $subscribers->withQueryString()->links('pagination::bootstrap-5') }}
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Subscriber Modal -->

<div class="modal fade" id="addSubscriberModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-person-plus me-2"></i>Add New Subscriber
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addSubscriberForm" method="POST" action="{{ route('admin.content.newsletter.store-subscriber') }}">

                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Email Address <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" name="email" placeholder="john.doe@email.com" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="active" selected>Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="unsubscribed">Unsubscribed</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Join Date</label>
                        <input type="date" class="form-control" name="joined_date" value="{{ date('Y-m-d') }}">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary modern-btn" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary modern-btn">
                        <i class="bi bi-check-lg"></i> Add Subscriber
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Subscriber Modal -->
<div class="modal fade" id="editSubscriberModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-pencil-square me-2"></i>Edit Subscriber: <span id="editSubscriberEmail"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editSubscriberForm" method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" id="editSubscriberId" name="subscriber_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Email Address <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="editEmailInput" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" id="editStatusInput" name="status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="unsubscribed">Unsubscribed</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Join Date</label>
                        <input type="date" class="form-control" id="editJoinedDateInput" name="joined_date">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary modern-btn" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="editSubscriberForm" class="btn btn-primary modern-btn">
                    <i class="bi bi-check-lg"></i> Update Subscriber
                </button>
            </div>
        </div>
    </div>
</div>

<!-- View Subscriber Modal -->
<div class="modal fade" id="viewSubscriberModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-eye me-2"></i>Subscriber Details: <span id="viewSubscriberEmail"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Email Address</label>
                            <div class="form-control-plaintext" id="viewEmail"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Status</label>
                            <div class="form-control-plaintext" id="viewStatus"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Joined Date</label>
                            <div class="form-control-plaintext" id="viewJoinedDate"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Time Since Join</label>
                            <div class="form-control-plaintext" id="viewTimeSince"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Created At</label>
                            <div class="form-control-plaintext" id="viewCreatedAt"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Last Updated</label>
                            <div class="form-control-plaintext" id="viewUpdatedAt"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary modern-btn" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary modern-btn" onclick="openEditFromView()">
                    <i class="bi bi-pencil"></i> Edit Subscriber
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Import Modal -->
<div class="modal fade" id="bulkImportModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-upload me-2"></i>Bulk Import Subscribers
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="bulkImportForm" method="POST" action="{{ route('admin.content.newsletter.bulk-import') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Upload CSV File</label>
                        <input type="file" class="form-control" name="csv_file" accept=".csv" required>
                        <div class="form-text">Upload a CSV file with columns: email, status, joined_date</div>
                    </div>
                    
                    <div class="mb-3">
                        <h6>CSV Format Example:</h6>
                        <pre class="bg-light p-3 rounded"><code>email,status,joined_date
john@email.com,active,2025-01-15
sarah@email.com,active,2025-01-16</code></pre>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="skipDuplicates" name="skip_duplicates" checked>
                            <label class="form-check-label" for="skipDuplicates">
                                Skip duplicate email addresses
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary modern-btn" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="bulkImportForm" class="btn btn-primary modern-btn">
                    <i class="bi bi-upload"></i> Import Subscribers
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Global function for loading subscriber data
function loadSubscriberData(subscriberId, mode) {
    if (mode === 'view') {
        document.getElementById('viewSubscriberEmail').textContent = 'Loading...';
    } else {
        document.getElementById('editSubscriberEmail').textContent = 'Loading...';
    }
    
    fetch(`{{ url('admin/content/newsletter/subscribers') }}/${subscriberId}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (mode === 'view') {
                populateViewModal(data.subscriber);
            } else {
                populateEditModal(data.subscriber);
            }
        } else {
            alert('Error loading subscriber details');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error loading subscriber details');
    });
}

function populateViewModal(subscriber) {
    document.getElementById('viewSubscriberEmail').textContent = subscriber.email;
    document.getElementById('viewEmail').textContent = subscriber.email;
    document.getElementById('viewStatus').innerHTML = `<span class="badge-status status-${subscriber.status}">${subscriber.status}</span>`;
    document.getElementById('viewJoinedDate').textContent = subscriber.joined_date || 'N/A';
    document.getElementById('viewTimeSince').textContent = subscriber.joined_time_ago || 'N/A';
    document.getElementById('viewCreatedAt').textContent = subscriber.created_at || 'N/A';
    document.getElementById('viewUpdatedAt').textContent = subscriber.updated_at || 'N/A';
}

function populateEditModal(subscriber) {
    document.getElementById('editSubscriberEmail').textContent = subscriber.email;
    document.getElementById('editSubscriberId').value = subscriber.id;
    document.getElementById('editEmailInput').value = subscriber.email;
    document.getElementById('editStatusInput').value = subscriber.status;
    document.getElementById('editJoinedDateInput').value = subscriber.joined_date ? subscriber.joined_date.split(' ')[0] : '';
    
    // Update form action
    document.getElementById('editSubscriberForm').action = `{{ url('admin/content/newsletter/subscribers') }}/${subscriber.id}`;
}

function openEditFromView() {
    const subscriberEmail = document.getElementById('viewSubscriberEmail').textContent;
    const subscriberId = document.getElementById('viewSubscriberEmail').dataset.subscriberId;
    
    if (subscriberId) {
        const viewModal = bootstrap.Modal.getInstance(document.getElementById('viewSubscriberModal'));
        viewModal.hide();
        
        setTimeout(() => {
            loadSubscriberData(subscriberId, 'edit');
            const editModal = new bootstrap.Modal(document.getElementById('editSubscriberModal'));
            editModal.show();
        }, 300);
    }
}

function changeSubscriberStatus(subscriberId, action) {
    if (confirm(`Are you sure you want to ${action} this subscriber?`)) {
        fetch(`{{ url('admin/content/newsletter/subscribers') }}/${subscriberId}/${action}`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred');
        });
    }
}

function deleteSubscriber(subscriberId) {
    if (confirm('Are you sure you want to delete this subscriber? This action cannot be undone.')) {
        fetch(`{{ url('admin/content/newsletter/subscribers') }}/${subscriberId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Subscriber deleted successfully');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred');
        });
    }
}

// Bulk operations
function toggleBulkActions() {
    const bulkBar = document.getElementById('bulkActionsBar');
    if (bulkBar.style.display === 'none') {
        bulkBar.style.display = 'block';
    } else {
        bulkBar.style.display = 'none';
        clearSelection();
    }
}

function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.subscriber-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
    
    updateSelection();
}

function updateSelection() {
    const checkboxes = document.querySelectorAll('.subscriber-checkbox:checked');
    document.getElementById('selectedCount').textContent = checkboxes.length;
    
    if (checkboxes.length > 0) {
        document.getElementById('bulkActionsBar').style.display = 'block';
    } else {
        document.getElementById('bulkActionsBar').style.display = 'none';
    }
}

function clearSelection() {
    const checkboxes = document.querySelectorAll('.subscriber-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
    document.getElementById('selectAll').checked = false;
    document.getElementById('bulkActionsBar').style.display = 'none';
}

function bulkAction(action) {
    const checkboxes = document.querySelectorAll('.subscriber-checkbox:checked');
    if (checkboxes.length === 0) {
        alert('Please select subscribers first');
        return;
    }
    
    const subscriberIds = Array.from(checkboxes).map(cb => cb.value);
    
    if (confirm(`Are you sure you want to ${action} ${subscriberIds.length} subscriber(s)?`)) {
        fetch(`{{ url('admin/content/newsletter/subscribers/bulk') }}/${action}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                subscriber_ids: subscriberIds
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred');
        });
    }
}

// Form submissions
document.getElementById('editSubscriberForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const subscriberId = document.getElementById('editSubscriberId').value;
    
    fetch(`{{ url('admin/content/newsletter/subscribers') }}/${subscriberId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('editSubscriberModal'));
            modal.hide();
            alert('Subscriber updated successfully!');
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating the subscriber');
    });
});
</script>

@endsection

