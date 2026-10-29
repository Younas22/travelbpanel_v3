@include('common.header', ['hideNavbar' => true])

<style>
    :root { --primary:#2563EB; --primary-m:#1D4ED8; --text:#0f172a; --muted:#64748b; --border:#e2e8f0; --surface:#f8fafc; }
    .fp-wrap { min-height:80vh; display:flex; align-items:center; justify-content:center; padding:48px 16px; background:var(--surface); }
    .fp-card { width:100%; max-width:420px; background:#fff; border:1px solid var(--border); border-radius:18px; box-shadow:0 12px 48px rgba(0,0,0,.09); overflow:hidden; }
    .fp-head { background: var(--primary); padding:28px 32px 22px; text-align:center; color:#fff; }
    .fp-head .ico { width:52px; height:52px; background:rgba(255,255,255,.15); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 14px; font-size:1.3rem; }
    .fp-head h4 { font-size:1.15rem; font-weight:800; margin:0 0 3px; }
    .fp-head p { color:rgba(255,255,255,.7); font-size:.8rem; margin:0; }
    .fp-body { padding:26px 32px 30px; }
    .fp-label { display:block; font-size:.78rem; font-weight:600; color:#374151; margin-bottom:5px; }
    .fp-input { width:100%; border:1.5px solid #d1d5db; border-radius:9px; padding:12px 14px; font-size:.9rem; font-family:inherit; background:#fafafa; color:var(--text); }
    .fp-input:focus { outline:none; border-color:var(--primary); box-shadow:0 0 0 3px rgba(37,99,235,.11); background:#fff; }
    .fp-err { font-size:.78rem; color:#ef4444; margin-top:6px; display:block; }
    .fp-alert { border-radius:9px; font-size:.82rem; padding:11px 14px; margin-bottom:18px; }
    .fp-alert.ok { background:#f0fdf4; color:#16a34a; border:1px solid #bbf7d0; }
    .fp-btn { width:100%; background:var(--primary); color:#fff; border:none; border-radius:10px; padding:12px; font-size:.92rem; font-weight:700; cursor:pointer; margin-top:18px; }
    .fp-btn:hover { background:#1D4ED8; }
    .fp-hint { text-align:center; font-size:.8rem; color:var(--muted); margin-top:16px; }
    .fp-hint a { color:var(--primary); font-weight:600; text-decoration:none; }

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

<div class="fp-wrap">
    <div class="fp-card">
        <div class="fp-head">
            <div class="ico"><i class="fas fa-key"></i></div>
            <h4>Forgot Password?</h4>
            <p>We'll email you a link to reset it</p>
        </div>
        <div class="fp-body">
            @if(session('success'))
                <div class="fp-alert ok"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
            @endif
            <form method="POST" action="{{ route('password.email') }}">
                @csrf
                <label class="fp-label" for="email">Email Address</label>
                <input type="email" id="email" name="email" class="fp-input" value="{{ old('email') }}" placeholder="you@example.com" required autofocus>
                @error('email')<span class="fp-err">{{ $message }}</span>@enderror
                <button type="submit" class="fp-btn"><i class="fas fa-paper-plane"></i> Send Reset Link</button>
            </form>
            <div class="fp-hint"><a href="{{ route('login') }}">Back to login</a></div>
        </div>
    </div>
</div>

@include('common.footer', ['hideFooter' => true])
