<?php
/**
 * Database Configuration
 * Connection settings for SIPRES database
 */

// Database credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'sipres');

/**
 * Get database connection
 * @return mysqli Database connection object
 * @throws Exception if connection fails
 */
function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Set charset to prevent SQL injection
    $conn->set_charset("utf8mb4");
    
    return $conn;
}

/**
 * Sanitize input to prevent SQL injection
 * @param mysqli $conn Database connection
 * @param string $data Input data to sanitize
 * @return string Sanitized data
 */
function sanitizeInput($conn, $data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = mysqli_real_escape_string($conn, $data);
    return $data;
}
?>
