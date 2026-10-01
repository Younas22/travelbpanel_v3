@include('common.header')

<style>
    :root {
        --primary:   #0077BE;
        --primary-m: #005a8f;
        --accent:    #0077BE;
        --accent-l:  #e8f4fd;
        --text:      #0f172a;
        --muted:     #64748b;
        --border:    #e2e8f0;
        --surface:   #f8fafc;
    }

    /* ─── HERO ─── */
    .ag-hero {
        background: linear-gradient(160deg, #005a8f 0%, #0077BE 55%, #0093e0 100%);
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
    .ag-hero h1 span { color: #cceeff; }
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
    .btn-ag-primary:hover { background: #e8f4fd; transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,.2); }

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
    .ben-card:hover { box-shadow: 0 8px 28px rgba(0,53,128,.07); transform: translateY(-3px); border-color: #bfdbfe; }
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
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-m) 100%);
        border-radius: 18px; padding: 44px 36px; text-align: center;
        position: relative; overflow: hidden;
    }
    .cta-band::before {
        content: '';
        position: absolute; inset: 0;
        background: radial-gradient(circle at 75% 50%, rgba(0,119,190,.32) 0%, transparent 60%);
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
    .btn-cta-w:hover { background: #f0f9ff; transform: translateY(-2px); }

    /* ─── FORM ─── */
    .form-wrap {
        max-width: 640px; margin: 0 auto;
        background: #fff; border: 1px solid var(--border);
        border-radius: 18px; box-shadow: 0 12px 48px rgba(0,53,128,.09);
        overflow: hidden;
    }
    .form-head {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-m) 100%);
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
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-m) 100%);
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
    .f-input:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 0 3px rgba(0,119,190,.11); background: #fff; }
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
    .btn-sub:hover { background: #0066a5; transform: translateY(-1px); box-shadow: 0 6px 18px rgba(0,119,190,.35); }
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
</style>


{{-- ══ HERO ══ --}}
<section class="ag-hero">
    <div class="ag-hero-grid"></div>
    <div class="ag-hero-inner">
        <div class="ag-badge">
            <i class="fas fa-shield-alt"></i>&nbsp; Trusted by 100+ Travel Agents
        </div>
        <h1>
            Become a Travel <span>Agent</span> &amp;<br>Start Your Business Today
        </h1>
        <p class="ag-hero-sub">
            Sell flights, hotels, tours, Umrah packages &amp; visa services —
            all from one dashboard, earning commission on every booking. No upfront costs.
        </p>
        <div class="ag-hero-btns">
            <button class="btn-ag-primary" onclick="openModal()">
                <i class="fas fa-rocket"></i> Become a Travel Agent
            </button>
            <a href="#how-it-works" class="btn-ag-ghost">
                <i class="fas fa-play-circle"></i> How It Works
            </a>
        </div>
        <div class="ag-stats">
            <div class="ag-stat"><div class="ag-stat-n">100+</div><div class="ag-stat-l">Active Agents</div></div>
            <div class="ag-stat"><div class="ag-stat-n">50K+</div><div class="ag-stat-l">Bookings</div></div>
            <div class="ag-stat"><div class="ag-stat-n">Worldwide</div><div class="ag-stat-l">Presence</div></div>
            <div class="ag-stat"><div class="ag-stat-n">24/7</div><div class="ag-stat-l">Support</div></div>
        </div>
    </div>
</section>


{{-- ══ TRUST STRIP ══ --}}
<div class="ag-trust">
    <div class="ag-trust-inner">
        <div class="ag-ti"><i class="fas fa-shield-alt"></i> Secure Platform</div>
        <div class="ag-ti"><i class="fas fa-bolt"></i> Instant Access</div>
        <div class="ag-ti"><i class="fas fa-globe"></i> Global Inventory</div>
        <div class="ag-ti"><i class="fas fa-dollar-sign"></i> Earn Commission</div>
        <div class="ag-ti"><i class="fas fa-headset"></i> Dedicated Support</div>
        <div class="ag-ti"><i class="fas fa-mobile-alt"></i> Mobile Dashboard</div>
    </div>
</div>


{{-- ══ MODULES ══ --}}
<section class="ag-sec-sm" style="background:#fff;">
    <div class="ag-inner">
        <div style="text-align:center; margin-bottom:28px;">
            <div class="ag-eyebrow" style="justify-content:center;">What You Can Sell</div>
            <h2 class="ag-h">One Account, <span>Every Travel Product</span></h2>
            <p class="ag-p" style="margin:0 auto; text-align:center;">Sell across all our modules from a single dashboard — no separate signups.</p>
        </div>
        <div class="ag-row">
            @php
                $mod_cards = [
                    ['fas fa-plane',    'Flights', 'Domestic & international flight bookings.'],
                    ['fas fa-hotel',    'Hotels',  'Thousands of hotels worldwide, best rates.'],
                    ['fas fa-suitcase', 'Tours',   'Curated tour packages for every budget.'],
                    ['fas fa-mosque',   'Umrah',   'Umrah packages with flights & stay included.'],
                    ['fas fa-passport', 'Visa',    'Visa processing & documentation support.'],
                ];
            @endphp
            @foreach($mod_cards as [$ico, $title, $desc])
            <div class="ag-col" style="flex:1; min-width:170px;">
                <div class="ben-card" style="text-align:center;">
                    <div class="ben-ico" style="margin:0 auto 12px;"><i class="{{ $ico }}"></i></div>
                    <h6>{{ $title }}</h6>
                    <p>{{ $desc }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>


{{-- ══ BENEFITS ══ --}}
<section class="ag-sec" style="background: var(--surface);">
    <div class="ag-inner">
        <div class="ag-row" style="align-items:center;">
            <div class="ag-col ag-col-4" style="min-width:260px; flex:1;">
                <div class="ag-eyebrow">Why Choose Us</div>
                <h2 class="ag-h">Everything to Run Your <span>Travel Business</span></h2>
                <p class="ag-p">All the tools top travel agencies use — available from day one, with zero upfront costs.</p>
                <button class="btn-ag-primary mt-4" onclick="openModal()" style="margin-top:20px;">
                    <i class="fas fa-user-plus"></i> Join Free
                </button>
            </div>
            <div class="ag-col" style="flex:2; min-width:280px;">
                <div class="ag-row">
                    @php
                        $benefits = [
                            ['fas fa-globe-americas',   'All-in-One Platform',   'Flights, hotels, tours, Umrah & visa — one login, one dashboard.'],
                            ['fas fa-chart-line',       'Earn Commission',       'Set your markup and earn on every booking across every module.'],
                            ['fas fa-tachometer-alt',   'Ready-to-Use System',   'Log in and start selling immediately — no technical setup needed.'],
                            ['fas fa-lock',             'Secure Payments',       'Enterprise-grade security on every transaction, always.'],
                            ['fas fa-file-invoice',     'Instant Invoices',      'Auto-generate professional invoices and e-tickets for clients.'],
                            ['fas fa-headset',          'Dedicated Support',     '24/7 agent support whenever you need help closing a sale.'],
                        ];
                    @endphp
                    @foreach($benefits as [$ico, $title, $desc])
                    <div class="ag-col ag-col-6">
                        <div class="ben-card">
                            <div class="ben-ico"><i class="{{ $ico }}"></i></div>
                            <h6>{{ $title }}</h6>
                            <p>{{ $desc }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>


{{-- ══ HOW IT WORKS ══ --}}
<section class="ag-sec" id="how-it-works">
    <div class="ag-inner">
        <div style="text-align:center; margin-bottom:28px;">
            <div class="ag-eyebrow" style="justify-content:center;">Simple Process</div>
            <h2 class="ag-h">Up &amp; Running in <span>3 Steps</span></h2>
            <p class="ag-p" style="margin:0 auto; text-align:center;">From registration to your first booking — in under 24 hours.</p>
        </div>
        <div class="ag-row">
            <div class="ag-col ag-col-4">
                <div class="step-card">
                    <div class="step-num">1</div>
                    <h6>Register Your Account</h6>
                    <p>Fill the quick form — name, email, phone. Takes under 2 minutes.</p>
                    <div class="step-arr"><i class="fas fa-arrow-right"></i></div>
                </div>
            </div>
            <div class="ag-col ag-col-4">
                <div class="step-card">
                    <div class="step-num">2</div>
                    <h6>Get Dashboard Access</h6>
                    <p>Admin review &amp; approval. Your full agent dashboard becomes live.</p>
                    <div class="step-arr"><i class="fas fa-arrow-right"></i></div>
                </div>
            </div>
            <div class="ag-col ag-col-4">
                <div class="step-card">
                    <div class="step-num">3</div>
                    <h6>Sell &amp; Earn</h6>
                    <p>Search inventory, book for clients, earn commission — repeat.</p>
                </div>
            </div>
        </div>
        <div style="text-align:center; margin-top:24px;">
            <button class="btn-ag-primary" onclick="openModal()">
                <i class="fas fa-user-plus"></i> Create Free Account
            </button>
        </div>
    </div>
</section>


{{-- ══ CTA BAND ══ --}}
<section class="ag-sec-sm" style="background:var(--surface);">
    <div class="ag-inner">
        <div class="cta-band">
            <h3>Ready to Launch Your Travel Business?</h3>
            <p>No monthly fees. No contracts. Start selling in minutes.</p>
            <button class="btn-cta-w" onclick="openModal()">
                <i class="fas fa-rocket"></i> Become a Travel Agent
            </button>
        </div>
    </div>
</section>


{{-- ══ INLINE REGISTER FORM ══ --}}
<section class="ag-sec" id="register-form">
    <div class="ag-inner">
        <div style="text-align:center; margin-bottom:24px;">
            <div class="ag-eyebrow" style="justify-content:center;">Get Started</div>
            <h2 class="ag-h">Create Your <span>Agent Account</span></h2>
        </div>
        <div class="form-wrap">
            <div class="form-head">
                <h4><i class="fas fa-user-plus me-2"></i>Agent Registration</h4>
                <p>Free forever &nbsp;·&nbsp; Approved within 24 hrs &nbsp;·&nbsp; No credit card needed</p>
            </div>
            <div class="form-body">
                @if(session('success'))
                    <div class="f-alert ok"><i class="fas fa-check-circle me-1"></i>{{ session('success') }}</div>
                @endif
                @if($errors->any())
                    <div class="f-alert err">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        <ul style="margin:4px 0 0 16px; padding:0;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                @endif
                <form method="POST" action="{{ route('agent.register.submit') }}" id="inlineForm">
                    @csrf
                    <div class="ag-row">
                        <div class="ag-col ag-col-6">
                            <label class="f-label">First Name <span class="req">*</span></label>
                            <input type="text" name="first_name" class="f-input @error('first_name') is-invalid @enderror" value="{{ old('first_name') }}" placeholder="John" required>
                            @error('first_name')<span class="f-err">{{ $message }}</span>@enderror
                        </div>
                        <div class="ag-col ag-col-6">
                            <label class="f-label">Last Name <span class="req">*</span></label>
                            <input type="text" name="last_name" class="f-input @error('last_name') is-invalid @enderror" value="{{ old('last_name') }}" placeholder="Doe" required>
                            @error('last_name')<span class="f-err">{{ $message }}</span>@enderror
                        </div>
                        <div class="ag-col ag-col-12">
                            <label class="f-label">Email Address <span class="req">*</span></label>
                            <input type="email" name="email" class="f-input @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="your@email.com" required>
                            @error('email')<span class="f-err">{{ $message }}</span>@enderror
                        </div>
                        <div class="ag-col ag-col-6">
                            <label class="f-label">Phone Number <span class="req">*</span></label>
                            <input type="text" name="phone" class="f-input @error('phone') is-invalid @enderror" value="{{ old('phone') }}" placeholder="+92 300 0000000" required>
                            @error('phone')<span class="f-err">{{ $message }}</span>@enderror
                        </div>
                        <div class="ag-col ag-col-6">
                            <label class="f-label">Company Name</label>
                            <input type="text" name="company_name" class="f-input" value="{{ old('company_name') }}" placeholder="ABC Travels">
                        </div>
                        <div class="ag-col ag-col-6">
                            <label class="f-label">Password <span class="req">*</span></label>
                            <div class="f-pw">
                                <input type="password" name="password" id="ip1" class="f-input @error('password') is-invalid @enderror" placeholder="Min. 8 characters" required>
                                <button type="button" class="pw-eye" onclick="pwT('ip1','ie1')"><i class="fas fa-eye" id="ie1"></i></button>
                            </div>
                            @error('password')<span class="f-err">{{ $message }}</span>@enderror
                        </div>
                        <div class="ag-col ag-col-6">
                            <label class="f-label">Confirm Password <span class="req">*</span></label>
                            <div class="f-pw">
                                <input type="password" name="password_confirmation" id="ip2" class="f-input" placeholder="Repeat password" required>
                                <button type="button" class="pw-eye" onclick="pwT('ip2','ie2')"><i class="fas fa-eye" id="ie2"></i></button>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn-sub">
                        <span class="lbl"><i class="fas fa-user-check"></i> Create My Agent Account</span>
                        <span class="spin"><div style="width:16px;height:16px;border:2px solid rgba(255,255,255,.4);border-top-color:#fff;border-radius:50%;animation:spin .7s linear infinite;"></div> Creating account…</span>
                    </button>
                    <p class="f-note">By registering you agree to become an authorised {{ getSetting('business_name','main','TravelBookingPanel') }} agent.</p>
                </form>
                <div class="f-hint">Already have an account? <a href="{{ route('login') }}">Sign In</a></div>
            </div>
        </div>
    </div>
</section>


{{-- ══ MODAL ══ --}}
<div class="m-ov" id="regModal" onclick="ovClick(event)">
    <div class="m-box">
        <div class="m-head">
            <h5><i class="fas fa-user-plus" style="margin-right:6px;"></i>Create Your Agent Account</h5>
            <p>Free forever &nbsp;·&nbsp; Approved within 24 hrs &nbsp;·&nbsp; No credit card needed</p>
            <button class="m-close" onclick="closeModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="m-body">
            @if(session('success'))
                <div class="f-alert ok"><i class="fas fa-check-circle me-1"></i>{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="f-alert err">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    <ul style="margin:4px 0 0 16px; padding:0;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif
            <form method="POST" action="{{ route('agent.register.submit') }}" id="modalForm">
                @csrf
                <div class="ag-row">
                    <div class="ag-col ag-col-6">
                        <label class="f-label">First Name <span class="req">*</span></label>
                        <input type="text" name="first_name" class="f-input @error('first_name') is-invalid @enderror" value="{{ old('first_name') }}" placeholder="John" required autofocus>
                        @error('first_name')<span class="f-err">{{ $message }}</span>@enderror
                    </div>
                    <div class="ag-col ag-col-6">
                        <label class="f-label">Last Name <span class="req">*</span></label>
                        <input type="text" name="last_name" class="f-input @error('last_name') is-invalid @enderror" value="{{ old('last_name') }}" placeholder="Doe" required>
                        @error('last_name')<span class="f-err">{{ $message }}</span>@enderror
                    </div>
                    <div class="ag-col ag-col-12">
                        <label class="f-label">Email Address <span class="req">*</span></label>
                        <input type="email" name="email" class="f-input @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="your@email.com" required>
                        @error('email')<span class="f-err">{{ $message }}</span>@enderror
                    </div>
                    <div class="ag-col ag-col-6">
                        <label class="f-label">Phone Number <span class="req">*</span></label>
                        <input type="text" name="phone" class="f-input @error('phone') is-invalid @enderror" value="{{ old('phone') }}" placeholder="+92 300 0000000" required>
                        @error('phone')<span class="f-err">{{ $message }}</span>@enderror
                    </div>
                    <div class="ag-col ag-col-6">
                        <label class="f-label">Company Name</label>
                        <input type="text" name="company_name" class="f-input" value="{{ old('company_name') }}" placeholder="ABC Travels">
                    </div>
                    <div class="ag-col ag-col-6">
                        <label class="f-label">Password <span class="req">*</span></label>
                        <div class="f-pw">
                            <input type="password" name="password" id="mp1" class="f-input @error('password') is-invalid @enderror" placeholder="Min. 8 characters" required>
                            <button type="button" class="pw-eye" onclick="pwT('mp1','me1')"><i class="fas fa-eye" id="me1"></i></button>
                        </div>
                        @error('password')<span class="f-err">{{ $message }}</span>@enderror
                    </div>
                    <div class="ag-col ag-col-6">
                        <label class="f-label">Confirm Password <span class="req">*</span></label>
                        <div class="f-pw">
                            <input type="password" name="password_confirmation" id="mp2" class="f-input" placeholder="Repeat password" required>
                            <button type="button" class="pw-eye" onclick="pwT('mp2','me2')"><i class="fas fa-eye" id="me2"></i></button>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn-sub">
                    <span class="lbl"><i class="fas fa-user-check"></i> Create My Agent Account</span>
                    <span class="spin"><div style="width:16px;height:16px;border:2px solid rgba(255,255,255,.4);border-top-color:#fff;border-radius:50%;animation:spin .7s linear infinite;"></div> Creating account…</span>
                </button>
                <p class="f-note">By registering you agree to become an authorised {{ getSetting('business_name','main','TravelBookingPanel') }} agent.</p>
            </form>
            <div class="f-hint">Already have an account? <a href="{{ route('login') }}">Sign In</a></div>
        </div>
    </div>
</div>

<style>
    @keyframes spin { to { transform: rotate(360deg); } }
</style>

<script>
    function openModal() {
        document.getElementById('regModal').classList.add('show');
        document.body.style.overflow = 'hidden';
        setTimeout(() => document.querySelector('#modalForm input[name="first_name"]')?.focus(), 300);
    }
    function closeModal() {
        document.getElementById('regModal').classList.remove('show');
        document.body.style.overflow = '';
    }
    function ovClick(e) { if (e.target === document.getElementById('regModal')) closeModal(); }
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });

    function pwT(fid, iid) {
        const f = document.getElementById(fid), i = document.getElementById(iid);
        f.type = f.type === 'password' ? 'text' : 'password';
        i.className = f.type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
    }

    ['inlineForm','modalForm'].forEach(id => {
        document.getElementById(id)?.addEventListener('submit', () => document.body.classList.add('ag-loading'));
    });

    @if($errors->any())
        document.addEventListener('DOMContentLoaded', openModal);
    @endif
</script>

@include('common.footer')
