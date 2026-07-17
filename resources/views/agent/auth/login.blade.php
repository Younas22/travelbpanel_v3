<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agent Login - {{ getSetting('business_name', 'main', 'TravelPanel') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #0066cc;
            --secondary-color: #004499;
            --accent-color: #0066cc;
            --danger-color: #dc3545;
            --success-color: #28a745;
            --dark-color: #343a40;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #0066cc, #004499);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .floating-elements {
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            pointer-events: none;
            z-index: 1;
        }

        .floating-elements::before,
        .floating-elements::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.08);
            animation: float 6s ease-in-out infinite;
        }

        .floating-elements::before {
            width: 220px; height: 220px;
            top: 8%; left: 8%;
            animation-delay: 0s;
        }

        .floating-elements::after {
            width: 160px; height: 160px;
            bottom: 10%; right: 8%;
            animation-delay: 3s;
        }

        .bubble {
            position: absolute;
            border-radius: 50%;
            background: rgba(255,255,255,0.06);
            animation: float 8s ease-in-out infinite;
        }

        .bubble-1 { width: 100px; height: 100px; top: 30%; left: 5%; animation-delay: 1s; }
        .bubble-2 { width: 70px;  height: 70px;  top: 60%; right: 5%; animation-delay: 2s; }
        .bubble-3 { width: 50px;  height: 50px;  top: 20%; right: 20%; animation-delay: 4s; }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }

        .login-container {
            position: relative;
            z-index: 5;
            width: 100%;
            max-width: 450px;
            padding: 20px;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.97);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.25);
            border: 1px solid rgba(255, 255, 255, 0.3);
            animation: slideUp 0.7s ease-out;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .brand-logo {
            text-align: center;
            margin-bottom: 32px;
        }

        .brand-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: #fff;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            padding: 5px 14px;
            border-radius: 20px;
            margin-top: 12px;
        }

        .form-group { margin-bottom: 22px; position: relative; }

        .form-label {
            display: flex;
            align-items: center;
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 8px;
            font-size: 0.9rem;
        }

        .form-label i { margin-right: 7px; color: var(--primary-color); }

        .form-control {
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            background: #fafafa;
        }

        .form-control:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 0.2rem rgba(0, 102, 204, 0.25);
            background: #fff;
            outline: none;
        }

        .form-control.is-invalid { border-color: var(--danger-color); }

        .input-group .form-control {
            border-right: none;
            border-radius: 12px 0 0 12px;
        }

        .input-group-text {
            background: #fafafa;
            border: 2px solid #e5e7eb;
            border-left: none;
            border-radius: 0 12px 12px 0;
            cursor: pointer;
            transition: all 0.3s ease;
            color: #9ca3af;
        }

        .input-group:focus-within .form-control,
        .input-group:focus-within .input-group-text {
            border-color: var(--accent-color);
        }

        .input-group:focus-within .input-group-text {
            background: #fff;
        }

        .login-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .form-check-label { font-size: 0.88rem; color: #6b7280; }

        .submit-btn {
            width: 100%;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            border-radius: 12px;
            padding: 13px 24px;
            font-size: 1rem;
            font-weight: 600;
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 102, 204, 0.4);
        }

        .submit-btn:active { transform: translateY(0); }

        .btn-text {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: opacity 0.3s ease;
        }

        .btn-spinner {
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        body.loading .btn-text { opacity: 0; }
        body.loading .btn-spinner { opacity: 1; }
        body.loading .submit-btn { cursor: not-allowed; }

        .alert {
            border-radius: 12px;
            border: none;
            font-size: 0.9rem;
            margin-bottom: 20px;
        }

        .alert-danger  { background: rgba(220, 53, 69, 0.1);  color: var(--danger-color); }
        .alert-success { background: rgba(40, 167, 69, 0.1);  color: var(--success-color); }

        .invalid-feedback { display: block; font-size: 0.82rem; color: var(--danger-color); margin-top: 4px; }

        .divider {
            text-align: center;
            margin: 24px 0 16px;
            position: relative;
        }

        .divider::before {
            content: '';
            position: absolute;
            top: 50%; left: 0;
            width: 100%; height: 1px;
            background: #e5e7eb;
        }

        .divider span {
            position: relative;
            background: #fff;
            padding: 0 12px;
            color: #9ca3af;
            font-size: 0.82rem;
        }

        .register-link {
            text-align: center;
            font-size: 0.9rem;
            color: #6b7280;
        }

        .register-link a {
            color: var(--primary-color);
            font-weight: 600;
            text-decoration: none;
            transition: color 0.2s;
        }

        .register-link a:hover { color: var(--secondary-color); text-decoration: underline; }

        .login-footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 16px;
            border-top: 1px solid #f3f4f6;
            color: #9ca3af;
            font-size: 0.82rem;
        }

        @media (max-width: 576px) {
            .login-container { padding: 15px; }
            .login-card { padding: 28px 22px; }
        }
    </style>
</head>
<body>
    <div class="floating-elements"></div>
    <div class="bubble bubble-1"></div>
    <div class="bubble bubble-2"></div>
    <div class="bubble bubble-3"></div>

    <div class="login-container">
        <div class="login-card">

            <!-- Brand -->
            <div class="brand-logo">
                <img src="{{ getSettingImage('business_logo','branding') }}"
                     alt="{{ getSetting('business_name', 'main', 'TravelPanel') }}"
                     class="img-fluid" style="max-height:60px; height:auto; width:auto;">
                <div><span class="brand-badge"><i class="bi bi-person-badge"></i> Agent Portal</span></div>
            </div>

            <!-- Alerts -->
            @if(session('success'))
                <div class="alert alert-success">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    @foreach($errors->all() as $error){{ $error }}<br>@endforeach
                </div>
            @endif

            <!-- Form -->
            <form id="loginForm" method="POST" action="{{ route('agent.login.submit') }}">
                @csrf

                <div class="form-group">
                    <label class="form-label">
                        <i class="bi bi-envelope"></i> Email Address
                    </label>
                    <input type="email" name="email"
                           class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email') }}"
                           placeholder="your@email.com" required autofocus>
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="bi bi-lock"></i> Password
                    </label>
                    <div class="input-group">
                        <input type="password" name="password" id="password"
                               class="form-control @error('password') is-invalid @enderror"
                               placeholder="Enter your password" required>
                        <span class="input-group-text" onclick="togglePassword()">
                            <i class="bi bi-eye" id="password-toggle"></i>
                        </span>
                    </div>
                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="login-options">
                    <div class="form-check mb-0">
                        <input type="checkbox" name="remember" class="form-check-input" id="remember" {{ old('remember') ? 'checked' : '' }}>
                        <label class="form-check-label" for="remember">Remember me</label>
                    </div>
                </div>

                <button type="submit" class="submit-btn" id="submitBtn">
                    <span class="btn-text">
                        <i class="bi bi-box-arrow-in-right"></i> Sign In to Agent Panel
                    </span>
                    <div class="btn-spinner">
                        <div class="spinner-border spinner-border-sm text-white" role="status"></div>
                    </div>
                </button>
            </form>

            <div class="divider"><span>New to Agent Portal?</span></div>

            <div class="register-link">
                Don't have an account?
                <a href="{{ route('agent.register') }}">Register as Agent</a>
            </div>

            <div class="login-footer">
                &copy; {{ date('Y') }} {{ getSetting('business_name', 'main', 'TravelPanel') }}.
                Authorized agent access only.
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword() {
            const input  = document.getElementById('password');
            const icon   = document.getElementById('password-toggle');
            input.type   = input.type === 'password' ? 'text' : 'password';
            icon.className = input.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
        }

        document.getElementById('loginForm').addEventListener('submit', function() {
            document.body.classList.add('loading');
        });

        // Auto-hide alerts
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.alert').forEach(alert => {
                setTimeout(() => {
                    alert.style.transition = 'opacity 0.5s ease';
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 500);
                }, 5000);
            });
        });
    </script>
</body>
</html>
