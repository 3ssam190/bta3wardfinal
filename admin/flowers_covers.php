<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/config/database.php';

// Initialize database connection
try {
    $pdo = Database::connect();
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Handle delete action for flowers
if (isset($_GET['delete_flower'])) {
    $flower_id = intval($_GET['delete_flower']);
    
    try {
        $stmt = $pdo->prepare("DELETE FROM Flowers WHERE id = ?");
        $stmt->execute([$flower_id]);
        $_SESSION['message'] = "Flower deleted successfully";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error deleting flower: " . $e->getMessage();
    }
    
    header('Location: flowers_covers.php');
    exit;
}

// Handle delete action for covers
if (isset($_GET['delete_cover'])) {
    $cover_id = intval($_GET['delete_cover']);
    
    try {
        $stmt = $pdo->prepare("DELETE FROM BouquetCovers WHERE id = ?");
        $stmt->execute([$cover_id]);
        $_SESSION['message'] = "Bouquet cover deleted successfully";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error deleting bouquet cover: " . $e->getMessage();
    }
    
    header('Location: flowers_covers.php');
    exit;
}

// Get all flowers with Arabic translations
$flowers = $pdo->query("
    SELECT f.*, 
        (SELECT translated_text FROM Translations 
         WHERE entity_type = 'flower' 
         AND entity_id = f.id 
         AND field_name = 'name' 
         AND language_code = 'ar' LIMIT 1) AS name_ar,
        (SELECT translated_text FROM Translations 
         WHERE entity_type = 'flower' 
         AND entity_id = f.id 
         AND field_name = 'color' 
         AND language_code = 'ar' LIMIT 1) AS color_ar
    FROM Flowers f
    ORDER BY f.name
")->fetchAll(PDO::FETCH_ASSOC);

// Get all bouquet covers with Arabic translations
$covers = $pdo->query("
    SELECT b.*, 
        (SELECT translated_text FROM Translations 
         WHERE entity_type = 'bouquet' 
         AND entity_id = b.id 
         AND field_name = 'name' 
         AND language_code = 'ar' LIMIT 1) AS name_ar,
        (SELECT translated_text FROM Translations 
         WHERE entity_type = 'bouquet' 
         AND entity_id = b.id 
         AND field_name = 'description' 
         AND language_code = 'ar' LIMIT 1) AS description_ar
    FROM BouquetCovers b
    ORDER BY b.name
")->fetchAll(PDO::FETCH_ASSOC);
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
        <h2 class="h3 mb-3 mb-md-0 header-text">Flowers & Bouquet Covers</h2>
        <div class="d-flex gap-2">
            <!-- Add Flower Button -->
            <button id="addFlowerButton" class="btn btn-plant">
                <i class="fas fa-plus me-1"></i> Add Flower
            </button>
            
            <!-- Add Cover Button -->
            <button id="addCoverButton" class="btn btn-plant">
                <i class="fas fa-plus me-1"></i> Add Cover
            </button>
        </div>
    </div>

    <!-- Tabs for Flowers and Covers -->
    <ul class="nav nav-tabs mb-4" id="flowersCoversTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="flowers-tab" data-bs-toggle="tab" data-bs-target="#flowers" type="button" role="tab">
                Flowers
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="covers-tab" data-bs-toggle="tab" data-bs-target="#covers" type="button" role="tab">
                Bouquet Covers
            </button>
        </li>
    </ul>

    <div class="tab-content" id="flowersCoversTabsContent">
        <!-- Flowers Tab -->
        <div class="tab-pane fade show active" id="flowers" role="tabpanel">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-plant">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Flower</th>
                                    <th>Color</th>
                                    <th>Season</th>
                                    <th>Price</th>
                                    <th>Image</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($flowers as $flower): ?>
                                <tr>
                                    <td><?= htmlspecialchars($flower['id']) ?></td>
                                    <td>
                                        <div class="fw-bold"><?= htmlspecialchars($flower['name']) ?></div>
                                        <?php if (!empty($flower['name_ar'])): ?>
                                        <div class="text-muted small" dir="rtl"><?= htmlspecialchars($flower['name_ar']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($flower['color']) ?>
                                        <?php if (!empty($flower['color_ar'])): ?>
                                        <div class="text-muted small" dir="rtl"><?= htmlspecialchars($flower['color_ar']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($flower['season']) ?></td>
                                    <td>EGP <?= number_format($flower['price_per_unit'], 2) ?></td>
                                    <td>
                                        <img src="assets/images/flowers/<?= htmlspecialchars($flower['image_url'] ?? 'default-flower.jpg') ?>" 
                                             width="40" height="40" class="rounded-circle"
                                             onerror="this.src='assets/images/default-flower.jpg'">
                                    </td>
                                    <td>
                                        <button data-flower-id="<?= $flower['id'] ?>" 
                                                class="btn btn-sm btn-outline-plant me-2 edit-flower-btn">
                                            Edit
                                        </button>
                                        <a href="flowers_covers.php?delete_flower=<?= $flower['id'] ?>&csrf_token=<?= $_SESSION['csrf_token'] ?>" 
                                           class="btn btn-sm btn-outline-danger" 
                                           onclick="return confirm('Are you sure you want to delete this flower?')">
                                            Delete
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Covers Tab -->
        <div class="tab-pane fade" id="covers" role="tabpanel">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-plant">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Cover</th>
                                    <th>Price</th>
                                    <th>Description</th>
                                    <th>Image</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($covers as $cover): ?>
                                <tr>
                                    <td><?= htmlspecialchars($cover['id']) ?></td>
                                    <td>
                                        <div class="fw-bold"><?= htmlspecialchars($cover['name']) ?></div>
                                        <?php if (!empty($cover['name_ar'])): ?>
                                        <div class="text-muted small" dir="rtl"><?= htmlspecialchars($cover['name_ar']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>EGP <?= number_format($cover['price'], 2) ?></td>
                                    <td>
                                        <?= substr(htmlspecialchars($cover['description']), 0, 30) ?>...
                                        <?php if (!empty($cover['description_ar'])): ?>
                                        <div class="text-muted small" dir="rtl"><?= substr(htmlspecialchars($cover['description_ar']), 0, 30) ?>...</div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <img src="assets/images/covers/<?= htmlspecialchars($cover['image_url'] ?? 'default-cover.jpg') ?>" 
                                             width="40" height="40" class="rounded-circle"
                                             onerror="this.src='assets/images/default-cover.jpg'">
                                    </td>
                                    <td>
                                        <button data-cover-id="<?= $cover['id'] ?>" 
                                                class="btn btn-sm btn-outline-plant me-2 edit-cover-btn">
                                            Edit
                                        </button>
                                        <a href="flowers_covers.php?delete_cover=<?= $cover['id'] ?>&csrf_token=<?= $_SESSION['csrf_token'] ?>" 
                                           class="btn btn-sm btn-outline-danger" 
                                           onclick="return confirm('Are you sure you want to delete this bouquet cover?')">
                                            Delete
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Flower Modal -->
    <div class="modal fade" id="addFlowerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="actions/add_flower.php" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <div class="modal-header bg-plant header-text">
                        <h5 class="modal-title">Add New Flower</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <!-- Flower Name -->
                            <div class="col-md-12">
                                <label for="flower_name" class="form-label">Flower Name *</label>
                                <input type="text" class="form-control" name="name" id="flower_name" required>
                            </div>
                            <div class="col-md-12">
                                <label for="flower_name_ar" class="form-label">اسم الزهرة (العربية)</label>
                                <input type="text" class="form-control" name="name_ar" id="flower_name_ar" dir="rtl">
                            </div>
                            
                            <!-- Color and Price -->
                            <div class="col-md-6">
                                <label for="flower_color" class="form-label">Color *</label>
                                <input type="text" class="form-control" name="color" id="flower_color" required>
                            </div>
                            <div class="col-md-6">
                                <label for="flower_color_ar" class="form-label">اللون (العربية)</label>
                                <input type="text" class="form-control" name="color_ar" id="flower_color_ar" dir="rtl">
                            </div>
                            
                            <div class="col-md-6">
                                <label for="flower_price" class="form-label">Price per Unit (EGP) *</label>
                                <input type="number" step="0.01" min="0" class="form-control" name="price_per_unit" id="flower_price" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="flower_season" class="form-label">Season *</label>
                                <input type="text" class="form-control" name="season" id="flower_season" required>
                            </div>
                            
                            <!-- Flower Image -->
                            <div class="col-md-12">
                                <label class="form-label">Flower Image *</label>
                                <div class="border border-2 border-dashed rounded p-4 text-center">
                                    <div class="mb-2">
                                        <input type="file" id="flower_image" name="image_url" required accept="image/*" class="d-none">
                                        <label for="flower_image" class="btn btn-sm btn-plant">
                                            <i class="fas fa-upload me-1"></i> Upload Image
                                        </label>
                                        <p class="small text-muted mt-2 mb-0">PNG, JPG up to 5MB</p>
                                    </div>
                                    <div id="flower-image-preview" class="d-flex flex-wrap gap-2 mt-2 justify-content-center"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_flower" class="btn btn-primary">Add Flower</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Cover Modal -->
    <div class="modal fade" id="addCoverModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="actions/add_cover.php" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <div class="modal-header bg-plant header-text">
                        <h5 class="modal-title">Add New Bouquet Cover</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <!-- Cover Name -->
                            <div class="col-md-12">
                                <label for="cover_name" class="form-label">Cover Name *</label>
                                <input type="text" class="form-control" name="name" id="cover_name" required>
                            </div>
                            <div class="col-md-12">
                                <label for="cover_name_ar" class="form-label">اسم الغلاف (العربية)</label>
                                <input type="text" class="form-control" name="name_ar" id="cover_name_ar" dir="rtl">
                            </div>
                            
                            <!-- Price and Description -->
                            <div class="col-md-6">
                                <label for="cover_price" class="form-label">Price (EGP) *</label>
                                <input type="number" step="0.01" min="0" class="form-control" name="price" id="cover_price" required>
                            </div>
                            
                            <div class="col-md-12">
                                <label for="cover_description" class="form-label">Description</label>
                                <textarea class="form-control" name="description" id="cover_description" rows="2"></textarea>
                            </div>
                            <div class="col-md-12">
                                <label for="cover_description_ar" class="form-label">الوصف (العربية)</label>
                                <textarea class="form-control" name="description_ar" id="cover_description_ar" rows="2" dir="rtl"></textarea>
                            </div>
                            
                            <!-- Cover Image -->
                            <div class="col-md-12">
                                <label class="form-label">Cover Image *</label>
                                <div class="border border-2 border-dashed rounded p-4 text-center">
                                    <div class="mb-2">
                                        <input type="file" id="cover_image" name="image_url" required accept="image/*" class="d-none">
                                        <label for="cover_image" class="btn btn-sm btn-plant">
                                            <i class="fas fa-upload me-1"></i> Upload Image
                                        </label>
                                        <p class="small text-muted mt-2 mb-0">PNG, JPG up to 5MB</p>
                                    </div>
                                    <div id="cover-image-preview" class="d-flex flex-wrap gap-2 mt-2 justify-content-center"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_cover" class="btn btn-primary">Add Cover</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Flower Modal -->
    <div class="modal fade" id="editFlowerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="actions/update_flower.php" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <input type="hidden" name="flower_id" id="edit_flower_id">
                    <div class="modal-header bg-plant header-text">
                        <h5 class="modal-title">Edit Flower</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="editFlowerModalBody">
                        <!-- Content will be loaded dynamically via JavaScript -->
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="update_flower" class="btn btn-primary">Update Flower</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Cover Modal -->
    <div class="modal fade" id="editCoverModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="actions/update_cover.php" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <input type="hidden" name="cover_id" id="edit_cover_id">
                    <div class="modal-header bg-plant header-text">
                        <h5 class="modal-title">Edit Bouquet Cover</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="editCoverModalBody">
                        <!-- Content will be loaded dynamically via JavaScript -->
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="update_cover" class="btn btn-primary">Update Cover</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<script>
// JavaScript to handle modals and dynamic loading
document.addEventListener('DOMContentLoaded', function() {
    // Add Flower Button
    document.getElementById('addFlowerButton').addEventListener('click', function() {
        var modal = new bootstrap.Modal(document.getElementById('addFlowerModal'));
        modal.show();
    });

    // Add Cover Button
    document.getElementById('addCoverButton').addEventListener('click', function() {
        var modal = new bootstrap.Modal(document.getElementById('addCoverModal'));
        modal.show();
    });

    // Edit Flower Buttons
    document.querySelectorAll('.edit-flower-btn').forEach(button => {
        button.addEventListener('click', function() {
            const flowerId = this.getAttribute('data-flower-id');
            document.getElementById('edit_flower_id').value = flowerId;
            
            // Load flower data via AJAX
            fetch(`actions/get_flower.php?id=${flowerId}`)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('editFlowerModalBody').innerHTML = html;
                    var modal = new bootstrap.Modal(document.getElementById('editFlowerModal'));
                    modal.show();
                });
        });
    });

    // Edit Cover Buttons
    document.querySelectorAll('.edit-cover-btn').forEach(button => {
        button.addEventListener('click', function() {
            const coverId = this.getAttribute('data-cover-id');
            document.getElementById('edit_cover_id').value = coverId;
            
            // Load cover data via AJAX
            fetch(`actions/get_cover.php?id=${coverId}`)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('editCoverModalBody').innerHTML = html;
                    var modal = new bootstrap.Modal(document.getElementById('editCoverModal'));
                    modal.show();
                });
        });
    });

    // Image preview for flower upload
    document.getElementById('flower_image').addEventListener('change', function(e) {
        const preview = document.getElementById('flower-image-preview');
        preview.innerHTML = '';
        
        if (this.files && this.files[0]) {
            const img = document.createElement('img');
            img.src = URL.createObjectURL(this.files[0]);
            img.width = 100;
            img.height = 100;
            img.className = 'img-thumbnail';
            preview.appendChild(img);
        }
    });

    // Image preview for cover upload
    document.getElementById('cover_image').addEventListener('change', function(e) {
        const preview = document.getElementById('cover-image-preview');
        preview.innerHTML = '';
        
        if (this.files && this.files[0]) {
            const img = document.createElement('img');
            img.src = URL.createObjectURL(this.files[0]);
            img.width = 100;
            img.height = 100;
            img.className = 'img-thumbnail';
            preview.appendChild(img);
        }
    });
});
</script>

<?php 
require_once __DIR__ . '/includes/footer.php';
?>