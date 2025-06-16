<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ob_start();
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/config/database.php';

// Authentication and authorization checks
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Only Super Admin can manage shifts
if ($_SESSION['admin_role'] !== 'Super Admin') {
    echo "<script>alert('You do not have permission to access this page.'); window.location.href='index.php';</script>";
    exit;
}

$admin_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$pdo = Database::connect();

// Check if admin exists and is not exempt
$stmt = $pdo->prepare("SELECT * FROM Admins WHERE admin_id = ?");
$stmt->execute([$admin_id]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$admin) {
    $_SESSION['error'] = "Admin not found.";
    header('Location: users.php?type=admins');
    exit;
}

if ($admin['is_exempt']) {
    $_SESSION['error'] = "This admin is exempt from shifts.";
    header('Location: users.php?type=admins');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Delete existing shifts
    $pdo->prepare("DELETE FROM AdminShifts WHERE admin_id = ?")->execute([$admin_id]);
    
    // Add new shifts
    $days = $_POST['day'] ?? [];
    $start_times = $_POST['start_time'] ?? [];
    $end_times = $_POST['end_time'] ?? [];
    
    $stmt = $pdo->prepare("INSERT INTO AdminShifts (admin_id, day_of_week, start_time, end_time) VALUES (?, ?, ?, ?)");
    
    foreach ($days as $index => $day) {
        if (!empty($day) && !empty($start_times[$index]) && !empty($end_times[$index])) {
            $stmt->execute([$admin_id, $day, $start_times[$index], $end_times[$index]]);
        }
    }
    
    $_SESSION['message'] = "Shifts updated successfully.";
    header("Location: admin_shifts.php?id=$admin_id");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_shift'])) {
    $adminIdToDelete = $_POST['admin_id'] ?? null;
    $dayToDelete = $_POST['day_of_week'] ?? null;

    if ($adminIdToDelete && $dayToDelete) {
        $stmt = $pdo->prepare("DELETE FROM AdminShifts WHERE admin_id = ? AND day_of_week = ?");
        $stmt->execute([$adminIdToDelete, $dayToDelete]);

        // Redirect to avoid resubmission
        header("Location: admin_shifts.php");
        exit;
    }
}

// Get current shifts
$stmt = $pdo->prepare("
    SELECT * FROM AdminShifts 
    WHERE admin_id = ? 
    ORDER BY FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')
");
$stmt->execute([$admin_id]);
$shifts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="container-fluid py-4" style="margin-top: 70px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0 header-text">
            <a href="users.php?type=admins" class="text-decoration-none header-text">
                <i class="fas fa-arrow-left me-2"></i>
            </a>
            Shift Management for <?= htmlspecialchars($admin['first_name'] . ' ' . $admin['last_name']) ?>
        </h2>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-success">
                            <tr>
                                <th>Day</th>
                                <th>Start Time</th>
                                <th>End Time</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="shiftRows">
                            <?php 
                            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                            foreach ($days as $day): 
                                $shift = array_filter($shifts, function($s) use ($day) { return $s['day_of_week'] === $day; });
                                $shift = reset($shift);
                            ?>
                                <tr>
                                    <td>
                                        <input type="hidden" name="day[]" value="<?= $day ?>">
                                        <?= $day ?>
                                    </td>
                                    <td>
                                        <input type="time" name="start_time[]" class="form-control" 
                                               value="<?= $shift ? $shift['start_time'] : '09:00' ?>">
                                    </td>
                                    <td>
                                        <input type="time" name="end_time[]" class="form-control" 
                                               value="<?= $shift ? $shift['end_time'] : '17:00' ?>">
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-shift">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="text-end mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Save Shifts
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle shift removal
    document.querySelectorAll('.remove-shift').forEach(btn => {
        btn.addEventListener('click', function() {
            this.closest('tr').remove();
        });
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>