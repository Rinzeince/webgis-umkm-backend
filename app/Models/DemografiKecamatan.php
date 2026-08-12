<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DemografiKecamatan extends Model
{
    use HasFactory;

    protected $table = 'demografi_kecamatan';
    protected $primaryKey = 'id_demografi';
    public $timestamps = true;

    protected $fillable = [
        'id_kecamatan',
        'tahun',
        'kepadatan_penduduk',
        'pertumbuhan_penduduk',
        'jarak_ke_ibukota',
    ];

    protected $casts = [
        'tahun' => 'integer',
        'kepadatan_penduduk' => 'integer',
        'pertumbuhan_penduduk' => 'decimal:2',
        'jarak_ke_ibukota' => 'decimal:2',
    ];

    /**
     * Get the kecamatan that owns the demographic record.
     */
    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(Kecamatan::class, 'id_kecamatan', 'id_kecamatan');
    }
}
