@extends('common.layout')
@section('page_class', 'view-static-page')
@section('content')

<div id="siteRedesign">
    <section class="hero-section is-compact">
        <div class="hero-content">
            @php
                // title typed for this language → footer translation of the built-in pages → title in the default language / page name
                $heading = $page->translation('title', null, false) ?: t('footer.' . $page->name);
                if ($heading === 'footer.' . $page->name) {
                    $heading = $page->title;
                }
            @endphp
            <h1 class="hero-title">{{ $heading }}</h1>
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
