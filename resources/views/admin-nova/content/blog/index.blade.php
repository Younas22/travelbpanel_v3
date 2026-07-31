@extends('admin-nova.layouts.app')

@section('title', 'Blog Management')

@push('styles')
@include('admin-nova.content.blog._styles')
<style>
    #blPage .dropdown-menu { border-radius: 1rem; border-color: #E5E7EB; font-family: 'Plus Jakarta Sans', sans-serif; padding: 6px; }
    #blPage .dropdown-item { border-radius: 10px; font-size: 12px; padding: 8px 12px; display: flex; align-items: center; gap: 8px; }
    #blPage .dropdown-item:hover { background: #F7F8FC; }
    #blPage .dropdown-item.text-danger:hover { background: #FEF2F2; }
    #blPage .dropdown-item.text-warning:hover { background: #FFFBEB; }
</style>
@endpush

@section('content')
@php
    $blSeoStyle = [
        'excellent' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600', 'label' => 'Excellent'],
        'good' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-600', 'label' => 'Good'],
        'poor' => ['bg' => 'bg-red-50', 'text' => 'text-novadanger', 'label' => 'Needs work'],
    ];
    $blStatusStyle = [
        'published' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600'],
        'draft' => ['bg' => 'bg-slate-100', 'text' => 'text-novamuted'],
        'scheduled' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-600'],
    ];
@endphp

<div id="blPage" class="tt-fade-in font-jakarta">

    {{-- ============ HEADER ============ --}}
    <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
        <div>
            <h1 class="text-lg font-bold text-novatext">Blog Management</h1>
            <p class="text-xs text-novamuted mt-1">Create and manage travel blog content</p>
        </div>
        <div class="flex items-center gap-2">
            <button class="bl-btn-nova px-4 py-2.5 text-xs font-semibold" data-bs-toggle="modal" data-bs-target="#manageCategoriesModal">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="m20.6 13-7.6 7.6a2 2 0 0 1-2.8 0l-6.8-6.8a2 2 0 0 1 0-2.8L10.9 3.4A2 2 0 0 1 12.4 3H18a2 2 0 0 1 2 2v5.6a2 2 0 0 1-.6 1.4Z"/><circle cx="14.5" cy="8.5" r="1.5"/></svg>
                Manage Categories
            </button>
            <a href="{{ route('admin.content.blog.create') }}" class="bl-btn-nova bl-btn-primary px-4 py-2.5 text-xs font-semibold">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                Create New Post
            </a>
        </div>
    </div>

    {{-- ============ KPI CARDS ============ --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
        <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-3">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-medium text-novamuted">Total Posts</p>
                <div class="w-7 h-7 rounded-full bg-novablue text-white flex items-center justify-center flex-shrink-0">
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2h9l3 3v17H6z"/><path d="M15 2v3h3M9 12h6M9 16h6"/></svg>
                </div>
            </div>
            <span class="block text-lg font-bold text-novatext">{{ number_format($stats['total_posts']) }}</span>
            <span class="text-[10px] {{ $stats['posts_growth'] >= 0 ? 'text-novasuccess' : 'text-novadanger' }} font-semibold">{{ $stats['posts_this_month'] }} this month</span>
        </div>
        <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-3">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-medium text-novamuted">Total Views</p>
                <div class="w-7 h-7 rounded-full bg-novasuccess text-white flex items-center justify-center flex-shrink-0">
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M2.5 12S6 5 12 5s9.5 7 9.5 7-3.5 7-9.5 7-9.5-7-9.5-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                </div>
            </div>
            <span class="block text-lg font-bold text-novatext">{{ number_format($stats['total_views']) }}</span>
            <span class="text-[10px] {{ $stats['views_growth'] >= 0 ? 'text-novasuccess' : 'text-novadanger' }} font-semibold">{{ round($stats['views_growth'], 1) }}% this month</span>
        </div>
        <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-3">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-medium text-novamuted">Comments</p>
                <div class="w-7 h-7 rounded-full bg-novawarning text-white flex items-center justify-center flex-shrink-0">
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.4 8.4 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.4 8.4 0 0 1-3.8-.9L3 21l1.9-5.7a8.4 8.4 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.4 8.4 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5Z"/></svg>
                </div>
            </div>
            <span class="block text-lg font-bold text-novatext">{{ number_format($stats['total_comments']) }}</span>
            <span class="text-[10px] text-novawarning font-semibold">{{ $stats['pending_comments'] }} pending</span>
        </div>
        <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-3">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-medium text-novamuted">Draft Posts</p>
                <div class="w-7 h-7 rounded-full bg-novacyan text-white flex items-center justify-center flex-shrink-0">
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M16.474 5.408 18.592 7.526M4 20l1.11-3.92a2 2 0 0 1 .53-.9l9.9-9.9a1.5 1.5 0 0 1 2.12 0l1.06 1.06a1.5 1.5 0 0 1 0 2.12l-9.9 9.9a2 2 0 0 1-.9.53L4 20Z"/></svg>
                </div>
            </div>
            <span class="block text-lg font-bold text-novatext">{{ number_format($stats['draft_posts']) }}</span>
            <span class="text-[10px] text-novacyan font-semibold">Ready to publish</span>
        </div>
    </div>

    {{-- ============ FILTERS ============ --}}
    <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 sm:p-5 mb-5">
        <form method="GET" action="{{ route('admin.content.blog.index') }}">
            <div class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-semibold text-novamuted mb-1.5">Search posts</label>
                    <div class="relative">
                        <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-novamuted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by title, content, author..."
                               class="w-full text-sm border border-novaborder rounded-full pl-10 pr-4 py-2.5">
                    </div>
                </div>
                <div class="w-36">
                    <label class="block text-xs font-semibold text-novamuted mb-1.5">Status</label>
                    <select name="status" class="w-full text-sm border border-novaborder rounded-full px-3.5 py-2.5">
                        <option value="">All Status</option>
                        <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                    </select>
                </div>
                <div class="w-40">
                    <label class="block text-xs font-semibold text-novamuted mb-1.5">Category</label>
                    <select name="category" class="w-full text-sm border border-novaborder rounded-full px-3.5 py-2.5">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->slug }}" {{ request('category') == $category->slug ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="w-36">
                    <label class="block text-xs font-semibold text-novamuted mb-1.5">Author</label>
                    <select name="author" class="w-full text-sm border border-novaborder rounded-full px-3.5 py-2.5">
                        <option value="">All Authors</option>
                        @foreach($authors as $author)
                            <option value="{{ $author->id }}" {{ request('author') == $author->id ? 'selected' : '' }}>{{ $author->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <button type="submit" class="bl-btn-nova bl-btn-primary w-10 h-10" data-tooltip="Apply filters">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 5h16M7 12h10M10 19h4"/></svg>
                    </button>
                    <a href="{{ route('admin.content.blog.index') }}" class="bl-btn-nova w-10 h-10" data-tooltip="Reset filters">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 1 2.64 6.36M3 12v6m0-6h6"/></svg>
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- ============ POSTS LIST ============ --}}
    <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 sm:p-5">
        <div class="flex items-center gap-2 mb-4">
            <h2 class="text-sm font-semibold text-novatext">Blog Posts</h2>
            <span class="text-xs text-novamuted">{{ number_format($posts->total()) }} total</span>
        </div>

        <div class="space-y-3">
            @forelse($posts as $post)
                @php
                    $st = $blStatusStyle[$post->status] ?? ['bg' => 'bg-slate-100', 'text' => 'text-novamuted'];
                    $seoKey = $post->seo_score >= 80 ? 'excellent' : ($post->seo_score >= 60 ? 'good' : 'poor');
                    $seo = $blSeoStyle[$seoKey];
                @endphp
                <div class="tt-row rounded-2xl border border-novaborder p-3.5 lg:p-4">
                    <div class="flex flex-col lg:flex-row lg:items-center gap-3">
                        {{-- Post preview --}}
                        <div class="flex items-center gap-3 lg:w-72 lg:flex-shrink-0 min-w-0">
                            <div class="w-14 h-14 rounded-xl bg-novabg bg-cover bg-center flex-shrink-0" style="background-image:url('{{ asset('public/'.$post->featured_image) }}');"></div>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-novatext truncate">{{ Str::limit($post->title, 50) }}</p>
                                <p class="text-[11px] text-novamuted truncate mt-0.5">{{ Str::limit($post->excerpt, 60) }}</p>
                                <p class="text-[10px] text-novamuted mt-0.5">{{ $post->created_at->format('M j, Y') }} &middot; {{ $post->reading_time_text }}</p>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-3 lg:gap-4 flex-1 min-w-0">
                            {{-- Author --}}
                            <div class="flex items-center gap-2 lg:w-28 lg:flex-shrink-0">
                                <div class="w-7 h-7 rounded-full bg-blue-50 text-novablue flex items-center justify-center text-[9px] font-bold flex-shrink-0 overflow-hidden">
                                    @if($post->author && $post->author->profile_image)
                                        <img src="{{ asset('public/' . $post->author->profile_image) }}" alt="{{ $post->author->full_name }}" class="w-full h-full object-cover">
                                    @else
                                        {{ $post->author ? $post->author->initials : 'AU' }}
                                    @endif
                                </div>
                                <span class="text-[11px] text-novamuted truncate">{{ $post->author ? $post->author->full_name : 'Admin' }}</span>
                            </div>

                            {{-- Category --}}
                            <div class="lg:w-28 lg:flex-shrink-0">
                                <span class="px-2.5 py-1 rounded-full text-[9px] font-semibold text-white" style="background:{{ $post->category->color ?? '#667eea' }};">{{ $post->category->name }}</span>
                            </div>

                            {{-- Status --}}
                            <div class="lg:w-20 lg:flex-shrink-0">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold {{ $st['bg'] }} {{ $st['text'] }}">{{ ucfirst($post->status) }}</span>
                            </div>

                            {{-- Views/Engagement --}}
                            <div class="lg:w-24 lg:flex-shrink-0">
                                @if($post->status === 'published')
                                    <p class="text-xs font-semibold text-novatext">{{ number_format($post->views_count) }} views</p>
                                    <p class="text-[10px] text-novamuted mt-0.5">♥ {{ $post->likes_count }} &middot; 💬 {{ $post->comments_count }}</p>
                                @elseif($post->status === 'scheduled')
                                    <p class="text-xs text-novamuted">Scheduled</p>
                                    <p class="text-[10px] text-novamuted mt-0.5">{{ $post->scheduled_at->format('M j') }}</p>
                                @else
                                    <p class="text-xs text-novamuted">Not published</p>
                                @endif
                            </div>

                            {{-- SEO --}}
                            <div class="lg:w-24 lg:flex-shrink-0">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold {{ $seo['bg'] }} {{ $seo['text'] }}">{{ $post->seo_score }} &middot; {{ $seo['label'] }}</span>
                            </div>

                            {{-- Published --}}
                            <div class="lg:w-28 lg:flex-shrink-0">
                                @if($post->published_at)
                                    <p class="text-xs text-novatext">{{ $post->published_at->format('M j, Y') }}</p>
                                    <p class="text-[10px] text-novamuted">{{ $post->published_at->diffForHumans() }}</p>
                                @elseif($post->scheduled_at)
                                    <p class="text-xs text-novatext">{{ $post->scheduled_at->format('M j, Y') }}</p>
                                    <p class="text-[10px] text-novamuted">{{ $post->scheduled_at->diffForHumans() }}</p>
                                @else
                                    <p class="text-xs text-novamuted">{{ ucfirst($post->status) }}</p>
                                @endif
                            </div>

                            {{-- Actions --}}
                            <div class="flex items-center gap-1.5 ml-auto flex-shrink-0">
                                @if($post->status === 'published')
                                    <a href="{{ route('blog.detail', $post->slug) }}" target="_blank" class="bl-icon-btn" data-tooltip="View Post">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M2.5 12S6 5 12 5s9.5 7 9.5 7-3.5 7-9.5 7-9.5-7-9.5-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </a>
                                @elseif($post->status === 'draft')
                                    <button class="bl-icon-btn bl-icon-success" onclick="publishPost({{ $post->id }})" data-tooltip="Publish">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 15V3m0 0 4 4m-4-4-4 4M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"/></svg>
                                    </button>
                                @elseif($post->status === 'scheduled')
                                    <span class="bl-icon-btn bl-icon-warn" data-tooltip="Edit Schedule">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
                                    </span>
                                @endif

                                <a href="{{ route('admin.content.blog.edit', $post) }}" class="bl-icon-btn" data-tooltip="Edit">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M16.474 5.408 18.592 7.526M4 20l1.11-3.92a2 2 0 0 1 .53-.9l9.9-9.9a1.5 1.5 0 0 1 2.12 0l1.06 1.06a1.5 1.5 0 0 1 0 2.12l-9.9 9.9a2 2 0 0 1-.9.53L4 20Z"/></svg>
                                </a>

                                <div class="dropdown">
                                    <button class="bl-icon-btn" data-bs-toggle="dropdown" data-tooltip="More">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><circle cx="5" cy="12" r="1.8"/><circle cx="12" cy="12" r="1.8"/><circle cx="19" cy="12" r="1.8"/></svg>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        @if($post->status !== 'published')
                                            <li><a class="dropdown-item" href="#"><svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M2.5 12S6 5 12 5s9.5 7 9.5 7-3.5 7-9.5 7-9.5-7-9.5-7Z"/><circle cx="12" cy="12" r="3"/></svg> Preview</a></li>
                                            @if($post->status === 'scheduled')
                                                <li><a class="dropdown-item" href="#" data-action="publish-now" data-post-id="{{ $post->id }}"><svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 15V3m0 0 4 4m-4-4-4 4M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"/></svg> Publish Now</a></li>
                                            @endif
                                        @endif
                                        <li><a class="dropdown-item" href="#" data-action="duplicate" data-post-id="{{ $post->id }}"><svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="8" y="8" width="12" height="12" rx="2"/><path d="M4 16V4a2 2 0 0 1 2-2h10"/></svg> Duplicate</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        @if($post->status === 'scheduled')
                                            <li><a class="dropdown-item text-warning" href="#" data-action="cancel-schedule" data-post-id="{{ $post->id }}"><svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="6" y="5" width="4" height="14" rx="1"/><rect x="14" y="5" width="4" height="14" rx="1"/></svg> Cancel Schedule</a></li>
                                        @endif
                                        <li>
                                            <form action="{{ route('admin.content.blog.destroy', $post) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Are you sure?')" style="width:100%;">
                                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0-1 14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2L4 6h16Z"/></svg> Delete
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-16">
                    <svg class="w-10 h-10 text-novaborder mx-auto mb-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2h9l3 3v17H6z"/><path d="M15 2v3h3M9 12h6M9 16h6"/></svg>
                    <p class="text-sm font-semibold text-novatext">No blog posts found</p>
                </div>
            @endforelse
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 mt-6 pt-5 border-t border-novaborder">
            <p class="text-xs text-novamuted">Showing {{ $posts->firstItem() }} to {{ $posts->lastItem() }} of {{ $posts->total() }} entries</p>
            {{ $posts->appends(request()->query())->links() }}
        </div>
    </div>
</div>

{{-- ============ ADD CATEGORY MODAL ============ --}}
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:1.5rem; font-family:'Plus Jakarta Sans',sans-serif;">
            <div class="modal-header" style="border-bottom:1px solid #E5E7EB;">
                <h5 class="modal-title" style="font-weight:700;">Add New Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addCategoryForm">
                    <div class="mb-3">
                        <label class="form-label" style="font-size:.8rem; font-weight:600;">Category Name <span style="color:#EF4444;">*</span></label>
                        <input type="text" name="name" class="form-control" style="border-radius:9999px;" placeholder="e.g., Travel Guides" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-size:.8rem; font-weight:600;">Slug</label>
                        <input type="text" name="slug" class="form-control" style="border-radius:9999px;" placeholder="travel-guides">
                        <div class="form-text">URL-friendly version (auto-generated if empty)</div>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-size:.8rem; font-weight:600;">Description</label>
                        <textarea name="description" class="form-control" style="border-radius:1rem;" rows="3" placeholder="Brief description of this category"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-size:.8rem; font-weight:600;">Color</label>
                        <div class="d-flex gap-2 align-items-center">
                            <input type="color" name="color" class="form-control form-control-color" value="#667eea">
                            <span class="text-muted" style="font-size:.8rem;">Choose category color for tags</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-size:.8rem; font-weight:600;">Parent Category</label>
                        <select name="parent_id" class="form-select" style="border-radius:9999px;">
                            <option value="">None (Top Level)</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer" style="border-top:1px solid #E5E7EB;">
                <button type="button" data-bs-dismiss="modal"
                        style="display:inline-flex; align-items:center; border-radius:9999px; padding:10px 20px; font-size:13px; font-weight:600; font-family:'Plus Jakarta Sans',sans-serif; background:#F7F8FC; color:#000; border:1px solid #E5E7EB; cursor:pointer;">Cancel</button>
                <button type="submit" form="addCategoryForm"
                        style="display:inline-flex; align-items:center; border-radius:9999px; padding:10px 20px; font-size:13px; font-weight:600; font-family:'Plus Jakarta Sans',sans-serif; background:#2563EB; color:#fff; border:1px solid #2563EB; cursor:pointer;">Add Category</button>
            </div>
        </div>
    </div>
</div>

{{-- ============ EDIT CATEGORY MODAL ============ --}}
<div class="modal fade" id="editCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:1.5rem; font-family:'Plus Jakarta Sans',sans-serif;">
            <div class="modal-header" style="border-bottom:1px solid #E5E7EB;">
                <h5 class="modal-title" style="font-weight:700;">Edit Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editCategoryForm">
                    <input type="hidden" name="category_id">
                    <div class="mb-3">
                        <label class="form-label" style="font-size:.8rem; font-weight:600;">Category Name <span style="color:#EF4444;">*</span></label>
                        <input type="text" name="name" class="form-control" style="border-radius:9999px;" placeholder="e.g., Travel Guides" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-size:.8rem; font-weight:600;">Slug</label>
                        <input type="text" name="slug" class="form-control" style="border-radius:9999px;" placeholder="travel-guides">
                        <div class="form-text">URL-friendly version (auto-generated if empty)</div>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-size:.8rem; font-weight:600;">Description</label>
                        <textarea name="description" class="form-control" style="border-radius:1rem;" rows="3" placeholder="Brief description of this category"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-size:.8rem; font-weight:600;">Color</label>
                        <div class="d-flex gap-2 align-items-center">
                            <input type="color" name="color" class="form-control form-control-color">
                            <span class="text-muted" style="font-size:.8rem;">Choose category color for tags</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-size:.8rem; font-weight:600;">Parent Category</label>
                        <select name="parent_id" class="form-select" style="border-radius:9999px;">
                            <option value="">None (Top Level)</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3 form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" id="editCategoryActive">
                        <label class="form-check-label" for="editCategoryActive" style="font-size:.8rem;">Active (visible on website)</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer" style="border-top:1px solid #E5E7EB;">
                <button type="button" data-bs-dismiss="modal"
                        style="display:inline-flex; align-items:center; border-radius:9999px; padding:10px 20px; font-size:13px; font-weight:600; font-family:'Plus Jakarta Sans',sans-serif; background:#F7F8FC; color:#000; border:1px solid #E5E7EB; cursor:pointer;">Cancel</button>
                <button type="submit" form="editCategoryForm"
                        style="display:inline-flex; align-items:center; border-radius:9999px; padding:10px 20px; font-size:13px; font-weight:600; font-family:'Plus Jakarta Sans',sans-serif; background:#2563EB; color:#fff; border:1px solid #2563EB; cursor:pointer;">Update Category</button>
            </div>
        </div>
    </div>
</div>

{{-- ============ MANAGE CATEGORIES MODAL ============ --}}
<div class="modal fade" id="manageCategoriesModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius:1.5rem; font-family:'Plus Jakarta Sans',sans-serif;">
            <div class="modal-header" style="border-bottom:1px solid #E5E7EB;">
                <h5 class="modal-title" style="font-weight:700;">Manage Categories</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 style="font-weight:700; font-size:.9rem;">Blog Categories</h6>
                    <button onclick="showAddCategoryModal()"
                            style="display:inline-flex; align-items:center; gap:6px; border-radius:9999px; padding:8px 16px; font-size:12px; font-weight:600; font-family:'Plus Jakarta Sans',sans-serif; background:#2563EB; color:#fff; border:1px solid #2563EB; cursor:pointer;">
                        <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                        Add New
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover" id="categoriesTable" style="font-size:.8rem;">
                        <thead>
                        <tr>
                            <th>Category</th><th>Posts</th><th>Views</th><th>Status</th><th>Actions</th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                <div id="categoriesLoading" class="text-center py-4" style="display:none;">
                    <div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>
                </div>
                <div id="categoriesEmpty" class="text-center py-4" style="display:none;">
                    <p style="font-weight:600;">No categories found</p>
                    <p class="text-muted">Create your first category to get started.</p>
                    <button onclick="showAddCategoryModal()"
                            style="display:inline-flex; align-items:center; gap:6px; border-radius:9999px; padding:10px 20px; font-size:13px; font-weight:600; font-family:'Plus Jakarta Sans',sans-serif; background:#2563EB; color:#fff; border:1px solid #2563EB; cursor:pointer;">Add First Category</button>
                </div>
            </div>
            <div class="modal-footer" style="border-top:1px solid #E5E7EB;">
                <button type="button" data-bs-dismiss="modal"
                        style="display:inline-flex; align-items:center; border-radius:9999px; padding:10px 20px; font-size:13px; font-weight:600; font-family:'Plus Jakarta Sans',sans-serif; background:#F7F8FC; color:#000; border:1px solid #E5E7EB; cursor:pointer;">Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
<script>
    const routes = {
        publish: "{{ route('admin.content.blog.publish', ['post' => ':postId']) }}",
        cancelSchedule: "{{ route('admin.content.blog.cancel-schedule', ['post' => ':postId']) }}",
        duplicate: "{{ route('admin.content.blog.duplicate', ['post' => ':postId']) }}",
    };

    function handleApiResponse(response) {
        if (!response.ok) { throw new Error(`HTTP error! status: ${response.status}`); }
        return response.json();
    }

    function handleSuccess(data) {
        if (data.success) {
            Toastify({ text: data.message, duration: 3000, close: true, gravity: "top", position: "right", backgroundColor: "#22C55E" }).showToast();
            setTimeout(() => window.location.reload(), 1000);
        } else { throw new Error(data.message || 'Action failed'); }
    }

    function handleError(error) {
        console.error('Error:', error);
        Toastify({ text: error.message || 'An error occurred. Please try again.', duration: 5000, close: true, gravity: "top", position: "right", backgroundColor: "#EF4444" }).showToast();
    }

    function publishPost(postId) {
        if (confirm('Are you sure you want to publish this post?')) {
            fetch(routes.publish.replace(':postId', postId), {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'Content-Type': 'application/json' }
            }).then(handleApiResponse).then(handleSuccess).catch(handleError);
        }
    }

    function cancelSchedule(postId) {
        if (confirm('Are you sure you want to cancel this scheduled post?')) {
            fetch(routes.cancelSchedule.replace(':postId', postId), {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'Content-Type': 'application/json' }
            }).then(handleApiResponse).then(handleSuccess).catch(handleError);
        }
    }

    function duplicatePost(postId) {
        if (confirm('Are you sure you want to duplicate this post?')) {
            fetch(routes.duplicate.replace(':postId', postId), {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'Content-Type': 'application/json' }
            }).then(handleApiResponse).then(data => {
                if (data.success) {
                    Toastify({ text: data.message, duration: 3000, close: true, gravity: "top", position: "right", backgroundColor: "#22C55E" }).showToast();
                    if (data.redirect) { window.location.href = data.redirect; } else { window.location.reload(); }
                } else { throw new Error(data.message); }
            }).catch(handleError);
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('[data-action="publish-now"]').forEach(button => {
            button.addEventListener('click', function(e) { e.preventDefault(); publishPost(this.dataset.postId); });
        });
        document.querySelectorAll('[data-action="cancel-schedule"]').forEach(button => {
            button.addEventListener('click', function(e) { e.preventDefault(); cancelSchedule(this.dataset.postId); });
        });
        document.querySelectorAll('[data-action="duplicate"]').forEach(button => {
            button.addEventListener('click', function(e) { e.preventDefault(); duplicatePost(this.dataset.postId); });
        });
    });

    const categoryUrl = "{{ route('admin.content.categories.index') }}";
    let categoriesData = [];

    document.getElementById('manageCategoriesModal').addEventListener('show.bs.modal', function() { loadCategories(); });

    document.querySelector('#addCategoryForm input[name="name"]').addEventListener('input', function() {
        const slug = this.value.toLowerCase().replace(/[^a-z0-9 -]/g, '').replace(/\s+/g, '-').replace(/-+/g, '-');
        document.querySelector('#addCategoryForm input[name="slug"]').value = slug;
    });
    document.querySelector('#editCategoryForm input[name="name"]').addEventListener('input', function() {
        const slug = this.value.toLowerCase().replace(/[^a-z0-9 -]/g, '').replace(/\s+/g, '-').replace(/-+/g, '-');
        document.querySelector('#editCategoryForm input[name="slug"]').value = slug;
    });

    function loadCategories() {
        showLoading(true);
        fetch(categoryUrl).then(response => response.json()).then(categories => {
            categoriesData = categories;
            displayCategories(categories);
            populateParentOptions(categories);
            showLoading(false);
        }).catch(error => { console.error('Error loading categories:', error); showError('Failed to load categories'); showLoading(false); });
    }

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
                        <span style="background-color:${category.color}; color:white; padding:4px 12px; border-radius:9999px; font-size:.7rem; font-weight:600; margin-right:8px;">${category.name}</span>
                        <div>
                            ${category.description ? `<small class="text-muted">${category.description}</small>` : ''}
                            ${category.parent ? `<div><small style="color:#06B6D4;">Parent: ${category.parent.name}</small></div>` : ''}
                        </div>
                    </div>
                </td>
                <td><span style="background:#EFF6FF; color:#2563EB; padding:3px 10px; border-radius:9999px; font-size:.7rem; font-weight:600;">${category.posts_count || 0}</span></td>
                <td><span style="font-weight:600;">${formatNumber(category.views_count || 0)}</span></td>
                <td>
                    <button onclick="toggleCategoryStatus(${category.id})"
                            style="display:inline-flex; align-items:center; gap:4px; border-radius:9999px; padding:6px 14px; font-size:.7rem; font-weight:600; border:1px solid ${category.is_active ? '#22C55E' : '#E5E7EB'}; background:${category.is_active ? '#ECFDF5' : '#F7F8FC'}; color:${category.is_active ? '#22C55E' : '#000'}; cursor:pointer;">
                        ${category.is_active ? 'Active' : 'Inactive'}
                    </button>
                </td>
                <td>
                    <button onclick="editCategory(${category.id})" title="Edit" style="width:28px; height:28px; border-radius:9999px; border:1px solid #E5E7EB; background:#fff; cursor:pointer; margin-right:4px;">✎</button>
                    <button onclick="deleteCategory(${category.id})" title="Delete" style="width:28px; height:28px; border-radius:9999px; border:1px solid #E5E7EB; background:#fff; color:#EF4444; cursor:pointer;">🗑</button>
                </td>
            </tr>
        `).join('');
    }

    function populateParentOptions(categories, excludeId = null) {
        const parentCategories = categories.filter(cat => !cat.parent_id && cat.id !== excludeId);
        const addSelect = document.querySelector('#addCategoryForm select[name="parent_id"]');
        const editSelect = document.querySelector('#editCategoryForm select[name="parent_id"]');
        const options = parentCategories.map(cat => `<option value="${cat.id}">${cat.name}</option>`).join('');
        addSelect.innerHTML = '<option value="">None (Top Level)</option>' + options;
        editSelect.innerHTML = '<option value="">None (Top Level)</option>' + options;
    }

    function showAddCategoryModal() {
        const mainModal = bootstrap.Modal.getInstance(document.getElementById('manageCategoriesModal'));
        if (mainModal) { mainModal.hide(); }
        document.getElementById('addCategoryForm').reset();
        new bootstrap.Modal(document.getElementById('addCategoryModal')).show();
    }

    document.getElementById('addCategoryForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const data = Object.fromEntries(formData.entries());
        clearErrors(this);
        fetch(categoryUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: JSON.stringify(data)
        }).then(response => response.json()).then(result => {
            if (result.success) {
                showSuccess(result.message);
                bootstrap.Modal.getInstance(document.getElementById('addCategoryModal')).hide();
                loadCategories();
            } else { showError(result.message); }
        }).catch(error => { console.error('Error:', error); showError('Failed to create category'); });
    });

    function editCategory(categoryId) {
        const category = categoriesData.find(cat => cat.id === categoryId);
        if (!category) return;
        const mainModal = bootstrap.Modal.getInstance(document.getElementById('manageCategoriesModal'));
        if (mainModal) { mainModal.hide(); }
        const form = document.getElementById('editCategoryForm');
        form.querySelector('input[name="category_id"]').value = category.id;
        form.querySelector('input[name="name"]').value = category.name;
        form.querySelector('input[name="slug"]').value = category.slug;
        form.querySelector('textarea[name="description"]').value = category.description || '';
        form.querySelector('input[name="color"]').value = category.color || '#667eea';
        form.querySelector('select[name="parent_id"]').value = category.parent_id || '';
        form.querySelector('input[name="is_active"]').checked = category.is_active;
        populateParentOptions(categoriesData, categoryId);
        new bootstrap.Modal(document.getElementById('editCategoryModal')).show();
    }

    document.getElementById('editCategoryForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const data = Object.fromEntries(formData.entries());
        const categoryId = data.category_id;
        data.is_active = formData.has('is_active');
        clearErrors(this);
        fetch(`{{ url('admin/content/categories') }}/${categoryId}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: JSON.stringify(data)
        }).then(response => response.json()).then(result => {
            if (result.success) {
                showSuccess(result.message);
                bootstrap.Modal.getInstance(document.getElementById('editCategoryModal')).hide();
                loadCategories();
            } else { showError(result.message); }
        }).catch(error => { console.error('Error:', error); showError('Failed to update category'); });
    });

    function deleteCategory(categoryId) {
        const category = categoriesData.find(cat => cat.id === categoryId);
        if (!category) return;
        if (confirm(`Are you sure you want to delete the category "${category.name}"?`)) {
            fetch(`{{ url('admin/content/categories') }}/${categoryId}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
            }).then(response => response.json()).then(result => {
                if (result.success) { showSuccess(result.message); loadCategories(); } else { showError(result.message); }
            }).catch(error => { console.error('Error:', error); showError('Failed to delete category'); });
        }
    }

    function toggleCategoryStatus(categoryId) {
        fetch(`{{ url('admin/content/categories') }}/${categoryId}/toggle-status`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        }).then(response => response.json()).then(result => {
            if (result.success) { showSuccess(result.message); loadCategories(); } else { showError(result.message); }
        }).catch(error => { console.error('Error:', error); showError('Failed to update category status'); });
    }

    function showLoading(show) {
        document.getElementById('categoriesLoading').style.display = show ? 'block' : 'none';
        document.querySelector('#categoriesTable').style.display = show ? 'none' : 'table';
    }

    function formatNumber(num) {
        if (num >= 1000000) { return (num / 1000000).toFixed(1) + 'M'; }
        if (num >= 1000) { return (num / 1000).toFixed(1) + 'K'; }
        return num.toString();
    }

    function showSuccess(message) {
        const el = document.createElement('div');
        el.style.cssText = 'position:fixed; top:20px; right:20px; z-index:9999; background:#22C55E; color:#fff; padding:12px 18px; border-radius:9999px; font-size:13px; font-weight:600; font-family:"Plus Jakarta Sans",sans-serif; box-shadow:0 8px 20px rgba(0,0,0,.15);';
        el.textContent = message;
        document.body.appendChild(el);
        setTimeout(() => el.remove(), 4000);
    }

    function showError(message) {
        const el = document.createElement('div');
        el.style.cssText = 'position:fixed; top:20px; right:20px; z-index:9999; background:#EF4444; color:#fff; padding:12px 18px; border-radius:9999px; font-size:13px; font-weight:600; font-family:"Plus Jakarta Sans",sans-serif; box-shadow:0 8px 20px rgba(0,0,0,.15);';
        el.textContent = message;
        document.body.appendChild(el);
        setTimeout(() => el.remove(), 4000);
    }

    function clearErrors(form) {
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        form.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
    }
</script>
@endpush
@endsection
