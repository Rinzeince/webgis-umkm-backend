<?php

use App\Http\Controllers\ApiDocController;
use App\Http\Controllers\ExportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Public API v1 Documentation
Route::get('/api/v1/docs', [ApiDocController::class, 'index'])->name('api.docs');

// Admin Export Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/admin/export/analisis/{id}/pdf', [ExportController::class, 'exportAnalisisPdf'])->name('admin.export.analisis.pdf');
    Route::get('/admin/export/analisis/{id}/csv', [ExportController::class, 'exportAnalisisCsv'])->name('admin.export.analisis.csv');
    Route::get('/admin/export/umkm/pdf', [ExportController::class, 'exportUmkmPdf'])->name('admin.export.umkm.pdf');
    Route::get('/admin/export/umkm/csv', [ExportController::class, 'exportUmkmCsv'])->name('admin.export.umkm.csv');
});
