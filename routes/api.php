<?php

use App\Http\Controllers\Api\AnalisisController;
use App\Http\Controllers\Api\ArtikelController;
use App\Http\Controllers\Api\CentroidController;
use App\Http\Controllers\Api\ClusterController;
use App\Http\Controllers\Api\KategoriUmkmController;
use App\Http\Controllers\Api\KecamatanController;
use App\Http\Controllers\Api\StatistikController;
use App\Http\Controllers\Api\UmkmController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public REST API Routes (WebGIS Map Engine)
|--------------------------------------------------------------------------
|
| All routes are public (no authentication required) and prefixed with /api/v1.
| These endpoints are consumed by the React + Leaflet WebGIS frontend.
|
*/

Route::prefix('v1')->middleware('throttle:api')->group(function () {
    // Kecamatan
    Route::get('/kecamatan', [KecamatanController::class, 'index']);
    Route::get('/kecamatan/{id}', [KecamatanController::class, 'show']);

    // Analisis K-Means
    Route::get('/analisis/latest', [AnalisisController::class, 'latest']);

    // Cluster & Centroid
    Route::get('/cluster', [ClusterController::class, 'index']);
    Route::get('/centroid', [CentroidController::class, 'index']);

    // UMKM & Point Search (Protected with tighter search rate limiter)
    Route::get('/umkm/search', [UmkmController::class, 'search'])->middleware('throttle:search-limiter');
    Route::get('/umkm', [UmkmController::class, 'index']);
    Route::get('/umkm/{id}', [UmkmController::class, 'show']);

    // Kategori UMKM
    Route::get('/kategori-umkm', [KategoriUmkmController::class, 'index']);

    // Artikel
    Route::get('/artikel', [ArtikelController::class, 'index'])->name('api.artikel.index');
    Route::get('/artikel/{slug}', [ArtikelController::class, 'show'])->name('api.artikel.show');

    // Statistik
    Route::get('/statistik', [StatistikController::class, 'index']);
});
