<?php
// home.php - the storefront landing page (the View loaded by index.php).
// In MVC, the View layer queries via the Controller, never the Model directly.
require_once __DIR__ . "/../core/core.php";
require_once __DIR__ . "/../controllers/ProductController.php";

$controller = new ProductController();

// Redirect to single_product.php if ?pro_id is passed directly to home/index
if (isset($_GET['pro_id'])) {
    $detail_id = filter_var($_GET['pro_id'], FILTER_VALIDATE_INT);
    if ($detail_id && $detail_id > 0) {
        redirect(BASE_URL . '/views/single_product.php?pro_id=' . $detail_id);
    }
}

// Notification for cart additions if ?add_cart was clicked
$cart_msg = '';
if (isset($_GET['add_cart'])) {
    $add_cart_id = filter_var($_GET['add_cart'], FILTER_VALIDATE_INT);
    if ($add_cart_id && $add_cart_id > 0) {
        $added_prod = $controller->getProductById($add_cart_id);
        $prod_title = $added_prod ? $added_prod['product_title'] : 'Product';
        $cart_msg = htmlspecialchars($prod_title) . ' was added to your cart!';
    }
}

// Check for GET params: ?cat=N or ?brand=N or featured
$cat_id   = isset($_GET['cat']) ? filter_var($_GET['cat'], FILTER_VALIDATE_INT) : null;
$brand_id = isset($_GET['brand']) ? filter_var($_GET['brand'], FILTER_VALIDATE_INT) : null;

$heading = 'Featured Products';
$subheading = 'Check out our hand-picked selection of quality goods.';

if ($cat_id && $cat_id > 0) {
    $products = $controller->getProductsByCategory($cat_id);
    $selected_cat = $controller->getCategoryById($cat_id);
    $cat_name = $selected_cat ? $selected_cat['cat_name'] : 'Category #' . $cat_id;
    $heading = 'Category: ' . htmlspecialchars($cat_name);
    $subheading = 'Browsing all products in this category.';
} elseif ($brand_id && $brand_id > 0) {
    $products = $controller->getProductsByBrand($brand_id);
    $selected_brand = $controller->getBrandById($brand_id);
    $brand_name = $selected_brand ? $selected_brand['brand_name'] : 'Brand #' . $brand_id;
    $heading = 'Brand: ' . htmlspecialchars($brand_name);
    $subheading = 'Browsing all products by this brand.';
} else {
    $products = $controller->getFeaturedProducts(6);
}

require_once __DIR__ . "/layout/header.php";
?>

<div class="storefront-layout">
    <?php require_once __DIR__ . "/layout/sidebar.php"; ?>

    <section class="storefront-main">
        <?php if (!empty($cart_msg)): ?>
            <div class="alert alert-success" role="alert">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                <?= $cart_msg ?>
            </div>
        <?php endif; ?>

        <div class="section-header">
            <h2><?= $heading ?></h2>
            <p class="section-subtitle"><?= $subheading ?></p>
        </div>

        <?php if (!empty($products)): ?>
            <div class="product-grid">
                <?php foreach ($products as $product): ?>
                    <?php
                    $img_src = '';
                    if (!empty($product['product_image'])) {
                        if (strpos($product['product_image'], 'http://') === 0 || strpos($product['product_image'], 'https://') === 0) {
                            $img_src = $product['product_image'];
                        } elseif (file_exists(__DIR__ . '/../images/products/' . $product['product_image'])) {
                            $img_src = BASE_URL . '/images/products/' . htmlspecialchars($product['product_image']);
                        } elseif (file_exists(__DIR__ . '/../' . $product['product_image'])) {
                            $img_src = BASE_URL . '/' . htmlspecialchars($product['product_image']);
                        } else {
                            $img_src = BASE_URL . '/images/products/' . htmlspecialchars($product['product_image']);
                        }
                    }
                    ?>
                    <article class="product-card">
                        <div class="product-card-image">
                            <?php if (!empty($img_src)): ?>
                                <img src="<?= $img_src ?>" alt="<?= htmlspecialchars($product['product_title']) ?>" loading="lazy">
                            <?php else: ?>
                                <div class="product-image-fallback">
                                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                        <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                        <polyline points="21 15 16 10 5 21"></polyline>
                                    </svg>
                                    <span>No Image</span>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($product['cat_name'])): ?>
                                <span class="product-badge"><?= htmlspecialchars($product['cat_name']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="product-card-body">
                            <?php if (!empty($product['brand_name'])): ?>
                                <p class="product-brand-tag"><?= htmlspecialchars($product['brand_name']) ?></p>
                            <?php endif; ?>
                            <h3 class="product-title" title="<?= htmlspecialchars($product['product_title']) ?>">
                                <a href="<?= BASE_URL ?>/views/single_product.php?pro_id=<?= (int) $product['product_id'] ?>">
                                    <?= htmlspecialchars($product['product_title']) ?>
                                </a>
                            </h3>
                            <div class="product-price-row">
                                <span class="product-price">GH₵ <?= number_format((float) $product['product_price'], 2) ?></span>
                            </div>
                            <div class="product-card-actions">
                                <a href="<?= BASE_URL ?>/views/single_product.php?pro_id=<?= (int) $product['product_id'] ?>" class="btn-details">
                                    Details
                                </a>
                                <a href="?add_cart=<?= (int) $product['product_id'] ?>" class="btn-cart">
                                    Add to Cart
                                </a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="M8 12h8"></path>
                </svg>
                <p class="empty-title">No products found</p>
                <p class="empty-desc">We couldn't find any products in this view. Try exploring other categories or brands.</p>
                <a href="<?= BASE_URL ?>/index.php" class="btn btn-secondary">Browse All Products</a>
            </div>
        <?php endif; ?>
    </section>
</div>

<?php
require_once __DIR__ . "/layout/footer.php";
