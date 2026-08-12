<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HasilCluster extends Model
{
    use HasFactory;

    protected $table = 'hasil_cluster';
    protected $primaryKey = 'id_hasil';
    public $timestamps = false;

    protected $fillable = [
        'id_analisis',
        'id_kecamatan',
        'label_cluster',
        'interpretasi',
        'sektor_top1',
        'sektor_top2',
        'sektor_bottom1',
        'sektor_bottom2',
        'ranking_sektor_5',
        'flag_imputasi',
    ];

    protected $casts = [
        'label_cluster' => 'integer',
        'ranking_sektor_5' => 'array',
    ];

    protected $attributes = [
        'flag_imputasi' => 'OK',
    ];

    /**
     * Get the analysis that owns the cluster result.
     */
    public function analisis(): BelongsTo
    {
        return $this->belongsTo(Analisis::class, 'id_analisis', 'id_analisis');
    }

    /**
     * Get the kecamatan that owns the cluster result.
     */
    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(Kecamatan::class, 'id_kecamatan', 'id_kecamatan');
    }
}
