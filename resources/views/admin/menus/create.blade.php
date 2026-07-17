{{-- resources/views/admin/menus/create.blade.php --}}

@extends('admin.layouts.app')

@section('title', 'Add Menu Item')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="bi bi-plus-circle me-2"></i>Add New Menu Item
                    </h3>
                    <a href="{{ route('admin.menus.index', ['category' => $category]) }}" class="btn btn-light btn-sm">
                        <i class="bi bi-arrow-left me-1"></i> Back to Menu
                    </a>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.menus.store') }}" method="POST" id="menuForm">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-8">
                                <!-- Menu Name -->
                                <div class="form-group mb-3">
                                    <label for="name" class="form-label">
                                        <i class="bi bi-type me-1"></i>Menu Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
                                           value="{{ old('name') }}" required placeholder="Enter menu item name">
                                    @error('name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <!-- URL -->
                                <div class="form-group mb-3">
                                    <label for="url" class="form-label">
                                        <i class="bi bi-link me-1"></i>URL/Link
                                    </label>
                                    <input type="text" name="url" id="url" class="form-control @error('url') is-invalid @enderror" 
                                           value="{{ old('url') }}" placeholder="e.g., /about, https://example.com, #contact">
                                    <div class="form-text">
                                        <small class="text-muted">
                                            Leave empty for dropdown parent items. Can be relative (/about) or absolute (https://example.com)
                                        </small>
                                    </div>
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
                                              rows="3" placeholder="Optional description for this menu item">{{ old('description') }}</textarea>
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

                            <div class="col-md-4">
                                <!-- Category -->
                                <div class="form-group mb-3">
                                    <label for="category" class="form-label">
                                        <i class="bi bi-folder me-1"></i>Menu Category <span class="text-danger">*</span>
                                    </label>
                                    <select name="category" id="category" class="form-select @error('category') is-invalid @enderror" required onchange="updateParentItems()">
                                        @foreach($categories as $key => $label)
                                            <option value="{{ $key }}" {{ old('category', $category) === $key ? 'selected' : '' }}>
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
                                            <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
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
                                               value="{{ old('icon') }}" placeholder="e.g., bi bi-house">
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
                                        <option value="_self" {{ old('target', '_self') === '_self' ? 'selected' : '' }}>
                                            Same Window (_self)
                                        </option>
                                        <option value="_blank" {{ old('target') === '_blank' ? 'selected' : '' }}>
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
                                               class="form-check-input" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            <i class="bi bi-toggle-on me-1"></i>Active (Visible on website)
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center mt-4 pt-3" style="border-top: 1px solid #dee2e6;">
                                    <div>
                                        <button type="button" class="btn btn-outline-info" onclick="previewMenuItem()">
                                            <i class="bi bi-eye me-1"></i> Preview
                                        </button>
                                    </div>
                                    <div>
                                        <button type="button" class="btn btn-secondary me-2" onclick="window.location='{{ route('admin.menus.index', ['category' => $category]) }}'">
                                            <i class="bi bi-x me-1"></i> Cancel
                                        </button>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-check-lg me-1"></i> Create Menu Item
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
                            'bi-check-circle', 'bi-x-circle', 'bi-plus-circle', 'bi-arrow-right', 'bi-arrow-left'
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
                <button type="button" class="btn btn-primary" onclick="selectCustomIcon()">Use Custom</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Update parent items when category changes
function updateParentItems() {
    const category = document.getElementById('category').value;
    const parentSelect = document.getElementById('parent_id');
    
    // Clear current options
    parentSelect.innerHTML = '<option value="">None (Top Level)</option>';
    
    if (category) {
        fetch(`{{ route('admin.menus.get-parent-items') }}?category=${category}`, {
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
                <i class="${iconClass} me-2"></i>
                Preview: ${iconClass}
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
    
    if (!name) {
        alert('Please enter a menu name first');
        return;
    }
    
    const previewHtml = `
        <div class="alert alert-success">
            <h6>Menu Item Preview:</h6>
            <div class="d-flex align-items-center">
                ${icon ? `<i class="${icon} me-2"></i>` : ''}
                <span>${name}</span>
                ${url ? `<small class="ms-2 text-muted">(${url})</small>` : ''}
                ${target === '_blank' ? '<i class="bi bi-box-arrow-up-right ms-1"></i>' : ''}
            </div>
        </div>
    `;
    
    // Show preview in a temporary element
    const existingPreview = document.getElementById('menuPreview');
    if (existingPreview) {
        existingPreview.remove();
    }
    
    const previewDiv = document.createElement('div');
    previewDiv.id = 'menuPreview';
    previewDiv.innerHTML = previewHtml;
    
    const form = document.getElementById('menuForm');
    form.parentNode.insertBefore(previewDiv, form);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        const preview = document.getElementById('menuPreview');
        if (preview) {
            preview.remove();
        }
    }, 5000);
}

// Form validation
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
        submitButton.innerHTML = '<i class="bi bi-spinner-border spinner-border-sm me-1"></i> Creating...';
        submitButton.disabled = true;
    }
});

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    // Trigger icon preview for existing value
    const iconInput = document.getElementById('icon');
    if (iconInput.value) {
        iconInput.dispatchEvent(new Event('input'));
    }
});
</script>
@endsection