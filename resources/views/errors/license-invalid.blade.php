<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>License Invalid — TravelBookingPanel</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #0f172a;
            color: #e2e8f0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .card {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 1rem;
            max-width: 560px;
            width: 100%;
            padding: 3rem 2.5rem;
            text-align: center;
            box-shadow: 0 25px 50px rgba(0,0,0,.5);
        }
        .icon { font-size: 3.5rem; margin-bottom: 1.5rem; }
        h1 { font-size: 1.6rem; font-weight: 700; color: #f1f5f9; margin-bottom: .5rem; }
        .subtitle { color: #94a3b8; margin-bottom: 1.5rem; font-size: .95rem; line-height: 1.6; }
        .reason-box {
            background: rgba(239,68,68,.1);
            border: 1px solid rgba(239,68,68,.3);
            border-radius: .5rem;
            padding: .75rem 1rem;
            font-family: monospace;
            font-size: .85rem;
            color: #fca5a5;
            margin-bottom: 1.5rem;
            text-align: left;
        }
        .label { font-size: .7rem; text-transform: uppercase; letter-spacing: .05em; color: #64748b; margin-bottom: .3rem; }
        .steps { text-align: left; margin-bottom: 2rem; }
        .steps h3 { font-size: .8rem; color: #64748b; text-transform: uppercase; letter-spacing: .05em; margin-bottom: .75rem; }
        .step { display: flex; gap: .75rem; margin-bottom: .6rem; }
        .n { min-width: 22px; height: 22px; background: #334155; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: .75rem; font-weight: 700; color: #94a3b8; flex-shrink: 0; margin-top: 1px; }
        .step p { font-size: .87rem; color: #94a3b8; line-height: 1.5; }
        code { background: #0f172a; padding: .1em .35em; border-radius: .25rem; font-size: .82em; color: #7dd3fc; }
        hr { border-color: #334155; margin: 1.5rem 0; }
        .contact { font-size: .82rem; color: #64748b; }
        .contact a { color: #2563eb; text-decoration: none; }
    </style>
</head>
<body>
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
