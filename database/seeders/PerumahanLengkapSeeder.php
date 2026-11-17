<?php

namespace Database\Seeders;

use App\Models\Perumahan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PerumahanLengkapSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/data/perumahan_lengkap.csv');
        if (!file_exists($path)) {
            $this->command->error('File not found: ' . $path);
            return;
        }

        $file = fopen($path, 'r');
        $header = fgetcsv($file, 0, ';');

        $count = 0;
        while (($row = fgetcsv($file, 0, ';')) !== false) {
            if (empty($row[0])) {
                continue;
            }

            $data = array_combine($header, $row);

            $nama = trim($data['NAMA PERUMAHAN'] ?? '');
            if (empty($nama)) {
                continue;
            }

            // Create slug
            $slugBase = Str::slug($nama) ?: 'perumahan';
            $slug = $slugBase;
            $counter = 2;
            while (Perumahan::where('slug', $slug)->exists()) {
                $slug = $slugBase . '-' . $counter++;
            }

            // Parse latitude and longitude
            $latitude = !empty($data['LATITDUE']) ? (float)str_replace(',', '.', $data['LATITDUE']) : null;
            $longitude = !empty($data['LONGITUDE']) ? (float)str_replace(',', '.', $data['LONGITUDE']) : null;
            if ($longitude !== null && $longitude < 50.0) {
                $longitude = $longitude * 10;
            }

            // Parse harga (price)
            $harga = !empty($data['Harga']) ? (int)str_replace(',', '', $data['Harga']) : null;

            // Parse fasilitas (facilities)
            $fasilitas = [];
            if (!empty($data['Fasilitas Umum'])) {
                $fasilitas[] = trim($data['Fasilitas Umum']);
            }

            try {
                $p = Perumahan::firstOrCreate(
                    ['slug' => $slug],
                    [
                        'nama' => $nama,
                        'alamat' => trim($data['ALAMAT JALAN'] ?? ''),
                        'kelurahan' => trim($data['DESA'] ?? ''),
                        'kecamatan' => trim($data['NAMA KECAMATAN'] ?? ''),
                        'kabupaten' => 'Sukabumi',
                        'provinsi' => 'Jawa Barat',
                        'latitude' => $latitude,
                        'longitude' => $longitude,
                        'harga_min' => $harga,
                        'harga_max' => $harga,
                        'jenis_perumahan' => trim($data['JENIS TIPE PERUMAHAN'] ?? ''),
                        'fasilitas' => !empty($fasilitas) ? $fasilitas : null,
                        'status' => 'tersedia',
                    ]
                );

                // Calculate and seed risks based on DEM, Slope and Rainfall
                $dem = isset($data['Data_DEM_mdpl']) && $data['Data_DEM_mdpl'] !== '' ? (int)$data['Data_DEM_mdpl'] : 300;
                $slope = isset($data['Kemiringan_Lereng_derajat']) && $data['Kemiringan_Lereng_derajat'] !== '' ? (float)str_replace(',', '.', $data['Kemiringan_Lereng_derajat']) : 5.0;
                $rain = isset($data['Curah_Hujan_mm_thn']) && $data['Curah_Hujan_mm_thn'] !== '' ? (int)$data['Curah_Hujan_mm_thn'] : 2500;

                // 1. Calculate Landslide (Longsor) Risk
                $longsorTingkat = 'rendah';
                $longsorDesc = "Risiko longsor rendah karena kemiringan lereng landai ({$slope}°).";
                $longsorMitigasi = "Pemantauan rutin kondisi lereng dan pemeliharaan vegetasi.";

                if ($slope > 15.0) {
                    if ($rain > 3000) {
                        $longsorTingkat = 'kritis';
                        $longsorDesc = "Risiko longsor kritis akibat lereng sangat curam ({$slope}°) dan curah hujan tinggi ({$rain} mm/tahun).";
                        $longsorMitigasi = "Pembuatan dinding penahan tanah (turap) beton, sistem drainase lereng, dan evakuasi saat hujan lebat terus-menerus.";
                    } else if ($rain > 2000) {
                        $longsorTingkat = 'tinggi';
                        $longsorDesc = "Risiko longsor tinggi akibat lereng curam ({$slope}°) dengan curah hujan sedang ({$rain} mm/tahun).";
                        $longsorMitigasi = "Pemasangan jaring kawat penahan tebing (wire mesh), penanaman pohon berakar dalam (seperti vetiver), dan larangan mendirikan bangunan di bibir lereng.";
                    } else {
                        $longsorTingkat = 'sedang';
                        $longsorDesc = "Risiko longsor sedang karena lereng curam ({$slope}°) namun curah hujan relatif rendah ({$rain} mm/tahun).";
                        $longsorMitigasi = "Terasering lereng, perbaikan saluran air permukaan, dan pemantauan retakan tanah.";
                    }
                } else if ($slope >= 8.0) {
                    if ($rain > 3000) {
                        $longsorTingkat = 'tinggi';
                        $longsorDesc = "Risiko longsor tinggi akibat kemiringan lereng sedang ({$slope}°) berpadu dengan curah hujan sangat tinggi ({$rain} mm/tahun).";
                        $longsorMitigasi = "Penguatan tebing, perbaikan saluran drainase agar air tidak meresap liar ke lereng, dan penghijauan.";
                    } else if ($rain > 2000) {
                        $longsorTingkat = 'sedang';
                        $longsorDesc = "Risiko longsor sedang karena kemiringan lereng sedang ({$slope}°) dan curah hujan sedang ({$rain} mm/tahun).";
                        $longsorMitigasi = "Terasering, penanaman rumput penahan erosi, dan pemeliharaan saluran air.";
                    } else {
                        $longsorTingkat = 'rendah';
                        $longsorDesc = "Risiko longsor rendah karena lereng sedang ({$slope}°) dan curah hujan rendah ({$rain} mm/tahun).";
                        $longsorMitigasi = "Pemeliharaan vegetasi penutup tanah.";
                    }
                } else { // $slope < 8.0
                    if ($rain > 3000) {
                        $longsorTingkat = 'sedang';
                        $longsorDesc = "Risiko longsor sedang akibat curah hujan sangat tinggi ({$rain} mm/tahun) meskipun lereng relatif landai ({$slope}°).";
                        $longsorMitigasi = "Pembersihan saluran drainase secara berkala untuk menghindari genangan air yang melunakkan tanah.";
                    }
                }

                // 2. Calculate Flood (Banjir) Risk
                $banjirTingkat = 'rendah';
                $banjirDesc = "Risiko banjir rendah karena elevasi dataran tinggi ({$dem} mdpl).";
                $banjirMitigasi = "Pemeliharaan saluran pembuangan air hujan.";

                if ($dem < 150) {
                    if ($slope < 5.0) {
                        if ($rain > 3000) {
                            $banjirTingkat = 'kritis';
                            $banjirDesc = "Risiko banjir kritis karena berada di dataran rendah ({$dem} mdpl) yang sangat datar ({$slope}°) dengan curah hujan sangat tinggi ({$rain} mm/tahun).";
                            $banjirMitigasi = "Pembuatan kolam retensi, peninggian fondasi rumah, pompa banjir berkala, dan normalisasi sungai terdekat.";
                        } else if ($rain > 2000) {
                            $banjirTingkat = 'tinggi';
                            $banjirDesc = "Risiko banjir tinggi karena berada di dataran rendah ({$dem} mdpl) yang datar ({$slope}°) dengan curah hujan sedang ({$rain} mm/tahun).";
                            $banjirMitigasi = "Pembuatan tanggul penahan genangan, pelebaran parit utama, serta edukasi sistem peringatan dini banjir.";
                        } else {
                            $banjirTingkat = 'sedang';
                            $banjirDesc = "Risiko banjir sedang karena kawasan rendah ({$dem} mdpl) meskipun curah hujan rendah.";
                            $banjirMitigasi = "Penyediaan sumur resapan air dan pemeliharaan saluran drainase agar tidak tersumbat sampah.";
                        }
                    } else {
                        if ($rain > 2500) {
                            $banjirTingkat = 'tinggi';
                            $banjirDesc = "Risiko banjir tinggi di dataran rendah ({$dem} mdpl) akibat curah hujan tinggi ({$rain} mm/tahun).";
                            $banjirMitigasi = "Pembersihan berkala saluran air dan penataan ruang terbuka hijau.";
                        } else {
                            $banjirTingkat = 'sedang';
                            $banjirDesc = "Risiko banjir sedang karena berada di ketinggian rendah ({$dem} mdpl).";
                            $banjirMitigasi = "Pemeliharaan saluran drainase.";
                        }
                    }
                } else if ($dem <= 500) {
                    if ($slope < 5.0 && $rain > 2500) {
                        $banjirTingkat = 'sedang';
                        $banjirDesc = "Risiko banjir sedang karena kontur yang sangat datar ({$slope}°) di ketinggian sedang ({$dem} mdpl) dengan curah hujan tinggi ({$rain} mm/tahun).";
                        $banjirMitigasi = "Pembuatan lubang biopori dan drainase lingkungan yang memadai.";
                    }
                }

                // Delete existing risks for this perumahan to avoid duplicates on re-seed
                $p->risikos()->delete();
                \App\Models\RisikoPerumahan::where('perumahan_id', $p->id)->delete();

                // Save to `risikos`
                $p->risikos()->create([
                    'nama_risiko' => 'Longsor',
                    'tipe' => 'longsor',
                    'tingkat' => $longsorTingkat,
                    'deskripsi' => $longsorDesc,
                    'mitigasi' => $longsorMitigasi,
                    'status_tindak_lanjut' => 'Terpantau',
                ]);

                $p->risikos()->create([
                    'nama_risiko' => 'Banjir',
                    'tipe' => 'banjir',
                    'tingkat' => $banjirTingkat,
                    'deskripsi' => $banjirDesc,
                    'mitigasi' => $banjirMitigasi,
                    'status_tindak_lanjut' => 'Terpantau',
                ]);

                if ($longsorTingkat === 'rendah' && $banjirTingkat === 'rendah') {
                    $p->risikos()->create([
                        'nama_risiko' => 'Aman',
                        'tipe' => 'aman',
                        'tingkat' => 'rendah',
                        'deskripsi' => "Kawasan perumahan relatif aman dari bencana alam karena elevasi memadai ({$dem} mdpl) dan lereng landai ({$slope}°).",
                        'mitigasi' => "Tetap jaga kebersihan lingkungan dan kelestarian saluran air.",
                        'status_tindak_lanjut' => 'Terpantau',
                    ]);
                }

                // Save to `risiko_perumahans`
                \App\Models\RisikoPerumahan::create([
                    'perumahan_id' => $p->id,
                    'tipe' => 'Longsor',
                    'tingkat' => $longsorTingkat,
                    'deskripsi' => $longsorDesc,
                    'mitigasi' => $longsorMitigasi,
                    'status_tindak_lanjut' => 'dipantau',
                ]);

                \App\Models\RisikoPerumahan::create([
                    'perumahan_id' => $p->id,
                    'tipe' => 'Banjir',
                    'tingkat' => $banjirTingkat,
                    'deskripsi' => $banjirDesc,
                    'mitigasi' => $banjirMitigasi,
                    'status_tindak_lanjut' => 'dipantau',
                ]);

                $count++;
            } catch (\Exception $e) {
                $this->command->warn("Error processing {$nama}: " . $e->getMessage());
            }
        }

        fclose($file);
        $this->command->info("Successfully imported {$count} perumahan records!");
    }
}
