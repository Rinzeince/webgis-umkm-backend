<?php

namespace App\Filament\Resources\Umkms\Pages;

use App\Filament\Resources\Umkms\UmkmResource;
use App\Filament\Resources\Umkms\Widgets\UmkmCategoryStatsOverview;
use App\Filament\Resources\Umkms\Widgets\UmkmCategoryStatusDonutChart;
use App\Filament\Resources\Umkms\Widgets\UmkmGrowthLineChart;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUmkms extends ListRecords
{
    protected static string $resource = UmkmResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportPdf')
                ->label('Export Laporan PDF')
                ->icon('heroicon-o-document-text')
                ->color('warning')
                ->url(route('admin.export.umkm.pdf'))
                ->openUrlInNewTab(),

            Action::make('exportCsv')
                ->label('Export CSV / Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('info')
                ->url(route('admin.export.umkm.csv'))
                ->openUrlInNewTab(),

            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            UmkmCategoryStatsOverview::class,
            UmkmGrowthLineChart::class,
            UmkmCategoryStatusDonutChart::class,
        ];
    }
}
