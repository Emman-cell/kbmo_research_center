<?php
require_once 'config/database.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit();
}

if (!isset($_GET['document_id'])) {
    echo json_encode(['success' => false, 'error' => 'Document ID required']);
    exit();
}

$document_id = intval($_GET['document_id']);
$user_id = $_SESSION['user_id'];
$is_admin = ($_SESSION['user_type'] == 'admin');

// Build query based on user type
if ($is_admin) {
    $stmt = $pdo->prepare("SELECT d.*, s.name as service_name, s.price, u.username, u.full_name, u.email 
                          FROM documents d 
                          JOIN services s ON d.service_id = s.id 
                          JOIN users u ON d.user_id = u.id 
                          WHERE d.id = ?");
    $stmt->execute([$document_id]);
} else {
    $stmt = $pdo->prepare("SELECT d.*, s.name as service_name, s.price 
                          FROM documents d 
                          JOIN services s ON d.service_id = s.id 
                          WHERE d.id = ? AND d.user_id = ?");
    $stmt->execute([$document_id, $user_id]);
}

$document = $stmt->fetch();

if ($document) {
    echo json_encode([
        'success' => true,
        'document' => $document
    ]);
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Document not found'
    ]);
}
?>