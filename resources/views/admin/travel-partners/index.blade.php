@extends('admin.layouts.app')

@section('title', 'Travel Partner Management')

@section('content')
    <div class="content-area p-4">

        @php
            $icMap = [
                'flights'=>'bi-airplane','flight'=>'bi-airplane',
                'hotels'=>'bi-building','hotel'=>'bi-building','stay'=>'bi-building',
                'visa'=>'bi-passport','visas'=>'bi-passport',
                'transfers'=>'bi-car-front','transfer'=>'bi-car-front',
                'tours'=>'bi-map','tour'=>'bi-map',
                'umrah'=>'bi-moon-stars',
                'insurance'=>'bi-shield-check',
                'packages'=>'bi-box-seam','package'=>'bi-box-seam',
            ];

            // Theme map: light + dark pairs drawn from one consistent palette
            $themeMap = [
                'bi-airplane'     => ['bg'=>'#E6F1FB','text'=>'#0C447C','dbg'=>'#0c2f4d','dtext'=>'#7db8f0'],
                'bi-building'     => ['bg'=>'#EAF3DE','text'=>'#27500A','dbg'=>'#0a2e1a','dtext'=>'#6dd499'],
                'bi-passport'     => ['bg'=>'#FAEEDA','text'=>'#633806','dbg'=>'#2e1e05','dtext'=>'#f0b054'],
                'bi-car-front'    => ['bg'=>'#EEEDFE','text'=>'#3C3489','dbg'=>'#1e1553','dtext'=>'#b5adf5'],
                'bi-map'          => ['bg'=>'#FBEAF0','text'=>'#72243E','dbg'=>'#2e0a18','dtext'=>'#f4a8c4'],
                'bi-moon-stars'   => ['bg'=>'#EEF0F8','text'=>'#2D3480','dbg'=>'#0e1040','dtext'=>'#a0a8f5'],
                'bi-shield-check' => ['bg'=>'#E1F5EE','text'=>'#085041','dbg'=>'#04201a','dtext'=>'#7fd9bf'],
                'bi-box-seam'     => ['bg'=>'#FAECE7','text'=>'#712B13','dbg'=>'#2e1208','dtext'=>'#f5a280'],
                'bi-puzzle'       => ['bg'=>'#F1EFE8','text'=>'#444441','dbg'=>'#2c2c2a','dtext'=>'#b4b2a9'],
            ];

            // Avatar palette for manually-added partners (picked by name hash, not random)
            $avatarPalette = [
                ['bg'=>'#B5D4F4','text'=>'#0C447C'],
                ['bg'=>'#C0DD97','text'=>'#27500A'],
                ['bg'=>'#FAC775','text'=>'#633806'],
                ['bg'=>'#F4C0D1','text'=>'#72243E'],
                ['bg'=>'#CECBF6','text'=>'#3C3489'],
            ];
        @endphp

            <!-- ===== TAB BAR ===== -->
        <div class="tp-tabs" id="tp-sortable-tabs">
            <button class="tp-tab active tp-tab-fixed" onclick="switchTPTab('all', this)">
                <i class="bi bi-grid-1x2"></i>
                <span class="tp-tab-label">All Modules</span>
                <span class="tp-tab-count">{{ $modules->count() }}</span>
            </button>
            @foreach($modules as $module)
                @php $tabIcon = $icMap[strtolower($module->name)] ?? 'bi-puzzle'; @endphp
                <button class="tp-tab {{ $module->status !== 'active' ? 'tp-tab-inactive' : '' }}"
                        data-module-id="{{ $module->id }}"
                        onclick="switchTPTab('mod-{{ $module->id }}', this)">
                    <span class="tp-drag-grip"><span></span><span></span><span></span><span></span><span></span><span></span></span>
                    <i class="bi {{ $tabIcon }}"></i>
                    <span class="tp-tab-label">{{ $module->name }}</span>
                    <span class="tp-tab-status-dot {{ $module->status === 'active' ? 'tp-dot-active' : 'tp-dot-inactive' }}"></span>
                    <span class="tp-tab-count">{{ $module->partners_count }}</span>
                </button>
            @endforeach
        </div>

        <!-- ===== ALL MODULES TAB ===== -->
        <div id="tpane-all" class="tp-pane active">
            @if($modules->isEmpty())
                <div class="alert alert-info mt-3">
                    <i class="bi bi-info-circle me-2"></i>No modules found.
                    <a href="#" data-bs-toggle="modal" data-bs-target="#addModuleModal">Create one now</a>
                </div>
            @else
                @php $hasAnyPartner = false; @endphp
                <div class="tp-grid tp-grid-lg">
                    @foreach($modules as $module)
                        @foreach($module->partners as $partner)
                            @php
                                $hasAnyPartner = true;
                                $isManual = $partner->supplier_type == 'manual';
                                $avatar = $avatarPalette[crc32($partner->company_name) % count($avatarPalette)];
                            @endphp
                            <div class="tp-card {{ $isManual ? 'tp-manual' : '' }}"
                                 @if($isManual)
                                     data-bs-toggle="popover" data-bs-trigger="hover" data-bs-placement="top" data-bs-html="true"
                                 data-bs-content="<div class='manual-popover-content'><i class='bi bi-person-fill-gear text-warning me-1'></i> <strong>Your Manual Entries</strong><br><small>This section displays the data you have added directly. No third-party API is connected — all records here are managed by you.</small></div>"
                                @endif>

                                @if($isManual)
                                    <div class="tp-avatar" style="background:{{ $avatar['bg'] }}; color:{{ $avatar['text'] }}">
                                        <span class="tp-avatar-init">{{ strtoupper(substr($partner->company_name, 0, 2)) }}</span>
                                    </div>
                                @else
                                    <div class="tp-avatar">
                                        <img src="{{ asset('public/assets/images/partners/'.$partner->company_name.'.png') }}"
                                             alt="{{ $partner->company_name }}" onerror="this.style.display='none'">


                                    </div>
                                @endif

                                @php
                                    $modIcon  = $icMap[strtolower($module->name)] ?? 'bi-puzzle';
                                    $modTheme = $themeMap[$modIcon] ?? $themeMap['bi-puzzle'];
                                @endphp
                                <div class="tp-pinfo">
                                    <div class="tp-pname">{{ $isManual ? $module->name : ucwords(str_replace('_',' ',$partner->company_name)) }}</div>
                                    <div class="tp-ptype">
                                        {{ $isManual ? 'manual_entry' : ucwords(str_replace('_',' ',$partner->company_name)).' API' }}
                                        @if($isManual)
                                            <i class="bi bi-info-circle tp-info-ico" title="Added manually — no third-party API connected"></i>
                                        @endif
                                    </div>
                                    <div class="tp-mod-badge" style="background:{{ $modTheme['bg'] }};color:{{ $modTheme['text'] }};">
                                        <i class="bi {{ $modIcon }}"></i>
                                        {{ $module->name }}
                                    </div>
                                </div>

                                <div class="tp-pacts">
                                    <label class="tp-switch tp-switch-sm">
                                        <input type="checkbox" class="partner-switch-input" data-partner-id="{{ $partner->id }}" data-module-id="{{ $module->id }}"
                                               {{ $partner->status === 'active' ? 'checked' : '' }}
                                               onchange="togglePartnerStatus(event, {{ $partner->id }}, {{ $module->id }})">
                                        <span class="tp-switch-slider"></span>
                                    </label>
                                    @if($module->id != '3')
                                        <a href="{{ route('admin.travel-partners.edit', $partner->id) }}" class="tp-edit-btn" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @endforeach

                    @if(!$hasAnyPartner)
                        <div class="tp-empty">No partners added yet</div>
                    @endif
                </div>
            @endif
        </div>

        <!-- ===== INDIVIDUAL MODULE TABS ===== -->
        @foreach($modules as $module)
            @php
                $iconClass = $icMap[strtolower($module->name)] ?? 'bi-puzzle';
                $theme = $themeMap[$iconClass] ?? $themeMap['bi-puzzle'];
            @endphp
            <div id="tpane-mod-{{ $module->id }}" class="tp-pane">
                <div class="tp-mod-header"
                     style="--mod-bg:{{ $theme['bg'] }}; --mod-text:{{ $theme['text'] }}; --mod-dbg:{{ $theme['dbg'] }}; --mod-dtext:{{ $theme['dtext'] }}">
                    <div class="tp-mod-left">
                        <div class="tp-mod-icon">
                            <i class="bi {{ $iconClass }}"></i>
                        </div>
                        <div>
                            <div class="tp-mod-name">{{ $module->name }}</div>
                            <div class="tp-mod-sub">{{ $module->partners_count }} {{ $module->partners_count == 1 ? 'partner' : 'partners' }}</div>
                        </div>
                    </div>
                    <div class="tp-mod-right">
                        @if($module->status === 'active')
                            <span class="tp-status tp-status-active">Active</span>
                        @else
                            <span class="tp-status tp-status-inactive">Inactive</span>
                        @endif
                        <label class="tp-switch">
                            <input type="checkbox" class="module-switch-input" data-module-id="{{ $module->id }}"
                                   {{ $module->status === 'active' ? 'checked' : '' }}
                                   onchange="toggleModuleStatus(event, {{ $module->id }})">
                            <span class="tp-switch-slider"></span>
                        </label>
                    </div>
                </div>

                <div class="tp-grid mt-3">
                    @forelse($module->partners as $partner)
                        @php
                            $isManual = $partner->supplier_type == 'manual';
                            $avatar = $avatarPalette[crc32($partner->company_name) % count($avatarPalette)];
                        @endphp
                        <div class="tp-card {{ $isManual ? 'tp-manual' : '' }}"
                             @if($isManual)
                                 data-bs-toggle="popover" data-bs-trigger="hover" data-bs-placement="top" data-bs-html="true"
                             data-bs-content="<div class='manual-popover-content'><i class='bi bi-person-fill-gear text-warning me-1'></i> <strong>Your Manual Entries</strong><br><small>This section displays the data you have added directly. No third-party API is connected — all records here are managed by you.</small></div>"
                            @endif>

                            @if($isManual)
                                <div class="tp-avatar" style="background:{{ $avatar['bg'] }}; color:{{ $avatar['text'] }}">
                                    <span class="tp-avatar-init">{{ strtoupper(substr($partner->company_name, 0, 2)) }}</span>
                                </div>
                            @else
                                <div class="tp-avatar">
                                    <img src="{{ asset('public/assets/images/partners/'.$partner->company_name.'.png') }}"
                                         alt="{{ $partner->company_name }}" onerror="this.style.display='none'">


                                </div>
                            @endif

                            <div class="tp-pinfo">
                                <div class="tp-pname">{{ $isManual ? $module->name : $partner->company_name }}</div>
                                <div class="tp-ptype">
                                    {{ $isManual ? 'manual_entry' : 'API' }}
                                    @if($isManual)
                                        <i class="bi bi-info-circle tp-info-ico" title="Added manually — no third-party API connected"></i>
                                    @endif
                                </div>
                            </div>

                            <div class="tp-pacts">
                                <label class="tp-switch tp-switch-sm">
                                    <input type="checkbox" class="partner-switch-input" data-partner-id="{{ $partner->id }}" data-module-id="{{ $module->id }}"
                                           {{ $partner->status === 'active' ? 'checked' : '' }}
                                           onchange="togglePartnerStatus(event, {{ $partner->id }}, {{ $module->id }})">
                                    <span class="tp-switch-slider"></span>
                                </label>
                                @if($module->id != '3')
                                    <a href="{{ route('admin.travel-partners.edit', $partner->id) }}" class="tp-edit-btn" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="tp-empty">No partners added yet</div>
                    @endforelse
                </div>
            </div>
        @endforeach

        <!-- Add Module Modal -->
        <div class="modal fade" id="addModuleModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Create New Module</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="addModuleForm" class="modern-form">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Module Name</label>
                                <input type="text" name="name" class="form-control" placeholder="e.g., Flight, Hotel, Visa" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="3" placeholder="Module description..."></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" form="addModuleForm" class="btn btn-primary modern-btn"><i class="bi bi-check-lg"></i> Create Module</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Partner Modal -->
        <div class="modal fade" id="addPartnerModal" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Add New Partner</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="addPartnerForm" class="modern-form">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Company Name</label>
                                        <input type="text" name="company_name" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Module</label>
                                        <select name="module_id" class="form-select" required>
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
                                        <label class="form-label">Commission Rate (%)</label>
                                        <input type="number" name="commission_rate" class="form-control" min="0" max="100" step="0.1" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Status</label>
                                        <select name="status" class="form-select" required>
                                            <option value="active">Active</option>
                                            <option value="pending">Pending</option>
                                            <option value="suspended">Suspended</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" form="addPartnerForm" class="btn btn-primary modern-btn"><i class="bi bi-check-lg"></i> Add Partner</button>
                    </div>
                </div>
            </div>
        </div>

    </div>


    @push('scripts')
        <script>
            const BASE_URL = "{{ url('') }}";

            var _tpJustDragged = false;

            function switchTPTab(id, btn) {
                if (_tpJustDragged) return;
                document.querySelectorAll('.tp-pane').forEach(function(p) { p.classList.remove('active'); });
                document.querySelectorAll('.tp-tab').forEach(function(b) { b.classList.remove('active'); });
                document.getElementById('tpane-' + id).classList.add('active');
                btn.classList.add('active');
            }

            function toggleModuleStatus(event, moduleId) {
                event.stopPropagation();
                fetch(`${BASE_URL}/admin/modules/${moduleId}/update-status`, {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'X-Requested-With': 'XMLHttpRequest' },
                    body: JSON.stringify({})
                }).then(r => r.json()).then(data => { if (data.success) location.reload(); }).catch(e => console.error('Error:', e));
            }

            function togglePartnerStatus(event, partnerId, moduleId) {
                event.stopPropagation();
                fetch(`${BASE_URL}/admin/travel-partners/${partnerId}/toggle-status`, {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'X-Requested-With': 'XMLHttpRequest' },
                    body: JSON.stringify({ module_id: moduleId })
                }).then(r => r.json()).then(data => {
                    if (data.success) {
                        if (data.module_status_changed) {
                            const ms = document.querySelector(`.module-switch-input[data-module-id="${moduleId}"]`);
                            if (ms) ms.checked = data.new_module_status === 'active';
                        }
                        location.reload();
                    }
                }).catch(error => { console.error('Error:', error); event.target.checked = !event.target.checked; });
            }

            function suspendPartner(partnerId) {
                if (confirm('Are you sure you want to suspend this partner?')) {
                    fetch(`${BASE_URL}/admin/travel-partners/suspend/${partnerId}`, {
                        method: 'PATCH',
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'X-Requested-With': 'XMLHttpRequest' }
                    }).then(r => r.json()).then(data => { if (data.success) location.reload(); });
                }
            }

            function activatePartner(partnerId) {
                if (confirm('Are you sure you want to activate this partner?')) {
                    fetch(`${BASE_URL}/admin/travel-partners/activate/${partnerId}`, {
                        method: 'PATCH',
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'X-Requested-With': 'XMLHttpRequest' }
                    }).then(r => r.json()).then(data => { if (data.success) location.reload(); });
                }
            }

            function deletePartner(partnerId) {
                if (confirm('Are you sure you want to delete this partner? This action cannot be undone.')) {
                    fetch(`${BASE_URL}/admin/travel-partners/destroy/${partnerId}`, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'X-Requested-With': 'XMLHttpRequest' }
                    }).then(r => r.json()).then(data => { if (data.success) location.reload(); });
                }
            }

            document.getElementById('addModuleForm')?.addEventListener('submit', function(e) {
                e.preventDefault();
                fetch('{{ route("admin.modules.store") }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'X-Requested-With': 'XMLHttpRequest' },
                    body: new FormData(this)
                }).then(r => r.json()).then(data => { if (data.success) location.reload(); }).catch(e => console.error('Error:', e));
            });

            document.getElementById('addPartnerForm')?.addEventListener('submit', function(e) {
                e.preventDefault();
                fetch('{{ route("admin.travel-partners.store") }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'X-Requested-With': 'XMLHttpRequest' },
                    body: new FormData(this)
                }).then(r => r.json()).then(data => { if (data.success) location.reload(); }).catch(e => console.error('Error:', e));
            });

            const selectAllCheckbox = document.querySelector('.select-all-checkbox');
            if (selectAllCheckbox) {
                selectAllCheckbox.addEventListener('change', function() {
                    document.querySelectorAll('.row-checkbox').forEach(cb => { cb.checked = this.checked; });
                });
            }

            document.addEventListener('DOMContentLoaded', function() {
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
                tooltipTriggerList.map(function(el) { return new bootstrap.Tooltip(el); });

                var popoverTriggerList = [].slice.call(document.querySelectorAll('.tp-manual[data-bs-toggle="popover"]'));
                popoverTriggerList.map(function(el) {
                    return new bootstrap.Popover(el, { trigger: 'hover', html: true, placement: 'top', container: 'body' });
                });
            });

            // ===== MODULE TAB DRAG-AND-DROP =====
            document.addEventListener('DOMContentLoaded', function () {
                const container = document.getElementById('tp-sortable-tabs');
                if (!container) return;

                Sortable.create(container, {
                    animation: 150,
                    filter: '.tp-tab-fixed',
                    draggable: '.tp-tab:not(.tp-tab-fixed)',
                    ghostClass: 'sortable-ghost',
                    chosenClass: 'sortable-chosen',
                    onStart: function () { _tpJustDragged = false; },
                    onMove:  function () { _tpJustDragged = true; },
                    onEnd: function () {
                        // Reset flag after click event has fired
                        setTimeout(function () { _tpJustDragged = false; }, 50);
                        if (!_tpJustDragged) return;
                        const items = [];
                        container.querySelectorAll('.tp-tab[data-module-id]').forEach(function (btn, index) {
                            items.push({ id: parseInt(btn.dataset.moduleId), sort_order: index + 1 });
                        });

                        fetch('{{ route("admin.modules.reorder") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: JSON.stringify({ items: items }),
                        })
                        .then(r => r.json())
                        .then(data => {
                            if (data.success) showTPToast('Module order saved!', 'success');
                            else showTPToast('Error saving order', 'error');
                        })
                        .catch(() => showTPToast('Error saving order', 'error'));
                    }
                });
            });

            function showTPToast(msg, type) {
                const t = document.createElement('div');
                t.textContent = msg;
                t.style.cssText = 'position:fixed;bottom:20px;right:20px;z-index:9999;padding:10px 18px;border-radius:8px;font-size:13px;font-weight:500;color:#fff;background:' + (type === 'success' ? '#22c55e' : '#ef4444');
                document.body.appendChild(t);
                setTimeout(() => t.remove(), 2500);
            }
        </script>

        <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    @endpush
@endsection
