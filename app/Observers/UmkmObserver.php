<?php

namespace App\Observers;

use App\Models\DatasetAgregat;
use App\Models\KategoriUmkm;
use App\Models\Umkm;

class UmkmObserver
{
    /**
     * Handle the Umkm "created" event.
     */
    public function created(Umkm $umkm): void
    {
        if ($umkm->status_operasional !== 'aktif') {
            return;
        }

        $column = $this->getColumnForKategori($umkm->id_kategori);
        if ($column && $umkm->id_kecamatan > 0) {
            $agregat = DatasetAgregat::firstOrCreate(['id_kecamatan' => $umkm->id_kecamatan]);
            $agregat->increment($column);
            $agregat->update(['status_analisis' => 'perlu_analisis']);
        }
    }

    /**
     * Handle the Umkm "updated" event.
     */
    public function updated(Umkm $umkm): void
    {
        $oldKecamatan = (int) ($umkm->getOriginal('id_kecamatan') ?? $umkm->id_kecamatan);
        $newKecamatan = (int) $umkm->id_kecamatan;

        $oldKategori = (int) ($umkm->getOriginal('id_kategori') ?? $umkm->id_kategori);
        $newKategori = (int) $umkm->id_kategori;

        $oldStatus = (string) ($umkm->getOriginal('status_operasional') ?? $umkm->status_operasional);
        $newStatus = (string) $umkm->status_operasional;

        $wasActive = ($oldStatus === 'aktif');
        $isActive = ($newStatus === 'aktif');

        // Step 1: Decrement old record if it was previously active
        if ($wasActive) {
            $oldCol = $this->getColumnForKategori($oldKategori);
            if ($oldCol && $oldKecamatan > 0) {
                $oldAgregat = DatasetAgregat::where('id_kecamatan', $oldKecamatan)->first();
                if ($oldAgregat && $oldAgregat->{$oldCol} > 0) {
                    $oldAgregat->decrement($oldCol);
                    $oldAgregat->update(['status_analisis' => 'perlu_analisis']);
                }
            }
        }

        // Step 2: Increment new record if it is currently active
        if ($isActive) {
            $newCol = $this->getColumnForKategori($newKategori);
            if ($newCol && $newKecamatan > 0) {
                $newAgregat = DatasetAgregat::firstOrCreate(['id_kecamatan' => $newKecamatan]);
                $newAgregat->increment($newCol);
                $newAgregat->update(['status_analisis' => 'perlu_analisis']);
            }
        }
    }

    /**
     * Handle the Umkm "deleted" event.
     */
    public function deleted(Umkm $umkm): void
    {
        if ($umkm->status_operasional !== 'aktif') {
            return;
        }

        $column = $this->getColumnForKategori($umkm->id_kategori);
        if ($column && $umkm->id_kecamatan > 0) {
            $agregat = DatasetAgregat::where('id_kecamatan', $umkm->id_kecamatan)->first();
            if ($agregat && $agregat->{$column} > 0) {
                $agregat->decrement($column);
                $agregat->update(['status_analisis' => 'perlu_analisis']);
            }
        }
    }

    /**
     * Handle the Umkm "restored" event.
     */
    public function restored(Umkm $umkm): void
    {
        $this->created($umkm);
    }

    /**
     * Handle the Umkm "forceDeleted" event.
     */
    public function forceDeleted(Umkm $umkm): void
    {
        $this->deleted($umkm);
    }

    /**
     * Helper to map id_kategori to corresponding column in dataset_agregat.
     */
    protected function getColumnForKategori(?int $idKategori): ?string
    {
        if (!$idKategori) {
            return null;
        }

        $kategori = KategoriUmkm::find($idKategori);
        if (!$kategori) {
            return null;
        }

        $name = strtolower(trim($kategori->nama_kategori));

        return match ($name) {
            'makanan' => 'jml_makanan',
            'kerajinan' => 'jml_kerajinan',
            'fashion' => 'jml_fashion',
            'jasa' => 'jml_jasa',
            'lainnya' => 'jml_lainnya',
            default => 'jml_lainnya',
        };
    }
}
