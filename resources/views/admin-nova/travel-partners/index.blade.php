@extends('admin-nova.layouts.app')

@section('title', 'Supplier Integrations')

@section('content')
@php
    $icMap = [
        'flights'=>'bi-airplane','flight'=>'bi-airplane',
        'hotels'=>'bi-building','hotel'=>'bi-building','stay'=>'bi-building',
        'visa'=>'bi-file-earmark-text','visas'=>'bi-file-earmark-text',
        'transfers'=>'bi-car-front','transfer'=>'bi-car-front','cars'=>'bi-car-front',
        'tours'=>'bi-map','tour'=>'bi-map',
        'umrah'=>'bi-moon',
        'insurance'=>'bi-shield-check',
        'packages'=>'bi-box-seam','package'=>'bi-box-seam',
    ];
    $badgeKeys = ['flight', 'stay', 'tours', 'visa', 'umrah'];
    $avatarPalette = [
        'flight' => ['bg' => '#EAF1FF', 'text' => '#2563EB'],
        'stay'   => ['bg' => '#E8F8EF', 'text' => '#16A34A'],
        'tours'  => ['bg' => '#E8F8EF', 'text' => '#16A34A'],
        'visa'   => ['bg' => '#E8F8EF', 'text' => '#16A34A'],
        'umrah'  => ['bg' => '#E8F8EF', 'text' => '#16A34A'],
        'default'=> ['bg' => '#F1F5F9', 'text' => '#334155'],
    ];

    // All numbers are aggregated from the same $modules->partners collection
    // TravelPartnerController::index already provides.
    $tpAllPartners = $modules->flatMap(fn($m) => $m->partners);
    $tpTotalSuppliers = $tpAllPartners->count();
    $tpConnectedApis = $tpAllPartners->where('supplier_type', '!=', 'manual')->where('status', 'active')->count();
    $tpManualProviders = $tpAllPartners->where('supplier_type', 'manual')->count();
    $tpInactiveProviders = $tpAllPartners->where('status', '!=', 'active')->count();

    $cardData = function ($partner, $module) use ($icMap, $badgeKeys, $avatarPalette) {
        $isManual = $partner->supplier_type == 'manual';
        $modName = strtolower($module->name);
        $modIcon = $icMap[$modName] ?? 'bi-puzzle';
        $modKey = in_array($modName, $badgeKeys) ? $modName : 'default';
        $avatar = $avatarPalette[$modKey] ?? $avatarPalette['default'];
        $displayName = $isManual ? $module->name : ucwords(str_replace('_', ' ', $partner->company_name));
        $isConnected = !$isManual && $partner->status === 'active';
        $logoFile = 'public/assets/images/partners/'.$partner->company_name.'.png';
        $logoUrl = !$isManual && is_file(base_path($logoFile)) ? asset($logoFile) : null;
        return compact('partner', 'module', 'isManual', 'modIcon', 'modKey', 'avatar', 'displayName', 'isConnected', 'logoUrl');
    };
@endphp

<div class="tbp-supplier-integrations-page" id="tpPage">

    {{-- ============ HEADER ============ --}}
    <div class="tbp-supplier-integrations-header">
        <div>
            <h1 class="tbp-supplier-integrations-title">Supplier Integrations</h1>
            <p class="tbp-supplier-integrations-subtitle">Manage all travel APIs and manual booking providers from one place.</p>
        </div>
        <div class="tbp-supplier-integrations-search">
            <i class="bi bi-search"></i>
            <input type="text" id="tpSearch" placeholder="Search supplier..." autocomplete="off">
        </div>
    </div>

    {{-- ============ SUMMARY CARDS ============ --}}
    <div class="tbp-supplier-integrations-stats">
        <div class="tbp-supplier-integrations-stat-card tbp-supplier-integrations-tone-blue">
            <div class="tbp-supplier-integrations-stat-icon"><i class="bi bi-stack"></i></div>
            <div><div class="tbp-supplier-integrations-stat-label">Total Suppliers</div><div class="tbp-supplier-integrations-stat-value">{{ number_format($tpTotalSuppliers) }}</div></div>
            <span class="tbp-supplier-integrations-stat-badge"><i class="bi bi-list-ul"></i></span>
        </div>
        <div class="tbp-supplier-integrations-stat-card tbp-supplier-integrations-tone-green">
            <div class="tbp-supplier-integrations-stat-icon"><i class="bi bi-link-45deg"></i></div>
            <div><div class="tbp-supplier-integrations-stat-label">Connected APIs</div><div class="tbp-supplier-integrations-stat-value">{{ number_format($tpConnectedApis) }}</div></div>
            <span class="tbp-supplier-integrations-stat-badge"><i class="bi bi-check-lg"></i></span>
        </div>
        <div class="tbp-supplier-integrations-stat-card tbp-supplier-integrations-tone-sky">
            <div class="tbp-supplier-integrations-stat-icon"><i class="bi bi-file-earmark-text"></i></div>
            <div><div class="tbp-supplier-integrations-stat-label">Manual Providers</div><div class="tbp-supplier-integrations-stat-value">{{ number_format($tpManualProviders) }}</div></div>
            <span class="tbp-supplier-integrations-stat-badge"><i class="bi bi-pencil"></i></span>
        </div>
        <div class="tbp-supplier-integrations-stat-card tbp-supplier-integrations-tone-red">
            <div class="tbp-supplier-integrations-stat-icon"><i class="bi bi-slash-circle"></i></div>
            <div><div class="tbp-supplier-integrations-stat-label">Inactive Providers</div><div class="tbp-supplier-integrations-stat-value">{{ number_format($tpInactiveProviders) }}</div></div>
            <span class="tbp-supplier-integrations-stat-badge"><i class="bi bi-x-lg"></i></span>
        </div>
    </div>

    {{-- ============ CATEGORIES + STATUS FILTER + SORT ============ --}}
    <div class="tbp-supplier-integrations-filters">
        {{-- Module tabs stay drag-reorderable (Sortable.js + admin.modules.reorder). --}}
        <div class="tbp-supplier-integrations-categories" id="tp-sortable-tabs">
            <button type="button" class="tbp-supplier-integrations-category tp-tab tp-tab-fixed is-active" data-category="all">
                <i class="bi bi-grid"></i> All <span class="tbp-supplier-integrations-category-count">{{ $tpTotalSuppliers }}</span>
            </button>
            @foreach($modules as $module)
                <button type="button" class="tbp-supplier-integrations-category tp-tab {{ $module->status !== 'active' ? 'is-inactive-module' : '' }}"
                        data-category="{{ $module->id }}" data-module-id="{{ $module->id }}"
                        title="{{ $module->status !== 'active' ? 'Module inactive' : '' }}">
                    <i class="bi {{ $icMap[strtolower($module->name)] ?? 'bi-puzzle' }}"></i> {{ $module->name }}
                    <span class="tbp-supplier-integrations-category-count">{{ $module->partners_count }}</span>
                </button>
            @endforeach
        </div>
        <div class="tbp-supplier-integrations-status-row">
            <div class="tbp-supplier-integrations-status-filter">
                <span class="tbp-supplier-integrations-status-label">Filter by Status:</span>
                <button type="button" class="tbp-supplier-integrations-chip is-active" data-filter="all">All</button>
                <button type="button" class="tbp-supplier-integrations-chip" data-filter="manual">Manual</button>
                <button type="button" class="tbp-supplier-integrations-chip" data-filter="api">API</button>
                <button type="button" class="tbp-supplier-integrations-chip" data-filter="connected">Connected</button>
                <button type="button" class="tbp-supplier-integrations-chip" data-filter="disconnected">Disconnected</button>
            </div>
        </div>
    </div>

    {{-- Module status control, shown when a single module tab is selected. --}}
    @foreach($modules as $module)
        <div class="tbp-supplier-integrations-module-bar" data-module-bar="{{ $module->id }}">
            <div>
                <div class="tbp-supplier-integrations-module-bar-name">{{ $module->name }} module</div>
                <div class="tbp-supplier-integrations-module-bar-meta">{{ $module->partners_count }} {{ Str::plural('supplier', $module->partners_count) }} · {{ $module->status === 'active' ? 'Active' : 'Inactive' }}</div>
            </div>
            <label class="tbp-supplier-integrations-switch" title="Enable / disable this module">
                <input type="checkbox" class="module-switch-input" data-module-id="{{ $module->id }}"
                       {{ $module->status === 'active' ? 'checked' : '' }}
                       onchange="toggleModuleStatus(event, {{ $module->id }})">
                <span></span>
            </label>
        </div>
    @endforeach

    {{-- ============ SUPPLIER CARDS ============ --}}
    <div class="tbp-supplier-integrations-grid" id="tpGrid">
        @foreach($modules as $module)
            @foreach($module->partners as $partner)
                @php extract($cardData($partner, $module)); @endphp
                @include('admin-nova.travel-partners._card')
            @endforeach
        @endforeach
        <div class="tbp-supplier-integrations-empty" id="tpEmpty" @if($tpTotalSuppliers) hidden @endif>
            No suppliers match the selected filters.
        </div>
    </div>

    {{-- ============ ADD MODULE MODAL (Bootstrap modal shell kept for
         real functionality, content restyled) ============ --}}
    <div class="modal fade" id="addModuleModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" style="border-radius:1.5rem; font-family:'Plus Jakarta Sans', sans-serif;">
                <div class="modal-header" style="border-bottom:1px solid #E5E7EB;">
                    <h5 class="modal-title" style="font-weight:700;">Create New Module</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addModuleForm">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" style="font-size:.8rem; font-weight:600;">Module Name</label>
                            <input type="text" name="name" class="form-control" style="border-radius:9999px;" placeholder="e.g., Flight, Hotel, Visa" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" style="font-size:.8rem; font-weight:600;">Description</label>
                            <textarea name="description" class="form-control" style="border-radius:1rem;" rows="3" placeholder="Module description..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" style="font-size:.8rem; font-weight:600;">Status</label>
                            <select name="status" class="form-select" style="border-radius:9999px;">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer" style="border-top:1px solid #E5E7EB;">
                    <button type="button" data-bs-dismiss="modal"
                            style="display:inline-flex; align-items:center; justify-content:center; border-radius:9999px; padding:10px 20px; font-size:13px; font-weight:600; font-family:'Plus Jakarta Sans',sans-serif; background:#F7F8FC; color:#000; border:1px solid #E5E7EB; cursor:pointer;">Cancel</button>
                    <button type="submit" form="addModuleForm"
                            style="display:inline-flex; align-items:center; justify-content:center; border-radius:9999px; padding:10px 20px; font-size:13px; font-weight:600; font-family:'Plus Jakarta Sans',sans-serif; background:#2563EB; color:#fff; border:1px solid #2563EB; cursor:pointer;">Create Module</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ============ ADD PARTNER MODAL ============ --}}
    <div class="modal fade" id="addPartnerModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content" style="border-radius:1.5rem; font-family:'Plus Jakarta Sans', sans-serif;">
                <div class="modal-header" style="border-bottom:1px solid #E5E7EB;">
                    <h5 class="modal-title" style="font-weight:700;">Add New Integration</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addPartnerForm">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label" style="font-size:.8rem; font-weight:600;">Company Name</label>
                                    <input type="text" name="company_name" class="form-control" style="border-radius:9999px;" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label" style="font-size:.8rem; font-weight:600;">Module</label>
                                    <select name="module_id" class="form-select" style="border-radius:9999px;" required>
                                        <option value="">Select a module</option>
                                        @foreach($modules as $module)
                                            <option value="{{ $module->id }}">{{ $module->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label" style="font-size:.8rem; font-weight:600;">Commission Rate (%)</label>
                                    <input type="number" name="commission_rate" class="form-control" style="border-radius:9999px;" min="0" max="100" step="0.1" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label" style="font-size:.8rem; font-weight:600;">Status</label>
                                    <select name="status" class="form-select" style="border-radius:9999px;" required>
                                        <option value="active">Active</option>
                                        <option value="pending">Pending</option>
                                        <option value="suspended">Suspended</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer" style="border-top:1px solid #E5E7EB;">
                    <button type="button" data-bs-dismiss="modal"
                            style="display:inline-flex; align-items:center; justify-content:center; border-radius:9999px; padding:10px 20px; font-size:13px; font-weight:600; font-family:'Plus Jakarta Sans',sans-serif; background:#F7F8FC; color:#000; border:1px solid #E5E7EB; cursor:pointer;">Cancel</button>
                    <button type="submit" form="addPartnerForm"
                            style="display:inline-flex; align-items:center; justify-content:center; border-radius:9999px; padding:10px 20px; font-size:13px; font-weight:600; font-family:'Plus Jakarta Sans',sans-serif; background:#2563EB; color:#fff; border:1px solid #2563EB; cursor:pointer;">Add Integration</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const BASE_URL = "{{ url('') }}";
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;
    const tpState = { category: 'all', filter: 'all', query: '', sort: 'name-asc' };
    let tpJustDragged = false;

    const tpGrid = document.getElementById('tpGrid');
    const tpCards = () => Array.from(tpGrid.querySelectorAll('.tbp-supplier-integrations-card'));

    function tpApply() {
        let visible = 0;
        tpCards().forEach(card => {
            const ok = (tpState.category === 'all' || card.dataset.moduleId === tpState.category)
                && (tpState.filter === 'all'
                    || (tpState.filter === 'manual' && card.dataset.supplierType === 'manual')
                    || (tpState.filter === 'api' && card.dataset.supplierType === 'api')
                    || (tpState.filter === 'connected' && card.dataset.connected === '1')
                    || (tpState.filter === 'disconnected' && card.dataset.supplierType === 'api' && card.dataset.connected === '0'))
                && (!tpState.query || (card.dataset.name || '').includes(tpState.query));
            card.classList.toggle('is-hidden', !ok);
            if (ok) visible++;
        });
        document.getElementById('tpEmpty').hidden = visible > 0;
        document.querySelectorAll('[data-module-bar]').forEach(bar => bar.classList.toggle('is-visible', bar.dataset.moduleBar === tpState.category));
    }

    function tpSort() {
        const [key, dir] = tpState.sort.split('-');
        const sign = dir === 'asc' ? 1 : -1;
        const sorted = tpCards().sort((a, b) => {
            if (key === 'name') return sign * a.dataset.name.localeCompare(b.dataset.name);
            const va = parseFloat(a.dataset[key]) || 0, vb = parseFloat(b.dataset[key]) || 0;
            return sign * (va - vb) || a.dataset.name.localeCompare(b.dataset.name);
        });
        const empty = document.getElementById('tpEmpty');
        sorted.forEach(c => tpGrid.insertBefore(c, empty));
    }

    document.querySelectorAll('#tp-sortable-tabs .tbp-supplier-integrations-category').forEach(btn => btn.addEventListener('click', () => {
        if (tpJustDragged) return;
        document.querySelectorAll('#tp-sortable-tabs .tbp-supplier-integrations-category').forEach(b => b.classList.toggle('is-active', b === btn));
        tpState.category = btn.dataset.category;
        tpApply();
    }));
    document.querySelectorAll('.tbp-supplier-integrations-chip').forEach(chip => chip.addEventListener('click', () => {
        document.querySelectorAll('.tbp-supplier-integrations-chip').forEach(c => c.classList.toggle('is-active', c === chip));
        tpState.filter = chip.dataset.filter;
        tpApply();
    }));
    document.getElementById('tpSearch').addEventListener('input', e => { tpState.query = e.target.value.toLowerCase().trim(); tpApply(); });

    function toggleModuleStatus(event, moduleId) {
        event.stopPropagation();
        fetch(`${BASE_URL}/admin/modules/${moduleId}/update-status`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({})
        }).then(r => r.json()).then(data => { if (data.success) location.reload(); }).catch(e => console.error('Error:', e));
    }

    function togglePartnerStatus(event, partnerId, moduleId) {
        event.stopPropagation();
        fetch(`${BASE_URL}/admin/travel-partners/${partnerId}/toggle-status`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ module_id: moduleId })
        }).then(r => r.json()).then(data => { if (data.success) location.reload(); })
          .catch(error => { console.error('Error:', error); event.target.checked = !event.target.checked; });
    }

    document.getElementById('addModuleForm')?.addEventListener('submit', function (e) {
        e.preventDefault();
        fetch('{{ route("admin.modules.store") }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' }, body: new FormData(this) })
            .then(r => r.json()).then(data => { if (data.success) location.reload(); }).catch(e => console.error('Error:', e));
    });
    document.getElementById('addPartnerForm')?.addEventListener('submit', function (e) {
        e.preventDefault();
        fetch('{{ route("admin.travel-partners.store") }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' }, body: new FormData(this) })
            .then(r => r.json()).then(data => { if (data.success) location.reload(); }).catch(e => console.error('Error:', e));
    });

    function showTPToast(msg, type) {
        const t = document.createElement('div');
        t.textContent = msg;
        t.style.cssText = 'position:fixed;bottom:20px;right:20px;z-index:9999;padding:10px 18px;border-radius:9999px;font-size:13px;font-weight:600;color:#fff;background:' + (type === 'success' ? '#22C55E' : '#EF4444');
        document.body.appendChild(t);
        setTimeout(() => t.remove(), 2500);
    }

    document.addEventListener('DOMContentLoaded', function () {
        const container = document.getElementById('tp-sortable-tabs');
        if (container && window.Sortable) {
            Sortable.create(container, {
                animation: 150,
                filter: '.tp-tab-fixed',
                draggable: '.tp-tab:not(.tp-tab-fixed)',
                ghostClass: 'sortable-ghost',
                onStart: () => { tpJustDragged = false; },
                onMove: () => { tpJustDragged = true; },
                onEnd: function () {
                    setTimeout(() => { tpJustDragged = false; }, 50);
                    const items = [];
                    container.querySelectorAll('.tp-tab[data-module-id]').forEach((btn, index) => items.push({ id: parseInt(btn.dataset.moduleId), sort_order: index + 1 }));
                    fetch('{{ route("admin.modules.reorder") }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' },
                        body: JSON.stringify({ items })
                    }).then(r => r.json()).then(data => showTPToast(data.success ? 'Module order saved!' : 'Error saving order', data.success ? 'success' : 'error'))
                      .catch(() => showTPToast('Error saving order', 'error'));
                }
            });
        }
    });

    tpSort();
    tpApply();
</script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>
@endpush
