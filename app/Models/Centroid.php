<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Centroid extends Model
{
    use HasFactory;

    protected $table = 'centroid';
    protected $primaryKey = 'id_centroid';
    public $timestamps = false;

    protected $fillable = [
        'id_analisis',
        'label_cluster',
        'interpretasi',
        'sektor_dominan',
        'sektor_rendah',
        'ranking_sektor',
        'nilai_fitur',
    ];

    protected $casts = [
        'label_cluster' => 'integer',
        'sektor_dominan'  => 'array',
        'sektor_rendah'   => 'array',
        'ranking_sektor'  => 'array',
        'nilai_fitur' => 'array',
    ];

    /**
     * Get the analysis that owns the centroid.
     */
    public function analisis(): BelongsTo
    {
        return $this->belongsTo(Analisis::class, 'id_analisis', 'id_analisis');
    }
}
