<?php
// session.php - Session configuration

// Set session configuration BEFORE session_start()
// ini_set('session.cookie_httponly', 1);
// ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
// ini_set('session.use_strict_mode', 1);

if (session_status() == PHP_SESSION_NONE) {
    session_start();

    // Regenerate session ID to prevent fixation
    if (!isset($_SESSION['created'])) {
        session_regenerate_id(true);
        $_SESSION['created'] = time();
    } else if (time() - $_SESSION['created'] > 1800) {
        // Regenerate every 30 minutes
        session_regenerate_id(true);
        $_SESSION['created'] = time();
    }
}
