@extends('admin-nova.layouts.app')

@section('title', 'Menu Management')

@push('styles')
@include('admin-nova.menus._styles')
@endpush

@section('content')
<div id="mnPage" class="tt-fade-in font-jakarta">

    {{-- ============ HEADER ============ --}}
    <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
        <div>
            <h1 class="text-lg font-bold text-novatext">Menu Management</h1>
            <p class="text-xs text-novamuted mt-1">Organize your website navigation menus with drag &amp; drop</p>
        </div>
        <div class="flex items-center gap-2">
            <button class="mn-btn-nova mn-bulk-only px-4 py-2.5 text-xs font-semibold" onclick="bulkAction('delete')" id="bulkDeleteBtn" style="color:#EF4444; border-color:#EF4444;">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0-1 14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2L4 6h16Z"/></svg>
                Delete Selected
            </button>
            <a href="{{ route('admin.menus.create', ['category' => $category]) }}" class="mn-btn-nova mn-btn-primary px-4 py-2.5 text-xs font-semibold">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                Add Menu Item
            </a>
        </div>
    </div>

    {{-- ============ KPI CARDS ============ --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
        <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-3">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-medium text-novamuted">Total Menu Items</p>
                <div class="w-7 h-7 rounded-full bg-novablue text-white flex items-center justify-center flex-shrink-0">
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
                </div>
            </div>
            <span class="block text-lg font-bold text-novatext">{{ $stats['total'] }}</span>
        </div>
        <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-3">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-medium text-novamuted">Active Items</p>
                <div class="w-7 h-7 rounded-full bg-novasuccess text-white flex items-center justify-center flex-shrink-0">
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12.75 11.25 15 15 9.75"/></svg>
                </div>
            </div>
            <span class="block text-lg font-bold text-novatext">{{ $stats['active'] }}</span>
        </div>
        <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-3">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-medium text-novamuted">Header Menu</p>
                <div class="w-7 h-7 rounded-full bg-novawarning text-white flex items-center justify-center flex-shrink-0">
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="4" rx="1"/><rect x="3" y="10" width="18" height="4" rx="1"/><rect x="3" y="16" width="18" height="4" rx="1"/></svg>
                </div>
            </div>
            <span class="block text-lg font-bold text-novatext">{{ $stats['header'] }}</span>
        </div>
        <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-3">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-medium text-novamuted">Footer Menu</p>
                <div class="w-7 h-7 rounded-full bg-novacyan text-white flex items-center justify-center flex-shrink-0">
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                </div>
            </div>
            <span class="block text-lg font-bold text-novatext">{{ $stats['footer_total'] }}</span>
        </div>
    </div>

    {{-- ============ CATEGORY TABS ============ --}}
    <div class="flex flex-wrap items-center gap-2 mb-5">
        @foreach($categories as $key => $label)
            <a href="{{ route('admin.menus.index', ['category' => $key]) }}"
               class="mn-tab {{ $category === $key ? 'mn-tab-active' : '' }} inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold">
                @switch($key)
                    @case('header')
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="4" rx="1"/><rect x="3" y="10" width="18" height="4" rx="1"/><rect x="3" y="16" width="18" height="4" rx="1"/></svg>
                        @break
                    @case('footer_quick_links')
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2 3 14h7l-1 8 10-12h-7l1-8Z"/></svg>
                        @break
                    @case('footer_services')
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1Z"/></svg>
                        @break
                    @case('footer_support')
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M9.5 9a2.5 2.5 0 1 1 3.5 2.3c-.7.35-1 .9-1 1.7v.5M12 17h.01"/></svg>
                        @break
                @endswitch
                {{ $label }}
            </a>
        @endforeach
    </div>

    {{-- ============ MENU CONTAINER ============ --}}
    <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 sm:p-5">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
            <h2 class="text-sm font-semibold text-novatext">{{ $categories[$category] }}</h2>
            <div class="flex items-center gap-2">
                <button class="mn-btn-nova mn-bulk-only px-3 py-2 text-xs font-semibold" style="color:#22C55E; border-color:#22C55E;" onclick="bulkAction('activate')" id="bulkActivateBtn">Activate</button>
                <button class="mn-btn-nova mn-bulk-only px-3 py-2 text-xs font-semibold" style="color:#F59E0B; border-color:#F59E0B;" onclick="bulkAction('deactivate')" id="bulkDeactivateBtn">Deactivate</button>
                <button class="mn-btn-nova mn-btn-cyan px-3 py-2 text-xs font-semibold" onclick="saveOrder()">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"/><path d="M17 21v-8H7v8M7 3v5h8"/></svg>
                    Save Order
                </button>
            </div>
        </div>

        {{-- Bulk Actions Bar --}}
        <div class="mn-bulk-bar tt-card bg-blue-50/40 rounded-2xl border border-blue-100 p-4 mb-4 items-center justify-between" id="bulkActionsBar">
            <span class="text-xs text-novatext flex items-center gap-2">
                <svg class="w-4 h-4 text-novablue" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12.75 11.25 15 15 9.75"/><circle cx="12" cy="12" r="9"/></svg>
                <strong><span id="selectedCount">0</span></strong> items selected
            </span>
            <div class="flex items-center gap-2">
                <button class="mn-btn-nova px-3 py-2 text-xs font-semibold" style="background:#22C55E; color:#fff; border-color:#22C55E;" onclick="bulkAction('activate')">Activate</button>
                <button class="mn-btn-nova px-3 py-2 text-xs font-semibold" style="background:#F59E0B; color:#fff; border-color:#F59E0B;" onclick="bulkAction('deactivate')">Deactivate</button>
                <button class="mn-btn-nova px-3 py-2 text-xs font-semibold" style="background:#EF4444; color:#fff; border-color:#EF4444;" onclick="bulkAction('delete')">Delete</button>
                <button class="mn-btn-nova px-3 py-2 text-xs font-semibold" onclick="clearSelection()">Cancel</button>
            </div>
        </div>

        {{-- Menu Items --}}
        <div id="menuItems" class="space-y-3">
            @forelse($menuItems as $item)
                <div class="mn-item rounded-2xl border border-novaborder p-3.5" data-id="{{ $item->id }}" data-parent-id="{{ $item->parent_id }}">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <input type="checkbox" class="menu-checkbox flex-shrink-0" value="{{ $item->id }}" onchange="updateBulkActions()">
                            <span class="mn-drag-handle flex-shrink-0">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><circle cx="9" cy="6" r="1.3"/><circle cx="9" cy="12" r="1.3"/><circle cx="9" cy="18" r="1.3"/><circle cx="15" cy="6" r="1.3"/><circle cx="15" cy="12" r="1.3"/><circle cx="15" cy="18" r="1.3"/></svg>
                            </span>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    @if($item->icon)<i class="{{ $item->icon }} text-novamuted"></i>@endif
                                    <span class="text-sm font-semibold text-novatext">{{ $item->name }}</span>
                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-semibold {{ $item->is_active ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-novamuted' }}">{{ $item->is_active ? 'Active' : 'Inactive' }}</span>
                                    @if($item->children->count() > 0)
                                        <span class="px-2 py-0.5 rounded-full text-[9px] font-semibold bg-blue-50 text-novablue">{{ $item->children->count() }} sub-items</span>
                                    @endif
                                </div>
                                @if($item->url)
                                    <p class="text-[11px] text-novamuted mt-1 truncate">{{ $item->url }} @if($item->target === '_blank') ↗ @endif</p>
                                @endif
                                @if($item->description)
                                    <p class="text-[11px] text-novamuted mt-0.5 truncate">{{ $item->description }}</p>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5 flex-shrink-0">
                            @if($item->url && $item->is_active)
                                <a href="{{ $item->full_url }}" target="{{ $item->target }}" class="mn-icon-btn" data-tooltip="Preview">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M2.5 12S6 5 12 5s9.5 7 9.5 7-3.5 7-9.5 7-9.5-7-9.5-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                </a>
                            @endif
                            <button class="mn-icon-btn {{ $item->is_active ? 'mn-icon-warn' : 'mn-icon-success' }}" onclick="toggleStatus({{ $item->id }})" data-menu-id="{{ $item->id }}" data-tooltip="{{ $item->is_active ? 'Deactivate' : 'Activate' }}">
                                @if($item->is_active)
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="6" y="5" width="4" height="14" rx="1"/><rect x="14" y="5" width="4" height="14" rx="1"/></svg>
                                @else
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M6 4.5v15l13-7.5-13-7.5Z"/></svg>
                                @endif
                            </button>
                            <a href="{{ route('admin.menus.edit', $item) }}" class="mn-icon-btn" data-tooltip="Edit">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M16.474 5.408 18.592 7.526M4 20l1.11-3.92a2 2 0 0 1 .53-.9l9.9-9.9a1.5 1.5 0 0 1 2.12 0l1.06 1.06a1.5 1.5 0 0 1 0 2.12l-9.9 9.9a2 2 0 0 1-.9.53L4 20Z"/></svg>
                            </a>
                            <form method="POST" action="{{ route('admin.menus.duplicate', $item) }}">
                                @csrf
                                <button type="submit" class="mn-icon-btn" data-tooltip="Duplicate">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="8" y="8" width="12" height="12" rx="2"/><path d="M4 16V4a2 2 0 0 1 2-2h10"/></svg>
                                </button>
                            </form>
                            <button class="mn-icon-btn mn-icon-danger" onclick="deleteMenuItem({{ $item->id }})" data-tooltip="Delete">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0-1 14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2L4 6h16Z"/></svg>
                            </button>
                        </div>
                    </div>

                    @if($item->children->count() > 0)
                        <div class="mt-3 pl-8 space-y-2">
                            @foreach($item->children as $child)
                                <div class="rounded-xl border border-novaborder p-3" data-id="{{ $child->id }}" data-parent-id="{{ $child->parent_id }}">
                                    <div class="flex flex-wrap items-center justify-between gap-3">
                                        <div class="flex items-center gap-2.5 min-w-0">
                                            <input type="checkbox" class="menu-checkbox flex-shrink-0" value="{{ $child->id }}" onchange="updateBulkActions()">
                                            <span class="mn-drag-handle flex-shrink-0">
                                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><circle cx="9" cy="6" r="1.3"/><circle cx="9" cy="12" r="1.3"/><circle cx="9" cy="18" r="1.3"/><circle cx="15" cy="6" r="1.3"/><circle cx="15" cy="12" r="1.3"/><circle cx="15" cy="18" r="1.3"/></svg>
                                            </span>
                                            <div class="min-w-0">
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    @if($child->icon)<i class="{{ $child->icon }} text-novamuted"></i>@endif
                                                    <span class="text-xs font-semibold text-novatext">{{ $child->name }}</span>
                                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-semibold {{ $child->is_active ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-novamuted' }}">{{ $child->is_active ? 'Active' : 'Inactive' }}</span>
                                                </div>
                                                @if($child->url)
                                                    <p class="text-[11px] text-novamuted mt-1 truncate">{{ $child->url }} @if($child->target === '_blank') ↗ @endif</p>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-1.5 flex-shrink-0">
                                            @if($child->url && $child->is_active)
                                                <a href="{{ $child->full_url }}" target="{{ $child->target }}" class="mn-icon-btn" data-tooltip="Preview">
                                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M2.5 12S6 5 12 5s9.5 7 9.5 7-3.5 7-9.5 7-9.5-7-9.5-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                                </a>
                                            @endif
                                            <button class="mn-icon-btn {{ $child->is_active ? 'mn-icon-warn' : 'mn-icon-success' }}" onclick="toggleStatus({{ $child->id }})" data-menu-id="{{ $child->id }}" data-tooltip="{{ $child->is_active ? 'Deactivate' : 'Activate' }}">
                                                @if($child->is_active)
                                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="6" y="5" width="4" height="14" rx="1"/><rect x="14" y="5" width="4" height="14" rx="1"/></svg>
                                                @else
                                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M6 4.5v15l13-7.5-13-7.5Z"/></svg>
                                                @endif
                                            </button>
                                            <a href="{{ route('admin.menus.edit', $child) }}" class="mn-icon-btn" data-tooltip="Edit">
                                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M16.474 5.408 18.592 7.526M4 20l1.11-3.92a2 2 0 0 1 .53-.9l9.9-9.9a1.5 1.5 0 0 1 2.12 0l1.06 1.06a1.5 1.5 0 0 1 0 2.12l-9.9 9.9a2 2 0 0 1-.9.53L4 20Z"/></svg>
                                            </a>
                                            <button class="mn-icon-btn mn-icon-danger" onclick="deleteMenuItem({{ $child->id }})" data-tooltip="Delete">
                                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0-1 14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2L4 6h16Z"/></svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @empty
                <div class="text-center py-16">
                    <svg class="w-10 h-10 text-novaborder mx-auto mb-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
                    <p class="text-sm font-semibold text-novatext">No menu items found</p>
                    <p class="text-xs text-novamuted mt-1">Start building your {{ strtolower($categories[$category]) }} by adding your first menu item.</p>
                    <a href="{{ route('admin.menus.create', ['category' => $category]) }}" class="mn-btn-nova mn-btn-primary px-4 py-2 text-xs font-semibold mt-4 inline-flex">Add First Menu Item</a>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
let sortable;

document.addEventListener('DOMContentLoaded', function() {
    const menuContainer = document.getElementById('menuItems');
    if (menuContainer) {
        sortable = Sortable.create(menuContainer, {
            handle: '.mn-drag-handle',
            animation: 150,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'dragging',
            onStart: function(evt) { evt.item.classList.add('dragging'); },
            onEnd: function(evt) { evt.item.classList.remove('dragging'); updateSortOrder(); }
        });
    }
});

function updateSortOrder() {
    const items = [];
    document.querySelectorAll('#mnPage .mn-item[data-id]').forEach((item, index) => {
        items.push({ id: parseInt(item.dataset.id), sort_order: index + 1, parent_id: item.dataset.parentId ? parseInt(item.dataset.parentId) : null });
    });
    if (items.length > 0) { saveOrderData(items); }
}

function saveOrder() {
    const items = [];
    document.querySelectorAll('#mnPage .mn-item[data-id]').forEach((item, index) => {
        items.push({ id: parseInt(item.dataset.id), sort_order: index + 1, parent_id: item.dataset.parentId ? parseInt(item.dataset.parentId) : null });
    });
    saveOrderData(items);
}

function saveOrderData(items) {
    fetch('{{ route("admin.menus.update-sort-order") }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify({ items: items })
    })
    .then(response => response.json())
    .then(data => { showNotification(data.success ? 'Menu order updated successfully!' : 'Error updating menu order', data.success ? 'success' : 'error'); })
    .catch(error => { console.error('Error:', error); showNotification('Error updating menu order', 'error'); });
}

function toggleStatus(menuId) {
    fetch(`{{ url('admin/menus') }}/${menuId}/toggle-status`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const toggleBtn = document.querySelector(`[data-menu-id="${menuId}"]`);
            if (toggleBtn) {
                const svg = toggleBtn.querySelector('svg');
                if (data.is_active) {
                    svg.innerHTML = '<rect x="6" y="5" width="4" height="14" rx="1"/><rect x="14" y="5" width="4" height="14" rx="1"/>';
                    toggleBtn.classList.remove('mn-icon-success'); toggleBtn.classList.add('mn-icon-warn');
                    toggleBtn.dataset.tooltip = 'Deactivate';
                } else {
                    svg.innerHTML = '<path d="M6 4.5v15l13-7.5-13-7.5Z"/>';
                    toggleBtn.classList.remove('mn-icon-warn'); toggleBtn.classList.add('mn-icon-success');
                    toggleBtn.dataset.tooltip = 'Activate';
                }
            }
            const menuItem = document.querySelector(`[data-id="${menuId}"]`);
            if (menuItem) {
                const statusBadge = menuItem.querySelector('span.rounded-full');
                if (statusBadge) {
                    statusBadge.textContent = data.is_active ? 'Active' : 'Inactive';
                    statusBadge.className = `px-2 py-0.5 rounded-full text-[9px] font-semibold ${data.is_active ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-novamuted'}`;
                }
            }
            showNotification(data.message, 'success');
        } else { showNotification('Error updating menu status', 'error'); }
    })
    .catch(error => { console.error('Error:', error); showNotification('Error updating menu status', 'error'); });
}

function updateBulkActions() {
    const checkboxes = document.querySelectorAll('.menu-checkbox:checked');
    const count = checkboxes.length;
    document.getElementById('selectedCount').textContent = count;
    document.getElementById('bulkActionsBar').classList.toggle('mn-visible', count > 0);
    document.getElementById('bulkActivateBtn').classList.toggle('mn-show', count > 0);
    document.getElementById('bulkDeactivateBtn').classList.toggle('mn-show', count > 0);
    document.getElementById('bulkDeleteBtn').classList.toggle('mn-show', count > 0);
}

function clearSelection() {
    document.querySelectorAll('.menu-checkbox').forEach(cb => { cb.checked = false; });
    updateBulkActions();
}

function bulkAction(action) {
    const checkboxes = document.querySelectorAll('.menu-checkbox:checked');
    if (checkboxes.length === 0) { showNotification('Please select menu items first', 'error'); return; }
    const menuIds = Array.from(checkboxes).map(cb => cb.value);
    if (confirm(`Are you sure you want to ${action} ${menuIds.length} menu item(s)?`)) {
        fetch('{{ route("admin.menus.bulk-action") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ action: action, menu_ids: menuIds })
        })
        .then(response => response.json())
        .then(data => {
            showNotification(data.message, data.success ? 'success' : 'error');
            if (data.success) { setTimeout(() => location.reload(), 1000); }
        })
        .catch(error => { console.error('Error:', error); showNotification('An error occurred', 'error'); });
    }
}

function deleteMenuItem(menuId) {
    if (confirm('Are you sure you want to delete this menu item? This will also delete any sub-menu items.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `{{ url('admin/menus') }}/${menuId}`;
        form.innerHTML = `@csrf @method('DELETE')`;
        document.body.appendChild(form);
        form.submit();
    }
}

function showNotification(message, type = 'success') {
    const t = document.createElement('div');
    t.className = 'mn-toast';
    t.style.background = type === 'success' ? '#22C55E' : '#EF4444';
    t.textContent = message;
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 4000);
}
</script>
@endpush
