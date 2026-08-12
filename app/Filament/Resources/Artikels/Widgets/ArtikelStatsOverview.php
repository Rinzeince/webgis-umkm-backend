<?php

namespace App\Filament\Resources\Artikels\Widgets;

use App\Models\Artikel;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ArtikelStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $total = Artikel::count();
        $published = Artikel::where('is_published', true)->count();
        $draft = Artikel::where('is_published', false)->count();

        return [
            Stat::make('Total Artikel', number_format($total) . ' Konten')
                ->description('Keseluruhan artikel & berita')
                ->descriptionIcon('heroicon-o-document-text')
                ->color('primary'),

            Stat::make('Artikel Diterbitkan', number_format($published) . ' Diterbitkan')
                ->description('Tampil di publik WebGIS')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make('Draf / Belum Publish', number_format($draft) . ' Draf')
                ->description('Belum dirilis ke publik')
                ->descriptionIcon('heroicon-o-pencil-square')
                ->color('warning'),
        ];
    }
}
