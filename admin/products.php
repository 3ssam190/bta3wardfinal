<?php
require_once __DIR__ . '/includes/header.php';
// require_once __DIR__ . '/includes/topbar.php';
require_once __DIR__ . '/config/database.php';

// Initialize database connection
try {
    $pdo = Database::connect();
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

function saveProductTranslation($productId, $field, $lang, $text) {
    global $conn;
    
    // Check if translation exists
    $stmt = $conn->prepare("
        SELECT translation_id FROM Translations 
        WHERE entity_type = 'product' 
        AND entity_id = ? 
        AND field_name = ? 
        AND language_code = ?
    ");
    $stmt->bind_param("iss", $productId, $field, $lang);
    $stmt->execute();
    $exists = $stmt->get_result()->fetch_assoc();
    
    if ($exists) {
        // Update existing
        $stmt = $conn->prepare("
            UPDATE Translations SET translated_text = ? 
            WHERE translation_id = ?
        ");
        $stmt->bind_param("si", $text, $exists['translation_id']);
    } else {
        // Insert new
        $stmt = $conn->prepare("
            INSERT INTO Translations 
            (entity_type, entity_id, field_name, language_code, translated_text) 
            VALUES ('product', ?, ?, ?, ?)
        ");
        $stmt->bind_param("isss", $productId, $field, $lang, $text);
    }
    
    return $stmt->execute();
}

// Handle delete action
if (isset($_GET['delete'])) {
    $product_id = intval($_GET['delete']);
    
    try {
        $pdo->beginTransaction();
        
        // First delete images from server and database
        $stmt = $pdo->prepare("SELECT image_url FROM ProductImages WHERE product_id = ?");
        $stmt->execute([$product_id]);
        $images = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        foreach ($images as $image) {
            $file_path = __DIR__ . '/assets/images/products/' . $image;
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
        
        // Delete from ProductImages
        $stmt = $pdo->prepare("DELETE FROM ProductImages WHERE product_id = ?");
        $stmt->execute([$product_id]);
        
        // Then delete product
        $stmt = $pdo->prepare("DELETE FROM Products WHERE product_id = ?");
        $stmt->execute([$product_id]);
        
        $pdo->commit();
        $_SESSION['message'] = "Product deleted successfully";
    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Error deleting product: " . $e->getMessage();
    }
    
    header('Location: products.php');
    exit;
}






// Get filter inputs safely from GET
$filter_category = isset($_GET['category']) && $_GET['category'] !== '' ? intval($_GET['category']) : null;
$filter_featured = isset($_GET['featured']) && $_GET['featured'] !== '' ? $_GET['featured'] : null;
$filter_stock = isset($_GET['stock']) && $_GET['stock'] !== '' ? $_GET['stock'] : null;

// Pagination setup
$perPage = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $perPage;

// Build WHERE clauses and params
$whereClauses = [];
$params = [];

if ($filter_category) {
    $whereClauses[] = "p.category_id = ?";
    $params[] = $filter_category;
}

if ($filter_featured !== null) {
    $whereClauses[] = "p.is_featured = ?";
    $params[] = $filter_featured;
}

if ($filter_stock === 'low') {
    $whereClauses[] = "p.stock_quantity < 10 AND p.stock_quantity > 0";
} elseif ($filter_stock === 'out') {
    $whereClauses[] = "p.stock_quantity = 0";
}

$whereSQL = '';
if ($whereClauses) {
    $whereSQL = 'WHERE ' . implode(' AND ', $whereClauses);
}

// 1. Get total count of filtered products
$countQuery = "SELECT COUNT(*) FROM Products p $whereSQL";
$countStmt = $pdo->prepare($countQuery);
foreach ($params as $index => $value) {
    $countStmt->bindValue($index + 1, $value);
}
$countStmt->execute();
$totalProducts = $countStmt->fetchColumn();
$totalPages = ceil($totalProducts / $perPage);

// 2. Get filtered products with pagination
$productQuery = "
    SELECT 
        p.*, 
        c.name AS category_name,
        (SELECT image_url FROM ProductImages WHERE product_id = p.product_id AND is_primary = 1 LIMIT 1) AS primary_image,
        (SELECT translated_text FROM Translations 
         WHERE entity_type = 'product' 
         AND entity_id = p.product_id 
         AND field_name = 'name' 
         AND language_code = 'ar' LIMIT 1) AS name_ar,
        (SELECT translated_text FROM Translations 
         WHERE entity_type = 'product' 
         AND entity_id = p.product_id 
         AND field_name = 'description' 
         AND language_code = 'ar' LIMIT 1) AS description_ar
    FROM Products p
    JOIN Categories c ON p.category_id = c.category_id
    $whereSQL
    ORDER BY p.product_id DESC
    LIMIT ? OFFSET ?
";

$productStmt = $pdo->prepare($productQuery);

// Bind filter params (1 to N)
$paramIndex = 1;
foreach ($params as $value) {
    $productStmt->bindValue($paramIndex, $value);
    $paramIndex++;
}

// Bind limit and offset (last two positional params)
$productStmt->bindValue($paramIndex++, $perPage, PDO::PARAM_INT);
$productStmt->bindValue($paramIndex++, $offset, PDO::PARAM_INT);

$productStmt->execute();
$products = $productStmt->fetchAll(PDO::FETCH_ASSOC);

// Get categories for dropdown (no filter)
$categories = $pdo->query("SELECT * FROM Categories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
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
        <h2 class="h3 mb-3 mb-md-0 header-text">Products Management</h2>
        <div class="d-flex gap-2">
            <!-- Search Box -->
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" class="form-control" placeholder="Search products..." id="productSearch">
            </div>
            
            <!-- Add Product Button -->
            <button id="addProductButton" class="btn btn-plant">
                <i class="fas fa-plus me-1"></i> Add Plant
            </button>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3" id="filterForm">
                <div class="col-md-3">
                    <label for="category" class="form-label">Category</label>
                    <select class="form-select" name="category" id="category">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['category_id'] ?>" <?= ($_GET['category'] ?? '') == $category['category_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($category['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="featured" class="form-label">Featured</label>
                    <select class="form-select" name="featured" id="featured">
                        <option value="">All</option>
                        <option value="1" <?= ($_GET['featured'] ?? '') === '1' ? 'selected' : '' ?>>Featured Only</option>
                        <option value="0" <?= ($_GET['featured'] ?? '') === '0' ? 'selected' : '' ?>>Non-Featured</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="stock" class="form-label">Stock</label>
                    <select class="form-select" name="stock" id="stock">
                        <option value="">All</option>
                        <option value="low" <?= ($_GET['stock'] ?? '') === 'low' ? 'selected' : '' ?>>Low Stock (<10)</option>
                        <option value="out" <?= ($_GET['stock'] ?? '') === 'out' ? 'selected' : '' ?>>Out of Stock</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">Filter</button>
                    <a href="products.php" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>
    <!-- Products Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-plant" id="productsTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Plant</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Featured</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?= htmlspecialchars($product['product_id']) ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img class="rounded-circle me-3" width="40" height="40"
                                         src="assets/images/products/<?= htmlspecialchars($product['primary_image'] ?? 'default-product.jpg') ?>"
                                         alt="<?= htmlspecialchars($product['name']) ?>"
                                         onerror="this.src='assets/images/default-product.jpg'">
                                    <div>
                                        <div class="fw-bold"><?= htmlspecialchars($product['name']) ?></div>
                                        <?php if (!empty($product['name_ar'])): ?>
                                        <div class="text-muted small" dir="rtl"><?= htmlspecialchars($product['name_ar']) ?></div>
                                        <?php endif; ?>
                                        <div class="text-muted small">
                                            <?= substr(htmlspecialchars($product['description']), 0, 30) ?>...
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($product['category_name']) ?></td>
                            <td>EGP <?= number_format($product['price'], 2) ?></td>
                            <td><?= htmlspecialchars($product['stock_quantity']) ?></td>
                            <td>
                                <span class="badge <?= $product['is_featured'] ? 'bg-success' : 'bg-secondary' ?>">
                                    <?= $product['is_featured'] ? 'Yes' : 'No' ?>
                                </span>
                            </td>
                            <td>
                                <button data-product-id="<?= $product['product_id'] ?>" 
                                        class="btn btn-sm btn-outline-plant me-2 edit-product-btn">
                                    Edit
                                </button>
                                <a href="products.php?delete=<?= $product['product_id'] ?>&csrf_token=<?= $_SESSION['csrf_token'] ?>" 
                                   class="btn btn-sm btn-outline-danger" 
                                   onclick="return confirm('Are you sure you want to delete this plant? This action cannot be undone.')">
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
                        <span class="fw-bold"><?= min($offset + $perPage, $totalProducts) ?></span> of 
                        <span class="fw-bold"><?= $totalProducts ?></span> results
                    </p>
                </div>
                <ul class="pagination mb-0">
                    <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="products.php?page=<?= $page - 1 ?>">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="products.php?page=<?= $i ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="products.php?page=<?= $page + 1 ?>">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>

    <!-- Add Product Modal -->
    <div class="modal fade" id="addProductModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" action="actions/add_product.php" enctype="multipart/form-data" id="addProductForm">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <div class="modal-header bg-plant header-text">
                        <h5 class="modal-title">Add New Product</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <!-- Product Name -->
                            <div class="col-md-12">
                                <label for="name" class="form-label">Product Name *</label>
                                <input type="text" class="form-control" name="name" id="name" required>
                            </div>
                            <div class="col-md-12">
                                <label for="name_ar" class="form-label">اسم المنتج (العربية)</label>
                                <input type="text" class="form-control" name="name_ar" id="name_ar" dir="rtl">
                            </div>
                            
                            <!-- Description -->
                            <div class="col-md-12">
                                <label for="description" class="form-label">Description *</label>
                                <textarea class="form-control" name="description" id="description" rows="3" required></textarea>
                            </div>
                            <div class="col-md-12">
                                <label for="description_ar" class="form-label">الوصف (العربية)</label>
                                <textarea class="form-control" name="description_ar" id="description_ar" rows="3" dir="rtl"></textarea>
                            </div>
                            
                            <!-- Category and Price -->
                            <div class="col-md-6">
                                <label for="category_id" class="form-label">Category *</label>
                                <select class="form-select" name="category_id" id="category_id" required>
                                    <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category['category_id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="price" class="form-label">Price (EGP) *</label>
                                <input type="number" step="0.01" min="0" class="form-control" name="price" id="price" required>
                            </div>
                            
                            <!-- Stock and Environment -->
                            <div class="col-md-6">
                                <label for="stock_quantity" class="form-label">Stock Quantity *</label>
                                <input type="number" min="0" class="form-control" name="stock_quantity" id="stock_quantity" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="environment_suitability" class="form-label">Environment</label>
                                <input type="text" class="form-control" name="environment_suitability" id="environment_suitability" placeholder="Indoor, Outdoor, etc.">
                            </div>
                            <div class="col-md-6">
                                <label for="environment_suitability_ar" class="form-label">البيئة المناسبة (العربية)</label>
                                <input type="text" class="form-control" name="environment_suitability_ar" id="environment_suitability_ar" dir="rtl">
                            </div>
                            
                            <!-- Care Instructions -->
                            <div class="col-md-12">
                                <label for="care_instructions" class="form-label">Care Instructions</label>
                                <textarea class="form-control" name="care_instructions" id="care_instructions" rows="2"></textarea>
                            </div>
                            <div class="col-md-12">
                                <label for="care_instructions_ar" class="form-label">تعليمات العناية (العربية)</label>
                                <textarea class="form-control" name="care_instructions_ar" id="care_instructions_ar" rows="2" dir="rtl"></textarea>
                            </div>
                            
                            <!-- Featured Product -->
                            <div class="col-md-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_featured" id="is_featured">
                                    <label class="form-check-label" for="is_featured">Featured Product</label>
                                </div>
                            </div>
                            
                            <!-- Product Images -->
                            <div class="col-md-12">
                                <label class="form-label">Product Images *</label>
                                <div class="border border-2 border-dashed rounded p-4 text-center">
                                    <div class="mb-2">
                                        <input type="file" id="product_images" name="product_images[]" multiple required accept="image/*" class="d-none">
                                        <label for="product_images" class="btn btn-sm btn-plant">
                                            <i class="fas fa-upload me-1"></i> Upload files
                                        </label>
                                        <p class="small text-muted mt-2 mb-0">or drag and drop</p>
                                        <p class="small text-muted">PNG, JPG, GIF up to 5MB each (max 5 images)</p>
                                    </div>
                                    <div id="image-preview" class="d-flex flex-wrap gap-2 mt-2 justify-content-center"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_product" class="btn btn-primary">Add Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Product Modal -->
    <div class="modal fade" id="editProductModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" action="actions/update_product.php" enctype="multipart/form-data" id="editProductForm">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <input type="hidden" name="product_id" id="edit_product_id">
                    <div class="modal-header bg-plant header-text">
                        <h5 class="modal-title">Edit Product</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="editProductModalBody">
                        <!-- Content will be loaded dynamically via JavaScript -->
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="update_product" class="btn btn-primary">Update Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<?php 
require_once __DIR__ . '/includes/footer.php';
?>