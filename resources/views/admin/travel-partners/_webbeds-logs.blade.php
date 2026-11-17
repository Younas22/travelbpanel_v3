{{-- WebBeds API logs (public/webbeds-logs): one log per customer journey, search → booking. Expects $partner. --}}
<div class="tbp-webbeds-logs" id="wbLogs"
     data-list-url="{{ route('admin.travel-partners.webbeds-logs.index', $partner) }}"
     data-base-url="{{ url('admin/travel-partners/'.$partner->id.'/webbeds-logs') }}">

    <div class="tbp-webbeds-panel">
        <div class="tbp-webbeds-logs-toolbar">
            <div class="tbp-webbeds-search"><i class="bi bi-search"></i><input type="search" class="form-control" id="wbLogSearch" placeholder="Search booking ref, PNR, hotel ID, error…" autocomplete="off"></div>
            <select class="form-select" id="wbLogFilter">
                <option value="all">All logs</option>
                <option value="errors">With errors</option>
                <option value="bookings">Bookings only</option>
            </select>
            <button type="button" class="tbp-partner-edit-btn tbp-partner-edit-btn-outline" id="wbLogRefresh"><i class="bi bi-arrow-clockwise"></i> Refresh</button>
            <button type="button" class="tbp-partner-edit-btn tbp-partner-edit-btn-outline tbp-webbeds-logs-danger" id="wbLogDeleteAll"><i class="bi bi-trash"></i> Delete all</button>
        </div>

        <div class="tbp-partner-edit-hint" id="wbLogCount"></div>

        <div class="table-responsive tbp-webbeds-table-wrap">
            <table class="table tbp-webbeds-logs-table tbp-webbeds-table">
                <thead>
                    <tr>
                        <th>Last activity</th>
                        <th>Journey</th>
                        <th>Steps</th>
                        <th>Booking</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody id="wbLogRows"><tr><td colspan="6" class="tbp-partner-edit-hint">Loading…</td></tr></tbody>
            </table>
        </div>
        <p class="tbp-webbeds-secure-note"><i class="bi bi-shield-lock"></i> Card numbers, CVV and passwords are masked in the logs. The log folder is not publicly accessible — download from here.</p>
    </div>
</div>

<script>
(function () {
    const root = document.getElementById('wbLogs');
    if (!root) return;
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const headers = { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf };
    const rowsEl = document.getElementById('wbLogRows');
    const search = document.getElementById('wbLogSearch');
    const filter = document.getElementById('wbLogFilter');
    const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const when = ts => ts ? new Date(ts).toLocaleString() : '—';
    const size = b => b > 1048576 ? (b / 1048576).toFixed(1) + ' MB' : Math.max(1, Math.round((b || 0) / 1024)) + ' KB';

    function status(r) {
        if (r.booking_status === 'confirmed') return '<span class="tbp-webbeds-log-badge is-ok"><i class="bi bi-check-circle-fill"></i> Booked</span>';
        if (r.booking_status === 'failed') return '<span class="tbp-webbeds-log-badge is-error"><i class="bi bi-x-circle-fill"></i> Booking failed</span>';
        if (r.errors > 0) return `<span class="tbp-webbeds-log-badge is-warn">${r.errors} error(s)</span>`;
        return '<span class="tbp-webbeds-log-badge is-ok"><i class="bi bi-check-circle-fill"></i> OK</span>';
    }

    async function load() {
        const q = new URLSearchParams({ search: search.value.trim(), filter: filter.value });
        try {
            const r = await fetch(root.dataset.listUrl + '?' + q, { headers });
            const d = await r.json();
            document.getElementById('wbLogCount').textContent = `${d.total} log(s)` + (d.total > d.logs.length ? ` — showing latest ${d.logs.length}` : '');
            if (!d.logs.length) {
                rowsEl.innerHTML = `<tr><td colspan="6"><div class="tbp-webbeds-empty"><span class="tbp-webbeds-empty-icon"><i class="bi bi-file-earmark-text"></i></span><strong>${search.value.trim() || filter.value !== 'all' ? 'No matching logs' : 'No logs yet'}</strong><span>Logs appear after a WebBeds search or booking.</span></div></td></tr>`;
                return;
            }
            rowsEl.innerHTML = d.logs.map(r => `
                <tr class="${r.booking_status === 'failed' ? 'is-failed' : ''}">
                    <td>${esc(when(r.updated_at))}<div class="tbp-partner-edit-hint">${esc(size(r.size))} · ${r.env === 'pro' ? 'Live' : 'Sandbox'}</div></td>
                    <td><code>${esc(r.trace)}</code>${r.hotel_id ? `<div class="tbp-partner-edit-hint">Hotel ${esc(r.hotel_id)}</div>` : ''}</td>
                    <td>${(r.steps || []).map(s => `<span class="tbp-webbeds-log-step">${esc(s)}</span>`).join(' ')}</td>
                    <td>${r.booking_ref ? `Ref <strong>${esc(r.booking_ref)}</strong>` : '—'}${r.booking_pnr ? `<div class="tbp-partner-edit-hint">PNR ${esc(r.booking_pnr)}</div>` : ''}</td>
                    <td>${status(r)}${r.last_error ? `<div class="tbp-webbeds-log-error" title="${esc(r.last_error)}">${esc(r.last_error_step)}: ${esc(r.last_error)}</div>` : ''}</td>
                    <td class="text-end text-nowrap">
                        <a class="tbp-partner-edit-btn tbp-partner-edit-btn-outline btn-sm" href="${root.dataset.baseUrl}/${encodeURIComponent(r.trace)}/download" title="Download"><i class="bi bi-download"></i></a>
                        <button type="button" class="tbp-partner-edit-btn tbp-partner-edit-btn-outline btn-sm tbp-webbeds-logs-danger" data-delete="${esc(r.trace)}" title="Delete"><i class="bi bi-trash"></i></button>
                    </td>
                </tr>`).join('');
        } catch (e) {
            rowsEl.innerHTML = '<tr><td colspan="6" class="tbp-partner-edit-hint">Could not load logs.</td></tr>';
        }
    }

    async function remove(url, question) {
        if (!confirm(question)) return;
        const r = await fetch(url, { method: 'DELETE', headers });
        const d = await r.json().catch(() => ({}));
        if (!r.ok) alert(d.message || 'Delete failed.');
        load();
    }

    rowsEl.addEventListener('click', e => {
        const btn = e.target.closest('[data-delete]');
        if (btn) remove(`${root.dataset.baseUrl}/${encodeURIComponent(btn.dataset.delete)}`, 'Delete this log?');
    });
    document.getElementById('wbLogDeleteAll').addEventListener('click', () => remove(root.dataset.baseUrl, 'Delete ALL WebBeds logs? This cannot be undone.'));
    document.getElementById('wbLogRefresh').addEventListener('click', load);
    filter.addEventListener('change', load);
    let t;
    search.addEventListener('input', () => { clearTimeout(t); t = setTimeout(load, 300); });
    search.addEventListener('keydown', e => { if (e.key === 'Enter') e.preventDefault(); }); // inside the supplier form

    // Load when the Logs step is first opened
    const panel = root.closest('[data-step-panel]');
    if (!panel) { load(); return; }
    let loaded = false;
    const check = () => { if (!loaded && panel.classList.contains('is-active')) { loaded = true; load(); } };
    new MutationObserver(check).observe(panel, { attributes: true, attributeFilter: ['class'] });
    check();
})();
</script>
