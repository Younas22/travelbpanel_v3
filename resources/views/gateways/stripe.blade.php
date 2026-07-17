<?php
use Stripe\Stripe;
use Stripe\Checkout\Session;

Stripe::setApiKey($payment_gatway['credential_2']);
$success_url = url($booking->module.'/payment/success?token='.$booking->booking_code_ref.'&gateway=stripe');
$cancel_url = route($booking->module.'.invoice', ['booking_ref' => $booking->booking_code_ref]);
$amount = intval($booking->booking_fare_base * 100);

try {
    $session = Session::create([
        'customer_email' => "usamam744@gmail.com",
        'payment_method_types' => ['card'],
        'mode' => 'payment',
        'line_items' => [[
            'price_data' => [
                'currency' => strtolower($booking->booking_currency_origin),
                'unit_amount' => $amount,
                'product_data' => [
                    'name' => $booking->module.' Booking',
                    'description' => $booking->module.' for Invoice ' . $booking->booking_code_ref,
                ],
            ],
            'quantity' => 1,
        ]],
        'success_url' => $success_url,
        'cancel_url' => $cancel_url,
    ]);

    $session_id = $session->id;

} catch (Exception $e) {
    die("Stripe Error: " . $e->getMessage());
}
?>

<script src="https://js.stripe.com/v3/"></script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        var stripe = Stripe("<?= $payment_gatway['credential_1'] ?>");

        stripe.redirectToCheckout({
            sessionId: "<?= $session_id; ?>"
        }).then(function (result) {
            if (result.error) {
                alert(result.error.message);
            }
        });
    });
</script>
