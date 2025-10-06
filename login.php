<?php
require_once 'config/database.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_type'] == 'admin') {
        header("Location: admin_dashboard.php");
    } else {
        header("Location: dashboard.php");
    }
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_type'] = $user['user_type'];
            $_SESSION['full_name'] = $user['full_name'];

            if ($user['user_type'] == 'admin') {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: dashboard.php");
            }
            exit();
        } else {
            $error = "Invalid username or password!";
        }
    }
}

$pageTitle = "Login - KBMO Center";
include 'includes/header.php';
?>

<style>
    .login-page-wrapper {
        min-height: 100vh;
        display: flex;
        flex-direction: column;
    }

    .login-main-content {
        flex: 1;
        display: flex;
        min-height: calc(100vh - 140px);
    }

    .login-form-section {
        flex: 1;
        max-width: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px 20px;
        background: white;
    }

    .login-image-section {
        flex: 1;
        max-width: 50%;
        background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('images/backg.jpg') center/cover;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
    }

    .login-image-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7));
    }

    .login-form-container {
        width: 100%;
        max-width: 400px;
        margin: 0 auto;
    }

    .login-image-content {
        position: relative;
        z-index: 2;
        color: white;
        text-align: center;
        padding: 40px;
        max-width: 500px;
    }

    .form-logo {
        text-align: center;
        margin-bottom: 40px;
    }

    .form-logo i {
        font-size: 3rem;
        color: #2c3e50;
        margin-bottom: 15px;
    }

    .form-logo h2 {
        font-size: 2rem;
        font-weight: 700;
        color: #2c3e50;
    }

    .welcome-text {
        text-align: center;
        margin-bottom: 30px;
    }

    .welcome-text h3 {
        font-size: 1.8rem;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 10px;
    }

    .welcome-text p {
        color: #6c757d;
        font-size: 1rem;
    }

    .login-image-content h2 {
        font-size: 2.2rem;
        font-weight: 700;
        margin-bottom: 20px;
        line-height: 1.3;
    }

    .login-image-content .lead {
        font-size: 1.1rem;
        margin-bottom: 25px;
        line-height: 1.5;
    }

    .feature-item {
        font-size: 1rem;
        margin-bottom: 12px;
        text-align: left;
        padding: 8px 0;
    }

    .feature-item i {
        color: #2ecc71;
        margin-right: 10px;
        font-size: 1.1rem;
    }

    .login-form-control {
        padding: 12px 15px;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        font-size: 1rem;
        transition: all 0.3s ease;
    }

    .login-form-control:focus {
        border-color: #3498db;
        box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
    }

    .login-input-group {
        display: flex;
        align-items: center;
    }

    .login-input-group-text {
        background: transparent;
        border: 2px solid #e9ecef;
        border-right: none;
        padding: 12px 15px;
        border-radius: 8px 0 0 8px;
    }

    .login-input-group .login-form-control {
        border-left: none;
        border-radius: 0 8px 8px 0;
    }

    .login-btn-primary {
        background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7));
        border: none;
        padding: 12px 20px;
        font-size: 1rem;
        font-weight: 600;
        border-radius: 8px;
        color: white;
        transition: all 0.3s ease;
        width: 100%;
    }

    .login-btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(52, 152, 219, 0.4);
    }

    .demo-accounts-box {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 20px;
        margin-top: 25px;
    }

    .demo-accounts-box h6 {
        color: #2c3e50;
        margin-bottom: 15px;
        text-align: center;
    }

    .demo-account-card {
        background: white;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 10px;
        transition: all 0.3s ease;
    }

    .demo-account-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
    }

    .login-alert {
        border-radius: 8px;
        border: none;
        padding: 12px 15px;
        margin-bottom: 20px;
    }

    @media (max-width: 768px) {
        .login-main-content {
            flex-direction: column;
        }

        .login-form-section,
        .login-image-section {
            max-width: 100%;
            min-height: auto;
        }

        .login-form-section {
            order: 2;
            padding: 30px 15px;
        }

        .login-image-section {
            order: 1;
            padding: 30px 15px;
        }

        .login-image-content {
            padding: 20px;
        }

        .login-image-content h2 {
            font-size: 1.8rem;
        }
    }
</style>

<div class="login-page-wrapper">
    <div class="login-main-content">
        <!-- Login Form Section -->
        <div class="login-form-section">
            <div class="login-form-container">
                <!-- Logo -->
                <div class="form-logo">
                    <i class="fas fa-graduation-cap"></i>
                    <h2>KBMO CENTER</h2>
                </div>

                <!-- Welcome Text -->
                <div class="welcome-text">
                    <h3>Welcome Back</h3>
                    <p>Sign in to your account to continue</p>
                </div>

                <!-- Notifications -->
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger login-alert" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success login-alert" role="alert">
                        <i class="fas fa-check-circle me-2"></i><?php echo $_SESSION['success'];
                                                                unset($_SESSION['success']); ?>
                    </div>
                <?php endif; ?>

                <!-- Login Form -->
                <form method="POST" id="loginForm">
                    <div class="mb-3">
                        <label for="username" class="form-label fw-semibold">Username or Email</label>
                        <div class="login-input-group">
                            <span class="login-input-group-text">
                                <i class="fas fa-user text-primary"></i>
                            </span>
                            <input type="text" class="form-control login-form-control" id="username" name="username"
                                value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                                placeholder="Enter your username or email" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold">Password</label>
                        <div class="login-input-group">
                            <span class="login-input-group-text">
                                <i class="fas fa-lock text-primary"></i>
                            </span>
                            <input type="password" class="form-control login-form-control" id="password" name="password"
                                placeholder="Enter your password" required>
                            <button type="button" class="login-input-group-text toggle-password-btn">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="rememberMe">
                            <label class="form-check-label text-muted" for="rememberMe">
                                Remember me
                            </label>
                        </div>
                        <a href="forgot-password.php" class="text-primary text-decoration-none">
                            Forgot password?
                        </a>
                    </div>

                    <button type="submit" class="login-btn-primary mb-3">
                        <i class="fas fa-sign-in-alt me-2"></i>Sign In
                    </button>

                    <div class="text-center">
                        <p class="text-muted mb-0">Don't have an account?
                            <a href="register.php" class="text-primary fw-semibold text-decoration-none">Create one here</a>
                        </p>
                    </div>
                </form>
            </div>
        </div>

        <!-- Image/Content Section -->
        <div class="login-image-section">
            <div class="login-image-content">
                <h2>Transform Your Research Journey</h2>
                <p class="lead">Professional research support from concept development to publication. Join thousands of researchers who trust KBMO Center for excellence.</p>

                <div class="features-list">
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Comprehensive Research Proposal Development</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Thesis and Dissertation Writing Support</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Advanced Data Analysis & Statistical Interpretation</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Journal Manuscript Preparation & Submission</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Post-Submission Review Management</span>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-top border-white border-opacity-25">
                    <div class="row text-center">
                        <div class="col-4">
                            <h4 class="fw-bold mb-1">500+</h4>
                            <small>Projects Completed</small>
                        </div>
                        <div class="col-4">
                            <h4 class="fw-bold mb-1">98%</h4>
                            <small>Success Rate</small>
                        </div>
                        <div class="col-4">
                            <h4 class="fw-bold mb-1">24/7</h4>
                            <small>Expert Support</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle password visibility
        const togglePassword = document.querySelector('.toggle-password-btn');
        const password = document.getElementById('password');

        if (togglePassword && password) {
            togglePassword.addEventListener('click', function() {
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);
                this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
            });
        }

        // Form submission loading state
        const form = document.getElementById('loginForm');
        if (form) {
            form.addEventListener('submit', function() {
                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn) {
                    const originalText = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Signing in...';
                    submitBtn.disabled = true;

                    setTimeout(() => {
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    }, 10000);
                }
            });
        }
    });
</script>

<?php include 'includes/footer.php'; ?>