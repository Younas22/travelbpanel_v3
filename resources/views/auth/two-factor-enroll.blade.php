@include('common.header', ['hideNavbar' => true, 'pageClass' => 'view-2fa-enroll'])


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
