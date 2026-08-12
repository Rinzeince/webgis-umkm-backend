<?php

namespace App\Filament\Resources\DemografiKecamatans\Pages;

use App\Filament\Resources\DemografiKecamatans\DemografiKecamatanResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDemografiKecamatan extends CreateRecord
{
    protected static string $resource = DemografiKecamatanResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
