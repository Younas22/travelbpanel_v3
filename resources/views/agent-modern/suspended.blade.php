@php
    $__theme = app(\App\Services\ThemeService::class)->active(auth()->id());
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Suspended</title>
    <link href="{{ url('public/assets/css/bootstrap-5.3.6-dist/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ url('public/assets/libs/bootstrap-icons/font/bootstrap-icons.min.css') }}" rel="stylesheet">
    <link href="{{ asset('public/assets/css/agent-modern.css') }}?v={{ @filemtime(public_path('assets/css/agent-modern.css')) ?: 1 }}" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style id="theme-vars">{!! app(\App\Services\ThemeService::class)->inlineStyleTag($__theme) !!}</style>
</head>
<body>
    <div class="ap-status-shell">
        <div class="ap-status-card">
            <i class="bi bi-slash-circle ap-status-icon" style="color: var(--danger-color);"></i>
            <h4>Account Suspended</h4>
            <p>Your agent account has been suspended. Please contact admin for more details.</p>
            <form method="POST" action="{{ route('agent.logout') }}">
                @csrf
                <button type="submit" class="ap-btn-outline">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </button>
            </form>
        </div>
    </div>
</body>
</html>
