<?php
require_once __DIR__ . '/../config/database.php';

if (!isset($_GET['id'])) {
    die("Invalid request");
}

$flowerId = intval($_GET['id']);

try {
    $pdo = Database::connect();
    
    // Get flower data
    $stmt = $pdo->prepare("SELECT * FROM Flowers WHERE id = ?");
    $stmt->execute([$flowerId]);
    $flower = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$flower) {
        die("Flower not found");
    }
    
    // Get translations
    $translations = [];
    $stmt = $pdo->prepare("SELECT field_name, translated_text FROM Translations WHERE entity_type = 'flower' AND entity_id = ? AND language_code = 'ar'");
    $stmt->execute([$flowerId]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $translations[$row['field_name']] = $row['translated_text'];
    }
    
    // Output the edit form
    ?>
    <div class="row g-3">
        <!-- Flower Name -->
        <div class="col-md-12">
            <label for="edit_flower_name" class="form-label">Flower Name *</label>
            <input type="text" class="form-control" name="name" id="edit_flower_name" value="<?= htmlspecialchars($flower['name']) ?>" required>
        </div>
        <div class="col-md-12">
            <label for="edit_flower_name_ar" class="form-label">اسم الزهرة (العربية)</label>
            <input type="text" class="form-control" name="name_ar" id="edit_flower_name_ar" value="<?= htmlspecialchars($translations['name'] ?? '') ?>" dir="rtl">
        </div>
        
        <!-- Color and Price -->
        <div class="col-md-6">
            <label for="edit_flower_color" class="form-label">Color *</label>
            <input type="text" class="form-control" name="color" id="edit_flower_color" value="<?= htmlspecialchars($flower['color']) ?>" required>
        </div>
        <div class="col-md-6">
            <label for="edit_flower_color_ar" class="form-label">اللون (العربية)</label>
            <input type="text" class="form-control" name="color_ar" id="edit_flower_color_ar" value="<?= htmlspecialchars($translations['color'] ?? '') ?>" dir="rtl">
        </div>
        
        <div class="col-md-6">
            <label for="edit_flower_price" class="form-label">Price per Unit (EGP) *</label>
            <input type="number" step="0.01" min="0" class="form-control" name="price_per_unit" id="edit_flower_price" value="<?= htmlspecialchars($flower['price_per_unit']) ?>" required>
        </div>
        
        <div class="col-md-6">
            <label for="edit_flower_season" class="form-label">Season *</label>
            <input type="text" class="form-control" name="season" id="edit_flower_season" value="<?= htmlspecialchars($flower['season']) ?>" required>
        </div>
        
        <!-- Flower Image -->
        <div class="col-md-12">
            <label class="form-label">Current Image</label>
            <div>
                <img src="/admin/assets/images/flowers/<?= htmlspecialchars($flower['image_url']) ?>" width="100" class="img-thumbnail mb-2">
            </div>
            <label class="form-label">Update Image</label>
            <div class="border border-2 border-dashed rounded p-4 text-center">
                <div class="mb-2">
                    <input type="file" id="edit_flower_image" name="image_url" accept="image/*" class="d-none">
                    <label for="edit_flower_image" class="btn btn-sm btn-plant">
                        <i class="fas fa-upload me-1"></i> Upload New Image
                    </label>
                    <p class="small text-muted mt-2 mb-0">PNG, JPG up to 5MB</p>
                </div>
                <div id="edit-flower-image-preview" class="d-flex flex-wrap gap-2 mt-2 justify-content-center"></div>
            </div>
        </div>
    </div>
    <?php
} catch (Exception $e) {
    die("Error loading flower data: " . $e->getMessage());
}
?>