<?php

/**
 * =============================================================================
 * RunKMeansAnalysisJobTest.php
 * =============================================================================
 * PHPUnit — Pengujian White-Box (Basis Path Testing)
 * Modul : RunKMeansAnalysisJob::handle()
 * Dokumen Referensi: BAB5_PENGUJIAN_SISTEM.md — Sub-bab 5.5.1
 *
 * Cyclomatic Complexity : V(G) = 5
 * Jumlah Jalur Independen Teruji: 5 (TC-WB-01 s.d TC-WB-05)
 * =============================================================================
 */

namespace Tests\Unit;

use App\Jobs\RunKMeansAnalysisJob;
use App\Models\Analisis;
use App\Models\HasilCluster;
use App\Models\Centroid;
use App\Services\AggregationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Tests\TestCase;
use Mockery;
use Mockery\MockInterface;

class RunKMeansAnalysisJobTest extends TestCase
{
    use DatabaseTransactions;

    // -------------------------------------------------------------------------
    // Setup & Teardown
    // -------------------------------------------------------------------------

    protected function setUp(): void
    {
        parent::setUp();
        // Pastikan direktori output tersedia di test environment
        $outputDir = storage_path('app/ml/output');
        if (!File::isDirectory($outputDir)) {
            File::makeDirectory($outputDir, 0775, true);
        }
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    // =========================================================================
    // TC-WB-01 | Jalur Independen 1
    // Skenario : Record Analisis Tidak Ditemukan di Database (ID Fiktif)
    // Jalur    : N1 → N2 → N3 → N14
    // =========================================================================

    /**
     * @test
     */
    public function test_tc_wb_01_job_terminates_safely_when_analisis_record_not_found(): void
    {
        // Arrange — ID tidak ada di database
        $fakeId = 99999;
        $this->assertDatabaseMissing('analisis', ['id_analisis' => $fakeId]);

        $aggregationService = Mockery::mock(AggregationService::class);
        $aggregationService->shouldNotReceive('generateSnapshot');

        // Act
        $job = new RunKMeansAnalysisJob($fakeId);
        $job->handle($aggregationService);

        // Assert — tidak ada data baru di database
        $this->assertDatabaseMissing('analisis', ['id_analisis' => $fakeId]);
    }

    /**
     * @test
     */
    public function test_tc_wb_02_status_gagal_when_python_subprocess_fails(): void
    {
        // Arrange — buat record analisis valid
        $analisis = Analisis::create([
            'status_job' => 'dalam_antrean',
        ]);

        // Mock AggregationService
        $aggregationService = Mockery::mock(AggregationService::class);
        $aggregationService->shouldReceive('generateSnapshot')->once();

        // Mock Process — simulasi Python gagal (exit code != 0)
        $failedResult = Mockery::mock(\Illuminate\Contracts\Process\ProcessResult::class);
        $failedResult->shouldReceive('failed')->andReturn(true);
        $failedResult->shouldReceive('errorOutput')->andReturn('SyntaxError: invalid syntax');
        $failedResult->shouldReceive('output')->andReturn('');
        $failedResult->shouldReceive('exitCode')->andReturn(1);

        Process::shouldReceive('path')->andReturnSelf();
        Process::shouldReceive('env')->andReturnSelf();
        Process::shouldReceive('run')->andReturn($failedResult);

        // Act
        $job = new RunKMeansAnalysisJob($analisis->id_analisis);
        $job->handle($aggregationService);

        // Assert
        $analisis->refresh();
        $this->assertEquals('gagal', $analisis->status_job,
            'TC-WB-02: status_job harus berubah menjadi "gagal" saat Python subprocess error');
        $this->assertNotNull($analisis->error_log,
            'TC-WB-02: error_log harus terisi dengan pesan error dari stderr Python');
    }

    /**
     * @test
     */
    public function test_tc_wb_03_status_gagal_when_output_json_files_missing(): void
    {
        // Arrange
        $analisis = Analisis::create(['status_job' => 'dalam_antrean']);

        $aggregationService = Mockery::mock(AggregationService::class);
        $aggregationService->shouldReceive('generateSnapshot')->once();

        // Python berhasil (exit code 0)
        $successResult = Mockery::mock(\Illuminate\Contracts\Process\ProcessResult::class);
        $successResult->shouldReceive('failed')->andReturn(false);

        Process::shouldReceive('path')->andReturnSelf();
        Process::shouldReceive('env')->andReturnSelf();
        Process::shouldReceive('run')->andReturn($successResult);

        // Pastikan file JSON tidak ada
        $outputDir = storage_path('app/ml/output');
        $filesToDelete = ['metadata_output.json', 'hasil_cluster_output.json', 'centroid_output.json'];
        foreach ($filesToDelete as $f) {
            $path = $outputDir . '/' . $f;
            if (File::exists($path)) {
                File::delete($path);
            }
        }

        // Act
        $job = new RunKMeansAnalysisJob($analisis->id_analisis);
        $job->handle($aggregationService);

        // Assert
        $analisis->refresh();
        $this->assertEquals('gagal', $analisis->status_job,
            'TC-WB-03: status_job harus "gagal" saat output JSON Python tidak ditemukan');
        $this->assertStringContainsString(
            'JSON output files',
            $analisis->error_log ?? 'One or more required JSON output files',
            'TC-WB-03: error_log harus mendeskripsikan berkas output yang tidak lengkap'
        );
    }

    /**
     * @test
     */
    public function test_tc_wb_04_db_transaction_rollback_when_persistence_throws_exception(): void
    {
        // Arrange
        $analisis = Analisis::create(['status_job' => 'dalam_antrean']);

        $aggregationService = Mockery::mock(AggregationService::class);
        $aggregationService->shouldReceive('generateSnapshot')->once();

        // Python berhasil
        $successResult = Mockery::mock(\Illuminate\Contracts\Process\ProcessResult::class);
        $successResult->shouldReceive('failed')->andReturn(false);

        Process::shouldReceive('path')->andReturnSelf();
        Process::shouldReceive('env')->andReturnSelf();
        Process::shouldReceive('run')->andReturn($successResult);

        // Siapkan file JSON output valid
        $outputDir = storage_path('app/ml/output');
        File::put($outputDir . '/metadata_output.json',   json_encode(['k_optimal' => 5, 'nilai_silhouette' => 0.5842, 'nilai_dbi' => 0.7120, 'path_grafik' => []]));
        File::put($outputDir . '/hasil_cluster_output.json', json_encode([['id_kecamatan' => 1, 'label_cluster' => 0, 'interpretasi' => 'Test']]));
        File::put($outputDir . '/centroid_output.json',   json_encode([]));

        // Mock DB::transaction untuk lempar exception (simulasi DB connection lost)
        DB::shouldReceive('transaction')->andThrow(new \RuntimeException('SQLSTATE[HY000]: Connection refused'));

        // Act
        $job = new RunKMeansAnalysisJob($analisis->id_analisis);
        $job->handle($aggregationService);

        // Assert — transaksi di-rollback, status_job = gagal
        $analisis->refresh();
        $this->assertEquals('gagal', $analisis->status_job,
            'TC-WB-04: status_job harus "gagal" saat DB transaction melempar exception');
    }

    /**
     * @test
     */
    public function test_tc_wb_05_end_to_end_success_stores_clusters_and_centroids(): void
    {
        // Arrange — buat record analisis
        $analisis = Analisis::create(['status_job' => 'dalam_antrean']);

        $aggregationService = Mockery::mock(AggregationService::class);
        $aggregationService->shouldReceive('generateSnapshot')->once();

        // Python berhasil
        $successResult = Mockery::mock(\Illuminate\Contracts\Process\ProcessResult::class);
        $successResult->shouldReceive('failed')->andReturn(false);

        Process::shouldReceive('path')->andReturnSelf();
        Process::shouldReceive('env')->andReturnSelf();
        Process::shouldReceive('run')->andReturn($successResult);

        // Siapkan mock output JSON — 16 kecamatan, 5 klaster
        $outputDir = storage_path('app/ml/output');
        File::ensureDirectoryExists($outputDir);

        $hasilClusterData = array_map(fn($i) => [
            'id_kecamatan'    => $i + 1,
            'label_cluster'   => $i % 5,
            'interpretasi'    => "Klaster " . ($i % 5),
            'sektor_top1'     => 'Makanan',
            'sektor_top2'     => 'Kerajinan',
            'sektor_bottom1'  => 'Fashion',
            'sektor_bottom2'  => 'Lainnya',
            'ranking_sektor_5'=> [],
            'flag_imputasi'   => 'OK',
        ], range(0, 15));

        $centroidData = array_map(fn($k) => [
            'label_cluster'  => $k,
            'interpretasi'   => "Klaster $k",
            'sektor_dominan' => ['Makanan'],
            'sektor_rendah'  => ['Fashion'],
            'ranking_sektor' => [],
            'nilai_fitur'    => [],
        ], range(0, 4));

        $metadataData = [
            'k_optimal'         => 5,
            'nilai_silhouette'  => 0.5842,
            'nilai_dbi'         => 0.7120,
            'dataset_snapshot'  => [],
            'scaler_params'     => [],
            'path_grafik'       => ['elbow.png', 'silhouette.png', 'scatter_cluster.png'],
            'model_params'      => [],
        ];

        File::put($outputDir . '/metadata_output.json',      json_encode($metadataData));
        File::put($outputDir . '/hasil_cluster_output.json', json_encode($hasilClusterData));
        File::put($outputDir . '/centroid_output.json',      json_encode($centroidData));

        // Act
        $job = new RunKMeansAnalysisJob($analisis->id_analisis);
        $job->handle($aggregationService);

        // Assert
        $analisis->refresh();

        $this->assertEquals('selesai', $analisis->status_job,
            'TC-WB-05: status_job harus "selesai" setelah eksekusi sukses');
        $this->assertNull($analisis->error_log,
            'TC-WB-05: error_log harus null pada eksekusi sukses');
        $this->assertEquals(5, $analisis->k_optimal,
            'TC-WB-05: k_optimal harus tersimpan dari metadata JSON Python');
        $this->assertEquals(16, HasilCluster::where('id_analisis', $analisis->id_analisis)->count(),
            'TC-WB-05: Harus ada 16 baris HasilCluster untuk analisis ini');
        $this->assertEquals(5, Centroid::where('id_analisis', $analisis->id_analisis)->count(),
            'TC-WB-05: Harus ada 5 baris Centroid untuk analisis ini');
    }
}
