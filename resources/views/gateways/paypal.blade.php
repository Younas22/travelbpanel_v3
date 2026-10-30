<?php
$success_url = url($booking->module.'/payment/success?token='.$booking->booking_code_ref.'&gateway=paypal');
$cancel_url  = route($booking->module.'.invoice', ['booking_ref' => $booking->booking_code_ref]);
$amount      = number_format($booking->booking_fare_base, 2, '.', '');
$currency    = strtoupper($booking->booking_currency_origin);
?>

<link rel="stylesheet" type="text/css" href="{{ url('public/assets/css/style.css') }}?v={{ filemtime(public_path('assets/css/style.css')) }}">

<!-- PayPal SDK -->
<script src="https://www.paypal.com/sdk/js?client-id=<?= $payment_gatway['credential_1'] ?>&currency=<?= $currency ?>&intent=capture&disable-funding=credit,card&components=buttons"></script>

<div id="paypal-wrapper">

    <!-- Order Summary -->
    <div id="order-summary">
        <h3>Order Summary</h3>
        <div class="summary-row">
            <span>Booking Ref</span>
            <span><?= htmlspecialchars($booking->booking_code_ref) ?></span>
        </div>
        <div class="summary-row">
            <span>Base Fare</span>
            <span><?= $currency ?> <?= $amount ?></span>
        </div>
        <div class="summary-row total">
            <span>Total Payable</span>
            <span><?= $currency ?> <?= $amount ?></span>
        </div>
    </div>

    <!-- Status Message (shown on error/cancel) -->
    <div id="payment-message"></div>

    <!-- PayPal Button renders here — opens popup, user never leaves page -->
    <div id="paypal-button-container"></div>

</div>

<!-- Loading Overlay (shown after successful capture, before redirect) -->
<div id="loading-overlay">
    <div class="spinner"></div>
    <p>Confirming your payment, please wait...</p>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {

        if (typeof paypal === 'undefined') {
            showMessage('error', 'PayPal failed to load. Please refresh the page and try again.');
            return;
        }

        paypal.Buttons({
            style: {
                layout : 'vertical',
                color  : 'blue',
                shape  : 'rect',
                label  : 'pay',
                height : 45
            },

            // Called when PayPal popup opens — create order server-side or client-side
            createOrder: function (data, actions) {
                hideMessage();
                return actions.order.create({
                    intent: 'CAPTURE',
                    purchase_units: [{
                        reference_id: '<?= addslashes($booking->booking_code_ref) ?>',
                        description : 'Booking #<?= addslashes($booking->booking_code_ref) ?>',
                        amount: {
                            currency_code: '<?= $currency ?>',
                            value        : '<?= $amount ?>'
                        }
                    }],
                    application_context: {
                        shipping_preference: 'NO_SHIPPING', // hides shipping form in popup
                        user_action        : 'PAY_NOW'      // shows "Pay Now" instead of "Continue"
                    }
                });
            },

            // Called after user approves in popup — capture happens HERE, user never redirected
            onApprove: function (data, actions) {
                return actions.order.capture().then(function (details) {

                    // Validate capture status
                    if (details.status !== 'COMPLETED') {
                        showMessage('error', 'Payment was not completed. Please try again.');
                        return;
                    }

                    // Show loading overlay BEFORE redirect (only happens after success)
                    document.getElementById('loading-overlay').style.display = 'flex';

                    // Redirect to your success handler (server logs transaction)
                    window.location.href = '<?= $success_url ?>&transaction_id=' + details.id;
                });
            },

            // User closed the PayPal popup — stays on page, show friendly message
            onCancel: function (data) {
                showMessage('error', 'Payment cancelled. You can try again whenever you\'re ready.');
            },

            // Any SDK or network error
            onError: function (err) {
                console.error('PayPal SDK Error:', err);
                showMessage('error', 'Something went wrong. Please refresh and try again, or contact support.');
            }

        }).render('#paypal-button-container');

        // --- Helpers ---
        function showMessage(type, text) {
            var el = document.getElementById('payment-message');
            el.className = 'payment-message ' + type;
            el.textContent = text;
            el.style.display = 'block';
            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        function hideMessage() {
            var el = document.getElementById('payment-message');
            el.style.display = 'none';
        }

    });
</script>
