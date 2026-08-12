<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KecamatanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id_kecamatan' => $this->id_kecamatan,
            'nama_kecamatan' => $this->nama_kecamatan,
            'geojson_path' => $this->geojson_path,
            'cluster' => $this->whenLoaded('latestCluster', function () {
                return $this->latestCluster ? [
                    'label_cluster' => $this->latestCluster->label_cluster,
                    'interpretasi' => $this->latestCluster->interpretasi,
                    'sektor_top1' => $this->latestCluster->sektor_top1,
                    'sektor_top2' => $this->latestCluster->sektor_top2,
                    'sektor_bottom1' => $this->latestCluster->sektor_bottom1,
                ] : null;
            }),
            'agregat' => $this->whenLoaded('datasetAgregat', function () {
                return $this->datasetAgregat ? [
                    'jml_makanan' => $this->datasetAgregat->jml_makanan,
                    'jml_kerajinan' => $this->datasetAgregat->jml_kerajinan,
                    'jml_fashion' => $this->datasetAgregat->jml_fashion,
                    'jml_jasa' => $this->datasetAgregat->jml_jasa,
                    'jml_lainnya' => $this->datasetAgregat->jml_lainnya,
                    'total_umkm' => $this->datasetAgregat->jml_makanan
                        + $this->datasetAgregat->jml_kerajinan
                        + $this->datasetAgregat->jml_fashion
                        + $this->datasetAgregat->jml_jasa
                        + $this->datasetAgregat->jml_lainnya,
                ] : null;
            }),
            'demografi' => $this->whenLoaded('latestDemografi', function () {
                return $this->latestDemografi ? [
                    'tahun' => $this->latestDemografi->tahun,
                    'kepadatan_penduduk' => $this->latestDemografi->kepadatan_penduduk,
                    'pertumbuhan_penduduk' => $this->latestDemografi->pertumbuhan_penduduk,
                    'jarak_ke_ibukota' => $this->latestDemografi->jarak_ke_ibukota,
                ] : null;
            }),
        ];
    }
}
