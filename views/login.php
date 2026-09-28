<?php
// login.php - the login form (View layer). Minimal PHP: shows a session
// error and the form. No SQL or business logic here.
require_once __DIR__ . "/../core/core.php";

if (is_logged_in()) {
    redirect(BASE_URL . '/index.php');
}

$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']);

require_once __DIR__ . "/layout/header.php";
?>

<section>
    <h2>Log in</h2>

    <?php if ($error !== ''): ?>
        <p role="alert"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form id="loginForm" action="<?= BASE_URL ?>/actions/login_action.php" method="POST" novalidate>
        <div>
            <label for="login_email">Email</label>
            <input type="text" name="customer_email" id="login_email" maxlength="50">
            <span id="err_login_email"></span>
        </div>
        <div>
            <label for="login_pass">Password</label>
            <input type="password" name="customer_pass" id="login_pass">
            <span id="err_login_pass"></span>
        </div>
        <div>
            <button type="submit" id="loginBtn">Log in</button>
        </div>
    </form>

    <p>Don't have an account? <a href="<?= BASE_URL ?>/views/register.php">Register</a>.</p>
</section>

<script src="<?= BASE_URL ?>/js/validate.js"></script>

<?php require_once __DIR__ . "/layout/footer.php"; ?>
