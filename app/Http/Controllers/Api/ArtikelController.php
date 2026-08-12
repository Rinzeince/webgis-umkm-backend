<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ArtikelResource;
use App\Models\Artikel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ArtikelController extends Controller
{
    /**
     * GET /api/v1/artikel
     * List published articles with optional search (`q` or `search`) and pagination (`per_page`).
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = min((int) ($request->input('per_page', 10)), 100);

        $queryBuilder = Artikel::with('author')
            ->where('is_published', true);

        if ($search = $request->input('q') ?? $request->input('search')) {
            $queryBuilder->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('excerpt', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%")
                  ->orWhere('penulis', 'like', "%{$search}%");
            });
        }

        $artikels = $queryBuilder
            ->orderByDesc('published_at')
            ->paginate($perPage);

        return ArtikelResource::collection($artikels);
    }

    /**
     * GET /api/v1/artikel/{slug}
     * Detail article by slug or ID.
     */
    public function show(string $slug): ArtikelResource|JsonResponse
    {
        $artikel = Artikel::with('author')
            ->where('is_published', true)
            ->where(function ($query) use ($slug) {
                $query->where('slug', $slug)
                      ->orWhere('id_artikel', $slug);
            })
            ->first();

        if (!$artikel) {
            return response()->json([
                'message' => 'Artikel tidak ditemukan.',
            ], 404);
        }

        return new ArtikelResource($artikel);
    }
}
