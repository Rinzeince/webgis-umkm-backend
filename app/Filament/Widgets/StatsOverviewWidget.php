<?php

namespace App\Filament\Widgets;

use App\Models\Artikel;
use App\Models\DatasetAgregat;
use App\Models\Kecamatan;
use App\Models\Umkm;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalUmkmAktif = Umkm::where('status_operasional', 'aktif')->count();
        $totalKecamatan = Kecamatan::count();
        $totalArtikel = Artikel::count();

        $perluAnalisis = DatasetAgregat::where('status_analisis', 'perlu_analisis')->exists();

        return [
            Stat::make('Total UMKM Aktif', number_format($totalUmkmAktif))
                ->description('UMKM berstatus aktif di WebGIS')
                ->descriptionIcon('heroicon-o-building-storefront')
                ->color('success'),

            Stat::make('Total Kecamatan', $totalKecamatan)
                ->description('Kecamatan Kab. Bandung Barat')
                ->descriptionIcon('heroicon-o-map-pin')
                ->color('info'),

            Stat::make('Total Artikel', $totalArtikel)
                ->description('Konten informasi & berita')
                ->descriptionIcon('heroicon-o-document-text')
                ->color('primary'),

            Stat::make('Status Dataset', $perluAnalisis ? 'Perlu Analisis' : 'Sudah Dianalisis')
                ->description($perluAnalisis ? 'Data operasional berubah' : 'Hasil clustering sinkron')
                ->descriptionIcon($perluAnalisis ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-check-circle')
                ->color($perluAnalisis ? 'warning' : 'success'),
        ];
    }
}
