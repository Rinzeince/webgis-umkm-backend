<?php

namespace App\Filament\Widgets;

use App\Models\KategoriUmkm;
use Filament\Widgets\ChartWidget;

class KategoriUmkmChartWidget extends ChartWidget
{
    protected ?string $heading = 'Distribusi UMKM Aktif per Kategori';

    protected static ?int $sort = 4;

    protected ?string $maxHeight = '280px';

    protected function getData(): array
    {
        $kategoriList = KategoriUmkm::withCount(['umkm' => function ($query) {
            $query->where('status_operasional', 'aktif');
        }])->get();

        $labels = $kategoriList->pluck('nama_kategori')->toArray();
        $data = $kategoriList->pluck('umkm_count')->toArray();
        $backgroundColor = $kategoriList->pluck('warna_marker')->map(fn ($color) => $color ?: '#00684A')->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah UMKM',
                    'data' => $data,
                    'backgroundColor' => $backgroundColor,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
