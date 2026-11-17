-- Create perumahans table if it doesn't exist
CREATE TABLE IF NOT EXISTS perumahans (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(255) NOT NULL,
  slug VARCHAR(255) NOT NULL UNIQUE,
  deskripsi LONGTEXT NULL,
  alamat VARCHAR(255) NULL,
  kelurahan VARCHAR(255) NULL,
  kecamatan VARCHAR(255) NULL,
  kabupaten VARCHAR(255) NULL,
  provinsi VARCHAR(255) NULL,
  latitude DECIMAL(10,7) NULL,
  longitude DECIMAL(10,7) NULL,
  harga_min BIGINT UNSIGNED NULL,
  harga_max BIGINT UNSIGNED NULL,
  luas DECIMAL(12,2) NULL,
  jumlah_unit INT UNSIGNED NULL,
  developer VARCHAR(255) NULL,
  status ENUM('tersedia','proses','terjual','tidak_aktif') DEFAULT 'tersedia',
  jenis_perumahan VARCHAR(255) NULL,
  fasilitas JSON NULL,
  telepon VARCHAR(20) NULL,
  email VARCHAR(255) NULL,
  website_url VARCHAR(255) NULL,
  google_maps_url TEXT NULL,
  is_recommended BOOLEAN DEFAULT 0,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  KEY idx_kecamatan (kecamatan),
  KEY idx_latitude (latitude),
  KEY idx_longitude (longitude),
  KEY idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create perumahan_images table if it doesn't exist
CREATE TABLE IF NOT EXISTS perumahan_images (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  perumahan_id BIGINT UNSIGNED NOT NULL,
  image_path VARCHAR(255) NOT NULL,
  sort_order INT UNSIGNED DEFAULT 0,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  FOREIGN KEY (perumahan_id) REFERENCES perumahans(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create risiko_perumahans table if it doesn't exist
CREATE TABLE IF NOT EXISTS risiko_perumahans (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  perumahan_id BIGINT UNSIGNED NOT NULL,
  risiko_id BIGINT UNSIGNED NOT NULL,
  nilai DECIMAL(10,2) NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  FOREIGN KEY (perumahan_id) REFERENCES perumahans(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create risikos table if it doesn't exist
CREATE TABLE IF NOT EXISTS risikos (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(255) NOT NULL,
  deskripsi LONGTEXT NULL,
  tipe VARCHAR(100) NOT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create users table if it doesn't exist
CREATE TABLE IF NOT EXISTS users (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  email_verified_at TIMESTAMP NULL,
  password VARCHAR(255) NOT NULL,
  remember_token VARCHAR(100) NULL,
  role VARCHAR(50) DEFAULT 'user',
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
