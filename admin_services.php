<?php
require_once 'config/database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("Location: login.php");
    exit();
}

$pageTitle = "Service Management - KBMO Center";
include 'includes/header.php';

// Handle form submissions
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_service'])) {
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $price = trim($_POST['price']);

        if (empty($name) || empty($description) || empty($price)) {
            $error = "All fields are required.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO services (name, description, price) VALUES (?, ?, ?)");
            if ($stmt->execute([$name, $description, $price])) {
                $success = "Service added successfully!";
            } else {
                $error = "Failed to add service.";
            }
        }
    } elseif (isset($_POST['update_service'])) {
        $service_id = $_POST['service_id'];
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $price = trim($_POST['price']);

        $stmt = $pdo->prepare("UPDATE services SET name = ?, description = ?, price = ? WHERE id = ?");
        if ($stmt->execute([$name, $description, $price, $service_id])) {
            $success = "Service updated successfully!";
        } else {
            $error = "Failed to update service.";
        }
    } elseif (isset($_POST['delete_service'])) {
        $service_id = $_POST['service_id'];

        // Check if service has documents
        $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM documents WHERE service_id = ?");
        $check_stmt->execute([$service_id]);
        $document_count = $check_stmt->fetchColumn();

        if ($document_count > 0) {
            $error = "Cannot delete service. There are documents associated with this service.";
        } else {
            $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
            if ($stmt->execute([$service_id])) {
                $success = "Service deleted successfully!";
            } else {
                $error = "Failed to delete service.";
            }
        }
    }
}

// Get all services
$services = $pdo->query("SELECT * FROM services ORDER BY created_at DESC")->fetchAll();

// Get service statistics
$total_services = count($services);
$popular_service = $pdo->query("SELECT s.name, COUNT(d.id) as usage_count 
                               FROM services s 
                               LEFT JOIN documents d ON s.id = d.service_id 
                               GROUP BY s.id, s.name 
                               ORDER BY usage_count DESC 
                               LIMIT 1")->fetch();
$total_revenue_from_services = $pdo->query("SELECT COALESCE(SUM(s.price), 0) 
                                           FROM documents d 
                                           JOIN services s ON d.service_id = s.id 
                                           JOIN payments p ON d.id = p.document_id 
                                           WHERE p.status = 'completed'")->fetchColumn();
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'includes/admin_sidebar.php'; ?>

        <!-- Main content -->
        <div class="col-md-9 col-lg-10 ms-sm-auto px-4 py-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Service Management</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addServiceModal">
                        <i class="fas fa-plus me-1"></i>Add New Service
                    </button>
                </div>
            </div>

            <!-- Notifications -->
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card dashboard-card text-white bg-primary">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4><?php echo $total_services; ?></h4>
                                    <p>Total Services</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-concierge-bell fa-2x"></i>
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
                                    <h4>$<?php echo number_format($total_revenue_from_services, 2); ?></h4>
                                    <p>Total Revenue</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-dollar-sign fa-2x"></i>
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
                                    <h4><?php echo $popular_service ? $popular_service['usage_count'] : 0; ?></h4>
                                    <p>Most Popular</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-chart-line fa-2x"></i>
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
                                    <h4><?php echo $popular_service ? htmlspecialchars($popular_service['name']) : 'N/A'; ?></h4>
                                    <p>Top Service</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-crown fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Services Table -->
            <div class="card dashboard-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Services List</h5>
                    <span class="text-muted"><?php echo $total_services; ?> services</span>
                </div>
                <div class="card-body">
                    <?php if (empty($services)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-concierge-bell fa-3x text-muted mb-3"></i>
                            <h5>No services found</h5>
                            <p class="text-muted">Get started by adding your first service.</p>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addServiceModal">
                                <i class="fas fa-plus me-1"></i>Add First Service
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Service Name</th>
                                        <th>Description</th>
                                        <th>Price</th>
                                        <th>Usage</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($services as $service):
                                        // Get usage count
                                        $usage_stmt = $pdo->prepare("SELECT COUNT(*) FROM documents WHERE service_id = ?");
                                        $usage_stmt->execute([$service['id']]);
                                        $usage_count = $usage_stmt->fetchColumn();
                                    ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($service['name']); ?></strong>
                                            </td>
                                            <td>
                                                <p class="mb-0 text-muted"><?php echo htmlspecialchars($service['description']); ?></p>
                                            </td>
                                            <td>
                                                <strong>$<?php echo number_format($service['price'], 2); ?></strong>
                                                <br>
                                                <small class="text-muted">UGX <?php echo number_format($service['price'] * 3800, 0); ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo $usage_count > 0 ? 'primary' : 'secondary'; ?>">
                                                    <?php echo $usage_count; ?> orders
                                                </span>
                                            </td>
                                            <td>
                                                <?php echo date('M j, Y', strtotime($service['created_at'])); ?>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-primary edit-service"
                                                        data-service-id="<?php echo $service['id']; ?>"
                                                        data-service-name="<?php echo htmlspecialchars($service['name']); ?>"
                                                        data-service-description="<?php echo htmlspecialchars($service['description']); ?>"
                                                        data-service-price="<?php echo $service['price']; ?>"
                                                        title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-outline-danger delete-service"
                                                        data-service-id="<?php echo $service['id']; ?>"
                                                        data-service-name="<?php echo htmlspecialchars($service['name']); ?>"
                                                        data-usage-count="<?php echo $usage_count; ?>"
                                                        title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Service Modal -->
<div class="modal fade" id="addServiceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Add New Service</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Service Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="price" class="form-label">Price ($ USD)</label>
                        <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" required>
                        <div class="form-text">Price in US Dollars. Will be converted to UGX for payments.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" name="add_service">Add Service</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Service Modal -->
<div class="modal fade" id="editServiceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Edit Service</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" id="edit_service_id" name="service_id">
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Service Name</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="edit_price" class="form-label">Price ($ USD)</label>
                        <input type="number" class="form-control" id="edit_price" name="price" step="0.01" min="0" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" name="update_service">Update Service</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Service Modal -->
<div class="modal fade" id="deleteServiceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Delete Service</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" id="delete_service_id" name="service_id">
                    <p>Are you sure you want to delete the service "<strong id="delete_service_name"></strong>"?</p>
                    <div class="alert alert-warning" id="delete_warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <span id="delete_warning_text"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger" name="delete_service">Delete Service</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Edit service
    document.querySelectorAll('.edit-service').forEach(button => {
        button.addEventListener('click', function() {
            const serviceId = this.getAttribute('data-service-id');
            const serviceName = this.getAttribute('data-service-name');
            const serviceDescription = this.getAttribute('data-service-description');
            const servicePrice = this.getAttribute('data-service-price');

            document.getElementById('edit_service_id').value = serviceId;
            document.getElementById('edit_name').value = serviceName;
            document.getElementById('edit_description').value = serviceDescription;
            document.getElementById('edit_price').value = servicePrice;

            new bootstrap.Modal(document.getElementById('editServiceModal')).show();
        });
    });

    // Delete service
    document.querySelectorAll('.delete-service').forEach(button => {
        button.addEventListener('click', function() {
            const serviceId = this.getAttribute('data-service-id');
            const serviceName = this.getAttribute('data-service-name');
            const usageCount = this.getAttribute('data-usage-count');

            document.getElementById('delete_service_id').value = serviceId;
            document.getElementById('delete_service_name').textContent = serviceName;

            const warningElement = document.getElementById('delete_warning');
            const warningText = document.getElementById('delete_warning_text');

            if (parseInt(usageCount) > 0) {
                warningElement.style.display = 'block';
                warningText.textContent = `This service has ${usageCount} associated document(s). Deleting it may affect existing records.`;
            } else {
                warningElement.style.display = 'none';
            }

            new bootstrap.Modal(document.getElementById('deleteServiceModal')).show();
        });
    });
</script>

<?php include 'includes/footer.php'; ?>