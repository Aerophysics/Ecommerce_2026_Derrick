<?php
// my_account.php - the customer's account landing page.
// Access control comes FIRST, before any output: only logged-in users
// may see this page. Full account features (edit, change password,
// delete) are built in a later task; this is the minimal protected page
// that registration and login redirect to.
require_once __DIR__ . "/../../core/core.php";
require_login();

require_once __DIR__ . "/../layout/header.php";
?>

<section>
    <h2>My Account</h2>
    <p>Welcome, <?php echo htmlspecialchars($_SESSION['customer_name'] ?? ''); ?>!</p>
    <ul>
        <li>Email: <?php echo htmlspecialchars($_SESSION['customer_email'] ?? ''); ?></li>
        <li>Role: <?php echo ((int) ($_SESSION['user_role'] ?? 2) === 1) ? 'Admin' : 'Customer'; ?></li>
    </ul>
    <p><a href="<?= BASE_URL ?>/logout.php">Log out</a></p>
</section>

<?php require_once __DIR__ . "/../layout/footer.php"; ?>
