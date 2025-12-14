<?php
/**
 * Dosen Room/Jadwal Page
 * View all assigned courses and schedules
 */

require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../includes/dosen_functions.php';

// Start session
startSecureSession();

// Require dosen role
if ($_SESSION['role'] != 'dosen') {
    header("Location: /auth/login.php");
    exit;
}

$nama = $_SESSION['nama'] ?? 'Dosen';
$username = $_SESSION['username'] ?? '';
$user_id = $_SESSION['user_id'] ?? 0;
$id_dosen = getDosenId($user_id);

// Get assigned courses/schedules
$jadwal_list = getJadwalByDosen($id_dosen);

// Group by mata kuliah
$courses = [];
foreach ($jadwal_list as $jadwal) {
    $id_mk = $jadwal['id_mk'];
    if (!isset($courses[$id_mk])) {
        $courses[$id_mk] = [
            'nama_mk' => $jadwal['nama_mk'],
            'kode_mk' => $jadwal['kode_mk'],
            'sks' => $jadwal['sks'],
            'schedules' => []
        ];
    }
    $courses[$id_mk]['schedules'][] = $jadwal;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mata Kuliah Saya - SIPRES Dosen</title>
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <style>
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #007bff;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        .course-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .course-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .course-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        }
        .course-header {
            border-bottom: 2px solid #007bff;
            padding-bottom: 15px;
            margin-bottom: 15px;
        }
        .course-title {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin: 0 0 5px 0;
        }
        .course-code {
            color: #666;
            font-size: 14px;
        }
        .course-sks {
            display: inline-block;
            background: #28a745;
            color: white;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 12px;
            margin-top: 5px;
        }
        .schedule-item {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 10px;
            border-left: 3px solid #007bff;
        }
        .schedule-day {
            font-weight: bold;
            color: #007bff;
            margin-bottom: 5px;
        }
        .schedule-time {
            color: #666;
            font-size: 14px;
        }
        .schedule-room {
            color: #666;
            font-size: 14px;
            margin-top: 3px;
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .empty-state h3 {
            color: #666;
            margin-bottom: 10px;
        }
        .empty-state p {
            color: #999;
        }
        .info-box {
            background: #e7f3ff;
            border: 1px solid #b3d9ff;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .info-box p {
            margin: 5px 0;
            color: #004085;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="header">
            <div class="header-left">
                <h1>👨‍🏫 SIPRES Dosen</h1>
                <p>Mata Kuliah & Jadwal Saya</p>
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

        <a href="/modules/dosen/dashboard.php" class="back-link">← Kembali ke Dashboard</a>

        <div class="info-box">
            <p><strong>ℹ️ Informasi:</strong> Berikut adalah daftar mata kuliah yang telah diassign kepada Anda oleh admin. Setiap mata kuliah memiliki jadwal perkuliahan yang dapat Anda gunakan untuk membuka sesi absensi.</p>
        </div>

        <?php if (empty($courses)): ?>
            <div class="empty-state">
                <h3>📚 Belum Ada Mata Kuliah</h3>
                <p>Anda belum diassign ke mata kuliah manapun. Silakan hubungi admin untuk assignment mata kuliah.</p>
            </div>
        <?php else: ?>
            <div class="course-grid">
                <?php foreach ($courses as $course): ?>
                    <div class="course-card">
                        <div class="course-header">
                            <h3 class="course-title"><?php echo htmlspecialchars($course['nama_mk']); ?></h3>
                            <div class="course-code"><?php echo htmlspecialchars($course['kode_mk']); ?></div>
                            <span class="course-sks"><?php echo htmlspecialchars($course['sks']); ?> SKS</span>
                        </div>
                        <div class="schedule-list">
                            <h4 style="margin: 0 0 10px 0; font-size: 14px; color: #666;">Jadwal Perkuliahan:</h4>
                            <?php foreach ($course['schedules'] as $schedule): ?>
                                <div class="schedule-item">
                                    <div class="schedule-day">
                                        📅 <?php echo htmlspecialchars($schedule['hari']); ?>
                                    </div>
                                    <div class="schedule-time">
                                        🕐 <?php echo htmlspecialchars(substr($schedule['jam_mulai'], 0, 5) . ' - ' . substr($schedule['jam_selesai'], 0, 5)); ?>
                                    </div>
                                    <div class="schedule-room">
                                        📍 <?php echo htmlspecialchars($schedule['ruangan']); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div style="margin-top: 30px; background: white; border-radius: 8px; padding: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
            <h3>🎯 Langkah Selanjutnya</h3>
            <ul style="line-height: 1.8;">
                <li>Gunakan menu <strong>Absensi Dosen</strong> untuk membuka sesi perkuliahan dan mendapatkan token</li>
                <li>Bagikan token kepada mahasiswa untuk melakukan presensi</li>
                <li>Gunakan <strong>Kelola Mahasiswa</strong> untuk set status kehadiran manual (Izin/Sakit/Alpha)</li>
                <li>Pantau kehadiran melalui menu <strong>Log Presensi</strong></li>
            </ul>
        </div>
    </div>
</body>
</html>
