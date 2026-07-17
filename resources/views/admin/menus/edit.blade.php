{{-- resources/views/admin/menus/edit.blade.php --}}

@extends('admin.layouts.app')

@section('title', 'Edit Menu Item')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="bi bi-pencil-square me-2"></i>Edit Menu Item
                    </h3>
                    <a href="{{ route('admin.menus.index', ['category' => $menu->category]) }}" class="btn btn-light btn-sm modern-btn">
                        <i class="bi bi-arrow-left me-1"></i> Back to Menu
                    </a>
                </div>

                <!-- Current Status Display -->
                <div class="card-body">
                    <div class="alert alert-info mb-4">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h6 class="mb-1 d-flex align-items-center">
                                    <i class="bi bi-info-circle me-2"></i>
                                    Current Menu Item: <strong class="ms-2">{{ $menu->name }}</strong>
                                </h6>
                                <div class="d-flex align-items-center gap-2 mt-2">
                                    <span class="badge {{ $menu->is_active ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $menu->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                    <small class="text-muted">Category: {{ $categories[$menu->category] }}</small>
                                    @if($menu->children->count() > 0)
                                        <small class="text-muted">• {{ $menu->children->count() }} sub-items</small>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-4 text-md-end">
                                @if($menu->url)
                                    <a href="{{ $menu->full_url }}" target="{{ $menu->target }}" class="btn btn-outline-success btn-sm modern-btn">
                                        <i class="bi bi-eye me-1"></i> Preview Current
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('admin.menus.update', $menu) }}" method="POST" id="menuForm">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <!-- Main Content Section -->
                            <div class="col-md-8">
                                <!-- Menu Name -->
                                <div class="form-group mb-3">
                                    <label for="name" class="form-label">
                                        <i class="bi bi-type me-1"></i>Menu Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
                                           value="{{ old('name', $menu->name) }}" required placeholder="Enter menu item name">
                                    @error('name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                @php
                                    $protectedSlugs = ['/flights', '/hotels', '/visa'];
                                    $isProtected = in_array($menu->url, $protectedSlugs);
                                @endphp

                                <!-- URL -->
                                <div class="form-group mb-3">
                                    <label for="url" class="form-label">
                                        <i class="bi bi-link me-1"></i>URL/Link
                                    </label>

                                    <input type="text" 
                                        name="url" 
                                        id="url" 
                                        class="form-control @error('url') is-invalid @enderror" 
                                        value="{{ old('url', $menu->url) }}" 
                                        placeholder="e.g., /about, https://example.com, #contact"
                                        {{ $isProtected ? 'readonly' : '' }}>

                                    @if($isProtected)
                                        <small class="text-danger">
                                            This URL cannot be changed because it is system protected.
                                        </small>
                                    @endif

                                    @error('url')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>


                                <!-- Description -->
                                <div class="form-group mb-3">
                                    <label for="description" class="form-label">
                                        <i class="bi bi-text-paragraph me-1"></i>Description
                                    </label>
                                    <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" 
                                              rows="3" placeholder="Optional description for this menu item">{{ old('description', $menu->description) }}</textarea>
                                    <div class="form-text">
                                        <small class="text-muted">Brief description for admin reference (not shown on frontend)</small>
                                    </div>
                                    @error('description')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <!-- Settings Section -->
                            <div class="col-md-4">
                                <!-- Category -->
                                <div class="form-group mb-3">
                                    <label for="category" class="form-label">
                                        <i class="bi bi-folder me-1"></i>Menu Category <span class="text-danger">*</span>
                                    </label>
                                    <select name="category" id="category" class="form-select @error('category') is-invalid @enderror" required onchange="updateParentItems()">
                                        @foreach($categories as $key => $label)
                                            <option value="{{ $key }}" {{ old('category', $menu->category) === $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <!-- Parent Item -->
                                <div class="form-group mb-3">
                                    <label for="parent_id" class="form-label">
                                        <i class="bi bi-diagram-3 me-1"></i>Parent Item
                                    </label>
                                    <select name="parent_id" id="parent_id" class="form-select @error('parent_id') is-invalid @enderror">
                                        <option value="">None (Top Level)</option>
                                        @foreach($parentItems as $parent)
                                            <option value="{{ $parent->id }}" {{ old('parent_id', $menu->parent_id) == $parent->id ? 'selected' : '' }}>
                                                {{ $parent->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="form-text">
                                        <small class="text-muted">Select a parent to create a dropdown/sub-menu item</small>
                                    </div>
                                    @error('parent_id')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <!-- Icon -->
                                <div class="form-group mb-3">
                                    <label for="icon" class="form-label">
                                        <i class="bi bi-emoji-smile me-1"></i>Icon
                                    </label>
                                    <div class="input-group">
                                        <input type="text" name="icon" id="icon" class="form-control @error('icon') is-invalid @enderror" 
                                               value="{{ old('icon', $menu->icon) }}" placeholder="e.g., bi bi-house">
                                        <button type="button" class="btn btn-outline-secondary" onclick="showIconPicker()">
                                            <i class="bi bi-palette"></i>
                                        </button>
                                    </div>
                                    <div class="form-text">
                                        <small class="text-muted">
                                            Bootstrap Icons class (e.g., bi bi-house, bi bi-info-circle)
                                            <a href="https://icons.getbootstrap.com/" target="_blank">Browse icons</a>
                                        </small>
                                    </div>
                                    <div id="iconPreview" class="mt-2"></div>
                                    @error('icon')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <!-- Target -->
                                <div class="form-group mb-3">
                                    <label for="target" class="form-label">
                                        <i class="bi bi-box-arrow-up-right me-1"></i>Link Target <span class="text-danger">*</span>
                                    </label>
                                    <select name="target" id="target" class="form-select @error('target') is-invalid @enderror" required>
                                        <option value="_self" {{ old('target', $menu->target) === '_self' ? 'selected' : '' }}>
                                            Same Window (_self)
                                        </option>
                                        <option value="_blank" {{ old('target', $menu->target) === '_blank' ? 'selected' : '' }}>
                                            New Window (_blank)
                                        </option>
                                    </select>
                                    @error('target')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <!-- Status -->
                                <div class="form-group mb-3">
                                    <div class="form-check form-switch">
                                        <input type="checkbox" name="is_active" id="is_active" 
                                               class="form-check-input" value="1" {{ old('is_active', $menu->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            <i class="bi bi-toggle-on me-1"></i>Active (Visible on website)
                                        </label>
                                    </div>
                                    <div class="form-text">
                                        <small class="text-muted">
                                            Current status: 
                                            <span class="fw-semibold {{ $menu->is_active ? 'text-success' : 'text-danger' }}">
                                                {{ $menu->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </small>
                                    </div>
                                </div>

                                @if($menu->children->count() > 0)
                                    <div class="alert alert-warning">
                                        <i class="bi bi-exclamation-triangle me-2"></i>
                                        <strong>Note:</strong> This item has {{ $menu->children->count() }} sub-menu items. 
                                        Changing the category may affect sub-menu visibility.
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center mt-4 pt-3" style="border-top: 1px solid #dee2e6;">
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-outline-info modern-btn" onclick="previewMenuItem()">
                                            <i class="bi bi-eye me-1"></i> Preview Changes
                                        </button>
                                        @if($menu->url)
                                            <a href="{{ $menu->full_url }}" target="{{ $menu->target }}" class="btn btn-outline-success modern-btn">
                                                <i class="bi bi-box-arrow-up-right me-1"></i> Open Current Link
                                            </a>
                                        @endif
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-secondary modern-btn me-2" onclick="window.location='{{ route('admin.menus.index', ['category' => $menu->category]) }}'">
                                            <i class="bi bi-x me-1"></i> Cancel
                                        </button>
                                        <button type="submit" class="btn btn-primary modern-btn">
                                            <i class="bi bi-check-lg me-1"></i> Update Menu Item
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Icon Picker Modal -->
<div class="modal fade" id="iconPickerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Choose an Icon</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row" id="iconGrid">
                    <!-- Popular Bootstrap Icons -->
                    @php
                        $popularIcons = [
                            'bi-house', 'bi-info-circle', 'bi-telephone', 'bi-envelope', 'bi-person', 
                            'bi-gear', 'bi-search', 'bi-cart', 'bi-heart', 'bi-star', 'bi-bookmark',
                            'bi-calendar', 'bi-clock', 'bi-map', 'bi-chat', 'bi-image', 'bi-file-text',
                            'bi-download', 'bi-upload', 'bi-share', 'bi-question-circle', 'bi-exclamation-circle',
                            'bi-check-circle', 'bi-x-circle', 'bi-plus-circle', 'bi-arrow-right', 'bi-arrow-left',
                            'bi-menu-app', 'bi-list', 'bi-grid', 'bi-layers', 'bi-collection', 'bi-folder'
                        ];
                    @endphp
                    
                    @foreach($popularIcons as $icon)
                        <div class="col-2 text-center mb-3">
                            <button type="button" class="btn btn-outline-secondary w-100 icon-option" onclick="selectIcon('bi {{ $icon }}')">
                                <i class="bi {{ $icon }} d-block mb-1" style="font-size: 1.5rem;"></i>
                                <small>{{ str_replace('bi-', '', $icon) }}</small>
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="modal-footer">
                <input type="text" class="form-control me-2" placeholder="Or enter custom icon class" id="customIcon">
                <button type="button" class="btn btn-primary modern-btn" onclick="selectCustomIcon()">Use Custom</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Update parent items when category changes
function updateParentItems() {
    const category = document.getElementById('category').value;
    const parentSelect = document.getElementById('parent_id');
    const currentParentId = {{ $menu->parent_id ?? 'null' }};
    
    // Clear current options
    parentSelect.innerHTML = '<option value="">None (Top Level)</option>';
    
    if (category) {
        fetch(`{{ route('admin.menus.get-parent-items') }}?category=${category}&exclude_id={{ $menu->id }}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            data.forEach(item => {
                const option = document.createElement('option');
                option.value = item.id;
                option.textContent = item.name;
                if (currentParentId && item.id == currentParentId) {
                    option.selected = true;
                }
                parentSelect.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Error fetching parent items:', error);
        });
    }
}

// Icon preview
document.getElementById('icon').addEventListener('input', function() {
    const iconClass = this.value;
    const preview = document.getElementById('iconPreview');
    
    if (iconClass) {
        preview.innerHTML = `
            <div class="alert alert-info p-2">
                <div class="d-flex align-items-center gap-2">
                    <i class="${iconClass}" style="font-size: 1.5rem;"></i>
                    <div>
                        <div class="fw-semibold">Icon Preview</div>
                        <small class="text-muted">${iconClass}</small>
                    </div>
                </div>
            </div>
        `;
    } else {
        preview.innerHTML = '';
    }
});

// Show icon picker modal
function showIconPicker() {
    const modal = new bootstrap.Modal(document.getElementById('iconPickerModal'));
    modal.show();
}

// Select icon from picker
function selectIcon(iconClass) {
    document.getElementById('icon').value = iconClass;
    document.getElementById('icon').dispatchEvent(new Event('input'));
    
    const modal = bootstrap.Modal.getInstance(document.getElementById('iconPickerModal'));
    modal.hide();
}

// Select custom icon
function selectCustomIcon() {
    const customIcon = document.getElementById('customIcon').value;
    if (customIcon) {
        selectIcon(customIcon);
    }
}

// Preview menu item
function previewMenuItem() {
    const name = document.getElementById('name').value;
    const url = document.getElementById('url').value;
    const icon = document.getElementById('icon').value;
    const target = document.getElementById('target').value;
    const isActive = document.getElementById('is_active').checked;
    
    if (!name) {
        alert('Please enter a menu name first');
        return;
    }
    
    const previewHtml = `
        <div class="alert alert-success" id="livePreview">
            <h6><i class="bi bi-eye me-2"></i>Live Preview:</h6>
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    ${icon ? `<i class="${icon}"></i>` : ''}
                    <span class="fw-semibold">${name}</span>
                    <span class="badge ${isActive ? 'bg-success' : 'bg-secondary'}">${isActive ? 'Active' : 'Inactive'}</span>
                    ${target === '_blank' ? '<i class="bi bi-box-arrow-up-right"></i>' : ''}
                </div>
                <button onclick="this.parentElement.parentElement.parentElement.remove()" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x"></i>
                </button>
            </div>
            ${url ? `<div class="mt-2"><small class="text-muted"><i class="bi bi-link me-1"></i>${url}</small></div>` : ''}
            <div class="mt-2">
                <small class="text-success">
                    <i class="bi bi-lightbulb me-1"></i>
                    This is how your menu item will appear. Changes will be saved when you click "Update Menu Item".
                </small>
            </div>
        </div>
    `;
    
    // Remove existing preview
    const existingPreview = document.getElementById('livePreview');
    if (existingPreview) {
        existingPreview.parentElement.remove();
    }
    
    // Add new preview
    const previewDiv = document.createElement('div');
    previewDiv.innerHTML = previewHtml;
    
    const form = document.getElementById('menuForm');
    form.parentNode.insertBefore(previewDiv, form);
    
    // Auto remove after 15 seconds
    setTimeout(() => {
        const preview = document.getElementById('livePreview');
        if (preview) {
            preview.parentElement.remove();
        }
    }, 15000);
}

// Form validation and submission
document.getElementById('menuForm').addEventListener('submit', function(e) {
    const name = document.getElementById('name').value.trim();
    
    if (!name) {
        e.preventDefault();
        alert('Please enter a menu name.');
        document.getElementById('name').focus();
        return;
    }
    
    // Show loading state
    const submitButton = this.querySelector('button[type="submit"]');
    if (submitButton) {
        const originalText = submitButton.innerHTML;
        submitButton.innerHTML = '<i class="spinner-border spinner-border-sm me-2"></i>Updating...';
        submitButton.disabled = true;
        
        // Re-enable button after 5 seconds as fallback
        setTimeout(() => {
            submitButton.innerHTML = originalText;
            submitButton.disabled = false;
        }, 5000);
    }
});

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    // Trigger icon preview for existing value
    const iconInput = document.getElementById('icon');
    if (iconInput.value) {
        iconInput.dispatchEvent(new Event('input'));
    }
    
    // Add warning for category change if has children
    const categorySelect = document.getElementById('category');
    const originalCategory = '{{ $menu->category }}';
    const hasChildren = {{ $menu->children->count() }};
    
    if (hasChildren > 0) {
        categorySelect.addEventListener('change', function() {
            if (this.value !== originalCategory) {
                if (!confirm(`This menu item has ${hasChildren} sub-items. Changing the category may affect their visibility. Continue?`)) {
                    this.value = originalCategory;
                    return;
                }
            }
        });
    }
});

// Show notification when form loads
document.addEventListener('DOMContentLoaded', function() {
    // Show a subtle notification about editing
    setTimeout(() => {
        const notificationHtml = `
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="bi bi-info-circle me-2"></i>
                You are editing <strong>{{ $menu->name }}</strong>. Make your changes and click "Update Menu Item" to save.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        const form = document.getElementById('menuForm');
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = notificationHtml;
        form.parentNode.insertBefore(tempDiv, form);
        
        // Auto dismiss after 5 seconds
        setTimeout(() => {
            const alert = tempDiv.querySelector('.alert');
            if (alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        }, 5000);
    }, 500);
});
</script>
@endpush