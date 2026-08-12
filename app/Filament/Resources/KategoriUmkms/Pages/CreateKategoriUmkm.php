<?php

namespace App\Filament\Resources\KategoriUmkms\Pages;

use App\Filament\Resources\KategoriUmkms\KategoriUmkmResource;
use Filament\Resources\Pages\CreateRecord;

class CreateKategoriUmkm extends CreateRecord
{
    protected static string $resource = KategoriUmkmResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
