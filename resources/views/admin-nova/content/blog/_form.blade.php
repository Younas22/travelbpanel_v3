{{-- Expects: $post (BlogPost|null — null on create, the model on edit),
     $categories, $tags (edit only, comma-joined tag names). Classic's
     create.blade.php has no slug input at all (auto-generated server-side
     on store); edit.blade.php adds one. Preserved exactly — the slug field
     below only renders when editing. --}}
@php $isEdit = (bool) $post; @endphp

<form action="{{ $isEdit ? route('admin.content.blog.update', $post->id) : route('admin.content.blog.store') }}"
      method="POST" enctype="multipart/form-data" id="blogForm">
    @csrf
    @if($isEdit)
        @method('PATCH')
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <div class="lg:col-span-2 space-y-5">
            <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 sm:p-5">
                <div class="mb-4">
                    <label class="bl-label" for="title">Post Title <span class="bl-required">*</span></label>
                    <input type="text" name="title" id="title" class="bl-input @error('title') is-invalid @enderror"
                           value="{{ old('title', $post->title ?? '') }}" required placeholder="Enter an engaging title for your blog post">
                    <p class="bl-help"><span id="titleCounter">0</span>/100 characters (Recommended: 50-60)</p>
                    @error('title') <p class="bl-error">{{ $message }}</p> @enderror
                </div>

                @if($isEdit)
                    <div class="mb-4">
                        <label class="bl-label" for="slug">URL Slug <span class="bl-required">*</span></label>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-novamuted flex-shrink-0">{{ url('/blog/') }}/</span>
                            <input type="text" name="slug" id="slug" class="bl-input @error('slug') is-invalid @enderror"
                                   value="{{ old('slug', $post->slug) }}" required>
                            <button type="button" class="bl-icon-btn flex-shrink-0" onclick="generateSlug()" data-tooltip="Regenerate">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 1 2.64 6.36M3 12v6m0-6h6"/></svg>
                            </button>
                        </div>
                        <p class="bl-help">URL-friendly version of the title (auto-generated from title)</p>
                        @error('slug') <p class="bl-error">{{ $message }}</p> @enderror
                    </div>
                @endif

                <div class="mb-4">
                    <label class="bl-label" for="content">Content <span class="bl-required">*</span></label>
                    <textarea name="content" id="content" class="hidden" required>{{ old('content', $post->content ?? '') }}</textarea>
                    <p class="bl-help">Use the rich text editor to format your content. You can upload images, add links, and more.</p>
                    @error('content') <p class="bl-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="bl-label mb-0" for="excerpt">Excerpt</label>
                        <button type="button" class="text-[11px] font-semibold text-novablue auto-excerpt-btn" onclick="autoGenerateExcerpt()">
                            <svg class="w-3 h-3 inline" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3-1.5 4.5L6 9l4.5 1.5L12 15l1.5-4.5L18 9l-4.5-1.5L12 3Z"/></svg>
                            Auto-generate
                        </button>
                    </div>
                    <textarea name="excerpt" id="excerpt" class="bl-input" rows="3"
                              placeholder="A brief summary of your post (will be auto-generated if left empty)">{{ old('excerpt', $post->excerpt ?? '') }}</textarea>
                    <p class="bl-help"><span id="excerptCounter">0</span>/300 characters - Brief summary for previews and SEO</p>
                    @error('excerpt') <p class="bl-error">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="space-y-5">
            {{-- Publishing Options --}}
            <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 sm:p-5 space-y-4">
                <h2 class="text-sm font-semibold text-novatext">Publishing Options</h2>
                <div>
                    <label class="bl-label" for="status">Status <span class="bl-required">*</span></label>
                    <select name="status" id="status" class="bl-input @error('status') is-invalid @enderror" required>
                        <option value="draft" {{ old('status', $post->status ?? 'draft') == 'draft' ? 'selected' : '' }}>📝 Draft</option>
                        <option value="published" {{ old('status', $post->status ?? '') == 'published' ? 'selected' : '' }}>🚀 Published</option>
                        <option value="scheduled" {{ old('status', $post->status ?? '') == 'scheduled' ? 'selected' : '' }}>⏰ Scheduled</option>
                    </select>
                    @error('status') <p class="bl-error">{{ $message }}</p> @enderror
                </div>

                <div id="scheduled_at_field" class="{{ old('status', $post->status ?? '') == 'scheduled' ? '' : 'hidden' }}">
                    <label class="bl-label" for="scheduled_at">Scheduled Date/Time</label>
                    <input type="datetime-local" name="scheduled_at" id="scheduled_at" class="bl-input @error('scheduled_at') is-invalid @enderror"
                           value="{{ old('scheduled_at', $isEdit && $post->scheduled_at ? $post->scheduled_at->format('Y-m-d\TH:i') : '') }}">
                    @error('scheduled_at') <p class="bl-error">{{ $message }}</p> @enderror
                </div>

                <label class="flex items-center gap-3">
                    <span class="bl-switch">
                        <input type="checkbox" name="is_featured" id="is_featured" value="1" {{ old('is_featured', $post->is_featured ?? false) ? 'checked' : '' }}>
                        <span class="bl-slider"></span>
                    </span>
                    <span class="text-xs font-semibold text-novatext">⭐ Featured Post</span>
                </label>

                <label class="flex items-center gap-3">
                    <span class="bl-switch">
                        <input type="checkbox" name="allow_comments" id="allow_comments" value="1" {{ old('allow_comments', $post->allow_comments ?? true) ? 'checked' : '' }}>
                        <span class="bl-slider"></span>
                    </span>
                    <span class="text-xs font-semibold text-novatext">💬 Allow Comments</span>
                </label>

                @if($isEdit)
                    <div class="pt-3 border-t border-novaborder space-y-1 text-[11px] text-novamuted">
                        <p><strong class="text-novatext">Created:</strong> {{ $post->created_at->format('M j, Y g:i A') }}</p>
                        <p><strong class="text-novatext">Updated:</strong> {{ $post->updated_at->format('M j, Y g:i A') }}</p>
                        @if($post->published_at)
                            <p><strong class="text-novatext">Published:</strong> {{ $post->published_at->format('M j, Y g:i A') }}</p>
                        @endif
                        <p><strong class="text-novatext">Views:</strong> {{ $post->views ?? 0 }}</p>
                    </div>
                @endif
            </div>

            {{-- Category & Tags --}}
            <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 sm:p-5 space-y-4">
                <h2 class="text-sm font-semibold text-novatext">Category &amp; Tags</h2>
                <div>
                    <label class="bl-label" for="category_id">Category <span class="bl-required">*</span></label>
                    <select name="category_id" id="category_id" class="bl-input @error('category_id') is-invalid @enderror" required>
                        <option value="">Select Category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', $post->category_id ?? '') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id') <p class="bl-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="bl-label" for="tags">Tags</label>
                    <input type="text" name="tags" id="tags" class="bl-input @error('tags') is-invalid @enderror"
                           value="{{ old('tags', $isEdit ? ($tags ?? '') : '') }}" placeholder="travel, tips, guide, vacation">
                    <p class="bl-help">Separate tags with commas</p>
                    @error('tags') <p class="bl-error">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Featured Image --}}
            <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 sm:p-5">
                <h2 class="text-sm font-semibold text-novatext mb-4">Featured Image</h2>

                @if($isEdit && $post->featured_image)
                    <div class="mb-4">
                        <p class="bl-label">Current Featured Image</p>
                        <img src="{{ asset('public/' . $post->featured_image) }}" class="w-full rounded-xl mb-2" alt="Current featured image">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="remove_featured_image" id="remove_featured_image">
                            <span class="text-xs font-semibold text-novadanger">Remove current image</span>
                        </label>
                    </div>
                @elseif(!$isEdit)
                    <div class="text-center text-novamuted mb-4">
                        <svg class="w-10 h-10 mx-auto mb-2 text-novaborder" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg>
                        <p class="text-xs">No featured image set</p>
                    </div>
                @endif

                <label class="bl-label">{{ $isEdit && $post->featured_image ? 'Replace with New Image' : 'Upload Featured Image' }}</label>
                <div class="bl-dropzone" onclick="document.getElementById('featured_image').click()">
                    <svg class="w-7 h-7 mx-auto mb-2 text-novamuted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 15V3m0 0 4 4m-4-4-4 4M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"/></svg>
                    <p class="text-xs text-novamuted">Click to upload {{ $isEdit && $post->featured_image ? 'new' : '' }} image</p>
                    <p class="text-[10px] text-novamuted mt-1">Max 2MB (JPG, PNG, GIF)</p>
                </div>
                <input type="file" name="featured_image" id="featured_image" class="hidden @error('featured_image') is-invalid @enderror"
                       accept="image/*" onchange="{{ $isEdit ? 'previewNewImage(this)' : 'previewImage(this)' }}">
                <div id="{{ $isEdit ? 'newImagePreview' : 'imagePreview' }}" class="mt-3"></div>
                @error('featured_image') <p class="bl-error">{{ $message }}</p> @enderror
            </div>

            {{-- SEO Settings --}}
            <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 sm:p-5 space-y-4">
                <h2 class="text-sm font-semibold text-novatext">SEO Settings</h2>
                <div>
                    <label class="bl-label" for="seo_title">SEO Title</label>
                    <input type="text" name="seo_title" id="seo_title" class="bl-input @error('seo_title') is-invalid @enderror"
                           value="{{ old('seo_title', $post->seo_title ?? '') }}" placeholder="SEO optimized title">
                    <p class="bl-help"><span id="seoTitleCounter">0</span>/60 characters</p>
                    @error('seo_title') <p class="bl-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="bl-label" for="meta_description">Meta Description</label>
                    <textarea name="meta_description" id="meta_description" class="bl-input @error('meta_description') is-invalid @enderror" rows="3"
                              placeholder="Brief description for search engines">{{ old('meta_description', $post->meta_description ?? '') }}</textarea>
                    <p class="bl-help"><span id="metaDescCounter">0</span>/160 characters</p>
                    @error('meta_description') <p class="bl-error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="space-y-2">
                <button type="submit" class="bl-btn-nova bl-btn-primary w-full py-3 text-sm font-semibold">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 12 6 6L21 6"/></svg>
                    {{ $isEdit ? 'Update Post' : 'Publish Post' }}
                </button>
                <button type="button" class="bl-btn-nova w-full py-3 text-sm font-semibold" onclick="saveDraft()">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"/><path d="M17 21v-8H7v8M7 3v5h8"/></svg>
                    Save as Draft
                </button>
                @if($isEdit && $post->status !== 'published')
                    <button type="button" class="bl-btn-nova w-full py-3 text-sm font-semibold" onclick="previewPost()">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M2.5 12S6 5 12 5s9.5 7 9.5 7-3.5 7-9.5 7-9.5-7-9.5-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                        Preview
                    </button>
                @endif
                <a href="{{ route('admin.content.blog.index') }}" class="bl-btn-nova w-full py-3 text-sm font-semibold text-center">Cancel</a>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script>
let editorInstance;

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
        xhr.open('POST', '{{ route("admin.content.blog.upload-image") }}', true);
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
            xhr.upload.addEventListener('progress', evt => { if (evt.lengthComputable) { loader.uploadTotal = evt.total; loader.uploaded = evt.loaded; } });
        }
    }
    _sendRequest(file) { const data = new FormData(); data.append('upload', file); this.xhr.send(data); }
}

function MyCustomUploadAdapterPlugin(editor) {
    editor.plugins.get('FileRepository').createUploadAdapter = (loader) => new MyUploadAdapter(loader);
}

ClassicEditor.create(document.querySelector('#content'), {
    extraPlugins: [MyCustomUploadAdapterPlugin],
    toolbar: {
        items: ['heading', '|', 'bold', 'italic', 'underline', 'strikethrough', '|', 'link', 'imageUpload', 'mediaEmbed', '|',
                'bulletedList', 'numberedList', 'outdent', 'indent', '|', 'blockQuote', 'insertTable', 'codeBlock', '|',
                'undo', 'redo', '|', 'alignment', 'fontColor', 'fontBackgroundColor', '|', 'removeFormat', 'specialCharacters']
    },
    heading: {
        options: [
            { model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
            { model: 'heading1', view: 'h1', title: 'Heading 1', class: 'ck-heading_heading1' },
            { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' },
            { model: 'heading3', view: 'h3', title: 'Heading 3', class: 'ck-heading_heading3' },
            { model: 'heading4', view: 'h4', title: 'Heading 4', class: 'ck-heading_heading4' },
        ]
    },
    image: { toolbar: ['imageTextAlternative', 'imageStyle:inline', 'imageStyle:block', 'imageStyle:side', 'linkImage', 'imageResize'] },
    table: { contentToolbar: ['tableColumn', 'tableRow', 'mergeTableCells', 'tableCellProperties', 'tableProperties'] },
    link: { decorators: { openInNewTab: { mode: 'manual', label: 'Open in a new tab', attributes: { target: '_blank', rel: 'noopener noreferrer' } } } },
    mediaEmbed: { previewsInData: true },
})
.then(editor => {
    editor.ui.view.editable.element.style.minHeight = '400px';
    editor.ui.view.editable.element.style.maxHeight = '600px';
    editorInstance = editor;
    editor.model.document.on('change:data', () => { document.querySelector('#content').value = editor.getData(); });
    const initialContent = document.querySelector('#content').value;
    if (initialContent) { editor.setData(initialContent); }
})
.catch(error => {
    console.error('CKEditor initialization error:', error);
    const contentTextarea = document.querySelector('#content');
    contentTextarea.classList.remove('hidden');
    contentTextarea.style.minHeight = '400px';
});

function setupCharacterCounters() {
    const bind = (inputId, counterId, max) => {
        const input = document.getElementById(inputId);
        const counter = document.getElementById(counterId);
        if (!input || !counter) return;
        input.addEventListener('input', function() {
            counter.textContent = this.value.length;
            counter.style.color = this.value.length > max ? '#EF4444' : '';
        });
    };
    bind('title', 'titleCounter', 100);
    bind('excerpt', 'excerptCounter', 300);
    bind('seo_title', 'seoTitleCounter', 60);
    bind('meta_description', 'metaDescCounter', 160);
}

@if($isEdit)
function generateSlug() {
    const title = document.getElementById('title').value;
    const slug = title.toLowerCase().replace(/[^a-z0-9 -]/g, '').replace(/\s+/g, '-').replace(/-+/g, '-').replace(/^-|-$/g, '');
    document.getElementById('slug').value = slug;
}
const titleInputForSlug = document.getElementById('title');
const slugInputEl = document.getElementById('slug');
if (titleInputForSlug && slugInputEl) {
    titleInputForSlug.addEventListener('input', function() { if (!slugInputEl.value) { generateSlug(); } });
}
@endif

const statusSelectEl = document.getElementById('status');
if (statusSelectEl) {
    statusSelectEl.addEventListener('change', function() {
        const scheduledField = document.getElementById('scheduled_at_field');
        const scheduledInput = document.getElementById('scheduled_at');
        if (this.value === 'scheduled') { scheduledField.classList.remove('hidden'); scheduledInput.required = true; }
        else { scheduledField.classList.add('hidden'); scheduledInput.required = false; }
    });
}

function previewImage(input) {
    const previewDiv = document.getElementById('imagePreview');
    if (input.files && input.files[0]) {
        const file = input.files[0];
        if (file.size > 2 * 1024 * 1024) { alert('File size must be less than 2MB'); input.value = ''; return; }
        if (!file.type.match('image.*')) { alert('Please select an image file'); input.value = ''; return; }
        const reader = new FileReader();
        reader.onload = function(e) {
            previewDiv.innerHTML = `<img src="${e.target.result}" class="w-full rounded-xl" alt="Preview">
                <div class="flex items-center justify-between mt-2">
                    <span class="text-[11px] text-novamuted">${file.name} (${(file.size / 1024).toFixed(1)} KB)</span>
                    <button type="button" class="bl-icon-btn bl-icon-danger" onclick="removeImage()"><svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg></button>
                </div>`;
        };
        reader.readAsDataURL(file);
    }
}
function removeImage() { document.getElementById('featured_image').value = ''; document.getElementById('imagePreview').innerHTML = ''; }

function previewNewImage(input) {
    const previewDiv = document.getElementById('newImagePreview');
    if (input.files && input.files[0]) {
        const file = input.files[0];
        if (file.size > 2 * 1024 * 1024) { alert('File size must be less than 2MB'); input.value = ''; return; }
        if (!file.type.match('image.*')) { alert('Please select an image file'); input.value = ''; return; }
        const reader = new FileReader();
        reader.onload = function(e) {
            previewDiv.innerHTML = `<p class="text-[11px] font-semibold text-novasuccess mb-1">New Image Preview:</p>
                <img src="${e.target.result}" class="w-full rounded-xl" alt="New image preview">
                <div class="flex items-center justify-between mt-2">
                    <span class="text-[11px] text-novamuted">${file.name} (${(file.size / 1024).toFixed(1)} KB)</span>
                    <button type="button" class="bl-icon-btn bl-icon-danger" onclick="removeNewImage()"><svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg></button>
                </div>`;
        };
        reader.readAsDataURL(file);
    }
}
function removeNewImage() { document.getElementById('featured_image').value = ''; document.getElementById('newImagePreview').innerHTML = ''; }

function saveDraft() {
    document.getElementById('status').value = 'draft';
    if (editorInstance) { document.querySelector('#content').value = editorInstance.getData(); }
    document.getElementById('blogForm').submit();
}

function previewPost() { alert('Preview functionality can be implemented based on your requirements'); }

document.getElementById('blogForm').addEventListener('submit', function(e) {
    if (editorInstance) { document.querySelector('#content').value = editorInstance.getData(); }
    const title = document.getElementById('title').value.trim();
    const content = document.querySelector('#content').value.trim();
    if (!title) { e.preventDefault(); alert('Please enter a title for your blog post.'); document.getElementById('title').focus(); return; }
    if (!content || content === '<p>&nbsp;</p>' || content === '') {
        e.preventDefault(); alert('Please add some content to your blog post.');
        if (editorInstance) { editorInstance.editing.view.focus(); }
        return;
    }
    const submitButton = this.querySelector('button[type="submit"]');
    if (submitButton) { submitButton.disabled = true; }
});

function autoGenerateExcerpt() {
    if (editorInstance) {
        const excerptInput = document.getElementById('excerpt');
        if (!excerptInput.value && editorInstance.getData()) {
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = editorInstance.getData();
            const plainText = tempDiv.textContent || tempDiv.innerText || '';
            excerptInput.value = plainText.length > 150 ? plainText.substring(0, 147) + '...' : plainText;
            excerptInput.dispatchEvent(new Event('input'));
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    setupCharacterCounters();
    ['title', 'excerpt', 'seo_title', 'meta_description'].forEach(id => {
        const counterMap = { title: 'titleCounter', excerpt: 'excerptCounter', seo_title: 'seoTitleCounter', meta_description: 'metaDescCounter' };
        const inputEl = document.getElementById(id);
        const counterEl = document.getElementById(counterMap[id]);
        if (inputEl && counterEl) { counterEl.textContent = inputEl.value.length; }
    });
});
</script>
@endpush
