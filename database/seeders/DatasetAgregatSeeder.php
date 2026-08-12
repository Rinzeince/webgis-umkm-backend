<?php

namespace Database\Seeders;

use App\Models\DatasetAgregat;
use App\Models\Kecamatan;
use Illuminate\Database\Seeder;

class DatasetAgregatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kecamatans = Kecamatan::all();

        foreach ($kecamatans as $kecamatan) {
            DatasetAgregat::firstOrCreate(
                ['id_kecamatan' => $kecamatan->id_kecamatan],
                [
                    'jml_makanan' => 0,
                    'jml_kerajinan' => 0,
                    'jml_fashion' => 0,
                    'jml_jasa' => 0,
                    'jml_lainnya' => 0,
                    'status_analisis' => 'perlu_analisis',
                ]
            );
        }
    }
}
