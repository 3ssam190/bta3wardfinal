<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/config/database.php';

// Initialize database connection
try {
    $pdo = Database::connect();
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Handle delete action
if (isset($_GET['delete'])) {
    $category_id = intval($_GET['delete']);
    
    // Check if category is used by any products
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM Products WHERE category_id = ?");
    $stmt->execute([$category_id]);
    $product_count = $stmt->fetchColumn();
    
    if ($product_count > 0) {
        $_SESSION['error'] = "Cannot delete category - it is being used by $product_count products";
    } else {
        try {
            $stmt = $pdo->prepare("DELETE FROM Categories WHERE category_id = ?");
            $stmt->execute([$category_id]);
            $_SESSION['message'] = "Category deleted successfully";
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error deleting category: " . $e->getMessage();
        }
    }
    
    header('Location: categories.php');
    exit;
}

// Handle form submission for add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $csrf_token)) {
        $_SESSION['error'] = "CSRF token validation failed";
        header('Location: categories.php');
        exit;
    }

    $category_id = $_POST['category_id'] ?? null;
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);

    // Validate inputs
    if (empty($name)) {
        $_SESSION['error'] = "Category name is required";
        header('Location: categories.php');
        exit;
    }

    try {
        if ($category_id) {
            // Update existing category
            $stmt = $pdo->prepare("UPDATE Categories SET name = ?, description = ?, updated_at = NOW() WHERE category_id = ?");
            $stmt->execute([$name, $description, $category_id]);
            $_SESSION['message'] = "Category updated successfully";
        } else {
            // Add new category
            $stmt = $pdo->prepare("INSERT INTO Categories (name, description) VALUES (?, ?)");
            $stmt->execute([$name, $description]);
            $_SESSION['message'] = "Category added successfully";
        }
    } catch (PDOException $e) {
        // Check for duplicate entry error
        if ($e->errorInfo[1] == 1062) {
            $_SESSION['error'] = "A category with this name already exists";
        } else {
            $_SESSION['error'] = "Database error: " . $e->getMessage();
        }
    }

    header('Location: categories.php');
    exit;
}

// Pagination setup
$perPage = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $perPage;

// Get total number of categories
$totalCategories = $pdo->query("SELECT COUNT(*) FROM Categories")->fetchColumn();
$totalPages = ceil($totalCategories / $perPage);

// Get categories with product counts
$query = "
    SELECT 
        c.*,
        COUNT(p.product_id) AS product_count
    FROM Categories c
    LEFT JOIN Products p ON c.category_id = p.category_id
    GROUP BY c.category_id
    ORDER BY c.name
    LIMIT :limit OFFSET :offset
";

$stmt = $pdo->prepare($query);
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Main Content -->
<main class="container-fluid py-4" style="margin-top: 70px;">
    <!-- Success/Error Messages -->
    <?php if (isset($_SESSION['message'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_SESSION['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['message']); endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_SESSION['error']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['error']); endif; ?>

    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-3 mb-md-0 header-text">Categories Management</h2>
        <div>
            <!-- Add Category Button -->
            <button id="addCategoryButton" class="btn btn-plant">
                <i class="fas fa-plus me-1"></i> Add Category
            </button>
        </div>
    </div>

    <!-- Categories Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-plant" id="categoriesTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Products</th>
                            <th>Created</th>
                            <th>Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $category): ?>
                        <tr>
                            <td><?= htmlspecialchars($category['category_id']) ?></td>
                            <td class="fw-bold"><?= htmlspecialchars($category['name']) ?></td>
                            <td><?= htmlspecialchars(substr($category['description'], 0, 50)) ?><?= strlen($category['description']) > 50 ? '...' : '' ?></td>
                            <td><?= htmlspecialchars($category['product_count']) ?></td>
                            <td><?= date('M j, Y', strtotime($category['created_at'])) ?></td>
                            <td><?= date('M j, Y', strtotime($category['updated_at'])) ?></td>
                            <td>
                                <button data-category-id="<?= $category['category_id'] ?>" 
                                        data-category-name="<?= htmlspecialchars($category['name']) ?>"
                                        data-category-description="<?= htmlspecialchars($category['description']) ?>"
                                        class="btn btn-sm btn-outline-plant me-2 edit-category-btn">
                                    Edit
                                </button>
                                <a href="categories.php?delete=<?= $category['category_id'] ?>&csrf_token=<?= $_SESSION['csrf_token'] ?>" 
                                   class="btn btn-sm btn-outline-danger" 
                                   onclick="return confirm('Are you sure you want to delete this category? This action cannot be undone.')">
                                    Delete
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <nav class="d-flex justify-content-between align-items-center mt-4">
                <div>
                    <p class="small text-muted mb-0">
                        Showing <span class="fw-bold"><?= $offset + 1 ?></span> to 
                        <span class="fw-bold"><?= min($offset + $perPage, $totalCategories) ?></span> of 
                        <span class="fw-bold"><?= $totalCategories ?></span> categories
                    </p>
                </div>
                <ul class="pagination mb-0">
                    <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="categories.php?page=<?= $page - 1 ?>">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="categories.php?page=<?= $i ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="categories.php?page=<?= $page + 1 ?>">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>

    <!-- Add Category Modal -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="categories.php" id="addCategoryForm">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <div class="modal-header bg-plant header-text">
                        <h5 class="modal-title">Add New Category</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Category Name *</label>
                            <input type="text" class="form-control" name="name" id="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" name="description" id="description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="save_category" class="btn btn-primary">Save Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Category Modal -->
    <div class="modal fade" id="editCategoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="categories.php" id="editCategoryForm">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <input type="hidden" name="category_id" id="edit_category_id">
                    <div class="modal-header bg-plant header-text">
                        <h5 class="modal-title">Edit Category</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_name" class="form-label">Category Name *</label>
                            <input type="text" class="form-control" name="name" id="edit_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_description" class="form-label">Description</label>
                            <textarea class="form-control" name="description" id="edit_description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="update_category" class="btn btn-primary">Update Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<script>
// JavaScript for handling modals
document.addEventListener('DOMContentLoaded', function() {
    // Add Category Modal
    const addCategoryButton = document.getElementById('addCategoryButton');
    const addCategoryModal = new bootstrap.Modal(document.getElementById('addCategoryModal'));
    
    if (addCategoryButton) {
        addCategoryButton.addEventListener('click', function() {
            document.getElementById('addCategoryForm').reset();
            addCategoryModal.show();
        });
    }

    // Edit Category Modal
    const editCategoryButtons = document.querySelectorAll('.edit-category-btn');
    const editCategoryModal = new bootstrap.Modal(document.getElementById('editCategoryModal'));
    
    editCategoryButtons.forEach(button => {
        button.addEventListener('click', function() {
            const categoryId = this.getAttribute('data-category-id');
            const categoryName = this.getAttribute('data-category-name');
            const categoryDescription = this.getAttribute('data-category-description');
            
            document.getElementById('edit_category_id').value = categoryId;
            document.getElementById('edit_name').value = categoryName;
            document.getElementById('edit_description').value = categoryDescription;
            
            editCategoryModal.show();
        });
    });

    // Form validation
    const forms = document.querySelectorAll('form[id$="CategoryForm"]');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const nameInput = form.querySelector('input[name="name"]');
            if (nameInput.value.trim() === '') {
                e.preventDefault();
                nameInput.classList.add('is-invalid');
                nameInput.focus();
            }
        });
    });
});
</script>

<?php 
require_once __DIR__ . '/includes/footer.php';
?>