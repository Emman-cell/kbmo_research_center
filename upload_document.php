<?php
require_once 'config/database.php';
$pageTitle = "Upload Document - KBMO Center";
include 'includes/header.php';

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

// Get services for dropdown
$services_stmt = $pdo->query("SELECT * FROM services");
$services = $services_stmt->fetchAll();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $service_id = $_POST['service_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];

    // Validate inputs
    if (empty($service_id) || empty($title) || empty($description)) {
        $error = "Please fill in all required fields.";
    } else {
        // File upload handling
        $upload_dir = 'uploads/';

        // Create uploads directory if it doesn't exist
        if (!is_dir($upload_dir)) {
            if (!mkdir($upload_dir, 0755, true)) {
                $error = "Failed to create upload directory.";
            }
        }

        $file_name = '';
        $content = '';
        $word_count = 0;
        $file_size = 0;

        if (isset($_FILES['document_file']) && $_FILES['document_file']['error'] == UPLOAD_ERR_OK) {
            // Validate file
            $allowed_types = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'text/plain', 'application/rtf'];
            $max_size = 5 * 1024 * 1024; // 5MB

            $file_type = $_FILES['document_file']['type'];
            $file_size = $_FILES['document_file']['size'];

            if (!in_array($file_type, $allowed_types)) {
                $error = "Invalid file type. Please upload PDF, DOC, DOCX, TXT, or RTF files only.";
            } elseif ($file_size > $max_size) {
                $error = "File too large. Maximum size is 5MB.";
            } else {
                $file_extension = strtolower(pathinfo($_FILES['document_file']['name'], PATHINFO_EXTENSION));
                $file_name = 'doc_' . time() . '_' . uniqid() . '.' . $file_extension;
                $file_path = $upload_dir . $file_name;

                // Move uploaded file
                if (!move_uploaded_file($_FILES['document_file']['tmp_name'], $file_path)) {
                    $error = "Failed to upload file. Please try again.";
                } else {
                    // Extract text content based on file type
                    switch ($file_extension) {
                        case 'txt':
                            $content = file_get_contents($file_path);
                            break;
                        case 'pdf':
                            $content = "[PDF Document - Content extraction requires PDF parser library]";
                            break;
                        case 'doc':
                        case 'docx':
                            $content = "[Word Document - Content extraction requires PHPWord library]";
                            break;
                        default:
                            $content = "[Document content]";
                    }
                    // Calculate word count
                    if (!empty($content)) {
                        $word_count = str_word_count(strip_tags($content));
                    }
                }
            }
        } elseif (isset($_FILES['document_file']) && $_FILES['document_file']['error'] != UPLOAD_ERR_NO_FILE) {
            $error = "File upload error: " . $_FILES['document_file']['error'];
        }

        if (empty($error)) {
            try {
                // Insert document into database
                $stmt = $pdo->prepare("INSERT INTO documents (user_id, service_id, title, description, file_path, content, word_count, file_size) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                if ($stmt->execute([$_SESSION['user_id'], $service_id, $title, $description, $file_name, $content, $word_count, $file_size])) {
                    $document_id = $pdo->lastInsertId();
                    $_SESSION['success'] = "Document uploaded successfully!";
                    // Redirect to payment page BEFORE any output
                    header("Location: payment.php?document_id={$document_id}");
                    exit();
                } else {
                    $error = "Failed to save document information.";
                }
            } catch (PDOException $e) {
                $error = "Database error: " . $e->getMessage();
            }
        }
    }
}

// Show success message if redirected from upload
if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}
?>
<section class="hero-section" style="background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('images/backg.jpg') center/cover;">
    <div class="container">
        <h1 class="display-4 fw-bold mb-4">Upload Your Doccument KBMO Center</h1>
        <p class="lead mb-4">Excellence in Translational Research Support</p>
    </div>
</section>
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card dashboard-card">
                <div class="card-header bg-primary text-white">
                    <h4 class="card-title mb-0"><i class="fas fa-upload me-2"></i>Upload Document</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                            <p class="mb-0">Redirecting to payment page...</p>
                        </div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data" id="uploadForm">
                        <div class="mb-3">
                            <label for="service_id" class="form-label">Select Service <span class="text-danger">*</span></label>
                            <select class="form-select" id="service_id" name="service_id" required>
                                <option value="">Choose a service...</option>
                                <?php foreach ($services as $service): ?>
                                    <option value="<?php echo $service['id']; ?>"
                                        data-price="<?php echo $service['price']; ?>"
                                        <?php echo isset($_POST['service_id']) && $_POST['service_id'] == $service['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($service['name']); ?> - $<?php echo number_format($service['price'], 2); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="title" class="form-label">Document Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title"
                                value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>"
                                placeholder="Enter document title" required>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description/Requirements <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="description" name="description" rows="4"
                                placeholder="Describe your requirements, deadlines, and any specific instructions..." required><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="document_file" class="form-label">Upload Document (Optional)</label>
                            <input type="file" class="form-control" id="document_file" name="document_file"
                                accept=".pdf,.doc,.docx,.txt,.rtf">
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Supported formats: PDF, DOC, DOCX, TXT, RTF (Max: 5MB)
                            </div>
                        </div>

                        <!-- Service Information Display -->
                        <div class="mb-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title"><i class="fas fa-info-circle me-2"></i>Service Information</h6>
                                    <p id="service-info" class="card-text text-muted">Please select a service to see details</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <strong>Price: $<span id="service-price">0.00</span></strong>
                                        <small class="text-muted" id="service-duration">Select service for timeline</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-upload me-2"></i>Upload & Proceed to Payment
                            </button>
                            <a href="services.php" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back to Services
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const serviceSelect = document.getElementById('service_id');
        const serviceInfo = document.getElementById('service-info');
        const servicePrice = document.getElementById('service-price');
        const serviceDuration = document.getElementById('service-duration');

        // Service details
        const serviceDetails = <?php
                                $details = [];
                                foreach ($services as $service) {
                                    $details[$service['id']] = [
                                        'description' => $service['description'],
                                        'price' => $service['price'],
                                        'duration' => (function ($name) {
                                            switch ($name) {
                                                case 'Concept Note Drafting':
                                                    return '3-5 days';
                                                case 'Proposal Writing':
                                                    return '7-10 days';
                                                case 'Thesis Writing (Master)':
                                                    return '4-6 weeks';
                                                case 'Thesis Writing (PhD)':
                                                    return '8-12 weeks';
                                                case 'Data Analysis':
                                                    return '5-7 days';
                                                case 'Manuscript Writing':
                                                    return '10-14 days';
                                                case 'Journal Submission':
                                                    return '2-3 days';
                                                case 'Post-Submission Review':
                                                    return '5-7 days';
                                                default:
                                                    return '5-7 days';
                                            }
                                        })($service['name'])
                                    ];
                                }
                                echo json_encode($details);
                                ?>;

        serviceSelect.addEventListener('change', function() {
            const serviceId = this.value;

            if (serviceId && serviceDetails[serviceId]) {
                const service = serviceDetails[serviceId];
                serviceInfo.textContent = service.description;
                servicePrice.textContent = parseFloat(service.price).toFixed(2);
                serviceDuration.textContent = 'Estimated: ' + service.duration;
            } else {
                serviceInfo.textContent = 'Please select a service to see details';
                servicePrice.textContent = '0.00';
                serviceDuration.textContent = 'Select service for timeline';
            }
        });

        // Trigger change event on page load if a service is already selected
        if (serviceSelect.value) {
            serviceSelect.dispatchEvent(new Event('change'));
        }

        // Form submission loading state
        const uploadForm = document.getElementById('uploadForm');
        if (uploadForm) {
            uploadForm.addEventListener('submit', function() {
                const submitBtn = this.querySelector('button[type="submit"]');
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Uploading...';
                submitBtn.disabled = true;
            });
        }
    });
</script>

<?php include 'includes/footer.php'; ?>