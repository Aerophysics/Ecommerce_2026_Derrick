<?php

require_once __DIR__ . "/../core/core.php";
require_admin();

require_once __DIR__ . "/../controllers/ProductController.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $brand_id   = filter_var($_POST['brand_id'] ?? null, FILTER_VALIDATE_INT);
    $brand_name = trim($_POST['brand_name'] ?? '');

    if (!$brand_id || $brand_id <= 0) {
        $_SESSION['error'] = 'Invalid brand ID.';
        redirect(BASE_URL . '/views/admin/brand.php');
    }

    if (empty($brand_name)) {
        $_SESSION['error'] = 'Brand name cannot be empty.';
        redirect(BASE_URL . '/views/admin/brand.php?edit_id=' . $brand_id);
    }

    $controller = new ProductController();
    $result = $controller->updateBrand($brand_id, $brand_name);

    if ($result) {
        $_SESSION['success'] = 'Brand updated successfully.';
    } else {
        $_SESSION['error'] = 'Failed to update brand. Please try again.';
    }

    redirect(BASE_URL . '/views/admin/brand.php');
} else {
    redirect(BASE_URL . '/views/admin/brand.php');
}
