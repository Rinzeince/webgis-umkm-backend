<?php

namespace App\Filament\Resources\Kecamatans;

use App\Filament\Resources\Kecamatans\Pages\CreateKecamatan;
use App\Filament\Resources\Kecamatans\Pages\EditKecamatan;
use App\Filament\Resources\Kecamatans\Pages\ListKecamatans;
use App\Filament\Resources\Kecamatans\Pages\ViewKecamatan;
use App\Filament\Resources\Kecamatans\Schemas\KecamatanForm;
use App\Filament\Resources\Kecamatans\Schemas\KecamatanInfolist;
use App\Filament\Resources\Kecamatans\Tables\KecamatansTable;
use App\Models\Kecamatan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class KecamatanResource extends Resource
{
    protected static ?string $model = Kecamatan::class;
    
    protected static ?string $modelLabel = 'Data Kecamatan';

    protected static ?string $pluralModelLabel = 'Data Kecamatan';

    protected static \UnitEnum|string|null $navigationGroup = 'Kecamatan';

    protected static ?string $navigationLabel = 'Data Kecamatan';

    protected static ?int $navigationSort = 1;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'nama_kecamatan';

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
        return KecamatanForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return KecamatanInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return KecamatansTable::configure($table);
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
            'index' => ListKecamatans::route('/'),
            'create' => CreateKecamatan::route('/create'),
            'view' => ViewKecamatan::route('/{record}'),
            'edit' => EditKecamatan::route('/{record}/edit'),
        ];
    }
}
