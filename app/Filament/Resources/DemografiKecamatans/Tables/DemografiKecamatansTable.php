<?php

namespace App\Filament\Resources\DemografiKecamatans\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DemografiKecamatansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kecamatan.nama_kecamatan')
                    ->label('Kecamatan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tahun')
                    ->label('Tahun')
                    ->sortable(),
                TextColumn::make('kepadatan_penduduk')
                    ->label('Kepadatan (jiwa/km²)')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('pertumbuhan_penduduk')
                    ->label('Pertumbuhan (%)')
                    ->numeric(2)
                    ->sortable(),
                TextColumn::make('jarak_ke_ibukota')
                    ->label('Jarak Ibukota (km)')
                    ->numeric(2)
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('id_kecamatan')
                    ->label('Kecamatan')
                    ->relationship('kecamatan', 'nama_kecamatan'),
                SelectFilter::make('tahun')
                    ->label('Tahun')
                    ->options(fn (): array => \App\Models\DemografiKecamatan::distinct()->pluck('tahun', 'tahun')->toArray()),
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn () => auth()->user()?->isAdmin()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()?->isAdmin()),
                ]),
            ]);
    }
}
