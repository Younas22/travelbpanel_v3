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
                <div class="cur-stat-icon cur-icon-blue">
                    <i class="bi bi-currency-exchange"></i>
                </div>
                <div>
                    <div class="cur-stat-label">Total</div>
                    <div class="cur-stat-val">{{ $stats['total'] }}</div>
                </div>
            </div>
            <div class="cur-stat-card">
                <div class="cur-stat-icon cur-icon-green">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div>
                    <div class="cur-stat-label">Active</div>
                    <div class="cur-stat-val">{{ $stats['active'] }}</div>
                </div>
            </div>
            <div class="cur-stat-card">
                <div class="cur-stat-icon cur-icon-red">
                    <i class="bi bi-x-circle"></i>
                </div>
                <div>
                    <div class="cur-stat-label">Inactive</div>
                    <div class="cur-stat-val">{{ $stats['inactive'] }}</div>
                </div>
            </div>
            <div class="cur-stat-card">
                <div class="cur-stat-icon cur-icon-amber">
                    <i class="bi bi-star-fill"></i>
                </div>
                <div>
                    <div class="cur-stat-label">Default</div>
                    <div class="cur-stat-val">{{ $stats['default'] }}</div>
                </div>
            </div>
        </div>

        {{-- Bulk Actions Bar --}}
        <div class="cur-bulk-bar cur-hidden" id="bulkActionsBar">
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
                        <th class="cur-col-check">
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
                                <a href="{{ route('admin.currencies.create') }}" class="cur-add-btn cur-add-btn-sm">
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
