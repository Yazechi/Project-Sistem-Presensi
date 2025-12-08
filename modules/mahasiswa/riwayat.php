<?php
/**
 * Mahasiswa Riwayat Presensi Page
 * Page for students to view complete attendance history with entry/exit times
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
$nim = $mahasiswa_data['nim'];
$stmt->close();

// Get complete attendance history
$history_query = "SELECT pm.id_presensi, pm.waktu_scan, pm.status, pm.keterangan,
                         jp.tanggal, jp.jam_masuk, jp.jam_keluar, jp.materi_kuliah,
                         mk.kode_mk, mk.nama_mk, mk.sks,
                         d.nama_lengkap as nama_dosen, d.nidn,
                         jk.ruangan, jk.hari, jk.jam_mulai, jk.jam_selesai
                  FROM presensi_mahasiswa pm
                  JOIN jurnal_perkuliahan jp ON pm.id_jurnal = jp.id_jurnal
                  JOIN jadwal_kuliah jk ON jp.id_jadwal = jk.id_jadwal
                  JOIN matakuliah mk ON jk.id_mk = mk.id_mk
                  JOIN dosen d ON jk.id_dosen = d.id_dosen
                  WHERE pm.id_mahasiswa = ?
                  ORDER BY jp.tanggal DESC, pm.waktu_scan DESC";
$stmt = $conn->prepare($history_query);
$stmt->bind_param("i", $id_mahasiswa);
$stmt->execute();
$history_result = $stmt->get_result();
$stmt->close();

// Get attendance statistics
$stats_query = "SELECT 
                    COUNT(*) as total_presensi,
                    SUM(CASE WHEN pm.status = 'Hadir' THEN 1 ELSE 0 END) as total_hadir,
                    SUM(CASE WHEN pm.status = 'Izin' THEN 1 ELSE 0 END) as total_izin,
                    SUM(CASE WHEN pm.status = 'Sakit' THEN 1 ELSE 0 END) as total_sakit,
                    SUM(CASE WHEN pm.status = 'Alpha' THEN 1 ELSE 0 END) as total_alpha
                FROM presensi_mahasiswa pm
                WHERE pm.id_mahasiswa = ?";
$stmt = $conn->prepare($stats_query);
$stmt->bind_param("i", $id_mahasiswa);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get attendance by course
$per_course_query = "SELECT mk.nama_mk, mk.kode_mk,
                            COUNT(pm.id_presensi) as total,
                            SUM(CASE WHEN pm.status = 'Hadir' THEN 1 ELSE 0 END) as hadir
                     FROM presensi_mahasiswa pm
                     JOIN jurnal_perkuliahan jp ON pm.id_jurnal = jp.id_jurnal
                     JOIN jadwal_kuliah jk ON jp.id_jadwal = jk.id_jadwal
                     JOIN matakuliah mk ON jk.id_mk = mk.id_mk
                     WHERE pm.id_mahasiswa = ?
                     GROUP BY mk.id_mk";
$stmt = $conn->prepare($per_course_query);
$stmt->bind_param("i", $id_mahasiswa);
$stmt->execute();
$per_course = $stmt->get_result();
$stmt->close();

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Presensi - SIPRES</title>
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <style>
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        
        .stat-number {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #6c757d;
            font-size: 14px;
        }
        
        .stat-hadir { color: #28a745; }
        .stat-izin { color: #ffc107; }
        .stat-sakit { color: #17a2b8; }
        .stat-alpha { color: #dc3545; }
        
        .history-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        
        .history-card {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            transition: all 0.3s;
        }
        
        .history-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .history-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .course-info {
            flex: 1;
        }
        
        .course-name {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }
        
        .course-code {
            color: #6c757d;
            font-size: 14px;
        }
        
        .status-badge {
            padding: 6px 14px;
            border-radius: 15px;
            font-size: 13px;
            font-weight: 600;
        }
        
        .badge-hadir {
            background: #d4edda;
            color: #155724;
        }
        
        .badge-izin {
            background: #fff3cd;
            color: #856404;
        }
        
        .badge-sakit {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .badge-alpha {
            background: #f8d7da;
            color: #721c24;
        }
        
        .history-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .detail-item {
            display: flex;
            align-items: start;
            gap: 10px;
        }
        
        .detail-icon {
            font-size: 18px;
            margin-top: 2px;
        }
        
        .detail-content {
            flex: 1;
        }
        
        .detail-label {
            font-size: 12px;
            color: #6c757d;
            margin-bottom: 3px;
        }
        
        .detail-value {
            font-size: 14px;
            color: #333;
            font-weight: 500;
        }
        
        .time-section {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 6px;
            margin-top: 15px;
        }
        
        .time-label {
            font-size: 12px;
            color: #6c757d;
            margin-bottom: 8px;
        }
        
        .time-info {
            display: flex;
            justify-content: space-around;
            text-align: center;
        }
        
        .time-item {
            flex: 1;
        }
        
        .time-value {
            font-size: 18px;
            font-weight: 600;
            color: #333;
        }
        
        .time-desc {
            font-size: 11px;
            color: #6c757d;
            margin-top: 3px;
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
        }
        
        .course-summary {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        
        .course-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        .course-table th,
        .course-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }
        
        .course-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        
        .percentage {
            font-weight: 600;
        }
        
        .percentage-good { color: #28a745; }
        .percentage-warning { color: #ffc107; }
        .percentage-danger { color: #dc3545; }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="header">
            <div class="header-left">
                <h1>👨‍🎓 SIPRES Mahasiswa</h1>
                <p>Sistem Informasi Presensi - Riwayat Presensi</p>
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
        
        <!-- Statistics Section -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['total_presensi'] ?? 0; ?></div>
                <div class="stat-label">Total Presensi</div>
            </div>
            <div class="stat-card">
                <div class="stat-number stat-hadir"><?php echo $stats['total_hadir'] ?? 0; ?></div>
                <div class="stat-label">Hadir</div>
            </div>
            <div class="stat-card">
                <div class="stat-number stat-izin"><?php echo $stats['total_izin'] ?? 0; ?></div>
                <div class="stat-label">Izin</div>
            </div>
            <div class="stat-card">
                <div class="stat-number stat-sakit"><?php echo $stats['total_sakit'] ?? 0; ?></div>
                <div class="stat-label">Sakit</div>
            </div>
            <div class="stat-card">
                <div class="stat-number stat-alpha"><?php echo $stats['total_alpha'] ?? 0; ?></div>
                <div class="stat-label">Alpha</div>
            </div>
        </div>
        
        <!-- Course Summary -->
        <div class="course-summary">
            <h2 class="section-title">📊 Ringkasan Per Mata Kuliah</h2>
            
            <?php if ($per_course->num_rows > 0): ?>
                <table class="course-table">
                    <thead>
                        <tr>
                            <th>Mata Kuliah</th>
                            <th>Kode</th>
                            <th>Total Presensi</th>
                            <th>Hadir</th>
                            <th>Persentase</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($course = $per_course->fetch_assoc()): 
                            $percentage = $course['total'] > 0 ? ($course['hadir'] / $course['total']) * 100 : 0;
                            $percentage_class = $percentage >= 75 ? 'percentage-good' : 
                                              ($percentage >= 60 ? 'percentage-warning' : 'percentage-danger');
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($course['nama_mk']); ?></td>
                                <td><?php echo htmlspecialchars($course['kode_mk']); ?></td>
                                <td><?php echo $course['total']; ?></td>
                                <td><?php echo $course['hadir']; ?></td>
                                <td>
                                    <span class="percentage <?php echo $percentage_class; ?>">
                                        <?php echo number_format($percentage, 1); ?>%
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Belum ada data presensi per mata kuliah.</p>
            <?php endif; ?>
        </div>
        
        <!-- History Section -->
        <div class="history-container">
            <h2 class="section-title">📋 Riwayat Presensi Lengkap</h2>
            
            <?php if ($history_result->num_rows > 0): ?>
                <?php while ($row = $history_result->fetch_assoc()): ?>
                    <div class="history-card">
                        <div class="history-header">
                            <div class="course-info">
                                <div class="course-name">
                                    <?php echo htmlspecialchars($row['nama_mk']); ?>
                                </div>
                                <div class="course-code">
                                    <?php echo htmlspecialchars($row['kode_mk']); ?> • 
                                    <?php echo htmlspecialchars($row['sks']); ?> SKS
                                </div>
                            </div>
                            <span class="status-badge badge-<?php echo strtolower($row['status']); ?>">
                                <?php echo htmlspecialchars($row['status']); ?>
                            </span>
                        </div>
                        
                        <div class="history-details">
                            <div class="detail-item">
                                <span class="detail-icon">📅</span>
                                <div class="detail-content">
                                    <div class="detail-label">Tanggal Perkuliahan</div>
                                    <div class="detail-value">
                                        <?php echo date('d F Y', strtotime($row['tanggal'])); ?> 
                                        (<?php echo htmlspecialchars($row['hari']); ?>)
                                    </div>
                                </div>
                            </div>
                            
                            <div class="detail-item">
                                <span class="detail-icon">👨‍🏫</span>
                                <div class="detail-content">
                                    <div class="detail-label">Dosen Pengajar</div>
                                    <div class="detail-value">
                                        <?php echo htmlspecialchars($row['nama_dosen']); ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="detail-item">
                                <span class="detail-icon">🏢</span>
                                <div class="detail-content">
                                    <div class="detail-label">Ruangan</div>
                                    <div class="detail-value">
                                        <?php echo htmlspecialchars($row['ruangan']); ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="detail-item">
                                <span class="detail-icon">⏱️</span>
                                <div class="detail-content">
                                    <div class="detail-label">Waktu Presensi Anda</div>
                                    <div class="detail-value">
                                        <?php echo date('H:i:s', strtotime($row['waktu_scan'])); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="time-section">
                            <div class="time-label">⏰ Waktu Perkuliahan</div>
                            <div class="time-info">
                                <div class="time-item">
                                    <div class="time-value">
                                        <?php echo date('H:i', strtotime($row['jam_masuk'])); ?>
                                    </div>
                                    <div class="time-desc">Jam Masuk Dosen</div>
                                </div>
                                <div class="time-item" style="color: #6c757d;">
                                    <div style="font-size: 20px;">→</div>
                                </div>
                                <div class="time-item">
                                    <div class="time-value">
                                        <?php echo $row['jam_keluar'] ? date('H:i', strtotime($row['jam_keluar'])) : '-'; ?>
                                    </div>
                                    <div class="time-desc">Jam Keluar Dosen</div>
                                </div>
                            </div>
                        </div>
                        
                        <?php if (!empty($row['materi_kuliah'])): ?>
                            <div style="margin-top: 15px; padding: 12px; background: #fff3cd; border-radius: 6px;">
                                <strong>📚 Materi Perkuliahan:</strong> 
                                <?php echo htmlspecialchars($row['materi_kuliah']); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($row['keterangan'])): ?>
                            <div style="margin-top: 10px; padding: 10px; background: #f8d7da; border-radius: 6px;">
                                <strong>📝 Keterangan:</strong> 
                                <?php echo htmlspecialchars($row['keterangan']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>Belum ada riwayat presensi.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
