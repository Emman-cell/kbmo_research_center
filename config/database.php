<?php
// Database configuration
$host = 'localhost';
$dbname = 'kbmocenter';
$username = 'root';
$password = 'Em.ma.45';

// Include session configuration
require_once 'session.php';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>