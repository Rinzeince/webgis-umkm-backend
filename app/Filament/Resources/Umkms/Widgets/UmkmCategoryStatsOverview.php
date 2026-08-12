<?php

namespace App\Filament\Resources\Umkms\Widgets;

use App\Models\KategoriUmkm;
use App\Models\Umkm;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UmkmCategoryStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $kategoriList = KategoriUmkm::withCount([
            'umkm as total_count',
            'umkm as aktif_count' => function ($query) {
                $query->where('status_operasional', 'aktif');
            },
        ])->get();

        $stats = [];

        foreach ($kategoriList as $kat) {
            $total = $kat->total_count;
            $aktif = $kat->aktif_count;
            $persen = $total > 0 ? round(($aktif / $total) * 100, 1) : 0;

            $stats[] = Stat::make($kat->nama_kategori, number_format($total) . ' UMKM')
                ->description("{$aktif} Aktif ({$persen}%)")
                ->descriptionIcon('heroicon-o-building-storefront')
                ->color($kat->nama_kategori === 'Makanan' ? 'success' : ($kat->nama_kategori === 'Fashion' ? 'danger' : 'info'));
        }

        // Add 6th Stat card: UMKM Non-Aktif
        $nonAktifCount = Umkm::where('status_operasional', 'nonaktif')->count();
        $totalUmkm = Umkm::count();
        $persenNonAktif = $totalUmkm > 0 ? round(($nonAktifCount / $totalUmkm) * 100, 1) : 0;

        $stats[] = Stat::make('UMKM Non-Aktif', number_format($nonAktifCount) . ' UMKM')
            ->description("{$nonAktifCount} Nonaktif ({$persenNonAktif}%)")
            ->descriptionIcon('heroicon-o-x-circle')
            ->color('warning');

        return $stats;
    }
}
