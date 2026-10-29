@extends('admin-nova.layouts.app')

@section('title', 'Currencies Management')

@section('content')
@php
    $cards = [
        ['Total Currencies', 'bi-list-ul', 'is-blue', $stats['total']],
        ['Active', 'bi-check2-circle', 'is-green', $stats['active']],
        ['Inactive', 'bi-x-circle', 'is-red', $stats['inactive']],
        ['Default', 'bi-star', 'is-amber', $stats['default']],
    ];
@endphp
<div class="tbp-admin-page" id="crPage">
    <header class="tbp-admin-header">
        <div><h1>Currencies</h1><p>Manage exchange rates, status and default currency</p></div>
        <a href="{{ route('admin.currencies.create') }}" class="tbp-admin-btn is-primary"><i class="bi bi-plus-lg"></i> Add Currency</a>
    </header>

    <div class="tbp-admin-stats">
        @foreach($cards as [$label, $icon, $tone, $value])
            <div class="tbp-admin-stat"><span class="tbp-admin-stat-icon {{ $tone }}"><i class="bi {{ $icon }}"></i></span><div><small>{{ $label }}</small><strong>{{ number_format($value) }}</strong></div></div>
        @endforeach
    </div>

    <section class="tbp-admin-card tbp-admin-api">
        <div class="tbp-admin-api-text">
            <h2>CurrencyLayer API</h2>
            <p>To update exchange rates automatically, create an account at <a href="https://currencylayer.com/" target="_blank" rel="noopener noreferrer">CurrencyLayer</a>, choose a suitable plan and get your API Access Key. Enter the key below and click <strong>Update Currencies</strong> to fetch the latest rates.</p>
            @if($currencylayerLastUpdated)<small><i class="bi bi-clock-history"></i> Last updated: {{ $currencylayerLastUpdated }}</small>@endif
        </div>
        <div id="currencylayerAlert" class="tbp-admin-alert" style="display: none;"></div>
        <div class="tbp-admin-api-row">
            <div class="tbp-admin-field is-wide">
                <span class="tbp-admin-control"><i class="bi bi-key"></i>
                    <input type="password" id="currencylayerApiKey" placeholder="CurrencyLayer Access Key" value="{{ $currencylayerApiKey }}" autocomplete="off" aria-label="CurrencyLayer access key"></span>
                <small class="tbp-admin-error" id="currencylayerApiKeyError" style="display: none;"></small>
            </div>
            <button type="button" class="tbp-admin-btn is-primary" id="updateCurrenciesBtn" onclick="updateExchangeRates()">
                <span id="updateCurrenciesBtnIcon"><i class="bi bi-arrow-repeat"></i></span>
                <span id="updateCurrenciesBtnText">Update Currencies</span>
            </button>
            <a href="https://currencylayer.com/" target="_blank" rel="noopener noreferrer" class="tbp-admin-btn is-outline"><i class="bi bi-box-arrow-up-right"></i> Get API Key</a>
        </div>
    </section>

    <div class="tbp-admin-bulk" id="bulkActionsBar">
        <span><strong id="selectedCount">0</strong> currencies selected</span>
        <div>
            <button type="button" class="tbp-admin-btn is-danger" onclick="bulkAction('delete')"><i class="bi bi-trash"></i> Delete</button>
            <button type="button" class="tbp-admin-btn" onclick="clearSelection()">Cancel</button>
        </div>
    </div>

    <section class="tbp-admin-card">
        <header class="tbp-admin-card-head"><h2>All currencies <span>{{ number_format($currencies->total()) }} total</span></h2></header>
        <table class="tbp-admin-table">
            <thead><tr><th class="is-check"><input type="checkbox" id="selectAll" onchange="toggleSelectAll()" aria-label="Select all currencies"></th><th>Currency</th><th>Country</th><th class="is-num">Rate</th><th>Default</th><th>Status</th><th class="is-actions">Actions</th></tr></thead>
            <tbody>
            @forelse ($currencies as $currency)
                <tr>
                    <td class="is-check"><input type="checkbox" class="page-checkbox" value="{{ $currency->id }}" onchange="updateSelection()" aria-label="Select {{ $currency->currency_name }}"></td>
                    <td data-label="Currency"><div class="tbp-admin-person"><span class="tbp-admin-code">{{ strtoupper(substr($currency->currency_name, 0, 3)) }}</span><strong>{{ $currency->currency_name }}</strong></div></td>
                    <td data-label="Country">{{ ucfirst($currency->currency_country) }}</td>
                    <td data-label="Rate" class="is-num"><strong>{{ $currency->currency_rate }}</strong></td>
                    <td data-label="Default"><label class="tbp-admin-switch"><input type="checkbox" class="toggle-default" data-id="{{ $currency->id }}" @checked($currency->currency_default) aria-label="Default currency"><i></i></label></td>
                    <td data-label="Status"><label class="tbp-admin-switch"><input type="checkbox" class="toggle-status" data-id="{{ $currency->id }}" @checked($currency->currency_status) aria-label="Active"><i></i></label></td>
                    <td class="is-actions">
                        <div class="tbp-admin-actions">
                            <a href="{{ route('admin.currencies.edit', $currency->id) }}" title="Edit"><i class="bi bi-pencil"></i></a>
                            @unless($currency->currency_name == "USD")
                                <button type="button" class="is-danger" title="Delete" onclick="deleteCurrceny({{ $currency->id }})"><i class="bi bi-trash"></i></button>
                            @endunless
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="tbp-admin-empty"><i class="bi bi-currency-exchange"></i><strong>No currencies found</strong><a href="{{ route('admin.currencies.create') }}" class="tbp-admin-btn is-primary">Add First Currency</a></td></tr>
            @endforelse
            </tbody>
        </table>
        @include('admin-nova.partials._tbp-pagination', ['paginator' => $currencies])
    </section>
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

    // There's no bulk endpoint — delete each selected currency through the
    // existing single-delete route (DELETE /admin/currencies/{id}).
    function bulkAction(action) {
        const checkboxes = document.querySelectorAll('.page-checkbox:checked');
        if (checkboxes.length === 0) { alert('Please select currencies first'); return; }
        const ids = Array.from(checkboxes).map(cb => cb.value);
        if (!confirm(`Are you sure you want to ${action} ${ids.length} currency(s)?`)) return;
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        Promise.all(ids.map(id => {
            const body = new FormData();
            body.append('_method', 'DELETE');
            body.append('_token', token);
            return fetch(`{{ url('/admin/currencies') }}/${id}`, { method: 'POST', body, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        })).then(() => location.reload()).catch(() => alert('An error occurred'));
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
        alertBox.className = `tbp-admin-alert is-${type}`;
        alertBox.textContent = message;
        alertBox.style.display = 'block';
    }

    function setApiKeyError(message) {
        const input = document.getElementById('currencylayerApiKey');
        const error = document.getElementById('currencylayerApiKeyError');
        if (message) {
            input.classList.add('is-invalid');
            error.textContent = message;
            error.style.display = 'block';
        } else {
            input.classList.remove('is-invalid');
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
        btnIcon.innerHTML = '<svg class="tbp-admin-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9" stroke-opacity=".25"/><path d="M21 12a9 9 0 0 0-9-9"/></svg>';
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
                btnIcon.innerHTML = '<i class="bi bi-arrow-repeat"></i>';
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
