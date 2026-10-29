@extends('admin-nova.layouts.app')

@section('title', 'Hotels')

@section('content')
@php
    $cards = [
        ['Total', 'bi-buildings', 'is-blue', $stats['total']],
        ['Active', 'bi-check-circle', 'is-green', $stats['active']],
        ['Inactive', 'bi-x-circle', 'is-red', $stats['inactive']],
        ['Pending', 'bi-clock', 'is-amber', $stats['pending']],
    ];
    $locationOf = fn ($h) => $h->location ? $h->location->city . ', ' . $h->location->country : ($h->address ?: '—');
    $imageOf = fn ($h) => $h->images->first() ? asset('public/assets/images/' . $h->images->first()->image_path) : null;
@endphp
<div class="tbp-hotels-page" id="tbpHotels">

    <header class="tbp-hotels-header">
        <div><h1>Hotels</h1><p>Manage all hotels</p></div>
        <div class="tbp-hotels-header-actions">
            <div class="tbp-hotels-view" role="group" aria-label="View">
                <button type="button" data-view="grid"><i class="bi bi-grid"></i> Grid</button>
                <button type="button" data-view="list"><i class="bi bi-list-ul"></i> List</button>
            </div>
            <a href="{{ route('admin.hotels.create') }}" class="tbp-hotels-btn is-primary"><i class="bi bi-plus-lg"></i> Add New Hotel</a>
        </div>
    </header>

    <div class="tbp-hotels-stats">
        @foreach($cards as [$label, $icon, $tone, $value])
            <div class="tbp-hotels-stat"><span class="tbp-hotels-stat-icon {{ $tone }}"><i class="bi {{ $icon }}"></i></span><div><strong>{{ number_format($value) }}</strong><small>{{ $label }}</small></div></div>
        @endforeach
    </div>

    <form method="GET" action="{{ route('admin.hotels.index') }}" class="tbp-hotels-filters">
        <label class="tbp-hotels-search"><i class="bi bi-search"></i>
            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Search hotels by name, address or location..." aria-label="Search hotels"></label>
        <label class="tbp-hotels-field"><span>Status</span>
            <select name="status">
                <option value="">All status</option>
                <option value="1" @selected(($status ?? '') == '1')>Active</option>
                <option value="0" @selected(($status ?? '') === '0')>Inactive</option>
            </select></label>
        <label class="tbp-hotels-field"><span>Type</span>
            <select name="type">
                <option value="">All types</option>
                @foreach(\App\Support\HotelTypes::options() as $v => $l)
                    <option value="{{ $v }}" @selected(($type ?? '') == $v)>{{ $l }}</option>
                @endforeach
            </select></label>
        <button type="submit" class="tbp-hotels-btn is-primary is-icon" title="Apply filters"><i class="bi bi-funnel"></i></button>
        <a href="{{ route('admin.hotels.index') }}" class="tbp-hotels-btn is-icon" title="Reset filters"><i class="bi bi-arrow-clockwise"></i></a>
    </form>

    @if($hotels->isEmpty())
        <section class="tbp-hotels-card tbp-hotels-empty">
            <i class="bi bi-buildings"></i>
            <strong>No hotels found</strong>
            <span>{{ ($search || $status !== null && $status !== '' || $type) ? 'No hotels match your search or filters.' : 'Add your first hotel to get started.' }}</span>
            <a href="{{ route('admin.hotels.create') }}" class="tbp-hotels-btn is-primary"><i class="bi bi-plus-lg"></i> Add New Hotel</a>
        </section>
    @else
        {{-- ============ LIST VIEW ============ --}}
        <section class="tbp-hotels-card" data-view-panel="list">
            <table class="tbp-hotels-table">
                <thead><tr><th>Hotel</th><th>Type</th><th>Rooms</th><th>Contact</th><th>Status</th><th class="is-actions">Actions</th></tr></thead>
                <tbody>
                @foreach($hotels as $hotel)
                    @php $img = $imageOf($hotel); @endphp
                    <tr>
                        <td data-label="Hotel">
                            <div class="tbp-hotels-name">
                                <span class="tbp-hotels-thumb">@if($img)<img src="{{ $img }}" alt="" loading="lazy">@else<i class="bi bi-building"></i>@endif</span>
                                <div><span class="tbp-hotels-title">{{ $hotel->name }}</span><small>{{ $locationOf($hotel) }}</small>@include('admin-nova.hotels._no-rooms', ['hotel' => $hotel])</div>
                            </div>
                        </td>
                        <td data-label="Type"><span class="tbp-hotels-type">{{ ucwords($hotel->type ?? 'hotel') }}</span></td>
                        <td data-label="Rooms"><a href="{{ route('admin.hotels.rooms.index', ['hotel_id' => $hotel->id]) }}" class="tbp-hotels-btn is-small" title="{{ number_format($hotel->rooms_count) }} rooms"><i class="bi bi-door-closed"></i> View Rooms</a></td>
                        <td data-label="Contact">{{ $hotel->phone ?: ($hotel->email ?: '—') }}</td>
                        <td data-label="Status">@include('admin-nova.hotels._status', ['hotel' => $hotel])</td>
                        <td class="is-actions">@include('admin-nova.hotels._actions', ['hotel' => $hotel])</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            @include('admin-nova.hotels._pagination')
        </section>

        {{-- ============ GRID VIEW ============ --}}
        <section data-view-panel="grid">
            <div class="tbp-hotels-grid">
                @foreach($hotels as $hotel)
                    @php $img = $imageOf($hotel); @endphp
                    <article class="tbp-hotels-tile">
                        <div class="tbp-hotels-cover">
                            @if($img)<img src="{{ $img }}" alt="" loading="lazy">@else<i class="bi bi-building"></i>@endif
                            <div class="tbp-hotels-overlay">@include('admin-nova.hotels._status', ['hotel' => $hotel])</div>
                        </div>
                        <div class="tbp-hotels-tile-body">
                            <span class="tbp-hotels-title">{{ $hotel->name }}</span>
                            <small><i class="bi bi-geo-alt"></i> {{ $locationOf($hotel) }}</small>
                            <span class="tbp-hotels-type">{{ ucwords($hotel->type ?? 'hotel') }}</span>
                            @include('admin-nova.hotels._no-rooms', ['hotel' => $hotel])
                        </div>
                        @php $approval = $hotel->approval_status ?? 'approved'; @endphp
                        <div class="tbp-hotels-tile-flags">
                            @if($approval === 'pending')
                                <form method="POST" action="{{ route('admin.hotels.approve', $hotel) }}">@csrf<button type="submit" class="tbp-hotels-chip is-approve"><i class="bi bi-check2"></i> Approve</button></form>
                                <form method="POST" action="{{ route('admin.hotels.reject', $hotel) }}" onsubmit="return confirm('Reject {{ addslashes($hotel->name) }}?');">@csrf<button type="submit" class="tbp-hotels-chip is-reject"><i class="bi bi-x-lg"></i> Reject</button></form>
                            @endif
                            <button type="button" class="tbp-hotels-chip is-featured js-toggle-featured {{ $hotel->is_featured ? 'is-on' : '' }}" data-id="{{ $hotel->id }}" data-featured="{{ $hotel->is_featured ? '1' : '0' }}"><i class="bi {{ $hotel->is_featured ? 'bi-star-fill' : 'bi-star' }}"></i> <span>{{ $hotel->is_featured ? 'Featured' : 'Feature' }}</span></button>
                        </div>
                        <footer class="tbp-hotels-tile-actions">
                            <a href="{{ route('admin.hotels.rooms.index', ['hotel_id' => $hotel->id]) }}" class="tbp-hotels-btn is-small"><i class="bi bi-door-closed"></i> View Rooms</a>
                            <a href="{{ route('admin.hotels.edit', $hotel) }}" class="tbp-hotels-btn is-small"><i class="bi bi-pencil"></i> Edit</a>
                            <button type="button" class="tbp-hotels-btn is-small is-danger js-delete-hotel" data-id="{{ $hotel->id }}" data-name="{{ $hotel->name }}" data-rooms="{{ $hotel->rooms_count }}" data-types="{{ $hotel->room_types_count }}"><i class="bi bi-trash3"></i> Delete</button>
                        </footer>
                    </article>
                @endforeach
            </div>
            <div class="tbp-hotels-card tbp-hotels-grid-footer">@include('admin-nova.hotels._pagination')</div>
        </section>
    @endif

    {{-- ============ DELETE CONFIRMATION ============ --}}
    <div class="tbp-hotels-modal" id="tbpDeleteModal" hidden role="dialog" aria-modal="true" aria-labelledby="tbpDeleteTitle">
        <div class="tbp-hotels-modal-box">
            <button type="button" class="tbp-hotels-modal-close" data-close aria-label="Close"><i class="bi bi-x-lg"></i></button>
            <span class="tbp-hotels-modal-icon"><i class="bi bi-trash3"></i></span>
            <h2 id="tbpDeleteTitle">Delete Hotel?</h2>
            <p>Are you sure you want to delete <strong data-hotel-name></strong>? This action cannot be undone.</p>
            <p class="tbp-hotels-modal-warn" data-related hidden></p>
            <div class="tbp-hotels-modal-actions">
                <button type="button" class="tbp-hotels-btn" data-close>Cancel</button>
                <button type="button" class="tbp-hotels-btn is-danger-solid" data-confirm><span>Delete Hotel</span></button>
            </div>
        </div>
    </div>
    <div class="tbp-hotels-toast" id="tbpHotelsToast" role="status" hidden></div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var page = document.getElementById('tbpHotels');
    var VIEW_KEY = 'tbpHotelsView';
    var csrf = document.querySelector('meta[name="csrf-token"]').content;
    var base = @json(url('admin/hotels'));

    function toast(msg, type) {
        var t = document.getElementById('tbpHotelsToast');
        t.textContent = msg; t.className = 'tbp-hotels-toast is-' + (type || 'success'); t.hidden = false;
        clearTimeout(t._t); t._t = setTimeout(function () { t.hidden = true; }, 3000);
    }

    // Grid / List — client-side only, so search, filters and page stay as they are.
    function setView(view) {
        page.querySelectorAll('[data-view]').forEach(function (b) { b.classList.toggle('is-active', b.dataset.view === view); b.setAttribute('aria-pressed', b.dataset.view === view); });
        page.querySelectorAll('[data-view-panel]').forEach(function (p) { p.hidden = p.dataset.viewPanel !== view; });
        try { localStorage.setItem(VIEW_KEY, view); } catch (e) {}
    }
    page.querySelectorAll('[data-view]').forEach(function (b) { b.addEventListener('click', function () { setView(b.dataset.view); }); });
    var saved = 'list';
    try { saved = localStorage.getItem(VIEW_KEY) || 'list'; } catch (e) {}
    setView(saved);

    // Active toggle → existing PATCH toggle-status; reverts on failure.
    page.querySelectorAll('.js-toggle-status').forEach(function (input) {
        input.addEventListener('change', function () {
            var id = input.dataset.id, wanted = input.checked;
            var all = page.querySelectorAll('.js-toggle-status[data-id="' + id + '"]');
            all.forEach(function (i) { i.disabled = true; i.checked = wanted; });
            fetch(base + '/' + id + '/toggle-status', { method: 'PATCH', headers: { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
                .then(function (r) { if (!r.ok) throw new Error(); return r.json(); })
                .then(function (data) {
                    var active = !!Number(data.status);
                    all.forEach(function (i) { i.checked = active; });
                    page.querySelectorAll('.js-status-label[data-id="' + id + '"]').forEach(function (l) { l.textContent = active ? 'Active' : 'Inactive'; l.classList.toggle('is-active', active); });
                    toast(data.message || 'Status updated');
                })
                .catch(function () { all.forEach(function (i) { i.checked = !wanted; }); toast('Could not update the status. Please try again.', 'error'); })
                .finally(function () { all.forEach(function (i) { i.disabled = false; }); });
        });
    });

    // Featured (More menu) → existing PATCH toggle-featured.
    page.querySelectorAll('.js-toggle-featured').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            var id = btn.dataset.id;
            fetch(base + '/' + id + '/toggle-featured', { method: 'PATCH', headers: { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
                .then(function (r) { if (!r.ok) throw new Error(); return r.json(); })
                .then(function (data) {
                    var on = btn.dataset.featured !== '1';
                    page.querySelectorAll('.js-toggle-featured[data-id="' + id + '"]').forEach(function (b) {
                        b.dataset.featured = on ? '1' : '0';
                        b.classList.toggle('is-on', on);
                        b.querySelector('span').textContent = on ? 'Featured' : 'Feature';
                        b.querySelector('.bi').className = 'bi ' + (on ? 'bi-star-fill' : 'bi-star');
                        b.title = on ? 'Remove from featured' : 'Mark as featured';
                    });
                    toast(data.message || 'Featured status updated');
                })
                .catch(function () { toast('Could not update featured status.', 'error'); });
        });
    });

    // Delete → confirmation modal → existing DELETE route.
    var modal = document.getElementById('tbpDeleteModal'), current = null, busy = false;
    function openModal(btn) {
        current = btn;
        modal.querySelector('[data-hotel-name]').textContent = btn.dataset.name;
        var rooms = +btn.dataset.rooms, types = +btn.dataset.types, rel = modal.querySelector('[data-related]');
        rel.hidden = !(rooms || types);
        rel.textContent = 'This will also delete its ' + types + ' room type' + (types === 1 ? '' : 's') + ' and ' + rooms + ' room' + (rooms === 1 ? '' : 's') + '.';
        modal.hidden = false;
        modal.querySelector('[data-confirm]').focus();
    }
    function closeModal() { if (busy) return; modal.hidden = true; current = null; }
    page.querySelectorAll('.js-delete-hotel').forEach(function (b) { b.addEventListener('click', function () { openModal(b); }); });
    modal.querySelectorAll('[data-close]').forEach(function (b) { b.addEventListener('click', closeModal); });
    modal.addEventListener('click', function (e) { if (e.target === modal) closeModal(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !modal.hidden) closeModal(); });
    modal.querySelector('[data-confirm]').addEventListener('click', function () {
        if (!current || busy) return;
        busy = true;
        var btn = this; btn.disabled = true; btn.querySelector('span').textContent = 'Deleting…';
        var form = document.createElement('form');
        form.method = 'POST'; form.action = base + '/' + current.dataset.id;
        form.innerHTML = '<input type="hidden" name="_token" value="' + csrf + '"><input type="hidden" name="_method" value="DELETE">';
        document.body.appendChild(form);
        form.submit(); // the existing destroy() redirects back with a success message, so the list and stats refresh
    });
})();
</script>
@endpush
