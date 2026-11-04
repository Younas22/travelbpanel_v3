@extends('common.layout')
@section('page_class', 'view-flight-error')
@section('content')

@php
    // support contact from admin → Settings → Contact (same values as the Contact page)
    $feEmail = getSetting('support_email', 'contact') ?: getSetting('contact_email', 'contact');
    $fePhone = getSetting('contact_phone', 'contact');
    $feNotFound = ($error_code ?? '') === 'NO_FLIGHTS_FOUND';
@endphp

<div id="siteRedesign">
    <section class="hero-section is-compact">
        <div class="hero-content">
            <h1 class="hero-title">{{ $error_title ?? 'Flight Search Error' }}</h1>
            <p class="hero-subtitle">{{ $error_message ?? 'We encountered an issue while processing your flight search. Please check your search parameters and try again.' }}</p>
        </div>
    </section>

    <div class="form-container is-overlap fe-panel">
        <div class="fe-head">
            <span class="fe-icon"><i class="fas {{ $feNotFound ? 'fa-plane-slash' : 'fa-triangle-exclamation' }}"></i></span>
            <div class="fe-head-text">
                <span class="fe-code">{{ t('error.flight_error_code') }}: <span dir="ltr">{{ $error_code ?? 'FLIGHT_SEARCH_ERROR' }}</span></span>
                <h2>{{ $error_title ?? 'Flight Search Error' }}</h2>
                @if(!empty($error_details))
                    <p>{{ $error_details }}</p>
                @endif
            </div>
        </div>

        {{-- validation problems with the search --}}
        @if(isset($search_errors) && $search_errors->any())
            <div class="fe-box fe-box-warn">
                <h3><i class="fas fa-list-ul"></i> {{ t('error.flight_search_issues') }}</h3>
                <ul>
                    @foreach($search_errors->all() as $searchError)
                        <li>{{ $searchError }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="fe-box">
            <h3><i class="fas fa-lightbulb"></i> {{ t('error.flight_tips_title') }}</h3>
            <ul class="fe-tips">
                <li><i class="fas fa-calendar-day"></i> {{ t('error.flight_tip_dates') }}</li>
                <li><i class="fas fa-right-left"></i> {{ t('error.flight_tip_return') }}</li>
                <li><i class="fas fa-location-dot"></i> {{ t('error.flight_tip_airports') }}</li>
                <li><i class="fas fa-users"></i> {{ t('error.flight_tip_passengers') }}</li>
            </ul>
        </div>

        <div class="fe-actions">
            <a href="{{ route('flights.index') }}" class="fe-btn fe-btn-primary">
                <i class="fas fa-magnifying-glass"></i> {{ t('error.flight_new_search') }}
            </a>
            <a href="{{ url('/') }}" class="fe-btn">
                <i class="fas fa-house"></i> {{ t('error.flight_home') }}
            </a>
        </div>

        <div class="fe-support">
            <span class="fe-support-icon"><i class="fas fa-headset"></i></span>
            <div class="fe-support-text">
                <b>{{ t('error.flight_need_help') }}</b>
                <small>{{ t('error.flight_help_text') }}</small>
            </div>
            <div class="fe-support-links">
                @if($feEmail)
                    <a href="mailto:{{ $feEmail }}"><i class="fas fa-envelope"></i> <span dir="ltr">{{ $feEmail }}</span></a>
                @endif
                @if($fePhone)
                    <a href="tel:{{ preg_replace('/[^\d+]/', '', $fePhone) }}"><i class="fas fa-phone"></i> <span dir="ltr">{{ $fePhone }}</span></a>
                @endif
            </div>
        </div>

        <p class="fe-time">{{ t('error.flight_error_time') }}: <span dir="ltr">{{ date('Y-m-d H:i:s') }}</span></p>
    </div>
</div>
@endsection
