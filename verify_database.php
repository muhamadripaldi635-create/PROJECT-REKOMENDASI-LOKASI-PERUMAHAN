<?php
/**
 * Database Verification Script
 * Check if database is properly set up and data is accessible
 */

echo "═══════════════════════════════════════════════════════════════\n";
echo "   GiS Perumahan Database Verification\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// Database connection
$host = '127.0.0.1';
$db = 'gis_perumahan_zenit';
$user = 'root';
$password = '';

try {
    $conn = new mysqli($host, $user, $password, $db);
    
    if ($conn->connect_error) {
        echo "❌ Connection failed: " . $conn->connect_error . "\n";
        exit(1);
    }
    
    echo "✅ Database Connection: SUCCESS\n";
    echo "   Host: $host\n";
    echo "   Database: $db\n";
    echo "   User: $user\n\n";
    
    // Check tables
    echo "📋 Tables Status:\n";
    $tables = ['perumahans', 'perumahan_images', 'risiko_perumahans', 'risikos', 'users'];
    foreach ($tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result->num_rows > 0) {
            echo "   ✅ $table\n";
        } else {
            echo "   ❌ $table (missing)\n";
        }
    }
    
    echo "\n📊 Data Summary:\n";
    
    // Count perumahans
    $result = $conn->query("SELECT COUNT(*) as total FROM perumahans");
    $row = $result->fetch_assoc();
    echo "   • Total Perumahan: " . $row['total'] . " records\n";
    
    // Count per kecamatan
    $result = $conn->query("SELECT COUNT(DISTINCT kecamatan) as total FROM perumahans WHERE kecamatan IS NOT NULL AND kecamatan != ''");
    $row = $result->fetch_assoc();
    echo "   • Total Kecamatan: " . $row['total'] . "\n";
    
    // Count per jenis
    echo "\n   📍 Breakdown by Type:\n";
    $result = $conn->query("SELECT jenis_perumahan, COUNT(*) as count FROM perumahans GROUP BY jenis_perumahan ORDER BY count DESC");
    while ($row = $result->fetch_assoc()) {
        echo "      - " . ($row['jenis_perumahan'] ?: 'Unknown') . ": " . $row['count'] . "\n";
    }
    
    // Count per kecamatan
    echo "\n   🗺️  Breakdown by District (Kecamatan):\n";
    $result = $conn->query("SELECT kecamatan, COUNT(*) as count FROM perumahans GROUP BY kecamatan ORDER BY count DESC LIMIT 10");
    while ($row = $result->fetch_assoc()) {
        echo "      - " . $row['kecamatan'] . ": " . $row['count'] . "\n";
    }
    
    // Sample data
    echo "\n📌 Sample Records:\n";
    $result = $conn->query("SELECT id, nama, kecamatan, jenis_perumahan, harga_min FROM perumahans LIMIT 5");
    $i = 1;
    while ($row = $result->fetch_assoc()) {
        echo "   $i. {$row['nama']} ({$row['kecamatan']}) - " . ($row['jenis_perumahan'] ?: 'N/A') . "\n";
        echo "      Harga: Rp " . number_format($row['harga_min']) . "\n";
        $i++;
    }
    
    echo "\n✨ Database Setup Complete!\n";
    echo "   All data is ready to be used in your Laravel application.\n\n";
    
    // Check coordinates
    echo "🗺️  Coordinate Data Check:\n";
    $result = $conn->query("SELECT COUNT(*) as total FROM perumahans WHERE latitude IS NOT NULL AND longitude IS NOT NULL");
    $row = $result->fetch_assoc();
    echo "   • Records with coordinates: " . $row['total'] . "\n";
    
    $result = $conn->query("SELECT COUNT(*) as total FROM perumahans WHERE latitude IS NULL OR longitude IS NULL");
    $row = $result->fetch_assoc();
    echo "   • Records missing coordinates: " . $row['total'] . "\n";
    
    $conn->close();
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n═══════════════════════════════════════════════════════════════\n";
echo "For detailed documentation, see: DATABASE_SETUP_DOKUMENTASI.md\n";
echo "═══════════════════════════════════════════════════════════════\n";
