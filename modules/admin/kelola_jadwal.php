<?php
/**
 * Admin Jadwal Management Page
 * Manage course schedules and assign dosen to courses
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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action == 'add_jadwal') {
        $result = addJadwalKuliah(
            intval($_POST['id_mk']),
            intval($_POST['id_dosen']),
            $_POST['hari'],
            $_POST['jam_mulai'],
            $_POST['jam_selesai'],
            trim($_POST['ruangan'])
        );
        
        if ($result['success']) {
            $message = $result['message'];
        } else {
            $error = $result['message'];
        }
    } elseif ($action == 'edit_jadwal') {
        $result = updateJadwalKuliah(
            intval($_POST['id_jadwal']),
            intval($_POST['id_mk']),
            intval($_POST['id_dosen']),
            $_POST['hari'],
            $_POST['jam_mulai'],
            $_POST['jam_selesai'],
            trim($_POST['ruangan'])
        );
        
        if ($result['success']) {
            $message = $result['message'];
        } else {
            $error = $result['message'];
        }
    } elseif ($action == 'delete_jadwal') {
        $result = deleteJadwalKuliah(intval($_POST['id_jadwal']));
        
        if ($result['success']) {
            $message = $result['message'];
        } else {
            $error = $result['message'];
        }
    }
}

// Get data
$jadwal_list = getAllJadwal();
$matakuliah_list = getAllMatakuliah();
$dosen_list = getAllDosen();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Jadwal - SIPRES Admin</title>
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
        .form-card {
            background: white;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
        }
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin-right: 10px;
            text-decoration: none;
            display: inline-block;
        }
        .btn-primary {
            background: #007bff;
            color: white;
        }
        .btn-primary:hover {
            background: #0056b3;
        }
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        .btn-secondary:hover {
            background: #545b62;
        }
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        .btn-danger:hover {
            background: #c82333;
        }
        .table-container {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #f8f9fa;
            font-weight: bold;
            color: #333;
        }
        tr:hover {
            background: #f8f9fa;
        }
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        .action-buttons button {
            padding: 5px 10px;
            font-size: 12px;
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
            background-color: #fefefe;
            margin: 5% auto;
            padding: 30px;
            border-radius: 8px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.3);
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover {
            color: #000;
        }
        .info-box {
            background: #e7f3ff;
            border: 1px solid #b3d9ff;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .info-box h4 {
            margin: 0 0 10px 0;
            color: #004085;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="header">
            <div class="header-left">
                <h1>🔐 SIPRES Admin</h1>
                <p>Kelola Jadwal Kuliah & Assignment Dosen</p>
            </div>
            <div class="header-right">
                <div class="user-info">
                    <div class="user-name"><?php echo htmlspecialchars($nama); ?></div>
                    <div class="user-role">
                        <span class="role-badge admin">Admin</span>
                    </div>
                </div>
                <a href="/auth/logout.php" class="btn-logout">Logout</a>
            </div>
        </div>

        <a href="/modules/admin/dashboard.php" class="back-link">← Kembali ke Dashboard</a>

        <div class="info-box">
            <h4>ℹ️ Informasi</h4>
            <p>Halaman ini digunakan untuk membuat jadwal kuliah dan mengassign dosen ke mata kuliah tertentu. Setiap jadwal akan menjadi "room" tempat dosen membuka sesi dan mahasiswa melakukan presensi.</p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="form-card">
            <h2>➕ Tambah Jadwal Baru</h2>
            <form method="POST">
                <input type="hidden" name="action" value="add_jadwal">
                <div class="form-row">
                    <div class="form-group">
                        <label for="id_mk">Mata Kuliah *</label>
                        <select id="id_mk" name="id_mk" required>
                            <option value="">Pilih Mata Kuliah</option>
                            <?php foreach ($matakuliah_list as $mk): ?>
                                <option value="<?php echo $mk['id_mk']; ?>">
                                    <?php echo htmlspecialchars($mk['kode_mk'] . ' - ' . $mk['nama_mk']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="id_dosen">Dosen *</label>
                        <select id="id_dosen" name="id_dosen" required>
                            <option value="">Pilih Dosen</option>
                            <?php foreach ($dosen_list as $dosen): ?>
                                <option value="<?php echo $dosen['id_dosen']; ?>">
                                    <?php echo htmlspecialchars($dosen['nama_lengkap'] . ' (' . $dosen['nidn'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="hari">Hari *</label>
                        <select id="hari" name="hari" required>
                            <option value="">Pilih Hari</option>
                            <option value="Senin">Senin</option>
                            <option value="Selasa">Selasa</option>
                            <option value="Rabu">Rabu</option>
                            <option value="Kamis">Kamis</option>
                            <option value="Jumat">Jumat</option>
                            <option value="Sabtu">Sabtu</option>
                            <option value="Minggu">Minggu</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="ruangan">Ruangan *</label>
                        <input type="text" id="ruangan" name="ruangan" required placeholder="Lab Komputer 1">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="jam_mulai">Jam Mulai *</label>
                        <input type="time" id="jam_mulai" name="jam_mulai" required>
                    </div>
                    <div class="form-group">
                        <label for="jam_selesai">Jam Selesai *</label>
                        <input type="time" id="jam_selesai" name="jam_selesai" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Tambah Jadwal</button>
            </form>
        </div>

        <div class="table-container">
            <h2>📅 Daftar Jadwal Kuliah</h2>
            <table>
                <thead>
                    <tr>
                        <th>Mata Kuliah</th>
                        <th>Dosen</th>
                        <th>Hari</th>
                        <th>Waktu</th>
                        <th>Ruangan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($jadwal_list)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center;">Belum ada jadwal kuliah</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($jadwal_list as $jadwal): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($jadwal['nama_mk']); ?></strong><br>
                                    <small><?php echo htmlspecialchars($jadwal['kode_mk']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($jadwal['nama_dosen']); ?></td>
                                <td><?php echo htmlspecialchars($jadwal['hari']); ?></td>
                                <td><?php echo htmlspecialchars(substr($jadwal['jam_mulai'], 0, 5) . ' - ' . substr($jadwal['jam_selesai'], 0, 5)); ?></td>
                                <td><?php echo htmlspecialchars($jadwal['ruangan']); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-secondary" onclick="editJadwal(<?php echo htmlspecialchars(json_encode($jadwal)); ?>)">Edit</button>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Yakin ingin menghapus jadwal ini?');">
                                            <input type="hidden" name="action" value="delete_jadwal">
                                            <input type="hidden" name="id_jadwal" value="<?php echo $jadwal['id_jadwal']; ?>">
                                            <button type="submit" class="btn btn-danger">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditModal()">&times;</span>
            <h2>Edit Jadwal</h2>
            <form method="POST">
                <input type="hidden" name="action" value="edit_jadwal">
                <input type="hidden" id="edit_id_jadwal" name="id_jadwal">
                <div class="form-group">
                    <label for="edit_id_mk">Mata Kuliah *</label>
                    <select id="edit_id_mk" name="id_mk" required>
                        <option value="">Pilih Mata Kuliah</option>
                        <?php foreach ($matakuliah_list as $mk): ?>
                            <option value="<?php echo $mk['id_mk']; ?>">
                                <?php echo htmlspecialchars($mk['kode_mk'] . ' - ' . $mk['nama_mk']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="edit_id_dosen">Dosen *</label>
                    <select id="edit_id_dosen" name="id_dosen" required>
                        <option value="">Pilih Dosen</option>
                        <?php foreach ($dosen_list as $dosen): ?>
                            <option value="<?php echo $dosen['id_dosen']; ?>">
                                <?php echo htmlspecialchars($dosen['nama_lengkap'] . ' (' . $dosen['nidn'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="edit_hari">Hari *</label>
                    <select id="edit_hari" name="hari" required>
                        <option value="">Pilih Hari</option>
                        <option value="Senin">Senin</option>
                        <option value="Selasa">Selasa</option>
                        <option value="Rabu">Rabu</option>
                        <option value="Kamis">Kamis</option>
                        <option value="Jumat">Jumat</option>
                        <option value="Sabtu">Sabtu</option>
                        <option value="Minggu">Minggu</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="edit_ruangan">Ruangan *</label>
                    <input type="text" id="edit_ruangan" name="ruangan" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_jam_mulai">Jam Mulai *</label>
                        <input type="time" id="edit_jam_mulai" name="jam_mulai" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_jam_selesai">Jam Selesai *</label>
                        <input type="time" id="edit_jam_selesai" name="jam_selesai" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Batal</button>
            </form>
        </div>
    </div>

    <script>
        function editJadwal(jadwal) {
            document.getElementById('edit_id_jadwal').value = jadwal.id_jadwal;
            document.getElementById('edit_id_mk').value = jadwal.id_mk;
            document.getElementById('edit_id_dosen').value = jadwal.id_dosen;
            document.getElementById('edit_hari').value = jadwal.hari;
            document.getElementById('edit_ruangan').value = jadwal.ruangan;
            document.getElementById('edit_jam_mulai').value = jadwal.jam_mulai;
            document.getElementById('edit_jam_selesai').value = jadwal.jam_selesai;
            document.getElementById('editModal').style.display = 'block';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('editModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>
