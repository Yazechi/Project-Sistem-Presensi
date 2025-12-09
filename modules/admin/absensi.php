<?php
/**
 * Admin Attendance Monitoring Page
 * View all attendance records from dosen and mahasiswa
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
$view = $_GET['view'] ?? 'dosen'; // dosen or mahasiswa

// Get records based on view
if ($view == 'mahasiswa') {
    $records = getAllStudentAttendance($start_date, $end_date, $search);
} else {
    $records = getAllAttendanceRecords($start_date, $end_date, $search);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring Absensi - SIPRES Admin</title>
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <style>
        .view-toggle {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        .view-toggle a {
            padding: 10px 20px;
            background: #e0e0e0;
            color: #333;
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.3s;
        }
        .view-toggle a.active {
            background: #007bff;
            color: white;
        }
        .view-toggle a:hover {
            background: #0056b3;
            color: white;
        }
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
        .filter-row input, .filter-row select {
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
        .records-table {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .records-table table {
            width: 100%;
            border-collapse: collapse;
        }
        .records-table th {
            background: #007bff;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: 600;
        }
        .records-table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }
        .records-table tr:hover {
            background: #f8f9fa;
        }
        .status-badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-open {
            background: #28a745;
            color: white;
        }
        .status-closed {
            background: #6c757d;
            color: white;
        }
        .status-hadir {
            background: #28a745;
            color: white;
        }
        .status-izin {
            background: #ffc107;
            color: #333;
        }
        .status-sakit {
            background: #17a2b8;
            color: white;
        }
        .status-alpha {
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
                <h1>📊 Monitoring Absensi</h1>
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

        <div class="view-toggle">
            <a href="?view=dosen&start_date=<?php echo htmlspecialchars($start_date); ?>&end_date=<?php echo htmlspecialchars($end_date); ?>&search=<?php echo htmlspecialchars($search); ?>" 
               class="<?php echo $view == 'dosen' ? 'active' : ''; ?>">
                👨‍🏫 Absensi Dosen
            </a>
            <a href="?view=mahasiswa&start_date=<?php echo htmlspecialchars($start_date); ?>&end_date=<?php echo htmlspecialchars($end_date); ?>&search=<?php echo htmlspecialchars($search); ?>" 
               class="<?php echo $view == 'mahasiswa' ? 'active' : ''; ?>">
                🎓 Absensi Mahasiswa
            </a>
        </div>

        <div class="filter-form">
            <form method="GET" action="">
                <input type="hidden" name="view" value="<?php echo htmlspecialchars($view); ?>">
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
                        <input type="text" name="search" placeholder="Nama / NIM / Mata Kuliah" value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                </div>
                <button type="submit" class="btn-filter">🔍 Filter Data</button>
            </form>
        </div>

        <?php if ($view == 'dosen'): ?>
            <!-- Dosen Attendance View -->
            <?php
            $total_sesi = count($records);
            $total_hadir = array_sum(array_column($records, 'jumlah_hadir'));
            $sesi_open = count(array_filter($records, function($r) { return $r['status_sesi'] == 'Open'; }));
            $sesi_closed = $total_sesi - $sesi_open;
            ?>
            <div class="stats-grid">
                <div class="stat-card">
                    <h3><?php echo $total_sesi; ?></h3>
                    <p>Total Sesi</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $total_hadir; ?></h3>
                    <p>Total Kehadiran Mahasiswa</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $sesi_open; ?></h3>
                    <p>Sesi Aktif</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $sesi_closed; ?></h3>
                    <p>Sesi Selesai</p>
                </div>
            </div>

            <div class="records-table">
                <?php if (empty($records)): ?>
                    <div class="no-data">
                        <p>Tidak ada data absensi dosen untuk periode yang dipilih.</p>
                    </div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Mata Kuliah</th>
                                <th>Dosen</th>
                                <th>Ruangan</th>
                                <th>Jam Masuk</th>
                                <th>Jam Keluar</th>
                                <th>Token</th>
                                <th>Status</th>
                                <th>Jumlah Hadir</th>
                                <th>Materi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($records as $record): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y', strtotime($record['tanggal'])); ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($record['kode_mk']); ?></strong><br>
                                        <small><?php echo htmlspecialchars($record['nama_mk']); ?></small>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($record['nama_dosen']); ?><br>
                                        <small><?php echo htmlspecialchars($record['nidn']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($record['ruangan']); ?></td>
                                    <td><?php echo date('H:i', strtotime($record['jam_masuk'])); ?></td>
                                    <td><?php echo $record['jam_keluar'] ? date('H:i', strtotime($record['jam_keluar'])) : '-'; ?></td>
                                    <td><strong><?php echo htmlspecialchars($record['token_presensi']); ?></strong></td>
                                    <td>
                                        <span class="status-badge status-<?php echo strtolower($record['status_sesi']); ?>">
                                            <?php echo $record['status_sesi']; ?>
                                        </span>
                                    </td>
                                    <td><strong><?php echo $record['jumlah_hadir']; ?></strong> mahasiswa</td>
                                    <td><?php echo htmlspecialchars(substr($record['materi_kuliah'], 0, 50)) . (strlen($record['materi_kuliah']) > 50 ? '...' : ''); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

        <?php else: ?>
            <!-- Mahasiswa Attendance View -->
            <?php
            $total_records = count($records);
            $hadir = count(array_filter($records, function($r) { return $r['status'] == 'Hadir'; }));
            $izin = count(array_filter($records, function($r) { return $r['status'] == 'Izin'; }));
            $sakit = count(array_filter($records, function($r) { return $r['status'] == 'Sakit'; }));
            $alpha = count(array_filter($records, function($r) { return $r['status'] == 'Alpha'; }));
            ?>
            <div class="stats-grid">
                <div class="stat-card">
                    <h3><?php echo $total_records; ?></h3>
                    <p>Total Presensi</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $hadir; ?></h3>
                    <p>Hadir</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $izin; ?></h3>
                    <p>Izin</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $sakit; ?></h3>
                    <p>Sakit</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $alpha; ?></h3>
                    <p>Alpha</p>
                </div>
            </div>

            <div class="records-table">
                <?php if (empty($records)): ?>
                    <div class="no-data">
                        <p>Tidak ada data absensi mahasiswa untuk periode yang dipilih.</p>
                    </div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Mahasiswa</th>
                                <th>Jurusan</th>
                                <th>Mata Kuliah</th>
                                <th>Dosen</th>
                                <th>Waktu Scan</th>
                                <th>Status</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($records as $record): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y', strtotime($record['tanggal'])); ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($record['nama_mahasiswa']); ?></strong><br>
                                        <small><?php echo htmlspecialchars($record['nim']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($record['jurusan']); ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($record['kode_mk']); ?></strong><br>
                                        <small><?php echo htmlspecialchars($record['nama_mk']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($record['nama_dosen']); ?></td>
                                    <td><?php echo date('H:i:s', strtotime($record['waktu_scan'])); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo strtolower($record['status']); ?>">
                                            <?php echo $record['status']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($record['keterangan'] ?? '-'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
