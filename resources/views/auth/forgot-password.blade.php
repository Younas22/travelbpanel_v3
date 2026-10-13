@include('common.header', ['hideNavbar' => true])

<style>
    :root { --primary:#0077BE; --primary-m:#005a8f; --text:#0f172a; --muted:#64748b; --border:#e2e8f0; --surface:#f8fafc; }
    .fp-wrap { min-height:80vh; display:flex; align-items:center; justify-content:center; padding:48px 16px; background:var(--surface); }
    .fp-card { width:100%; max-width:420px; background:#fff; border:1px solid var(--border); border-radius:18px; box-shadow:0 12px 48px rgba(0,53,128,.09); overflow:hidden; }
    .fp-head { background:linear-gradient(135deg,var(--primary) 0%,var(--primary-m) 100%); padding:28px 32px 22px; text-align:center; color:#fff; }
    .fp-head .ico { width:52px; height:52px; background:rgba(255,255,255,.15); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 14px; font-size:1.3rem; }
    .fp-head h4 { font-size:1.15rem; font-weight:800; margin:0 0 3px; }
    .fp-head p { color:rgba(255,255,255,.7); font-size:.8rem; margin:0; }
    .fp-body { padding:26px 32px 30px; }
    .fp-label { display:block; font-size:.78rem; font-weight:600; color:#374151; margin-bottom:5px; }
    .fp-input { width:100%; border:1.5px solid #d1d5db; border-radius:9px; padding:12px 14px; font-size:.9rem; font-family:inherit; background:#fafafa; color:var(--text); }
    .fp-input:focus { outline:none; border-color:var(--primary); box-shadow:0 0 0 3px rgba(0,119,190,.11); background:#fff; }
    .fp-err { font-size:.78rem; color:#ef4444; margin-top:6px; display:block; }
    .fp-alert { border-radius:9px; font-size:.82rem; padding:11px 14px; margin-bottom:18px; }
    .fp-alert.ok { background:#f0fdf4; color:#16a34a; border:1px solid #bbf7d0; }
    .fp-btn { width:100%; background:var(--primary); color:#fff; border:none; border-radius:10px; padding:12px; font-size:.92rem; font-weight:700; cursor:pointer; margin-top:18px; }
    .fp-btn:hover { background:#0066a5; }
    .fp-hint { text-align:center; font-size:.8rem; color:var(--muted); margin-top:16px; }
    .fp-hint a { color:var(--primary); font-weight:600; text-decoration:none; }
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
