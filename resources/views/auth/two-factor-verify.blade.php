@include('common.header', ['hideNavbar' => true, 'pageClass' => 'view-2fa-verify'])


<div class="tf-wrap">
    <div class="tf-card">
        <div class="tf-head">
            <div class="ico"><i class="fas fa-shield-halved"></i></div>
            <h4>Two-Step Verification</h4>
            <p>Enter the 6-digit code from Google Authenticator</p>
        </div>
        <div class="tf-body">
            <form method="POST" action="{{ route('2fa.verify') }}" autocomplete="off">
                @csrf
                <label class="tf-label" for="code">Verification code</label>
                <input type="text" id="code" name="code" class="tf-input" inputmode="numeric" pattern="\d{6}" maxlength="6"
                       placeholder="000000" required autofocus>
                @error('code')<span class="tf-err">{{ $message }}</span>@enderror
                <button type="submit" class="tf-btn"><i class="fas fa-check"></i> Verify &amp; Sign In</button>
            </form>
            <div class="tf-hint"><a href="{{ route('login') }}">Back to login</a></div>
        </div>
    </div>
</div>

@include('common.footer', ['hideFooter' => true])
