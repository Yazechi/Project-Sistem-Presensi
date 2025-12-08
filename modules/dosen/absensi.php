<?php
/**
 * Dosen Absensi Page
 * Page for lecturer check-in and check-out
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

$message = '';
$error = '';
$token = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'checkin') {
            $id_jadwal = intval($_POST['id_jadwal']);
            $tanggal = $_POST['tanggal'];
            $jam_masuk = $_POST['jam_masuk'];
            $materi = trim($_POST['materi']);
            
            $result = bukaSesilPerkuliahan($id_jadwal, $tanggal, $jam_masuk, $materi);
            
            if ($result['success']) {
                $message = "Sesi berhasil dibuka! Token: " . $result['token'];
                $token = $result['token'];
            } else {
                $error = $result['message'];
            }
        } elseif ($_POST['action'] == 'checkout') {
            $id_jurnal = intval($_POST['id_jurnal']);
            $jam_keluar = $_POST['jam_keluar'];
            
            $result = tutupSesiPerkuliahan($id_jurnal, $jam_keluar);
            
            if ($result['success']) {
                $message = $result['message'];
            } else {
                $error = $result['message'];
            }
        }
    }
}

// Get jadwal kuliah
$jadwal_list = getJadwalByDosen($id_dosen);

// Get recent jurnal
$jurnal_list = getJurnalByDosen($id_dosen, 5);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absensi Dosen - SIPRES</title>
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
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        .form-group textarea {
            min-height: 80px;
            resize: vertical;
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
        .btn-danger {
            background-color: #dc3545;
            color: white;
        }
        .btn-danger:hover {
            background-color: #c82333;
        }
        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .token-display {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
            padding: 15px;
            background-color: #e7f3ff;
            border-radius: 4px;
            text-align: center;
            margin: 10px 0;
        }
        .jurnal-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .jurnal-table th, .jurnal-table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        .jurnal-table th {
            background-color: #f8f9fa;
            font-weight: bold;
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
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="header">
            <div class="header-left">
                <h1>👨‍🏫 SIPRES Dosen - Absensi</h1>
                <p>Sistem Informasi Presensi - Absensi Dosen</p>
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
            <a href="kelola_mahasiswa.php">Kelola Mahasiswa</a>
            <a href="log_presensi.php">Log Presensi</a>
        </div>

        <?php if ($message): ?>
            <div class="message success">
                <?php echo htmlspecialchars($message); ?>
                <?php if ($token): ?>
                    <div class="token-display">Token: <?php echo htmlspecialchars($token); ?></div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="info-grid">
            <div class="info-card">
                <h3>📝 Check-In (Buka Sesi Perkuliahan)</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="checkin">
                    
                    <div class="form-group">
                        <label for="id_jadwal">Mata Kuliah:</label>
                        <select name="id_jadwal" id="id_jadwal" required>
                            <option value="">Pilih Mata Kuliah</option>
                            <?php foreach ($jadwal_list as $jadwal): ?>
                                <option value="<?php echo $jadwal['id_jadwal']; ?>">
                                    <?php echo htmlspecialchars($jadwal['kode_mk'] . ' - ' . $jadwal['nama_mk'] . ' (' . $jadwal['hari'] . ' ' . $jadwal['jam_mulai'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="tanggal">Tanggal:</label>
                        <input type="date" name="tanggal" id="tanggal" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="jam_masuk">Jam Masuk:</label>
                        <input type="time" name="jam_masuk" id="jam_masuk" value="<?php echo date('H:i'); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="materi">Materi Kuliah:</label>
                        <textarea name="materi" id="materi" required placeholder="Masukkan materi perkuliahan hari ini"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">Check-In</button>
                </form>
            </div>

            <div class="info-card">
                <h3>⏱️ Sesi Aktif</h3>
                <?php 
                $active_found = false;
                foreach ($jurnal_list as $jurnal): 
                    if ($jurnal['status_sesi'] == 'Open'):
                        $active_found = true;
                ?>
                    <div style="margin-bottom: 15px; padding: 10px; background: #f8f9fa; border-radius: 4px;">
                        <p><strong><?php echo htmlspecialchars($jurnal['nama_mk']); ?></strong></p>
                        <p>Token: <strong style="color: #007bff;"><?php echo htmlspecialchars($jurnal['token_presensi']); ?></strong></p>
                        <p>Tanggal: <?php echo date('d/m/Y', strtotime($jurnal['tanggal'])); ?></p>
                        <p>Jam Masuk: <?php echo $jurnal['jam_masuk']; ?></p>
                        <p>Mahasiswa Hadir: <?php echo $jurnal['jumlah_hadir']; ?></p>
                        
                        <form method="POST" style="margin-top: 10px;">
                            <input type="hidden" name="action" value="checkout">
                            <input type="hidden" name="id_jurnal" value="<?php echo $jurnal['id_jurnal']; ?>">
                            <div class="form-group">
                                <label for="jam_keluar">Jam Keluar:</label>
                                <input type="time" name="jam_keluar" id="jam_keluar" value="<?php echo date('H:i'); ?>" required>
                            </div>
                            <button type="submit" class="btn btn-danger">Check-Out</button>
                        </form>
                    </div>
                <?php 
                    endif;
                endforeach; 
                
                if (!$active_found):
                ?>
                    <p>Tidak ada sesi aktif. Silakan check-in terlebih dahulu.</p>
                <?php endif; ?>
            </div>
        </div>

        <h3>📋 Riwayat Absensi Terakhir</h3>
        <table class="jurnal-table">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Mata Kuliah</th>
                    <th>Jam Masuk</th>
                    <th>Jam Keluar</th>
                    <th>Mahasiswa Hadir</th>
                    <th>Status</th>
                    <th>Token</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($jurnal_list as $jurnal): ?>
                    <tr>
                        <td><?php echo date('d/m/Y', strtotime($jurnal['tanggal'])); ?></td>
                        <td><?php echo htmlspecialchars($jurnal['nama_mk']); ?></td>
                        <td><?php echo $jurnal['jam_masuk']; ?></td>
                        <td><?php echo $jurnal['jam_keluar'] ? $jurnal['jam_keluar'] : '-'; ?></td>
                        <td><?php echo $jurnal['jumlah_hadir']; ?></td>
                        <td>
                            <span class="status-badge <?php echo strtolower($jurnal['status_sesi']); ?>">
                                <?php echo $jurnal['status_sesi']; ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($jurnal['token_presensi']); ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($jurnal_list)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center;">Belum ada riwayat absensi</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
