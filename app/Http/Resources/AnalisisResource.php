<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnalisisResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id_analisis' => $this->id_analisis,
            'k_optimal' => $this->k_optimal,
            'nilai_silhouette' => $this->nilai_silhouette !== null ? (float) $this->nilai_silhouette : null,
            'nilai_dbi' => $this->nilai_dbi !== null ? (float) $this->nilai_dbi : null,
            'model_params' => $this->model_params,
            'path_grafik' => $this->path_grafik,
            'status_job' => $this->status_job,
            'is_published' => (bool) $this->is_published,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
