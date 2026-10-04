<?php
// Guard admin access before any HTML or output
require_once __DIR__ . "/../../core/core.php";
require_admin();

require_once __DIR__ . "/../../controllers/ProductController.php";

$controller = new ProductController();

// Edit Mode Check
$is_edit_mode = false;
$edit_cat_id = null;
$edit_cat_name = '';

if (isset($_GET['edit_id'])) {
    $edit_id = filter_var($_GET['edit_id'], FILTER_VALIDATE_INT);
    if ($edit_id && $edit_id > 0) {
        $cat_data = $controller->getCategoryById($edit_id);
        if ($cat_data) {
            $is_edit_mode = true;
            $edit_cat_id = $cat_data['cat_id'];
            $edit_cat_name = $cat_data['cat_name'];
        } else {
            $_SESSION['error'] = 'Category not found.';
        }
    }
}

// Session Notifications
$success_msg = $_SESSION['success'] ?? '';
$error_msg   = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

// Fetch All Categories
$categories = $controller->getAllCategories();

require_once __DIR__ . "/../layout/header.php";
?>

<section class="admin-category-section">
    <h2>Product Categories Management</h2>

    <?php if (!empty($success_msg)): ?>
        <p class="alert alert-success" role="alert"><?php echo htmlspecialchars($success_msg); ?></p>
    <?php endif; ?>

    <?php if (!empty($error_msg)): ?>
        <p class="alert alert-error" role="alert"><?php echo htmlspecialchars($error_msg); ?></p>
    <?php endif; ?>

    <!-- Category Form (Add or Edit) -->
    <div class="form-card">
        <h3><?php echo $is_edit_mode ? 'Edit Category' : 'Add New Category'; ?></h3>
        
        <form action="<?php echo BASE_URL . ($is_edit_mode ? '/actions/update_category_action.php' : '/actions/add_category_action.php'); ?>" method="POST">
            <?php if ($is_edit_mode): ?>
                <input type="hidden" name="cat_id" value="<?php echo htmlspecialchars($edit_cat_id); ?>">
            <?php endif; ?>

            <div class="form-group">
                <label for="cat_name">Category Name</label>
                <input 
                    type="text" 
                    id="cat_name" 
                    name="cat_name" 
                    value="<?php echo htmlspecialchars($edit_cat_name); ?>" 
                    required 
                    placeholder="Enter category name"
                >
            </div>

            <div>
                <button type="submit"><?php echo $is_edit_mode ? 'Update Category' : 'Add Category'; ?></button>
                <?php if ($is_edit_mode): ?>
                    <a href="<?php echo BASE_URL; ?>/views/admin/category.php" class="btn btn-secondary">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Categories List Table -->
    <div class="brands-list-container">
        <h3>Existing Categories</h3>
        <?php if (!empty($categories)): ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Category Name</th>
                        <th style="text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $c): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($c['cat_id']); ?></td>
                            <td><?php echo htmlspecialchars($c['cat_name']); ?></td>
                            <td style="text-align: center;">
                                <a href="<?php echo BASE_URL; ?>/views/admin/category.php?edit_id=<?php echo htmlspecialchars($c['cat_id']); ?>" class="action-edit-btn">Edit</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No categories found in the database.</p>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . "/../layout/footer.php"; ?>
