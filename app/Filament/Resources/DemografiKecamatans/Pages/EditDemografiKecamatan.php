<?php

namespace App\Filament\Resources\DemografiKecamatans\Pages;

use App\Filament\Resources\DemografiKecamatans\DemografiKecamatanResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDemografiKecamatan extends EditRecord
{
    protected static string $resource = DemografiKecamatanResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
