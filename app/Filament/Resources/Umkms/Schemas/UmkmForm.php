<?php

namespace App\Filament\Resources\Umkms\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UmkmForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Dasar UMKM')
                    ->components([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('nama_umkm')
                                    ->label('Nama UMKM')
                                    ->required()
                                    ->maxLength(150),
                                TextInput::make('nama_pemilik')
                                    ->label('Nama Pemilik')
                                    ->placeholder('Hanya terlihat oleh Admin')
                                    ->maxLength(150),
                                Select::make('id_kecamatan')
                                    ->label('Kecamatan')
                                    ->relationship('kecamatan', 'nama_kecamatan')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                Select::make('id_kategori')
                                    ->label('Kategori UMKM')
                                    ->relationship('kategori', 'nama_kategori')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                Select::make('status_operasional')
                                    ->label('Status Operasional')
                                    ->options([
                                        'aktif' => 'Aktif (Dihitung Agregat & Tampil Publik)',
                                        'nonaktif' => 'Nonaktif (Dikecualikan dari Agregat & Publik)',
                                    ])
                                    ->default('aktif')
                                    ->required(),
                                TextInput::make('kontak')
                                    ->label('Kontak / No. Telp')
                                    ->maxLength(50),
                                TextInput::make('jam_operasional')
                                    ->label('Jam Operasional')
                                    ->placeholder('Misal: 08:00 - 17:00')
                                    ->maxLength(100),
                            ]),
                        Textarea::make('alamat_lengkap')
                            ->label('Alamat Lengkap')
                            ->rows(3),
                        FileUpload::make('foto_url')
                            ->label('Foto Usaha')
                            ->directory('umkm/foto')
                            ->image()
                            ->maxSize(2048),
                    ]),
                Section::make('Koordinat Lokasi (Spasial)')
                    ->components([
                        ViewField::make('map_picker')
                            ->label('Location Picker (Peta Spasial Leaflet)')
                            ->view('filament.forms.components.leaflet-map-picker'),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('latitude')
                                    ->label('Latitude')
                                    ->numeric()
                                    ->live(onBlur: true)
                                    ->placeholder('-6.81427310')
                                    ->required(),
                                TextInput::make('longitude')
                                    ->label('Longitude')
                                    ->numeric()
                                    ->live(onBlur: true)
                                    ->placeholder('107.61748100')
                                    ->required(),
                            ]),
                    ]),
            ]);
    }
}
