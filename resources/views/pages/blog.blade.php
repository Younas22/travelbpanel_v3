@extends('common.layout')
@section('page_class', 'view-blog')
@section('content')

@php
    // Icon per category, matched on its name (categories come from admin)
    $categoryIcon = fn($name) => match (true) {
        str_contains(strtolower($name), 'flight') => 'fa-plane',
        str_contains(strtolower($name), 'hotel') || str_contains(strtolower($name), 'stay') => 'fa-hotel',
        str_contains(strtolower($name), 'visa') => 'fa-passport',
        str_contains(strtolower($name), 'tour') => 'fa-suitcase',
        str_contains(strtolower($name), 'umrah') => 'fa-kaaba',
        str_contains(strtolower($name), 'tip') => 'fa-lightbulb',
        str_contains(strtolower($name), 'guide') => 'fa-book-open',
        str_contains(strtolower($name), 'news') => 'fa-newspaper',
        str_contains(strtolower($name), 'destination') => 'fa-location-dot',
        default => 'fa-tag',
    };
@endphp

<div id="siteRedesign">
    <section class="hero-section is-compact pg-blog-hero">
        <div class="hero-content">
            <span class="pg-blog-eyebrow">{{ t('blog.title') }}</span>
            <h1 class="hero-title">{{ t('blog.heroTitle') }}</h1>
            <p class="hero-subtitle">{{ t('blog.heroSubtitle') }}</p>

            <!-- Search: filters every published post, results keep the chosen category -->
            <form class="pg-blog-search" action="{{ route('blog.index') }}" method="GET" role="search">
                <i class="fas fa-magnifying-glass" aria-hidden="true"></i>
                <input type="search" name="q" id="blogSearch" value="{{ $search }}" placeholder="{{ t('blog.searchHint') }}" aria-label="{{ t('blog.search') }}">
                @if($categoryId)
                    <input type="hidden" name="category" value="{{ $categoryId }}">
                @endif
                <button type="submit" class="pg-blog-search-btn">{{ t('blog.search') }}</button>
            </form>
        </div>
    </section>

    <!-- Categories (only ones that have published posts) -->
    @if($categories->count())
        <nav class="pg-blog-cats" aria-label="{{ t('blog.category') }}">
            <div class="pg-chips">
                <a href="{{ route('blog.index', array_filter(['q' => $search])) }}" class="pg-chip {{ $categoryId ? '' : 'is-active' }}" @unless($categoryId) aria-current="page" @endunless>
                    <i class="fas fa-border-all"></i> {{ t('blog.allPosts') }}
                </a>
                @foreach($categories as $category)
                    @php $isActive = (string) $categoryId === (string) $category->id; @endphp
                    <a href="{{ route('blog.index', array_filter(['category' => $category->id, 'q' => $search])) }}" class="pg-chip {{ $isActive ? 'is-active' : '' }}" @if($isActive) aria-current="page" @endif>
                        <i class="fas {{ $categoryIcon($category->name) }}"></i> {{ $category->name }}
                    </a>
                @endforeach
            </div>
        </nav>
    @endif

    <div class="pg-blog">
        <div class="pg-blog-grid" id="blogGrid">
            @forelse($blogs as $blog)
                @php $url = route('blog.detail', $blog->slug); @endphp
                <article class="pg-post">
                    <a href="{{ $url }}" class="pg-post-media">
                        @if($blog->featured_image)
                            <img src="{{ asset('public/' . $blog->featured_image) }}" alt="{{ $blog->title }}" loading="lazy" onerror="this.onerror=null;this.parentNode.classList.add('is-broken');this.remove()">
                        @else
                            <span class="pg-post-placeholder"><i class="fas fa-image"></i></span>
                        @endif
                        @if($blog->category)
                            <span class="pg-post-badge"><i class="fas {{ $categoryIcon($blog->category->name) }}"></i> {{ $blog->category->name }}</span>
                        @endif
                    </a>
                    <div class="pg-post-body">
                        <div class="pg-post-meta">
                            <span><i class="far fa-calendar"></i> {{ ($blog->published_at ?? $blog->created_at)->format('d M Y') }}</span>
                            @if($blog->reading_time)
                                <span><i class="far fa-clock"></i> {{ $blog->reading_time }} {{ t('blog.minRead') }}</span>
                            @endif
                        </div>
                        <h2 class="pg-post-title" dir="auto"><a href="{{ $url }}">{{ $blog->title }}</a></h2>
                        @if($blog->excerpt)
                            <p class="pg-post-excerpt">{{ $blog->excerpt }}</p>
                        @endif
                        <div class="pg-post-foot">
                            <a href="{{ $url }}" class="pg-read">{{ t('blog.readMore') }} <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                </article>
            @empty
                <div class="tl-empty pg-wide"><i class="fas fa-inbox"></i><h3>{{ t('blog.noBlogsFound') }}</h3></div>
            @endforelse
        </div>

        @if($blogs->hasPages())
            <nav class="tl-pagination pg-pagination" aria-label="Pagination">
                <div class="tl-pages">
                    @if($blogs->onFirstPage())
                        <span class="tl-page-btn pg-page-step is-disabled" aria-hidden="true"><i class="fas fa-chevron-left"></i> {{ t('blog.previousPage') }}</span>
                    @else
                        <a class="tl-page-btn pg-page-step" href="{{ $blogs->previousPageUrl() }}"><i class="fas fa-chevron-left"></i> {{ t('blog.previousPage') }}</a>
                    @endif
                    @for($n = 1; $n <= $blogs->lastPage(); $n++)
                        @if($n == $blogs->currentPage())
                            <span class="tl-page-btn is-active" aria-current="page">{{ $n }}</span>
                        @else
                            <a class="tl-page-btn" href="{{ $blogs->url($n) }}">{{ $n }}</a>
                        @endif
                    @endfor
                    @if($blogs->hasMorePages())
                        <a class="tl-page-btn pg-page-step" href="{{ $blogs->nextPageUrl() }}">{{ t('blog.nextPage') }} <i class="fas fa-chevron-right"></i></a>
                    @else
                        <span class="tl-page-btn pg-page-step is-disabled" aria-hidden="true">{{ t('blog.nextPage') }} <i class="fas fa-chevron-right"></i></span>
                    @endif
                </div>
            </nav>
        @endif
    </div>
</div>
@endsection
