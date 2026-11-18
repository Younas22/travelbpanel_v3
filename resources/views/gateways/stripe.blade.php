<?php
use Stripe\Stripe;
use Stripe\Checkout\Session;

Stripe::setApiKey($payment_gatway['credential_2']);
$success_url = url($booking->module.'/payment/success?token='.$booking->booking_code_ref.'&gateway=stripe');
$cancel_url = route($booking->module.'.invoice', ['booking_ref' => $booking->booking_code_ref]);
// Email of the customer paying: booking's user data (hotel/tour: "email", flight: "user_email",
// sometimes JSON-encoded twice), else the booking's user account, else the logged-in user.
$customerEmail = null;
$userData = $booking->booking_user_data ?? null;
for ($i = 0; $i < 2 && is_string($userData); $i++) {
    $userData = json_decode($userData, true);
}
if (is_object($userData)) $userData = (array) $userData;
foreach (['email', 'user_email'] as $key) {
    if (!empty($userData[$key])) { $customerEmail = $userData[$key]; break; }
}
if (!$customerEmail && !empty($booking->user_id)) {
    $customerEmail = \App\Models\User::whereKey($booking->user_id)->value('email');
}
$customerEmail = $customerEmail ?: auth()->user()?->email;
$customerEmail = filter_var(trim((string) $customerEmail), FILTER_VALIDATE_EMAIL) ?: null;

$amount = (int) round($booking->booking_fare_base * 100); // round: intval(1788.82*100) can give 178881

try {
    $session = Session::create(array_filter([
        'customer_email' => $customerEmail, // the paying customer's own email (left out when unknown)
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
    ]));

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
