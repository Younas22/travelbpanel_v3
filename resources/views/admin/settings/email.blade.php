@extends('admin.layouts.app')

@section('title', 'Email Settings')

@section('content')
<div class="content-area p-4">
    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="mb-1">Email Settings</h2>
                <p class="text-muted mb-0">Configure email delivery settings and test email functionality</p>
            </div>
            <div class="col-md-4">
                <div class="text-end">
                    <button class="btn btn-success modern-btn" onclick="saveEmailSettings()">
                        <i class="bi bi-check-lg"></i> Save Settings
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Email Settings Container -->
    <div class="email-settings-container p-4">
        <!-- API Credentials Section -->
        <div class="settings-section">
            <div class="section-title">
                <div class="section-icon">
                    <i class="bi bi-key"></i>
                </div>
                API Credentials
            </div>
            <div class="section-description">
                Configure your Resend API credentials to enable email delivery for booking confirmations, newsletters, and notifications.
            </div>

            <!-- Resend Information -->
            <div class="resend-info">
                <div class="resend-logo">
                    <i class="bi bi-envelope-paper"></i> Resend
                </div>
                <div class="resend-description">
                    Please signup at Resend and get your API Keys to use below. Resend is a reliable email delivery service that ensures your emails reach customers' inboxes.
                </div>
                <a href="https://resend.com/signup" target="_blank" class="resend-link">
                    <i class="bi bi-box-arrow-up-right"></i>
                    Sign up at Resend
                </a>
            </div>

            <!-- API Status -->
            <div class="api-status {{ ($settings['api_status'] ?? 'disconnected') == 'connected' ? 'connected' : 'disconnected' }}" id="apiStatus">
                <div class="status-indicator status-{{ ($settings['api_status'] ?? 'disconnected') == 'connected' ? 'connected' : 'disconnected' }}"></div>
                <div>
                    <strong>API Status: {{ ($settings['api_status'] ?? 'disconnected') == 'connected' ? 'Connected' : 'Disconnected' }}</strong>
                    <div class="small">
                        @if(($settings['api_status'] ?? 'disconnected') == 'connected')
                            Email service is ready and configured
                        @else
                            Please enter your API credentials to connect
                        @endif
                    </div>
                </div>
            </div>

            <!-- API Form -->
            <form id="apiCredentialsForm" class="modern-form" onsubmit="saveApiCredentials(event)">
                @csrf
                <div class="row">
                    <div class="col-12">
                        <div class="form-group">
                            <label class="form-label required-field">
                                <i class="bi bi-key"></i>
                                API Key
                            </label>
                            <input type="text" class="form-control api-key-input" 
                                   id="apiKey" name="resend_api_key" 
                                   value="{{ $settings['resend_api_key'] ?? '' }}"
                                   placeholder="re_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" required>
                            <div class="form-text">Your Resend API key (starts with 're_')</div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label required-field">
                                <i class="bi bi-person"></i>
                                Sender Name
                            </label>
                            <input type="text" class="form-control" 
                                   id="senderName" name="sender_name" 
                                   value="{{ $settings['sender_name'] ?? 'SkyBooking Support' }}"
                                   placeholder="Your Company Name" required>
                            <div class="form-text">Name that appears in recipient's inbox</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label required-field">
                                <i class="bi bi-envelope"></i>
                                Sender Email
                            </label>
                            <input type="email" class="form-control" 
                                   id="senderEmail" name="sender_email" 
                                   value="{{ $settings['sender_email'] ?? 'noreply@skybooking.com' }}"
                                   placeholder="noreply@yourdomain.com" required>
                            <div class="form-text">Must be from your verified domain</div>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-primary modern-btn" onclick="testConnection()">
                        <i class="bi bi-wifi"></i> Test Connection
                    </button>
                    <button type="submit" class="btn btn-primary modern-btn">
                        <i class="bi bi-check-lg"></i> Save API Settings
                    </button>
                </div>
            </form>
        </div>

        <!-- Email Testing Section -->
        <div class="settings-section">
            <div class="section-title">
                <div class="section-icon">
                    <i class="bi bi-send"></i>
                </div>
                Email Testing
            </div>
            <div class="section-description">
                Test your email configuration by sending a test email to verify that everything is working correctly.
            </div>

            <div class="test-section">
                <div class="test-title">
                    <i class="bi bi-envelope-check"></i> Send Test Email
                </div>

                <div class="test-instructions">
                    <div class="d-flex">
                        <i class="bi bi-info-circle-fill alert-icon"></i>
                        <div>
                            <strong>Testing Instructions:</strong><br>
                            To test email, please add the API key in the above form and add your email below, then hit the <strong>"Send Test Email"</strong> button to check email in your inbox. If you still don't receive the email within 5 minutes, please check that the API keys are correct.
                        </div>
                    </div>
                </div>

                <form id="testEmailForm" class="modern-form" onsubmit="sendTestEmail(event)">
                    @csrf
                    <div class="row align-items-end">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label class="form-label required-field">
                                    <i class="bi bi-envelope"></i>
                                    Test Email Address
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-at"></i>
                                    </span>
                                    <input type="email" class="form-control" 
                                           id="testEmail" name="test_email" 
                                           value="{{ $settings['last_test_email'] ?? '' }}"
                                           placeholder="your.email@example.com" required>
                                </div>
                                <div class="form-text">Enter your email address to receive the test email</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <button type="submit" class="btn send-test-btn modern-btn w-100" 
                                        id="sendTestBtn" {{ empty($settings['resend_api_key']) ? 'disabled' : '' }}>
                                    <i class="bi bi-send"></i> Send Test Email
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Test Result -->
                <div id="testResult" class="test-result" style="display: none;">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-check-circle-fill me-2" id="testResultIcon"></i>
                        <strong id="testResultMessage">Test email sent successfully!</strong>
                    </div>
                    <div class="small mt-1" id="testResultDetails">Please check your inbox (and spam folder) for the test email.</div>
                </div>
            </div>
        </div>

        <!-- Email Statistics Section -->
        @if(!empty($settings['resend_api_key']))
        <div class="settings-section d-none">
            <div class="section-title">
                <div class="section-icon">
                    <i class="bi bi-graph-up"></i>
                </div>
                Email Statistics
            </div>
            <div class="section-description">
                Overview of your email delivery performance and usage.
            </div>

            <div class="row g-4">
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="stats-icon icon-blue">
                            <i class="bi bi-envelope"></i>
                        </div>
                        <div class="stats-content">
                            <div class="stats-value">{{ $stats['total_sent'] ?? 0 }}</div>
                            <div class="stats-label">Total Emails Sent</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="stats-icon icon-green">
                            <i class="bi bi-check-circle"></i>
                        </div>
                        <div class="stats-content">
                            <div class="stats-value">{{ $stats['delivered'] ?? 0 }}</div>
                            <div class="stats-label">Delivered</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="stats-icon icon-orange">
                            <i class="bi bi-eye"></i>
                        </div>
                        <div class="stats-content">
                            <div class="stats-value">{{ $stats['opened'] ?? 0 }}</div>
                            <div class="stats-label">Opened</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="stats-icon icon-red">
                            <i class="bi bi-x-circle"></i>
                        </div>
                        <div class="stats-content">
                            <div class="stats-value">{{ $stats['bounced'] ?? 0 }}</div>
                            <div class="stats-label">Bounced</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Save Section -->
        <div class="save-section">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="text-start">
                        <h6 class="mb-1">Email Configuration</h6>
                        <small class="text-muted">Make sure to save your settings after making any changes</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <button class="btn btn-success modern-btn" onclick="saveEmailSettings()">
                        <i class="bi bi-check-lg"></i> Save All Email Settings
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Test API connection
function testConnection() {
    const apiKey = document.getElementById('apiKey').value;
    
    if (!apiKey) {
        showNotification('Please enter an API key first', 'error');
        return;
    }
    
    if (!apiKey.startsWith('re_')) {
        showNotification('API key must start with "re_"', 'error');
        return;
    }
    
    // Show loading state
    const testBtn = event.target;
    const originalText = testBtn.innerHTML;
    testBtn.innerHTML = '<i class="bi bi-spinner-border spinner-border-sm me-2"></i>Testing...';
    testBtn.disabled = true;
    
    fetch('{{ route("admin.settings.email.test-connection") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            api_key: apiKey
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateApiStatus('connected', 'API connection successful!');
            showNotification('API connection successful!', 'success');
            // Enable test email button
            document.getElementById('sendTestBtn').disabled = false;
        } else {
            updateApiStatus('disconnected', data.message);
            showNotification('Connection failed: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        updateApiStatus('disconnected', 'Connection error occurred');
        showNotification('Connection error occurred', 'error');
    })
    .finally(() => {
        // Restore button state
        testBtn.innerHTML = originalText;
        testBtn.disabled = false;
    });
}

// Save API credentials
function saveApiCredentials(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    
    // Show loading state
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="bi bi-spinner-border spinner-border-sm me-2"></i>Saving...';
    submitBtn.disabled = true;
    
    fetch('{{ route("admin.settings.email.update") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateApiStatus('connected', 'Email service is ready and configured');
            showNotification('Email settings saved successfully!', 'success');
            // Enable test email button
            document.getElementById('sendTestBtn').disabled = false;
        } else {
            showNotification('Error saving settings: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred while saving settings', 'error');
    })
    .finally(() => {
        // Restore button state
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

// Send test email
function sendTestEmail(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    const testEmail = document.getElementById('testEmail').value;
    
    if (!testEmail) {
        showNotification('Please enter a test email address', 'error');
        return;
    }
    
    // Show loading state
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="bi bi-spinner-border spinner-border-sm me-2"></i>Sending...';
    submitBtn.disabled = true;
    
    fetch('{{ route("admin.settings.email.send-test") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        const testResult = document.getElementById('testResult');
        const testResultIcon = document.getElementById('testResultIcon');
        const testResultMessage = document.getElementById('testResultMessage');
        const testResultDetails = document.getElementById('testResultDetails');
        
        if (data.success) {
            testResult.className = 'test-result success';
            testResultIcon.className = 'bi bi-check-circle-fill me-2';
            testResultMessage.textContent = 'Test email sent successfully!';
            testResultDetails.textContent = `Test email sent to ${testEmail}. Please check your inbox (and spam folder).`;
            showNotification('Test email sent successfully!', 'success');
        } else {
            testResult.className = 'test-result error';
            testResultIcon.className = 'bi bi-x-circle-fill me-2';
            testResultMessage.textContent = 'Failed to send test email';
            testResultDetails.textContent = data.message;
            showNotification('Failed to send test email: ' + data.message, 'error');
        }
        
        testResult.style.display = 'block';
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred while sending test email', 'error');
    })
    .finally(() => {
        // Restore button state
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

// Save all email settings
function saveEmailSettings() {
    // Trigger form submission for API credentials
    const apiForm = document.getElementById('apiCredentialsForm');
    if (apiForm) {
        apiForm.dispatchEvent(new Event('submit'));
    }
}

// Update API status display
function updateApiStatus(status, message) {
    const apiStatus = document.getElementById('apiStatus');
    const statusIndicator = apiStatus.querySelector('.status-indicator');
    const statusText = apiStatus.querySelector('strong');
    const statusMessage = apiStatus.querySelector('.small');
    
    // Update classes
    apiStatus.className = `api-status ${status}`;
    statusIndicator.className = `status-indicator status-${status}`;
    
    // Update text
    statusText.textContent = `API Status: ${status === 'connected' ? 'Connected' : 'Disconnected'}`;
    statusMessage.textContent = message;
}

// Show notification
function showNotification(message, type = 'success') {
    // You can customize this based on your notification system
    // For now, using a simple alert
    if (type === 'success') {
        alert('✅ ' + message);
    } else if (type === 'error') {
        alert('❌ ' + message);
    } else {
        alert(message);
    }
}

// Auto-enable test button when API key is entered
document.getElementById('apiKey').addEventListener('input', function() {
    const testBtn = document.getElementById('sendTestBtn');
    if (this.value && this.value.startsWith('re_')) {
        testBtn.disabled = false;
    } else {
        testBtn.disabled = true;
    }
});

// Page load initialization
document.addEventListener('DOMContentLoaded', function() {
    // Enable test button if API key exists
    const apiKey = document.getElementById('apiKey').value;
    const testBtn = document.getElementById('sendTestBtn');
    
    if (apiKey && apiKey.startsWith('re_')) {
        testBtn.disabled = false;
    }
});
</script>

@endsection

