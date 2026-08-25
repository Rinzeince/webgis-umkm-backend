#!/usr/bin/env python3
# -*- coding: utf-8 -*-
# =============================================================================
# SEMMA_KMeans_UMKM_KBB.py — v3.0 (Production)
# =============================================================================
# SISTEM PENDUKUNG KEPUTUSAN BERBASIS SPASIAL (WebGIS)
# Pemetaan & Klasterisasi UMKM — Kabupaten Bandung Barat
# Metodologi   : SEMMA (Sample, Explore, Modify, Model, Assess)
# Pendekatan   : Unsupervised Learning — K-Means Clustering
# Kerangka     : Design Science Research Methodology (DSRM)
#
# Dikonversi dari notebook Google Colab ke script produksi yang dipanggil oleh
# Laravel Job (RunKMeansAnalysisJob) melalui:
#
#     $pythonPath = base_path('python/venv/Scripts/python.exe'); // Windows/Laragon
#     $scriptPath = base_path('python/SEMMA_KMeans_UMKM_KBB.py');
#     $process = Process::run("$pythonPath $scriptPath");
#
# INPUT (default, relatif terhadap lokasi file ini → backend/storage/...):
#     storage/app/ml/input/dataset_snapshot.json   (format §4.5 spesifikasi)
#
# OUTPUT (default, ke backend/storage/app/ml/output/):
#     metadata_output.json        → INSERT tabel `analisis`
#     hasil_cluster_output.json   → INSERT tabel `hasil_cluster`
#     centroid_output.json        → INSERT tabel `centroid`
#     elbow.png                   → path_grafik[0]
#     silhouette.png              → path_grafik[1]
#     scatter_cluster.png         → path_grafik[2]
#     lampiran/*                  → lampiran skripsi (EDA/model/CSV, di luar kontrak DB)
#
# EXIT CODE:
#     0 = sukses, semua file output berhasil ditulis
#     1 = gagal — pesan error di stderr (Laravel Job set status_job='gagal',
#         error_log = output stderr)
#
# CATATAN PERBAIKAN DARI VERSI NOTEBOOK (lihat ringkasan lengkap di chat):
#   1. FIX kolom kritis: mapping DB 'pertumbuhan' → 'pertumbuhan_penduduk'
#      (nama kolom asli di tabel demografi_kecamatan, lihat CLAUDE.md & schema.sql)
#   2. Backend matplotlib diset 'Agg' (headless, wajib untuk server/production)
#   3. Fix encoding stdout/stderr UTF-8 di Windows (Process::run() capture)
#   4. Resolusi path берbasis lokasi file (bukan CWD) agar aman dipanggil dari
#      direktori kerja manapun
#   5. Relabeling kolom pada tahap interpretasi centroid (proporsi vs UMKM
#      absolut sempat tertukar nama, nilai akhir TIDAK berubah/tetap sesuai
#      contoh schema.sql, hanya label & log konsol yang diperbaiki)
#   6. df_hasil kini dibangun dari df_processed (data hasil imputasi), bukan
#      df_raw, agar CSV lampiran konsisten dengan data yang benar-benar dipakai
#      model (tidak memengaruhi JSON DB-ready)
#   7. Validasi input + error handling end-to-end (try/except + exit code)
#   8. K_RANGE otomatis menyesuaikan jika jumlah kecamatan < 9 (mencegah crash
#      silhouette_score saat data uji kecil)
#   9. Mode --demo untuk testing lokal tanpa perlu file input dari Laravel
# =============================================================================

import sys
import json
import argparse
import traceback
import warnings
from pathlib import Path
from datetime import date

# --- Auto-resolve site-packages for Windows Web Server environments (Apache/Laragon)
if sys.platform == 'win32':
    for _stream in (sys.stdout, sys.stderr):
        try:
            _stream.reconfigure(encoding='utf-8')
        except Exception:
            pass
    import site
    import os
    user_site = site.getusersitepackages()
    if user_site and os.path.exists(user_site) and user_site not in sys.path:
        sys.path.insert(0, user_site)
    appdata = os.environ.get('APPDATA', r'C:\Users\v14\AppData\Roaming')
    for py_ver in ['Python310', 'Python311', 'Python312', 'Python39']:
        roaming_site = os.path.join(appdata, 'Python', py_ver, 'site-packages')
        if os.path.exists(roaming_site) and roaming_site not in sys.path:
            sys.path.insert(0, roaming_site)

    # --- Fix matplotlib home directory resolution for web server environments
    if 'MPLCONFIGDIR' not in os.environ:
        temp_dir = os.environ.get('TMP', os.environ.get('TEMP', r'C:\Windows\Temp'))
        os.environ['MPLCONFIGDIR'] = os.path.join(temp_dir, 'matplotlib_config')
    if 'USERPROFILE' not in os.environ and 'HOME' not in os.environ:
        os.environ['USERPROFILE'] = os.path.dirname(appdata) if appdata else r'C:\Users\v14'

import numpy as np
import pandas as pd

import matplotlib
matplotlib.use('Agg')  # WAJIB: tidak ada display di server/production (headless)
import matplotlib.pyplot as plt
import matplotlib.ticker as ticker
import seaborn as sns

from sklearn.preprocessing import StandardScaler
from sklearn.cluster import KMeans
from sklearn.metrics import silhouette_score, davies_bouldin_score
from sklearn.decomposition import PCA

warnings.filterwarnings('ignore')

plt.rcParams['figure.dpi'] = 120
plt.rcParams['font.family'] = 'DejaVu Sans'
plt.rcParams['axes.spines.top'] = False
plt.rcParams['axes.spines.right'] = False
PALETTE = ['#2563EB', '#16A34A', '#DC2626', '#D97706', '#7C3AED']


def log(msg=""):
    """Print dengan flush=True agar log langsung tampil saat dijalankan sbg subprocess."""
    print(msg, flush=True)


# =============================================================================
# KONFIGURASI PATH DEFAULT
# Dihitung relatif terhadap lokasi file script ini (BUKAN CWD proses), supaya
# tetap benar berapa pun working-directory yang dipakai saat Process::run()
# memanggil "$pythonPath $scriptPath" dari Laravel.
#   Lokasi script : backend/python/SEMMA_KMeans_UMKM_KBB.py
#   Input default : backend/storage/app/ml/input/dataset_snapshot.json
#   Output default: backend/storage/app/ml/output/
# =============================================================================
SCRIPT_DIR = Path(__file__).resolve().parent
BACKEND_DIR = SCRIPT_DIR.parent
DEFAULT_INPUT = BACKEND_DIR / 'storage' / 'app' / 'ml' / 'input' / 'dataset_snapshot.json'
DEFAULT_OUTPUT_DIR = BACKEND_DIR / 'storage' / 'app' / 'ml' / 'output'


# =============================================================================
# TAHAP 1 — SAMPLE
# Pemetaan nama kolom antar layer sistem (single source of truth) +
# definisi kolom identifier/fitur + pemuatan dataset.
# =============================================================================

# Peta 1: DB column name (dataset_agregat / demografi_kecamatan) → nama internal script
# FIX PENTING: kolom DB yang benar adalah `pertumbuhan_penduduk`, BUKAN `pertumbuhan`
# (lihat CLAUDE.md §Database, "Nama kolom kritis — jangan typo").
KOLOM_MAP_DB_KE_SCRIPT = {
    'jml_makanan'          : 'UMKM_Makanan',
    'jml_kerajinan'        : 'UMKM_Kerajinan',
    'jml_fashion'          : 'UMKM_Fashion',
    'jml_jasa'             : 'UMKM_Jasa',
    'jml_lainnya'          : 'UMKM_Lainnya',
    'kepadatan_penduduk'   : 'Kepadatan_Penduduk',
    'pertumbuhan_penduduk' : 'Pertumbuhan_Penduduk',   # FIXED (dulu: 'pertumbuhan')
    'jarak_ke_ibukota'     : 'Jarak_ke_Ibukota',
}
# Peta 2: nama internal script → field JSON di centroid.nilai_fitur (kolom demografis saja;
# 5 kolom proporsi sektor ditangani terpisah via awalan 'f_prop_' di TAHAP 5.6)
KOLOM_MAP_SCRIPT_KE_JSON = {
    'Kepadatan_Penduduk'  : 'f_kepadatan',
    'Pertumbuhan_Penduduk': 'f_pertumbuhan',
    'Jarak_ke_Ibukota'    : 'f_jarak',
}
# Peta 3: nama internal script → nama kolom DB (dipakai saat membangun dataset_snapshot
# di dalam metadata_output.json, agar Laravel menerima kembali nama kolom yang ia kenal)
KOLOM_MAP_SCRIPT_KE_DB = {v: k for k, v in KOLOM_MAP_DB_KE_SCRIPT.items()}

COLS_IDENTIFIER = ['id_kecamatan', 'nama_kecamatan']
COLS_FITUR = [
    'UMKM_Makanan', 'UMKM_Kerajinan', 'UMKM_Fashion',
    'UMKM_Jasa', 'UMKM_Lainnya',
    'Kepadatan_Penduduk', 'Pertumbuhan_Penduduk', 'Jarak_ke_Ibukota',
]
COLS_UMKM_5 = ['UMKM_Makanan', 'UMKM_Kerajinan', 'UMKM_Fashion', 'UMKM_Jasa', 'UMKM_Lainnya']
COLS_UMKM_4_MAIN = ['UMKM_Makanan', 'UMKM_Kerajinan', 'UMKM_Fashion', 'UMKM_Jasa']
COLS_PROP = ['PROP_Makanan', 'PROP_Kerajinan', 'PROP_Fashion', 'PROP_Jasa', 'PROP_Lainnya']
COLS_DEMOGRAFI = ['Kepadatan_Penduduk', 'Pertumbuhan_Penduduk', 'Jarak_ke_Ibukota']
# Fitur gabungan untuk K-Means: 5 proporsi sektor + 3 demografis = 8 fitur
COLS_FITUR_CLUSTER = COLS_PROP + COLS_DEMOGRAFI

NAMA_SEKTOR = {
    'UMKM_Makanan'  : 'Makanan',
    'UMKM_Kerajinan': 'Kerajinan',
    'UMKM_Fashion'  : 'Fashion',
    'UMKM_Jasa'     : 'Jasa',
    'UMKM_Lainnya'  : 'Lainnya',
}
# Nama sektor per kolom PROPORSI (dipakai di TAHAP interpretasi centroid,
# menggantikan pemakaian COLS_FITUR yang salah label di versi notebook)
NAMA_SEKTOR_PROP = {
    'PROP_Makanan'  : 'Makanan',
    'PROP_Kerajinan': 'Kerajinan',
    'PROP_Fashion'  : 'Fashion',
    'PROP_Jasa'     : 'Jasa',
    'PROP_Lainnya'  : 'Lainnya',
}


class NumpyJSONEncoder(json.JSONEncoder):
    """Safety net: konversi tipe numpy -> tipe native Python saat json.dump,
    berjaga-jaga jika ada nilai yang lolos dari casting eksplisit float()/int()."""
    def default(self, obj):
        if isinstance(obj, np.integer):
            return int(obj)
        if isinstance(obj, np.floating):
            return float(obj)
        if isinstance(obj, np.ndarray):
            return obj.tolist()
        if isinstance(obj, np.bool_):
            return bool(obj)
        return super().default(obj)


def write_json(obj, path: Path):
    with open(path, 'w', encoding='utf-8') as f:
        json.dump(obj, f, ensure_ascii=False, indent=2, cls=NumpyJSONEncoder)


def load_dataset_snapshot(input_path: Path):
    """Opsi C (satu-satunya jalur produksi): baca dataset_snapshot.json
    yang ditulis Laravel Job sebelum invoke script ini. Format sesuai §4.5:
        {
          "tanggal_snapshot": "2026-07-24",
          "data": [
            {"id_kecamatan": 1, "nama_kecamatan": "Batujajar",
             "tahun_demografi": 2023, "fitur": {"jml_makanan": 120, ...}},
            ...
          ]
        }
    """
    log(f"📥 [SAMPLE] Membaca dataset_snapshot dari: {input_path}")
    if not input_path.exists():
        raise FileNotFoundError(f"File input tidak ditemukan: {input_path}")

    with open(input_path, 'r', encoding='utf-8') as f:
        try:
            snapshot_raw = json.load(f)
        except json.JSONDecodeError as e:
            raise ValueError(f"dataset_snapshot.json tidak valid (bukan JSON yang benar): {e}")

    if not isinstance(snapshot_raw, dict) or 'data' not in snapshot_raw \
            or not isinstance(snapshot_raw['data'], list):
        raise ValueError("Format dataset_snapshot.json tidak valid: key 'data' (list) tidak ditemukan.")
    if len(snapshot_raw['data']) == 0:
        raise ValueError("dataset_snapshot.json kosong: tidak ada data kecamatan pada key 'data'.")

    fitur_wajib = list(KOLOM_MAP_DB_KE_SCRIPT.keys())
    records = []
    tahun_demografi = {}
    for i, item in enumerate(snapshot_raw['data']):
        if 'id_kecamatan' not in item:
            raise ValueError(f"Baris ke-{i} pada dataset_snapshot tidak memiliki 'id_kecamatan'.")
        kec_id = int(item['id_kecamatan'])
        row = {
            'id_kecamatan'  : kec_id,
            'nama_kecamatan': item.get('nama_kecamatan', f"Kecamatan-{kec_id}"),
        }
        fitur_item = item.get('fitur', {}) or {}
        for db_col in fitur_wajib:
            script_col = KOLOM_MAP_DB_KE_SCRIPT[db_col]
            # Kolom hilang -> NaN -> ditangani Median Imputation di MODIFY 3.1
            row[script_col] = fitur_item.get(db_col, np.nan)
        records.append(row)
        tahun_demografi[kec_id] = item.get('tahun_demografi')

    df_raw = pd.DataFrame(records)

    n = len(df_raw)
    if n != 16:
        log(f"   ⚠️  PERINGATAN: jumlah kecamatan pada snapshot = {n} (spesifikasi mengharapkan 16).")
    if n < 3:
        raise ValueError(f"Jumlah data kecamatan terlalu sedikit untuk clustering (n={n}, minimal 3).")
    if df_raw['id_kecamatan'].duplicated().any():
        dup = df_raw.loc[df_raw['id_kecamatan'].duplicated(), 'id_kecamatan'].tolist()
        raise ValueError(f"id_kecamatan duplikat pada dataset_snapshot: {dup}")

    tanggal_snapshot = snapshot_raw.get('tanggal_snapshot', str(date.today()))
    log(f"   ✅ Dataset berhasil dimuat. Shape: {df_raw.shape}")
    log(f"   Tipe id_kecamatan: {df_raw['id_kecamatan'].dtype} (harus int)")
    return df_raw, tanggal_snapshot, tahun_demografi


def load_demo_dataset():
    """Data sintetis representatif untuk testing lokal (--demo), TIDAK dipakai
    di jalur produksi. Setara dengan 'Opsi D' pada notebook asli."""
    log("📥 [SAMPLE] Mode --demo aktif: membangkitkan data sintetis (bukan data riil)...")
    np.random.seed(42)
    n_kecamatan = 16
    data_sintetis = {
        'id_kecamatan'        : list(range(1, n_kecamatan + 1)),
        'nama_kecamatan'      : [
            'Batujajar', 'Cihampelas', 'Cikalongwetan', 'Cililin', 'Cipatat',
            'Cipongkor', 'Cisarua', 'Gununghalu', 'Lembang', 'Ngamprah',
            'Padalarang', 'Parongpong', 'Rongga', 'Saguling', 'Sindangkerta',
            'Cipeundeuy'
        ],
        'UMKM_Makanan'        : np.random.randint(50, 500, n_kecamatan),
        'UMKM_Kerajinan'      : np.random.randint(10, 250, n_kecamatan),
        'UMKM_Fashion'        : np.random.randint(5, 200, n_kecamatan),
        'UMKM_Jasa'           : np.random.randint(20, 300, n_kecamatan),
        'UMKM_Lainnya'        : np.random.randint(5, 150, n_kecamatan),
        'Kepadatan_Penduduk'  : np.random.randint(200, 8000, n_kecamatan),
        'Pertumbuhan_Penduduk': np.round(np.random.uniform(0.5, 3.5, n_kecamatan), 2),
        'Jarak_ke_Ibukota'    : np.round(np.random.uniform(2.0, 55.0, n_kecamatan), 1),
    }
    df_raw = pd.DataFrame(data_sintetis)
    tanggal_snapshot = str(date.today())
    tahun_demografi = {i: None for i in range(1, n_kecamatan + 1)}
    log(f"   ✅ Dataset sintetis berhasil dibuat. Shape: {df_raw.shape}")
    return df_raw, tanggal_snapshot, tahun_demografi


# =============================================================================
# TAHAP 2 — EXPLORE
# Statistik agregat, integritas data, korelasi. Visualisasi per-variabel
# (histogram/boxplot) sengaja dihindari: dengan n=16, estimasi distribusi
# sangat tidak stabil (lihat keterbatasan penelitian §3.3 spesifikasi).
# =============================================================================
def explore(df_raw: pd.DataFrame, lampiran_dir: Path, skip_lampiran: bool):
    log("\n📌 [EXPLORE 2.1] Informasi Tipe Data (df.info()):\n")
    df_raw.info()

    log("\n📌 [EXPLORE 2.2] Lima baris pertama dataset (df.head()):\n")
    log(df_raw.head().to_string(index=False))

    log("\n📌 [EXPLORE 2.3] Statistik Deskriptif Fitur:\n")
    desc = df_raw[COLS_FITUR].describe().T
    desc['range'] = desc['max'] - desc['min']
    desc['cv_%'] = (desc['std'] / desc['mean'] * 100).round(2)
    log(desc.round(2).to_string())
    log("\n  Interpretasi CV% (Koefisien Variasi):")
    log("  - CV% tinggi antar fitur mengkonfirmasi perbedaan skala ekstrem.")
    log("  - Ini menjustifikasi penggunaan StandardScaler di tahap MODIFY.")

    log("\n📌 [EXPLORE 2.4] Pemeriksaan Missing Values per Fitur:\n")
    missing = df_raw[COLS_FITUR].isnull().sum().reset_index()
    missing.columns = ['Fitur', 'Jumlah_Missing']
    missing['Persentase (%)'] = (missing['Jumlah_Missing'] / len(df_raw) * 100).round(2)
    log(missing.to_string(index=False))
    total_missing = missing['Jumlah_Missing'].sum()
    if total_missing == 0:
        log("\n  ✅ Tidak ada missing values terdeteksi.")
    else:
        log(f"\n  ⚠️  Total {total_missing} missing values ditemukan.")
        log("     Seluruhnya akan ditangani via Median Imputation di tahap MODIFY.")

    if skip_lampiran:
        return
    log("\n📌 [EXPLORE 2.5] Membuat Heatmap Korelasi Pearson (lampiran)...")
    corr_matrix = df_raw[COLS_FITUR].corr(method='pearson')
    fig, ax = plt.subplots(figsize=(10, 8))
    sns.heatmap(
        corr_matrix, annot=True, fmt='.2f', cmap='RdYlGn', center=0,
        vmin=-1, vmax=1, linewidths=0.5, linecolor='white',
        ax=ax, annot_kws={'size': 9}, square=True
    )
    ax.set_title(
        'Heatmap Korelasi Pearson Antar Fitur UMKM\nKabupaten Bandung Barat (Data BPS)',
        fontsize=13, fontweight='bold', pad=15
    )
    ax.tick_params(axis='x', rotation=45, labelsize=9)
    ax.tick_params(axis='y', rotation=0, labelsize=9)
    plt.tight_layout()
    plt.savefig(lampiran_dir / 'explore_heatmap_korelasi.png', bbox_inches='tight')
    plt.close(fig)
    log("  ✅ Disimpan: lampiran/explore_heatmap_korelasi.png")


# =============================================================================
# TAHAP 3 — MODIFY
# (1) Median Imputation, (2) Kalkulasi proporsi sektor, (3) StandardScaler.
# =============================================================================
def modify(df_raw: pd.DataFrame):
    log("\n📌 [MODIFY 3.1] Imputasi Missing Values dengan Median...")
    log("   Justifikasi: Median lebih robust terhadap outlier dibanding Mean.\n")

    total_umkm_raw = df_raw[COLS_UMKM_5].sum(axis=1)
    n_umkm_na = df_raw[COLS_UMKM_5].isnull().sum(axis=1)
    is_imputasi = (n_umkm_na >= 3) | (total_umkm_raw == 0) | (df_raw[COLS_UMKM_5] == 0).all(axis=1)
    kec_banyak_na = df_raw.loc[is_imputasi, ['nama_kecamatan']]
    if len(kec_banyak_na) > 0:
        log("  ⚠️  PERINGATAN — Kecamatan dengan data UMKM diimputasi:")
        log(kec_banyak_na.to_string(index=False))
        keg_imputasi_tinggi = kec_banyak_na['nama_kecamatan'].tolist()
    else:
        keg_imputasi_tinggi = []
        log("  ✅ Tidak ada kecamatan dengan data UMKM yang diimputasi.")

    df_processed = df_raw.copy()
    log_imputasi = []
    for col in COLS_FITUR:
        n_missing = df_processed[col].isnull().sum()
        if n_missing > 0:
            median_val = df_processed[col].median()
            if pd.isna(median_val):
                median_val = 0.0
            df_processed[col] = df_processed[col].fillna(median_val)
            log_imputasi.append({
                'Fitur': col, 'Missing Diisi': int(n_missing), 'Nilai Median': round(float(median_val), 4)
            })
    if log_imputasi:
        log(pd.DataFrame(log_imputasi).to_string(index=False))
        log(f"\n  ✅ {len(log_imputasi)} fitur berhasil diimputasi.")
    else:
        log("  ✅ Tidak ada imputasi diperlukan (dataset sudah bersih).")
    assert df_processed[COLS_FITUR].isnull().sum().sum() == 0, "Masih ada missing values setelah imputasi!"
    log("  ✅ Verifikasi: 0 missing values tersisa.")

    # --- 3.2 Kalkulasi Proporsi Sektor UMKM ---
    log("\n📌 [MODIFY 3.2] Kalkulasi Proporsi Sektor UMKM...")
    log("   Formula: PROP_Sektor = UMKM_Sektor / Total_UMKM (5 sektor)\n")
    df_processed['total_umkm_5'] = df_processed[COLS_UMKM_5].sum(axis=1)
    for col_umkm, col_prop in zip(COLS_UMKM_5, COLS_PROP):
        df_processed[col_prop] = np.where(
            df_processed['total_umkm_5'] > 0,
            df_processed[col_umkm] / df_processed['total_umkm_5'],
            0.2  # uniform 20% jika kecamatan tidak punya data UMKM sama sekali
        )
    sum_prop = df_processed[COLS_PROP].sum(axis=1)
    assert np.allclose(sum_prop, 1.0), "Proporsi sektor tidak menjumlah ke 1!"
    log("  ✅ Proporsi berhasil dihitung. Tiap baris sum = 1.0 (100%)")

    # --- 3.3 Standardisasi Z-score ---
    log("\n📌 [MODIFY 3.3] Standardisasi Z-score pada Fitur Clustering...")
    log("   Fitur yang di-scale: 5 proporsi UMKM + 3 demografis/spasial\n")
    scaler = StandardScaler()
    X_scaled = scaler.fit_transform(df_processed[COLS_FITUR_CLUSTER])
    df_scaled = pd.DataFrame(X_scaled, columns=COLS_FITUR_CLUSTER)
    verif = pd.DataFrame({
        'Fitur': COLS_FITUR_CLUSTER,
        'Mean (setelah)': df_scaled.mean().round(10).values,
        'Std  (setelah)': df_scaled.std().round(4).values,
    })
    log(verif.to_string(index=False))
    log("\n  ✅ Semua fitur clustering kini berada pada skala setara (Z-score).")

    # --- 3.4 scaler_params (disimpan ke analisis.scaler_params) ---
    scaler_params_dict = {
        "feature_names": COLS_FITUR_CLUSTER,
        "mean_"        : scaler.mean_.tolist(),
        "scale_"       : scaler.scale_.tolist(),
        "catatan_unit" : "f_prop_* dalam skala persen (0-100)",
        "catatan"      : "Fitur input adalah PROPORSI (0-1), bukan nilai absolut.",
    }
    log("\n📌 [MODIFY 3.4] scaler_params siap diekspor.")

    return df_processed, X_scaled, scaler, scaler_params_dict, keg_imputasi_tinggi


# =============================================================================
# TAHAP 4 — MODEL
# Elbow + Silhouette untuk K optimal, lalu fit K-Means final.
# =============================================================================
def run_model(X_scaled, n_samples: int, output_dir: Path):
    log("\n📌 [MODEL 4.1] Pencarian K Optimal...\n")
    # Guard: silhouette_score butuh 2 <= k <= n_samples-1. Default range spec
    # adalah 2..8 (untuk n=16); di-cap otomatis agar tidak crash pada n kecil
    # (mis. saat --demo dijalankan dengan dataset custom yang lebih kecil).
    max_k = min(8, n_samples - 1)
    if max_k < 2:
        raise ValueError(f"Jumlah sampel terlalu sedikit untuk mencari K optimal (n={n_samples}, minimal 3).")
    K_RANGE = range(2, max_k + 1)

    inertia, sil_scores = [], []
    for k in K_RANGE:
        km = KMeans(n_clusters=k, init='k-means++', n_init=20, random_state=42)
        km.fit(X_scaled)
        sil = silhouette_score(X_scaled, km.labels_)
        inertia.append(km.inertia_)
        sil_scores.append(sil)
        log(f"   K={k} | Inertia: {km.inertia_:8.2f} | Silhouette Score: {sil:.4f}")

    best_k_idx = int(np.argmax(sil_scores))
    K_OPTIMAL = list(K_RANGE)[best_k_idx]
    log(f"\n  ✅ K Optimal terpilih: K = {K_OPTIMAL}")
    log(f"     Justifikasi: Silhouette Score tertinggi ({max(sil_scores):.4f})")

    # --- 4.2 Elbow plot -> elbow.png (path_grafik[0]) ---
    log("\n📌 [MODEL 4.2] Membuat plot Elbow Method → elbow.png...")
    fig, ax = plt.subplots(figsize=(8, 5))
    ax.plot(K_RANGE, inertia, 'o-', color='#2563EB', linewidth=2.5, markersize=9,
            markerfacecolor='white', markeredgewidth=2.5)
    ax.plot(K_OPTIMAL, inertia[best_k_idx], 'o', color='#DC2626', markersize=14,
            zorder=5, label=f'K Optimal = {K_OPTIMAL}')
    ax.set_xlabel('Jumlah Klaster (K)', fontsize=11)
    ax.set_ylabel('Inertia / WCSS\n(Within-Cluster Sum of Squares)', fontsize=11)
    ax.set_title('Elbow Method — Penentuan K Optimal\nK-Means Clustering UMKM Kabupaten Bandung Barat',
                 fontsize=12, fontweight='bold', pad=12)
    ax.xaxis.set_major_locator(ticker.MultipleLocator(1))
    ax.grid(axis='y', linestyle='--', alpha=0.5)
    ax.legend(fontsize=10)
    for x, y in zip(K_RANGE, inertia):
        ax.annotate(f'{y:.1f}', (x, y), textcoords='offset points', xytext=(0, 12),
                    ha='center', fontsize=8, color='#374151')
    plt.tight_layout()
    plt.savefig(output_dir / 'elbow.png', bbox_inches='tight')
    plt.close(fig)
    log("  ✅ Disimpan: elbow.png → path_grafik[0]")

    # --- 4.3 Silhouette plot -> silhouette.png (path_grafik[1]) ---
    log("\n📌 [MODEL 4.3] Membuat plot Silhouette Score → silhouette.png...")
    fig, ax = plt.subplots(figsize=(8, 5))
    colors = [PALETTE[i % len(PALETTE)] for i in range(len(K_RANGE))]
    bars = ax.bar(K_RANGE, sil_scores, color=colors, edgecolor='white', linewidth=0.8, alpha=0.88)
    bars[best_k_idx].set_edgecolor('#1D1D1D')
    bars[best_k_idx].set_linewidth(2.5)
    ax.set_xlabel('Jumlah Klaster (K)', fontsize=11)
    ax.set_ylabel('Silhouette Score', fontsize=11)
    ax.set_title('Silhouette Score per Nilai K\nK-Means Clustering UMKM Kabupaten Bandung Barat',
                 fontsize=12, fontweight='bold', pad=12)
    ax.xaxis.set_major_locator(ticker.MultipleLocator(1))
    ax.set_ylim(0, max(sil_scores) * 1.3)
    ax.grid(axis='y', linestyle='--', alpha=0.5)
    for bar, score in zip(bars, sil_scores):
        ax.text(bar.get_x() + bar.get_width() / 2., bar.get_height() + 0.005,
                f'{score:.3f}', ha='center', va='bottom', fontsize=9, fontweight='bold')
    ax.annotate(f'  ← K Optimal = {K_OPTIMAL}\n     (Skor Tertinggi)',
                xy=(K_OPTIMAL, sil_scores[best_k_idx]),
                xytext=(K_OPTIMAL + 0.5, sil_scores[best_k_idx] * 1.0),
                fontsize=9, color='#DC2626',
                arrowprops=dict(arrowstyle='->', color='#DC2626', lw=1.5))
    plt.tight_layout()
    plt.savefig(output_dir / 'silhouette.png', bbox_inches='tight')
    plt.close(fig)
    log("  ✅ Disimpan: silhouette.png → path_grafik[1]")

    # --- 4.4 Fit K-Means final ---
    log(f"\n📌 [MODEL 4.4] Fitting K-Means Final (K={K_OPTIMAL})...")
    kmeans_final = KMeans(n_clusters=K_OPTIMAL, init='k-means++', n_init=50,
                           max_iter=500, random_state=42)
    kmeans_final.fit(X_scaled)
    log(f"  ✅ Model K-Means final berhasil di-fit. Inertia={kmeans_final.inertia_:.4f}, "
        f"Konvergensi={kmeans_final.n_iter_} iterasi")

    return K_OPTIMAL, kmeans_final, inertia, sil_scores, best_k_idx


def plot_centroid_heatmap(kmeans_final, K_OPTIMAL, output_dir_lampiran: Path):
    """--- 4.5 Heatmap Profil Centroid (Skala Z-score) — lampiran skripsi ---
    FIX: kolom dilabeli COLS_FITUR_CLUSTER (proporsi + demografi) yang memang
    menjadi ruang fitur clustering — bukan COLS_FITUR (UMKM absolut) seperti
    pada notebook asli, yang salah label meskipun nilainya tidak berubah."""
    log("\n📌 [MODEL 4.5] Visualisasi Profil Centroid Klaster (lampiran)...")
    centroid_scaled_df = pd.DataFrame(
        kmeans_final.cluster_centers_,
        columns=COLS_FITUR_CLUSTER,
        index=[f'Klaster {i}' for i in range(K_OPTIMAL)]
    )
    fig, ax = plt.subplots(figsize=(12, max(4, K_OPTIMAL * 1.2)))
    sns.heatmap(centroid_scaled_df, annot=True, fmt='.2f', cmap='coolwarm', center=0,
                linewidths=0.5, linecolor='white', ax=ax, annot_kws={'size': 9})
    ax.set_title(f'Profil Centroid Klaster (Nilai Z-score, K={K_OPTIMAL})\n'
                 f'Merah = Di Atas Rata-rata | Biru = Di Bawah Rata-rata',
                 fontsize=12, fontweight='bold', pad=12)
    ax.tick_params(axis='x', rotation=40, labelsize=9)
    ax.tick_params(axis='y', rotation=0, labelsize=9)
    plt.tight_layout()
    plt.savefig(output_dir_lampiran / 'model_centroid_heatmap.png', bbox_inches='tight')
    plt.close(fig)
    log("  ✅ Disimpan: lampiran/model_centroid_heatmap.png")


# =============================================================================
# TAHAP 5 — ASSESS
# Evaluasi model + interpretasi cluster + ekspor 3 JSON DB-ready + scatter PCA.
# =============================================================================
def assess(df_processed, X_scaled, scaler, scaler_params_dict, kmeans_final, K_OPTIMAL,
           keg_imputasi_tinggi, tanggal_snapshot, tahun_demografi, inertia_meta,
           output_dir: Path, lampiran_dir: Path, skip_lampiran: bool):
    labels = kmeans_final.labels_

    # --- 5.1 Metrik evaluasi ---
    log("\n📌 [ASSESS 5.1] Kalkulasi Metrik Evaluasi Klasterisasi...\n")
    silhouette_final = silhouette_score(X_scaled, labels)
    dbi_final = davies_bouldin_score(X_scaled, labels)
    log(f"   Silhouette Coefficient : {silhouette_final:.4f}")
    log(f"   Davies-Bouldin Index   : {dbi_final:.4f}")
    log("   Catatan: n kecil menyebabkan nilai metrik kurang stabil dibanding dataset besar;")
    log("   validasi tambahan dilakukan via profil centroid vs. data BPS riil (§3.3 spesifikasi).")

    # --- 5.2 Interpretasi cluster berbasis profil sektor (proporsi centroid) ---
    log("\n📌 [ASSESS 5.2] Interpretasi Cluster Berbasis Profil Sektor UMKM...\n")
    # Centroid dalam ruang PROPORSI + demografis (skala asli, hasil inverse_transform).
    # Dilabeli dengan nama yang BENAR (COLS_FITUR_CLUSTER), bukan COLS_FITUR.
    centroid_prop_df = pd.DataFrame(
        scaler.inverse_transform(kmeans_final.cluster_centers_),
        columns=COLS_FITUR_CLUSTER,
        index=[f'Klaster {i}' for i in range(K_OPTIMAL)]
    )
    log("   Nilai Centroid (skala asli — proporsi [0-1] utk sektor, absolut utk demografi):")
    log(centroid_prop_df.round(4).to_string())

    # Rata-rata TOTAL UMKM absolut per klaster (statistik naratif yang benar,
    # dihitung dari data mentah ter-imputasi, BUKAN dari sum(proporsi)≈1 seperti
    # pada notebook asli yang menghasilkan angka "Total UMKM: 1 unit" yang keliru).
    df_tmp = df_processed.copy()
    df_tmp['label_cluster'] = labels
    rata_total_umkm_per_cluster = df_tmp.groupby('label_cluster')['total_umkm_5'].mean()

    rata_rata_lintas_klaster = centroid_prop_df[COLS_PROP].mean(axis=0)
    CLUSTER_KE_INTERPRETASI = {}
    CLUSTER_KE_PROFIL_DETAIL = {}
    for cluster_id in range(K_OPTIMAL):
        centroid_prop = centroid_prop_df.loc[f'Klaster {cluster_id}', COLS_PROP]  # sum ≈ 1.0
        share_persen = centroid_prop * 100  # karena sum≈1.0, share = proporsi * 100
        di_atas_baseline = centroid_prop > rata_rata_lintas_klaster
        ranking_sektor = centroid_prop.sort_values(ascending=False)
        top_2 = [NAMA_SEKTOR_PROP[s] for s in ranking_sektor.index[:2]]
        bottom_1 = NAMA_SEKTOR_PROP[ranking_sektor.index[-1]]
        label = f"Dominan {' & '.join(top_2)} | Rendah {bottom_1}"
        CLUSTER_KE_INTERPRETASI[cluster_id] = label
        CLUSTER_KE_PROFIL_DETAIL[cluster_id] = {
            "label_cluster": cluster_id,
            "interpretasi": label,
            "sektor_dominan": top_2,
            "sektor_rendah": [bottom_1],
            "ranking_sektor": [
                {
                    "sektor": NAMA_SEKTOR_PROP[s],
                    "nilai": round(float(centroid_prop[s]), 4),        # proporsi 0-1
                    "share_pct": round(float(share_persen[s]), 1),     # persen 0-100
                    "vs_baseline": "ATAS" if di_atas_baseline[s] else "BAWAH",
                }
                for s in ranking_sektor.index
            ],
        }
        total_umkm_riil = rata_total_umkm_per_cluster.get(cluster_id, float('nan'))
        log(f"\n  📍 Klaster {cluster_id}: {label}")
        log(f"     Rata-rata total UMKM per kecamatan anggota klaster: {total_umkm_riil:.0f} unit")
        for s in ranking_sektor.index:
            status = "↑ di atas" if di_atas_baseline[s] else "↓ di bawah"
            log(f"     {NAMA_SEKTOR_PROP[s]:<12} {share_persen[s]:>6.1f}%  vs baseline: {status}")

    # --- Tempelkan label & flag ke df_hasil (dibangun dari df_processed, sudah diimputasi) ---
    df_hasil = df_processed.copy()
    df_hasil['label_cluster'] = labels
    df_hasil['interpretasi'] = df_hasil['label_cluster'].map(CLUSTER_KE_INTERPRETASI)
    df_hasil['flag_imputasi'] = df_hasil['nama_kecamatan'].apply(
        lambda x: 'PERLU_VALIDASI' if x in keg_imputasi_tinggi else 'OK'
    )
    if keg_imputasi_tinggi:
        log(f"\n   ⚠️  Kecamatan berikut perlu validasi kualitatif (banyak nilai UMKM diimputasi):")
        for nm in keg_imputasi_tinggi:
            log(f"       - {nm}")

    # --- 5.3 Distribusi anggota per klaster ---
    log("\n📌 [ASSESS 5.3] Distribusi Anggota per Klaster:\n")
    dist = df_hasil.groupby(['label_cluster', 'interpretasi']).agg(
        Jumlah_Kecamatan=('nama_kecamatan', 'count'),
        Anggota=('nama_kecamatan', lambda x: ', '.join(x))
    ).reset_index()
    log(dist.to_string(index=False))

    # --- 5.4 Ranking sektor per kecamatan (nilai ABSOLUT, dari df_processed) ---
    log("\n📌 [ASSESS 5.4] Ranking Sektor Per Kecamatan (berbasis nilai absolut)...\n")
    sektor_top1_list, sektor_top2_list, sektor_bottom1_list = [], [], []
    ranking_detail_list = []
    for _, row in df_processed.iterrows():
        nilai_4 = {col: row[col] for col in COLS_UMKM_4_MAIN}
        ranked_4 = sorted(nilai_4.items(), key=lambda x: (-x[1], NAMA_SEKTOR[x[0]]))
        sektor_top1_list.append(NAMA_SEKTOR[ranked_4[0][0]])
        sektor_top2_list.append(NAMA_SEKTOR[ranked_4[1][0]])
        sektor_bottom1_list.append(NAMA_SEKTOR[ranked_4[-1][0]])

        nilai_5 = {col: row[col] for col in COLS_UMKM_5}
        ranked_5 = sorted(nilai_5.items(), key=lambda x: (-x[1], NAMA_SEKTOR[x[0]]))
        ranking_detail_list.append([
            {"rank": r + 1, "sektor": NAMA_SEKTOR[s], "nilai": round(float(v), 1)}
            for r, (s, v) in enumerate(ranked_5)
        ])
    df_hasil['sektor_top1'] = sektor_top1_list
    df_hasil['sektor_top2'] = sektor_top2_list
    df_hasil['sektor_bottom1'] = sektor_bottom1_list
    df_hasil['sektor_bottom2'] = 'Lainnya'
    df_hasil['ranking_sektor_json'] = ranking_detail_list
    log(f"   {'Kecamatan':<17} {'Klaster':<9} {'Top-1':>10} {'Top-2':>10} {'Bottom-1':>10} {'Bottom-2':>10}")
    for _, row in df_hasil.iterrows():
        flag = " ⚠️" if row['flag_imputasi'] == 'PERLU_VALIDASI' else ""
        log(f"   {row['nama_kecamatan']:<17} K{row['label_cluster']:<8} "
            f"{row['sektor_top1']:>10} {row['sektor_top2']:>10} "
            f"{row['sektor_bottom1']:>10} {row['sektor_bottom2']:>10}{flag}")

    # --- 5.5 Scatter PCA 2D -> scatter_cluster.png (path_grafik[2]) ---
    log("\n📌 [ASSESS 5.5] Membuat Scatter Plot PCA 2D → scatter_cluster.png...")
    pca = PCA(n_components=2, random_state=42)
    X_pca = pca.fit_transform(X_scaled)
    explained_var = pca.explained_variance_ratio_
    fig, ax = plt.subplots(figsize=(10, 7))
    df_hasil_reset = df_hasil.reset_index(drop=True)
    for cluster_id in range(K_OPTIMAL):
        mask = labels == cluster_id
        lbl = f"Klaster {cluster_id}: {CLUSTER_KE_INTERPRETASI[cluster_id]}"
        ax.scatter(X_pca[mask, 0], X_pca[mask, 1], s=130, label=lbl,
                   color=PALETTE[cluster_id % len(PALETTE)],
                   edgecolors='white', linewidth=1.2, zorder=3, alpha=0.90)
        idxs = np.where(mask)[0]
        for i in idxs:
            ax.annotate(df_hasil_reset.loc[i, 'nama_kecamatan'], (X_pca[i, 0], X_pca[i, 1]),
                       fontsize=7.5, ha='left', xytext=(5, 5), textcoords='offset points',
                       color='#374151')
    centroids_pca = pca.transform(kmeans_final.cluster_centers_)
    ax.scatter(centroids_pca[:, 0], centroids_pca[:, 1], s=300, marker='*',
               color='black', zorder=5, label='Centroid', alpha=0.85)
    ax.set_xlabel(f'PC-1 ({explained_var[0]*100:.1f}% variansi)', fontsize=11)
    ax.set_ylabel(f'PC-2 ({explained_var[1]*100:.1f}% variansi)', fontsize=11)
    ax.set_title(f'Scatter Plot K-Means (K={K_OPTIMAL}) — Proyeksi PCA 2D\n'
                f'Total variansi terwakili: {sum(explained_var)*100:.1f}%',
                fontsize=12, fontweight='bold', pad=12)
    ax.legend(fontsize=9, framealpha=0.85, loc='best')
    ax.grid(linestyle='--', alpha=0.35)
    plt.tight_layout()
    plt.savefig(output_dir / 'scatter_cluster.png', bbox_inches='tight')
    plt.close(fig)
    log("  ✅ Disimpan: scatter_cluster.png → path_grafik[2]")

    # --- 5.6 Ekspor 3 JSON DB-ready ---
    log("\n" + "=" * 70)
    log("📌 [ASSESS 5.6] Ekspor Output DB-Ready (3 File JSON)")
    log("=" * 70)

    # A) metadata_output.json -> tabel `analisis`
    snapshot_data = []
    for _, row in df_processed.iterrows():
        kec_id = int(row['id_kecamatan'])
        fitur_db = {KOLOM_MAP_SCRIPT_KE_DB[c]: round(float(row[c]), 4) for c in COLS_FITUR}
        snapshot_data.append({
            "id_kecamatan": kec_id,
            "tahun_demografi": tahun_demografi.get(kec_id),
            "fitur": fitur_db,
        })
    dataset_snapshot_json = {"tanggal_snapshot": tanggal_snapshot, "data": snapshot_data}
    metadata_output = {
        "k_optimal": int(K_OPTIMAL),
        "nilai_silhouette": round(float(silhouette_final), 4),
        "nilai_dbi": round(float(dbi_final), 4),
        "scaler_params": scaler_params_dict,
        "model_params": {
            "init": "k-means++",
            "n_init": 50,
            "max_iter": 500,
            "random_state": 42,
            "inertia_final": round(float(kmeans_final.inertia_), 4),
            "n_iter": int(kmeans_final.n_iter_),
        },
        "path_grafik": ["elbow.png", "silhouette.png", "scatter_cluster.png"],
        "dataset_snapshot": dataset_snapshot_json,
    }
    write_json(metadata_output, output_dir / 'metadata_output.json')
    log("  ✅ Disimpan: metadata_output.json → INSERT ke tabel analisis")

    # B) hasil_cluster_output.json -> tabel `hasil_cluster`
    hasil_cluster_output = []
    for _, row in df_hasil.iterrows():
        hasil_cluster_output.append({
            "id_kecamatan": int(row['id_kecamatan']),
            "label_cluster": int(row['label_cluster']),
            "interpretasi": str(row['interpretasi']),
            "sektor_top1": str(row['sektor_top1']),
            "sektor_top2": str(row['sektor_top2']),
            "sektor_bottom1": str(row['sektor_bottom1']),
            "sektor_bottom2": "Lainnya",
            "ranking_sektor_5": row['ranking_sektor_json'],
            "flag_imputasi": str(row['flag_imputasi']),
        })
    write_json(hasil_cluster_output, output_dir / 'hasil_cluster_output.json')
    log(f"  ✅ Disimpan: hasil_cluster_output.json → INSERT ke tabel hasil_cluster ({len(hasil_cluster_output)} baris)")

    # C) centroid_output.json -> tabel `centroid`
    centroid_output = []
    for cluster_id in range(K_OPTIMAL):
        nilai_prop_centroid = scaler.inverse_transform(
            kmeans_final.cluster_centers_[cluster_id].reshape(1, -1)
        )[0]
        nilai_fitur_json = {}
        for i, col_cluster in enumerate(COLS_FITUR_CLUSTER):
            if col_cluster in COLS_PROP:
                nama_json = 'f_prop_' + col_cluster.replace('PROP_', '').lower()
                # f_prop_* WAJIB skala persen (0-100), bukan fraksi (0-1) — lihat CLAUDE.md
                nilai_fitur_json[nama_json] = round(float(nilai_prop_centroid[i]) * 100, 2)
            else:
                col_json = KOLOM_MAP_SCRIPT_KE_JSON.get(col_cluster, col_cluster.lower())
                nilai_fitur_json[col_json] = round(float(nilai_prop_centroid[i]), 4)
        profil = CLUSTER_KE_PROFIL_DETAIL[cluster_id]
        centroid_output.append({
            "label_cluster": int(cluster_id),
            "interpretasi": CLUSTER_KE_INTERPRETASI[cluster_id],
            "sektor_dominan": profil["sektor_dominan"],
            "sektor_rendah": profil["sektor_rendah"],
            "ranking_sektor": profil["ranking_sektor"],
            "nilai_fitur": nilai_fitur_json,
        })
    write_json(centroid_output, output_dir / 'centroid_output.json')
    log(f"  ✅ Disimpan: centroid_output.json → INSERT ke tabel centroid ({len(centroid_output)} baris)")

    # --- 5.7 Lampiran: heatmap centroid + CSV flat ---
    if not skip_lampiran:
        plot_centroid_heatmap(kmeans_final, K_OPTIMAL, lampiran_dir)

        log("\n📌 [ASSESS 5.7] Ekspor CSV flat (lampiran skripsi)...")
        cols_csv = (COLS_IDENTIFIER + COLS_FITUR + ['label_cluster', 'interpretasi']
                    + ['sektor_top1', 'sektor_top2', 'sektor_bottom1', 'sektor_bottom2']
                    + ['flag_imputasi'])
        df_ekspor = df_hasil[cols_csv].copy()
        csv_path = lampiran_dir / 'hasil_klasterisasi_umkm_kbb.csv'
        df_ekspor.to_csv(csv_path, index=False, encoding='utf-8-sig')
        log(f"  ✅ Disimpan: lampiran/hasil_klasterisasi_umkm_kbb.csv (shape={df_ekspor.shape})")

    return metadata_output, hasil_cluster_output, centroid_output, silhouette_final, dbi_final


# =============================================================================
# MAIN / ORKESTRASI PIPELINE
# =============================================================================
def parse_args():
    p = argparse.ArgumentParser(
        description="SEMMA K-Means Clustering UMKM Kabupaten Bandung Barat (production)."
    )
    p.add_argument('--input', type=str, default=None,
                    help=f"Path ke dataset_snapshot.json (default: {DEFAULT_INPUT})")
    p.add_argument('--output-dir', type=str, default=None,
                    help=f"Direktori output (default: {DEFAULT_OUTPUT_DIR})")
    p.add_argument('--demo', action='store_true',
                    help="Gunakan data sintetis untuk testing lokal (mengabaikan --input)")
    p.add_argument('--skip-lampiran', action='store_true',
                    help="Lewati pembuatan file lampiran skripsi (heatmap EDA, heatmap model, CSV)")
    return p.parse_args()


def main():
    args = parse_args()
    input_path = Path(args.input).resolve() if args.input else DEFAULT_INPUT
    output_dir = Path(args.output_dir).resolve() if args.output_dir else DEFAULT_OUTPUT_DIR
    lampiran_dir = output_dir / 'lampiran'

    output_dir.mkdir(parents=True, exist_ok=True)
    if not args.skip_lampiran:
        lampiran_dir.mkdir(parents=True, exist_ok=True)

    log("=" * 70)
    log("SEMMA K-Means Clustering — UMKM Kabupaten Bandung Barat (v3.0)")
    log("=" * 70)
    log(f"Mode         : {'DEMO (data sintetis)' if args.demo else 'PRODUKSI'}")
    log(f"Input        : {'-- (demo) --' if args.demo else input_path}")
    log(f"Output dir   : {output_dir}")

    try:
        # SAMPLE
        if args.demo:
            df_raw, tanggal_snapshot, tahun_demografi = load_demo_dataset()
        else:
            df_raw, tanggal_snapshot, tahun_demografi = load_dataset_snapshot(input_path)

        # EXPLORE
        explore(df_raw, lampiran_dir, args.skip_lampiran)

        # MODIFY
        df_processed, X_scaled, scaler, scaler_params_dict, keg_imputasi_tinggi = modify(df_raw)

        # MODEL
        K_OPTIMAL, kmeans_final, inertia, sil_scores, best_k_idx = run_model(
            X_scaled, n_samples=len(df_processed), output_dir=output_dir
        )

        # ASSESS
        _, _, _, silhouette_final, dbi_final = assess(
            df_processed, X_scaled, scaler, scaler_params_dict, kmeans_final, K_OPTIMAL,
            keg_imputasi_tinggi, tanggal_snapshot, tahun_demografi, inertia,
            output_dir, lampiran_dir, args.skip_lampiran
        )

        log("\n" + "=" * 70)
        log("✅ PIPELINE SELESAI")
        log("=" * 70)
        log(f"  K optimal      : {K_OPTIMAL}")
        log(f"  Silhouette     : {silhouette_final:.4f}")
        log(f"  Davies-Bouldin : {dbi_final:.4f}")
        log(f"  Output ditulis ke: {output_dir}")
        return 0

    except Exception:
        log("\n" + "=" * 70)
        log("❌ PIPELINE GAGAL")
        log("=" * 70)
        # Traceback lengkap ke stderr agar tertangkap Process::run()->errorOutput()
        # dan bisa disimpan Laravel Job ke kolom analisis.error_log.
        traceback.print_exc(file=sys.stderr)
        sys.stderr.flush()
        return 1


if __name__ == '__main__':
    sys.exit(main())