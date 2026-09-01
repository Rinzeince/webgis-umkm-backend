-- ============================================================
-- schema.sql — Skripsi WebGIS K-Means UMKM Kab. Bandung Barat
-- Laravel 13.x + Filament 4.x + MariaDB
-- Versi Final v3 — diselaraskan dengan migration aktual
-- ============================================================
-- CATATAN PENTING:
-- 1. Jalankan SETELAH migration Laravel default (users, sessions, dll)
--    karena tabel `artikel` dan `activity_logs` FK ke `users(id)`.
-- 2. Setelah CREATE TABLE, wajib jalankan DatabaseSeeder untuk
--    mengisi 16 baris kecamatan + 16 baris dataset_agregat (default 0).
-- 3. File ini adalah referensi — implementasi sesungguhnya via
--    Laravel Migrations (php artisan make:migration).
-- 4. foto_url menyimpan URL foto UMKM, konsisten dengan thumbnail_url pada tabel artikel.
--    Konversi ke full URL dilakukan di API Resource/Controller via Storage::url().
--    Wajib jalankan: php artisan storage:link
-- ============================================================

-- ============================================================
-- KELOMPOK 0: AUTENTIKASI & SYSTEM LOGS
-- ============================================================

CREATE TABLE users (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  role ENUM('admin', 'editor') NOT NULL DEFAULT 'admin',
  email_verified_at TIMESTAMP NULL,
  password VARCHAR(255) NOT NULL,
  remember_token VARCHAR(100) NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL
);

CREATE TABLE activity_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NULL,
  user_name VARCHAR(255) NOT NULL,
  user_role VARCHAR(255) DEFAULT 'guest',
  action VARCHAR(255) NOT NULL,            -- e.g. LOGIN, LOGOUT, CREATE, UPDATE, DELETE, ANALISIS, PUBLISH
  subject_type VARCHAR(255) NULL,          -- e.g. UMKM, Artikel, User, Analisis
  description TEXT NOT NULL,
  ip_address VARCHAR(45) NULL,
  user_agent TEXT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- ============================================================
-- KELOMPOK 1: DATA MASTER & OPERASIONAL
-- ============================================================

CREATE TABLE kecamatan (
  id_kecamatan INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nama_kecamatan VARCHAR(50) NOT NULL,
  kode_kemendagri VARCHAR(20) NULL,         -- e.g. "32.17.01", menggantikan geojson_path
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE kategori_umkm (
  id_kategori INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nama_kategori VARCHAR(50) NOT NULL,
  warna_marker VARCHAR(7) NOT NULL,        -- HEX wajib diisi, misal #F97316
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE umkm (
  id_umkm BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_kecamatan INT UNSIGNED NOT NULL,
  id_kategori INT UNSIGNED NOT NULL,
  nama_umkm VARCHAR(150) NOT NULL,
  nama_pemilik VARCHAR(150) NULL,          -- admin-only, TIDAK di-expose ke API publik
  latitude DECIMAL(10,8) NULL,
  longitude DECIMAL(11,8) NULL,
  alamat_lengkap TEXT NULL,
  foto_url VARCHAR(255) NULL,
  -- menyimpan path relatif storage Laravel, misal: "umkm/foto/nama-file.jpg"
  -- simpan URL langsung (konsisten dengan thumbnail_url pada tabel artikel)
  kontak VARCHAR(50) NULL,
  google_maps_url VARCHAR(255) NULL,
  status_operasional ENUM('aktif','nonaktif') DEFAULT 'aktif',
  -- 'aktif'   = UMKM beroperasi, dihitung di agregat & tampil di WebGIS
  -- 'nonaktif'= UMKM tidak beroperasi, DIKECUALIKAN dari agregat & WebGIS publik
  jam_buka TIME NULL,                       -- Jam mulai operasional
  jam_tutup TIME NULL,                      -- Jam selesai operasional
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_status_nama (status_operasional, nama_umkm),
  FOREIGN KEY (id_kecamatan) REFERENCES kecamatan(id_kecamatan) ON DELETE RESTRICT,
  FOREIGN KEY (id_kategori) REFERENCES kategori_umkm(id_kategori) ON DELETE RESTRICT
);

-- ============================================================
-- KELOMPOK 2: DATA STATIS BPS (VERSIONING PER TAHUN)
-- ============================================================

CREATE TABLE demografi_kecamatan (
  id_demografi INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_kecamatan INT UNSIGNED NOT NULL,
  tahun SMALLINT NOT NULL,
  kepadatan_penduduk INT NULL,
  pertumbuhan_penduduk DECIMAL(5,2) NULL,  -- kolom: pertumbuhan_penduduk (bukan pertumbuhan)
  jarak_ke_ibukota DECIMAL(6,2) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_kec_tahun (id_kecamatan, tahun),
  FOREIGN KEY (id_kecamatan) REFERENCES kecamatan(id_kecamatan) ON DELETE RESTRICT
);
-- ATURAN: sistem selalu pakai baris MAX(tahun) per kecamatan saat membuat snapshot

-- ============================================================
-- KELOMPOK 3: AGREGASI OTOMATIS (MIKRO → MAKRO)
-- ============================================================

CREATE TABLE dataset_agregat (
  id_agregat INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_kecamatan INT UNSIGNED NOT NULL,
  -- TIDAK ada kolom tahun — ini state terkini, bukan historis per-tahun
  jml_makanan INT DEFAULT 0,
  jml_kerajinan INT DEFAULT 0,
  jml_fashion INT DEFAULT 0,
  jml_jasa INT DEFAULT 0,
  jml_lainnya INT DEFAULT 0,
  -- FILTER AGREGASI: hanya hitung umkm.status_operasional = 'aktif'
  -- umkm berstatus 'nonaktif' DIKECUALIKAN dari hitungan
  status_analisis ENUM('perlu_analisis','sudah_dianalisis') DEFAULT 'perlu_analisis',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_kecamatan (id_kecamatan),
  FOREIGN KEY (id_kecamatan) REFERENCES kecamatan(id_kecamatan) ON DELETE RESTRICT
);
-- WAJIB: seed 16 baris sebelum aplikasi digunakan
-- Observer/Trigger hanya UPDATE — tanpa seeding awal = silent failure

-- ============================================================
-- KELOMPOK 4: DATA ANALITIK & MACHINE LEARNING (VERSIONING)
-- ============================================================

CREATE TABLE analisis (
  id_analisis BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  k_optimal TINYINT NULL,
  nilai_silhouette DECIMAL(5,4) NULL,
  nilai_dbi DECIMAL(8,4) NULL,
  dataset_snapshot JSON NULL,
  -- {"tanggal_snapshot":"2026-07-24","data":[{"id_kecamatan":1,"tahun_demografi":2023,"fitur":{...}},...]}
  scaler_params JSON NULL,
  -- {"feature_names":[...],"mean_":[...],"scale_":[...],"catatan_unit":"f_prop_* dalam skala persen (0-100)"}
  path_grafik JSON NULL,
  -- ["elbow.png","silhouette.png","scatter_cluster.png"]
  model_params JSON NULL,
  -- {"init":"k-means++","n_init":50,"max_iter":500,"random_state":42,"inertia_final":65.23,"n_iter":2}
  status_job ENUM('dalam_antrean','diproses','selesai','gagal') DEFAULT 'dalam_antrean',
  is_published BOOLEAN DEFAULT FALSE,       -- flag penanda analisis yang dipublikasikan ke WebGIS publik
  error_log TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_analisis_published (is_published)
  -- POLA VERSIONING: setiap run = INSERT baru (BUKAN updateOrCreate/is_aktif)
);

CREATE TABLE hasil_cluster (
  id_hasil BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_analisis BIGINT UNSIGNED NOT NULL,
  id_kecamatan INT UNSIGNED NOT NULL,
  label_cluster TINYINT NOT NULL,
  interpretasi VARCHAR(60) NULL,
  sektor_top1 VARCHAR(20) NULL,
  sektor_top2 VARCHAR(20) NULL,
  sektor_bottom1 VARCHAR(20) NULL,
  sektor_bottom2 VARCHAR(20) NULL,         -- secara empiris selalu "Lainnya" di data KBB
  ranking_sektor_5 JSON NULL,
  -- [{"rank":1,"sektor":"Fashion","nilai":303.0},...]
  flag_imputasi ENUM('OK','PERLU_VALIDASI') DEFAULT 'OK',
  UNIQUE KEY uq_analisis_kecamatan (id_analisis, id_kecamatan),
  FOREIGN KEY (id_analisis) REFERENCES analisis(id_analisis) ON DELETE CASCADE,
  FOREIGN KEY (id_kecamatan) REFERENCES kecamatan(id_kecamatan) ON DELETE RESTRICT
);

CREATE TABLE centroid (
  id_centroid BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_analisis BIGINT UNSIGNED NOT NULL,
  label_cluster TINYINT NOT NULL,
  interpretasi VARCHAR(60) NULL,
  -- "Dominan Kerajinan & Fashion | Rendah Lainnya"
  sektor_dominan JSON NULL,
  -- ["Kerajinan","Fashion"]
  sektor_rendah JSON NULL,
  -- ["Lainnya"]
  ranking_sektor JSON NULL,
  -- [{"sektor":"Kerajinan","nilai":0.3,"share_pct":27.5,"vs_baseline":"ATAS"},...]
  nilai_fitur JSON NULL,
  -- f_prop_* SKALA PERSEN (0-100), BUKAN fraksi (0-1)
  -- {"f_prop_makanan":26.85,"f_prop_kerajinan":27.54,"f_prop_fashion":26.97,
  --  "f_prop_jasa":12.58,"f_prop_lainnya":6.05,
  --  "f_kepadatan":2769.67,"f_pertumbuhan":2.34,"f_jarak":8.73}
  UNIQUE KEY uq_analisis_cluster (id_analisis, label_cluster),
  FOREIGN KEY (id_analisis) REFERENCES analisis(id_analisis) ON DELETE CASCADE
  -- label_cluster di centroid & hasil_cluster: relasi LOGIS, bukan FK formal
  -- dijaga via DB::transaction saat Laravel menyimpan kedua tabel bersamaan
);

-- ============================================================
-- KELOMPOK 5: KONTEN INFORMASIONAL (BERDIRI SENDIRI)
-- ============================================================

CREATE TABLE artikel (
  id_artikel BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(150) NOT NULL,
  slug VARCHAR(170) NOT NULL,
  excerpt VARCHAR(255) NULL,
  content LONGTEXT NOT NULL,
  thumbnail_url VARCHAR(255) NULL,
  is_published BOOLEAN DEFAULT FALSE,
  id_author BIGINT UNSIGNED NULL,
  published_at TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_slug (slug),
  FOREIGN KEY (id_author) REFERENCES users(id) ON DELETE SET NULL
  -- API publik HANYA mengembalikan artikel dengan is_published = true
);
