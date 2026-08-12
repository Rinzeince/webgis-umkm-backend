<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\KecamatanResource;
use App\Models\Kecamatan;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class KecamatanController extends Controller
{
    /**
     * GET /api/v1/kecamatan
     * List 16 kecamatan with latest cluster result summary.
     */
    public function index(): AnonymousResourceCollection
    {
        $kecamatans = Kecamatan::with(['latestCluster', 'datasetAgregat'])
            ->orderBy('id_kecamatan')
            ->get();

        return KecamatanResource::collection($kecamatans);
    }

    /**
     * GET /api/v1/kecamatan/{id}
     * Detail kecamatan with demografi, agregat UMKM, and cluster.
     */
    public function show(int $id): KecamatanResource
    {
        $kecamatan = Kecamatan::with(['latestCluster', 'datasetAgregat', 'latestDemografi'])
            ->where('id_kecamatan', $id)
            ->firstOrFail();

        return new KecamatanResource($kecamatan);
    }
}
