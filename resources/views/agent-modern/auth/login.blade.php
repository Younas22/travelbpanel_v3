{{--
    Disclosure: this mirrors agent/auth/login.blade.php, which is currently
    dead/unreachable code — Agent\AuthController::showLogin() returns it,
    but no GET route in routes/agent.php ever calls that method (only
    agent.login.submit / POST exists). The real, live agent login page is
    the shared admin.auth.login view (Admin\AuthController::showLogin(),
    bound to routes 'login' / 'admin.login', posting to admin.signin.post,
    which authenticates and redirects by role — see redirectByRole()).
    Its Modern equivalent already lives at admin-modern/auth/login.blade.php.
    Kept here, theme-aware rather than hardcoded, for file-tree parity with
    the Classic agent/ directory and in case a future GET route is wired
    up to Agent\AuthController::showLogin().
--}}
@php
    $__loginTheme = app(\App\Services\ThemeService::class)->active(null);
    $__loginPrimary = $__loginTheme->primary_color ?: '#0C6DFD';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agent Login - {{ getSetting('business_name', 'main', 'TravelPanel') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="{{ url('public/assets/css/bootstrap-5.3.6-dist/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ url('public/assets/libs/bootstrap-icons/font/bootstrap-icons.min.css') }}" rel="stylesheet">
    <link href="{{ asset('public/assets/css/agent-modern.css') }}?v={{ @filemtime(public_path('assets/css/agent-modern.css')) ?: 1 }}" rel="stylesheet">
    <style id="theme-vars">:root{--primary-color:{{ $__loginPrimary }};}</style>
</head>
<body>
    <div class="auth-shell">
        <div class="auth-card">

            <div class="auth-brand">
                <img src="{{ getSettingImage('business_logo','branding') }}" alt="{{ getSetting('business_name', 'main', 'TravelPanel') }}">
                <span class="auth-badge"><i class="bi bi-person-badge"></i> Agent Portal</span>
            </div>

            <h1 class="auth-title">Welcome back</h1>
            <p class="auth-sub">Sign in to manage your bookings, wallet and properties.</p>

            @if(session('success'))
                <div class="am-alert am-alert-success"><i class="bi bi-check-circle-fill"></i><span>{{ session('success') }}</span></div>
            @endif

            @if($errors->any())
                <div class="am-alert am-alert-danger">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <span>@foreach($errors->all() as $error){{ $error }}<br>@endforeach</span>
                </div>
            @endif

            <form id="loginForm" method="POST" action="{{ route('agent.login.submit') }}">
                @csrf

                <div class="auth-field">
                    <label><i class="bi bi-envelope"></i> Email Address</label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email') }}" placeholder="your@email.com" required autofocus>
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="auth-field">
                    <label><i class="bi bi-lock"></i> Password</label>
                    <div class="auth-input-group">
                        <input type="password" name="password" id="password"
                               class="form-control @error('password') is-invalid @enderror"
                               placeholder="Enter your password" required>
                        <button type="button" class="auth-eye" onclick="togglePassword()">
                            <i class="bi bi-eye" id="password-toggle"></i>
                        </button>
                    </div>
                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="auth-options">
                    <div class="form-check mb-0">
                        <input type="checkbox" name="remember" class="form-check-input" id="remember" {{ old('remember') ? 'checked' : '' }}>
                        <label class="form-check-label" for="remember">Remember me</label>
                    </div>
                </div>

                <button type="submit" class="auth-submit" id="submitBtn">
                    <i class="bi bi-box-arrow-in-right"></i> Sign In to Agent Panel
                </button>
            </form>

            <div class="auth-divider"><span>New to Agent Portal?</span></div>

            <div class="auth-footer-link">
                Don't have an account?
                <a href="{{ route('agent.register') }}">Register as Agent</a>
            </div>

            <div class="auth-footnote">
                &copy; {{ date('Y') }} {{ getSetting('business_name', 'main', 'TravelPanel') }}.
                Authorized agent access only.
            </div>
        </div>
    </div>

    <script src="{{ url('public/assets/libs/bootstrap-5.3.6-dist/js/bootstrap.bundle.min.js') }}"></script>
    <script>
        function togglePassword() {
            const input  = document.getElementById('password');
            const icon   = document.getElementById('password-toggle');
            input.type   = input.type === 'password' ? 'text' : 'password';
            icon.className = input.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
        }

        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.am-alert').forEach(function (alert) {
                setTimeout(function () {
                    alert.style.transition = 'opacity 0.5s ease';
                    alert.style.opacity = '0';
                    setTimeout(function () { alert.remove(); }, 500);
                }, 5000);
            });
        });
    </script>
</body>
</html>
