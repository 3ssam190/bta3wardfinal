<?php
require_once __DIR__ . '/../config/database.php';

if (!isset($_GET['id'])) {
    die("Invalid request");
}

$coverId = intval($_GET['id']);

try {
    $pdo = Database::connect();
    
    // Get cover data
    $stmt = $pdo->prepare("SELECT * FROM BouquetCovers WHERE id = ?");
    $stmt->execute([$coverId]);
    $cover = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$cover) {
        die("Bouquet cover not found");
    }
    
    // Get translations
    $translations = [];
    $stmt = $pdo->prepare("SELECT field_name, translated_text FROM Translations WHERE entity_type = 'bouquet' AND entity_id = ? AND language_code = 'ar'");
    $stmt->execute([$coverId]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $translations[$row['field_name']] = $row['translated_text'];
    }
    
    // Output the edit form
    ?>
    <div class="row g-3">
        <!-- Cover Name -->
        <div class="col-md-12">
            <label for="edit_cover_name" class="form-label">Cover Name *</label>
            <input type="text" class="form-control" name="name" id="edit_cover_name" value="<?= htmlspecialchars($cover['name']) ?>" required>
        </div>
        <div class="col-md-12">
            <label for="edit_cover_name_ar" class="form-label">اسم الغلاف (العربية)</label>
            <input type="text" class="form-control" name="name_ar" id="edit_cover_name_ar" value="<?= htmlspecialchars($translations['name'] ?? '') ?>" dir="rtl">
        </div>
        
        <!-- Price and Description -->
        <div class="col-md-6">
            <label for="edit_cover_price" class="form-label">Price (EGP) *</label>
            <input type="number" step="0.01" min="0" class="form-control" name="price" id="edit_cover_price" value="<?= htmlspecialchars($cover['price']) ?>" required>
        </div>
        
        <div class="col-md-12">
            <label for="edit_cover_description" class="form-label">Description</label>
            <textarea class="form-control" name="description" id="edit_cover_description" rows="2"><?= htmlspecialchars($cover['description']) ?></textarea>
        </div>
        <div class="col-md-12">
            <label for="edit_cover_description_ar" class="form-label">الوصف (العربية)</label>
            <textarea class="form-control" name="description_ar" id="edit_cover_description_ar" rows="2" dir="rtl"><?= htmlspecialchars($translations['description'] ?? '') ?></textarea>
        </div>
        
        <!-- Cover Image -->
        <div class="col-md-12">
            <label class="form-label">Current Image</label>
            <div>
                <img src="/admin/assets/images/covers/<?= htmlspecialchars($cover['image_url']) ?>" width="100" class="img-thumbnail mb-2">
            </div>
            <label class="form-label">Update Image</label>
            <div class="border border-2 border-dashed rounded p-4 text-center">
                <div class="mb-2">
                    <input type="file" id="edit_cover_image" name="image_url" accept="image/*" class="d-none">
                    <label for="edit_cover_image" class="btn btn-sm btn-plant">
                        <i class="fas fa-upload me-1"></i> Upload New Image
                    </label>
                    <p class="small text-muted mt-2 mb-0">PNG, JPG up to 5MB</p>
                </div>
                <div id="edit-cover-image-preview" class="d-flex flex-wrap gap-2 mt-2 justify-content-center"></div>
            </div>
        </div>
    </div>
    <?php
} catch (Exception $e) {
    die("Error loading cover data: " . $e->getMessage());
}
?>