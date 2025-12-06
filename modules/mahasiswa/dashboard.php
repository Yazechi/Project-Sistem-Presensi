<?php
/**
 * Mahasiswa Dashboard
 * Protected page - only accessible by users with 'mahasiswa' role
 */

require_once __DIR__ . '/../../config/session.php';

// Start session
startSecureSession();

// Require mahasiswa role
if ($_SESSION['role'] != 'mahasiswa') {
    header("Location: /auth/login.php");
    exit;
}

$nama = $_SESSION['nama'] ?? 'Mahasiswa';
$username = $_SESSION['username'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Mahasiswa - SIPRES</title>
    <link rel="stylesheet" href="/assets/css/dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <div class="header">
            <div class="header-left">
                <h1>👨‍🎓 SIPRES Mahasiswa</h1>
                <p>Sistem Informasi Presensi - Panel Mahasiswa</p>
            </div>
            <div class="header-right">
                <div class="user-info">
                    <div class="user-name"><?php echo htmlspecialchars($nama); ?></div>
                    <div class="user-role">
                        <span class="role-badge mahasiswa">Mahasiswa</span>
                    </div>
                </div>
                <a href="/auth/logout.php" class="btn-logout">Logout</a>
            </div>
        </div>
        
        <div class="welcome-card">
            <h2>Selamat Datang, <?php echo htmlspecialchars($nama); ?>!</h2>
            <p>Anda login sebagai Mahasiswa dengan NIM: <?php echo htmlspecialchars($username); ?></p>
        </div>
        
        <div class="info-grid">
            <div class="info-card">
                <h3>✅ Presensi Online</h3>
                <p>Lakukan absensi untuk mata kuliah yang Anda ikuti. Gunakan kode QR atau kode unik yang diberikan dosen.</p>
            </div>
            <div class="info-card">
                <h3>📅 Jadwal Kuliah</h3>
                <p>Lihat jadwal kuliah Anda. Cek waktu dan tempat perkuliahan secara real-time.</p>
            </div>
            <div class="info-card">
                <h3>📊 Riwayat Kehadiran</h3>
                <p>Monitor kehadiran Anda di setiap mata kuliah. Lihat persentase kehadiran per matkul.</p>
            </div>
            <div class="info-card">
                <h3>📈 Statistik Presensi</h3>
                <p>Lihat statistik kehadiran Anda. Pastikan kehadiran memenuhi syarat minimal.</p>
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
