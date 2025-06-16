<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

// Authentication and authorization
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_role'] !== 'Super Admin') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// CSRF protection
$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $data['csrf_token'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit;
}

$pdo = Database::connect();

try {
    $pdo->beginTransaction();
    
    // Get all admins who are not exempt
    $admins = $pdo->query("
        SELECT a.admin_id, a.first_name, a.last_name, 
               asal.base_salary, asal.commission_rate
        FROM Admins a
        LEFT JOIN AdminSalaries asal ON a.admin_id = asal.admin_id AND 
            asal.effective_date = (
                SELECT MAX(effective_date) 
                FROM AdminSalaries 
                WHERE admin_id = a.admin_id
            )
        WHERE a.is_exempt = 0
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    // Get the last payment date to determine period start
    $lastPayment = $pdo->query("
        SELECT MAX(period_end) as last_period_end 
        FROM AdminPayments
    ")->fetch(PDO::FETCH_ASSOC);
    
    $periodStart = $lastPayment['last_period_end'] ? 
        date('Y-m-d', strtotime($lastPayment['last_period_end'] . ' +1 day')) : 
        date('Y-m-01', strtotime('-1 month'));
    
    $periodEnd = date('Y-m-d', strtotime('yesterday'));
    $paymentDate = date('Y-m-d');
    
    foreach ($admins as $admin) {
        // Calculate sales and commission for the period
        $sales = $pdo->prepare("
            SELECT 
                COUNT(o.order_id) AS order_count,
                SUM(o.total_amount + o.delivery_fee) AS total_sales,
                COALESCE(SUM((o.total_amount + o.delivery_fee) * (:commission_rate/100)), 0) AS commission
            FROM Orders o
            JOIN Payments p ON o.order_id = p.order_id
            WHERE p.verified_by = :admin_id
            AND p.payment_status = 'Completed'
            AND o.status != 'Cancelled'
            AND o.order_date BETWEEN :period_start AND :period_end
        ")->execute([
            'admin_id' => $admin['admin_id'],
            'commission_rate' => $admin['commission_rate'] ?? 0,
            'period_start' => $periodStart,
            'period_end' => $periodEnd
        ])->fetch(PDO::FETCH_ASSOC);
        
        $totalAmount = ($admin['base_salary'] ?? 0) + ($sales['commission'] ?? 0);
        
        if ($totalAmount > 0) {
            // Create payment record
            $pdo->prepare("
                INSERT INTO AdminPayments (
                    admin_id, amount, payment_date, period_start, period_end,
                    base_salary, commission, status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending')
            ")->execute([
                $admin['admin_id'],
                $totalAmount,
                $paymentDate,
                $periodStart,
                $periodEnd,
                $admin['base_salary'] ?? 0,
                $sales['commission'] ?? 0
            ]);
        }
    }
    
    $pdo->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}