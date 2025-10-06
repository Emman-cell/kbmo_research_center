<?php
include 'config/database.php';
session_start();

// Check if user is admin
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] != 'admin') {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $document_id = $_POST['document_id'];
    $status = $_POST['status'];
    
    $stmt = $pdo->prepare("UPDATE documents SET status = ?, updated_at = NOW() WHERE id = ?");
    
    if ($stmt->execute([$status, $document_id])) {
        echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update status']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>