<?php

namespace App\Filament\Resources\Kecamatans\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class KecamatanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama_kecamatan')
                    ->label('Nama Kecamatan')
                    ->required()
                    ->maxLength(50),
                TextInput::make('kode_kemendagri')
                    ->label('Kode Wilayah Kemendagri')
                    ->placeholder('Misal: 32.17.01')
                    ->maxLength(20),
            ]);
    }
}
