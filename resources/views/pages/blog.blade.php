@extends('common.layout')
@section('page_class', 'view-blog')
@section('content')

<div id="siteRedesign">
    <section class="hero-section is-compact">
        <div class="hero-content">
            <h1 class="hero-title">{{ t('blog.title') }}</h1>
            <p class="hero-subtitle">{{ t('blog.subtitle') }}</p>
        </div>
    </section>

    <!-- Search + categories (filter the articles on this page) -->
    <div class="form-container is-overlap pg-panel pg-blog-tools">
        <div class="tl-input-icon pg-blog-search">
            <i class="fas fa-magnifying-glass"></i>
            <input type="search" id="blogSearch" class="tl-name-search" placeholder="{{ t('blog.searchPlaceholder') }}" aria-label="{{ t('blog.search') }}">
        </div>
        @if($categories->count())
            <div class="pg-chips" role="group" aria-label="{{ t('blog.category') }}">
                <button type="button" class="pg-chip is-active" data-category="">All</button>
                @foreach($categories as $category)
                    <button type="button" class="pg-chip" data-category="{{ $category->id }}">{{ $category->name }}</button>
                @endforeach
            </div>
        @endif
    </div>

    <div class="pg-blog">
        <div class="pg-blog-grid" id="blogGrid">
            @forelse($blogs as $blog)
                @php $url = route('blog.detail', $blog->slug); @endphp
                <article class="pg-post" data-category="{{ $blog->category_id }}" data-text="{{ strtolower($blog->title . ' ' . $blog->excerpt) }}">
                    <a href="{{ $url }}" class="pg-post-media">
                        @if($blog->featured_image)
                            <img src="{{ asset('public/' . $blog->featured_image) }}" alt="{{ $blog->title }}" loading="lazy" onerror="this.onerror=null;this.parentNode.classList.add('is-broken');this.remove()">
                        @else
                            <span class="pg-post-placeholder"><i class="fas fa-image"></i></span>
                        @endif
                        @if($blog->category)
                            <span class="th-chip">{{ $blog->category->name }}</span>
                        @endif
                    </a>
                    <div class="pg-post-body">
                        <div class="pg-post-meta">
                            <span><i class="fas fa-calendar"></i> {{ $blog->created_at->format('d M Y') }}</span>
                            <span><i class="fas fa-clock"></i> {{ $blog->reading_time }} {{ t('blog.minRead') }}</span>
                        </div>
                        <h2 class="pg-post-title" dir="auto"><a href="{{ $url }}">{{ $blog->title }}</a></h2>
                        @if($blog->excerpt)
                            <p class="pg-post-excerpt">{{ Str::limit($blog->excerpt, 120) }}</p>
                        @endif
                        <div class="pg-post-foot">
                            <span class="pg-author">
                                <span class="pg-avatar">
                                    @if($blog->author && $blog->author->profile_image)
                                        <img src="{{ asset('public/' . $blog->author->profile_image) }}" alt="">
                                    @else
                                        {{ $blog->author ? $blog->author->initials : 'AU' }}
                                    @endif
                                </span>
                                {{ $blog->author ? $blog->author->full_name : 'Admin' }}
                            </span>
                            <a href="{{ $url }}" class="pg-read">{{ t('blog.readMore') }} <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                </article>
            @empty
                <div class="tl-empty pg-wide"><i class="fas fa-inbox"></i><h3>{{ t('blog.noBlogsFound') }}</h3></div>
            @endforelse
            <div class="tl-empty pg-wide" id="blogNoMatch" hidden><i class="fas fa-magnifying-glass"></i><h3>{{ t('blog.noBlogsFound') }}</h3></div>
        </div>

        @if($blogs->hasPages())
            <nav class="tl-pagination pg-pagination" aria-label="Pagination">
                <div class="tl-pages">
                    @if($blogs->onFirstPage())
                        <span class="tl-page-btn is-disabled" aria-hidden="true"><i class="fas fa-chevron-left"></i></span>
                    @else
                        <a class="tl-page-btn" href="{{ $blogs->previousPageUrl() }}" aria-label="{{ t('blog.previousPage') }}"><i class="fas fa-chevron-left"></i></a>
                    @endif
                    @for($n = 1; $n <= $blogs->lastPage(); $n++)
                        @if($n == $blogs->currentPage())
                            <span class="tl-page-btn is-active" aria-current="page">{{ $n }}</span>
                        @else
                            <a class="tl-page-btn" href="{{ $blogs->url($n) }}">{{ $n }}</a>
                        @endif
                    @endfor
                    @if($blogs->hasMorePages())
                        <a class="tl-page-btn" href="{{ $blogs->nextPageUrl() }}" aria-label="{{ t('blog.nextPage') }}"><i class="fas fa-chevron-right"></i></a>
                    @else
                        <span class="tl-page-btn is-disabled" aria-hidden="true"><i class="fas fa-chevron-right"></i></span>
                    @endif
                </div>
                <div class="tl-page-summary">{{ t('blog.page') }} {{ $blogs->currentPage() }} {{ t('blog.of') }} {{ $blogs->lastPage() }}</div>
            </nav>
        @endif
    </div>
</div>

<script>
    (function () {
        const search = document.getElementById('blogSearch');
        const chips = Array.from(document.querySelectorAll('.pg-chip'));
        const posts = Array.from(document.querySelectorAll('.pg-post'));
        const noMatch = document.getElementById('blogNoMatch');
        let category = '';

        function apply() {
            const q = search.value.trim().toLowerCase();
            let visible = 0;
            posts.forEach(function (post) {
                const ok = (category === '' || post.dataset.category === category) && (q === '' || post.dataset.text.includes(q));
                post.hidden = !ok;
                if (ok) visible++;
            });
            noMatch.hidden = !(posts.length && visible === 0);
        }

        search.addEventListener('input', apply);
        chips.forEach(function (chip) {
            chip.addEventListener('click', function () {
                category = chip.dataset.category;
                chips.forEach(c => c.classList.toggle('is-active', c === chip));
                apply();
            });
        });
    })();
</script>
@endsection
