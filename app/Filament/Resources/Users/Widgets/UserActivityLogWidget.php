<?php

namespace App\Filament\Resources\Users\Widgets;

use App\Models\ActivityLog;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class UserActivityLogWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Riwayat Aktivitas User (User Activity Logs)';

    public function table(Table $table): Table
    {
        return $table
            ->query(function () {
                $query = ActivityLog::query()->latest('created_at');

                // Filter to own user logs if not super admin
                if (!auth()->user()?->isAdmin()) {
                    $query->where('user_id', auth()->id());
                }

                return $query;
            })
            ->columns([
                TextColumn::make('created_at')
                    ->label('Waktu Activity')
                    ->dateTime('d M Y H:i:s')
                    ->sortable(),
                TextColumn::make('user_name')
                    ->label('Nama User')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user_role')
                    ->label('Role')
                    ->badge()
                    ->color(fn (string $state): string => match (strtolower($state)) {
                        'admin' => 'danger',
                        'editor' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => strtoupper($state)),
                TextColumn::make('action')
                    ->label('Aksi (Action)')
                    ->badge()
                    ->color(fn (string $state): string => match (strtoupper($state)) {
                        'LOGIN' => 'success',
                        'LOGOUT' => 'warning',
                        'CREATE' => 'info',
                        'UPDATE' => 'primary',
                        'DELETE' => 'danger',
                        'ANALISIS' => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('subject_type')
                    ->label('Subjek')
                    ->badge()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('description')
                    ->label('Rincian Kegiatan / Aktivitas')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('action')
                    ->label('Filter Jenis Aksi')
                    ->options([
                        'LOGIN' => 'LOGIN',
                        'LOGOUT' => 'LOGOUT',
                        'CREATE' => 'CREATE',
                        'UPDATE' => 'UPDATE',
                        'DELETE' => 'DELETE',
                        'ANALISIS' => 'ANALISIS K-MEANS',
                    ]),
                SelectFilter::make('user_role')
                    ->label('Filter Role')
                    ->options([
                        'admin' => 'Admin',
                        'editor' => 'Editor',
                    ])
                    ->visible(fn () => auth()->user()?->isAdmin()),
            ])
            ->paginated([5, 10, 25]);
    }
}
