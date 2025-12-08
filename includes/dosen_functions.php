<?php
/**
 * Dosen Functions
 * Helper functions for lecturer operations
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Get dosen ID from user ID
 */
function getDosenId($user_id) {
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT id_dosen FROM dosen WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $stmt->close();
            $conn->close();
            return $row['id_dosen'];
        }
        
        $stmt->close();
        $conn->close();
        return null;
    } catch (Exception $e) {
        error_log("Error getting dosen ID: " . $e->getMessage());
        return null;
    }
}

/**
 * Get jadwal kuliah for a dosen
 */
function getJadwalByDosen($id_dosen) {
    try {
        $conn = getDBConnection();
        $query = "SELECT jk.*, mk.kode_mk, mk.nama_mk, mk.sks
                  FROM jadwal_kuliah jk
                  JOIN matakuliah mk ON jk.id_mk = mk.id_mk
                  WHERE jk.id_dosen = ?
                  ORDER BY jk.hari, jk.jam_mulai";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id_dosen);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $jadwal = [];
        while ($row = $result->fetch_assoc()) {
            $jadwal[] = $row;
        }
        
        $stmt->close();
        $conn->close();
        return $jadwal;
    } catch (Exception $e) {
        error_log("Error getting jadwal: " . $e->getMessage());
        return [];
    }
}

/**
 * Generate random 6-character token
 */
function generateToken() {
    return strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));
}

/**
 * Create new jurnal perkuliahan (check-in)
 */
function bukaSesiPerkuliahan($id_jadwal, $tanggal, $jam_masuk, $materi) {
    try {
        $conn = getDBConnection();
        $token = generateToken();
        
        $stmt = $conn->prepare("INSERT INTO jurnal_perkuliahan (id_jadwal, tanggal, jam_masuk, materi_kuliah, token_presensi, status_sesi) VALUES (?, ?, ?, ?, ?, 'Open')");
        $stmt->bind_param("issss", $id_jadwal, $tanggal, $jam_masuk, $materi, $token);
        
        if ($stmt->execute()) {
            $id_jurnal = $conn->insert_id;
            $stmt->close();
            $conn->close();
            return ['success' => true, 'id_jurnal' => $id_jurnal, 'token' => $token];
        }
        
        $stmt->close();
        $conn->close();
        return ['success' => false, 'message' => 'Gagal membuka sesi'];
    } catch (Exception $e) {
        error_log("Error opening session: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

/**
 * Close jurnal perkuliahan (check-out)
 */
function tutupSesiPerkuliahan($id_jurnal, $jam_keluar) {
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("UPDATE jurnal_perkuliahan SET jam_keluar = ?, status_sesi = 'Closed' WHERE id_jurnal = ?");
        $stmt->bind_param("si", $jam_keluar, $id_jurnal);
        
        if ($stmt->execute()) {
            $stmt->close();
            $conn->close();
            return ['success' => true, 'message' => 'Sesi berhasil ditutup'];
        }
        
        $stmt->close();
        $conn->close();
        return ['success' => false, 'message' => 'Gagal menutup sesi'];
    } catch (Exception $e) {
        error_log("Error closing session: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

/**
 * Get jurnal perkuliahan by dosen
 */
function getJurnalByDosen($id_dosen, $limit = 10) {
    try {
        $conn = getDBConnection();
        $query = "SELECT jp.*, jk.hari, jk.ruangan, mk.kode_mk, mk.nama_mk,
                         COUNT(pm.id_presensi) as jumlah_hadir
                  FROM jurnal_perkuliahan jp
                  JOIN jadwal_kuliah jk ON jp.id_jadwal = jk.id_jadwal
                  JOIN matakuliah mk ON jk.id_mk = mk.id_mk
                  LEFT JOIN presensi_mahasiswa pm ON jp.id_jurnal = pm.id_jurnal AND pm.status = 'Hadir'
                  WHERE jk.id_dosen = ?
                  GROUP BY jp.id_jurnal
                  ORDER BY jp.tanggal DESC, jp.jam_masuk DESC
                  LIMIT ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $id_dosen, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $jurnal = [];
        while ($row = $result->fetch_assoc()) {
            $jurnal[] = $row;
        }
        
        $stmt->close();
        $conn->close();
        return $jurnal;
    } catch (Exception $e) {
        error_log("Error getting jurnal: " . $e->getMessage());
        return [];
    }
}

/**
 * Get students for a specific jurnal session
 * Returns students who have already checked in for this session
 */
function getMahasiswaByJurnal($id_jurnal) {
    try {
        $conn = getDBConnection();
        // First, get students who have attendance records for this session
        $query = "SELECT m.id_mahasiswa, m.nim, m.nama_lengkap, m.jurusan,
                         pm.id_presensi, pm.waktu_scan, pm.status, pm.keterangan
                  FROM presensi_mahasiswa pm
                  JOIN mahasiswa m ON pm.id_mahasiswa = m.id_mahasiswa
                  WHERE pm.id_jurnal = ?
                  ORDER BY m.nama_lengkap";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id_jurnal);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $mahasiswa = [];
        while ($row = $result->fetch_assoc()) {
            $mahasiswa[] = $row;
        }
        
        $stmt->close();
        $conn->close();
        return $mahasiswa;
    } catch (Exception $e) {
        error_log("Error getting mahasiswa: " . $e->getMessage());
        return [];
    }
}

/**
 * Get all students (for enrollment - if needed in future)
 * This function can be used when implementing course enrollment feature
 */
function getAllMahasiswa() {
    try {
        $conn = getDBConnection();
        $query = "SELECT id_mahasiswa, nim, nama_lengkap, jurusan, angkatan
                  FROM mahasiswa
                  ORDER BY nama_lengkap";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $mahasiswa = [];
        while ($row = $result->fetch_assoc()) {
            $mahasiswa[] = $row;
        }
        
        $stmt->close();
        $conn->close();
        return $mahasiswa;
    } catch (Exception $e) {
        error_log("Error getting all mahasiswa: " . $e->getMessage());
        return [];
    }
}

/**
 * Set or update student attendance status
 */
function setStatusMahasiswa($id_jurnal, $id_mahasiswa, $status, $keterangan = '') {
    try {
        $conn = getDBConnection();
        
        // Check if attendance record exists
        $check_stmt = $conn->prepare("SELECT id_presensi FROM presensi_mahasiswa WHERE id_jurnal = ? AND id_mahasiswa = ?");
        $check_stmt->bind_param("ii", $id_jurnal, $id_mahasiswa);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            // Update existing record
            $stmt = $conn->prepare("UPDATE presensi_mahasiswa SET status = ?, keterangan = ?, waktu_scan = CURRENT_TIMESTAMP WHERE id_jurnal = ? AND id_mahasiswa = ?");
            $stmt->bind_param("ssii", $status, $keterangan, $id_jurnal, $id_mahasiswa);
        } else {
            // Insert new record
            $stmt = $conn->prepare("INSERT INTO presensi_mahasiswa (id_jurnal, id_mahasiswa, status, keterangan) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiss", $id_jurnal, $id_mahasiswa, $status, $keterangan);
        }
        
        if ($stmt->execute()) {
            $check_stmt->close();
            $stmt->close();
            $conn->close();
            return ['success' => true, 'message' => 'Status berhasil disimpan'];
        }
        
        $check_stmt->close();
        $stmt->close();
        $conn->close();
        return ['success' => false, 'message' => 'Gagal menyimpan status'];
    } catch (Exception $e) {
        error_log("Error setting status: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

/**
 * Get attendance summary for dosen
 */
function getRekapPresensiDosen($id_dosen, $start_date = null, $end_date = null) {
    try {
        $conn = getDBConnection();
        $query = "SELECT jp.tanggal, jp.jam_masuk, jp.jam_keluar, jp.status_sesi,
                         mk.kode_mk, mk.nama_mk, jk.ruangan,
                         COUNT(DISTINCT pm.id_mahasiswa) as total_mahasiswa,
                         SUM(CASE WHEN pm.status = 'Hadir' THEN 1 ELSE 0 END) as hadir,
                         SUM(CASE WHEN pm.status = 'Izin' THEN 1 ELSE 0 END) as izin,
                         SUM(CASE WHEN pm.status = 'Sakit' THEN 1 ELSE 0 END) as sakit,
                         SUM(CASE WHEN pm.status = 'Alpha' THEN 1 ELSE 0 END) as alpha
                  FROM jurnal_perkuliahan jp
                  JOIN jadwal_kuliah jk ON jp.id_jadwal = jk.id_jadwal
                  JOIN matakuliah mk ON jk.id_mk = mk.id_mk
                  LEFT JOIN presensi_mahasiswa pm ON jp.id_jurnal = pm.id_jurnal
                  WHERE jk.id_dosen = ?";
        
        if ($start_date && $end_date) {
            $query .= " AND jp.tanggal BETWEEN ? AND ?";
        }
        
        $query .= " GROUP BY jp.id_jurnal ORDER BY jp.tanggal DESC";
        
        $stmt = $conn->prepare($query);
        
        if ($start_date && $end_date) {
            $stmt->bind_param("iss", $id_dosen, $start_date, $end_date);
        } else {
            $stmt->bind_param("i", $id_dosen);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $rekap = [];
        while ($row = $result->fetch_assoc()) {
            $rekap[] = $row;
        }
        
        $stmt->close();
        $conn->close();
        return $rekap;
    } catch (Exception $e) {
        error_log("Error getting rekap: " . $e->getMessage());
        return [];
    }
}
?>
