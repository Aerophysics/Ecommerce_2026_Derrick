<?php

require_once __DIR__ . "/../core/core.php";
require_admin();

require_once __DIR__ . "/../controllers/ProductController.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cat_id   = filter_var($_POST['cat_id'] ?? null, FILTER_VALIDATE_INT);
    $cat_name = trim($_POST['cat_name'] ?? '');

    if (!$cat_id || $cat_id <= 0) {
        $_SESSION['error'] = 'Invalid category ID.';
        redirect(BASE_URL . '/views/admin/category.php');
    }

    if (empty($cat_name)) {
        $_SESSION['error'] = 'Category name cannot be empty.';
        redirect(BASE_URL . '/views/admin/category.php?edit_id=' . $cat_id);
    }

    $controller = new ProductController();
    $result = $controller->updateCategory($cat_id, $cat_name);

    if ($result) {
        $_SESSION['success'] = 'Category updated successfully.';
    } else {
        $_SESSION['error'] = 'Failed to update category. Please try again.';
    }

    redirect(BASE_URL . '/views/admin/category.php');
} else {
    redirect(BASE_URL . '/views/admin/category.php');
}
