<?php
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['document_id'])) {
    die('Document ID required');
}

$document_id = intval($_GET['document_id']);
$user_id = $_SESSION['user_id'];
$is_admin = ($_SESSION['user_type'] == 'admin');

// Build query based on user type
if ($is_admin) {
    $stmt = $pdo->prepare("SELECT d.*, u.username FROM documents d JOIN users u ON d.user_id = u.id WHERE d.id = ?");
    $stmt->execute([$document_id]);
} else {
    $stmt = $pdo->prepare("SELECT * FROM documents WHERE id = ? AND user_id = ?");
    $stmt->execute([$document_id, $user_id]);
}

$document = $stmt->fetch();

if (!$document || !$document['file_path']) {
    die('Document not found or no file available');
}

$file_path = 'uploads/' . $document['file_path'];

if (!file_exists($file_path)) {
    die('File not found on server');
}

// Set headers for download
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($document['title'] . '_' . $document['file_path']) . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($file_path));
readfile($file_path);
exit;
?>