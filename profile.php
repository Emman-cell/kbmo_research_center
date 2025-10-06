<?php
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$pageTitle = "My Profile - KBMO Center";
include 'includes/header.php';

// Get user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $bio = trim($_POST['bio']);
    $institution = trim($_POST['institution']);
    $department = trim($_POST['department']);
    $research_interests = trim($_POST['research_interests']);
    
    // Validate required fields
    if (empty($full_name) || empty($email)) {
        $error = "Full name and email are required fields.";
    } else {
        try {
            // Check if email already exists for another user
            $email_check = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $email_check->execute([$email, $_SESSION['user_id']]);
            
            if ($email_check->rowCount() > 0) {
                $error = "Email address is already registered to another user.";
            } else {
                // Handle profile image upload
                $profile_image = $user['profile_image']; // Keep existing image by default
                
                if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == UPLOAD_ERR_OK) {
                    $upload_dir = 'uploads/profiles/';
                    
                    // Create directory if it doesn't exist
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }
                    
                    // Validate image
                    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                    $max_size = 2 * 1024 * 1024; // 2MB
                    
                    $file_type = $_FILES['profile_image']['type'];
                    $file_size = $_FILES['profile_image']['size'];
                    
                    if (!in_array($file_type, $allowed_types)) {
                        $error = "Invalid file type. Please upload JPEG, PNG, or GIF images only.";
                    } elseif ($file_size > $max_size) {
                        $error = "File too large. Maximum size is 2MB.";
                    } else {
                        // Generate unique filename
                        $file_extension = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
                        $profile_image = 'profile_' . $_SESSION['user_id'] . '_' . time() . '.' . $file_extension;
                        $file_path = $upload_dir . $profile_image;
                        
                        // Delete old profile image if exists
                        if (!empty($user['profile_image']) && file_exists($upload_dir . $user['profile_image'])) {
                            unlink($upload_dir . $user['profile_image']);
                        }
                        
                        // Move uploaded file
                        if (!move_uploaded_file($_FILES['profile_image']['tmp_name'], $file_path)) {
                            $error = "Failed to upload profile image. Please try again.";
                        }
                    }
                }
                
                if (empty($error)) {
                    // Update user profile
                    $update_stmt = $pdo->prepare("UPDATE users SET 
                                                full_name = ?, 
                                                email = ?, 
                                                phone = ?, 
                                                address = ?, 
                                                profile_image = ?, 
                                                bio = ?, 
                                                institution = ?, 
                                                department = ?, 
                                                research_interests = ?, 
                                                updated_at = NOW() 
                                                WHERE id = ?");
                    
                    if ($update_stmt->execute([$full_name, $email, $phone, $address, $profile_image, $bio, $institution, $department, $research_interests, $_SESSION['user_id']])) {
                        $success = "Profile updated successfully!";
                        
                        // Update session variables
                        $_SESSION['full_name'] = $full_name;
                        $_SESSION['username'] = $user['username']; // Keep username from session
                        
                        // Refresh user data
                        $stmt->execute([$_SESSION['user_id']]);
                        $user = $stmt->fetch();
                    } else {
                        $error = "Failed to update profile. Please try again.";
                    }
                }
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}

// Get user statistics for profile
$documents_count = $pdo->prepare("SELECT COUNT(*) FROM documents WHERE user_id = ?");
$documents_count->execute([$_SESSION['user_id']]);
$total_documents = $documents_count->fetchColumn();

$completed_projects = $pdo->prepare("SELECT COUNT(*) FROM documents WHERE user_id = ? AND status = 'completed'");
$completed_projects->execute([$_SESSION['user_id']]);
$total_completed = $completed_projects->fetchColumn();

$total_spent = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE user_id = ? AND status = 'completed'");
$total_spent->execute([$_SESSION['user_id']]);
$total_amount_spent = $total_spent->fetchColumn();
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 sidebar">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
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
                        <a class="nav-link active" href="profile.php">
                            <i class="fas fa-user me-2"></i>Profile
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a>
                    </li>
                </ul>
                
                <!-- Profile Quick Stats -->
                <div class="mt-4 p-3 bg-dark rounded">
                    <h6 class="sidebar-heading d-flex justify-content-between align-items-center">
                        <span>Profile Stats</span>
                    </h6>
                    <div class="small">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Projects:</span>
                            <strong><?php echo $total_documents; ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span>Completed:</span>
                            <strong><?php echo $total_completed; ?></strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Total Spent:</span>
                            <strong>$<?php echo number_format($total_amount_spent, 2); ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <div class="col-md-9 col-lg-10 ms-sm-auto px-4 py-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">My Profile</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="dashboard.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
                    </a>
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

            <div class="row">
                <!-- Profile Overview Card -->
                <div class="col-md-4 mb-4">
                    <div class="card dashboard-card">
                        <div class="card-header bg-primary text-white text-center">
                            <h5 class="card-title mb-0">Profile Overview</h5>
                        </div>
                        <div class="card-body text-center">
                            <!-- Profile Image -->
                            <div class="position-relative d-inline-block mb-3">
                                <img src="<?php echo !empty($user['profile_image']) ? 'uploads/profiles/' . htmlspecialchars($user['profile_image']) : 'https://via.placeholder.com/150/3498db/ffffff?text=' . urlencode(substr($user['full_name'] ?: $user['username'], 0, 1)); ?>" 
                                     alt="Profile Image" 
                                     class="rounded-circle border border-4 border-primary"
                                     style="width: 150px; height: 150px; object-fit: cover;"
                                     id="profileImagePreview">
                                <div class="position-absolute bottom-0 end-0">
                                    <label for="profileImageInput" class="btn btn-primary btn-sm rounded-circle cursor-pointer" title="Change Photo">
                                        <i class="fas fa-camera"></i>
                                    </label>
                                </div>
                            </div>
                            
                            <h4 class="mb-1"><?php echo htmlspecialchars($user['full_name'] ?: $user['username']); ?></h4>
                            <p class="text-muted mb-2">@<?php echo htmlspecialchars($user['username']); ?></p>
                            
                            <?php if (!empty($user['institution'])): ?>
                                <p class="mb-1">
                                    <i class="fas fa-university me-2 text-muted"></i>
                                    <?php echo htmlspecialchars($user['institution']); ?>
                                </p>
                            <?php endif; ?>
                            
                            <?php if (!empty($user['department'])): ?>
                                <p class="mb-3">
                                    <i class="fas fa-graduation-cap me-2 text-muted"></i>
                                    <?php echo htmlspecialchars($user['department']); ?>
                                </p>
                            <?php endif; ?>
                            
                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                                    <i class="fas fa-key me-1"></i>Change Password
                                </button>
                            </div>
                        </div>
                        <div class="card-footer bg-light">
                            <small class="text-muted">
                                <i class="fas fa-calendar me-1"></i>
                                Member since <?php echo date('F Y', strtotime($user['created_at'])); ?>
                            </small>
                        </div>
                    </div>

                    <!-- Quick Stats -->
                    <div class="card dashboard-card mt-4">
                        <div class="card-header bg-light">
                            <h6 class="card-title mb-0">Account Statistics</h6>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-6 mb-3">
                                    <div class="border rounded p-3">
                                        <h4 class="text-primary mb-1"><?php echo $total_documents; ?></h4>
                                        <small class="text-muted">Projects</small>
                                    </div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="border rounded p-3">
                                        <h4 class="text-success mb-1"><?php echo $total_completed; ?></h4>
                                        <small class="text-muted">Completed</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="border rounded p-3">
                                        <h4 class="text-info mb-1">$<?php echo number_format($total_amount_spent, 2); ?></h4>
                                        <small class="text-muted">Total Spent</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="border rounded p-3">
                                        <h4 class="text-warning mb-1">
                                            <?php 
                                            $member_months = max(1, (int)((time() - strtotime($user['created_at'])) / (30 * 24 * 60 * 60)));
                                            echo $member_months; 
                                            ?>
                                        </h4>
                                        <small class="text-muted">Months</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Profile Edit Form -->
                <div class="col-md-8">
                    <div class="card dashboard-card">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-edit me-2"></i>Edit Profile Information
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" enctype="multipart/form-data" id="profileForm">
                                <!-- Hidden file input for profile image -->
                                <input type="file" id="profileImageInput" name="profile_image" accept="image/*" class="d-none" onchange="previewImage(this)">
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="full_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="full_name" name="full_name" 
                                               value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" required>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="username" class="form-label">Username</label>
                                        <input type="text" class="form-control" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                                        <div class="form-text">Username cannot be changed</div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="phone" class="form-label">Phone Number</label>
                                        <input type="tel" class="form-control" id="phone" name="phone" 
                                               value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="address" class="form-label">Address</label>
                                    <textarea class="form-control" id="address" name="address" rows="2"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="institution" class="form-label">Institution/University</label>
                                        <input type="text" class="form-control" id="institution" name="institution" 
                                               value="<?php echo htmlspecialchars($user['institution'] ?? ''); ?>">
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="department" class="form-label">Department</label>
                                        <input type="text" class="form-control" id="department" name="department" 
                                               value="<?php echo htmlspecialchars($user['department'] ?? ''); ?>">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="research_interests" class="form-label">Research Interests</label>
                                    <textarea class="form-control" id="research_interests" name="research_interests" rows="3" 
                                              placeholder="List your research interests or areas of expertise..."><?php echo htmlspecialchars($user['research_interests'] ?? ''); ?></textarea>
                                    <div class="form-text">Separate interests with commas</div>
                                </div>

                                <div class="mb-3">
                                    <label for="bio" class="form-label">Bio/About Me</label>
                                    <textarea class="form-control" id="bio" name="bio" rows="4" 
                                              placeholder="Tell us about yourself, your research background, and academic interests..."><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                                    <div class="form-text">Maximum 500 characters</div>
                                    <div class="form-text text-end">
                                        <span id="bioCharCount">0</span>/500 characters
                                    </div>
                                </div>

                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <button type="button" class="btn btn-outline-secondary" onclick="resetForm()">
                                        <i class="fas fa-undo me-1"></i>Reset
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>Update Profile
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Account Information -->
                    <div class="card dashboard-card mt-4">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-info-circle me-2"></i>Account Information
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Account Type:</strong><br>
                                        <span class="badge bg-primary"><?php echo ucfirst($user['user_type']); ?> Account</span>
                                    </p>
                                    <p><strong>Registration Date:</strong><br>
                                        <?php echo date('F j, Y', strtotime($user['created_at'])); ?>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Last Updated:</strong><br>
                                        <?php echo date('F j, Y g:i A', strtotime($user['updated_at'] ?: $user['created_at'])); ?>
                                    </p>
                                    <p><strong>Account Status:</strong><br>
                                        <span class="badge bg-success">Active</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="changePasswordModalLabel">
                    <i class="fas fa-key me-2"></i>Change Password
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="changePasswordForm">
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                    </div>
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                        <div class="form-text">Password must be at least 8 characters long</div>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="changePassword()">Change Password</button>
            </div>
        </div>
    </div>
</div>

<style>
.cursor-pointer {
    cursor: pointer;
}

.profile-image-container {
    position: relative;
    display: inline-block;
}

.profile-image-container:hover .profile-overlay {
    opacity: 1;
}

.profile-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.border-primary { border-color: #3498db !important; }
</style>

<script>
// Image preview functionality
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('profileImagePreview').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}

// Bio character counter
document.getElementById('bio').addEventListener('input', function() {
    const charCount = this.value.length;
    document.getElementById('bioCharCount').textContent = charCount;
    
    if (charCount > 500) {
        document.getElementById('bioCharCount').classList.add('text-danger');
    } else {
        document.getElementById('bioCharCount').classList.remove('text-danger');
    }
});

// Initialize character count on page load
document.addEventListener('DOMContentLoaded', function() {
    const bioField = document.getElementById('bio');
    document.getElementById('bioCharCount').textContent = bioField.value.length;
});

// Form reset function
function resetForm() {
    if (confirm('Are you sure you want to reset all changes?')) {
        document.getElementById('profileForm').reset();
        // Reset character count
        document.getElementById('bioCharCount').textContent = document.getElementById('bio').value.length;
    }
}

// Change password function
function changePassword() {
    const currentPassword = document.getElementById('current_password').value;
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (!currentPassword || !newPassword || !confirmPassword) {
        alert('Please fill in all password fields.');
        return;
    }
    
    if (newPassword.length < 8) {
        alert('New password must be at least 8 characters long.');
        return;
    }
    
    if (newPassword !== confirmPassword) {
        alert('New passwords do not match.');
        return;
    }
    
    // In a real implementation, you would send this to the server
    // For now, we'll show a success message
    alert('Password change functionality would be implemented here.\nIn production, this would validate and update the password securely.');
    
    // Close modal and reset form
    const modal = bootstrap.Modal.getInstance(document.getElementById('changePasswordModal'));
    modal.hide();
    document.getElementById('changePasswordForm').reset();
}

// Add click event to profile image to trigger file input
document.getElementById('profileImagePreview').addEventListener('click', function() {
    document.getElementById('profileImageInput').click();
});

// Form submission loading state
document.getElementById('profileForm').addEventListener('submit', function() {
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Updating...';
    submitBtn.disabled = true;
});
</script>

<?php include 'includes/footer.php'; ?>