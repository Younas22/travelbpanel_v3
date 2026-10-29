{{-- Left half of the split login / signup screens: who the company is, over
     the homepage cover image. Include inside .auth-split, before the form.
     Optional: $headline, $lead. --}}
@php
    $authBizName  = getSetting('business_name', 'main', 'TravelBookingPanel');
    $authHeadline = $headline ?? 'Your journey starts here';
    $authLead     = $lead ?? $authBizName . ' brings flights, hotels, tours, Umrah packages and visa services together in one place — with live availability, clear prices and secure booking.';
@endphp
<style>
    .auth-split {
        display: grid;
        grid-template-columns: 1fr 1fr;
        height: 100vh;
        height: 100dvh;
        overflow: hidden;
        background: #fff;
    }
    /* The form side scrolls on its own only if a very short window can't fit it. */
    .auth-split > .login-wrap,
    .auth-split > .reg-wrap,
    .auth-split > .fp-wrap {
        min-height: 0 !important;
        height: 100%;
        overflow-y: auto;
        padding: 32px 24px !important;
        background: #fff !important;
    }
    .auth-split .form-card,
    .auth-split .fp-card {
        border: 0 !important;
        box-shadow: none !important;
        max-width: 420px;
    }
    .auth-split .form-head,
    .auth-split .fp-head { padding: 0 0 6px !important; text-align: left; }
    .auth-split .form-head .logo-ico,
    .auth-split .fp-head .ico { margin: 0 0 14px; }
    .auth-split .form-body,
    .auth-split .fp-body { padding: 18px 0 0 !important; }

    .auth-brand {
        position: relative;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: 40px 48px;
        color: #fff;
        background: #0B1F4D url('<?=getSettingImage('cover_image', 'homepage')?>') center / cover no-repeat;
        overflow: hidden;
    }
    .auth-brand::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(160deg, rgba(11, 31, 77, .88) 0%, rgba(37, 99, 235, .72) 55%, rgba(11, 31, 77, .9) 100%);
    }
    .auth-brand > * { position: relative; }

    .auth-brand-logo { display: inline-flex; align-self: flex-start; }
    .auth-brand-logo img { max-height: 48px; max-width: 220px; width: auto; }

    .auth-brand-eyebrow {
        display: inline-block;
        margin-bottom: 14px;
        padding: 5px 12px;
        border-radius: 999px;
        background: rgba(255, 255, 255, .14);
        font-size: 12px;
        font-weight: 600;
        letter-spacing: .06em;
        text-transform: uppercase;
    }
    .auth-brand h2 {
        margin: 0 0 14px;
        font-size: clamp(28px, 3vw, 42px);
        font-weight: 700;
        line-height: 1.12;
        color: #fff;
    }
    .auth-brand-lead {
        max-width: 460px;
        margin: 0 0 26px;
        font-size: 15px;
        line-height: 1.65;
        color: rgba(255, 255, 255, .85);
    }
    .auth-brand-points { display: grid; gap: 14px; margin: 0; padding: 0; list-style: none; }
    .auth-brand-points li { display: flex; align-items: center; gap: 12px; font-size: 14px; font-weight: 600; }
    .auth-brand-points i {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        flex-shrink: 0;
        border-radius: 50%;
        background: rgba(255, 255, 255, .16);
        font-size: 14px;
    }
    .auth-brand-foot { font-size: 12px; color: rgba(255, 255, 255, .6); }

    /* Short desktop windows: tighten the panel so it still fits without scrolling. */
    @media (max-height: 700px) and (min-width: 961px) {
        .auth-brand { padding: 28px 40px; }
        .auth-brand-lead { margin-bottom: 18px; }
        .auth-brand-points { gap: 10px; }
    }
    /* Phones / tablets: just the form. */
    @media (max-width: 960px) {
        .auth-split { grid-template-columns: 1fr; height: auto; min-height: 100dvh; overflow: visible; }
        .auth-split > .login-wrap,
        .auth-split > .reg-wrap,
        .auth-split > .fp-wrap { height: auto; min-height: 100dvh !important; }
        .auth-brand { display: none; }
    }
</style>

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
