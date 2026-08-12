<?php

namespace App\Filament\Widgets;

use App\Models\Analisis;
use App\Models\HasilCluster;
use Filament\Widgets\ChartWidget;

class ClusterDistributionBarChart extends ChartWidget
{
    protected ?string $heading = 'Grafik Bar Distribusi Klaster K-Means';

    protected static ?int $sort = 3;

    protected ?string $maxHeight = '280px';

    protected function getData(): array
    {
        // Get published batch or latest completed
        $published = Analisis::where('is_published', true)->first()
            ?? Analisis::where('status_job', 'selesai')->latest('created_at')->first();

        if (!$published) {
            return [
                'datasets' => [
                    [
                        'label' => 'Jumlah Kecamatan',
                        'data' => [0, 0, 0],
                        'backgroundColor' => ['#ef4444', '#f59e0b', '#10b981'],
                    ],
                ],
                'labels' => ['Klaster Rendah (K1)', 'Klaster Sedang (K2)', 'Klaster Tinggi (K3)'],
            ];
        }

        $hasil = HasilCluster::where('id_analisis', $published->id_analisis)->get();

        $c0 = $hasil->where('label_cluster', 0)->count();
        $c1 = $hasil->where('label_cluster', 1)->count();
        $c2 = $hasil->where('label_cluster', 2)->count();

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Kecamatan',
                    'data' => [$c0, $c1, $c2],
                    'backgroundColor' => [
                        'rgba(239, 68, 68, 0.85)',   // Red for Low
                        'rgba(245, 158, 11, 0.85)',  // Amber for Medium
                        'rgba(16, 185, 129, 0.85)',  // Emerald for High
                    ],
                    'borderColor' => [
                        '#dc2626',
                        '#d97706',
                        '#059669',
                    ],
                    'borderWidth' => 2,
                    'borderRadius' => 6,
                ],
            ],
            'labels' => [
                'Klaster 0: Rendah',
                'Klaster 1: Sedang',
                'Klaster 2: Tinggi',
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
