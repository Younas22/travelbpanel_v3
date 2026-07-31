{{-- Expects: $menu (Menu|null — null on create, the model on edit),
     $categories, $parentItems, $category (current category key). --}}
@php
    $isEdit = (bool) $menu;
    $protectedSlugs = ['/flights', '/hotels', '/visa'];
    $isProtected = $isEdit && in_array($menu->url, $protectedSlugs);
@endphp

@if($isEdit)
    <div class="tt-card bg-blue-50/40 rounded-2xl border border-blue-100 p-4 sm:p-5 mb-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-sm font-semibold text-novatext flex items-center gap-2">
                    <svg class="w-4 h-4 text-novablue" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 8v4M12 16h.01"/></svg>
                    Current Menu Item: {{ $menu->name }}
                </p>
                <div class="flex items-center gap-2 mt-2">
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold {{ $menu->is_active ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-novamuted' }}">{{ $menu->is_active ? 'Active' : 'Inactive' }}</span>
                    <span class="text-xs text-novamuted">Category: {{ $categories[$menu->category] }}</span>
                    @if($menu->children->count() > 0)
                        <span class="text-xs text-novamuted">&bull; {{ $menu->children->count() }} sub-items</span>
                    @endif
                </div>
            </div>
            @if($menu->url)
                <a href="{{ $menu->full_url }}" target="{{ $menu->target }}" class="mn-btn-nova px-4 py-2 text-xs font-semibold" style="color:#22C55E; border-color:#22C55E;">Preview Current</a>
            @endif
        </div>
    </div>
@endif

<form action="{{ $isEdit ? route('admin.menus.update', $menu) : route('admin.menus.store') }}" method="POST" id="menuForm">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <div class="lg:col-span-2 space-y-5">
            <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 sm:p-5">
                <div class="mb-4">
                    <label class="mn-label" for="name">Menu Name <span class="mn-required">*</span></label>
                    <input type="text" name="name" id="name" class="mn-input @error('name') is-invalid @enderror"
                           value="{{ old('name', $menu->name ?? '') }}" required placeholder="Enter menu item name">
                    @error('name') <p class="mn-error">{{ $message }}</p> @enderror
                </div>

                <div class="mb-4">
                    <label class="mn-label" for="url">URL / Link</label>
                    <input type="text" name="url" id="url" class="mn-input @error('url') is-invalid @enderror"
                           value="{{ old('url', $menu->url ?? '') }}" placeholder="e.g., /about, https://example.com, #contact"
                           {{ $isProtected ? 'readonly' : '' }}>
                    @if($isProtected)
                        <p class="mn-error">This URL cannot be changed because it is system protected.</p>
                    @else
                        <p class="mn-help">Leave empty for dropdown parent items. Can be relative (/about) or absolute (https://example.com)</p>
                    @endif
                    @error('url') <p class="mn-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mn-label" for="description">Description</label>
                    <textarea name="description" id="description" class="mn-input @error('description') is-invalid @enderror" rows="3"
                              placeholder="Optional description for this menu item">{{ old('description', $menu->description ?? '') }}</textarea>
                    <p class="mn-help">Brief description for admin reference (not shown on frontend)</p>
                    @error('description') <p class="mn-error">{{ $message }}</p> @enderror
                </div>

                @if($isEdit && $menu->children->count() > 0)
                    <div class="tt-card bg-amber-50/60 rounded-2xl border border-amber-100 p-3.5 mt-4 flex items-start gap-2.5">
                        <svg class="w-4 h-4 text-novawarning flex-shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 9v4M12 17h.01M10.3 3.9 2 18a2 2 0 0 0 1.7 3h16.6a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"/></svg>
                        <p class="text-xs text-novatext"><strong>Note:</strong> This item has {{ $menu->children->count() }} sub-menu items. Changing the category may affect sub-menu visibility.</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="space-y-5">
            <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 sm:p-5 space-y-4">
                <div>
                    <label class="mn-label" for="category">Menu Category <span class="mn-required">*</span></label>
                    <select name="category" id="category" class="mn-input @error('category') is-invalid @enderror" required onchange="updateParentItems()">
                        @foreach($categories as $key => $label)
                            <option value="{{ $key }}" {{ old('category', $menu->category ?? $category) === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('category') <p class="mn-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mn-label" for="parent_id">Parent Item</label>
                    <select name="parent_id" id="parent_id" class="mn-input @error('parent_id') is-invalid @enderror">
                        <option value="">None (Top Level)</option>
                        @foreach($parentItems as $parent)
                            <option value="{{ $parent->id }}" {{ old('parent_id', $menu->parent_id ?? '') == $parent->id ? 'selected' : '' }}>{{ $parent->name }}</option>
                        @endforeach
                    </select>
                    <p class="mn-help">Select a parent to create a dropdown/sub-menu item</p>
                    @error('parent_id') <p class="mn-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mn-label" for="icon">Icon</label>
                    <div class="flex items-center gap-2">
                        <input type="text" name="icon" id="icon" class="mn-input @error('icon') is-invalid @enderror"
                               value="{{ old('icon', $menu->icon ?? '') }}" placeholder="e.g., bi bi-house">
                        <button type="button" class="mn-icon-btn flex-shrink-0" onclick="showIconPicker()" data-tooltip="Choose icon">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 3a6 6 0 1 0 5.4 8.6 3.5 3.5 0 0 1-1.9-6.2A6 6 0 0 0 12 3Z"/></svg>
                        </button>
                    </div>
                    <p class="mn-help">Bootstrap Icons class (e.g., bi bi-house) &mdash; <a href="https://icons.getbootstrap.com/" target="_blank" class="text-novablue font-semibold">Browse icons</a></p>
                    <div id="iconPreview" class="mt-2"></div>
                    @error('icon') <p class="mn-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mn-label" for="target">Link Target <span class="mn-required">*</span></label>
                    <select name="target" id="target" class="mn-input @error('target') is-invalid @enderror" required>
                        <option value="_self" {{ old('target', $menu->target ?? '_self') === '_self' ? 'selected' : '' }}>Same Window (_self)</option>
                        <option value="_blank" {{ old('target', $menu->target ?? '') === '_blank' ? 'selected' : '' }}>New Window (_blank)</option>
                    </select>
                    @error('target') <p class="mn-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="flex items-center gap-3">
                        <span class="mn-switch">
                            <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $menu->is_active ?? true) ? 'checked' : '' }}>
                            <span class="mn-slider"></span>
                        </span>
                        <span class="text-xs font-semibold text-novatext">Active (Visible on website)</span>
                    </label>
                    @if($isEdit)
                        <p class="mn-help">Current status: <strong class="{{ $menu->is_active ? 'text-novasuccess' : 'text-novadanger' }}">{{ $menu->is_active ? 'Active' : 'Inactive' }}</strong></p>
                    @endif
                </div>
            </div>

            <div class="space-y-2">
                <button type="submit" class="mn-btn-nova mn-btn-primary w-full py-3 text-sm font-semibold">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                    {{ $isEdit ? 'Update Menu Item' : 'Create Menu Item' }}
                </button>
                <button type="button" class="mn-btn-nova w-full py-3 text-sm font-semibold" onclick="previewMenuItem()">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M2.5 12S6 5 12 5s9.5 7 9.5 7-3.5 7-9.5 7-9.5-7-9.5-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                    Preview
                </button>
                <a href="{{ route('admin.menus.index', ['category' => $menu->category ?? $category]) }}" class="mn-btn-nova w-full py-3 text-sm font-semibold text-center">Cancel</a>
            </div>
        </div>
    </div>
</form>

{{-- ===== ICON PICKER MODAL (Bootstrap modal shell kept for real functionality) ===== --}}
<div class="modal fade" id="iconPickerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius:1.5rem; font-family:'Plus Jakarta Sans',sans-serif;">
            <div class="modal-header" style="border-bottom:1px solid #E5E7EB;">
                <h5 class="modal-title" style="font-weight:700;">Choose an Icon</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="grid grid-cols-4 sm:grid-cols-6 gap-2">
                    @php
                        $popularIcons = [
                            'bi-house', 'bi-info-circle', 'bi-telephone', 'bi-envelope', 'bi-person',
                            'bi-gear', 'bi-search', 'bi-cart', 'bi-heart', 'bi-star', 'bi-bookmark',
                            'bi-calendar', 'bi-clock', 'bi-map', 'bi-chat', 'bi-image', 'bi-file-text',
                            'bi-download', 'bi-upload', 'bi-share', 'bi-question-circle', 'bi-exclamation-circle',
                            'bi-check-circle', 'bi-x-circle', 'bi-plus-circle', 'bi-arrow-right', 'bi-arrow-left',
                            'bi-menu-app', 'bi-list', 'bi-grid', 'bi-layers', 'bi-collection', 'bi-folder',
                        ];
                    @endphp
                    @foreach($popularIcons as $icon)
                        <button type="button" class="mn-btn-nova flex-col py-2.5 text-center" onclick="selectIcon('bi {{ $icon }}')">
                            <i class="bi {{ $icon }}" style="font-size:1.2rem;"></i>
                            <small class="text-[10px] mt-1">{{ str_replace('bi-', '', $icon) }}</small>
                        </button>
                    @endforeach
                </div>
            </div>
            <div class="modal-footer" style="border-top:1px solid #E5E7EB; gap:8px;">
                <input type="text" class="mn-input" style="max-width:220px;" placeholder="Or enter custom icon class" id="customIcon">
                <button type="button" onclick="selectCustomIcon()"
                        style="display:inline-flex; align-items:center; border-radius:9999px; padding:10px 20px; font-size:13px; font-weight:600; font-family:'Plus Jakarta Sans',sans-serif; background:#2563EB; color:#fff; border:1px solid #2563EB; cursor:pointer;">Use Custom</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function updateParentItems() {
    const category = document.getElementById('category').value;
    const parentSelect = document.getElementById('parent_id');
    const currentParentId = {{ $isEdit ? ($menu->parent_id ?? 'null') : 'null' }};
    const excludeId = {{ $isEdit ? $menu->id : 'null' }};
    parentSelect.innerHTML = '<option value="">None (Top Level)</option>';
    if (category) {
        let url = `{{ route('admin.menus.get-parent-items') }}?category=${category}`;
        if (excludeId) { url += `&exclude_id=${excludeId}`; }
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(response => response.json())
            .then(data => {
                data.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.id;
                    option.textContent = item.name;
                    if (currentParentId && item.id == currentParentId) { option.selected = true; }
                    parentSelect.appendChild(option);
                });
            })
            .catch(error => console.error('Error fetching parent items:', error));
    }
}

document.getElementById('icon').addEventListener('input', function() {
    const iconClass = this.value;
    const preview = document.getElementById('iconPreview');
    if (iconClass) {
        preview.innerHTML = `<div class="flex items-center gap-2 text-xs text-novatext bg-novabg rounded-xl px-3 py-2"><i class="${iconClass}"></i> Preview: ${iconClass}</div>`;
    } else { preview.innerHTML = ''; }
});

function showIconPicker() { new bootstrap.Modal(document.getElementById('iconPickerModal')).show(); }

function selectIcon(iconClass) {
    document.getElementById('icon').value = iconClass;
    document.getElementById('icon').dispatchEvent(new Event('input'));
    bootstrap.Modal.getInstance(document.getElementById('iconPickerModal')).hide();
}

function selectCustomIcon() {
    const customIcon = document.getElementById('customIcon').value;
    if (customIcon) { selectIcon(customIcon); }
}

function previewMenuItem() {
    const name = document.getElementById('name').value;
    const url = document.getElementById('url').value;
    const icon = document.getElementById('icon').value;
    const target = document.getElementById('target').value;
    if (!name) { alert('Please enter a menu name first'); return; }

    const existingPreview = document.getElementById('menuPreview');
    if (existingPreview) { existingPreview.remove(); }

    const previewDiv = document.createElement('div');
    previewDiv.id = 'menuPreview';
    previewDiv.className = 'tt-card bg-emerald-50/60 rounded-2xl border border-emerald-100 p-4 mb-5 flex items-center gap-2 text-xs text-novatext';
    previewDiv.innerHTML = `${icon ? `<i class="${icon}"></i>` : ''}<span class="font-semibold">${name}</span>${url ? `<span class="text-novamuted">(${url})</span>` : ''}${target === '_blank' ? ' ↗' : ''}`;

    const form = document.getElementById('menuForm');
    form.parentNode.insertBefore(previewDiv, form);
    setTimeout(() => { const p = document.getElementById('menuPreview'); if (p) p.remove(); }, 5000);
}

document.getElementById('menuForm').addEventListener('submit', function(e) {
    const name = document.getElementById('name').value.trim();
    if (!name) { e.preventDefault(); alert('Please enter a menu name.'); document.getElementById('name').focus(); return; }
    const submitButton = this.querySelector('button[type="submit"]');
    if (submitButton) { submitButton.disabled = true; }
});

document.addEventListener('DOMContentLoaded', function() {
    const iconInput = document.getElementById('icon');
    if (iconInput.value) { iconInput.dispatchEvent(new Event('input')); }
});
</script>
@endpush
