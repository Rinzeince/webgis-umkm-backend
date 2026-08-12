<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UmkmResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * IMPORTANT: nama_pemilik is NEVER exposed to the public API.
     */
    public function toArray(Request $request): array
    {
        return [
            'id_umkm' => $this->id_umkm,
            'nama_umkm' => $this->nama_umkm,
            'alamat_lengkap' => $this->alamat_lengkap,
            'latitude' => (float) $this->latitude,
            'longitude' => (float) $this->longitude,
            'foto_url' => $this->foto_url,
            'kontak' => $this->kontak,
            'status_operasional' => $this->status_operasional,
            'jam_operasional' => $this->jam_operasional,
            'kecamatan' => $this->whenLoaded('kecamatan', function () {
                return [
                    'id_kecamatan' => $this->kecamatan->id_kecamatan,
                    'nama_kecamatan' => $this->kecamatan->nama_kecamatan,
                ];
            }),
            'kategori' => $this->whenLoaded('kategori', function () {
                return [
                    'id_kategori' => $this->kategori->id_kategori,
                    'nama_kategori' => $this->kategori->nama_kategori,
                    'warna_marker' => $this->kategori->warna_marker,
                ];
            }),
        ];
    }
}
