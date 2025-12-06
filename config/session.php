<?php
/**
 * Session Configuration
 * Manages user sessions with security features
 */

// Session timeout: 30 minutes (1800 seconds)
define('SESSION_TIMEOUT', 1800);

/**
 * Start secure session
 */
function startSecureSession() {
    // Set secure session parameters
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
    
    session_start();
    
    // Check for session timeout
    if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > SESSION_TIMEOUT)) {
        // Session expired
        session_unset();
        session_destroy();
        header("Location: /auth/login.php?timeout=1");
        exit;
    }
    
    // Update last activity time
    $_SESSION['LAST_ACTIVITY'] = time();
}

/**
 * Check if user is logged in
 * @return bool True if logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

/**
 * Check if user has specific role
 * @param string $required_role Role to check
 * @return bool True if user has role, false otherwise
 */
function hasRole($required_role) {
    return isLoggedIn() && $_SESSION['role'] === $required_role;
}

/**
 * Require login - redirect to login page if not logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: /auth/login.php");
        exit;
    }
}

/**
 * Require specific role - redirect to login if user doesn't have role
 * @param string $required_role Role required to access page
 */
function requireRole($required_role) {
    if (!hasRole($required_role)) {
        header("Location: /auth/login.php");
        exit;
    }
}

/**
 * Destroy session and logout user
 */
function logoutUser() {
    session_unset();
    session_destroy();
}
?>
