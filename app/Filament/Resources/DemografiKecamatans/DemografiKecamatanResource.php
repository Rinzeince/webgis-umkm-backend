<?php

namespace App\Filament\Resources\DemografiKecamatans;

use App\Filament\Resources\DemografiKecamatans\Pages\CreateDemografiKecamatan;
use App\Filament\Resources\DemografiKecamatans\Pages\EditDemografiKecamatan;
use App\Filament\Resources\DemografiKecamatans\Pages\ListDemografiKecamatans;
use App\Filament\Resources\DemografiKecamatans\Schemas\DemografiKecamatanForm;
use App\Filament\Resources\DemografiKecamatans\Tables\DemografiKecamatansTable;
use App\Models\DemografiKecamatan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class DemografiKecamatanResource extends Resource
{
    protected static ?string $model = DemografiKecamatan::class;

    protected static ?string $modelLabel = 'Demografi Kecamatan';

    protected static ?string $pluralModelLabel = 'Demografi Kecamatan';

    protected static \UnitEnum|string|null $navigationGroup = 'Kecamatan';

    protected static ?string $navigationLabel = 'Demografi Kecamatan';

    protected static ?int $navigationSort = 2;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static ?string $recordTitleAttribute = 'tahun';

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
        return DemografiKecamatanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DemografiKecamatansTable::configure($table);
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
            'index' => ListDemografiKecamatans::route('/'),
            'create' => CreateDemografiKecamatan::route('/create'),
            'edit' => EditDemografiKecamatan::route('/{record}/edit'),
        ];
    }
}
