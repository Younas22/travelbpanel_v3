{{-- Alternative login page: flights / agent focused, full-screen photo background.
     The classic page (admin/auth/login.blade.php) is untouched; switch with
     LOGIN_PAGE=flight in .env, or preview any time at /login-flight. --}}
@include('common.header', ['hideNavbar' => true, 'pageClass' => 'view-login-flight'])

@php
    $lfBizName = getSetting('business_name', 'main', 'TravelBookingPanel');
    $lfModel   = businessModel();
    $lfShowUserSignup  = in_array($lfModel, ['both', 'b2c']);
    $lfShowAgentSignup = in_array($lfModel, ['both', 'b2b']);
@endphp

<div class="lf-page">
    <div class="lf-shade"></div>

    <div class="lf-inner">
        {{-- Left: what the agent gets --}}
        <section class="lf-intro">
            <a href="{{ url('/') }}" class="lf-logo">
                <img src="{{ getSettingImage('business_logo_white', 'branding') }}" alt="{{ $lfBizName }}">
            </a>

            <div class="lf-intro-body">
                <span class="lf-eyebrow"><i class="fas fa-plane-departure"></i> Agent Portal · Flights</span>
                <h1>Welcome back, agent</h1>
                <p class="lf-lead">Search live fares, hold seats and issue e-tickets for your clients — all from one flight booking dashboard.</p>
                <ul class="lf-points">
                    <li><i class="fas fa-magnifying-glass-dollar"></i> Live fares from multiple airlines</li>
                    <li><i class="fas fa-ticket"></i> PNR, ticketing &amp; cancellations in one place</li>
                    <li><i class="fas fa-percent"></i> Your markup, your commission on every ticket</li>
                </ul>
            </div>

            <p class="lf-foot">&copy; {{ date('Y') }} {{ $lfBizName }}. All rights reserved.</p>
        </section>

        {{-- Right: sign-in card --}}
        <section class="lf-card">
            <div class="lf-card-head">
                <span class="lf-card-ico"><i class="fas fa-plane"></i></span>
                <h2>Agent sign in</h2>
                <p>Access your flight bookings and wallet</p>
            </div>

            @if(session('success'))
                <div class="lf-alert ok"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="lf-alert err">
                    <i class="fas fa-exclamation-triangle"></i>
                    @foreach($errors->all() as $e) {{ $e }}<br> @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('admin.signin.post') }}" id="lfForm">
                @csrf

                <div class="lf-group">
                    <label class="lf-label" for="lfEmail">Email address</label>
                    <div class="lf-field">
                        <i class="fas fa-envelope"></i>
                        <input type="email" name="email" id="lfEmail"
                               class="lf-input @error('email') is-invalid @enderror"
                               value="{{ old('email') }}" placeholder="agent@youragency.com" required autofocus>
                    </div>
                    @error('email')<span class="lf-err">{{ $message }}</span>@enderror
                </div>

                <div class="lf-group">
                    <div class="lf-label-row">
                        <label class="lf-label" for="lfPw">Password</label>
                        <a href="{{ route('password.request') }}" class="lf-link">Forgot password?</a>
                    </div>
                    <div class="lf-field">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" id="lfPw"
                               class="lf-input @error('password') is-invalid @enderror"
                               placeholder="Enter your password" required>
                        <button type="button" class="lf-eye" id="lfEye" aria-label="Show password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    @error('password')<span class="lf-err">{{ $message }}</span>@enderror
                </div>

                <button type="submit" class="lf-btn" id="lfBtn">
                    <span class="lbl"><i class="fas fa-right-to-bracket"></i> Sign In</span>
                    <span class="spin"><span class="lf-spinner"></span> Signing in…</span>
                </button>
            </form>

            @if($lfShowUserSignup || $lfShowAgentSignup)
                <div class="lf-divider"><span>New here?</span></div>
                <div class="lf-signup">
                    @if($lfShowAgentSignup)
                        <a href="{{ agentSignupUrl() }}" class="lf-signup-agent"><i class="fas fa-user-tie"></i> Become an agent</a>
                    @endif
                    @if($lfShowUserSignup)
                        <a href="{{ route('user.register') }}" class="lf-signup-user">Customer signup</a>
                    @endif
                </div>
            @endif
        </section>
    </div>
</div>

<script>
    (function () {
        var pw = document.getElementById('lfPw'), eye = document.getElementById('lfEye');
        eye.addEventListener('click', function () {
            pw.type = pw.type === 'password' ? 'text' : 'password';
            eye.firstElementChild.className = pw.type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
        });
        document.getElementById('lfForm').addEventListener('submit', function () {
            document.body.classList.add('submitting');
        });
    })();
</script>

@include('common.footer', ['hideFooter' => true])
