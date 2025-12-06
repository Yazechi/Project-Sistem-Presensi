# ⚡ Quick Start Guide - Database Setup

## 🚀 Setup dalam 5 Menit!

### Step 1: Install MySQL
```bash
# Ubuntu/Debian
sudo apt-get update
sudo apt-get install mysql-server

# macOS (dengan Homebrew)
brew install mysql

# Windows: Download dari https://dev.mysql.com/downloads/mysql/
```

### Step 2: Start MySQL Service
```bash
# Ubuntu/Debian
sudo systemctl start mysql
sudo systemctl enable mysql

# macOS
brew services start mysql

# Windows: MySQL Service akan start otomatis
```

### Step 3: Login ke MySQL
```bash
mysql -u root -p
```

### Step 4: Execute Database Script
```bash
# Option 1: Dari terminal
mysql -u root -p < database/db_presensi_uas.sql

# Option 2: Dari MySQL shell
mysql> source /path/to/database/db_presensi_uas.sql;
```

### Step 5: Verify Installation
```sql
USE db_presensi_uas;
SHOW TABLES;

-- Output harus menampilkan 8 tabel:
-- +---------------------------+
-- | Tables_in_db_presensi_uas |
-- +---------------------------+
-- | admin                     |
-- | dosen                     |
-- | jadwal_kuliah             |
-- | jurnal_perkuliahan        |
-- | mahasiswa                 |
-- | matakuliah                |
-- | presensi_mahasiswa        |
-- | users                     |
-- +---------------------------+
```

## ✅ Test Database

### Test 1: Cek Data Seeding
```sql
-- Cek users
SELECT username, role FROM users;
-- Harus ada 8 users (1 admin, 2 dosen, 5 mahasiswa)

-- Cek mata kuliah
SELECT kode_mk, nama_mk FROM matakuliah;
-- Harus ada 3 mata kuliah

-- Cek jadwal
SELECT * FROM view_jadwal_lengkap;
-- Harus ada 3 jadwal
```

### Test 2: Test Login
```sql
SELECT u.username, u.role, m.nama_lengkap
FROM users u
JOIN mahasiswa m ON u.id_user = m.user_id
WHERE u.username = 'mhs001';
-- Harus return: mhs001 | mhs | Budi Santoso
```

### Test 3: Test Stored Procedure
```sql
-- Generate token
CALL sp_generate_token();
-- Harus return token 6 karakter

-- Test presensi (gunakan token existing)
CALL sp_presensi_mahasiswa(5, 'GHI789');
-- Harus return: Presensi berhasil dicatat
```

## 🎯 Common Queries

### Query 1: Lihat Jadwal Hari Ini
```sql
SELECT * FROM view_jadwal_lengkap 
WHERE hari = DAYNAME(CURDATE());
```

### Query 2: Lihat Presensi Mahasiswa
```sql
SELECT * FROM view_rekap_presensi 
WHERE nim = '21001';
```

### Query 3: Lihat Jurnal Perkuliahan Aktif
```sql
SELECT * FROM view_jurnal_lengkap 
WHERE status_sesi = 'Open';
```

## 🔑 Default Credentials

| Username | Password | Role |
|----------|----------|------|
| admin | password | Admin |
| dosen001 | password | Dosen |
| dosen002 | password | Dosen |
| mhs001 | password | Mahasiswa |
| mhs002 | password | Mahasiswa |
| mhs003 | password | Mahasiswa |
| mhs004 | password | Mahasiswa |
| mhs005 | password | Mahasiswa |

> ⚠️ **PENTING:** Ganti semua password setelah setup!

## 📖 Next Steps

1. ✅ Database sudah siap digunakan
2. 📚 Baca [README.md](README.md) untuk dokumentasi lengkap
3. 🔧 Integrasikan dengan aplikasi PHP/Node.js/Python
4. 🔐 Implementasi authentication di aplikasi
5. 🎨 Buat UI untuk sistem presensi

## 🆘 Troubleshooting

### Error: Access denied for user
```bash
# Reset password MySQL root
sudo mysql
ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'your_password';
FLUSH PRIVILEGES;
```

### Error: Can't connect to MySQL server
```bash
# Cek status service
sudo systemctl status mysql

# Start service jika belum running
sudo systemctl start mysql
```

### Error: Database exists
```sql
-- Script sudah include DROP DATABASE
-- Jika masih error, hapus manual:
DROP DATABASE IF EXISTS db_presensi_uas;
```

### Error: Permission denied
```bash
# Pastikan user MySQL punya privilege
mysql -u root -p
GRANT ALL PRIVILEGES ON db_presensi_uas.* TO 'your_user'@'localhost';
FLUSH PRIVILEGES;
```

## 🛠️ Development Setup

### Create New Database User (Recommended)
```sql
-- Login sebagai root
mysql -u root -p

-- Create user untuk aplikasi
CREATE USER 'presensi_user'@'localhost' IDENTIFIED BY 'secure_password_here';

-- Grant privileges
GRANT ALL PRIVILEGES ON db_presensi_uas.* TO 'presensi_user'@'localhost';
FLUSH PRIVILEGES;

-- Test connection
mysql -u presensi_user -p db_presensi_uas
```

### Connection String Examples

**PHP (MySQLi):**
```php
<?php
$host = 'localhost';
$db   = 'db_presensi_uas';
$user = 'presensi_user';
$pass = 'secure_password_here';
$charset = 'utf8mb4';

$mysqli = new mysqli($host, $user, $pass, $db);
$mysqli->set_charset($charset);
?>
```

**PHP (PDO):**
```php
<?php
$host = 'localhost';
$db   = 'db_presensi_uas';
$user = 'presensi_user';
$pass = 'secure_password_here';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

$pdo = new PDO($dsn, $user, $pass, $options);
?>
```

**Node.js (mysql2):**
```javascript
const mysql = require('mysql2');

const connection = mysql.createConnection({
  host: 'localhost',
  user: 'presensi_user',
  password: 'secure_password_here',
  database: 'db_presensi_uas'
});
```

**Python (mysql-connector):**
```python
import mysql.connector

connection = mysql.connector.connect(
  host="localhost",
  user="presensi_user",
  password="secure_password_here",
  database="db_presensi_uas"
)
```

## 📊 Monitoring

### Check Database Size
```sql
SELECT 
    table_schema AS 'Database',
    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'Size (MB)'
FROM information_schema.TABLES
WHERE table_schema = 'db_presensi_uas'
GROUP BY table_schema;
```

### Check Table Row Counts
```sql
SELECT 
    table_name,
    table_rows
FROM information_schema.tables
WHERE table_schema = 'db_presensi_uas'
ORDER BY table_rows DESC;
```

## 🎓 Learning Resources

- **MySQL Documentation:** https://dev.mysql.com/doc/
- **SQL Tutorial:** https://www.w3schools.com/sql/
- **Database Design:** https://www.geeksforgeeks.org/database-design/

---

**Happy Coding! 🚀**

Jika ada pertanyaan, lihat [README.md](README.md) atau dokumentasi lengkap.
