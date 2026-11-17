<?php

namespace App\Http\Controllers;

use App\Models\Perumahan;
use App\Models\Risiko;
use App\Models\FasilitasUmum;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        $totalPerumahan = Perumahan::count();
        $totalKecamatan = Perumahan::whereNotNull('kecamatan')->distinct('kecamatan')->count('kecamatan');
        $totalRisiko = Risiko::count();
        $risikoTinggi = Risiko::whereIn('tingkat', ['tinggi', 'kritis'])->count();
        $risikoPerTingkat = Risiko::query()
            ->selectRaw('tingkat, COUNT(*) as total')
            ->groupBy('tingkat')
            ->pluck('total', 'tingkat');
        $grafikRisiko = collect(['rendah', 'sedang', 'tinggi'])
            ->mapWithKeys(fn ($tingkat) => [$tingkat => (int) ($risikoPerTingkat[$tingkat] ?? 0)]);
        $totalFasilitasUmum = FasilitasUmum::count();
        $grafikFasilitas = FasilitasUmum::query()
            ->selectRaw('kategori, COUNT(*) as total')
            ->groupBy('kategori')
            ->orderByDesc('total')
            ->pluck('total', 'kategori');
        $perumahanTerbaru = Perumahan::withCount('risikos')->latest()->take(6)->get();
        $risikoTerbaru = Risiko::with('perumahan')->latest()->take(6)->get();

        // Prepare map data
        $perumahans = Perumahan::with(['images', 'risikos'])->get();
        $fasilitasUmum = FasilitasUmum::all();

        $mapData = $perumahans->map(function ($item) {
            $cover = $item->images->first();
            
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
                $singleRiskStatus = 'danger';
                $singleRiskDesc = $highestRisk->deskripsi;
            } else {
                $singleRiskName = 'Aman';
                $singleRiskLevel = 'rendah';
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
                    'dasar_klasifikasi' => $highestRisk?->dasar_klasifikasi ?: ($highestRisk ? $riskAnalysis[$highestRisk->tipe]['dasar_klasifikasi'] : 'Aman berdasarkan peninjauan data GIS terhadap indikator banjir dan longsor pada lokasi ini. Tidak ditemukan risiko banjir atau longsor sedang maupun lebih tinggi pada data yang tersedia.'),
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

        return view('admin.dashboard', compact(
            'totalPerumahan', 'totalKecamatan', 'totalRisiko', 'risikoTinggi', 'grafikRisiko', 'totalFasilitasUmum', 'grafikFasilitas', 'perumahanTerbaru', 'risikoTerbaru',
            'mapData', 'fasilitasMapData'
        ));
    }
}
