<?php
$success_url = url($booking->module.'/payment/success?token='.$booking->booking_code_ref.'&gateway=paypal');
$cancel_url  = route($booking->module.'.invoice', ['booking_ref' => $booking->booking_code_ref]);
$amount      = number_format($booking->booking_fare_base, 2, '.', '');
$currency    = strtoupper($booking->booking_currency_origin);
?>

<style>
    #paypal-wrapper {
        max-width: 480px;
        margin: 30px auto;
        font-family: Arial, sans-serif;
    }

    /* Step 1: Order Summary Card */
    #order-summary {
        background: #f9f9f9;
        border: 1px solid #e0e0e0;
        border-radius: 10px;
        padding: 24px;
        margin-bottom: 20px;
    }
    #order-summary h3 {
        margin: 0 0 16px;
        font-size: 18px;
        color: #333;
    }
    .summary-row {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px solid #eee;
        font-size: 15px;
        color: #555;
    }
    .summary-row:last-child { border-bottom: none; }
    .summary-row.total {
        font-weight: bold;
        font-size: 17px;
        color: #222;
        margin-top: 8px;
    }

    /* Step 2: PayPal Button Area */
    #paypal-button-container {
        margin-top: 10px;
    }

    /* Loading Overlay */
    #loading-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(255,255,255,0.85);
        z-index: 9999;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        font-size: 18px;
        color: #333;
    }
    .spinner {
        width: 48px; height: 48px;
        border: 5px solid #ddd;
        border-top-color: #0070ba;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
        margin-bottom: 16px;
    }
    @keyframes spin { to { transform: rotate(360deg); } }

    /* Status Messages */
    #payment-message {
        display: none;
        padding: 14px 18px;
        border-radius: 8px;
        margin-bottom: 16px;
        font-size: 15px;
    }
    #payment-message.success { background: #e6f4ea; color: #2e7d32; border: 1px solid #a5d6a7; }
    #payment-message.error   { background: #fdecea; color: #c62828; border: 1px solid #ef9a9a; }
</style>

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
