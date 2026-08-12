<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CentroidResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'label_cluster' => $this->label_cluster,
            'interpretasi' => $this->interpretasi,
            'sektor_dominan' => $this->sektor_dominan,
            'sektor_rendah' => $this->sektor_rendah,
            'ranking_sektor' => $this->ranking_sektor,
            'nilai_fitur' => $this->nilai_fitur,
        ];
    }
}
