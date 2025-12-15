<?php
/**
 * Mahasiswa Functions
 * Helper functions for student operations
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Get mahasiswa ID from user ID
 */
function getMahasiswaId($user_id) {
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT id_mahasiswa FROM mahasiswa WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $stmt->close();
            $conn->close();
            return $row['id_mahasiswa'];
        }
        
        $stmt->close();
        $conn->close();
        return null;
    } catch (Exception $e) {
        error_log("Error getting mahasiswa ID: " . $e->getMessage());
        return null;
    }
}

/**
 * Get all available jadwal for enrollment
 */
function getAvailableJadwal($id_mahasiswa = null) {
    try {
        $conn = getDBConnection();
        
        $query = "SELECT jk.*, mk.kode_mk, mk.nama_mk, mk.sks, mk.semester,
                         d.nama_lengkap as nama_dosen, d.nidn";
        
        if ($id_mahasiswa) {
            $query .= ", e.id_enrollment, e.status as enrollment_status";
        }
        
        $query .= " FROM jadwal_kuliah jk
                    JOIN matakuliah mk ON jk.id_mk = mk.id_mk
                    JOIN dosen d ON jk.id_dosen = d.id_dosen";
        
        if ($id_mahasiswa) {
            $query .= " LEFT JOIN enrollment e ON jk.id_jadwal = e.id_jadwal AND e.id_mahasiswa = ?";
        }
        
        $query .= " ORDER BY mk.semester, mk.nama_mk, jk.hari";
        
        $stmt = $conn->prepare($query);
        
        if ($id_mahasiswa) {
            $stmt->bind_param("i", $id_mahasiswa);
        }
        
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
        error_log("Error getting available jadwal: " . $e->getMessage());
        return [];
    }
}

/**
 * Enroll mahasiswa to a jadwal
 */
function enrollMahasiswa($id_mahasiswa, $id_jadwal) {
    try {
        $conn = getDBConnection();
        
        // Check if already enrolled
        $check = $conn->prepare("SELECT id_enrollment FROM enrollment WHERE id_mahasiswa = ? AND id_jadwal = ?");
        $check->bind_param("ii", $id_mahasiswa, $id_jadwal);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $check->close();
            $conn->close();
            return ['success' => false, 'message' => 'Anda sudah terdaftar di jadwal ini!'];
        }
        $check->close();
        
        // Insert enrollment
        $query = "INSERT INTO enrollment (id_mahasiswa, id_jadwal, status) VALUES (?, ?, 'Aktif')";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $id_mahasiswa, $id_jadwal);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to enroll");
        }
        
        $stmt->close();
        $conn->close();
        
        return ['success' => true, 'message' => 'Berhasil mendaftar ke mata kuliah!'];
    } catch (Exception $e) {
        if (isset($conn)) {
            $conn->close();
        }
        error_log("Error enrolling mahasiswa: " . $e->getMessage());
        return ['success' => false, 'message' => 'Gagal mendaftar: ' . $e->getMessage()];
    }
}

/**
 * Unenroll mahasiswa from a jadwal
 */
function unenrollMahasiswa($id_mahasiswa, $id_jadwal) {
    try {
        $conn = getDBConnection();
        
        // Check if has attendance records
        $check = $conn->prepare("SELECT pm.id_presensi 
                                FROM presensi_mahasiswa pm
                                JOIN jurnal_perkuliahan jp ON pm.id_jurnal = jp.id_jurnal
                                WHERE pm.id_mahasiswa = ? AND jp.id_jadwal = ?
                                LIMIT 1");
        $check->bind_param("ii", $id_mahasiswa, $id_jadwal);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $check->close();
            $conn->close();
            return ['success' => false, 'message' => 'Tidak dapat keluar karena sudah ada riwayat presensi!'];
        }
        $check->close();
        
        // Delete enrollment
        $query = "DELETE FROM enrollment WHERE id_mahasiswa = ? AND id_jadwal = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $id_mahasiswa, $id_jadwal);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to unenroll");
        }
        
        $stmt->close();
        $conn->close();
        
        return ['success' => true, 'message' => 'Berhasil keluar dari mata kuliah!'];
    } catch (Exception $e) {
        if (isset($conn)) {
            $conn->close();
        }
        error_log("Error unenrolling mahasiswa: " . $e->getMessage());
        return ['success' => false, 'message' => 'Gagal keluar: ' . $e->getMessage()];
    }
}

/**
 * Get enrolled jadwal for mahasiswa
 */
function getEnrolledJadwal($id_mahasiswa) {
    try {
        $conn = getDBConnection();
        
        $query = "SELECT jk.*, mk.kode_mk, mk.nama_mk, mk.sks, mk.semester,
                         d.nama_lengkap as nama_dosen, d.nidn,
                         e.id_enrollment, e.tanggal_daftar, e.status as enrollment_status
                  FROM enrollment e
                  JOIN jadwal_kuliah jk ON e.id_jadwal = jk.id_jadwal
                  JOIN matakuliah mk ON jk.id_mk = mk.id_mk
                  JOIN dosen d ON jk.id_dosen = d.id_dosen
                  WHERE e.id_mahasiswa = ? AND e.status = 'Aktif'
                  ORDER BY mk.semester, mk.nama_mk, jk.hari";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id_mahasiswa);
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
        error_log("Error getting enrolled jadwal: " . $e->getMessage());
        return [];
    }
}
