@include('common.header', ['hideNavbar' => true, 'pageClass' => 'view-login'])


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
