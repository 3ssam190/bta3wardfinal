<?php
require_once __DIR__ . '/../config.php';

// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check for either user or admin login
if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_id'])) {
    header("Location: " . BASE_URL . "/pages/signin.php");
    exit();
}

$pageTitle = 'My Profile';
require_once __DIR__ . '/../includes/header.php';

// Determine user type and ID
$userType = isset($_SESSION['user_id']) ? 'user' : 'admin';
$userId = $_SESSION[$userType.'_id'];

// Initialize $user array
$user = [];

// Check if we have updated user data in session (from photo upload)
if (isset($_SESSION['updated_user_data'])) {
    $user = $_SESSION['updated_user_data'];
    unset($_SESSION['updated_user_data']);
} else {
    // Fetch fresh user data from database
    if ($userType === 'user') {
        $stmt = $conn->prepare("SELECT *, profile_photo FROM Users WHERE user_id = ?");
    } else {
        $stmt = $conn->prepare("SELECT *, admin_photo AS profile_photo FROM Admins WHERE admin_id = ?");
    }
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        // Handle case where user isn't found
        $_SESSION['error'] = "User data not found!";
        header("Location: " . BASE_URL . "/index.php");
        exit();
    }
}

// Handle profile photo path
$profilePhotoPath = '';
if (!empty($user['profile_photo'])) {
    // If it's already a full URL, use as-is
    if (strpos($user['profile_photo'], 'http') === 0) {
        $profilePhotoPath = $user['profile_photo'];
    } 
    // If it starts with /, prepend BASE_URL
    elseif (strpos($user['profile_photo'], '/') === 0) {
        $profilePhotoPath = BASE_URL . $user['profile_photo'];
    }
    // Otherwise assume it's relative to assets/images
    else {
        $profilePhotoPath = BASE_URL . '/assets/images/' . ltrim($user['profile_photo'], '/');
    }
} else {
    // Use default image based on user type
    $profilePhotoPath = BASE_URL . '/assets/images/default-' . ($userType === 'admin' ? 'admin' : 'profile') . '.png';
}

// Add cache-busting
$profilePhotoPath .= '?v=' . time();

// Handle form submissions
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_personal_info'])) {
        // Handle personal info update
        $firstName = trim($_POST['first_name']);
        $lastName = trim($_POST['last_name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);

        try {
            if ($userType === 'user') {
                $updateStmt = $conn->prepare("UPDATE Users SET first_name = ?, last_name = ?, email = ?, phone = ? WHERE user_id = ?");
            } else {
                $updateStmt = $conn->prepare("UPDATE Admins SET first_name = ?, last_name = ?, email = ?, phone = ? WHERE admin_id = ?");
            }
            $updateStmt->execute([$firstName, $lastName, $email, $phone, $userId]);
            
            // Update session variables
            $_SESSION[$userType.'_name'] = $firstName . ' ' . $lastName;
            if ($userType === 'user') {
                $_SESSION['user_email'] = $email;
            } else {
                $_SESSION['admin_email'] = $email;
            }
            
            $success_message = "Personal information updated successfully!";
            // Refresh user data
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $error_message = "Error updating information: " . $e->getMessage();
        }
    } elseif (isset($_POST['change_password'])) {
        // Handle password change
        $currentPassword = $_POST['current_password'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];

        if ($newPassword !== $confirmPassword) {
            $error_message = "New passwords don't match!";
        } elseif (!password_verify($currentPassword, $user['password_hash'])) {
            $error_message = "Current password is incorrect!";
        } else {
            try {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                if ($userType === 'user') {
                    $updateStmt = $conn->prepare("UPDATE Users SET password_hash = ? WHERE user_id = ?");
                } else {
                    $updateStmt = $conn->prepare("UPDATE Admins SET password_hash = ? WHERE admin_id = ?");
                }
                $updateStmt->execute([$hashedPassword, $userId]);
                $success_message = "Password changed successfully!";
            } catch (PDOException $e) {
                $error_message = "Error changing password: " . $e->getMessage();
            }
        }
    }
}

?>

<div class="container my-5">
    <?php if ($success_message): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
    <?php endif; ?>
    
    <?php if ($error_message): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-body text-center">
                    <img src="<?php echo htmlspecialchars($profilePhotoPath); ?>" 
                         alt="Profile Photo" 
                         class="rounded-circle mb-3" 
                         style="width: 150px; height: 150px; object-fit: cover;">
                    
                    <h4><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h4>
                    <p class="text-muted"><?php echo htmlspecialchars($user['email']); ?></p>
                    
                    <form action="<?php echo BASE_URL; ?>/actions/update-profile-photo.php" method="post" enctype="multipart/form-data" id="profilePhotoForm">
                        <div class="mb-3">
                            <input type="file" class="form-control" name="profile_photo" id="profilePhotoInput" accept="image/*" required>
                            <div class="form-text">Max size: 2MB (JPG, PNG, GIF)</div>
                        </div>
                        <button type="submit" class="btn btn-success w-100" id="uploadButton">
                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            Update Photo
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Account Details</h5>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>Member since</span>
                            <span><?php echo date('M Y', strtotime($user['created_at'])); ?></span>
                        </li>
                        <?php if ($userType === 'user'): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>Orders</span>
                            <span><?php echo $user['order_count'] ?? 0; ?></span>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title">Personal Information</h5>
                    <form method="POST" action="">
                        <input type="hidden" name="update_personal_info" value="1">
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" 
                                       value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" 
                                       value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                        </div>
                        
                        <button type="submit" class="btn btn-success">Update Information</button>
                    </form>
                </div>
            </div>
            
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title">Change Password</h5>
                    <form method="POST" action="">
                        <input type="hidden" name="change_password" value="1">
                        
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required minlength="8">
                            <div class="form-text">Minimum 8 characters</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                        
                        <button type="submit" class="btn btn-success">Change Password</button>
                    </form>
                </div>
            </div>
            
            <?php if ($userType === 'user'): ?>
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Shipping Address</h5>
                    <form method="POST" action="">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="address" class="form-label">Address</label>
                                <input type="text" class="form-control" id="address" name="address" 
                                       value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="city" class="form-label">City</label>
                                <input type="text" class="form-control" id="city" name="city" 
                                       value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="state" class="form-label">State/Province</label>
                                <input type="text" class="form-control" id="state" name="state" 
                                       value="<?php echo htmlspecialchars($user['state'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="zip_code" class="form-label">ZIP/Postal Code</label>
                                <input type="text" class="form-control" id="zip_code" name="zip_code" 
                                       value="<?php echo htmlspecialchars($user['zip_code'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="country" class="form-label">Country</label>
                            <select class="form-select" id="country" name="country">
                                <option value="">Select Country</option>
                                <option value="US" <?php echo (isset($user['country']) && $user['country'] === 'US') ? 'selected' : ''; ?>>United States</option>
                                <option value="CA" <?php echo (isset($user['country']) && $user['country'] === 'CA') ? 'selected' : ''; ?>>Canada</option>
                                <option value="UK" <?php echo (isset($user['country']) && $user['country'] === 'UK') ? 'selected' : ''; ?>>United Kingdom</option>
                                <!-- Add more countries as needed -->
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-success">Update Address</button>
                    </form>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('profilePhotoForm');
    const input = document.getElementById('profilePhotoInput');
    const button = document.getElementById('uploadButton');
    const spinner = button.querySelector('.spinner-border');
    
    // Show preview before upload
    input.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.querySelector('.rounded-circle').src = e.target.result;
            }
            reader.readAsDataURL(this.files[0]);
        }
    });
    
    // Show loading spinner during upload
    form.addEventListener('submit', function() {
        button.disabled = true;
        spinner.classList.remove('d-none');
    });
    
    // Handle potential errors from the upload
    <?php if (isset($_SESSION['error'])): ?>
        alert('<?php echo addslashes($_SESSION['error']); ?>');
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>