<?php
/**
 * Mahasiswa Enrollment Page
 * Join/enroll to course rooms
 */

require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../includes/mahasiswa_functions.php';

// Start session
startSecureSession();

// Require mahasiswa role
if ($_SESSION['role'] != 'mahasiswa') {
    header("Location: /auth/login.php");
    exit;
}

$nama = $_SESSION['nama'] ?? 'Mahasiswa';
$username = $_SESSION['username'] ?? '';
$user_id = $_SESSION['user_id'] ?? 0;
$id_mahasiswa = getMahasiswaId($user_id);

$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action == 'enroll') {
        $result = enrollMahasiswa($id_mahasiswa, intval($_POST['id_jadwal']));
        if ($result['success']) {
            $message = $result['message'];
        } else {
            $error = $result['message'];
        }
    } elseif ($action == 'unenroll') {
        $result = unenrollMahasiswa($id_mahasiswa, intval($_POST['id_jadwal']));
        if ($result['success']) {
            $message = $result['message'];
        } else {
            $error = $result['message'];
        }
    }
}

// Get all available jadwal with enrollment status
$jadwal_list = getAvailableJadwal($id_mahasiswa);

// Group by mata kuliah
$courses = [];
foreach ($jadwal_list as $jadwal) {
    $id_mk = $jadwal['id_mk'];
    if (!isset($courses[$id_mk])) {
        $courses[$id_mk] = [
            'kode_mk' => $jadwal['kode_mk'],
            'nama_mk' => $jadwal['nama_mk'],
            'sks' => $jadwal['sks'],
            'semester' => $jadwal['semester'],
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
    <title>Gabung Mata Kuliah - SIPRES Mahasiswa</title>
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <style>
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
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
        .course-info {
            display: flex;
            gap: 10px;
            margin-top: 8px;
        }
        .course-badge {
            display: inline-block;
            background: #6c757d;
            color: white;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 12px;
        }
        .course-badge.sks {
            background: #28a745;
        }
        .course-badge.semester {
            background: #17a2b8;
        }
        .schedule-item {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 10px;
            border-left: 3px solid #007bff;
            position: relative;
        }
        .schedule-item.enrolled {
            border-left-color: #28a745;
            background: #e8f5e9;
        }
        .schedule-dosen {
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        .schedule-day {
            color: #666;
            font-size: 14px;
            margin-bottom: 3px;
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
        .enrollment-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #28a745;
            color: white;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
        }
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin-top: 10px;
            width: 100%;
            text-align: center;
        }
        .btn-primary {
            background: #007bff;
            color: white;
        }
        .btn-primary:hover {
            background: #0056b3;
        }
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        .btn-danger:hover {
            background: #c82333;
        }
        .btn-success {
            background: #28a745;
            color: white;
            cursor: default;
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
                <h1>👨‍🎓 SIPRES Mahasiswa</h1>
                <p>Gabung Mata Kuliah</p>
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

        <a href="/modules/mahasiswa/dashboard.php" class="back-link">← Kembali ke Dashboard</a>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="info-box">
            <p><strong>ℹ️ Informasi:</strong> Pilih jadwal mata kuliah yang ingin Anda ikuti. Setelah bergabung, Anda dapat melakukan presensi menggunakan token yang diberikan dosen saat sesi perkuliahan berlangsung.</p>
        </div>

        <?php if (empty($courses)): ?>
            <div style="text-align: center; padding: 60px 20px; background: white; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                <h3 style="color: #666;">📚 Belum Ada Mata Kuliah</h3>
                <p style="color: #999;">Belum ada mata kuliah yang tersedia. Silakan hubungi admin.</p>
            </div>
        <?php else: ?>
            <div class="course-grid">
                <?php foreach ($courses as $course): ?>
                    <div class="course-card">
                        <div class="course-header">
                            <h3 class="course-title"><?php echo htmlspecialchars($course['nama_mk']); ?></h3>
                            <div class="course-code"><?php echo htmlspecialchars($course['kode_mk']); ?></div>
                            <div class="course-info">
                                <span class="course-badge sks"><?php echo htmlspecialchars($course['sks']); ?> SKS</span>
                                <span class="course-badge semester">Semester <?php echo htmlspecialchars($course['semester']); ?></span>
                            </div>
                        </div>
                        <div class="schedule-list">
                            <?php foreach ($course['schedules'] as $schedule): ?>
                                <div class="schedule-item <?php echo !empty($schedule['id_enrollment']) ? 'enrolled' : ''; ?>">
                                    <?php if (!empty($schedule['id_enrollment'])): ?>
                                        <span class="enrollment-badge">✓ Terdaftar</span>
                                    <?php endif; ?>
                                    <div class="schedule-dosen">
                                        👨‍🏫 <?php echo htmlspecialchars($schedule['nama_dosen']); ?>
                                    </div>
                                    <div class="schedule-day">
                                        📅 <?php echo htmlspecialchars($schedule['hari']); ?>
                                    </div>
                                    <div class="schedule-time">
                                        🕐 <?php echo htmlspecialchars(substr($schedule['jam_mulai'], 0, 5) . ' - ' . substr($schedule['jam_selesai'], 0, 5)); ?>
                                    </div>
                                    <div class="schedule-room">
                                        📍 <?php echo htmlspecialchars($schedule['ruangan']); ?>
                                    </div>
                                    <?php if (empty($schedule['id_enrollment'])): ?>
                                        <form method="POST" style="margin: 0;">
                                            <input type="hidden" name="action" value="enroll">
                                            <input type="hidden" name="id_jadwal" value="<?php echo $schedule['id_jadwal']; ?>">
                                            <button type="submit" class="btn btn-primary">Gabung Kelas Ini</button>
                                        </form>
                                    <?php else: ?>
                                        <form method="POST" style="margin: 0;" onsubmit="return confirm('Yakin ingin keluar dari kelas ini?');">
                                            <input type="hidden" name="action" value="unenroll">
                                            <input type="hidden" name="id_jadwal" value="<?php echo $schedule['id_jadwal']; ?>">
                                            <button type="submit" class="btn btn-danger">Keluar dari Kelas</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
