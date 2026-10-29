{{-- Expects: $language (Language|null — null on create, the model on edit). --}}
@php
    $val = fn ($key, $default = '') => old($key, $language->{$key} ?? $default);
    $cls = fn ($key) => 'tbp-language-form-input' . ($errors->has($key) ? ' is-invalid' : '');
@endphp
<div class="tbp-language-form-page">
    <a href="{{ route('admin.settings.languages.index') }}" class="tbp-language-form-back"><i class="bi bi-arrow-left"></i> Back to Languages</a>
    <div class="tbp-language-form-header">
        <h1>{{ $language ? 'Edit Language' : 'Add New Language' }}</h1>
        <p>{{ $language ? 'Update language information' : 'Add a new language to your application' }}</p>
    </div>

    <form class="tbp-language-form-card" action="{{ $language ? route('admin.settings.languages.update', $language) : route('admin.settings.languages.store') }}" method="POST">
        @csrf
        @if($language) @method('PATCH') @endif

        <div class="tbp-language-form-grid is-2">
            <label class="tbp-language-form-field"><span><i class="bi bi-tag"></i> Language Code <b>*</b></span>
                <input type="text" name="code" class="{{ $cls('code') }}" placeholder="e.g., en, nl, ar, ur" value="{{ $val('code') }}" maxlength="10" required>
                <small>ISO 639-1 code (2-letter)</small>
                @error('code')<em>{{ $message }}</em>@enderror</label>
            <label class="tbp-language-form-field"><span><i class="bi bi-translate"></i> Language Name <b>*</b></span>
                <input type="text" name="name" class="{{ $cls('name') }}" placeholder="e.g., English, Dutch, Arabic" value="{{ $val('name') }}" maxlength="100" required>
                <small>English name of the language</small>
                @error('name')<em>{{ $message }}</em>@enderror</label>
        </div>

        <div class="tbp-language-form-grid is-3">
            <label class="tbp-language-form-field"><span><i class="bi bi-globe"></i> Native Name</span>
                <input type="text" name="native_name" class="{{ $cls('native_name') }}" placeholder="e.g., Nederlands, العربية" value="{{ $val('native_name') }}" maxlength="100">
                <small>Native language name</small>
                @error('native_name')<em>{{ $message }}</em>@enderror</label>
            <label class="tbp-language-form-field"><span><i class="bi bi-arrow-left-right"></i> Direction <b>*</b></span>
                <select name="direction" class="{{ $cls('direction') }}" required>
                    <option value="ltr" @selected($val('direction', 'ltr') == 'ltr')>LTR (Left to Right)</option>
                    <option value="rtl" @selected($val('direction', 'ltr') == 'rtl')>RTL (Right to Left)</option>
                </select>
                <small>Text direction</small>
                @error('direction')<em>{{ $message }}</em>@enderror</label>
            <label class="tbp-language-form-field"><span><i class="bi bi-list-ol"></i> Sort Order</span>
                <input type="number" name="sort_order" class="{{ $cls('sort_order') }}" value="{{ $val('sort_order', 0) }}" min="0">
                <small>Display order</small>
                @error('sort_order')<em>{{ $message }}</em>@enderror</label>
        </div>

        <div class="tbp-language-form-grid is-2">
            <label class="tbp-language-form-field"><span><i class="bi bi-activity"></i> Status <b>*</b></span>
                <select name="status" class="{{ $cls('status') }}" required>
                    <option value="1" @selected((string) (int) $val('status', 1) === '1')>Active</option>
                    <option value="0" @selected((string) (int) $val('status', 1) === '0')>Inactive</option>
                </select>
                <small>Whether this language is active</small>
                @error('status')<em>{{ $message }}</em>@enderror</label>
            <div class="tbp-language-form-field"><span><i class="bi bi-star"></i> Default Language</span>
                {{-- Hidden 0 so unticking on Edit actually clears the default. --}}
                <input type="hidden" name="is_default" value="0">
                <label class="tbp-language-form-switch">
                    <input type="checkbox" name="is_default" value="1" @checked($val('is_default', false))>
                    <i aria-hidden="true"></i>
                    <span>Set as default language</span>
                </label>
                <small>Only one language can be set as default</small>
                @error('is_default')<em>{{ $message }}</em>@enderror</div>
        </div>

        <div class="tbp-language-form-actions">
            <a href="{{ route('admin.settings.languages.index') }}" class="tbp-language-form-btn"><i class="bi bi-x-lg"></i> Cancel</a>
            <button type="submit" class="tbp-language-form-btn is-primary"><i class="bi bi-check-lg"></i> <span>{{ $language ? 'Update Language' : 'Add Language' }}</span></button>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.querySelector('.tbp-language-form-card').addEventListener('submit', function () {
    var btn = this.querySelector('.is-primary');
    btn.disabled = true;
    btn.querySelector('span').textContent = 'Saving…';
});
</script>
@endpush
