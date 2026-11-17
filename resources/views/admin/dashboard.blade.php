@extends('layouts.admin')
@section('title', 'Dashboard Admin')
@section('page_title', 'Dashboard')
@section('content')
<div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-5">
    @foreach ([['Total Perumahan',$totalPerumahan,'holiday_village'],['Kecamatan',$totalKecamatan,'map'],['Total Risiko',$totalRisiko,'warning'],['Risiko Tinggi/Kritis',$risikoTinggi,'priority_high'],['Fasilitas Umum',$totalFasilitasUmum,'local_hospital']] as $card)
        <div class="card rounded-[2rem] p-6"><span class="material-symbols-outlined text-blue-600">{{ $card[2] }}</span><p class="mt-4 text-sm font-bold text-slate-500">{{ $card[0] }}</p><p class="mt-2 text-4xl font-black">{{ $card[1] }}</p></div>
    @endforeach
</div>

<!-- Peta GIS -->
<section class="mt-7 card rounded-[2rem] p-6">
    <div class="mb-6 flex items-center justify-between">
        <h2 class="text-xl font-black">Peta GIS Perumahan & Fasilitas</h2>
        <div class="flex items-center gap-3">
            <span class="rounded-full border border-line bg-white px-4 py-2 text-sm font-black text-slate-600"><span id="visible-count">{{ $mapData->count() }}</span> titik tampil</span>
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
    
    <div class="overflow-hidden rounded-[1.5rem] border border-line bg-white p-3">
        <div id="admin-map" class="map-shell rounded-[1.5rem]" style="height: 500px;"></div>
    </div>
</section>

<div class="mt-7 grid gap-7 xl:grid-cols-2">
    <section class="card rounded-[2rem] p-6"><div class="flex items-center justify-between"><h2 class="text-xl font-black">Data Perumahan Terbaru</h2></div><div class="mt-5 space-y-3">@forelse($perumahanTerbaru as $item)<div class="rounded-2xl border border-line bg-soft p-4"><p class="font-black">{{ $item->nama }}</p><p class="mt-1 text-sm text-slate-500">{{ $item->kecamatan ?: '-' }} · {{ $item->risikos_count }} risiko</p></div>@empty<p class="text-sm font-bold text-slate-500">Belum ada data.</p>@endforelse</div></section>
    <div class="space-y-7">
        <section class="card rounded-[2rem] p-6">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-black">Grafik Risiko</h2>
            </div>
            <div class="mt-6 flex flex-col items-center gap-8 sm:flex-row sm:items-center sm:justify-center">
                @php
                    $riskColors = [
                        'rendah' => ['bar' => 'bg-emerald-500', 'text' => 'text-emerald-700'],
                        'sedang' => ['bar' => 'bg-amber-400', 'text' => 'text-amber-700'],
                        'tinggi' => ['bar' => 'bg-orange-500', 'text' => 'text-orange-700'],
                    ];
                    $riskTotal = $grafikRisiko->sum();
                    $rendahEnd = $riskTotal ? ($grafikRisiko['rendah'] / $riskTotal) * 100 : 0;
                    $sedangEnd = $riskTotal ? $rendahEnd + (($grafikRisiko['sedang'] / $riskTotal) * 100) : 0;
                @endphp
                <div class="relative h-48 w-48 shrink-0 rounded-full p-5" role="img" aria-label="Grafik {{ $riskTotal }} total risiko" style="background: conic-gradient(#10b981 0 {{ $rendahEnd }}%, #fbbf24 {{ $rendahEnd }}% {{ $sedangEnd }}%, #f97316 {{ $sedangEnd }}% 100%);">
                    <div class="grid h-full w-full place-items-center rounded-full bg-white text-center shadow-inner">
                        <div>
                            <p class="text-3xl font-black text-slate-900">{{ $riskTotal }}</p>
                            <p class="text-xs font-bold text-slate-500">Total Risiko</p>
                        </div>
                    </div>
                </div>
                <div class="w-full max-w-xs space-y-4">
                    @foreach($grafikRisiko as $tingkat => $total)
                        <div class="flex items-center justify-between gap-4 text-sm">
                            <div class="flex items-center gap-2 font-bold capitalize {{ $riskColors[$tingkat]['text'] }}">
                                <span class="h-3 w-3 rounded-full {{ $riskColors[$tingkat]['bar'] }}"></span>{{ $tingkat }}
                            </div>
                            <span class="font-bold text-slate-500">{{ $total }} risiko</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
        <section class="card rounded-[2rem] p-6">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-black">Grafik Fasilitas Umum</h2>
            </div>
            <div class="mt-6 flex flex-col items-center gap-8 sm:flex-row sm:items-center sm:justify-center">
                @php
                    $facilityTotal = $grafikFasilitas->sum();
                    $facilityColors = ['#2563eb', '#10b981', '#f59e0b', '#f97316', '#8b5cf6', '#ec4899', '#14b8a6', '#64748b'];
                    $facilityStops = [];
                    $facilityOffset = 0;
                    foreach ($grafikFasilitas as $kategori => $total) {
                        $facilityNext = $facilityTotal ? $facilityOffset + (($total / $facilityTotal) * 100) : 0;
                        $facilityStops[] = $facilityColors[count($facilityStops) % count($facilityColors)] . " {$facilityOffset}% {$facilityNext}%";
                        $facilityOffset = $facilityNext;
                    }
                    $facilityGradient = $facilityStops ? implode(', ', $facilityStops) : '#e2e8f0 0 100%';
                @endphp
                <div class="relative h-48 w-48 shrink-0 rounded-full p-5" role="img" aria-label="Grafik {{ $facilityTotal }} total fasilitas umum" style="background: conic-gradient({{ $facilityGradient }});">
                    <div class="grid h-full w-full place-items-center rounded-full bg-white text-center shadow-inner">
                        <div>
                            <p class="text-3xl font-black text-slate-900">{{ $facilityTotal }}</p>
                            <p class="text-xs font-bold text-slate-500">Total Fasilitas</p>
                        </div>
                    </div>
                </div>
                <div class="grid w-full max-w-xs grid-cols-1 gap-3 sm:max-h-48 sm:overflow-y-auto">
                    @forelse($grafikFasilitas as $kategori => $total)
                        @php $facilityColor = $facilityColors[$loop->index % count($facilityColors)]; @endphp
                        <div class="flex items-center justify-between gap-4 text-sm">
                            <div class="flex min-w-0 items-center gap-2 font-bold text-slate-700">
                                <span class="h-3 w-3 shrink-0 rounded-full" style="background-color: {{ $facilityColor }}"></span>
                                <span class="truncate">{{ $kategori }}</span>
                            </div>
                            <span class="shrink-0 font-bold text-slate-500">{{ $total }}</span>
                        </div>
                    @empty
                        <p class="text-sm font-bold text-slate-500">Belum ada data fasilitas umum.</p>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
</div>

<script id="admin-perumahan-map-data" type="application/json">{!! json_encode($mapData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
<script id="admin-fasilitas-map-data" type="application/json">{!! json_encode($fasilitasMapData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>

@endsection

@push('scripts')
<script>
(function () {
    const raw = document.getElementById('admin-perumahan-map-data').textContent || '[]';
    const allData = JSON.parse(raw).filter(item => Number.isFinite(Number(item.latitude)) && Number.isFinite(Number(item.longitude)) && Number(item.latitude) !== 0 && Number(item.longitude) !== 0);
    const map = L.map('admin-map', { zoomControl: true, scrollWheelZoom: true }).setView([-6.9181, 106.9272], 12);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap contributors' }).addTo(map);
    const layer = L.layerGroup().addTo(map);
    const markers = new Map();

    // Layer Fasilitas Umum
    const fasilitasLayer = L.layerGroup();
    let activeLayer = 'perumahan';

    const fasilitasData = JSON.parse(document.getElementById('admin-fasilitas-map-data').textContent || '[]');
    const fasilitasIcon = kategori => ({
        'Rumah Sakit': '🏥', Klinik: '🏥', Apotek: '💊', Dokter: '⚕️',
        Sekolah: '🏫', 'Taman Kanak-Kanak': '🏫', 'Sekolah Musik': '🎓',
        Universitas: '🎓', 'Perguruan Tinggi': '🎓', 'Tempat Ibadah': '🕌',
        Bank: '🏦', ATM: '🏧', Pasar: '🛒', Minimarket: '🛒',
        Supermarket: '🛒', 'Pusat Perbelanjaan': '🏬', SPBU: '⛽',
    }[kategori] || '📍');
    function distanceKm(lat1, lng1, lat2, lng2) {
        const earthRadiusKm = 6371;
        const toRadians = value => value * Math.PI / 180;
        const latitudeDelta = toRadians(lat2 - lat1);
        const longitudeDelta = toRadians(lng2 - lng1);
        const a = Math.sin(latitudeDelta / 2) ** 2
            + Math.cos(toRadians(lat1)) * Math.cos(toRadians(lat2)) * Math.sin(longitudeDelta / 2) ** 2;
        return earthRadiusKm * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    }
    function formatDistance(distance) {
        return distance < 1 ? `${Math.round(distance * 1000)} m` : `${distance.toFixed(2)} km`;
    }
    function nearbyPerumahan(fas) {
        return allData
            .map(item => ({ ...item, distance: distanceKm(fas.lat, fas.lng, Number(item.latitude), Number(item.longitude)) }))
            .sort((first, second) => first.distance - second.distance || Number(first.id) - Number(second.id))
            .slice(0, 5);
    }
    function buildFasilitasPopup(fas) {
        const nearby = nearbyPerumahan(fas);
        const nearbyList = nearby.length
            ? `<div style="margin-top:10px;"><p style="font-size:11px;font-weight:800;margin-bottom:5px;">Perumahan terdekat dari fasilitas ini:</p>${nearby.map(item => `<div style="display:flex;justify-content:space-between;gap:12px;font-size:11px;line-height:1.5;margin-top:4px;"><span>${item.nama}</span><b style="white-space:nowrap;color:#2563eb;">${formatDistance(item.distance)}</b></div>`).join('')}</div>`
            : '<p style="font-size:11px;color:#64748b;margin-top:10px;">Belum ada data perumahan berkoordinat.</p>';
        const status = fas.is_manual ? '<span style="display:inline-block;margin-top:6px;background:#e0f2fe;color:#0369a1;padding:3px 8px;border-radius:9999px;font-size:10px;font-weight:800;">Baru ditambahkan</span>' : '<span style="display:inline-block;margin-top:6px;background:#f1f5f9;color:#64748b;padding:3px 8px;border-radius:9999px;font-size:10px;font-weight:800;">Data impor</span>';
        return `<div style="min-width:260px;"><p style="font-weight:900;font-size:13px;">${fas.nama}</p><p style="font-size:10px;color:#64748b;font-weight:600;margin-top:4px;">${fas.kategori}</p>${status}<p style="font-size:10px;color:#64748b;margin-top:4px;">Koordinat: ${fas.lat}, ${fas.lng}</p>${nearbyList}</div>`;
    }

    function buildFasilitasLayer() {
        fasilitasLayer.clearLayers();
        fasilitasData.forEach(fas => {
            const fasIcon = L.divIcon({
                className: '',
                html: `<div style="width:32px;height:32px;background:${fas.is_manual ? '#0ea5e9' : '#ffffff'};border:2px solid ${fas.is_manual ? '#0ea5e9' : '#cbd5e1'};border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:16px;color:${fas.is_manual ? '#ffffff' : '#64748b'};box-shadow:0 2px 8px rgba(0,0,0,.18);">${fasilitasIcon(fas.kategori)}</div>`,
                iconSize: [32, 32],
                iconAnchor: [16, 16],
                popupAnchor: [0, -18]
            });
            const marker = L.marker([fas.lat, fas.lng], { icon: fasIcon });
            marker.bindPopup(() => buildFasilitasPopup(fas));
            fasilitasLayer.addLayer(marker);
        });
    }
    buildFasilitasLayer();

    // Render markers perumahan
    function renderMarkers() {
        layer.clearLayers();
        markers.clear();
        allData.forEach(item => {
            const riskStatus = item.single_risk ? item.single_risk.status : 'safe';
            const markerColor = riskStatus === 'danger' ? '#dc2626' : '#10b981';
            const icon = L.divIcon({
                className: '',
                html: `<div style="width:24px;height:24px;background:${markerColor};border:2px solid white;border-radius:50%;box-shadow:0 2px 8px rgba(0,0,0,.2);"></div>`,
                iconSize: [24, 24],
                iconAnchor: [12, 12],
                popupAnchor: [0, -15]
            });
            const marker = L.marker([Number(item.latitude), Number(item.longitude)], { icon });
            const riskExplanation = item.single_risk && item.single_risk.dasar_klasifikasi
                ? `<p style="font-size:11px;line-height:1.5;margin-top:10px;"><b>Berdasarkan:</b> ${item.single_risk.dasar_klasifikasi}</p>`
                : '';
            marker.bindPopup(`<div style="min-width:260px;"><p style="font-weight:900;font-size:13px;">${item.nama}</p><p style="font-size:10px;color:#64748b;margin:4px 0;">${item.kecamatan || '-'}</p><p style="font-size:11px;font-weight:600;margin-top:4px;"><b>Status Risiko:</b> ${item.single_risk ? item.single_risk.nama + ' (' + item.single_risk.tingkat + ')' : 'Aman'}</p>${riskExplanation}</div>`);
            marker.addTo(layer);
            markers.set(item.id, marker);
        });
        document.getElementById('visible-count').textContent = allData.length;
    }
    renderMarkers();

    // Layer toggle
    window.toggleLayer = function(which) {
        activeLayer = which;
        document.querySelectorAll('.layer-btn').forEach(b => {
            b.classList.remove('active-layer', 'bg-blue-600', 'text-white', 'border-blue-600', 'bg-emerald-600', 'border-emerald-600');
            b.classList.add('bg-white', 'text-slate-700', 'border-line');
        });
        const btn = document.getElementById(`layer-btn-${which}`);
        if (which === 'perumahan') {
            btn.classList.add('bg-blue-600', 'text-white', 'border-blue-600');
            btn.classList.remove('bg-white', 'text-slate-700', 'border-line');
            if (!map.hasLayer(layer)) map.addLayer(layer);
            if (map.hasLayer(fasilitasLayer)) map.removeLayer(fasilitasLayer);
        } else {
            btn.classList.add('bg-emerald-600', 'text-white', 'border-emerald-600');
            btn.classList.remove('bg-white', 'text-slate-700', 'border-line');
            if (map.hasLayer(layer)) map.removeLayer(layer);
            if (!map.hasLayer(fasilitasLayer)) map.addLayer(fasilitasLayer);
        }
    };

    // Inject styles
    if (!document.getElementById('admin-map-style')) {
        const st = document.createElement('style');
        st.id = 'admin-map-style';
        st.textContent = `.layer-btn { transition: all .2s; }`;
        document.head.appendChild(st);
    }

    setTimeout(() => map.invalidateSize(), 250);
})();
</script>
@endpush
