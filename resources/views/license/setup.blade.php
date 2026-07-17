<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>License Setup — TravelBookingPanel</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
            max-width: 580px;
            width: 100%;
            padding: 2.5rem;
            box-shadow: 0 25px 50px rgba(0,0,0,.5);
        }
        .header { text-align: center; margin-bottom: 2rem; }
        .icon { font-size: 3rem; margin-bottom: 1rem; }
        h1 { font-size: 1.5rem; font-weight: 700; color: #f1f5f9; margin-bottom: .4rem; }
        .subtitle { color: #94a3b8; font-size: .9rem; line-height: 1.6; }

        .alert {
            border-radius: .5rem;
            padding: .75rem 1rem;
            font-size: .875rem;
            margin-bottom: 1.5rem;
        }
        .alert-error {
            background: rgba(239,68,68,.1);
            border: 1px solid rgba(239,68,68,.3);
            color: #fca5a5;
        }
        .alert-success {
            background: rgba(34,197,94,.1);
            border: 1px solid rgba(34,197,94,.3);
            color: #86efac;
        }
        .error-code {
            font-family: monospace;
            font-size: .78rem;
            color: #f87171;
            margin-top: .3rem;
        }

        .form-group { margin-bottom: 1.25rem; }
        label {
            display: block;
            font-size: .8rem;
            font-weight: 600;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: .04em;
            margin-bottom: .4rem;
        }
        input[type="text"], input[type="url"], input[type="password"] {
            width: 100%;
            background: #0f172a;
            border: 1px solid #334155;
            border-radius: .5rem;
            padding: .65rem .9rem;
            color: #f1f5f9;
            font-size: .95rem;
            font-family: monospace;
            outline: none;
            transition: border-color .2s;
        }
        input:focus { border-color: #3b82f6; }
        input::placeholder { color: #475569; }
        .field-hint { font-size: .78rem; color: #64748b; margin-top: .3rem; }

        .btn-row {
            display: flex;
            gap: .75rem;
            margin-top: 1.75rem;
        }
        button {
            flex: 1;
            padding: .7rem 1rem;
            border-radius: .5rem;
            font-size: .9rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: opacity .2s, background .2s;
        }
        button:disabled { opacity: .5; cursor: not-allowed; }
        .btn-test {
            background: #1e40af;
            color: #bfdbfe;
            border: 1px solid #2563eb;
            flex: 0 0 auto;
            padding: .7rem 1.25rem;
        }
        .btn-test:hover:not(:disabled) { background: #1d4ed8; }
        .btn-save {
            background: #166534;
            color: #bbf7d0;
            border: 1px solid #16a34a;
        }
        .btn-save:hover:not(:disabled) { background: #15803d; }

        .test-result {
            margin-top: 1rem;
            border-radius: .5rem;
            padding: .7rem 1rem;
            font-size: .85rem;
            display: none;
        }
        .test-result.ok  { background: rgba(34,197,94,.1); border: 1px solid rgba(34,197,94,.3); color: #86efac; }
        .test-result.err { background: rgba(239,68,68,.1);  border: 1px solid rgba(239,68,68,.3);  color: #fca5a5; }

        hr { border-color: #334155; margin: 1.75rem 0; }
        .info-list { list-style: none; }
        .info-list li {
            display: flex;
            gap: .6rem;
            font-size: .83rem;
            color: #64748b;
            margin-bottom: .4rem;
            align-items: flex-start;
        }
        .info-list li::before { content: '→'; color: #475569; flex-shrink: 0; }
        code {
            background: #0f172a;
            padding: .1em .35em;
            border-radius: .25rem;
            font-size: .82em;
            color: #7dd3fc;
        }
    </style>
</head>
<body>
<div class="card">
    <div class="header">
        <div class="icon">🔑</div>
        <h1>License Setup</h1>
        <p class="subtitle">Enter your license credentials to activate TravelBookingPanel.</p>
    </div>

    {{-- Error from POST --}}
    @if(session('error'))
        <div class="alert alert-error">
            {{ session('error') }}
            @if(session('error_code'))
                <div class="error-code">Code: {{ session('error_code') }}</div>
            @endif
        </div>
    @endif

    <form method="POST" action="{{ route('license.setup.save') }}" id="licenseForm">
        @csrf

        <div class="form-group">
            <label for="license_key">License Key</label>
            <input
                type="text"
                id="license_key"
                name="license_key"
                placeholder="XXXX-XXXX-XXXX-XXXX"
                value="{{ old('license_key', $current['license_key'] ?? '') }}"
                autocomplete="off"
                required
            >
            @error('license_key')
                <div class="field-hint" style="color:#f87171">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="license_api_key">API Key</label>
            <input
                type="text"
                id="license_api_key"
                name="license_api_key"
                placeholder="lak_xxxxxxxxxxxxxxxx"
                value="{{ old('license_api_key', $current['license_api_key'] ?? '') }}"
                autocomplete="off"
                required
            >
            <div class="field-hint">Must start with <code>lak_</code></div>
            @error('license_api_key')
                <div class="field-hint" style="color:#f87171">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="license_api_secret">API Secret</label>
            <input
                type="password"
                id="license_api_secret"
                name="license_api_secret"
                placeholder="Min 32 characters"
                autocomplete="off"
                required
            >
            @error('license_api_secret')
                <div class="field-hint" style="color:#f87171">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="license_server_url">License Server URL</label>
            <input
                type="url"
                id="license_server_url"
                name="license_server_url"
                placeholder="https://your-admin-panel.com"
                value="{{ old('license_server_url', $current['license_server_url'] ?? '') }}"
                required
            >
            <div class="field-hint">URL of the admin panel that validates licenses</div>
            @error('license_server_url')
                <div class="field-hint" style="color:#f87171">{{ $message }}</div>
            @enderror
        </div>

        <div id="testResult" class="test-result"></div>

        <div class="btn-row">
            <button type="button" class="btn-test" id="testBtn" onclick="testConnection()">
                Test Connection
            </button>
            <button type="submit" class="btn-save" id="saveBtn">
                Activate License
            </button>
        </div>
    </form>

    <hr>

    <ul class="info-list">
        <li>All four fields are required to activate the license.</li>
        <li>Use <strong>Test Connection</strong> to verify credentials before saving.</li>
        <li>Credentials are stored in <code>.env</code> and a local encrypted token in <code>storage/app/.license</code>.</li>
        <li>License is re-validated every 12 hours automatically.</li>
    </ul>
</div>

<script>
async function testConnection() {
    const btn    = document.getElementById('testBtn');
    const result = document.getElementById('testResult');

    const payload = {
        _token:             document.querySelector('meta[name="csrf-token"]').content,
        license_key:        document.getElementById('license_key').value.trim(),
        license_api_key:    document.getElementById('license_api_key').value.trim(),
        license_api_secret: document.getElementById('license_api_secret').value.trim(),
        license_server_url: document.getElementById('license_server_url').value.trim(),
    };

    if (!payload.license_key || !payload.license_api_key || !payload.license_api_secret || !payload.license_server_url) {
        result.className   = 'test-result err';
        result.style.display = 'block';
        result.textContent = 'Please fill in all fields before testing.';
        return;
    }

    btn.disabled    = true;
    btn.textContent = 'Testing…';
    result.style.display = 'none';

    try {
        const res  = await fetch('{{ route("license.setup.test") }}', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body:    JSON.stringify(payload),
        });
        const data = await res.json();

        if (data.status === 'valid') {
            result.className   = 'test-result ok';
            result.textContent = '✓ Connection successful! License is valid. You can now activate.';
        } else {
            result.className   = 'test-result err';
            result.textContent = '✗ ' + (data.message || 'Validation failed') + (data.code ? ' [' + data.code + ']' : '');
        }
    } catch (e) {
        result.className   = 'test-result err';
        result.textContent = '✗ Request failed: ' + e.message;
    }

    result.style.display = 'block';
    btn.disabled    = false;
    btn.textContent = 'Test Connection';
}
</script>
</body>
</html>
