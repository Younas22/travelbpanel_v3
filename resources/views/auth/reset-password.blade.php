@include('common.header', ['hideNavbar' => true, 'pageClass' => 'view-reset-password'])


<div class="fp-wrap">
    <div class="fp-card">
        <div class="fp-head">
            <div class="ico"><i class="fas fa-lock"></i></div>
            <h4>Set a New Password</h4>
            <p>Choose a new password for your account</p>
        </div>
        <div class="fp-body">
            @if($errors->any())
                <span class="fp-err" style="margin-bottom:14px; display:block;">{{ $errors->first() }}</span>
            @endif
            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div class="fp-group">
                    <label class="fp-label">Email</label>
                    <input type="email" class="fp-input" value="{{ $email }}" readonly>
                    <input type="hidden" name="email" value="{{ $email }}">
                </div>

                <div class="fp-group">
                    <label class="fp-label" for="password">New Password</label>
                    <input type="password" id="password" name="password" class="fp-input" placeholder="Min. 8 characters" required>
                </div>

                <div class="fp-group">
                    <label class="fp-label" for="password_confirmation">Confirm New Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="fp-input" placeholder="Repeat password" required>
                </div>

                <button type="submit" class="fp-btn"><i class="fas fa-check"></i> Reset Password</button>
            </form>
            <div class="fp-hint"><a href="{{ route('login') }}">Back to login</a></div>
        </div>
    </div>
</div>

@include('common.footer', ['hideFooter' => true])
