<?php
/**
 * Dosen Dashboard
 * Protected page - only accessible by users with 'dosen' role
 */

require_once __DIR__ . '/../../config/session.php';

// Start session
startSecureSession();

// Require dosen role
if ($_SESSION['role'] != 'dosen') {
    header("Location: /auth/login.php");
    exit;
}

$nama = $_SESSION['nama'] ?? 'Dosen';
$username = $_SESSION['username'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Dosen - SIPRES</title>
    <link rel="stylesheet" href="/assets/css/dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <div class="header">
            <div class="header-left">
                <h1>👨‍🏫 SIPRES Dosen</h1>
                <p>Sistem Informasi Presensi - Panel Dosen</p>
            </div>
            <div class="header-right">
                <div class="user-info">
                    <div class="user-name"><?php echo htmlspecialchars($nama); ?></div>
                    <div class="user-role">
                        <span class="role-badge dosen">Dosen</span>
                    </div>
                </div>
                <a href="/auth/logout.php" class="btn-logout">Logout</a>
            </div>
        </div>
        
        <div class="welcome-card">
            <h2>Selamat Datang, <?php echo htmlspecialchars($nama); ?>!</h2>
            <p>Anda login sebagai Dosen dengan NIP: <?php echo htmlspecialchars($username); ?></p>
        </div>
        
        <div class="info-grid">
            <div class="info-card">
                <h3>📋 Kelola Presensi</h3>
                <p>Buat dan kelola presensi untuk kelas yang Anda ampu. Monitor kehadiran mahasiswa secara real-time.</p>
            </div>
            <div class="info-card">
                <h3>📚 Mata Kuliah</h3>
                <p>Lihat daftar mata kuliah yang Anda ajar. Atur jadwal pertemuan dan absensi.</p>
            </div>
            <div class="info-card">
                <h3>👥 Data Mahasiswa</h3>
                <p>Akses data mahasiswa yang mengikuti mata kuliah Anda. Lihat riwayat kehadiran.</p>
            </div>
            <div class="info-card">
                <h3>📊 Laporan Kehadiran</h3>
                <p>Generate laporan kehadiran mahasiswa per mata kuliah atau per periode.</p>
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
