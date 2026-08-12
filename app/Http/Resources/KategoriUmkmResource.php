<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KategoriUmkmResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id_kategori' => $this->id_kategori,
            'nama_kategori' => $this->nama_kategori,
            'warna_marker' => $this->warna_marker,
        ];
    }
}
