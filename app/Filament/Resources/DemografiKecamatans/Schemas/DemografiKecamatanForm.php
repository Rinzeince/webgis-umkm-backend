<?php

namespace App\Filament\Resources\DemografiKecamatans\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DemografiKecamatanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Demografi & Spasial Kecamatan (BPS)')
                    ->components([
                        Grid::make(2)
                            ->schema([
                                Select::make('id_kecamatan')
                                    ->label('Kecamatan')
                                    ->relationship('kecamatan', 'nama_kecamatan')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                TextInput::make('tahun')
                                    ->label('Tahun Data BPS')
                                    ->numeric()
                                    ->required()
                                    ->minValue(2000)
                                    ->maxValue(2099)
                                    ->default(date('Y')),
                                TextInput::make('kepadatan_penduduk')
                                    ->label('Kepadatan Penduduk (jiwa/km²)')
                                    ->numeric()
                                    ->placeholder('Misal: 1500'),
                                TextInput::make('pertumbuhan_penduduk')
                                    ->label('Pertumbuhan Penduduk (%)')
                                    ->numeric()
                                    ->step(0.01)
                                    ->placeholder('Misal: 1.20'),
                                TextInput::make('jarak_ke_ibukota')
                                    ->label('Jarak ke Ibukota Ngamprah (km)')
                                    ->numeric()
                                    ->step(0.01)
                                    ->placeholder('Misal: 8.40'),
                            ]),
                    ]),
            ]);
    }
}
