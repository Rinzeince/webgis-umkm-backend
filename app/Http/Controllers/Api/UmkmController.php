<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UmkmResource;
use App\Models\Umkm;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UmkmController extends Controller
{
    /**
     * GET /api/v1/umkm
     * List UMKM aktif with filters: kecamatan, kategori, search, per_page.
     * Always applies WHERE status_operasional = 'aktif'.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Umkm::with(['kecamatan', 'kategori'])
            ->where('status_operasional', 'aktif');

        // Filter by kecamatan
        if ($request->filled('kecamatan')) {
            $query->where('id_kecamatan', (int) $request->input('kecamatan'));
        }

        // Filter by kategori
        if ($request->filled('kategori')) {
            $query->where('id_kategori', (int) $request->input('kategori'));
        }

        // Search by nama_umkm or alamat_lengkap
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('nama_umkm', 'like', "%{$search}%")
                  ->orWhere('alamat_lengkap', 'like', "%{$search}%");
            });
        }

        $perPage = min((int) ($request->input('per_page', 15)), 100);

        return UmkmResource::collection($query->orderBy('id_umkm')->paginate($perPage));
    }

    /**
     * GET /api/v1/umkm/search
     * Fast search endpoint for map point autocomplete & instant lookup by name / similarity.
     * Query params: ?q=... (search keyword), ?limit=15 (max 50)
     */
    public function search(Request $request): JsonResponse
    {
        $keyword = trim($request->input('q', $request->input('search', '')));

        if (mb_strlen($keyword) < 2) {
            return response()->json([
                'query' => $keyword,
                'total' => 0,
                'data' => [],
            ]);
        }

        $limit = min((int) $request->input('limit', 15), 50);

        $results = Umkm::with(['kecamatan', 'kategori'])
            ->where('status_operasional', 'aktif')
            ->where(function ($q) use ($keyword) {
                $q->where('nama_umkm', 'like', "%{$keyword}%")
                  ->orWhere('alamat_lengkap', 'like', "%{$keyword}%");
            })
            ->orderBy('nama_umkm')
            ->limit($limit)
            ->get();

        return response()->json([
            'query' => $keyword,
            'total' => $results->count(),
            'data' => UmkmResource::collection($results),
        ]);
    }

    /**
     * GET /api/v1/umkm/{id}
     * Detail UMKM. Returns 404 if nonaktif.
     */
    public function show(int $id): UmkmResource|JsonResponse
    {
        $umkm = Umkm::with(['kecamatan', 'kategori'])
            ->where('id_umkm', $id)
            ->where('status_operasional', 'aktif')
            ->first();

        if (!$umkm) {
            return response()->json([
                'message' => 'UMKM tidak ditemukan atau tidak aktif.',
            ], 404);
        }

        return new UmkmResource($umkm);
    }
}
