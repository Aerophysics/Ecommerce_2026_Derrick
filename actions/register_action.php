<?php

// register_action.php - the server entry point for the registration form.
// It reads the POST, sanitises and validates every field server-side,
// asks the controller to register the customer, then REDIRECTS. It never
// echoes HTML and never touches the database directly.
require_once __DIR__ . "/../core/core.php";
require_once __DIR__ . "/../controllers/CustomerController.php";

// This endpoint only handles form submissions.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . '/views/register.php');
}

// --- Sanitise all inputs server-side ---
// trim() removes stray whitespace; strip_tags() removes any HTML/script
// a user might try to inject. Never trust client-side validation alone.
$name    = strip_tags(trim($_POST['customer_name']    ?? ''));
$email   = strip_tags(trim($_POST['customer_email']   ?? ''));
$pass    = $_POST['customer_pass'] ?? '';   // not trimmed/stripped: spaces can be valid in a password
$country = strip_tags(trim($_POST['customer_country'] ?? ''));
$city    = strip_tags(trim($_POST['customer_city']    ?? ''));
$contact = strip_tags(trim($_POST['customer_contact'] ?? ''));

// --- Validate ---
$errors = [];

// Required fields
if ($name === '' || $email === '' || $pass === '' || $country === '' || $city === '' || $contact === '') {
    $errors[] = 'All fields are required.';
}

// Email must be a real email shape (mirrors the client-side regex)
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please enter a valid email address.';
}

// Field lengths must fit the DB schema (customer table column sizes)
if (strlen($email) > 50)   { $errors[] = 'Email must be 50 characters or fewer.'; }
if (strlen($name) > 100)   { $errors[] = 'Name must be 100 characters or fewer.'; }
if (strlen($country) > 30) { $errors[] = 'Country must be 30 characters or fewer.'; }
if (strlen($city) > 30)    { $errors[] = 'City must be 30 characters or fewer.'; }
if (strlen($contact) > 15) { $errors[] = 'Contact must be 15 characters or fewer.'; }

// Password policy: at least 8 chars and at least one digit (mirrors JS)
if ($pass !== '' && !preg_match('/^(?=.*\d).{8,}$/', $pass)) {
    $errors[] = 'Password must be at least 8 characters and include a number.';
}

// On any validation failure, stash the message and go back to the form.
if (!empty($errors)) {
    $_SESSION['error'] = implode(' ', $errors);
    redirect(BASE_URL . '/views/register.php');
}

// --- Hand off to the controller (which calls the model) ---
$controller = new CustomerController();
$result = $controller->register([
    'name'    => $name,
    'email'   => $email,
    'pass'    => $pass,
    'country' => $country,
    'city'    => $city,
    'contact' => $contact,
]);

if (!$result['success']) {
    $_SESSION['error'] = $result['error'];
    redirect(BASE_URL . '/views/register.php');
}

// Success: log the new customer straight in. We still hold the plaintext
// password, so we reuse the login flow to fetch the freshly-created row
// (with its new customer_id and user_role) and populate the session.
$login = $controller->login($email, $pass);
if ($login['success']) {
    $c = $login['customer'];
    $_SESSION['customer_id']    = $c['customer_id'];
    $_SESSION['customer_name']  = $c['customer_name'];
    $_SESSION['customer_email'] = $c['customer_email'];
    $_SESSION['user_role']      = $c['user_role'];
}

redirect(BASE_URL . '/views/account/my_account.php');
