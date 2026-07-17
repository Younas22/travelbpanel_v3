@extends('admin.layouts.app')

@section('title', 'Add Blog')

@section('styles')
<!-- CKEditor 5 Styles -->
<style>
.ck-editor__editable_inline {
    min-height: 400px;
}
.ck.ck-editor {
    max-width: 100%;
}
.ck-content {
    font-size: 16px;
    line-height: 1.6;
}
.image-preview {
    max-width: 200px;
    margin-top: 10px;
    border-radius: 8px;
    border: 2px solid #dee2e6;
}
.form-group {
    margin-bottom: 1.5rem;
}
.card {
    box-shadow: 0 0 20px rgba(0,0,0,0.1);
    border: none;
    border-radius: 10px;
}
.card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 10px 10px 0 0 !important;
}
.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    padding: 10px 25px;
    border-radius: 25px;
}
.btn-secondary {
    padding: 10px 25px;
    border-radius: 25px;
}
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-plus-circle me-2"></i>Create New Blog Post
                    </h3>
                    <a href="{{ route('admin.content.blog.index') }}" class="btn btn-light btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> Back to Posts
                    </a>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.content.blog.store') }}" method="POST" enctype="multipart/form-data" id="blogForm">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-8">
                                <!-- Post Title -->
                                <div class="form-group">
                                    <label for="title" class="form-label">
                                        <i class="fas fa-heading me-1"></i>Post Title <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" 
                                           value="{{ old('title') }}" required placeholder="Enter an engaging title for your blog post">
                                    @error('title')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                    <div class="form-text">
                                        <small class="text-muted">
                                            <span id="titleCounter">0</span>/100 characters (Recommended: 50-60)
                                        </small>
                                    </div>
                                </div>

                                <!-- Content Editor -->
                                <div class="form-group">
                                    <label for="content" class="form-label">
                                        <i class="fas fa-edit me-1"></i>Content <span class="text-danger">*</span>
                                    </label>
                                    <textarea name="content" id="content" class="form-control @error('content') is-invalid @enderror" 
                                              required style="display: none;">{{ old('content') }}</textarea>
                                    @error('content')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                    <div class="form-text">
                                        <small class="text-muted">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Use the rich text editor to format your content. You can upload images, add links, and more.
                                        </small>
                                    </div>
                                </div>

                                <!-- Excerpt -->
                                <div class="form-group">
                                    <label for="excerpt" class="form-label">
                                        <i class="fas fa-quote-left me-1"></i>Excerpt
                                    </label>
                                    <textarea name="excerpt" id="excerpt" class="form-control @error('excerpt') is-invalid @enderror" 
                                              rows="3" placeholder="A brief summary of your post (will be auto-generated if left empty)">{{ old('excerpt') }}</textarea>
                                    <div class="form-text">
                                        <small class="text-muted">
                                            <span id="excerptCounter">0</span>/300 characters - Brief summary for previews and SEO
                                        </small>
                                    </div>
                                    @error('excerpt')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
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
                                                <option value="draft" {{ old('status', 'draft') == 'draft' ? 'selected' : '' }}>
                                                    📝 Draft
                                                </option>
                                                <option value="published" {{ old('status') == 'published' ? 'selected' : '' }}>
                                                    🚀 Published
                                                </option>
                                                <option value="scheduled" {{ old('status') == 'scheduled' ? 'selected' : '' }}>
                                                    ⏰ Scheduled
                                                </option>
                                            </select>
                                            @error('status')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>

                                        <div class="form-group" id="scheduled_at_field" style="display: none;">
                                            <label for="scheduled_at" class="form-label">Scheduled Date/Time</label>
                                            <input type="datetime-local" name="scheduled_at" id="scheduled_at" 
                                                   class="form-control @error('scheduled_at') is-invalid @enderror" 
                                                   value="{{ old('scheduled_at') }}">
                                            @error('scheduled_at')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <div class="form-check">
                                                <input type="checkbox" name="is_featured" id="is_featured" 
                                                       class="form-check-input" value="1" {{ old('is_featured') ? 'checked' : '' }}>
                                                <label class="form-check-label" for="is_featured">
                                                    ⭐ Featured Post
                                                </label>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <div class="form-check">
                                                <input type="checkbox" name="allow_comments" id="allow_comments" 
                                                       class="form-check-input" value="1" {{ old('allow_comments', true) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="allow_comments">
                                                    💬 Allow Comments
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Category & Tags -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h6 class="mb-0">
                                            <i class="fas fa-tags me-1"></i>Category & Tags
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                                            <select name="category_id" id="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                                                <option value="">Select Category</option>
                                                @foreach($categories as $category)
                                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                                        {{ $category->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('category_id')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label for="tags" class="form-label">Tags</label>
                                            <input type="text" name="tags" id="tags" class="form-control @error('tags') is-invalid @enderror" 
                                                   value="{{ old('tags') }}" placeholder="travel, tips, guide, vacation">
                                            <div class="form-text">
                                                <small class="text-muted">Separate tags with commas</small>
                                            </div>
                                            @error('tags')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Featured Image -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h6 class="mb-0">
                                            <i class="fas fa-image me-1"></i>Featured Image
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <div class="image-upload-area" onclick="document.getElementById('featured_image').click()" 
                                                 style="border: 2px dashed #dee2e6; padding: 20px; text-align: center; cursor: pointer; border-radius: 8px;">
                                                <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                                                <p class="mb-0 text-muted">Click to upload featured image</p>
                                                <small class="text-muted">Max 2MB (JPG, PNG, GIF)</small>
                                            </div>
                                            <input type="file" name="featured_image" id="featured_image" 
                                                   class="d-none @error('featured_image') is-invalid @enderror"
                                                   accept="image/*" onchange="previewImage(this)">
                                            <div id="imagePreview"></div>
                                            @error('featured_image')
                                                <span class="invalid-feedback d-block" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- SEO Settings -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h6 class="mb-0">
                                            <i class="fas fa-search me-1"></i>SEO Settings
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="seo_title" class="form-label">SEO Title</label>
                                            <input type="text" name="seo_title" id="seo_title" 
                                                   class="form-control @error('seo_title') is-invalid @enderror" 
                                                   value="{{ old('seo_title') }}" placeholder="SEO optimized title">
                                            <div class="form-text">
                                                <small class="text-muted">
                                                    <span id="seoTitleCounter">0</span>/60 characters
                                                </small>
                                            </div>
                                            @error('seo_title')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label for="meta_description" class="form-label">Meta Description</label>
                                            <textarea name="meta_description" id="meta_description" 
                                                      class="form-control @error('meta_description') is-invalid @enderror" 
                                                      rows="3" placeholder="Brief description for search engines">{{ old('meta_description') }}</textarea>
                                            <div class="form-text">
                                                <small class="text-muted">
                                                    <span id="metaDescCounter">0</span>/160 characters
                                                </small>
                                            </div>
                                            @error('meta_description')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center mt-4 pt-3" style="border-top: 1px solid #dee2e6;">
                                    <div>
                                        <button type="button" class="btn btn-outline-secondary" onclick="saveDraft()">
                                            <i class="fas fa-save me-1"></i> Save as Draft
                                        </button>
                                    </div>
                                    <div>
                                        <button type="button" class="btn btn-secondary me-2" onclick="window.location='{{ route('admin.content.blog.index') }}'">
                                            <i class="fas fa-times me-1"></i> Cancel
                                        </button>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-paper-plane me-1"></i> Publish Post
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
let editorInstance;

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
        xhr.open('POST', '{{ route("admin.content.blog.upload-image") }}', true);
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

// Initialize CKEditor 5
ClassicEditor
    .create(document.querySelector('#content'), {
        extraPlugins: [MyCustomUploadAdapterPlugin],
        toolbar: {
            items: [
                'heading', '|',
                'bold', 'italic', 'underline', 'strikethrough', '|',
                'link', 'imageUpload', 'mediaEmbed', '|',
                'bulletedList', 'numberedList', 'outdent', 'indent', '|',
                'blockQuote', 'insertTable', 'codeBlock', '|',
                'undo', 'redo', '|',
                'alignment', 'fontColor', 'fontBackgroundColor', '|',
                'removeFormat', 'specialCharacters'
            ]
        },
        heading: {
            options: [
                { model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
                { model: 'heading1', view: 'h1', title: 'Heading 1', class: 'ck-heading_heading1' },
                { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' },
                { model: 'heading3', view: 'h3', title: 'Heading 3', class: 'ck-heading_heading3' },
                { model: 'heading4', view: 'h4', title: 'Heading 4', class: 'ck-heading_heading4' }
            ]
        },
        image: {
            toolbar: [
                'imageTextAlternative', 'imageStyle:inline', 'imageStyle:block', 'imageStyle:side',
                'linkImage', 'imageResize'
            ]
        },
        table: {
            contentToolbar: [
                'tableColumn', 'tableRow', 'mergeTableCells',
                'tableCellProperties', 'tableProperties'
            ]
        },
        link: {
            decorators: {
                openInNewTab: {
                    mode: 'manual',
                    label: 'Open in a new tab',
                    attributes: {
                        target: '_blank',
                        rel: 'noopener noreferrer'
                    }
                }
            }
        },
        mediaEmbed: {
            previewsInData: true
        }
    })
    .then(editor => {
        // Set editor height
        editor.ui.view.editable.element.style.minHeight = '400px';
        editor.ui.view.editable.element.style.maxHeight = '600px';
        
        editorInstance = editor;
        
        // Update content on change
        editor.model.document.on('change:data', () => {
            document.querySelector('#content').value = editor.getData();
        });

        // Set initial content if editing
        const initialContent = document.querySelector('#content').value;
        if (initialContent) {
            editor.setData(initialContent);
        }

        console.log('✅ CKEditor 5 initialized successfully with image upload support!');
    })
    .catch(error => {
        console.error('❌ CKEditor initialization error:', error);
        
        // Fallback to simple textarea if CKEditor fails
        const contentTextarea = document.querySelector('#content');
        contentTextarea.style.display = 'block';
        contentTextarea.style.minHeight = '400px';
        alert('Rich text editor failed to load. Using simple text editor instead.');
    });

// Character counters
function setupCharacterCounters() {
    // Title counter
    const titleInput = document.getElementById('title');
    const titleCounter = document.getElementById('titleCounter');
    if (titleInput && titleCounter) {
        titleInput.addEventListener('input', function() {
            titleCounter.textContent = this.value.length;
            if (this.value.length > 100) {
                titleCounter.style.color = 'red';
            } else if (this.value.length > 60) {
                titleCounter.style.color = 'orange';
            } else {
                titleCounter.style.color = '';
            }
        });
    }

    // Excerpt counter
    const excerptInput = document.getElementById('excerpt');
    const excerptCounter = document.getElementById('excerptCounter');
    if (excerptInput && excerptCounter) {
        excerptInput.addEventListener('input', function() {
            excerptCounter.textContent = this.value.length;
            if (this.value.length > 300) {
                excerptCounter.style.color = 'red';
            } else {
                excerptCounter.style.color = '';
            }
        });
    }

    // SEO Title counter
    const seoTitleInput = document.getElementById('seo_title');
    const seoTitleCounter = document.getElementById('seoTitleCounter');
    if (seoTitleInput && seoTitleCounter) {
        seoTitleInput.addEventListener('input', function() {
            seoTitleCounter.textContent = this.value.length;
            if (this.value.length > 60) {
                seoTitleCounter.style.color = 'red';
            } else {
                seoTitleCounter.style.color = '';
            }
        });
    }

    // Meta Description counter
    const metaDescInput = document.getElementById('meta_description');
    const metaDescCounter = document.getElementById('metaDescCounter');
    if (metaDescInput && metaDescCounter) {
        metaDescInput.addEventListener('input', function() {
            metaDescCounter.textContent = this.value.length;
            if (this.value.length > 160) {
                metaDescCounter.style.color = 'red';
            } else {
                metaDescCounter.style.color = '';
            }
        });
    }
}

// Status change handler
const statusSelect = document.getElementById('status');
if (statusSelect) {
    statusSelect.addEventListener('change', function() {
        const scheduledField = document.getElementById('scheduled_at_field');
        const scheduledInput = document.getElementById('scheduled_at');
        
        if (this.value === 'scheduled') {
            scheduledField.style.display = 'block';
            scheduledInput.required = true;
        } else {
            scheduledField.style.display = 'none';
            scheduledInput.required = false;
        }
    });
}

// Image preview
function previewImage(input) {
    const previewDiv = document.getElementById('imagePreview');
    if (input.files && input.files[0]) {
        const file = input.files[0];
        
        // Check file size (2MB = 2 * 1024 * 1024 bytes)
        if (file.size > 2 * 1024 * 1024) {
            alert('File size must be less than 2MB');
            input.value = '';
            return;
        }
        
        // Check file type
        if (!file.type.match('image.*')) {
            alert('Please select an image file');
            input.value = '';
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            previewDiv.innerHTML = `
                <img src="${e.target.result}" class="image-preview" alt="Preview">
                <div class="mt-2">
                    <small class="text-muted">${file.name} (${(file.size / 1024).toFixed(1)} KB)</small>
                    <button type="button" class="btn btn-sm btn-danger ms-2" onclick="removeImage()">
                        <i class="fas fa-trash"></i> Remove
                    </button>
                </div>
            `;
        };
        reader.readAsDataURL(file);
    }
}

// Remove image
function removeImage() {
    const imageInput = document.getElementById('featured_image');
    const previewDiv = document.getElementById('imagePreview');
    
    imageInput.value = '';
    previewDiv.innerHTML = '';
}

// Save as draft
function saveDraft() {
    const statusSelect = document.getElementById('status');
    statusSelect.value = 'draft';
    
    // Update editor content before saving
    if (editorInstance) {
        document.querySelector('#content').value = editorInstance.getData();
    }
    
    document.getElementById('blogForm').submit();
}

// Form submission handler
const blogForm = document.getElementById('blogForm');
if (blogForm) {
    blogForm.addEventListener('submit', function(e) {
        // Update textarea with editor content before submit
        if (editorInstance) {
            const editorData = editorInstance.getData();
            document.querySelector('#content').value = editorData;
        }
        
        // Validate required fields
        const title = document.getElementById('title').value.trim();
        const content = document.querySelector('#content').value.trim();
        
        if (!title) {
            e.preventDefault();
            alert('Please enter a title for your blog post.');
            document.getElementById('title').focus();
            return;
        }
        
        if (!content || content === '<p>&nbsp;</p>' || content === '') {
            e.preventDefault();
            alert('Please add some content to your blog post.');
            if (editorInstance) {
                editorInstance.editing.view.focus();
            }
            return;
        }

        // Show loading state
        const submitButton = this.querySelector('button[type="submit"]');
        if (submitButton) {
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Publishing...';
            submitButton.disabled = true;
        }
    });
}

// Initialize everything when page loads
document.addEventListener('DOMContentLoaded', function() {
    setupCharacterCounters();
    
    // Initialize counters with existing values
    const elements = [
        { input: 'title', counter: 'titleCounter' },
        { input: 'excerpt', counter: 'excerptCounter' },
        { input: 'seo_title', counter: 'seoTitleCounter' },
        { input: 'meta_description', counter: 'metaDescCounter' }
    ];
    
    elements.forEach(element => {
        const inputEl = document.getElementById(element.input);
        const counterEl = document.getElementById(element.counter);
        
        if (inputEl && counterEl) {
            counterEl.textContent = inputEl.value.length;
        }
    });
    
    console.log('🚀 Blog form initialized successfully!');
});

// Auto-generate SEO title from post title
const titleInput = document.getElementById('title');
const seoTitleInput = document.getElementById('seo_title');

if (titleInput && seoTitleInput) {
    titleInput.addEventListener('input', function() {
        if (!seoTitleInput.value) {
            seoTitleInput.value = this.value;
            // Trigger the counter update
            seoTitleInput.dispatchEvent(new Event('input'));
        }
    });
}

// Auto-generate excerpt from content (if CKEditor is available)
function autoGenerateExcerpt() {
    if (editorInstance) {
        const excerptInput = document.getElementById('excerpt');
        if (!excerptInput.value && editorInstance.getData()) {
            // Get plain text from editor content
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = editorInstance.getData();
            const plainText = tempDiv.textContent || tempDiv.innerText || '';
            
            // Generate excerpt (first 150 characters)
            if (plainText.length > 150) {
                excerptInput.value = plainText.substring(0, 147) + '...';
            } else {
                excerptInput.value = plainText;
            }
            
            // Trigger counter update
            excerptInput.dispatchEvent(new Event('input'));
        }
    }
}

// Add auto-excerpt generation button
function addAutoExcerptButton() {
    const excerptGroup = document.getElementById('excerpt').closest('.form-group');
    if (excerptGroup) {
        const autoButton = document.createElement('button');
        autoButton.type = 'button';
        autoButton.className = 'btn btn-sm btn-outline-secondary mt-1';
        autoButton.innerHTML = '<i class="fas fa-magic me-1"></i> Auto-generate';
        autoButton.onclick = autoGenerateExcerpt;
        
        excerptGroup.appendChild(autoButton);
    }
}

// Add auto-excerpt button after a short delay
setTimeout(addAutoExcerptButton, 1000);
</script>
@endpush

