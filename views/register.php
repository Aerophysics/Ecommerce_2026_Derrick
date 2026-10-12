<?php
// register.php - the registration form (View layer). Minimal PHP: it only
// displays a session error message. No SQL, no business logic here.
require_once __DIR__ . "/../core/core.php";

// If already logged in, there's no reason to register again.
if (is_logged_in()) {
    redirect(BASE_URL . '/index.php');
}

// Pull any error set by the action file, then clear it so it shows once.
$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']);

require_once __DIR__ . "/layout/header.php";
?>

<section>
    <h2>Create an account</h2>

    <?php if ($error !== ''): ?>
        <p role="alert"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <!--
        The form posts to the action file. Each input's name maps to a
        customer column; each id is what js/validate.js reads. The empty
        <span> after each field is where inline JS errors are written.
        (The customer table has no address column, so there is no address
        field - only columns that actually exist are collected.)
    -->
    <form id="registerForm" action="<?= BASE_URL ?>/actions/register_action.php" method="POST" novalidate>
        <div>
            <label for="reg_name">Full Name</label>
            <input type="text" name="customer_name" id="reg_name" maxlength="100">
            <span id="err_name"></span>
        </div>
        <div>
            <label for="reg_email">Email</label>
            <input type="text" name="customer_email" id="reg_email" maxlength="50">
            <span id="err_email"></span>
        </div>
        <div>
            <label for="reg_pass">Password</label>
            <input type="password" name="customer_pass" id="reg_pass">
            <span id="err_pass"></span>
        </div>
        <div>
            <label for="reg_country">Country</label>
            <select name="customer_country" id="reg_country">
                <option value="">-- Select country --</option>
                <option value="Ghana">Ghana</option>
                <option value="Nigeria">Nigeria</option>
                <option value="Kenya">Kenya</option>
                <option value="South Africa">South Africa</option>
                <option value="United States">United States</option>
                <option value="United Kingdom">United Kingdom</option>
            </select>
            <span id="err_country"></span>
        </div>
        <div>
            <label for="reg_city">City</label>
            <input type="text" name="customer_city" id="reg_city" maxlength="30">
            <span id="err_city"></span>
        </div>
        <div>
            <label for="reg_contact">Contact Number</label>
            <input type="text" name="customer_contact" id="reg_contact" maxlength="15">
            <span id="err_contact"></span>
        </div>
        <div>
            <button type="submit" id="registerBtn">Register</button>
        </div>
    </form>

    <p>Already have an account? <a href="<?= BASE_URL ?>/views/login.php">Log in</a>.</p>
</section>

<!-- Client-side validation lives here and runs before the form submits -->
<script src="<?= BASE_URL ?>/js/validate.js"></script>

<?php require_once __DIR__ . "/layout/footer.php"; ?>
