@php
    $__theme = app(\App\Services\ThemeService::class)->active(auth()->id());
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied</title>
    <link href="{{ url('public/assets/css/bootstrap-5.3.6-dist/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ url('public/assets/libs/bootstrap-icons/font/bootstrap-icons.min.css') }}" rel="stylesheet">
    <link href="{{ asset('public/assets/css/agent-modern.css') }}?v={{ @filemtime(public_path('assets/css/agent-modern.css')) ?: 1 }}" rel="stylesheet">
    <style id="theme-vars">{!! app(\App\Services\ThemeService::class)->inlineStyleTag($__theme) !!}</style>
</head>
<body>
    <div class="ap-status-shell">
        <div class="ap-status-card">
            <i class="bi bi-lock ap-status-icon" style="color: var(--secondary-color);"></i>
            <h4>Access Denied</h4>
            <p>You do not have permission to access this module. Please contact your admin to enable this feature for your account.</p>
            <a href="{{ route('agent.dashboard') }}" class="ap-btn-primary">
                <i class="bi bi-house"></i> Back to Dashboard
            </a>
        </div>
    </div>
</body>
</html>
