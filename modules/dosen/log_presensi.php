<?php
/**
 * Log Presensi Page
 * Page for viewing attendance logs for dosen and students
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
$user_id = $_SESSION['user_id'] ?? 0;
$id_dosen = getDosenId($user_id);

// Get filter parameters
$start_date = $_GET['start_date'] ?? date('Y-m-01'); // First day of current month
$end_date = $_GET['end_date'] ?? date('Y-m-d'); // Today

// Get attendance summary
$rekap = getRekapPresensiDosen($id_dosen, $start_date, $end_date);

// Calculate statistics
$total_sesi = count($rekap);
$total_mahasiswa_hadir = 0;
$total_izin = 0;
$total_sakit = 0;
$total_alpha = 0;

foreach ($rekap as $r) {
    $total_mahasiswa_hadir += intval($r['hadir']);
    $total_izin += intval($r['izin']);
    $total_sakit += intval($r['sakit']);
    $total_alpha += intval($r['alpha']);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Presensi - SIPRES</title>
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <style>
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input, .form-group select {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin-right: 10px;
        }
        .btn-primary {
            background-color: #007bff;
            color: white;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-card h4 {
            margin: 0 0 10px 0;
            color: #666;
            font-size: 14px;
        }
        .stat-card .number {
            font-size: 32px;
            font-weight: bold;
            margin: 0;
        }
        .stat-card.total .number {
            color: #007bff;
        }
        .stat-card.hadir .number {
            color: #28a745;
        }
        .stat-card.izin .number {
            color: #ffc107;
        }
        .stat-card.sakit .number {
            color: #17a2b8;
        }
        .stat-card.alpha .number {
            color: #dc3545;
        }
        .rekap-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .rekap-table th, .rekap-table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        .rekap-table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .rekap-table tr:hover {
            background-color: #f8f9fa;
        }
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-badge.open {
            background-color: #28a745;
            color: white;
        }
        .status-badge.closed {
            background-color: #6c757d;
            color: white;
        }
        .nav-links {
            margin-bottom: 20px;
        }
        .nav-links a {
            margin-right: 15px;
            text-decoration: none;
            color: #007bff;
        }
        .nav-links a:hover {
            text-decoration: underline;
        }
        .filter-section {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .filter-form {
            display: flex;
            gap: 15px;
            align-items: flex-end;
        }
        .filter-form .form-group {
            margin-bottom: 0;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="header">
            <div class="header-left">
                <h1>👨‍🏫 SIPRES Dosen - Log Presensi</h1>
                <p>Sistem Informasi Presensi - Riwayat Absensi</p>
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

        <div class="nav-links">
            <a href="dashboard.php">← Kembali ke Dashboard</a>
            <a href="absensi.php">Absensi Dosen</a>
            <a href="kelola_mahasiswa.php">Kelola Mahasiswa</a>
        </div>

        <div class="filter-section">
            <h3>🔍 Filter Periode</h3>
            <form method="GET" class="filter-form">
                <div class="form-group">
                    <label for="start_date">Tanggal Mulai:</label>
                    <input type="date" name="start_date" id="start_date" value="<?php echo htmlspecialchars($start_date); ?>" required>
                </div>
                <div class="form-group">
                    <label for="end_date">Tanggal Akhir:</label>
                    <input type="date" name="end_date" id="end_date" value="<?php echo htmlspecialchars($end_date); ?>" required>
                </div>
                <button type="submit" class="btn btn-primary">Tampilkan</button>
            </form>
        </div>

        <h3>📊 Statistik Kehadiran</h3>
        <div class="stats-grid">
            <div class="stat-card total">
                <h4>Total Sesi</h4>
                <p class="number"><?php echo $total_sesi; ?></p>
            </div>
            <div class="stat-card hadir">
                <h4>Total Hadir</h4>
                <p class="number"><?php echo $total_mahasiswa_hadir; ?></p>
            </div>
            <div class="stat-card izin">
                <h4>Total Izin</h4>
                <p class="number"><?php echo $total_izin; ?></p>
            </div>
            <div class="stat-card sakit">
                <h4>Total Sakit</h4>
                <p class="number"><?php echo $total_sakit; ?></p>
            </div>
            <div class="stat-card alpha">
                <h4>Total Alpha</h4>
                <p class="number"><?php echo $total_alpha; ?></p>
            </div>
        </div>

        <h3>📋 Riwayat Absensi Lengkap</h3>
        <p style="color: #666; margin-bottom: 15px;">
            Periode: <?php echo date('d/m/Y', strtotime($start_date)); ?> - <?php echo date('d/m/Y', strtotime($end_date)); ?>
        </p>
        
        <table class="rekap-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Mata Kuliah</th>
                    <th>Ruangan</th>
                    <th>Jam Masuk</th>
                    <th>Jam Keluar</th>
                    <th>Status Sesi</th>
                    <th>Total Mhs</th>
                    <th>Hadir</th>
                    <th>Izin</th>
                    <th>Sakit</th>
                    <th>Alpha</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                foreach ($rekap as $r): 
                    $total_mhs = intval($r['total_mahasiswa']);
                ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><?php echo date('d/m/Y', strtotime($r['tanggal'])); ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($r['kode_mk']); ?></strong><br>
                            <?php echo htmlspecialchars($r['nama_mk']); ?>
                        </td>
                        <td><?php echo htmlspecialchars($r['ruangan']); ?></td>
                        <td><?php echo $r['jam_masuk']; ?></td>
                        <td><?php echo $r['jam_keluar'] ? $r['jam_keluar'] : '-'; ?></td>
                        <td>
                            <span class="status-badge <?php echo strtolower($r['status_sesi']); ?>">
                                <?php echo $r['status_sesi']; ?>
                            </span>
                        </td>
                        <td style="text-align: center;"><?php echo $total_mhs; ?></td>
                        <td style="text-align: center; color: #28a745; font-weight: bold;"><?php echo $r['hadir']; ?></td>
                        <td style="text-align: center; color: #ffc107; font-weight: bold;"><?php echo $r['izin']; ?></td>
                        <td style="text-align: center; color: #17a2b8; font-weight: bold;"><?php echo $r['sakit']; ?></td>
                        <td style="text-align: center; color: #dc3545; font-weight: bold;"><?php echo $r['alpha']; ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($rekap)): ?>
                    <tr>
                        <td colspan="12" style="text-align: center;">Tidak ada data untuk periode yang dipilih</td>
                    </tr>
                <?php endif; ?>
            </tbody>
            <?php if (!empty($rekap)): ?>
            <tfoot>
                <tr style="background-color: #e9ecef; font-weight: bold;">
                    <td colspan="7" style="text-align: right;">Total:</td>
                    <td style="text-align: center;">-</td>
                    <td style="text-align: center; color: #28a745;"><?php echo $total_mahasiswa_hadir; ?></td>
                    <td style="text-align: center; color: #ffc107;"><?php echo $total_izin; ?></td>
                    <td style="text-align: center; color: #17a2b8;"><?php echo $total_sakit; ?></td>
                    <td style="text-align: center; color: #dc3545;"><?php echo $total_alpha; ?></td>
                </tr>
            </tfoot>
            <?php endif; ?>
        </table>

        <div class="info-card" style="margin-top: 30px;">
            <h3>ℹ️ Keterangan</h3>
            <ul>
                <li><strong>Hadir:</strong> Mahasiswa yang melakukan presensi dan hadir di kelas</li>
                <li><strong>Izin:</strong> Mahasiswa yang tidak hadir dengan izin resmi</li>
                <li><strong>Sakit:</strong> Mahasiswa yang tidak hadir karena sakit dengan surat keterangan</li>
                <li><strong>Alpha:</strong> Mahasiswa yang tidak hadir tanpa keterangan</li>
            </ul>
        </div>
    </div>
</body>
</html>
