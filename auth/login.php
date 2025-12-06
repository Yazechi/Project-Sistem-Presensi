<?php
/**
 * Login Page
 * Multi-level login for Admin, Dosen, and Mahasiswa
 */

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/auth_functions.php';

// Start session
startSecureSession();

// Redirect if already logged in
if (isLoggedIn()) {
    header("Location: " . getRedirectURL($_SESSION['role']));
    exit;
}

$error_message = '';
$success_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error_message = 'Username dan password harus diisi!';
    } else {
        // Authenticate user
        $user = authenticateUser($username, $password);
        
        if ($user) {
            // Create session
            createUserSession($user);
            
            // Redirect based on role
            header("Location: " . getRedirectURL($user['role']));
            exit;
        } else {
            $error_message = 'Username atau password salah!';
        }
    }
}

// Check for timeout parameter
if (isset($_GET['timeout']) && $_GET['timeout'] == 1) {
    $error_message = 'Sesi Anda telah berakhir. Silakan login kembali.';
}

// Check for logout parameter
if (isset($_GET['logout']) && $_GET['logout'] == 1) {
    $success_message = 'Anda telah berhasil logout.';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SIPRES</title>
    <link rel="stylesheet" href="/assets/css/login.css">
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>🔐 SIPRES</h1>
            <p>Sistem Informasi Presensi</p>
        </div>
        
        <div class="login-body">
            <?php if ($error_message): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username (NIP/NIM)</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        placeholder="Masukkan username Anda"
                        required
                        autofocus
                        value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                    >
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="Masukkan password Anda"
                        required
                    >
                </div>
                
                <button type="submit" class="btn">Login</button>
            </form>
            
            <div class="demo-accounts">
                <h3>Akun Demo untuk Pengujian</h3>
                <div class="demo-account">
                    <strong>Admin:</strong> <code>admin</code> / <code>admin123</code>
                </div>
                <div class="demo-account">
                    <strong>Dosen:</strong> <code>198001012005011001</code> / <code>dosen123</code>
                </div>
                <div class="demo-account">
                    <strong>Mahasiswa:</strong> <code>210001001</code> / <code>mhs123</code>
                </div>
            </div>
        </div>
        
        <div class="login-footer">
            &copy; <?php echo date('Y'); ?> SIPRES - Sistem Presensi
        </div>
    </div>
</body>
</html>
