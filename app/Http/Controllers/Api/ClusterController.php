<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\HasilClusterResource;
use App\Models\Analisis;
use App\Models\HasilCluster;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ClusterController extends Controller
{
    /**
     * GET /api/v1/cluster
     * Cluster assignment for 16 kecamatan from the published analysis.
     */
    public function index(): AnonymousResourceCollection|JsonResponse
    {
        $analisis = Analisis::where('is_published', true)->first()
            ?? Analisis::where('status_job', 'selesai')->latest('created_at')->first();

        if (!$analisis) {
            return response()->json([
                'message' => 'Belum ada hasil analisis.',
            ], 404);
        }

        $clusters = HasilCluster::with('kecamatan')
            ->where('id_analisis', $analisis->id_analisis)
            ->orderBy('id_kecamatan')
            ->get();

        return HasilClusterResource::collection($clusters);
    }
}
