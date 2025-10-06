<?php
require_once 'config/database.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

$user_id = $_GET['id'];
$stmt = $pdo->prepare("SELECT u.*, 
                      (SELECT COUNT(*) FROM documents WHERE user_id = u.id) as project_count,
                      (SELECT COALESCE(SUM(amount), 0) FROM payments WHERE user_id = u.id AND status = 'completed') as total_spent
                      FROM users u WHERE u.id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if ($user) {
    $html = '
    <div class="row">
        <div class="col-md-4 text-center">
            <img src="' . (!empty($user['profile_image']) ? 'uploads/profiles/' . htmlspecialchars($user['profile_image']) : 'https://via.placeholder.com/150/3498db/ffffff?text=' . urlencode(substr($user['full_name'] ?: $user['username'], 0, 1))) . '" 
                 alt="User" class="rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">
        </div>
        <div class="col-md-8">
            <h4>' . htmlspecialchars($user['full_name'] ?: $user['username']) . '</h4>
            <p class="text-muted">@' . htmlspecialchars($user['username']) . '</p>
            
            <div class="row mb-3">
                <div class="col-6">
                    <strong>Email:</strong><br>
                    ' . htmlspecialchars($user['email']) . '
                </div>
                <div class="col-6">
                    <strong>Phone:</strong><br>
                    ' . ($user['phone'] ? htmlspecialchars($user['phone']) : 'Not provided') . '
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-6">
                    <strong>User Type:</strong><br>
                    <span class="badge bg-' . ($user['user_type'] == 'admin' ? 'warning' : 'primary') . '">' . ucfirst($user['user_type']) . '</span>
                </div>
                <div class="col-6">
                    <strong>Status:</strong><br>
                    <span class="badge bg-success">Active</span>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-6">
                    <strong>Projects:</strong><br>
                    ' . $user['project_count'] . '
                </div>
                <div class="col-6">
                    <strong>Total Spent:</strong><br>
                    $' . number_format($user['total_spent'], 2) . '
                </div>
            </div>
            
            <div class="mb-3">
                <strong>Registered:</strong><br>
                ' . date('F j, Y g:i A', strtotime($user['created_at'])) . '
            </div>
            
            ' . ($user['bio'] ? '<div class="mb-3"><strong>Bio:</strong><br>' . htmlspecialchars($user['bio']) . '</div>' : '') . '
        </div>
    </div>';

    echo json_encode(['success' => true, 'html' => $html]);
} else {
    echo json_encode(['success' => false, 'error' => 'User not found']);
}
