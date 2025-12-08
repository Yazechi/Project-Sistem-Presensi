<?php
/**
 * Kelola Mahasiswa Page
 * Page for managing student attendance status
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
$selected_jurnal = null;
$mahasiswa_list = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'set_status') {
            $id_jurnal = intval($_POST['id_jurnal']);
            $id_mahasiswa = intval($_POST['id_mahasiswa']);
            $status = $_POST['status'];
            $keterangan = trim($_POST['keterangan'] ?? '');
            
            $result = setStatusMahasiswa($id_jurnal, $id_mahasiswa, $status, $keterangan);
            
            if ($result['success']) {
                $message = $result['message'];
            } else {
                $error = $result['message'];
            }
        }
    }
}

// Get selected jurnal
if (isset($_GET['id_jurnal']) || isset($_POST['id_jurnal'])) {
    $id_jurnal = isset($_GET['id_jurnal']) ? intval($_GET['id_jurnal']) : intval($_POST['id_jurnal']);
    $mahasiswa_list = getMahasiswaByJurnal($id_jurnal);
    
    // Get jurnal details
    $jurnal_details = getJurnalByDosen($id_dosen, 100);
    foreach ($jurnal_details as $j) {
        if ($j['id_jurnal'] == $id_jurnal) {
            $selected_jurnal = $j;
            break;
        }
    }
}

// Get all jurnal for selection
$jurnal_list = getJurnalByDosen($id_dosen, 50);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Mahasiswa - SIPRES</title>
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
        .btn-success {
            background-color: #28a745;
            color: white;
        }
        .btn-warning {
            background-color: #ffc107;
            color: #212529;
        }
        .btn-danger {
            background-color: #dc3545;
            color: white;
        }
        .btn-info {
            background-color: #17a2b8;
            color: white;
        }
        .btn-small {
            padding: 5px 10px;
            font-size: 12px;
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
        .mahasiswa-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .mahasiswa-table th, .mahasiswa-table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        .mahasiswa-table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-badge.hadir {
            background-color: #28a745;
            color: white;
        }
        .status-badge.izin {
            background-color: #ffc107;
            color: #212529;
        }
        .status-badge.sakit {
            background-color: #17a2b8;
            color: white;
        }
        .status-badge.alpha {
            background-color: #dc3545;
            color: white;
        }
        .status-badge.belum {
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
        .session-info {
            background-color: #e7f3ff;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .session-info h4 {
            margin-top: 0;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 20px;
            border-radius: 8px;
            width: 80%;
            max-width: 500px;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover {
            color: black;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="header">
            <div class="header-left">
                <h1>👨‍🏫 SIPRES Dosen - Kelola Mahasiswa</h1>
                <p>Sistem Informasi Presensi - Kelola Status Mahasiswa</p>
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
            <a href="log_presensi.php">Log Presensi</a>
        </div>

        <?php if ($message): ?>
            <div class="message success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="info-card">
            <h3>📚 Pilih Sesi Perkuliahan</h3>
            <form method="GET">
                <div class="form-group">
                    <label for="id_jurnal">Sesi:</label>
                    <select name="id_jurnal" id="id_jurnal" onchange="this.form.submit()" required>
                        <option value="">Pilih Sesi Perkuliahan</option>
                        <?php foreach ($jurnal_list as $jurnal): ?>
                            <option value="<?php echo $jurnal['id_jurnal']; ?>" <?php echo (isset($_GET['id_jurnal']) && $_GET['id_jurnal'] == $jurnal['id_jurnal']) ? 'selected' : ''; ?>>
                                <?php echo date('d/m/Y', strtotime($jurnal['tanggal'])) . ' - ' . htmlspecialchars($jurnal['nama_mk']) . ' (' . $jurnal['status_sesi'] . ')'; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </div>

        <?php if ($selected_jurnal): ?>
            <div class="session-info">
                <h4>ℹ️ Informasi Sesi</h4>
                <p><strong>Mata Kuliah:</strong> <?php echo htmlspecialchars($selected_jurnal['nama_mk']); ?></p>
                <p><strong>Tanggal:</strong> <?php echo date('d/m/Y', strtotime($selected_jurnal['tanggal'])); ?></p>
                <p><strong>Jam:</strong> <?php echo $selected_jurnal['jam_masuk']; ?> - <?php echo $selected_jurnal['jam_keluar'] ?? 'Sedang Berlangsung'; ?></p>
                <p><strong>Token:</strong> <?php echo htmlspecialchars($selected_jurnal['token_presensi']); ?></p>
                <p><strong>Status Sesi:</strong> 
                    <span class="status-badge <?php echo strtolower($selected_jurnal['status_sesi']); ?>">
                        <?php echo $selected_jurnal['status_sesi']; ?>
                    </span>
                </p>
                <p><strong>Materi:</strong> <?php echo htmlspecialchars($selected_jurnal['materi_kuliah']); ?></p>
            </div>

            <h3>👥 Daftar Mahasiswa</h3>
            <p style="color: #666; font-size: 14px; margin-bottom: 10px;">
                <em>Catatan: Menampilkan mahasiswa yang sudah melakukan presensi untuk sesi ini. 
                Untuk menambah mahasiswa yang belum presensi (izin/sakit), mahasiswa harus presensi terlebih dahulu atau 
                dosen dapat menambahkannya secara manual melalui sistem database.</em>
            </p>
            <table class="mahasiswa-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>NIM</th>
                        <th>Nama</th>
                        <th>Jurusan</th>
                        <th>Waktu Scan</th>
                        <th>Status</th>
                        <th>Keterangan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    foreach ($mahasiswa_list as $mhs): 
                        $current_status = $mhs['status'] ?? 'Belum Absen';
                    ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><?php echo htmlspecialchars($mhs['nim']); ?></td>
                            <td><?php echo htmlspecialchars($mhs['nama_lengkap']); ?></td>
                            <td><?php echo htmlspecialchars($mhs['jurusan']); ?></td>
                            <td><?php echo $mhs['waktu_scan'] ? date('H:i:s', strtotime($mhs['waktu_scan'])) : '-'; ?></td>
                            <td>
                                <?php
                                $status_class = 'belum';
                                switch ($current_status) {
                                    case 'Hadir':
                                        $status_class = 'hadir';
                                        break;
                                    case 'Izin':
                                        $status_class = 'izin';
                                        break;
                                    case 'Sakit':
                                        $status_class = 'sakit';
                                        break;
                                    case 'Alpha':
                                        $status_class = 'alpha';
                                        break;
                                }
                                ?>
                                <span class="status-badge <?php echo $status_class; ?>">
                                    <?php echo $current_status; ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($mhs['keterangan'] ?? '-'); ?></td>
                            <td>
                                <button class="btn btn-success btn-small" onclick="showStatusModal(<?php echo $mhs['id_mahasiswa']; ?>, '<?php echo htmlspecialchars($mhs['nama_lengkap']); ?>', 'Hadir')">Hadir</button>
                                <button class="btn btn-warning btn-small" onclick="showStatusModal(<?php echo $mhs['id_mahasiswa']; ?>, '<?php echo htmlspecialchars($mhs['nama_lengkap']); ?>', 'Izin')">Izin</button>
                                <button class="btn btn-info btn-small" onclick="showStatusModal(<?php echo $mhs['id_mahasiswa']; ?>, '<?php echo htmlspecialchars($mhs['nama_lengkap']); ?>', 'Sakit')">Sakit</button>
                                <button class="btn btn-danger btn-small" onclick="showStatusModal(<?php echo $mhs['id_mahasiswa']; ?>, '<?php echo htmlspecialchars($mhs['nama_lengkap']); ?>', 'Alpha')">Alpha</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($mahasiswa_list)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center;">Tidak ada data mahasiswa</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- Modal for setting status -->
    <div id="statusModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h3>Set Status Mahasiswa</h3>
            <form method="POST">
                <input type="hidden" name="action" value="set_status">
                <input type="hidden" name="id_jurnal" value="<?php echo $selected_jurnal['id_jurnal'] ?? ''; ?>">
                <input type="hidden" name="id_mahasiswa" id="modal_id_mahasiswa">
                <input type="hidden" name="status" id="modal_status">
                
                <div class="form-group">
                    <label>Mahasiswa:</label>
                    <p id="modal_nama_mahasiswa"></p>
                </div>

                <div class="form-group">
                    <label>Status:</label>
                    <p id="modal_status_display"></p>
                </div>

                <div class="form-group">
                    <label for="keterangan">Keterangan (Opsional):</label>
                    <textarea name="keterangan" id="keterangan" placeholder="Tambahkan keterangan jika diperlukan"></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Simpan</button>
                <button type="button" class="btn" onclick="closeModal()">Batal</button>
            </form>
        </div>
    </div>

    <script>
        function showStatusModal(id_mahasiswa, nama_mahasiswa, status) {
            document.getElementById('modal_id_mahasiswa').value = id_mahasiswa;
            document.getElementById('modal_status').value = status;
            document.getElementById('modal_nama_mahasiswa').textContent = nama_mahasiswa;
            document.getElementById('modal_status_display').textContent = status;
            document.getElementById('statusModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('statusModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            var modal = document.getElementById('statusModal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>
