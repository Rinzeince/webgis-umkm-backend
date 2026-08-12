<?php

namespace App\Services;

use App\Models\DatasetAgregat;
use App\Models\DemografiKecamatan;
use App\Models\Kecamatan;
use Illuminate\Support\Facades\File;

class AggregationService
{
    /**
     * Generate dataset_snapshot JSON for Python ML input.
     * Always uses demografi data with MAX(tahun) per kecamatan.
     */
    public function generateSnapshot(): array
    {
        $kecamatans = Kecamatan::orderBy('id_kecamatan')->get();
        $dataList = [];

        foreach ($kecamatans as $kecamatan) {
            $demografi = DemografiKecamatan::where('id_kecamatan', $kecamatan->id_kecamatan)
                ->orderByDesc('tahun')
                ->first();

            $agregat = DatasetAgregat::where('id_kecamatan', $kecamatan->id_kecamatan)->first();

            $fitur = [
                'jml_makanan' => $agregat?->jml_makanan ?? 0,
                'jml_kerajinan' => $agregat?->jml_kerajinan ?? 0,
                'jml_fashion' => $agregat?->jml_fashion ?? 0,
                'jml_jasa' => $agregat?->jml_jasa ?? 0,
                'jml_lainnya' => $agregat?->jml_lainnya ?? 0,
                'kepadatan_penduduk' => $demografi?->kepadatan_penduduk !== null ? (int) $demografi->kepadatan_penduduk : null,
                'pertumbuhan_penduduk' => $demografi?->pertumbuhan_penduduk !== null ? (float) $demografi->pertumbuhan_penduduk : null,
                'jarak_ke_ibukota' => $demografi?->jarak_ke_ibukota !== null ? (float) $demografi->jarak_ke_ibukota : null,
            ];

            $dataList[] = [
                'id_kecamatan' => $kecamatan->id_kecamatan,
                'nama_kecamatan' => $kecamatan->nama_kecamatan,
                'tahun_demografi' => $demografi?->tahun,
                'fitur' => $fitur,
            ];
        }

        $snapshot = [
            'tanggal_snapshot' => now()->toDateString(),
            'data' => $dataList,
        ];

        $directory = storage_path('app/ml/input');
        File::ensureDirectoryExists($directory);

        $filePath = $directory . '/dataset_snapshot.json';
        File::put($filePath, json_encode($snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return $snapshot;
    }
}
