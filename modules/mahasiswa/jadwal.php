<?php
/**
 * Mahasiswa Jadwal Page
 * Page for students to view class schedules, lecturers, and session information
 */

require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';

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

// Get database connection
$conn = getDBConnection();

// Get mahasiswa ID from user_id
$mahasiswa_query = "SELECT id_mahasiswa, nim, nama_lengkap, jurusan FROM mahasiswa WHERE user_id = ?";
$stmt = $conn->prepare($mahasiswa_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    $conn->close();
    header("Location: /modules/mahasiswa/dashboard.php?error=profile_not_found");
    exit;
}

$mahasiswa_data = $result->fetch_assoc();
$id_mahasiswa = $mahasiswa_data['id_mahasiswa'];
$stmt->close();

// Get all available schedules (forums)
$jadwal_query = "SELECT jk.id_jadwal, mk.kode_mk, mk.nama_mk, mk.sks, mk.semester,
                        d.nama_lengkap as nama_dosen, d.nidn, d.email as email_dosen,
                        jk.hari, jk.jam_mulai, jk.jam_selesai, jk.ruangan
                 FROM jadwal_kuliah jk
                 JOIN matakuliah mk ON jk.id_mk = mk.id_mk
                 JOIN dosen d ON jk.id_dosen = d.id_dosen
                 ORDER BY 
                     FIELD(jk.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'),
                     jk.jam_mulai";
$jadwal_result = $conn->query($jadwal_query);

// Get open sessions (active forums)
$open_sessions_query = "SELECT jp.id_jurnal, jp.tanggal, jp.jam_masuk, jp.token_presensi,
                               jp.materi_kuliah, jp.status_sesi,
                               mk.nama_mk, d.nama_lengkap as nama_dosen, jk.ruangan, jk.hari
                        FROM jurnal_perkuliahan jp
                        JOIN jadwal_kuliah jk ON jp.id_jadwal = jk.id_jadwal
                        JOIN matakuliah mk ON jk.id_mk = mk.id_mk
                        JOIN dosen d ON jk.id_dosen = d.id_dosen
                        WHERE jp.status_sesi = 'Open'
                        ORDER BY jp.tanggal DESC, jp.jam_masuk DESC";
$open_sessions = $conn->query($open_sessions_query);

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Kuliah - SIPRES</title>
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <style>
        .schedule-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        
        .schedule-card {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            transition: all 0.3s;
        }
        
        .schedule-card:hover {
            border-color: #667eea;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
        }
        
        .schedule-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
        }
        
        .course-title {
            font-size: 20px;
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }
        
        .course-code {
            color: #6c757d;
            font-size: 14px;
        }
        
        .schedule-badge {
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 13px;
            font-weight: 500;
        }
        
        .badge-sks {
            background: #e7f3ff;
            color: #0066cc;
        }
        
        .schedule-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        
        .detail-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .detail-icon {
            font-size: 20px;
        }
        
        .detail-content {
            flex: 1;
        }
        
        .detail-label {
            font-size: 12px;
            color: #6c757d;
            margin-bottom: 2px;
        }
        
        .detail-value {
            font-size: 14px;
            color: #333;
            font-weight: 500;
        }
        
        .lecturer-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
        }
        
        .lecturer-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }
        
        .lecturer-detail {
            font-size: 13px;
            color: #6c757d;
        }
        
        .open-session {
            background: #d4edda;
            border: 2px solid #c3e6cb;
        }
        
        .open-badge {
            background: #28a745;
            color: white;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .session-token {
            background: #fff3cd;
            border: 1px solid #ffc107;
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
            text-align: center;
        }
        
        .token-text {
            font-size: 24px;
            font-weight: 700;
            color: #856404;
            letter-spacing: 3px;
        }
        
        .btn-back {
            display: inline-block;
            padding: 10px 20px;
            background: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .btn-back:hover {
            background: #5a6268;
        }
        
        .section-title {
            font-size: 24px;
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
        }
        
        .day-group {
            margin-bottom: 30px;
        }
        
        .day-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="header">
            <div class="header-left">
                <h1>👨‍🎓 SIPRES Mahasiswa</h1>
                <p>Sistem Informasi Presensi - Jadwal & Forum</p>
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
        
        <a href="/modules/mahasiswa/dashboard.php" class="btn-back">← Kembali ke Dashboard</a>
        
        <!-- Open Sessions Section -->
        <?php if ($open_sessions->num_rows > 0): ?>
            <div class="schedule-container">
                <h2 class="section-title">🔴 Sesi Aktif (Forum Terbuka)</h2>
                <p style="color: #6c757d; margin-bottom: 20px;">
                    Forum berikut sedang berlangsung dan terbuka untuk presensi
                </p>
                
                <?php while ($session = $open_sessions->fetch_assoc()): ?>
                    <div class="schedule-card open-session">
                        <div class="schedule-header">
                            <div>
                                <div class="course-title">
                                    <?php echo htmlspecialchars($session['nama_mk']); ?>
                                </div>
                                <div class="course-code">
                                    Tanggal: <?php echo date('d/m/Y', strtotime($session['tanggal'])); ?>
                                </div>
                            </div>
                            <span class="open-badge">● AKTIF</span>
                        </div>
                        
                        <div class="schedule-details">
                            <div class="detail-item">
                                <span class="detail-icon">👨‍🏫</span>
                                <div class="detail-content">
                                    <div class="detail-label">Dosen Pengajar</div>
                                    <div class="detail-value"><?php echo htmlspecialchars($session['nama_dosen']); ?></div>
                                </div>
                            </div>
                            
                            <div class="detail-item">
                                <span class="detail-icon">🏢</span>
                                <div class="detail-content">
                                    <div class="detail-label">Ruangan</div>
                                    <div class="detail-value"><?php echo htmlspecialchars($session['ruangan']); ?></div>
                                </div>
                            </div>
                            
                            <div class="detail-item">
                                <span class="detail-icon">⏰</span>
                                <div class="detail-content">
                                    <div class="detail-label">Jam Masuk</div>
                                    <div class="detail-value"><?php echo date('H:i', strtotime($session['jam_masuk'])); ?></div>
                                </div>
                            </div>
                        </div>
                        
                        <?php if (!empty($session['materi_kuliah'])): ?>
                            <div style="margin-top: 15px; padding: 10px; background: white; border-radius: 5px;">
                                <strong>📚 Materi:</strong> <?php echo htmlspecialchars($session['materi_kuliah']); ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="session-token">
                            <div style="font-size: 14px; margin-bottom: 5px;">Token Presensi:</div>
                            <div class="token-text"><?php echo htmlspecialchars($session['token_presensi']); ?></div>
                            <div style="font-size: 12px; margin-top: 5px; color: #856404;">
                                Gunakan token ini untuk melakukan presensi
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
        
        <!-- Regular Schedule Section -->
        <div class="schedule-container">
            <h2 class="section-title">📅 Jadwal Kuliah Lengkap</h2>
            <p style="color: #6c757d; margin-bottom: 20px;">
                Informasi lengkap jadwal perkuliahan, dosen, dan ruangan
            </p>
            
            <?php
            $current_day = '';
            $has_schedule = false;
            if ($jadwal_result->num_rows > 0) {
                $has_schedule = true;
                while ($row = $jadwal_result->fetch_assoc()) {
                    // Group by day
                    if ($current_day !== $row['hari']) {
                        if ($current_day !== '') {
                            echo '</div>'; // Close previous day-group
                        }
                        $current_day = $row['hari'];
                        echo '<div class="day-group">';
                        echo '<div class="day-header">' . htmlspecialchars($current_day) . '</div>';
                    }
            ?>
                    <div class="schedule-card">
                        <div class="schedule-header">
                            <div>
                                <div class="course-title">
                                    <?php echo htmlspecialchars($row['nama_mk']); ?>
                                </div>
                                <div class="course-code">
                                    <?php echo htmlspecialchars($row['kode_mk']); ?> • 
                                    Semester <?php echo htmlspecialchars($row['semester']); ?>
                                </div>
                            </div>
                            <span class="schedule-badge badge-sks">
                                <?php echo htmlspecialchars($row['sks']); ?> SKS
                            </span>
                        </div>
                        
                        <div class="schedule-details">
                            <div class="detail-item">
                                <span class="detail-icon">⏰</span>
                                <div class="detail-content">
                                    <div class="detail-label">Waktu</div>
                                    <div class="detail-value">
                                        <?php echo date('H:i', strtotime($row['jam_mulai'])); ?> - 
                                        <?php echo date('H:i', strtotime($row['jam_selesai'])); ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="detail-item">
                                <span class="detail-icon">🏢</span>
                                <div class="detail-content">
                                    <div class="detail-label">Ruangan</div>
                                    <div class="detail-value"><?php echo htmlspecialchars($row['ruangan']); ?></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="lecturer-info">
                            <div class="lecturer-name">
                                👨‍🏫 <?php echo htmlspecialchars($row['nama_dosen']); ?>
                            </div>
                            <div class="lecturer-detail">
                                NIDN: <?php echo htmlspecialchars($row['nidn']); ?>
                                <?php if (!empty($row['email_dosen'])): ?>
                                    • Email: <?php echo htmlspecialchars($row['email_dosen']); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
            <?php
                }
                if ($current_day !== '') {
                    echo '</div>'; // Close last day-group
                }
            }
            
            if (!$has_schedule) {
                echo '<p>Belum ada jadwal kuliah tersedia.</p>';
            }
            ?>
        </div>
    </div>
</body>
</html>
