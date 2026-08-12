<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AnalisisResource;
use App\Models\Analisis;
use Illuminate\Http\JsonResponse;

class AnalisisController extends Controller
{
    /**
     * GET /api/v1/analisis/latest
     * Metadata of the published analysis (or latest completed as fallback).
     */
    public function latest(): AnalisisResource|JsonResponse
    {
        $analisis = Analisis::where('is_published', true)->first();

        if (!$analisis) {
            $analisis = Analisis::where('status_job', 'selesai')
                ->latest('created_at')
                ->first();
        }

        if (!$analisis) {
            return response()->json([
                'message' => 'Belum ada hasil analisis.',
            ], 404);
        }

        return new AnalisisResource($analisis);
    }
}
