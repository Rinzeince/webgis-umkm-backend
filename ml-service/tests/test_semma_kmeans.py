"""
=============================================================================
test_semma_kmeans.py
=============================================================================
Unit Test & Pytest Compatible — Pipeline Machine Learning SEMMA K-Means
Dokumen Referensi: BAB5_PENGUJIAN_SISTEM.md — Sub-bab 5.5.2

Menguji:
1. Tahap Sample  : Validasi format snapshot input JSON
2. Tahap Modify  : Imputasi median, kalkulasi proporsi (sum=1.0), Z-score (mean=0, std=1)
3. Tahap Model   : Pencarian K optimal via Silhouette Score & Elbow Method (K=2..8)
4. Tahap Assess  : Kalkulasi metrik Silhouette, Davies-Bouldin Index, profil Centroid & Ranking Sektoral
=============================================================================
"""

import os
import sys
import json
import shutil
import tempfile
import unittest
from pathlib import Path
import numpy as np
import pandas as pd

# Tambahkan direktori ml-service dan python site-packages ke sys.path jika di Windows
TEST_DIR = Path(__file__).resolve().parent
ML_SERVICE_DIR = TEST_DIR.parent
if str(ML_SERVICE_DIR) not in sys.path:
    sys.path.insert(0, str(ML_SERVICE_DIR))

if sys.platform == 'win32':
    appdata = os.environ.get('APPDATA', r'C:\Users\v14\AppData\Roaming')
    for py_ver in ['Python310', 'Python311', 'Python312', 'Python39']:
        roaming_site = os.path.join(appdata, 'Python', py_ver, 'site-packages')
        if os.path.exists(roaming_site) and roaming_site not in sys.path:
            sys.path.insert(0, roaming_site)

from semma_kmeans_umkm_kbb import (
    load_dataset_snapshot,
    load_demo_dataset,
    modify,
    run_model,
    assess,
    COLS_FITUR,
    COLS_PROP,
    COLS_DEMOGRAFI,
    COLS_FITUR_CLUSTER,
)


class TestSemmaKMeansPipeline(unittest.TestCase):

    def setUp(self):
        self.temp_dir = tempfile.mkdtemp()
        self.tmp_path = Path(self.temp_dir)

    def tearDown(self):
        shutil.rmtree(self.temp_dir, ignore_errors=True)

    def create_sample_snapshot_file(self) -> Path:
        """Helper membuat file dataset_snapshot.json sintetis 16 kecamatan."""
        data = []
        nama_kec = [
            "Batujajar", "Cihampelas", "Cikalongwetan", "Cililin", "Cipatat",
            "Cipeundeuy", "Cipongkor", "Cisarua", "Gununghalu", "Lembang",
            "Ngamprah", "Padalarang", "Parongpong", "Rongga", "Saguling", "Sindangkerta"
        ]
        np.random.seed(42)
        for i, nama in enumerate(nama_kec):
            data.append({
                "id_kecamatan": i + 1,
                "nama_kecamatan": nama,
                "tahun_demografi": 2023,
                "fitur": {
                    "jml_makanan": int(np.random.randint(10, 200)),
                    "jml_kerajinan": int(np.random.randint(5, 80)),
                    "jml_fashion": int(np.random.randint(5, 100)),
                    "jml_jasa": int(np.random.randint(10, 90)),
                    "jml_lainnya": int(np.random.randint(1, 30)),
                    "kepadatan_penduduk": int(np.random.randint(500, 3000)),
                    "pertumbuhan_penduduk": float(round(np.random.uniform(0.8, 2.5), 2)),
                    "jarak_ke_ibukota": float(round(np.random.uniform(5.0, 75.0), 1)),
                }
            })

        snapshot_json = {
            "tanggal_snapshot": "2026-08-18",
            "data": data
        }
        file_path = self.tmp_path / "dataset_snapshot.json"
        with open(file_path, "w", encoding="utf-8") as f:
            json.dump(snapshot_json, f)
        return file_path

    # =========================================================================
    # 1. TAHAP SAMPLE — PENGUJIAN INPUT & INTEGRITAS
    # =========================================================================

    def test_sample_stage_loads_valid_snapshot(self):
        """Memverifikasi pembacaan dataset_snapshot.json menghasilkan DataFrame 16 baris."""
        file_path = self.create_sample_snapshot_file()
        df_raw, tanggal_snapshot, tahun_demografi = load_dataset_snapshot(file_path)

        self.assertIsInstance(df_raw, pd.DataFrame)
        self.assertEqual(len(df_raw), 16)
        self.assertEqual(tanggal_snapshot, "2026-08-18")
        for col in COLS_FITUR:
            self.assertIn(col, df_raw.columns)

    def test_sample_stage_raises_error_on_missing_file(self):
        """Memverifikasi exception dilempar jika file input tidak ditemukan."""
        non_existent = self.tmp_path / "non_existent.json"
        with self.assertRaises(FileNotFoundError):
            load_dataset_snapshot(non_existent)

    def test_sample_stage_raises_error_on_invalid_json(self):
        """Memverifikasi exception jika format JSON corrupt atau rusak."""
        corrupt_file = self.tmp_path / "corrupt.json"
        corrupt_file.write_text("INVALID_JSON_CONTENT", encoding="utf-8")
        with self.assertRaises(ValueError):
            load_dataset_snapshot(corrupt_file)

    # =========================================================================
    # 2. TAHAP MODIFY — PENGUJIAN IMPUTASI, PROPORSI & STANDARDISASI Z-SCORE
    # =========================================================================

    def test_modify_stage_handles_missing_values_with_median(self):
        """Memverifikasi penanganan missing values dengan median imputation."""
        df_demo, _, _ = load_demo_dataset()
        df_demo.loc[0, "UMKM_Makanan"] = np.nan
        df_demo.loc[1, "Kepadatan_Penduduk"] = np.nan

        df_processed, _, _, _, _ = modify(df_demo)

        self.assertEqual(df_processed[COLS_FITUR].isnull().sum().sum(), 0)
        self.assertFalse(np.isnan(df_processed.loc[0, "UMKM_Makanan"]))
        self.assertFalse(np.isnan(df_processed.loc[1, "Kepadatan_Penduduk"]))

    def test_modify_stage_proportions_sum_to_one(self):
        """Memverifikasi bahwa jumlah 5 proporsi sektor untuk setiap kecamatan selalu tepat 1.0 (100%)."""
        df_demo, _, _ = load_demo_dataset()
        df_processed, _, _, _, _ = modify(df_demo)

        for col in COLS_PROP:
            self.assertIn(col, df_processed.columns)

        sum_prop = df_processed[COLS_PROP].sum(axis=1)
        np.testing.assert_allclose(sum_prop.values, 1.0, rtol=1e-5,
                                   err_msg="Proporsi sektor harus menjumlah ke 1.0 untuk tiap kecamatan")

    def test_modify_stage_zscore_properties(self):
        """Memverifikasi sifat matematis StandardScaler: E[Z] = 0 dan Var[Z] = 1 (skala setara di R^8)."""
        df_demo, _, _ = load_demo_dataset()
        _, X_scaled, scaler, scaler_params, _ = modify(df_demo)

        self.assertEqual(X_scaled.shape, (16, 8))
        means = np.mean(X_scaled, axis=0)
        np.testing.assert_allclose(means, 0.0, atol=1e-7,
                                   err_msg="Mean tiap fitur setelah standardisasi harus mendekati 0")
        stds = np.std(X_scaled, axis=0)
        np.testing.assert_allclose(stds, 1.0, atol=1e-7,
                                   err_msg="Std tiap fitur setelah standardisasi harus mendekati 1")

    # =========================================================================
    # 3. TAHAP MODEL — PENGUJIAN SELEKSI K OPTIMAL & FITTING K-MEANS
    # =========================================================================

    def test_model_stage_k_optimal_selection(self):
        """Memverifikasi bahwa penentuan K optimal berada dalam rentang 2..8 dan model K-Means konvergen."""
        df_demo, _, _ = load_demo_dataset()
        _, X_scaled, _, _, _ = modify(df_demo)

        K_OPTIMAL, kmeans_final, inertia_list, sil_scores, best_k_idx = run_model(
            X_scaled, len(df_demo), self.tmp_path
        )

        self.assertTrue(2 <= K_OPTIMAL <= 8)
        self.assertEqual(len(inertia_list), 7)  # K=2 s.d K=8
        self.assertEqual(len(sil_scores), 7)
        self.assertEqual(K_OPTIMAL, list(range(2, 9))[best_k_idx])
        self.assertEqual(max(sil_scores), sil_scores[best_k_idx])

        self.assertEqual(kmeans_final.n_clusters, K_OPTIMAL)
        self.assertTrue(hasattr(kmeans_final, "cluster_centers_"))
        self.assertEqual(kmeans_final.cluster_centers_.shape, (K_OPTIMAL, 8))
        self.assertTrue(hasattr(kmeans_final, "labels_"))
        self.assertEqual(len(kmeans_final.labels_), 16)

        self.assertTrue((self.tmp_path / "elbow.png").exists())
        self.assertTrue((self.tmp_path / "silhouette.png").exists())

    # =========================================================================
    # 4. TAHAP ASSESS — PENGUJIAN METRIK EVALUASI, EXPORT JSON & RANKING SEKTOR
    # =========================================================================

    def test_assess_stage_calculates_valid_metrics_and_exports_json(self):
        """Memverifikasi kalkulasi Silhouette, DBI, dan kelengkapan 3 output JSON untuk database."""
        output_dir = self.tmp_path / "output"
        lampiran_dir = self.tmp_path / "lampiran"
        output_dir.mkdir(parents=True)
        lampiran_dir.mkdir(parents=True)

        df_demo, tanggal_snapshot, tahun_demografi = load_demo_dataset()
        df_processed, X_scaled, scaler, scaler_params, keg_imputasi = modify(df_demo)
        K_OPTIMAL, kmeans_final, inertia_list, _, _ = run_model(X_scaled, len(df_demo), output_dir)

        metadata_out, hasil_out, centroid_out, sil_score, dbi_score = assess(
            df_processed, X_scaled, scaler, scaler_params, kmeans_final, K_OPTIMAL,
            keg_imputasi, tanggal_snapshot, tahun_demografi, inertia_list,
            output_dir, lampiran_dir, skip_lampiran=True
        )

        metadata_path = output_dir / "metadata_output.json"
        hasil_path = output_dir / "hasil_cluster_output.json"
        centroid_path = output_dir / "centroid_output.json"

        self.assertTrue(metadata_path.exists())
        self.assertTrue(hasil_path.exists())
        self.assertTrue(centroid_path.exists())

        self.assertEqual(metadata_out["k_optimal"], K_OPTIMAL)
        self.assertTrue(-1.0 <= sil_score <= 1.0)
        self.assertTrue(dbi_score > 0.0)
        self.assertIn("path_grafik", metadata_out)

        with open(hasil_path, "r", encoding="utf-8") as f:
            hasil = json.load(f)
        self.assertEqual(len(hasil), 16)
        for item in hasil:
            self.assertIn("id_kecamatan", item)
            self.assertIn("label_cluster", item)
            self.assertIn("sektor_top1", item)
            self.assertIn("sektor_top2", item)
            self.assertIn("sektor_bottom1", item)
            self.assertEqual(item["sektor_bottom2"], "Lainnya")

        with open(centroid_path, "r", encoding="utf-8") as f:
            centroids = json.load(f)
        self.assertEqual(len(centroids), K_OPTIMAL)
        for c in centroids:
            self.assertIn("label_cluster", c)
            self.assertIn("interpretasi", c)
            self.assertIn("nilai_fitur", c)


if __name__ == "__main__":
    unittest.main()
