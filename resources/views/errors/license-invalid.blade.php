<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>License Invalid — TravelBookingPanel</title>
    <link rel="stylesheet" type="text/css" href="{{ url('public/assets/css/style.css') }}?v={{ filemtime(public_path('assets/css/style.css')) }}">
</head>
<body class="view-standalone view-license-invalid">
<div class="card">
    <div class="icon">🔒</div>
    <h1>License Validation Failed</h1>
    <p class="subtitle">
        System access has been blocked. Your license could not be verified with the license server.
    </p>

    @php
        $reason = request('reason', 'unknown');
        $messages = [
            'license_not_found'        => 'The license key does not exist.',
            'license_expired'          => 'Your license has expired.',
            'license_revoked'          => 'This license has been permanently revoked.',
            'license_suspended'        => 'This license is temporarily suspended.',
            'license_inactive'         => 'This license has not been activated yet.',
            'domain_not_allowed'       => 'This domain is not authorized for this license.',
            'ip_not_allowed'           => 'This server IP is not authorized for this license.',
            'activation_limit_reached' => 'Maximum activation limit has been reached.',
            'activation_revoked'       => 'This domain/IP activation slot was revoked.',
            'missing_config'           => 'LICENSE_KEY / API credentials not set in .env file.',
            'server_unreachable'       => 'Cannot connect to the license server.',
            'invalid_signature'        => 'Request signature mismatch — check LICENSE_API_SECRET.',
        ];
        $msg = $messages[$reason] ?? 'An unexpected license error occurred.';
    @endphp

    <div class="reason-box">
        <div class="label">Error Code</div>
        {{ $reason }}<br>
        <small style="color:#94a3b8;margin-top:.25rem;display:block">{{ $msg }}</small>
    </div>

    <div class="steps">
        <h3>Steps to fix</h3>
        <div class="step">
            <div class="n">1</div>
            <p>Open <code>.env</code> and verify these 4 keys are set:<br>
               <code>LICENSE_KEY</code> &nbsp; <code>LICENSE_API_KEY</code> &nbsp;
               <code>LICENSE_API_SECRET</code> &nbsp; <code>LICENSE_SERVER_URL</code>
            </p>
        </div>
        <div class="step">
            <div class="n">2</div>
            <p>Make sure <code>LICENSE_SERVER_URL</code> points to the admin panel, e.g.:<br>
               <code>https://travelbookingpanel.com</code>
            </p>
        </div>
        <div class="step">
            <div class="n">3</div>
            <p>Delete <code>storage/app/.license</code> and refresh to force revalidation.</p>
        </div>
        <div class="step">
            <div class="n">4</div>
            <p>Contact your administrator if the license should be active.</p>
        </div>
    </div>

    <hr>
    <p class="contact">Support: <a href="mailto:support@travelbookingpanel.com">support@travelbookingpanel.com</a></p>
</div>
</body>
</html>
