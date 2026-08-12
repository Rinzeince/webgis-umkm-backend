<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Kecamatan extends Model
{
    use HasFactory;

    protected $table = 'kecamatan';
    protected $primaryKey = 'id_kecamatan';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'nama_kecamatan',
        'kode_kemendagri',
    ];

    /**
     * Get the UMKM records for this kecamatan.
     */
    public function umkm(): HasMany
    {
        return $this->hasMany(Umkm::class, 'id_kecamatan', 'id_kecamatan');
    }

    /**
     * Get the demographic records for this kecamatan.
     */
    public function demografiKecamatan(): HasMany
    {
        return $this->hasMany(DemografiKecamatan::class, 'id_kecamatan', 'id_kecamatan');
    }

    /**
     * Get the aggregate dataset record for this kecamatan.
     */
    public function datasetAgregat(): HasOne
    {
        return $this->hasOne(DatasetAgregat::class, 'id_kecamatan', 'id_kecamatan');
    }

    /**
     * Get the cluster results for this kecamatan.
     */
    public function hasilCluster(): HasMany
    {
        return $this->hasMany(HasilCluster::class, 'id_kecamatan', 'id_kecamatan');
    }

    /**
     * Get the cluster result for this kecamatan from the published analysis.
     * Falls back to the most recent completed analysis if none is published.
     */
    public function latestCluster(): HasOne
    {
        // Prefer the published analysis, fallback to latest completed
        $publishedId = Analisis::where('is_published', true)->value('id_analisis');

        if (!$publishedId) {
            $publishedId = Analisis::where('status_job', 'selesai')
                ->latest('created_at')
                ->value('id_analisis');
        }

        return $this->hasOne(HasilCluster::class, 'id_kecamatan', 'id_kecamatan')
            ->where('id_analisis', $publishedId ?? 0);
    }

    /**
     * Get the latest demografi record for this kecamatan (MAX tahun).
     */
    public function latestDemografi(): HasOne
    {
        return $this->hasOne(DemografiKecamatan::class, 'id_kecamatan', 'id_kecamatan')
            ->orderByDesc('tahun');
    }
}
