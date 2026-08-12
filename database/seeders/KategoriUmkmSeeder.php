<?php

namespace Database\Seeders;

use App\Models\KategoriUmkm;
use Illuminate\Database\Seeder;

class KategoriUmkmSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['id_kategori' => 1, 'nama_kategori' => 'Makanan', 'warna_marker' => '#F97316'],
            ['id_kategori' => 2, 'nama_kategori' => 'Kerajinan', 'warna_marker' => '#8B5CF6'],
            ['id_kategori' => 3, 'nama_kategori' => 'Fashion', 'warna_marker' => '#EC4899'],
            ['id_kategori' => 4, 'nama_kategori' => 'Jasa', 'warna_marker' => '#3B82F6'],
            ['id_kategori' => 5, 'nama_kategori' => 'Lainnya', 'warna_marker' => '#6B7280'],
        ];

        foreach ($categories as $category) {
            KategoriUmkm::updateOrCreate(
                ['id_kategori' => $category['id_kategori']],
                [
                    'nama_kategori' => $category['nama_kategori'],
                    'warna_marker' => $category['warna_marker'],
                ]
            );
        }
    }
}
