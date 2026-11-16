<?php
/**
 * my_account.php — the logged-in customer's account page (View).
 * Protected: require_login() runs before any output.
 */
require_once __DIR__ . '/../../core/core.php';
require_login();

require_once __DIR__ . '/../../controllers/CustomerController.php';
require_once __DIR__ . '/../../controllers/CartController.php';

$controller = new CustomerController();
$profile = $controller->getProfileByEmail($_SESSION['customer_email']);

$cartController = new CartController();
$orders = $cartController->getCustomerOrders((int) ($_SESSION['customer_id'] ?? 0));


$page_title = 'My Account';
require_once __DIR__ . '/../layout/header.php';
?>
<div class="card wide">
    <h1>My Account</h1>
    <p class="sub">Your shoppn profile.</p>

    <?php require __DIR__ . '/../layout/flash.php'; ?>

    <?php if ($profile): ?>
        <dl class="account-grid">
            <dt>Name</dt>    <dd><?php echo htmlspecialchars($profile['customer_name']); ?></dd>
            <dt>Email</dt>   <dd><?php echo htmlspecialchars($profile['customer_email']); ?></dd>
            <dt>Country</dt> <dd><?php echo htmlspecialchars($profile['customer_country']); ?></dd>
            <dt>City</dt>    <dd><?php echo htmlspecialchars($profile['customer_city']); ?></dd>
            <dt>Contact</dt> <dd><?php echo htmlspecialchars($profile['customer_contact']); ?></dd>
            <?php if (!empty($profile['customer_address'])): ?>
                <dt>Address</dt> <dd><?php echo nl2br(htmlspecialchars($profile['customer_address'])); ?></dd>
            <?php endif; ?>
            <dt>Role</dt>    <dd><?php echo ((int) $profile['user_role'] === 1) ? 'Administrator' : 'Customer'; ?></dd>
            <dt>Joined</dt>  <dd><?php echo htmlspecialchars($profile['created_at'] ?? '—'); ?></dd>
        </dl>
    <?php else: ?>
        <p class="muted">Could not load your profile.</p>
    <?php endif; ?>

    <hr style="margin: 2rem 0; border: none; border-top: 1px solid var(--border-color);">

    <h2>My Orders</h2>
    <?php if (!empty($orders)): ?>
        <table class="table" style="margin-top: 1rem;">
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Reference</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $ord): ?>
                    <tr>
                        <td><strong>#<?php echo htmlspecialchars($ord['invoice_no']); ?></strong></td>
                        <td><?php echo htmlspecialchars($ord['order_date']); ?></td>
                        <td><?php echo money($ord['amt']); ?></td>
                        <td><span class="badge" style="background: #f0fdf4; color: #166534; padding: 2px 8px; border-radius: 4px; font-weight: 600;"><?php echo strtoupper(htmlspecialchars($ord['order_status'])); ?></span></td>
                        <td><code style="font-size: 12px;"><?php echo htmlspecialchars($ord['reference'] ?? '—'); ?></code></td>
                        <td>
                            <a class="btn-sm" href="<?php echo app_url('views/payment_success.php?order_id=' . (int) $ord['order_id']); ?>">View Invoice</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="muted">You have not placed any orders yet. <a href="<?php echo app_url('index.php'); ?>">Start shopping</a>.</p>
    <?php endif; ?>

    <p class="form-foot" style="margin-top: 2rem;">
        <a class="btn-sm" href="<?php echo app_url('logout.php'); ?>">Logout</a>
    </p>
</div>
<?php require_once __DIR__ . '/../layout/footer.php'; ?>

