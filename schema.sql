-- DDL Schema Database WebGIS UMKM Kabupaten Bandung Barat (MariaDB)
-- Version: 2.0 (Updated 2026)

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `activity_logs`;
DROP TABLE IF EXISTS `artikel`;
DROP TABLE IF EXISTS `hasil_cluster`;
DROP TABLE IF EXISTS `centroid`;
DROP TABLE IF EXISTS `analisis`;
DROP TABLE IF EXISTS `dataset_agregat`;
DROP TABLE IF EXISTS `demografi_kecamatan`;
DROP TABLE IF EXISTS `umkm`;
DROP TABLE IF EXISTS `kategori_umkm`;
DROP TABLE IF EXISTS `kecamatan`;
DROP TABLE IF EXISTS `users`;

SET FOREIGN_KEY_CHECKS = 1;

-- 1. Tabel Users
CREATE TABLE `users` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `role` ENUM('admin', 'editor') NOT NULL DEFAULT 'editor',
  `email_verified_at` TIMESTAMP NULL DEFAULT NULL,
  `password` VARCHAR(255) NOT NULL,
  `remember_token` VARCHAR(100) NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tabel Kecamatan
CREATE TABLE `kecamatan` (
  `id_kecamatan` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `nama_kecamatan` VARCHAR(100) NOT NULL UNIQUE,
  `kode_kemendagri` VARCHAR(20) NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Tabel Kategori UMKM
CREATE TABLE `kategori_umkm` (
  `id_kategori` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `nama_kategori` VARCHAR(100) NOT NULL UNIQUE,
  `warna_marker` VARCHAR(7) NOT NULL DEFAULT '#00684A',
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Tabel UMKM
CREATE TABLE `umkm` (
  `id_umkm` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `id_kecamatan` BIGINT UNSIGNED NOT NULL,
  `id_kategori` BIGINT UNSIGNED NOT NULL,
  `nama_umkm` VARCHAR(150) NOT NULL,
  `pemilik` VARCHAR(100) NULL DEFAULT NULL,
  `alamat` TEXT NOT NULL,
  `latitude` DECIMAL(10, 8) NOT NULL,
  `longitude` DECIMAL(11, 8) NOT NULL,
  `no_telepon` VARCHAR(20) NULL DEFAULT NULL,
  `is_active` BOOLEAN NOT NULL DEFAULT TRUE,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  INDEX `idx_umkm_search` (`nama_umkm`, `alamat`(255)),
  CONSTRAINT `fk_umkm_kecamatan` FOREIGN KEY (`id_kecamatan`) REFERENCES `kecamatan` (`id_kecamatan`) ON DELETE CASCADE,
  CONSTRAINT `fk_umkm_kategori` FOREIGN KEY (`id_kategori`) REFERENCES `kategori_umkm` (`id_kategori`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Tabel Demografi Kecamatan
CREATE TABLE `demografi_kecamatan` (
  `id_demografi` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `id_kecamatan` BIGINT UNSIGNED NOT NULL,
  `tahun` SMALLINT UNSIGNED NOT NULL,
  `kepadatan_penduduk` DECIMAL(10, 2) NOT NULL,
  `pertumbuhan_penduduk` DECIMAL(5, 2) NOT NULL,
  `jarak_ke_ibukota` DECIMAL(6, 2) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  UNIQUE KEY `uk_demografi_kecamatan_tahun` (`id_kecamatan`, `tahun`),
  CONSTRAINT `fk_demografi_kecamatan` FOREIGN KEY (`id_kecamatan`) REFERENCES `kecamatan` (`id_kecamatan`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Tabel Dataset Agregat
CREATE TABLE `dataset_agregat` (
  `id_agregat` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `id_kecamatan` BIGINT UNSIGNED NOT NULL,
  `tahun` SMALLINT UNSIGNED NOT NULL,
  `jumlah_umkm` INT UNSIGNED NOT NULL DEFAULT 0,
  `kepadatan_penduduk` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  `pertumbuhan_penduduk` DECIMAL(5, 2) NOT NULL DEFAULT 0.00,
  `jarak_ke_ibukota` DECIMAL(6, 2) NOT NULL DEFAULT 0.00,
  `status_analisis` ENUM('perlu_analisis', 'sudah_dianalisis') NOT NULL DEFAULT 'perlu_analisis',
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  CONSTRAINT `fk_agregat_kecamatan` FOREIGN KEY (`id_kecamatan`) REFERENCES `kecamatan` (`id_kecamatan`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Tabel Analisis (Batch Versi Analisis ML)
CREATE TABLE `analisis` (
  `id_analisis` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `k_optimal` TINYINT UNSIGNED NOT NULL DEFAULT 3,
  `iterations` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `sse` DECIMAL(15, 4) NULL DEFAULT NULL,
  `silhouette_score` DECIMAL(6, 4) NULL DEFAULT NULL,
  `status_job` ENUM('dalam_antrean', 'diproses', 'selesai', 'gagal') NOT NULL DEFAULT 'selesai',
  `error_log` TEXT NULL DEFAULT NULL,
  `is_published` BOOLEAN NOT NULL DEFAULT FALSE,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Tabel Hasil Cluster (Hasil Pengelompokan Kecamatan per Batch Analisis)
CREATE TABLE `hasil_cluster` (
  `id_hasil` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `id_analisis` BIGINT UNSIGNED NOT NULL,
  `id_kecamatan` BIGINT UNSIGNED NOT NULL,
  `cluster_label` VARCHAR(20) NOT NULL,
  `distance_to_centroid` DECIMAL(10, 4) NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  CONSTRAINT `fk_hasil_analisis` FOREIGN KEY (`id_analisis`) REFERENCES `analisis` (`id_analisis`) ON DELETE CASCADE,
  CONSTRAINT `fk_hasil_kecamatan` FOREIGN KEY (`id_kecamatan`) REFERENCES `kecamatan` (`id_kecamatan`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Tabel Centroid (Titik Pusat Cluster per Batch Analisis)
CREATE TABLE `centroid` (
  `id_centroid` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `id_analisis` BIGINT UNSIGNED NOT NULL,
  `label_cluster` VARCHAR(20) NOT NULL,
  `mean_jumlah_umkm` DECIMAL(10, 2) NOT NULL,
  `mean_kepadatan` DECIMAL(10, 2) NOT NULL,
  `mean_pertumbuhan` DECIMAL(5, 2) NOT NULL,
  `mean_jarak` DECIMAL(6, 2) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  CONSTRAINT `fk_centroid_analisis` FOREIGN KEY (`id_analisis`) REFERENCES `analisis` (`id_analisis`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Tabel Artikel
CREATE TABLE `artikel` (
  `id_artikel` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `id_author` BIGINT UNSIGNED NULL DEFAULT NULL,
  `penulis` VARCHAR(100) NULL DEFAULT NULL,
  `title` VARCHAR(150) NOT NULL,
  `slug` VARCHAR(170) NOT NULL UNIQUE,
  `excerpt` VARCHAR(255) NULL DEFAULT NULL,
  `content` TEXT NOT NULL,
  `thumbnail_url` VARCHAR(255) NULL DEFAULT NULL,
  `is_published` BOOLEAN NOT NULL DEFAULT FALSE,
  `published_at` DATETIME NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  CONSTRAINT `fk_artikel_author` FOREIGN KEY (`id_author`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Tabel Activity Logs (Audit Trail)
CREATE TABLE `activity_logs` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `user_name` VARCHAR(100) NOT NULL,
  `user_role` VARCHAR(50) NOT NULL,
  `action` VARCHAR(50) NOT NULL,
  `subject_type` VARCHAR(50) NULL DEFAULT NULL,
  `description` TEXT NOT NULL,
  `ip_address` VARCHAR(45) NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  CONSTRAINT `fk_activity_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
