<?php
$amount = intval($booking->booking_fare_base * 100);

$request       = "authorization";
$portalid      = 2046245;
$aid           = 64215;
$key           = "1e9febc9-9ab2-4da7-8fdc-ed4c48c30f03";

$currency      = "EUR";
$reference     = $booking->booking_code_ref;
$customerid    = "";
$clearingtype  = "cc";
$mode          = "test";
$ecommercemode = "3ds";

$successurl =url($booking->module.'/payment/success/'.$booking->booking_code_ref.'/payone');
$errorurl = route($booking->module.'.invoice', ['booking_ref' => $booking->booking_code_ref]);


$id[1] = "123-345";
$pr[1] = $amount;
$no[1] = 1;
$de[1] = "Puma Outdoor";
$va[1] = 19;

$amount = $pr[1] * $no[1];

// Build hash string EXACTLY in alphabetical order
$hash_string =
    $aid .
    $amount .
    $clearingtype .
    $currency .
    $customerid .
    $de[1] .
    $ecommercemode .
    $errorurl .
    $id[1] .
    $mode .
    $no[1] .
    $portalid .
    $pr[1] .
    $reference .
    $request .
    $successurl .
    $va[1];

$hash = hash_hmac('sha384', $hash_string, $key);

$url = "https://frontend.pay1.de/frontend/v2/?"
    . "request=$request"
    . "&aid=$aid"
    . "&portalid=$portalid"
    . "&customerid=$customerid"
    . "&currency=$currency"
    . "&amount=$amount"
    . "&reference=$reference"
    . "&clearingtype=$clearingtype"
    . "&mode=$mode"
    . "&ecommercemode=$ecommercemode"
    . "&successurl=$successurl"
    . "&errorurl=$errorurl"
    . "&id[1]=$id[1]"
    . "&pr[1]=$pr[1]"
    . "&no[1]=$no[1]"
    . "&de[1]=$de[1]"
    . "&va[1]=$va[1]"
    . "&hash=$hash";

header("Location: $url");
exit;
?>

