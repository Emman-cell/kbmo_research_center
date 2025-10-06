<?php
// admin_reviews.php - Complete Reviews Management System
include 'config/database.php';

// Handle actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $review_id = intval($_GET['id']);
    $action = $_GET['action'];

    switch ($action) {
        case 'approve':
            $stmt = $pdo->prepare("UPDATE reviews SET status = 'approved', updated_at = NOW() WHERE id = ?");
            $stmt->execute([$review_id]);
            $message = "Review approved successfully!";
            break;

        case 'reject':
            $stmt = $pdo->prepare("UPDATE reviews SET status = 'rejected', updated_at = NOW() WHERE id = ?");
            $stmt->execute([$review_id]);
            $message = "Review rejected successfully!";
            break;

        case 'delete':
            $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ?");
            $stmt->execute([$review_id]);
            $message = "Review deleted successfully!";
            break;

        case 'pending':
            $stmt = $pdo->prepare("UPDATE reviews SET status = 'pending', updated_at = NOW() WHERE id = ?");
            $stmt->execute([$review_id]);
            $message = "Review set to pending!";
            break;
    }

    // Redirect to avoid form resubmission
    header("Location: admin_reviews.php?message=" . urlencode($message));
    exit();
}

// Handle bulk actions
if (isset($_POST['bulk_action']) && isset($_POST['selected_reviews'])) {
    $selected_reviews = $_POST['selected_reviews'];
    $placeholders = str_repeat('?,', count($selected_reviews) - 1) . '?';

    switch ($_POST['bulk_action']) {
        case 'approve_selected':
            $stmt = $pdo->prepare("UPDATE reviews SET status = 'approved', updated_at = NOW() WHERE id IN ($placeholders)");
            $stmt->execute($selected_reviews);
            $message = "Selected reviews approved successfully!";
            break;

        case 'reject_selected':
            $stmt = $pdo->prepare("UPDATE reviews SET status = 'rejected', updated_at = NOW() WHERE id IN ($placeholders)");
            $stmt->execute($selected_reviews);
            $message = "Selected reviews rejected successfully!";
            break;

        case 'delete_selected':
            $stmt = $pdo->prepare("DELETE FROM reviews WHERE id IN ($placeholders)");
            $stmt->execute($selected_reviews);
            $message = "Selected reviews deleted successfully!";
            break;
    }

    header("Location: admin_reviews.php?message=" . urlencode($message));
    exit();
}

// Filter reviews by status
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$where_clause = "";
$params = [];

if ($status_filter != 'all') {
    $where_clause = "WHERE r.status = ?";
    $params[] = $status_filter;
}

// Get all reviews with filtering
$sql = "SELECT r.*, u.username as user_username 
        FROM reviews r 
        LEFT JOIN users u ON r.user_id = u.id 
        $where_clause
        ORDER BY r.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$all_reviews = $stmt->fetchAll();

// Get statistics
$stats_stmt = $pdo->query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
        AVG(CASE WHEN status = 'approved' THEN rating ELSE NULL END) as avg_rating
    FROM reviews
");
$stats = $stats_stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Reviews - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .stat-card {
            border: none;
            border-radius: 10px;
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .review-comment {
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.075);
        }

        .action-buttons .btn {
            margin: 2px;
        }

        .bulk-actions {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .status-badge {
            font-size: 0.75em;
        }

        .filter-active {
            background-color: #0d6efd !important;
            color: white !important;
        }
    </style>
</head>

<body>
    <?php include("includes/header.php"); ?>
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">Reviews Management</h1>
                <p class="text-muted mb-0">Manage and moderate customer reviews</p>
            </div>
            <div>
                <a href="admin_dashboard.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                </a>
            </div>
        </div>

        <!-- Success Message -->
        <?php if (isset($_GET['message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_GET['message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-xl-2 col-md-4 col-6 mb-3">
                <div class="card stat-card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0"><?php echo $stats['total']; ?></h4>
                                <p class="mb-0">Total Reviews</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-comments fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-6 mb-3">
                <div class="card stat-card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0"><?php echo $stats['approved']; ?></h4>
                                <p class="mb-0">Approved</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-check-circle fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-6 mb-3">
                <div class="card stat-card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0"><?php echo $stats['pending']; ?></h4>
                                <p class="mb-0">Pending</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-clock fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-6 mb-3">
                <div class="card stat-card bg-danger text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0"><?php echo $stats['rejected']; ?></h4>
                                <p class="mb-0">Rejected</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-times-circle fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-6 mb-3">
                <div class="card stat-card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0"><?php echo number_format($stats['avg_rating'] ?? 0, 1); ?></h4>
                                <p class="mb-0">Avg Rating</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-star fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters and Bulk Actions -->
        <div class="row">
            <div class="col-12">
                <div class="bulk-actions">
                    <div class="row align-items-center">
                        <div class="col-md-6 mb-2 mb-md-0">
                            <form method="post" id="bulkForm">
                                <div class="d-flex gap-2 flex-wrap">
                                    <select name="bulk_action" class="form-select" style="width: auto;">
                                        <option value="">Bulk Actions</option>
                                        <option value="approve_selected">Approve Selected</option>
                                        <option value="reject_selected">Reject Selected</option>
                                        <option value="delete_selected">Delete Selected</option>
                                    </select>
                                    <button type="submit" class="btn btn-primary" id="applyBulkAction">Apply</button>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="selectAll">
                                        <label class="form-check-label" for="selectAll">Select All</label>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex gap-2 justify-content-md-end flex-wrap">
                                <a href="admin_reviews.php?status=all"
                                    class="btn btn-sm <?php echo $status_filter == 'all' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                                    All (<?php echo $stats['total']; ?>)
                                </a>
                                <a href="admin_reviews.php?status=approved"
                                    class="btn btn-sm <?php echo $status_filter == 'approved' ? 'btn-success' : 'btn-outline-success'; ?>">
                                    Approved (<?php echo $stats['approved']; ?>)
                                </a>
                                <a href="admin_reviews.php?status=pending"
                                    class="btn btn-sm <?php echo $status_filter == 'pending' ? 'btn-warning' : 'btn-outline-warning'; ?>">
                                    Pending (<?php echo $stats['pending']; ?>)
                                </a>
                                <a href="admin_reviews.php?status=rejected"
                                    class="btn btn-sm <?php echo $status_filter == 'rejected' ? 'btn-danger' : 'btn-outline-danger'; ?>">
                                    Rejected (<?php echo $stats['rejected']; ?>)
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reviews Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th width="30">
                                    <input type="checkbox" id="selectAllHeader">
                                </th>
                                <th>User Details</th>
                                <th>Rating</th>
                                <th>Comment</th>
                                <th>Service</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($all_reviews)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">No reviews found</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($all_reviews as $review): ?>
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="selected_reviews[]" value="<?php echo $review['id']; ?>" class="review-checkbox">
                                        </td>
                                        <td>
                                            <div>
                                                <strong><?php echo htmlspecialchars($review['user_name']); ?></strong>
                                                <?php if ($review['user_username']): ?>
                                                    <br><small class="text-muted">@<?php echo htmlspecialchars($review['user_username']); ?></small>
                                                <?php endif; ?>
                                                <?php if ($review['user_email']): ?>
                                                    <br><small class="text-muted"><?php echo htmlspecialchars($review['user_email']); ?></small>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="fas fa-star <?php echo $i <= $review['rating'] ? 'text-warning' : 'text-light'; ?>"></i>
                                                <?php endfor; ?>
                                                <small class="text-muted ms-2">(<?php echo $review['rating']; ?>/5)</small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="review-comment" title="<?php echo htmlspecialchars($review['comment']); ?>">
                                                <?php echo htmlspecialchars($review['comment']); ?>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-link p-0 mt-1"
                                                data-bs-toggle="modal"
                                                data-bs-target="#commentModal<?php echo $review['id']; ?>">
                                                Read full
                                            </button>
                                        </td>
                                        <td>
                                            <?php if ($review['service_used']): ?>
                                                <span class="badge bg-secondary"><?php echo htmlspecialchars($review['service_used']); ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">Not specified</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge status-badge bg-<?php
                                                                                echo $review['status'] == 'approved' ? 'success' : ($review['status'] == 'rejected' ? 'danger' : 'warning');
                                                                                ?>">
                                                <?php echo ucfirst($review['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?php echo date('M j, Y', strtotime($review['created_at'])); ?><br>
                                                <span class="text-muted"><?php echo date('g:i A', strtotime($review['created_at'])); ?></span>
                                            </small>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <?php if ($review['status'] == 'pending'): ?>
                                                    <a href="admin_reviews.php?action=approve&id=<?php echo $review['id']; ?>"
                                                        class="btn btn-success btn-sm" title="Approve">
                                                        <i class="fas fa-check"></i>
                                                    </a>
                                                    <a href="admin_reviews.php?action=reject&id=<?php echo $review['id']; ?>"
                                                        class="btn btn-danger btn-sm" title="Reject">
                                                        <i class="fas fa-times"></i>
                                                    </a>
                                                <?php elseif ($review['status'] == 'approved'): ?>
                                                    <a href="admin_reviews.php?action=reject&id=<?php echo $review['id']; ?>"
                                                        class="btn btn-warning btn-sm" title="Reject">
                                                        <i class="fas fa-times"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <a href="admin_reviews.php?action=approve&id=<?php echo $review['id']; ?>"
                                                        class="btn btn-success btn-sm" title="Approve">
                                                        <i class="fas fa-check"></i>
                                                    </a>
                                                <?php endif; ?>

                                                <a href="admin_reviews.php?action=delete&id=<?php echo $review['id']; ?>"
                                                    class="btn btn-outline-danger btn-sm"
                                                    title="Delete"
                                                    onclick="return confirm('Are you sure you want to delete this review?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Comment Modal -->
                                    <div class="modal fade" id="commentModal<?php echo $review['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Review Comment</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pagination (if needed in future) -->
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="text-muted">
                Showing <?php echo count($all_reviews); ?> review(s)
            </div>
            <!-- Add pagination here if you have many reviews -->
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Select All functionality
            const selectAllHeader = document.getElementById('selectAllHeader');
            const selectAll = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.review-checkbox');

            function updateSelectAll() {
                const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                const someChecked = Array.from(checkboxes).some(cb => cb.checked);

                selectAllHeader.checked = allChecked;
                selectAllHeader.indeterminate = someChecked && !allChecked;
                selectAll.checked = allChecked;
            }

            selectAllHeader.addEventListener('change', function() {
                checkboxes.forEach(cb => cb.checked = this.checked);
                updateSelectAll();
            });

            selectAll.addEventListener('change', function() {
                checkboxes.forEach(cb => cb.checked = this.checked);
                updateSelectAll();
            });

            checkboxes.forEach(cb => {
                cb.addEventListener('change', updateSelectAll);
            });

            // Bulk form validation
            document.getElementById('applyBulkAction').addEventListener('click', function(e) {
                const bulkAction = document.querySelector('select[name="bulk_action"]');
                const selectedReviews = document.querySelectorAll('.review-checkbox:checked');

                if (!bulkAction.value) {
                    e.preventDefault();
                    alert('Please select a bulk action');
                    return;
                }

                if (selectedReviews.length === 0) {
                    e.preventDefault();
                    alert('Please select at least one review');
                    return;
                }

                if (bulkAction.value.includes('delete')) {
                    if (!confirm('Are you sure you want to delete the selected reviews?')) {
                        e.preventDefault();
                    }
                }
            });

            // Auto-hide success message after 5 seconds
            const alert = document.querySelector('.alert');
            if (alert) {
                setTimeout(() => {
                    alert.classList.remove('show');
                    setTimeout(() => alert.remove(), 150);
                }, 5000);
            }
        });
    </script>
    <?php include("includes/footer.php"); ?>
</body>

</html>