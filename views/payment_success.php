<?php
/**
 * payment_success.php — Order invoice & confirmation receipt (Task 13).
 *
 * Displays the complete invoice and optionally emails a copy to the customer.
 */

require_once __DIR__ . '/../core/core.php';
require_login();
require_once __DIR__ . '/../controllers/CartController.php';

$orderId = filter_var($_GET['order_id'] ?? 0, FILTER_VALIDATE_INT);
if (!$orderId || $orderId <= 0) {
    $_SESSION['error'] = 'Invalid order reference.';
    redirect(app_url('index.php'));
}

$cartController = new CartController();
$order          = $cartController->getOrderById($orderId);

if (!$order) {
    $_SESSION['error'] = 'Order could not be found.';
    redirect(app_url('index.php'));
}

// Verify that the logged in customer owns this order (or is an admin)
if ((int) $order['customer_id'] !== (int) $_SESSION['customer_id'] && !is_admin()) {
    $_SESSION['error'] = 'You do not have permission to view this order.';
    redirect(app_url('index.php'));
}

// ── Send Confirmation Email (Task 13) ────────────────────────
$emailNotice = '';
if (empty($_SESSION['order_emailed_' . $orderId])) {
    $to      = $order['customer_email'];
    $subject = "Shoppn Order Confirmation — Invoice #" . $order['invoice_no'];
    $headers = [
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8',
        'From: Shoppn Store <no-reply@' . ($_SERVER['SERVER_NAME'] ?? 'localhost') . '>',
    ];

    $itemsListHtml = '';
    foreach ($order['items'] as $item) {
        $itemsListHtml .= '<tr>'
            . '<td style="padding: 10px; border-bottom: 1px solid #e2e8f0;">' . htmlspecialchars($item['product_title']) . '</td>'
            . '<td style="padding: 10px; border-bottom: 1px solid #e2e8f0; text-align: center;">' . (int) $item['qty'] . '</td>'
            . '<td style="padding: 10px; border-bottom: 1px solid #e2e8f0; text-align: right;">' . money($item['product_price']) . '</td>'
            . '<td style="padding: 10px; border-bottom: 1px solid #e2e8f0; text-align: right;">' . money($item['line_total']) . '</td>'
            . '</tr>';
    }

    $emailBody = '
    <div style="font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Arial, sans-serif; max-width: 600px; margin: auto; padding: 24px; border: 1px solid #e2e8f0; border-radius: 12px; background: #ffffff;">
        <div style="border-bottom: 2px solid #4f46e5; padding-bottom: 16px; margin-bottom: 20px;">
            <h1 style="color: #4f46e5; margin: 0; font-size: 24px;">Shoppn</h1>
            <p style="color: #64748b; margin: 4px 0 0; font-size: 14px;">Official Order Receipt &amp; Invoice</p>
        </div>
        <p style="font-size: 16px; color: #1e293b;">Dear <strong>' . htmlspecialchars($order['customer_name']) . '</strong>,</p>
        <p style="font-size: 14px; color: #475569;">Thank you for your order! Your payment was verified and completed successfully via Paystack.</p>
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px; margin-bottom: 20px; font-size: 14px;">
            <p style="margin: 0 0 6px;"><strong>Invoice No:</strong> #' . htmlspecialchars($order['invoice_no']) . '</p>
            <p style="margin: 0 0 6px;"><strong>Order Date:</strong> ' . htmlspecialchars($order['order_date']) . '</p>
            <p style="margin: 0 0 6px;"><strong>Status:</strong> <span style="color: #166534; font-weight: bold;">PAID</span></p>
            <p style="margin: 0;"><strong>Paystack Reference:</strong> ' . htmlspecialchars($order['payment_reference'] ?? 'N/A') . '</p>
        </div>
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 14px;">
            <thead>
                <tr style="background: #f1f5f9; color: #334155;">
                    <th style="padding: 10px; text-align: left;">Product</th>
                    <th style="padding: 10px; text-align: center;">Qty</th>
                    <th style="padding: 10px; text-align: right;">Unit Price</th>
                    <th style="padding: 10px; text-align: right;">Line Total</th>
                </tr>
            </thead>
            <tbody>' . $itemsListHtml . '</tbody>
            <tfoot>
                <tr>
                    <th colspan="3" style="padding: 12px 10px; text-align: right; font-size: 15px;">Total Paid:</th>
                    <th style="padding: 12px 10px; text-align: right; color: #4f46e5; font-size: 16px;">' . money($order['total']) . '</th>
                </tr>
            </tfoot>
        </table>
        <div style="border-top: 1px solid #e2e8f0; padding-top: 14px; color: #64748b; font-size: 12px;">
            <p style="margin: 0 0 4px;"><strong>Delivering To:</strong> ' . htmlspecialchars($order['customer_address'] ?? '') . ', ' . htmlspecialchars($order['customer_city'] ?? '') . ', ' . htmlspecialchars($order['customer_country'] ?? '') . '</p>
            <p style="margin: 0;">Contact Phone: ' . htmlspecialchars($order['customer_contact'] ?? '') . '</p>
        </div>
    </div>';

    // Suppress warning if local PHP mail/sendmail is not configured on local machine
    $sent = @mail($to, $subject, $emailBody, implode("\r\n", $headers));
    if ($sent) {
        $emailNotice = 'A confirmation email with your invoice was sent to ' . htmlspecialchars($to) . '.';
    }
    $_SESSION['order_emailed_' . $orderId] = true;
}

$page_title = 'Invoice #' . $order['invoice_no'];
require_once __DIR__ . '/layout/header.php';
?>
<div class="layout-with-sidebar">
    <?php require __DIR__ . '/layout/sidebar.php'; ?>

    <section class="content">
        <div class="invoice-page-actions no-print">
            <a class="btn btn-secondary" href="<?php echo app_url('index.php'); ?>">&larr; Continue Shopping</a>
            <button type="button" class="btn btn-primary" onclick="window.print()">Print Invoice</button>
        </div>

        <?php require __DIR__ . '/layout/flash.php'; ?>

        <?php if (!empty($emailNotice)): ?>
            <div class="flash flash-success no-print"><?php echo htmlspecialchars($emailNotice); ?></div>
        <?php endif; ?>

        <!-- Printable Invoice Container -->
        <article class="invoice-card" id="invoice">
            <header class="invoice-header">
                <div class="invoice-brand">
                    <span class="invoice-logo-text">shoppn</span>
                    <span class="invoice-type-tag">Official Invoice</span>
                </div>
                <div class="invoice-number-box">
                    <div class="invoice-id">Invoice #<?php echo htmlspecialchars($order['invoice_no']); ?></div>
                    <div class="invoice-status-badge">PAID</div>
                </div>
            </header>

            <div class="invoice-meta-grid">
                <div class="invoice-meta-block">
                    <h3>Order Information</h3>
                    <p>
                        <strong>Order Date:</strong> <?php echo htmlspecialchars($order['order_date']); ?><br>
                        <strong>Order Status:</strong> <?php echo strtoupper(htmlspecialchars($order['order_status'])); ?><br>
                        <strong>Payment Method:</strong> Paystack (GHS)<br>
                        <strong>Payment Reference:</strong> <code><?php echo htmlspecialchars($order['payment_reference'] ?? 'Verified'); ?></code>
                    </p>
                </div>

                <div class="invoice-meta-block">
                    <h3>Billed &amp; Delivered To</h3>
                    <p>
                        <strong><?php echo htmlspecialchars($order['customer_name']); ?></strong><br>
                        <strong>Email:</strong> <?php echo htmlspecialchars($order['customer_email']); ?><br>
                        <strong>Phone:</strong> <?php echo htmlspecialchars($order['customer_contact']); ?><br>
                        <?php if (!empty($order['customer_address'])): ?>
                            <strong>Address:</strong> <?php echo nl2br(htmlspecialchars($order['customer_address'])); ?>,
                        <?php endif; ?>
                        <?php echo htmlspecialchars($order['customer_city']); ?>, <?php echo htmlspecialchars($order['customer_country']); ?>
                    </p>
                </div>
            </div>

            <!-- Items Table -->
            <div class="invoice-table-wrapper">
                <table class="table invoice-table">
                    <thead>
                        <tr>
                            <th>Item Description</th>
                            <th>Qty</th>
                            <th>Unit Price</th>
                            <th>Line Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order['items'] as $item): ?>
                            <tr>
                                <td>
                                    <div class="invoice-product-flex">
                                        <?php if (!empty($item['product_image'])): ?>
                                            <img class="invoice-item-thumb"
                                                 src="<?php echo product_image_url($item['product_image']); ?>"
                                                 alt="<?php echo htmlspecialchars($item['product_title']); ?>">
                                        <?php endif; ?>
                                        <div class="invoice-product-title">
                                            <strong><?php echo htmlspecialchars($item['product_title']); ?></strong>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo (int) $item['qty']; ?></td>
                                <td><?php echo money($item['product_price']); ?></td>
                                <td><?php echo money($item['line_total']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3">Subtotal</th>
                            <td><?php echo money($order['total']); ?></td>
                        </tr>
                        <tr>
                            <th colspan="3">Shipping &amp; Handling</th>
                            <td>GH₵ 0.00 <span class="badge-free">FREE</span></td>
                        </tr>
                        <tr class="invoice-grand-total">
                            <th colspan="3">Total Paid</th>
                            <td><?php echo money($order['total']); ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <footer class="invoice-footer">
                <p>Thank you for shopping with <strong>shoppn</strong>! Keep this invoice for your records.</p>
                <p class="muted">Payments verified and powered securely by Paystack.</p>
            </footer>
        </article>

        <div class="invoice-bottom-nav no-print">
            <a class="btn btn-secondary" href="<?php echo app_url('views/account/my_account.php'); ?>">View Order History</a>
            <a class="btn btn-primary" href="<?php echo app_url('index.php'); ?>">Return to Storefront</a>
        </div>
    </section>
</div>
<?php require_once __DIR__ . '/layout/footer.php'; ?>
