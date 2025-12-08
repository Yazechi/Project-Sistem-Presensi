<?php
/**
 * Mahasiswa Absensi Page
 * Page for students to submit attendance using token
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

$success_message = '';
$error_message = '';

// Get mahasiswa ID from user_id
$conn = getDBConnection();
$mahasiswa_query = "SELECT id_mahasiswa, nim, nama_lengkap FROM mahasiswa WHERE user_id = ?";
$stmt = $conn->prepare($mahasiswa_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Error: Mahasiswa profile not found");
}

$mahasiswa_data = $result->fetch_assoc();
$id_mahasiswa = $mahasiswa_data['id_mahasiswa'];
$nim = $mahasiswa_data['nim'];
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['token'])) {
    $token = trim($_POST['token']);
    
    if (empty($token)) {
        $error_message = "Token tidak boleh kosong!";
    } else {
        // Check if token exists and session is open
        $token_query = "SELECT jp.id_jurnal, jp.status_sesi, jp.tanggal, jp.jam_masuk, 
                               mk.nama_mk, d.nama_lengkap as nama_dosen, jk.ruangan
                        FROM jurnal_perkuliahan jp
                        JOIN jadwal_kuliah jk ON jp.id_jadwal = jk.id_jadwal
                        JOIN matakuliah mk ON jk.id_mk = mk.id_mk
                        JOIN dosen d ON jk.id_dosen = d.id_dosen
                        WHERE jp.token_presensi = ?";
        $stmt = $conn->prepare($token_query);
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $error_message = "Token tidak valid!";
        } else {
            $jurnal = $result->fetch_assoc();
            
            if ($jurnal['status_sesi'] !== 'Open') {
                $error_message = "Sesi presensi sudah ditutup!";
            } else {
                // Check if already attended
                $check_query = "SELECT id_presensi FROM presensi_mahasiswa 
                               WHERE id_jurnal = ? AND id_mahasiswa = ?";
                $check_stmt = $conn->prepare($check_query);
                $check_stmt->bind_param("ii", $jurnal['id_jurnal'], $id_mahasiswa);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                
                if ($check_result->num_rows > 0) {
                    $error_message = "Anda sudah melakukan presensi untuk sesi ini!";
                } else {
                    // Insert attendance
                    $insert_query = "INSERT INTO presensi_mahasiswa (id_jurnal, id_mahasiswa, status) 
                                    VALUES (?, ?, 'Hadir')";
                    $insert_stmt = $conn->prepare($insert_query);
                    $insert_stmt->bind_param("ii", $jurnal['id_jurnal'], $id_mahasiswa);
                    
                    if ($insert_stmt->execute()) {
                        $success_message = "Presensi berhasil! Mata Kuliah: {$jurnal['nama_mk']}, Dosen: {$jurnal['nama_dosen']}";
                    } else {
                        $error_message = "Gagal menyimpan presensi: " . $insert_stmt->error;
                    }
                    $insert_stmt->close();
                }
                $check_stmt->close();
            }
        }
        $stmt->close();
    }
}

// Get recent attendance records for this student
$recent_query = "SELECT pm.waktu_scan, pm.status, mk.nama_mk, d.nama_lengkap as nama_dosen,
                        jp.tanggal, jp.jam_masuk, jp.jam_keluar, jk.ruangan
                 FROM presensi_mahasiswa pm
                 JOIN jurnal_perkuliahan jp ON pm.id_jurnal = jp.id_jurnal
                 JOIN jadwal_kuliah jk ON jp.id_jadwal = jk.id_jadwal
                 JOIN matakuliah mk ON jk.id_mk = mk.id_mk
                 JOIN dosen d ON jk.id_dosen = d.id_dosen
                 WHERE pm.id_mahasiswa = ?
                 ORDER BY pm.waktu_scan DESC
                 LIMIT 5";
$stmt = $conn->prepare($recent_query);
$stmt->bind_param("i", $id_mahasiswa);
$stmt->execute();
$recent_attendance = $stmt->get_result();
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absensi Mahasiswa - SIPRES</title>
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <style>
        .form-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            text-transform: uppercase;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .btn-submit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
        }
        
        .btn-submit:hover {
            opacity: 0.9;
        }
        
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        
        .alert-error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        
        .attendance-table {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        
        .status-badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .status-hadir {
            background: #d4edda;
            color: #155724;
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
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="header">
            <div class="header-left">
                <h1>👨‍🎓 SIPRES Mahasiswa</h1>
                <p>Sistem Informasi Presensi - Absensi</p>
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
        
        <div class="form-container">
            <h2>📝 Form Absensi</h2>
            <p>Masukkan token yang diberikan oleh dosen untuk melakukan presensi.</p>
            
            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    ✓ <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="alert alert-error">
                    ✗ <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="token">Token Presensi</label>
                    <input type="text" id="token" name="token" maxlength="6" 
                           placeholder="Contoh: ABC123" required>
                    <small>Token terdiri dari 6 karakter yang diberikan oleh dosen</small>
                </div>
                
                <button type="submit" class="btn-submit">Submit Presensi</button>
            </form>
        </div>
        
        <div class="attendance-table">
            <h2>📊 Riwayat Presensi Terakhir</h2>
            
            <?php if ($recent_attendance->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Waktu Scan</th>
                            <th>Mata Kuliah</th>
                            <th>Dosen</th>
                            <th>Ruangan</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $recent_attendance->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo date('d/m/Y', strtotime($row['tanggal'])); ?></td>
                                <td><?php echo date('H:i', strtotime($row['waktu_scan'])); ?></td>
                                <td><?php echo htmlspecialchars($row['nama_mk']); ?></td>
                                <td><?php echo htmlspecialchars($row['nama_dosen']); ?></td>
                                <td><?php echo htmlspecialchars($row['ruangan']); ?></td>
                                <td>
                                    <span class="status-badge status-hadir">
                                        <?php echo htmlspecialchars($row['status']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Belum ada riwayat presensi.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
