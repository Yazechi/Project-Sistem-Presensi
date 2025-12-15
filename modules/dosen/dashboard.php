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
                <h3>📚 Mata Kuliah Saya</h3>
                <p>Lihat semua mata kuliah yang telah ditugaskan kepada Anda beserta jadwal perkuliahan lengkap.</p>
                <p style="margin-top: 10px;">
                    <a href="jadwal.php" style="display: inline-block; padding: 8px 16px; background-color: #6f42c1; color: white; text-decoration: none; border-radius: 4px;">Lihat Mata Kuliah</a>
                </p>
            </div>
            <div class="info-card">
                <h3>📋 Absensi Dosen</h3>
                <p>Check-in dan check-out untuk membuka/menutup sesi perkuliahan. Dapatkan token unik untuk mahasiswa.</p>
                <p style="margin-top: 10px;">
                    <a href="absensi.php" style="display: inline-block; padding: 8px 16px; background-color: #007bff; color: white; text-decoration: none; border-radius: 4px;">Buka Halaman</a>
                </p>
            </div>
            <div class="info-card">
                <h3>👥 Kelola Mahasiswa</h3>
                <p>Set status kehadiran mahasiswa: Hadir, Izin, Sakit, atau Alpha untuk setiap sesi perkuliahan.</p>
                <p style="margin-top: 10px;">
                    <a href="kelola_mahasiswa.php" style="display: inline-block; padding: 8px 16px; background-color: #28a745; color: white; text-decoration: none; border-radius: 4px;">Buka Halaman</a>
                </p>
            </div>
            <div class="info-card">
                <h3>📊 Log Presensi</h3>
                <p>Lihat riwayat absensi dosen dan mahasiswa. Monitor statistik kehadiran per periode.</p>
                <p style="margin-top: 10px;">
                    <a href="log_presensi.php" style="display: inline-block; padding: 8px 16px; background-color: #17a2b8; color: white; text-decoration: none; border-radius: 4px;">Buka Halaman</a>
                </p>
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
