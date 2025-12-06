<?php
/**
 * Main Index Page
 * Redirects to appropriate page based on login status
 */

require_once __DIR__ . '/config/session.php';

// Start session
startSecureSession();

// Check if user is logged in
if (isLoggedIn()) {
    // Redirect to appropriate dashboard
    header("Location: " . getRedirectURL($_SESSION['role']));
} else {
    // Redirect to login page
    header("Location: /auth/login.php");
}
exit;
?>
