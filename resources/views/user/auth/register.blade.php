@include('common.header', ['hideNavbar' => true, 'pageClass' => 'view-register'])


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
