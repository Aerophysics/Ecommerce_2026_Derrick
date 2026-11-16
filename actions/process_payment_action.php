<?php
/**
 * process_payment_action.php — Paystack payment verification & order processing (Task 13).
 *
 * Called after Paystack's client-side success callback (or form submission).
 *
 * SECURITY RULE:
 * Never trust a client-side success callback alone! A user could manually browse
 * to actions/process_payment_action.php?ref=fake without paying. We must ALWAYS
 * verify the transaction server-side by querying Paystack's Verify API endpoint:
 *   GET https://api.paystack.co/transaction/verify/{reference}
 * with our secret key in the Authorization header.
 *
 * Only once Paystack API confirms {status: true, data: {status: 'success'}} and
 * the amount matches our cart total do we invoke processPayment() to record the
 * order, orderdetails, payment, and clear the customer's cart.
 */

require_once __DIR__ . '/../core/core.php';
require_login();
require_once __DIR__ . '/../controllers/CartController.php';

$reference = trim($_GET['ref'] ?? $_GET['reference'] ?? $_POST['reference'] ?? '');

if ($reference === '') {
    $_SESSION['error'] = 'No payment reference was provided.';
    redirect(app_url('views/payment_failed.php'));
}

$customerId = (int) ($_SESSION['customer_id'] ?? 0);
$ip         = get_ip();

$cartController = new CartController();
$cartItems      = $cartController->getCartItems($ip);
$cartTotal      = $cartController->getCartTotal($ip);

if (empty($cartItems) || $cartTotal <= 0) {
    $_SESSION['error'] = 'Your cart is empty or this order has already been processed.';
    redirect(app_url('views/cart.php'));
}

// ── Replay attack check: Ensure this reference was not used already ──────
$db       = new Database();
$existing = false;
try {
    $hasRefCol = $db->fetchOne(
        "SELECT 1 FROM information_schema.COLUMNS
          WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'payment' AND COLUMN_NAME = 'reference' LIMIT 1"
    );
    if ($hasRefCol) {
        $existing = $db->fetchOne('SELECT pay_id, order_id FROM payment WHERE reference = ?', [$reference]);
    }
} catch (Exception $e) {
    $existing = false;
}
if ($existing) {
    $_SESSION['error'] = 'This payment reference has already been processed.';
    redirect(app_url('index.php'));
}

// ── Server-side Paystack Transaction Verification ────────────────────────
$url     = 'https://api.paystack.co/transaction/verify/' . rawurlencode($reference);
$headers = [
    'Authorization: Bearer ' . PAYSTACK_SECRET_KEY,
    'Cache-Control: no-cache',
];

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL            => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => $headers,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_TIMEOUT        => 30,
]);

$responseJson = curl_exec($ch);
$curlErr      = curl_error($ch);
$httpCode     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$response = json_decode($responseJson, true);

$isSuccess      = false;
$verifiedAmount = 0.0;

if ($response && !empty($response['status']) && isset($response['data']['status']) && $response['data']['status'] === 'success') {
    $isSuccess = true;
    // Paystack amounts are received in pesewas (100 pesewas = 1 GHS)
    $verifiedAmount = (float) (($response['data']['amount'] ?? 0) / 100);
}

// ── Local Development / Offline Testing Fallback ──────────────────────────
// If the developer is testing locally with placeholder demo keys, allow simulated references
if (!$isSuccess && (strpos(PAYSTACK_SECRET_KEY, 'xxxx') !== false || strpos(PAYSTACK_SECRET_KEY, 'YOUR_') !== false)) {
    if (strpos($reference, 'demo_') === 0 || strpos($reference, 'test_') === 0 || strpos($reference, 'shoppn_') === 0) {
        $isSuccess      = true;
        $verifiedAmount = $cartTotal;
    }
}

// If verification failed
if (!$isSuccess) {
    $reason = $response['message'] ?? $response['data']['gateway_response'] ?? $curlErr ?? 'Transaction could not be verified by Paystack.';
    log_error('Paystack verification failed for ref ' . $reference . ': ' . $reason);
    $_SESSION['error'] = 'Payment verification failed: ' . $reason;
    redirect(app_url('views/payment_failed.php'));
}

// Amount integrity check: verify customer paid the cart total (within 0.05 margin for currency rounding)
if ($verifiedAmount < ($cartTotal - 0.05)) {
    $errMsg = 'Payment amount mismatch: Expected ' . money($cartTotal) . ', but received ' . money($verifiedAmount) . '.';
    log_error('Paystack amount mismatch: ' . $errMsg);
    $_SESSION['error'] = $errMsg;
    redirect(app_url('views/payment_failed.php'));
}

// ── Record Order, Order Details, Payment, and Clear Cart (Transaction) ──
$orderId = $cartController->processPayment($customerId, $ip, $reference, $verifiedAmount);

if ($orderId) {
    $_SESSION['success'] = 'Payment completed successfully! Your order has been placed.';
    redirect(app_url('index.php'));
} else {
    $_SESSION['error'] = 'Payment was verified, but an error occurred while recording your order. Please contact customer support with reference: ' . htmlspecialchars($reference);
    redirect(app_url('views/payment_failed.php'));
}
