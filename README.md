# SIPRES - Sistem Informasi Presensi

## 🔐 Sistem Autentikasi Multi-Level

SIPRES adalah sistem presensi berbasis web dengan fitur autentikasi multi-level yang membedakan akses antara **Admin**, **Dosen**, dan **Mahasiswa**.

### ✨ Fitur Utama

- **Multi-Level Login**: Satu halaman login untuk semua role dengan redirect otomatis
- **Password Hashing**: Menggunakan algoritma `PASSWORD_BCRYPT` untuk keamanan maksimal
- **SQL Injection Prevention**: Sanitasi input dan prepared statements
- **Session Timeout**: Otomatis logout setelah 30 menit tidak ada aktivitas
- **Role-Based Access Control**: Setiap modul dilindungi dengan pengecekan role

### 🚀 Quick Start

```bash
# 1. Clone repository
git clone https://github.com/Yazechi/Project-Sstem-Presensi.git
cd Project-Sstem-Presensi

# 2. Setup database
mysql -u root -p < database/sipres.sql

# 3. Generate demo users
php database/create_demo_users.php

# 4. Run development server
php -S localhost:8000

# 5. Access application
# Browser: http://localhost:8000
```

### 👥 Akun Demo

Gunakan salah satu akun berikut untuk testing:

| Role | Username | Password |
|------|----------|----------|
| Admin | `admin` | `admin123` |
| Dosen | `198001012005011001` | `dosen123` |
| Mahasiswa | `210001001` | `mhs123` |

### 📋 Alur Login

1. User memasukkan **Username** (NIP/NIM) dan **Password**
2. Sistem melakukan **sanitasi input** untuk mencegah SQL Injection
3. Password diverifikasi menggunakan `password_verify()`
4. Session dibuat dengan data: `user_id`, `role`, `nama`
5. Redirect otomatis ke dashboard sesuai role:
   - Admin → `/modules/admin/dashboard.php`
   - Dosen → `/modules/dosen/dashboard.php`
   - Mahasiswa → `/modules/mahasiswa/dashboard.php`

### 🔒 Fitur Keamanan

- ✅ Password hashing dengan `PASSWORD_BCRYPT`
- ✅ Prepared statements untuk query database
- ✅ Input sanitasi dengan `mysqli_real_escape_string`
- ✅ Session timeout (30 menit inaktivitas)
- ✅ HttpOnly cookies untuk mencegah XSS
- ✅ Role-based access control

### 📁 Struktur Project

```
Project-Sstem-Presensi/
├── assets/              # CSS dan JavaScript
├── auth/                # Halaman login dan logout
├── config/              # Konfigurasi database dan session
├── database/            # Schema SQL dan script setup
├── includes/            # Fungsi-fungsi helper
├── modules/             # Modul untuk setiap role
│   ├── admin/
│   ├── dosen/
│   └── mahasiswa/
└── index.php            # Entry point
```

### 📖 Dokumentasi Lengkap

Lihat [INSTALLATION.md](INSTALLATION.md) untuk panduan instalasi detail.

### 🛠️ Teknologi

- PHP 7.4+
- MySQL/MariaDB
- HTML5, CSS3
- Vanilla JavaScript

### 📝 License

This project is open source and available under the MIT License.

### 🤝 Contributing

Pull requests are welcome! For major changes, please open an issue first to discuss what you would like to change.