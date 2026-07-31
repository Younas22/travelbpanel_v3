@extends('admin-nova.layouts.app')

@section('title', 'Pages Management')

@push('styles')
@include('admin-nova.pages._styles')
@endpush

@section('content')
@php
    // Page::status_badge returns Bootstrap semantic names (success/warning/
    // secondary), not Tailwind color names — mapped here to real Tailwind
    // classes rather than interpolating "bg-{{ $badge }}-50" directly,
    // which would generate non-existent classes like bg-success-50.
    $pgStatusStyle = [
        'success' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600'],
        'warning' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-600'],
        'secondary' => ['bg' => 'bg-slate-100', 'text' => 'text-novamuted'],
    ];
@endphp
<div id="pgPage" class="tt-fade-in font-jakarta">

    {{-- ============ HEADER ============ --}}
    <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
        <div>
            <h1 class="text-lg font-bold text-novatext">Pages Management</h1>
            <p class="text-xs text-novamuted mt-1">Manage your website pages and content</p>
        </div>
        <div class="flex items-center gap-2">
            <button class="pg-btn-nova pg-bulk-only px-4 py-2.5 text-xs font-semibold" onclick="bulkAction('delete')" id="bulkDeleteBtn" style="color:#EF4444; border-color:#EF4444;">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0-1 14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2L4 6h16Z"/></svg>
                Delete Selected
            </button>
            <a href="{{ route('admin.pages.create') }}" class="pg-btn-nova pg-btn-primary px-4 py-2.5 text-xs font-semibold">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                Add New Page
            </a>
        </div>
    </div>

    {{-- ============ KPI CARDS ============ --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
        <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-3">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-medium text-novamuted">Total Pages</p>
                <div class="w-7 h-7 rounded-full bg-novablue text-white flex items-center justify-center flex-shrink-0">
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2h9l3 3v17H6z"/><path d="M15 2v3h3M9 12h6M9 16h6"/></svg>
                </div>
            </div>
            <span class="block text-lg font-bold text-novatext">{{ number_format($stats['total']) }}</span>
        </div>
        <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-3">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-medium text-novamuted">Published</p>
                <div class="w-7 h-7 rounded-full bg-novasuccess text-white flex items-center justify-center flex-shrink-0">
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12.75 11.25 15 15 9.75"/></svg>
                </div>
            </div>
            <span class="block text-lg font-bold text-novatext">{{ number_format($stats['published']) }}</span>
        </div>
        <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-3">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-medium text-novamuted">Draft</p>
                <div class="w-7 h-7 rounded-full bg-novawarning text-white flex items-center justify-center flex-shrink-0">
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2h9l3 3v17H6z"/></svg>
                </div>
            </div>
            <span class="block text-lg font-bold text-novatext">{{ number_format($stats['draft']) }}</span>
        </div>
        <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-3">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-medium text-novamuted">Private</p>
                <div class="w-7 h-7 rounded-full bg-novacyan text-white flex items-center justify-center flex-shrink-0">
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V7a4 4 0 1 1 8 0v4"/></svg>
                </div>
            </div>
            <span class="block text-lg font-bold text-novatext">{{ number_format($stats['private']) }}</span>
        </div>
    </div>

    {{-- ============ FILTERS ============ --}}
    <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 sm:p-5 mb-5">
        <form method="GET" action="{{ route('admin.pages.index') }}">
            <div class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[220px]">
                    <label class="block text-xs font-semibold text-novamuted mb-1.5">Search pages</label>
                    <div class="relative">
                        <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-novamuted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, slug, or content..."
                               class="w-full text-sm border border-novaborder rounded-full pl-10 pr-4 py-2.5">
                    </div>
                </div>
                <div class="w-40">
                    <label class="block text-xs font-semibold text-novamuted mb-1.5">Status</label>
                    <select name="status" class="w-full text-sm border border-novaborder rounded-full px-3.5 py-2.5">
                        <option value="">All Status</option>
                        <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="private" {{ request('status') == 'private' ? 'selected' : '' }}>Private</option>
                    </select>
                </div>
                <div class="w-44">
                    <label class="block text-xs font-semibold text-novamuted mb-1.5">Order By</label>
                    <select name="order_by" class="w-full text-sm border border-novaborder rounded-full px-3.5 py-2.5">
                        <option value="sort_order" {{ request('order_by') == 'sort_order' ? 'selected' : '' }}>Sort Order</option>
                        <option value="name" {{ request('order_by') == 'name' ? 'selected' : '' }}>Name</option>
                        <option value="created_at" {{ request('order_by') == 'created_at' ? 'selected' : '' }}>Created Date</option>
                        <option value="updated_at" {{ request('order_by') == 'updated_at' ? 'selected' : '' }}>Updated Date</option>
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <button type="submit" class="pg-btn-nova pg-btn-primary w-10 h-10" data-tooltip="Apply filters">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 5h16M7 12h10M10 19h4"/></svg>
                    </button>
                    <a href="{{ route('admin.pages.index') }}" class="pg-btn-nova w-10 h-10" data-tooltip="Reset filters">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 1 2.64 6.36M3 12v6m0-6h6"/></svg>
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- ============ BULK ACTIONS BAR ============ --}}
    <div class="pg-bulk-bar tt-card bg-blue-50/40 rounded-2xl border border-blue-100 p-4 mb-4 items-center justify-between" id="bulkActionsBar">
        <span class="text-xs text-novatext"><strong><span id="selectedCount">0</span></strong> pages selected</span>
        <div class="flex flex-wrap items-center gap-2">
            <button class="pg-btn-nova px-3 py-2 text-xs font-semibold" style="color:#22C55E; border-color:#22C55E;" onclick="bulkAction('publish')">Publish</button>
            <button class="pg-btn-nova px-3 py-2 text-xs font-semibold" style="color:#F59E0B; border-color:#F59E0B;" onclick="bulkAction('unpublish')">Unpublish</button>
            <button class="pg-btn-nova px-3 py-2 text-xs font-semibold" onclick="bulkAction('private')">Make Private</button>
            <button class="pg-btn-nova px-3 py-2 text-xs font-semibold" style="background:#EF4444; color:#fff; border-color:#EF4444;" onclick="bulkAction('delete')">Delete</button>
            <button class="pg-btn-nova px-3 py-2 text-xs font-semibold" onclick="clearSelection()">Cancel</button>
        </div>
    </div>

    {{-- ============ PAGES LIST ============ --}}
    <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 sm:p-5">
        <div class="hidden lg:flex lg:items-center lg:gap-3 lg:px-4 pb-2 mb-1">
            <span class="w-6 flex-shrink-0"><input type="checkbox" id="selectAll" onchange="toggleSelectAll()"></span>
            <span class="w-64 flex-shrink-0 text-[10px] font-semibold uppercase tracking-wide text-novamuted">Page Details</span>
            <span class="w-24 flex-shrink-0 text-[10px] font-semibold uppercase tracking-wide text-novamuted">Status</span>
            <span class="w-20 flex-shrink-0 text-[10px] font-semibold uppercase tracking-wide text-novamuted">Menu</span>
            <span class="flex-1 min-w-0 text-[10px] font-semibold uppercase tracking-wide text-novamuted">Modified</span>
            <span class="flex-shrink-0 text-[10px] font-semibold uppercase tracking-wide text-novamuted" style="width:210px;">Actions</span>
        </div>

        <div class="space-y-3">
            @forelse ($pages as $page)
                <div class="tt-row flex flex-col lg:flex-row lg:items-center gap-2.5 lg:gap-3 rounded-2xl border border-novaborder p-3.5 lg:p-4">
                    <div class="flex items-center gap-3 lg:contents">
                        <span class="lg:w-6 lg:flex-shrink-0"><input type="checkbox" class="page-checkbox" value="{{ $page->id }}" onchange="updateSelection()"></span>
                        <div class="min-w-0 lg:w-64 lg:flex-shrink-0">
                            <p class="text-sm font-semibold text-novatext truncate flex items-center gap-1.5">
                                {{ $page->name }}
                                @if($page->is_homepage)
                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-semibold bg-blue-50 text-novablue">Homepage</span>
                                @endif
                            </p>
                            <p class="text-[11px] text-novamuted truncate mt-0.5 font-mono">/{{ $page->slug }}</p>
                            @if($page->content)
                                <p class="text-[11px] text-novamuted truncate mt-0.5">{{ $page->excerpt }}</p>
                            @endif
                        </div>
                        <span class="lg:hidden ml-auto px-2.5 py-1 rounded-full text-[10px] font-semibold {{ ($pgStatusStyle[$page->status_badge] ?? $pgStatusStyle['secondary'])['bg'] }} {{ ($pgStatusStyle[$page->status_badge] ?? $pgStatusStyle['secondary'])['text'] }}">{{ ucfirst($page->status) }}</span>
                    </div>

                    <div class="hidden lg:block lg:w-24 lg:flex-shrink-0">
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold {{ ($pgStatusStyle[$page->status_badge] ?? $pgStatusStyle['secondary'])['bg'] }} {{ ($pgStatusStyle[$page->status_badge] ?? $pgStatusStyle['secondary'])['text'] }}">{{ ucfirst($page->status) }}</span>
                        @if($page->published_at)
                            <p class="text-[10px] text-novamuted mt-1">{{ $page->published_at->format('M j, Y') }}</p>
                        @endif
                    </div>

                    <div class="lg:w-20 lg:flex-shrink-0">
                        <p class="text-[9px] text-novamuted lg:hidden">Menu</p>
                        @if($page->show_in_menu)
                            <span class="px-2 py-0.5 rounded-full text-[9px] font-semibold bg-emerald-50 text-emerald-600">Yes</span>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-[9px] font-semibold bg-slate-100 text-novamuted">No</span>
                        @endif
                        <p class="text-[10px] text-novamuted mt-1">Order: {{ $page->sort_order }}</p>
                    </div>

                    <div class="lg:flex-1 min-w-0">
                        <p class="text-[9px] text-novamuted lg:hidden">Modified</p>
                        <p class="text-xs text-novatext">{{ $page->updated_at->format('M j, Y') }}</p>
                        <p class="text-[10px] text-novamuted">{{ $page->updated_at->diffForHumans() }}</p>
                    </div>

                    <div class="flex items-center gap-1.5 flex-wrap lg:flex-nowrap lg:flex-shrink-0" style="width:210px;">
                        @if($page->status === 'published')
                            <a href="{{ $page->url }}" target="_blank" class="pg-icon-btn" data-tooltip="View Page">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M2.5 12S6 5 12 5s9.5 7 9.5 7-3.5 7-9.5 7-9.5-7-9.5-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                            </a>
                        @endif
                        <a href="{{ route('admin.pages.edit', $page) }}" class="pg-icon-btn" data-tooltip="Edit">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M16.474 5.408 18.592 7.526M4 20l1.11-3.92a2 2 0 0 1 .53-.9l9.9-9.9a1.5 1.5 0 0 1 2.12 0l1.06 1.06a1.5 1.5 0 0 1 0 2.12l-9.9 9.9a2 2 0 0 1-.9.53L4 20Z"/></svg>
                        </a>
                        <form method="POST" action="{{ route('admin.pages.duplicate', $page) }}">
                            @csrf
                            <button type="submit" class="pg-icon-btn" data-tooltip="Duplicate">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="8" y="8" width="12" height="12" rx="2"/><path d="M4 16V4a2 2 0 0 1 2-2h10"/></svg>
                            </button>
                        </form>
                        @if($page->status === 'published')
                            <button class="pg-icon-btn pg-icon-warn" onclick="changePageStatus({{ $page->id }}, 'unpublish')" data-tooltip="Unpublish">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="6" y="5" width="4" height="14" rx="1"/><rect x="14" y="5" width="4" height="14" rx="1"/></svg>
                            </button>
                        @else
                            <button class="pg-icon-btn pg-icon-success" onclick="changePageStatus({{ $page->id }}, 'publish')" data-tooltip="Publish">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M6 4.5v15l13-7.5-13-7.5Z"/></svg>
                            </button>
                        @endif
                        @unless($page->is_homepage)
                            <button class="pg-icon-btn pg-icon-danger" onclick="deletePage({{ $page->id }})" data-tooltip="Delete">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0-1 14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2L4 6h16Z"/></svg>
                            </button>
                        @endunless
                    </div>
                </div>
            @empty
                <div class="text-center py-16">
                    <svg class="w-10 h-10 text-novaborder mx-auto mb-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2h9l3 3v17H6z"/><path d="M15 2v3h3M9 12h6M9 16h6"/></svg>
                    <p class="text-sm font-semibold text-novatext">No pages found</p>
                    <a href="{{ route('admin.pages.create') }}" class="pg-btn-nova pg-btn-primary px-4 py-2 text-xs font-semibold mt-4 inline-flex">Create First Page</a>
                </div>
            @endforelse
        </div>

        @if($pages->hasPages())
            <div class="flex flex-wrap items-center justify-between gap-3 mt-6 pt-5 border-t border-novaborder">
                <p class="text-xs text-novamuted">Showing {{ $pages->firstItem() }} to {{ $pages->lastItem() }} of {{ $pages->total() }} entries</p>
                {{ $pages->withQueryString()->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    document.querySelectorAll('.page-checkbox').forEach(cb => { cb.checked = selectAll.checked; });
    updateSelection();
}

function updateSelection() {
    const checkboxes = document.querySelectorAll('.page-checkbox:checked');
    const count = checkboxes.length;
    document.getElementById('selectedCount').textContent = count;
    document.getElementById('bulkActionsBar').classList.toggle('pg-visible', count > 0);
    document.getElementById('bulkDeleteBtn').classList.toggle('pg-show', count > 0);
}

function clearSelection() {
    document.querySelectorAll('.page-checkbox').forEach(cb => { cb.checked = false; });
    document.getElementById('selectAll').checked = false;
    updateSelection();
}

function bulkAction(action) {
    const checkboxes = document.querySelectorAll('.page-checkbox:checked');
    if (checkboxes.length === 0) { alert('Please select pages first'); return; }
    const pageIds = Array.from(checkboxes).map(cb => cb.value);
    if (confirm(`Are you sure you want to ${action} ${pageIds.length} page(s)?`)) {
        fetch('{{ route("admin.pages.bulk-action") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ action: action, page_ids: pageIds })
        })
        .then(response => response.json())
        .then(data => { if (data.success) { alert(data.message); location.reload(); } else { alert(data.message); } })
        .catch(error => { console.error('Error:', error); alert('An error occurred'); });
    }
}

function changePageStatus(pageId, action) {
    if (confirm(`Are you sure you want to ${action} this page?`)) {
        fetch('{{ route("admin.pages.bulk-action") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ action: action, page_ids: [pageId] })
        })
        .then(response => response.json())
        .then(data => { if (data.success) { alert(data.message); location.reload(); } else { alert(data.message); } })
        .catch(error => { console.error('Error:', error); alert('An error occurred'); });
    }
}

function deletePage(pageId) {
    if (confirm('Are you sure you want to delete this page? This action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `{{ url('admin/pages') }}/${pageId}`;
        form.innerHTML = `@csrf @method('DELETE')`;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endpush
