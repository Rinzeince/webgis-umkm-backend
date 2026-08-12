<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\KategoriUmkmResource;
use App\Models\KategoriUmkm;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class KategoriUmkmController extends Controller
{
    /**
     * GET /api/v1/kategori-umkm
     * List 5 kategori UMKM with marker colors.
     */
    public function index(): AnonymousResourceCollection
    {
        return KategoriUmkmResource::collection(
            KategoriUmkm::orderBy('id_kategori')->get()
        );
    }
}
