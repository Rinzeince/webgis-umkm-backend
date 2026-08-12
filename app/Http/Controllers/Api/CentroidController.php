<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CentroidResource;
use App\Models\Analisis;
use App\Models\Centroid;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CentroidController extends Controller
{
    /**
     * GET /api/v1/centroid
     * Centroid profiles per cluster from the published analysis.
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

        $centroids = Centroid::where('id_analisis', $analisis->id_analisis)
            ->orderBy('label_cluster')
            ->get();

        return CentroidResource::collection($centroids);
    }
}
