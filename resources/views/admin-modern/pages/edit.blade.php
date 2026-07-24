{{-- resources/views/admin/pages/create.blade.php --}}

@extends('admin-modern.layouts.app')

@section('title', 'Edit Page')


@section('content')
<div class="container-fluid cke-gradient-form">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-edit me-2"></i>Edit Page: {{ Str::limit($page->name, 30) }}
                    </h3>
                    <div class="d-flex gap-2">
                        @if($page->status === 'published')
                            <a href="{{ $page->url }}" target="_blank" class="btn btn-info btn-sm">
                                <i class="fas fa-external-link-alt me-1"></i> View Live
                            </a>
                        @endif
                        <a href="{{ route('admin.pages.index') }}" class="btn btn-light btn-sm">
                            <i class="fas fa-arrow-left me-1"></i> Back to Pages
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.pages.update', $page) }}" method="POST" id="pageForm">
                        @csrf
                        @method('PATCH')

                        <div class="row">
                            <div class="col-md-8">
                                <!-- Page Name -->
                                <div class="form-group">
                                    <label for="name" class="form-label">
                                        <i class="fas fa-heading me-1"></i>Page Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                                           value="{{ old('name', $page->name) }}" required placeholder="Enter page name">
                                    @error('name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                    <div class="form-text">
                                        <small class="text-muted">
                                            <span id="nameCounter">0</span>/255 characters
                                        </small>
                                    </div>
                                </div>

                                <!-- URL Slug -->
                                <div class="form-group">
                                    <label for="slug" class="form-label">
                                        <i class="fas fa-link me-1"></i>URL Slug
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">{{ url('/') }}/</span>
                                        <input type="text" name="slug" id="slug" class="form-control @error('slug') is-invalid @enderror"
                                               value="{{ old('slug', $page->slug) }}" placeholder="auto-generated-from-name">
                                        <button type="button" class="btn btn-outline-secondary" onclick="generateSlug()">
                                            <i class="fas fa-sync"></i>
                                        </button>
                                    </div>
                                    <div class="form-text">
                                        <small class="text-muted">Leave empty to auto-generate from page name</small>
                                    </div>
                                    @error('slug')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <!-- Language Tabs -->
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-language me-1"></i>Content & SEO (Multi-language)
                                    </label>

                                    <ul class="nav nav-tabs" id="languageTabs" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link active" id="en-tab" data-bs-toggle="tab" data-bs-target="#en-content" type="button" role="tab">
                                                🇬🇧 English
                                            </button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="ln-tab" data-bs-toggle="tab" data-bs-target="#ln-content" type="button" role="tab">
                                                🇳🇱 Dutch
                                            </button>
                                        </li>
                                    </ul>

                                    <div class="tab-content border p-4 bg-white" id="languageTabsContent">
                                        <!-- English Content -->
                                        <div class="tab-pane fade show active" id="en-content" role="tabpanel">
                                            <h5 class="mb-3">English Content</h5>

                                            <div class="form-group">
                                                <label for="en_content" class="form-label">Page Content</label>
                                                <textarea name="en_content" id="en_content" class="form-control ckeditor d-none">{{ old('en_content', $page->en_content) }}</textarea>
                                            </div>

                                            <div class="form-group">
                                                <label for="en_meta_title" class="form-label">Meta Title</label>
                                                <input type="text" name="en_meta_title" id="en_meta_title"
                                                       class="form-control" value="{{ old('en_meta_title', $page->en_meta_title) }}" placeholder="SEO optimized title">
                                            </div>

                                            <div class="form-group">
                                                <label for="en_meta_description" class="form-label">Meta Description</label>
                                                <textarea name="en_meta_description" id="en_meta_description"
                                                          class="form-control" rows="3" placeholder="Brief description for search engines">{{ old('en_meta_description', $page->en_meta_description) }}</textarea>
                                            </div>

                                            <div class="form-group">
                                                <label for="en_meta_keywords" class="form-label">Meta Keywords</label>
                                                <input type="text" name="en_meta_keywords" id="en_meta_keywords"
                                                       class="form-control" value="{{ old('en_meta_keywords', $page->en_meta_keywords) }}" placeholder="keyword1, keyword2, keyword3">
                                            </div>
                                        </div>

                                        <!-- Dutch Content -->
                                        <div class="tab-pane fade" id="ln-content" role="tabpanel">
                                            <h5 class="mb-3">Dutch Content</h5>

                                            <div class="form-group">
                                                <label for="nl_content" class="form-label">Page Content</label>
                                                <textarea name="nl_content" id="nl_content" class="form-control ckeditor d-none">{{ old('nl_content', $page->nl_content) }}</textarea>
                                            </div>

                                            <div class="form-group">
                                                <label for="ln_meta_title" class="form-label">Meta Title</label>
                                                <input type="text" name="ln_meta_title" id="ln_meta_title"
                                                       class="form-control" value="{{ old('ln_meta_title', $page->ln_meta_title) }}" placeholder="SEO optimized title">
                                            </div>

                                            <div class="form-group">
                                                <label for="ln_meta_description" class="form-label">Meta Description</label>
                                                <textarea name="ln_meta_description" id="ln_meta_description"
                                                          class="form-control" rows="3" placeholder="Brief description for search engines">{{ old('ln_meta_description', $page->ln_meta_description) }}</textarea>
                                            </div>

                                            <div class="form-group">
                                                <label for="ln_meta_keywords" class="form-label">Meta Keywords</label>
                                                <input type="text" name="ln_meta_keywords" id="ln_meta_keywords"
                                                       class="form-control" value="{{ old('ln_meta_keywords', $page->ln_meta_keywords) }}" placeholder="keyword1, keyword2, keyword3">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <!-- Publishing Options -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h6 class="mb-0">
                                            <i class="fas fa-cog me-1"></i>Publishing Options
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                                <option value="draft" {{ old('status', $page->status) == 'draft' ? 'selected' : '' }}>
                                                    Draft
                                                </option>
                                                <option value="published" {{ old('status', $page->status) == 'published' ? 'selected' : '' }}>
                                                    Published
                                                </option>
                                                <option value="private" {{ old('status', $page->status) == 'private' ? 'selected' : '' }}>
                                                    Private
                                                </option>
                                            </select>
                                            @error('status')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label for="sort_order" class="form-label">Sort Order</label>
                                            <input type="number" name="sort_order" id="sort_order"
                                                   class="form-control @error('sort_order') is-invalid @enderror"
                                                   value="{{ old('sort_order', $page->sort_order) }}" min="0">
                                            <div class="form-text">
                                                <small class="text-muted">Lower numbers appear first in navigation</small>
                                            </div>
                                            @error('sort_order')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <div class="form-check">
                                                <input type="checkbox" name="is_homepage" id="is_homepage"
                                                       class="form-check-input" value="1" {{ old('is_homepage', $page->is_homepage) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="is_homepage">
                                                    Set as Homepage
                                                </label>
                                            </div>
                                            <div class="form-text">
                                                <small class="text-muted">This will replace the current homepage</small>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <div class="form-check">
                                                <input type="checkbox" name="show_in_menu" id="show_in_menu"
                                                       class="form-check-input" value="1" {{ old('show_in_menu', $page->show_in_menu) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="show_in_menu">
                                                    Show in Navigation Menu
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center mt-4 pt-3 section-divider-top">
                                    <div>
                                        <button type="button" class="btn btn-outline-secondary" onclick="saveDraft()">
                                            <i class="fas fa-save me-1"></i> Save as Draft
                                        </button>
                                    </div>
                                    <div>
                                        <button type="button" class="btn btn-secondary me-2" onclick="window.location='{{ route('admin.pages.index') }}'">
                                            <i class="fas fa-times me-1"></i> Cancel
                                        </button>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i> Update Page
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- CKEditor 5 CDN -->
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>

<script>
let editorInstances = {};

// Custom Upload Adapter
class MyUploadAdapter {
    constructor(loader) {
        this.loader = loader;
    }

    upload() {
        return this.loader.file
            .then(file => new Promise((resolve, reject) => {
                this._initRequest();
                this._initListeners(resolve, reject, file);
                this._sendRequest(file);
            }));
    }

    abort() {
        if (this.xhr) {
            this.xhr.abort();
        }
    }

    _initRequest() {
        const xhr = this.xhr = new XMLHttpRequest();
        xhr.open('POST', '{{ route("admin.pages.upload-image") }}', true);
        xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');
        xhr.responseType = 'json';
    }

    _initListeners(resolve, reject, file) {
        const xhr = this.xhr;
        const loader = this.loader;
        const genericErrorText = `Couldn't upload file: ${file.name}.`;

        xhr.addEventListener('error', () => reject(genericErrorText));
        xhr.addEventListener('abort', () => reject());
        xhr.addEventListener('load', () => {
            const response = xhr.response;

            if (!response || xhr.status !== 200) {
                return reject(response && response.message ? response.message : genericErrorText);
            }

            if (!response.url) {
                return reject(response.message || genericErrorText);
            }

            resolve({
                default: response.url
            });
        });

        if (xhr.upload) {
            xhr.upload.addEventListener('progress', evt => {
                if (evt.lengthComputable) {
                    loader.uploadTotal = evt.total;
                    loader.uploaded = evt.loaded;
                }
            });
        }
    }

    _sendRequest(file) {
        const data = new FormData();
        data.append('upload', file);
        this.xhr.send(data);
    }
}

// Plugin to register the upload adapter
function MyCustomUploadAdapterPlugin(editor) {
    editor.plugins.get('FileRepository').createUploadAdapter = (loader) => {
        return new MyUploadAdapter(loader);
    };
}

// Initialize CKEditor for each language
function initializeEditor(elementId) {
    const element = document.querySelector(`#${elementId}`);
    if (!element) return;

    ClassicEditor
        .create(element, {
            extraPlugins: [MyCustomUploadAdapterPlugin],
            toolbar: {
                items: [
                    'heading', '|',
                    'bold', 'italic', 'underline', '|',
                    'link', 'imageUpload', '|',
                    'bulletedList', 'numberedList', '|',
                    'blockQuote', 'insertTable', '|',
                    'undo', 'redo'
                ]
            },
            image: {
                toolbar: ['imageTextAlternative', 'imageStyle:inline', 'imageStyle:block']
            }
        })
        .then(editor => {
            editor.ui.view.editable.element.style.minHeight = '300px';
            editorInstances[elementId] = editor;

            editor.model.document.on('change:data', () => {
                element.value = editor.getData();
            });
        })
        .catch(error => {
            console.error(`Error initializing editor for ${elementId}:`, error);
            element.style.display = 'block';
            element.style.minHeight = '300px';
        });
}

// Initialize editors when tab is shown
document.addEventListener('DOMContentLoaded', function() {
    // Initialize English editor immediately
    initializeEditor('en_content');

    // Initialize Dutch editor when its tab is shown for the first time
    let lnEditorInitialized = false;
    const lnTab = document.getElementById('ln-tab');
    if (lnTab) {
        lnTab.addEventListener('shown.bs.tab', function() {
            if (!lnEditorInitialized) {
                initializeEditor('nl_content');
                lnEditorInitialized = true;
            }
        });
    }

    // Character counter for name
    const nameInput = document.getElementById('name');
    const nameCounter = document.getElementById('nameCounter');
    if (nameInput && nameCounter) {
        nameInput.addEventListener('input', function() {
            nameCounter.textContent = this.value.length;
        });
    }
});

// Slug generation
function generateSlug() {
    const name = document.getElementById('name').value;
    const slug = name.toLowerCase()
        .replace(/[^a-z0-9 -]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .replace(/^-|-$/g, '');

    document.getElementById('slug').value = slug;
}

// Auto-generate slug from name
const nameInput = document.getElementById('name');
const slugInput = document.getElementById('slug');

if (nameInput) {
    nameInput.addEventListener('input', function() {
        if (!slugInput.value) {
            generateSlug();
        }
    });
}

// Save as draft
function saveDraft() {
    document.getElementById('status').value = 'draft';
    updateEditorContents();
    document.getElementById('pageForm').submit();
}

// Update all editor contents before form submit
function updateEditorContents() {
    for (const [id, editor] of Object.entries(editorInstances)) {
        if (editor) {
            document.getElementById(id).value = editor.getData();
        }
    }
}

// Form submission handler
const pageForm = document.getElementById('pageForm');
if (pageForm) {
    pageForm.addEventListener('submit', function(e) {
        updateEditorContents();

        const submitButton = this.querySelector('button[type="submit"]');
        if (submitButton) {
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Updating...';
            submitButton.disabled = true;
        }
    });
}
</script>
@endpush
