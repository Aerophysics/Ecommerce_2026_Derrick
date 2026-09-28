<?php
// home.php - the storefront landing page (the View loaded by index.php).
// It composes the shared layout pieces. The product grid is added in
// Task 10; for now it renders a welcome so the scaffold is verifiable.
require_once __DIR__ . "/layout/header.php";
require_once __DIR__ . "/layout/sidebar.php";
?>

<section>
    <h2>Welcome to Shoppn</h2>
    <p>Your one-stop shop. Browse products, add them to your cart, and check out.</p>
    <p>Product listings arrive in a later task. For now you can
        <a href="<?= BASE_URL ?>/views/register.php">register</a> or
        <a href="<?= BASE_URL ?>/views/login.php">log in</a>.</p>
</section>

<?php
require_once __DIR__ . "/layout/footer.php";
