<?php
include 'config/database.php';
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$userType = $isLoggedIn ? $_SESSION['user_type'] : '';
$username = $isLoggedIn ? $_SESSION['username'] : '';
$fullName = $isLoggedIn ? $_SESSION['full_name'] : '';
$userId = $isLoggedIn ? $_SESSION['user_id'] : null;

$unreadCount = 0;
if ($isLoggedIn && $userType == 'admin') {
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as unread_count 
            FROM chat_messages cm 
            JOIN chat_conversations cc ON cm.conversation_id = cc.id 
            WHERE cm.message_type = 'user' 
            AND cm.read_status = 0 
            AND cc.assigned_admin = ?
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $result = $stmt->fetch();
        $unreadCount = $result['unread_count'];
    } catch (PDOException $e) {
        // If tables don't exist yet, set unreadCount to 0
        $unreadCount = 0;
    }
}
$userMessages = [];
$unreadMessageCount = 0;

if ($isLoggedIn && $userType == 'user') {
    try {

        // Create user_messages table if not exists
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS user_messages (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT NOT NULL,
                admin_id INT,
                subject VARCHAR(255) NOT NULL,
                message TEXT NOT NULL,
                is_read BOOLEAN DEFAULT FALSE,
                message_type ENUM('announcement', 'personal', 'project_update') DEFAULT 'announcement',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE SET NULL
            )
        ");

        // Get user messages
        $stmt = $pdo->prepare("
            SELECT um.*, a.full_name as admin_name 
            FROM user_messages um 
            LEFT JOIN admins a ON um.admin_id = a.id 
            WHERE um.user_id = ? 
            ORDER BY um.created_at DESC 
            LIMIT 10
        ");
        $stmt->execute([$userId]);
        $userMessages = $stmt->fetchAll();

        // Get unread message count
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as unread_count 
            FROM user_messages 
            WHERE user_id = ? AND is_read = FALSE
        ");
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        $unreadMessageCount = $result['unread_count'];
    } catch (PDOException $e) {
        error_log("Message system error: " . $e->getMessage());
    }
}

// Get unread chat messages count for admin users
$unreadChatCount = 0;
if ($isLoggedIn && $userType == 'admin') {
    try {
        include 'config/database.php';
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as unread_count 
            FROM chat_messages cm 
            JOIN chat_conversations cc ON cm.conversation_id = cc.id 
            WHERE cm.message_type = 'user' 
            AND cm.read_status = 0 
            AND cc.assigned_admin = ?
        ");
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        $unreadChatCount = $result['unread_count'];
    } catch (PDOException $e) {
        $unreadChatCount = 0;
    }
}

// Debug logging
error_log("Header - Logged in: " . ($isLoggedIn ? 'Yes' : 'No') . ", User: " . $username);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'KBMO Center for Translational Research'; ?></title>
    <link rel="icon" href="images/favicon_io/android-chrome-192x192.png" type="image/x-icon">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>

    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --light-color: #ecf0f1;
            --dark-color: #2c3e50;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: #333;
        }

        .navbar-brand {
            font-weight: 700;
            color: var(--primary-color) !important;
        }

        .hero-section {
            background: linear-gradient(rgba(44, 62, 80, 0.8), rgba(44, 62, 80, 0.9)), #2c3e50;
            background-size: cover;
            background-position: center;
            color: white;
            padding: 100px 0;
            text-align: center;
        }

        .service-card {
            transition: transform 0.3s, box-shadow 0.3s;
            border: none;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .service-icon {
            font-size: 2.5rem;
            color: var(--secondary-color);
            margin-bottom: 15px;
        }

        .btn-primary {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        .btn-primary:hover {
            background-color: #2980b9;
            border-color: #2980b9;
        }

        .footer {
            background-color: var(--primary-color);
            color: white;
            padding: 40px 0;
        }

        .dashboard-card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            transition: all 0.3s;
        }

        .dashboard-card:hover {
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-in-progress {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }

        .status-rejected {
            background-color: #f8d7da;
            color: #721c24;
        }

        .login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            background-color: white;
        }

        .form-control:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }

        .sidebar {
            background-color: var(--primary-color);
            color: white;
            min-height: 100vh;
            padding: 0;
        }

        .sidebar .nav-link {
            color: white;
            padding: 15px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .sidebar .nav-link.active {
            background-color: var(--secondary-color);
        }
    </style>
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="images/logo.jpg" alt="KBMO Center" height="40" class="d-inline-block align-top me-2">
                KBMO CENTER FOR TRANSLATIONAL RESEARCH
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="index.php">
                            <i class="fas fa-home me-1"></i>Home
                        </a>
                    </li>
                    <?php if (!$isLoggedIn): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'services.php' ? 'active' : ''; ?>" href="services.php">
                                <i class="fas fa-concierge-bell me-1"></i>Services
                            </a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : ''; ?>" href="about.php">
                            <i class="fas fa-info-circle me-1"></i>About
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'careers.php' ? 'active' : ''; ?>" href="careers.php">
                            <i class="fas fa-briefcase me-1"></i>Career
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : ''; ?>" href="contact.php">
                            <i class="fas fa-envelope me-1"></i>Contact
                        </a>
                    </li>
                </ul>
                <?php if ($isLoggedIn): ?>
                    <!-- Upload Document -->
                    <li class="nav-item" style="list-style: none; margin: 20px; padding: 0;">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'upload_document.php' ? 'active' : ''; ?>" href="upload_document.php">
                            <i class="fas fa-cloud-upload-alt me-1"></i>Upload
                        </a>
                    </li>
                    <?php if ($userType == 'user'): ?>
                        <li class="nav-item" style="list-style: none; margin: 20px; padding: 0;">
                            <a class="nav-link position-relative <?php echo basename($_SERVER['PHP_SELF']) == 'includes/admin_chat.php' ? 'active' : ''; ?>" href="includes/admin_chat.php">
                                <i class="fas fa-comments me-1"></i>Messages
                                <?php if ($unreadCount > 0): ?>
                                    <span class="notification-badge" id="admin-notification-badge">
                                        <?php echo $unreadCount > 9 ? '9+' : $unreadCount; ?>
                                    </span>
                                <?php endif; ?>
                            </a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item dropdown" style="list-style: none; margin: 20px; padding: 0;">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i><?php echo $fullName ?: $username; ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="<?php echo $userType == 'admin' ? 'admin_dashboard.php' : 'dashboard.php'; ?>">
                                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="profile.php">
                                    <i class="fas fa-user-circle me-2"></i>My Profile
                                </a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <a class="dropdown-item text-danger" href="logout.php">
                                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php else: ?>
                    <!-- Login/Register Buttons -->
                    <li class="nav-item" style="list-style: none; margin: 20px; padding: 5px;">
                        <a class="nav-link btn btn-outline-light border-0 me-2" href="login.php">
                            <i class="fas fa-sign-in-alt me-1"></i>Login
                        </a>
                    </li>
                    <li class="nav-item" style="list-style: none; margin: 20px; padding: 5px;">
                        <a class="nav-link btn btn-outline-light border-0 me-2" href="register.php">
                            <i class="fas fa-user-plus me-1"></i>Register
                        </a>
                    </li>
                <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>