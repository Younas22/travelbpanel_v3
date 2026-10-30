{{-- Left half of the split login / signup screens: who the company is, over
     the homepage cover image. Include inside .auth-split, before the form.
     Optional: $headline, $lead. --}}
@php
    $authBizName  = getSetting('business_name', 'main', 'TravelBookingPanel');
    $authHeadline = $headline ?? 'Your journey starts here';
    $authLead     = $lead ?? $authBizName . ' brings flights, hotels, tours, Umrah packages and visa services together in one place — with live availability, clear prices and secure booking.';
@endphp

<aside class="auth-brand">
    <a href="{{ url('/') }}" class="auth-brand-logo">
        <img src="{{ getSettingImage('business_logo_white', 'branding') }}" alt="{{ $authBizName }}">
    </a>

    <div>
        <span class="auth-brand-eyebrow">Your trusted travel partner</span>
        <h2>{{ $authHeadline }}</h2>
        <p class="auth-brand-lead">{{ $authLead }}</p>
        <ul class="auth-brand-points">
            <li><i class="fas fa-tags"></i> Best prices on flights, hotels &amp; tours</li>
            <li><i class="fas fa-shield-halved"></i> Secure payments and instant confirmation</li>
            <li><i class="fas fa-headset"></i> 24/7 support whenever you need us</li>
        </ul>
    </div>

    <p class="auth-brand-foot">&copy; {{ date('Y') }} {{ $authBizName }}. All rights reserved.</p>
</aside>
