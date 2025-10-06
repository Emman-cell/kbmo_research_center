<?php
require_once 'config/database.php';

// Set Uganda timezone
date_default_timezone_set('Africa/Kampala');

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("Location: login.php");
    exit();
}

$pageTitle = "Admin Dashboard - KBMO Center";
include 'includes/header.php';

// Get comprehensive statistics
$users_count = $pdo->query("SELECT COUNT(*) FROM users WHERE user_type = 'user'")->fetchColumn();
$documents_count = $pdo->query("SELECT COUNT(*) FROM documents")->fetchColumn();
$pending_documents = $pdo->query("SELECT COUNT(*) FROM documents WHERE status = 'pending'")->fetchColumn();
$total_revenue = $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status = 'completed'")->fetchColumn();
$pending_payments = $pdo->query("SELECT COUNT(*) FROM payments WHERE status = 'pending'")->fetchColumn();
$completed_services = $pdo->query("SELECT COUNT(*) FROM documents WHERE status = 'completed'")->fetchColumn();

// Get recent documents
$stmt = $pdo->query("SELECT d.*, u.username, u.full_name, s.name as service_name 
                     FROM documents d 
                     JOIN users u ON d.user_id = u.id 
                     JOIN services s ON d.service_id = s.id 
                     ORDER BY d.created_at DESC 
                     LIMIT 10");
$recent_documents = $stmt->fetchAll();

// Get revenue data for charts
$revenue_data = $pdo->query("SELECT DATE(created_at) as date, SUM(amount) as daily_revenue 
                            FROM payments 
                            WHERE status = 'completed' 
                            AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                            GROUP BY DATE(created_at) 
                            ORDER BY date")->fetchAll();

// Get service distribution
$service_distribution = $pdo->query("SELECT s.name, COUNT(d.id) as count 
                                   FROM services s 
                                   LEFT JOIN documents d ON s.id = d.service_id 
                                   GROUP BY s.id, s.name")->fetchAll();

// Get user growth data
$user_growth = $pdo->query("SELECT DATE(created_at) as date, COUNT(*) as new_users 
                           FROM users 
                           WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                           GROUP BY DATE(created_at) 
                           ORDER BY date")->fetchAll();

// Get chat statistics
$stats = [
    'active_conversations' => $pdo->query("SELECT COUNT(*) FROM chat_conversations WHERE status = 'active'")->fetchColumn()
];

// Handle document status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $document_id = $_POST['document_id'];
    $new_status = $_POST['status'];

    $stmt = $pdo->prepare("UPDATE documents SET status = ? WHERE id = ?");
    if ($stmt->execute([$new_status, $document_id])) {
        $status_message = "Document status updated successfully!";
        // Refresh the page to show updated data
        header("Location: admin_dashboard.php");
        exit();
    } else {
        $status_message = "Error updating document status.";
    }
}

// Get all payments for payment management section
$all_payments = $pdo->query("SELECT p.*, u.username, u.full_name, d.title as document_title
                            FROM payments p 
                            JOIN users u ON p.user_id = u.id 
                            LEFT JOIN documents d ON p.document_id = d.id 
                            ORDER BY p.created_at DESC")->fetchAll();
?>
<!-- <style>
    .cursor-pointer {
        cursor: pointer;
    }

    .sidebar-heading {
        font-size: 0.875rem;
        font-weight: 600;
        text-transform: uppercase;
        color: #6c757d;
    }

    .dashboard-card {
        transition: transform 0.2s;
        border-radius: 10px;
        overflow: hidden;
    }

    .dashboard-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }

    .status-badge {
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .status-pending {
        background-color: #fff3cd;
        color: #856404;
    }

    .status-in-progress {
        background-color: #cce7ff;
        color: #004085;
    }

    .status-completed {
        background-color: #d4edda;
        color: #155724;
    }

    .status-rejected {
        background-color: #f8d7da;
        color: #721c24;
    }

    .sidebar {
        background-color: #f8f9fa;
        min-height: 100vh;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    .nav-link {
        color: #495057;
        border-radius: 5px;
        margin-bottom: 5px;
    }

    .nav-link:hover,
    .nav-link.active {
        background-color: #e9ecef;
        color: #0d6efd;
    }

    .modal-content {
        border-radius: 10px;
        border: none;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    }

    .modal-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
        border-radius: 10px 10px 0 0;
    }

    .document-details {
        background-color: #f8f9fa;
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 15px;
    }

    .document-details p {
        margin-bottom: 5px;
    }

    .document-details strong {
        color: #495057;
    }

    .table-responsive {
        border-radius: 10px;
        overflow: hidden;
    }

    .table th {
        background-color: #f8f9fa;
        border-top: none;
        font-weight: 600;
    }

    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
        font-weight: 600;
    }

    .btn {
        border-radius: 5px;
    }

    .btn-sm {
        padding: 5px 10px;
        font-size: 0.875rem;
    }

    .section-content {
        display: none;
    }

    #dashboard-section {
        display: block;
    }

    .payment-status-pending {
        background-color: #fff3cd;
        color: #856404;
    }

    .payment-status-completed {
        background-color: #d4edda;
        color: #155724;
    }

    .payment-status-failed {
        background-color: #f8d7da;
        color: #721c24;
    }

    .timezone-badge {
        background-color: #3498db;
        color: white;
        padding: 3px 8px;
        border-radius: 4px;
        font-size: 0.75rem;
    }
</style> -->
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 sidebar">
            <div class="position-sticky pt-3">
                <!-- User Profile Quick View -->
                <div class="text-center mb-4 p-3 bg-dark rounded">
                    <img src="<?php echo !empty($_SESSION['profile_image']) ? 'uploads/profiles/' . htmlspecialchars($_SESSION['profile_image']) : 'https://via.placeholder.com/80/3498db/ffffff?text=' . urlencode(substr($_SESSION['full_name'] ?: $_SESSION['username'], 0, 1)); ?>"
                        alt="Admin"
                        class="rounded-circle mb-2 border border-3 border-primary"
                        style="width: 80px; height: 80px; object-fit: cover;">
                    <h6 class="text-white mb-1"><?php echo htmlspecialchars($_SESSION['full_name'] ?: $_SESSION['username']); ?></h6>
                    <small class="text-light">Administrator</small>
                    <div class="mt-2">
                        <span class="timezone-badge">Uganda Time (EAT)</span>
                    </div>
                </div>

                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="#" onclick="showSection('dashboard-section')">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                    </li>

                    <!-- User Management -->
                    <li class="nav-item">
                        <a class="nav-link" href="#" onclick="showSection('user-management')">
                            <i class="fas fa-users me-2"></i>User Management
                        </a>
                    </li>

                    <!-- Document Management -->
                    <li class="nav-item">
                        <a class="nav-link" href="#" onclick="showSection('document-management')">
                            <i class="fas fa-file-alt me-2"></i>Documents
                        </a>
                    </li>

                    <!-- Payment Management -->
                    <li class="nav-item">
                        <a class="nav-link" href="#" onclick="showSection('payment-management')">
                            <i class="fas fa-dollar-sign me-2"></i>Payments
                        </a>
                    </li>

                    <!-- Service Management -->
                    <li class="nav-item">
                        <a class="nav-link" href="#" onclick="showSection('service-management')">
                            <i class="fas fa-concierge-bell me-2"></i>Services
                        </a>
                    </li>

                    <!-- Analytics -->
                    <li class="nav-item">
                        <a class="nav-link" href="#" onclick="showSection('analytics')">
                            <i class="fas fa-chart-bar me-2"></i>Analytics
                        </a>
                    </li>

                    <!-- System Settings -->
                    <li class="nav-item">
                        <a class="nav-link" href="#" onclick="showSection('settings')">
                            <i class="fas fa-cog me-2"></i>Settings
                        </a>
                    </li>

                    <li class="nav-item mt-3">
                        <a class="nav-link text-danger" href="logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Main content -->
        <div class="col-md-9 col-lg-10 ms-sm-auto px-4 py-4">
            <!-- Dashboard Section -->
            <div id="dashboard-section" class="section-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Admin Dashboard</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary">Export</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary">Print</button>
                        </div>
                        <span class="me-2 text-muted">Welcome, <?php echo $_SESSION['full_name']; ?>!</span>
                        <span class="badge bg-info"><?php echo date('h:i A, M j, Y'); ?> EAT</span>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
                        <div class="card dashboard-card text-white bg-primary">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4><?php echo $users_count; ?></h4>
                                        <p>Total Users</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-users fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
                        <div class="card dashboard-card text-white bg-success">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4><?php echo $documents_count; ?></h4>
                                        <p>Total Projects</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-file-alt fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
                        <div class="card dashboard-card text-white bg-warning">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4><?php echo $pending_documents; ?></h4>
                                        <p>Pending Projects</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-clock fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
                        <div class="card dashboard-card text-white bg-info">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4>$<?php echo number_format($total_revenue, 2); ?></h4>
                                        <p>Total Revenue</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-dollar-sign fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
                        <div class="card dashboard-card text-white bg-danger">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4><?php echo $pending_payments; ?></h4>
                                        <p>Pending Payments</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-exclamation-triangle fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
                        <div class="card dashboard-card text-white bg-secondary">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4><?php echo $completed_services; ?></h4>
                                        <p>Completed</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-check-circle fa-2x"></i>
                                    </div>
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
                                <h5 class="card-title mb-0">Revenue Overview (Last 30 Days)</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="revenueChart" width="400" height="200"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Service Distribution -->
                    <div class="col-md-4 mb-4">
                        <div class="card dashboard-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Service Distribution</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="serviceChart" width="400" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3D Charts Section -->
                <div class="row mb-4">
                    <!-- 3D User Growth Chart -->
                    <div class="col-md-6 mb-4">
                        <div class="card dashboard-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">User Growth (3D View)</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="userGrowth3DChart" width="400" height="300"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- 3D Revenue Chart -->
                    <div class="col-md-6 mb-4">
                        <div class="card dashboard-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Revenue Distribution (3D)</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="revenue3DChart" width="400" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Analytics -->
                <div class="row">
                    <!-- Recent Activity -->
                    <div class="col-md-6 mb-4">
                        <div class="card dashboard-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Recent Activity</h5>
                            </div>
                            <div class="card-body">
                                <div class="list-group list-group-flush">
                                    <?php
                                    $activity_stmt = $pdo->query("SELECT 'document' as type, title as description, created_at FROM documents 
                                                                     UNION ALL 
                                                                     SELECT 'payment' as type, CONCAT('Payment $', amount) as description, created_at FROM payments 
                                                                     UNION ALL 
                                                                     SELECT 'user' as type, CONCAT('New user: ', username) as description, created_at FROM users 
                                                                     ORDER BY created_at DESC LIMIT 10");
                                    $activities = $activity_stmt->fetchAll();

                                    foreach ($activities as $activity):
                                        $icon = '';
                                        $color = '';
                                        switch ($activity['type']) {
                                            case 'document':
                                                $icon = 'fa-file-alt';
                                                $color = 'primary';
                                                break;
                                            case 'payment':
                                                $icon = 'fa-dollar-sign';
                                                $color = 'success';
                                                break;
                                            case 'user':
                                                $icon = 'fa-user';
                                                $color = 'info';
                                                break;
                                        }
                                    ?>
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <i class="fas <?php echo $icon; ?> text-<?php echo $color; ?> me-2"></i>
                                                <?php echo htmlspecialchars($activity['description']); ?>
                                            </div>
                                            <small class="text-muted"><?php echo date('M j, H:i', strtotime($activity['created_at'])); ?></small>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- System Status -->
                    <div class="col-md-6 mb-4">
                        <div class="card dashboard-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">System Status</h5>
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-6 mb-3">
                                        <div class="bg-light rounded p-3">
                                            <i class="fas fa-database fa-2x text-primary mb-2"></i>
                                            <h6>Database</h6>
                                            <span class="badge bg-success">Online</span>
                                        </div>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <div class="bg-light rounded p-3">
                                            <i class="fas fa-server fa-2x text-primary mb-2"></i>
                                            <h6>Server</h6>
                                            <span class="badge bg-success">Stable</span>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="bg-light rounded p-3">
                                            <i class="fas fa-payment fa-2x text-primary mb-2"></i>
                                            <h6>Payments</h6>
                                            <span class="badge bg-success">Active</span>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="bg-light rounded p-3">
                                            <i class="fas fa-upload fa-2x text-primary mb-2"></i>
                                            <h6>Uploads</h6>
                                            <span class="badge bg-success">Normal</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Chat Management Card -->
                <div class="col-md-6 col-xl-3 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="card-title text-muted text-uppercase fs-7 fw-bold">Chat Management</h6>
                                    <h3 class="fw-bold text-primary mb-1"><?php echo $stats['active_conversations']; ?></h3>
                                    <p class="text-muted mb-0">Active Conversations</p>
                                </div>
                                <div class="avatar avatar-sm">
                                    <i class="fas fa-comments fa-2x text-primary"></i>
                                </div>
                            </div>
                            <div class="mt-3">
                                <a href="./includes/admin_chat.php" class="btn btn-primary btn-sm">Manage Chats</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Documents Table -->
                <div class="card dashboard-card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Recent Projects</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>User</th>
                                        <th>Service</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_documents as $document): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($document['title']); ?></td>
                                            <td><?php echo htmlspecialchars($document['full_name'] ?: $document['username']); ?></td>
                                            <td><?php echo htmlspecialchars($document['service_name']); ?></td>
                                            <td>
                                                <?php
                                                $status_class = '';
                                                switch ($document['status']) {
                                                    case 'pending':
                                                        $status_class = 'status-pending';
                                                        break;
                                                    case 'in_progress':
                                                        $status_class = 'status-in-progress';
                                                        break;
                                                    case 'completed':
                                                        $status_class = 'status-completed';
                                                        break;
                                                    case 'rejected':
                                                        $status_class = 'status-rejected';
                                                        break;
                                                }
                                                ?>
                                                <span class="status-badge <?php echo $status_class; ?>">
                                                    <?php echo ucfirst(str_replace('_', ' ', $document['status'])); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('M j, Y', strtotime($document['created_at'])); ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary" onclick="viewDocumentDetails(<?php echo $document['id']; ?>)">
                                                    <i class="fas fa-eye"></i> View
                                                </button>
                                                <button class="btn btn-sm btn-outline-warning update-status" data-id="<?php echo $document['id']; ?>" data-status="<?php echo $document['status']; ?>">
                                                    <i class="fas fa-edit"></i> Status
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Management Section -->
            <div id="user-management" class="section-content" style="display: none;">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">User Management</h1>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">All Users</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Username</th>
                                        <th>Full Name</th>
                                        <th>Email</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $users = $pdo->query("SELECT * FROM users ORDER BY id DESC")->fetchAll();
                                    foreach ($users as $user):
                                    ?>
                                        <tr>
                                            <td><?php echo $user['id']; ?></td>
                                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                                            <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td><?php echo ucfirst($user['user_type']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $user['status'] == 'active' ? 'success' : 'danger'; ?>">
                                                    <?php echo ucfirst($user['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary">Edit</button>
                                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Document Management Section -->
            <div id="document-management" class="section-content" style="display: none;">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Document Management</h1>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">All Documents</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Title</th>
                                        <th>User</th>
                                        <th>Service</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $all_documents = $pdo->query("SELECT d.*, u.username, u.full_name, s.name as service_name 
                                                                     FROM documents d 
                                                                     JOIN users u ON d.user_id = u.id 
                                                                     JOIN services s ON d.service_id = s.id 
                                                                     ORDER BY d.created_at DESC")->fetchAll();
                                    foreach ($all_documents as $document):
                                    ?>
                                        <tr>
                                            <td><?php echo $document['id']; ?></td>
                                            <td><?php echo htmlspecialchars($document['title']); ?></td>
                                            <td><?php echo htmlspecialchars($document['full_name'] ?: $document['username']); ?></td>
                                            <td><?php echo htmlspecialchars($document['service_name']); ?></td>
                                            <td>
                                                <?php
                                                $status_class = '';
                                                switch ($document['status']) {
                                                    case 'pending':
                                                        $status_class = 'status-pending';
                                                        break;
                                                    case 'in_progress':
                                                        $status_class = 'status-in-progress';
                                                        break;
                                                    case 'completed':
                                                        $status_class = 'status-completed';
                                                        break;
                                                    case 'rejected':
                                                        $status_class = 'status-rejected';
                                                        break;
                                                }
                                                ?>
                                                <span class="status-badge <?php echo $status_class; ?>">
                                                    <?php echo ucfirst(str_replace('_', ' ', $document['status'])); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('M j, Y', strtotime($document['created_at'])); ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary" onclick="viewDocumentDetails(<?php echo $document['id']; ?>)">
                                                    <i class="fas fa-eye"></i> View
                                                </button>
                                                <button class="btn btn-sm btn-outline-warning update-status" data-id="<?php echo $document['id']; ?>" data-status="<?php echo $document['status']; ?>">
                                                    <i class="fas fa-edit"></i> Status
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Management Section -->
            <div id="payment-management" class="section-content" style="display: none;">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Payment Management</h1>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">All Payments</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>User</th>
                                        <th>Document</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($all_payments as $payment): ?>
                                        <tr>
                                            <td><?php echo $payment['id']; ?></td>
                                            <td><?php echo htmlspecialchars($payment['full_name'] ?: $payment['username']); ?></td>
                                            <td><?php echo htmlspecialchars($payment['document_title'] ?: 'N/A'); ?></td>
                                            <td>$<?php echo number_format($payment['amount'], 2); ?></td>
                                            <td>
                                                <?php
                                                $status_class = '';
                                                switch ($payment['status']) {
                                                    case 'pending':
                                                        $status_class = 'payment-status-pending';
                                                        break;
                                                    case 'completed':
                                                        $status_class = 'payment-status-completed';
                                                        break;
                                                    case 'failed':
                                                        $status_class = 'payment-status-failed';
                                                        break;
                                                }
                                                ?>
                                                <span class="status-badge <?php echo $status_class; ?>">
                                                    <?php echo ucfirst($payment['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('M j, Y', strtotime($payment['created_at'])); ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary">View</button>
                                                <button class="btn btn-sm btn-outline-warning">Update</button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Service Management Section -->
            <div id="service-management" class="section-content" style="display: none;">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Service Management</h1>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">All Services</h5>
                        <button class="btn btn-primary btn-sm">Add New Service</button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Description</th>
                                        <th>Price</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $services = $pdo->query("SELECT * FROM services ORDER BY id DESC")->fetchAll();
                                    foreach ($services as $service):
                                    ?>
                                        <tr>
                                            <td><?php echo $service['id']; ?></td>
                                            <td><?php echo htmlspecialchars($service['name']); ?></td>
                                            <td><?php echo htmlspecialchars($service['description']); ?></td>
                                            <td>$<?php echo number_format($service['price'], 2); ?></td>
                                            <?php
                                            $service_status = isset($service['status']) ? $service['status'] : 'inactive';
                                            ?>
                                            <span class="badge bg-<?php echo $service_status == 'active' ? 'success' : 'danger'; ?>">
                                                <?php echo ucfirst($service_status); ?>
                                            </span>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary">Edit</button>
                                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Analytics Section -->
            <div id="analytics" class="section-content" style="display: none;">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Analytics</h1>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">User Registration Trend</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="analyticsUserChart" width="400" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Document Status Distribution</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="analyticsDocumentChart" width="400" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Settings Section -->
            <div id="settings" class="section-content" style="display: none;">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">System Settings</h1>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">General Settings</h5>
                    </div>
                    <div class="card-body">
                        <form>
                            <div class="mb-3">
                                <label for="siteName" class="form-label">Site Name</label>
                                <input type="text" class="form-control" id="siteName" value="KBMO Center">
                            </div>
                            <div class="mb-3">
                                <label for="siteEmail" class="form-label">Site Email</label>
                                <input type="email" class="form-control" id="siteEmail" value="admin@kbmocenter.com">
                            </div>
                            <div class="mb-3">
                                <label for="currency" class="form-label">Currency</label>
                                <select class="form-control" id="currency">
                                    <option value="USD" selected>USD ($)</option>
                                    <option value="EUR">EUR (€)</option>
                                    <option value="GBP">GBP (£)</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="timezone" class="form-label">Timezone</label>
                                <select class="form-control" id="timezone">
                                    <option value="Africa/Kampala" selected>Uganda (EAT)</option>
                                    <option value="UTC">UTC</option>
                                    <option value="EST">EST</option>
                                    <option value="PST">PST</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Save Settings</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Document Details Modal -->
<div class="modal fade" id="documentModal" tabindex="-1" aria-labelledby="documentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="documentModalLabel">Document Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="documentDetails">
                <!-- Document details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Status Update Modal -->
<div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="statusModalLabel">Update Document Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <input type="hidden" name="document_id" id="statusDocumentId">
                    <div class="mb-3">
                        <label for="statusSelect" class="form-label">Select Status</label>
                        <select class="form-control" id="statusSelect" name="status">
                            <option value="pending">Pending</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Include Chart.js and 3D chart libraries -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns/dist/chartjs-adapter-date-fns.bundle.min.js"></script>

<script>
    // Navigation function
    function showSection(sectionId) {
        // Hide all sections
        document.querySelectorAll('.section-content').forEach(section => {
            section.style.display = 'none';
        });

        // Show the selected section
        document.getElementById(sectionId).style.display = 'block';

        // Update active nav link
        document.querySelectorAll('.nav-link').forEach(link => {
            link.classList.remove('active');
        });
        event.target.classList.add('active');
    }

    // Prepare chart data
    const revenueData = <?php echo json_encode($revenue_data); ?>;
    const serviceData = <?php echo json_encode($service_distribution); ?>;
    const userGrowthData = <?php echo json_encode($user_growth); ?>;

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

    // Service Distribution Chart
    const serviceCtx = document.getElementById('serviceChart').getContext('2d');
    new Chart(serviceCtx, {
        type: 'doughnut',
        data: {
            labels: serviceData.map(item => item.name),
            datasets: [{
                data: serviceData.map(item => item.count),
                backgroundColor: [
                    '#3498db', '#2ecc71', '#e74c3c', '#f39c12', '#9b59b6',
                    '#1abc9c', '#34495e', '#d35400', '#c0392b', '#16a085'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // 3D User Growth Chart
    const userGrowth3DCtx = document.getElementById('userGrowth3DChart').getContext('2d');
    new Chart(userGrowth3DCtx, {
        type: 'bar',
        data: {
            labels: userGrowthData.map(item => new Date(item.date).toLocaleDateString()),
            datasets: [{
                label: 'New Users',
                data: userGrowthData.map(item => item.new_users),
                backgroundColor: 'rgba(52, 152, 219, 0.8)',
                borderColor: '#2980b9',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'User Registration Trend'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // 3D Revenue Distribution Chart
    const revenue3DCtx = document.getElementById('revenue3DChart').getContext('2d');
    new Chart(revenue3DCtx, {
        type: 'bar',
        data: {
            labels: ['Concept Notes', 'Proposals', 'Thesis', 'Data Analysis', 'Manuscripts'],
            datasets: [{
                label: 'Revenue ($)',
                data: [1500, 3000, 8000, 2500, 4000],
                backgroundColor: [
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(255, 206, 86, 0.8)',
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(153, 102, 255, 0.8)'
                ],
                borderColor: [
                    'rgb(255, 99, 132)',
                    'rgb(54, 162, 235)',
                    'rgb(255, 206, 86)',
                    'rgb(75, 192, 192)',
                    'rgb(153, 102, 255)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                },
                title: {
                    display: true,
                    text: 'Revenue by Service Type'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Analytics User Chart
    const analyticsUserCtx = document.getElementById('analyticsUserChart').getContext('2d');
    new Chart(analyticsUserCtx, {
        type: 'line',
        data: {
            labels: userGrowthData.map(item => new Date(item.date).toLocaleDateString()),
            datasets: [{
                label: 'New Users',
                data: userGrowthData.map(item => item.new_users),
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
                    text: 'User Growth Trend'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Analytics Document Chart
    const analyticsDocumentCtx = document.getElementById('analyticsDocumentChart').getContext('2d');
    new Chart(analyticsDocumentCtx, {
        type: 'pie',
        data: {
            labels: ['Pending', 'In Progress', 'Completed', 'Rejected'],
            datasets: [{
                data: [<?php echo $pending_documents; ?>,
                    <?php echo $pdo->query("SELECT COUNT(*) FROM documents WHERE status = 'in_progress'")->fetchColumn(); ?>,
                    <?php echo $completed_services; ?>,
                    <?php echo $pdo->query("SELECT COUNT(*) FROM documents WHERE status = 'rejected'")->fetchColumn(); ?>
                ],
                backgroundColor: [
                    '#ffc107',
                    '#17a2b8',
                    '#28a745',
                    '#dc3545'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Document viewer function
    function viewDocumentDetails(documentId) {
        // In a real implementation, you would fetch document details via AJAX
        // For this example, we'll use a placeholder
        const documentDetails = `
                <div class="document-details">
                    <p><strong>Title:</strong> Document #${documentId}</p>
                    <p><strong>User:</strong> John Doe</p>
                    <p><strong>Service:</strong> Research Proposal</p>
                    <p><strong>Status:</strong> <span class="status-badge status-in-progress">In Progress</span></p>
                    <p><strong>Date Submitted:</strong> ${new Date().toLocaleDateString()}</p>
                    <p><strong>Description:</strong> This is a detailed description of the document requirements and specifications.</p>
                </div>
                <div class="mb-3">
                    <h6>Files:</h6>
                    <ul>
                        <li><a href="#">research_proposal.docx</a></li>
                        <li><a href="#">supporting_materials.zip</a></li>
                    </ul>
                </div>
                <div class="mb-3">
                    <h6>Comments:</h6>
                    <div class="border p-3 rounded">
                        <p class="mb-1"><strong>Admin:</strong> We are currently working on your document.</p>
                        <small class="text-muted">${new Date().toLocaleDateString()}</small>
                    </div>
                </div>
            `;

        document.getElementById('documentDetails').innerHTML = documentDetails;
        const modal = new bootstrap.Modal(document.getElementById('documentModal'));
        modal.show();
    }

    // Status update modal
    document.querySelectorAll('.update-status').forEach(button => {
        button.addEventListener('click', function() {
            const documentId = this.getAttribute('data-id');
            const currentStatus = this.getAttribute('data-status');

            document.getElementById('statusDocumentId').value = documentId;
            document.getElementById('statusSelect').value = currentStatus;

            const modal = new bootstrap.Modal(document.getElementById('statusModal'));
            modal.show();
        });
    });

    // Update time every minute
    function updateTime() {
        const now = new Date();
        const options = {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            month: 'short',
            day: 'numeric',
            year: 'numeric',
            timeZone: 'Africa/Kampala'
        };
        const timeString = now.toLocaleString('en-US', options) + ' EAT';

        const timeElement = document.querySelector('.bg-info.badge');
        if (timeElement) {
            timeElement.textContent = timeString;
        }
    }

    // Update time immediately and then every minute
    updateTime();
    setInterval(updateTime, 60000);
</script>

<?php include 'includes/footer.php'; ?>