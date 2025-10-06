<?php
require_once 'config/database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("Location: login.php");
    exit();
}

$pageTitle = "Document Management - KBMO Center";
include 'includes/header.php';

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Search and filter
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$service_filter = isset($_GET['service']) ? $_GET['service'] : '';

// Build query
$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(d.title LIKE ? OR d.description LIKE ? OR u.username LIKE ? OR u.full_name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($status_filter)) {
    $where_conditions[] = "d.status = ?";
    $params[] = $status_filter;
}

if (!empty($service_filter)) {
    $where_conditions[] = "d.service_id = ?";
    $params[] = $service_filter;
}

$where_sql = '';
if (!empty($where_conditions)) {
    $where_sql = 'WHERE ' . implode(' AND ', $where_conditions);
}

// Get total count
$count_sql = "SELECT COUNT(*) FROM documents d JOIN users u ON d.user_id = u.id $where_sql";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_documents = $count_stmt->fetchColumn();
$total_pages = ceil($total_documents / $limit);

// Get documents
$documents_sql = "SELECT d.*, u.username, u.full_name, u.email, s.name as service_name, s.price 
                 FROM documents d 
                 JOIN users u ON d.user_id = u.id 
                 JOIN services s ON d.service_id = s.id 
                 $where_sql 
                 ORDER BY d.created_at DESC 
                 LIMIT $limit OFFSET $offset";
$documents_stmt = $pdo->prepare($documents_sql);
$documents_stmt->execute($params);
$documents = $documents_stmt->fetchAll();

// Get services for filter
$services = $pdo->query("SELECT id, name FROM services")->fetchAll();

// Get document statistics
$pending_count = $pdo->query("SELECT COUNT(*) FROM documents WHERE status = 'pending'")->fetchColumn();
$in_progress_count = $pdo->query("SELECT COUNT(*) FROM documents WHERE status = 'in_progress'")->fetchColumn();
$completed_count = $pdo->query("SELECT COUNT(*) FROM documents WHERE status = 'completed'")->fetchColumn();
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'includes/admin_sidebar.php'; ?>

        <!-- Main content -->
        <div class="col-md-9 col-lg-10 ms-sm-auto px-4 py-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Document Management</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary">Export</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary">Print</button>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card dashboard-card text-white bg-primary">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4><?php echo $total_documents; ?></h4>
                                    <p>Total Documents</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-file-alt fa-2x"></i>
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
                                    <h4><?php echo $pending_count; ?></h4>
                                    <p>Pending</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-clock fa-2x"></i>
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
                                    <h4><?php echo $in_progress_count; ?></h4>
                                    <p>In Progress</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-spinner fa-2x"></i>
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
                                    <h4><?php echo $completed_count; ?></h4>
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

            <!-- Search and Filter -->
            <div class="card dashboard-card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <input type="text" class="form-control" name="search" placeholder="Search documents..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" name="status">
                                <option value="">All Status</option>
                                <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="in_progress" <?php echo $status_filter == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                <option value="completed" <?php echo $status_filter == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="rejected" <?php echo $status_filter == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" name="service">
                                <option value="">All Services</option>
                                <?php foreach ($services as $service): ?>
                                    <option value="<?php echo $service['id']; ?>" <?php echo $service_filter == $service['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($service['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">Filter</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Documents Table -->
            <div class="card dashboard-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Documents List</h5>
                    <span class="text-muted"><?php echo $total_documents; ?> documents found</span>
                </div>
                <div class="card-body">
                    <?php if (empty($documents)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                            <h5>No documents found</h5>
                            <p class="text-muted">No documents match your search criteria.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Document</th>
                                        <th>User</th>
                                        <th>Service</th>
                                        <th>Price</th>
                                        <th>Status</th>
                                        <th>Submitted</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($documents as $document): ?>
                                        <tr>
                                            <td>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($document['title']); ?></strong>
                                                    <?php if ($document['word_count']): ?>
                                                        <br><small class="text-muted"><?php echo number_format($document['word_count']); ?> words</small>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($document['full_name'] ?: $document['username']); ?></strong>
                                                    <br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($document['email']); ?></small>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($document['service_name']); ?></td>
                                            <td>
                                                <strong>$<?php echo number_format($document['price'], 2); ?></strong>
                                            </td>
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
                                            <td>
                                                <?php echo date('M j, Y', strtotime($document['created_at'])); ?>
                                                <br>
                                                <small class="text-muted"><?php echo date('g:i A', strtotime($document['created_at'])); ?></small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-primary view-document" data-document-id="<?php echo $document['id']; ?>" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button class="btn btn-outline-warning update-status" data-id="<?php echo $document['id']; ?>" data-status="<?php echo $document['status']; ?>" title="Update Status">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <?php if ($document['file_path']): ?>
                                                        <a href="download_document.php?document_id=<?php echo $document['id']; ?>" class="btn btn-outline-info" title="Download">
                                                            <i class="fas fa-download"></i>
                                                        </a>
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
                            <nav aria-label="Document pagination">
                                <ul class="pagination justify-content-center">
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&service=<?php echo urlencode($service_filter); ?>">Previous</a>
                                        </li>
                                    <?php endif; ?>

                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&service=<?php echo urlencode($service_filter); ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>

                                    <?php if ($page < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&service=<?php echo urlencode($service_filter); ?>">Next</a>
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

<!-- Status Update Modal -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Document Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="statusForm">
                    <input type="hidden" id="document_id" name="document_id">
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="pending">Pending</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="admin_notes" class="form-label">Admin Notes (Optional)</label>
                        <textarea class="form-control" id="admin_notes" name="admin_notes" rows="3" placeholder="Add any notes or comments..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveStatus">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Status update functionality
    document.querySelectorAll('.update-status').forEach(button => {
        button.addEventListener('click', function() {
            const documentId = this.getAttribute('data-id');
            const currentStatus = this.getAttribute('data-status');

            document.getElementById('document_id').value = documentId;
            document.getElementById('status').value = currentStatus;

            new bootstrap.Modal(document.getElementById('statusModal')).show();
        });
    });

    // Save status
    document.getElementById('saveStatus').addEventListener('click', function() {
        const formData = new FormData(document.getElementById('statusForm'));

        fetch('admin_update_status.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error updating status: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error: ' + error);
            });
    });

    // View document details
    document.querySelectorAll('.view-document').forEach(button => {
        button.addEventListener('click', function() {
            const documentId = this.getAttribute('data-document-id');
            // Use the same viewDocumentDetails function from dashboard
            if (typeof viewDocumentDetails === 'function') {
                viewDocumentDetails(documentId);
            }
        });
    });
</script>

<?php include 'includes/footer.php'; ?>