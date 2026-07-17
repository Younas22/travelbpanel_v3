@extends('common.layout')
@section('content')

<div class="flight-container">

    <!-- Back Link -->
    <a href="{{ route('blog.index') }}" class="back-link">
        <i class="fas fa-arrow-left"></i> {{t('blog.backToBlog')}}
    </a>

    <!-- Article Container -->
    <div class="article-container">

        <!-- Featured Image -->
        <div class="featured-image">
            @if($blog->featured_image)
                <img src="{{ asset('public/'.$blog->featured_image) }}" class="img-fluid" alt="{{ $blog->title }}">
            @else
                <i class="fas fa-image"></i>
            @endif
            @if($blog->image_caption)
                <div class="image-credit">{{ $blog->image_caption }}</div>
            @endif
        </div>

        <!-- Article Header -->
        <div class="article-header">

            <div class="article-meta">
                <span class="article-category">{{ $blog->category->name }}</span>

                <div class="article-meta-item">
                    <i class="fas fa-calendar"></i>
                    {{ $blog->created_at->format('d M Y') }}
                </div>

                <div class="article-meta-item">
                    <i class="fas fa-clock"></i>
                    {{ $blog->reading_time }} {{t('blog.minRead')}}
                </div>
            </div>

            <h1 class="article-title">{{ $blog->title }}</h1>

            @if($blog->excerpt)
            <p class="article-excerpt">
                {{ $blog->excerpt }}
            </p>
            @endif

            <!-- Author Info -->
            <div class="author-section">
                <div class="author-avatar">
                    @if($blog->author && $blog->author->profile_image)
                        <img src="{{ asset('public/' . $blog->author->profile_image) }}" alt="{{ $blog->author->full_name }}" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
                    @else
                        {{ $blog->author ? $blog->author->initials : 'AU' }}
                    @endif
                </div>
                <div class="author-info">
                    <div class="author-name">{{ $blog->author ? $blog->author->full_name : 'Admin' }}</div>
                    <div class="author-details">{{ $blog->author_bio ?? t('blog.authorBio') }}</div>
                </div>
            </div>
        </div>

        <!-- Article Body -->
        <div class="article-body">
            <div class="article-section">
                {!! processImages($blog->content) !!}
            </div>

            <!-- Share Section -->
            <!-- <div class="share-section">
                <div class="share-title">{{t('blog.shareThisArticle')}}</div>
                <div class="share-buttons">
                    <button onclick="sharePost('facebook')" class="share-btn">
                        <i class="fab fa-facebook"></i> {{t('blog.shareFacebook')}}
                    </button>
                    <button onclick="sharePost('twitter')" class="share-btn">
                        <i class="fab fa-twitter"></i> {{t('blog.shareTwitter')}}
                    </button>
                    <button onclick="sharePost('linkedin')" class="share-btn">
                        <i class="fab fa-linkedin"></i> {{t('blog.shareLinkedin')}}
                    </button>
                    <button onclick="sharePost('whatsapp')" class="share-btn">
                        <i class="fab fa-whatsapp"></i> {{t('blog.shareWhatsapp')}}
                    </button>
                </div>
            </div> -->

            <!-- Tags -->
            @if($blog->tags)
            <div class="article-section">
                <p><strong>{{t('blog.tags')}}:</strong></p>
                <div class="tags-section">
                    @foreach(explode(',', $blog->tags) as $tag)
                        <span class="tag">{{ trim($tag) }}</span>
                    @endforeach
                </div>
            </div>
            @endif

            <style>
                
            </style>

            <!-- Related Articles -->
            <div class="related-section">
                <h2 class="related-title">{{t('blog.relatedArticles')}}</h2>

                <div class="related-grid">
                    @forelse($relatedPosts as $blog)
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
                        <p>{{t('blog.noRelatedArticles')}}</p>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
</div>

<script>
function sharePost(platform) {
    const url = encodeURIComponent(window.location.href);
    const title = encodeURIComponent(document.querySelector('.article-title').textContent);
    let shareUrl = '';

    switch (platform) {
        case 'facebook':
            shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${url}`;
            break;
        case 'twitter':
            shareUrl = `https://twitter.com/intent/tweet?url=${url}&text=${title}`;
            break;
        case 'linkedin':
            shareUrl = `https://www.linkedin.com/sharing/share-offsite/?url=${url}`;
            break;
        case 'whatsapp':
            shareUrl = `https://wa.me/?text=${title}%20${url}`;
            break;
    }

    window.open(shareUrl, '_blank', 'width=600,height=400');
}

function openBlogDetail(slug) {
    window.location.href = "{{ route('blog.detail', ':slug') }}".replace(':slug', slug);
}
</script>

@endsection