@extends('admin-nova.layouts.app')

@section('title', 'Email Settings')

@section('content')
@php
    $connected = ($settings['api_status'] ?? 'disconnected') === 'connected';
@endphp
<div class="tbp-email-settings-page">

    <div class="tbp-email-settings-header">
        <div>
            <h1>Email Settings</h1>
            <p>Configure email delivery settings and test email functionality</p>
        </div>
        <button type="button" class="tbp-email-settings-btn" onclick="saveEmailSettings()"><i class="bi bi-check-lg"></i> Save Settings</button>
    </div>

    {{-- ===== API CREDENTIALS ===== --}}
    <section class="tbp-email-settings-card">
        <header class="tbp-email-settings-card-head">
            <span class="tbp-email-settings-icon"><i class="bi bi-key"></i></span>
            <div><h2>API Credentials</h2><p>Configure your Resend API credentials to enable email delivery for booking confirmations, newsletters, and notifications.</p></div>
        </header>

        <div class="tbp-email-settings-provider">
            <div class="tbp-email-settings-provider-row">
                <i class="bi bi-envelope-paper"></i>
                <div>
                    <strong>Resend</strong>
                    <p>Please signup at Resend and get your API keys to use below. Resend is a reliable email delivery service that ensures your emails reach customers' inboxes.</p>
                    <a href="https://resend.com/signup" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right"></i> Sign up at Resend</a>
                </div>
            </div>
            <div class="tbp-email-settings-status {{ $connected ? 'connected' : 'disconnected' }}" id="apiStatus">
                <span class="status-indicator"></span>
                <div>
                    <strong>API Status: {{ $connected ? 'Connected' : 'Disconnected' }}</strong>
                    <div class="small">{{ $connected ? 'Email service is ready and configured' : 'Please enter your API credentials to connect' }}</div>
                </div>
            </div>
        </div>

        <form id="apiCredentialsForm" class="tbp-email-settings-form" onsubmit="saveApiCredentials(event)">
            @csrf
            <label class="tbp-email-settings-field"><span>API Key <b>*</b></span>
                <input type="text" id="apiKey" name="resend_api_key" value="{{ $settings['resend_api_key'] ?? '' }}" placeholder="re_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" required autocomplete="off">
                <small>Your Resend API key (starts with "re_")</small></label>
            <div class="tbp-email-settings-grid">
                <label class="tbp-email-settings-field"><span><i class="bi bi-person"></i> Sender Name <b>*</b></span>
                    <input type="text" id="senderName" name="sender_name" value="{{ $settings['sender_name'] ?? 'SkyBooking Support' }}" placeholder="Your Company Name" required>
                    <small>Name that appears in recipient's inbox</small></label>
                <label class="tbp-email-settings-field"><span><i class="bi bi-envelope"></i> Sender Email <b>*</b></span>
                    <input type="email" id="senderEmail" name="sender_email" value="{{ $settings['sender_email'] ?? 'noreply@skybooking.com' }}" placeholder="noreply@yourdomain.com" required>
                    <small>Must be from your verified domain</small></label>
            </div>
            <div class="tbp-email-settings-buttons">
                <button type="button" class="tbp-email-settings-btn is-outline" onclick="testConnection(this)"><i class="bi bi-send"></i> <span>Test Connection</span></button>
                <button type="submit" class="tbp-email-settings-btn"><i class="bi bi-check-lg"></i> <span>Save API Settings</span></button>
            </div>
        </form>
    </section>

    {{-- ===== EMAIL TESTING ===== --}}
    <section class="tbp-email-settings-card">
        <header class="tbp-email-settings-card-head">
            <span class="tbp-email-settings-icon"><i class="bi bi-send"></i></span>
            <div><h2>Email Testing</h2><p>Test your email configuration by sending a test email to verify that everything is working correctly.</p></div>
        </header>
        <form id="testEmailForm" class="tbp-email-settings-test" onsubmit="sendTestEmail(event)">
            @csrf
            <label class="tbp-email-settings-field"><span>Test Email Address <b>*</b></span>
                <span class="tbp-email-settings-input-group"><i class="bi bi-at"></i>
                    <input type="email" id="testEmail" name="test_email" value="{{ $settings['last_test_email'] ?? '' }}" placeholder="your.email@example.com" required></span>
                <small>Enter your email address to receive the test email</small></label>
            <button type="submit" class="tbp-email-settings-btn" id="sendTestBtn" @disabled(empty($settings['resend_api_key']))><i class="bi bi-send"></i> <span>Send Test Email</span></button>
        </form>
        <div id="testResult" class="tbp-email-settings-result" hidden>
            <strong id="testResultMessage"></strong>
            <span id="testResultDetails"></span>
        </div>
    </section>

    {{-- ===== SAVE BAR ===== --}}
    <section class="tbp-email-settings-card tbp-email-settings-savebar">
        <span class="tbp-email-settings-icon"><i class="bi bi-gear"></i></span>
        <div><h2>Email Configuration</h2><p>Make sure to save your settings after making any changes</p></div>
        <button type="button" class="tbp-email-settings-btn" onclick="saveEmailSettings()"><i class="bi bi-check-lg"></i> Save All Email Settings</button>
    </section>

    <div class="tbp-website-settings-toast" id="tbpEmailToast" role="status" hidden></div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var csrf = document.querySelector('meta[name="csrf-token"]').content;
    var apiKey = document.getElementById('apiKey'), sendBtn = document.getElementById('sendTestBtn');

    function busy(btn, text) {
        var label = btn.querySelector('span');
        btn.dataset.label = label.textContent; label.textContent = text; btn.disabled = true;
    }
    function idle(btn) { btn.querySelector('span').textContent = btn.dataset.label; btn.disabled = false; }

    function notify(message, type) {
        var t = document.getElementById('tbpEmailToast');
        t.textContent = message;
        t.className = 'tbp-website-settings-toast is-' + (type || 'success');
        t.hidden = false;
        clearTimeout(t._t); t._t = setTimeout(function () { t.hidden = true; }, 3500);
    }

    function updateApiStatus(status, message) {
        var box = document.getElementById('apiStatus');
        box.className = 'tbp-email-settings-status ' + status;
        box.querySelector('strong').textContent = 'API Status: ' + (status === 'connected' ? 'Connected' : 'Disconnected');
        box.querySelector('.small').textContent = message;
    }

    window.testConnection = function (btn) {
        var key = apiKey.value;
        if (!key) return notify('Please enter an API key first', 'error');
        if (!key.startsWith('re_')) return notify('API key must start with "re_"', 'error');
        busy(btn, 'Testing…');
        fetch(@json(route('admin.settings.email.test-connection')), {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ api_key: key })
        }).then(function (r) { return r.json(); }).then(function (data) {
            if (data.success) { updateApiStatus('connected', 'API connection successful!'); notify('API connection successful!'); sendBtn.disabled = false; }
            else { updateApiStatus('disconnected', data.message); notify('Connection failed: ' + data.message, 'error'); }
        }).catch(function () {
            updateApiStatus('disconnected', 'Connection error occurred'); notify('Connection error occurred', 'error');
        }).finally(function () { idle(btn); });
    };

    window.saveApiCredentials = function (event) {
        event.preventDefault();
        var form = event.target, btn = form.querySelector('button[type="submit"]');
        if (!form.reportValidity()) return;
        busy(btn, 'Saving…');
        fetch(@json(route('admin.settings.email.update')), {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            body: new FormData(form)
        }).then(function (r) { return r.json(); }).then(function (data) {
            if (data.success) { updateApiStatus('connected', 'Email service is ready and configured'); notify('Email settings saved successfully!'); sendBtn.disabled = false; }
            else notify('Error saving settings: ' + (data.message || 'please check the fields'), 'error');
        }).catch(function () { notify('An error occurred while saving settings', 'error'); })
          .finally(function () { idle(btn); });
    };

    window.sendTestEmail = function (event) {
        event.preventDefault();
        var form = event.target, btn = form.querySelector('button[type="submit"]'), email = document.getElementById('testEmail').value;
        if (!email) return notify('Please enter a test email address', 'error');
        busy(btn, 'Sending…');
        fetch(@json(route('admin.settings.email.send-test')), {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            body: new FormData(form)
        }).then(function (r) { return r.json(); }).then(function (data) {
            var box = document.getElementById('testResult');
            box.className = 'tbp-email-settings-result ' + (data.success ? 'is-success' : 'is-error');
            document.getElementById('testResultMessage').textContent = data.success ? 'Test email sent successfully!' : 'Failed to send test email';
            document.getElementById('testResultDetails').textContent = data.success ? 'Sent to ' + email + '. Please check your inbox (and spam folder).' : data.message;
            box.hidden = false;
            notify(data.success ? 'Test email sent successfully!' : 'Failed to send test email', data.success ? 'success' : 'error');
        }).catch(function () { notify('An error occurred while sending test email', 'error'); })
          .finally(function () { idle(btn); });
    };

    window.saveEmailSettings = function () {
        document.getElementById('apiCredentialsForm').requestSubmit();
    };

    apiKey.addEventListener('input', function () { sendBtn.disabled = !(this.value && this.value.startsWith('re_')); });
})();
</script>
@endpush
