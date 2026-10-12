<?php

require_once __DIR__ . "/../core/core.php";
require_admin();

require_once __DIR__ . "/../controllers/ProductController.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cat_name = trim($_POST['cat_name'] ?? '');

    if (empty($cat_name)) {
        $_SESSION['error'] = 'Category name cannot be empty.';
        redirect(BASE_URL . '/views/admin/category.php');
    }

    $controller = new ProductController();
    $result = $controller->addCategory($cat_name);

    if ($result) {
        $_SESSION['success'] = 'Category added.';
    } else {
        $_SESSION['error'] = 'Failed to add category. Please try again.';
    }

    redirect(BASE_URL . '/views/admin/category.php');
} else {
    redirect(BASE_URL . '/views/admin/category.php');
}
