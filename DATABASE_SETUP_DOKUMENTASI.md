# Database Setup & Data Import - Dokumentasi Lengkap

## ✅ Status: Selesai

Data perumahan telah berhasil dimasukkan ke dalam database dan semua koneksi database sudah siap digunakan.

---

## 📊 Ringkasan Data yang Diimpor

- **Total Perumahan**: 97 records
- **Total Kecamatan**: 9 kecamatan  
- **Database**: `gis_perumahan_zenit`
- **Server**: MySQL (XAMPP)
- **Host**: 127.0.0.1:3306
- **User**: root
- **Password**: (kosong)

### Data per Kecamatan:
```
Cicurug      : ~15 perumahan
Cidahu       : ~15 perumahan
Parakansalak : ~10 perumahan
Klapanunggal : ~20 perumahan
Cikembar     : ~10 perumahan
Parungkuda   : ~12 perumahan
Cisaat       : ~12 perumahan
Nagrak       : ~15 perumahan
Cibadak      : ~10 perumahan
```

---

## 🗄️ Struktur Database

### Tabel Utama: `perumahans`

| Field | Tipe | Keterangan |
|-------|------|-----------|
| id | BIGINT | Primary Key |
| nama | VARCHAR(255) | Nama perumahan |
| slug | VARCHAR(255) | Slug untuk URL (unique) |
| alamat | VARCHAR(255) | Alamat jalan |
| kelurahan | VARCHAR(255) | Desa/Kelurahan |
| kecamatan | VARCHAR(255) | Nama kecamatan |
| kabupaten | VARCHAR(255) | Kabupaten (default: Sukabumi) |
| provinsi | VARCHAR(255) | Provinsi (default: Jawa Barat) |
| latitude | DECIMAL(10,7) | Koordinat lintang (untuk mapping) |
| longitude | DECIMAL(10,7) | Koordinat bujur (untuk mapping) |
| harga_min | BIGINT | Harga minimum perumahan |
| harga_max | BIGINT | Harga maksimum perumahan |
| jenis_perumahan | VARCHAR(255) | Jenis (Subsidi/Cluster/Komersial/Rumah Individu) |
| fasilitas | JSON | Array fasilitas umum |
| status | ENUM | Status (tersedia/proses/terjual/tidak_aktif) |
| is_recommended | BOOLEAN | Rekomendasi khusus |
| created_at | TIMESTAMP | Tanggal dibuat |
| updated_at | TIMESTAMP | Tanggal diubah terakhir |

### Tabel Relasi:
- `perumahan_images`: Gambar/foto perumahan
- `risiko_perumahans`: Data risiko per perumahan
- `risikos`: Master data risiko
- `users`: User/admin

---

## 📁 File-File yang Dibuat

### 1. **database/seeders/data/perumahan_lengkap.csv**
   - File CSV berisi semua 97 data perumahan
   - Format: Semicolon (;) delimiter
   - Kolom: Nama, Alamat, Jenis, Desa, Kecamatan, Lat, Long, Fasilitas, Harga, DEM, Kemiringan, Curah Hujan

### 2. **database/seeders/PerumahanLengkapSeeder.php**
   - Laravel Seeder untuk mengimpor data CSV
   - Bisa dijalankan dengan: `php artisan db:seed --class=PerumahanLengkapSeeder`
   - Sudah terintegrasi dalam `DatabaseSeeder`

### 3. **database/create_tables.sql**
   - Script SQL untuk membuat semua tabel database
   - Jalankan jika perlu setup manual

### 4. **import_data.php**
   - Script PHP standalone untuk import data
   - Tidak membutuhkan Laravel framework
   - Cocok untuk environment PHP 8.0 dengan kompatibilitas terbatas
   - Usage: `php import_data.php`

---

## 🔧 Cara Menggunakan Data di Aplikasi

### 1. **Mengakses Data di Laravel (Controller)**

```php
<?php

namespace App\Http\Controllers;

use App\Models\Perumahan;

class PerumahanController extends Controller
{
    // Get semua perumahan
    public function index()
    {
        $perumahans = Perumahan::all();
        return view('perumahan.index', compact('perumahans'));
    }
    
    // Get perumahan per kecamatan
    public function byKecamatan($kecamatan)
    {
        $perumahans = Perumahan::where('kecamatan', $kecamatan)->get();
        return view('perumahan.list', compact('perumahans'));
    }
    
    // Search berdasarkan lokasi (latitude/longitude)
    public function nearLocation($lat, $lng, $radius = 5)
    {
        $perumahans = Perumahan::whereRaw(
            '( 6371 * acos( cos( radians(?) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(?) ) + sin( radians(?) ) * sin( radians( latitude ) ) ) ) <= ?',
            [$lat, $lng, $lat, $radius]
        )->get();
        
        return response()->json($perumahans);
    }
    
    // Get detail perumahan
    public function show($slug)
    {
        $perumahan = Perumahan::where('slug', $slug)->firstOrFail();
        $images = $perumahan->images;
        
        return view('perumahan.detail', compact('perumahan', 'images'));
    }
}
```

### 2. **Menampilkan di Blade Template**

```blade
<!-- resources/views/perumahan/index.blade.php -->

<div class="perumahan-list">
    @foreach($perumahans as $perumahan)
        <div class="card">
            <h3>{{ $perumahan->nama }}</h3>
            <p><strong>Lokasi:</strong> {{ $perumahan->kelurahan }}, {{ $perumahan->kecamatan }}</p>
            <p><strong>Jenis:</strong> {{ $perumahan->jenis_perumahan }}</p>
            <p><strong>Harga:</strong> Rp {{ number_format($perumahan->harga_min) }} - Rp {{ number_format($perumahan->harga_max) }}</p>
            <p><strong>Fasilitas:</strong> 
                @forelse($perumahan->fasilitas ?? [] as $facility)
                    <span class="badge">{{ $facility }}</span>
                @empty
                    Tidak ada fasilitas terdaftar
                @endforelse
            </p>
            <a href="{{ route('perumahan.show', $perumahan->slug) }}">Lihat Detail</a>
        </div>
    @endforeach
</div>
```

### 3. **Query Contoh**

```php
// Cari perumahan tipe "Subsidi" di Cicurug
$perumahans = Perumahan::where('jenis_perumahan', 'Subsidi')
    ->where('kecamatan', 'Cicurug')
    ->get();

// Cari perumahan dengan fasilitas tertentu
$perumahans = Perumahan::whereJsonContains('fasilitas', 'Sekolah (TK/SD/SMP/SMA)')
    ->get();

// Cari perumahan dalam range harga
$perumahans = Perumahan::whereBetween('harga_min', [180000000, 300000000])
    ->get();

// Cari perumahan terdekat (dengan koordinat)
$nearbyPerumahans = Perumahan::selectRaw(
    "*, ( 6371 * acos( cos( radians(?) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(?) ) + sin( radians(?) ) * sin( radians( latitude ) ) ) ) AS distance",
    [-6.8, 106.8, -6.8]
)->having('distance', '<', 10)
    ->orderBy('distance')
    ->get();
```

### 4. **API Response JSON**

```json
{
  "id": 1,
  "nama": "Griya Nanggerang Cicurug",
  "slug": "griya-nanggerang-cicurug",
  "alamat": "Nanggerang, Kec. Cicurug, Kabupaten Sukabumi, Jawa Barat 43359",
  "kelurahan": "Nanggerang",
  "kecamatan": "Cicurug",
  "kabupaten": "Sukabumi",
  "provinsi": "Jawa Barat",
  "latitude": -6.7803641,
  "longitude": 10.6789801,
  "harga_min": 185000000,
  "harga_max": 185000000,
  "jenis_perumahan": "Subsidi",
  "fasilitas": ["Sekolah (TK/SD/SMP/SMA)"],
  "status": "tersedia",
  "is_recommended": false,
  "created_at": "2026-08-18T18:44:12Z",
  "updated_at": "2026-08-18T18:44:12Z"
}
```

---

## 🗺️ Integrasi dengan Mapping (Google Maps / Leaflet)

```javascript
// Contoh dengan Leaflet.js
const map = L.map('map').setView([-6.8, 106.8], 10);

// Fetch dari API Laravel
fetch('/api/perumahans')
    .then(response => response.json())
    .then(perumahans => {
        perumahans.forEach(perumahan => {
            if (perumahan.latitude && perumahan.longitude) {
                L.marker([perumahan.latitude, perumahan.longitude])
                    .bindPopup(`
                        <strong>${perumahan.nama}</strong><br>
                        ${perumahan.kecamatan}<br>
                        Rp ${perumahan.harga_min.toLocaleString('id-ID')}
                    `)
                    .addTo(map);
            }
        });
    });
```

---

## 🔗 Koneksi Database di .env

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gis_perumahan_zenit
DB_USERNAME=root
DB_PASSWORD=
```

Pengaturan sudah benar di `.env` file Anda.

---

## ⚠️ Catatan Penting

1. **PHP Version**: Project ini memerlukan PHP 8.3+, namun saat ini XAMPP memiliki PHP 8.0
   - Solusi: Upgrade PHP atau gunakan Docker/WSL2
   - Data import berhasil dilakukan karena menggunakan script standalone yang PHP 8.0 compatible

2. **Password Protection**: Database root tidak memiliki password
   - Recommendation: Tambahkan password untuk production

3. **Koordinat**: Beberapa koordinat di CSV mungkin perlu validasi/cleaning lebih lanjut

4. **Backup**: Backup data sebelum melakukan operasi mass-update:
   ```sql
   mysqldump -u root gis_perumahan_zenit > backup_2026_08_18.sql
   ```

---

## 📝 Re-Import Data (Jika Diperlukan)

```bash
# Via PHP Standalone Script
cd c:\Users\user\GiS_Perumahan-
C:\xampp\php\php.exe import_data.php

# Via Laravel Seeder (setelah PHP upgrade)
php artisan db:seed --class=PerumahanLengkapSeeder

# Via SQL Manual
mysql -u root gis_perumahan_zenit < database/create_tables.sql
```

---

## 📞 Debugging & Monitoring

```bash
# Check database connection
php -r "mysqli_connect('127.0.0.1', 'root', '', 'gis_perumahan_zenit') ? print('✓ Connected') : print('✗ Failed');"

# Count records
mysql -u root gis_perumahan_zenit -e "SELECT COUNT(*) as total FROM perumahans;"

# Export data
mysqldump -u root gis_perumahan_zenit > export_perumahan.sql

# Check table structure
mysql -u root gis_perumahan_zenit -e "DESC perumahans;"
```

---

## ✨ Selesai!

Database dan data perumahan sudah siap digunakan dalam aplikasi Laravel Anda. Semua 97 records perumahan dari file CSV telah berhasil diimpor ke database.

Hubungi untuk bantuan lebih lanjut!

