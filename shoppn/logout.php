<?php
// logout.php - end the session and return to the home page.
require_once __DIR__ . "/core/core.php";

// Clear all session variables, then destroy the session itself.
$_SESSION = [];

// Remove the session cookie from the browser as well.
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

session_destroy();

// Back to the storefront.
redirect(BASE_URL . '/index.php');
