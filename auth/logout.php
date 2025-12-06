<?php
/**
 * Logout Page
 * Destroys user session and redirects to login
 */

require_once __DIR__ . '/../config/session.php';

// Start session
startSecureSession();

// Logout user
logoutUser();

// Redirect to login page
header("Location: /auth/login.php?logout=1");
exit;
?>
