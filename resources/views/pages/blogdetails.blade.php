@extends('common.layout')
@section('content')
@php
    $post = $blog;
    $shareUrl = urlencode(url()->current());
    $shareTitle = urlencode($post->title);
@endphp

<div class="td-page pg-article-page">
    <!-- Breadcrumbs -->
    <nav class="td-breadcrumb" aria-label="Breadcrumb">
        <a href="{{ url('/') }}">{{ t('header.Home') }}</a>
        <i class="fas fa-chevron-right" aria-hidden="true"></i>
        <a href="{{ route('blog.index') }}">{{ t('blog.title') }}</a>
        @if($post->category)
            <i class="fas fa-chevron-right" aria-hidden="true"></i>
            <span>{{ $post->category->name }}</span>
        @endif
    </nav>

    <!-- Header -->
    <div class="td-header">
        <h1 class="td-title pg-article-title" dir="auto">{{ $post->title }}</h1>
        <div class="td-meta">
            @if($post->category)<span class="fb-tag">{{ $post->category->name }}</span>@endif
            <span class="td-meta-item"><i class="fas fa-calendar"></i> {{ $post->created_at->format('d M Y') }}</span>
            <span class="td-meta-item"><i class="fas fa-clock"></i> {{ $post->reading_time }} {{ t('blog.minRead') }}</span>
            <span class="td-meta-item"><i class="fas fa-user"></i> {{ $post->author ? $post->author->full_name : 'Admin' }}</span>
        </div>
    </div>

    <div class="td-main">
        <div class="td-main-col">
            @if($post->featured_image)
                <figure class="pg-article-image">
                    <img src="{{ asset('public/' . $post->featured_image) }}" alt="{{ $post->title }}">
                    @if($post->image_caption)<figcaption>{{ $post->image_caption }}</figcaption>@endif
                </figure>
            @endif

            <article class="td-card">
                @if($post->excerpt)
                    <p class="pg-lead" dir="auto">{{ $post->excerpt }}</p>
                @endif
                <div class="pg-prose" dir="auto">
                    {!! processImages($post->content) !!}
                </div>

                @if($post->tags)
                    <div class="pg-tags">
                        <b>{{ t('blog.tags') }}:</b>
                        @foreach(explode(',', $post->tags) as $tag)
                            @if(trim($tag) !== '')<span class="tl-pill">#{{ trim($tag) }}</span>@endif
                        @endforeach
                    </div>
                @endif
            </article>

            <!-- Previous / next -->
            @if($previousPost || $nextPost)
                <nav class="pg-prevnext" aria-label="Articles">
                    @if($previousPost)
                        <a href="{{ route('blog.detail', $previousPost->slug) }}" class="pg-prevnext-link">
                            <small><i class="fas fa-arrow-left"></i> {{ t('blog.previousPage') }}</small>
                            <b dir="auto">{{ $previousPost->title }}</b>
                        </a>
                    @else
                        <span></span>
                    @endif
                    @if($nextPost)
                        <a href="{{ route('blog.detail', $nextPost->slug) }}" class="pg-prevnext-link is-next">
                            <small>{{ t('blog.nextPage') }} <i class="fas fa-arrow-right"></i></small>
                            <b dir="auto">{{ $nextPost->title }}</b>
                        </a>
                    @endif
                </nav>
            @endif
        </div>

        <aside class="td-sidebar">
            <!-- Author -->
            <div class="td-card">
                <div class="pg-author-card">
                    <span class="pg-avatar is-lg">
                        @if($post->author && $post->author->profile_image)
                            <img src="{{ asset('public/' . $post->author->profile_image) }}" alt="">
                        @else
                            {{ $post->author ? $post->author->initials : 'AU' }}
                        @endif
                    </span>
                    <div>
                        <small>{{ t('blog.author') }}</small>
                        <b>{{ $post->author ? $post->author->full_name : 'Admin' }}</b>
                        @if($post->author_bio)<p>{{ $post->author_bio }}</p>@endif
                    </div>
                </div>
            </div>

            <!-- Share -->
            <div class="td-card">
                <h2 class="td-side-title">{{ t('blog.shareThisArticle') }}</h2>
                <div class="sf-social pg-share">
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ $shareUrl }}" target="_blank" rel="noopener" aria-label="{{ t('blog.shareFacebook') }}"><i class="fab fa-facebook-f"></i></a>
                    <a href="https://twitter.com/intent/tweet?url={{ $shareUrl }}&text={{ $shareTitle }}" target="_blank" rel="noopener" aria-label="{{ t('blog.shareTwitter') }}"><i class="fab fa-twitter"></i></a>
                    <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ $shareUrl }}" target="_blank" rel="noopener" aria-label="{{ t('blog.shareLinkedin') }}"><i class="fab fa-linkedin-in"></i></a>
                    <a href="https://wa.me/?text={{ $shareTitle }}%20{{ $shareUrl }}" target="_blank" rel="noopener" aria-label="{{ t('blog.shareWhatsapp') }}"><i class="fab fa-whatsapp"></i></a>
                </div>
            </div>

            <!-- Related -->
            <div class="td-card">
                <h2 class="td-side-title">{{ t('blog.relatedArticles') }}</h2>
                @forelse($relatedPosts as $related)
                    <a href="{{ route('blog.detail', $related->slug) }}" class="pg-related">
                        @if($related->featured_image)
                            <img src="{{ asset('public/' . $related->featured_image) }}" alt="" loading="lazy">
                        @else
                            <span class="pg-post-placeholder"><i class="fas fa-image"></i></span>
                        @endif
                        <span>
                            <b dir="auto">{{ $related->title }}</b>
                            <small>{{ $related->created_at->format('d M Y') }}</small>
                        </span>
                    </a>
                @empty
                    <p class="pg-muted">{{ t('blog.noRelatedArticles') }}</p>
                @endforelse
            </div>
        </aside>
    </div>
</div>
@endsection
