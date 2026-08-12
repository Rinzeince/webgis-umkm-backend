<?php

namespace Database\Seeders;

use App\Models\DemografiKecamatan;
use App\Models\Kecamatan;
use Illuminate\Database\Seeder;

class DemografiKecamatanSeeder extends Seeder
{
    /**
     * Run the database seeds for BPS Demografi data per kecamatan (Standard GeoJSON order 1..16).
     */
    public function run(): void
    {
        $demografiData = [
            1  => ['kepadatan_penduduk' => 1520, 'pertumbuhan_penduduk' => 1.25, 'jarak_ke_ibukota' => 18.5], // Lembang
            2  => ['kepadatan_penduduk' => 1240, 'pertumbuhan_penduduk' => 1.10, 'jarak_ke_ibukota' => 16.2], // Parongpong
            3  => ['kepadatan_penduduk' => 980,  'pertumbuhan_penduduk' => 0.95, 'jarak_ke_ibukota' => 14.0], // Cisarua
            4  => ['kepadatan_penduduk' => 850,  'pertumbuhan_penduduk' => 1.45, 'jarak_ke_ibukota' => 22.5], // Cikalongwetan
            5  => ['kepadatan_penduduk' => 620,  'pertumbuhan_penduduk' => 1.05, 'jarak_ke_ibukota' => 30.0], // Cipeundeuy
            6  => ['kepadatan_penduduk' => 2850, 'pertumbuhan_penduduk' => 2.15, 'jarak_ke_ibukota' => 0.0],  // Ngamprah
            7  => ['kepadatan_penduduk' => 1150, 'pertumbuhan_penduduk' => 1.42, 'jarak_ke_ibukota' => 16.8], // Cipatat
            8  => ['kepadatan_penduduk' => 3150, 'pertumbuhan_penduduk' => 2.30, 'jarak_ke_ibukota' => 3.5],  // Padalarang
            9  => ['kepadatan_penduduk' => 1650, 'pertumbuhan_penduduk' => 1.40, 'jarak_ke_ibukota' => 9.2],  // Batujajar
            10 => ['kepadatan_penduduk' => 1480, 'pertumbuhan_penduduk' => 1.60, 'jarak_ke_ibukota' => 12.4], // Cihampelas
            11 => ['kepadatan_penduduk' => 1320, 'pertumbuhan_penduduk' => 1.35, 'jarak_ke_ibukota' => 18.0], // Cililin
            12 => ['kepadatan_penduduk' => 780,  'pertumbuhan_penduduk' => 1.12, 'jarak_ke_ibukota' => 28.5], // Cipongkor
            13 => ['kepadatan_penduduk' => 520,  'pertumbuhan_penduduk' => 0.85, 'jarak_ke_ibukota' => 42.0], // Rongga
            14 => ['kepadatan_penduduk' => 890,  'pertumbuhan_penduduk' => 1.20, 'jarak_ke_ibukota' => 24.0], // Sindangkerta
            15 => ['kepadatan_penduduk' => 580,  'pertumbuhan_penduduk' => 0.90, 'jarak_ke_ibukota' => 38.5], // Gununghalu
            16 => ['kepadatan_penduduk' => 490,  'pertumbuhan_penduduk' => 0.75, 'jarak_ke_ibukota' => 25.5], // Saguling
        ];

        foreach ($demografiData as $idKecamatan => $data) {
            DemografiKecamatan::updateOrCreate(
                [
                    'id_kecamatan' => $idKecamatan,
                    'tahun' => 2025,
                ],
                [
                    'kepadatan_penduduk' => $data['kepadatan_penduduk'],
                    'pertumbuhan_penduduk' => $data['pertumbuhan_penduduk'],
                    'jarak_ke_ibukota' => $data['jarak_ke_ibukota'],
                ]
            );
        }
    }
}
