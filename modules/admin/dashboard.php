<?php
/**
 * Admin Dashboard
 * Protected page - only accessible by users with 'admin' role
 */

require_once __DIR__ . '/../../config/session.php';

// Start session
startSecureSession();

// Require admin role
if ($_SESSION['role'] != 'admin') {
    header("Location: /auth/login.php");
    exit;
}

$nama = $_SESSION['nama'] ?? 'Administrator';
$username = $_SESSION['username'] ?? '';
$role = $_SESSION['role'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - SIPRES</title>
    <link rel="stylesheet" href="/assets/css/dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <div class="header">
            <div class="header-left">
                <h1>🔐 SIPRES Admin</h1>
                <p>Sistem Informasi Presensi - Panel Administrator</p>
            </div>
            <div class="header-right">
                <div class="user-info">
                    <div class="user-name"><?php echo htmlspecialchars($nama); ?></div>
                    <div class="user-role">
                        <span class="role-badge admin">Admin</span>
                    </div>
                </div>
                <a href="/auth/logout.php" class="btn-logout">Logout</a>
            </div>
        </div>
        
        <div class="welcome-card">
            <h2>Selamat Datang, <?php echo htmlspecialchars($nama); ?>!</h2>
            <p>Anda login sebagai Administrator dengan username: <?php echo htmlspecialchars($username); ?></p>
        </div>
        
        <div class="info-grid">
            <div class="info-card">
                <h3>📊 Manajemen Data</h3>
                <p>Kelola data pengguna, dosen, dan mahasiswa. Atur hak akses dan permissions untuk setiap role.</p>
            </div>
            <div class="info-card">
                <h3>👥 Manajemen User</h3>
                <p>Tambah, edit, atau hapus akun pengguna. Monitor aktivitas login dan akses sistem.</p>
            </div>
            <div class="info-card">
                <h3>⚙️ Konfigurasi Sistem</h3>
                <p>Atur pengaturan sistem, backup database, dan maintenance aplikasi.</p>
            </div>
            <div class="info-card">
                <h3>📈 Laporan & Statistik</h3>
                <p>Lihat statistik presensi, generate laporan, dan analisis data kehadiran.</p>
            </div>
        </div>
        
        <div class="security-info">
            <h3>🔒 Fitur Keamanan Aktif</h3>
            <ul>
                <li><strong>Password Hashing:</strong> Menggunakan algoritma PASSWORD_BCRYPT untuk keamanan maksimal</li>
                <li><strong>SQL Injection Prevention:</strong> Menggunakan prepared statements dan sanitasi input</li>
                <li><strong>Session Timeout:</strong> Otomatis logout setelah 30 menit tidak ada aktivitas</li>
                <li><strong>Role-Based Access Control:</strong> Setiap modul dilindungi dengan pengecekan role</li>
                <li><strong>Secure Session:</strong> Session cookie dengan httponly flag untuk mencegah XSS</li>
            </ul>
            
            <div class="session-timer">
                ⏱️ Sesi Anda akan otomatis berakhir setelah 30 menit tidak ada aktivitas
            </div>
        </div>
    </div>
</body>
</html>
