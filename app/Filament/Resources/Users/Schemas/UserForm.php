<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Akun Pengguna')
                    ->components([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nama Lengkap')
                                    ->required()
                                    ->maxLength(100),
                                TextInput::make('email')
                                    ->label('Alamat Email')
                                    ->email()
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(100),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Select::make('role')
                                    ->label('Peran / Hak Akses (Role)')
                                    ->options([
                                        'admin' => 'Super Admin (Akses Penuh Seluruh Sistem)',
                                        'editor' => 'Editor (Operator Data & Artikel)',
                                    ])
                                    ->default('editor')
                                    ->required()
                                    ->disabled(fn (?Model $record) => $record && auth()->id() === $record->id),

                                TextInput::make('password')
                                    ->label('Kata Sandi (Password)')
                                    ->password()
                                    ->revealable()
                                    ->required()
                                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                                    ->visible(fn (string $operation) => $operation === 'create'),
                            ]),
                    ]),

                // SECTION 1: Admin Force Reset Password (When Admin edits ANOTHER user)
                Section::make('Reset Kata Sandi Akun (Admin Force Reset)')
                    ->description('Admin dapat mereset kata sandi akun pengguna ini tanpa memerlukan kata sandi lama. Kosongkan jika tidak ingin mengubah kata sandi.')
                    ->visible(fn (string $operation, ?Model $record) => $operation === 'edit' && auth()->user()?->isAdmin() && $record && auth()->id() !== $record->id)
                    ->components([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('admin_new_password')
                                    ->label('Kata Sandi Baru (Opsional)')
                                    ->password()
                                    ->revealable()
                                    ->minLength(8)
                                    ->nullable(),
                                TextInput::make('admin_new_password_confirmation')
                                    ->label('Konfirmasi Kata Sandi Baru')
                                    ->password()
                                    ->revealable()
                                    ->nullable()
                                    ->same('admin_new_password')
                                    ->validationMessages([
                                        'same' => 'Konfirmasi kata sandi baru harus cocok dengan kata sandi baru.',
                                    ]),
                            ]),
                    ]),

                // SECTION 2: Self Password Change with Toggle (When user edits THEIR OWN account)
                Section::make('Ubah Kata Sandi Saya')
                    ->description('Aktifkan tombol di bawah ini untuk mengubah kata sandi akun Anda.')
                    ->visible(fn (string $operation, ?Model $record) => $operation === 'edit' && $record && auth()->id() === $record->id)
                    ->components([
                        Toggle::make('change_password_toggle')
                            ->label('Ubah Kata Sandi')
                            ->default(false)
                            ->live(),

                        Grid::make(1)
                            ->visible(fn (Get $get) => (bool) $get('change_password_toggle'))
                            ->schema([
                                TextInput::make('self_current_password')
                                    ->label('Kata Sandi Lama (Saat Ini)')
                                    ->password()
                                    ->revealable()
                                    ->requiredIf('change_password_toggle', true)
                                    ->rules([
                                        fn () => function (string $attribute, $value, \Closure $fail) {
                                            if (filled($value) && !Hash::check($value, auth()->user()->password)) {
                                                $fail('Kata sandi lama yang Anda masukkan tidak cocok dengan database.');
                                            }
                                        },
                                    ]),
                            ]),

                        Grid::make(2)
                            ->visible(fn (Get $get) => (bool) $get('change_password_toggle'))
                            ->schema([
                                TextInput::make('self_new_password')
                                    ->label('Kata Sandi Baru')
                                    ->password()
                                    ->revealable()
                                    ->requiredIf('change_password_toggle', true)
                                    ->minLength(8),
                                TextInput::make('self_new_password_confirmation')
                                    ->label('Konfirmasi Kata Sandi Baru')
                                    ->password()
                                    ->revealable()
                                    ->requiredIf('change_password_toggle', true)
                                    ->same('self_new_password')
                                    ->validationMessages([
                                        'same' => 'Konfirmasi kata sandi baru harus cocok dengan kata sandi baru.',
                                    ]),
                            ]),
                    ]),
            ]);
    }
}
