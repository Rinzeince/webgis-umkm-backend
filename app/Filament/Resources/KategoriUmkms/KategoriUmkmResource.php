<?php

namespace App\Filament\Resources\KategoriUmkms;

use App\Filament\Resources\KategoriUmkms\Pages\CreateKategoriUmkm;
use App\Filament\Resources\KategoriUmkms\Pages\EditKategoriUmkm;
use App\Filament\Resources\KategoriUmkms\Pages\ListKategoriUmkms;
use App\Filament\Resources\KategoriUmkms\Schemas\KategoriUmkmForm;
use App\Filament\Resources\KategoriUmkms\Tables\KategoriUmkmsTable;
use App\Models\KategoriUmkm;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class KategoriUmkmResource extends Resource
{
    protected static ?string $model = KategoriUmkm::class;

    protected static ?string $modelLabel = 'Kategori UMKM';

    protected static ?string $pluralModelLabel = 'Kategori UMKM';

    protected static \UnitEnum|string|null $navigationGroup = 'UMKM';

    protected static ?string $navigationLabel = 'Kategori UMKM';

    protected static ?int $navigationSort = 2;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static ?string $recordTitleAttribute = 'nama_kategori';

    public static function canViewAny(): bool
    {
        return auth()->check();
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return KategoriUmkmForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return KategoriUmkmsTable::configure($table);
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
            'index' => ListKategoriUmkms::route('/'),
            'create' => CreateKategoriUmkm::route('/create'),
            'edit' => EditKategoriUmkm::route('/{record}/edit'),
        ];
    }
}
