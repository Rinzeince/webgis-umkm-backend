<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $modelLabel = 'Pengguna';

    protected static ?string $pluralModelLabel = 'Manajemen User';

    protected static \UnitEnum|string|null $navigationGroup = 'Manajemen Pengguna';

    protected static ?string $navigationLabel = 'User & Hak Akses';

    protected static ?int $navigationSort = 1;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // Editor can only view their own account row in user management list
        if (!auth()->user()?->isAdmin()) {
            $query->where('id', auth()->id());
        }

        return $query;
    }

    public static function canViewAny(): bool
    {
        return true;
    }

    public static function canCreate(): bool
    {
        /** @var User|null $user */
        $user = auth()->user();

        return $user?->isAdmin() ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        /** @var User|null $user */
        $user = auth()->user();

        return $user && ($user->isAdmin() || $user->id === $record->id);
    }

    public static function canDelete(Model $record): bool
    {
        /** @var User|null $user */
        $user = auth()->user();

        return $user?->isAdmin() ?? false;
    }

    public static function canDeleteAny(): bool
    {
        /** @var User|null $user */
        $user = auth()->user();

        return $user?->isAdmin() ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
