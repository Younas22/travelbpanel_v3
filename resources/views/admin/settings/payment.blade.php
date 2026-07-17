@extends('admin.layouts.app')

@section('title', 'Payment Settings')

@section('content')
<div class="content-area p-4">
    <!-- Page Header -->
    {{--<div class="page-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="mb-1">Payment Settings</h2>
                <p class="text-muted mb-0">Configure payment gateways and processing methods</p>
            </div>
            <div class="col-md-4">
                <div class="text-end">
                    <button class="btn btn-success modern-btn" onclick="saveAllPaymentSettings()">
                        <i class="bi bi-check-lg"></i> Save All Changes
                    </button>
                </div>
            </div>
        </div>
    </div>--}}

    <!-- Payment Methods Container -->
    <div class="payment-methods-container">
        <div class="p-4">
            <h4 class="mb-3">Payment Gateways</h4>
            <p class="text-muted mb-4">Manage and configure your payment processing methods</p>

            <div class="row">
                @foreach($payment_gateways as $key=>$value)
                <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
                    <!-- Payment Method Card -->
                    <div class="payment-method-card h-100">
                        <div class="payment-header">
                            <div class="payment-info">
                                <div class="payment-logo logo-paypal"
                                    style="background:#fff; border-radius:25px; display:flex; align-items:center; justify-content:center; width:50px; height:50px; overflow:hidden;">
                                    <img src="{{ url('public/assets/images/settings/payment/'.$value->name.'.png') }}"
                                        alt="{{$value->name}}"
                                        style="width:100%; height:100%; object-fit:contain;">
                                </div>

                                <div class="payment-details">
                                    <h5>{{ucfirst($value->name)}}</h5>
                                </div>
                            </div>
                            <div class="payment-actions">
                                <div class="status-toggle">
                                    <label>
                                        <input type="checkbox"
                                            id="{{$value->name}}Status"
                                            {{ ($value->status ?? false) ? 'checked' : '' }}
                                            onchange="togglePaymentMethod({{$value->id}}, this.checked)">
                                        <span class="status-slider"></span>
                                    </label>
                                </div>

                                @if(strtolower($value->name) !== 'after_pay')
                                <a href="{{ route('admin.settings.payment.edit', $value->name) }}" class="config-btn">
                                    <i class="bi bi-gear"></i>
                                    Configure
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    window.togglePaymentMethod = function (gatewayId, enabled) {
        const statusValue = enabled ? '1' : '0';

        console.log('Gateway ID:', gatewayId);
        console.log('Status:', statusValue);

        fetch('{{ route("admin.settings.payment.update") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                gateway_id: gatewayId,
                status: statusValue
            })
        })
        .then(response => {
            console.log('Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            if (data.success) {
                showNotification(data.message || `Payment gateway ${enabled ? 'enabled' : 'disabled'} successfully!`, 'success');
            } else {
                showNotification(data.message || 'Error updating payment method status', 'error');
            }
        })
        .catch(error => {
            console.error('Error details:', error);
            showNotification('Error updating payment method status: ' + error.message, 'error');
        });
    }
});

// Show notification
function showNotification(message, type = 'success') {
    if (type === 'success') {
        alert('✓ ' + message);
    } else {
        alert('✗ ' + message);
    }
}
</script>

@endsection
