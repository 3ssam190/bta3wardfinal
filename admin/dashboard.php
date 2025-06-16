<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ob_start();
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/config/database.php';
$pdo = Database::connect();

// Get total products
$totalProducts = $pdo->query("SELECT COUNT(*) FROM Products")->fetchColumn();

// Get total orders
$totalOrders = $pdo->query("SELECT COUNT(*) FROM Orders")->fetchColumn();

// Get total users
$totalUsers = $pdo->query("SELECT COUNT(*) FROM Users")->fetchColumn();

// Get recent orders
$recentOrders = $pdo->query("
    SELECT o.order_id, CONCAT(u.first_name, ' ', u.last_name) AS customer_name, 
           o.total_amount + o.delivery_fee AS total, o.status
    FROM Orders o
    JOIN Users u ON o.user_id = u.user_id
    ORDER BY o.order_date DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Get sales data for chart
$salesData = $pdo->query("
    SELECT 
        DATE_FORMAT(order_date, '%Y-%m') AS month,
        SUM(total_amount + delivery_fee) AS total_sales,
        COUNT(*) AS order_count
    FROM Orders
    WHERE status != 'Cancelled' AND order_date >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(order_date, '%Y-%m')
    ORDER BY month ASC
")->fetchAll(PDO::FETCH_ASSOC);


$statusData = $pdo->query("
    SELECT 
        status,
        COUNT(*) AS count
    FROM Orders
    GROUP BY status
")->fetchAll(PDO::FETCH_ASSOC);

// Prepare data for status chart
$statusLabels = ['Pending', 'Confirmed', 'Processing', 'Shipped', 'Delivered', 'Cancelled'];
$statusCounts = array_fill_keys($statusLabels, 0);

foreach ($statusData as $status) {
    $statusCounts[$status['status']] = $status['count'];
}

$salesByAdmin = $pdo->query("
    SELECT 
        a.admin_id, 
        CONCAT(a.first_name, ' ', a.last_name) AS admin_name,
        COUNT(o.order_id) AS order_count,
        SUM(o.total_amount + o.delivery_fee) AS total_sales,
        COALESCE(SUM((o.total_amount + o.delivery_fee) * (ads.commission_rate/100)), 0) AS total_commission
    FROM Orders o
    JOIN Payments p ON o.order_id = p.order_id
    JOIN Admins a ON p.verified_by = a.admin_id
    LEFT JOIN AdminSalaries ads ON a.admin_id = ads.admin_id AND 
        ads.effective_date = (
            SELECT MAX(effective_date) 
            FROM AdminSalaries 
            WHERE admin_id = a.admin_id AND effective_date <= o.order_date
        )
    WHERE o.status != 'Cancelled'
    GROUP BY a.admin_id
    ORDER BY total_sales DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Get total commissions payable
$totalCommissionPayable = array_sum(array_column($salesByAdmin, 'total_commission'));
?>

<!-- Main Content -->
<main class="container-fluid py-4" style="margin-top: 70px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0 text-gradient">Dashboard Overview</h2>
        <!--<div class="d-flex gap-2">-->
        <!--    <button class="btn btn-sm btn-outline-primary">-->
        <!--        <i class="fas fa-download me-1"></i> Export-->
        <!--    </button>-->
        <!--    <button class="btn btn-sm btn-primary">-->
        <!--        <i class="fas fa-plus me-1"></i> Quick Add-->
        <!--    </button>-->
        <!--</div>-->
    </div>
    
    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-4 animate-fadeIn">
            <div class="card stats-card border-start border-5 border-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h3 class="h5 card-title text-muted">Total Plants</h3>
                            <p class="display-6 fw-bold"><?= $totalProducts ?></p>
                            <p class="small text-success">
                                <i class="fas fa-arrow-up me-1"></i> 12% from last month
                            </p>
                        </div>
                        <div class="icon-wrapper bg-success bg-opacity-10 text-success">
                            <i class="fas fa-leaf"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 animate-fadeIn delay-1">
            <div class="card stats-card border-start border-5 border-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h3 class="h5 card-title text-muted">Total Orders</h3>
                            <p class="display-6 fw-bold"><?= $totalOrders ?></p>
                            <p class="small text-primary">
                                <i class="fas fa-arrow-up me-1"></i> 24% from last month
                            </p>
                        </div>
                        <div class="icon-wrapper bg-primary bg-opacity-10 text-primary">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 animate-fadeIn delay-2">
            <div class="card stats-card border-start border-5 border-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h3 class="h5 card-title text-muted">Total Users</h3>
                            <p class="display-6 fw-bold"><?= $totalUsers ?></p>
                            <p class="small text-warning">
                                <i class="fas fa-arrow-up me-1"></i> 8% from last month
                            </p>
                        </div>
                        <div class="icon-wrapper bg-warning bg-opacity-10 text-warning">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row g-4 mb-4">
        <!-- Sales Chart -->
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="h5 mb-0">Sales Overview</h3>
                    <!--<div class="btn-group">-->
                    <!--    <button class="btn btn-sm btn-outline-secondary active">Monthly</button>-->
                    <!--    <button class="btn btn-sm btn-outline-secondary">Weekly</button>-->
                    <!--    <button class="btn btn-sm btn-outline-secondary">Daily</button>-->
                    <!--</div>-->
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Status Distribution -->
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="h5 mb-0">Orders Status</h3>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="card shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="h5 mb-0">Recent Orders</h3>
            <a href="orders.php" class="btn btn-sm btn-primary">View All</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentOrders as $order): ?>
                        <tr>
                            <td class="fw-bold">#<?= str_pad($order['order_id'], 5, '0', STR_PAD_LEFT) ?></td>
                            <td><?= htmlspecialchars($order['customer_name']) ?></td>
                            <td>EGP <?= number_format($order['total'], 2) ?></td>
                            <td>
                                <span class="badge <?= 
                                    $order['status'] === 'Pending' ? 'bg-warning text-dark' : 
                                    ($order['status'] === 'Confirmed' ? 'bg-info' : 
                                    ($order['status'] === 'Processing' ? 'bg-primary' : 
                                    ($order['status'] === 'Shipped' ? 'bg-secondary' : 
                                    ($order['status'] === 'Delivered' ? 'bg-success' : 'bg-danger')))) ?>">
                                    <?= $order['status'] ?>
                                </span>
                            </td>
                            <td>
                                 <button onclick="viewOrderDetails(<?= $order['order_id'] ?>)" 
                                        class="btn btn-sm btn-outline-primary me-2">
                                    <i class="fas fa-eye me-1"></i> View
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Top Products -->
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="h5 mb-0">Top Selling Plants</h3>
            <a href="products.php" class="btn btn-sm btn-primary">View All</a>
        </div>
        <div class="card-body">
            <div class="row g-4">
                <?php
                $topProducts = $pdo->query("
                    SELECT p.product_id, p.name, p.price, 
                           (SELECT image_url FROM ProductImages WHERE product_id = p.product_id AND is_primary = 1 LIMIT 1) AS image,
                           SUM(oi.quantity) AS total_sold
                    FROM Products p
                    LEFT JOIN OrderItems oi ON p.product_id = oi.product_id
                    GROUP BY p.product_id
                    ORDER BY total_sold DESC
                    LIMIT 4
                ")->fetchAll(PDO::FETCH_ASSOC);
                
                foreach ($topProducts as $product):
                ?>
                <div class="col-md-3 col-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-img-top overflow-hidden" style="height: 150px;">
                            <img src="assets/images/products/<?= htmlspecialchars($product['image'] ?? 'default-product.jpg') ?>" 
                                 class="img-fluid w-100 h-100 object-fit-cover"
                                 onerror="this.src='assets/images/default-product.jpg'">
                        </div>
                        <div class="card-body">
                            <h5 class="card-title h6 mb-1"><?= htmlspecialchars($product['name']) ?></h5>
                            <p class="text-success fw-bold mb-2">EGP <?= number_format($product['price'], 2) ?></p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-primary bg-opacity-10 text-primary">
                                    <i class="fas fa-shopping-bag me-1"></i> <?= $product['total_sold'] ?? 0 ?> sold
                                </span>
                                <button class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div class="card shadow-sm mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="h5 mb-0">Sales Performance</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Admin</th>
                        <th>Orders</th>
                        <th>Total Sales</th>
                        <th>Commission</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($salesByAdmin as $admin): ?>
                    <tr>
                        <td><?= htmlspecialchars($admin['admin_name']) ?></td>
                        <td><?= $admin['order_count'] ?></td>
                        <td>EGP <?= number_format($admin['total_sales'], 2) ?></td>
                        <td>EGP <?= number_format($admin['total_commission'], 2) ?></td>
                        <td>
                            <a href="admin_salary.php?id=<?= $admin['admin_id'] ?>" 
                               class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye me-1"></i> View
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <tr class="table-active">
                        <td colspan="2" class="fw-bold">Total</td>
                        <td class="fw-bold">EGP <?= number_format(array_sum(array_column($salesByAdmin, 'total_sales')), 2) ?></td>
                        <td class="fw-bold">EGP <?= number_format($totalCommissionPayable, 2) ?></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-header text-success">
                    <h5 class="modal-title">Order Details</h5>
                    <button type="button" class="btn-close btn-close-black" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="orderDetailsContent">
                    <!-- Content will be loaded dynamically via JavaScript -->
                    <div class="text-center py-4">
                        <div class="spinner-border header-text" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success" onclick="printOrderDetails()">
                        <i class="fas fa-print me-1"></i> Print
                    </button>
                </div>
            </div>
        </div>
    </div>
</main>

<?php
function isAdminOnShift($admin_id, $pdo) {
    $currentDay = date('l'); // e.g., "Monday"
    $currentTime = date('H:i:s');
    
    $stmt = $pdo->prepare("
        SELECT 1 FROM AdminShifts 
        WHERE admin_id = ? 
        AND day_of_week = ? 
        AND start_time <= ? 
        AND end_time >= ?
    ");
    $stmt->execute([$admin_id, $currentDay, $currentTime, $currentTime]);
    
    return $stmt->fetchColumn() || isAdminExempt($admin_id, $pdo);
}

function isAdminExempt($admin_id, $pdo) {
    $stmt = $pdo->prepare("SELECT is_exempt FROM Admins WHERE admin_id = ?");
    $stmt->execute([$admin_id]);
    return $stmt->fetchColumn();
}


// if (!isAdminOnShift($admin_id, $pdo) && !isAdminExempt($admin_id, $pdo)) {
//     $_SESSION['error'] = "You are not scheduled to work at this time.";
//     header('Location: login.php');
//     exit;
// }
?>

<script>
// Sales Chart
const salesCtx = document.getElementById('salesChart').getContext('2d');
const salesChart = new Chart(salesCtx, {
    type: 'line',
    data: {
        labels: <?= json_encode(array_map(function($month) {
            return date('M Y', strtotime($month . '-01'));
        }, array_column($salesData, 'month'))) ?>,
        datasets: [{
            label: 'Total Sales (EGP)',
            data: <?= json_encode(array_column($salesData, 'total_sales')) ?>,
            borderColor: '#4f46e5',
            backgroundColor: 'rgba(79, 70, 229, 0.1)',
            tension: 0.3,
            fill: true,
            yAxisID: 'y'
        }, {
            label: 'Number of Orders',
            data: <?= json_encode(array_column($salesData, 'order_count')) ?>,
            borderColor: '#10b981',
            backgroundColor: 'rgba(16, 185, 129, 0.1)',
            tension: 0.3,
            fill: true,
            yAxisID: 'y1'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        let label = context.dataset.label || '';
                        if (label.includes('Sales')) {
                            label += ': ' + context.raw.toLocaleString('en-EG', {
                                style: 'currency',
                                currency: 'EGP'
                            });
                        } else {
                            label += ': ' + context.raw;
                        }
                        return label;
                    }
                }
            }
        },
        scales: {
            y: {
                type: 'linear',
                display: true,
                position: 'left',
                title: {
                    display: true,
                    text: 'Sales Amount (EGP)'
                },
                beginAtZero: true,
                grid: {
                    drawOnChartArea: true,
                },
                ticks: {
                    callback: function(value) {
                        return value.toLocaleString('en-EG', {
                            style: 'currency',
                            currency: 'EGP',
                            maximumFractionDigits: 0
                        });
                    }
                }
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                title: {
                    display: true,
                    text: 'Number of Orders'
                },
                beginAtZero: true,
                grid: {
                    drawOnChartArea: false,
                }
            }
        }
    }
});

// Status Chart
const statusCtx = document.getElementById('statusChart').getContext('2d');
const statusChart = new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: <?= json_encode($statusLabels) ?>,
        datasets: [{
            data: <?= json_encode(array_values($statusCounts)) ?>,
            backgroundColor: [
                'rgba(245, 158, 11, 0.8)', // Pending - yellow
                'rgba(59, 130, 246, 0.8)', // Confirmed - blue
                'rgba(79, 70, 229, 0.8)',  // Processing - indigo
                'rgba(100, 116, 139, 0.8)', // Shipped - gray
                'rgba(16, 185, 129, 0.8)', // Delivered - green
                'rgba(239, 68, 68, 0.8)'   // Cancelled - red
            ],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'right',
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const label = context.label || '';
                        const value = context.raw || 0;
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = Math.round((value / total) * 100);
                        return `${label}: ${value} (${percentage}%)`;
                    }
                }
            }
        },
        cutout: '70%'
    }
});

// Dark mode toggle
document.getElementById('darkModeToggle').addEventListener('change', function() {
    document.body.classList.toggle('dark-mode');
    
    // Save preference to session or localStorage
    fetch('actions/toggle_dark_mode.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ dark_mode: this.checked })
    });
    
    // Update charts for dark mode
    updateChartsForDarkMode(this.checked);
});

function updateChartsForDarkMode(isDark) {
    const gridColor = isDark ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';
    const textColor = isDark ? 'rgba(255, 255, 255, 0.8)' : 'rgba(0, 0, 0, 0.8)';
    
    // Update sales chart
    salesChart.options.scales.x.grid.color = gridColor;
    salesChart.options.scales.y.grid.color = gridColor;
    salesChart.options.scales.x.ticks.color = textColor;
    salesChart.options.scales.y.ticks.color = textColor;
    salesChart.update();
    
    // Update status chart
    statusChart.options.plugins.legend.labels.color = textColor;
    statusChart.update();
}


// Initialize AOS animations
AOS.init({
    duration: 800,
    easing: 'ease-in-out',
    once: true
});



</script>

<?php 
require_once __DIR__ . '/includes/footer.php';
?>