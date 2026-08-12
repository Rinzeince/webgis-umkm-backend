<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriUmkm extends Model
{
    use HasFactory;

    protected $table = 'kategori_umkm';
    protected $primaryKey = 'id_kategori';
    public $timestamps = false;

    protected $fillable = [
        'nama_kategori',
        'warna_marker',
    ];

    /**
     * Get the UMKM records for this category.
     */
    public function umkm(): HasMany
    {
        return $this->hasMany(Umkm::class, 'id_kategori', 'id_kategori');
    }
}
