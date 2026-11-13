{{-- WebBeds city import: hotels per city into database/webbeds.db, refreshed monthly. Expects $partner. --}}
@php
    $wbImporter = app(\App\Services\WebbedsCityImporter::class);
    $wbCronUrl = route('cron.webbeds.city-import', $wbImporter->cronToken());
@endphp

<div class="tbp-webbeds-import" id="wbImport"
     data-stats-url="{{ route('admin.travel-partners.webbeds-cities.stats', $partner) }}"
     data-cities-url="{{ route('admin.travel-partners.webbeds-cities.index', $partner) }}"
     data-batch-url="{{ route('admin.travel-partners.webbeds-cities.run-batch', $partner) }}"
     data-start-url="{{ route('admin.travel-partners.webbeds-cities.start', $partner) }}"
     data-stop-url="{{ route('admin.travel-partners.webbeds-cities.stop', $partner) }}"
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
        <span class="tbp-partner-edit-hint" id="wbRunStatus">Start runs the import on the server through the cron job — you can leave this page. Stop pauses the cron import.</span>
    </div>

    <div class="tbp-webbeds-import-live" id="wbLive" hidden>
        <div class="tbp-webbeds-import-live-head">
            <span class="tbp-webbeds-import-pulse"></span>
            <span>Importing <strong id="wbLiveCity">…</strong> <span class="tbp-partner-edit-hint" id="wbLiveCountry"></span></span>
            <span class="tbp-webbeds-import-live-trigger" id="wbLiveTrigger"></span>
        </div>
        <div class="tbp-webbeds-import-live-meta">
            <span>This batch: <strong id="wbLiveDone">0</strong> cities</span>
            <span><strong id="wbLiveHotels">0</strong> hotels saved</span>
            <span><strong id="wbLiveFailed">0</strong> failed</span>
            <span><strong id="wbLiveElapsed">0</strong>s</span>
        </div>
        <div class="tbp-partner-edit-hint" id="wbLiveLast"></div>
    </div>

    <div class="tbp-webbeds-import-cron">
        <div class="tbp-webbeds-import-cron-title"><i class="bi bi-clock-history"></i> Monthly auto-import (cron job)
            <span class="tbp-webbeds-cron-badge" id="wbCronBadge" data-state="never">Checking…</span></div>
        {{-- Live cron health: filled from stats.cron (heartbeat written by every cron / console run) --}}
        <div class="tbp-webbeds-cron-status" id="wbCronStatus" hidden>
            <div id="wbCronInfo"></div>
            <div class="tbp-webbeds-cron-error" id="wbCronError" hidden><i class="bi bi-exclamation-octagon"></i> <span></span></div>
        </div>
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
        renderLive(stats);
        const lr = stats.last_run;
        $('wbLastRun').textContent = lr ? `Last run (${lr.trigger}): ${lr.finished_at} — ${lr.message}` : 'Last run: never';
        renderCron(stats.cron || {});
        return stats;
    }

    // Cron health (server cron job) for the admin
    function renderCron(c) {
        const labels = { active: 'Running', error: 'Error', stopped: 'Not running', never: 'Not detected', paused: 'Stopped' };
        const badge = $('wbCronBadge');
        badge.dataset.state = c.state || 'never';
        badge.textContent = labels[c.state] || labels.never;

        const ago = m => m == null ? '' : (m < 1 ? 'just now' : (m < 60 ? `${m} min ago` : (m < 1440 ? `${Math.floor(m / 60)} h ago` : `${Math.floor(m / 1440)} day(s) ago`)));
        const how = c.trigger === 'console' ? 'php artisan command' : 'cron URL';
        let info;
        if (c.state === 'never') info = 'No cron call has been received yet. Add one of the cron lines below in cPanel → Cron Jobs (every minute).';
        else if (c.state === 'paused') info = `Import is stopped by admin — the cron job is still calling (last call ${ago(c.minutes_ago)}) but imports nothing. Click "Start import" to resume.`;
        else if (c.state === 'stopped') info = `The cron job is not running — last call ${ago(c.minutes_ago)} (${utcToLocal(c.last_at)}, via ${how}). Check the cron job in cPanel.`;
        else info = `Last call ${ago(c.minutes_ago)} via ${how}${c.message ? ' — ' + c.message : ''}`;
        $('wbCronInfo').textContent = info;

        // Current error, else the most recent one (with when it happened); also an old/wrong cron link
        let err = '';
        if (c.bad_token_at) err = `The cron job is calling an old cron link (${utcToLocal(c.bad_token_at)}) — it was rejected. Copy the Cron URL below into cPanel again.`;
        else if (c.state === 'error' && c.error) err = `Last cron run failed: ${c.error}`;
        else if (c.last_error && c.last_error_at && c.state !== 'never') err = `Last cron error (${utcToLocal(c.last_error_at)}): ${c.last_error}`;
        const box = $('wbCronError');
        box.hidden = !err;
        box.classList.toggle('is-old', !c.bad_token_at && c.state !== 'error');
        box.querySelector('span').textContent = err;
        $('wbCronStatus').hidden = false;
    }

    let pollTimer = null;
    function startPolling() {
        if (pollTimer) return;
        pollTimer = setInterval(() => { loadStats(); loadCities(); }, 3000);
    }
    function stopPolling() { clearInterval(pollTimer); pollTimer = null; }

    function renderLive(stats) {
        const cur = stats.current;
        const active = stats.running;
        setRunning(!!stats.enabled);
        $('wbLive').hidden = !active;
        $('wbBar').classList.toggle('is-running', active);
        if (!active && !stats.enabled) { stopPolling(); return; }
        startPolling();
        if (!active) return;
        if (!cur) { $('wbLiveCity').textContent = 'next city…'; return; }
        $('wbLiveCity').textContent = cur.city;
        $('wbLiveCountry').textContent = cur.country ? '— ' + cur.country + ' (' + cur.code + ')' : '';
        $('wbLiveTrigger').textContent = cur.trigger === 'cron' ? 'Cron job' : (cur.trigger === 'console' ? 'Console' : 'Started from this page');
        $('wbLiveDone').textContent = fmt(cur.cities_done);
        $('wbLiveHotels').textContent = fmt(cur.hotels_saved);
        $('wbLiveFailed').textContent = fmt(cur.cities_failed);
        $('wbLiveElapsed').textContent = cur.elapsed;
        $('wbLiveLast').textContent = cur.last ? `Last finished: ${cur.last.city} — ${cur.last.success ? fmt(cur.last.hotels_saved) + ' hotels' : 'failed'}` : '';
        $('wbRunStatus').textContent = 'Import is running on the server — counts and the city list update live.';
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

    // Start/Stop reflect the server switch (stats.enabled), not this page
    function setRunning(on) {
        state.running = on;
        $('wbStartBtn').hidden = on;
        $('wbStopBtn').hidden = !on;
    }

    async function toggleImport(url, btn) {
        btn.disabled = true;
        try {
            const r = await fetch(url, { method: 'POST', headers });
            const d = await r.json();
            $('wbRunStatus').textContent = d.message || '';
            setRunning(!!d.enabled);
            startPolling();
        } catch (err) {
            $('wbRunStatus').textContent = 'Request failed: ' + err.message;
        } finally {
            btn.disabled = false;
            setTimeout(() => { loadStats(); loadCities(); }, 1500);
        }
    }
    $('wbStartBtn').addEventListener('click', e => toggleImport(root.dataset.startUrl, e.currentTarget));
    $('wbStopBtn').addEventListener('click', e => toggleImport(root.dataset.stopUrl, e.currentTarget));

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
