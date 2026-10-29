@include('common.header', ['hideNavbar' => true])

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

    .login-wrap {
        min-height: 80vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 48px 16px;
        background: var(--surface);
    }

    .form-card {
        width: 100%;
        max-width: 460px;
        background: #fff;
        border: 1px solid var(--border);
        border-radius: 18px;
        box-shadow: 0 12px 48px rgba(0,0,0,.09);
        overflow: hidden;
        animation: slideUp .45s cubic-bezier(.34,1.1,.64,1);
    }
    @keyframes slideUp {
        from { opacity: 0; transform: translateY(22px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .form-head {
        background: var(--primary);
        padding: 28px 32px 22px;
        text-align: center;
    }
    .form-head .logo-ico {
        width: 52px; height: 52px;
        background: rgba(255,255,255,.15);
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 14px;
        font-size: 1.3rem; color: #fff;
    }
    .form-head img  { max-height: 52px; height: auto; width: auto; }
    .form-head h4   { color: #fff; font-size: 1.15rem; font-weight: 800; margin: 14px 0 3px; letter-spacing: -.01em; }
    .form-head p    { color: rgba(255,255,255,.65); font-size: .8rem; margin: 0; }

    .form-body { padding: 26px 32px 30px; }

    .f-group  { margin-bottom: 18px; }
    .f-label  { display: block; font-size: .78rem; font-weight: 600; color: #374151; margin-bottom: 5px; }
    .f-input  {
        width: 100%; border: 1.5px solid #d1d5db; border-radius: 9px;
        padding: 10px 14px; font-size: .88rem; font-family: inherit;
        background: #fafafa; color: var(--text);
        transition: border-color .2s, box-shadow .2s;
    }
    .f-input:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 0 3px rgba(37,99,235,.11); background: #fff; }
    .f-input.is-invalid { border-color: #ef4444; }
    .f-err { font-size: .73rem; color: #ef4444; margin-top: 3px; display: block; }

    .f-pw { position: relative; }
    .f-pw .f-input { padding-right: 42px; }
    .pw-eye {
        position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
        background: none; border: none; cursor: pointer;
        color: #9ca3af; font-size: .9rem; padding: 0;
    }
    .pw-eye:hover { color: var(--accent); }

    .f-alert { border-radius: 9px; font-size: .8rem; padding: 10px 14px; margin-bottom: 18px; }
    .f-alert.err { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
    .f-alert.ok  { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }

    .btn-login {
        width: 100%; background: var(--accent); color: #fff; border: none;
        border-radius: 10px; padding: 12px; font-size: .92rem; font-weight: 700;
        cursor: pointer; transition: all .2s; margin-top: 6px;
        display: flex; align-items: center; justify-content: center; gap: 7px;
        font-family: inherit;
    }
    .btn-login:hover { background: #1D4ED8; transform: translateY(-1px); box-shadow: 0 6px 20px rgba(37,99,235,.35); }

    body.submitting .btn-login .lbl { display: none; }
    body.submitting .btn-login .spin { display: flex; }
    .btn-login .spin { display: none; align-items: center; gap: 7px; }
    @keyframes spin { to { transform: rotate(360deg); } }

    .f-divider { text-align: center; margin: 18px 0; font-size: .78rem; color: var(--muted); position: relative; }
    .f-divider::before, .f-divider::after {
        content: ''; position: absolute; top: 50%; width: 38%; height: 1px; background: var(--border);
    }
    .f-divider::before { left: 0; }
    .f-divider::after  { right: 0; }

    .f-hint { text-align: center; font-size: .82rem; color: var(--muted); margin-top: 14px; }
    .f-hint a { color: var(--primary); font-weight: 600; text-decoration: none; }
    .f-hint a:hover { color: var(--accent); }

    @media (max-width: 480px) {
        .form-head, .form-body { padding-left: 20px; padding-right: 20px; }
        .role-tab { font-size: .74rem; }
    }

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

<div class="auth-split">
@include('common.auth-brand', ['headline' => 'Welcome back, traveller'])
<div class="login-wrap">
    <div class="form-card">

        <div class="form-head">
            <!-- <img src="{{ getSettingImage('business_logo','branding') }}" alt="{{ getSetting('business_name','main','TravelBookingPanel') }}"> -->
            <h4>Welcome Back</h4>
            <p>Sign in to your account to continue</p>
        </div>

        <div class="form-body">

            @if(session('success'))
                <div class="f-alert ok"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="f-alert err">
                    <i class="fas fa-exclamation-triangle"></i>
                    @foreach($errors->all() as $e) {{ $e }}<br> @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('admin.signin.post') }}" id="loginForm">
                @csrf

                <div class="f-group">
                    <label class="f-label">Email Address</label>
                    <input type="email" name="email"
                           class="f-input @error('email') is-invalid @enderror"
                           value="{{ old('email') }}"
                           placeholder="you@example.com" required autofocus>
                    @error('email')<span class="f-err">{{ $message }}</span>@enderror
                </div>

                <div class="f-group">
                    <div class="flex items-center justify-between" style="display:flex; align-items:center; justify-content:space-between;">
                        <label class="f-label" style="margin-bottom:0;">Password</label>
                        <a href="{{ route('password.request') }}" style="font-size:.78rem; font-weight:600; color:var(--primary); text-decoration:none;">Forgot password?</a>
                    </div>
                    <div class="f-pw" style="margin-top:5px;">
                        <input type="password" name="password" id="pwField"
                               class="f-input @error('password') is-invalid @enderror"
                               placeholder="Enter your password" required>
                        <button type="button" class="pw-eye" onclick="togglePw()">
                            <i class="fas fa-eye" id="pwEye"></i>
                        </button>
                    </div>
                    @error('password')<span class="f-err">{{ $message }}</span>@enderror
                </div>

                <button type="submit" class="btn-login" id="loginBtn">
                    <span class="lbl"><i class="fas fa-right-to-bracket"></i> Sign In</span>
                    <span class="spin">
                        <div style="width:16px;height:16px;border:2px solid rgba(255,255,255,.4);border-top-color:#fff;border-radius:50%;animation:spin .7s linear infinite;"></div>
                        Signing in…
                    </span>
                </button>
            </form>

            @php
                $__businessModel   = getSetting('business_model', 'system', 'both');
                $__showUserSignup  = in_array($__businessModel, ['both', 'b2c']);
                $__showAgentSignup = in_array($__businessModel, ['both', 'b2b']);
            @endphp
            @if($__showUserSignup || $__showAgentSignup)
                <div class="f-divider">or</div>
                <div class="f-hint">
                    Don't have an account?
                    @if($__showUserSignup)
                        <a href="{{ route('user.register') }}">User Signup</a>
                    @endif
                    @if($__showUserSignup && $__showAgentSignup)
                        &nbsp;·&nbsp;
                    @endif
                    @if($__showAgentSignup)
                        <a href="{{ agentSignupUrl() }}">Agent Signup</a>
                    @endif
                </div>
            @endif

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

@include('common.footer', ['hideFooter' => true])
