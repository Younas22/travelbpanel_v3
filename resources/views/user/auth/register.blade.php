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

    .reg-wrap {
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
    }

    .form-head {
        background: var(--primary);
        padding: 28px 32px;
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
    .form-head h4 { color: #fff; font-size: 1.2rem; font-weight: 800; margin-bottom: 4px; letter-spacing: -.01em; }
    .form-head p  { color: rgba(255,255,255,.65); font-size: .8rem; margin: 0; }

    .form-body { padding: 28px 32px; }

    .f-label { display: block; font-size: .78rem; font-weight: 600; color: #374151; margin-bottom: 4px; }
    .f-label .req { color: var(--accent); }
    .f-group { margin-bottom: 16px; }

    .f-input {
        width: 100%; border: 1.5px solid #d1d5db; border-radius: 8px;
        padding: 10px 12px; font-size: .88rem; font-family: inherit;
        background: #fafafa; color: var(--text);
        transition: border-color .2s, box-shadow .2s;
        box-sizing: border-box;
    }
    .f-input:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 0 3px rgba(37,99,235,.11); background: #fff; }
    .f-input.is-invalid { border-color: #ef4444; }
    .f-err { display: block; font-size: .73rem; color: #ef4444; margin-top: 3px; }

    .f-row { display: flex; gap: 12px; }
    .f-row .f-group { flex: 1; }

    .f-pw { position: relative; }
    .f-pw .f-input { padding-right: 40px; }
    .pw-eye {
        position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
        background: none; border: none; cursor: pointer; color: #9ca3af;
        font-size: .9rem; padding: 0; line-height: 1;
    }
    .pw-eye:hover { color: var(--accent); }

    .btn-sub {
        width: 100%; background: var(--accent); color: #fff; border: none;
        border-radius: 9px; padding: 12px; font-size: .92rem; font-weight: 700;
        cursor: pointer; transition: all .2s; margin-top: 6px;
        display: flex; align-items: center; justify-content: center; gap: 6px;
        font-family: inherit;
    }
    .btn-sub:hover { background: #1D4ED8; transform: translateY(-1px); box-shadow: 0 6px 18px rgba(37,99,235,.35); }

    .f-divider { text-align: center; margin: 18px 0; font-size: .78rem; color: var(--muted); position: relative; }
    .f-divider::before, .f-divider::after {
        content: ''; position: absolute; top: 50%; width: 38%; height: 1px; background: var(--border);
    }
    .f-divider::before { left: 0; }
    .f-divider::after  { right: 0; }

    .f-hint { text-align: center; font-size: .82rem; color: var(--muted); margin-top: 14px; }
    .f-hint a { color: var(--primary); font-weight: 600; text-decoration: none; }
    .f-hint a:hover { color: var(--accent); }

    .f-alert { border-radius: 8px; font-size: .8rem; padding: 9px 12px; margin-bottom: 16px; }
    .f-alert.err { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
    .f-alert.ok  { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }

    /* loading */
    body.loading .btn-sub .lbl { display: none; }
    body.loading .btn-sub .spin { display: flex; }
    .btn-sub .spin { display: none; align-items: center; gap: 7px; }
    @keyframes spin { to { transform: rotate(360deg); } }

    @media (max-width: 480px) {
        .form-head, .form-body { padding: 22px 20px; }
        .f-row { flex-direction: column; gap: 0; }
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
@include('common.auth-brand', ['headline' => 'Start exploring the world'])
<div class="reg-wrap">
    <div class="form-card">

        <div class="form-head">
            <div class="logo-ico"><i class="fas fa-user-plus"></i></div>
            <h4>Create an Account</h4>
            <p>Sign up to get started — it's free</p>
        </div>

        <div class="form-body">

            @if(session('success'))
                <div class="f-alert ok"><i class="fas fa-check-circle me-1"></i>{{ session('success') }}</div>
            @endif

            @if($errors->any())
                <div class="f-alert err">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    <ul style="margin:4px 0 0 16px; padding:0;">
                        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('user.register.submit') }}" id="regForm">
                @csrf

                <div class="f-row">
                    <div class="f-group">
                        <label class="f-label">First Name <span class="req">*</span></label>
                        <input type="text" name="first_name"
                               class="f-input @error('first_name') is-invalid @enderror"
                               value="{{ old('first_name') }}" placeholder="John" required autofocus>
                        @error('first_name')<span class="f-err">{{ $message }}</span>@enderror
                    </div>
                    <div class="f-group">
                        <label class="f-label">Last Name <span class="req">*</span></label>
                        <input type="text" name="last_name"
                               class="f-input @error('last_name') is-invalid @enderror"
                               value="{{ old('last_name') }}" placeholder="Doe" required>
                        @error('last_name')<span class="f-err">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="f-group">
                    <label class="f-label">Email Address <span class="req">*</span></label>
                    <input type="email" name="email"
                           class="f-input @error('email') is-invalid @enderror"
                           value="{{ old('email') }}" placeholder="you@example.com" required>
                    @error('email')<span class="f-err">{{ $message }}</span>@enderror
                </div>

                <div class="f-row">
                    <div class="f-group">
                        <label class="f-label">Password <span class="req">*</span></label>
                        <div class="f-pw">
                            <input type="password" name="password" id="pw1"
                                   class="f-input @error('password') is-invalid @enderror"
                                   placeholder="Min. 8 characters" required>
                            <button type="button" class="pw-eye" onclick="pwToggle('pw1','pe1')">
                                <i class="fas fa-eye" id="pe1"></i>
                            </button>
                        </div>
                        @error('password')<span class="f-err">{{ $message }}</span>@enderror
                    </div>
                    <div class="f-group">
                        <label class="f-label">Confirm Password <span class="req">*</span></label>
                        <div class="f-pw">
                            <input type="password" name="password_confirmation" id="pw2"
                                   class="f-input" placeholder="Repeat password" required>
                            <button type="button" class="pw-eye" onclick="pwToggle('pw2','pe2')">
                                <i class="fas fa-eye" id="pe2"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-sub">
                    <span class="lbl"><i class="fas fa-user-check"></i> Create Account</span>
                    <span class="spin">
                        <div style="width:16px;height:16px;border:2px solid rgba(255,255,255,.4);border-top-color:#fff;border-radius:50%;animation:spin .7s linear infinite;"></div>
                        Creating account…
                    </span>
                </button>
            </form>

            <div class="f-divider">or</div>
            <div class="f-hint">
                Already have an account? <a href="{{ route('login') }}">Sign In</a>
            </div>

        </div>
    </div>
</div>
</div>

<script>
    function pwToggle(fid, iid) {
        const f = document.getElementById(fid), i = document.getElementById(iid);
        f.type = f.type === 'password' ? 'text' : 'password';
        i.className = f.type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
    }
    document.getElementById('regForm').addEventListener('submit', () => document.body.classList.add('loading'));
</script>

@include('common.footer', ['hideFooter' => true])
