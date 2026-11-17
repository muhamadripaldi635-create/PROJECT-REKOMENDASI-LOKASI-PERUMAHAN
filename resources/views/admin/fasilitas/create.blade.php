@extends('layouts.admin')
@section('title', 'Tambah Fasilitas Umum')
@section('page_title', 'Tambah Fasilitas Umum')
@section('content')
<div class="mb-6 max-w-4xl">
    <form action="{{ route('admin.fasilitas.store') }}" method="POST" class="card rounded-[2rem] p-5 sm:p-8">
        @csrf

        <div class="mb-6">
            <label class="mb-2 block text-sm font-bold">Nama Fasilitas <span class="text-red-500">*</span></label>
            <input type="text" name="nama" value="{{ old('nama') }}" class="w-full rounded-2xl border-line px-4 py-3 focus:border-blue-600 focus:ring-blue-600 @error('nama') border-red-500 @enderror" placeholder="Contoh: RSU Keluarga" required>
            @error('nama')
                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-6">
            <label class="mb-2 block text-sm font-bold">Kategori <span class="text-red-500">*</span></label>
            <input type="text" name="kategori" value="{{ old('kategori') }}" class="w-full rounded-2xl border-line px-4 py-3 focus:border-blue-600 focus:ring-blue-600 @error('kategori') border-red-500 @enderror" placeholder="Contoh: Rumah Sakit, Sekolah, Bank, Pasar, Masjid" required>
            @error('kategori')
                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-6">
            <label class="mb-2 block text-sm font-bold">Lokasi Peta <span class="text-red-500">*</span></label>
            <p class="mb-3 text-sm text-slate-600">Klik pada peta untuk memilih lokasi fasilitas</p>
            <div id="map" style="height: 400px; border-radius: 1rem;" class="border-line"></div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm font-bold">Latitude <span class="text-red-500">*</span></label>
                <input type="text" inputmode="decimal" name="latitude" id="latitude" value="{{ old('latitude') }}" class="w-full rounded-2xl border-line px-4 py-3 focus:border-blue-600 focus:ring-blue-600 @error('latitude') border-red-500 @enderror" placeholder="-6.200000 atau -69223917" required>
                @error('latitude')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="mb-2 block text-sm font-bold">Longitude <span class="text-red-500">*</span></label>
                <input type="text" inputmode="decimal" name="longitude" id="longitude" value="{{ old('longitude') }}" class="w-full rounded-2xl border-line px-4 py-3 focus:border-blue-600 focus:ring-blue-600 @error('longitude') border-red-500 @enderror" placeholder="106.800000 atau 1075984768" required>
                @error('longitude')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mt-8 flex flex-col gap-3 sm:flex-row">
            <button type="submit" class="rounded-2xl btn-blue px-6 py-3 font-bold">Simpan Fasilitas</button>
            <a href="{{ route('admin.fasilitas.index') }}" class="rounded-2xl border-line border-2 px-6 py-3 text-center font-bold hover:bg-slate-50">Batal</a>
        </div>
    </form>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>

<script>
    const mapContainer = L.map('map').setView([-6.200, 106.815], 11);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(mapContainer);

    let marker = null;

    // Jika ada data lama, tampilkan marker
    const latitudeInput = document.getElementById('latitude');
    const longitudeInput = document.getElementById('longitude');
    const savedLat = latitudeInput.value;
    const savedLng = longitudeInput.value;
    if (savedLat && savedLng) {
        const normalizedLat = normalizeCoordinate(savedLat);
        const normalizedLng = normalizeCoordinate(savedLng);
        latitudeInput.value = normalizedLat.toFixed(7);
        longitudeInput.value = normalizedLng.toFixed(7);
        marker = L.marker([normalizedLat, normalizedLng]).addTo(mapContainer);
        mapContainer.setView([normalizedLat, normalizedLng], 13);
    }

    mapContainer.on('click', function(e) {
        const lat = e.latlng.lat.toFixed(7);
        const lng = e.latlng.lng.toFixed(7);

        if (marker) {
            mapContainer.removeLayer(marker);
        }

        marker = L.marker([lat, lng]).addTo(mapContainer);
        document.getElementById('latitude').value = lat;
        document.getElementById('longitude').value = lng;
    });

    // Update marker saat input berubah
    latitudeInput.addEventListener('change', updateMarker);
    longitudeInput.addEventListener('change', updateMarker);
    document.querySelector('form').addEventListener('submit', normalizeFormCoordinates);

    function updateMarker() {
        const lat = document.getElementById('latitude').value;
        const lng = document.getElementById('longitude').value;
        if (lat && lng) {
            const normalizedLat = normalizeCoordinate(lat);
            const normalizedLng = normalizeCoordinate(lng);
            latitudeInput.value = normalizedLat.toFixed(7);
            longitudeInput.value = normalizedLng.toFixed(7);
            if (marker) {
                mapContainer.removeLayer(marker);
            }
            marker = L.marker([normalizedLat, normalizedLng]).addTo(mapContainer);
            mapContainer.setView([normalizedLat, normalizedLng], 13);
        }
    }

    function normalizeCoordinate(value) {
        const coordinate = parseFloat(value);
        return Math.abs(coordinate) > 180 ? coordinate / 10000000 : coordinate;
    }

    function normalizeFormCoordinates() {
        latitudeInput.value = normalizeCoordinate(latitudeInput.value).toFixed(7);
        longitudeInput.value = normalizeCoordinate(longitudeInput.value).toFixed(7);
    }
</script>
@endsection
