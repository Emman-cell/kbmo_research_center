<?php
// admin_sidebar.php - Admin Navigation Sidebar
?>
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
        </div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'admin_dashboard.php' ? 'active' : ''; ?>" href="admin_dashboard.php">
                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                </a>
            </li>

            <!-- User Management -->
            <li class="nav-item">
                <a class="nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), ['admin_users.php', 'admin_user_add.php', 'admin_user_edit.php']) ? 'active' : ''; ?>" href="admin_users.php">
                    <i class="fas fa-users me-2"></i>User Management
                </a>
            </li>

            <!-- Document Management -->
            <li class="nav-item">
                <a class="nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), ['admin_documents.php', 'admin_document_view.php']) ? 'active' : ''; ?>" href="admin_documents.php">
                    <i class="fas fa-file-alt me-2"></i>Documents
                </a>
            </li>

            <!-- Payment Management -->
            <li class="nav-item">
                <a class="nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), ['admin_payments.php', 'admin_payment_view.php']) ? 'active' : ''; ?>" href="admin_payments.php">
                    <i class="fas fa-dollar-sign me-2"></i>Payments
                </a>
            </li>

            <!-- Service Management -->
            <li class="nav-item">
                <a class="nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), ['admin_services.php', 'admin_service_add.php', 'admin_service_edit.php']) ? 'active' : ''; ?>" href="admin_services.php">
                    <i class="fas fa-concierge-bell me-2"></i>Services
                </a>
            </li>

            <!-- Analytics -->
            <li class="nav-item">
                <a class="nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), ['admin_analytics.php', 'admin_reports.php']) ? 'active' : ''; ?>" href="admin_analytics.php">
                    <i class="fas fa-chart-bar me-2"></i>Analytics
                </a>
            </li>
            <!-- Analytics -->
            <li class="nav-item">
                <a class="nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), ['admin_reviews.php', 'admin_reports.php']) ? 'active' : ''; ?>" href="admin_reviews.php">
                    <i class="fas fa-chart-bar me-2"></i>Reviews
                </a>
            </li>

            <!-- System Settings -->
            <li class="nav-item">
                <a class="nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), ['admin_settings.php', 'admin_email_templates.php']) ? 'active' : ''; ?>" href="admin_settings.php">
                    <i class="fas fa-cog me-2"></i>Settings
                </a>
            </li>

            <li class="nav-item mt-3">
                <a class="nav-link text-danger" href="logout.php">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </a>
            </li>
        </ul>

        <!-- Quick Stats -->
        <div class="mt-4 p-3 bg-dark rounded">
            <h6 class="sidebar-heading d-flex justify-content-between align-items-center">
                <span>Quick Stats</span>
                <i class="fas fa-sync-alt small cursor-pointer" onclick="location.reload()" title="Refresh"></i>
            </h6>
            <div class="small">
                <?php
                $users_count = $pdo->query("SELECT COUNT(*) FROM users WHERE user_type = 'user'")->fetchColumn();
                $documents_count = $pdo->query("SELECT COUNT(*) FROM documents")->fetchColumn();
                $pending_documents = $pdo->query("SELECT COUNT(*) FROM documents WHERE status = 'pending'")->fetchColumn();
                ?>
                <div class="d-flex justify-content-between mb-1">
                    <span>Users:</span>
                    <strong><?php echo $users_count; ?></strong>
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <span>Documents:</span>
                    <strong><?php echo $documents_count; ?></strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Pending:</span>
                    <strong class="text-warning"><?php echo $pending_documents; ?></strong>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .cursor-pointer {
        cursor: pointer;
    }

    .sidebar-heading {
        font-size: 0.875rem;
        font-weight: 600;
        text-transform: uppercase;
        color: #6c757d;
    }
</style>