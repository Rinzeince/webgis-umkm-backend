<?php

namespace App\Http\Controllers;

use App\Models\Analisis;
use App\Models\Centroid;
use App\Models\HasilCluster;
use App\Models\KategoriUmkm;
use App\Models\Kecamatan;
use App\Models\Umkm;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    /**
     * Export Executive Report PDF for K-Means Analysis Batch
     */
    public function exportAnalisisPdf(int $id)
    {
        $analisis = Analisis::findOrFail($id);
        $centroids = Centroid::where('id_analisis', $analisis->id_analisis)->orderBy('label_cluster')->get();
        $hasilClusters = HasilCluster::where('id_analisis', $analisis->id_analisis)
            ->with('kecamatan')
            ->orderBy('id_kecamatan')
            ->get();

        ActivityLogger::log(
            action: 'EXPORT',
            description: "Mengekspor Laporan PDF Hasil Analisis K-Means Batch #{$analisis->id_analisis}",
            subjectType: 'Analisis'
        );

        $data = [
            'analisis' => $analisis,
            'centroids' => $centroids,
            'hasilClusters' => $hasilClusters,
            'totalUmkm' => Umkm::where('status_operasional', 'aktif')->count(),
            'totalKecamatan' => Kecamatan::count(),
            'generatedAt' => now()->translatedFormat('d F Y H:i:s'),
            'user' => auth()->user() ?? (object)['name' => 'Admin Administrator', 'role' => 'admin'],
        ];

        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            try {
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.analisis-pdf', $data);
                $pdf->setPaper('a4', 'portrait');
                return $pdf->download("Laporan_Analisis_KMeans_Batch_{$analisis->id_analisis}.pdf");
            } catch (\Throwable $e) {
                // Fallback to printable HTML if dompdf encounters font/rendering issue
            }
        }

        return view('reports.analisis-pdf', array_merge($data, ['autoPrint' => true]));
    }

    /**
     * Export CSV Spreadsheet for K-Means Analysis Batch
     */
    public function exportAnalisisCsv(int $id): StreamedResponse
    {
        $analisis = Analisis::findOrFail($id);
        $hasilClusters = HasilCluster::where('id_analisis', $analisis->id_analisis)
            ->with('kecamatan')
            ->orderBy('id_kecamatan')
            ->get();

        ActivityLogger::log(
            action: 'EXPORT',
            description: "Mengekspor Berkas CSV Hasil Analisis K-Means Batch #{$analisis->id_analisis}",
            subjectType: 'Analisis'
        );

        $fileName = "Data_Klaster_KMeans_Batch_{$analisis->id_analisis}_" . date('Ymd_His') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($analisis, $hasilClusters) {
            $file = fopen('php://output', 'w');
            // Add UTF-8 BOM for Excel compatibility
            fputs($file, "\xEF\xBB\xBF");

            // Meta header
            fputcsv($file, ['LAPORAN HASIL KLASTERISASI K-MEANS SPASIAL UMKM KABUPATEN BANDUNG BARAT']);
            fputcsv($file, ['ID Batch Analisis', $analisis->id_analisis]);
            fputcsv($file, ['K Optimal', $analisis->k_optimal]);
            fputcsv($file, ['Silhouette Score', number_format($analisis->nilai_silhouette ?? 0, 4)]);
            fputcsv($file, ['DBI Score', number_format($analisis->nilai_dbi ?? 0, 4)]);
            fputcsv($file, ['Tanggal Analisis', $analisis->created_at?->format('d/m/Y H:i:s')]);
            fputcsv($file, []);

            // Data table header
            fputcsv($file, ['No', 'ID Kecamatan', 'Kode Kemendagri', 'Nama Kecamatan', 'Label Cluster', 'Interpretasi Profil', 'Top 1 Sektor', 'Top 2 Sektor', 'Sektor Terendah']);

            foreach ($hasilClusters as $index => $item) {
                fputcsv($file, [
                    $index + 1,
                    $item->id_kecamatan,
                    $item->kecamatan?->kode_kemendagri ?? '-',
                    $item->kecamatan?->nama_kecamatan ?? '-',
                    "Klaster " . $item->label_cluster,
                    $item->interpretasi ?? '-',
                    $item->sektor_top1 ?? '-',
                    $item->sektor_top2 ?? '-',
                    $item->sektor_bottom1 ?? '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export CSV Spreadsheet for UMKM Data Records
     */
    public function exportUmkmCsv(Request $request): StreamedResponse
    {
        $query = Umkm::with(['kecamatan', 'kategori']);

        if ($request->filled('kecamatan_id')) {
            $query->where('id_kecamatan', $request->input('kecamatan_id'));
        }
        if ($request->filled('kategori_id')) {
            $query->where('id_kategori', $request->input('kategori_id'));
        }
        if ($request->filled('status')) {
            $query->where('status_operasional', $request->input('status'));
        }

        $umkmList = $query->orderBy('nama_umkm')->get();

        ActivityLogger::log(
            action: 'EXPORT',
            description: "Mengekspor Data UMKM ke format CSV ({$umkmList->count()} record)",
            subjectType: 'UMKM'
        );

        $fileName = "Rekapitulasi_Data_UMKM_KBB_" . date('Ymd_His') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($umkmList) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");

            fputcsv($file, ['REKAPITULASI DATA UMKM KABUPATEN BANDUNG BARAT']);
            fputcsv($file, ['Tanggal Cetak', date('d/m/Y H:i:s')]);
            fputcsv($file, ['Total Record', $umkmList->count()]);
            fputcsv($file, []);

            fputcsv($file, [
                'No',
                'ID UMKM',
                'Nama UMKM',
                'Pemilik / Pengelola',
                'Kategori Usaha',
                'Kecamatan',
                'Alamat Lengkap',
                'Latitude',
                'Longitude',
                'No Telepon',
                'Status Operasional',
                'Tanggal Terdaftar',
            ]);

            foreach ($umkmList as $index => $item) {
                fputcsv($file, [
                    $index + 1,
                    $item->id_umkm,
                    $item->nama_umkm,
                    $item->nama_pemilik ?? $item->pemilik ?? '-',
                    $item->kategori?->nama_kategori ?? '-',
                    $item->kecamatan?->nama_kecamatan ?? '-',
                    $item->alamat_lengkap ?? $item->alamat ?? '-',
                    $item->latitude,
                    $item->longitude,
                    $item->kontak ?? $item->no_telepon ?? '-',
                    ucfirst($item->status_operasional ?? 'aktif'),
                    $item->created_at?->format('d/m/Y H:i') ?? '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export PDF / Printable View for UMKM Data Records
     */
    public function exportUmkmPdf(Request $request)
    {
        $query = Umkm::with(['kecamatan', 'kategori']);

        if ($request->filled('kecamatan_id')) {
            $query->where('id_kecamatan', $request->input('kecamatan_id'));
        }
        if ($request->filled('kategori_id')) {
            $query->where('id_kategori', $request->input('kategori_id'));
        }
        if ($request->filled('status')) {
            $query->where('status_operasional', $request->input('status'));
        }

        $umkmList = $query->orderBy('nama_umkm')->get();

        ActivityLogger::log(
            action: 'EXPORT',
            description: "Mengekspor Laporan PDF Rekapitulasi Data UMKM ({$umkmList->count()} record)",
            subjectType: 'UMKM'
        );

        $data = [
            'umkmList' => $umkmList,
            'generatedAt' => now()->translatedFormat('d F Y H:i:s'),
            'user' => auth()->user() ?? (object)['name' => 'Admin Administrator', 'role' => 'admin'],
        ];

        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            try {
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.umkm-pdf', $data);
                $pdf->setPaper('a4', 'landscape');
                return $pdf->download("Rekapitulasi_UMKM_KBB_" . date('Ymd') . ".pdf");
            } catch (\Throwable $e) {
                // Fallback to printable HTML
            }
        }

        return view('reports.umkm-pdf', array_merge($data, ['autoPrint' => true]));
    }
}
