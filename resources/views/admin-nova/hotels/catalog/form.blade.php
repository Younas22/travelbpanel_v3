@extends('admin-nova.layouts.app')

@php
    $isEdit = (bool) $item;
    $default = optional($languages->firstWhere('is_default', true))->code ?? 'en';
    $flags = ['en' => 'gb', 'nl' => 'nl', 'fr' => 'fr', 'de' => 'de', 'es' => 'es', 'it' => 'it', 'ar' => 'ae', 'tr' => 'tr', 'ru' => 'ru', 'cn' => 'cn', 'zh' => 'cn', 'ja' => 'jp', 'ko' => 'kr', 'ur' => 'pk', 'pt' => 'pt', 'hi' => 'in'];
    $tr = $isEdit ? $item->translations->keyBy('locale') : collect();
    $trVal = function ($locale, $field) use ($tr, $isEdit, $item, $default) {
        $saved = optional($tr->get($locale))->{$field};
        if (blank($saved) && $isEdit && $locale === $default && $field === 'name') $saved = $item->name;
        return old("translations.$locale.$field", $saved ?? '');
    };
    $isActive = (string) old('status', $isEdit ? (int) $item->status : 1) === '1';
    $title = ($isEdit ? 'Edit ' : 'Add ') . $cfg['singular'];
@endphp

@section('title', $title)

@section('content')
<div class="tbp-hotel-form-page tbp-catalog-form">
    <header class="tbp-hotel-form-header">
        <div><h1>{{ $title }}</h1><p>{{ $isEdit ? $item->name : $cfg['hint'] }}</p></div>
        <a href="{{ route('admin.hotels.catalog.index', $section) }}" class="tbp-hotel-btn"><i class="bi bi-arrow-left"></i> Back to {{ $cfg['title'] }}</a>
    </header>

    @if($errors->any())
        <div class="tbp-hotel-form-errors"><i class="bi bi-exclamation-circle"></i><div><strong>Please fix the following:</strong><ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div></div>
    @endif

    <form method="POST" action="{{ $isEdit ? route('admin.hotels.catalog.update', [$section, $item->id]) : route('admin.hotels.catalog.store', $section) }}" class="tbp-hotel-form-layout is-room-type" id="tbpCatalogForm" novalidate>
        @csrf
        @if($isEdit) @method('PATCH') @endif

        <div class="tbp-hotel-form-main">
            <section class="tbp-hotel-form-card">
                <header><i class="bi bi-translate"></i><div><h2>Name in every language</h2><p>{{ strtoupper($default) }} is required; other languages fall back to it when left empty.</p></div></header>
                <div class="tbp-catalog-langs-grid">
                    @foreach($languages as $lang)
                        @php $code = $lang->code; $isDefault = $code === $default; $rtl = $lang->direction === 'rtl'; @endphp
                        <div class="tbp-catalog-lang" dir="{{ $rtl ? 'rtl' : 'ltr' }}">
                            <div class="tbp-catalog-lang-head" dir="ltr">
                                @if(isset($flags[$code]))<span class="fi fi-{{ $flags[$code] }}"></span>@endif
                                <strong>{{ $lang->name }}</strong>
                                @if($lang->native_name && $lang->native_name !== $lang->name)<small>{{ $lang->native_name }}</small>@endif
                                @if($isDefault)<em class="tbp-catalog-default">Default</em>@endif
                            </div>
                            <label class="tbp-hotel-form-field"><span>Name @if($isDefault)<b>*</b>@endif</span>
                                <input type="text" name="translations[{{ $code }}][name]" value="{{ $trVal($code, 'name') }}" maxlength="255" placeholder="{{ $cfg['singular'] }} name in {{ $lang->name }}" @if($isDefault) required data-label="Name ({{ $lang->name }})" @endif>
                                @error("translations.$code.name")<em class="tbp-hotel-field-error">{{ $message }}</em>@enderror</label>
                            @if($cfg['description'] ?? true)
                                <label class="tbp-hotel-form-field"><span>Description</span>
                                    <textarea name="translations[{{ $code }}][description]" rows="2" maxlength="2000" placeholder="Optional description in {{ $lang->name }}">{{ $trVal($code, 'description') }}</textarea></label>
                            @endif
                        </div>
                    @endforeach
                </div>
            </section>
        </div>

        <aside class="tbp-hotel-form-side">
            <section class="tbp-hotel-form-card">
                <header><i class="bi bi-gear"></i><div><h2>Settings</h2><p>Status{{ $cfg['icon'] ? ' and icon' : '' }}.</p></div></header>
                <input type="hidden" name="status" value="0">
                <label class="tbp-hotel-toggle">
                    <input type="checkbox" name="status" value="1" id="tbpCatalogStatus" @checked($isActive)>
                    <i aria-hidden="true"></i>
                    <span><strong>Active</strong><small>Inactive items are hidden from selection lists but kept on existing records.</small></span>
                    <em class="tbp-hotel-badge {{ $isActive ? 'is-on' : '' }}" id="tbpCatalogStatusBadge">{{ $isActive ? 'Active' : 'Inactive' }}</em>
                </label>

                @if($cfg['icon'])
                    <label class="tbp-hotel-form-field tbp-catalog-icon-field"><span>Icon (Bootstrap Icons class)</span>
                        <span class="tbp-hotel-form-icon"><i class="{{ old('icon', $isEdit ? $item->icon : '') ?: 'bi bi-question-square' }}" id="tbpCatalogIconPreview"></i>
                            <input type="text" name="icon" id="tbpCatalogIcon" value="{{ old('icon', $isEdit ? $item->icon : '') }}" placeholder="e.g. bi bi-wifi" maxlength="100"></span>
                        <small class="tbp-hotel-form-note">Find icons at <a href="https://icons.getbootstrap.com/" target="_blank" rel="noopener">icons.getbootstrap.com</a> — copy the class, e.g. <code>bi bi-wifi</code>.</small></label>
                @endif

                @if($cfg['kind'])
                    <label class="tbp-hotel-form-field"><span>Sort order</span>
                        <input type="number" name="sort_order" min="0" value="{{ old('sort_order', $isEdit ? $item->sort_order : '') }}" placeholder="Auto (end of list)"></label>
                @endif
            </section>

            <button type="submit" class="tbp-hotel-btn is-primary is-block" id="tbpCatalogSubmit"><i class="bi bi-check-circle"></i> <span>{{ $isEdit ? 'Update ' : 'Add ' }}{{ $cfg['singular'] }}</span></button>
            <a href="{{ route('admin.hotels.catalog.index', $section) }}" class="tbp-hotel-btn is-block tbp-catalog-cancel">Cancel</a>
        </aside>
    </form>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flag-icons/7.2.3/css/flag-icons.min.css">
@endpush

@push('scripts')
<script>
(function () {
    var form = document.getElementById('tbpCatalogForm');
    var status = document.getElementById('tbpCatalogStatus'), badge = document.getElementById('tbpCatalogStatusBadge');
    status.addEventListener('change', function () { badge.textContent = status.checked ? 'Active' : 'Inactive'; badge.classList.toggle('is-on', status.checked); });

    var icon = document.getElementById('tbpCatalogIcon'), preview = document.getElementById('tbpCatalogIconPreview');
    if (icon) icon.addEventListener('input', function () { preview.className = icon.value.trim() || 'bi bi-question-square'; });

    function box(el) { return el.closest('.tbp-hotel-form-field'); }
    function clearError(el) { var b = box(el); b.classList.remove('has-error'); b.querySelectorAll('.tbp-hotel-field-error').forEach(function (m) { m.remove(); }); }
    var required = form.querySelectorAll('[required][data-label]');
    required.forEach(function (el) { el.addEventListener('input', function () { if (el.value.trim()) clearError(el); }); });
    form.querySelectorAll('.tbp-hotel-field-error').forEach(function (m) { box(m).classList.add('has-error'); });

    form.addEventListener('submit', function (e) {
        var btn = document.getElementById('tbpCatalogSubmit');
        if (btn.disabled) { e.preventDefault(); return; }
        var first = null;
        required.forEach(function (el) {
            if (!el.value.trim()) {
                clearError(el); var b = box(el); b.classList.add('has-error');
                var m = document.createElement('em'); m.className = 'tbp-hotel-field-error'; m.textContent = el.dataset.label + ' is required.'; b.appendChild(m);
                first = first || el;
            }
        });
        if (first) { e.preventDefault(); box(first).scrollIntoView({ behavior: 'smooth', block: 'center' }); first.focus({ preventScroll: true }); return; }
        btn.disabled = true; btn.querySelector('span').textContent = 'Saving…';
    });
})();
</script>
@endpush
