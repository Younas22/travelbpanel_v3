@extends('admin.layouts.app')

@section('title', 'Edit Payment Gateway')

@section('content')
<div class="content-area p-4 payment-gateway-edit">
    <!-- Page Header -->
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="mb-1">{{ucfirst($payment_gateway->name)}} Payment Gateway</h2>
                <p class="text-muted mb-0">Configure {{ucfirst($payment_gateway->name)}} payment gateway settings</p>
            </div>
            <div class="col-md-4">
                <div class="text-end">
                    <a href="{{ route('admin.settings.payment') }}" class="btn btn-outline-secondary modern-btn">
                        <i class="bi bi-arrow-left"></i> Back to Payment Settings
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Gateway Card -->
    <div class="card modern-card">
        <div class="card-body p-4">
            <!-- Gateway Header -->
            <div class="d-flex align-items-center mb-4">
                <div class="payment-logo logo-paypal me-3 payment-logo-generic-lg">
                    <img src="{{ url('public/assets/images/settings/payment/'.$payment_gateway->name.'.png') }}"
                        alt="{{$payment_gateway->name}}"
                        class="payment-logo-img">
                </div>
                <div>
                    <h4 class="mb-1">{{ucfirst($payment_gateway->name)}}</h4>
                    <span class="badge {{ $payment_gateway->status ? 'bg-success' : 'bg-secondary' }}">
                        {{ $payment_gateway->status ? 'Active' : 'Inactive' }}
                    </span>
                </div>
            </div>

            <hr>

            <!-- Edit Form -->
            <form id="paymentGatewayForm" onsubmit="savePaymentGateway(event)">
                @csrf
                <input type="hidden" name="gateway" value="{{$payment_gateway->name}}">

                <div class="config-section mb-4">
                    <div class="section-title mb-3">{{ucfirst($payment_gateway->name)}} API Integration</div>
                    <div class="section-description mb-4">Enter your {{ucfirst($payment_gateway->name)}} API credentials from your {{ucfirst($payment_gateway->name)}} Dashboard</div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label class="form-label required-field">Credential 1</label>
                                <input type="text" class="form-control credential-input"
                                       name="credential_1"
                                       value="{{ $payment_gateway->credential_1 ?? '' }}"
                                       placeholder="Enter credential 1">
                                <div class="form-text">Your {{ucfirst($payment_gateway->name)}} first credential</div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label class="form-label required-field">Credential 2</label>
                                <input type="text" class="form-control credential-input"
                                       name="credential_2"
                                       value="{{ $payment_gateway->credential_2 ?? '' }}"
                                       placeholder="Enter credential 2">
                                <div class="form-text">Your {{ucfirst($payment_gateway->name)}} second credential</div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label class="form-label">Credential 3</label>
                                <input type="text" class="form-control credential-input"
                                       name="credential_3"
                                       value="{{ $payment_gateway->credential_3 ?? '' }}"
                                       placeholder="Enter credential 3">
                                <div class="form-text">Your {{ucfirst($payment_gateway->name)}} third credential (optional)</div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label class="form-label">Credential 4</label>
                                <input type="text" class="form-control credential-input"
                                       name="credential_4"
                                       value="{{ $payment_gateway->credential_4 ?? '' }}"
                                       placeholder="Enter credential 4">
                                <div class="form-text">Your {{ucfirst($payment_gateway->name)}} fourth credential (optional)</div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label class="form-label">Credential 5</label>
                                <input type="text" class="form-control credential-input"
                                       name="credential_5"
                                       value="{{ $payment_gateway->credential_5 ?? '' }}"
                                       placeholder="Enter credential 5">
                                <div class="form-text">Your {{ucfirst($payment_gateway->name)}} fifth credential (optional)</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Development Mode Section -->
                <div class="dev-mode-section mb-4">
                    <div class="section-title mb-3">Payment Mode</div>
                    <div class="dev-mode-toggle">
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input"
                                   name="mode" id="{{$payment_gateway->name}}Mode" value="1"
                                   {{ ($payment_gateway->mode ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="{{$payment_gateway->name}}Mode">
                                <strong>Test Mode Enabled</strong>
                                <div class="small text-muted">Use test API keys for development</div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Active/Inactive Section -->
                <div class="dev-mode-section mb-4">
                    <div class="section-title mb-3">Gateway Status</div>
                    <div class="dev-mode-toggle">
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input"
                                   name="status" id="{{$payment_gateway->name}}Status" value="1"
                                   {{ ($payment_gateway->status ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="{{$payment_gateway->name}}Status">
                                <strong>Gateway Active</strong>
                                <div class="small text-muted">Enable/disable this payment gateway</div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Save Button -->
                <div class="save-section">
                    <button type="submit" class="btn btn-primary modern-btn">
                        <i class="bi bi-check-lg"></i> Save {{ucfirst($payment_gateway->name)}} Configuration
                    </button>
                    <a href="{{ route('admin.settings.payment') }}" class="btn btn-outline-secondary modern-btn ms-2">
                        <i class="bi bi-x"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function savePaymentGateway(event) {
    event.preventDefault();

    const form = event.target;
    const formData = new FormData(form);

    // Convert FormData to JSON
    const settings = {};
    for (let [key, value] of formData.entries()) {
        if (key !== 'gateway') {
            settings[key] = value;
        }
    }

    const gateway = formData.get('gateway');

    fetch('{{ route("admin.settings.payment.update") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            gateway: gateway,
            settings: settings
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(`${gateway} settings saved successfully!`, 'success');
            // Redirect back to payment settings after 1 second
            setTimeout(() => {
                window.location.href = '{{ route("admin.settings.payment") }}';
            }, 1000);
        } else {
            showNotification('Error saving settings: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred while saving settings', 'error');
    });
}

function showNotification(message, type = 'success') {
    // You can customize this based on your notification system
    alert(message);
}
</script>

@endsection
