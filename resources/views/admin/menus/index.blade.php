{{-- resources/views/admin/menus/index.blade.php --}}

@extends('admin.layouts.app')

@section('title', 'Menu Management')


<style>
/* Menu-specific modern styles */
.menu-management-wrapper {
    background: var(--bs-secondary-bg);
    min-height: 100vh;
}

/* Modern Menu Item Design */
.menu-item {
    background: var(--bs-body-bg);
    border: 2px solid transparent;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1rem;
    cursor: grab;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    position: relative;
}

.menu-item:hover {
    border-color: var(--primary-color);
    box-shadow: 0 8px 25px rgba(0, 102, 204, 0.15);
    transform: translateY(-3px);
}

.menu-item.dragging {
    opacity: 0.8;
    transform: rotate(2deg) scale(1.02);
    cursor: grabbing;
    box-shadow: 0 15px 35px rgba(0, 102, 204, 0.3);
    z-index: 1000;
}

.menu-item-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
}

.menu-item-info {
    flex: 1;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.menu-item-actions {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

/* Drag Handle */
.drag-handle {
    cursor: grab;
    color: #94a3b8;
    margin-right: 1rem;
    font-size: 1.3rem;
    padding: 0.5rem;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.drag-handle:hover {
    background: var(--bs-secondary-bg);
    color: var(--primary-color);
}

.drag-handle:active {
    cursor: grabbing;
}

/* Menu Item Content */
.menu-content {
    flex: 1;
}

.menu-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--bs-body-color);
    margin-bottom: 0.25rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.menu-url {
    font-size: 0.875rem;
    color: var(--bs-secondary-color);
    font-family: 'Monaco', 'Menlo', monospace;
    background: var(--bs-secondary-bg);
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    display: inline-block;
    margin-top: 0.5rem;
}

.menu-description {
    font-size: 0.85rem;
    color: var(--bs-secondary-color);
    margin-top: 0.5rem;
    line-height: 1.4;
}

/* Badges */
.status-badge {
    padding: 0.3rem 0.8rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-active {
    background: #dcfce7;
    color: #166534;
}

.status-inactive {
    background: #fee2e2;
    color: #991b1b;
}

.children-badge {
    background: #e0e7ff;
    color: #3730a3;
    padding: 0.25rem 0.5rem;
    border-radius: 10px;
    font-size: 0.75rem;
    font-weight: 600;
    margin-left: 0.5rem;
}

/* Child Menu Items */
.child-menu-item {
    margin-left: 2rem;
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid var(--bs-border-color);
    background: var(--bs-secondary-bg);
    border-radius: 8px;
    padding: 1rem;
    position: relative;
}

.child-menu-item::before {
    content: '';
    position: absolute;
    left: -2rem;
    top: 50%;
    width: 1.5rem;
    height: 1px;
    background: var(--bs-border-color);
}

.child-menu-item::after {
    content: '';
    position: absolute;
    left: -2rem;
    top: 0;
    width: 1px;
    height: 50%;
    background: var(--bs-border-color);
}

/* Sortable States */
.sortable-ghost {
    opacity: 0.5;
    background: var(--bs-secondary-bg);
    border-color: var(--bs-border-color);
    transform: scale(0.95);
}

.sortable-chosen {
    background: #fef3c7;
    border-color: #f59e0b;
    box-shadow: 0 8px 25px rgba(245, 158, 11, 0.3);
}

/* Modern Category Tabs */
.category-tabs {
    background: var(--bs-body-bg);
    border-radius: 12px;
    padding: 0.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.category-tab {
    flex: 1;
    padding: 1rem 1.5rem;
    background: transparent;
    border: none;
    border-radius: 8px;
    text-decoration: none;
    color: var(--bs-secondary-color);
    font-weight: 500;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 150px;
}

.category-tab:hover {
    background: var(--bs-secondary-bg);
    color: var(--bs-body-color);
    text-decoration: none;
    transform: translateY(-1px);
}

.category-tab.active {
    background: linear-gradient(135deg, var(--primary-color), #0052a3);
    color: white;
    box-shadow: 0 4px 15px rgba(0, 102, 204, 0.4);
}

.category-tab i {
    margin-right: 0.5rem;
    font-size: 1.1rem;
}

/* Menu Container */
.menu-container {
    background: var(--bs-body-bg);
    border: 1px solid var(--bs-border-color);
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.menu-header {
    background: var(--bs-secondary-bg);
    padding: 1.5rem 2rem;
    border-bottom: 1px solid var(--bs-border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.menu-title-main {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--bs-body-color);
    margin: 0;
}

.menu-actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

/* Bulk Actions Bar */
.bulk-actions-bar {
    background: linear-gradient(135deg, #06b6d4, #0891b2);
    color: white;
    padding: 1rem 2rem;
    display: none;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.bulk-actions-bar.show {
    display: flex;
}

.bulk-actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

/* Menu Items Container */
.menu-items-container {
    padding: 2rem;
    min-height: 400px;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    color: var(--bs-secondary-color);
}

.empty-state i {
    font-size: 4rem;
    color: var(--bs-border-color);
    margin-bottom: 1.5rem;
}

.empty-state h4 {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--bs-body-color);
    margin-bottom: 0.5rem;
}

.empty-state p {
    font-size: 1rem;
    margin-bottom: 2rem;
}

/* Selection Checkbox */
.menu-checkbox {
    width: 18px;
    height: 18px;
    accent-color: var(--primary-color);
    margin-right: 1rem;
}

/* Action Buttons */
.btn-modern-sm {
    padding: 0.375rem 0.75rem;
    border-radius: 6px;
    font-size: 0.875rem;
    font-weight: 500;
    transition: all 0.3s ease;
    border: 1px solid var(--bs-border-color);
    background: var(--bs-body-bg);
    color: var(--bs-body-color);
}

.btn-modern-sm:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.btn-modern-sm.btn-outline-warning:hover {
    background: #f59e0b;
    border-color: #f59e0b;
    color: white;
}

.btn-modern-sm.btn-outline-success:hover {
    background: #22c55e;
    border-color: #22c55e;
    color: white;
}

.btn-modern-sm.btn-outline-primary:hover {
    background: var(--primary-color);
    border-color: var(--primary-color);
    color: white;
}

.btn-modern-sm.btn-outline-danger:hover {
    background: #ef4444;
    border-color: #ef4444;
    color: white;
}

.btn-modern-sm.btn-outline-info:hover {
    background: #06b6d4;
    border-color: #06b6d4;
    color: white;
}

/* Responsive Design */
@media (max-width: 768px) {
    .category-tabs {
        flex-direction: column;
    }
    
    .category-tab {
        min-width: auto;
    }
    
    .menu-header {
        flex-direction: column;
        align-items: stretch;
        text-align: center;
    }
    
    .menu-item-header {
        flex-wrap: wrap;
    }
    
    .menu-item-actions {
        margin-top: 1rem;
        justify-content: center;
    }
    
    .child-menu-item {
        margin-left: 1rem;
    }
    
    .child-menu-item::before,
    .child-menu-item::after {
        left: -1rem;
    }
    
    .menu-items-container {
        padding: 1rem;
    }
}

/* Loading Animation */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.fade-in-up {
    animation: fadeInUp 0.6s ease-out;
}

/* Notification Styles */
.notification-toast {
    position: fixed;
    top: 2rem;
    right: 2rem;
    z-index: 9999;
    min-width: 300px;
    border-radius: 8px;
    padding: 1rem 1.5rem;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    animation: slideInRight 0.3s ease;
}

.notification-success {
    background: #dcfce7;
    border: 1px solid #22c55e;
    color: #166534;
}

.notification-error {
    background: #fee2e2;
    border: 1px solid #ef4444;
    color: #991b1b;
}

@keyframes slideInRight {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideOutRight {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(100%);
        opacity: 0;
    }
}
</style>



@section('content')
<div class="menu-management-wrapper">
    <div class="content-area p-4">
        <!-- Page Header -->
        <div class="page-header fade-in-up">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h2 class="mb-1">
                        <i class="bi bi-menu-app me-2 text-primary"></i>
                        Menu Management
                    </h2>
                    <p class="text-muted mb-0">Organize your website navigation menus with drag & drop</p>
                </div>
                <div class="col-md-6">
                    <div class="text-end">
                        <button class="btn btn-outline-danger modern-btn me-2" onclick="bulkAction('delete')" id="bulkDeleteBtn" style="display: none;">
                            <i class="bi bi-trash"></i> Delete Selected
                        </button>
                        <a href="{{ route('admin.menus.create', ['category' => $category]) }}" class="btn btn-primary modern-btn">
                            <i class="bi bi-plus-circle"></i> Add Menu Item
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row g-4 mb-4 fade-in-up">
            <div class="col-xl-3 col-md-6">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon icon-blue">
                            <i class="bi bi-list"></i>
                        </div>
                        <div class="ms-3">
                            <div class="small text-muted">Total Menu Items</div>
                            <div class="h4 mb-0">{{ $stats['total'] }}</div>
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
                            <div class="small text-muted">Active Items</div>
                            <div class="h4 mb-0">{{ $stats['active'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon icon-orange">
                            <i class="bi bi-menu-app"></i>
                        </div>
                        <div class="ms-3">
                            <div class="small text-muted">Header Menu</div>
                            <div class="h4 mb-0">{{ $stats['header'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon icon-purple">
                            <i class="bi bi-menu-down"></i>
                        </div>
                        <div class="ms-3">
                            <div class="small text-muted">Footer Menu</div>
                            <div class="h4 mb-0">{{ $stats['footer_total'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Category Tabs -->
        <div class="category-tabs fade-in-up">
            @foreach($categories as $key => $label)
                <a href="{{ route('admin.menus.index', ['category' => $key]) }}" 
                   class="category-tab {{ $category === $key ? 'active' : '' }}">
                    @switch($key)
                        @case('header')
                            <i class="bi bi-menu-app"></i>
                            @break
                        @case('footer_quick_links')
                            <i class="bi bi-lightning"></i>
                            @break
                        @case('footer_services')
                            <i class="bi bi-gear"></i>
                            @break
                        @case('footer_support')
                            <i class="bi bi-question-circle"></i>
                            @break
                    @endswitch
                    {{ $label }}
                </a>
            @endforeach
        </div>

        <!-- Menu Container -->
        <div class="menu-container fade-in-up">
            <!-- Menu Header -->
            <div class="menu-header">
                <h3 class="menu-title-main">{{ $categories[$category] }}</h3>
                <div class="menu-actions">
                    <button class="btn btn-sm btn-outline-success modern-btn" onclick="bulkAction('activate')" style="display: none;" id="bulkActivateBtn">
                        <i class="bi bi-check-circle"></i> Activate
                    </button>
                    <button class="btn btn-sm btn-outline-warning modern-btn" onclick="bulkAction('deactivate')" style="display: none;" id="bulkDeactivateBtn">
                        <i class="bi bi-pause-circle"></i> Deactivate
                    </button>
                    <button class="btn btn-sm btn-info modern-btn" onclick="saveOrder()">
                        <i class="bi bi-save"></i> Save Order
                    </button>
                </div>
            </div>

            <!-- Bulk Actions Bar -->
            <div class="bulk-actions-bar" id="bulkActionsBar">
                <div class="d-flex align-items-center gap-3">
                    <i class="bi bi-check-circle-fill"></i>
                    <strong><span id="selectedCount">0</span> items selected</strong>
                </div>
                <div class="bulk-actions">
                    <button class="btn btn-success btn-sm modern-btn" onclick="bulkAction('activate')">
                        <i class="bi bi-check-circle"></i> Activate
                    </button>
                    <button class="btn btn-warning btn-sm modern-btn" onclick="bulkAction('deactivate')">
                        <i class="bi bi-pause-circle"></i> Deactivate
                    </button>
                    <button class="btn btn-danger btn-sm modern-btn" onclick="bulkAction('delete')">
                        <i class="bi bi-trash"></i> Delete
                    </button>
                    <button class="btn btn-outline-secondary btn-sm modern-btn" onclick="clearSelection()">
                        Cancel
                    </button>
                </div>
            </div>

            <!-- Menu Items Container -->
            <div class="menu-items-container">
                <div id="menuItems" class="sortable-container">
                    @forelse($menuItems as $item)
                        <div class="menu-item" data-id="{{ $item->id }}" data-parent-id="{{ $item->parent_id }}">
                            <div class="menu-item-header">
                                <div class="menu-item-info">
                                    <input type="checkbox" class="menu-checkbox" value="{{ $item->id }}" onchange="updateBulkActions()">
                                    <div class="drag-handle">
                                        <i class="bi bi-grip-vertical"></i>
                                    </div>
                                    <div class="menu-content">
                                        <div class="menu-title">
                                            @if($item->icon)
                                                <i class="{{ $item->icon }}"></i>
                                            @endif
                                            {{ $item->name }}
                                            <span class="status-badge {{ $item->is_active ? 'status-active' : 'status-inactive' }}">
                                                {{ $item->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                            @if($item->children->count() > 0)
                                                <span class="children-badge">
                                                    {{ $item->children->count() }} sub-items
                                                </span>
                                            @endif
                                        </div>
                                        @if($item->url)
                                            <div class="menu-url">
                                                <i class="bi bi-link me-1"></i>{{ $item->url }}
                                                @if($item->target === '_blank')
                                                    <i class="bi bi-box-arrow-up-right ms-1"></i>
                                                @endif
                                            </div>
                                        @endif
                                        @if($item->description)
                                            <div class="menu-description">{{ $item->description }}</div>
                                        @endif
                                    </div>
                                </div>
                                <div class="menu-item-actions">
                                    @if($item->url && $item->is_active)
                                        <a href="{{ $item->full_url }}" target="{{ $item->target }}" 
                                           class="btn btn-modern-sm btn-outline-info" title="Preview">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    @endif
                                    
                                    <button class="btn btn-modern-sm {{ $item->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}" 
                                            onclick="toggleStatus({{ $item->id }})" 
                                            data-menu-id="{{ $item->id }}"
                                            title="{{ $item->is_active ? 'Deactivate' : 'Activate' }}">
                                        <i class="bi {{ $item->is_active ? 'bi-pause-circle' : 'bi-play-circle' }}"></i>
                                    </button>
                                    
                                    <a href="{{ route('admin.menus.edit', $item) }}" 
                                       class="btn btn-modern-sm btn-outline-primary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    
                                    <form method="POST" action="{{ route('admin.menus.duplicate', $item) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-modern-sm btn-outline-secondary" title="Duplicate">
                                            <i class="bi bi-files"></i>
                                        </button>
                                    </form>
                                    
                                    <button class="btn btn-modern-sm btn-outline-danger" 
                                            onclick="deleteMenuItem({{ $item->id }})" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                            
                            @if($item->children->count() > 0)
                                @foreach($item->children as $child)
                                    <div class="child-menu-item" data-id="{{ $child->id }}" data-parent-id="{{ $child->parent_id }}">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div class="d-flex align-items-center gap-2">
                                                <input type="checkbox" class="menu-checkbox" value="{{ $child->id }}" onchange="updateBulkActions()">
                                                <div class="drag-handle">
                                                    <i class="bi bi-grip-vertical"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-semibold">
                                                        @if($child->icon)
                                                            <i class="{{ $child->icon }} me-2"></i>
                                                        @endif
                                                        {{ $child->name }}
                                                        <span class="status-badge {{ $child->is_active ? 'status-active' : 'status-inactive' }}">
                                                            {{ $child->is_active ? 'Active' : 'Inactive' }}
                                                        </span>
                                                    </div>
                                                    @if($child->url)
                                                        <div class="menu-url">
                                                            <i class="bi bi-link me-1"></i>{{ $child->url }}
                                                            @if($child->target === '_blank')
                                                                <i class="bi bi-box-arrow-up-right ms-1"></i>
                                                            @endif
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="d-flex gap-1">
                                                @if($child->url && $child->is_active)
                                                    <a href="{{ $child->full_url }}" target="{{ $child->target }}" 
                                                       class="btn btn-modern-sm btn-outline-info">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                @endif
                                                <button class="btn btn-modern-sm {{ $child->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}" 
                                                        onclick="toggleStatus({{ $child->id }})" 
                                                        data-menu-id="{{ $child->id }}">
                                                    <i class="bi {{ $child->is_active ? 'bi-pause-circle' : 'bi-play-circle' }}"></i>
                                                </button>
                                                <a href="{{ route('admin.menus.edit', $child) }}" 
                                                   class="btn btn-modern-sm btn-outline-primary">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <button class="btn btn-modern-sm btn-outline-danger" 
                                                        onclick="deleteMenuItem({{ $child->id }})">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    @empty
                        <div class="empty-state">
                            <i class="bi bi-menu-app"></i>
                            <h4>No menu items found</h4>
                            <p>Start building your {{ strtolower($categories[$category]) }} by adding your first menu item.</p>
                            <a href="{{ route('admin.menus.create', ['category' => $category]) }}" class="btn btn-primary modern-btn">
                                <i class="bi bi-plus-circle"></i> Add First Menu Item
                            </a>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- SortableJS for drag and drop -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

<script>
let sortable;

// Initialize Sortable
document.addEventListener('DOMContentLoaded', function() {
    const menuContainer = document.getElementById('menuItems');
    
    if (menuContainer) {
        sortable = Sortable.create(menuContainer, {
            handle: '.drag-handle',
            animation: 150,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'dragging',
            onStart: function(evt) {
                evt.item.classList.add('dragging');
            },
            onEnd: function(evt) {
                evt.item.classList.remove('dragging');
                updateSortOrder();
            }
        });
    }
});

// Update sort order
function updateSortOrder() {
    const items = [];
    const menuItems = document.querySelectorAll('.menu-item[data-id]');
    
    menuItems.forEach((item, index) => {
        items.push({
            id: parseInt(item.dataset.id),
            sort_order: index + 1,
            parent_id: item.dataset.parentId ? parseInt(item.dataset.parentId) : null
        });
    });
    
    // Auto-save order after drag
    if (items.length > 0) {
        saveOrderData(items);
    }
}

// Save order to database
function saveOrder() {
    const items = [];
    const menuItems = document.querySelectorAll('.menu-item[data-id]');
    
    menuItems.forEach((item, index) => {
        items.push({
            id: parseInt(item.dataset.id),
            sort_order: index + 1,
            parent_id: item.dataset.parentId ? parseInt(item.dataset.parentId) : null
        });
    });
    
    saveOrderData(items);
}

function saveOrderData(items) {
    fetch('{{ route("admin.menus.update-sort-order") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ items: items })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Menu order updated successfully!', 'success');
        } else {
            showNotification('Error updating menu order', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error updating menu order', 'error');
    });
}

// Toggle menu item status
function toggleStatus(menuId) {
    fetch(`{{ url('admin/menus') }}/${menuId}/toggle-status`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update UI
            const toggleBtn = document.querySelector(`[data-menu-id="${menuId}"]`);
            if (toggleBtn) {
                const icon = toggleBtn.querySelector('i');
                if (data.is_active) {
                    icon.className = 'bi bi-pause-circle';
                    toggleBtn.className = 'btn btn-modern-sm btn-outline-warning';
                    toggleBtn.title = 'Deactivate';
                } else {
                    icon.className = 'bi bi-play-circle';
                    toggleBtn.className = 'btn btn-modern-sm btn-outline-success';
                    toggleBtn.title = 'Activate';
                }
            }
            
            // Update status badge
            const menuItem = document.querySelector(`[data-id="${menuId}"]`);
            if (menuItem) {
                const statusBadge = menuItem.querySelector('.status-badge');
                if (statusBadge) {
                    statusBadge.textContent = data.is_active ? 'Active' : 'Inactive';
                    statusBadge.className = `status-badge ${data.is_active ? 'status-active' : 'status-inactive'}`;
                }
            }
            
            showNotification(data.message, 'success');
        } else {
            showNotification('Error updating menu status', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error updating menu status', 'error');
    });
}

// Bulk selection
function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.menu-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
    
    updateBulkActions();
}

function updateBulkActions() {
    const checkboxes = document.querySelectorAll('.menu-checkbox:checked');
    const count = checkboxes.length;
    
    document.getElementById('selectedCount').textContent = count;
    const bulkBar = document.getElementById('bulkActionsBar');
    
    if (count > 0) {
        bulkBar.classList.add('show');
    } else {
        bulkBar.classList.remove('show');
    }
    
    // Show/hide individual bulk buttons
    document.getElementById('bulkActivateBtn').style.display = count > 0 ? 'inline-block' : 'none';
    document.getElementById('bulkDeactivateBtn').style.display = count > 0 ? 'inline-block' : 'none';
    document.getElementById('bulkDeleteBtn').style.display = count > 0 ? 'inline-block' : 'none';
}

function clearSelection() {
    const checkboxes = document.querySelectorAll('.menu-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
    updateBulkActions();
}

// Bulk actions
function bulkAction(action) {
    const checkboxes = document.querySelectorAll('.menu-checkbox:checked');
    if (checkboxes.length === 0) {
        showNotification('Please select menu items first', 'error');
        return;
    }
    
    const menuIds = Array.from(checkboxes).map(cb => cb.value);
    
    if (confirm(`Are you sure you want to ${action} ${menuIds.length} menu item(s)?`)) {
        fetch('{{ route("admin.menus.bulk-action") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                action: action,
                menu_ids: menuIds
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                setTimeout(() => location.reload(), 1000);
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

// Delete menu item
function deleteMenuItem(menuId) {
    if (confirm('Are you sure you want to delete this menu item? This will also delete any sub-menu items.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `{{ url('admin/menus') }}/${menuId}`;
        form.innerHTML = `
            @csrf
            @method('DELETE')
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Enhanced notification system
function showNotification(message, type = 'success') {
    const toastClass = type === 'success' ? 'notification-success' : 'notification-error';
    const icon = type === 'success' ? 'bi-check-circle' : 'bi-x-circle';
    
    const toastHtml = `
        <div class="notification-toast ${toastClass}">
            <div class="d-flex align-items-center gap-2">
                <i class="bi ${icon}"></i>
                <span>${message}</span>
                <button onclick="this.parentElement.parentElement.remove()" class="btn-close btn-close-sm ms-auto"></button>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', toastHtml);
    
    // Auto remove after 4 seconds
    setTimeout(() => {
        const toast = document.querySelector('.notification-toast:last-of-type');
        if (toast) {
            toast.style.animation = 'slideOutRight 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }
    }, 4000);
}
</script>
@endpush