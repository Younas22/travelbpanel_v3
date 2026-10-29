@include('common.header')

<style>
    :root {
        --primary:   #2563EB;
        --primary-m: #1D4ED8;
        --accent:    #2563EB;
        --accent-l:  #EFF4FF;
        --text:      #0f172a;
        --muted:     #64748b;
        --border:    #e2e8f0;
        --surface:   #f8fafc;
    }

    /* ─── HERO ─── */
    .ag-hero {
        background: #2563EB;
        padding: 42px 24px 36px;
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    .ag-hero-grid {
        position: absolute; inset: 0; opacity: .04;
        background-image: linear-gradient(#fff 1px,transparent 1px),linear-gradient(90deg,#fff 1px,transparent 1px);
        background-size: 48px 48px;
    }
    .ag-hero-inner { max-width: 620px; margin: 0 auto; position: relative; z-index: 2; }
    .ag-badge {
        display: inline-flex; align-items: center; gap: 6px;
        background: rgba(255,255,255,.18); border: 1px solid rgba(255,255,255,.35);
        color: #fff; font-size: .72rem; font-weight: 600;
        letter-spacing: .6px; text-transform: uppercase;
        padding: 4px 14px; border-radius: 50px; margin-bottom: 16px;
    }
    .ag-hero h1 {
        font-size: clamp(1.55rem, 4vw, 2.35rem);
        font-weight: 800; color: #fff; line-height: 1.18;
        letter-spacing: -.02em; margin-bottom: 12px;
    }
    .ag-hero h1 span { color: #FFFFFF; }
    .ag-hero-sub {
        font-size: .88rem; color: rgba(255,255,255,.85);
        max-width: 480px; margin: 0 auto 24px; line-height: 1.65;
    }
    .ag-hero-btns { display: flex; flex-wrap: wrap; gap: 10px; justify-content: center; margin-bottom: 28px; }

    .btn-ag-primary {
        background: #fff; color: var(--accent) !important; text-decoration: none !important;
        border: none; border-radius: 9px; padding: 11px 26px;
        font-size: .88rem; font-weight: 700; cursor: pointer;
        transition: all .2s; display: inline-flex; align-items: center; gap: 7px;
        box-shadow: 0 4px 18px rgba(0,0,0,.15); letter-spacing: -.01em;
        font-family: inherit;
    }
    .btn-ag-primary:hover { background: #EFF4FF; transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,.2); }

    .btn-ag-ghost {
        background: rgba(255,255,255,.15); color: #fff !important;
        border: 1.5px solid rgba(255,255,255,.4); text-decoration: none !important;
        border-radius: 9px; padding: 10px 22px;
        font-size: .88rem; font-weight: 600; cursor: pointer; transition: all .2s;
        display: inline-flex; align-items: center; gap: 7px;
        font-family: inherit;
    }
    .btn-ag-ghost:hover { background: rgba(255,255,255,.18); border-color: rgba(255,255,255,.4); }

    .ag-stats { display: flex; flex-wrap: wrap; gap: 10px; justify-content: center; }
    .ag-stat {
        background: rgba(255,255,255,.18); border: 1px solid rgba(255,255,255,.3);
        border-radius: 10px; padding: 12px 20px; text-align: center; min-width: 90px;
    }
    .ag-stat-n { font-size: 1.3rem; font-weight: 800; color: #fff; letter-spacing: -.02em; }
    .ag-stat-l { font-size: .67rem; color: rgba(255,255,255,.8); margin-top: 1px; text-transform: uppercase; letter-spacing: .4px; }

    /* ─── TRUST STRIP ─── */
    .ag-trust {
        background: var(--surface); border-bottom: 1px solid var(--border);
        padding: 12px 24px;
    }
    .ag-trust-inner {
        max-width: 1060px; margin: 0 auto;
        display: flex; flex-wrap: wrap; gap: 6px 24px; align-items: center; justify-content: center;
    }
    .ag-ti { display: flex; align-items: center; gap: 5px; font-size: .77rem; font-weight: 500; color: var(--muted); }
    .ag-ti i { color: var(--accent); font-size: .8rem; }

    /* ─── SECTION ─── */
    .ag-sec { padding: 52px 24px; }
    .ag-sec-sm { padding: 40px 24px; }
    .ag-inner { max-width: 1120px; margin: 0 auto; }

    .ag-eyebrow {
        display: inline-flex; align-items: center; gap: 5px;
        color: var(--accent); font-size: .7rem; font-weight: 700;
        letter-spacing: .9px; text-transform: uppercase; margin-bottom: 8px;
    }
    .ag-eyebrow::before { content: ''; width: 16px; height: 2px; background: var(--accent); border-radius: 2px; }
    .ag-h { font-size: clamp(1.2rem, 2.5vw, 1.75rem); font-weight: 800; color: var(--text); letter-spacing: -.02em; margin-bottom: 8px; }
    .ag-h span { color: var(--accent); }
    .ag-p { font-size: .85rem; color: var(--muted); line-height: 1.65; max-width: 440px; }

    /* ─── BENEFIT CARD ─── */
    .ben-card {
        background: #fff; border: 1px solid var(--border);
        border-radius: 14px; padding: 20px; height: 100%;
        transition: box-shadow .25s, transform .25s, border-color .25s;
    }
    .ben-card:hover { box-shadow: 0 8px 28px rgba(0,0,0,.07); transform: translateY(-3px); border-color: #bfdbfe; }
    .ben-ico {
        width: 40px; height: 40px; background: var(--accent-l); border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        color: var(--accent); font-size: 1rem; margin-bottom: 12px;
    }
    .ben-card h6 { font-size: .88rem; font-weight: 700; color: var(--text); margin-bottom: 5px; }
    .ben-card p { font-size: .8rem; color: var(--muted); line-height: 1.6; margin: 0; }

    /* ─── HOW IT WORKS ─── */
    .step-card {
        background: #fff; border: 1px solid var(--border);
        border-radius: 14px; padding: 24px 20px; text-align: center;
        height: 100%; position: relative;
    }
    .step-num {
        width: 38px; height: 38px; border-radius: 50%;
        background: var(--primary); color: #fff;
        font-size: .95rem; font-weight: 800;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 14px;
    }
    .step-card h6 { font-size: .88rem; font-weight: 700; margin-bottom: 6px; color: var(--text); }
    .step-card p { font-size: .8rem; color: var(--muted); line-height: 1.6; margin: 0; }
    .step-arr { position: absolute; top: 34px; right: -14px; color: #cbd5e1; font-size: 1rem; z-index: 1; }

    /* ─── CTA BAND ─── */
    .cta-band {
        background: var(--primary);
        border-radius: 18px; padding: 44px 36px; text-align: center;
        position: relative; overflow: hidden;
    }
    .cta-band::before {
        content: '';
        position: absolute; inset: 0;
        background: none;
    }
    .cta-band h3 {
        font-size: clamp(1.1rem, 2.2vw, 1.55rem); font-weight: 800;
        color: #fff; letter-spacing: -.02em; margin-bottom: 6px; position: relative;
    }
    .cta-band p { color: rgba(255,255,255,.68); font-size: .85rem; margin-bottom: 22px; position: relative; }
    .btn-cta-w {
        background: #fff; color: var(--primary) !important; text-decoration: none !important;
        border: none; border-radius: 9px; padding: 11px 28px;
        font-size: .9rem; font-weight: 700; cursor: pointer; transition: all .2s;
        display: inline-flex; align-items: center; gap: 7px;
        box-shadow: 0 4px 16px rgba(0,0,0,.18); position: relative;
        font-family: inherit;
    }
    .btn-cta-w:hover { background: #EFF4FF; transform: translateY(-2px); }

    /* ─── FORM ─── */
    .form-wrap {
        max-width: 640px; margin: 0 auto;
        background: #fff; border: 1px solid var(--border);
        border-radius: 18px; box-shadow: 0 12px 48px rgba(0,0,0,.09);
        overflow: hidden;
    }
    .form-head {
        background: var(--primary);
        padding: 24px 32px; text-align: center;
    }
    .form-head h4 { color: #fff; font-size: 1.1rem; font-weight: 800; margin-bottom: 3px; letter-spacing: -.01em; }
    .form-head p { color: rgba(255,255,255,.65); font-size: .78rem; margin: 0; }
    .form-body { padding: 28px 32px; }

    /* ─── MODAL ─── */
    .m-ov {
        display: none; position: fixed; inset: 0;
        background: rgba(2,12,36,.72);
        z-index: 9999; align-items: center; justify-content: center;
        padding: 16px; backdrop-filter: blur(6px);
    }
    .m-ov.show { display: flex; }
    .m-box {
        background: #fff; border-radius: 18px; width: 100%; max-width: 540px;
        max-height: 92vh; overflow-y: auto;
        box-shadow: 0 32px 80px rgba(0,0,0,.35);
        animation: mIn .28s cubic-bezier(.34,1.4,.64,1);
    }
    @keyframes mIn {
        from { opacity:0; transform: translateY(20px) scale(.96); }
        to   { opacity:1; transform: translateY(0) scale(1); }
    }
    .m-head {
        background: var(--primary);
        padding: 22px 26px; border-radius: 18px 18px 0 0; position: relative;
    }
    .m-head h5 { color: #fff; font-size: 1rem; font-weight: 800; margin-bottom: 2px; letter-spacing: -.01em; }
    .m-head p  { color: rgba(255,255,255,.62); font-size: .76rem; margin: 0; }
    .m-close {
        position: absolute; top: 12px; right: 14px;
        background: rgba(255,255,255,.13); border: none; color: #fff;
        width: 28px; height: 28px; border-radius: 50%; cursor: pointer;
        font-size: .82rem; display: flex; align-items: center; justify-content: center;
        transition: background .2s;
    }
    .m-close:hover { background: rgba(255,255,255,.26); }
    .m-body { padding: 24px 26px; }

    /* ─── FORM FIELDS ─── */
    .f-label { display: block; font-size: .78rem; font-weight: 600; color: #374151; margin-bottom: 4px; }
    .f-label .req { color: var(--accent); }
    .f-input {
        width: 100%; border: 1.5px solid #d1d5db; border-radius: 8px;
        padding: 9px 12px; font-size: .85rem; font-family: inherit;
        background: #fafafa; color: var(--text); transition: border-color .2s, box-shadow .2s;
    }
    .f-input:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 0 3px rgba(37,99,235,.11); background: #fff; }
    .f-input.is-invalid { border-color: #ef4444; }
    .f-err { display: block; font-size: .73rem; color: #ef4444; margin-top: 3px; }
    .f-pw { position: relative; }
    .f-pw .f-input { padding-right: 38px; }
    .pw-eye {
        position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
        background: none; border: none; cursor: pointer; color: #9ca3af;
        font-size: .9rem; padding: 0; line-height: 1; display: flex; align-items: center;
    }
    .pw-eye:hover { color: var(--accent); }
    .btn-sub {
        width: 100%; background: var(--accent); color: #fff; border: none;
        border-radius: 9px; padding: 12px; font-size: .9rem; font-weight: 700;
        cursor: pointer; transition: all .2s; margin-top: 18px;
        display: flex; align-items: center; justify-content: center; gap: 6px;
        font-family: inherit; letter-spacing: -.01em;
    }
    .btn-sub:hover { background: #1D4ED8; transform: translateY(-1px); box-shadow: 0 6px 18px rgba(37,99,235,.35); }
    .f-note { font-size: .72rem; color: var(--muted); text-align: center; margin-top: 10px; line-height: 1.5; }
    .f-hint { text-align: center; margin-top: 12px; font-size: .8rem; color: var(--muted); }
    .f-hint a { color: var(--primary); font-weight: 600; text-decoration: none; }
    .f-hint a:hover { color: var(--accent); }
    .f-alert { border-radius: 8px; font-size: .8rem; padding: 9px 12px; margin-bottom: 12px; }
    .f-alert.err { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
    .f-alert.ok  { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }

    /* ─── GRID HELPERS ─── */
    .ag-row { display: flex; flex-wrap: wrap; margin: -8px; }
    .ag-col { padding: 8px; }
    .ag-col-12 { width: 100%; }
    .ag-col-6  { width: 50%; }
    .ag-col-4  { width: 33.333%; }
    @media (max-width: 640px) {
        .ag-col-6 { width: 100%; }
        .ag-col-4 { width: 100%; }
        .ag-hero h1 { font-size: 1.45rem; }
        .ag-sec { padding: 36px 16px; }
        .ag-sec-sm { padding: 28px 16px; }
        .cta-band { padding: 32px 20px; }
        .form-head, .form-body { padding: 20px; }
        .m-body, .m-head { padding: 18px 18px; }
        .step-arr { display: none; }
    }

    /* submit loading state */
    body.ag-loading .btn-sub .lbl { display: none; }
    body.ag-loading .btn-sub .spin { display: flex; }
    .btn-sub .spin { display: none; align-items: center; gap: 7px; }

    /* ---- Site redesign: brand blue, white card on the homepage cover image ---- */
    .login-wrap, .reg-wrap, .fp-wrap {
        min-height: calc(100vh - 0px);
        background: #F5F9FE url('<?=getSettingImage('cover_image', 'homepage')?>') center 35% / cover no-repeat;
    }
    .form-card, .fp-card, .form-wrap {
        border: 1px solid rgba(0,0,0,.08);
        border-radius: 20px;
        box-shadow: 0 24px 64px rgba(0,0,0,.14);
        animation: none;
    }
    .form-head, .fp-head {
        background: #fff;
        padding: 30px 32px 6px;
        color: #000;
    }
    .form-head h4, .fp-head h4 { color: #000; font-size: 1.5rem; font-weight: 700; letter-spacing: 0; margin: 0 0 4px; }
    .form-head p, .fp-head p { color: rgba(0,0,0,.6); font-size: .85rem; }
    .form-head .logo-ico, .fp-head .ico { background: rgba(37,99,235,.1); color: #2563EB; }
    .f-label, .fp-label {
        color: rgba(0,0,0,.55); font-size: 11px; font-weight: 600;
        letter-spacing: .06em; text-transform: uppercase;
    }
    .f-input, .fp-input {
        height: 46px; padding: 0 14px;
        border: 1px solid rgba(0,0,0,.12); border-radius: 12px;
        background: #fff; color: #000; font-size: 14px;
    }
    select.f-input { padding-right: 32px; }
    .f-input:hover, .fp-input:hover { border-color: rgba(37,99,235,.45); }
    .f-input:focus, .fp-input:focus { border-color: #2563EB; box-shadow: 0 0 0 3px rgba(37,99,235,.12); background: #fff; }
    .btn-login, .btn-sub, .fp-btn {
        height: 48px; padding: 0 16px; border-radius: 12px;
        background: #2563EB; font-weight: 600; box-shadow: 0 8px 18px rgba(37,99,235,.28);
    }
    .btn-login:hover, .btn-sub:hover, .fp-btn:hover { background: #1D4ED8; transform: none; }
    .f-hint a, .fp-hint a { color: #2563EB; }
</style>


{{-- ══ HERO ══ --}}
@php $apBusiness = getSetting('business_name', 'main', 'TravelBookingPanel'); @endphp
<div class="ap-page">
<section class="ap-hero">
    <div class="ap-inner ap-hero-grid">
        <div class="ap-hero-text">
            <span class="ap-badge"><i class="fas fa-shield-halved"></i> Trusted by 100+ Travel Agents</span>
            <h1 class="ap-hero-title">
                Become a <span>Travel Agent</span> &amp; <span>Start Your Business Today</span>
            </h1>
            <p class="ap-hero-sub">
                Sell flights, hotels, tours, Umrah packages &amp; visa services — all from one powerful dashboard.
                Earn commission on every booking with no upfront costs.
            </p>
            <div class="ap-hero-btns">
                <a href="#register-form" class="ap-btn"><i class="fas fa-user-plus"></i> Become a Travel Agent</a>
                <a href="#how-it-works" class="ap-btn is-outline"><i class="fas fa-circle-play"></i> How It Works</a>
            </div>
        </div>

        {{-- Illustrative dashboard + phone (decorative; built in HTML/CSS, no real data) --}}
        <div class="ap-devices" aria-hidden="true">
            <div class="ap-laptop">
                <div class="ap-laptop-screen">
                    <div class="ap-dash">
                        <div class="ap-dash-side">
                            <span class="ap-dash-logo"></span>
                            @foreach(['fa-gauge', 'fa-plane', 'fa-hotel', 'fa-umbrella-beach', 'fa-kaaba', 'fa-passport', 'fa-wallet'] as $i => $icon)
                                <span class="ap-dash-nav {{ $i === 0 ? 'is-active' : '' }}"><i class="fas {{ $icon }}"></i><b></b></span>
                            @endforeach
                        </div>
                        <div class="ap-dash-main">
                            <div class="ap-dash-top"><b>Dashboard</b><span></span></div>
                            <div class="ap-dash-cards">
                                @foreach(['fa-ticket', 'fa-sack-dollar', 'fa-users', 'fa-chart-line'] as $icon)
                                    <div class="ap-dash-card"><i class="fas {{ $icon }}"></i><b></b><span></span></div>
                                @endforeach
                            </div>
                            <div class="ap-dash-row">
                                <div class="ap-dash-chart">
                                    <span class="ap-dash-label"></span>
                                    <div class="ap-bars">
                                        @for($b = 0; $b < 12; $b++)<i></i>@endfor
                                    </div>
                                </div>
                                <div class="ap-dash-list">
                                    <span class="ap-dash-label"></span>
                                    @for($r = 0; $r < 5; $r++)
                                        <div class="ap-dash-item"><i></i><b></b><span></span></div>
                                    @endfor
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="ap-laptop-base"></div>
            </div>
            <div class="ap-phone">
                <div class="ap-phone-screen">
                    <div class="ap-phone-head"><span></span><b>{{ $apBusiness }}</b></div>
                    <div class="ap-phone-search"><i class="fas fa-magnifying-glass"></i><span></span></div>
                    <div class="ap-phone-grid">
                        @foreach([['fa-plane', 'Flights'], ['fa-hotel', 'Hotels'], ['fa-umbrella-beach', 'Tours'], ['fa-kaaba', 'Umrah'], ['fa-passport', 'Visa'], ['fa-ticket', 'Bookings']] as [$icon, $label])
                            <span><i class="fas {{ $icon }}"></i>{{ $label }}</span>
                        @endforeach
                    </div>
                    <div class="ap-phone-card"><i></i><div><b></b><span></span></div></div>
                    <div class="ap-phone-card"><i></i><div><b></b><span></span></div></div>
                    <div class="ap-phone-btn">Book Now</div>
                </div>
            </div>
        </div>
    </div>

    <div class="ap-inner">
        <div class="ap-stats">
            @foreach([['fa-user-tie', '100+', 'Active Agents'], ['fa-ticket', '50K+', 'Bookings'], ['fa-earth-americas', 'Worldwide', 'Presence'], ['fa-headset', '24/7', 'Support']] as [$icon, $value, $label])
                <div class="ap-stat">
                    <span class="ap-ico"><i class="fas {{ $icon }}"></i></span>
                    <div><b>{{ $value }}</b><small>{{ $label }}</small></div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ══ SERVICES ══ --}}
<section class="ap-sec">
    <div class="ap-inner">
        <div class="ap-head is-center">
            <span class="ap-eyebrow">One Account, Every Travel Product</span>
            <h2 class="ap-title">Sell Across <span>All Travel Services</span></h2>
            <p class="ap-sub">From flights to hotels, tours, Umrah and visa — everything in one dashboard.</p>
        </div>
        <div class="ap-services">
            @foreach([
                ['fa-plane', 'Flights', 'Domestic & international flight bookings.'],
                ['fa-hotel', 'Hotels', 'Thousands of hotels worldwide, best rates.'],
                ['fa-umbrella-beach', 'Tours', 'Curated tour packages for every budget.'],
                ['fa-kaaba', 'Umrah', 'Umrah packages with flights & stay included.'],
                ['fa-passport', 'Visa', 'Visa processing & documentation support.'],
            ] as [$icon, $title, $desc])
                <div class="ap-card is-center">
                    <span class="ap-ico is-lg"><i class="fas {{ $icon }}"></i></span>
                    <h3>{{ $title }}</h3>
                    <p>{{ $desc }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ══ WHY CHOOSE US ══ --}}
<section class="ap-sec is-tinted">
    <div class="ap-inner ap-why">
        <div class="ap-head">
            <span class="ap-eyebrow">Why Choose Us</span>
            <h2 class="ap-title">Everything You Need to Run Your <span>Travel Business</span></h2>
            <p class="ap-sub">All the tools top travel agencies use — available from day one, with zero upfront costs.</p>
            <a href="#register-form" class="ap-btn"><i class="fas fa-user-plus"></i> Join Free</a>
        </div>
        <div class="ap-benefits">
            @foreach([
                ['fa-earth-americas', 'All-in-One Platform', 'Flights, hotels, tours, Umrah and visa in one login and one dashboard.'],
                ['fa-chart-line', 'Earn Commission', 'Set your markup and earn on every booking across the modules you sell.'],
                ['fa-gauge-high', 'Ready-to-Use System', 'Once your account is approved, your agent dashboard is ready to use — no technical setup.'],
                ['fa-lock', 'Secure Payments', 'Pay for bookings from your agent wallet or the payment gateways enabled on the platform.'],
                ['fa-file-invoice', 'Instant Invoices', 'Every confirmed booking generates an invoice you can share with your client.'],
                ['fa-headset', 'Dedicated Support', 'Our support team is available to help with bookings and your account.'],
            ] as [$icon, $title, $desc])
                <div class="ap-card is-row">
                    <span class="ap-ico"><i class="fas {{ $icon }}"></i></span>
                    <div>
                        <h3>{{ $title }}</h3>
                        <p>{{ $desc }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ══ HOW IT WORKS ══ --}}
<section class="ap-sec" id="how-it-works">
    <div class="ap-inner">
        <div class="ap-head is-center">
            <span class="ap-eyebrow">Simple Process</span>
            <h2 class="ap-title">Up &amp; Running in <span>3 Steps</span></h2>
            <p class="ap-sub">From registration to your first booking.</p>
        </div>
        <div class="ap-steps">
            @foreach([
                ['fa-user-pen', 'Register Your Account', 'Fill in the form below with your name, email, phone and company — it takes a couple of minutes.'],
                ['fa-user-check', 'Get Dashboard Access', 'Our team reviews and approves your account, then your agent dashboard becomes active.'],
                ['fa-sack-dollar', 'Sell & Earn', 'Search the available inventory, book for your clients and earn your markup on each booking.'],
            ] as $n => [$icon, $title, $desc])
                <div class="ap-card ap-step">
                    <div class="ap-step-top">
                        <span class="ap-step-num">{{ $n + 1 }}</span>
                        <span class="ap-ico"><i class="fas {{ $icon }}"></i></span>
                    </div>
                    <h3>{{ $title }}</h3>
                    <p>{{ $desc }}</p>
                    @if($n < 2)<i class="fas fa-arrow-right ap-step-arrow" aria-hidden="true"></i>@endif
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ══ REGISTER ══ --}}
<section class="ap-sec" id="register-form">
    <div class="ap-inner">
        <div class="ap-register">
            <div class="ap-register-promo">
                <span class="ap-eyebrow is-light">Get Started Today</span>
                <h2>Create Your Agent Account</h2>
                <p>Start building your travel business with {{ $apBusiness }}.</p>
                <ul>
                    <li><i class="fas fa-circle-check"></i> Access flights, hotels, tours, Umrah &amp; visa</li>
                    <li><i class="fas fa-circle-check"></i> Earn your markup on every booking</li>
                    <li><i class="fas fa-circle-check"></i> No upfront costs to register</li>
                    <li><i class="fas fa-circle-check"></i> Start selling once your account is approved</li>
                </ul>
            </div>

            <div class="ap-register-form">
                <h3>Agent Registration</h3>
                <p class="ap-register-sub">Join as a travel agent and start your business today.</p>

                @if(session('success'))
                    <div class="f-alert ok"><i class="fas fa-check-circle me-1"></i>{{ session('success') }}</div>
                @endif
                @if($errors->any())
                    <div class="f-alert err">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        <ul class="ap-errors">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('agent.register.submit') }}" id="inlineForm">
                    @csrf
                    <div class="fb-grid">
                        <label class="fb-field">
                            <span>First Name *</span>
                            <input type="text" name="first_name" class="fb-input @error('first_name') is-invalid @enderror" value="{{ old('first_name') }}" placeholder="John" required>
                            @error('first_name')<em class="f-err">{{ $message }}</em>@enderror
                        </label>
                        <label class="fb-field">
                            <span>Last Name *</span>
                            <input type="text" name="last_name" class="fb-input @error('last_name') is-invalid @enderror" value="{{ old('last_name') }}" placeholder="Doe" required>
                            @error('last_name')<em class="f-err">{{ $message }}</em>@enderror
                        </label>
                        <label class="fb-field is-wide">
                            <span>Email Address *</span>
                            <input type="email" name="email" class="fb-input @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="your@email.com" required>
                            @error('email')<em class="f-err">{{ $message }}</em>@enderror
                        </label>
                        <label class="fb-field">
                            <span>Phone Number *</span>
                            <input type="text" name="phone" class="fb-input @error('phone') is-invalid @enderror" value="{{ old('phone') }}" placeholder="+92 300 0000000" required>
                            @error('phone')<em class="f-err">{{ $message }}</em>@enderror
                        </label>
                        <label class="fb-field">
                            <span>Company Name</span>
                            <input type="text" name="company_name" class="fb-input" value="{{ old('company_name') }}" placeholder="ABC Travels">
                        </label>
                        <div class="fb-field">
                            <span>Password *</span>
                            <div class="f-pw">
                                <input type="password" name="password" id="ip1" class="fb-input @error('password') is-invalid @enderror" placeholder="Min. 8 characters" required>
                                <button type="button" class="pw-eye" onclick="pwT('ip1','ie1')" aria-label="Show password"><i class="fas fa-eye" id="ie1"></i></button>
                            </div>
                            @error('password')<em class="f-err">{{ $message }}</em>@enderror
                        </div>
                        <div class="fb-field">
                            <span>Confirm Password *</span>
                            <div class="f-pw">
                                <input type="password" name="password_confirmation" id="ip2" class="fb-input" placeholder="Repeat password" required>
                                <button type="button" class="pw-eye" onclick="pwT('ip2','ie2')" aria-label="Show password"><i class="fas fa-eye" id="ie2"></i></button>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn-sub ap-submit">
                        <span class="lbl"><i class="fas fa-user-check"></i> Create My Agent Account</span>
                        <span class="spin"><span class="ap-spinner"></span> Creating account…</span>
                    </button>
                    <p class="f-note">By registering you agree to become an authorised {{ $apBusiness }} agent.</p>
                </form>
                <div class="f-hint">Already have an account? <a href="{{ route('login') }}">Sign In</a></div>
            </div>
        </div>
    </div>
</section>
</div>

<style>
    @keyframes spin { to { transform: rotate(360deg); } }
</style>

<script>
    function pwT(fid, iid) {
        const f = document.getElementById(fid), i = document.getElementById(iid);
        f.type = f.type === 'password' ? 'text' : 'password';
        i.className = f.type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
    }

    document.getElementById('inlineForm')?.addEventListener('submit', () => document.body.classList.add('ag-loading'));

    @if($errors->any() || session('success'))
        // Show the result of a submission (errors or success) next to the form
        document.addEventListener('DOMContentLoaded', () => document.getElementById('register-form').scrollIntoView());
    @endif
</script>

@include('common.footer')
