<?php
// header.php - the View's shared layout layer. It is included at the top
// of every page and contains ONLY HTML plus minimal PHP for session-based
// nav items. No SQL, no business logic.
//
// Included defensively so the header still works if a view is browsed
// directly. require_once means core is loaded at most once per request.
require_once __DIR__ . "/../../core/core.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Shoppn - E-commerce Lab</title>
    <!-- Stylesheet lives in css/style.css (styling is out of scope for now) -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
</head>
<body>
    <header>
        <h1><a href="<?= BASE_URL ?>/index.php">Shoppn</a></h1>

        <!-- Search box: submits a keyword to the search results page (GET) -->
        <form action="<?= BASE_URL ?>/views/search_results.php" method="GET" role="search">
            <input type="text" name="user_query" placeholder="Search products...">
            <button type="submit">Search</button>
        </form>

        <!-- Primary navigation. What shows here depends on the session. -->
        <nav>
            <a href="<?= BASE_URL ?>/index.php">Home</a>

            <?php if (is_logged_in()): ?>
                <!-- Logged-in customer: greeting, account, logout -->
                <span>Welcome <?php echo htmlspecialchars($_SESSION['customer_name'] ?? 'customer'); ?></span>
                <a href="<?= BASE_URL ?>/views/account/my_account.php">My Account</a>

                <?php if (is_admin()): ?>
                    <!-- Admin-only links (features built in later tasks) -->
                    <a href="<?= BASE_URL ?>/views/admin/brand.php">Brands</a>
                    <a href="<?= BASE_URL ?>/views/admin/category.php">Categories</a>
                    <a href="<?= BASE_URL ?>/views/admin/product.php">Products</a>
                <?php endif; ?>

                <a href="<?= BASE_URL ?>/logout.php">Logout</a>
            <?php else: ?>
                <!-- Guest: offer registration and login -->
                <a href="<?= BASE_URL ?>/views/register.php">Register</a>
                <a href="<?= BASE_URL ?>/views/login.php">Login</a>
            <?php endif; ?>
        </nav>
    </header>
    <main>
