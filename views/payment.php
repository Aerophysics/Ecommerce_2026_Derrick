<?php
/**
 * payment.php — Paystack payment page (Task 13).
 *
 * Displays order/cart summary and initiates Paystack Inline checkout popup.
 * In MVC, views interact with controllers, not models directly.
 */

require_once __DIR__ . '/../core/core.php';
require_login();
require_once __DIR__ . '/../controllers/CartController.php';
require_once __DIR__ . '/../controllers/CustomerController.php';

$controller = new CartController();
$ip         = get_ip();
$items      = $controller->getCartItems($ip);
$total      = $controller->getCartTotal($ip);

if (!$items || $total <= 0) {
    $_SESSION['error'] = 'Your cart is empty.';
    redirect(app_url('views/cart.php'));
}

$profile = (new CustomerController())->getProfileByEmail($_SESSION['customer_email']);

$page_title = 'Payment — Paystack';
require_once __DIR__ . '/layout/header.php';
?>
<div class="layout-with-sidebar">
    <?php require __DIR__ . '/layout/sidebar.php'; ?>

    <section class="content">
        <h1>Payment</h1>
        <p class="sub">Review your order and complete payment securely via Paystack.</p>

        <?php require __DIR__ . '/layout/flash.php'; ?>

        <div class="payment-grid">
            <!-- Order Summary Card -->
            <div class="card wide">
                <h2>Cart Summary</h2>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Unit Price</th>
                            <th>Line Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($item['product_title']); ?></strong>
                                </td>
                                <td><?php echo (int) $item['qty']; ?></td>
                                <td><?php echo money($item['product_price']); ?></td>
                                <td><?php echo money($item['qty'] * $item['product_price']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3">Total Payable</th>
                            <th class="payment-total-highlight"><?php echo money($total); ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Delivery Details Card -->
            <div class="card wide">
                <h2>Billing & Delivery Details</h2>
                <?php if ($profile): ?>
                    <p>
                        <strong>Recipient:</strong> <?php echo htmlspecialchars($profile['customer_name']); ?><br>
                        <strong>Email:</strong> <?php echo htmlspecialchars($profile['customer_email']); ?><br>
                        <strong>Phone:</strong> <?php echo htmlspecialchars($profile['customer_contact']); ?><br>
                        <?php if (!empty($profile['customer_address'])): ?>
                            <strong>Address:</strong> <?php echo nl2br(htmlspecialchars($profile['customer_address'])); ?>,
                        <?php endif; ?>
                        <?php echo htmlspecialchars($profile['customer_city']); ?>, <?php echo htmlspecialchars($profile['customer_country']); ?>
                    </p>
                <?php endif; ?>
            </div>

            <!-- Paystack Payment Card -->
            <div class="card wide paystack-box">
                <div class="paystack-header">
                    <h2>Pay with Paystack</h2>
                    <span class="paystack-badge">Verified Secure</span>
                </div>
                <p>
                    Your transaction will be processed in Ghana Cedis (GHS) via <strong>Paystack</strong>'s secure payment infrastructure.
                </p>

                <div class="paystack-test-info">
                    <p><strong>Paystack Test Mode Instructions:</strong></p>
                    <p>Use test card: <code>4084 0840 8408 4081</code>, any future expiry date (e.g. <code>12/30</code>), CVV <code>408</code>.</p>
                </div>

                <div class="paystack-action-buttons">
                    <button type="button" id="paystack-btn" class="btn btn-paystack" onclick="payWithPaystack()">
                        Pay <?php echo money($total); ?> with Paystack
                    </button>

                    <!-- Offline / Demo testing fallback button when live internet or secret keys are not configured -->
                    <button type="button" class="btn btn-secondary btn-demo-test" onclick="simulateTestPayment()">
                        Simulate Test Payment (Demo)
                    </button>
                </div>

                <p class="form-foot" style="margin-top: 1.5rem;">
                    <a href="<?php echo app_url('views/checkout.php'); ?>">&larr; Back to Checkout</a>
                    | <a href="<?php echo app_url('views/cart.php'); ?>">Return to Cart</a>
                </p>
            </div>
        </div>
    </section>
</div>

<!-- Paystack Inline JS Library -->
<script src="https://js.paystack.co/v1/inline.js"></script>

<script>
/**
 * Initiate Paystack Inline Checkout popup.
 * Converts total GHS amount to pesewas/kobo by multiplying by 100.
 */
function payWithPaystack() {
    var customerEmail = '<?php echo htmlspecialchars($_SESSION["customer_email"] ?? ""); ?>';
    var amountInPesewas = <?php echo (int) round($total * 100); ?>;
    var publicKey = '<?php echo htmlspecialchars(PAYSTACK_PUBLIC_KEY); ?>';

    var handler = PaystackPop.setup({
        key: publicKey,
        email: customerEmail,
        amount: amountInPesewas,
        currency: 'GHS',
        ref: 'shoppn_' + Math.floor((Math.random() * 1000000000) + 1),
        onSuccess: function(response) {
            // Redirect to server-side verification action with received transaction reference
            window.location = '<?php echo app_url("actions/process_payment_action.php?ref="); ?>' + encodeURIComponent(response.reference);
        },
        onCancel: function() {
            alert('Payment was cancelled.');
        }
    });

    handler.openIframe();
}

/**
 * Local simulation helper: allows testing the complete order & invoice pipeline
 * without requiring active internet connectivity or an external test card.
 */
function simulateTestPayment() {
    if (confirm('Proceed with test payment simulation? This will record the order and generate your invoice.')) {
        var testRef = 'demo_' + Math.floor((Math.random() * 1000000000) + 1);
        window.location = '<?php echo app_url("actions/process_payment_action.php?ref="); ?>' + encodeURIComponent(testRef);
    }
}
</script>

<?php require_once __DIR__ . '/layout/footer.php'; ?>
