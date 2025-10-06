<?php
require_once 'config/database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("Location: login.php");
    exit();
}

$pageTitle = "Analytics - KBMO Center";
include 'includes/header.php';

// Get analytics data
$total_users = $pdo->query("SELECT COUNT(*) FROM users WHERE user_type = 'user'")->fetchColumn();
$total_documents = $pdo->query("SELECT COUNT(*) FROM documents")->fetchColumn();
$total_revenue = $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status = 'completed'")->fetchColumn();
$avg_order_value = $pdo->query("SELECT COALESCE(AVG(amount), 0) FROM payments WHERE status = 'completed'")->fetchColumn();

// Revenue data for charts (last 30 days)
$revenue_data = $pdo->query("SELECT DATE(created_at) as date, SUM(amount) as daily_revenue 
                            FROM payments 
                            WHERE status = 'completed' 
                            AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                            GROUP BY DATE(created_at) 
                            ORDER BY date")->fetchAll();

// User growth data
$user_growth = $pdo->query("SELECT DATE(created_at) as date, COUNT(*) as new_users 
                           FROM users 
                           WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                           GROUP BY DATE(created_at) 
                           ORDER BY date")->fetchAll();

// Service performance
$service_performance = $pdo->query("SELECT s.name, COUNT(d.id) as order_count, COALESCE(SUM(p.amount), 0) as revenue
                                   FROM services s 
                                   LEFT JOIN documents d ON s.id = d.service_id 
                                   LEFT JOIN payments p ON d.id = p.document_id AND p.status = 'completed'
                                   GROUP BY s.id, s.name 
                                   ORDER BY revenue DESC")->fetchAll();

// Document status distribution
$status_distribution = $pdo->query("SELECT status, COUNT(*) as count FROM documents GROUP BY status")->fetchAll();
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'includes/admin_sidebar.php'; ?>

        <!-- Main content -->
        <div class="col-md-9 col-lg-10 ms-sm-auto px-4 py-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Analytics Dashboard</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <select class="form-select form-select-sm" id="timeRange">
                            <option value="7">Last 7 Days</option>
                            <option value="30" selected>Last 30 Days</option>
                            <option value="90">Last 90 Days</option>
                            <option value="365">Last Year</option>
                        </select>
                    </div>
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-outline-secondary">Export Report</button>
                    </div>
                </div>
            </div>

            <!-- Key Metrics -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card dashboard-card border-primary">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="text-primary"><?php echo $total_users; ?></h4>
                                    <p class="text-muted">Total Users</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-users fa-2x text-primary"></i>
                                </div>
                            </div>
                            <div class="mt-2">
                                <small class="text-success">
                                    <i class="fas fa-arrow-up me-1"></i>
                                    <?php
                                    $new_users_week = $pdo->query("SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn();
                                    echo $new_users_week;
                                    ?> new this week
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card dashboard-card border-success">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="text-success"><?php echo $total_documents; ?></h4>
                                    <p class="text-muted">Total Projects</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-file-alt fa-2x text-success"></i>
                                </div>
                            </div>
                            <div class="mt-2">
                                <small class="text-success">
                                    <i class="fas fa-arrow-up me-1"></i>
                                    <?php
                                    $new_docs_week = $pdo->query("SELECT COUNT(*) FROM documents WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn();
                                    echo $new_docs_week;
                                    ?> new this week
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card dashboard-card border-info">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="text-info">$<?php echo number_format($total_revenue, 2); ?></h4>
                                    <p class="text-muted">Total Revenue</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-dollar-sign fa-2x text-info"></i>
                                </div>
                            </div>
                            <div class="mt-2">
                                <small class="text-success">
                                    <i class="fas fa-arrow-up me-1"></i>
                                    UGX <?php echo number_format($total_revenue * 3800, 0); ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card dashboard-card border-warning">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="text-warning">$<?php echo number_format($avg_order_value, 2); ?></h4>
                                    <p class="text-muted">Avg Order Value</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-chart-line fa-2x text-warning"></i>
                                </div>
                            </div>
                            <div class="mt-2">
                                <small class="text-muted">
                                    Per completed order
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="row mb-4">
                <!-- Revenue Chart -->
                <div class="col-md-8 mb-4">
                    <div class="card dashboard-card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Revenue Trend (Last 30 Days)</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="revenueChart" width="400" height="200"></canvas>
                        </div>
                    </div>
                </div>

                <!-- User Growth Chart -->
                <div class="col-md-4 mb-4">
                    <div class="card dashboard-card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">User Growth</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="userGrowthChart" width="400" height="200"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Charts -->
            <div class="row mb-4">
                <!-- Service Performance -->
                <div class="col-md-6 mb-4">
                    <div class="card dashboard-card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Service Performance</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="serviceChart" width="400" height="250"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Document Status Distribution -->
                <div class="col-md-6 mb-4">
                    <div class="card dashboard-card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Project Status Distribution</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="statusChart" width="400" height="250"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Reports -->
            <div class="row">
                <!-- Top Services Table -->
                <div class="col-md-6 mb-4">
                    <div class="card dashboard-card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Top Performing Services</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Service</th>
                                            <th>Orders</th>
                                            <th>Revenue</th>
                                            <th>Avg. Price</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (array_slice($service_performance, 0, 5) as $service): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($service['name']); ?></td>
                                                <td><?php echo $service['order_count']; ?></td>
                                                <td>$<?php echo number_format($service['revenue'], 2); ?></td>
                                                <td>
                                                    <?php if ($service['order_count'] > 0): ?>
                                                        $<?php echo number_format($service['revenue'] / $service['order_count'], 2); ?>
                                                    <?php else: ?>
                                                        $0.00
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="col-md-6 mb-4">
                    <div class="card dashboard-card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Recent Activity</h5>
                        </div>
                        <div class="card-body">
                            <div class="list-group list-group-flush">
                                <?php
                                $recent_activity = $pdo->query("SELECT 'payment' as type, CONCAT('Payment: $', amount) as description, created_at 
                                                               FROM payments WHERE status = 'completed'
                                                               UNION ALL
                                                               SELECT 'document' as type, CONCAT('New project: ', title) as description, created_at 
                                                               FROM documents 
                                                               UNION ALL
                                                               SELECT 'user' as type, CONCAT('New user: ', username) as description, created_at 
                                                               FROM users
                                                               ORDER BY created_at DESC LIMIT 6")->fetchAll();

                                foreach ($recent_activity as $activity):
                                    $icon = '';
                                    $color = '';
                                    switch ($activity['type']) {
                                        case 'payment':
                                            $icon = 'fa-dollar-sign';
                                            $color = 'success';
                                            break;
                                        case 'document':
                                            $icon = 'fa-file-alt';
                                            $color = 'primary';
                                            break;
                                        case 'user':
                                            $icon = 'fa-user';
                                            $color = 'info';
                                            break;
                                    }
                                ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0">
                                        <div>
                                            <i class="fas <?php echo $icon; ?> text-<?php echo $color; ?> me-2"></i>
                                            <small><?php echo htmlspecialchars($activity['description']); ?></small>
                                        </div>
                                        <small class="text-muted"><?php echo date('M j', strtotime($activity['created_at'])); ?></small>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // Prepare chart data
    const revenueData = <?php echo json_encode($revenue_data); ?>;
    const userGrowthData = <?php echo json_encode($user_growth); ?>;
    const serviceData = <?php echo json_encode($service_performance); ?>;
    const statusData = <?php echo json_encode($status_distribution); ?>;

    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: revenueData.map(item => new Date(item.date).toLocaleDateString()),
            datasets: [{
                label: 'Daily Revenue ($)',
                data: revenueData.map(item => parseFloat(item.daily_revenue)),
                borderColor: '#3498db',
                backgroundColor: 'rgba(52, 152, 219, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Revenue Trend'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // User Growth Chart
    const userGrowthCtx = document.getElementById('userGrowthChart').getContext('2d');
    new Chart(userGrowthCtx, {
        type: 'bar',
        data: {
            labels: userGrowthData.map(item => new Date(item.date).toLocaleDateString()),
            datasets: [{
                label: 'New Users',
                data: userGrowthData.map(item => item.new_users),
                backgroundColor: 'rgba(46, 204, 113, 0.8)',
                borderColor: '#27ae60',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'User Registration'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Service Performance Chart
    const serviceCtx = document.getElementById('serviceChart').getContext('2d');
    new Chart(serviceCtx, {
        type: 'bar',
        data: {
            labels: serviceData.map(item => item.name),
            datasets: [{
                label: 'Revenue ($)',
                data: serviceData.map(item => parseFloat(item.revenue)),
                backgroundColor: 'rgba(155, 89, 182, 0.8)',
                borderColor: '#8e44ad',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Revenue by Service'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Status Distribution Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: statusData.map(item => item.status.replace('_', ' ')),
            datasets: [{
                data: statusData.map(item => item.count),
                backgroundColor: [
                    '#3498db', // pending
                    '#f39c12', // in_progress
                    '#2ecc71', // completed
                    '#e74c3c' // rejected
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                title: {
                    display: true,
                    text: 'Project Status Distribution'
                }
            }
        }
    });

    // Time range filter
    document.getElementById('timeRange').addEventListener('change', function() {
        // In a real implementation, this would reload the page with new time range
        alert('Time range filter would reload data for selected period.');
    });
</script>

<?php include 'includes/footer.php'; ?>