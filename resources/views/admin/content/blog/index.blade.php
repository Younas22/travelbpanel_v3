@extends('admin.layouts.app')

@section('title', 'Blog Management')

@section('content')
<div class="content-area p-4">
    <!-- Page Header with Stats -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h2 class="mb-1">Blog Management</h2>
                <p class="text-muted mb-0">Create and manage travel blog content</p>
            </div>
            <div class="col-md-6">
                <div class="text-end">
                    <button class="btn btn-outline-primary modern-btn me-2" data-bs-toggle="modal" data-bs-target="#manageCategoriesModal">
                        <i class="bi bi-tags"></i> Manage Categories
                    </button>
                    <a href="{{ route('admin.content.blog.create') }}" class="btn btn-primary modern-btn">
                        <i class="bi bi-plus-circle"></i> Create New Post
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="stats-card">
                <div class="d-flex align-items-center">
                    <div class="stats-icon icon-blue">
                        <i class="bi bi-file-earmark-text"></i>
                    </div>
                    <div class="ms-3">
                        <div class="small text-muted">Total Posts</div>
                        <div class="h4 mb-0">{{ number_format($stats['total_posts']) }}</div>
                        <div class="small {{ $stats['posts_growth'] >= 0 ? 'text-success' : 'text-danger' }}">
                            <i class="bi bi-arrow-{{ $stats['posts_growth'] >= 0 ? 'up' : 'down' }}"></i> 
                            {{ $stats['posts_this_month'] }} this month
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="stats-card">
                <div class="d-flex align-items-center">
                    <div class="stats-icon icon-green">
                        <i class="bi bi-eye"></i>
                    </div>
                    <div class="ms-3">
                        <div class="small text-muted">Total Views</div>
                        <div class="h4 mb-0">{{ number_format($stats['total_views']) }}</div>
                        <div class="small {{ $stats['views_growth'] >= 0 ? 'text-success' : 'text-danger' }}">
                            <i class="bi bi-arrow-{{ $stats['views_growth'] >= 0 ? 'up' : 'down' }}"></i> 
                            {{ round($stats['views_growth'], 1) }}% this month
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="stats-card">
                <div class="d-flex align-items-center">
                    <div class="stats-icon icon-orange">
                        <i class="bi bi-chat-dots"></i>
                    </div>
                    <div class="ms-3">
                        <div class="small text-muted">Comments</div>
                        <div class="h4 mb-0">{{ number_format($stats['total_comments']) }}</div>
                        <div class="small text-warning">
                            <i class="bi bi-clock"></i> {{ $stats['pending_comments'] }} pending
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="stats-card">
                <div class="d-flex align-items-center">
                    <div class="stats-icon icon-purple">
                        <i class="bi bi-pencil-square"></i>
                    </div>
                    <div class="ms-3">
                        <div class="small text-muted">Draft Posts</div>
                        <div class="h4 mb-0">{{ number_format($stats['draft_posts']) }}</div>
                        <div class="small text-info">
                            <i class="bi bi-clock"></i> Ready to publish
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Filters -->
        <div class="col-12">
            <form method="GET" action="{{ route('admin.content.blog.index') }}">
                <div class="filter-card">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">Search Posts</label>
                            <div class="search-container">
                                <i class="bi bi-search"></i>
                                <input type="text" name="search" class="form-control search-input" 
                                       value="{{ request('search') }}" 
                                       placeholder="Search by title, content, author...">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Category</label>
                            <select name="category" class="form-select">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->slug }}" {{ request('category') == $category->slug ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Author</label>
                            <select name="author" class="form-select">
                                <option value="">All Authors</option>
                                @foreach($authors as $author)
                                    <option value="{{ $author->id }}" {{ request('author') == $author->id ? 'selected' : '' }}>
                                        {{ $author->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-funnel"></i> Filter
                                </button>
                                <a href="{{ route('admin.content.blog.index') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </a>
                                <!-- <button type="button" class="btn btn-success" id="bulkPublish">
                                    <i class="bi bi-upload"></i> Bulk Publish
                                </button> -->
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Posts Table -->
        <div class="col-12">
            <div class="posts-table">
                <div class="table-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Blog Posts ({{ $posts->total() }})</h5>
                        <!-- <div class="d-flex gap-2">
                            <div class="dropdown export-dropdown">
                                <button class="btn btn-outline-primary modern-btn dropdown-toggle" data-bs-toggle="dropdown">
                                    <i class="bi bi-download"></i> Export
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#"><i class="bi bi-file-earmark-excel me-2"></i>Export as Excel</a></li>
                                    <li><a class="dropdown-item" href="#"><i class="bi bi-file-earmark-text me-2"></i>Export as CSV</a></li>
                                    <li><a class="dropdown-item" href="#"><i class="bi bi-file-earmark-pdf me-2"></i>Export as PDF</a></li>
                                </ul>
                            </div>
                        </div> -->
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <!-- <th>
                                    <input type="checkbox" class="form-check-input" id="selectAll">
                                </th> -->
                                <th>Post Details</th>
                                <th>Author</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Views</th>
                                <th>SEO</th>
                                <th>Published</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($posts as $post)
                            <tr>
                                <!-- <td>
                                    <input type="checkbox" class="form-check-input post-checkbox" value="{{ $post->id }}">
                                </td> -->
                                <td>
                                    <div class="post-preview">
                                        <div class="post-thumbnail" style="background-image: url('{{ asset('public/'.$post->featured_image) }}');"></div>
                                        <div class="post-details">
                                            <div class="post-title">{{ Str::limit($post->title, 50) }}</div>
                                            <div class="post-excerpt">{{ Str::limit($post->excerpt, 80) }}</div>
                                            <div class="post-meta">
                                                <span class="me-2">
                                                    <i class="bi bi-calendar"></i> {{ $post->created_at->format('M j, Y') }}
                                                </span>
                                                <span>
                                                    <i class="bi bi-clock"></i> {{ $post->reading_time_text }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="text-center">
                                        <div class="author-avatar mx-auto">
                                            @if($post->author && $post->author->profile_image)
                                                <img src="{{ asset('public/' . $post->author->profile_image) }}" alt="{{ $post->author->full_name }}" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
                                            @else
                                                {{ $post->author ? $post->author->initials : 'AU' }}
                                            @endif
                                        </div>
                                        <div class="small text-muted mt-1" style="font-size:11px;">{{ $post->author ? $post->author->full_name : 'Admin' }}</div>
                                    </div>
                                </td>
                                <td>
                                    <span class="post-category category-{{ $post->category->slug }}">
                                        {{ $post->category->name }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge-status status-{{ $post->status }}">
                                        {{ ucfirst($post->status) }}
                                    </span>
                                </td>
                                <td>
                                    @if($post->status === 'published')
                                        <div class="views-count">{{ number_format($post->views_count) }}</div>
                                        <div class="engagement-stats">
                                            <div class="stat-item">
                                                <i class="bi bi-heart"></i> {{ $post->likes_count }}
                                            </div>
                                            <div class="stat-item">
                                                <i class="bi bi-chat"></i> {{ $post->comments_count }}
                                            </div>
                                        </div>
                                    @elseif($post->status === 'scheduled')
                                        <div class="views-count">-</div>
                                        <div class="small text-muted">Scheduled for {{ $post->scheduled_at->format('M j') }}</div>
                                    @else
                                        <div class="views-count">-</div>
                                        <div class="small text-muted">Not published yet</div>
                                    @endif
                                </td>
                                <td>
                                    <div class="seo-score">
                                        @php
                                            $seoClass = 'score-poor';
                                            $seoText = 'Needs work';
                                            $seoTextClass = 'text-danger';
                                            
                                            if($post->seo_score >= 80) {
                                                $seoClass = 'score-excellent';
                                                $seoText = 'Excellent';
                                                $seoTextClass = 'text-success';
                                            } elseif($post->seo_score >= 60) {
                                                $seoClass = 'score-good';
                                                $seoText = 'Good';
                                                $seoTextClass = 'text-warning';
                                            }
                                        @endphp
                                        <div class="score-indicator {{ $seoClass }}">{{ $post->seo_score }}</div>
                                        <small class="{{ $seoTextClass }}">{{ $seoText }}</small>
                                    </div>
                                </td>
                                <td>
                                    @if($post->published_at)
                                        <div class="publish-date">{{ $post->published_at->format('M j, Y') }}</div>
                                        <div class="small text-muted">{{ $post->published_at->diffForHumans() }}</div>
                                    @elseif($post->scheduled_at)
                                        <div class="publish-date">{{ $post->scheduled_at->format('M j, Y') }}</div>
                                        <div class="small text-muted">{{ $post->scheduled_at->diffForHumans() }}</div>
                                    @else
                                        <div class="publish-date">-</div>
                                        <div class="small text-muted">{{ ucfirst($post->status) }}</div>
                                    @endif
                                </td>
                                <td>
                                    <div class="action-buttons d-flex gap-1">
                                        @if($post->status === 'published')
                                            <a href="{{ route('blog.detail', $post->slug) }}" target="_blank" class="btn btn-outline-primary btn-sm" title="View Post">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        @elseif($post->status === 'draft')
                                            <button class="btn btn-outline-success btn-sm" title="Publish" onclick="publishPost({{ $post->id }})">
                                                <i class="bi bi-upload"></i>
                                            </button>
                                        @elseif($post->status === 'scheduled')
                                            <button class="btn btn-outline-warning btn-sm" title="Edit Schedule">
                                                <i class="bi bi-clock"></i>
                                            </button>
                                        @endif
                                        
                                        <a href="{{ route('admin.content.blog.edit', $post) }}" class="btn btn-outline-secondary btn-sm" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        
                                        <div class="dropdown">
                                            <button class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown" title="More">
                                                <i class="bi bi-three-dots"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                @if($post->status === 'published')
                                                    <!-- <li><a class="dropdown-item" href="#"><i class="bi bi-share"></i>Share Post</a></li> -->
                                                    <!-- <li><a class="dropdown-item" href="#"><i class="bi bi-bar-chart"></i>View Analytics</a></li> -->
                                                @else
                                                    <li><a class="dropdown-item" href="#"><i class="bi bi-eye"></i>Preview</a></li>
                                                    @if($post->status === 'scheduled')
                                                        <li>
                                                            <a class="dropdown-item" href="#" data-action="publish-now" data-post-id="{{ $post->id }}">
                                                                <i class="bi bi-upload"></i>Publish Now
                                                            </a>
                                                        </li>
                                                    @endif
                                                @endif
                                                    <li>
                                                        <a class="dropdown-item" href="#" data-action="duplicate" data-post-id="{{ $post->id }}">
                                                            <i class="bi bi-copy"></i> Duplicate
                                                        </a>
                                                    </li>                                                
                                                    <li><hr class="dropdown-divider"></li>
                                                @if($post->status === 'scheduled')
                                                    <li>
                                                        <a class="dropdown-item text-warning" href="#" data-action="cancel-schedule" data-post-id="{{ $post->id }}">
                                                            <i class="bi bi-pause"></i>Cancel Schedule
                                                        </a>
                                                    </li>
                                                @endif
                                                <li>
                                                    <form action="{{ route('admin.content.blog.destroy', $post) }}" method="POST" style="display: inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Are you sure?')">
                                                            <i class="bi bi-trash"></i>Delete
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-file-earmark-text fs-1"></i>
                                        <p class="mt-2">No blog posts found</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="pagination-container">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted">
                            Showing {{ $posts->firstItem() }} to {{ $posts->lastItem() }} of {{ $posts->total() }} entries
                        </div>
                        <nav>
                            {{ $posts->appends(request()->query())->links() }}
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

 
<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-plus me-2"></i>Add New Category
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addCategoryForm" class="modern-form">
                    <div class="mb-3">
                        <label class="form-label">Category Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g., Travel Guides" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Slug</label>
                        <input type="text" name="slug" class="form-control" placeholder="travel-guides">
                        <div class="form-text">URL-friendly version (auto-generated if empty)</div>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Brief description of this category"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Color</label>
                        <div class="d-flex gap-2 align-items-center">
                            <input type="color" name="color" class="form-control form-control-color" value="#667eea" style="width: 60px;">
                            <span class="text-muted">Choose category color for tags</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Parent Category</label>
                        <select name="parent_id" class="form-select">
                            <option value="">None (Top Level)</option>
                            <!-- Will be populated dynamically -->
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary modern-btn" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="addCategoryForm" class="btn btn-primary modern-btn">
                    <i class="bi bi-check-lg"></i> Add Category
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-pencil me-2"></i>Edit Category
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editCategoryForm" class="modern-form">
                    <input type="hidden" name="category_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Category Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g., Travel Guides" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Slug</label>
                        <input type="text" name="slug" class="form-control" placeholder="travel-guides">
                        <div class="form-text">URL-friendly version (auto-generated if empty)</div>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Brief description of this category"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Color</label>
                        <div class="d-flex gap-2 align-items-center">
                            <input type="color" name="color" class="form-control form-control-color" style="width: 60px;">
                            <span class="text-muted">Choose category color for tags</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Parent Category</label>
                        <select name="parent_id" class="form-select">
                            <option value="">None (Top Level)</option>
                            <!-- Will be populated dynamically -->
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="editCategoryActive">
                            <label class="form-check-label" for="editCategoryActive">
                                Active (visible on website)
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary modern-btn" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="editCategoryForm" class="btn btn-primary modern-btn">
                    <i class="bi bi-check-lg"></i> Update Category
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Categories Management Modal -->
<div class="modal fade" id="manageCategoriesModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-tags me-2"></i>Manage Categories
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6>Blog Categories</h6>
                    <button class="btn btn-primary modern-btn btn-sm" onclick="showAddCategoryModal()">
                        <i class="bi bi-plus"></i> Add New
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover" id="categoriesTable">
                        <thead class="table-light">
                            <tr>
                                <th>Category</th>
                                <th>Posts</th>
                                <th>Views</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Will be populated dynamically -->
                        </tbody>
                    </table>
                </div>

                <div id="categoriesLoading" class="text-center py-4" style="display: none;">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>

                <div id="categoriesEmpty" class="text-center py-4" style="display: none;">
                    <i class="bi bi-tags fs-1 text-muted"></i>
                    <h6 class="text-muted mt-2">No categories found</h6>
                    <p class="text-muted">Create your first category to get started.</p>
                    <button class="btn btn-primary modern-btn" onclick="showAddCategoryModal()">
                        <i class="bi bi-plus"></i> Add First Category
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary modern-btn" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>



<!-- Before closing body tag -->
<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
@push('scripts')
<script>
    // Store routes with placeholders
    const routes = {
        publish: "{{ route('admin.content.blog.publish', ['post' => ':postId']) }}",
        cancelSchedule: "{{ route('admin.content.blog.cancel-schedule', ['post' => ':postId']) }}",
        duplicate: "{{ route('admin.content.blog.duplicate', ['post' => ':postId']) }}"

    };

    function handleApiResponse(response) {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    }

    function handleSuccess(data) {
        if (data.success) {
            // Show success message
            Toastify({
                text: data.message,
                duration: 3000,
                close: true,
                gravity: "top",
                position: "right",
                backgroundColor: "#4fbe87",
            }).showToast();
            
            // Reload page after short delay
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            throw new Error(data.message || 'Action failed');
        }
    }

    function handleError(error) {
        console.error('Error:', error);
        Toastify({
            text: error.message || 'An error occurred. Please try again.',
            duration: 5000,
            close: true,
            gravity: "top",
            position: "right",
            backgroundColor: "#ff6b6b",
        }).showToast();
    }

    function publishPost(postId) {
        if (confirm('Are you sure you want to publish this post?')) {
            const url = routes.publish.replace(':postId', postId);
            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(handleApiResponse)
            .then(handleSuccess)
            .catch(handleError);
        }
    }

    function cancelSchedule(postId) {
        if (confirm('Are you sure you want to cancel this scheduled post?')) {
            const url = routes.cancelSchedule.replace(':postId', postId);
            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(handleApiResponse)
            .then(handleSuccess)
            .catch(handleError);
        }
    }

    // Initialize event listeners
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('[data-action="publish-now"]').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                publishPost(this.dataset.postId);
            });
        });

        document.querySelectorAll('[data-action="cancel-schedule"]').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                cancelSchedule(this.dataset.postId);
            });
        });

        // Add to your DOMContentLoaded event listener
        document.querySelectorAll('[data-action="duplicate"]').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                duplicatePost(this.dataset.postId);
            });
        });
    });



// Add duplicatePost function
function duplicatePost(postId) {
    if (confirm('Are you sure you want to duplicate this post?')) {
        const url = routes.duplicate.replace(':postId', postId);
        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(handleApiResponse)
        .then(data => {
            if (data.success) {
                Toastify({
                    text: data.message,
                    duration: 3000,
                    close: true,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#4fbe87",
                }).showToast();
                
                // Redirect to edit page for the new duplicate
                if (data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    window.location.reload();
                }
            } else {
                throw new Error(data.message);
            }
        })
        .catch(handleError);
    }
}
</script>



<script>

    const categoryUrl = "{{ route('admin.content.categories.index') }}";
// Category Management JavaScript
let categoriesData = [];

// Load categories when modal opens
document.getElementById('manageCategoriesModal').addEventListener('show.bs.modal', function() {
    loadCategories();
});

// Auto-generate slug from name
document.querySelector('#addCategoryForm input[name="name"]').addEventListener('input', function() {
    const slug = this.value.toLowerCase()
        .replace(/[^a-z0-9 -]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .trim('-');
    document.querySelector('#addCategoryForm input[name="slug"]').value = slug;
});

document.querySelector('#editCategoryForm input[name="name"]').addEventListener('input', function() {
    const slug = this.value.toLowerCase()
        .replace(/[^a-z0-9 -]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .trim('-');
    document.querySelector('#editCategoryForm input[name="slug"]').value = slug;
});

// Load categories
function loadCategories() {
    showLoading(true);
    
    fetch(categoryUrl)
        .then(response => response.json())
        .then(categories => {
            categoriesData = categories;
            displayCategories(categories);
            populateParentOptions(categories);
            showLoading(false);
        })
        .catch(error => {
            console.error('Error loading categories:', error);
            showError('Failed to load categories');
            showLoading(false);
        });
}

// Display categories in table
function displayCategories(categories) {
    const tbody = document.querySelector('#categoriesTable tbody');
    
    if (categories.length === 0) {
        document.getElementById('categoriesEmpty').style.display = 'block';
        document.querySelector('#categoriesTable').style.display = 'none';
        return;
    }
    
    document.getElementById('categoriesEmpty').style.display = 'none';
    document.querySelector('#categoriesTable').style.display = 'table';
    
    tbody.innerHTML = categories.map(category => `
        <tr data-category-id="${category.id}">
            <td>
                <div class="d-flex align-items-center">
                    <span class="post-category me-2" style="background-color: ${category.color}; color: white; padding: 0.25rem 0.75rem; border-radius: 0.5rem; font-size: 0.7rem; font-weight: 600;">
                        ${category.name}
                    </span>
                    <div>
                        <div class="fw-semibold">${category.name}</div>
                        ${category.description ? `<small class="text-muted">${category.description}</small>` : ''}
                        ${category.parent ? `<div><small class="text-info">Parent: ${category.parent.name}</small></div>` : ''}
                    </div>
                </div>
            </td>
            <td>
                <span class="badge bg-primary">${category.posts_count || 0}</span>
            </td>
            <td>
                <span class="fw-semibold">${formatNumber(category.views_count || 0)}</span>
            </td>
            <td>
                <button class="btn btn-sm ${category.is_active ? 'btn-success' : 'btn-secondary'}" onclick="toggleCategoryStatus(${category.id})">
                    <i class="bi ${category.is_active ? 'bi-check-circle' : 'bi-x-circle'}"></i>
                    ${category.is_active ? 'Active' : 'Inactive'}
                </button>
            </td>
            <td>
                <button class="btn btn-outline-primary btn-sm me-1" onclick="editCategory(${category.id})" title="Edit">
                    <i class="bi bi-pencil"></i>
                </button>
                <button class="btn btn-outline-danger btn-sm" onclick="deleteCategory(${category.id})" title="Delete">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

// Populate parent category options
function populateParentOptions(categories, excludeId = null) {
    const parentCategories = categories.filter(cat => 
        !cat.parent_id && cat.id !== excludeId
    );
    
    const addSelect = document.querySelector('#addCategoryForm select[name="parent_id"]');
    const editSelect = document.querySelector('#editCategoryForm select[name="parent_id"]');
    
    const options = parentCategories.map(cat => 
        `<option value="${cat.id}">${cat.name}</option>`
    ).join('');
    
    addSelect.innerHTML = '<option value="">None (Top Level)</option>' + options;
    editSelect.innerHTML = '<option value="">None (Top Level)</option>' + options;
}

// Show add category modal
function showAddCategoryModal() {
    // Hide main categories modal
    const mainModal = bootstrap.Modal.getInstance(document.getElementById('manageCategoriesModal'));
    if (mainModal) {
        mainModal.hide();
    }
    document.getElementById('addCategoryForm').reset();
    const addModal = new bootstrap.Modal(document.getElementById('addCategoryModal'));
    addModal.show();
}

// Add category form submission
document.getElementById('addCategoryForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());
    
    // Clear previous errors
    clearErrors(this);
    
    fetch(categoryUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showSuccess(result.message);
            bootstrap.Modal.getInstance(document.getElementById('addCategoryModal')).hide();
            loadCategories();
        } else {
            showError(result.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('Failed to create category');
    });
});

// Edit category
function editCategory(categoryId) {
    const category = categoriesData.find(cat => cat.id === categoryId);
    if (!category) return;
    
        // Hide main categories modal first
    const mainModal = bootstrap.Modal.getInstance(document.getElementById('manageCategoriesModal'));
    if (mainModal) {
        mainModal.hide();
    }

    // Populate form
    const form = document.getElementById('editCategoryForm');
    form.querySelector('input[name="category_id"]').value = category.id;
    form.querySelector('input[name="name"]').value = category.name;
    form.querySelector('input[name="slug"]').value = category.slug;
    form.querySelector('textarea[name="description"]').value = category.description || '';
    form.querySelector('input[name="color"]').value = category.color || '#667eea';
    form.querySelector('select[name="parent_id"]').value = category.parent_id || '';
    form.querySelector('input[name="is_active"]').checked = category.is_active;
    
    // Update parent options (exclude current category)
    populateParentOptions(categoriesData, categoryId);
    
    const editModal = new bootstrap.Modal(document.getElementById('editCategoryModal'));
    editModal.show();
}

// Edit category form submission
document.getElementById('editCategoryForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());
    const categoryId = data.category_id;
    
    // Convert checkbox to boolean
    data.is_active = formData.has('is_active');
    
    // Clear previous errors
    clearErrors(this);
    
    fetch(`{{ url('admin/content/categories') }}/${categoryId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showSuccess(result.message);
            bootstrap.Modal.getInstance(document.getElementById('editCategoryModal')).hide();
            loadCategories();
        } else {
            showError(result.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('Failed to update category');
    });
});

// Delete category
function deleteCategory(categoryId) {
    const category = categoriesData.find(cat => cat.id === categoryId);
    if (!category) return;
    
    if (confirm(`Are you sure you want to delete the category "${category.name}"?`)) {
        fetch(`{{ url('admin/content/categories') }}/${categoryId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })

        .then(response => response.json())
        .then(result => {
            if (result.success) {
                showSuccess(result.message);
                loadCategories();
            } else {
                showError(result.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('Failed to delete category');
        });
    }
}

// Toggle category status
function toggleCategoryStatus(categoryId) {
    fetch(`{{ url('admin/content/categories') }}/${categoryId}/toggle-status`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showSuccess(result.message);
            loadCategories();
        } else {
            showError(result.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('Failed to update category status');
    });
}

// Helper functions
function showLoading(show) {
    document.getElementById('categoriesLoading').style.display = show ? 'block' : 'none';
    document.querySelector('#categoriesTable').style.display = show ? 'none' : 'table';
}

function formatNumber(num) {
    if (num >= 1000000) {
        return (num / 1000000).toFixed(1) + 'M';
    } else if (num >= 1000) {
        return (num / 1000).toFixed(1) + 'K';
    }
    return num.toString();
}

function showSuccess(message) {
    // You can implement your notification system here
    const alertHtml = `
        <div class="alert alert-success alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 9999;">
            <i class="bi bi-check-circle me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', alertHtml);
    
    setTimeout(() => {
        const alert = document.querySelector('.alert:last-of-type');
        if (alert) alert.remove();
    }, 5000);
}

function showError(message) {
    const alertHtml = `
        <div class="alert alert-danger alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 9999;">
            <i class="bi bi-exclamation-circle me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', alertHtml);
    
    setTimeout(() => {
        const alert = document.querySelector('.alert:last-of-type');
        if (alert) alert.remove();
    }, 5000);
}

function clearErrors(form) {
    form.querySelectorAll('.is-invalid').forEach(element => {
        element.classList.remove('is-invalid');
    });
    form.querySelectorAll('.invalid-feedback').forEach(element => {
        element.textContent = '';
    });
}
</script>
@endpush

@endsection