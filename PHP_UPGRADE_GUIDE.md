# ⚠️ PHP 8.0 Compatibility Issue

## Masalah

Proyek ini menggunakan **Laravel 13** yang memerlukan **PHP 8.3+**, tetapi sistem saat ini hanya memiliki **PHP 8.0.30** di XAMPP.

### Incompatibilities yang Ditemukan

1. **First-class callable syntax** `functionName(...)` - PHP 8.1+
2. **readonly class modifier** - PHP 8.2+
3. **Mixed type** dan fitur lainnya yang tidak ada di PHP 8.0

Syntax ini tersebar di banyak file vendor (Symfony, Laravel framework).

---

## ✅ Solusi: Upgrade PHP (Recommended)

### Langkah 1: Download PHP 8.3

1. Kunjungi: https://windows.php.net/download/
2. Download: **PHP 8.3 (Thread Safe, Zip)**
3. Extract ke folder, misal: `C:\php-8.3.10`

### Langkah 2: Update XAMPP Configuration

**Opsi A: Update php.exe di XAMPP**
```bash
# Backup original
Move-Item "C:\xampp\php" "C:\xampp\php-8.0-backup"

# Copy PHP 8.3
Copy-Item "C:\php-8.3.10" "C:\xampp\php"

# Restart XAMPP dari Control Panel
```

**Opsi B: Update PATH (Jika tidak mau menimpa)**
```bash
# Edit System Environment Variables
# Pasang C:\php-8.3.10 di urutan PERTAMA dalam PATH
# Sebelum C:\xampp\php
```

### Langkah 3: Verifikasi PHP Version
```bash
C:\xampp\php\php.exe -v
# Harusnya output: PHP 8.3.x
```

### Langkah 4: Restart Apache
```bash
# Buka XAMPP Control Panel
# Click "Stop" pada Apache, tunggu 3 detik
# Click "Start" pada Apache
```

### Langkah 5: Test Akses
```
http://localhost/gis_perumahan/
```

---

## 🐳 Alternatif: Gunakan Docker

Jika tidak ingin memodifikasi XAMPP, gunakan Docker:

### Install Docker Desktop

1. Download: https://www.docker.com/products/docker-desktop
2. Install dan restart sistem
3. Buka PowerShell di project folder:

```powershell
cd c:\Users\user\GiS_Perumahan-

# Buat docker-compose.yml
@"
version: '3.8'

services:
  app:
    image: php:8.3-apache
    ports:
      - "8080:80"
    volumes:
      - .:/var/www/html
    environment:
      DB_HOST: db
      DB_DATABASE: gis_perumahan_zenit
      DB_USERNAME: root
      DB_PASSWORD: root
    depends_on:
      - db

  db:
    image: mysql:8
    ports:
      - "3306:3306"
    environment:
      MYSQL_ROOT_PASSWORD: root
      MYSQL_DATABASE: gis_perumahan_zenit
    volumes:
      - mysql_data:/var/lib/mysql

volumes:
  mysql_data:
"@ | Out-File docker-compose.yml

# Start Docker containers
docker-compose up -d

# Akses aplikasi di http://localhost:8080
```

---

## 🎯 Langkah Selanjutnya (Setelah PHP Upgrade)

Setelah upgrade PHP 8.3:

### 1. Clear Laravel Cache
```bash
cd c:\Users\user\GiS_Perumahan-
C:\xampp\php\php.exe artisan config:clear
C:\xampp\php\php.exe artisan cache:clear
```

### 2. Run Migrations (Jika belum)
```bash
C:\xampp\php\php.exe artisan migrate:fresh --seed
```

### 3. Start Development Server
```bash
# Opsi 1: Via Artisan
C:\xampp\php\php.exe artisan serve
# Akses di http://localhost:8000

# Opsi 2: Via Apache (jika sudah di htdocs)
# Akses di http://localhost/gis_perumahan/
```

---

## 📋 Checklist

- [ ] Download PHP 8.3 dari https://windows.php.net/download/
- [ ] Extract PHP 8.3 ke folder
- [ ] Update XAMPP atau PATH dengan PHP 8.3
- [ ] Restart Apache dari XAMPP Control Panel
- [ ] Verifikasi: `php -v` (harusnya 8.3+)
- [ ] Akses: http://localhost/gis_perumahan/
- [ ] Jika error, cek: http://localhost/gis_perumahan/public/index.php

---

## 🆘 Troubleshooting

### Error: "Command not found: php"
```bash
# Pastikan PHP 8.3 path sudah di Environment Variables
$env:Path -split ';' | Select-String 'php'
# Harusnya muncul: C:\php-8.3.10 atau C:\xampp\php
```

### Error: "Apache can't find PHP"
```bash
# Restart Apache dari XAMPP Control Panel
# Atau restart dari PowerShell:
Stop-Service -Name "Apache2.4" -Force
Start-Service -Name "Apache2.4"
```

### Error: "Database connection failed"
```bash
# Pastikan MySQL running di XAMPP
# Check: MySQL port 3306 listening
netstat -ano | findstr 3306
```

### Error: "Still showing PHP 8.0"
```bash
# Flush DNS cache
ipconfig /flushdns

# Restart semua terminal/PowerShell yang terbuka
# Restart XAMPP Control Panel
```

---

## 📞 Support

Jika masih ada masalah:

1. Check log Apache: `C:\xampp\apache\logs\error.log`
2. Check log PHP: `C:\xampp\php\logs\php_error.log`
3. Buka phpMyAdmin: `http://localhost/phpmyadmin/`
4. Test database connection: Gunakan `verify_database.php`

---

## ✨ Setelah Setup Berhasil

```
✅ Database: 101 perumahan records siap
✅ PHP 8.3: Compatible dengan Laravel 13
✅ Aplikasi: Running di http://localhost/gis_perumahan/
✅ Development: Siap untuk build features
```

**Kira-kira 10-15 menit untuk upgrade + test!**

