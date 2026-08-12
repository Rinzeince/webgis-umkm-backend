<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Umkm extends Model
{
    use HasFactory;

    protected $table = 'umkm';
    protected $primaryKey = 'id_umkm';

    protected $fillable = [
        'id_kecamatan',
        'id_kategori',
        'nama_umkm',
        'nama_pemilik',
        'latitude',
        'longitude',
        'alamat_lengkap',
        'foto_url',
        'kontak',
        'status_operasional',
        'jam_operasional',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    /**
     * Get the kecamatan that owns the UMKM.
     */
    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(Kecamatan::class, 'id_kecamatan', 'id_kecamatan');
    }

    /**
     * Get the category that owns the UMKM.
     */
    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriUmkm::class, 'id_kategori', 'id_kategori');
    }
}
