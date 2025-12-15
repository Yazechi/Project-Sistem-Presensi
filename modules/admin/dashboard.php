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
            <a href="/modules/admin/kelola_matakuliah.php" style="text-decoration: none;">
                <div class="info-card">
                    <h3>📚 Kelola Mata Kuliah</h3>
                    <p>Tambah, edit, atau hapus mata kuliah (course room). Kelola master data mata kuliah sistem.</p>
                </div>
            </a>
            <a href="/modules/admin/kelola_jadwal.php" style="text-decoration: none;">
                <div class="info-card">
                    <h3>📅 Kelola Jadwal & Assignment</h3>
                    <p>Buat jadwal kuliah dan assign dosen ke mata kuliah. Atur waktu dan ruangan perkuliahan.</p>
                </div>
            </a>
            <a href="/modules/admin/kelola_pengguna.php" style="text-decoration: none;">
                <div class="info-card">
                    <h3>👥 Kelola Pengguna</h3>
                    <p>Tambah, edit, atau hapus data dosen dan mahasiswa. Kelola akun pengguna sistem.</p>
                </div>
            </a>
            <a href="/modules/admin/absensi.php" style="text-decoration: none;">
                <div class="info-card">
                    <h3>📊 Cek Absensi</h3>
                    <p>Monitor absensi dosen dan mahasiswa. Lihat statistik kehadiran real-time dan riwayat presensi lengkap.</p>
                </div>
            </a>
            <a href="/modules/admin/kelola_forum.php" style="text-decoration: none;">
                <div class="info-card">
                    <h3>📝 Kelola Forum Absensi</h3>
                    <p>Buat dan kelola forum absensi untuk dosen dan mahasiswa. Generate token presensi otomatis.</p>
                </div>
            </a>
            <a href="/modules/admin/log_aktivitas.php" style="text-decoration: none;">
                <div class="info-card">
                    <h3>📋 Log Aktivitas</h3>
                    <p>Lihat semua aktivitas sistem termasuk login, presensi, dan perubahan data pengguna.</p>
                </div>
            </a>
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
