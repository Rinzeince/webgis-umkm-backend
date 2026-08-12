<?php

namespace App\Filament\Resources\KategoriUmkms\Pages;

use App\Filament\Resources\KategoriUmkms\KategoriUmkmResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListKategoriUmkms extends ListRecords
{
    protected static string $resource = KategoriUmkmResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->visible(fn () => auth()->user()?->isAdmin()),
        ];
    }
}
