<?php
/**
 * Admin Activity Log Page
 * View system activity logs
 */

require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../includes/admin_functions.php';

// Start session
startSecureSession();

// Require admin role
if ($_SESSION['role'] != 'admin') {
    header("Location: /auth/login.php");
    exit;
}

$nama = $_SESSION['nama'] ?? 'Administrator';

// Get filter parameters
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');
$search = $_GET['search'] ?? '';

// Get logs
$logs = getActivityLogs($start_date, $end_date, $search);

// If no log table exists, show recent activities from database
if (empty($logs)) {
    // Get recent activities from attendance records as alternative
    try {
        $conn = require_once __DIR__ . '/../../config/database.php';
        $conn = getDBConnection();
        
        // Get recent jurnal perkuliahan activities
        $query = "SELECT 
                    'Dosen Check-in' as activity_type,
                    jp.created_at,
                    d.nama_lengkap as user_name,
                    CONCAT('Membuka sesi ', mk.nama_mk, ' dengan token ', jp.token_presensi) as description
                  FROM jurnal_perkuliahan jp
                  JOIN jadwal_kuliah jk ON jp.id_jadwal = jk.id_jadwal
                  JOIN matakuliah mk ON jk.id_mk = mk.id_mk
                  JOIN dosen d ON jk.id_dosen = d.id_dosen
                  WHERE DATE(jp.created_at) >= ? AND DATE(jp.created_at) <= ?
                  UNION ALL
                  SELECT 
                    'Mahasiswa Presensi' as activity_type,
                    pm.created_at,
                    m.nama_lengkap as user_name,
                    CONCAT('Melakukan presensi untuk ', mk.nama_mk, ' - Status: ', pm.status) as description
                  FROM presensi_mahasiswa pm
                  JOIN mahasiswa m ON pm.id_mahasiswa = m.id_mahasiswa
                  JOIN jurnal_perkuliahan jp ON pm.id_jurnal = jp.id_jurnal
                  JOIN jadwal_kuliah jk ON jp.id_jadwal = jk.id_jadwal
                  JOIN matakuliah mk ON jk.id_mk = mk.id_mk
                  WHERE DATE(pm.created_at) >= ? AND DATE(pm.created_at) <= ?
                  ORDER BY created_at DESC
                  LIMIT 500";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssss", $start_date, $end_date, $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result();
        $logs = $result->fetch_all(MYSQLI_ASSOC);
        
        $stmt->close();
        $conn->close();
    } catch (Exception $e) {
        error_log("Error getting activity logs: " . $e->getMessage());
        $logs = [];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Aktivitas - SIPRES Admin</title>
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <style>
        .filter-form {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .filter-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }
        .filter-row label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .filter-row input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .btn-filter {
            padding: 10px 30px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-filter:hover {
            background: #0056b3;
        }
        .logs-container {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .log-item {
            padding: 15px;
            border-bottom: 1px solid #eee;
            display: grid;
            grid-template-columns: 150px 200px 1fr;
            gap: 15px;
            align-items: center;
        }
        .log-item:hover {
            background: #f8f9fa;
        }
        .log-time {
            color: #666;
            font-size: 14px;
        }
        .log-user {
            font-weight: bold;
            color: #007bff;
        }
        .log-activity {
            color: #333;
        }
        .activity-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            margin-right: 8px;
        }
        .badge-checkin {
            background: #28a745;
            color: white;
        }
        .badge-presensi {
            background: #007bff;
            color: white;
        }
        .badge-edit {
            background: #ffc107;
            color: #333;
        }
        .badge-delete {
            background: #dc3545;
            color: white;
        }
        .no-data {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-card h3 {
            margin: 0;
            font-size: 32px;
            color: #007bff;
        }
        .stat-card p {
            margin: 5px 0 0 0;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="header">
            <div class="header-left">
                <h1>📋 Log Aktivitas Sistem</h1>
                <p>Sistem Informasi Presensi - Panel Administrator</p>
            </div>
            <div class="header-right">
                <div class="user-info">
                    <div class="user-name"><?php echo htmlspecialchars($nama); ?></div>
                    <div class="user-role">
                        <span class="role-badge admin">Admin</span>
                    </div>
                </div>
                <a href="/modules/admin/dashboard.php" class="btn-logout">Dashboard</a>
                <a href="/auth/logout.php" class="btn-logout">Logout</a>
            </div>
        </div>

        <?php
        $total_logs = count($logs);
        $checkin_count = count(array_filter($logs, function($l) { return isset($l['activity_type']) && $l['activity_type'] == 'Dosen Check-in'; }));
        $presensi_count = count(array_filter($logs, function($l) { return isset($l['activity_type']) && $l['activity_type'] == 'Mahasiswa Presensi'; }));
        ?>
        <div class="stats-grid">
            <div class="stat-card">
                <h3><?php echo $total_logs; ?></h3>
                <p>Total Aktivitas</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $checkin_count; ?></h3>
                <p>Check-in Dosen</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $presensi_count; ?></h3>
                <p>Presensi Mahasiswa</p>
            </div>
        </div>

        <div class="filter-form">
            <h3>🔍 Filter Log</h3>
            <form method="GET" action="">
                <div class="filter-row">
                    <div>
                        <label>Tanggal Mulai:</label>
                        <input type="date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>">
                    </div>
                    <div>
                        <label>Tanggal Akhir:</label>
                        <input type="date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>">
                    </div>
                    <div>
                        <label>Cari:</label>
                        <input type="text" name="search" placeholder="Nama pengguna atau aktivitas" value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                </div>
                <button type="submit" class="btn-filter">Filter</button>
            </form>
        </div>

        <div class="logs-container">
            <?php if (empty($logs)): ?>
                <div class="no-data">
                    <p>📭 Tidak ada aktivitas untuk periode yang dipilih.</p>
                    <p><small>Log aktivitas akan muncul saat ada aktivitas check-in dosen atau presensi mahasiswa.</small></p>
                </div>
            <?php else: ?>
                <?php foreach ($logs as $log): ?>
                    <div class="log-item">
                        <div class="log-time">
                            <?php 
                            $timestamp = isset($log['created_at']) ? $log['created_at'] : '';
                            if ($timestamp) {
                                echo date('d/m/Y H:i:s', strtotime($timestamp));
                            }
                            ?>
                        </div>
                        <div class="log-user">
                            <?php 
                            if (isset($log['activity_type'])) {
                                $badge_class = 'badge-presensi';
                                if ($log['activity_type'] == 'Dosen Check-in') {
                                    $badge_class = 'badge-checkin';
                                }
                                echo '<span class="activity-badge ' . $badge_class . '">' . htmlspecialchars($log['activity_type']) . '</span><br>';
                            }
                            echo htmlspecialchars($log['user_name'] ?? 'Unknown');
                            ?>
                        </div>
                        <div class="log-activity">
                            <?php echo htmlspecialchars($log['description'] ?? $log['activity'] ?? '-'); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div style="background: white; padding: 20px; border-radius: 8px; margin-top: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h3>ℹ️ Informasi Log Aktivitas</h3>
            <p>Sistem ini menampilkan aktivitas dari:</p>
            <ul>
                <li>✅ Check-in dan check-out dosen</li>
                <li>✅ Presensi mahasiswa</li>
                <li>✅ Perubahan status kehadiran</li>
            </ul>
            <p><small>Log disimpan otomatis setiap kali ada aktivitas dalam sistem presensi.</small></p>
        </div>
    </div>
</body>
</html>
