<?php
/**
 * Admin Matakuliah Management Page
 * Manage course rooms and assign to dosen via jadwal
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
    
    if ($action == 'add_matakuliah') {
        $result = addMatakuliah(
            trim($_POST['kode_mk']),
            trim($_POST['nama_mk']),
            intval($_POST['sks']),
            intval($_POST['semester'])
        );
        
        if ($result['success']) {
            $message = $result['message'];
        } else {
            $error = $result['message'];
        }
    } elseif ($action == 'edit_matakuliah') {
        $result = updateMatakuliah(
            intval($_POST['id_mk']),
            trim($_POST['kode_mk']),
            trim($_POST['nama_mk']),
            intval($_POST['sks']),
            intval($_POST['semester'])
        );
        
        if ($result['success']) {
            $message = $result['message'];
        } else {
            $error = $result['message'];
        }
    } elseif ($action == 'delete_matakuliah') {
        $result = deleteMatakuliah(intval($_POST['id_mk']));
        
        if ($result['success']) {
            $message = $result['message'];
        } else {
            $error = $result['message'];
        }
    }
}

// Get data
$matakuliah_list = getAllMatakuliah();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Mata Kuliah - SIPRES Admin</title>
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
        .btn-success {
            background: #28a745;
            color: white;
        }
        .btn-success:hover {
            background: #218838;
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
            margin: 10% auto;
            padding: 30px;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
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
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="header">
            <div class="header-left">
                <h1>🔐 SIPRES Admin</h1>
                <p>Kelola Mata Kuliah</p>
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

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="form-card">
            <h2>➕ Tambah Mata Kuliah Baru</h2>
            <form method="POST">
                <input type="hidden" name="action" value="add_matakuliah">
                <div class="form-row">
                    <div class="form-group">
                        <label for="kode_mk">Kode Mata Kuliah *</label>
                        <input type="text" id="kode_mk" name="kode_mk" required placeholder="IF101">
                    </div>
                    <div class="form-group">
                        <label for="nama_mk">Nama Mata Kuliah *</label>
                        <input type="text" id="nama_mk" name="nama_mk" required placeholder="Pemrograman Web">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="sks">SKS *</label>
                        <input type="number" id="sks" name="sks" required min="1" max="6" placeholder="3">
                    </div>
                    <div class="form-group">
                        <label for="semester">Semester *</label>
                        <input type="number" id="semester" name="semester" required min="1" max="8" placeholder="3">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Tambah Mata Kuliah</button>
            </form>
        </div>

        <div class="table-container">
            <h2>📚 Daftar Mata Kuliah</h2>
            <table>
                <thead>
                    <tr>
                        <th>Kode MK</th>
                        <th>Nama Mata Kuliah</th>
                        <th>SKS</th>
                        <th>Semester</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($matakuliah_list)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center;">Belum ada data mata kuliah</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($matakuliah_list as $mk): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($mk['kode_mk']); ?></td>
                                <td><?php echo htmlspecialchars($mk['nama_mk']); ?></td>
                                <td><?php echo htmlspecialchars($mk['sks']); ?></td>
                                <td><?php echo htmlspecialchars($mk['semester']); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-secondary" onclick="editMatakuliah(<?php echo htmlspecialchars(json_encode($mk)); ?>)">Edit</button>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Yakin ingin menghapus mata kuliah ini?');">
                                            <input type="hidden" name="action" value="delete_matakuliah">
                                            <input type="hidden" name="id_mk" value="<?php echo $mk['id_mk']; ?>">
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
            <h2>Edit Mata Kuliah</h2>
            <form method="POST">
                <input type="hidden" name="action" value="edit_matakuliah">
                <input type="hidden" id="edit_id_mk" name="id_mk">
                <div class="form-group">
                    <label for="edit_kode_mk">Kode Mata Kuliah *</label>
                    <input type="text" id="edit_kode_mk" name="kode_mk" required>
                </div>
                <div class="form-group">
                    <label for="edit_nama_mk">Nama Mata Kuliah *</label>
                    <input type="text" id="edit_nama_mk" name="nama_mk" required>
                </div>
                <div class="form-group">
                    <label for="edit_sks">SKS *</label>
                    <input type="number" id="edit_sks" name="sks" required min="1" max="6">
                </div>
                <div class="form-group">
                    <label for="edit_semester">Semester *</label>
                    <input type="number" id="edit_semester" name="semester" required min="1" max="8">
                </div>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Batal</button>
            </form>
        </div>
    </div>

    <script>
        function editMatakuliah(mk) {
            document.getElementById('edit_id_mk').value = mk.id_mk;
            document.getElementById('edit_kode_mk').value = mk.kode_mk;
            document.getElementById('edit_nama_mk').value = mk.nama_mk;
            document.getElementById('edit_sks').value = mk.sks;
            document.getElementById('edit_semester').value = mk.semester;
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
