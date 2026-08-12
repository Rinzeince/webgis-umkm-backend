<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Analisis extends Model
{
    use HasFactory;

    protected $table = 'analisis';
    protected $primaryKey = 'id_analisis';
    public $timestamps = true;
    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    protected $fillable = [
        'k_optimal',
        'nilai_silhouette',
        'nilai_dbi',
        'dataset_snapshot',
        'scaler_params',
        'path_grafik',
        'model_params',
        'status_job',
        'is_published',
        'error_log',
    ];

    protected $casts = [
        'k_optimal' => 'integer',
        'nilai_silhouette' => 'decimal:4',
        'nilai_dbi' => 'decimal:4',
        'dataset_snapshot' => 'array',
        'scaler_params' => 'array',
        'path_grafik' => 'array',
        'model_params' => 'array',
        'is_published' => 'boolean',
    ];

    protected $attributes = [
        'status_job' => 'dalam_antrean',
    ];

    /**
     * Get the cluster results for this analysis.
     */
    public function hasilCluster(): HasMany
    {
        return $this->hasMany(HasilCluster::class, 'id_analisis', 'id_analisis');
    }

    /**
     * Get the centroids for this analysis.
     */
    public function centroids(): HasMany
    {
        return $this->hasMany(Centroid::class, 'id_analisis', 'id_analisis');
    }
}
