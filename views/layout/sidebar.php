<?php
// sidebar.php - categories & brands navigation for the storefront.
// In MVC, views communicate with controllers, never directly with models.
require_once __DIR__ . "/../../core/core.php";
require_once __DIR__ . "/../../controllers/ProductController.php";

$sidebarController = new ProductController();
$categories = $sidebarController->getAllCategories();
$brands     = $sidebarController->getAllBrands();

$active_cat   = isset($_GET['cat']) ? (int) $_GET['cat'] : null;
$active_brand = isset($_GET['brand']) ? (int) $_GET['brand'] : null;
?>
<aside class="sidebar">
    <div class="sidebar-section">
        <h3>Categories</h3>
        <ul class="sidebar-list">
            <li>
                <a href="<?= BASE_URL ?>/index.php" class="<?= ($active_cat === null && $active_brand === null) ? 'active' : '' ?>">All Categories</a>
            </li>
            <?php if (!empty($categories)): ?>
                <?php foreach ($categories as $cat): ?>
                    <li>
                        <a href="<?= BASE_URL ?>/index.php?cat=<?= (int) $cat['cat_id'] ?>" class="<?= ($active_cat === (int) $cat['cat_id']) ? 'active' : '' ?>">
                            <?= htmlspecialchars($cat['cat_name']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li class="sidebar-empty">No categories available</li>
            <?php endif; ?>
        </ul>
    </div>

    <div class="sidebar-section">
        <h3>Brands</h3>
        <ul class="sidebar-list">
            <li>
                <a href="<?= BASE_URL ?>/index.php" class="<?= ($active_cat === null && $active_brand === null) ? 'active' : '' ?>">All Brands</a>
            </li>
            <?php if (!empty($brands)): ?>
                <?php foreach ($brands as $brand): ?>
                    <li>
                        <a href="<?= BASE_URL ?>/index.php?brand=<?= (int) $brand['brand_id'] ?>" class="<?= ($active_brand === (int) $brand['brand_id']) ? 'active' : '' ?>">
                            <?= htmlspecialchars($brand['brand_name']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li class="sidebar-empty">No brands available</li>
            <?php endif; ?>
        </ul>
    </div>
</aside>
