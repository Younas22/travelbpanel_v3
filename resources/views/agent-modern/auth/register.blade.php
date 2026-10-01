@include('common.header')

@php
    $__regTheme = app(\App\Services\ThemeService::class)->active(null);
    $__regPrimary = $__regTheme->primary_color ?: '#0C6DFD';
@endphp
<link href="{{ asset('public/assets/css/agent-modern.css') }}?v={{ @filemtime(public_path('assets/css/agent-modern.css')) ?: 1 }}" rel="stylesheet">
<style>:root{--primary-color:{{ $__regPrimary }};}</style>

{{-- ══ HERO ══ --}}
<section class="reg-hero">
    <div class="reg-hero-inner">
        <div class="reg-badge">
            <i class="fas fa-shield-alt"></i>&nbsp; Trusted by 100+ Travel Agents
        </div>
        <h1>
            Become a Travel <span>Agent</span> &amp;<br>Start Your Business Today
        </h1>
        <p class="reg-hero-sub">
            Sell flights, hotels, tours, Umrah packages &amp; visa services —
            all from one dashboard, earning commission on every booking. No upfront costs.
        </p>
        <div class="reg-hero-btns">
            <button class="btn-reg-white" onclick="openAmModal()">
                <i class="fas fa-rocket"></i> Become a Travel Agent
            </button>
            <a href="#how-it-works" class="btn-reg-ghost">
                <i class="fas fa-play-circle"></i> How It Works
            </a>
        </div>
        <div class="reg-stats">
            <div class="reg-stat"><div class="reg-stat-n">100+</div><div class="reg-stat-l">Active Agents</div></div>
            <div class="reg-stat"><div class="reg-stat-n">50K+</div><div class="reg-stat-l">Bookings</div></div>
            <div class="reg-stat"><div class="reg-stat-n">Worldwide</div><div class="reg-stat-l">Presence</div></div>
            <div class="reg-stat"><div class="reg-stat-n">24/7</div><div class="reg-stat-l">Support</div></div>
        </div>
    </div>
</section>

{{-- ══ MODULES ══ --}}
<section class="reg-sec-sm" style="background: var(--card-bg);">
    <div class="reg-inner">
        <div class="text-center mb-4">
            <div class="reg-eyebrow justify-content-center">What You Can Sell</div>
            <h2 class="reg-h">One Account, <span>Every Travel Product</span></h2>
            <p class="reg-p mx-auto text-center">Sell across all our modules from a single dashboard — no separate signups.</p>
        </div>
        <div class="row g-3">
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
            <div class="col-6 col-md">
                <div class="reg-benefit-card" style="text-align:center;">
                    <div class="reg-benefit-ico" style="margin:0 auto 12px;"><i class="{{ $ico }}"></i></div>
                    <h6>{{ $title }}</h6>
                    <p>{{ $desc }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ══ BENEFITS ══ --}}
<section class="reg-sec" style="background: var(--body-bg);">
    <div class="reg-inner">
        <div class="row align-items-center g-4">
            <div class="col-lg-4">
                <div class="reg-eyebrow">Why Choose Us</div>
                <h2 class="reg-h">Everything to Run Your <span>Travel Business</span></h2>
                <p class="reg-p">All the tools top travel agencies use — available from day one, with zero upfront costs.</p>
                <button class="btn-reg-white" onclick="openAmModal()" style="margin-top:20px; color: var(--primary-color) !important; background: var(--card-bg);">
                    <i class="fas fa-user-plus"></i> Join Free
                </button>
            </div>
            <div class="col-lg-8">
                <div class="row g-3">
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
                    <div class="col-md-6">
                        <div class="reg-benefit-card">
                            <div class="reg-benefit-ico"><i class="{{ $ico }}"></i></div>
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
<section class="reg-sec" id="how-it-works">
    <div class="reg-inner">
        <div class="text-center mb-4">
            <div class="reg-eyebrow justify-content-center">Simple Process</div>
            <h2 class="reg-h">Up &amp; Running in <span>3 Steps</span></h2>
            <p class="reg-p mx-auto text-center">From registration to your first booking — in under 24 hours.</p>
        </div>
        <div class="row g-3">
            <div class="col-md-4">
                <div class="reg-step-card">
                    <div class="reg-step-num">1</div>
                    <h6>Register Your Account</h6>
                    <p>Fill the quick form — name, email, phone. Takes under 2 minutes.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="reg-step-card">
                    <div class="reg-step-num">2</div>
                    <h6>Get Dashboard Access</h6>
                    <p>Admin review &amp; approval. Your full agent dashboard becomes live.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="reg-step-card">
                    <div class="reg-step-num">3</div>
                    <h6>Sell &amp; Earn</h6>
                    <p>Search inventory, book for clients, earn commission — repeat.</p>
                </div>
            </div>
        </div>
        <div class="text-center mt-4">
            <button class="btn-reg-white" onclick="openAmModal()" style="color: var(--primary-color) !important; background: var(--card-bg); box-shadow: var(--am-shadow-md);">
                <i class="fas fa-user-plus"></i> Create Free Account
            </button>
        </div>
    </div>
</section>

{{-- ══ CTA BAND ══ --}}
<section class="reg-sec-sm" style="background: var(--body-bg);">
    <div class="reg-inner">
        <div class="reg-cta-band">
            <h3>Ready to Launch Your Travel Business?</h3>
            <p>No monthly fees. No contracts. Start selling in minutes.</p>
            <button class="btn-reg-white" onclick="openAmModal()">
                <i class="fas fa-rocket"></i> Become a Travel Agent
            </button>
        </div>
    </div>
</section>

{{-- ══ INLINE REGISTER FORM ══ --}}
<section class="reg-sec" id="register-form">
    <div class="reg-inner">
        <div class="text-center mb-4">
            <div class="reg-eyebrow justify-content-center">Get Started</div>
            <h2 class="reg-h">Create Your <span>Agent Account</span></h2>
        </div>
        <div class="reg-form-wrap">
            <div class="reg-form-head">
                <h4><i class="fas fa-user-plus me-2"></i>Agent Registration</h4>
                <p>Free forever &nbsp;·&nbsp; Approved within 24 hrs &nbsp;·&nbsp; No credit card needed</p>
            </div>
            <div class="reg-form-body">
                @if(session('success'))
                    <div class="am-alert am-alert-success"><i class="bi bi-check-circle-fill"></i><span>{{ session('success') }}</span></div>
                @endif
                @if($errors->any())
                    <div class="am-alert am-alert-danger">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <span><ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></span>
                    </div>
                @endif
                <form method="POST" action="{{ route('agent.register.submit') }}" id="inlineForm">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">First Name <span style="color:var(--primary-color);">*</span></label>
                            <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror" value="{{ old('first_name') }}" placeholder="John" required>
                            @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Last Name <span style="color:var(--primary-color);">*</span></label>
                            <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror" value="{{ old('last_name') }}" placeholder="Doe" required>
                            @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Email Address <span style="color:var(--primary-color);">*</span></label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="your@email.com" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone Number <span style="color:var(--primary-color);">*</span></label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}" placeholder="+92 300 0000000" required>
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Company Name</label>
                            <input type="text" name="company_name" class="form-control" value="{{ old('company_name') }}" placeholder="ABC Travels">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Password <span style="color:var(--primary-color);">*</span></label>
                            <div class="auth-input-group">
                                <input type="password" name="password" id="ip1" class="form-control @error('password') is-invalid @enderror" placeholder="Min. 8 characters" required>
                                <button type="button" class="auth-eye" onclick="amPwToggle('ip1','ie1')"><i class="fas fa-eye" id="ie1"></i></button>
                            </div>
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirm Password <span style="color:var(--primary-color);">*</span></label>
                            <div class="auth-input-group">
                                <input type="password" name="password_confirmation" id="ip2" class="form-control" placeholder="Repeat password" required>
                                <button type="button" class="auth-eye" onclick="amPwToggle('ip2','ie2')"><i class="fas fa-eye" id="ie2"></i></button>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="auth-submit mt-4">
                        <i class="fas fa-user-check"></i> Create My Agent Account
                    </button>
                    <p class="form-text text-center mt-2">By registering you agree to become an authorised {{ getSetting('business_name','main','TravelBookingPanel') }} agent.</p>
                </form>
                <div class="auth-footer-link mt-3">Already have an account? <a href="{{ route('login') }}">Sign In</a></div>
            </div>
        </div>
    </div>
</section>

{{-- ══ MODAL ══ --}}
<div class="modal fade" id="amRegModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius: 18px; overflow: hidden; border: none;">
            <div class="reg-form-head" style="position: relative;">
                <h4 style="color:#fff;"><i class="fas fa-user-plus me-2"></i>Create Your Agent Account</h4>
                <p style="color: rgba(255,255,255,.7);">Free forever &nbsp;·&nbsp; Approved within 24 hrs &nbsp;·&nbsp; No credit card needed</p>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" style="position:absolute; top:16px; right:16px;"></button>
            </div>
            <div class="modal-body reg-form-body">
                @if(session('success'))
                    <div class="am-alert am-alert-success"><i class="bi bi-check-circle-fill"></i><span>{{ session('success') }}</span></div>
                @endif
                @if($errors->any())
                    <div class="am-alert am-alert-danger">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <span><ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></span>
                    </div>
                @endif
                <form method="POST" action="{{ route('agent.register.submit') }}" id="modalForm">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">First Name <span style="color:var(--primary-color);">*</span></label>
                            <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror" value="{{ old('first_name') }}" placeholder="John" required>
                            @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Last Name <span style="color:var(--primary-color);">*</span></label>
                            <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror" value="{{ old('last_name') }}" placeholder="Doe" required>
                            @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Email Address <span style="color:var(--primary-color);">*</span></label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="your@email.com" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone Number <span style="color:var(--primary-color);">*</span></label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}" placeholder="+92 300 0000000" required>
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Company Name</label>
                            <input type="text" name="company_name" class="form-control" value="{{ old('company_name') }}" placeholder="ABC Travels">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Password <span style="color:var(--primary-color);">*</span></label>
                            <div class="auth-input-group">
                                <input type="password" name="password" id="mp1" class="form-control @error('password') is-invalid @enderror" placeholder="Min. 8 characters" required>
                                <button type="button" class="auth-eye" onclick="amPwToggle('mp1','me1')"><i class="fas fa-eye" id="me1"></i></button>
                            </div>
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirm Password <span style="color:var(--primary-color);">*</span></label>
                            <div class="auth-input-group">
                                <input type="password" name="password_confirmation" id="mp2" class="form-control" placeholder="Repeat password" required>
                                <button type="button" class="auth-eye" onclick="amPwToggle('mp2','me2')"><i class="fas fa-eye" id="me2"></i></button>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="auth-submit mt-4">
                        <i class="fas fa-user-check"></i> Create My Agent Account
                    </button>
                    <p class="form-text text-center mt-2">By registering you agree to become an authorised {{ getSetting('business_name','main','TravelBookingPanel') }} agent.</p>
                </form>
                <div class="auth-footer-link mt-3">Already have an account? <a href="{{ route('login') }}">Sign In</a></div>
            </div>
        </div>
    </div>
</div>

<script>
    function openAmModal() {
        var modalEl = document.getElementById('amRegModal');
        var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
        setTimeout(function () {
            var el = modalEl.querySelector('input[name="first_name"]');
            if (el) el.focus();
        }, 300);
    }

    function amPwToggle(fid, iid) {
        var f = document.getElementById(fid), i = document.getElementById(iid);
        f.type = f.type === 'password' ? 'text' : 'password';
        i.className = f.type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
    }

    @if($errors->any())
        document.addEventListener('DOMContentLoaded', openAmModal);
    @endif
</script>

@include('common.footer')
