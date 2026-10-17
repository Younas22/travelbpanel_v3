@extends('admin-nova.layouts.app')

@section('title', 'Currencies Management')

@push('styles')
@include('admin-nova.currencies._styles')
@endpush

@section('content')
<div id="crPage" class="tt-fade-in font-jakarta">

    {{-- ============ HEADER ============ --}}
    <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
        <div>
            <h1 class="text-lg font-bold text-novatext">Currencies</h1>
            <p class="text-xs text-novamuted mt-1">Manage exchange rates, status and default currency</p>
        </div>
        <a href="{{ route('admin.currencies.create') }}" class="cr-btn-nova cr-btn-primary px-4 py-2.5 text-xs font-semibold">
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
            Add Currency
        </a>
    </div>

    {{-- ============ KPI CARDS ============ --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
        <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-3">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-medium text-novamuted">Total</p>
                <div class="w-7 h-7 rounded-full bg-novablue text-white flex items-center justify-center flex-shrink-0">
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M9 12h6M12 9v6"/></svg>
                </div>
            </div>
            <span class="block text-lg font-bold text-novatext">{{ $stats['total'] }}</span>
        </div>
        <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-3">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-medium text-novamuted">Active</p>
                <div class="w-7 h-7 rounded-full bg-novasuccess text-white flex items-center justify-center flex-shrink-0">
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12.75 11.25 15 15 9.75"/></svg>
                </div>
            </div>
            <span class="block text-lg font-bold text-novatext">{{ $stats['active'] }}</span>
        </div>
        <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-3">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-medium text-novamuted">Inactive</p>
                <div class="w-7 h-7 rounded-full bg-novadanger text-white flex items-center justify-center flex-shrink-0">
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                </div>
            </div>
            <span class="block text-lg font-bold text-novatext">{{ $stats['inactive'] }}</span>
        </div>
        <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-3">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-medium text-novamuted">Default</p>
                <div class="w-7 h-7 rounded-full bg-novawarning text-white flex items-center justify-center flex-shrink-0">
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="m12 2.5 3 6.5 7 .8-5.2 4.8L18.2 21 12 17.3 5.8 21l1.4-6.4L2 9.8 9 9l3-6.5Z"/></svg>
                </div>
            </div>
            <span class="block text-lg font-bold text-novatext">{{ $stats['default'] }}</span>
        </div>
    </div>

    {{-- ============ CURRENCYLAYER API ============ --}}
    <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 sm:p-5 mb-4">
        <h2 class="text-sm font-bold text-novatext mb-1">CurrencyLayer API</h2>
        <p class="text-xs text-novamuted mb-3">
            To update currency exchange rates automatically, please create an account at
            <a href="https://currencylayer.com/" target="_blank" rel="noopener noreferrer" class="text-novablue font-semibold hover:underline">CurrencyLayer</a>,
            choose a suitable plan, and obtain your API Access Key. Enter your key below and click
            <strong class="text-novatext">Update Currency</strong> to fetch the latest exchange rates.
            @if($currencylayerLastUpdated)
                <span class="block mt-1 text-[11px] text-novamuted">Last updated: {{ $currencylayerLastUpdated }}</span>
            @endif
        </p>

        <div id="currencylayerAlert" class="text-xs font-medium rounded-lg px-3 py-2 mb-3" style="display: none;"></div>

        <div class="flex flex-wrap items-start gap-3">
            <div class="flex-1 min-w-[240px]">
                <input type="password"
                       id="currencylayerApiKey"
                       class="w-full text-xs rounded-lg border border-novaborder px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-novablue/30"
                       placeholder="CurrencyLayer Access Key"
                       value="{{ $currencylayerApiKey }}"
                       autocomplete="off">
                <p class="text-[11px] text-novadanger mt-1" id="currencylayerApiKeyError" style="display: none;"></p>
            </div>
            <button type="button" class="cr-btn-nova cr-btn-primary px-4 py-2.5 text-xs font-semibold" id="updateCurrenciesBtn" onclick="updateExchangeRates()">
                <span id="updateCurrenciesBtnIcon">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 2v6h-6M3 22v-6h6M21 8a9 9 0 0 0-15-4.9L3 8M3 16a9 9 0 0 0 15 4.9L21 16"/></svg>
                </span>
                <span id="updateCurrenciesBtnText">Update Currencies</span>
            </button>
            <a href="https://currencylayer.com/" target="_blank" rel="noopener noreferrer"
               class="cr-btn-nova px-4 py-2.5 text-xs font-semibold" style="color:#2563EB; border-color:#2563EB;">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><path d="M15 3h6v6"/><path d="M10 14 21 3"/></svg>
                Get API Key
            </a>
        </div>
    </div>

    {{-- ============ BULK ACTIONS BAR ============ --}}
    <div class="cr-bulk-bar tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 mb-4 items-center justify-between" id="bulkActionsBar">
        <span class="text-xs text-novatext"><strong><span id="selectedCount">0</span></strong> currencies selected</span>
        <div class="flex items-center gap-2">
            <button class="cr-btn-nova cr-btn-danger px-4 py-2 text-xs font-semibold" onclick="bulkAction('delete')">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0-1 14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2L4 6h16Z"/></svg>
                Delete
            </button>
            <button class="cr-btn-nova px-4 py-2 text-xs font-semibold" onclick="clearSelection()">Cancel</button>
        </div>
    </div>

    {{-- ============ TABLE ============ --}}
    <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 sm:p-5">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                <tr class="border-b border-novaborder">
                    <th class="text-left py-2.5 px-3"><input type="checkbox" id="selectAll" onchange="toggleSelectAll()"></th>
                    <th class="text-left text-[10px] font-semibold uppercase tracking-wide text-novamuted py-2.5 px-3">Currency</th>
                    <th class="text-left text-[10px] font-semibold uppercase tracking-wide text-novamuted py-2.5 px-3">Country</th>
                    <th class="text-left text-[10px] font-semibold uppercase tracking-wide text-novamuted py-2.5 px-3">Rate</th>
                    <th class="text-left text-[10px] font-semibold uppercase tracking-wide text-novamuted py-2.5 px-3">Default</th>
                    <th class="text-left text-[10px] font-semibold uppercase tracking-wide text-novamuted py-2.5 px-3">Status</th>
                    <th class="text-right text-[10px] font-semibold uppercase tracking-wide text-novamuted py-2.5 px-3">Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($currencies as $currency)
                    <tr class="tt-row border-b border-novaborder last:border-0">
                        <td class="py-3 px-3"><input type="checkbox" class="page-checkbox" value="{{ $currency->id }}" onchange="updateSelection()"></td>
                        <td class="py-3 px-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-9 h-9 rounded-full bg-blue-50 text-novablue flex items-center justify-center text-[10px] font-bold flex-shrink-0">
                                    {{ strtoupper(substr($currency->currency_name, 0, 3)) }}
                                </div>
                                <span class="font-semibold text-novatext">{{ $currency->currency_name }}</span>
                            </div>
                        </td>
                        <td class="py-3 px-3 text-novamuted">{{ ucfirst($currency->currency_country) }}</td>
                        <td class="py-3 px-3 font-semibold text-novatext">{{ $currency->currency_rate }}</td>
                        <td class="py-3 px-3">
                            <label class="cr-switch">
                                <input type="checkbox" class="toggle-default" data-id="{{ $currency->id }}" {{ $currency->currency_default ? 'checked' : '' }}>
                                <span class="cr-slider"></span>
                            </label>
                        </td>
                        <td class="py-3 px-3">
                            <label class="cr-switch">
                                <input type="checkbox" class="toggle-status" data-id="{{ $currency->id }}" {{ $currency->currency_status ? 'checked' : '' }}>
                                <span class="cr-slider"></span>
                            </label>
                        </td>
                        <td class="py-3 px-3">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('admin.currencies.edit', $currency->id) }}" class="cr-icon-btn" data-tooltip="Edit">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M16.474 5.408 18.592 7.526M4 20l1.11-3.92a2 2 0 0 1 .53-.9l9.9-9.9a1.5 1.5 0 0 1 2.12 0l1.06 1.06a1.5 1.5 0 0 1 0 2.12l-9.9 9.9a2 2 0 0 1-.9.53L4 20Z"/></svg>
                                </a>
                                @unless($currency->currency_name == "USD")
                                    <button class="cr-icon-btn cr-icon-danger" data-tooltip="Delete" onclick="deleteCurrceny({{ $currency->id }})">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0-1 14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2L4 6h16Z"/></svg>
                                    </button>
                                @endunless
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-16">
                            <svg class="w-10 h-10 text-novaborder mx-auto mb-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M9 12h6M12 9v6"/></svg>
                            <p class="text-sm font-semibold text-novatext">No currencies found</p>
                            <a href="{{ route('admin.currencies.create') }}" class="cr-btn-nova cr-btn-primary px-4 py-2 text-xs font-semibold mt-3 inline-flex">Add First Currency</a>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($currencies->hasPages())
            <div class="flex flex-wrap items-center justify-between gap-3 mt-6 pt-5 border-t border-novaborder">
                <p class="text-xs text-novamuted">Showing {{ $currencies->firstItem() }}–{{ $currencies->lastItem() }} of {{ $currencies->total() }}</p>
                {{ $currencies->withQueryString()->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>

@push('scripts')
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
        document.getElementById('bulkActionsBar').classList.toggle('cr-visible', count > 0);
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

    function showCurrencylayerAlert(type, message) {
        const alertBox = document.getElementById('currencylayerAlert');
        const classesByType = {
            success: 'bg-green-50 text-novasuccess',
            danger: 'bg-red-50 text-novadanger'
        };
        alertBox.className = `text-xs font-medium rounded-lg px-3 py-2 mb-3 ${classesByType[type] || ''}`;
        alertBox.textContent = message;
        alertBox.style.display = 'block';
    }

    function setApiKeyError(message) {
        const input = document.getElementById('currencylayerApiKey');
        const error = document.getElementById('currencylayerApiKeyError');
        if (message) {
            input.classList.add('border-novadanger');
            error.textContent = message;
            error.style.display = 'block';
        } else {
            input.classList.remove('border-novadanger');
            error.textContent = '';
            error.style.display = 'none';
        }
    }

    function updateExchangeRates() {
        const btn = document.getElementById('updateCurrenciesBtn');
        const btnIcon = document.getElementById('updateCurrenciesBtnIcon');
        const btnText = document.getElementById('updateCurrenciesBtnText');
        const apiKey = document.getElementById('currencylayerApiKey').value.trim();

        document.getElementById('currencylayerAlert').style.display = 'none';
        setApiKeyError(null);

        if (!apiKey) {
            setApiKeyError('CurrencyLayer access key is required.');
            return;
        }
        if (apiKey.length < 10) {
            setApiKeyError('Please enter a valid CurrencyLayer access key.');
            return;
        }

        btn.disabled = true;
        btn.classList.add('opacity-60', 'pointer-events-none');
        btnIcon.innerHTML = '<svg class="w-3.5 h-3.5 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9" stroke-opacity=".25"/><path d="M21 12a9 9 0 0 0-9-9"/></svg>';
        btnText.textContent = 'Updating...';

        fetch('{{ route("admin.currencies.update_exchange_rates") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ currencylayer_api_key: apiKey })
        })
            .then(async (r) => {
                const data = await r.json().catch(() => ({}));
                return { ok: r.ok, status: r.status, data };
            })
            .then(({ ok, status, data }) => {
                if (ok && data.success) {
                    showCurrencylayerAlert('success', data.message || 'Exchange rates updated successfully.');
                    setTimeout(() => location.reload(), 1200);
                    return;
                }

                if (status === 422 && data.errors && data.errors.currencylayer_api_key) {
                    setApiKeyError(data.errors.currencylayer_api_key[0]);
                    return;
                }

                showCurrencylayerAlert('danger', data.message || 'Failed to update exchange rates.');
            })
            .catch(() => {
                showCurrencylayerAlert('danger', 'An error occurred while contacting the server.');
            })
            .finally(() => {
                btn.disabled = false;
                btn.classList.remove('opacity-60', 'pointer-events-none');
                btnIcon.innerHTML = '<svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 2v6h-6M3 22v-6h6M21 8a9 9 0 0 0-15-4.9L3 8M3 16a9 9 0 0 0 15 4.9L21 16"/></svg>';
                btnText.textContent = 'Update Currencies';
            });
    }

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
@endpush
@endsection
