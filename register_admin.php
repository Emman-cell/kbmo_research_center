<?php
require_once 'config/database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = 'Emman';
    $email = 'adminkbmo@gmail.com';
    $password = 'Em.ma.45';
    $full_name = 'Admin Emma';
    $phone = '0770680938';
    $admin_secret = $_POST['admin_secret'];
    
    // Verify admin secret key
    if ($admin_secret !== 'KBMO_ADMIN_SECRET_2024') {
        $error = "Invalid admin secret key!";
    } else {
        // Check if admin already exists
        $check_stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $check_stmt->execute([$username, $email]);
        
        if ($check_stmt->rowCount() > 0) {
            $error = "Admin user already exists!";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, user_type, full_name, phone) VALUES (?, ?, ?, 'admin', ?, ?)");
            
            if ($stmt->execute([$username, $email, $hashed_password, $full_name, $phone])) {
                $success = "Admin user created successfully!";
            } else {
                $error = "Failed to create admin user.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Admin - KBMO Center</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h4 class="card-title mb-0">Admin Registration</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <?php echo $success; ?><br>
                                <strong>Login Credentials:</strong><br>
                                Username: Emman<br>
                                Password: Em.ma.45<br>
                                <a href="login.php" class="btn btn-primary mt-2">Go to Login</a>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <strong>Warning:</strong> This will create an admin user with predefined credentials.
                            </div>
                            
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="admin_secret" class="form-label">Admin Secret Key</label>
                                    <input type="password" class="form-control" id="admin_secret" name="admin_secret" required>
                                    <div class="form-text">Enter the admin secret key to proceed.</div>
                                </div>
                                <button type="submit" class="btn btn-danger w-100">Create Admin User</button>
                            </form>
                            
                            <div class="mt-3 p-3 bg-light rounded">
                                <h6>Admin User Details:</h6>
                                <ul class="mb-0">
                                    <li>Username: <strong>Emman</strong></li>
                                    <li>Email: <strong>adminkbmo@gmail.com</strong></li>
                                    <li>Password: <strong>Em.ma.45</strong></li>
                                    <li>Full Name: <strong>Admin Emma</strong></li>
                                    <li>Phone: <strong>0770680938</strong></li>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>