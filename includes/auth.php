<?php
// Authentication functions

function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
}

function requireAdmin() {
    requireLogin();
    if ($_SESSION['user_type'] != 'admin') {
        header("Location: dashboard.php");
        exit();
    }
}

function isAdmin() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'admin';
}
?>