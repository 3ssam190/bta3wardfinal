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

// Determine user type and ID
$userType = isset($_SESSION['user_id']) ? 'user' : 'admin';
$userId = $_SESSION[$userType.'_id'];

// Check if file was uploaded
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_photo'])) {
    try {
        // File upload validation
        $file = $_FILES['profile_photo'];
        
        // Check for errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('File upload error: ' . $file['error']);
        }
        
        // Check file size (max 2MB)
        if ($file['size'] > 2097152) {
            throw new Exception('File size exceeds 2MB limit');
        }
        
        // Check file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($fileInfo, $file['tmp_name']);
        finfo_close($fileInfo);
        
        if (!in_array($mimeType, $allowedTypes)) {
            throw new Exception('Only JPG, PNG, and GIF files are allowed');
        }
        
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = $userType . '_' . $userId . '_' . time() . '.' . $extension;
        $uploadPath = __DIR__ . '/../assets/images/profile_photos/' . $filename;
        
        // Create directory if it doesn't exist
        if (!is_dir(dirname($uploadPath))) {
            mkdir(dirname($uploadPath), 0755, true);
        }
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
            throw new Exception('Failed to move uploaded file');
        }
        
        // Relative path for database
        $relativePath = '/assets/images/profile_photos/' . $filename;
        
        // Update database
        $conn = Database::connect();
        if ($userType === 'user') {
            $stmt = $conn->prepare("UPDATE Users SET profile_photo = ? WHERE user_id = ?");
        } else {
            $stmt = $conn->prepare("UPDATE Admins SET admin_photo = ? WHERE admin_id = ?");
        }
        $stmt->execute([$relativePath, $userId]);
        
        // Fetch updated user data
        if ($userType === 'user') {
            $stmt = $conn->prepare("SELECT * FROM Users WHERE user_id = ?");
        } else {
            $stmt = $conn->prepare("SELECT *, admin_photo AS profile_photo FROM Admins WHERE admin_id = ?");
        }
        $stmt->execute([$userId]);
        $updatedUser = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Store in session to avoid another DB query
        $_SESSION['updated_user_data'] = $updatedUser;
        
        // Update session photo if exists
        if ($userType === 'user') {
            $_SESSION['user_photo'] = $relativePath;
        } else {
            $_SESSION['admin_photo'] = $relativePath;
        }
        
        $_SESSION['success'] = "Profile photo updated successfully!";
        
    } catch (Exception $e) {
        $_SESSION['error'] = "Error updating profile photo: " . $e->getMessage();
    }
    
    // Redirect back to profile page
    header("Location: " . BASE_URL . "/pages/profile.php");
    exit();
} else {
    $_SESSION['error'] = "No file was uploaded";
    header("Location: " . BASE_URL . "/pages/profile.php");
    exit();
}