@extends('common.layout')
@section('content')
    <div class="flight-container">
        <!-- Hero Section -->
        <div class="page-header">
            <h1>{{t('blog.title')}}</h1>
            <p class="mb-2">{{t('blog.subtitle')}}</p>
            <div class="search-box">
                <input type="text" class="search-input" placeholder="{{t('blog.searchPlaceholder')}}">
                <button class="search-btn">
                    <i class="fas fa-search"></i> {{t('blog.search')}}
                </button>
            </div>
        </div>

        <section class="blog-grid mt-4">
            @forelse($blogs as $blog)
                <div class="blog-card" onclick="openBlogDetail('{{ $blog->slug }}')">

                    <!-- Blog Image -->
                    <div class="blog-image">
                        @if($blog->featured_image)
                            <img src="{{ asset('public/'.$blog->featured_image) }}" alt="{{ $blog->title }}">
                        @else
                            <div class="blog-image-placeholder">
                                <i class="fas fa-image"></i>
                            </div>
                        @endif

                        <div class="blog-category">
                            {{ $blog->category->name }}
                        </div>
                    </div>

                    <!-- Blog Content -->
                    <div class="blog-content">
                        <div class="blog-date">
                            <i class="fas fa-calendar"></i>
                            {{ $blog->created_at->format('d M Y') }}
                        </div>

                        <div class="blog-title">{{ $blog->title }}</div>

                        <div class="blog-excerpt">
                            {{ Str::limit($blog->excerpt, 120) }}
                        </div>
                    </div>

                    <!-- Blog Footer -->
                        <div class="blog-footer">
                            <div class="blog-author">
                                <div class="author-avatar">
                                    @if($blog->author && $blog->author->profile_image)
                                        <img src="{{ asset('public/' . $blog->author->profile_image) }}" alt="{{ $blog->author->full_name }}" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
                                    @else
                                        {{ $blog->author ? $blog->author->initials : 'AU' }}
                                    @endif
                                </div>
                                <div class="author-name">{{ $blog->author ? $blog->author->full_name : 'Admin' }}</div>
                            </div>

                            <div class="read-time">
                                <i class="fas fa-clock"></i>
                                {{ $blog->reading_time }} {{t('blog.minRead')}}
                            </div>
                        </div>

                </div>
            @empty
                <div class="no-blogs-found">
                    <i class="fas fa-inbox"></i>
                    <p>{{t('blog.noBlogsFound')}}</p>
                </div>
            @endforelse
        </section>

        <!-- Pagination -->
        <div class="blog-pagination mt-4">
            {{ $blogs->links() }}
        </div>
    </div>

    <script>
        function openBlogDetail(slug) {
            window.location.href = "{{ route('blog.detail', ':slug') }}".replace(':slug', slug);
        }
    </script>

@endsection