<?php

namespace App\Filament\Resources\Kecamatans\Pages;

use App\Filament\Resources\Kecamatans\KecamatanResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewKecamatan extends ViewRecord
{
    protected static string $resource = KecamatanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->visible(fn () => auth()->user()?->isAdmin()),
        ];
    }
}
