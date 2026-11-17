<?php
// Direct database import script - bypasses Laravel for PHP 8.0 compatibility
// Usage: php import_data.php

// Database connection details
$host = '127.0.0.1';
$db = 'gis_perumahan_zenit';
$user = 'root';
$password = '';

try {
    $conn = new mysqli($host, $user, $password, $db);
    
    if ($conn->connect_error) {
        die('Connection failed: ' . $conn->connect_error);
    }
    
    // Read CSV file
    $csvFile = 'database/seeders/data/perumahan_lengkap.csv';
    if (!file_exists($csvFile)) {
        die("CSV file not found: $csvFile");
    }
    
    $file = fopen($csvFile, 'r');
    $header = fgetcsv($file, 0, ';');
    
    $count = 0;
    $errors = [];
    
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
        $slug = preg_replace('/[^a-z0-9]+/', '-', strtolower(trim($nama)));
        $slug = trim($slug, '-');
        if (empty($slug)) {
            $slug = 'perumahan';
        }
        
        // Check if slug exists
        $checkResult = $conn->query("SELECT id FROM perumahans WHERE slug = '$slug'");
        if ($checkResult->num_rows > 0) {
            // Slug exists, add counter
            $counter = 2;
            $newSlug = $slug;
            while ($conn->query("SELECT id FROM perumahans WHERE slug = '$newSlug'")->num_rows > 0) {
                $newSlug = $slug . '-' . $counter;
                $counter++;
            }
            $slug = $newSlug;
        }
        
        // Parse coordinates
        $latitude = !empty($data['LATITDUE']) ? (float)str_replace(',', '.', $data['LATITDUE']) : null;
        $longitude = !empty($data['LONGITUDE']) ? (float)str_replace(',', '.', $data['LONGITUDE']) : null;
        if ($longitude !== null && $longitude < 50.0) {
            $longitude = $longitude * 10;
        }
        
        // Parse price
        $harga = !empty($data['Harga']) ? (int)str_replace(',', '', $data['Harga']) : null;
        
        // Parse facilities
        $fasilitas = !empty($data['Fasilitas Umum']) ? "'" . $conn->real_escape_string(json_encode([trim($data['Fasilitas Umum'])])) . "'" : 'NULL';
        
        // Prepare values for insertion
        $nama_escaped = $conn->real_escape_string($nama);
        $alamat = $conn->real_escape_string($data['ALAMAT JALAN'] ?? '');
        $kelurahan = $conn->real_escape_string($data['DESA'] ?? '');
        $kecamatan = $conn->real_escape_string($data['NAMA KECAMATAN'] ?? '');
        $jenis_perumahan = $conn->real_escape_string($data['JENIS TIPE PERUMAHAN'] ?? '');
        
        // SQL Insert
        $sql = "INSERT INTO perumahans 
                (nama, slug, alamat, kelurahan, kecamatan, kabupaten, provinsi, latitude, longitude, 
                 harga_min, harga_max, jenis_perumahan, fasilitas, status, created_at, updated_at)
                VALUES 
                ('$nama_escaped', '$slug', '$alamat', '$kelurahan', '$kecamatan', 'Sukabumi', 'Jawa Barat', 
                 $latitude, $longitude, $harga, $harga, '$jenis_perumahan', $fasilitas, 'tersedia', NOW(), NOW())
                ON DUPLICATE KEY UPDATE updated_at = NOW()";
        
        if ($conn->query($sql) === true) {
            $count++;
        } else {
            $errors[] = "Error for $nama: " . $conn->error;
        }
    }
    
    fclose($file);
    $conn->close();
    
    echo "✓ Successfully imported $count perumahan records!\n";
    if (!empty($errors)) {
        echo "\n⚠ Warnings/Errors:\n";
        foreach ($errors as $error) {
            echo "  - $error\n";
        }
    }
    
} catch (Exception $e) {
    die("Fatal error: " . $e->getMessage());
}
