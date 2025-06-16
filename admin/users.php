<?php
/**
 * PlantsStore - Admin Dashboard - Users Management
 * ------------------------------------------------
 * File: users.php
 * Description: Manages users (customers and admins) in the PlantsStore admin panel.
 * Provides functionality to view, search, edit, add, and delete users/admins.
 */

// Initialize error reporting (for development)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include necessary files
require_once __DIR__ . '/includes/header.php'; // Header (session, auth, nav)
require_once __DIR__ . '/config/database.php'; // Database connection

// --- Database Connection ---
try {
    $pdo = Database::connect(); // Establish database connection
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage()); // Handle connection error
}

// --- Authentication and Authorization ---
// Check if admin is logged in and has necessary permissions
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php'); // Redirect to login if not logged in
    exit;
}

// --- Role-Based Access Control ---
// Define allowed roles for this page
$allowed_roles = ['Super Admin', 'Product Manager', 'Order Manager'];
if (!isset($_SESSION['admin_role']) || !in_array($_SESSION['admin_role'], $allowed_roles)) {
    //  echo "<script>alert('You do not have permission to access this page.'); window.location.href='index.php';</script>"; // changed to js alert
    //  exit;
     // Use JavaScript to redirect
     echo "<script>
            alert('You do not have permission to access this page.');
            window.location.href = 'index.php';
          </script>";
     exit; // IMPORTANT: Stop further execution to prevent displaying page content
}

// --- CSRF Protection ---
// CSRF token generation (if not already set in header.php)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// --- Input Handling and Validation ---
// Helper function to sanitize input data
function sanitize_input($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

// --- User Actions ---
// 1. Delete User (Customer)
if (isset($_GET['delete_user'])) {
    $user_id = intval($_GET['delete_user']); // Ensure integer
    $csrf_token = $_GET['csrf_token'] ?? '';

     // Validate CSRF token
     if (!hash_equals($_SESSION['csrf_token'], $csrf_token)) {
        $_SESSION['error'] = "Invalid CSRF token.";
        header('Location: users.php');
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM Users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        
        $_SESSION['message'] = "User deleted successfully.";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error deleting user: " . $e->getMessage();
    }
    
    header('Location: users.php'); // Redirect after action
    exit;
}

// 2. Delete Admin
if (isset($_GET['delete_admin'])) {
    $admin_id = intval($_GET['delete_admin']); // Ensure integer
     $csrf_token = $_GET['csrf_token'] ?? '';

      // Validate CSRF token
    if (!hash_equals($_SESSION['csrf_token'], $csrf_token)) {
        $_SESSION['error'] = "Invalid CSRF token.";
        header('Location: users.php');
        exit;
    }
    
    try {
        // Prevent deleting the currently logged-in admin
        if ($admin_id == $_SESSION['admin_id']) {
            throw new Exception("You cannot delete your own account.");
        }
        
        $stmt = $pdo->prepare("DELETE FROM Admins WHERE admin_id = ?");
        $stmt->execute([$admin_id]);
        
        $_SESSION['message'] = "Admin deleted successfully.";
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
    
     header('Location: users.php'); // Redirect
     exit;
}

// 3. Update User Status (Verification)
if (isset($_POST['update_user_status'])) {
    $user_id = intval($_POST['user_id']); // Ensure integer
    $is_verified = isset($_POST['is_verified']) ? 1 : 0;
    
    try {
        $stmt = $pdo->prepare("UPDATE Users SET is_verified = ? WHERE user_id = ?");
        $stmt->execute([$is_verified, $user_id]);
        
        $_SESSION['message'] = "User status updated successfully.";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error updating user status: " . $e->getMessage();
    }
    
    header('Location: users.php'); // consistent redirect
    exit;
}

// --- Pagination Setup ---
$perPage = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1; // Ensure >= 1
$offset = ($page - 1) * $perPage;

// --- Filter Parameters ---
$user_type = $_GET['type'] ?? 'customers'; // 'customers' or 'admins' - default to 'customers'
$search_query = $_GET['search'] ?? ''; // Default empty search

// --- Data Retrieval ---
// 1. Get Total Count of Users/Admins (for pagination)
if ($user_type === 'admins') {
    $count_query = "SELECT COUNT(*) FROM Admins";
    $query = "SELECT * FROM Admins ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
} else {
    $count_query = "SELECT COUNT(*) FROM Users";
    $query = "SELECT * FROM Users ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
}

// Apply search filter if provided
if ($search_query) {
    $search_param = "%" . sanitize_input($search_query) . "%"; // Sanitize search query
    if ($user_type === 'admins') {
        $count_query .= " WHERE email LIKE ? OR first_name LIKE ? OR last_name LIKE ?";
        $query = "SELECT * FROM Admins WHERE email LIKE ? OR first_name LIKE ? OR last_name LIKE ? ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
    } else {
        $count_query .= " WHERE email LIKE ? OR first_name LIKE ? OR last_name LIKE ?";
        $query = "SELECT * FROM Users WHERE email LIKE ? OR first_name LIKE ? OR last_name LIKE ? ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
    }
}

// Get total number of users/admins
$stmt = $pdo->prepare($count_query);
if ($search_query) {
    $stmt->execute([$search_param, $search_param, $search_param]);
} else {
    $stmt->execute();
}
$totalUsers = $stmt->fetchColumn();
$totalPages = ceil($totalUsers / $perPage);

// 2. Get Users/Admins Data
$stmt = $pdo->prepare($query);
if ($search_query) {
    $stmt->bindValue(1, $search_param);
    $stmt->bindValue(2, $search_param);
    $stmt->bindValue(3, $search_param);
    $stmt->bindValue(4, $perPage, PDO::PARAM_INT);
    $stmt->bindValue(5, $offset, PDO::PARAM_INT);
} else {
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
}
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- Role Options for Add/Edit Admin Forms ---
$role_options = [
    'Super Admin' => 'Super Admin',
    'Product Manager' => 'Product Manager',
    'Order Manager' => 'Order Manager'
];
?>

<main class="container-fluid py-4" style="margin-top: 70px;">
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

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-3 mb-md-0 header-text">Users Management</h2>
        <div class="d-flex gap-2">
            <ul class="nav nav-pills nav-users">
                <li class="nav-item">
                    <a class="nav-link <?= $user_type === 'customers' ? 'active' : '' ?>"
                       href="users.php?type=customers">Customers</a>
                </li>
                <?php if (isset($_SESSION['admin_role']) && in_array($_SESSION['admin_role'], ['Super Admin', 'Product Manager'])): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $user_type === 'admins' ? 'active' : '' ?>"
                           href="users.php?type=admins">Admins</a>
                    </li>
                <?php endif; ?>
            </ul>
            
            <form method="GET" class="d-flex gap-2">
                <input type="hidden" name="type" value="<?= $user_type ?>">
                <div class="input-group">
                    <input type="text" class="form-control" name="search" placeholder="Search users..."
                           value="<?= htmlspecialchars($search_query) ?>">
                    <button class="btn btn-primary" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                    <?php if ($search_query): ?>
                        <a href="users.php?type=<?= $user_type ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i>
                        </a>
                    <?php endif; ?>
                </div>
                
                <?php if ($user_type === 'admins' && $_SESSION['admin_role'] === 'Super Admin'): ?>
                    <button type="button" onclick="openAddAdminModal()" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Add Admin
                    </button>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-primary">
                        <tr>
                            <?php if ($user_type === 'customers'): ?>
                                <th>User ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Location</th>
                                <th>Status</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            <?php else: ?>
                                <th>Admin ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Shift</th>
                                <th>Salary</th>
                                <th>Created</th>
                                <th>Last Login</th>
                                <th>Actions</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <?php if ($user_type === 'customers'): ?>
                                    <td>#<?= str_pad($user['user_id'], 5, '0', STR_PAD_LEFT) ?></td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-bold"><?= htmlspecialchars($user['first_name']) . ' ' . htmlspecialchars($user['last_name']) ?></span>
                                            <?php if ($user['social_login_provider']): ?>
                                                <span class="badge bg-info text-dark small">
                                                    <?= ucfirst($user['social_login_provider']) ?> login
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td><?= $user['phone'] ? htmlspecialchars($user['phone']) : 'N/A' ?></td>
                                    <td>
                                        <?= $user['city'] ? htmlspecialchars($user['city']) . ', ' : '' ?>
                                        <?= htmlspecialchars($user['region']) ?>
                                    </td>
                                    <td>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="is_verified"
                                                       id="verified_<?= $user['user_id'] ?>"
                                                    <?= $user['is_verified'] ? 'checked' : '' ?>
                                                       onchange="this.form.submit()">
                                                <label class="form-check-label" for="verified_<?= $user['user_id'] ?>">
                                                    <?= $user['is_verified'] ? 'Verified' : 'Pending' ?>
                                                </label>
                                            </div>
                                            <input type="hidden" name="update_user_status" value="1">
                                        </form>
                                    </td>
                                    <td><?= date('M j, Y', strtotime($user['created_at'])) ?></td>
                                    <td>
                                        <!--<a href="#" class="btn btn-sm btn-outline-primary me-2">-->
                                        <!--    <i class="fas fa-eye me-1"></i> View  </a>-->
                                        <a href="users.php?delete_user=<?= $user['user_id'] ?>&csrf_token=<?= $_SESSION['csrf_token'] ?>"
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                            <i class="fas fa-trash me-1"></i> Delete
                                        </a>
                                    </td>
                                <?php else: ?>
                                    <td>#<?= str_pad($user['admin_id'], 3, '0', STR_PAD_LEFT) ?></td>
                                    <td>
                                        <span class="fw-bold"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></span>
                                    </td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
<td>
    <span class="badge <?=
        $user['role'] === 'Super Admin' ? 'bg-primary' :
        ($user['role'] === 'Product Manager' ? 'bg-success' : 'bg-info') ?>">
        <?= $user['role'] ?>
    </span>
    <?php if ($user['is_exempt']): ?>
        <span class="badge bg-warning text-dark">Exempt</span>
    <?php endif; ?>
</td>
<td>
    <a href="admin_shifts.php?id=<?= $user['admin_id'] ?>" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-clock"></i> Shifts
    </a>
</td>
<td>
    <a href="admin_salary.php?id=<?= $user['admin_id'] ?>" class="btn btn-sm btn-outline-warning">
        <i class="fas fa-money-bill-wave"></i> Salary
    </a>
</td>
<td><?= date('M j, Y', strtotime($user['created_at'])) ?></td>
                                    <td>
                                        <?= $user['last_login'] ? date('M j, Y g:i A', strtotime($user['last_login'])) : 'Never' ?>
                                    </td>
                                    <td>
                                        <?php if ($_SESSION['admin_role'] === 'Super Admin' || $user['admin_id'] == $_SESSION['admin_id']): ?>
                                            <button onclick="openEditAdminModal(<?= $user['admin_id'] ?>)"
                                                    class="btn btn-sm btn-outline-primary me-2">
                                                <i class="fas fa-edit me-1"></i> Edit
                                            </button>
                                        <?php endif; ?>
                                        
                                        <?php if ($_SESSION['admin_role'] === 'Super Admin' && $user['admin_id'] != $_SESSION['admin_id']): ?>
                                            <a href="users.php?delete_admin=<?= $user['admin_id'] ?>&csrf_token=<?= $_SESSION['csrf_token'] ?>"
                                               class="btn btn-sm btn-outline-danger"
                                               onclick="return confirm('Are you sure you want to delete this admin? This action cannot be undone.')">
                                                <i class="fas fa-trash me-1"></i> Delete
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalPages > 1): ?>
                <nav class="d-flex justify-content-between align-items-center mt-4">
                    <div>
                        <p class="small text-muted mb-0">
                            Showing <span class="fw-bold"><?= $offset + 1 ?></span> to
                            <span class="fw-bold"><?= min($offset + $perPage, $totalUsers) ?></span> of
                            <span class="fw-bold"><?= $totalUsers ?></span> results
                        </p>
                    </div>
                    <ul class="pagination mb-0">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link"
                                   href="users.php?page=<?= $page - 1 ?>&type=<?= $user_type ?><?= $search_query ? '&search=' . urlencode($search_query) : '' ?>">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link"
                                   href="users.php?page=<?= $i ?>&type=<?= $user_type ?><?= $search_query ? '&search=' . urlencode($search_query) : '' ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link"
                                   href="users.php?page=<?= $page + 1 ?>&type=<?= $user_type ?><?= $search_query ? '&search=' . urlencode($search_query) : '' ?>">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>

    <div class="modal fade" id="addAdminModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="actions/add_admin.php" id="addAdminForm">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <div class="modal-header bg-header header-text">
                        <h5 class="modal-title">Add New Admin</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="first_name" class="form-label">First Name *</label>
                                <input type="text" class="form-control" name="first_name" id="first_name" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="last_name" class="form-label">Last Name *</label>
                                <input type="text" class="form-control" name="last_name" id="last_name" required>
                            </div>
                            
                            <div class="col-md-12">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" class="form-control" name="email" id="email" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="password" class="form-label">Password *</label>
                                <input type="password" class="form-control" name="password" id="password" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="confirm_password" class="form-label">Confirm Password *</label>
                                <input type="password" class="form-control" name="confirm_password"
                                       id="confirm_password" required>
                            </div>
                            
                            <div class="col-md-12">
                                <label for="role" class="form-label">Role *</label>
                                <select class="form-select" name="role" id="role" required>
                                    <?php foreach ($role_options as $value => $label): ?>
                                        <option value="<?= $value ?>"><?= $label ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_admin" class="btn btn-primary">Add Admin</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editAdminModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="actions/update_admin.php" id="editAdminForm">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <input type="hidden" name="admin_id" id="edit_admin_id">
                    <div class="modal-header bg-header header-text">
                        <h5 class="modal-title">Edit Admin</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="editAdminModalBody">
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="update_admin" class="btn btn-primary">Update Admin</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<script>
// --- Global Functions (accessible from HTML) ---
// 1. Open Add Admin Modal
window.openAddAdminModal = function () {
    const modal = new bootstrap.Modal(document.getElementById('addAdminModal'));
    modal.show();
};

// 2. Open Edit Admin Modal (Fetch data and populate form)
window.openEditAdminModal = async function (adminId) {
    try {
        // Show loading state
        document.getElementById('editAdminModalBody').innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `;
        
        // Show modal immediately
        const modal = new bootstrap.Modal(document.getElementById('editAdminModal'));
        modal.show();

        // Fetch admin data from server
        const response = await fetch(`actions/get_admin.php?id=${adminId}`);
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        const data = await response.json();
        if (!data.success) {
            throw new Error(data.error || 'Failed to load admin data');
        }

        // Populate the modal with the received admin data
        const admin = data.admin;
        const isSuperAdmin = <?= ($_SESSION['admin_role'] === 'Super Admin') ? 'true' : 'false' ?>;  //check if the current user is super admin.

        let roleField = '';
        if (isSuperAdmin) {
            roleField = `
                <div class="col-md-12">
                    <label for="edit_role" class="form-label">Role *</label>
                    <select class="form-select" name="role" id="edit_role" required>
                        <option value="Super Admin" ${admin.role === 'Super Admin' ? 'selected' : ''}>Super Admin</option>
                        <option value="Product Manager" ${admin.role === 'Product Manager' ? 'selected' : ''}>Product Manager</option>
                        <option value="Order Manager" ${admin.role === 'Order Manager' ? 'selected' : ''}>Order Manager</option>
                    </select>
                </div>
            `;
        }

        let passwordFields = '';
         // Only show password fields if the user is a Super Admin or is editing their own profile
        if (isSuperAdmin || adminId == <?= $_SESSION['admin_id'] ?? 0 ?>) {
            passwordFields = `
                <div class="col-md-6">
                    <label for="edit_password" class="form-label">New Password</label>
                    <input type="password" class="form-control" name="password" id="edit_password">
                    <small class="text-muted">Leave blank to keep current password</small>
                </div>
                <div class="col-md-6">
                    <label for="edit_confirm_password" class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" name="confirm_password" id="edit_confirm_password">
                </div>
            `;
        }

        document.getElementById('editAdminModalBody').innerHTML = `
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="edit_first_name" class="form-label">First Name *</label>
                    <input type="text" class="form-control" name="first_name" id="edit_first_name"
                           value="${escapeHtml(admin.first_name)}" required>
                </div>
                <div class="col-md-6">
                    <label for="edit_last_name" class="form-label">Last Name *</label>
                    <input type="text" class="form-control" name="last_name" id="edit_last_name"
                           value="${escapeHtml(admin.last_name)}" required>
                </div>
                <div class="col-md-12">
                    <label for="edit_email" class="form-label">Email *</label>
                    <input type="email" class="form-control" name="email" id="edit_email"
                           value="${escapeHtml(admin.email)}" required>
                </div>
                ${passwordFields}
                ${roleField}
            </div>
        `;

        // Set the admin ID in the hidden field of the form
        document.getElementById('edit_admin_id').value = adminId;

    } catch (error) {
        console.error('Error:', error);
        document.getElementById('editAdminModalBody').innerHTML = `
            <div class="alert alert-danger">
                Failed to load admin data: ${escapeHtml(error.message)}
            </div>
        `;
    }
};



// --- Helper Functions ---
// 1. Escape HTML (prevent XSS) - moved inside the <script> tag
function escapeHtml(unsafe) {
    if (!unsafe) return '';
    return unsafe.toString()
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}


// --- Event Listeners ---
// 1. Form Validation (Add/Edit Admin) - Password match validation
document.addEventListener('DOMContentLoaded', function () {
    const addAdminForm = document.getElementById('addAdminForm');
    const editAdminForm = document.getElementById('editAdminForm');

    if (addAdminForm) {
        addAdminForm.addEventListener('submit', function (e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            if (password !== confirmPassword) {
                e.preventDefault();
                showNotification('error', 'Passwords do not match');
                return false;
            }
            return true;
        });
    }

    if (editAdminForm) {
        editAdminForm.addEventListener('submit', function (e) {
            const password = document.getElementById('edit_password')?.value; // Use optional chaining
            const confirmPassword = document.getElementById('edit_confirm_password')?.value;

            if (password && password !== confirmPassword) {
                e.preventDefault();
                showNotification('error', 'Passwords do not match');
                return false;
            }
            return true;
        });
    }
});
</script>

<?php
// Include footer
require_once __DIR__ . '/includes/footer.php';
?>
