{{-- WebBeds city import: hotels per city into database/webbeds.db, refreshed monthly. Expects $partner. --}}
@php
    $wbImporter = app(\App\Services\WebbedsCityImporter::class);
    $wbCronUrl = route('cron.webbeds.city-import', $wbImporter->cronToken());
@endphp

<div class="tbp-webbeds-import" id="wbImport"
     data-stats-url="{{ route('admin.travel-partners.webbeds-cities.stats', $partner) }}"
     data-cities-url="{{ route('admin.travel-partners.webbeds-cities.index', $partner) }}"
     data-batch-url="{{ route('admin.travel-partners.webbeds-cities.run-batch', $partner) }}"
     data-token-url="{{ route('admin.travel-partners.webbeds-cities.regenerate-token', $partner) }}"
     data-import-url="{{ url('admin/travel-partners/'.$partner->id.'/webbeds-cities') }}">

    <div class="tbp-webbeds-import-stats">
        <div class="tbp-webbeds-import-stat"><span>Total cities</span><strong data-wb="total_cities">—</strong></div>
        <div class="tbp-webbeds-import-stat is-green"><span>Imported (<span data-wb="month_label">this month</span>)</span><strong data-wb="imported_month">—</strong></div>
        <div class="tbp-webbeds-import-stat is-amber"><span>Pending</span><strong data-wb="pending_month">—</strong></div>
        <div class="tbp-webbeds-import-stat is-red"><span>Failed</span><strong data-wb="failed_month">—</strong></div>
        <div class="tbp-webbeds-import-stat"><span>Hotels in database</span><strong data-wb="total_hotels">—</strong></div>
    </div>

    <div class="tbp-webbeds-import-progress">
        <div class="tbp-webbeds-import-progress-head">
            <span>Monthly import progress</span>
            <strong><span data-wb="progress">0</span>%</strong>
        </div>
        <div class="tbp-webbeds-import-bar"><div class="tbp-webbeds-import-bar-fill" id="wbBar" style="width:0%"></div></div>
        <div class="tbp-partner-edit-hint" id="wbLastRun">Last run: —</div>
    </div>

    <div class="tbp-webbeds-import-actions">
        <button type="button" class="tbp-partner-edit-btn tbp-partner-edit-btn-primary" id="wbStartBtn"><i class="bi bi-play-fill"></i> <span>Start import</span></button>
        <button type="button" class="tbp-partner-edit-btn tbp-partner-edit-btn-outline" id="wbStopBtn" hidden><i class="bi bi-stop-fill"></i> Stop</button>
        <span class="tbp-partner-edit-hint" id="wbRunStatus">Imports run in batches. You can leave this page — the cron job continues automatically.</span>
    </div>

    <div class="tbp-webbeds-import-cron">
        <div class="tbp-webbeds-import-cron-title"><i class="bi bi-clock-history"></i> Monthly auto-import (cron job)</div>
        <p class="tbp-partner-edit-hint">Add this link to your server's cron. Each call imports cities for ~50 seconds. When every city has been imported for the month it does nothing, and on the 1st of every month all cities are re-imported with the latest data.</p>
        <label class="tbp-webbeds-import-label">Cron URL</label>
        <div class="tbp-webbeds-import-copy">
            <input type="text" readonly id="wbCronUrl" value="{{ $wbCronUrl }}" class="form-control">
            <button type="button" class="tbp-partner-edit-btn tbp-partner-edit-btn-outline" data-copy="#wbCronUrl"><i class="bi bi-clipboard"></i> Copy</button>
            <button type="button" class="tbp-partner-edit-btn tbp-partner-edit-btn-outline" id="wbRegenBtn" title="Create a new secret link (the old one stops working)"><i class="bi bi-arrow-repeat"></i></button>
        </div>
        <label class="tbp-webbeds-import-label">Crontab line (every minute — recommended)</label>
        <div class="tbp-webbeds-import-copy">
            <input type="text" readonly id="wbCronLine" value='* * * * * curl -s --max-time 300 "{{ $wbCronUrl }}" > /dev/null 2>&1' class="form-control">
            <button type="button" class="tbp-partner-edit-btn tbp-partner-edit-btn-outline" data-copy="#wbCronLine"><i class="bi bi-clipboard"></i> Copy</button>
        </div>
        <label class="tbp-webbeds-import-label">Or, without a URL (server shell access)</label>
        <div class="tbp-webbeds-import-copy">
            <input type="text" readonly id="wbCronCli" value='* * * * * cd {{ base_path() }} && php artisan webbeds:import-cities > /dev/null 2>&1' class="form-control">
            <button type="button" class="tbp-partner-edit-btn tbp-partner-edit-btn-outline" data-copy="#wbCronCli"><i class="bi bi-clipboard"></i> Copy</button>
        </div>
    </div>

    <div class="tbp-partner-edit-import-list-header">
        <h6>Cities</h6>
        <span class="tbp-partner-edit-hint">Green = imported this month. Outdated = imported in an earlier month, will be refreshed.</span>
    </div>
    <div class="tbp-webbeds-import-filters">
        <input type="search" id="wbSearch" class="form-control" placeholder="Search city, country or code…">
        <select id="wbStatus" class="form-select">
            <option value="all">All cities</option>
            <option value="imported">Imported this month</option>
            <option value="pending">Never imported</option>
            <option value="outdated">Outdated</option>
            <option value="failed">Failed</option>
        </select>
    </div>
    <div class="tbp-partner-edit-import-table-wrap">
        <table class="tbp-partner-edit-import-table">
            <thead><tr><th>City</th><th>Country</th><th>Code</th><th>Status</th><th>Hotels</th><th>Imported</th><th></th></tr></thead>
            <tbody id="wbCitiesBody"><tr><td colspan="7" class="tbp-partner-edit-import-empty">Loading…</td></tr></tbody>
        </table>
    </div>
    <div class="tbp-webbeds-import-pager">
        <span class="tbp-partner-edit-hint" id="wbPagerInfo"></span>
        <div>
            <button type="button" class="tbp-partner-edit-btn tbp-partner-edit-btn-outline" id="wbPrev"><i class="bi bi-chevron-left"></i></button>
            <button type="button" class="tbp-partner-edit-btn tbp-partner-edit-btn-outline" id="wbNext"><i class="bi bi-chevron-right"></i></button>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const root = document.getElementById('wbImport');
    if (!root) return;
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const headers = { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf };
    const esc = s => { const d = document.createElement('div'); d.textContent = s ?? ''; return d.innerHTML; };
    const fmt = n => Number(n || 0).toLocaleString();
    const state = { page: 1, pages: 1, search: '', status: 'all', running: false };
    const $ = id => document.getElementById(id);

    function utcToLocal(ts) {
        if (!ts) return '—';
        const d = new Date(ts.replace(' ', 'T') + 'Z');
        return isNaN(d) ? ts : d.toLocaleString();
    }

    async function loadStats() {
        const r = await fetch(root.dataset.statsUrl, { headers });
        const { stats } = await r.json();
        root.querySelectorAll('[data-wb]').forEach(el => {
            const v = stats[el.dataset.wb];
            el.textContent = typeof v === 'number' && el.dataset.wb !== 'progress' ? fmt(v) : (v ?? '—');
        });
        $('wbBar').style.width = Math.min(100, stats.progress) + '%';
        const lr = stats.last_run;
        $('wbLastRun').textContent = lr ? `Last run (${lr.trigger}): ${lr.finished_at} — ${lr.message}` : 'Last run: never';
        return stats;
    }

    const badge = {
        imported: '<span class="tbp-partner-edit-status-badge tbp-partner-edit-status-success">Imported</span>',
        pending: '<span class="tbp-partner-edit-status-badge tbp-webbeds-import-badge-pending">Pending</span>',
        outdated: '<span class="tbp-partner-edit-status-badge tbp-webbeds-import-badge-outdated">Outdated</span>',
        failed: '<span class="tbp-partner-edit-status-badge tbp-partner-edit-status-failed">Failed</span>',
    };

    async function loadCities() {
        const q = new URLSearchParams({ search: state.search, status: state.status, page: state.page });
        const r = await fetch(root.dataset.citiesUrl + '?' + q, { headers });
        const d = await r.json();
        state.pages = d.pages;
        $('wbCitiesBody').innerHTML = d.data.length ? d.data.map(c => `
            <tr>
                <td><strong>${esc(c.name)}</strong></td>
                <td>${esc(c.countryName)}</td>
                <td>${esc(c.code)}</td>
                <td title="${esc(c.message || '')}">${badge[c.state] || esc(c.state)}</td>
                <td>${c.hotels_saved === null ? '—' : fmt(c.hotels_saved)}</td>
                <td>${utcToLocal(c.fetched_at)}</td>
                <td><button type="button" class="tbp-partner-edit-btn tbp-partner-edit-btn-outline tbp-webbeds-import-city-btn" data-code="${esc(c.code)}">${c.state === 'pending' ? 'Import' : 'Re-import'}</button></td>
            </tr>`).join('') : '<tr><td colspan="7" class="tbp-partner-edit-import-empty">No cities match.</td></tr>';
        $('wbPagerInfo').textContent = `${fmt(d.total)} cities · page ${d.page} of ${fmt(d.pages)}`;
        $('wbPrev').disabled = d.page <= 1;
        $('wbNext').disabled = d.page >= d.pages;
    }

    let searchTimer;
    $('wbSearch').addEventListener('input', e => { clearTimeout(searchTimer); searchTimer = setTimeout(() => { state.search = e.target.value.trim(); state.page = 1; loadCities(); }, 300); });
    $('wbStatus').addEventListener('change', e => { state.status = e.target.value; state.page = 1; loadCities(); });
    $('wbPrev').addEventListener('click', () => { if (state.page > 1) { state.page--; loadCities(); } });
    $('wbNext').addEventListener('click', () => { if (state.page < state.pages) { state.page++; loadCities(); } });

    $('wbCitiesBody').addEventListener('click', async e => {
        const btn = e.target.closest('.tbp-webbeds-import-city-btn');
        if (!btn) return;
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-arrow-repeat tbp-partner-edit-spin"></i>';
        try {
            const r = await fetch(`${root.dataset.importUrl}/${encodeURIComponent(btn.dataset.code)}/import`, { method: 'POST', headers });
            const d = await r.json();
            $('wbRunStatus').textContent = d.message || (d.success ? 'Imported.' : 'Import failed.');
        } catch (err) {
            $('wbRunStatus').textContent = 'Import failed: ' + err.message;
        }
        await Promise.all([loadStats(), loadCities()]);
    });

    function setRunning(on) {
        state.running = on;
        $('wbStartBtn').hidden = on;
        $('wbStopBtn').hidden = !on;
    }

    $('wbStartBtn').addEventListener('click', async () => {
        setRunning(true);
        while (state.running) {
            $('wbRunStatus').innerHTML = '<i class="bi bi-arrow-repeat tbp-partner-edit-spin"></i> Importing next batch…';
            let d;
            try {
                const r = await fetch(root.dataset.batchUrl, { method: 'POST', headers });
                d = await r.json();
            } catch (err) {
                $('wbRunStatus').textContent = 'Batch failed: ' + err.message;
                break;
            }
            $('wbRunStatus').textContent = d.message || '';
            await Promise.all([loadStats(), loadCities()]);
            if (d.complete || (!d.success && !d.running)) break;
            if (d.running) await new Promise(res => setTimeout(res, 10000));
        }
        setRunning(false);
    });
    $('wbStopBtn').addEventListener('click', () => { state.running = false; $('wbRunStatus').textContent = 'Stopping after the current batch…'; });

    root.querySelectorAll('[data-copy]').forEach(btn => btn.addEventListener('click', async () => {
        const input = root.querySelector(btn.dataset.copy);
        try { await navigator.clipboard.writeText(input.value); } catch (e) { input.select(); document.execCommand('copy'); }
        btn.innerHTML = '<i class="bi bi-check-lg"></i> Copied';
        setTimeout(() => { btn.innerHTML = '<i class="bi bi-clipboard"></i> Copy'; }, 1500);
    }));

    $('wbRegenBtn').addEventListener('click', async () => {
        if (!confirm('Create a new cron link? The current link will stop working until you update your cron job.')) return;
        const r = await fetch(root.dataset.tokenUrl, { method: 'POST', headers });
        const d = await r.json();
        const old = $('wbCronUrl').value;
        $('wbCronUrl').value = d.cron_url;
        $('wbCronLine').value = $('wbCronLine').value.replace(old, d.cron_url);
    });

    loadStats();
    loadCities();
})();
</script>
@endpush
