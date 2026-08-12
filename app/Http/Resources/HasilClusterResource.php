<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HasilClusterResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id_kecamatan' => $this->id_kecamatan,
            'nama_kecamatan' => $this->whenLoaded('kecamatan', fn () => $this->kecamatan?->nama_kecamatan),
            'label_cluster' => $this->label_cluster,
            'interpretasi' => $this->interpretasi,
            'sektor_top1' => $this->sektor_top1,
            'sektor_top2' => $this->sektor_top2,
            'sektor_bottom1' => $this->sektor_bottom1,
            'sektor_bottom2' => $this->sektor_bottom2,
            'ranking_sektor_5' => $this->ranking_sektor_5,
            'flag_imputasi' => $this->flag_imputasi,
        ];
    }
}
