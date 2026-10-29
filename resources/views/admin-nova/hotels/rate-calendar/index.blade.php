@extends('admin-nova.layouts.app')

@section('title', 'Rate Calendar')

@section('content')
<div class="tbp-hotel-form-page tbp-rates-page" id="tbpRates">
    @include('admin.hotels.room-types._tabs')

    <header class="tbp-hotel-form-header">
        <div><h1>Rate Calendar</h1><p>Set the refundable and non-refundable nightly price for each date of the next 12 months. Empty dates use the room type's base prices.</p></div>
    </header>

    <section class="tbp-hotel-form-card tbp-rates-pick">
        <div class="tbp-rates-pick-row">
            <label class="tbp-hotel-form-field"><span>Hotel <b>*</b></span>
                <span class="tbp-hotel-form-icon"><i class="bi bi-building"></i>
                    <select id="tbpRatesHotel">
                        <option value="">Select hotel</option>
                        @foreach($hotels as $hotel)<option value="{{ $hotel->id }}" @selected($hotelId === (string) $hotel->id)>{{ $hotel->name }}</option>@endforeach
                    </select></span></label>
            <label class="tbp-hotel-form-field"><span>Room Type <b>*</b></span>
                <span class="tbp-hotel-form-icon"><i class="bi bi-door-closed"></i>
                    <select id="tbpRatesType"><option value="">Select a hotel first</option></select></span></label>
            <div class="tbp-rates-bases">
                <div class="tbp-rates-base-chip is-r"><small>Refundable base</small><strong id="tbpRatesBase">—</strong></div>
                <div class="tbp-rates-base-chip is-n"><small>Non-refundable base</small><strong id="tbpRatesBaseNr">—</strong></div>
            </div>
        </div>
    </section>

    <div class="tbp-rates-empty" id="tbpRatesEmpty">
        <i class="bi bi-calendar3"></i>
        <strong>Select a hotel and room type</strong>
        <span>Its 12-month rate calendar will appear here.</span>
    </div>

    <div class="tbp-rates-work" id="tbpRatesWork" hidden>
        <section class="tbp-rates-cal">
            <header class="tbp-rates-cal-head">
                <div class="tbp-rates-nav">
                    <button type="button" class="tbp-rates-iconbtn" id="tbpRatesPrev" aria-label="Previous month"><i class="bi bi-chevron-left"></i></button>
                    <h2 id="tbpRatesTitle">—</h2>
                    <button type="button" class="tbp-rates-iconbtn" id="tbpRatesNext" aria-label="Next month"><i class="bi bi-chevron-right"></i></button>
                    <button type="button" class="tbp-rates-pill" id="tbpRatesToday">Today</button>
                </div>
                <div class="tbp-rates-seg" role="group" aria-label="Prices shown" id="tbpRatesShow">
                    <button type="button" data-show="both" class="is-on">Both</button>
                    <button type="button" data-show="p">Refundable</button>
                    <button type="button" data-show="n">Non-refundable</button>
                </div>
            </header>

            <nav class="tbp-rates-strip" id="tbpRatesStrip" aria-label="Months"></nav>

            <div class="tbp-rates-grid" id="tbpRatesGrid" data-show="both"></div>

            <footer class="tbp-rates-summary" id="tbpRatesSummary"></footer>
        </section>

        <aside class="tbp-rates-side">
            <section class="tbp-rates-panel">
                <header><i class="bi bi-sliders"></i><div><h3>Bulk update</h3><p>Click two days in the calendar, or pick dates here.</p></div></header>
                <div class="tbp-rates-panel-body">
                    <div class="tbp-rates-range">
                        <label class="tbp-hotel-form-field"><span>From</span><input type="date" id="tbpBulkFrom" min="{{ $start }}"></label>
                        <label class="tbp-hotel-form-field"><span>To</span><input type="date" id="tbpBulkTo" min="{{ $start }}"></label>
                    </div>
                    <div class="tbp-hotel-form-field"><span>Days</span>
                        <div class="tbp-rates-weekdays" id="tbpBulkDays">
                            @foreach(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $i => $d)
                                <label><input type="checkbox" value="{{ ($i + 1) % 7 }}" checked><span>{{ $d }}</span></label>
                            @endforeach
                        </div></div>
                    <label class="tbp-hotel-form-field"><span><i class="tbp-rates-dot is-r"></i> Refundable price</span>
                        <span class="tbp-hotel-form-icon is-text"><i>USD</i><input type="number" id="tbpBulkPrice" min="0" step="0.01" placeholder="Leave empty to keep"></span></label>
                    <label class="tbp-hotel-form-field"><span><i class="tbp-rates-dot is-n"></i> Non-refundable price</span>
                        <span class="tbp-hotel-form-icon is-text"><i>USD</i><input type="number" id="tbpBulkNr" min="0" step="0.01" placeholder="Leave empty to keep"></span></label>
                    <div class="tbp-rates-bulk-actions">
                        <button type="button" class="tbp-hotel-btn is-primary" id="tbpBulkApply"><i class="bi bi-check2"></i> Apply</button>
                        <button type="button" class="tbp-hotel-btn" id="tbpBulkReset" title="Remove custom prices in this range"><i class="bi bi-arrow-counterclockwise"></i> Reset to base</button>
                    </div>
                    <small class="tbp-hotel-form-note" id="tbpBulkMsg" role="status"></small>
                </div>
            </section>

            <section class="tbp-rates-panel">
                <header><i class="bi bi-info-circle"></i><div><h3>Legend</h3></div></header>
                <ul class="tbp-rates-legend">
                    <li><i class="tbp-rates-dot is-r"></i> Refundable price</li>
                    <li><i class="tbp-rates-dot is-n"></i> Non-refundable price</li>
                    <li><i class="tbp-rates-swatch is-custom"></i> Custom price saved</li>
                    <li><i class="tbp-rates-swatch is-dirty"></i> Unsaved change</li>
                    <li><i class="tbp-rates-swatch is-selected"></i> Selected range</li>
                </ul>
            </section>
        </aside>
    </div>

    <div class="tbp-rates-savebar" id="tbpRatesSavebar" hidden>
        <span><strong id="tbpRatesDirty">0</strong> unsaved change(s)</span>
        <div>
            <button type="button" class="tbp-hotel-btn" id="tbpRatesDiscard">Discard</button>
            <button type="button" class="tbp-hotel-btn is-primary" id="tbpRatesSave"><i class="bi bi-check-circle"></i> <span>Save changes</span></button>
        </div>
    </div>
    <div class="tbp-website-settings-toast" id="tbpRatesToast" role="status" hidden></div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
(function () {
    var types = @json($roomTypes);
    var START = @json($start), DAYS = {{ $days }};
    var ratesUrl = @json(url('admin/hotels/rate-calendar')) + '/';
    var csrf = document.querySelector('meta[name="csrf-token"]').content;
    var hotelSel = document.getElementById('tbpRatesHotel'), typeSel = document.getElementById('tbpRatesType');
    var grid = document.getElementById('tbpRatesGrid'), strip = document.getElementById('tbpRatesStrip');
    var KEYS = { p: 'Refundable', n: 'Non-refundable' };
    // saved / dirty: date → { p, n } (null = base price). base: { p, n } (n may be null = no non-refundable rate)
    var state = { typeId: null, base: { p: 0, n: null }, saved: {}, dirty: {} };
    var view = null, sel = { from: null, to: null };

    function toast(msg, type) {
        var t = document.getElementById('tbpRatesToast');
        t.textContent = msg; t.className = 'tbp-website-settings-toast is-' + (type || 'success'); t.hidden = false;
        clearTimeout(t._t); t._t = setTimeout(function () { t.hidden = true; }, 3000);
    }
    function iso(d) { return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0'); }
    function parse(s) { var p = s.split('-'); return new Date(+p[0], +p[1] - 1, +p[2]); }
    var startDate = parse(START), endDate = new Date(startDate); endDate.setDate(endDate.getDate() + DAYS - 1);
    var END = iso(endDate);
    var firstMonth = new Date(startDate.getFullYear(), startDate.getMonth(), 1), lastMonth = new Date(endDate.getFullYear(), endDate.getMonth(), 1);
    function money(v) { return v === null || v === undefined ? '' : Number(v).toFixed(2).replace(/\.00$/, ''); }
    function round(v) { return Math.round(Number(v) * 100) / 100; }
    function none(v) { return v === null || v === undefined; }
    function dirtyCount() { return Object.keys(state.dirty).length; }
    function pairOf(date) { var x = state.dirty[date] || state.saved[date]; return x ? { p: x.p, n: x.n } : { p: null, n: null }; }
    function effective(date, k) { var v = pairOf(date)[k]; return none(v) ? state.base[k] : v; }
    function isCustom(date) { var s = state.saved[date]; return !!s && (!none(s.p) || !none(s.n)); }

    // ---- hotel / room type pickers (select2) --------------------------------------------
    $(hotelSel).select2({ placeholder: 'Search and select hotel...', width: '100%' });
    function fillTypes(keepId) {
        var list = types.filter(function (t) { return String(t.hotel_id) === hotelSel.value; });
        typeSel.innerHTML = '';
        var o0 = document.createElement('option'); o0.value = '';
        o0.textContent = !hotelSel.value ? 'Select a hotel first' : (list.length ? 'Select room type' : 'No room types for this hotel');
        typeSel.appendChild(o0);
        list.forEach(function (t) {
            var o = document.createElement('option'); o.value = t.id;
            o.textContent = t.name + (t.status ? '' : ' (inactive)');
            if (String(t.id) === String(keepId)) o.selected = true;
            typeSel.appendChild(o);
        });
        $(typeSel).select2({ placeholder: 'Search and select room type...', width: '100%' });
    }
    function confirmLeave() { return !dirtyCount() || window.confirm('You have unsaved price changes. Discard them?'); }
    var lastHotel = hotelSel.value;
    $(hotelSel).on('change', function () {
        if (!confirmLeave()) { $(hotelSel).val(lastHotel).trigger('change.select2'); return; }
        lastHotel = hotelSel.value; fillTypes(null); load(null);
    });
    var lastType = '';
    $(typeSel).on('change', function () {
        if (!confirmLeave()) { $(typeSel).val(lastType).trigger('change.select2'); return; }
        lastType = typeSel.value; load(typeSel.value || null);
    });
    fillTypes(@json($typeId));
    lastType = typeSel.value;

    // ---- load -------------------------------------------------------------------------------
    function load(typeId) {
        state = { typeId: typeId, base: { p: 0, n: null }, saved: {}, dirty: {} };
        sel = { from: null, to: null };
        updateSavebar();
        document.getElementById('tbpRatesWork').hidden = !typeId;
        document.getElementById('tbpRatesEmpty').hidden = !!typeId;
        document.getElementById('tbpRatesBase').textContent = '—';
        document.getElementById('tbpRatesBaseNr').textContent = '—';
        if (!typeId) return;
        grid.innerHTML = '<p class="tbp-rates-loading">Loading calendar…</p>';
        var u = new URL(location.href); u.searchParams.set('hotel_id', hotelSel.value); u.searchParams.set('room_type_id', typeId); history.replaceState(null, '', u);
        fetch(ratesUrl + typeId + '/rates', { headers: { 'Accept': 'application/json' } })
            .then(function (r) { if (!r.ok) throw new Error(); return r.json(); })
            .then(function (d) {
                state.base = { p: d.base_price, n: d.base_nr };
                state.saved = d.rates || {};
                document.getElementById('tbpRatesBase').textContent = 'USD ' + money(d.base_price);
                document.getElementById('tbpRatesBaseNr').textContent = none(d.base_nr) ? 'Not set' : 'USD ' + money(d.base_nr);
                view = view || new Date(firstMonth);
                render();
            })
            .catch(function () { grid.innerHTML = '<p class="tbp-rates-loading">Could not load the calendar.</p>'; });
    }

    // ---- render: month strip + one month grid + summary -----------------------------------------
    function monthKey(d) { return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0'); }
    function render() { renderStrip(); renderMonth(); renderSummary(); }

    function renderStrip() {
        strip.innerHTML = '';
        var c = new Date(firstMonth);
        while (c <= lastMonth) {
            var key = monthKey(c), n = 0, dirty = false;
            Object.keys(state.saved).forEach(function (d) { if (d.indexOf(key) === 0 && isCustom(d)) n++; });
            Object.keys(state.dirty).forEach(function (d) { if (d.indexOf(key) === 0) dirty = true; });
            var b = document.createElement('button'); b.type = 'button';
            b.className = 'tbp-rates-chip' + (key === monthKey(view) ? ' is-on' : '') + (dirty ? ' has-dirty' : '');
            b.innerHTML = '<span>' + c.toLocaleString('en-US', { month: 'short' }) + '</span><small>' + c.getFullYear() + '</small>' + (n ? '<em>' + n + '</em>' : '');
            b.dataset.month = key;
            b.addEventListener('click', function () { var p = this.dataset.month.split('-'); view = new Date(+p[0], +p[1] - 1, 1); render(); });
            strip.appendChild(b);
            c.setMonth(c.getMonth() + 1);
        }
        var on = strip.querySelector('.is-on'); if (on) strip.scrollLeft = on.offsetLeft - strip.clientWidth / 2 + on.offsetWidth / 2;
    }

    function inSel(date) { return !!sel.from && date >= sel.from && date <= (sel.to || sel.from); }

    function renderMonth() {
        var y = view.getFullYear(), m = view.getMonth(), today = iso(new Date());
        document.getElementById('tbpRatesTitle').textContent = view.toLocaleString('en-US', { month: 'long', year: 'numeric' });
        document.getElementById('tbpRatesPrev').disabled = view <= firstMonth;
        document.getElementById('tbpRatesNext').disabled = view >= lastMonth;
        grid.innerHTML = '';
        ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'].forEach(function (d) { var h = document.createElement('div'); h.className = 'tbp-rates-dow'; h.textContent = d; grid.appendChild(h); });
        var offset = (new Date(y, m, 1).getDay() + 6) % 7, daysIn = new Date(y, m + 1, 0).getDate();
        for (var i = 0; i < offset; i++) { var e = document.createElement('div'); e.className = 'tbp-rates-day is-blank'; grid.appendChild(e); }
        for (var d = 1; d <= daysIn; d++) {
            var dt = new Date(y, m, d), date = iso(dt), dow = dt.getDay();
            var cell = document.createElement('div'); cell.className = 'tbp-rates-day'; cell.dataset.date = date;
            if (dow === 0 || dow === 6) cell.classList.add('is-weekend');
            if (date === today) cell.classList.add('is-today');
            var head = document.createElement('button'); head.type = 'button'; head.className = 'tbp-rates-num';
            head.innerHTML = '<b>' + d + '</b>' + (date === today ? '<small>Today</small>' : '');
            cell.appendChild(head);
            if (date < START || date > END) {
                cell.classList.add('is-off'); head.disabled = true;
            } else {
                head.title = 'Select for bulk update';
                head.addEventListener('click', onPick);
                ['p', 'n'].forEach(function (k) {
                    var row = document.createElement('label'); row.className = 'tbp-rates-price is-' + (k === 'p' ? 'r' : 'n');
                    var tag = document.createElement('i'); tag.className = 'tbp-rates-dot is-' + (k === 'p' ? 'r' : 'n'); row.appendChild(tag);
                    var input = document.createElement('input');
                    input.type = 'number'; input.min = '0'; input.step = '0.01'; input.dataset.date = date; input.dataset.key = k;
                    input.placeholder = '—';
                    input.value = money(effective(date, k));
                    input.setAttribute('aria-label', KEYS[k] + ' price on ' + date);
                    input.addEventListener('change', onEdit);
                    row.appendChild(input);
                    cell.appendChild(row);
                });
                paint(cell, date);
            }
            grid.appendChild(cell);
        }
    }

    function paint(cell, date) {
        var dirty = date in state.dirty;
        cell.classList.toggle('is-dirty', dirty);
        cell.classList.toggle('is-custom', !dirty && isCustom(date));
        cell.classList.toggle('is-selected', inSel(date));
        var pair = pairOf(date);
        cell.querySelectorAll('.tbp-rates-price').forEach(function (row) {
            row.classList.toggle('is-own', !none(pair[row.classList.contains('is-r') ? 'p' : 'n']));
        });
    }
    function repaintAll() { grid.querySelectorAll('.tbp-rates-day[data-date]').forEach(function (c) { if (!c.classList.contains('is-off')) paint(c, c.dataset.date); }); }

    function renderSummary() {
        var r = [], n = [], custom = 0;
        var d = new Date(view), stop = new Date(view.getFullYear(), view.getMonth() + 1, 0);
        while (d <= stop) {
            var s = iso(d);
            if (s >= START && s <= END) {
                r.push(effective(s, 'p'));
                var nv = effective(s, 'n'); if (!none(nv)) n.push(nv);
                if (isCustom(s)) custom++;
            }
            d.setDate(d.getDate() + 1);
        }
        function stat(list, label, cls) {
            if (!list.length) return '<div class="tbp-rates-stat"><i class="tbp-rates-dot ' + cls + '"></i><span>' + label + '</span><strong>Not set</strong></div>';
            var min = Math.min.apply(null, list), max = Math.max.apply(null, list), avg = list.reduce(function (a, b) { return a + b; }, 0) / list.length;
            return '<div class="tbp-rates-stat"><i class="tbp-rates-dot ' + cls + '"></i><span>' + label + '</span><strong>USD ' + money(round(min)) + (max !== min ? ' – ' + money(round(max)) : '') + '</strong><small>avg ' + money(round(avg)) + '</small></div>';
        }
        document.getElementById('tbpRatesSummary').innerHTML = stat(r, 'Refundable', 'is-r') + stat(n, 'Non-refundable', 'is-n')
            + '<div class="tbp-rates-stat"><i class="bi bi-pencil-square"></i><span>Custom days</span><strong>' + custom + '</strong></div>';
    }

    document.getElementById('tbpRatesPrev').addEventListener('click', function () { view.setMonth(view.getMonth() - 1); render(); });
    document.getElementById('tbpRatesNext').addEventListener('click', function () { view.setMonth(view.getMonth() + 1); render(); });
    document.getElementById('tbpRatesToday').addEventListener('click', function () { view = new Date(firstMonth); render(); });
    document.getElementById('tbpRatesShow').addEventListener('click', function (e) {
        var b = e.target.closest('[data-show]'); if (!b) return;
        this.querySelectorAll('button').forEach(function (x) { x.classList.toggle('is-on', x === b); });
        grid.dataset.show = b.dataset.show;
    });

    // ---- range selection for bulk update (click a day number, then another) ----------------------
    function onPick(e) {
        var date = e.currentTarget.parentNode.dataset.date;
        if (!sel.from || sel.to) sel = { from: date, to: null };
        else if (date < sel.from) sel = { from: date, to: sel.from };
        else sel.to = date;
        document.getElementById('tbpBulkFrom').value = sel.from;
        document.getElementById('tbpBulkTo').value = sel.to || sel.from;
        repaintAll();
        document.getElementById('tbpBulkMsg').textContent = sel.to ? 'Range selected — enter prices and click Apply.' : 'Now click the last day of the range (or Apply for this day only).';
    }
    ['tbpBulkFrom', 'tbpBulkTo'].forEach(function (id) {
        document.getElementById(id).addEventListener('change', function () {
            sel = { from: document.getElementById('tbpBulkFrom').value || null, to: document.getElementById('tbpBulkTo').value || null };
            repaintAll();
        });
    });

    // ---- editing ---------------------------------------------------------------------------------
    // value: number, or null = back to the base price
    function setPrice(date, k, value) {
        if (value !== null && !none(state.base[k]) && Math.abs(value - state.base[k]) < 0.005) value = null;
        var pair = pairOf(date); pair[k] = value;
        var saved = state.saved[date] || { p: null, n: null };
        function eq(a, b) { return none(a) ? none(b) : (!none(b) && Math.abs(a - b) < 0.005); }
        if (eq(pair.p, saved.p) && eq(pair.n, saved.n)) delete state.dirty[date]; else state.dirty[date] = pair;
        var cell = grid.querySelector('.tbp-rates-day[data-date="' + date + '"]');
        if (cell) {
            cell.querySelectorAll('input').forEach(function (i) { i.value = money(effective(date, i.dataset.key)); });
            paint(cell, date);
        }
    }
    function onEdit(e) {
        var v = e.target.value.trim(), date = e.target.dataset.date, k = e.target.dataset.key;
        if (v !== '' && (isNaN(v) || Number(v) < 0)) { toast('Price must be 0 or more.', 'error'); e.target.value = money(effective(date, k)); return; }
        setPrice(date, k, v === '' ? null : round(v));
        updateSavebar(); renderStrip(); renderSummary();
    }

    function bulk(reset) {
        var from = document.getElementById('tbpBulkFrom').value, to = document.getElementById('tbpBulkTo').value;
        var p = document.getElementById('tbpBulkPrice').value.trim(), n = document.getElementById('tbpBulkNr').value.trim();
        var days = Array.prototype.filter.call(document.querySelectorAll('#tbpBulkDays input'), function (c) { return c.checked; }).map(function (c) { return +c.value; });
        var msg = document.getElementById('tbpBulkMsg');
        if (!from || !to) { msg.textContent = 'Choose a From and To date (or click two days in the calendar).'; return; }
        if (to < from) { msg.textContent = '“To” must be on or after “From”.'; return; }
        if (!days.length) { msg.textContent = 'Select at least one day.'; return; }
        if (!reset) {
            if (p === '' && n === '') { msg.textContent = 'Enter a refundable and/or non-refundable price.'; return; }
            if ((p !== '' && (isNaN(p) || Number(p) < 0)) || (n !== '' && (isNaN(n) || Number(n) < 0))) { msg.textContent = 'Prices must be 0 or more.'; return; }
        }
        var d = parse(from < START ? START : from), stop = parse(to > END ? END : to), count = 0;
        while (d <= stop) {
            if (days.indexOf(d.getDay()) !== -1) {
                var s = iso(d);
                if (reset) { setPrice(s, 'p', null); setPrice(s, 'n', null); }
                else { if (p !== '') setPrice(s, 'p', round(p)); if (n !== '') setPrice(s, 'n', round(n)); }
                count++;
            }
            d.setDate(d.getDate() + 1);
        }
        msg.textContent = count + ' date(s) updated — click “Save changes” to keep them.';
        updateSavebar(); render();
    }
    document.getElementById('tbpBulkApply').addEventListener('click', function () { bulk(false); });
    document.getElementById('tbpBulkReset').addEventListener('click', function () { bulk(true); });

    // ---- save / discard -----------------------------------------------------------------------------
    function updateSavebar() {
        var n = dirtyCount();
        document.getElementById('tbpRatesDirty').textContent = n;
        document.getElementById('tbpRatesSavebar').hidden = !n;
    }
    document.getElementById('tbpRatesDiscard').addEventListener('click', function () { state.dirty = {}; render(); updateSavebar(); });
    document.getElementById('tbpRatesSave').addEventListener('click', function () {
        var btn = this; if (btn.disabled || !dirtyCount()) return;
        btn.disabled = true; btn.querySelector('span').textContent = 'Saving…';
        fetch(ratesUrl + state.typeId + '/rates', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ rates: state.dirty })
        }).then(function (r) { return r.json().then(function (d) { if (!r.ok || !d.success) throw new Error(d.message || 'Could not save.'); return d; }); })
          .then(function (d) { state.saved = d.rates || {}; state.dirty = {}; render(); updateSavebar(); toast(d.message); })
          .catch(function (err) { toast(err.message || 'Could not save.', 'error'); })
          .finally(function () { btn.disabled = false; btn.querySelector('span').textContent = 'Save changes'; });
    });
    window.addEventListener('beforeunload', function (e) { if (dirtyCount()) { e.preventDefault(); e.returnValue = ''; } });

    load(typeSel.value || null);
})();
</script>
@endpush
