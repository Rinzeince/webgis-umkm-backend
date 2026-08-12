<?php

namespace App\Filament\Resources\KategoriUmkms\Pages;

use App\Filament\Resources\KategoriUmkms\KategoriUmkmResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditKategoriUmkm extends EditRecord
{
    protected static string $resource = KategoriUmkmResource::class;

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
