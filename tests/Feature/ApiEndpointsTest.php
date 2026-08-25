<?php

/**
 * =============================================================================
 * ApiEndpointsTest.php
 * =============================================================================
 * PHPUnit — Pengujian Integrasi & Kontrak REST API (12 Endpoints)
 * Dokumen Referensi: BAB5_PENGUJIAN_SISTEM.md — Sub-bab 5.5.3.C (API-01 s.d API-12)
 * =============================================================================
 */

namespace Tests\Feature;

use App\Models\Analisis;
use App\Models\Artikel;
use App\Models\Centroid;
use App\Models\HasilCluster;
use App\Models\KategoriUmkm;
use App\Models\Kecamatan;
use App\Models\Umkm;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ApiEndpointsTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Helper untuk menyiapkan dataset awal.
     */
    private function seedTestData(): array
    {
        $kategori = KategoriUmkm::create([
            'nama_kategori' => 'Kuliner',
            'warna_marker' => '#00684A',
        ]);

        $kecamatan = Kecamatan::create([
            'nama_kecamatan' => 'Kecamatan Test Lembang',
            'kode_kemendagri' => '32.17.99',
        ]);

        $analisis = Analisis::create([
            'k_optimal' => 5,
            'nilai_silhouette' => 0.5842,
            'nilai_dbi' => 0.7120,
            'status_job' => 'selesai',
            'is_published' => true,
            'path_grafik' => ['elbow.png', 'silhouette.png', 'scatter_cluster.png'],
        ]);

        $cluster = HasilCluster::create([
            'id_analisis' => $analisis->id_analisis,
            'id_kecamatan' => $kecamatan->id_kecamatan,
            'label_cluster' => 0,
            'interpretasi' => 'Sentra Kuliner Unggulan',
            'sektor_top1' => 'Makanan',
            'sektor_top2' => 'Jasa',
            'sektor_bottom1' => 'Fashion',
            'sektor_bottom2' => 'Lainnya',
            'flag_imputasi' => 'OK',
        ]);

        $centroid = Centroid::create([
            'id_analisis' => $analisis->id_analisis,
            'label_cluster' => 0,
            'interpretasi' => 'Sentra Kuliner Unggulan',
            'sektor_dominan' => ['Makanan', 'Jasa'],
            'sektor_rendah' => ['Fashion'],
            'ranking_sektor' => [],
            'nilai_fitur' => ['prop_makanan' => 0.584],
        ]);

        $umkm = Umkm::create([
            'id_kategori' => $kategori->id_kategori,
            'id_kecamatan' => $kecamatan->id_kecamatan,
            'nama_umkm' => 'Wajit Cililin Asli',
            'nama_pemilik' => 'Haji Salim',
            'alamat_lengkap' => 'Jl. Raya Lembang No. 123',
            'latitude' => -6.81700000,
            'longitude' => 107.61700000,
            'status_operasional' => 'aktif',
        ]);

        $user = User::create([
            'name' => 'Admin Test',
            'email' => 'admin_test_' . uniqid() . '@sigap-umkm.test',
            'password' => bcrypt('password123'),
        ]);

        $artikel = Artikel::create([
            'id_author' => $user->id,
            'penulis' => 'Admin Test',
            'title' => 'Potensi UMKM Unggulan KBB',
            'slug' => 'potensi-umkm-kbb-' . uniqid(),
            'excerpt' => 'Ringkasan artikel potensi UMKM',
            'content' => 'Konten lengkap mengenai pemetaan UMKM di KBB.',
            'is_published' => true,
            'published_at' => now(),
        ]);

        return compact('kategori', 'kecamatan', 'analisis', 'cluster', 'centroid', 'umkm', 'artikel', 'user');
    }

    /** @test — API-01: GET /api/v1/kecamatan */
    public function test_api_01_get_kecamatan_list_returns_200(): void
    {
        $this->seedTestData();
        $response = $this->getJson('/api/v1/kecamatan');
        $response->assertStatus(200)
                 ->assertJsonStructure(['data' => [['id_kecamatan', 'nama_kecamatan']]]);
    }

    /** @test — API-02: GET /api/v1/kecamatan/{id} */
    public function test_api_02_get_kecamatan_detail_returns_200(): void
    {
        $data = $this->seedTestData();
        $response = $this->getJson('/api/v1/kecamatan/' . $data['kecamatan']->id_kecamatan);
        $response->assertStatus(200)
                 ->assertJsonPath('data.nama_kecamatan', $data['kecamatan']->nama_kecamatan);
    }

    /** @test — API-03: GET /api/v1/cluster */
    public function test_api_03_get_cluster_published_returns_200(): void
    {
        $this->seedTestData();
        $response = $this->getJson('/api/v1/cluster');
        $response->assertStatus(200)
                 ->assertJsonStructure(['data' => [['id_kecamatan', 'label_cluster', 'sektor_top1']]]);
    }

    /** @test — API-04: GET /api/v1/centroid */
    public function test_api_04_get_centroid_returns_200(): void
    {
        $this->seedTestData();
        $response = $this->getJson('/api/v1/centroid');
        $response->assertStatus(200)
                 ->assertJsonStructure(['data' => [['label_cluster', 'interpretasi']]]);
    }

    /** @test — API-05: GET /api/v1/analisis/latest */
    public function test_api_05_get_analisis_latest_returns_200(): void
    {
        $this->seedTestData();
        $response = $this->getJson('/api/v1/analisis/latest');
        $response->assertStatus(200)
                 ->assertJsonPath('data.k_optimal', 5)
                 ->assertJsonPath('data.status_job', 'selesai');
    }

    /** @test — API-06: GET /api/v1/umkm with filter */
    public function test_api_06_get_umkm_list_with_filter_returns_200(): void
    {
        $data = $this->seedTestData();
        $response = $this->getJson('/api/v1/umkm?kategori=' . $data['kategori']->id_kategori . '&kecamatan=' . $data['kecamatan']->id_kecamatan);
        $response->assertStatus(200)
                 ->assertJsonStructure(['data' => [['id_umkm', 'nama_umkm', 'latitude', 'longitude']]]);
    }

    /** @test — API-07: GET /api/v1/umkm/search */
    public function test_api_07_search_umkm_returns_matching_results(): void
    {
        $this->seedTestData();
        $response = $this->getJson('/api/v1/umkm/search?q=Wajit');
        $response->assertStatus(200)
                 ->assertJsonStructure(['data']);
    }

    /** @test — API-08: GET /api/v1/umkm/{id} invalid ID negative test */
    public function test_api_08_get_umkm_invalid_id_returns_404(): void
    {
        $response = $this->getJson('/api/v1/umkm/99999');
        $response->assertStatus(404);
    }

    /** @test — API-09: GET /api/v1/kategori-umkm */
    public function test_api_09_get_kategori_list_returns_200(): void
    {
        $this->seedTestData();
        $response = $this->getJson('/api/v1/kategori-umkm');
        $response->assertStatus(200)
                 ->assertJsonStructure(['data' => [['id_kategori', 'nama_kategori']]]);
    }

    /** @test — API-10: GET /api/v1/artikel */
    public function test_api_10_get_artikel_list_returns_200(): void
    {
        $this->seedTestData();
        $response = $this->getJson('/api/v1/artikel');
        $response->assertStatus(200)
                 ->assertJsonStructure(['data' => [['id_artikel', 'title', 'slug']]]);
    }

    /** @test — API-11: GET /api/v1/artikel/{slug} */
    public function test_api_11_get_artikel_by_slug_returns_200(): void
    {
        $data = $this->seedTestData();
        $response = $this->getJson('/api/v1/artikel/' . $data['artikel']->slug);
        $response->assertStatus(200)
                 ->assertJsonPath('data.slug', $data['artikel']->slug);
    }

    /** @test — API-12: GET /api/v1/statistik */
    public function test_api_12_get_statistik_returns_200(): void
    {
        $this->seedTestData();
        $response = $this->getJson('/api/v1/statistik');
        $response->assertStatus(200)
                 ->assertJsonStructure(['total_umkm_aktif', 'total_kecamatan', 'distribusi_kategori', 'distribusi_cluster']);
    }
}
