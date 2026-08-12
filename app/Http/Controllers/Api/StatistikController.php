<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Analisis;
use App\Models\DatasetAgregat;
use App\Models\HasilCluster;
use App\Models\Umkm;
use Illuminate\Http\JsonResponse;

class StatistikController extends Controller
{
    /**
     * GET /api/v1/statistik
     * Summary: total active UMKM, distribution per category, cluster distribution, published analysis info.
     */
    public function index(): JsonResponse
    {
        // Total UMKM aktif
        $totalUmkmAktif = Umkm::where('status_operasional', 'aktif')->count();

        // Distribution per kategori from dataset_agregat
        $agregats = DatasetAgregat::all();
        $distribusiKategori = [
            'makanan' => $agregats->sum('jml_makanan'),
            'kerajinan' => $agregats->sum('jml_kerajinan'),
            'fashion' => $agregats->sum('jml_fashion'),
            'jasa' => $agregats->sum('jml_jasa'),
            'lainnya' => $agregats->sum('jml_lainnya'),
        ];

        // Published analysis (or latest completed as fallback)
        $publishedAnalisis = Analisis::where('is_published', true)->first()
            ?? Analisis::where('status_job', 'selesai')->latest('created_at')->first();

        // Cluster distribution from published analysis
        $distribusiCluster = [];
        if ($publishedAnalisis) {
            $distribusiCluster = HasilCluster::where('id_analisis', $publishedAnalisis->id_analisis)
                ->selectRaw('label_cluster, COUNT(*) as jumlah_kecamatan')
                ->groupBy('label_cluster')
                ->orderBy('label_cluster')
                ->get()
                ->map(fn ($row) => [
                    'label_cluster' => $row->label_cluster,
                    'jumlah_kecamatan' => (int) $row->jumlah_kecamatan,
                ])
                ->values();
        }

        return response()->json([
            'total_umkm_aktif' => $totalUmkmAktif,
            'total_kecamatan' => 16,
            'distribusi_kategori' => $distribusiKategori,
            'analisis_terakhir' => $publishedAnalisis ? [
                'id_analisis' => $publishedAnalisis->id_analisis,
                'k_optimal' => $publishedAnalisis->k_optimal,
                'nilai_silhouette' => $publishedAnalisis->nilai_silhouette !== null
                    ? (float) $publishedAnalisis->nilai_silhouette : null,
                'nilai_dbi' => $publishedAnalisis->nilai_dbi !== null
                    ? (float) $publishedAnalisis->nilai_dbi : null,
                'is_published' => (bool) $publishedAnalisis->is_published,
                'created_at' => $publishedAnalisis->created_at?->toIso8601String(),
            ] : null,
            'distribusi_cluster' => $distribusiCluster,
        ]);
    }
}
