@extends('common.layout')
@section('content')
<style>
    /* Hero layout lives in style.css (#siteRedesign); only the cover image is set here. */
    #siteRedesign .hero-section {
        background-image: url('<?=getSettingImage('cover_image', 'homepage')?>');
    }
</style>

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
