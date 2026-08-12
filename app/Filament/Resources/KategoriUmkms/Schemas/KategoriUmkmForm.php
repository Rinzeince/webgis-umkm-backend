<?php

namespace App\Filament\Resources\KategoriUmkms\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class KategoriUmkmForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama_kategori')
                    ->label('Nama Kategori')
                    ->required()
                    ->maxLength(50),
                ColorPicker::make('warna_marker')
                    ->label('Warna Marker')
                    ->required()
                    ->default('#F97316'),
            ]);
    }
}
