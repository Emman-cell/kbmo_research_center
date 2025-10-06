<?php
require_once 'config/database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("Location: login.php");
    exit();
}

$pageTitle = "User Management - KBMO Center";
include 'includes/header.php';

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Search and filter
$search = isset($_GET['search']) ? $_GET['search'] : '';
$user_type_filter = isset($_GET['user_type']) ? $_GET['user_type'] : '';

// Build query
$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(username LIKE ? OR email LIKE ? OR full_name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($user_type_filter)) {
    $where_conditions[] = "user_type = ?";
    $params[] = $user_type_filter;
}

$where_sql = '';
if (!empty($where_conditions)) {
    $where_sql = 'WHERE ' . implode(' AND ', $where_conditions);
}

// Get total count
$count_sql = "SELECT COUNT(*) FROM users $where_sql";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_users = $count_stmt->fetchColumn();
$total_pages = ceil($total_users / $limit);

// Get users
$users_sql = "SELECT * FROM users $where_sql ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$users_stmt = $pdo->prepare($users_sql);
$users_stmt->execute($params);
$users = $users_stmt->fetchAll();

// Get user statistics
$total_admins = $pdo->query("SELECT COUNT(*) FROM users WHERE user_type = 'admin'")->fetchColumn();
$total_regular_users = $pdo->query("SELECT COUNT(*) FROM users WHERE user_type = 'user'")->fetchColumn();
$new_users_today = $pdo->query("SELECT COUNT(*) FROM users WHERE DATE(created_at) = CURDATE()")->fetchColumn();
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'includes/admin_sidebar.php'; ?>

        <!-- Main content -->
        <div class="col-md-9 col-lg-10 ms-sm-auto px-4 py-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">User Management</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="admin_user_add.php" class="btn btn-primary">
                        <i class="fas fa-user-plus me-1"></i>Add New User
                    </a>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card dashboard-card text-white bg-primary">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4><?php echo $total_users; ?></h4>
                                    <p>Total Users</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-users fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card dashboard-card text-white bg-success">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4><?php echo $total_regular_users; ?></h4>
                                    <p>Regular Users</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-user fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card dashboard-card text-white bg-warning">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4><?php echo $total_admins; ?></h4>
                                    <p>Administrators</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-user-shield fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card dashboard-card text-white bg-info">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4><?php echo $new_users_today; ?></h4>
                                    <p>New Today</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-user-clock fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search and Filter -->
            <div class="card dashboard-card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-6">
                            <input type="text" class="form-control" name="search" placeholder="Search users..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="col-md-4">
                            <select class="form-select" name="user_type">
                                <option value="">All User Types</option>
                                <option value="user" <?php echo $user_type_filter == 'user' ? 'selected' : ''; ?>>Regular Users</option>
                                <option value="admin" <?php echo $user_type_filter == 'admin' ? 'selected' : ''; ?>>Administrators</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">Search</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Users Table -->
            <div class="card dashboard-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Users List</h5>
                    <span class="text-muted"><?php echo $total_users; ?> users found</span>
                </div>
                <div class="card-body">
                    <?php if (empty($users)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <h5>No users found</h5>
                            <p class="text-muted">No users match your search criteria.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Contact</th>
                                        <th>Type</th>
                                        <th>Projects</th>
                                        <th>Status</th>
                                        <th>Registered</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user):
                                        // Get user project count
                                        $project_count = $pdo->prepare("SELECT COUNT(*) FROM documents WHERE user_id = ?");
                                        $project_count->execute([$user['id']]);
                                        $user_projects = $project_count->fetchColumn();
                                    ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="<?php echo !empty($user['profile_image']) ? 'uploads/profiles/' . htmlspecialchars($user['profile_image']) : 'https://via.placeholder.com/40/3498db/ffffff?text=' . urlencode(substr($user['full_name'] ?: $user['username'], 0, 1)); ?>"
                                                        alt="User"
                                                        class="rounded-circle me-3"
                                                        style="width: 40px; height: 40px; object-fit: cover;">
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($user['full_name'] ?: $user['username']); ?></strong>
                                                        <br>
                                                        <small class="text-muted">@<?php echo htmlspecialchars($user['username']); ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <small><?php echo htmlspecialchars($user['email']); ?></small>
                                                    <br>
                                                    <?php if (!empty($user['phone'])): ?>
                                                        <small class="text-muted"><?php echo htmlspecialchars($user['phone']); ?></small>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo $user['user_type'] == 'admin' ? 'warning' : 'primary'; ?>">
                                                    <?php echo ucfirst($user['user_type']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <strong><?php echo $user_projects; ?></strong>
                                                <br>
                                                <small class="text-muted">projects</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-success">Active</span>
                                            </td>
                                            <td>
                                                <?php echo date('M j, Y', strtotime($user['created_at'])); ?>
                                                <br>
                                                <small class="text-muted"><?php echo date('g:i A', strtotime($user['created_at'])); ?></small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="admin_user_edit.php?id=<?php echo $user['id']; ?>" class="btn btn-outline-primary" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button class="btn btn-outline-info view-user" data-user-id="<?php echo $user['id']; ?>" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                        <button class="btn btn-outline-danger delete-user" data-user-id="<?php echo $user['id']; ?>" data-user-name="<?php echo htmlspecialchars($user['full_name'] ?: $user['username']); ?>" title="Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <nav aria-label="User pagination">
                                <ul class="pagination justify-content-center">
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&user_type=<?php echo urlencode($user_type_filter); ?>">Previous</a>
                                        </li>
                                    <?php endif; ?>

                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&user_type=<?php echo urlencode($user_type_filter); ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>

                                    <?php if ($page < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&user_type=<?php echo urlencode($user_type_filter); ?>">Next</a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- User View Modal -->
<div class="modal fade" id="userViewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">User Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="userDetails">
                <!-- User details will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
    // View user details
    document.querySelectorAll('.view-user').forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.getAttribute('data-user-id');
            fetch('admin_get_user_details.php?id=' + userId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('userDetails').innerHTML = data.html;
                        new bootstrap.Modal(document.getElementById('userViewModal')).show();
                    }
                });
        });
    });

    // Delete user confirmation
    document.querySelectorAll('.delete-user').forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.getAttribute('data-user-id');
            const userName = this.getAttribute('data-user-name');

            if (confirm(`Are you sure you want to delete user "${userName}"? This action cannot be undone.`)) {
                window.location.href = 'admin_user_delete.php?id=' + userId;
            }
        });
    });
</script>

<?php include 'includes/footer.php'; ?>