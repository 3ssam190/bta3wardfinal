<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/config/database.php';

// Authentication and authorization
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_role'] !== 'Super Admin') {
    echo "<script>alert('You do not have permission to access this page.'); window.location.href='index.php';</script>";
    exit;
}

$pdo = Database::connect();

// Get all shifts
$shifts = $pdo->query("
    SELECT a.admin_id, a.first_name, a.last_name, a.is_exempt,
           s.day_of_week, s.start_time, s.end_time
    FROM Admins a
    LEFT JOIN AdminShifts s ON a.admin_id = s.admin_id
    ORDER BY a.first_name, a.last_name, 
        FIELD(s.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')
")->fetchAll(PDO::FETCH_ASSOC);

// Group shifts by admin
$adminShifts = [];
foreach ($shifts as $shift) {
    $adminId = $shift['admin_id'];
    if (!isset($adminShifts[$adminId])) {
        $adminShifts[$adminId] = [
            'name' => $shift['first_name'] . ' ' . $shift['last_name'],
            'is_exempt' => $shift['is_exempt'],
            'shifts' => []
        ];
    }
    if ($shift['day_of_week']) {
        $adminShifts[$adminId]['shifts'][$shift['day_of_week']] = [
            'start' => $shift['start_time'],
            'end' => $shift['end_time']
        ];
    }
}
?>

<main class="container-fluid py-4" style="margin-top: 70px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0">Admin Shifts Calendar</h2>
        <a href="users.php?type=admins" class="btn btn-outline-primary">
            <i class="fas fa-users me-1"></i> Manage Admins
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-primary">
                        <tr>
                            <th>Admin</th>
                            <th>Monday</th>
                            <th>Tuesday</th>
                            <th>Wednesday</th>
                            <th>Thursday</th>
                            <th>Friday</th>
                            <th>Saturday</th>
                            <th>Sunday</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($adminShifts as $adminId => $admin): ?>
                            <tr>
                                <td>
                                    <?= htmlspecialchars($admin['name']) ?>
                                    <?php if ($admin['is_exempt']): ?>
                                        <span class="badge bg-warning text-dark ms-2">Exempt</span>
                                    <?php endif; ?>
                                </td>
                                <?php 
                                $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                                foreach ($days as $day): 
                                    $shift = $admin['shifts'][$day] ?? null;
                                ?>
                                    <td class="<?= $shift ? 'bg-success bg-opacity-10' : '' ?>">
                                        <?php if ($shift): ?>
                                            <?= date('g:i A', strtotime($shift['start'])) ?> - 
                                            <?= date('g:i A', strtotime($shift['end'])) ?>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>