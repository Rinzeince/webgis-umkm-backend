<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DatasetAgregat extends Model
{
    use HasFactory;

    protected $table = 'dataset_agregat';
    protected $primaryKey = 'id_agregat';
    public $timestamps = true;
    const UPDATED_AT = 'updated_at';
    const CREATED_AT = 'created_at';

    protected $fillable = [
        'id_kecamatan',
        'jml_makanan',
        'jml_kerajinan',
        'jml_fashion',
        'jml_jasa',
        'jml_lainnya',
        'status_analisis',
    ];

    protected $casts = [
        'jml_makanan' => 'integer',
        'jml_kerajinan' => 'integer',
        'jml_fashion' => 'integer',
        'jml_jasa' => 'integer',
        'jml_lainnya' => 'integer',
    ];

    protected $attributes = [
        'jml_makanan' => 0,
        'jml_kerajinan' => 0,
        'jml_fashion' => 0,
        'jml_jasa' => 0,
        'jml_lainnya' => 0,
        'status_analisis' => 'perlu_analisis',
    ];

    /**
     * Get the kecamatan that owns the dataset aggregate.
     */
    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(Kecamatan::class, 'id_kecamatan', 'id_kecamatan');
    }
}
