@php
    // This page is rendered before authentication, so it can't read a
    // per-user theme — it always follows the global admin theme, same
    // rule the classic admin.auth.login page effectively follows today.
    $__loginTheme = app(\App\Services\ThemeService::class)->active(null);
    $__loginPrimary = $__loginTheme->primary_color ?: '#0C6DFD';
@endphp
@include('common.header')

<style>
    :root {
        --mlogin-primary: {{ $__loginPrimary }};
        --mlogin-primary-dark: color-mix(in srgb, {{ $__loginPrimary }} 75%, black);
        --mlogin-ink: #0b0f19;
        --mlogin-muted: #6b7280;
        --mlogin-border: #e5e7eb;
    }

    .mlogin-shell {
        min-height: 88vh;
        display: grid;
        grid-template-columns: 1.1fr 1fr;
        background: #fff;
    }

    .mlogin-aside {
        background: linear-gradient(160deg, var(--mlogin-ink) 0%, color-mix(in srgb, var(--mlogin-primary) 35%, var(--mlogin-ink)) 100%);
        color: #fff;
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: 64px;
        position: relative;
        overflow: hidden;
    }
    .mlogin-aside::before {
        content: '';
        position: absolute; inset: -20% -20% auto auto; width: 480px; height: 480px;
        background: radial-gradient(circle, color-mix(in srgb, var(--mlogin-primary) 55%, transparent) 0%, transparent 70%);
    }
    .mlogin-aside-brand { display: flex; align-items: center; gap: 12px; margin-bottom: 48px; position: relative; z-index: 1; }
    .mlogin-aside-logo {
        width: 42px; height: 42px; border-radius: 12px;
        background: color-mix(in srgb, var(--mlogin-primary) 30%, transparent);
        display: flex; align-items: center; justify-content: center; font-size: 19px;
        border: 1px solid rgba(255,255,255,.15);
    }
    .mlogin-aside-name { font-weight: 700; font-size: 1.0625rem; letter-spacing: -0.01em; }
    .mlogin-aside h1 { font-size: 2.125rem; font-weight: 800; letter-spacing: -0.03em; line-height: 1.2; margin: 0 0 16px; position: relative; z-index: 1; }
    .mlogin-aside p { color: rgba(255,255,255,.65); font-size: 0.9375rem; line-height: 1.7; max-width: 400px; position: relative; z-index: 1; }
    .mlogin-aside-points { margin-top: 40px; display: flex; flex-direction: column; gap: 18px; position: relative; z-index: 1; }
    .mlogin-aside-point { display: flex; align-items: center; gap: 12px; font-size: 0.875rem; color: rgba(255,255,255,.85); }
    .mlogin-aside-point i {
        width: 30px; height: 30px; border-radius: 9px; flex-shrink: 0;
        background: rgba(255,255,255,.1); display: flex; align-items: center; justify-content: center; font-size: 14px;
    }

    .mlogin-form-side { display: flex; align-items: center; justify-content: center; padding: 48px 32px; }
    .mlogin-form-card { width: 100%; max-width: 380px; animation: mloginUp .5s cubic-bezier(.34,1.1,.64,1); }
    @keyframes mloginUp { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: translateY(0); } }

    .mlogin-form-card h2 { font-size: 1.625rem; font-weight: 800; letter-spacing: -0.02em; color: var(--mlogin-ink); margin: 0 0 6px; }
    .mlogin-form-card .mlogin-sub { color: var(--mlogin-muted); font-size: 0.875rem; margin-bottom: 32px; }

    .mf-group { margin-bottom: 18px; }
    .mf-label { display: block; font-size: 0.8125rem; font-weight: 650; color: #374151; margin-bottom: 6px; }
    .mf-input {
        width: 100%; border: 1.5px solid transparent; border-radius: 10px;
        padding: 11px 14px; font-size: 0.875rem; font-family: inherit;
        background: #f4f5f7; color: var(--mlogin-ink);
        transition: border-color .2s, box-shadow .2s, background .2s;
    }
    .mf-input:focus { outline: none; border-color: var(--mlogin-primary); box-shadow: 0 0 0 4px color-mix(in srgb, var(--mlogin-primary) 14%, transparent); background: #fff; }
    .mf-input.is-invalid { border-color: #ef4444; background: #fef2f2; }
    .mf-err { font-size: 0.75rem; color: #ef4444; margin-top: 4px; display: block; font-weight: 500; }

    .mf-pw { position: relative; }
    .mf-pw .mf-input { padding-right: 44px; }
    .mf-pw-eye {
        position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
        background: none; border: none; cursor: pointer; color: #9ca3af; font-size: 0.9375rem; padding: 0;
    }
    .mf-pw-eye:hover { color: var(--mlogin-primary); }

    .mf-alert { border-radius: 10px; font-size: 0.8125rem; padding: 11px 14px; margin-bottom: 18px; display: flex; align-items: center; gap: 8px; }
    .mf-alert.err { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
    .mf-alert.ok  { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }

    .mf-submit {
        width: 100%; background: linear-gradient(135deg, var(--mlogin-primary), var(--mlogin-primary-dark));
        color: #fff; border: none; border-radius: 10px; padding: 13px;
        font-size: 0.9375rem; font-weight: 700; cursor: pointer; transition: all .2s; margin-top: 8px;
        display: flex; align-items: center; justify-content: center; gap: 8px; font-family: inherit;
        box-shadow: 0 4px 14px color-mix(in srgb, var(--mlogin-primary) 35%, transparent);
    }
    .mf-submit:hover { transform: translateY(-2px); box-shadow: 0 10px 24px color-mix(in srgb, var(--mlogin-primary) 40%, transparent); }

    body.submitting .mf-submit .mlbl { display: none; }
    body.submitting .mf-submit .mspin { display: flex; }
    .mf-submit .mspin { display: none; align-items: center; gap: 8px; }
    @keyframes mSpin { to { transform: rotate(360deg); } }

    .mf-divider { text-align: center; margin: 22px 0; font-size: 0.75rem; color: var(--mlogin-muted); position: relative; }
    .mf-divider::before, .mf-divider::after { content: ''; position: absolute; top: 50%; width: 38%; height: 1px; background: var(--mlogin-border); }
    .mf-divider::before { left: 0; }
    .mf-divider::after  { right: 0; }

    .mf-hint { text-align: center; font-size: 0.8125rem; color: var(--mlogin-muted); }
    .mf-hint a { color: var(--mlogin-primary); font-weight: 650; text-decoration: none; }
    .mf-hint a:hover { text-decoration: underline; }

    @media (max-width: 900px) {
        .mlogin-shell { grid-template-columns: 1fr; }
        .mlogin-aside { display: none; }
        .mlogin-form-side { padding: 32px 20px; }
    }
</style>

<div class="mlogin-shell">
    <div class="mlogin-aside">
        <div class="mlogin-aside-brand">
            <div class="mlogin-aside-logo"><i class="fas fa-plane"></i></div>
            <div class="mlogin-aside-name">{{ getSetting('business_name', 'main', 'TravelBookingPanel') }}</div>
        </div>
        <h1>Manage every booking from one modern dashboard.</h1>
        <p>Bookings, agents, hotels, tours and more — all in a workspace built for speed.</p>
        <div class="mlogin-aside-points">
            <div class="mlogin-aside-point"><i class="fas fa-bolt"></i> Real-time booking &amp; agent overview</div>
            <div class="mlogin-aside-point"><i class="fas fa-shield-halved"></i> Role-based access for agents &amp; staff</div>
            <div class="mlogin-aside-point"><i class="fas fa-chart-line"></i> Revenue &amp; performance at a glance</div>
        </div>
    </div>

    <div class="mlogin-form-side">
        <div class="mlogin-form-card">
            <h2>Welcome back</h2>
            <p class="mlogin-sub">Sign in to your account to continue</p>

            @if(session('success'))
                <div class="mf-alert ok"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="mf-alert err">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>@foreach($errors->all() as $e) {{ $e }}<br> @endforeach</span>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.signin.post') }}" id="loginForm">
                @csrf

                <div class="mf-group">
                    <label class="mf-label">Email Address</label>
                    <input type="email" name="email"
                           class="mf-input @error('email') is-invalid @enderror"
                           value="{{ old('email') }}"
                           placeholder="you@example.com" required autofocus>
                    @error('email')<span class="mf-err">{{ $message }}</span>@enderror
                </div>

                <div class="mf-group">
                    <label class="mf-label">Password</label>
                    <div class="mf-pw">
                        <input type="password" name="password" id="pwField"
                               class="mf-input @error('password') is-invalid @enderror"
                               placeholder="Enter your password" required>
                        <button type="button" class="mf-pw-eye" onclick="togglePw()">
                            <i class="fas fa-eye" id="pwEye"></i>
                        </button>
                    </div>
                    @error('password')<span class="mf-err">{{ $message }}</span>@enderror
                </div>

                <button type="submit" class="mf-submit" id="loginBtn">
                    <span class="mlbl"><i class="fas fa-right-to-bracket"></i> Sign In</span>
                    <span class="mspin">
                        <div style="width:16px;height:16px;border:2px solid rgba(255,255,255,.4);border-top-color:#fff;border-radius:50%;animation:mSpin .7s linear infinite;"></div>
                        Signing in&hellip;
                    </span>
                </button>
            </form>

            <div class="mf-divider">or</div>
            <div class="mf-hint">
                Don't have an account?
                <a href="{{ route('user.register') }}">User Signup</a> &nbsp;&middot;&nbsp;
                <a href="{{ route('agent.register') }}">Agent Signup</a>
            </div>
        </div>
    </div>
</div>

<script>
    function togglePw() {
        const f = document.getElementById('pwField'), i = document.getElementById('pwEye');
        f.type = f.type === 'password' ? 'text' : 'password';
        i.className = f.type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
    }

    document.getElementById('loginForm').addEventListener('submit', () => {
        document.body.classList.add('submitting');
    });
</script>

@include('common.footer')
