@include('common.header', ['hideNavbar' => true])

<style>
    :root { --primary:#0077BE; --primary-m:#005a8f; --text:#0f172a; --muted:#64748b; --border:#e2e8f0; --surface:#f8fafc; }
    .tf-wrap { min-height:80vh; display:flex; align-items:center; justify-content:center; padding:48px 16px; background:var(--surface); }
    .tf-card { width:100%; max-width:460px; background:#fff; border:1px solid var(--border); border-radius:18px; box-shadow:0 12px 48px rgba(0,53,128,.09); overflow:hidden; }
    .tf-head { background:linear-gradient(135deg,var(--primary) 0%,var(--primary-m) 100%); padding:28px 32px 22px; text-align:center; color:#fff; }
    .tf-head .ico { width:52px; height:52px; background:rgba(255,255,255,.15); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 14px; font-size:1.3rem; }
    .tf-head h4 { font-size:1.15rem; font-weight:800; margin:0 0 3px; }
    .tf-head p { color:rgba(255,255,255,.7); font-size:.8rem; margin:0; }
    .tf-body { padding:26px 32px 30px; }
    .tf-step { font-size:.85rem; color:#374151; margin-bottom:14px; line-height:1.5; }
    #tfQr { display:flex; justify-content:center; margin:16px 0 20px; }
    .tf-key-box { background:#f8fafc; border:1px solid var(--border); border-radius:10px; padding:12px 14px; text-align:center; margin-bottom:20px; }
    .tf-key-box .lbl { font-size:.72rem; color:var(--muted); margin-bottom:4px; }
    .tf-key-box .key { font-family:monospace; font-size:.95rem; letter-spacing:.06em; color:var(--text); word-break:break-all; }
    .tf-label { display:block; font-size:.78rem; font-weight:600; color:#374151; margin-bottom:5px; }
    .tf-input { width:100%; border:1.5px solid #d1d5db; border-radius:9px; padding:12px 14px; font-size:1.25rem; letter-spacing:.4em; text-align:center; font-family:monospace; background:#fafafa; color:var(--text); }
    .tf-input:focus { outline:none; border-color:var(--primary); box-shadow:0 0 0 3px rgba(0,119,190,.11); background:#fff; }
    .tf-err { font-size:.78rem; color:#ef4444; margin-top:6px; display:block; text-align:center; }
    .tf-btn { width:100%; background:var(--primary); color:#fff; border:none; border-radius:10px; padding:12px; font-size:.92rem; font-weight:700; cursor:pointer; margin-top:18px; }
    .tf-btn:hover { background:#0066a5; }
    .tf-hint { text-align:center; font-size:.8rem; color:var(--muted); margin-top:16px; }
    .tf-hint a { color:var(--primary); font-weight:600; text-decoration:none; }
</style>

<div class="tf-wrap">
    <div class="tf-card">
        <div class="tf-head">
            <div class="ico"><i class="fas fa-qrcode"></i></div>
            <h4>Set Up Google Authenticator</h4>
            <p>One-time setup — this only shows once</p>
        </div>
        <div class="tf-body">
            <p class="tf-step">Open <strong>Google Authenticator</strong>, tap <strong>+</strong>, then <strong>Scan a QR code</strong> and point your camera here:</p>

            <div id="tfQr"></div>

            <div class="tf-key-box">
                <div class="lbl">Can't scan? Choose "Enter a setup key" instead and use:</div>
                <div class="key">{{ $secret }}</div>
            </div>

            <form method="POST" action="{{ route('2fa.enroll.confirm') }}" autocomplete="off">
                @csrf
                <label class="tf-label" for="code">Enter the 6-digit code shown in the app</label>
                <input type="text" id="code" name="code" class="tf-input" inputmode="numeric" pattern="\d{6}" maxlength="6"
                       placeholder="000000" required autofocus>
                @error('code')<span class="tf-err">{{ $message }}</span>@enderror
                <button type="submit" class="tf-btn"><i class="fas fa-check"></i> Confirm &amp; Sign In</button>
            </form>
            <div class="tf-hint"><a href="{{ route('login') }}">Cancel and back to login</a></div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    new QRCode(document.getElementById('tfQr'), {
        text: @json($qrUri),
        width: 200,
        height: 200,
        colorDark: '#0f172a',
        colorLight: '#ffffff',
    });
</script>

@include('common.footer', ['hideFooter' => true])
