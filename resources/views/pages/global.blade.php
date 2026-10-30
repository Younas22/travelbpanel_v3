@extends('common.layout')
@section('page_class', 'view-static-page')
@section('content')

<div id="siteRedesign">
    <section class="hero-section is-compact">
        <div class="hero-content">
            <h1 class="hero-title">{{ t('footer.' . $page->name) }}</h1>
            <p class="hero-subtitle"><i class="fas fa-calendar"></i> Last Updated: {{ date('F j, Y', strtotime($page->updated_at)) }}</p>
        </div>
    </section>

    <div class="form-container is-overlap pg-panel">
        <article class="pg-prose">
            {!! $page->content !!}
        </article>
    </div>
</div>
@endsection
