<?php

namespace App\Filament\Resources\DemografiKecamatans\Pages;

use App\Filament\Resources\DemografiKecamatans\DemografiKecamatanResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDemografiKecamatans extends ListRecords
{
    protected static string $resource = DemografiKecamatanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->visible(fn () => auth()->user()?->isAdmin()),
        ];
    }
}
