@extends('common.layout')
@section('content')

<div class="flight-container">
 
    <div class="page-header">
        <h1><?= t('footer.'.$page->name); ?></h1>
        <p>Last Updated: <?= date('F j, Y', strtotime($page->updated_at)); ?></p>
    </div>

        <style>
        .page-data h1 {
    font-size: 32px;
    font-weight: 700;
    margin-bottom: 15px;
}

.page-data h2 {
    font-size: 26px;
    font-weight: 600;
    margin-bottom: 12px;
}

.page-data h3 {
    font-size: 22px;
    font-weight: 600;
    margin-bottom: 10px;
}

.page-data p {
    font-size: 16px;
    line-height: 1.7;
    margin-bottom: 15px;
}

.page-content ul {
    padding-left: 20px;
    margin-bottom: 15px;
}

.page-content ul li {
    list-style: disc;
    margin-bottom: 6px;
}

    </style>
    <div class="content-grid contact-info  page-data">
        {!! $page->content !!}
    </div>



</div>

@endsection
