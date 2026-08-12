<?php

namespace App\Filament\Resources\Kecamatans\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class KecamatanInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('nama_kecamatan')
                    ->label('Nama Kecamatan'),
                TextEntry::make('kode_kemendagri')
                    ->label('Kode Kemendagri')
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
