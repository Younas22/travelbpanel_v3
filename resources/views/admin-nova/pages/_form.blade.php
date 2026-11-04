{{-- Expects: $page (Page|null — null on create, the model on edit), $languages (active languages). --}}
@php $isEdit = (bool) $page; @endphp

<form action="{{ $isEdit ? route('admin.pages.update', $page) : route('admin.pages.store') }}" method="POST" id="pageForm">
    @csrf
    @if($isEdit)
        @method('PATCH')
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <div class="lg:col-span-2 space-y-5">
            <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 sm:p-5">
                <div class="mb-4">
                    <label class="pg-label" for="name">Page Name <span class="pg-required">*</span></label>
                    <input type="text" name="name" id="name" class="pg-input @error('name') is-invalid @enderror"
                           value="{{ old('name', $page->name ?? '') }}" required placeholder="Enter page name">
                    <p class="pg-help"><span id="nameCounter">0</span>/255 characters</p>
                    @error('name') <p class="pg-error">{{ $message }}</p> @enderror
                </div>

                <div class="mb-5">
                    <label class="pg-label" for="slug">URL Slug</label>
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-novamuted flex-shrink-0">{{ url('/page') }}/</span>
                        <input type="text" name="slug" id="slug" class="pg-input @error('slug') is-invalid @enderror"
                               value="{{ old('slug', $page->slug ?? '') }}" placeholder="auto-generated-from-name">
                        <button type="button" class="pg-icon-btn flex-shrink-0" onclick="generateSlug()" data-tooltip="Regenerate">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 1 2.64 6.36M3 12v6m0-6h6"/></svg>
                        </button>
                    </div>
                    <p class="pg-help">Fills in from the page name; the page opens at this address</p>
                    @error('slug') <p class="pg-error">{{ $message }}</p> @enderror
                </div>

                {{-- ===== LANGUAGE TABS: one per active language (Bootstrap tab JS kept, pills restyled) ===== --}}
                <div>
                    <label class="pg-label mb-3">Content &amp; SEO (Multi-language)</label>
                    <div class="flex flex-wrap items-center gap-2 mb-4" id="languageTabs" role="tablist">
                        @foreach($languages as $language)
                            <button class="pg-tab {{ $loop->first ? 'active' : '' }}" id="{{ $language->code }}-tab" data-lang="{{ $language->code }}"
                                    data-bs-toggle="tab" data-bs-target="#{{ $language->code }}-content" type="button" role="tab"
                                    @if($language->is_default) data-tooltip="Default language" @endif>
                                {{ $language->native_name ?: $language->name }}
                                <span class="opacity-60">{{ strtoupper($language->code) }}</span>
                            </button>
                        @endforeach
                    </div>

                    <div class="tab-content" id="languageTabsContent">
                        @foreach($languages as $language)
                            @php
                                $code = $language->code;
                                $value = fn ($field) => old("translations.$code.$field", $page ? $page->translation($field, $code, false) : '');
                            @endphp
                            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="{{ $code }}-content" role="tabpanel">
                                @if(!$language->is_default)
                                    <p class="pg-help mb-4">Leave empty to show the {{ $languages->firstWhere('is_default', true)?->name ?? 'default' }} version in {{ $language->name }}.</p>
                                @endif
                                <div class="mb-4">
                                    <label class="pg-label" for="title_{{ $code }}">Page Title</label>
                                    <input type="text" name="translations[{{ $code }}][title]" id="title_{{ $code }}" dir="{{ $language->direction }}"
                                           class="pg-input @error("translations.$code.title") is-invalid @enderror" value="{{ $value('title') }}" placeholder="Heading shown on the page">
                                    @error("translations.$code.title") <p class="pg-error">{{ $message }}</p> @enderror
                                </div>
                                <div class="mb-4">
                                    <label class="pg-label">Page Content</label>
                                    <textarea name="translations[{{ $code }}][content]" id="content_{{ $code }}" data-lang="{{ $code }}" class="hidden">{{ $value('content') }}</textarea>
                                </div>
                                <div class="mb-4">
                                    <label class="pg-label" for="meta_title_{{ $code }}">Meta Title</label>
                                    <input type="text" name="translations[{{ $code }}][meta_title]" id="meta_title_{{ $code }}" dir="{{ $language->direction }}"
                                           class="pg-input @error("translations.$code.meta_title") is-invalid @enderror" value="{{ $value('meta_title') }}" placeholder="SEO optimized title">
                                    @error("translations.$code.meta_title") <p class="pg-error">{{ $message }}</p> @enderror
                                </div>
                                <div class="mb-4">
                                    <label class="pg-label" for="meta_description_{{ $code }}">Meta Description</label>
                                    <textarea name="translations[{{ $code }}][meta_description]" id="meta_description_{{ $code }}" dir="{{ $language->direction }}"
                                              class="pg-input @error("translations.$code.meta_description") is-invalid @enderror" rows="3" placeholder="Brief description for search engines">{{ $value('meta_description') }}</textarea>
                                    @error("translations.$code.meta_description") <p class="pg-error">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="pg-label" for="meta_keywords_{{ $code }}">Meta Keywords</label>
                                    <input type="text" name="translations[{{ $code }}][meta_keywords]" id="meta_keywords_{{ $code }}" dir="{{ $language->direction }}"
                                           class="pg-input @error("translations.$code.meta_keywords") is-invalid @enderror" value="{{ $value('meta_keywords') }}" placeholder="keyword1, keyword2, keyword3">
                                    @error("translations.$code.meta_keywords") <p class="pg-error">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-5">
            <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 sm:p-5 space-y-4">
                <h2 class="text-sm font-semibold text-novatext">Publishing Options</h2>
                <div>
                    <label class="pg-label" for="status">Status <span class="pg-required">*</span></label>
                    <select name="status" id="status" class="pg-input @error('status') is-invalid @enderror" required>
                        <option value="draft" {{ old('status', $page->status ?? 'draft') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="published" {{ old('status', $page->status ?? '') == 'published' ? 'selected' : '' }}>Published</option>
                        <option value="private" {{ old('status', $page->status ?? '') == 'private' ? 'selected' : '' }}>Private</option>
                    </select>
                    @error('status') <p class="pg-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="pg-label" for="sort_order">Sort Order</label>
                    <input type="number" name="sort_order" id="sort_order" class="pg-input @error('sort_order') is-invalid @enderror"
                           value="{{ old('sort_order', $page->sort_order ?? 0) }}" min="0">
                    <p class="pg-help">Lower numbers appear first in navigation</p>
                    @error('sort_order') <p class="pg-error">{{ $message }}</p> @enderror
                </div>

                <label class="flex items-center gap-3">
                    <span class="pg-switch">
                        <input type="checkbox" name="is_homepage" id="is_homepage" value="1" {{ old('is_homepage', $page->is_homepage ?? false) ? 'checked' : '' }}>
                        <span class="pg-slider"></span>
                    </span>
                    <span class="text-xs font-semibold text-novatext">Set as Homepage</span>
                </label>
                <p class="pg-help -mt-2">This will replace the current homepage</p>

                <label class="flex items-center gap-3">
                    <span class="pg-switch">
                        <input type="checkbox" name="show_in_menu" id="show_in_menu" value="1" {{ old('show_in_menu', $page->show_in_menu ?? true) ? 'checked' : '' }}>
                        <span class="pg-slider"></span>
                    </span>
                    <span class="text-xs font-semibold text-novatext">Show in Navigation Menu</span>
                </label>
            </div>

            <div class="space-y-2">
                <button type="submit" class="pg-btn-nova pg-btn-primary w-full py-3 text-sm font-semibold">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                    {{ $isEdit ? 'Update Page' : 'Create Page' }}
                </button>
                <button type="button" class="pg-btn-nova w-full py-3 text-sm font-semibold" onclick="saveDraft()">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"/><path d="M17 21v-8H7v8M7 3v5h8"/></svg>
                    Save as Draft
                </button>
                <a href="{{ route('admin.pages.index') }}" class="pg-btn-nova w-full py-3 text-sm font-semibold text-center">Cancel</a>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script>
let editorInstances = {};

class MyUploadAdapter {
    constructor(loader) { this.loader = loader; }
    upload() {
        return this.loader.file.then(file => new Promise((resolve, reject) => {
            this._initRequest(); this._initListeners(resolve, reject, file); this._sendRequest(file);
        }));
    }
    abort() { if (this.xhr) { this.xhr.abort(); } }
    _initRequest() {
        const xhr = this.xhr = new XMLHttpRequest();
        xhr.open('POST', '{{ route("admin.pages.upload-image") }}', true);
        xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');
        xhr.responseType = 'json';
    }
    _initListeners(resolve, reject, file) {
        const xhr = this.xhr; const loader = this.loader;
        const genericErrorText = `Couldn't upload file: ${file.name}.`;
        xhr.addEventListener('error', () => reject(genericErrorText));
        xhr.addEventListener('abort', () => reject());
        xhr.addEventListener('load', () => {
            const response = xhr.response;
            if (!response || xhr.status !== 200) { return reject(response && response.message ? response.message : genericErrorText); }
            if (!response.url) { return reject(response.message || genericErrorText); }
            resolve({ default: response.url });
        });
        if (xhr.upload) {
            xhr.upload.addEventListener('progress', evt => {
                if (evt.lengthComputable) { loader.uploadTotal = evt.total; loader.uploaded = evt.loaded; }
            });
        }
    }
    _sendRequest(file) { const data = new FormData(); data.append('upload', file); this.xhr.send(data); }
}

function MyCustomUploadAdapterPlugin(editor) {
    editor.plugins.get('FileRepository').createUploadAdapter = (loader) => new MyUploadAdapter(loader);
}

function initializeEditor(elementId) {
    const element = document.querySelector(`#${elementId}`);
    if (!element) return;
    ClassicEditor.create(element, {
        extraPlugins: [MyCustomUploadAdapterPlugin],
        language: { ui: 'en', content: element.dataset.lang || 'en' },
        toolbar: { items: ['heading', '|', 'bold', 'italic', 'underline', '|', 'link', 'imageUpload', '|', 'bulletedList', 'numberedList', '|', 'blockQuote', 'insertTable', '|', 'undo', 'redo'] },
        image: { toolbar: ['imageTextAlternative', 'imageStyle:inline', 'imageStyle:block'] }
    }).then(editor => {
        editorInstances[elementId] = editor;
        editor.model.document.on('change:data', () => { element.value = editor.getData(); });
    }).catch(error => {
        console.error(`Error initializing editor for ${elementId}:`, error);
        element.style.display = 'block';
        element.style.minHeight = '300px';
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // First language's editor right away, the others the first time their tab opens
    document.querySelectorAll('#languageTabs [data-lang]').forEach(function(tab, index) {
        const editorId = 'content_' + tab.dataset.lang;
        if (index === 0) {
            initializeEditor(editorId);
        } else {
            tab.addEventListener('shown.bs.tab', function() {
                if (!editorInstances[editorId]) { initializeEditor(editorId); }
            });
        }
        tab.addEventListener('click', function() {
            document.querySelectorAll('#pgPage .pg-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
        });
    });

    const nameField = document.getElementById('name');
    const nameCounter = document.getElementById('nameCounter');
    if (nameField && nameCounter) {
        nameCounter.textContent = nameField.value.length;
        nameField.addEventListener('input', function() { nameCounter.textContent = this.value.length; });
    }
});

// Same slug the server makes (Str::slug): lowercase, accents dropped, words joined by "-"
function slugify(text) {
    return text.normalize('NFKD').replace(/[̀-ͯ]/g, '')
        .toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '')
        .trim()
        .replace(/[\s-]+/g, '-');
}

function generateSlug() {
    document.getElementById('slug').value = slugify(document.getElementById('name').value);
}

// The slug follows the name while typing, until it is edited by hand
// (on edit: only while it still matches the name, so a custom slug is kept)
const nameInput = document.getElementById('name');
const slugInput = document.getElementById('slug');
let slugEditedByHand = slugInput.value !== '' && slugInput.value !== slugify(nameInput.value);

nameInput.addEventListener('input', function() {
    if (!slugEditedByHand) {
        generateSlug();
    }
});
slugInput.addEventListener('input', function() {
    slugEditedByHand = this.value !== '';
});

function saveDraft() {
    document.getElementById('status').value = 'draft';
    updateEditorContents();
    document.getElementById('pageForm').submit();
}

function updateEditorContents() {
    for (const [id, editor] of Object.entries(editorInstances)) {
        if (editor) { document.getElementById(id).value = editor.getData(); }
    }
}

document.getElementById('pageForm')?.addEventListener('submit', function(e) {
    updateEditorContents();
    const submitButton = this.querySelector('button[type="submit"]');
    if (submitButton) { submitButton.disabled = true; }
});
</script>
@endpush
