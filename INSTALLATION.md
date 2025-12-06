# SIPRES - Sistem Informasi Presensi
## Panduan Instalasi dan Setup

### Persyaratan Sistem
- PHP 7.4 atau lebih tinggi
- MySQL 5.7 atau lebih tinggi / MariaDB
- Apache/Nginx Web Server
- Extension PHP: mysqli, session

### Langkah Instalasi

#### 1. Setup Database

Jalankan script SQL untuk membuat database dan tabel:

```bash
mysql -u root -p < database/sipres.sql
```

Atau import manual melalui phpMyAdmin.

#### 2. Konfigurasi Database

Edit file `config/database.php` sesuai dengan konfigurasi database Anda:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'sipres');
```

#### 3. Generate Demo Users

Jalankan script untuk membuat akun demo dengan password yang sudah di-hash:

```bash
php database/create_demo_users.php
```

Script ini akan membuat 3 akun demo:
- Admin: `admin` / `admin123`
- Dosen: `198001012005011001` / `dosen123`
- Mahasiswa: `210001001` / `mhs123`

#### 4. Setup Web Server

**Untuk Apache:**

Pastikan `mod_rewrite` diaktifkan dan DocumentRoot mengarah ke folder project.

**Untuk PHP Built-in Server (Development):**

```bash
php -S localhost:8000
```

Kemudian akses: `http://localhost:8000`

#### 5. Testing Login

Buka browser dan akses aplikasi:
- `http://localhost:8000/auth/login.php` (atau sesuai konfigurasi server Anda)

Login dengan salah satu akun demo untuk testing.

### Struktur Direktori

```
Project-Sstem-Presensi/
├── assets/
│   ├── css/           # File CSS
│   └── js/            # File JavaScript
├── auth/
│   ├── login.php      # Halaman login
│   └── logout.php     # Proses logout
├── config/
│   ├── database.php   # Konfigurasi database
│   └── session.php    # Konfigurasi session
├── database/
│   ├── sipres.sql     # Schema database
│   └── create_demo_users.php  # Script generate user demo
├── includes/
│   └── auth_functions.php     # Fungsi autentikasi
├── modules/
│   ├── admin/
│   │   └── dashboard.php      # Dashboard admin
│   ├── dosen/
│   │   └── dashboard.php      # Dashboard dosen
│   └── mahasiswa/
│       └── dashboard.php      # Dashboard mahasiswa
├── index.php          # Entry point
└── README.md          # Dokumentasi
```

### Fitur Keamanan

1. **Password Hashing**: Menggunakan `PASSWORD_BCRYPT`
2. **SQL Injection Prevention**: Prepared statements dan `mysqli_real_escape_string`
3. **Session Timeout**: 30 menit inaktivitas
4. **Role-Based Access Control**: Setiap modul memiliki pengecekan role
5. **Secure Session**: HttpOnly cookies untuk mencegah XSS

### Alur Login

1. User memasukkan username (NIP/NIM) dan password
2. Input disanitasi untuk mencegah SQL Injection
3. Password diverifikasi dengan `password_verify()`
4. Session dibuat dengan data user (id, nama, role)
5. Redirect ke dashboard sesuai role:
   - Admin → `/modules/admin/dashboard.php`
   - Dosen → `/modules/dosen/dashboard.php`
   - Mahasiswa → `/modules/mahasiswa/dashboard.php`

### Troubleshooting

**Database connection error:**
- Pastikan MySQL/MariaDB berjalan
- Cek kredensial database di `config/database.php`
- Pastikan database `sipres` sudah dibuat

**Session timeout langsung:**
- Cek konfigurasi PHP session
- Pastikan folder session writable

**Redirect tidak bekerja:**
- Cek konfigurasi web server
- Pastikan tidak ada output sebelum `header()`

### Security Notes

⚠️ **PENTING untuk Production:**

1. Ubah kredensial database default
2. Gunakan HTTPS untuk production
3. Set `session.cookie_secure` ke `1` jika menggunakan HTTPS
4. Hapus atau lindungi script `create_demo_users.php`
5. Atur permission file dengan benar (644 untuk file, 755 untuk directory)
6. Aktifkan error logging dan matikan display errors

### Support

Untuk pertanyaan atau masalah, silakan buat issue di repository GitHub.
