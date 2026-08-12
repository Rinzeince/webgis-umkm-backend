<?php

namespace App\Filament\Resources\Artikels\Pages;

use App\Filament\Resources\Artikels\ArtikelResource;
use Filament\Resources\Pages\CreateRecord;

class CreateArtikel extends CreateRecord
{
    protected static string $resource = ArtikelResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['id_author'] = auth()->id();
        if (empty($data['penulis'])) {
            $data['penulis'] = auth()->user()?->name ?? 'Admin';
        }

        return $data;
    }
}
