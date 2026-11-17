@extends('layouts.public')

@section('title', 'GIS PERUMAHAN - Peta Publik')

@section('content')
@php
    $mapData = $perumahans->map(function ($item) {
        $cover = $item->images->first();
        
        // Determine single risk: Banjir or Longsor (prioritize highest, level >= sedang)
        $risikos = $item->risikos;
        $highestRisk = null;
        $highestTingkatVal = 0;
        $tingkatValues = ['rendah' => 1, 'sedang' => 2, 'tinggi' => 3, 'kritis' => 4];
        $defaultRiskBasis = [
            'banjir' => 'Berdasarkan peninjauan data GIS terhadap kedekatan dengan sungai atau saluran air, kondisi elevasi dan drainase, serta riwayat banjir bila tersedia. Tidak ditemukan indikator risiko banjir sedang atau lebih tinggi pada data yang tersedia.',
            'longsor' => 'Berdasarkan peninjauan data GIS terhadap topografi atau kemiringan lahan, kondisi tanah/geologi, serta riwayat longsor bila tersedia. Tidak ditemukan indikator risiko longsor sedang atau lebih tinggi pada data yang tersedia.',
        ];
        $riskAnalysis = [];
        
        foreach ($risikos as $r) {
            if (in_array($r->tipe, ['banjir', 'longsor'])) {
                $val = $tingkatValues[$r->tingkat] ?? 0;
                if (!isset($riskAnalysis[$r->tipe]) || $val > $riskAnalysis[$r->tipe]['value']) {
                    $riskAnalysis[$r->tipe] = [
                        'nama' => $r->nama_risiko,
                        'tingkat' => $r->tingkat,
                        'status' => $val >= 2 ? 'danger' : 'safe',
                        'dasar_klasifikasi' => $r->dasar_klasifikasi ?: $defaultRiskBasis[$r->tipe],
                        'value' => $val,
                    ];
                }
                if ($val >= 2 && $val > $highestTingkatVal) {
                    $highestRisk = $r;
                    $highestTingkatVal = $val;
                }
            }
        }

        foreach (['banjir', 'longsor'] as $riskType) {
            $riskAnalysis[$riskType] ??= [
                'nama' => ucfirst($riskType),
                'tingkat' => 'rendah',
                'status' => 'safe',
                'dasar_klasifikasi' => $defaultRiskBasis[$riskType],
                'value' => 1,
            ];
            unset($riskAnalysis[$riskType]['value']);
        }
        
        if ($highestRisk) {
            $singleRiskName = $highestRisk->nama_risiko;
            $singleRiskLevel = $highestRisk->tingkat;
            $singleRiskBasis = $highestRisk->dasar_klasifikasi ?: $defaultRiskBasis[$highestRisk->tipe];
            $singleRiskStatus = 'danger';
            $singleRiskDesc = $highestRisk->deskripsi;
        } else {
            $singleRiskName = 'Aman';
            $singleRiskLevel = 'rendah';
            $singleRiskBasis = 'Klasifikasi berdasarkan peninjauan data GIS lokasi. Tidak ditemukan risiko banjir atau longsor yang ditandai pada data yang tersedia.';
            $singleRiskStatus = 'safe';
            $singleRiskDesc = 'Kawasan perumahan ini aman dari risiko banjir dan longsor.';
        }
        
        return [
            'id' => $item->id,
            'slug' => $item->slug,
            'nama' => $item->nama,
            'deskripsi' => $item->deskripsi,
            'alamat' => $item->alamat,
            'kelurahan' => $item->kelurahan,
            'kecamatan' => $item->kecamatan,
            'kabupaten' => $item->kabupaten,
            'provinsi' => $item->provinsi,
            'latitude' => (float) $item->latitude,
            'longitude' => (float) $item->longitude,
            'harga_min' => $item->harga_min,
            'harga_max' => $item->harga_max,
            'status' => $item->status,
            'jenis_perumahan' => $item->jenis_perumahan,
            'fasilitas' => $item->fasilitas ?? [],
            'telepon' => $item->telepon,
            'email' => $item->email,
            'website_url' => $item->website_url,
            'maps_url' => $item->googleMapsDirectUrl(),
            'single_risk' => [
                'nama' => $singleRiskName,
                'tingkat' => $singleRiskLevel,
                'dasar_klasifikasi' => $singleRiskBasis,
                'status' => $singleRiskStatus,
                'deskripsi' => $singleRiskDesc,
            ],
            'risk_analysis' => $riskAnalysis,
            'cover' => $cover ? asset('storage/' . $cover->path) : null,
            'url' => route('perumahan.show', $item),
        ];
    })->values();

    $fasilitasMapData = $fasilitasUmum->map(fn ($item) => [
        'id' => $item->id,
        'nama' => $item->nama,
        'kategori' => $item->kategori,
        'lat' => (float) $item->latitude,
        'lng' => (float) $item->longitude,
        'is_manual' => (bool) $item->is_manual,
    ])->values();
@endphp

<section
    class="relative min-h-[500px] md:min-h-[560px] bg-cover bg-center bg-no-repeat flex items-center justify-center text-center"
    style="background-image: url('/bgg.jpg');">

    <!-- Overlay -->
    <div class="absolute inset-0 bg-black/60"></div>

    <!-- Content -->
    <div class="relative z-10 mx-auto max-w-6xl px-4 py-20 sm:px-6 md:py-28 lg:px-8 flex flex-col items-center">
        <p class="text-xs sm:text-sm font-black uppercase tracking-[.35em] text-blue-300">
            
        </p>

        <h1 class="mt-4 max-w-5xl text-3xl font-black leading-snug tracking-tight text-white sm:text-4xl md:text-5xl lg:text-[3.25rem]">
            Sistem Informasi Geografis untuk Menemukan Perumahan Terbaik di Kabupaten Sukabumi.
        </h1>

        <p class="mt-4 max-w-3xl text-sm sm:text-base leading-relaxed text-slate-200">
            Temukan lokasi perumahan ideal dengan integrasi data fasilitas publik, analisis risiko bencana, dan navigasi rute terbaik.
        </p>

        <div class="mt-8 flex flex-wrap items-center justify-center gap-3.5">
            <a href="#peta"
                class="rounded-full bg-blue-600 px-8 py-3.5 text-sm font-bold text-white shadow-lg transition hover:bg-blue-700 hover:scale-105">
                Buka Peta GIS
            </a>

            <a href="#data"
                class="rounded-full border border-white/40 bg-white/10 backdrop-blur-sm px-8 py-3.5 text-sm font-bold text-white transition hover:bg-white hover:text-black">
                Lihat Data
            </a>
        </div>
    </div>
</section>

<!-- Statistik di Bawah Foto Hero Banner -->
<section class="relative z-20 -mt-10 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
    <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
        <div class="rounded-3xl border border-line bg-white p-6 shadow-zenit transition hover:-translate-y-1">
            <p class="text-xs font-black uppercase tracking-wider text-slate-400">Total Perumahan</p>
            <p class="mt-2 text-3xl sm:text-4xl font-black text-ink">{{ $perumahans->count() }}</p>
        </div>

        <div class="rounded-3xl border border-line bg-white p-6 shadow-zenit transition hover:-translate-y-1">
            <p class="text-xs font-black uppercase tracking-wider text-slate-400">Fasilitas Umum</p>
            <p class="mt-2 text-3xl sm:text-4xl font-black text-emerald-600">{{ $fasilitasUmum->count() }}</p>
        </div>

        <div class="rounded-3xl border border-line bg-white p-6 shadow-zenit transition hover:-translate-y-1">
            <p class="text-xs font-black uppercase tracking-wider text-slate-400">Kecamatan</p>
            <p class="mt-2 text-3xl sm:text-4xl font-black text-ink">{{ $kecamatans->count() }}</p>
        </div>

        <div class="rounded-3xl border border-line bg-white p-6 shadow-zenit transition hover:-translate-y-1">
            <p class="text-xs font-black uppercase tracking-wider text-slate-400">Risiko Tercatat</p>
            <p class="mt-2 text-3xl sm:text-4xl font-black text-ink">{{ $perumahans->sum(fn($p) => $p->risikos->count()) }}</p>
        </div>
    </div>
</section>

<section id="rekomendasi" class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
    <div class="mb-6 flex flex-col justify-between gap-4 md:flex-row md:items-end">
        <div>
            <p class="text-xs font-black uppercase tracking-[.3em] text-blue-600">Rekomendasi</p>
            <h2 class="mt-2 text-3xl font-black tracking-tight">Lokasi Perumahan yang Di Rekomendasikan</h2>
        </div>
        <div class="rounded-full border border-line bg-white px-4 py-2 text-sm font-black text-slate-600">{{ $recommendedPerumahans->count() }} rekomendasi</div>
    </div>

    @if($recommendedPerumahans->isEmpty())
        <div class="rounded-[2rem] border border-line bg-white p-8 text-center text-sm font-bold text-slate-500">Belum ada rekomendasi perumahan saat ini.</div>
    @else
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($recommendedPerumahans as $item)
                @php
                    $coverImage = $item->images->first();
                    $coverUrl = $coverImage ? asset('storage/' . $coverImage->path) : null;

                    // Hitung tingkat risiko tertinggi
                    $risikos = $item->risikos;
                    $highestRisk = null;
                    $highestTingkatVal = 0;
                    $tingkatValues = ['rendah' => 1, 'sedang' => 2, 'tinggi' => 3, 'kritis' => 4];

                    foreach ($risikos as $r) {
                        if (in_array($r->tipe, ['banjir', 'longsor'])) {
                            $val = $tingkatValues[$r->tingkat] ?? 0;
                            if ($val >= 2 && $val > $highestTingkatVal) {
                                $highestRisk = $r;
                                $highestTingkatVal = $val;
                            }
                        }
                    }

                    $statusMap = [
                        'tersedia' => 'Tersedia',
                        'proses' => 'Proses',
                        'terjual' => 'Terjual',
                        'tidak_aktif' => 'Tidak Aktif'
                    ];
                    $statusDisplay = $statusMap[$item->status] ?? 'Tersedia';
                @endphp
                <article class="card-hover overflow-hidden rounded-[2rem] border border-line bg-white shadow-sm">
                    <a href="{{ route('perumahan.show', $item) }}" class="block w-full text-left">
                        <div class="relative h-52 bg-soft">
                            @if($coverUrl)
                                <img src="{{ $coverUrl }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" alt="{{ $item->nama }}">
                            @else
                                <div class="grid h-full place-items-center text-xs font-black uppercase tracking-wider text-slate-400">Gambar belum tersedia</div>
                            @endif
                            <span class="absolute left-4 top-4 rounded-full bg-white/90 px-3 py-1 text-[11px] font-black text-ink">{{ $statusDisplay }}</span>
                        </div>
                        <div class="p-6">
                            <p class="text-[11px] font-black uppercase tracking-[.22em] text-blue-600">{{ $item->kecamatan ?? '-' }}</p>
                            <h3 class="mt-2 text-lg font-black leading-snug text-ink">{{ $item->nama }}</h3>
                            <p class="mt-2 line-clamp-2 text-sm leading-6 text-slate-500">{{ $item->alamat ?? '-' }}</p>
                            <div class="mt-4 flex flex-wrap gap-2">
                                @if ($highestRisk)
                                    <span class="inline-flex rounded-full bg-red-100 px-3 py-1.5 text-xs font-black uppercase text-red-700">
                                        {{ $highestRisk->nama_risiko }} ({{ ucfirst($highestRisk->tingkat) }})
                                    </span>
                                @else
                                    <span class="inline-flex rounded-full bg-green-100 px-3 py-1.5 text-xs font-black uppercase text-green-700">
                                        Aman
                                    </span>
                                @endif
                            </div>
                            <div class="mt-5 flex items-center justify-between gap-3 border-t border-line pt-4">
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-wider text-slate-400">Harga</p>
                                    <p class="text-sm font-black text-ink">
                                        @if($item->harga_min && $item->harga_max)
                                            Rp {{ number_format($item->harga_min, 0, ',', '.') }} - Rp {{ number_format($item->harga_max, 0, ',', '.') }}
                                        @elseif($item->harga_min)
                                            Rp {{ number_format($item->harga_min, 0, ',', '.') }}
                                        @elseif($item->harga_max)
                                            Rp {{ number_format($item->harga_max, 0, ',', '.') }}
                                        @else
                                            Belum tersedia
                                        @endif
                                    </p>
                                </div>
                                <span class="rounded-full bg-blue-600 px-4 py-2 text-xs font-black text-white hover:bg-blue-700">Detail</span>
                            </div>
                        </div>
                    </a>
                </article>
            @endforeach
        </div>
    @endif
</section>

<section id="peta" class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <div class="mb-6 flex flex-col justify-between gap-4 md:flex-row md:items-end">
        <div><p class="text-xs font-black uppercase tracking-[.3em] text-blue-600">Peta GIS Publik</p><h2 class="mt-2 text-3xl font-black tracking-tight">Titik Lokasi Perumahan</h2></div>
        <div class="flex items-center gap-3">
            <span class="rounded-full border border-line bg-white px-4 py-2 text-sm font-black text-slate-600"><span id="visible-count">{{ $perumahans->count() }}</span> titik tampil</span>
        </div>
    </div>
    <!-- Layer Toggle Buttons -->
    <div class="mb-4 flex flex-wrap gap-2">
        <button id="layer-btn-perumahan" onclick="toggleLayer('perumahan')" class="layer-btn active-layer inline-flex items-center gap-2 rounded-full border border-blue-600 bg-blue-600 px-4 py-2 text-xs font-black text-white transition">
            <span style="display:inline-block;width:12px;height:12px;border-radius:50%;background:#fff;"></span> Layer Perumahan
        </button>
        <button id="layer-btn-fasilitas" onclick="toggleLayer('fasilitas')" class="layer-btn inline-flex items-center gap-2 rounded-full border border-line bg-white px-4 py-2 text-xs font-black text-slate-700 transition hover:border-emerald-500 hover:text-emerald-600">
            <span style="display:inline-block;width:12px;height:12px;border-radius:50%;background:#10b981;"></span> Layer Fasilitas Umum
        </button>
    </div>
    <div class="grid gap-6 lg:grid-cols-[1fr_390px]">
        <div class="overflow-hidden rounded-[2rem] border border-line bg-white p-3 shadow-zenit"><div id="public-map" class="map-shell rounded-[1.5rem]"></div></div>
        <aside class="rounded-[2rem] border border-line bg-white p-6 shadow-zenit">
            <p class="text-xs font-black uppercase tracking-[.3em] text-blue-600">Detail Terpilih</p>
            <div id="selected-detail" class="mt-5 text-sm text-slate-600"><div class="rounded-2xl bg-soft p-6 text-center font-semibold text-slate-500">Klik marker atau kartu data untuk menampilkan detail ringkas.</div></div>
        </aside>
    </div>
</section>

<section id="data" class="border-y border-line bg-soft py-14">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mb-7 flex flex-col justify-between gap-5 xl:flex-row xl:items-end">
            <div><p class="text-xs font-black uppercase tracking-[.3em] text-blue-600">Full Data</p><h2 class="mt-2 text-3xl font-black tracking-tight">Daftar Perumahan</h2></div>
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <input id="filter-keyword" class="rounded-2xl border-line px-4 py-3 text-sm focus:border-blue-600 focus:ring-blue-600" placeholder="Cari nama, alamat, kontak...">
                <select id="filter-kecamatan" class="rounded-2xl border-line px-4 py-3 text-sm focus:border-blue-600 focus:ring-blue-600"><option value="">Semua Kecamatan</option>@foreach ($kecamatans as $kecamatan)<option value="{{ $kecamatan }}">{{ $kecamatan }}</option>@endforeach</select>
                <select id="filter-risk-type" class="rounded-2xl border-line px-4 py-3 text-sm focus:border-blue-600 focus:ring-blue-600"><option value="">Semua Jenis Risiko</option>@foreach ($riskTypes as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach</select>
                <select id="filter-tipe-perumahan" class="rounded-2xl border-line px-4 py-3 text-sm focus:border-blue-600 focus:ring-blue-600">
                    <option value="">Semua Tipe</option>
                    <option value="subsidi">🟢 Subsidi</option>
                    <option value="komersial">🟣 Komersial</option>
                    <option value="individu">🏠 Perumahan Individu</option>
                    <option value="cluster">🏘️ Cluster</option>
                </select>
            </div>
        </div>
        <div id="cards-grid" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3"></div>
    </div>
</section>

<section id="tentang" class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
    <div class="mb-10 text-center">
        <h2 class="text-2xl font-black tracking-tight text-slate-800 sm:text-3xl">Bagaimana SukaGIS Membantu Anda?</h2>
        <div class="mx-auto mt-3 h-1 w-16 rounded-full bg-emerald-800"></div>
    </div>

    <div class="grid gap-6 md:grid-cols-3">
        <!-- Integrasi Data Geospasial -->
        <div class="rounded-2xl border border-line bg-white p-7 shadow-sm transition hover:shadow-md">
            <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-900 text-white shadow-sm">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                </svg>
            </div>
            <h3 class="text-lg font-bold text-slate-800">Integrasi Data Geospasial</h3>
            <p class="mt-2.5 text-sm leading-relaxed text-slate-500">
                Menyatukan data fasilitas publik, akses jalan, dan tata ruang Kabupaten Sukabumi.
            </p>
        </div>

        <!-- Analisis Cerdas -->
        <div class="rounded-2xl border border-line bg-white p-7 shadow-sm transition hover:shadow-md">
            <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-900 text-white shadow-sm">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
            </div>
            <h3 class="text-lg font-bold text-slate-800">Analisis Cerdas</h3>
            <p class="mt-2.5 text-sm leading-relaxed text-slate-500">
                Algoritma yang menghitung skor lokasi berdasarkan kedekatan dengan fasilitas penting dan keamanan lingkungan.
            </p>
        </div>

        <!-- Rekomendasi Personalisasi -->
        <div class="rounded-2xl border border-line bg-white p-7 shadow-sm transition hover:shadow-md">
            <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-900 text-white shadow-sm">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5" />
                </svg>
            </div>
            <h3 class="text-lg font-bold text-slate-800">Rekomendasi Personalisasi</h3>
            <p class="mt-2.5 text-sm leading-relaxed text-slate-500">
                Memberikan saran perumahan yang paling sesuai dengan kriteria dan kebutuhan gaya hidup Anda.
            </p>
        </div>
    </div>
</section>

<script id="perumahan-map-data" type="application/json">{!! json_encode($mapData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
<script id="fasilitas-map-data" type="application/json">{!! json_encode($fasilitasMapData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
@endsection

@push('scripts')
<script>
(function () {
    const raw = document.getElementById('perumahan-map-data').textContent || '[]';
    const allData = JSON.parse(raw).filter(item => Number.isFinite(Number(item.latitude)) && Number.isFinite(Number(item.longitude)) && Number(item.latitude) !== 0 && Number(item.longitude) !== 0);
    const state = { keyword: '', kecamatan: '', riskType: '', status: '', tipePerumahan: '', selected: allData[0] || null, selectedFacility: null };
    const map = L.map('public-map', { zoomControl: true, scrollWheelZoom: true }).setView([-6.9181, 106.9272], 12);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap contributors' }).addTo(map);
    const layer = L.layerGroup().addTo(map);
    const markers = new Map();

    // ── Layer Fasilitas Umum ─────────────────────────────────────────────────────
    const fasilitasLayer = L.layerGroup();
    let activeLayer = 'perumahan'; // 'perumahan' | 'fasilitas' | 'both'

    const fasilitasData = JSON.parse(document.getElementById('fasilitas-map-data').textContent || '[]');
    const fasilitasIcon = kategori => ({
        'Rumah Sakit': '🏥', Klinik: '🏥', Apotek: '💊', Dokter: '⚕️',
        Sekolah: '🏫', 'Taman Kanak-Kanak': '🏫', 'Sekolah Musik': '🎓',
        Universitas: '🎓', 'Perguruan Tinggi': '🎓', 'Tempat Ibadah': '🕌',
        Bank: '🏦', ATM: '🏧', Pasar: '🛒', Minimarket: '🛒',
        Supermarket: '🛒', 'Pusat Perbelanjaan': '🏬', SPBU: '⛽',
    }[kategori] || '📍');

    // Haversine distance (km)
    function haversine(lat1, lng1, lat2, lng2) {
        const R = 6371;
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLng = (lng2 - lng1) * Math.PI / 180;
        const a = Math.sin(dLat/2)**2 + Math.cos(lat1*Math.PI/180) * Math.cos(lat2*Math.PI/180) * Math.sin(dLng/2)**2;
        return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    }

    function getNearestPerumahan(fasLat, fasLng, topN = 4) {
        return allData
            .map(p => ({ ...p, jarak: haversine(fasLat, fasLng, Number(p.latitude), Number(p.longitude)) }))
            .sort((a, b) => a.jarak - b.jarak)
            .slice(0, topN);
    }

    function formatDistance(distance) {
        return distance < 1 ? `${Math.round(distance * 1000)} m` : `${distance.toFixed(2)} km`;
    }

    function getNearestFasilitas(housing, topN = 3) {
        return fasilitasData
            .filter(fas => Number.isFinite(Number(fas.lat)) && Number.isFinite(Number(fas.lng)) && Number(fas.lat) !== 0 && Number(fas.lng) !== 0)
            .map(fas => ({ ...fas, jarak: haversine(Number(housing.latitude), Number(housing.longitude), fas.lat, fas.lng) }))
            .sort((a, b) => a.jarak - b.jarak)
            .slice(0, topN);
    }

    function renderNearestFasilitas(housing) {
        const nearest = getNearestFasilitas(housing);
        if (!nearest.length) return '';
        return `<div style="margin-top:10px;padding-top:10px;border-top:1px solid #e2e8f0;">
            <p style="font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:#94a3b8;margin-bottom:6px;">Fasilitas Umum Terdekat</p>
            ${nearest.map(fas => `<div style="display:flex;justify-content:space-between;gap:8px;font-size:11px;line-height:1.5;margin-top:4px;"><span>${fasilitasIcon(fas.kategori)} ${esc(fas.nama)}</span><b style="white-space:nowrap;color:#059669;">${formatDistance(fas.jarak)}</b></div>`).join('')}
        </div>`;
    }

    function buildFasilitasLayer() {
        fasilitasLayer.clearLayers();
        fasilitasData.forEach(fas => {
            const statusColor = fas.is_manual ? '#0ea5e9' : '#cbd5e1';
            const markerBackground = fas.is_manual ? '#0ea5e9' : '#ffffff';
            const markerIconColor = fas.is_manual ? '#ffffff' : '#64748b';
            const statusLabel = fas.is_manual ? 'Baru ditambahkan' : 'Data impor';
            const fasIcon = L.divIcon({
                className: '',
                html: `<div title="${statusLabel}" style="width:32px;height:32px;background:${markerBackground};border:2px solid ${statusColor};border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:16px;color:${markerIconColor};box-shadow:0 2px 8px rgba(0,0,0,.18);">${fasilitasIcon(fas.kategori)}</div>`,
                iconSize: [32, 32],
                iconAnchor: [16, 16],
                popupAnchor: [0, -18]
            });
            const marker = L.marker([fas.lat, fas.lng], { icon: fasIcon });
            marker.on('click', () => {
                state.selectedFacility = fas;
                renderDetail();
                const nearest = getNearestPerumahan(fas.lat, fas.lng, 4);
                const listHtml = nearest.map((p, i) => {
                    const riskBadge = p.single_risk
                        ? (p.single_risk.status === 'danger'
                            ? `<span style="background:#fee2e2;color:#b91c1c;padding:1px 8px;border-radius:9999px;font-size:10px;font-weight:800;text-transform:uppercase;">${esc(p.single_risk.nama)}</span>`
                            : `<span style="background:#dcfce7;color:#15803d;padding:1px 8px;border-radius:9999px;font-size:10px;font-weight:800;">Aman</span>`)
                        : '';
                    return `<div style="padding:.5rem .6rem;border-radius:.75rem;background:${i===0?'#eff6ff':'#f8fafc'};border:1px solid ${i===0?'#bfdbfe':'#e2e8f0'};">
                        <div style="display:flex;justify-content:space-between;align-items:center;gap:4px;">
                            <b style="font-size:12px;">${i===0?'⭐ ':''}${esc(p.nama)}</b>
                            <span style="background:#2563eb;color:#fff;padding:1px 7px;border-radius:9999px;font-size:10px;font-weight:800;white-space:nowrap;">${p.jarak.toFixed(1)} km</span>
                        </div>
                        <div style="margin-top:3px;font-size:11px;color:#64748b;">${esc(p.kecamatan||'-')} ${riskBadge}</div>
                        <div style="display:flex;align-items:center;gap:8px;margin-top:4px;">
                            <button type="button" class="facility-route-btn" data-facility-id="${fas.id}" data-housing-id="${p.id}" style="border:0;background:transparent;padding:0;color:#2563eb;font-size:10px;font-weight:800;cursor:pointer;">Rute Perumahan → Fasilitas</button>
                            <a href="${esc(p.url)}" style="font-size:10px;color:#2563eb;font-weight:700;">Lihat Detail →</a>
                        </div>
                    </div>`;
                }).join('');
                marker.bindPopup(`
                    <div style="min-width:240px;max-width:280px;">
                        <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
                            <span style="font-size:22px;">${fasilitasIcon(fas.kategori)}</span>
                            <div><p style="font-weight:900;font-size:13px;">${esc(fas.nama)}</p><p style="font-size:10px;color:#64748b;font-weight:600;">${esc(fas.kategori)}</p><span style="display:inline-block;margin-top:4px;background:${fas.is_manual ? '#e0f2fe' : '#f1f5f9'};color:${fas.is_manual ? '#0369a1' : '#64748b'};padding:2px 7px;border-radius:9999px;font-size:9px;font-weight:800;">${fas.is_manual ? 'Baru ditambahkan' : 'Data impor'}</span></div>
                        </div>
                        <p style="font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:#94a3b8;margin-bottom:6px;">Perumahan Terdekat</p>
                        <div style="display:flex;flex-direction:column;gap:6px;">${listHtml}</div>
                    </div>`).openPopup();
            });
            marker.on('popupopen', () => {
                marker.getPopup().getElement().querySelectorAll('.facility-route-btn').forEach(button => {
                    button.onclick = () => {
                        const housing = allData.find(item => String(item.id) === String(button.dataset.housingId));
                        if (!housing) return;
                        button.disabled = true;
                        button.textContent = 'Menghitung rute...';
                        drawFacilityRoute(fas, housing, button);
                    };
                });
            });
            fasilitasLayer.addLayer(marker);
        });
    }
    buildFasilitasLayer();

    // Layer toggle control
    window.toggleLayer = function(which) {
        activeLayer = which;
        document.querySelectorAll('.layer-btn').forEach(b => {
            b.classList.remove('active-layer','bg-blue-600','text-white','border-blue-600','bg-emerald-600','border-emerald-600');
            b.classList.add('bg-white','text-slate-700','border-line');
        });
        const btn = document.getElementById(`layer-btn-${which}`);
        if (which === 'perumahan') {
            btn.classList.add('bg-blue-600','text-white','border-blue-600');
            btn.classList.remove('bg-white','text-slate-700','border-line');
            if (!map.hasLayer(layer)) map.addLayer(layer);
            if (map.hasLayer(fasilitasLayer)) map.removeLayer(fasilitasLayer);
        } else {
            btn.classList.add('bg-emerald-600','text-white','border-emerald-600');
            btn.classList.remove('bg-white','text-slate-700','border-line');
            if (map.hasLayer(layer)) map.removeLayer(layer);
            if (!map.hasLayer(fasilitasLayer)) map.addLayer(fasilitasLayer);
            // fit to fasilitas bounds
            const bounds = fasilitasData.map(f => [f.lat, f.lng]);
            map.fitBounds(bounds, { padding: [40, 40], maxZoom: 13 });
        }
    };
    // Inject layer button active style
    if (!document.getElementById('layer-style')) {
        const ls = document.createElement('style');
        ls.id = 'layer-style';
        ls.textContent = `.layer-btn { transition: all .2s; }`;
        document.head.appendChild(ls);
    }

    // ── Routing State ───────────────────────────────────────────────────────────
    let userLocation = null;
    let userMarker = null;
    let routeLayer = null;
    let routePointLayer = null;

    // Pulse marker CSS for user location
    const userIcon = L.divIcon({
        className: '',
        html: '<div style="width:18px;height:18px;background:#3b82f6;border:3px solid white;border-radius:50%;box-shadow:0 0 0 6px rgba(59,130,246,0.25);animation:pulse-user 1.5s infinite;"></div>',
        iconSize: [18, 18],
        iconAnchor: [9, 9],
        popupAnchor: [0, -12]
    });

    // Inject pulse animation once
    if (!document.getElementById('route-style')) {
        const st = document.createElement('style');
        st.id = 'route-style';
        st.textContent = `
            @keyframes pulse-user { 0%,100%{box-shadow:0 0 0 6px rgba(59,130,246,.25)} 50%{box-shadow:0 0 0 12px rgba(59,130,246,.05)} }
            .route-info-box { background:#fff; border:1px solid #e2e8f0; border-radius:1rem; padding:.75rem 1rem; margin-top:.5rem; font-size:11px; line-height:1.6; }
            .route-info-box strong { color:#2563eb; }
            .btn-route { display:block; width:100%; margin-top:.5rem; padding:.5rem; background:#2563eb; color:#fff; border:none; border-radius:9999px; font-size:11px; font-weight:800; cursor:pointer; text-align:center; }
            .btn-route:disabled { background:#94a3b8; cursor:not-allowed; }
            .btn-route-clear { display:block; width:100%; margin-top:.4rem; padding:.4rem; background:transparent; color:#94a3b8; border:1px solid #e2e8f0; border-radius:9999px; font-size:11px; font-weight:700; cursor:pointer; text-align:center; }
        `;
        document.head.appendChild(st);
    }

    function clearRoute() {
        if (routeLayer) { map.removeLayer(routeLayer); routeLayer = null; }
        if (routePointLayer) { map.removeLayer(routePointLayer); routePointLayer = null; }
    }

    function routePointIcon(color, symbol) {
        return L.divIcon({
            className: '',
            html: `<div style="width:30px;height:30px;background:${color};border:3px solid #fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:15px;box-shadow:0 2px 8px rgba(15,23,42,.35);">${symbol}</div>`,
            iconSize: [30, 30],
            iconAnchor: [15, 15],
            popupAnchor: [0, -16]
        });
    }

    function formatDistance(meters) {
        if (meters >= 1000) return (meters / 1000).toFixed(1) + ' km';
        return Math.round(meters) + ' m';
    }

    function formatDuration(seconds) {
        const mins = Math.round(seconds / 60);
        if (mins < 60) return mins + ' menit';
        const h = Math.floor(mins / 60), m = mins % 60;
        return h + ' jam ' + (m ? m + ' menit' : '');
    }

    function drawFacilityRoute(facility, housing, button) {
        clearRoute();
        const url = `https://router.project-osrm.org/route/v1/driving/${housing.longitude},${housing.latitude};${facility.lng},${facility.lat}?overview=full&geometries=geojson`;
        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (!data.routes || !data.routes.length) {
                    button.textContent = 'Rute tidak ditemukan';
                    button.disabled = false;
                    return;
                }
                const route = data.routes[0];
                routeLayer = L.geoJSON(route.geometry, {
                    style: { color: '#10b981', weight: 5, opacity: 0.85, dashArray: '10, 6', lineCap: 'round' }
                }).addTo(map);
                routePointLayer = L.layerGroup([
                    L.marker([Number(housing.latitude), Number(housing.longitude)], { icon: routePointIcon('#2563eb', '⌂') })
                        .bindPopup(`<b>Lokasi Perumahan</b><br>${esc(housing.nama)}`),
                    L.marker([facility.lat, facility.lng], { icon: routePointIcon('#059669', fasilitasIcon(facility.kategori)) })
                        .bindPopup(`<b>Lokasi Fasilitas</b><br>${esc(facility.nama)}`)
                ]).addTo(map);
                map.fitBounds(routeLayer.getBounds(), { padding: [60, 60] });
                button.textContent = `Perumahan → Fasilitas: ${formatDistance(route.distance)} · ${formatDuration(route.duration)}`;
                button.style.color = '#047857';
                button.disabled = false;
            })
            .catch(() => {
                button.textContent = 'Gagal menghitung rute';
                button.disabled = false;
            });
    }

    function drawRoute(destLat, destLng, callback) {
        if (!userLocation) {
            alert('Lokasi Anda belum terdeteksi. Aktifkan GPS dan coba lagi.');
            return;
        }
        clearRoute();
        const url = `https://router.project-osrm.org/route/v1/driving/${userLocation.lng},${userLocation.lat};${destLng},${destLat}?overview=full&geometries=geojson`;
        fetch(url)
            .then(r => r.json())
            .then(data => {
                if (!data.routes || !data.routes.length) { alert('Rute tidak ditemukan.'); return; }
                const route = data.routes[0];
                routeLayer = L.geoJSON(route.geometry, {
                    style: { color: '#2563eb', weight: 5, opacity: 0.8, dashArray: '10, 6', lineCap: 'round' }
                }).addTo(map);
                const dist = formatDistance(route.distance);
                const dur = formatDuration(route.duration);
                // Fit map to route bounds
                map.fitBounds(routeLayer.getBounds(), { padding: [60, 60] });
                if (callback) callback(dist, dur);
            })
            .catch(() => alert('Gagal menghitung rute. Cek koneksi internet Anda.'));
    }

    function getUserLocation(onSuccess) {
        if (!navigator.geolocation) { alert('Browser tidak mendukung GPS.'); return; }
        navigator.geolocation.getCurrentPosition(
            pos => {
                userLocation = { lat: pos.coords.latitude, lng: pos.coords.longitude };
                if (userMarker) map.removeLayer(userMarker);
                userMarker = L.marker([userLocation.lat, userLocation.lng], { icon: userIcon })
                    .bindPopup('<b style="font-size:12px">📍 Lokasi Anda</b>')
                    .addTo(map);
                if (onSuccess) onSuccess();
            },
            () => alert('Gagal mendapatkan lokasi. Pastikan izin GPS diberikan.')
        );
    }

    const rupiah = value => value ? new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value) : 'Belum tersedia';
    const price = item => item.harga_min && item.harga_max ? `${rupiah(item.harga_min)} - ${rupiah(item.harga_max)}` : rupiah(item.harga_min || item.harga_max);
    const statusText = s => ({ tersedia:'Tersedia', proses:'Proses', terjual:'Terjual', tidak_aktif:'Tidak Aktif' }[s] || '-');
    const riskLevelText = s => ({ rendah:'Rendah', sedang:'Sedang', tinggi:'Tinggi', kritis:'Kritis' }[s] || s);
    const esc = value => String(value ?? '').replace(/[&<>'"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;',"\"":'&quot;'}[c]));
    const valid = item => Number(item.latitude) >= -90 && Number(item.latitude) <= 90 && Number(item.longitude) >= -180 && Number(item.longitude) <= 180;
    const contactText = item => item.telepon || item.email || 'Kontak belum tersedia';

    function currentData() {
        const q = state.keyword.toLowerCase().trim();
        return allData.filter(item => {
            const haystack = [item.nama, item.alamat, item.kelurahan, item.kecamatan, item.telepon, item.email, item.jenis_perumahan].join(' ').toLowerCase();
            let matchesRiskType = true;
            if (state.riskType) {
                if (state.riskType === 'aman') {
                    matchesRiskType = item.single_risk && item.single_risk.status === 'safe';
                } else {
                    matchesRiskType = item.single_risk && item.single_risk.nama.toLowerCase() === state.riskType.toLowerCase();
                }
            }
            return (!q || haystack.includes(q)) && (!state.kecamatan || item.kecamatan === state.kecamatan) && matchesRiskType && (!state.status || item.status === state.status) && (!state.tipePerumahan || String(item.jenis_perumahan || '').toLowerCase() === state.tipePerumahan.toLowerCase());
        });
    }
    function makeIcon(item, active = false) {
        const riskStatus = item.single_risk ? item.single_risk.status : 'safe';
        const riskClass = riskStatus === 'danger' ? 'risk-danger' : 'risk-safe';
        return L.divIcon({ className: '', html: `<div class="zenit-marker ${riskClass} ${active ? 'active' : ''}"></div>`, iconSize: [24,24], iconAnchor: [12,12], popupAnchor: [0,-15] });
    }
    function renderRisks(item) {
        if (!item.single_risk) {
            return '<span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-bold text-slate-700">Belum ada risiko</span>';
        }
        const r = item.single_risk;
        if (r.status === 'danger') {
            return `<span class="inline-flex rounded-full bg-red-100 px-3 py-1.5 text-xs font-black text-red-700 uppercase">${esc(r.nama)} (${esc(riskLevelText(r.tingkat))})</span>`;
        }
        return `<span class="inline-flex rounded-full bg-green-100 px-3 py-1.5 text-xs font-black text-green-700 uppercase">${esc(r.nama)}</span>`;
    }
    function renderRiskAnalysis(item) {
        const risk = item.single_risk;
        if (!risk) return '';
        return `<p class="mt-3 text-xs leading-5 text-slate-600"><b>Berdasarkan:</b> ${esc(risk.dasar_klasifikasi || 'Dasar klasifikasi belum diisi.')}</p>`;
    }
    function renderMarkers(fit = false) {
        const data = currentData().filter(valid); layer.clearLayers(); markers.clear(); const bounds = [];
        data.forEach(item => {
            const marker = L.marker([Number(item.latitude), Number(item.longitude)], { icon: makeIcon(item, state.selected && state.selected.id === item.id) });
            const popupId = `popup-route-${item.id}`;
            marker.bindPopup(`
                <div class="space-y-3" style="min-width:220px">
                    <div><p class="text-base font-black text-ink">${esc(item.nama)}</p><p class="text-xs font-semibold text-slate-500">${esc(item.kecamatan || '-')} · ${esc(statusText(item.status))}</p></div>
                    <div class="rounded-2xl bg-soft p-3"><p class="text-[10px] font-black uppercase tracking-wider text-slate-400">Analisis Risiko</p><div class="mt-2 flex flex-wrap gap-1.5">${renderRisks(item)}</div>${renderRiskAnalysis(item)}</div>
                    <div class="grid gap-1 text-xs text-slate-600"><p><b>Harga:</b> ${esc(price(item))}</p><p><b>Kontak:</b> ${esc(contactText(item))}</p><p><b>Alamat:</b> ${esc(item.alamat || '-')}</p></div>
                    ${renderNearestFasilitas(item)}
                    <div id="${popupId}-info"></div>
                    <button class="btn-route" id="${popupId}-btn">📍 Hitung Jarak & Rute dari Lokasi Saya</button>
                    ${routeLayer ? `<button class="btn-route-clear" id="${popupId}-clear">✕ Hapus Rute</button>` : ''}
                    <div class="grid grid-cols-2 gap-2"><a class="rounded-full bg-blue-600 px-3 py-2 text-center text-xs font-black text-white" href="${esc(item.url)}">Detail</a>${item.maps_url ? `<a target="_blank" class="rounded-full border border-line px-3 py-2 text-center text-xs font-black text-ink" href="${esc(item.maps_url)}">Google Maps</a>` : `<span class="rounded-full border border-line px-3 py-2 text-center text-xs font-black text-slate-400">Maps -</span>`}</div>
                </div>`);
            marker.on('popupopen', () => {
                const btn = document.getElementById(`${popupId}-btn`);
                const infoBox = document.getElementById(`${popupId}-info`);
                const clearBtn = document.getElementById(`${popupId}-clear`);
                if (clearBtn) clearBtn.onclick = () => { clearRoute(); marker.closePopup(); };
                if (btn) btn.onclick = () => {
                    btn.disabled = true; btn.textContent = '⏳ Mendeteksi lokasi...';
                    const doRoute = () => {
                        btn.textContent = '⏳ Menghitung rute...';
                        drawRoute(Number(item.latitude), Number(item.longitude), (dist, dur) => {
                            if (infoBox) infoBox.innerHTML = `<div class="route-info-box">🛣️ <strong>Jarak:</strong> ${dist}<br>⏱️ <strong>Estimasi:</strong> ${dur} (berkendara)</div>`;
                            btn.textContent = '🔄 Hitung Ulang Rute';
                            btn.disabled = false;
                        });
                    };
                    if (userLocation) { doRoute(); }
                    else { getUserLocation(doRoute); }
                };
            });
            marker.on('click', () => selectItem(item, false)); marker.addTo(layer); markers.set(item.id, marker); bounds.push([Number(item.latitude), Number(item.longitude)]);
        });
        document.getElementById('visible-count').textContent = data.length;
        if (fit && bounds.length) map.fitBounds(bounds, { padding: [42,42], maxZoom: 14 });
    }
    function hasActiveFilter() {
        return state.keyword.trim() !== '' || state.kecamatan !== '' || state.riskType !== '' || (state.status !== '' && state.status !== undefined) || state.tipePerumahan !== '';
    }
    function renderCards() {
        const container = document.getElementById('cards-grid');
        const data = currentData();
        if (!data.length) {
            container.innerHTML = '<div class="col-span-full rounded-[2rem] border border-line bg-white py-16 text-center text-sm font-bold text-slate-500">Data tidak ditemukan.</div>';
            return;
        }
        
        const isFiltered = hasActiveFilter();
        const displayData = isFiltered ? data : data.slice(0, 6);

        const tipeLabel = t => ({ subsidi:'Subsidi', komersial:'Komersial', individu:'Perumahan Individu', cluster:'Cluster' }[String(t).toLowerCase()] || (t ? String(t).charAt(0).toUpperCase() + String(t).slice(1) : null));
        let html = displayData.map(item => {
            const tipe = tipeLabel(item.jenis_perumahan);
            const tipeBadge = tipe ? `<span class="inline-flex items-center rounded-full px-2.5 py-1 text-[10px] font-black uppercase tracking-wider ${item.jenis_perumahan && item.jenis_perumahan.toLowerCase()==='subsidi' ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200' : 'bg-violet-50 text-violet-700 ring-1 ring-violet-200'}">${esc(tipe)}</span>` : '';
            return `
            <article class="card-hover overflow-hidden rounded-[2rem] border border-line bg-white shadow-sm">
                <button type="button" data-id="${item.id}" class="select-card block w-full text-left">
                    <div class="relative h-52 bg-soft">${item.cover ? `<img src="${esc(item.cover)}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" alt="${esc(item.nama)}">` : `<div class="grid h-full place-items-center text-xs font-black uppercase tracking-wider text-slate-400">Gambar belum tersedia</div>`}<span class="absolute left-4 top-4 rounded-full bg-white/90 px-3 py-1 text-[11px] font-black text-ink">${esc(statusText(item.status))}</span>${tipe ? `<span class="absolute right-4 top-4 rounded-full px-2.5 py-1 text-[10px] font-black uppercase ${item.jenis_perumahan && item.jenis_perumahan.toLowerCase()==='subsidi' ? 'bg-emerald-600 text-white' : 'bg-violet-600 text-white'}">${esc(tipe)}</span>` : ''}</div>
                    <div class="p-6"><p class="text-[11px] font-black uppercase tracking-[.22em] text-blue-600">${esc(item.kecamatan || '-')}</p><h3 class="mt-2 text-lg font-black leading-snug">${esc(item.nama)}</h3><p class="mt-2 line-clamp-2 text-sm leading-6 text-slate-500">${esc(item.alamat || '-')}</p><div class="mt-3 flex flex-wrap gap-2">${tipeBadge}${renderRisks(item)}</div><div class="mt-5 flex items-center justify-between gap-3 border-t border-line pt-4"><div><p class="text-[10px] font-black uppercase tracking-wider text-slate-400">Harga</p><p class="text-sm font-black">${esc(price(item))}</p></div><a href="${esc(item.url)}" class="rounded-full bg-blue-600 px-4 py-2 text-xs font-black text-white hover:bg-blue-700">Detail</a></div></div>
                </button>
            </article>`;
        }).join('');

        container.innerHTML = html;
        container.querySelectorAll('.select-card').forEach(btn => btn.addEventListener('click', event => { if (event.target.closest('a')) return; const item = allData.find(row => String(row.id) === String(btn.dataset.id)); if (item) selectItem(item, true); }));
    }
    function renderFacilityDetail(facility) {
        const nearest = getNearestPerumahan(facility.lat, facility.lng, 5);
        const nearestHtml = nearest.length
            ? nearest.map((item, index) => `<div class="flex items-center justify-between gap-3 border-b border-line py-2 last:border-0"><div class="min-w-0"><p class="truncate text-xs font-black text-slate-700">${index === 0 ? '⭐ ' : ''}${esc(item.nama)}</p><p class="text-[10px] text-slate-500">${esc(item.kecamatan || '-')}</p></div><span class="shrink-0 rounded-full bg-emerald-50 px-2 py-1 text-[10px] font-black text-emerald-700">${formatDistance(item.jarak)}</span></div>`).join('')
            : '<p class="text-xs text-slate-500">Belum ada perumahan dengan koordinat yang valid.</p>';
        return `<div class="overflow-hidden rounded-2xl bg-emerald-50 p-5"><div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-white text-4xl shadow-sm">${fasilitasIcon(facility.kategori)}</div><p class="mt-4 text-[10px] font-black uppercase tracking-[.2em] text-emerald-700">Fasilitas Umum</p><h3 class="mt-2 text-2xl font-black tracking-tight text-slate-800">${esc(facility.nama)}</h3><p class="mt-1 text-sm font-semibold text-slate-600">${esc(facility.kategori || 'Kategori belum tersedia')}</p></div>
            <dl class="mt-5 grid grid-cols-2 gap-3 text-xs"><div class="col-span-2"><dt class="font-black uppercase tracking-wider text-slate-400">Koordinat</dt><dd class="mt-1 font-semibold text-slate-700">${Number(facility.lat).toFixed(6)}, ${Number(facility.lng).toFixed(6)}</dd></div><div><dt class="font-black uppercase tracking-wider text-slate-400">Perumahan dianalisis</dt><dd class="mt-1 font-black text-emerald-600">${nearest.length} lokasi</dd></div><div><dt class="font-black uppercase tracking-wider text-slate-400">Jarak terdekat</dt><dd class="mt-1 font-black text-emerald-600">${nearest.length ? formatDistance(nearest[0].jarak) : '-'}</dd></div></dl>
            <div class="mt-5 rounded-2xl bg-soft p-4"><p class="text-[10px] font-black uppercase tracking-wider text-slate-400">Analisis Kedekatan</p><p class="mt-2 text-xs leading-5 text-slate-600">Daftar berikut diurutkan dari perumahan dengan jarak garis lurus paling dekat ke fasilitas ini.</p><div class="mt-3">${nearestHtml}</div></div>`;
    }
    function renderDetail() {
        const box = document.getElementById('selected-detail');
        if (state.selectedFacility) { box.innerHTML = renderFacilityDetail(state.selectedFacility); return; }
        const item = state.selected;
        if (!item) { box.innerHTML = '<div class="rounded-2xl bg-soft p-6 text-center font-semibold text-slate-500">Belum ada data dipilih.</div>'; return; }
        
        const detailRiskBadge = item.single_risk ? (item.single_risk.status === 'danger' ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700') : 'bg-slate-100 text-slate-700';
        const detailRiskLabel = item.single_risk ? (item.single_risk.status === 'danger' ? `${item.single_risk.nama} (${riskLevelText(item.single_risk.tingkat)})` : item.single_risk.nama) : '-';
        const detailRiskDesc = item.single_risk ? item.single_risk.deskripsi : 'Belum ada deskripsi analisis risiko.';
        
        box.innerHTML = `
            <div class="overflow-hidden rounded-2xl bg-soft">${item.cover ? `<img src="${esc(item.cover)}" class="h-48 w-full object-cover" alt="${esc(item.nama)}">` : `<div class="grid h-48 place-items-center text-xs font-black text-slate-400">Gambar belum tersedia</div>`}</div>
            <div class="mt-5"><span class="inline-flex rounded-full ${detailRiskBadge} px-3 py-1.5 text-[11px] font-black uppercase">${esc(detailRiskLabel)}</span><h3 class="mt-3 text-2xl font-black tracking-tight">${esc(item.nama)}</h3><p class="mt-3 rounded-2xl bg-soft p-4 text-sm leading-7 text-slate-600">${esc(item.deskripsi || 'Deskripsi belum tersedia.')}</p></div>
            <dl class="mt-5 grid grid-cols-2 gap-3 text-xs"><div class="col-span-2"><dt class="font-black uppercase tracking-wider text-slate-400">Alamat</dt><dd class="mt-1 font-semibold text-slate-700">${esc(item.alamat || '-')}</dd></div><div><dt class="font-black uppercase tracking-wider text-slate-400">Kecamatan</dt><dd class="mt-1 font-semibold text-slate-700">${esc(item.kecamatan || '-')}</dd></div><div><dt class="font-black uppercase tracking-wider text-slate-400">Tipe Perumahan</dt><dd class="mt-1"><span class="inline-flex rounded-full px-2.5 py-1 text-[10px] font-black uppercase ${item.jenis_perumahan && item.jenis_perumahan.toLowerCase()==='subsidi' ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200' : item.jenis_perumahan ? 'bg-violet-50 text-violet-700 ring-1 ring-violet-200' : 'bg-slate-100 text-slate-500'}">${esc(item.jenis_perumahan ? (item.jenis_perumahan.charAt(0).toUpperCase() + item.jenis_perumahan.slice(1)) : 'Belum tersedia')}</span></dd></div><div><dt class="font-black uppercase tracking-wider text-slate-400">Harga</dt><dd class="mt-1 font-black text-blue-600">${esc(price(item))}</dd></div><div><dt class="font-black uppercase tracking-wider text-slate-400">Telepon</dt><dd class="mt-1 font-semibold text-slate-700">${esc(item.telepon || '-')}</dd></div><div><dt class="font-black uppercase tracking-wider text-slate-400">Email</dt><dd class="mt-1 font-semibold text-slate-700">${esc(item.email || '-')}</dd></div></dl>
            <div class="mt-5 rounded-2xl bg-soft p-4"><p class="text-[10px] font-black uppercase tracking-wider text-slate-400">Analisis Risiko</p><div class="mt-2 flex flex-wrap gap-2">${renderRisks(item)}</div><p class="mt-3 text-xs leading-5 text-slate-600 font-semibold">${esc(detailRiskDesc)}</p></div>
            <div id="detail-route-info" class="mt-3"></div>
            <button id="detail-route-btn" class="btn-route mt-3">📍 Hitung Jarak & Rute dari Lokasi Saya</button>
            <div class="mt-5 grid grid-cols-2 gap-2"><a href="${esc(item.url)}" class="rounded-full btn-blue px-4 py-3 text-center text-xs font-black">Detail Lengkap</a>${item.maps_url ? `<a href="${esc(item.maps_url)}" target="_blank" class="rounded-full border border-line bg-white px-4 py-3 text-center text-xs font-black text-ink">Google Maps</a>` : `<span class="rounded-full border border-line bg-white px-4 py-3 text-center text-xs font-black text-slate-400">Maps -</span>`}</div>`;
        // Bind route button in detail panel
        const detailBtn = document.getElementById('detail-route-btn');
        const detailInfoBox = document.getElementById('detail-route-info');
        if (detailBtn) detailBtn.onclick = () => {
            detailBtn.disabled = true; detailBtn.textContent = '⏳ Mendeteksi lokasi...';
            const doRoute = () => {
                detailBtn.textContent = '⏳ Menghitung rute...';
                drawRoute(Number(item.latitude), Number(item.longitude), (dist, dur) => {
                    if (detailInfoBox) detailInfoBox.innerHTML = `<div class="route-info-box">🛣️ <strong>Jarak:</strong> ${dist}<br>⏱️ <strong>Estimasi:</strong> ${dur} (berkendara)<br><button class="btn-route-clear" onclick="document.getElementById('detail-route-btn').disabled=false;document.getElementById('detail-route-btn').textContent='📍 Hitung Jarak & Rute dari Lokasi Saya';">✕ Hapus Rute</button></div>`;
                    detailBtn.textContent = '🔄 Hitung Ulang Rute';
                    detailBtn.disabled = false;
                    // also scroll map into view
                    document.getElementById('public-map').scrollIntoView({ behavior: 'smooth', block: 'center' });
                });
            };
            if (userLocation) { doRoute(); }
            else { getUserLocation(doRoute); }
        };
    }
    function selectItem(item, focusMap) { state.selected = item; state.selectedFacility = null; renderDetail(); renderMarkers(false); if (valid(item)) { const marker = markers.get(item.id); if (marker) marker.openPopup(); if (focusMap) { map.setView([Number(item.latitude), Number(item.longitude)], Math.max(map.getZoom(), 15)); } } }
    function refresh(fit=false) { renderMarkers(fit); renderCards(); renderDetail(); }
    const filterKeyword = document.getElementById('filter-keyword');
    if (filterKeyword) filterKeyword.addEventListener('input', e => { state.keyword = e.target.value; refresh(true); });
    const filterKecamatan = document.getElementById('filter-kecamatan');
    if (filterKecamatan) filterKecamatan.addEventListener('change', e => { state.kecamatan = e.target.value; refresh(true); });
    const filterRiskType = document.getElementById('filter-risk-type');
    if (filterRiskType) filterRiskType.addEventListener('change', e => { state.riskType = e.target.value; refresh(true); });
    const filterStatus = document.getElementById('filter-status');
    if (filterStatus) filterStatus.addEventListener('change', e => { state.status = e.target.value; refresh(true); });
    const filterTipe = document.getElementById('filter-tipe-perumahan');
    if (filterTipe) filterTipe.addEventListener('change', e => { state.tipePerumahan = e.target.value; refresh(true); });
    // Initial load: show map markers and first 6 cards
    renderMarkers(true); renderCards(); renderDetail();
    setTimeout(() => map.invalidateSize(), 250);
})();
</script>
@endpush
