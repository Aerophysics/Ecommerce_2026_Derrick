<?php
// Guard admin access before any HTML or output
require_once __DIR__ . "/../../core/core.php";
require_admin();

require_once __DIR__ . "/../../controllers/ProductController.php";

$controller = new ProductController();

// Edit Mode Check
$is_edit_mode = false;
$edit_brand_id = null;
$edit_brand_name = '';

if (isset($_GET['edit_id'])) {
    $edit_id = filter_var($_GET['edit_id'], FILTER_VALIDATE_INT);
    if ($edit_id && $edit_id > 0) {
        $brand_data = $controller->getBrandById($edit_id);
        if ($brand_data) {
            $is_edit_mode = true;
            $edit_brand_id = $brand_data['brand_id'];
            $edit_brand_name = $brand_data['brand_name'];
        } else {
            $_SESSION['error'] = 'Brand not found.';
        }
    }
}

// Session Notifications
$success_msg = $_SESSION['success'] ?? '';
$error_msg   = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

// Fetch All Brands
$brands = $controller->getAllBrands();

require_once __DIR__ . "/../layout/header.php";
?>

<section class="admin-brand-section">
    <h2>Product Brands Management</h2>

    <?php if (!empty($success_msg)): ?>
        <p class="alert alert-success" role="alert"><?php echo htmlspecialchars($success_msg); ?></p>
    <?php endif; ?>

    <?php if (!empty($error_msg)): ?>
        <p class="alert alert-error" role="alert"><?php echo htmlspecialchars($error_msg); ?></p>
    <?php endif; ?>

    <!-- Brand Form (Add or Edit) -->
    <div class="brand-form-card">
        <h3><?php echo $is_edit_mode ? 'Edit Brand' : 'Add New Brand'; ?></h3>
        
        <form action="<?php echo BASE_URL . ($is_edit_mode ? '/actions/update_brand_action.php' : '/actions/add_brand_action.php'); ?>" method="POST">
            <?php if ($is_edit_mode): ?>
                <input type="hidden" name="brand_id" value="<?php echo htmlspecialchars($edit_brand_id); ?>">
            <?php endif; ?>

            <div class="form-group">
                <label for="brand_name">Brand Name</label>
                <input 
                    type="text" 
                    id="brand_name" 
                    name="brand_name" 
                    value="<?php echo htmlspecialchars($edit_brand_name); ?>" 
                    required 
                    placeholder="Enter brand name"
                >
            </div>

            <div>
                <button type="submit"><?php echo $is_edit_mode ? 'Update Brand' : 'Add Brand'; ?></button>
                <?php if ($is_edit_mode): ?>
                    <a href="<?php echo BASE_URL; ?>/views/admin/brand.php" class="btn btn-secondary">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Brands List Table -->
    <div class="brands-list-container">
        <h3>Existing Brands</h3>
        <?php if (!empty($brands)): ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Brand Name</th>
                        <th style="text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($brands as $b): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($b['brand_id']); ?></td>
                            <td><?php echo htmlspecialchars($b['brand_name']); ?></td>
                            <td style="text-align: center;">
                                <a href="<?php echo BASE_URL; ?>/views/admin/brand.php?edit_id=<?php echo htmlspecialchars($b['brand_id']); ?>" class="action-edit-btn">Edit</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No brands found in the database.</p>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . "/../layout/footer.php"; ?>
