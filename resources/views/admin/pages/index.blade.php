{{-- resources/views/admin/pages/index.blade.php --}}

@extends('admin.layouts.app')

@section('title', 'Pages Management')

@section('content')
<div class="content-area p-4">
    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h2 class="mb-1">Pages Management</h2>
                <p class="text-muted mb-0">Manage your website pages and content</p>
            </div>
            <div class="col-md-6">
                <div class="text-end">
                    <button class="btn btn-outline-danger modern-btn me-2" onclick="bulkAction('delete')" id="bulkDeleteBtn" style="display: none;">
                        <i class="bi bi-trash"></i> Delete Selected
                    </button>
                    <a href="{{ route('admin.pages.create') }}" class="btn btn-primary modern-btn">
                        <i class="bi bi-plus-circle"></i> Add New Page
                    </a>
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
                        <i class="bi bi-file-earmark-text"></i>
                    </div>
                    <div class="ms-3">
                        <div class="small text-muted">Total Pages</div>
                        <div class="h4 mb-0">{{ number_format($stats['total']) }}</div>
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
                        <div class="small text-muted">Published</div>
                        <div class="h4 mb-0">{{ number_format($stats['published']) }}</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="stats-card">
                <div class="d-flex align-items-center">
                    <div class="stats-icon icon-orange">
                        <i class="bi bi-file-earmark"></i>
                    </div>
                    <div class="ms-3">
                        <div class="small text-muted">Draft</div>
                        <div class="h4 mb-0">{{ number_format($stats['draft']) }}</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="stats-card">
                <div class="d-flex align-items-center">
                    <div class="stats-icon icon-purple">
                        <i class="bi bi-lock"></i>
                    </div>
                    <div class="ms-3">
                        <div class="small text-muted">Private</div>
                        <div class="h4 mb-0">{{ number_format($stats['private']) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="filter-card mb-4">
        <form method="GET" action="{{ route('admin.pages.index') }}">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Search Pages</label>
                    <div class="search-container">
                        <i class="bi bi-search"></i>
                        <input type="text" name="search" class="form-control search-input" 
                               placeholder="Search by name, slug, or content..." 
                               value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="private" {{ request('status') == 'private' ? 'selected' : '' }}>Private</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Order By</label>
                    <select name="order_by" class="form-select">
                        <option value="sort_order" {{ request('order_by') == 'sort_order' ? 'selected' : '' }}>Sort Order</option>
                        <option value="name" {{ request('order_by') == 'name' ? 'selected' : '' }}>Name</option>
                        <option value="created_at" {{ request('order_by') == 'created_at' ? 'selected' : '' }}>Created Date</option>
                        <option value="updated_at" {{ request('order_by') == 'updated_at' ? 'selected' : '' }}>Updated Date</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary modern-btn">
                            <i class="bi bi-funnel"></i> Filter
                        </button>
                        <a href="{{ route('admin.pages.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-clockwise"></i>
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Bulk Actions Bar -->
    <div class="alert alert-info d-flex justify-content-between align-items-center" id="bulkActionsBar" style="display: none;">
        <div>
            <strong><span id="selectedCount">0</span></strong> pages selected
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-success btn-sm" onclick="bulkAction('publish')">
                <i class="bi bi-check-circle"></i> Publish
            </button>
            <button class="btn btn-warning btn-sm" onclick="bulkAction('unpublish')">
                <i class="bi bi-pause-circle"></i> Unpublish
            </button>
            <button class="btn btn-secondary btn-sm" onclick="bulkAction('private')">
                <i class="bi bi-lock"></i> Make Private
            </button>
            <button class="btn btn-danger btn-sm" onclick="bulkAction('delete')">
                <i class="bi bi-trash"></i> Delete
            </button>
            <button class="btn btn-outline-secondary btn-sm" onclick="clearSelection()">
                Cancel
            </button>
        </div>
    </div>

    <!-- Pages Table -->
    <div class="pages-table">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>
                            <input type="checkbox" class="form-check-input" id="selectAll" onchange="toggleSelectAll()">
                        </th>
                        <th>Page Details</th>
                        <th>Status</th>
                        <th>Menu</th>
                        <th>Modified</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pages as $page)
                    <tr>
                        <td>
                            <input type="checkbox" class="form-check-input page-checkbox" 
                                   value="{{ $page->id }}" onchange="updateSelection()">
                        </td>
                        <td>
                            <div class="page-info">
                                <div class="d-flex align-items-center">
                                    <div class="page-details">
                                        <h6 class="mb-1">
                                            {{ $page->name }}
                                            @if($page->is_homepage)
                                                <span class="badge bg-info ms-1">Homepage</span>
                                            @endif
                                        </h6>
                                        <div class="small text-muted">
                                            <i class="bi bi-link me-1"></i>
                                            <code>/{{ $page->slug }}</code>
                                        </div>
                                        @if($page->content)
                                            <div class="small text-muted mt-1">
                                                {{ $page->excerpt }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-{{ $page->status_badge }}">
                                {{ ucfirst($page->status) }}
                            </span>
                            @if($page->published_at)
                                <div class="small text-muted">
                                    {{ $page->published_at->format('M j, Y') }}
                                </div>
                            @endif
                        </td>
                        <td>
                            @if($page->show_in_menu)
                                <span class="badge bg-success">
                                    <i class="bi bi-check"></i> Yes
                                </span>
                            @else
                                <span class="badge bg-secondary">
                                    <i class="bi bi-x"></i> No
                                </span>
                            @endif
                            <div class="small text-muted">
                                Order: {{ $page->sort_order }}
                            </div>
                        </td>
                        <td>
                            <div class="small">
                                <div><strong>Updated:</strong> {{ $page->updated_at->format('M j, Y') }}</div>
                                <div class="text-muted">{{ $page->updated_at->diffForHumans() }}</div>
                            </div>
                        </td>
                        <td>
                            <div class="action-buttons d-flex gap-1">
                                <!-- View Button -->
                                @if($page->status === 'published')
                                    <a href="{{ $page->url }}" target="_blank" class="btn btn-outline-info btn-sm" title="View Page">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                @endif
                                
                                <!-- Edit Button -->
                                <a href="{{ route('admin.pages.edit', $page) }}" class="btn btn-outline-primary btn-sm" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                
                                <!-- Duplicate Button -->
                                <form method="POST" action="{{ route('admin.pages.duplicate', $page) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-secondary btn-sm" title="Duplicate">
                                        <i class="bi bi-files"></i>
                                    </button>
                                </form>
                                
                                <!-- Status Toggle -->
                                @if($page->status === 'published')
                                    <button class="btn btn-outline-warning btn-sm" title="Unpublish"
                                            onclick="changePageStatus({{ $page->id }}, 'unpublish')">
                                        <i class="bi bi-pause-circle"></i>
                                    </button>
                                @else
                                    <button class="btn btn-outline-success btn-sm" title="Publish"
                                            onclick="changePageStatus({{ $page->id }}, 'publish')">
                                        <i class="bi bi-play-circle"></i>
                                    </button>
                                @endif
                                
                                <!-- Delete Button -->
                                @unless($page->is_homepage)
                                    <button class="btn btn-outline-danger btn-sm" title="Delete"
                                            onclick="deletePage({{ $page->id }})">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                @endunless
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4">
                            <div class="text-muted">
                                <i class="bi bi-file-earmark-text fs-1"></i>
                                <p class="mt-2">No pages found</p>
                                <a href="{{ route('admin.pages.create') }}" class="btn btn-primary">
                                    <i class="bi bi-plus-circle"></i> Create First Page
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($pages->hasPages())
            <div class="pagination-container">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted">
                        Showing {{ $pages->firstItem() }} to {{ $pages->lastItem() }} of {{ $pages->total() }} entries
                    </div>
                    <nav>
                        {{ $pages->withQueryString()->links('pagination::bootstrap-5') }}
                    </nav>
                </div>
            </div>
        @endif
    </div>
</div>

<script>
// Bulk selection functionality
function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.page-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
    
    updateSelection();
}

function updateSelection() {
    const checkboxes = document.querySelectorAll('.page-checkbox:checked');
    const count = checkboxes.length;
    
    document.getElementById('selectedCount').textContent = count;
    document.getElementById('bulkActionsBar').style.display = count > 0 ? 'flex' : 'none';
    document.getElementById('bulkDeleteBtn').style.display = count > 0 ? 'inline-block' : 'none';
}

function clearSelection() {
    const checkboxes = document.querySelectorAll('.page-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
    document.getElementById('selectAll').checked = false;
    updateSelection();
}

// Bulk actions
function bulkAction(action) {
    const checkboxes = document.querySelectorAll('.page-checkbox:checked');
    if (checkboxes.length === 0) {
        alert('Please select pages first');
        return;
    }
    
    const pageIds = Array.from(checkboxes).map(cb => cb.value);
    
    if (confirm(`Are you sure you want to ${action} ${pageIds.length} page(s)?`)) {
        fetch('{{ route("admin.pages.bulk-action") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                action: action,
                page_ids: pageIds
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                location.reload();
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred', 'error');
        });
    }
}

// Individual page actions
function changePageStatus(pageId, action) {
    if (confirm(`Are you sure you want to ${action} this page?`)) {
        fetch('{{ route("admin.pages.bulk-action") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                action: action,
                page_ids: [pageId]
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                location.reload();
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred', 'error');
        });
    }
}

function deletePage(pageId) {
    if (confirm('Are you sure you want to delete this page? This action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `{{ url('admin/pages') }}/${pageId}`;
        form.innerHTML = `
            @csrf
            @method('DELETE')
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Notification helper
function showNotification(message, type = 'success') {
    // You can customize this based on your notification system
    alert(message);
}
</script>
@endsection