<?php
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Redirect admin users to admin dashboard
if ($_SESSION['user_type'] == 'admin') {
    header("Location: admin_dashboard.php");
    exit();
}

$pageTitle = "User Dashboard - KBMO Center";
include 'includes/header.php';

// Get user documents
$stmt = $pdo->prepare("SELECT d.*, s.name as service_name, s.price 
                      FROM documents d 
                      JOIN services s ON d.service_id = s.id 
                      WHERE d.user_id = ? 
                      ORDER BY d.created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$documents = $stmt->fetchAll();

// Get user info
$user_stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$user_stmt->execute([$_SESSION['user_id']]);
$user = $user_stmt->fetch();

// Get payment statistics
$total_paid = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE user_id = ? AND status = 'completed'");
$total_paid->execute([$_SESSION['user_id']]);
$total_paid_amount = $total_paid->fetchColumn();
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 sidebar">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="services.php">
                            <i class="fas fa-concierge-bell me-2"></i>Services
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="upload_document.php">
                            <i class="fas fa-upload me-2"></i>Upload Document
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">
                            <i class="fas fa-user me-2"></i>Profile
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a>
                    </li>
                </ul>
                
                <!-- Quick Stats in Sidebar -->
                <div class="mt-4 p-3 bg-dark rounded">
                    <h6 class="sidebar-heading">Quick Stats</h6>
                    <div class="small">
                        <div class="d-flex justify-content-between">
                            <span>Projects:</span>
                            <strong><?php echo count($documents); ?></strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Total Paid:</span>
                            <strong>$<?php echo number_format($total_paid_amount, 2); ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <div class="col-md-9 col-lg-10 ms-sm-auto px-4 py-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Dashboard</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="upload_document.php" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>New Order
                    </a>
                </div>
            </div>

            <!-- Welcome Message -->
            <div class="alert alert-info">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="alert-heading">Welcome, <?php echo htmlspecialchars($user['full_name']); ?>!</h4>
                        <p class="mb-0">Manage your research projects and track their progress here.</p>
                    </div>
                    <div class="text-end">
                        <small>Member since: <?php echo date('F Y', strtotime($user['created_at'])); ?></small>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card dashboard-card text-white bg-primary">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4><?php echo count($documents); ?></h4>
                                    <p>Total Projects</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-file-alt fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card dashboard-card text-white bg-success">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4><?php echo count(array_filter($documents, function($doc) { return $doc['status'] == 'completed'; })); ?></h4>
                                    <p>Completed</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-check-circle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card dashboard-card text-white bg-warning">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4><?php echo count(array_filter($documents, function($doc) { return $doc['status'] == 'in_progress'; })); ?></h4>
                                    <p>In Progress</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-spinner fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card dashboard-card text-white bg-secondary">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4><?php echo count(array_filter($documents, function($doc) { return $doc['status'] == 'pending'; })); ?></h4>
                                    <p>Pending</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-clock fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card dashboard-card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-md-3 mb-3">
                                    <a href="upload_document.php" class="btn btn-outline-primary btn-lg w-100 h-100 d-flex flex-column justify-content-center">
                                        <i class="fas fa-upload fa-2x mb-2"></i>
                                        <span>Upload Document</span>
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="services.php" class="btn btn-outline-success btn-lg w-100 h-100 d-flex flex-column justify-content-center">
                                        <i class="fas fa-concierge-bell fa-2x mb-2"></i>
                                        <span>Browse Services</span>
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="profile.php" class="btn btn-outline-info btn-lg w-100 h-100 d-flex flex-column justify-content-center">
                                        <i class="fas fa-user fa-2x mb-2"></i>
                                        <span>Update Profile</span>
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="contact.php" class="btn btn-outline-warning btn-lg w-100 h-100 d-flex flex-column justify-content-center">
                                        <i class="fas fa-headset fa-2x mb-2"></i>
                                        <span>Get Support</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Projects -->
            <div class="card dashboard-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Your Projects</h5>
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="filterAll">All</button>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="filterPending">Pending</button>
                        <button type="button" class="btn btn-sm btn-outline-warning" id="filterInProgress">In Progress</button>
                        <button type="button" class="btn btn-sm btn-outline-success" id="filterCompleted">Completed</button>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($documents)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                            <h5>No projects yet</h5>
                            <p class="text-muted">Get started by uploading your first document</p>
                            <a href="upload_document.php" class="btn btn-primary btn-lg">
                                <i class="fas fa-upload me-2"></i>Upload Your First Document
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover" id="projectsTable">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Service</th>
                                        <th>Price</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($documents as $document): ?>
                                    <tr class="project-row" data-status="<?php echo $document['status']; ?>">
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-file-alt text-muted me-2"></i>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($document['title']); ?></strong>
                                                    <?php if ($document['word_count']): ?>
                                                        <br><small class="text-muted"><?php echo number_format($document['word_count']); ?> words</small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($document['service_name']); ?></td>
                                        <td>
                                            <strong>$<?php echo number_format($document['price'], 2); ?></strong>
                                            <br>
                                            <small class="text-muted">UGX <?php echo number_format($document['price'] * 3800, 0); ?></small>
                                        </td>
                                        <td>
                                            <?php
                                            $status_class = '';
                                            $status_icon = '';
                                            switch($document['status']) {
                                                case 'pending': 
                                                    $status_class = 'status-pending';
                                                    $status_icon = 'fa-clock';
                                                    break;
                                                case 'in_progress': 
                                                    $status_class = 'status-in-progress';
                                                    $status_icon = 'fa-spinner';
                                                    break;
                                                case 'completed': 
                                                    $status_class = 'status-completed';
                                                    $status_icon = 'fa-check-circle';
                                                    break;
                                                case 'rejected': 
                                                    $status_class = 'status-rejected';
                                                    $status_icon = 'fa-times-circle';
                                                    break;
                                            }
                                            ?>
                                            <span class="status-badge <?php echo $status_class; ?>">
                                                <i class="fas <?php echo $status_icon; ?> me-1"></i>
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
                                                <?php
                                                // Check if payment already exists for this document
                                                $payment_stmt = $pdo->prepare("SELECT * FROM payments WHERE document_id = ?");
                                                $payment_stmt->execute([$document['id']]);
                                                $existing_payment = $payment_stmt->fetch();
                                                
                                                if ($existing_payment):
                                                    if ($existing_payment['status'] == 'completed'):
                                                ?>
                                                        <span class="badge bg-success">
                                                            <i class="fas fa-check me-1"></i>Paid
                                                        </span>
                                                <?php
                                                    else:
                                                ?>
                                                        <span class="badge bg-warning">
                                                            <i class="fas fa-clock me-1"></i>Payment Pending
                                                        </span>
                                                <?php
                                                    endif;
                                                else:
                                                ?>
                                                    <a href="payment.php?document_id=<?php echo $document['id']; ?>" class="btn btn-success" title="Pay Now">
                                                        <i class="fas fa-dollar-sign"></i>
                                                    </a>
                                                <?php endif; ?>
                                                
                                                <button class="btn btn-primary view-document" data-document-id="<?php echo $document['id']; ?>" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                
                                                <?php if ($document['file_path']): ?>
                                                <a href="download_document.php?document_id=<?php echo $document['id']; ?>" class="btn btn-info" title="Download File">
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
                        
                        <!-- Projects Summary -->
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="d-flex flex-wrap gap-2">
                                    <span class="badge bg-primary">Total: <?php echo count($documents); ?> projects</span>
                                    <span class="badge bg-success">Completed: <?php echo count(array_filter($documents, function($doc) { return $doc['status'] == 'completed'; })); ?></span>
                                    <span class="badge bg-warning">In Progress: <?php echo count(array_filter($documents, function($doc) { return $doc['status'] == 'in_progress'; })); ?></span>
                                    <span class="badge bg-secondary">Pending: <?php echo count(array_filter($documents, function($doc) { return $doc['status'] == 'pending'; })); ?></span>
                                </div>
                            </div>
                            <div class="col-md-6 text-end">
                                <small class="text-muted">
                                    Total Project Value: $<?php echo number_format(array_sum(array_column($documents, 'price')), 2); ?>
                                </small>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Recent Activity Timeline -->
            <?php if (!empty($documents)): ?>
            <div class="card dashboard-card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Recent Activity</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <?php
                        $recent_activities = array_slice($documents, 0, 5);
                        foreach($recent_activities as $activity):
                            $activity_icon = '';
                            $activity_color = '';
                            switch($activity['status']) {
                                case 'completed': $activity_icon = 'fa-check-circle'; $activity_color = 'success'; break;
                                case 'in_progress': $activity_icon = 'fa-spinner'; $activity_color = 'warning'; break;
                                case 'pending': $activity_icon = 'fa-clock'; $activity_color = 'secondary'; break;
                            }
                        ?>
                        <div class="timeline-item d-flex">
                            <div class="timeline-marker bg-<?php echo $activity_color; ?>">
                                <i class="fas <?php echo $activity_icon; ?>"></i>
                            </div>
                            <div class="timeline-content">
                                <div class="d-flex justify-content-between">
                                    <strong><?php echo htmlspecialchars($activity['title']); ?></strong>
                                    <small class="text-muted"><?php echo date('M j, g:i A', strtotime($activity['updated_at'] ?: $activity['created_at'])); ?></small>
                                </div>
                                <p class="mb-1"><?php echo htmlspecialchars($activity['service_name']); ?> - 
                                    <span class="status-badge status-<?php echo $activity['status']; ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $activity['status'])); ?>
                                    </span>
                                </p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Document Viewer Modal -->
<div class="modal fade" id="documentViewerModal" tabindex="-1" aria-labelledby="documentViewerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="documentViewerModalLabel">
                    <i class="fas fa-file-alt me-2"></i>Document Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <!-- Document Information Sidebar -->
                    <div class="col-md-4">
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Document Information</h6>
                            </div>
                            <div class="card-body">
                                <div id="documentInfo">
                                    <div class="text-center py-4">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <p class="mt-2 text-muted">Loading document information...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-concierge-bell me-2"></i>Service Details</h6>
                            </div>
                            <div class="card-body">
                                <div id="serviceInfo">
                                    <div class="text-center py-4">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Document Content Area -->
                    <div class="col-md-8">
                        <div class="card h-100">
                            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                <h6 class="mb-0"><i class="fas fa-file-text me-2"></i>Document Content</h6>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="copyContent">
                                        <i class="fas fa-copy me-1"></i>Copy
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="printContent">
                                        <i class="fas fa-print me-1"></i>Print
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div id="documentContent" style="max-height: 500px; overflow-y: auto; line-height: 1.6;">
                                    <div class="text-center py-5">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <p class="mt-2 text-muted">Loading document content...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Close
                </button>
                <button type="button" class="btn btn-primary" id="downloadDocumentBtn">
                    <i class="fas fa-download me-2"></i>Download Document
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    margin-bottom: 20px;
    position: relative;
}

.timeline-marker {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    position: absolute;
    left: -45px;
    top: 0;
}

.timeline-content {
    flex: 1;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 5px;
    border-left: 3px solid #dee2e6;
}

.project-row {
    transition: all 0.3s ease;
}

.project-row:hover {
    background-color: #f8f9fa;
    transform: translateY(-1px);
}

.document-content {
    white-space: pre-wrap;
    font-family: 'Courier New', monospace;
    background: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    border: 1px solid #e9ecef;
}

.status-badge {
    font-size: 0.75rem;
    padding: 4px 8px;
    border-radius: 12px;
}
</style>

<script>
// Document Viewer Function
function viewDocumentDetails(documentId) {
    // Show modal immediately
    const modal = new bootstrap.Modal(document.getElementById('documentViewerModal'));
    modal.show();
    
    // Fetch document details
    fetch('get_document_details.php?document_id=' + documentId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Populate document information
                document.getElementById('documentInfo').innerHTML = `
                    <div class="mb-3">
                        <strong class="text-muted">Title:</strong>
                        <p class="mb-1">${escapeHtml(data.document.title)}</p>
                    </div>
                    <div class="mb-3">
                        <strong class="text-muted">Description:</strong>
                        <p class="mb-1">${escapeHtml(data.document.description || 'No description provided')}</p>
                    </div>
                    <div class="mb-3">
                        <strong class="text-muted">Document Details:</strong>
                        <div class="small">
                            <div class="d-flex justify-content-between">
                                <span>Word Count:</span>
                                <strong>${data.document.word_count || 'N/A'}</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>File Size:</span>
                                <strong>${formatFileSize(data.document.file_size)}</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Uploaded:</span>
                                <strong>${new Date(data.document.created_at).toLocaleDateString()}</strong>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <strong class="text-muted">Status:</strong>
                        <div>
                            <span class="status-badge ${getStatusClass(data.document.status)}">
                                <i class="fas ${getStatusIcon(data.document.status)} me-1"></i>
                                ${data.document.status.replace('_', ' ')}
                            </span>
                        </div>
                    </div>
                `;
                
                // Populate service information
                document.getElementById('serviceInfo').innerHTML = `
                    <div class="mb-3">
                        <strong class="text-muted">Service:</strong>
                        <p class="mb-1">${escapeHtml(data.document.service_name)}</p>
                    </div>
                    <div class="mb-3">
                        <strong class="text-muted">Pricing:</strong>
                        <div class="small">
                            <div class="d-flex justify-content-between">
                                <span>Service Fee:</span>
                                <strong>$${parseFloat(data.document.price).toFixed(2)}</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>UGX Equivalent:</span>
                                <strong>UGX ${(parseFloat(data.document.price) * 3800).toLocaleString()}</strong>
                            </div>
                        </div>
                    </div>
                `;
                
                // Populate document content
                let content = data.document.content || 'No content available for preview.';
                if (content.length > 10000) {
                    content = content.substring(0, 10000) + '\n\n... [Content truncated - download to view full document]';
                }
                document.getElementById('documentContent').innerHTML = `<div class="document-content">${escapeHtml(content)}</div>`;
                
                // Set up download button
                const downloadBtn = document.getElementById('downloadDocumentBtn');
                if (data.document.file_path) {
                    downloadBtn.onclick = function() {
                        window.open('download_document.php?document_id=' + documentId, '_blank');
                    };
                    downloadBtn.disabled = false;
                } else {
                    downloadBtn.disabled = true;
                    downloadBtn.innerHTML = '<i class="fas fa-download me-2"></i>No File Available';
                }
                
                // Set up copy button
                document.getElementById('copyContent').onclick = function() {
                    navigator.clipboard.writeText(content).then(function() {
                        showToast('Content copied to clipboard!', 'success');
                    });
                };
                
                // Set up print button
                document.getElementById('printContent').onclick = function() {
                    const printContent = document.getElementById('documentContent').innerHTML;
                    const printWindow = window.open('', '_blank');
                    printWindow.document.write(`
                        <html>
                            <head>
                                <title>${data.document.title}</title>
                                <style>
                                    body { font-family: Arial, sans-serif; line-height: 1.6; padding: 20px; }
                                    .document-content { white-space: pre-wrap; }
                                </style>
                            </head>
                            <body>
                                <h2>${escapeHtml(data.document.title)}</h2>
                                <div class="document-content">${printContent}</div>
                            </body>
                        </html>
                    `);
                    printWindow.document.close();
                    printWindow.print();
                };
                
            } else {
                document.getElementById('documentInfo').innerHTML = '<div class="alert alert-danger">Error loading document: ' + data.error + '</div>';
                document.getElementById('documentContent').innerHTML = '<div class="alert alert-danger">Could not load document content.</div>';
            }
        })
        .catch(error => {
            document.getElementById('documentInfo').innerHTML = '<div class="alert alert-danger">Error loading document: ' + error + '</div>';
            document.getElementById('documentContent').innerHTML = '<div class="alert alert-danger">Network error occurred.</div>';
        });
}

// Utility functions
function escapeHtml(unsafe) {
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function formatFileSize(bytes) {
    if (!bytes) return 'N/A';
    if (bytes < 1024) return bytes + ' bytes';
    else if (bytes < 1048576) return (bytes / 1024).toFixed(2) + ' KB';
    else return (bytes / 1048576).toFixed(2) + ' MB';
}

function getStatusClass(status) {
    const statusMap = {
        'pending': 'status-pending',
        'in_progress': 'status-in-progress',
        'completed': 'status-completed',
        'rejected': 'status-rejected'
    };
    return statusMap[status] || 'status-pending';
}

function getStatusIcon(status) {
    const iconMap = {
        'pending': 'fa-clock',
        'in_progress': 'fa-spinner',
        'completed': 'fa-check-circle',
        'rejected': 'fa-times-circle'
    };
    return iconMap[status] || 'fa-clock';
}

function showToast(message, type = 'info') {
    // Simple toast implementation
    const toast = document.createElement('div');
    toast.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    toast.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 3000);
}

// Event Listeners
document.addEventListener('DOMContentLoaded', function() {
    // Add click listeners to all view buttons
    document.querySelectorAll('.view-document').forEach(button => {
        button.addEventListener('click', function() {
            const documentId = this.getAttribute('data-document-id');
            viewDocumentDetails(documentId);
        });
    });
    
    // Filter functionality
    const filterButtons = {
        'filterAll': 'all',
        'filterPending': 'pending',
        'filterInProgress': 'in_progress',
        'filterCompleted': 'completed'
    };
    
    Object.keys(filterButtons).forEach(buttonId => {
        document.getElementById(buttonId).addEventListener('click', function() {
            const status = filterButtons[buttonId];
            
            // Update button states
            document.querySelectorAll('.btn-group .btn').forEach(btn => {
                btn.classList.remove('active');
            });
            this.classList.add('active');
            
            // Filter rows
            document.querySelectorAll('.project-row').forEach(row => {
                if (status === 'all' || row.getAttribute('data-status') === status) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });
    
    // Auto-refresh payment status
    setInterval(() => {
        document.querySelectorAll('[data-transaction-id]').forEach(element => {
            // This would check payment status in a real implementation
        });
    }, 30000);
});
</script>

<?php include 'includes/footer.php'; ?>