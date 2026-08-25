<?php

namespace App\Filament\Pages;

use App\Models\Analisis;
use App\Models\Centroid;
use App\Models\DatasetAgregat;
use App\Models\HasilCluster;
use App\Services\ActivityLogger;
use App\Services\AnalysisService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Contracts\View\View;

class AnalisisDataPage extends Page
{
    protected string $view = 'filament.pages.analisis-data-page';

    protected static \UnitEnum|string|null $navigationGroup = 'Analisis';

    protected static ?string $navigationLabel = 'Analisis K-Means';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-pie';

    protected static ?string $title = 'Analisis & Klasterisasi Spasial K-Means';

    protected static ?int $navigationSort = 1;

    public ?int $selectedAnalisisId = null;

    public string $activeTab = 'visualisasi';

    /**
     * Allow access for Admin and Editor users.
     */
    public static function canAccess(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = auth()->user();

        return $user?->isAdmin() || $user?->isEditor();
    }

    public function mount(): void
    {
        // Clean up any old stuck jobs (older than 2 min)
        Analisis::whereIn('status_job', ['dalam_antrean', 'diproses'])
            ->where('created_at', '<', now()->subMinutes(2))
            ->each(function (Analisis $a) {
                $a->hasilCluster()->delete();
                $a->centroids()->delete();
                $a->delete();
            });

        // Default selection: published batch first, then latest completed
        $published = Analisis::where('is_published', true)->first();
        if ($published) {
            $this->selectedAnalisisId = $published->id_analisis;
        } else {
            $latest = Analisis::where('status_job', 'selesai')->latest('created_at')->first();
            $this->selectedAnalisisId = $latest?->id_analisis;
        }
    }

    /**
     * Custom Header Rendering: Title on Top, Actions Wrapped Below
     */
    public function getHeader(): ?View
    {
        return view('filament.pages.analisis-data-header', [
            'title' => $this->getTitle(),
            'actions' => $this->getCachedHeaderActions(),
        ]);
    }

    /**
     * Filament Header Actions: Export PDF, Export CSV (Admin & Editor)
     * Trigger Analysis, Delete Batch, Publish Batch (Admin Only)
     */
    protected function getHeaderActions(): array
    {
        return [
            // -- Export Laporan PDF Action (Admin & Editor) --
            Action::make('exportPdf')
                ->label('Export Laporan PDF')
                ->icon('heroicon-o-document-text')
                ->color('warning')
                ->visible(fn () => $this->selectedAnalisisId !== null)
                ->url(fn () => route('admin.export.analisis.pdf', ['id' => $this->selectedAnalisisId]))
                ->openUrlInNewTab(),

            // -- Export CSV / Excel Action (Admin & Editor) --
            Action::make('exportCsv')
                ->label('Export Data CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('info')
                ->visible(fn () => $this->selectedAnalisisId !== null)
                ->url(fn () => route('admin.export.analisis.csv', ['id' => $this->selectedAnalisisId]))
                ->openUrlInNewTab(),

            // -- Trigger Analysis Action with Confirmation (Admin Only) --
            Action::make('triggerAnalysis')
                ->label('Jalankan Analisis K-Means')
                ->icon('heroicon-o-play')
                ->color('primary')
                ->visible(fn () => auth()->user()?->isAdmin() ?? false)
                ->requiresConfirmation()
                ->modalHeading('Konfirmasi Jalankan Analisis K-Means')
                ->modalDescription('Apakah Anda yakin ingin menjalankan analisis K-Means baru? Proses ini akan mengambil snapshot dataset terkini dan menjalankan clustering ulang.')
                ->modalSubmitActionLabel('Ya, Jalankan Analisis')
                ->action(function (AnalysisService $analysisService) {
                    if (!auth()->user()?->isAdmin()) {
                        Notification::make()->title('Akses Ditolak')->body('Hanya Administrator yang dapat menjalankan analisis baru.')->danger()->send();
                        return;
                    }

                    $analisis = $analysisService->triggerAnalysis();

                    if ($analisis->status_job === 'selesai') {
                        $this->selectedAnalisisId = $analisis->id_analisis;

                        ActivityLogger::log(
                            action: 'ANALISIS',
                            description: "Memicu proses Analisis K-Means Clustering baru (Batch #{$analisis->id_analisis}, K={$analisis->k_optimal})",
                            subjectType: 'Analisis'
                        );

                        Notification::make()
                            ->title('Analisis K-Means Selesai')
                            ->body("Batch Analisis #{$analisis->id_analisis} (K={$analisis->k_optimal}) berhasil dijalankan!")
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Analisis Gagal')
                            ->body('Job analisis gagal: ' . ($analisis->error_log ?? 'Unknown'))
                            ->danger()
                            ->send();
                    }
                }),

            // -- Publish Batch Action with Confirmation (Admin Only) --
            Action::make('publishBatch')
                ->label('Publish ke API WebGIS')
                ->icon('heroicon-o-globe-alt')
                ->color('success')
                ->visible(fn () => (auth()->user()?->isAdmin() ?? false) && $this->selectedAnalisisId !== null)
                ->requiresConfirmation()
                ->modalHeading('Publish Batch Analisis ke API WebGIS')
                ->modalDescription(fn () => "Apakah Anda yakin ingin mempublish Batch #{$this->selectedAnalisisId} ke API WebGIS?")
                ->modalSubmitActionLabel('Ya, Publish ke API')
                ->action(function () {
                    if (!auth()->user()?->isAdmin()) {
                        Notification::make()->title('Akses Ditolak')->body('Hanya Administrator yang dapat mempublikasikan batch analisis.')->danger()->send();
                        return;
                    }

                    $analisis = Analisis::find($this->selectedAnalisisId);
                    if (!$analisis || $analisis->status_job !== 'selesai') {
                        Notification::make()
                            ->title('Gagal Publish')
                            ->body('Hanya batch analisis dengan status "selesai" yang dapat dipublish.')
                            ->danger()
                            ->send();
                        return;
                    }

                    // Unpublish all, then publish selected
                    Analisis::where('is_published', true)->update(['is_published' => false]);
                    $analisis->update(['is_published' => true]);

                    ActivityLogger::log(
                        action: 'ANALISIS',
                        description: "Mempublikasikan Batch Analisis #{$analisis->id_analisis} ke API WebGIS publik.",
                        subjectType: 'Analisis'
                    );

                    Notification::make()
                        ->title('Batch Published!')
                        ->body("Batch #{$analisis->id_analisis} telah dipublish ke API WebGIS!")
                        ->success()
                        ->send();
                }),

            // -- Delete Batch Action with Confirmation (Admin Only) --
            Action::make('deleteBatch')
                ->label('Hapus Batch')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->visible(fn () => (auth()->user()?->isAdmin() ?? false) && $this->selectedAnalisisId !== null)
                ->requiresConfirmation()
                ->modalHeading('Hapus Batch Analisis')
                ->modalDescription(fn () => "Apakah Anda yakin ingin menghapus Batch #{$this->selectedAnalisisId}?")
                ->modalSubmitActionLabel('Ya, Hapus Permanen')
                ->action(function () {
                    if (!auth()->user()?->isAdmin()) {
                        Notification::make()->title('Akses Ditolak')->body('Hanya Administrator yang dapat menghapus batch analisis.')->danger()->send();
                        return;
                    }

                    $analisis = Analisis::find($this->selectedAnalisisId);
                    if (!$analisis) {
                        return;
                    }

                    $deletedId = $analisis->id_analisis;
                    $analisis->hasilCluster()->delete();
                    $analisis->centroids()->delete();
                    $analisis->delete();

                    ActivityLogger::log(
                        action: 'DELETE',
                        description: "Menghapus Batch Analisis #{$deletedId} beserta seluruh hasil klastrenya.",
                        subjectType: 'Analisis'
                    );

                    // Select another batch after deletion
                    $published = Analisis::where('is_published', true)->first();
                    $next = $published ?? Analisis::where('status_job', 'selesai')->latest('created_at')->first();
                    $this->selectedAnalisisId = $next?->id_analisis;

                    Notification::make()
                        ->title('Batch Dihapus')
                        ->body("Batch #{$deletedId} telah dihapus permanen.")
                        ->success()
                        ->send();
                }),
        ];
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function getViewData(): array
    {
        $allBatches = Analisis::where('status_job', 'selesai')
            ->orderByDesc('created_at')
            ->get();

        $selectedAnalisis = $this->selectedAnalisisId
            ? Analisis::find($this->selectedAnalisisId)
            : null;

        if (!$selectedAnalisis) {
            $selectedAnalisis = Analisis::where('is_published', true)->first()
                ?? Analisis::where('status_job', 'selesai')->latest('created_at')->first();
            $this->selectedAnalisisId = $selectedAnalisis?->id_analisis;
        }

        $centroids = $selectedAnalisis
            ? Centroid::where('id_analisis', $selectedAnalisis->id_analisis)->orderBy('label_cluster')->get()
            : collect();

        $hasilClusters = $selectedAnalisis
            ? HasilCluster::where('id_analisis', $selectedAnalisis->id_analisis)
                ->with('kecamatan')
                ->orderBy('id_kecamatan')
                ->get()
            : collect();

        $perluAnalisis = DatasetAgregat::where('status_analisis', 'perlu_analisis')->exists();
        $publishedAnalisis = Analisis::where('is_published', true)->first();

        // Build chart URLs from path_grafik field.
        // In production (S3), path_grafik stores full S3 URLs.
        // In development (local), path_grafik stores relative paths → prepend /storage/analysis/.
        $graficUrls = [];
        if ($selectedAnalisis && !empty($selectedAnalisis->path_grafik)) {
            foreach (['elbow.png', 'silhouette.png', 'scatter_cluster.png'] as $idx => $gfx) {
                $stored = $selectedAnalisis->path_grafik[$idx] ?? null;
                if ($stored && str_starts_with($stored, 'http')) {
                    // Already a full URL (S3)
                    $graficUrls[$gfx] = $stored;
                } elseif ($stored) {
                    // Legacy relative path — make it a proper local URL
                    $graficUrls[$gfx] = '/storage/analysis/' . basename($stored);
                } else {
                    $graficUrls[$gfx] = '/storage/analysis/' . $gfx;
                }
            }
        } else {
            foreach (['elbow.png', 'silhouette.png', 'scatter_cluster.png'] as $gfx) {
                $graficUrls[$gfx] = '/storage/analysis/' . $gfx;
            }
        }

        return [
            'allBatches'        => $allBatches,
            'selectedAnalisis'  => $selectedAnalisis,
            'centroids'         => $centroids,
            'hasilClusters'     => $hasilClusters,
            'perluAnalisis'     => $perluAnalisis,
            'publishedAnalisis' => $publishedAnalisis,
            'graficUrls'        => $graficUrls,
        ];
    }
}
