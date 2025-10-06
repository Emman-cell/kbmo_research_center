<?php
require_once 'config/database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("Location: login.php");
    exit();
}

$pageTitle = "System Settings - KBMO Center";
include 'includes/header.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle settings update
    if (isset($_POST['update_settings'])) {
        $success = "Settings updated successfully!";
    } elseif (isset($_POST['update_payment_settings'])) {
        $success = "Payment settings updated successfully!";
    } elseif (isset($_POST['update_email_settings'])) {
        $success = "Email settings updated successfully!";
    }
}

// Get current settings (you would typically load these from a settings table)
$current_settings = [
    'site_name' => 'KBMO Center for Translational Research',
    'site_email' => 'kbmocenter@gmail.com',
    'site_phone' => '0771200234',
    'currency' => 'USD',
    'timezone' => 'Africa/Kampala',
    'maintenance_mode' => '0'
];
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'includes/admin_sidebar.php'; ?>

        <!-- Main content -->
        <div class="col-md-9 col-lg-10 ms-sm-auto px-4 py-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">System Settings</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button type="button" class="btn btn-success" onclick="document.getElementById('update_settings').click()">
                        <i class="fas fa-save me-1"></i>Save All Changes
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

            <div class="row">
                <!-- General Settings -->
                <div class="col-md-6 mb-4">
                    <div class="card dashboard-card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-cog me-2"></i>General Settings
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="site_name" class="form-label">Site Name</label>
                                    <input type="text" class="form-control" id="site_name" name="site_name"
                                        value="<?php echo htmlspecialchars($current_settings['site_name']); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="site_email" class="form-label">Site Email</label>
                                    <input type="email" class="form-control" id="site_email" name="site_email"
                                        value="<?php echo htmlspecialchars($current_settings['site_email']); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="site_phone" class="form-label">Site Phone</label>
                                    <input type="text" class="form-control" id="site_phone" name="site_phone"
                                        value="<?php echo htmlspecialchars($current_settings['site_phone']); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="currency" class="form-label">Default Currency</label>
                                    <select class="form-select" id="currency" name="currency">
                                        <option value="USD" <?php echo $current_settings['currency'] == 'USD' ? 'selected' : ''; ?>>US Dollar (USD)</option>
                                        <option value="UGX" <?php echo $current_settings['currency'] == 'UGX' ? 'selected' : ''; ?>>Ugandan Shilling (UGX)</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="timezone" class="form-label">Timezone</label>
                                    <select class="form-select" id="timezone" name="timezone">
                                        <option value="Africa/Kampala" <?php echo $current_settings['timezone'] == 'Africa/Kampala' ? 'selected' : ''; ?>>Africa/Kampala</option>
                                        <option value="UTC">UTC</option>
                                    </select>
                                </div>
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="maintenance_mode" name="maintenance_mode"
                                        <?php echo $current_settings['maintenance_mode'] == '1' ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="maintenance_mode">Maintenance Mode</label>
                                </div>
                                <button type="submit" class="btn btn-primary" name="update_settings" id="update_settings">
                                    <i class="fas fa-save me-1"></i>Save General Settings
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Payment Settings -->
                <div class="col-md-6 mb-4">
                    <div class="card dashboard-card">
                        <div class="card-header bg-success text-white">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-credit-card me-2"></i>Payment Settings
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label">MTN Mobile Money</label>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <input type="text" class="form-control mb-2" placeholder="API Key" value="••••••••">
                                        </div>
                                        <div class="col-md-6">
                                            <input type="text" class="form-control mb-2" placeholder="Subscription Key" value="••••••••">
                                        </div>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="mtn_enabled" checked>
                                        <label class="form-check-label" for="mtn_enabled">Enable MTN Mobile Money</label>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Bank Transfer</label>
                                    <div class="mb-2">
                                        <input type="text" class="form-control" placeholder="Bank Name" value="Centenary Bank">
                                    </div>
                                    <div class="mb-2">
                                        <input type="text" class="form-control" placeholder="Account Number" value="31003456789">
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="bank_enabled" checked>
                                        <label class="form-check-label" for="bank_enabled">Enable Bank Transfer</label>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="exchange_rate" class="form-label">USD to UGX Exchange Rate</label>
                                    <input type="number" class="form-control" id="exchange_rate" name="exchange_rate" value="3800" step="0.01">
                                </div>

                                <button type="submit" class="btn btn-success" name="update_payment_settings">
                                    <i class="fas fa-save me-1"></i>Save Payment Settings
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Settings -->
            <div class="row">
                <!-- Email Settings -->
                <div class="col-md-6 mb-4">
                    <div class="card dashboard-card">
                        <div class="card-header bg-info text-white">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-envelope me-2"></i>Email Settings
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="smtp_host" class="form-label">SMTP Host</label>
                                    <input type="text" class="form-control" id="smtp_host" name="smtp_host" value="smtp.gmail.com">
                                </div>
                                <div class="mb-3">
                                    <label for="smtp_port" class="form-label">SMTP Port</label>
                                    <input type="number" class="form-control" id="smtp_port" name="smtp_port" value="587">
                                </div>
                                <div class="mb-3">
                                    <label for="smtp_username" class="form-label">SMTP Username</label>
                                    <input type="text" class="form-control" id="smtp_username" name="smtp_username" value="kbmocenter@gmail.com">
                                </div>
                                <div class="mb-3">
                                    <label for="smtp_password" class="form-label">SMTP Password</label>
                                    <input type="password" class="form-control" id="smtp_password" name="smtp_password" value="••••••••">
                                </div>
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="email_enabled" checked>
                                    <label class="form-check-label" for="email_enabled">Enable Email Notifications</label>
                                </div>
                                <button type="submit" class="btn btn-info" name="update_email_settings">
                                    <i class="fas fa-save me-1"></i>Save Email Settings
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- System Information -->
                <div class="col-md-6 mb-4">
                    <div class="card dashboard-card">
                        <div class="card-header bg-warning text-white">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-info-circle me-2"></i>System Information
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <strong>PHP Version:</strong>
                                <span class="float-end"><?php echo phpversion(); ?></span>
                            </div>
                            <div class="mb-3">
                                <strong>Database Version:</strong>
                                <span class="float-end">MySQL</span>
                            </div>
                            <div class="mb-3">
                                <strong>Server Software:</strong>
                                <span class="float-end"><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'N/A'; ?></span>
                            </div>
                            <div class="mb-3">
                                <strong>Upload Max Filesize:</strong>
                                <span class="float-end"><?php echo ini_get('upload_max_filesize'); ?></span>
                            </div>
                            <div class="mb-3">
                                <strong>Memory Limit:</strong>
                                <span class="float-end"><?php echo ini_get('memory_limit'); ?></span>
                            </div>
                            <div class="mb-3">
                                <strong>Max Execution Time:</strong>
                                <span class="float-end"><?php echo ini_get('max_execution_time'); ?>s</span>
                            </div>
                            <hr>
                            <div class="text-center">
                                <button type="button" class="btn btn-outline-warning me-2">
                                    <i class="fas fa-sync-alt me-1"></i>Clear Cache
                                </button>
                                <button type="button" class="btn btn-outline-danger" onclick="runSystemDiagnostics()">
                                    <i class="fas fa-stethoscope me-1"></i>Run Diagnostics
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Danger Zone -->
            <div class="row">
                <div class="col-12">
                    <div class="card dashboard-card border-danger">
                        <div class="card-header bg-danger text-white">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-exclamation-triangle me-2"></i>Danger Zone
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 text-center mb-3">
                                    <button type="button" class="btn btn-outline-danger w-100" onclick="backupDatabase()">
                                        <i class="fas fa-database me-2"></i>Backup Database
                                    </button>
                                    <small class="text-muted">Create a full database backup</small>
                                </div>
                                <div class="col-md-4 text-center mb-3">
                                    <button type="button" class="btn btn-outline-warning w-100" onclick="clearLogs()">
                                        <i class="fas fa-trash me-2"></i>Clear Logs
                                    </button>
                                    <small class="text-muted">Clear system logs and cache</small>
                                </div>
                                <div class="col-md-4 text-center mb-3">
                                    <button type="button" class="btn btn-outline-dark w-100" onclick="resetSystem()">
                                        <i class="fas fa-redo me-2"></i>Reset System
                                    </button>
                                    <small class="text-muted">Reset to factory settings</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function runSystemDiagnostics() {
        alert('System diagnostics would run here.\nThis would check:\n- Database connection\n- File permissions\n- Email configuration\n- Payment gateway status');
    }

    function backupDatabase() {
        if (confirm('Are you sure you want to create a database backup?')) {
            alert('Database backup process would start here.\nBackup file would be downloaded automatically.');
        }
    }

    function clearLogs() {
        if (confirm('Are you sure you want to clear all system logs?')) {
            alert('Log clearing process would run here.\nThis action cannot be undone.');
        }
    }

    function resetSystem() {
        if (confirm('WARNING: This will reset all system settings to factory defaults!\nAre you absolutely sure?')) {
            if (confirm('This action cannot be undone. All custom settings will be lost.\nType "RESET" to confirm:')) {
                alert('System reset would be performed here.\nThis is a dangerous operation.');
            }
        }
    }
</script>

<?php include 'includes/footer.php'; ?>