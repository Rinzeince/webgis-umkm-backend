<?php

namespace App\Observers;

use App\Models\Artikel;
use App\Services\ActivityLogger;

class ArtikelObserver
{
    /**
     * Handle the Artikel "created" event.
     */
    public function created(Artikel $artikel): void
    {
        ActivityLogger::log(
            action: 'CREATE',
            description: "Membuat artikel baru: [{$artikel->title}]",
            subjectType: 'Artikel'
        );
    }

    /**
     * Handle the Artikel "updated" event.
     */
    public function updated(Artikel $artikel): void
    {
        ActivityLogger::log(
            action: 'UPDATE',
            description: "Memperbarui artikel: [{$artikel->title}]",
            subjectType: 'Artikel'
        );
    }

    /**
     * Handle the Artikel "deleted" event.
     */
    public function deleted(Artikel $artikel): void
    {
        ActivityLogger::log(
            action: 'DELETE',
            description: "Menghapus artikel: [{$artikel->title}]",
            subjectType: 'Artikel'
        );
    }
}
