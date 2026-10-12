<?php
// search_results.php - customer search results view.
// In MVC, View files call Controller methods to retrieve data, never Model methods directly.
require_once __DIR__ . "/../core/core.php";
require_once __DIR__ . "/../controllers/ProductController.php";

$controller = new ProductController();

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

// Read ?user_query from GET. Call searchProducts().
$query = isset($_GET['user_query']) ? trim($_GET['user_query']) : '';
$products = [];
if ($query !== '') {
    $products = $controller->searchProducts($query);
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
            <h2>Search Results</h2>
            <?php if ($query !== ''): ?>
                <p class="section-subtitle">
                    Found <strong><?= count($products) ?></strong> product<?= count($products) === 1 ? '' : 's' ?> matching "<strong><?= htmlspecialchars($query) ?></strong>"
                </p>
            <?php else: ?>
                <p class="section-subtitle">Please enter a keyword to search for products.</p>
            <?php endif; ?>
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
                                <a href="?user_query=<?= urlencode($query) ?>&add_cart=<?= (int) $product['product_id'] ?>" class="btn-cart">
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
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <p class="empty-title">No products found</p>
                <p class="empty-desc">
                    <?= $query !== '' ? 'No items match your search for "' . htmlspecialchars($query) . '". Try a different keyword.' : 'Please enter a search term above to find products.' ?>
                </p>
                <a href="<?= BASE_URL ?>/index.php" class="btn btn-secondary">Browse All Products</a>
            </div>
        <?php endif; ?>
    </section>
</div>

<?php
require_once __DIR__ . "/layout/footer.php";
