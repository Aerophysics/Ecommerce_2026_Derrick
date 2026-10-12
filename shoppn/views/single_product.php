<?php
// single_product.php - individual product detail view.
// In MVC, View files call Controller methods to retrieve data, never Model methods directly.
require_once __DIR__ . "/../core/core.php";
require_once __DIR__ . "/../controllers/ProductController.php";

// Require a valid ?pro_id=N GET param — redirect home if missing or invalid
if (!isset($_GET['pro_id'])) {
    redirect(BASE_URL . '/index.php');
}

$pro_id = filter_var($_GET['pro_id'], FILTER_VALIDATE_INT);
if (!$pro_id || $pro_id <= 0) {
    redirect(BASE_URL . '/index.php');
}

$controller = new ProductController();
$product = $controller->getProductById($pro_id);

// If product not found in database, redirect home
if (!$product) {
    $_SESSION['error'] = 'Product not found.';
    redirect(BASE_URL . '/index.php');
}

// Image handling
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

require_once __DIR__ . "/layout/header.php";
?>

<div class="storefront-layout">
    <?php require_once __DIR__ . "/layout/sidebar.php"; ?>

    <section class="storefront-main">
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="<?= BASE_URL ?>/index.php">Home</a>
            <span class="breadcrumb-separator">/</span>
            <?php if (!empty($product['product_cat']) && !empty($product['cat_name'])): ?>
                <a href="<?= BASE_URL ?>/index.php?cat=<?= (int) $product['product_cat'] ?>">
                    <?= htmlspecialchars($product['cat_name']) ?>
                </a>
                <span class="breadcrumb-separator">/</span>
            <?php endif; ?>
            <span class="breadcrumb-current"><?= htmlspecialchars($product['product_title']) ?></span>
        </nav>

        <article class="single-product-card">
            <div class="single-product-media">
                <?php if (!empty($img_src)): ?>
                    <img src="<?= $img_src ?>" alt="<?= htmlspecialchars($product['product_title']) ?>" class="single-product-image">
                <?php else: ?>
                    <div class="single-product-image-fallback">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                            <circle cx="8.5" cy="8.5" r="1.5"></circle>
                            <polyline points="21 15 16 10 5 21"></polyline>
                        </svg>
                        <span>No Image Available</span>
                    </div>
                <?php endif; ?>
            </div>

            <div class="single-product-details">
                <h1 class="single-product-title"><?= htmlspecialchars($product['product_title']) ?></h1>

                <div class="single-product-meta">
                    <div class="meta-row">
                        <span class="meta-label">Category:</span>
                        <span class="meta-value">
                            <?php if (!empty($product['product_cat'])): ?>
                                <a href="<?= BASE_URL ?>/index.php?cat=<?= (int) $product['product_cat'] ?>">
                                    <?= htmlspecialchars($product['cat_name'] ?? 'Category #' . $product['product_cat']) ?>
                                </a>
                            <?php else: ?>
                                <?= htmlspecialchars($product['cat_name'] ?? 'N/A') ?>
                            <?php endif; ?>
                        </span>
                    </div>

                    <div class="meta-row">
                        <span class="meta-label">Brand:</span>
                        <span class="meta-value">
                            <?php if (!empty($product['product_brand'])): ?>
                                <a href="<?= BASE_URL ?>/index.php?brand=<?= (int) $product['product_brand'] ?>">
                                    <?= htmlspecialchars($product['brand_name'] ?? 'Brand #' . $product['product_brand']) ?>
                                </a>
                            <?php else: ?>
                                <?= htmlspecialchars($product['brand_name'] ?? 'N/A') ?>
                            <?php endif; ?>
                        </span>
                    </div>
                </div>

                <div class="single-product-price-box">
                    <span class="price-amount">GH₵ <?= number_format((float) $product['product_price'], 2) ?></span>
                </div>

                <div class="single-product-description">
                    <h3>Description</h3>
                    <p><?= nl2br(htmlspecialchars($product['product_desc'] ?: 'No description provided for this product.')) ?></p>
                </div>

                <?php if (!empty($product['product_keywords'])): ?>
                    <div class="single-product-keywords">
                        <h3>Keywords</h3>
                        <div class="keyword-tags">
                            <?php
                            $tags = array_filter(array_map('trim', explode(',', $product['product_keywords'])));
                            foreach ($tags as $tag):
                            ?>
                                <a href="<?= BASE_URL ?>/views/search_results.php?user_query=<?= urlencode($tag) ?>" class="keyword-tag">
                                    <?= htmlspecialchars($tag) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="single-product-actions">
                    <a href="<?= BASE_URL ?>/index.php?add_cart=<?= (int) $product['product_id'] ?>" class="btn btn-cart-lg">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="9" cy="21" r="1"></circle>
                            <circle cx="20" cy="21" r="1"></circle>
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                        </svg>
                        Add to Cart
                    </a>
                    <a href="<?= BASE_URL ?>/index.php" class="btn btn-secondary">
                        &larr; Back to Products
                    </a>
                </div>
            </div>
        </article>
    </section>
</div>

<?php
require_once __DIR__ . "/layout/footer.php";
