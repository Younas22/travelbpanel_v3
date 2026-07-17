{{-- resources/views/admin/currencies/index.blade.php --}}

@extends('admin.layouts.app')

@section('title', 'Currencies Management')

@section('content')

    <div class="container-fluid py-4">

        {{-- Page Header --}}
        <div class="cur-page-header mb-4">
            <div>
                <h4 class="cur-page-title"><i class="bi bi-currency-exchange"></i> Currencies</h4>
                <p class="cur-page-sub">Manage exchange rates, status and default currency</p>
            </div>
            <a href="{{ route('admin.currencies.create') }}" class="cur-add-btn">
                <i class="bi bi-plus-lg"></i> Add Currency
            </a>
        </div>

        {{-- Stats --}}
        <div class="cur-stats-row mb-4">
            <div class="cur-stat-card">
                <div class="cur-stat-icon" style="background:#eff6ff; color:#3b82f6">
                    <i class="bi bi-currency-exchange"></i>
                </div>
                <div>
                    <div class="cur-stat-label">Total</div>
                    <div class="cur-stat-val">{{ $stats['total'] }}</div>
                </div>
            </div>
            <div class="cur-stat-card">
                <div class="cur-stat-icon" style="background:#ecfdf5; color:#10b981">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div>
                    <div class="cur-stat-label">Active</div>
                    <div class="cur-stat-val">{{ $stats['active'] }}</div>
                </div>
            </div>
            <div class="cur-stat-card">
                <div class="cur-stat-icon" style="background:#fff1f2; color:#f43f5e">
                    <i class="bi bi-x-circle"></i>
                </div>
                <div>
                    <div class="cur-stat-label">Inactive</div>
                    <div class="cur-stat-val">{{ $stats['inactive'] }}</div>
                </div>
            </div>
            <div class="cur-stat-card">
                <div class="cur-stat-icon" style="background:#fffbeb; color:#f59e0b">
                    <i class="bi bi-star-fill"></i>
                </div>
                <div>
                    <div class="cur-stat-label">Default</div>
                    <div class="cur-stat-val">{{ $stats['default'] }}</div>
                </div>
            </div>
        </div>

        {{-- Bulk Actions Bar --}}
        <div class="cur-bulk-bar" id="bulkActionsBar" style="display:none">
            <span><strong><span id="selectedCount">0</span></strong> currencies selected</span>
            <div class="d-flex gap-2">
                <button class="cur-bulk-del" onclick="bulkAction('delete')"><i class="bi bi-trash"></i> Delete</button>
                <button class="cur-bulk-cancel" onclick="clearSelection()">Cancel</button>
            </div>
        </div>

        {{-- Table --}}
        <div class="cur-table-wrap">
            <div class="table-responsive">
                <table class="table cur-table mb-0">
                    <thead>
                    <tr>
                        <th style="width:40px">
                            <input type="checkbox" class="cur-check" id="selectAll" onchange="toggleSelectAll()">
                        </th>
                        <th>Currency</th>
                        <th>Country</th>
                        <th>Rate</th>
                        <th>Default</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse ($currencies as $currency)
                        <tr class="cur-row">
                            <td>
                                <input type="checkbox" class="cur-check page-checkbox"
                                       value="{{ $currency->id }}" onchange="updateSelection()">
                            </td>
                            <td>
                                <div class="cur-name-cell">
                                    <div class="cur-symbol-badge">
                                        {{ strtoupper(substr($currency->currency_name, 0, 3)) }}
                                    </div>
                                    <div>
                                        <div class="cur-name">{{ $currency->currency_name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="cur-country">{{ ucfirst($currency->currency_country) }}</span>
                            </td>
                            <td>
                                <span class="cur-rate">{{ $currency->currency_rate }}</span>
                            </td>
                            <td>
                                <label class="cur-toggle">
                                    <input type="checkbox" class="toggle-default"
                                           data-id="{{ $currency->id }}"
                                        {{ $currency->currency_default ? 'checked' : '' }}>
                                    <span class="cur-toggle-track"></span>
                                </label>
                            </td>
                            <td>
                                <label class="cur-toggle">
                                    <input type="checkbox" class="toggle-status"
                                           data-id="{{ $currency->id }}"
                                        {{ $currency->currency_status ? 'checked' : '' }}>
                                    <span class="cur-toggle-track"></span>
                                </label>
                            </td>
                            <td>
                                <div class="cur-actions">
                                    <a href="{{ route('admin.currencies.edit', $currency->id) }}"
                                       class="cur-action-btn cur-edit" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @unless($currency->currency_name == "USD")
                                        <button class="cur-action-btn cur-delete" title="Delete"
                                                onclick="deleteCurrceny({{ $currency->id }})">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @endunless
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="cur-empty">
                                <i class="bi bi-currency-exchange"></i>
                                <p>No currencies found</p>
                                <a href="{{ route('admin.currencies.create') }}" class="cur-add-btn" style="font-size:13px;padding:8px 18px">
                                    <i class="bi bi-plus-lg"></i> Add First Currency
                                </a>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            @if($currencies->hasPages())
                <div class="cur-pagination">
                    <div class="cur-page-info">
                        Showing {{ $currencies->firstItem() }}–{{ $currencies->lastItem() }} of {{ $currencies->total() }}
                    </div>
                    <nav>{{ $currencies->withQueryString()->links('pagination::bootstrap-5') }}</nav>
                </div>
            @endif
        </div>

    </div>

    @push('styles')
        <style>
            /* ===== PAGE HEADER ===== */
            .cur-page-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                flex-wrap: wrap;
                gap: 12px;
            }
            .cur-page-title {
                font-size: 20px;
                font-weight: 600;
                color: #0f172a;
                margin: 0;
                display: flex;
                align-items: center;
                gap: 8px;
            }
            .cur-page-title i { color: #3b82f6; }
            .cur-page-sub {
                font-size: 13px;
                color: #94a3b8;
                margin: 3px 0 0;
            }

            /* ===== ADD BUTTON ===== */
            .cur-add-btn {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                padding: 9px 20px;
                background: #3b82f6;
                color: #fff;
                font-size: 13px;
                font-weight: 500;
                border-radius: 8px;
                text-decoration: none;
                border: none;
                cursor: pointer;
                transition: background .15s, transform .15s;
            }
            .cur-add-btn:hover { background: #2563eb; color: #fff; transform: translateY(-1px); text-decoration: none; }

            /* ===== STATS ===== */
            .cur-stats-row {
                display: grid;
                grid-template-columns: repeat(4, 1fr);
                gap: 12px;
            }
            .cur-stat-card {
                background: #fff;
                border: 1px solid #e2e8f0;
                border-radius: 12px;
                padding: 1.1rem 1.25rem;
                display: flex;
                align-items: center;
                gap: 14px;
                transition: box-shadow .15s;
            }
            .cur-stat-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.06); }
            .cur-stat-icon {
                width: 46px; height: 46px;
                border-radius: 10px;
                display: flex; align-items: center; justify-content: center;
                font-size: 20px;
                flex-shrink: 0;
            }
            .cur-stat-label { font-size: 12px; color: #94a3b8; font-weight: 500; }
            .cur-stat-val { font-size: 22px; font-weight: 700; color: #0f172a; line-height: 1.2; }

            /* ===== BULK BAR ===== */
            .cur-bulk-bar {
                display: flex;
                align-items: center;
                justify-content: space-between;
                background: #eff6ff;
                border: 1px solid #bfdbfe;
                border-radius: 10px;
                padding: .75rem 1.1rem;
                margin-bottom: 1rem;
                font-size: 13px;
                color: #1e40af;
            }
            .cur-bulk-del {
                padding: 5px 14px; font-size: 12px; font-weight: 500;
                background: #fee2e2; color: #dc2626;
                border: 1px solid #fca5a5; border-radius: 6px;
                cursor: pointer; transition: all .15s;
            }
            .cur-bulk-del:hover { background: #dc2626; color: #fff; }
            .cur-bulk-cancel {
                padding: 5px 14px; font-size: 12px; font-weight: 500;
                background: #fff; color: #64748b;
                border: 1px solid #e2e8f0; border-radius: 6px;
                cursor: pointer; transition: all .15s;
            }
            .cur-bulk-cancel:hover { background: #f1f5f9; }

            /* ===== TABLE WRAP ===== */
            .cur-table-wrap {
                background: #fff;
                border: 1px solid #e2e8f0;
                border-radius: 12px;
                overflow: hidden;
            }
            .cur-table { margin: 0; }
            .cur-table thead tr {
                background: #f8fafc;
                border-bottom: 1.5px solid #e2e8f0;
            }
            .cur-table thead th {
                font-size: 11px;
                font-weight: 600;
                color: #64748b;
                text-transform: uppercase;
                letter-spacing: .06em;
                padding: 13px 16px;
                border: none;
                white-space: nowrap;
            }
            .cur-table tbody td {
                padding: 13px 16px;
                vertical-align: middle;
                border-bottom: 1px solid #f1f5f9;
                font-size: 13px;
                color: #334155;
            }
            .cur-row:last-child td { border-bottom: none; }
            .cur-row:hover td { background: #f8fafc; }

            /* ===== CHECKBOX ===== */
            .cur-check {
                width: 15px; height: 15px;
                border: 1.5px solid #cbd5e1;
                border-radius: 4px;
                cursor: pointer;
                accent-color: #3b82f6;
            }

            /* ===== CURRENCY NAME CELL ===== */
            .cur-name-cell {
                display: flex;
                align-items: center;
                gap: 10px;
            }
            .cur-symbol-badge {
                width: 38px; height: 38px;
                background: #eff6ff;
                color: #1d4ed8;
                border-radius: 8px;
                display: flex; align-items: center; justify-content: center;
                font-size: 11px;
                font-weight: 700;
                letter-spacing: .04em;
                flex-shrink: 0;
                border: 1px solid #bfdbfe;
            }
            .cur-name {
                font-size: 13px;
                font-weight: 600;
                color: #0f172a;
            }
            .cur-country { font-size: 13px; color: #64748b; }
            .cur-rate {
                font-size: 13px;
                font-weight: 600;
                color: #0f172a;
                font-variant-numeric: tabular-nums;
                background: #f8fafc;
                padding: 3px 10px;
                border-radius: 6px;
                border: 1px solid #e2e8f0;
                display: inline-block;
            }

            /* ===== TOGGLE ===== */
            .cur-toggle {
                position: relative;
                display: inline-block;
                width: 38px; height: 20px;
                margin: 0;
                cursor: pointer;
            }
            .cur-toggle input { opacity: 0; width: 0; height: 0; }
            .cur-toggle-track {
                position: absolute; inset: 0;
                background: #cbd5e1;
                border-radius: 20px;
                transition: .2s;
            }
            .cur-toggle-track:before {
                content: "";
                position: absolute;
                width: 14px; height: 14px;
                left: 3px; top: 3px;
                background: #fff;
                border-radius: 50%;
                transition: .2s;
                box-shadow: 0 1px 3px rgba(0,0,0,.2);
            }
            .cur-toggle input:checked + .cur-toggle-track { background: #22c55e; }
            .cur-toggle input:checked + .cur-toggle-track:before { transform: translateX(18px); }

            /* ===== ACTIONS ===== */
            .cur-actions { display: flex; gap: 5px; align-items: center; }
            .cur-action-btn {
                width: 30px; height: 30px;
                display: inline-flex; align-items: center; justify-content: center;
                border-radius: 7px;
                font-size: 13px;
                cursor: pointer;
                text-decoration: none;
                border: 1px solid #e2e8f0;
                background: #f8fafc;
                transition: all .15s;
            }
            .cur-edit { color: #3b82f6; }
            .cur-edit:hover { background: #3b82f6; color: #fff; border-color: #3b82f6; text-decoration: none; }
            .cur-delete { color: #ef4444; }
            .cur-delete:hover { background: #ef4444; color: #fff; border-color: #ef4444; }

            /* ===== EMPTY ===== */
            .cur-empty {
                text-align: center;
                padding: 3rem 1rem;
                color: #94a3b8;
            }
            .cur-empty i { font-size: 2.5rem; display: block; margin-bottom: .75rem; }
            .cur-empty p { font-size: 14px; margin-bottom: 1rem; }

            /* ===== PAGINATION ===== */
            .cur-pagination {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: .9rem 1.25rem;
                border-top: 1px solid #f1f5f9;
                flex-wrap: wrap;
                gap: 8px;
            }
            .cur-page-info { font-size: 12px; color: #94a3b8; }

            /* ===== RESPONSIVE ===== */
            @media (max-width: 1000px) { .cur-stats-row { grid-template-columns: repeat(2,1fr); } }
            @media (max-width: 576px)  { .cur-stats-row { grid-template-columns: 1fr 1fr; } .cur-page-header { flex-direction: column; align-items: flex-start; } }
        </style>
    @endpush

    <script>
        function toggleSelectAll() {
            const selectAll = document.getElementById('selectAll');
            document.querySelectorAll('.page-checkbox').forEach(cb => { cb.checked = selectAll.checked; });
            updateSelection();
        }

        function updateSelection() {
            const checked = document.querySelectorAll('.page-checkbox:checked');
            const count = checked.length;
            document.getElementById('selectedCount').textContent = count;
            document.getElementById('bulkActionsBar').style.display = count > 0 ? 'flex' : 'none';
        }

        function clearSelection() {
            document.querySelectorAll('.page-checkbox').forEach(cb => { cb.checked = false; });
            document.getElementById('selectAll').checked = false;
            updateSelection();
        }

        function bulkAction(action) {
            const checkboxes = document.querySelectorAll('.page-checkbox:checked');
            if (checkboxes.length === 0) { alert('Please select currencies first'); return; }
            const ids = Array.from(checkboxes).map(cb => cb.value);
            if (confirm(`Are you sure you want to ${action} ${ids.length} currency(s)?`)) {
                fetch('', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    body: JSON.stringify({ action: action, page_ids: ids })
                }).then(r => r.json()).then(data => {
                    if (data.success) { location.reload(); } else { alert(data.message); }
                }).catch(() => alert('An error occurred'));
            }
        }

        function deleteCurrceny(cid) {
            if (confirm('Are you sure you want to delete this currency? This action cannot be undone.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `{{ url('/admin/currencies') }}/${cid}`;
                form.innerHTML = `@csrf @method('DELETE')`;
                document.body.appendChild(form);
                form.submit();
            }
        }

        document.querySelectorAll('.toggle-default').forEach(function(sw) {
            sw.addEventListener('change', function() {
                let id = this.dataset.id;
                let val = this.checked ? 1 : 0;
                if (val && !confirm('Set this currency as default?')) { this.checked = false; return; }
                fetch('{{ route("admin.currencies.toggle_default") }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    body: JSON.stringify({ id: id, currency_default: val })
                }).then(r => r.json()).then(data => {
                    if (data.success) { location.reload(); } else { alert(data.message); this.checked = !val; }
                }).catch(() => { alert('An error occurred'); this.checked = !val; });
            });
        });

        document.querySelectorAll('.toggle-status').forEach(function(sw) {
            sw.addEventListener('change', function() {
                let id = this.dataset.id;
                let val = this.checked ? 1 : 0;
                if (val && !confirm('Set this currency as active?')) { this.checked = false; return; }
                fetch('{{ route("admin.currencies.toggle_status") }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    body: JSON.stringify({ id: id, currency_active: val })
                }).then(r => r.json()).then(data => {
                    if (data.success) { location.reload(); } else { alert(data.message); this.checked = !val; }
                }).catch(() => { alert('An error occurred'); this.checked = !val; });
            });
        });
    </script>

@endsection
