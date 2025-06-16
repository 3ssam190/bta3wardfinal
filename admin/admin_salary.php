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

// Only Super Admin can manage salaries
if ($_SESSION['admin_role'] !== 'Super Admin') {
    echo "<script>alert('You do not have permission to access this page.'); window.location.href='index.php';</script>";
    exit;
}

$admin_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$pdo = Database::connect();

// Get admin info
$stmt = $pdo->prepare("SELECT * FROM Admins WHERE admin_id = ?");
$stmt->execute([$admin_id]);
$admin = $stmt->fetch();

if (!$admin) {
    $_SESSION['error'] = "Admin not found.";
    header('Location: users.php?type=admins');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $base_salary = floatval($_POST['base_salary']);
    $commission_rate = floatval($_POST['commission_rate']);
    $effective_date = $_POST['effective_date'];
    
    // Insert new salary record
    $stmt = $pdo->prepare("INSERT INTO AdminSalaries (admin_id, base_salary, commission_rate, effective_date) 
                          VALUES (?, ?, ?, ?)");
    $stmt->execute([$admin_id, $base_salary, $commission_rate, $effective_date]);
    
    $_SESSION['message'] = "Salary information updated successfully.";
    header("Location: admin_salary.php?id=$admin_id");
    exit;
}

// Get current salary
// Get current salary
$stmt = $pdo->prepare("SELECT * FROM AdminSalaries WHERE admin_id = ? 
                       ORDER BY effective_date DESC LIMIT 1");
$stmt->execute([$admin_id]);
$current_salary = $stmt->fetch();

// Get payment history
$stmt = $pdo->prepare("SELECT * FROM AdminPayments WHERE admin_id = ? 
                       ORDER BY payment_date DESC");
$stmt->execute([$admin_id]);
$payments = $stmt->fetchAll();

?>

<main class="container-fluid py-4" style="margin-top: 70px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0 header-text">
            <a href="users.php?type=admins" class="text-decoration-none header-text">
                <i class="fas fa-arrow-left me-2"></i>
            </a>
            Salary & Commission for <?= htmlspecialchars($admin['first_name'] . ' ' . $admin['last_name']) ?>
        </h2>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-header text-white">
                    <h3 class="h5 mb-0">Current Salary</h3>
                </div>
                <div class="card-body">
                    <?php if ($current_salary): ?>
                        <div class="mb-3">
                            <h4 class="fw-bold">EGP <?= number_format($current_salary['base_salary'], 2) ?></h4>
                            <p class="text-muted mb-1">Base Salary</p>
                        </div>
                        <div class="mb-3">
                            <h4 class="fw-bold"><?= $current_salary['commission_rate'] ?>%</h4>
                            <p class="text-muted mb-1">Commission Rate</p>
                        </div>
                        <div>
                            <p class="text-muted mb-0">
                                Effective since <?= date('M j, Y', strtotime($current_salary['effective_date'])) ?>
                            </p>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No salary information found.</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header bg-header text-white">
                    <h3 class="h5 mb-0">Update Salary</h3>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label for="base_salary" class="form-label">Base Salary (EGP)</label>
                            <input type="number" step="0.01" class="form-control" id="base_salary" name="base_salary" 
                                   value="<?= $current_salary ? $current_salary['base_salary'] : '' ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="commission_rate" class="form-label">Commission Rate (%)</label>
                            <input type="number" step="0.01" class="form-control" id="commission_rate" name="commission_rate" 
                                   value="<?= $current_salary ? $current_salary['commission_rate'] : '0.00' ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="effective_date" class="form-label">Effective Date</label>
                            <input type="date" class="form-control" id="effective_date" name="effective_date" 
                                   value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Update Salary</button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-header text-white">
                    <h3 class="h5 mb-0">Payment History</h3>
                </div>
                <div class="card-body">
                    <?php if ($payments): ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Period</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($payments as $payment): ?>
                                        <tr>
                                            <td><?= date('M j, Y', strtotime($payment['payment_date'])) ?></td>
                                            <td>EGP <?= number_format($payment['amount'], 2) ?></td>
                                            <td>
                                                <?= date('M j', strtotime($payment['period_start'])) ?> - 
                                                <?= date('M j', strtotime($payment['period_end'])) ?>
                                            </td>
                                            <td>
                                                <span class="badge <?= 
                                                    $payment['status'] === 'Paid' ? 'bg-success' : 
                                                    ($payment['status'] === 'Pending' ? 'bg-warning text-dark' : 'bg-danger') ?>">
                                                    <?= $payment['status'] ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No payment history found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>