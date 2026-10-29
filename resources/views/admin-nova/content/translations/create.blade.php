@extends('admin-nova.layouts.app')

@section('title', 'Create Translation Group')

@section('content')
<div class="tbp-translation-create-page">
    <a href="{{ route('admin.content.translations.index') }}" class="tbp-translation-create-back"><i class="bi bi-arrow-left"></i> Back to Translation Management</a>
    <div class="tbp-translation-create-header">
        <h1>Create Translation Group</h1>
        <p>Create a new translation group for multiple languages</p>
    </div>

    <form action="{{ route('admin.content.translations.store') }}" method="POST" id="translationForm" class="tbp-translation-create-card">
        @csrf

        @if($errors->any())
            <div class="tbp-translation-create-errors"><i class="bi bi-exclamation-circle"></i> {{ $errors->first() }}</div>
        @endif

        <section class="tbp-translation-create-section">
            <span class="tbp-translation-create-icon"><i class="bi bi-folder2"></i></span>
            <label class="tbp-translation-create-field">
                <span class="tbp-translation-create-title">Group Name <b>*</b></span>
                <input type="text" name="group_name" class="{{ $errors->has('group_name') ? 'is-invalid' : '' }}" placeholder="e.g., home, about, footer, menu" value="{{ old('group_name') }}" pattern="[a-z_]+" required>
                <small>Use lowercase letters and underscores only. This will create separate JSON files for each language.</small>
            </label>
        </section>

        <section class="tbp-translation-create-section">
            <span class="tbp-translation-create-icon"><i class="bi bi-globe"></i></span>
            <div>
                <span class="tbp-translation-create-title">Translation Keys and Values <b>*</b></span>
                <p class="tbp-translation-create-sub">Add translation keys and their corresponding values for each selected language.</p>
            </div>
        </section>

        <div class="tbp-translation-create-tabs" role="tablist">
            @foreach($languages as $language)
                <button type="button" class="tbp-translation-create-tab {{ $loop->first ? 'is-active' : '' }}" data-lang-tab="{{ $language->code }}" role="tab" aria-selected="{{ $loop->first ? 'true' : 'false' }}">{{ $language->name }} ({{ $language->native_name }})</button>
            @endforeach
        </div>

        @foreach($languages as $language)
            <div class="tbp-translation-create-pane" data-lang-pane="{{ $language->code }}" @unless($loop->first) hidden @endunless>
                <div class="tbp-translation-create-rows" id="translations-{{ $language->code }}"></div>
                <button type="button" class="tbp-translation-create-add" onclick="addTranslationPair('{{ $language->code }}')"><i class="bi bi-plus-lg"></i> Add Key</button>
            </div>
        @endforeach

        <input type="hidden" id="translationsData" name="translations">

        <div class="tbp-translation-create-actions">
            <button type="submit" class="tbp-translation-create-btn is-primary"><i class="bi bi-check-lg"></i> <span>Create Translation Group</span></button>
            <a href="{{ route('admin.content.translations.index') }}" class="tbp-translation-create-btn"><i class="bi bi-x-lg"></i> Cancel</a>
        </div>
    </form>

    <template id="translationPairTpl">
        <div class="translation-pair tbp-translation-create-row">
            <label class="tbp-translation-create-field"><span><i class="bi bi-key"></i> Key <b>*</b></span>
                <input type="text" placeholder="e.g., hero_title" data-key-input>
                <small>Translation key name (e.g., hero_title)</small></label>
            <label class="tbp-translation-create-field"><span><i class="bi bi-file-earmark-text"></i> Value <b>*</b></span>
                <input type="text" placeholder="Enter translation value" data-value-input>
                <small>Translation value for selected language</small></label>
            <button type="button" class="tbp-translation-create-remove" onclick="removeTranslationPair(this)" title="Remove key"><i class="bi bi-trash"></i></button>
        </div>
    </template>
</div>
@endsection

@push('scripts')
<script>
function addTranslationPair(lang) {
    var row = document.getElementById('translationPairTpl').content.firstElementChild.cloneNode(true);
    row.querySelector('[data-value-input]').setAttribute('data-lang', lang);
    document.getElementById('translations-' + lang).appendChild(row);
    return row;
}

function removeTranslationPair(button) {
    button.closest('.translation-pair').remove();
}

(function () {
    var form = document.getElementById('translationForm');
    document.querySelectorAll('[data-lang-pane]').forEach(function (pane) { addTranslationPair(pane.dataset.langPane); });

    // Only the selected language's rows are shown; the others keep their values while hidden.
    document.querySelectorAll('[data-lang-tab]').forEach(function (tab) {
        tab.addEventListener('click', function () {
            document.querySelectorAll('[data-lang-tab]').forEach(function (t) {
                var on = t === tab;
                t.classList.toggle('is-active', on);
                t.setAttribute('aria-selected', on ? 'true' : 'false');
            });
            document.querySelectorAll('[data-lang-pane]').forEach(function (p) { p.hidden = p.dataset.langPane !== tab.dataset.langTab; });
        });
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        if (!form.querySelector('[name="group_name"]').reportValidity()) return;

        var translations = {};
        form.querySelectorAll('.translation-pair').forEach(function (pair) {
            var key = pair.querySelector('[data-key-input]').value.trim();
            var valueInput = pair.querySelector('[data-value-input]');
            var value = valueInput.value.trim();
            if (key && value) {
                translations[key] = translations[key] || {};
                translations[key][valueInput.getAttribute('data-lang')] = value;
            }
        });

        if (!Object.keys(translations).length) {
            alert('Please add at least one translation key-value pair');
            return;
        }

        document.getElementById('translationsData').value = JSON.stringify(translations);
        var btn = form.querySelector('.is-primary');
        btn.disabled = true;
        btn.querySelector('span').textContent = 'Creating…';
        form.submit();
    });
})();
</script>
@endpush
