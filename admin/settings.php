<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/config/database.php';

// Initialize database connection
try {
    $pdo = Database::connect();
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            throw new Exception('Invalid CSRF token');
        }

        // Handle different setting sections
        if (isset($_POST['update_profile'])) {
            // Update profile information
            $first_name = trim($_POST['first_name']);
            $last_name = trim($_POST['last_name']);
            $email = trim($_POST['email']);
            
            // Validate inputs
            if (empty($first_name) || empty($last_name) || empty($email)) {
                throw new Exception('All fields are required');
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Invalid email format');
            }
            
            // Check if email is already taken by another admin
            $stmt = $pdo->prepare("SELECT admin_id FROM Admins WHERE email = ? AND admin_id != ?");
            $stmt->execute([$email, $_SESSION['admin_id']]);
            if ($stmt->fetch()) {
                throw new Exception('Email already in use by another admin');
            }
            
            // Update admin profile
            $stmt = $pdo->prepare("UPDATE Admins SET first_name = ?, last_name = ?, email = ? WHERE admin_id = ?");
            $stmt->execute([$first_name, $last_name, $email, $_SESSION['admin_id']]);
            
            // Update session data
            $_SESSION['admin_name'] = "$first_name $last_name";
            $_SESSION['admin_email'] = $email;
            
            $_SESSION['message'] = "Profile updated successfully";
            
        } elseif (isset($_POST['change_password'])) {
            // Change password
            $current_password = $_POST['current_password'];
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];
            
            // Validate inputs
            if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
                throw new Exception('All password fields are required');
            }
            
            if ($new_password !== $confirm_password) {
                throw new Exception('New passwords do not match');
            }
            
            if (strlen($new_password) < 8) {
                throw new Exception('Password must be at least 8 characters');
            }
            
            // Verify current password
            $stmt = $pdo->prepare("SELECT password_hash FROM Admins WHERE admin_id = ?");
            $stmt->execute([$_SESSION['admin_id']]);
            $admin = $stmt->fetch();
            
            if (!$admin || !password_verify($current_password, $admin['password_hash'])) {
                throw new Exception('Current password is incorrect');
            }
            
            // Update password
            $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE Admins SET password_hash = ? WHERE admin_id = ?");
            $stmt->execute([$new_password_hash, $_SESSION['admin_id']]);
            
            $_SESSION['message'] = "Password changed successfully";
            
        } elseif (isset($_POST['update_system_settings'])) {
            // System settings would typically be stored in a separate table
            // This is just a basic example
            
            $site_name = trim($_POST['site_name']);
            $site_email = trim($_POST['site_email']);
            $items_per_page = (int)$_POST['items_per_page'];
            
            // Validate inputs
            if (empty($site_name) || empty($site_email)) {
                throw new Exception('Site name and email are required');
            }
            
            if (!filter_var($site_email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Invalid site email format');
            }
            
            if ($items_per_page < 5 || $items_per_page > 100) {
                throw new Exception('Items per page must be between 5 and 100');
            }
            
            // In a real application, you would save these to a settings table
            // For now, we'll just show a success message
            $_SESSION['message'] = "System settings updated successfully";
        }
        
        // Redirect to prevent form resubmission
        header('Location: settings.php');
        exit;
        
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header('Location: settings.php');
        exit;
    }
}

// Get current admin data
$stmt = $pdo->prepare("SELECT * FROM Admins WHERE admin_id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

// Default system settings (in a real app, these would come from a database)
$system_settings = [
    'site_name' => 'Bta3 Ward',
    'site_email' => 'support@bta3ward.shop',
    'items_per_page' => 10,
    'maintenance_mode' => false
];
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

    <h2 class="h3 mb-4 header-text">Settings</h2>
    
    <div class="row">
        <div class="col-md-3">
            <!-- Settings Navigation -->
            <div class="card mb-4">
                <div class="card-body">
                    <ul class="nav nav-pills flex-column" id="settingsTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="profile-tab" data-bs-toggle="pill" href="#profile" role="tab">
                                <i class="fas fa-user me-2"></i> Profile
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="password-tab" data-bs-toggle="pill" href="#password" role="tab">
                                <i class="fas fa-lock me-2"></i> Password
                            </a>
                        </li>
                        <?php if ($_SESSION['admin_role'] === 'Super Admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" id="system-tab" data-bs-toggle="pill" href="#system" role="tab">
                                <i class="fas fa-cog me-2"></i> System Settings
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="col-md-9">
            <!-- Settings Content -->
            <div class="tab-content" id="settingsTabsContent">
                <!-- Profile Settings -->
                <div class="tab-pane fade show active" id="profile" role="tabpanel">
                    <div class="card">
                        <div class="card-header bg-header header-text">
                            <h3 class="h5 mb-0">Profile Settings</h3>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="first_name" class="form-label">First Name *</label>
                                        <input type="text" class="form-control" name="first_name" id="first_name" 
                                               value="<?= htmlspecialchars($admin['first_name']) ?>" required>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="last_name" class="form-label">Last Name *</label>
                                        <input type="text" class="form-control" name="last_name" id="last_name" 
                                               value="<?= htmlspecialchars($admin['last_name']) ?>" required>
                                    </div>
                                    
                                    <div class="col-md-12">
                                        <label for="email" class="form-label">Email *</label>
                                        <input type="email" class="form-control" name="email" id="email" 
                                               value="<?= htmlspecialchars($admin['email']) ?>" required>
                                    </div>
                                    
                                    <div class="col-12">
                                        <button type="submit" name="update_profile" class="btn btn-primary">
                                            Update Profile
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Password Settings -->
                <div class="tab-pane fade" id="password" role="tabpanel">
                    <div class="card">
                        <div class="card-header bg-header header-text">
                            <h3 class="h5 mb-0">Change Password</h3>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label for="current_password" class="form-label">Current Password *</label>
                                        <input type="password" class="form-control" name="current_password" id="current_password" required>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="new_password" class="form-label">New Password *</label>
                                        <input type="password" class="form-control" name="new_password" id="new_password" required>
                                        <small class="text-muted">Minimum 8 characters</small>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="confirm_password" class="form-label">Confirm Password *</label>
                                        <input type="password" class="form-control" name="confirm_password" id="confirm_password" required>
                                    </div>
                                    
                                    <div class="col-12">
                                        <button type="submit" name="change_password" class="btn btn-primary">
                                            Change Password
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <?php if ($_SESSION['admin_role'] === 'Super Admin'): ?>
                <!-- System Settings -->
                <div class="tab-pane fade" id="system" role="tabpanel">
                    <div class="card">
                        <div class="card-header bg-header header-text">
                            <h3 class="h5 mb-0">System Settings</h3>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="site_name" class="form-label">Site Name *</label>
                                        <input type="text" class="form-control" name="site_name" id="site_name" 
                                               value="<?= htmlspecialchars($system_settings['site_name']) ?>" required>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="site_email" class="form-label">Site Email *</label>
                                        <input type="email" class="form-control" name="site_email" id="site_email" 
                                               value="<?= htmlspecialchars($system_settings['site_email']) ?>" required>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="items_per_page" class="form-label">Items Per Page *</label>
                                        <input type="number" min="5" max="100" class="form-control" name="items_per_page" 
                                               id="items_per_page" value="<?= $system_settings['items_per_page'] ?>" required>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label class="form-label">Maintenance Mode</label>
                                        <div class="form-check form-switch mt-2">
                                            <input class="form-check-input" type="checkbox" name="maintenance_mode" 
                                                   id="maintenance_mode" <?= $system_settings['maintenance_mode'] ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="maintenance_mode">
                                                <?= $system_settings['maintenance_mode'] ? 'Enabled' : 'Disabled' ?>
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <div class="col-12">
                                        <button type="submit" name="update_system_settings" class="btn btn-primary">
                                            Save System Settings
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php 
require_once __DIR__ . '/includes/footer.php';
?>