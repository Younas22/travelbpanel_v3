@extends('admin-nova.layouts.app')

@section('title', $cfg['title'])

@section('content')
@php
    $default = optional($languages->firstWhere('is_default', true))->code ?? 'en';
    $others = $languages->where('code', '!=', $default);
@endphp
<div class="tbp-admin-page tbp-catalog-page">
    <header class="tbp-admin-header">
        <div><h1>{{ $cfg['title'] }}</h1><p>{{ $cfg['hint'] }}</p></div>
        <a href="{{ route('admin.hotels.catalog.create', $section) }}" class="tbp-admin-btn is-primary"><i class="bi bi-plus-lg"></i> Add {{ $cfg['singular'] }}</a>
    </header>

    <div class="tbp-admin-stats tbp-catalog-stats">
        <div class="tbp-admin-stat"><span class="tbp-admin-stat-icon is-blue"><i class="bi {{ $cfg['menu'] }}"></i></span><div><small>Total</small><strong>{{ $stats['total'] }}</strong></div></div>
        <div class="tbp-admin-stat"><span class="tbp-admin-stat-icon is-green"><i class="bi bi-check-circle"></i></span><div><small>Active</small><strong>{{ $stats['active'] }}</strong></div></div>
        <div class="tbp-admin-stat"><span class="tbp-admin-stat-icon is-red"><i class="bi bi-x-circle"></i></span><div><small>Inactive</small><strong>{{ $stats['inactive'] }}</strong></div></div>
    </div>

    <form method="GET" class="tbp-admin-filters">
        <label class="tbp-admin-field is-wide"><span>Search</span>
            <span class="tbp-admin-control"><i class="bi bi-search"></i><input type="text" name="search" value="{{ request('search') }}" placeholder="Search {{ strtolower($cfg['title']) }} in any language..."></span></label>
        <label class="tbp-admin-field"><span>Status</span>
            <select name="status"><option value="">All status</option><option value="1" @selected(request('status') === '1')>Active</option><option value="0" @selected(request('status') === '0')>Inactive</option></select></label>
        <div class="tbp-admin-filter-actions">
            <button type="submit" class="tbp-admin-btn is-primary"><i class="bi bi-funnel"></i> Filter</button>
            <a href="{{ route('admin.hotels.catalog.index', $section) }}" class="tbp-admin-btn is-icon" title="Reset"><i class="bi bi-arrow-clockwise"></i></a>
        </div>
    </form>

    <section class="tbp-admin-card">
        <header class="tbp-admin-card-head"><h2>All {{ strtolower($cfg['title']) }} <span>{{ $items->total() }} total</span></h2></header>
        <table class="tbp-admin-table">
            <thead><tr><th>Name ({{ strtoupper($default) }})</th><th>Translations</th><th>In use</th><th>Status</th><th class="is-actions">Actions</th></tr></thead>
            <tbody>
            @forelse($items as $item)
                @php $done = $item->translations->filter(fn ($t) => filled($t->name))->pluck('locale')->all(); @endphp
                <tr>
                    <td data-label="Name">
                        <div class="tbp-admin-person">
                            @if($cfg['icon'])<span class="tbp-catalog-icon"><i class="{{ $item->icon ?: 'bi bi-check2' }}"></i></span>@endif
                            <strong>{{ $item->name }}</strong>
                        </div>
                    </td>
                    <td data-label="Translations">
                        <span class="tbp-catalog-langs" title="{{ $languages->map(fn ($l) => $l->name . (in_array($l->code, $done) ? ' ✓' : ' —'))->implode(', ') }}">
                            {{ count(array_intersect($done, $languages->pluck('code')->all())) }}/{{ $languages->count() }} languages
                        </span>
                    </td>
                    <td data-label="In use">{{ $item->usage ? number_format($item->usage) : '—' }}</td>
                    <td data-label="Status">
                        <label class="tbp-admin-switch" title="Active / Inactive">
                            <input type="checkbox" class="js-catalog-status" data-url="{{ route('admin.hotels.catalog.toggle-status', [$section, $item->id]) }}" @checked($item->status) aria-label="{{ $item->name }} active"><i></i>
                        </label>
                    </td>
                    <td class="is-actions">
                        <div class="tbp-admin-actions">
                            <a href="{{ route('admin.hotels.catalog.edit', [$section, $item->id]) }}" title="Edit"><i class="bi bi-pencil"></i></a>
                            <form method="POST" action="{{ route('admin.hotels.catalog.destroy', [$section, $item->id]) }}" onsubmit="return confirm('Delete “{{ addslashes($item->name) }}”?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="is-danger" title="{{ $item->usage ? 'In use — set it to Inactive instead' : 'Delete' }}" @disabled($item->usage > 0)><i class="bi bi-trash3"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="tbp-admin-empty"><i class="bi {{ $cfg['menu'] }}"></i><strong>No {{ strtolower($cfg['title']) }} found</strong><a href="{{ route('admin.hotels.catalog.create', $section) }}" class="tbp-admin-btn is-primary">Add {{ $cfg['singular'] }}</a></td></tr>
            @endforelse
            </tbody>
        </table>
        @include('admin-nova.partials._tbp-pagination', ['paginator' => $items])
    </section>
    <div class="tbp-website-settings-toast" id="tbpCatalogToast" role="status" hidden></div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.js-catalog-status').forEach(function (input) {
    input.addEventListener('change', function () {
        var wanted = input.checked; input.disabled = true;
        var toast = document.getElementById('tbpCatalogToast');
        fetch(input.dataset.url, { method: 'PATCH', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' } })
            .then(function (r) { if (!r.ok) throw new Error(); return r.json(); })
            .then(function (d) { input.checked = !!d.status; toast.textContent = d.message; toast.className = 'tbp-website-settings-toast is-success'; })
            .catch(function () { input.checked = !wanted; toast.textContent = 'Could not update the status.'; toast.className = 'tbp-website-settings-toast is-error'; })
            .finally(function () { input.disabled = false; toast.hidden = false; clearTimeout(toast._t); toast._t = setTimeout(function () { toast.hidden = true; }, 2500); });
    });
});
</script>
@endpush
