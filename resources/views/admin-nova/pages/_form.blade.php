{{-- Expects: $page (Page|null — null on create, the model on edit).
     Note: Classic's create.blade.php named the Dutch content textarea
     "ln_content" while PageController's store() validates "nl_content" (the
     same name edit.blade.php already used correctly) — Dutch content was
     silently dropped on create. Fixed here to "nl_content" consistently so
     the field actually saves; no controller/validation changes involved. --}}
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
                        <span class="text-xs text-novamuted flex-shrink-0">{{ url('/') }}/</span>
                        <input type="text" name="slug" id="slug" class="pg-input @error('slug') is-invalid @enderror"
                               value="{{ old('slug', $page->slug ?? '') }}" placeholder="auto-generated-from-name">
                        <button type="button" class="pg-icon-btn flex-shrink-0" onclick="generateSlug()" data-tooltip="Regenerate">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 1 2.64 6.36M3 12v6m0-6h6"/></svg>
                        </button>
                    </div>
                    <p class="pg-help">Leave empty to auto-generate from page name</p>
                    @error('slug') <p class="pg-error">{{ $message }}</p> @enderror
                </div>

                {{-- ===== LANGUAGE TABS (Bootstrap tab JS kept, pills restyled) ===== --}}
                <div>
                    <label class="pg-label mb-3">Content &amp; SEO (Multi-language)</label>
                    <div class="flex items-center gap-2 mb-4" id="languageTabs" role="tablist">
                        <button class="pg-tab active" id="en-tab" data-bs-toggle="tab" data-bs-target="#en-content" type="button" role="tab">🇬🇧 English</button>
                        <button class="pg-tab" id="ln-tab" data-bs-toggle="tab" data-bs-target="#ln-content" type="button" role="tab">🇳🇱 Dutch</button>
                    </div>

                    <div class="tab-content" id="languageTabsContent">
                        <div class="tab-pane fade show active" id="en-content" role="tabpanel">
                            <div class="mb-4">
                                <label class="pg-label">Page Content</label>
                                <textarea name="en_content" id="en_content" class="hidden">{{ old('en_content', $page->en_content ?? '') }}</textarea>
                            </div>
                            <div class="mb-4">
                                <label class="pg-label">Meta Title</label>
                                <input type="text" name="en_meta_title" id="en_meta_title" class="pg-input"
                                       value="{{ old('en_meta_title', $page->en_meta_title ?? '') }}" placeholder="SEO optimized title">
                            </div>
                            <div class="mb-4">
                                <label class="pg-label">Meta Description</label>
                                <textarea name="en_meta_description" id="en_meta_description" class="pg-input" rows="3"
                                          placeholder="Brief description for search engines">{{ old('en_meta_description', $page->en_meta_description ?? '') }}</textarea>
                            </div>
                            <div>
                                <label class="pg-label">Meta Keywords</label>
                                <input type="text" name="en_meta_keywords" id="en_meta_keywords" class="pg-input"
                                       value="{{ old('en_meta_keywords', $page->en_meta_keywords ?? '') }}" placeholder="keyword1, keyword2, keyword3">
                            </div>
                        </div>

                        <div class="tab-pane fade" id="ln-content" role="tabpanel">
                            <div class="mb-4">
                                <label class="pg-label">Page Content</label>
                                <textarea name="nl_content" id="nl_content" class="hidden">{{ old('nl_content', $page->nl_content ?? '') }}</textarea>
                            </div>
                            <div class="mb-4">
                                <label class="pg-label">Meta Title</label>
                                <input type="text" name="ln_meta_title" id="ln_meta_title" class="pg-input"
                                       value="{{ old('ln_meta_title', $page->ln_meta_title ?? '') }}" placeholder="SEO optimized title">
                            </div>
                            <div class="mb-4">
                                <label class="pg-label">Meta Description</label>
                                <textarea name="ln_meta_description" id="ln_meta_description" class="pg-input" rows="3"
                                          placeholder="Brief description for search engines">{{ old('ln_meta_description', $page->ln_meta_description ?? '') }}</textarea>
                            </div>
                            <div>
                                <label class="pg-label">Meta Keywords</label>
                                <input type="text" name="ln_meta_keywords" id="ln_meta_keywords" class="pg-input"
                                       value="{{ old('ln_meta_keywords', $page->ln_meta_keywords ?? '') }}" placeholder="keyword1, keyword2, keyword3">
                            </div>
                        </div>
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
        toolbar: { items: ['heading', '|', 'bold', 'italic', 'underline', '|', 'link', 'imageUpload', '|', 'bulletedList', 'numberedList', '|', 'blockQuote', 'insertTable', '|', 'undo', 'redo'] },
        image: { toolbar: ['imageTextAlternative', 'imageStyle:inline', 'imageStyle:block'] }
    }).then(editor => {
        editor.ui.view.editable.element.style.minHeight = '300px';
        editorInstances[elementId] = editor;
        editor.model.document.on('change:data', () => { element.value = editor.getData(); });
    }).catch(error => {
        console.error(`Error initializing editor for ${elementId}:`, error);
        element.style.display = 'block';
        element.style.minHeight = '300px';
    });
}

document.addEventListener('DOMContentLoaded', function() {
    initializeEditor('en_content');
    let lnEditorInitialized = false;
    const lnTab = document.getElementById('ln-tab');
    if (lnTab) {
        lnTab.addEventListener('shown.bs.tab', function() {
            if (!lnEditorInitialized) { initializeEditor('nl_content'); lnEditorInitialized = true; }
        });
        lnTab.addEventListener('click', function() {
            document.querySelectorAll('#pgPage .pg-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
        });
    }
    document.getElementById('en-tab')?.addEventListener('click', function() {
        document.querySelectorAll('#pgPage .pg-tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
    });

    const nameInput = document.getElementById('name');
    const nameCounter = document.getElementById('nameCounter');
    if (nameInput && nameCounter) {
        nameCounter.textContent = nameInput.value.length;
        nameInput.addEventListener('input', function() { nameCounter.textContent = this.value.length; });
    }
});

function generateSlug() {
    const name = document.getElementById('name').value;
    const slug = name.toLowerCase().replace(/[^a-z0-9 -]/g, '').replace(/\s+/g, '-').replace(/-+/g, '-').replace(/^-|-$/g, '');
    document.getElementById('slug').value = slug;
}

const nameInputEl = document.getElementById('name');
const slugInputEl = document.getElementById('slug');
if (nameInputEl) {
    nameInputEl.addEventListener('input', function() { if (!slugInputEl.value) { generateSlug(); } });
}

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
