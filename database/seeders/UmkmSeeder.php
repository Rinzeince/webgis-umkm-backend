<?php

namespace Database\Seeders;

use App\Models\DatasetAgregat;
use App\Models\KategoriUmkm;
use App\Models\Kecamatan;
use App\Models\Umkm;
use Illuminate\Database\Seeder;

class UmkmSeeder extends Seeder
{
    /**
     * Run the database seeds for real BPS UMKM distribution dataset per kecamatan (KBB v3.0 - Standard GeoJSON IDs 1..16).
     */
    public function run(): void
    {
        $kategoriList = KategoriUmkm::all()->pluck('id_kategori', 'nama_kategori')->toArray();
        $kecamatanList = Kecamatan::all();

        if (empty($kategoriList) || $kecamatanList->isEmpty()) {
            return;
        }

        // Real BPS UMKM dataset distribution per kecamatan (Kabupaten Bandung Barat v3.0)
        // Standard GeoJSON order: Lembang (1) to Saguling (16)
        // Matches Google Colab v3.0 output: K=4, Silhouette=0.272, WCSS=53.4
        $bpsUmkmDistribution = [
            1  => ['Makanan' => 268, 'Kerajinan' => 51,  'Fashion' => 280, 'Jasa' => 68, 'Lainnya' => 35], // Lembang
            2  => ['Makanan' => 185, 'Kerajinan' => 35,  'Fashion' => 193, 'Jasa' => 47, 'Lainnya' => 24], // Parongpong
            3  => ['Makanan' => 115, 'Kerajinan' => 22,  'Fashion' => 120, 'Jasa' => 29, 'Lainnya' => 15], // Cisarua
            4  => ['Makanan' => 18,  'Kerajinan' => 19,  'Fashion' => 68,  'Jasa' => 8,  'Lainnya' => 2],  // Cikalongwetan
            5  => ['Makanan' => 12,  'Kerajinan' => 14,  'Fashion' => 45,  'Jasa' => 5,  'Lainnya' => 2],  // Cipeundeuy
            6  => ['Makanan' => 160, 'Kerajinan' => 98,  'Fashion' => 420, 'Jasa' => 47, 'Lainnya' => 22], // Ngamprah
            7  => ['Makanan' => 22,  'Kerajinan' => 25,  'Fashion' => 85,  'Jasa' => 10, 'Lainnya' => 3],  // Cipatat
            8  => ['Makanan' => 195, 'Kerajinan' => 118, 'Fashion' => 510, 'Jasa' => 57, 'Lainnya' => 27], // Padalarang
            9  => ['Makanan' => 57,  'Kerajinan' => 51,  'Fashion' => 315, 'Jasa' => 47, 'Lainnya' => 17], // Batujajar
            10 => ['Makanan' => 64,  'Kerajinan' => 115, 'Fashion' => 77,  'Jasa' => 13, 'Lainnya' => 5],  // Cihampelas
            11 => ['Makanan' => 0,   'Kerajinan' => 0,   'Fashion' => 0,   'Jasa' => 0,  'Lainnya' => 0],  // Cililin (Data Imputasi BPS ⚠️)
            12 => ['Makanan' => 6,   'Kerajinan' => 16,  'Fashion' => 52,  'Jasa' => 8,  'Lainnya' => 2],  // Cipongkor
            13 => ['Makanan' => 12,  'Kerajinan' => 10,  'Fashion' => 38,  'Jasa' => 4,  'Lainnya' => 1],  // Rongga
            14 => ['Makanan' => 0,   'Kerajinan' => 0,   'Fashion' => 0,   'Jasa' => 0,  'Lainnya' => 0],  // Sindangkerta (Data Imputasi BPS ⚠️)
            15 => ['Makanan' => 0,   'Kerajinan' => 0,   'Fashion' => 0,   'Jasa' => 0,  'Lainnya' => 0],  // Gununghalu (Data Imputasi BPS ⚠️)
            16 => ['Makanan' => 3,   'Kerajinan' => 9,   'Fashion' => 32,  'Jasa' => 4,  'Lainnya' => 1],  // Saguling
        ];

        foreach ($kecamatanList as $kec) {
            $counts = $bpsUmkmDistribution[$kec->id_kecamatan] ?? [
                'Makanan' => 50, 'Kerajinan' => 20, 'Fashion' => 30, 'Jasa' => 25, 'Lainnya' => 5
            ];

            // Update DatasetAgregat record directly with BPS data
            DatasetAgregat::updateOrCreate(
                ['id_kecamatan' => $kec->id_kecamatan],
                [
                    'jml_makanan' => $counts['Makanan'],
                    'jml_kerajinan' => $counts['Kerajinan'],
                    'jml_fashion' => $counts['Fashion'],
                    'jml_jasa' => $counts['Jasa'],
                    'jml_lainnya' => $counts['Lainnya'],
                    'status_analisis' => 'perlu_analisis',
                ]
            );

            // Create sample UMKM records for non-imputed categories
            foreach ($counts as $katName => $cnt) {
                if ($cnt === 0 || !isset($kategoriList[$katName])) {
                    continue;
                }

                Umkm::updateOrCreate(
                    [
                        'nama_umkm' => "UMKM Sample {$katName} - {$kec->nama_kecamatan}",
                        'id_kecamatan' => $kec->id_kecamatan,
                    ],
                    [
                        'id_kategori' => $kategoriList[$katName],
                        'nama_pemilik' => "Pemilik Usaha {$katName}",
                        'alamat_lengkap' => "Jl. Raya {$kec->nama_kecamatan} No. 10",
                        'status_operasional' => 'aktif',
                        'latitude' => -6.8 + (rand(-50, 50) / 1000),
                        'longitude' => 107.5 + (rand(-50, 50) / 1000),
                    ]
                );
            }
        }
    }
}
