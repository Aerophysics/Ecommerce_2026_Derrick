<?php

// ============================================================
// core.php
// ------------------------------------------------------------
// Included at the top of (almost) every page in the app via
//   require_once __DIR__ . "/../core/core.php";
// It is the single place for things that must happen on EVERY
// request: buffering, the session, timezone, the DB base class,
// and the small shared helper functions the whole site relies on.
// It contains NO business logic and NO SQL - that lives in the
// model classes (classes/*.php).
// ============================================================

// Start output buffering. header('Location: ...') redirects fail if
// any output (even a stray space before <?php) was already sent to the
// browser. Buffering lets action files redirect safely at any point.
if (!ob_get_level()) {
    ob_start();
}

// session_start() must run before $_SESSION can be read or written
// anywhere else in the app. Guard it so it is never started twice.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Make date/time functions consistent regardless of server locale.
date_default_timezone_set('UTC');

// ------------------------------------------------------------
// BASE_URL - the URL path the app is served from, detected at runtime.
// ------------------------------------------------------------
// The app must work no matter where it is mounted:
//   - Local XAMPP:  http://localhost/shoppn/...            -> "/shoppn"
//   - Remote host:  http://host/~user/e-commerce-labs/shoppn/... -> "/~user/e-commerce-labs/shoppn"
// Hard-coding "/shoppn" broke the remote deploy (every link 404'd because
// the real prefix is longer). Instead we work out the prefix from the
// running script: take its filesystem path relative to the app root, then
// strip that same tail off the URL the browser used to reach it.
if (!defined('BASE_URL')) {
    $__app_root   = realpath(dirname(__DIR__));                 // .../shoppn on disk
    $__script     = realpath($_SERVER['SCRIPT_FILENAME'] ?? '');// running script on disk
    $__script_url = $_SERVER['SCRIPT_NAME'] ?? '';              // its URL path

    $__base = '';
    if ($__app_root && $__script && strpos($__script, $__app_root) === 0) {
        // Script path relative to app root, e.g. "/views/register.php".
        $__rel = str_replace('\\', '/', substr($__script, strlen($__app_root)));
        if ($__rel !== '' && substr($__script_url, -strlen($__rel)) === $__rel) {
            $__base = substr($__script_url, 0, -strlen($__rel));
        } else {
            $__base = rtrim(dirname($__script_url), '/\\');
        }
    }
    // Normalise: never a trailing slash; "" means the site root.
    define('BASE_URL', rtrim($__base, '/'));
}

// The database base class every model extends. Loaded once here so any
// page that includes core.php already has Database available.
require_once __DIR__ . "/db_class.php";

// ------------------------------------------------------------
// Shared helper functions
// ------------------------------------------------------------

// Return the client's IP address. The cart is keyed on IP so guests can
// shop without an account (see later cart tasks). Checks the common proxy
// headers first, then falls back to the direct connection address.
if (!function_exists('get_ip')) {
    function get_ip()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // May contain a comma-separated list; the first entry is the client.
            return trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
        }
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}

// Send a Location redirect and stop the script. Every action file ends
// with one of these instead of echoing HTML.
if (!function_exists('redirect')) {
    function redirect($url)
    {
        header("Location: " . $url);
        exit;
    }
}

// True if a customer is currently logged in.
if (!function_exists('is_logged_in')) {
    function is_logged_in()
    {
        return isset($_SESSION['customer_id']);
    }
}

// True if the logged-in user is an admin (user_role === 1).
// Cast to int so a string "1" from the session still matches.
if (!function_exists('is_admin')) {
    function is_admin()
    {
        return isset($_SESSION['user_role']) && (int) $_SESSION['user_role'] === 1;
    }
}

// Guard a page that requires any logged-in user. Call at the very top,
// before any HTML output. Redirects to the login page if not logged in.
if (!function_exists('require_login')) {
    function require_login()
    {
        if (!is_logged_in()) {
            $_SESSION['error'] = 'Please log in to continue.';
            redirect(BASE_URL . '/views/login.php');
        }
    }
}

// Guard an admin-only page. Redirects home with an error if the current
// user is not an admin. Call at the very top of every admin view/action.
if (!function_exists('require_admin')) {
    function require_admin()
    {
        if (!is_admin()) {
            $_SESSION['error'] = 'Admin access required.';
            redirect(BASE_URL . '/index.php');
        }
    }
}
