<?php
/**
 * Admin Functions
 * Helper functions for admin operations
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Get all attendance records with filters
 */
function getAllAttendanceRecords($start_date = null, $end_date = null, $search = '') {
    try {
        $conn = getDBConnection();
        
        $query = "SELECT 
                    jp.id_jurnal,
                    jp.tanggal,
                    jp.jam_masuk,
                    jp.jam_keluar,
                    jp.materi_kuliah,
                    jp.token_presensi,
                    jp.status_sesi,
                    mk.kode_mk,
                    mk.nama_mk,
                    d.nama_lengkap as nama_dosen,
                    d.nidn,
                    jk.ruangan,
                    COUNT(DISTINCT pm.id_presensi) as jumlah_hadir
                  FROM jurnal_perkuliahan jp
                  JOIN jadwal_kuliah jk ON jp.id_jadwal = jk.id_jadwal
                  JOIN matakuliah mk ON jk.id_mk = mk.id_mk
                  JOIN dosen d ON jk.id_dosen = d.id_dosen
                  LEFT JOIN presensi_mahasiswa pm ON jp.id_jurnal = pm.id_jurnal AND pm.status = 'Hadir'
                  WHERE 1=1";
        
        $params = [];
        $types = "";
        
        if ($start_date) {
            $query .= " AND jp.tanggal >= ?";
            $params[] = $start_date;
            $types .= "s";
        }
        
        if ($end_date) {
            $query .= " AND jp.tanggal <= ?";
            $params[] = $end_date;
            $types .= "s";
        }
        
        if ($search) {
            $query .= " AND (mk.nama_mk LIKE ? OR d.nama_lengkap LIKE ? OR mk.kode_mk LIKE ?)";
            $search_param = "%$search%";
            $params[] = $search_param;
            $params[] = $search_param;
            $params[] = $search_param;
            $types .= "sss";
        }
        
        $query .= " GROUP BY jp.id_jurnal ORDER BY jp.tanggal DESC, jp.jam_masuk DESC";
        
        $stmt = $conn->prepare($query);
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $records = $result->fetch_all(MYSQLI_ASSOC);
        
        $stmt->close();
        $conn->close();
        
        return $records;
    } catch (Exception $e) {
        error_log("Error getting attendance records: " . $e->getMessage());
        return [];
    }
}

/**
 * Get all student attendance records
 */
function getAllStudentAttendance($start_date = null, $end_date = null, $search = '') {
    try {
        $conn = getDBConnection();
        
        $query = "SELECT 
                    pm.id_presensi,
                    pm.waktu_scan,
                    pm.status,
                    pm.keterangan,
                    m.nim,
                    m.nama_lengkap as nama_mahasiswa,
                    m.jurusan,
                    mk.kode_mk,
                    mk.nama_mk,
                    jp.tanggal,
                    jp.jam_masuk,
                    jp.jam_keluar,
                    d.nama_lengkap as nama_dosen
                  FROM presensi_mahasiswa pm
                  JOIN mahasiswa m ON pm.id_mahasiswa = m.id_mahasiswa
                  JOIN jurnal_perkuliahan jp ON pm.id_jurnal = jp.id_jurnal
                  JOIN jadwal_kuliah jk ON jp.id_jadwal = jk.id_jadwal
                  JOIN matakuliah mk ON jk.id_mk = mk.id_mk
                  JOIN dosen d ON jk.id_dosen = d.id_dosen
                  WHERE 1=1";
        
        $params = [];
        $types = "";
        
        if ($start_date) {
            $query .= " AND jp.tanggal >= ?";
            $params[] = $start_date;
            $types .= "s";
        }
        
        if ($end_date) {
            $query .= " AND jp.tanggal <= ?";
            $params[] = $end_date;
            $types .= "s";
        }
        
        if ($search) {
            $query .= " AND (m.nama_lengkap LIKE ? OR m.nim LIKE ? OR mk.nama_mk LIKE ?)";
            $search_param = "%$search%";
            $params[] = $search_param;
            $params[] = $search_param;
            $params[] = $search_param;
            $types .= "sss";
        }
        
        $query .= " ORDER BY jp.tanggal DESC, pm.waktu_scan DESC";
        
        $stmt = $conn->prepare($query);
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $records = $result->fetch_all(MYSQLI_ASSOC);
        
        $stmt->close();
        $conn->close();
        
        return $records;
    } catch (Exception $e) {
        error_log("Error getting student attendance: " . $e->getMessage());
        return [];
    }
}

/**
 * Get all dosen
 */
function getAllDosen() {
    try {
        $conn = getDBConnection();
        
        $query = "SELECT d.*, u.username 
                  FROM dosen d
                  JOIN users u ON d.user_id = u.id_user
                  ORDER BY d.nama_lengkap";
        
        $result = $conn->query($query);
        $dosen = $result->fetch_all(MYSQLI_ASSOC);
        
        $conn->close();
        return $dosen;
    } catch (Exception $e) {
        error_log("Error getting dosen list: " . $e->getMessage());
        return [];
    }
}

/**
 * Get all mahasiswa
 */
function getAllMahasiswa() {
    try {
        $conn = getDBConnection();
        
        $query = "SELECT m.*, u.username 
                  FROM mahasiswa m
                  JOIN users u ON m.user_id = u.id_user
                  ORDER BY m.nama_lengkap";
        
        $result = $conn->query($query);
        $mahasiswa = $result->fetch_all(MYSQLI_ASSOC);
        
        $conn->close();
        return $mahasiswa;
    } catch (Exception $e) {
        error_log("Error getting mahasiswa list: " . $e->getMessage());
        return [];
    }
}

/**
 * Get dosen by ID
 */
function getDosenById($id_dosen) {
    try {
        $conn = getDBConnection();
        
        $query = "SELECT d.*, u.username 
                  FROM dosen d
                  JOIN users u ON d.user_id = u.id_user
                  WHERE d.id_dosen = ?";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id_dosen);
        $stmt->execute();
        $result = $stmt->get_result();
        $dosen = $result->fetch_assoc();
        
        $stmt->close();
        $conn->close();
        
        return $dosen;
    } catch (Exception $e) {
        error_log("Error getting dosen: " . $e->getMessage());
        return null;
    }
}

/**
 * Get mahasiswa by ID
 */
function getMahasiswaById($id_mahasiswa) {
    try {
        $conn = getDBConnection();
        
        $query = "SELECT m.*, u.username 
                  FROM mahasiswa m
                  JOIN users u ON m.user_id = u.id_user
                  WHERE m.id_mahasiswa = ?";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id_mahasiswa);
        $stmt->execute();
        $result = $stmt->get_result();
        $mahasiswa = $result->fetch_assoc();
        
        $stmt->close();
        $conn->close();
        
        return $mahasiswa;
    } catch (Exception $e) {
        error_log("Error getting mahasiswa: " . $e->getMessage());
        return null;
    }
}

/**
 * Add new dosen
 */
function addDosen($nidn, $nama_lengkap, $email, $no_telp, $username, $password) {
    try {
        $conn = getDBConnection();
        
        // Start transaction
        $conn->begin_transaction();
        
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        
        // Insert user
        $query = "INSERT INTO users (username, password, role) VALUES (?, ?, 'dosen')";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $username, $hashed_password);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to create user account");
        }
        
        $user_id = $conn->insert_id;
        $stmt->close();
        
        // Insert dosen profile
        $query = "INSERT INTO dosen (nidn, nama_lengkap, email, no_telp, user_id) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssi", $nidn, $nama_lengkap, $email, $no_telp, $user_id);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to create dosen profile");
        }
        
        $stmt->close();
        $conn->commit();
        $conn->close();
        
        return ['success' => true, 'message' => 'Dosen berhasil ditambahkan'];
    } catch (Exception $e) {
        if (isset($conn)) {
            $conn->rollback();
            $conn->close();
        }
        error_log("Error adding dosen: " . $e->getMessage());
        return ['success' => false, 'message' => 'Gagal menambahkan dosen: ' . $e->getMessage()];
    }
}

/**
 * Add new mahasiswa
 */
function addMahasiswa($nim, $nama_lengkap, $jurusan, $angkatan, $email, $no_telp, $username, $password) {
    try {
        $conn = getDBConnection();
        
        // Start transaction
        $conn->begin_transaction();
        
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        
        // Insert user
        $query = "INSERT INTO users (username, password, role) VALUES (?, ?, 'mhs')";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $username, $hashed_password);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to create user account");
        }
        
        $user_id = $conn->insert_id;
        $stmt->close();
        
        // Insert mahasiswa profile
        $query = "INSERT INTO mahasiswa (nim, nama_lengkap, jurusan, angkatan, email, no_telp, user_id) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssssi", $nim, $nama_lengkap, $jurusan, $angkatan, $email, $no_telp, $user_id);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to create mahasiswa profile");
        }
        
        $stmt->close();
        $conn->commit();
        $conn->close();
        
        return ['success' => true, 'message' => 'Mahasiswa berhasil ditambahkan'];
    } catch (Exception $e) {
        if (isset($conn)) {
            $conn->rollback();
            $conn->close();
        }
        error_log("Error adding mahasiswa: " . $e->getMessage());
        return ['success' => false, 'message' => 'Gagal menambahkan mahasiswa: ' . $e->getMessage()];
    }
}

/**
 * Update dosen
 */
function updateDosen($id_dosen, $nidn, $nama_lengkap, $email, $no_telp, $username) {
    try {
        $conn = getDBConnection();
        
        // Start transaction
        $conn->begin_transaction();
        
        // Get user_id
        $query = "SELECT user_id FROM dosen WHERE id_dosen = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id_dosen);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $user_id = $row['user_id'];
        $stmt->close();
        
        // Update user
        $query = "UPDATE users SET username = ? WHERE id_user = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $username, $user_id);
        $stmt->execute();
        $stmt->close();
        
        // Update dosen profile
        $query = "UPDATE dosen SET nidn = ?, nama_lengkap = ?, email = ?, no_telp = ? WHERE id_dosen = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssi", $nidn, $nama_lengkap, $email, $no_telp, $id_dosen);
        $stmt->execute();
        $stmt->close();
        
        $conn->commit();
        $conn->close();
        
        return ['success' => true, 'message' => 'Dosen berhasil diupdate'];
    } catch (Exception $e) {
        if (isset($conn)) {
            $conn->rollback();
            $conn->close();
        }
        error_log("Error updating dosen: " . $e->getMessage());
        return ['success' => false, 'message' => 'Gagal mengupdate dosen: ' . $e->getMessage()];
    }
}

/**
 * Update mahasiswa
 */
function updateMahasiswa($id_mahasiswa, $nim, $nama_lengkap, $jurusan, $angkatan, $email, $no_telp, $username) {
    try {
        $conn = getDBConnection();
        
        // Start transaction
        $conn->begin_transaction();
        
        // Get user_id
        $query = "SELECT user_id FROM mahasiswa WHERE id_mahasiswa = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id_mahasiswa);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $user_id = $row['user_id'];
        $stmt->close();
        
        // Update user
        $query = "UPDATE users SET username = ? WHERE id_user = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $username, $user_id);
        $stmt->execute();
        $stmt->close();
        
        // Update mahasiswa profile
        $query = "UPDATE mahasiswa SET nim = ?, nama_lengkap = ?, jurusan = ?, angkatan = ?, email = ?, no_telp = ? WHERE id_mahasiswa = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssssi", $nim, $nama_lengkap, $jurusan, $angkatan, $email, $no_telp, $id_mahasiswa);
        $stmt->execute();
        $stmt->close();
        
        $conn->commit();
        $conn->close();
        
        return ['success' => true, 'message' => 'Mahasiswa berhasil diupdate'];
    } catch (Exception $e) {
        if (isset($conn)) {
            $conn->rollback();
            $conn->close();
        }
        error_log("Error updating mahasiswa: " . $e->getMessage());
        return ['success' => false, 'message' => 'Gagal mengupdate mahasiswa: ' . $e->getMessage()];
    }
}

/**
 * Delete dosen
 */
function deleteDosen($id_dosen) {
    try {
        $conn = getDBConnection();
        
        // Get user_id first
        $query = "SELECT user_id FROM dosen WHERE id_dosen = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id_dosen);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $user_id = $row['user_id'];
        $stmt->close();
        
        // Delete user (cascade will delete dosen profile)
        $query = "DELETE FROM users WHERE id_user = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();
        
        $conn->close();
        
        return ['success' => true, 'message' => 'Dosen berhasil dihapus'];
    } catch (Exception $e) {
        if (isset($conn)) {
            $conn->close();
        }
        error_log("Error deleting dosen: " . $e->getMessage());
        return ['success' => false, 'message' => 'Gagal menghapus dosen: ' . $e->getMessage()];
    }
}

/**
 * Delete mahasiswa
 */
function deleteMahasiswa($id_mahasiswa) {
    try {
        $conn = getDBConnection();
        
        // Get user_id first
        $query = "SELECT user_id FROM mahasiswa WHERE id_mahasiswa = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id_mahasiswa);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $user_id = $row['user_id'];
        $stmt->close();
        
        // Delete user (cascade will delete mahasiswa profile)
        $query = "DELETE FROM users WHERE id_user = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();
        
        $conn->close();
        
        return ['success' => true, 'message' => 'Mahasiswa berhasil dihapus'];
    } catch (Exception $e) {
        if (isset($conn)) {
            $conn->close();
        }
        error_log("Error deleting mahasiswa: " . $e->getMessage());
        return ['success' => false, 'message' => 'Gagal menghapus mahasiswa: ' . $e->getMessage()];
    }
}

/**
 * Get all matakuliah
 */
function getAllMatakuliah() {
    try {
        $conn = getDBConnection();
        
        $query = "SELECT * FROM matakuliah ORDER BY nama_mk";
        $result = $conn->query($query);
        $matakuliah = $result->fetch_all(MYSQLI_ASSOC);
        
        $conn->close();
        return $matakuliah;
    } catch (Exception $e) {
        error_log("Error getting matakuliah: " . $e->getMessage());
        return [];
    }
}

/**
 * Get all jadwal
 */
function getAllJadwal() {
    try {
        $conn = getDBConnection();
        
        $query = "SELECT jk.*, mk.kode_mk, mk.nama_mk, d.nama_lengkap as nama_dosen
                  FROM jadwal_kuliah jk
                  JOIN matakuliah mk ON jk.id_mk = mk.id_mk
                  JOIN dosen d ON jk.id_dosen = d.id_dosen
                  ORDER BY jk.hari, jk.jam_mulai";
        
        $result = $conn->query($query);
        $jadwal = $result->fetch_all(MYSQLI_ASSOC);
        
        $conn->close();
        return $jadwal;
    } catch (Exception $e) {
        error_log("Error getting jadwal: " . $e->getMessage());
        return [];
    }
}

/**
 * Generate random token
 */
function generateToken() {
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $token = '';
    $max = strlen($characters) - 1;
    for ($i = 0; $i < 6; $i++) {
        $token .= $characters[random_int(0, $max)];
    }
    return $token;
}

/**
 * Create forum absensi (jurnal perkuliahan)
 */
function createForumAbsensi($id_jadwal, $tanggal, $jam_masuk, $materi_kuliah) {
    try {
        $conn = getDBConnection();
        
        // Generate unique token
        $token = generateToken();
        
        // Check if token already exists today
        $check_query = "SELECT id_jurnal FROM jurnal_perkuliahan WHERE token_presensi = ? AND tanggal = ?";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("ss", $token, $tanggal);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // If token exists, generate a new one
        while ($result->num_rows > 0) {
            $token = generateToken();
            $stmt->close();
            $stmt = $conn->prepare($check_query);
            $stmt->bind_param("ss", $token, $tanggal);
            $stmt->execute();
            $result = $stmt->get_result();
        }
        $stmt->close();
        
        // Insert forum
        $query = "INSERT INTO jurnal_perkuliahan (id_jadwal, tanggal, jam_masuk, materi_kuliah, token_presensi, status_sesi) 
                  VALUES (?, ?, ?, ?, ?, 'Open')";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("issss", $id_jadwal, $tanggal, $jam_masuk, $materi_kuliah, $token);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to create forum");
        }
        
        $id_jurnal = $conn->insert_id;
        $stmt->close();
        $conn->close();
        
        return ['success' => true, 'message' => 'Forum absensi berhasil dibuat', 'token' => $token, 'id_jurnal' => $id_jurnal];
    } catch (Exception $e) {
        if (isset($conn)) {
            $conn->close();
        }
        error_log("Error creating forum: " . $e->getMessage());
        return ['success' => false, 'message' => 'Gagal membuat forum: ' . $e->getMessage()];
    }
}

/**
 * Close forum absensi
 */
function closeForumAbsensi($id_jurnal, $jam_keluar) {
    try {
        $conn = getDBConnection();
        
        $query = "UPDATE jurnal_perkuliahan SET jam_keluar = ?, status_sesi = 'Closed' WHERE id_jurnal = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $jam_keluar, $id_jurnal);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to close forum");
        }
        
        $stmt->close();
        $conn->close();
        
        return ['success' => true, 'message' => 'Forum absensi berhasil ditutup'];
    } catch (Exception $e) {
        if (isset($conn)) {
            $conn->close();
        }
        error_log("Error closing forum: " . $e->getMessage());
        return ['success' => false, 'message' => 'Gagal menutup forum: ' . $e->getMessage()];
    }
}

/**
 * Add new matakuliah
 */
function addMatakuliah($kode_mk, $nama_mk, $sks, $semester) {
    try {
        $conn = getDBConnection();
        
        // Check if kode_mk already exists
        $check = $conn->prepare("SELECT id_mk FROM matakuliah WHERE kode_mk = ?");
        $check->bind_param("s", $kode_mk);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $check->close();
            $conn->close();
            return ['success' => false, 'message' => 'Kode mata kuliah sudah ada!'];
        }
        $check->close();
        
        $query = "INSERT INTO matakuliah (kode_mk, nama_mk, sks, semester) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssii", $kode_mk, $nama_mk, $sks, $semester);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to add matakuliah");
        }
        
        $stmt->close();
        $conn->close();
        
        return ['success' => true, 'message' => 'Mata kuliah berhasil ditambahkan'];
    } catch (Exception $e) {
        if (isset($conn)) {
            $conn->close();
        }
        error_log("Error adding matakuliah: " . $e->getMessage());
        return ['success' => false, 'message' => 'Gagal menambahkan mata kuliah: ' . $e->getMessage()];
    }
}

/**
 * Update matakuliah
 */
function updateMatakuliah($id_mk, $kode_mk, $nama_mk, $sks, $semester) {
    try {
        $conn = getDBConnection();
        
        // Check if kode_mk already exists for other record
        $check = $conn->prepare("SELECT id_mk FROM matakuliah WHERE kode_mk = ? AND id_mk != ?");
        $check->bind_param("si", $kode_mk, $id_mk);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $check->close();
            $conn->close();
            return ['success' => false, 'message' => 'Kode mata kuliah sudah digunakan!'];
        }
        $check->close();
        
        $query = "UPDATE matakuliah SET kode_mk = ?, nama_mk = ?, sks = ?, semester = ? WHERE id_mk = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssiii", $kode_mk, $nama_mk, $sks, $semester, $id_mk);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to update matakuliah");
        }
        
        $stmt->close();
        $conn->close();
        
        return ['success' => true, 'message' => 'Mata kuliah berhasil diperbarui'];
    } catch (Exception $e) {
        if (isset($conn)) {
            $conn->close();
        }
        error_log("Error updating matakuliah: " . $e->getMessage());
        return ['success' => false, 'message' => 'Gagal memperbarui mata kuliah: ' . $e->getMessage()];
    }
}

/**
 * Delete matakuliah
 */
function deleteMatakuliah($id_mk) {
    try {
        $conn = getDBConnection();
        
        // Check if used in jadwal_kuliah
        $check = $conn->prepare("SELECT id_jadwal FROM jadwal_kuliah WHERE id_mk = ? LIMIT 1");
        $check->bind_param("i", $id_mk);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $check->close();
            $conn->close();
            return ['success' => false, 'message' => 'Mata kuliah tidak dapat dihapus karena masih digunakan di jadwal!'];
        }
        $check->close();
        
        $query = "DELETE FROM matakuliah WHERE id_mk = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id_mk);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to delete matakuliah");
        }
        
        $stmt->close();
        $conn->close();
        
        return ['success' => true, 'message' => 'Mata kuliah berhasil dihapus'];
    } catch (Exception $e) {
        if (isset($conn)) {
            $conn->close();
        }
        error_log("Error deleting matakuliah: " . $e->getMessage());
        return ['success' => false, 'message' => 'Gagal menghapus mata kuliah: ' . $e->getMessage()];
    }
}

/**
 * Add new jadwal_kuliah
 */
function addJadwalKuliah($id_mk, $id_dosen, $hari, $jam_mulai, $jam_selesai, $ruangan) {
    try {
        $conn = getDBConnection();
        
        $query = "INSERT INTO jadwal_kuliah (id_mk, id_dosen, hari, jam_mulai, jam_selesai, ruangan) 
                  VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iissss", $id_mk, $id_dosen, $hari, $jam_mulai, $jam_selesai, $ruangan);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to add jadwal");
        }
        
        $stmt->close();
        $conn->close();
        
        return ['success' => true, 'message' => 'Jadwal kuliah berhasil ditambahkan'];
    } catch (Exception $e) {
        if (isset($conn)) {
            $conn->close();
        }
        error_log("Error adding jadwal: " . $e->getMessage());
        return ['success' => false, 'message' => 'Gagal menambahkan jadwal: ' . $e->getMessage()];
    }
}

/**
 * Update jadwal_kuliah
 */
function updateJadwalKuliah($id_jadwal, $id_mk, $id_dosen, $hari, $jam_mulai, $jam_selesai, $ruangan) {
    try {
        $conn = getDBConnection();
        
        $query = "UPDATE jadwal_kuliah SET id_mk = ?, id_dosen = ?, hari = ?, jam_mulai = ?, jam_selesai = ?, ruangan = ? 
                  WHERE id_jadwal = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iissssi", $id_mk, $id_dosen, $hari, $jam_mulai, $jam_selesai, $ruangan, $id_jadwal);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to update jadwal");
        }
        
        $stmt->close();
        $conn->close();
        
        return ['success' => true, 'message' => 'Jadwal kuliah berhasil diperbarui'];
    } catch (Exception $e) {
        if (isset($conn)) {
            $conn->close();
        }
        error_log("Error updating jadwal: " . $e->getMessage());
        return ['success' => false, 'message' => 'Gagal memperbarui jadwal: ' . $e->getMessage()];
    }
}

/**
 * Delete jadwal_kuliah
 */
function deleteJadwalKuliah($id_jadwal) {
    try {
        $conn = getDBConnection();
        
        // Check if used in jurnal_perkuliahan
        $check = $conn->prepare("SELECT id_jurnal FROM jurnal_perkuliahan WHERE id_jadwal = ? LIMIT 1");
        $check->bind_param("i", $id_jadwal);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $check->close();
            $conn->close();
            return ['success' => false, 'message' => 'Jadwal tidak dapat dihapus karena sudah ada jurnal perkuliahan!'];
        }
        $check->close();
        
        $query = "DELETE FROM jadwal_kuliah WHERE id_jadwal = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id_jadwal);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to delete jadwal");
        }
        
        $stmt->close();
        $conn->close();
        
        return ['success' => true, 'message' => 'Jadwal kuliah berhasil dihapus'];
    } catch (Exception $e) {
        if (isset($conn)) {
            $conn->close();
        }
        error_log("Error deleting jadwal: " . $e->getMessage());
        return ['success' => false, 'message' => 'Gagal menghapus jadwal: ' . $e->getMessage()];
    }
}

/**
 * Get matakuliah by ID
 */
function getMatakuliahById($id_mk) {
    try {
        $conn = getDBConnection();
        
        $query = "SELECT * FROM matakuliah WHERE id_mk = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id_mk);
        $stmt->execute();
        $result = $stmt->get_result();
        $matakuliah = $result->fetch_assoc();
        
        $stmt->close();
        $conn->close();
        
        return $matakuliah;
    } catch (Exception $e) {
        error_log("Error getting matakuliah: " . $e->getMessage());
        return null;
    }
}

/**
 * Get jadwal by ID
 */
function getJadwalById($id_jadwal) {
    try {
        $conn = getDBConnection();
        
        $query = "SELECT jk.*, mk.kode_mk, mk.nama_mk, d.nama_lengkap as nama_dosen
                  FROM jadwal_kuliah jk
                  JOIN matakuliah mk ON jk.id_mk = mk.id_mk
                  JOIN dosen d ON jk.id_dosen = d.id_dosen
                  WHERE jk.id_jadwal = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id_jadwal);
        $stmt->execute();
        $result = $stmt->get_result();
        $jadwal = $result->fetch_assoc();
        
        $stmt->close();
        $conn->close();
        
        return $jadwal;
    } catch (Exception $e) {
        error_log("Error getting jadwal: " . $e->getMessage());
        return null;
    }
}

/**
 * Get activity logs (from database if log table exists, or return empty array)
 */
function getActivityLogs($start_date = null, $end_date = null, $search = '') {
    try {
        $conn = getDBConnection();
        
        // Check if activity_log table exists
        $check = $conn->query("SHOW TABLES LIKE 'activity_log'");
        if ($check->num_rows == 0) {
            $conn->close();
            return [];
        }
        
        $query = "SELECT * FROM activity_log WHERE 1=1";
        $params = [];
        $types = "";
        
        if ($start_date) {
            $query .= " AND DATE(created_at) >= ?";
            $params[] = $start_date;
            $types .= "s";
        }
        
        if ($end_date) {
            $query .= " AND DATE(created_at) <= ?";
            $params[] = $end_date;
            $types .= "s";
        }
        
        if ($search) {
            $query .= " AND (activity LIKE ? OR user_name LIKE ?)";
            $search_param = "%$search%";
            $params[] = $search_param;
            $params[] = $search_param;
            $types .= "ss";
        }
        
        $query .= " ORDER BY created_at DESC LIMIT 1000";
        
        $stmt = $conn->prepare($query);
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $logs = $result->fetch_all(MYSQLI_ASSOC);
        
        $stmt->close();
        $conn->close();
        
        return $logs;
    } catch (Exception $e) {
        error_log("Error getting activity logs: " . $e->getMessage());
        return [];
    }
}
