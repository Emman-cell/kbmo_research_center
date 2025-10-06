<?php
require_once 'config/database.php';

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
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);

    // Validate passwords match
    if ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long!";
    } else {
        // Check if username or email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);

        if ($stmt->rowCount() > 0) {
            $error = "Username or email already exists!";
        } else {
            // Insert new user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, phone) VALUES (?, ?, ?, ?, ?)");

            if ($stmt->execute([$username, $email, $hashed_password, $full_name, $phone])) {
                $_SESSION['success'] = "Registration successful! Please login.";
                header("Location: login.php");
                exit();
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}

$pageTitle = "Register - KBMO Center";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
            overflow-x: hidden;
        }

        .register-container {
            min-height: 100vh;
            display: flex;
        }

        .form-section {
            flex: 0 0 50%;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            background: white;
        }

        .image-section {
            flex: 0 0 50%;
            min-height: 100vh;
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('images/backg.jpg') center/cover;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .image-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7));
        }

        .form-content {
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
        }

        .image-content {
            position: relative;
            z-index: 2;
            color: white;
            text-align: center;
            padding: 2rem;
            width: 100%;
            max-width: 600px;
        }

        .logo-container {
            text-align: center;
            margin-bottom: 3rem;
        }

        .logo-container i {
            font-size: 4rem;
            color: #2c3e50;
            margin-bottom: 1rem;
        }

        .logo-container h2 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #2c3e50;
        }

        .welcome-text h3 {
            font-size: 2rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .welcome-text p {
            color: #6c757d;
            font-size: 1.1rem;
            margin-bottom: 2rem;
        }

        .image-content h2 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            line-height: 1.2;
        }

        .image-content .lead {
            font-size: 1.3rem;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .service-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin: 2rem 0;
        }

        .service-item {
            background: rgba(255, 255, 255, 0.1);
            padding: 1rem;
            border-radius: 10px;
            text-align: center;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .service-item:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-5px);
        }

        .service-item i {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            display: block;
        }

        .stats-container {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.3);
        }

        .stats-container h3 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .form-control {
            padding: 1rem 1.5rem;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #27ae60;
            box-shadow: 0 0 0 0.2rem rgba(39, 174, 96, 0.25);
        }

        .input-group-text {
            background: transparent;
            border: 2px solid #e9ecef;
            border-right: none;
            padding: 1rem 1.5rem;
        }

        .input-group .form-control {
            border-left: none;
            padding-left: 0;
        }

        .input-group .form-control:focus {
            border-left: none;
        }

        .btn-primary {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7));
            border: none;
            padding: 1rem 2rem;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(39, 174, 96, 0.4);
        }

        .benefits-section {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7));
            border-radius: 15px;
            padding: 2rem;
            margin-top: 2rem;
            color: white;
        }

        .benefit-item {
            text-align: center;
            padding: 1rem;
        }

        .benefit-item i {
            font-size: 2rem;
            margin-bottom: 1rem;
            display: block;
        }

        .password-strength {
            margin-top: 0.5rem;
        }

        .progress {
            height: 6px;
            border-radius: 3px;
            background: #e9ecef;
        }

        .progress-bar {
            transition: width 0.3s ease;
        }

        .password-weak {
            background-color: #e74c3c;
        }

        .password-medium {
            background-color: #f39c12;
        }

        .password-strong {
            background-color: blue;
        }

        .alert {
            border-radius: 10px;
            border: none;
            padding: 1rem 1.5rem;
        }

        @media (max-width: 1200px) {
            .form-section {
                flex: 0 0 55%;
            }

            .image-section {
                flex: 0 0 45%;
            }
        }

        @media (max-width: 992px) {
            .register-container {
                flex-direction: column;
            }

            .form-section,
            .image-section {
                flex: 0 0 100%;
                min-height: 50vh;
            }

            .form-section {
                order: 2;
            }

            .image-section {
                order: 1;
            }
        }

        @media (max-width: 768px) {
            .form-content {
                max-width: 100%;
                padding: 1rem;
            }

            .image-content {
                padding: 1rem;
            }

            .image-content h2 {
                font-size: 2rem;
            }

            .logo-container h2 {
                font-size: 2rem;
            }

            .service-grid {
                grid-template-columns: 1fr;
            }
        }

        .fade-in {
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body>
    <?php include 'includes/header.php'; ?></php>
    <div class="register-container">
        <!-- Left Side - Registration Form -->
        <div class="form-section">
            <div class="form-content fade-in">
                <!-- Logo and Welcome -->
                <div class="logo-container">
                    <i class="fas fa-graduation-cap"></i>
                    <h2>KBMO CENTER</h2>
                </div>

                <div class="welcome-text text-center">
                    <h3>Join Our Community</h3>
                    <p>Create your account and start your research journey</p>
                </div>

                <!-- Notifications -->
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Registration Form -->
                <form method="POST" id="registerForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="full_name" class="form-label fw-semibold">Full Name</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-user text-success"></i>
                                </span>
                                <input type="text" class="form-control" id="full_name" name="full_name"
                                    value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>"
                                    placeholder="Your full name" required>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label fw-semibold">Phone Number</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-phone text-success"></i>
                                </span>
                                <input type="tel" class="form-control" id="phone" name="phone"
                                    value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>"
                                    placeholder="Your phone number">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="username" class="form-label fw-semibold">Username</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-at text-success"></i>
                            </span>
                            <input type="text" class="form-control" id="username" name="username"
                                value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                                placeholder="Choose a username" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-envelope text-success"></i>
                            </span>
                            <input type="email" class="form-control" id="email" name="email"
                                value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                placeholder="Your email address" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label fw-semibold">Password</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-lock text-success"></i>
                                </span>
                                <input type="password" class="form-control" id="password" name="password"
                                    placeholder="Create password" required>
                                <button type="button" class="input-group-text toggle-password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="password-strength">
                                <div class="progress">
                                    <div class="progress-bar" id="passwordStrength" style="width: 0%"></div>
                                </div>
                                <small class="text-muted" id="passwordFeedback">Password strength</small>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="confirm_password" class="form-label fw-semibold">Confirm Password</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-lock text-success"></i>
                                </span>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                                    placeholder="Confirm password" required>
                                <button type="button" class="input-group-text toggle-password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div id="passwordMatch" class="mt-2"></div>
                        </div>
                    </div>

                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" id="terms" required>
                        <label class="form-check-label text-muted" for="terms">
                            I agree to the <a href="#" class="text-success">Terms of Service</a> and <a href="#" class="text-success">Privacy Policy</a>
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100 py-3 mb-4">
                        <i class="fas fa-user-plus me-2"></i>Create Account
                    </button>

                    <div class="text-center">
                        <p class="text-muted mb-0">Already have an account?
                            <a href="login.php" class="text-success fw-semibold text-decoration-none">Sign in here</a>
                        </p>
                    </div>
                </form>

                <!-- Benefits Section -->
                <div class="benefits-section">
                    <h6 class="text-center mb-4 fw-semibold">Why Join KBMO Center?</h6>
                    <div class="row g-3">
                        <div class="col-4">
                            <div class="benefit-item">
                                <i class="fas fa-rocket"></i>
                                <small>Fast Delivery</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="benefit-item">
                                <i class="fas fa-shield-alt"></i>
                                <small>Secure</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="benefit-item">
                                <i class="fas fa-headset"></i>
                                <small>24/7 Support</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side - Background Image -->
        <div class="image-section">
            <div class="image-content fade-in">
                <h2>Start Your Research Journey Today</h2>
                <p class="lead">Join thousands of researchers and academics who trust KBMO Center for professional research support and guidance.</p>

                <div class="service-grid">
                    <div class="service-item">
                        <i class="fas fa-lightbulb"></i>
                        <span>Concept Notes</span>
                    </div>
                    <div class="service-item">
                        <i class="fas fa-file-alt"></i>
                        <span>Research Proposals</span>
                    </div>
                    <div class="service-item">
                        <i class="fas fa-graduation-cap"></i>
                        <span>Thesis Writing</span>
                    </div>
                    <div class="service-item">
                        <i class="fas fa-chart-bar"></i>
                        <span>Data Analysis</span>
                    </div>
                </div>

                <div class="stats-container">
                    <div class="row text-center">
                        <div class="col-4">
                            <h3>500+</h3>
                            <small>Projects Completed</small>
                        </div>
                        <div class="col-4">
                            <h3>98%</h3>
                            <small>Success Rate</small>
                        </div>
                        <div class="col-4">
                            <h3>24/7</h3>
                            <small>Expert Support</small>
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-top border-white border-opacity-25">
                    <p class="mb-0">
                        <i class="fas fa-star me-2"></i>
                        Trusted by researchers from top universities worldwide
                    </p>
                </div>
            </div>
        </div>
    </div>
    <?php include 'includes/footer.php'; ?></php>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle password visibility
            const toggleButtons = document.querySelectorAll('.toggle-password');
            toggleButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const input = this.closest('.input-group').querySelector('input');
                    const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                    input.setAttribute('type', type);
                    this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
                });
            });

            // Password strength indicator
            const passwordInput = document.getElementById('password');
            const strengthBar = document.getElementById('passwordStrength');
            const strengthFeedback = document.getElementById('passwordFeedback');
            const confirmPassword = document.getElementById('confirm_password');
            const passwordMatch = document.getElementById('passwordMatch');

            passwordInput.addEventListener('input', function() {
                const password = this.value;
                let strength = 0;
                let feedback = '';

                if (password.length >= 8) strength += 25;
                if (/[a-z]/.test(password)) strength += 25;
                if (/[A-Z]/.test(password)) strength += 25;
                if (/[0-9]/.test(password)) strength += 15;
                if (/[^A-Za-z0-9]/.test(password)) strength += 10;

                strengthBar.style.width = strength + '%';

                if (strength < 50) {
                    strengthBar.className = 'progress-bar password-weak';
                    feedback = 'Weak password';
                } else if (strength < 75) {
                    strengthBar.className = 'progress-bar password-medium';
                    feedback = 'Medium strength';
                } else {
                    strengthBar.className = 'progress-bar password-strong';
                    feedback = 'Strong password';
                }

                strengthFeedback.textContent = feedback;
            });

            // Password match validation
            confirmPassword.addEventListener('input', function() {
                if (this.value !== passwordInput.value) {
                    passwordMatch.innerHTML = '<small class="text-danger"><i class="fas fa-times me-1"></i>Passwords do not match</small>';
                } else {
                    passwordMatch.innerHTML = '<small class="text-success"><i class="fas fa-check me-1"></i>Passwords match</small>';
                }
            });

            // Add loading state to form submission
            const form = document.getElementById('registerForm');
            form.addEventListener('submit', function() {
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Creating account...';
                submitBtn.disabled = true;

                setTimeout(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }, 10000);
            });
        });
    </script>
</body>

</html>