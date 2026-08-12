<?php

namespace Database\Seeders;

use App\Models\Kecamatan;
use Illuminate\Database\Seeder;

class KecamatanSeeder extends Seeder
{
    /**
     * Run the database seeds for 16 kecamatan in Kabupaten Bandung Barat (Standard GeoJSON order).
     */
    public function run(): void
    {
        $kecamatanList = [
            1  => ['nama' => 'Lembang',        'kode' => '32.17.01'],
            2  => ['nama' => 'Parongpong',     'kode' => '32.17.02'],
            3  => ['nama' => 'Cisarua',        'kode' => '32.17.03'],
            4  => ['nama' => 'Cikalongwetan',  'kode' => '32.17.04'],
            5  => ['nama' => 'Cipeundeuy',     'kode' => '32.17.05'],
            6  => ['nama' => 'Ngamprah',       'kode' => '32.17.06'],
            7  => ['nama' => 'Cipatat',        'kode' => '32.17.07'],
            8  => ['nama' => 'Padalarang',     'kode' => '32.17.08'],
            9  => ['nama' => 'Batujajar',      'kode' => '32.17.09'],
            10 => ['nama' => 'Cihampelas',     'kode' => '32.17.10'],
            11 => ['nama' => 'Cililin',        'kode' => '32.17.11'],
            12 => ['nama' => 'Cipongkor',      'kode' => '32.17.12'],
            13 => ['nama' => 'Rongga',         'kode' => '32.17.13'],
            14 => ['nama' => 'Sindangkerta',   'kode' => '32.17.14'],
            15 => ['nama' => 'Gununghalu',     'kode' => '32.17.15'],
            16 => ['nama' => 'Saguling',       'kode' => '32.17.16'],
        ];

        foreach ($kecamatanList as $id => $item) {
            Kecamatan::updateOrCreate(
                ['id_kecamatan' => $id],
                [
                    'nama_kecamatan' => $item['nama'],
                    'kode_kemendagri' => $item['kode'],
                ]
            );
        }
    }
}
