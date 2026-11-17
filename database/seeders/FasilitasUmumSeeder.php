<?php

namespace Database\Seeders;

use App\Models\FasilitasUmum;
use Illuminate\Database\Seeder;

class FasilitasUmumSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/data/fasilitas_umum.csv');
        if (!file_exists($path)) {
            $this->command->error('File not found: ' . $path);
            return;
        }

        $file = fopen($path, 'r');
        $header = fgetcsv($file, 0, ';');
        $count = 0;

        while (($row = fgetcsv($file, 0, ';')) !== false) {
            if (count($row) !== count($header) || empty(trim($row[0] ?? ''))) {
                continue;
            }

            $data = array_combine($header, $row);
            $latitude = $this->coordinate($data['Latitude'] ?? null);
            $longitude = $this->coordinate($data['Longitude'] ?? null);

            if ($latitude === null || $longitude === null || $latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
                continue;
            }

            FasilitasUmum::updateOrCreate(
                ['source_no' => (int) $data['No'],],
                [
                    'nama' => trim($data['Nama Fasilitas']),
                    'kategori' => trim($data['Kategori']),
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'is_manual' => false,
                ]
            );
            $count++;
        }

        fclose($file);
        $this->command->info("Imported {$count} fasilitas umum.");
    }

    private function coordinate(?string $value): ?float
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $number = (float) str_replace(',', '.', trim($value));
        return abs($number) > 180 ? $number / 10000000 : $number;
    }
}