<?php

require_once __DIR__ . "/../core/core.php";
require_admin();

require_once __DIR__ . "/../controllers/ProductController.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $brand_name = trim($_POST['brand_name'] ?? '');

    if (empty($brand_name)) {
        $_SESSION['error'] = 'Brand name cannot be empty.';
        redirect(BASE_URL . '/views/admin/brand.php');
    }

    $controller = new ProductController();
    $result = $controller->addBrand($brand_name);

    if ($result) {
        $_SESSION['success'] = 'Brand added.';
    } else {
        $_SESSION['error'] = 'Failed to add brand. Please try again.';
    }

    redirect(BASE_URL . '/views/admin/brand.php');
} else {
    redirect(BASE_URL . '/views/admin/brand.php');
}
