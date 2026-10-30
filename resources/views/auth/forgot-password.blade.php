@include('common.header', ['hideNavbar' => true, 'pageClass' => 'view-forgot-password'])


<div class="auth-split">
@include('common.auth-brand', ['headline' => 'Locked out? We\'ve got you'])
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
</div>

@include('common.footer', ['hideFooter' => true])
