<?php

namespace App\Filament\Resources\Artikels\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ArtikelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Konten Artikel')
                    ->components([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('title')
                                    ->label('Judul Artikel')
                                    ->required()
                                    ->maxLength(150)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (string $operation, $state, callable $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null),
                                TextInput::make('slug')
                                    ->label('Slug URL')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(170),
                                TextInput::make('penulis')
                                    ->label('Penulis / Author')
                                    ->default(fn () => auth()->user()?->name ?? 'Admin')
                                    ->required()
                                    ->maxLength(100),
                            ]),
                        Textarea::make('excerpt')
                            ->label('Ringkasan (Excerpt)')
                            ->rows(2)
                            ->maxLength(255),
                        RichEditor::make('content')
                            ->label('Isi Artikel')
                            ->required()
                            ->columnSpanFull(),
                    ]),
                Section::make('Publikasi & Media')
                    ->components([
                        Grid::make(2)
                            ->schema([
                                FileUpload::make('thumbnail_url')
                                    ->label('Gambar Sampul (Thumbnail)')
                                    ->directory('artikel/thumbnail')
                                    ->disk(config('filesystems.default') === 's3' ? 's3' : 'public')
                                    ->image()
                                    ->maxSize(2048),
                                Grid::make(1)
                                    ->schema([
                                        Toggle::make('is_published')
                                            ->label('Publikasikan Artikel')
                                            ->default(false)
                                            ->live()
                                            ->afterStateUpdated(function ($state, callable $set) {
                                                if ($state) {
                                                    $set('published_at', now()->toDateTimeString());
                                                }
                                            }),
                                        DateTimePicker::make('published_at')
                                            ->label('Waktu Publikasi'),
                                    ]),
                            ]),
                    ]),
            ]);
    }
}
