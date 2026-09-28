<?php

// login_action.php - the server entry point for the login form.
// POST only. Sanitises input, asks the controller to verify the
// credentials, sets up the session on success, then REDIRECTS.
require_once __DIR__ . "/../core/core.php";
require_once __DIR__ . "/../controllers/CustomerController.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . '/views/login.php');
}

// Sanitise. Password is left intact (spaces may be part of it).
$email = strip_tags(trim($_POST['customer_email'] ?? ''));
$pass  = $_POST['customer_pass'] ?? '';

if ($email === '' || $pass === '') {
    $_SESSION['error'] = 'Email and password are required.';
    redirect(BASE_URL . '/views/login.php');
}

$controller = new CustomerController();
$result = $controller->login($email, $pass);

if (!$result['success']) {
    // Deliberately vague message - don't reveal which field was wrong.
    $_SESSION['error'] = $result['error'];
    redirect(BASE_URL . '/views/login.php');
}

// Success: store identity in the session and go to the home page.
$c = $result['customer'];
$_SESSION['customer_id']    = $c['customer_id'];
$_SESSION['customer_name']  = $c['customer_name'];
$_SESSION['customer_email'] = $c['customer_email'];
$_SESSION['user_role']      = $c['user_role'];

redirect(BASE_URL . '/index.php');
