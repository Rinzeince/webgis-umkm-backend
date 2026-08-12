<?php

namespace App\Filament\Resources\Umkms;

use App\Filament\Resources\Umkms\Pages\CreateUmkm;
use App\Filament\Resources\Umkms\Pages\EditUmkm;
use App\Filament\Resources\Umkms\Pages\ListUmkms;
use App\Filament\Resources\Umkms\Schemas\UmkmForm;
use App\Filament\Resources\Umkms\Tables\UmkmsTable;
use App\Models\Umkm;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class UmkmResource extends Resource
{
    protected static ?string $model = Umkm::class;

    protected static ?string $modelLabel = 'Data UMKM';

    protected static ?string $pluralModelLabel = 'Data UMKM';

    protected static \UnitEnum|string|null $navigationGroup = 'UMKM';

    protected static ?string $navigationLabel = 'Data UMKM';

    protected static ?int $navigationSort = 1;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingStorefront;

    protected static ?string $recordTitleAttribute = 'nama_umkm';

    public static function canViewAny(): bool
    {
        return auth()->check();
    }

    public static function form(Schema $schema): Schema
    {
        return UmkmForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UmkmsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUmkms::route('/'),
            'create' => CreateUmkm::route('/create'),
            'edit' => EditUmkm::route('/{record}/edit'),
        ];
    }
}
