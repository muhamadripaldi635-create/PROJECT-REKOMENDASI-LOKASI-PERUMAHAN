# 🚀 Quick Start Guide - Database Perumahan

## ✅ Status: SIAP DIGUNAKAN

Database telah berhasil diatur dengan **101 records perumahan** dari 9 kecamatan di Sukabumi.

---

## 🎯 Setup Cepat (5 Menit)

### 1. Verifikasi Database
```bash
# Jalankan verification script
cd c:\Users\user\GiS_Perumahan-
C:\xampp\php\php.exe verify_database.php
```

Hasilnya akan menunjukkan:
- ✅ Database connection status
- 📊 Total records (101 perumahan)
- 📍 Breakdown by district/type
- 🗺️ Koordinat status

### 2. Koneksi Database di Laravel

File `.env` sudah dikonfigurasi dengan benar:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gis_perumahan_zenit
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Test Koneksi di Tinker
```bash
C:\xampp\php\php.exe artisan tinker

# Di dalam tinker:
>>> App\Models\Perumahan::count()  // Harus return 101

>>> App\Models\Perumahan::where('kecamatan', 'Cicurug')->count()

>>> App\Models\Perumahan::first()
```

---

## 📝 Contoh Penggunaan di Controller

### List Semua Perumahan
```php
<?php
namespace App\Http\Controllers;

use App\Models\Perumahan;

class PerumahanController extends Controller
{
    public function index()
    {
        $perumahans = Perumahan::paginate(15);
        return view('perumahan.index', ['perumahans' => $perumahans]);
    }
}
```

### Filter by Kecamatan
```php
public function byDistrict($kecamatan)
{
    $perumahans = Perumahan::where('kecamatan', $kecamatan)
        ->paginate(10);
    
    return view('perumahan.list', [
        'perumahans' => $perumahans,
        'district' => $kecamatan
    ]);
}
```

### Search dengan Multiple Criteria
```php
public function search(Request $request)
{
    $query = Perumahan::query();
    
    if ($request->has('kecamatan')) {
        $query->where('kecamatan', $request->kecamatan);
    }
    
    if ($request->has('jenis')) {
        $query->where('jenis_perumahan', $request->jenis);
    }
    
    if ($request->has('min_price') && $request->has('max_price')) {
        $query->whereBetween('harga_min', [
            $request->min_price,
            $request->max_price
        ]);
    }
    
    if ($request->has('search')) {
        $query->where('nama', 'LIKE', '%' . $request->search . '%')
              ->orWhere('alamat', 'LIKE', '%' . $request->search . '%');
    }
    
    return response()->json($query->get());
}
```

### Nearby Locations (Radius Search)
```php
public function nearLocation($lat, $lng, $radius = 5)
{
    $perumahans = Perumahan::selectRaw(
        "*, 
         (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * 
          cos(radians(longitude) - radians(?)) + 
          sin(radians(?)) * sin(radians(latitude)))) AS distance",
        [$lat, $lng, $lat]
    )
    ->having('distance', '<=', $radius)
    ->orderBy('distance')
    ->get();
    
    return response()->json($perumahans);
}
```

---

## 🎨 Contoh di Blade Template

### Tabel Perumahan
```blade
<table class="table">
    <thead>
        <tr>
            <th>Nama</th>
            <th>Lokasi</th>
            <th>Jenis</th>
            <th>Harga</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @forelse($perumahans as $p)
            <tr>
                <td>
                    <a href="{{ route('perumahan.show', $p->slug) }}">
                        {{ $p->nama }}
                    </a>
                </td>
                <td>{{ $p->kecamatan }}</td>
                <td><span class="badge">{{ $p->jenis_perumahan }}</span></td>
                <td>Rp {{ number_format($p->harga_min) }}</td>
                <td>
                    <a href="{{ route('perumahan.show', $p->slug) }}" class="btn btn-sm btn-info">
                        Detail
                    </a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center">Tidak ada data</td>
            </tr>
        @endforelse
    </tbody>
</table>

{{ $perumahans->links() }}
```

### Detail Card dengan Peta
```blade
<div class="row">
    <div class="col-md-8">
        <h1>{{ $perumahan->nama }}</h1>
        <p class="text-muted">{{ $perumahan->alamat }}</p>
        
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Informasi Perumahan</h5>
                <table class="table">
                    <tr>
                        <td>Kelurahan</td>
                        <td><strong>{{ $perumahan->kelurahan }}</strong></td>
                    </tr>
                    <tr>
                        <td>Kecamatan</td>
                        <td><strong>{{ $perumahan->kecamatan }}</strong></td>
                    </tr>
                    <tr>
                        <td>Jenis</td>
                        <td><strong>{{ $perumahan->jenis_perumahan }}</strong></td>
                    </tr>
                    <tr>
                        <td>Harga</td>
                        <td>
                            <strong>
                                Rp {{ number_format($perumahan->harga_min) }} 
                                - Rp {{ number_format($perumahan->harga_max) }}
                            </strong>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        
        @if($perumahan->fasilitas)
        <div class="card mt-3">
            <div class="card-body">
                <h5 class="card-title">Fasilitas</h5>
                <div class="d-flex flex-wrap gap-2">
                    @foreach($perumahan->fasilitas as $f)
                        <span class="badge bg-info">{{ $f }}</span>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>
    
    <div class="col-md-4">
        <!-- Map -->
        <div id="map" style="height: 400px; border-radius: 8px;"></div>
        
        <script>
            const map = L.map('map').setView(
                [{{ $perumahan->latitude }}, {{ $perumahan->longitude }}],
                15
            );
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
            
            L.marker([{{ $perumahan->latitude }}, {{ $perumahan->longitude }}])
                .bindPopup("<strong>{{ $perumahan->nama }}</strong>")
                .addTo(map);
        </script>
    </div>
</div>
```

---

## 🔌 API Routes (Contoh)

```php
// routes/api.php
Route::get('/perumahans', [PerumahanController::class, 'index']);
Route::get('/perumahans/search', [PerumahanController::class, 'search']);
Route::get('/perumahans/{slug}', [PerumahanController::class, 'show']);
Route::get('/perumahans/district/{kecamatan}', [PerumahanController::class, 'byDistrict']);
Route::get('/perumahans/nearby/{lat}/{lng}', [PerumahanController::class, 'nearLocation']);

// API Response:
// GET /api/perumahans → JSON array of all perumahans
// GET /api/perumahans/search?kecamatan=Cicurug → Filtered results
// GET /api/perumahans/griya-nanggerang-cicurug → Single perumahan detail
```

---

## 📱 JavaScript Integration

### Fetch Data dengan AJAX
```javascript
// Dapatkan semua perumahan
fetch('/api/perumahans')
    .then(res => res.json())
    .then(data => {
        console.log('Total perumahans:', data.length);
        displayPerumahans(data);
    });

// Search with filters
const params = new URLSearchParams({
    kecamatan: 'Cicurug',
    jenis: 'Subsidi',
    min_price: 100000000
});

fetch(`/api/perumahans/search?${params}`)
    .then(res => res.json())
    .then(data => displayPerumahans(data));

// Radius search (5km from location)
fetch(`/api/perumahans/nearby/-6.8/106.8?radius=5`)
    .then(res => res.json())
    .then(data => {
        data.forEach(p => {
            // Add marker to map
            L.marker([p.latitude, p.longitude])
                .bindPopup(`<strong>${p.nama}</strong>`)
                .addTo(map);
        });
    });
```

### Vue.js Component
```vue
<template>
    <div class="perumahan-list">
        <input v-model="searchQuery" type="text" placeholder="Cari perumahan...">
        
        <div class="results">
            <div v-for="p in filteredPerumahans" :key="p.id" class="card">
                <h3>{{ p.nama }}</h3>
                <p>{{ p.kecamatan }} - {{ p.jenis_perumahan }}</p>
                <p>Rp {{ formatPrice(p.harga_min) }}</p>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            searchQuery: '',
            perumahans: []
        }
    },
    computed: {
        filteredPerumahans() {
            return this.perumahans.filter(p => 
                p.nama.toLowerCase().includes(this.searchQuery.toLowerCase())
            );
        }
    },
    methods: {
        formatPrice(price) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR'
            }).format(price);
        }
    },
    mounted() {
        fetch('/api/perumahans')
            .then(res => res.json())
            .then(data => this.perumahans = data);
    }
}
</script>
```

---

## 🔍 Query Cheatsheet

```php
// Count
Perumahan::count()

// Get by ID
Perumahan::find(1)

// Get by slug
Perumahan::where('slug', 'griya-nanggerang-cicurug')->first()

// Filter by district
Perumahan::where('kecamatan', 'Cicurug')->get()

// Filter by type
Perumahan::where('jenis_perumahan', 'Subsidi')->get()

// Price range
Perumahan::whereBetween('harga_min', [100000000, 500000000])->get()

// With pagination
Perumahan::paginate(15)

// Order by
Perumahan::orderBy('nama')->get()
Perumahan::orderBy('harga_min', 'asc')->get()

// With search
Perumahan::where('nama', 'LIKE', '%Cicurug%')->get()

// JSON search
Perumahan::whereJsonContains('fasilitas', 'Sekolah')->get()

// Complex query
Perumahan::where('kecamatan', 'Cicurug')
    ->where('jenis_perumahan', 'Subsidi')
    ->whereBetween('harga_min', [150000000, 300000000])
    ->orderBy('harga_min')
    ->paginate(10)
```

---

## ⚡ Performance Tips

1. **Gunakan Index**
   - `kecamatan`, `latitude`, `longitude`, `status` sudah terindex

2. **Eager Loading** (Hindari N+1 problem)
   ```php
   Perumahan::with('images')->get()
   ```

3. **Select Only Needed Columns**
   ```php
   Perumahan::select('id', 'nama', 'kecamatan', 'harga_min')->get()
   ```

4. **Cache Results**
   ```php
   Cache::remember('perumahans.cicurug', 3600, function() {
       return Perumahan::where('kecamatan', 'Cicurug')->get();
   });
   ```

---

## 📞 Troubleshooting

### Connection Error
```bash
# Check MySQL status
netstat -an | findstr 3306

# Test connection
php -r "mysqli_connect('127.0.0.1', 'root', '', 'gis_perumahan_zenit') ? print('OK') : print('Error');"
```

### No Records Found
```bash
# Check data
mysql -u root gis_perumahan_zenit -e "SELECT COUNT(*) FROM perumahans;"

# Re-import if needed
php import_data.php
```

### Tinker Not Working (PHP 8.0)
```bash
# Use artisan commands instead or upgrade PHP to 8.3+
# Or use raw SQL queries
```

---

## 📚 File Reference

| File | Purpose |
|------|---------|
| `DATABASE_SETUP_DOKUMENTASI.md` | Dokumentasi lengkap setup |
| `verify_database.php` | Script verifikasi database |
| `import_data.php` | Script import data (standalone) |
| `database/create_tables.sql` | SQL untuk create tables |
| `database/seeders/PerumahanLengkapSeeder.php` | Laravel seeder |
| `database/seeders/data/perumahan_lengkap.csv` | Data CSV original |

---

## ✅ Checklist Sebelum Production

- [ ] Backup database: `mysqldump -u root gis_perumahan_zenit > backup.sql`
- [ ] Update DB password dari `.env`
- [ ] Setup caching untuk improved performance
- [ ] Configure API rate limiting
- [ ] Setup CORS if using separate frontend
- [ ] Add database connection pooling
- [ ] Configure search indexing

---

## 🎉 Selesai!

Database siap digunakan. Mulai building features dengan data perumahan ini! 

Pertanyaan? Lihat `DATABASE_SETUP_DOKUMENTASI.md` untuk detail lebih lanjut.

