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
        
        // Trim whitespace from username
        $username = trim($username);
        
        // Prepare statement to prevent SQL injection
        $query = "SELECT id_user, username, password, role FROM users WHERE username = ?";
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
                // Get nama from profile table based on role
                $nama = 'User';
                if ($user['role'] === 'admin') {
                    $profile_query = "SELECT nama_lengkap FROM admin WHERE user_id = ?";
                } elseif ($user['role'] === 'dosen') {
                    $profile_query = "SELECT nama_lengkap FROM dosen WHERE user_id = ?";
                } elseif ($user['role'] === 'mhs') {
                    $profile_query = "SELECT nama_lengkap FROM mahasiswa WHERE user_id = ?";
                }
                
                if (isset($profile_query)) {
                    $profile_stmt = $conn->prepare($profile_query);
                    $profile_stmt->bind_param("i", $user['id_user']);
                    $profile_stmt->execute();
                    $profile_result = $profile_stmt->get_result();
                    if ($profile_result->num_rows === 1) {
                        $profile = $profile_result->fetch_assoc();
                        $nama = $profile['nama_lengkap'];
                    }
                    $profile_stmt->close();
                }
                
                $stmt->close();
                $conn->close();
                
                // Normalize role name using ROLE_MAPPING constant
                $normalized_role = ROLE_MAPPING[$user['role']] ?? $user['role'];
                
                // Remove password from returned data
                unset($user['password']);
                $user['id'] = $user['id_user'];
                $user['nama'] = $nama;
                $user['role'] = $normalized_role;
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
