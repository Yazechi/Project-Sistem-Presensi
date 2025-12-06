# SIPRES - Feature Documentation

## 🔐 Sistem Autentikasi Multi-Level

### Overview
SIPRES menggunakan sistem login terpusat yang mendukung tiga tingkat akses berbeda: **Admin**, **Dosen**, dan **Mahasiswa**. Setiap role memiliki dashboard dan hak akses yang berbeda.

---

## Fitur Utama

### 1. Multi-Level Authentication
- **Single Login Page**: Satu halaman login untuk semua role
- **Automatic Role Detection**: Sistem otomatis mendeteksi role dari database
- **Role-Based Redirect**: Redirect otomatis ke dashboard sesuai role
- **Access Control**: Setiap modul dilindungi dengan pengecekan role

#### Alur Login:
```
User Input (Username + Password)
    ↓
Validasi Input (Empty Check)
    ↓
Database Query (Prepared Statement)
    ↓
Password Verification (password_verify)
    ↓
Session Creation (user_id, username, nama, role)
    ↓
Role-Based Redirect
    ├── Admin → /modules/admin/dashboard.php
    ├── Dosen → /modules/dosen/dashboard.php
    └── Mahasiswa → /modules/mahasiswa/dashboard.php
```

### 2. Security Features

#### A. Password Security
- **Algorithm**: BCrypt (PASSWORD_BCRYPT)
- **Function**: `password_hash()` untuk hashing
- **Verification**: `password_verify()` untuk validasi
- **Cost Factor**: Default 10 (2^10 iterations)

```php
// Hashing
$hashed = password_hash($password, PASSWORD_BCRYPT);

// Verification
if (password_verify($password, $hashed)) {
    // Login successful
}
```

#### B. SQL Injection Prevention
- **Method**: Prepared Statements dengan Bound Parameters
- **Implementation**: MySQLi prepared statements
- **Additional**: Input trimming dan validation

```php
$query = "SELECT * FROM users WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
```

#### C. XSS Prevention
- **Function**: `htmlspecialchars()` pada semua output
- **Additional**: Input sanitasi dengan `trim()` dan `stripslashes()`
- **Implementation**: Semua user input di-escape sebelum ditampilkan

```php
echo htmlspecialchars($user_input);
```

#### D. Session Management
- **Timeout**: 30 menit (1800 detik) inaktivitas
- **HttpOnly Cookies**: Mencegah akses JavaScript ke cookies
- **Session Regeneration**: Session ID diregenerasi saat login
- **Activity Tracking**: Last activity time dicatat setiap request

```php
// Session timeout check
if (time() - $_SESSION['LAST_ACTIVITY'] > SESSION_TIMEOUT) {
    session_destroy();
    redirect_to_login();
}
```

#### E. Role-Based Access Control (RBAC)
- **Implementation**: Setiap halaman module memvalidasi role
- **Method**: Exact match comparison
- **Redirect**: Unauthorized access → redirect ke login

```php
if ($_SESSION['role'] != 'admin') {
    header("Location: /auth/login.php");
    exit;
}
```

### 3. User Interface

#### Login Page
- **Design**: Modern gradient background (purple to blue)
- **Layout**: Centered card dengan shadow
- **Responsive**: Mobile-friendly design
- **Elements**:
  - Logo dan judul sistem
  - Form username dan password
  - Error/success message alerts
  - Demo account information
  - Submit button dengan hover effects

#### Dashboard Pages
- **Header**: 
  - System logo dan nama
  - User information dengan nama dan role badge
  - Logout button
- **Welcome Card**: 
  - Personalized greeting
  - User details (nama, username/NIP/NIM)
- **Info Grid**: 
  - 4 kartu informasi fitur
  - Icons dan deskripsi
- **Security Info**: 
  - Daftar fitur keamanan aktif
  - Session timeout warning

### 4. Database Structure

#### Users Table
```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    role ENUM('admin', 'dosen', 'mahasiswa') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    is_active TINYINT(1) DEFAULT 1,
    INDEX idx_username (username),
    INDEX idx_role (role)
);
```

### 5. Session Data Structure

```php
$_SESSION = [
    'user_id' => 1,                    // ID user dari database
    'username' => 'admin',              // Username/NIP/NIM
    'nama' => 'Administrator',          // Nama lengkap
    'role' => 'admin',                  // Role: admin/dosen/mahasiswa
    'LAST_ACTIVITY' => 1234567890       // Timestamp aktivitas terakhir
];
```

---

## Role Specifications

### Admin Dashboard
**Path**: `/modules/admin/dashboard.php`

**Access**: Role `admin`

**Features**:
- 📊 Manajemen Data
- 👥 Manajemen User
- ⚙️ Konfigurasi Sistem
- 📈 Laporan & Statistik

**Responsibilities**:
- Kelola semua user (Admin, Dosen, Mahasiswa)
- Atur hak akses dan permissions
- Konfigurasi sistem dan backup database
- Generate laporan dan analisis data

### Dosen Dashboard
**Path**: `/modules/dosen/dashboard.php`

**Access**: Role `dosen`

**Features**:
- 📋 Kelola Presensi
- 📚 Mata Kuliah
- 👥 Data Mahasiswa
- 📊 Laporan Kehadiran

**Responsibilities**:
- Buat dan kelola presensi kelas
- Monitor kehadiran mahasiswa
- Akses data mahasiswa di kelas
- Generate laporan kehadiran

### Mahasiswa Dashboard
**Path**: `/modules/mahasiswa/dashboard.php`

**Access**: Role `mahasiswa`

**Features**:
- ✅ Presensi Online
- 📅 Jadwal Kuliah
- 📊 Riwayat Kehadiran
- 📈 Statistik Presensi

**Responsibilities**:
- Lakukan absensi online
- Lihat jadwal kuliah
- Monitor kehadiran sendiri
- Cek persentase kehadiran

---

## Demo Accounts

| Role | Username | Password | Nama |
|------|----------|----------|------|
| Admin | `admin` | `admin123` | Administrator |
| Dosen | `198001012005011001` | `dosen123` | Dr. Ahmad Budiman |
| Mahasiswa | `210001001` | `mhs123` | Budi Santoso |

---

## File Structure

```
Project-Sstem-Presensi/
│
├── auth/                       # Authentication pages
│   ├── login.php              # Login page
│   └── logout.php             # Logout handler
│
├── config/                     # Configuration files
│   ├── database.php           # Database connection
│   └── session.php            # Session management
│
├── includes/                   # Shared functions
│   └── auth_functions.php     # Authentication functions
│
├── modules/                    # Role-specific modules
│   ├── admin/
│   │   └── dashboard.php      # Admin dashboard
│   ├── dosen/
│   │   └── dashboard.php      # Dosen dashboard
│   └── mahasiswa/
│       └── dashboard.php      # Mahasiswa dashboard
│
├── assets/                     # Static assets
│   ├── css/
│   │   ├── login.css          # Login page styles
│   │   └── dashboard.css      # Dashboard styles
│   └── js/                    # JavaScript files
│
├── database/                   # Database files
│   ├── sipres.sql             # Database schema
│   └── create_demo_users.php  # Demo user generator
│
├── index.php                   # Entry point
├── README.md                   # Project documentation
├── INSTALLATION.md             # Installation guide
└── .gitignore                  # Git ignore rules
```

---

## API Functions

### Authentication Functions (`includes/auth_functions.php`)

#### authenticateUser($username, $password)
Memvalidasi kredensial user dan mengembalikan data user jika valid.

**Parameters**:
- `$username` (string): Username/NIP/NIM
- `$password` (string): Password plain text

**Returns**:
- `array`: User data (id, username, nama, role) jika berhasil
- `false`: Jika gagal

**Example**:
```php
$user = authenticateUser('admin', 'admin123');
if ($user) {
    createUserSession($user);
}
```

#### createUserSession($user)
Membuat session untuk user yang sudah terautentikasi.

**Parameters**:
- `$user` (array): Data user dari database

**Example**:
```php
createUserSession([
    'id' => 1,
    'username' => 'admin',
    'nama' => 'Administrator',
    'role' => 'admin'
]);
```

#### getRedirectURL($role)
Mendapatkan URL dashboard berdasarkan role.

**Parameters**:
- `$role` (string): Role user (admin/dosen/mahasiswa)

**Returns**:
- `string`: URL dashboard

**Example**:
```php
$url = getRedirectURL('admin'); // Returns: /modules/admin/dashboard.php
```

### Session Functions (`config/session.php`)

#### startSecureSession()
Memulai session dengan security settings dan check timeout.

#### isLoggedIn()
Check apakah user sudah login.

**Returns**: `bool`

#### hasRole($required_role)
Check apakah user memiliki role tertentu.

**Parameters**:
- `$required_role` (string): Role yang diperlukan

**Returns**: `bool`

#### requireLogin()
Require user untuk login, redirect jika belum.

#### requireRole($required_role)
Require role tertentu, redirect jika tidak sesuai.

#### logoutUser()
Destroy session dan logout user.

---

## Security Checklist

### ✅ Implemented
- [x] Password hashing dengan BCrypt
- [x] Prepared statements untuk SQL queries
- [x] Input sanitization untuk output
- [x] Session timeout (30 menit)
- [x] HttpOnly session cookies
- [x] Role-based access control
- [x] XSS prevention
- [x] SQL injection prevention

### ⚠️ Production Requirements
- [ ] Enable HTTPS
- [ ] Set session.cookie_secure = 1
- [ ] Implement rate limiting
- [ ] Add CSRF protection
- [ ] Setup error logging
- [ ] Add audit logging
- [ ] Password strength requirements
- [ ] Account lockout mechanism
- [ ] Two-factor authentication (optional)

---

## Testing

### Manual Testing Steps

1. **Login Valid Admin**
   - Username: `admin`
   - Password: `admin123`
   - Expected: Redirect ke `/modules/admin/dashboard.php`

2. **Login Valid Dosen**
   - Username: `198001012005011001`
   - Password: `dosen123`
   - Expected: Redirect ke `/modules/dosen/dashboard.php`

3. **Login Valid Mahasiswa**
   - Username: `210001001`
   - Password: `mhs123`
   - Expected: Redirect ke `/modules/mahasiswa/dashboard.php`

4. **Login Invalid**
   - Username: `invalid`
   - Password: `invalid`
   - Expected: Error message "Username atau password salah!"

5. **Access Control**
   - Login sebagai mahasiswa
   - Akses `/modules/admin/dashboard.php`
   - Expected: Redirect ke login page

6. **Session Timeout**
   - Login dan tunggu 31 menit
   - Refresh halaman
   - Expected: Redirect ke login dengan pesan timeout

7. **Logout**
   - Klik tombol Logout
   - Expected: Redirect ke login dengan pesan success

---

## Best Practices

### When Adding New Features

1. **New Module Page**:
   ```php
   <?php
   require_once __DIR__ . '/../../config/session.php';
   startSecureSession();
   
   if ($_SESSION['role'] != 'expected_role') {
       header("Location: /auth/login.php");
       exit;
   }
   ?>
   ```

2. **Database Queries**:
   ```php
   $query = "SELECT * FROM table WHERE column = ?";
   $stmt = $conn->prepare($query);
   $stmt->bind_param("s", $value);
   $stmt->execute();
   ```

3. **Display User Input**:
   ```php
   echo htmlspecialchars($user_input);
   ```

4. **New User Creation**:
   ```php
   $password = password_hash($plain_password, PASSWORD_BCRYPT);
   ```

---

## Support

Untuk pertanyaan atau issue, buka issue di GitHub repository atau hubungi maintainer.

---

**Version**: 1.0.0  
**Last Updated**: 2025-12-06  
**Status**: ✅ Production Ready (dengan catatan production requirements)
