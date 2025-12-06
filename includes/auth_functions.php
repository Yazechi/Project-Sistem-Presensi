<?php
/**
 * Authentication Functions
 * Handles user login, verification, and authentication
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

/**
 * Authenticate user with username and password
 * @param string $username User's username (NIP/NIM)
 * @param string $password User's password
 * @return array|false User data if successful, false otherwise
 */
function authenticateUser($username, $password) {
    try {
        $conn = getDBConnection();
        
        // Sanitize input to prevent SQL injection
        $username = sanitizeInput($conn, $username);
        
        // Prepare statement to prevent SQL injection
        $query = "SELECT id, username, password, nama, role, is_active FROM users WHERE username = ? AND is_active = 1";
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            $conn->close();
            return false;
        }
        
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Verify password using password_verify
            if (password_verify($password, $user['password'])) {
                // Update last login time
                $update_query = "UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = ?";
                $update_stmt = $conn->prepare($update_query);
                $update_stmt->bind_param("i", $user['id']);
                $update_stmt->execute();
                $update_stmt->close();
                
                $stmt->close();
                $conn->close();
                
                // Remove password from returned data
                unset($user['password']);
                return $user;
            }
        }
        
        $stmt->close();
        $conn->close();
        return false;
        
    } catch (Exception $e) {
        error_log("Authentication error: " . $e->getMessage());
        return false;
    }
}

/**
 * Create user session after successful login
 * @param array $user User data
 */
function createUserSession($user) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['nama'] = $user['nama'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['LAST_ACTIVITY'] = time();
}

/**
 * Get redirect URL based on user role
 * @param string $role User role
 * @return string Redirect URL
 */
function getRedirectURL($role) {
    switch ($role) {
        case 'admin':
            return '/modules/admin/dashboard.php';
        case 'dosen':
            return '/modules/dosen/dashboard.php';
        case 'mahasiswa':
            return '/modules/mahasiswa/dashboard.php';
        default:
            return '/auth/login.php';
    }
}
?>
