<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Hasil Analisis K-Means Batch #{{ $analisis->id_analisis }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 1.5cm;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            font-size: 10pt;
            line-height: 1.4;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
        }

        /* Kop Surat Header */
        .kop-surat {
            border-bottom: 3px double #00684A;
            padding-bottom: 12px;
            margin-bottom: 20px;
            text-align: center;
            position: relative;
        }

        .kop-surat h3 {
            margin: 0;
            font-size: 11pt;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #475569;
        }

        .kop-surat h1 {
            margin: 4px 0;
            font-size: 15pt;
            font-weight: 800;
            text-transform: uppercase;
            color: #00684A;
            letter-spacing: 0.5px;
        }

        .kop-surat p {
            margin: 2px 0 0;
            font-size: 9pt;
            color: #64748b;
        }

        /* Report Header */
        .report-title {
            text-align: center;
            margin-bottom: 20px;
        }

        .report-title h2 {
            font-size: 13pt;
            margin: 0 0 6px 0;
            color: #0f172a;
            text-transform: uppercase;
        }

        .badge-published {
            display: inline-block;
            background-color: #dcfce7;
            color: #15803d;
            border: 1px solid #86efac;
            font-size: 8pt;
            font-weight: bold;
            padding: 2px 10px;
            border-radius: 12px;
            text-transform: uppercase;
        }

        .badge-draft {
            display: inline-block;
            background-color: #f1f5f9;
            color: #475569;
            border: 1px solid #cbd5e1;
            font-size: 8pt;
            font-weight: bold;
            padding: 2px 10px;
            border-radius: 12px;
            text-transform: uppercase;
        }

        /* Meta Box Grid */
        .meta-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
        }

        .meta-grid td {
            padding: 8px 12px;
            font-size: 9pt;
            vertical-align: top;
            border-bottom: 1px solid #f1f5f9;
        }

        .meta-label {
            color: #64748b;
            font-weight: 600;
            width: 25%;
        }

        .meta-value {
            color: #0f172a;
            font-weight: 700;
        }

        /* Table Styling */
        .section-heading {
            font-size: 10.5pt;
            font-weight: bold;
            color: #00684A;
            margin: 18px 0 8px 0;
            border-left: 4px solid #00684A;
            padding-left: 8px;
            text-transform: uppercase;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 8.5pt;
        }

        .data-table th {
            background-color: #00684A;
            color: #ffffff;
            font-weight: 700;
            text-align: left;
            padding: 7px 9px;
            font-size: 8.5pt;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: 1px solid #00684A;
        }

        .data-table td {
            padding: 6px 9px;
            border: 1px solid #e2e8f0;
            color: #334155;
        }

        .data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }

        /* Cluster Badges */
        .c-badge {
            display: inline-block;
            font-weight: bold;
            font-size: 8pt;
            padding: 2px 8px;
            border-radius: 4px;
            text-align: center;
        }

        .c0 { background-color: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
        .c1 { background-color: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .c2 { background-color: #dcfce7; color: #166534; border: 1px solid #86efac; }
        .c3 { background-color: #dbeafe; color: #1e40af; border: 1px solid #93c5fd; }
        .c4 { background-color: #f3e8ff; color: #6b21a8; border: 1px solid #d8b4fe; }

        /* Sign-off Box */
        .sign-off-wrapper {
            margin-top: 30px;
            width: 100%;
            page-break-inside: avoid;
        }

        .sign-off-table {
            width: 100%;
            border-collapse: collapse;
        }

        .sign-off-table td {
            width: 50%;
            vertical-align: top;
            text-align: center;
            font-size: 9.5pt;
        }

        .sign-title {
            color: #64748b;
            margin-bottom: 60px;
        }

        .sign-name {
            font-weight: bold;
            color: #0f172a;
            text-decoration: underline;
        }

        .sign-role {
            font-size: 8.5pt;
            color: #64748b;
        }

        @media print {
            .no-print { display: none !important; }
        }

        .print-bar {
            background-color: #0f172a;
            color: #ffffff;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-radius: 6px;
        }

        .btn-print {
            background-color: #00684A;
            color: #ffffff;
            border: none;
            padding: 8px 18px;
            font-weight: bold;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
</head>
<body>

    @if(isset($autoPrint) && $autoPrint)
    <div class="no-print print-bar">
        <span>📄 Dokumen Laporan Hasil Analisis K-Means Siap Dicetak / PDF</span>
        <button onclick="window.print()" class="btn-print">🖨️ Cetak / Save PDF</button>
    </div>
    @endif

    <!-- KOP SURAT PEMKAB KBB -->
    <div class="kop-surat">
        <h3>Pemerintah Kabupaten Bandung Barat</h3>
        <h1>Dinas Koperasi dan Usaha Mikro Kecil Menengah</h1>
        <p>Komplek Perkantoran Pemkab Bandung Barat, Jl. Raya Padalarang - Cisarua Km. 2, Ngamprah</p>
    </div>

    <!-- JUDUL LAPORAN -->
    <div class="report-title">
        <h2>Laporan Hasil Analisis Spasial K-Means Clustering</h2>
        <div>
            @if($analisis->is_published)
                <span class="badge-published">✓ BATCH TAYANG DI WEBGIS PUBLIK</span>
            @else
                <span class="badge-draft">ARSIP EKSPLORASI (DRAFT)</span>
            @endif
        </div>
    </div>

    <!-- METADATA ANALISIS -->
    <table class="meta-grid">
        <tr>
            <td class="meta-label">ID Batch Analisis:</td>
            <td class="meta-value">#{{ $analisis->id_analisis }}</td>
            <td class="meta-label">Tanggal Eksekusi:</td>
            <td class="meta-value">{{ $analisis->created_at?->translatedFormat('d F Y H:i') ?? '-' }}</td>
        </tr>
        <tr>
            <td class="meta-label">Jumlah Cluster (K):</td>
            <td class="meta-value">{{ $analisis->k_optimal }} Klaster</td>
            <td class="meta-label">Silhouette Coefficient:</td>
            <td class="meta-value">{{ number_format($analisis->nilai_silhouette ?? 0, 4) }} (Evaluasi Baik)</td>
        </tr>
        <tr>
            <td class="meta-label">Davies-Bouldin Index (DBI):</td>
            <td class="meta-value">{{ number_format($analisis->nilai_dbi ?? 0, 4) }}</td>
            <td class="meta-label">Dicetak Oleh:</td>
            <td class="meta-value">{{ $user->name ?? 'Admin' }} ({{ $generatedAt }})</td>
        </tr>
    </table>

    <!-- SEKSI 1: CENTROID PROFIL KLASTER -->
    <div class="section-heading">1. Profil Pusat Klaster (Centroid Fitur)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 10%;">Klaster</th>
                <th style="width: 32%;">Interpretasi Sektor</th>
                <th style="width: 20%;">Kepadatan BPS</th>
                <th style="width: 18%;">Pertumbuhan BPS</th>
                <th style="width: 20%;">Jarak Ibukota</th>
            </tr>
        </thead>
        <tbody>
            @forelse($centroids as $c)
            @php
                $lbl = (int) $c->label_cluster;
                $badgeClass = match($lbl) {
                    0 => 'c0',
                    1 => 'c1',
                    2 => 'c2',
                    3 => 'c3',
                    default => 'c4',
                };
            @endphp
            <tr>
                <td style="text-align: center;">
                    <span class="c-badge {{ $badgeClass }}">
                        Klaster {{ $lbl }}
                    </span>
                </td>
                <td><strong>{{ $c->interpretasi ?? '-' }}</strong></td>
                <td>{{ number_format($c->nilai_fitur['f_kepadatan'] ?? 0, 0) }} jiwa/km²</td>
                <td>{{ number_format($c->nilai_fitur['f_pertumbuhan'] ?? 0, 2) }}%</td>
                <td>{{ number_format($c->nilai_fitur['f_jarak'] ?? 0, 1) }} km</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align: center; color: #94a3b8;">Belum ada data centroid.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- SEKSI 2: HASIL KLASTERISASI 16 KECAMATAN -->
    <div class="section-heading">2. Hasil Pemetaan Klaster 16 Kecamatan KBB</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 6%;">No</th>
                <th style="width: 16%;">Kode Kemendagri</th>
                <th style="width: 25%;">Nama Kecamatan</th>
                <th style="width: 14%;">Klaster</th>
                <th style="width: 39%;">Interpretasi Profil Sektor</th>
            </tr>
        </thead>
        <tbody>
            @forelse($hasilClusters as $index => $item)
            @php
                $lbl = (int) $item->label_cluster;
                $badgeClass = match($lbl) {
                    0 => 'c0',
                    1 => 'c1',
                    2 => 'c2',
                    3 => 'c3',
                    default => 'c4',
                };
            @endphp
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td><code>{{ $item->kecamatan?->kode_kemendagri ?? '-' }}</code></td>
                <td><strong>{{ $item->kecamatan?->nama_kecamatan ?? '-' }}</strong></td>
                <td style="text-align: center;">
                    <span class="c-badge {{ $badgeClass }}">
                        Klaster {{ $lbl }}
                    </span>
                </td>
                <td>{{ $item->interpretasi ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align: center; color: #94a3b8;">Data hasil klaster belum tersedia.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- SEKSI 3: TANDA TANGAN PENANGGUNG JAWAB -->
    <div class="sign-off-wrapper">
        <table class="sign-off-table">
            <tr>
                <td></td>
                <td>
                    <div>Ngamprah, {{ date('d F Y') }}</div>
                    <div class="sign-title">Operator / Pengelola Sistem WebGIS</div>
                    <br><br><br>
                    <div class="sign-name">{{ $user->name ?? 'Admin Administrator' }}</div>
                    <div class="sign-role">NIP. 19850412 201001 1 008</div>
                </td>
            </tr>
        </table>
    </div>

    @if(isset($autoPrint) && $autoPrint)
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                window.print();
            }, 500);
        });
    </script>
    @endif
</body>
</html>
