<?php

/**
 * =============================================================================
 * AggregationServiceTest.php
 * =============================================================================
 * PHPUnit — Pengujian Unit Modul Agregasi & Snapshot Data
 * Dokumen Referensi: BAB5_PENGUJIAN_SISTEM.md — Sub-bab 5.5.1
 * =============================================================================
 */

namespace Tests\Unit;

use App\Services\AggregationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class AggregationServiceTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @test
     * @group whitebox
     * @group aggregation
     */
    public function test_generate_snapshot_creates_valid_json_with_correct_structure(): void
    {
        // Act
        $service = new AggregationService();
        $snapshot = $service->generateSnapshot();

        // Assert
        $this->assertIsArray($snapshot);
        $this->assertArrayHasKey('tanggal_snapshot', $snapshot);
        $this->assertArrayHasKey('data', $snapshot);
        $this->assertGreaterThanOrEqual(1, count($snapshot['data']));

        $firstItem = $snapshot['data'][0];
        $this->assertArrayHasKey('id_kecamatan', $firstItem);
        $this->assertArrayHasKey('nama_kecamatan', $firstItem);
        $this->assertArrayHasKey('fitur', $firstItem);

        $fitur = $firstItem['fitur'];
        $this->assertArrayHasKey('jml_makanan', $fitur);
        $this->assertArrayHasKey('jml_kerajinan', $fitur);
        $this->assertArrayHasKey('jml_fashion', $fitur);
        $this->assertArrayHasKey('jml_jasa', $fitur);
        $this->assertArrayHasKey('jml_lainnya', $fitur);
        $this->assertArrayHasKey('kepadatan_penduduk', $fitur);
        $this->assertArrayHasKey('pertumbuhan_penduduk', $fitur);
        $this->assertArrayHasKey('jarak_ke_ibukota', $fitur);

        // Verifikasi file fisik snapshot tertulis di storage
        $snapshotFilePath = storage_path('app/ml/input/dataset_snapshot.json');
        $this->assertFileExists($snapshotFilePath);
    }
}
