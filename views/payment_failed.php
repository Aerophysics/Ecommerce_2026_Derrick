<?php
/**
 * payment_failed.php — Payment failure view (Task 13).
 *
 * Rendered when Paystack payment is cancelled, declined, or fails server-side verification.
 */

require_once __DIR__ . '/../core/core.php';

$errorMsg = $_SESSION['error'] ?? 'Your payment could not be completed or verified. Please try again.';
unset($_SESSION['error']);

$page_title = 'Payment Failed — shoppn';
require_once __DIR__ . '/layout/header.php';
?>
<div class="layout-with-sidebar">
    <?php require __DIR__ . '/layout/sidebar.php'; ?>

    <section class="content">
        <div class="card wide payment-failed-card">
            <div class="payment-failed-icon">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="15" y1="9" x2="9" y2="15"></line>
                    <line x1="9" y1="9" x2="15" y2="15"></line>
                </svg>
            </div>

            <h1>Payment Not Completed</h1>
            <p class="payment-failed-msg">
                <?php echo htmlspecialchars($errorMsg); ?>
            </p>

            <div class="payment-failed-tips">
                <h3>Possible Reasons:</h3>
                <ul>
                    <li>The transaction was cancelled before completing on Paystack.</li>
                    <li>The card details or 3D Secure / OTP authorization failed.</li>
                    <li>Insufficient funds in the account or temporary bank gateway timeout.</li>
                    <li>The payment reference could not be verified by the Paystack API.</li>
                </ul>
                <p class="muted" style="margin-top: 0.75rem;">
                    <em>Note for test mode:</em> Use Paystack test card <code>4084 0840 8408 4081</code> with any future expiry and CVV <code>408</code>.
                </p>
            </div>

            <div class="cart-buttons" style="margin-top: 2rem;">
                <a class="btn btn-primary" href="<?php echo app_url('views/payment.php'); ?>">Try Payment Again</a>
                <a class="btn btn-secondary" href="<?php echo app_url('views/cart.php'); ?>">Return to Cart</a>
                <a class="btn btn-secondary" href="<?php echo app_url('index.php'); ?>">Back to Store</a>
            </div>
        </div>
    </section>
</div>
<?php require_once __DIR__ . '/layout/footer.php'; ?>
