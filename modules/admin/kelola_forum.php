<?php
/**
 * Admin Forum Management Page
 * Create and manage attendance forums (jurnal_perkuliahan)
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
$message = '';
$error = '';
$token_generated = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action == 'create_forum') {
        $result = createForumAbsensi(
            intval($_POST['id_jadwal']),
            $_POST['tanggal'],
            $_POST['jam_masuk'],
            $_POST['materi_kuliah']
        );
        
        if ($result['success']) {
            $message = $result['message'] . ' - Token: ' . $result['token'];
            $token_generated = $result['token'];
        } else {
            $error = $result['message'];
        }
    } elseif ($action == 'close_forum') {
        $result = closeForumAbsensi(
            intval($_POST['id_jurnal']),
            $_POST['jam_keluar']
        );
        
        if ($result['success']) {
            $message = $result['message'];
        } else {
            $error = $result['message'];
        }
    }
}

// Get filter parameters
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');

// Get data
$jadwal_list = getAllJadwal();
$forum_list = getAllAttendanceRecords($start_date, $end_date);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Forum Absensi - SIPRES Admin</title>
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
        .token-display {
            background: #007bff;
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            margin: 20px 0;
        }
        .token-display h2 {
            margin: 0 0 10px 0;
            font-size: 48px;
            letter-spacing: 5px;
        }
        .form-card {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }
        .form-group {
            display: flex;
            flex-direction: column;
        }
        .form-group label {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .form-group input, .form-group select, .form-group textarea {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }
        .btn-submit {
            padding: 12px 30px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }
        .btn-submit:hover {
            background: #0056b3;
        }
        .btn-close {
            padding: 6px 12px;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        .btn-close:hover {
            background: #c82333;
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
        .table-container {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            background: #007bff;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: 600;
        }
        td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }
        tr:hover {
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
                <h1>📝 Kelola Forum Absensi</h1>
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

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($token_generated): ?>
            <div class="token-display">
                <p>Token yang di-generate:</p>
                <h2><?php echo htmlspecialchars($token_generated); ?></h2>
                <p>Bagikan token ini kepada mahasiswa untuk melakukan presensi</p>
            </div>
        <?php endif; ?>

        <?php
        $total_forum = count($forum_list);
        $forum_open = count(array_filter($forum_list, function($f) { return $f['status_sesi'] == 'Open'; }));
        $forum_closed = $total_forum - $forum_open;
        $total_kehadiran = array_sum(array_column($forum_list, 'jumlah_hadir'));
        ?>
        <div class="stats-grid">
            <div class="stat-card">
                <h3><?php echo $total_forum; ?></h3>
                <p>Total Forum</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $forum_open; ?></h3>
                <p>Forum Aktif</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $forum_closed; ?></h3>
                <p>Forum Selesai</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $total_kehadiran; ?></h3>
                <p>Total Kehadiran</p>
            </div>
        </div>

        <!-- Create Forum Form -->
        <div class="form-card">
            <h2>➕ Buat Forum Absensi Baru</h2>
            <form method="POST" action="">
                <input type="hidden" name="action" value="create_forum">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label>Jadwal Kuliah: *</label>
                        <select name="id_jadwal" required>
                            <option value="">-- Pilih Jadwal --</option>
                            <?php foreach ($jadwal_list as $jadwal): ?>
                                <option value="<?php echo $jadwal['id_jadwal']; ?>">
                                    <?php echo htmlspecialchars($jadwal['kode_mk'] . ' - ' . $jadwal['nama_mk'] . ' (' . $jadwal['nama_dosen'] . ') - ' . $jadwal['hari'] . ' ' . date('H:i', strtotime($jadwal['jam_mulai']))); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Tanggal: *</label>
                        <input type="date" name="tanggal" required value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="form-group">
                        <label>Jam Masuk: *</label>
                        <input type="time" name="jam_masuk" required value="<?php echo date('H:i'); ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Materi Kuliah: *</label>
                    <textarea name="materi_kuliah" required placeholder="Masukkan materi kuliah yang akan diajarkan..."></textarea>
                </div>
                
                <button type="submit" class="btn-submit">🎯 Buat Forum & Generate Token</button>
            </form>
        </div>

        <!-- Filter Form -->
        <div class="filter-form">
            <h3>🔍 Filter Forum</h3>
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
                </div>
                <button type="submit" class="btn-submit">Filter</button>
            </form>
        </div>

        <!-- Forum List -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tanggal</th>
                        <th>Mata Kuliah</th>
                        <th>Dosen</th>
                        <th>Ruangan</th>
                        <th>Jam Masuk</th>
                        <th>Jam Keluar</th>
                        <th>Token</th>
                        <th>Status</th>
                        <th>Kehadiran</th>
                        <th>Materi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($forum_list)): ?>
                        <tr>
                            <td colspan="12" style="text-align: center; padding: 40px; color: #666;">
                                Tidak ada forum untuk periode yang dipilih.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($forum_list as $forum): ?>
                            <tr>
                                <td><?php echo $forum['id_jurnal']; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($forum['tanggal'])); ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($forum['kode_mk']); ?></strong><br>
                                    <small><?php echo htmlspecialchars($forum['nama_mk']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($forum['nama_dosen']); ?></td>
                                <td><?php echo htmlspecialchars($forum['ruangan']); ?></td>
                                <td><?php echo date('H:i', strtotime($forum['jam_masuk'])); ?></td>
                                <td><?php echo $forum['jam_keluar'] ? date('H:i', strtotime($forum['jam_keluar'])) : '-'; ?></td>
                                <td><strong><?php echo htmlspecialchars($forum['token_presensi']); ?></strong></td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($forum['status_sesi']); ?>">
                                        <?php echo $forum['status_sesi']; ?>
                                    </span>
                                </td>
                                <td><strong><?php echo $forum['jumlah_hadir']; ?></strong></td>
                                <td>
                                    <small><?php echo htmlspecialchars(substr($forum['materi_kuliah'], 0, 30)) . (strlen($forum['materi_kuliah']) > 30 ? '...' : ''); ?></small>
                                </td>
                                <td>
                                    <?php if ($forum['status_sesi'] == 'Open'): ?>
                                        <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Yakin ingin menutup forum ini?');">
                                            <input type="hidden" name="action" value="close_forum">
                                            <input type="hidden" name="id_jurnal" value="<?php echo $forum['id_jurnal']; ?>">
                                            <input type="hidden" name="jam_keluar" value="<?php echo date('H:i:s'); ?>">
                                            <button type="submit" class="btn-close">Tutup Forum</button>
                                        </form>
                                    <?php else: ?>
                                        <span style="color: #999;">Selesai</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
