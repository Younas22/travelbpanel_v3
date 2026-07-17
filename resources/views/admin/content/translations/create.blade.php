@extends('admin.layouts.app')

@section('title', 'Create Translation Group')

@section('content')
<div class="content-area">
    <!-- Page Header -->
    <div class="page-header">
        <h2 class="mb-1">Create Translation Group</h2>
        <p class="text-muted mb-0">Create a new translation group for multiple languages</p>
    </div>

    <!-- Form Card -->
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.content.translations.store') }}" method="POST" id="translationForm">
                @csrf

                <!-- Group Name -->
                <div class="mb-4">
                    <label class="form-label">Group Name *</label>
                    <input type="text" 
                           name="group_name" 
                           class="form-control @error('group_name') is-invalid @enderror"
                           placeholder="e.g., home, about, footer, menu"
                           value="{{ old('group_name') }}"
                           pattern="[a-z_]+"
                           required>
                    <small class="form-text text-muted">
                        Use lowercase letters and underscores only. This will create separate JSON files for each language.
                    </small>
                    @error('group_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Language Tabs -->
                <div class="mb-4">
                    <label class="form-label d-block mb-3">Translation Keys and Values *</label>
                    
                    <ul class="nav nav-tabs mb-3" role="tablist">
                        @foreach($languages as $index => $language)
                            <li class="nav-item">
                                <button class="nav-link {{ $index === 0 ? 'active' : '' }}"
                                        id="lang-{{ $language->code }}-tab"
                                        data-bs-toggle="tab"
                                        data-bs-target="#lang-{{ $language->code }}"
                                        type="button">
                                    {{ $language->name }} ({{ $language->native_name }})
                                </button>
                            </li>
                        @endforeach
                    </ul>

                    <div class="tab-content border p-3">
                        @foreach($languages as $index => $language)
                            <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}"
                                 id="lang-{{ $language->code }}"
                                 role="tabpanel">

                                <div class="translation-inputs" id="translations-{{ $language->code }}">
                                    <div class="translation-pair mb-3">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <input type="text" 
                                                       class="form-control" 
                                                       placeholder="Key (e.g., hero_title)"
                                                       data-key-input>
                                                <small class="form-text text-muted">Key name</small>
                                            </div>
                                            <div class="col-md-8">
                                                <div class="input-group">
                                                    <input type="text"
                                                           class="form-control"
                                                           placeholder="Value"
                                                           data-value-input
                                                           data-lang="{{ $language->code }}">
                                                    <button class="btn btn-outline-danger" 
                                                            type="button" 
                                                            onclick="removeTranslationPair(this)">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                                <small class="form-text text-muted">Translation value</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <button type="button"
                                        class="btn btn-sm btn-outline-primary"
                                        onclick="addTranslationPair(this, '{{ $language->code }}')">
                                    <i class="bi bi-plus-lg"></i> Add Key
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Hidden Input for translations -->
                <input type="hidden" id="translationsData" name="translations">

                <!-- Form Actions -->
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> Create Translation Group
                    </button>
                    <a href="{{ route('admin.content.translations.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-lg"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function addTranslationPair(button, lang) {
        const container = document.getElementById(`translations-${lang}`);
        const pair = `
            <div class="translation-pair mb-3">
                <div class="row">
                    <div class="col-md-4">
                        <input type="text" 
                               class="form-control" 
                               placeholder="Key (e.g., hero_title)"
                               data-key-input>
                        <small class="form-text text-muted">Key name</small>
                    </div>
                    <div class="col-md-8">
                        <div class="input-group">
                            <input type="text" 
                                   class="form-control" 
                                   placeholder="Value"
                                   data-value-input
                                   data-lang="${lang}">
                            <button class="btn btn-outline-danger" 
                                    type="button" 
                                    onclick="removeTranslationPair(this)">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                        <small class="form-text text-muted">Translation value</small>
                    </div>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', pair);
    }

    function removeTranslationPair(button) {
        button.closest('.translation-pair').remove();
    }

    document.getElementById('translationForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const translations = {};
        const allPairs = document.querySelectorAll('.translation-pair');

        allPairs.forEach(pair => {
            const keyInput = pair.querySelector('[data-key-input]');
            const valueInput = pair.querySelector('[data-value-input]');
            const lang = valueInput.getAttribute('data-lang');
            const key = keyInput.value.trim();
            const value = valueInput.value.trim();

            if (key && value) {
                if (!translations[key]) {
                    translations[key] = {};
                }
                translations[key][lang] = value;
            }
        });

        if (Object.keys(translations).length === 0) {
            alert('Please add at least one translation key-value pair');
            return;
        }

        document.getElementById('translationsData').value = JSON.stringify(translations);
        this.submit();
    });
</script>

<style>
    .translation-pair {
        padding: 15px;
        border: 1px solid #e9ecef;
        border-radius: 4px;
        background-color: #f8f9fa;
    }
</style>
@endsection