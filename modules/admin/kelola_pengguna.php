<?php
/**
 * Admin User Management Page
 * Manage dosen and mahasiswa users
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
    
    if ($action == 'add_dosen') {
        $result = addDosen(
            $_POST['nidn'],
            $_POST['nama_lengkap'],
            $_POST['email'],
            $_POST['no_telp'],
            $_POST['username'],
            $_POST['password']
        );
        
        if ($result['success']) {
            $message = $result['message'];
        } else {
            $error = $result['message'];
        }
    } elseif ($action == 'add_mahasiswa') {
        $result = addMahasiswa(
            $_POST['nim'],
            $_POST['nama_lengkap'],
            $_POST['jurusan'],
            $_POST['angkatan'],
            $_POST['email'],
            $_POST['no_telp'],
            $_POST['username'],
            $_POST['password']
        );
        
        if ($result['success']) {
            $message = $result['message'];
        } else {
            $error = $result['message'];
        }
    } elseif ($action == 'edit_dosen') {
        $result = updateDosen(
            intval($_POST['id_dosen']),
            $_POST['nidn'],
            $_POST['nama_lengkap'],
            $_POST['email'],
            $_POST['no_telp'],
            $_POST['username']
        );
        
        if ($result['success']) {
            $message = $result['message'];
        } else {
            $error = $result['message'];
        }
    } elseif ($action == 'edit_mahasiswa') {
        $result = updateMahasiswa(
            intval($_POST['id_mahasiswa']),
            $_POST['nim'],
            $_POST['nama_lengkap'],
            $_POST['jurusan'],
            $_POST['angkatan'],
            $_POST['email'],
            $_POST['no_telp'],
            $_POST['username']
        );
        
        if ($result['success']) {
            $message = $result['message'];
        } else {
            $error = $result['message'];
        }
    } elseif ($action == 'delete_dosen') {
        $result = deleteDosen(intval($_POST['id_dosen']));
        
        if ($result['success']) {
            $message = $result['message'];
        } else {
            $error = $result['message'];
        }
    } elseif ($action == 'delete_mahasiswa') {
        $result = deleteMahasiswa(intval($_POST['id_mahasiswa']));
        
        if ($result['success']) {
            $message = $result['message'];
        } else {
            $error = $result['message'];
        }
    }
}

// Get data
$view = $_GET['view'] ?? 'dosen';
$edit_id = $_GET['edit'] ?? null;
$edit_data = null;

if ($edit_id) {
    if ($view == 'dosen') {
        $edit_data = getDosenById(intval($edit_id));
    } else {
        $edit_data = getMahasiswaById(intval($edit_id));
    }
}

$dosen_list = getAllDosen();
$mahasiswa_list = getAllMahasiswa();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pengguna - SIPRES Admin</title>
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
        .form-group input, .form-group select {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .form-group input:required, .form-group select:required {
            border-left: 3px solid #007bff;
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
        .btn-cancel {
            padding: 12px 30px;
            background: #6c757d;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
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
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        .btn-edit, .btn-delete {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
        }
        .btn-edit {
            background: #28a745;
            color: white;
        }
        .btn-edit:hover {
            background: #218838;
        }
        .btn-delete {
            background: #dc3545;
            color: white;
        }
        .btn-delete:hover {
            background: #c82333;
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
                <h1>👥 Kelola Pengguna</h1>
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

        <div class="stats-grid">
            <div class="stat-card">
                <h3><?php echo count($dosen_list); ?></h3>
                <p>Total Dosen</p>
            </div>
            <div class="stat-card">
                <h3><?php echo count($mahasiswa_list); ?></h3>
                <p>Total Mahasiswa</p>
            </div>
            <div class="stat-card">
                <h3><?php echo count($dosen_list) + count($mahasiswa_list); ?></h3>
                <p>Total Pengguna</p>
            </div>
        </div>

        <div class="view-toggle">
            <a href="?view=dosen" class="<?php echo $view == 'dosen' ? 'active' : ''; ?>">
                👨‍🏫 Dosen
            </a>
            <a href="?view=mahasiswa" class="<?php echo $view == 'mahasiswa' ? 'active' : ''; ?>">
                🎓 Mahasiswa
            </a>
        </div>

        <?php if ($view == 'dosen'): ?>
            <!-- Dosen Form -->
            <div class="form-card">
                <h2><?php echo $edit_data ? '✏️ Edit Dosen' : '➕ Tambah Dosen Baru'; ?></h2>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="<?php echo $edit_data ? 'edit_dosen' : 'add_dosen'; ?>">
                    <?php if ($edit_data): ?>
                        <input type="hidden" name="id_dosen" value="<?php echo $edit_data['id_dosen']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label>NIDN: *</label>
                            <input type="text" name="nidn" required value="<?php echo $edit_data ? htmlspecialchars($edit_data['nidn']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label>Nama Lengkap: *</label>
                            <input type="text" name="nama_lengkap" required value="<?php echo $edit_data ? htmlspecialchars($edit_data['nama_lengkap']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label>Email:</label>
                            <input type="email" name="email" value="<?php echo $edit_data ? htmlspecialchars($edit_data['email']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label>No. Telepon:</label>
                            <input type="text" name="no_telp" value="<?php echo $edit_data ? htmlspecialchars($edit_data['no_telp']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label>Username: *</label>
                            <input type="text" name="username" required value="<?php echo $edit_data ? htmlspecialchars($edit_data['username']) : ''; ?>">
                        </div>
                        <?php if (!$edit_data): ?>
                            <div class="form-group">
                                <label>Password: *</label>
                                <input type="password" name="password" required minlength="6">
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <button type="submit" class="btn-submit">
                        <?php echo $edit_data ? '💾 Update Dosen' : '➕ Tambah Dosen'; ?>
                    </button>
                    <?php if ($edit_data): ?>
                        <a href="?view=dosen" class="btn-cancel">Batal</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Dosen List -->
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>NIDN</th>
                            <th>Nama Lengkap</th>
                            <th>Email</th>
                            <th>No. Telepon</th>
                            <th>Username</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dosen_list as $dosen): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($dosen['nidn']); ?></td>
                                <td><?php echo htmlspecialchars($dosen['nama_lengkap']); ?></td>
                                <td><?php echo htmlspecialchars($dosen['email'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($dosen['no_telp'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($dosen['username']); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="?view=dosen&edit=<?php echo $dosen['id_dosen']; ?>" class="btn-edit">Edit</a>
                                        <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Yakin ingin menghapus dosen ini?');">
                                            <input type="hidden" name="action" value="delete_dosen">
                                            <input type="hidden" name="id_dosen" value="<?php echo $dosen['id_dosen']; ?>">
                                            <button type="submit" class="btn-delete">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <?php else: ?>
            <!-- Mahasiswa Form -->
            <div class="form-card">
                <h2><?php echo $edit_data ? '✏️ Edit Mahasiswa' : '➕ Tambah Mahasiswa Baru'; ?></h2>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="<?php echo $edit_data ? 'edit_mahasiswa' : 'add_mahasiswa'; ?>">
                    <?php if ($edit_data): ?>
                        <input type="hidden" name="id_mahasiswa" value="<?php echo $edit_data['id_mahasiswa']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label>NIM: *</label>
                            <input type="text" name="nim" required value="<?php echo $edit_data ? htmlspecialchars($edit_data['nim']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label>Nama Lengkap: *</label>
                            <input type="text" name="nama_lengkap" required value="<?php echo $edit_data ? htmlspecialchars($edit_data['nama_lengkap']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label>Jurusan: *</label>
                            <input type="text" name="jurusan" required value="<?php echo $edit_data ? htmlspecialchars($edit_data['jurusan']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label>Angkatan: *</label>
                            <input type="text" name="angkatan" required value="<?php echo $edit_data ? htmlspecialchars($edit_data['angkatan']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label>Email:</label>
                            <input type="email" name="email" value="<?php echo $edit_data ? htmlspecialchars($edit_data['email']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label>No. Telepon:</label>
                            <input type="text" name="no_telp" value="<?php echo $edit_data ? htmlspecialchars($edit_data['no_telp']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label>Username: *</label>
                            <input type="text" name="username" required value="<?php echo $edit_data ? htmlspecialchars($edit_data['username']) : ''; ?>">
                        </div>
                        <?php if (!$edit_data): ?>
                            <div class="form-group">
                                <label>Password: *</label>
                                <input type="password" name="password" required minlength="6">
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <button type="submit" class="btn-submit">
                        <?php echo $edit_data ? '💾 Update Mahasiswa' : '➕ Tambah Mahasiswa'; ?>
                    </button>
                    <?php if ($edit_data): ?>
                        <a href="?view=mahasiswa" class="btn-cancel">Batal</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Mahasiswa List -->
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>NIM</th>
                            <th>Nama Lengkap</th>
                            <th>Jurusan</th>
                            <th>Angkatan</th>
                            <th>Email</th>
                            <th>No. Telepon</th>
                            <th>Username</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($mahasiswa_list as $mhs): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($mhs['nim']); ?></td>
                                <td><?php echo htmlspecialchars($mhs['nama_lengkap']); ?></td>
                                <td><?php echo htmlspecialchars($mhs['jurusan']); ?></td>
                                <td><?php echo htmlspecialchars($mhs['angkatan']); ?></td>
                                <td><?php echo htmlspecialchars($mhs['email'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($mhs['no_telp'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($mhs['username']); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="?view=mahasiswa&edit=<?php echo $mhs['id_mahasiswa']; ?>" class="btn-edit">Edit</a>
                                        <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Yakin ingin menghapus mahasiswa ini?');">
                                            <input type="hidden" name="action" value="delete_mahasiswa">
                                            <input type="hidden" name="id_mahasiswa" value="<?php echo $mhs['id_mahasiswa']; ?>">
                                            <button type="submit" class="btn-delete">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
