<?php

namespace App\Filament\Resources\Umkms\Widgets;

use App\Models\KategoriUmkm;
use App\Models\Kecamatan;
use Filament\Widgets\ChartWidget;

class UmkmCategoryStatusDonutChart extends ChartWidget
{
    protected ?string $heading = 'Grafik Donut Distribusi Kategori UMKM (Terfilter)';

    public ?string $filter = 'all';

    protected function getFilters(): ?array
    {
        $filters = [
            'all' => 'Semua Data (All)',
            'aktif' => 'Hanya Status Aktif',
            'nonaktif' => 'Hanya Status Non-Aktif',
        ];

        $kecamatanList = Kecamatan::orderBy('id_kecamatan')->get();
        foreach ($kecamatanList as $kec) {
            $filters['kec_' . $kec->id_kecamatan] = 'Kec. ' . $kec->nama_kecamatan;
        }

        return $filters;
    }

    protected function getData(): array
    {
        $activeFilter = $this->filter;

        $kategoriList = KategoriUmkm::withCount(['umkm' => function ($query) use ($activeFilter) {
            if ($activeFilter === 'aktif') {
                $query->where('status_operasional', 'aktif');
            } elseif ($activeFilter === 'nonaktif') {
                $query->where('status_operasional', 'nonaktif');
            } elseif (str_starts_with($activeFilter ?? '', 'kec_')) {
                $idKec = (int) str_replace('kec_', '', $activeFilter);
                $query->where('id_kecamatan', $idKec);
            }
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
