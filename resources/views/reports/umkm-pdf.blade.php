<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Rekapitulasi Data UMKM Kabupaten Bandung Barat</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 1.2cm;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            font-size: 10pt;
            line-height: 1.3;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
        }

        .kop-surat {
            border-bottom: 3px double #00684A;
            padding-bottom: 8px;
            margin-bottom: 14px;
            text-align: center;
        }

        .kop-surat h3 {
            margin: 0;
            font-size: 10pt;
            text-transform: uppercase;
            color: #475569;
        }

        .kop-surat h1 {
            margin: 2px 0;
            font-size: 14pt;
            font-weight: 800;
            text-transform: uppercase;
            color: #00684A;
        }

        .kop-surat p {
            margin: 0;
            font-size: 8.5pt;
            color: #64748b;
        }

        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .report-title h2 {
            font-size: 12pt;
            margin: 0;
            color: #0f172a;
            text-transform: uppercase;
        }

        .meta-info {
            font-size: 8.5pt;
            color: #64748b;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
            margin-bottom: 20px;
        }

        .data-table th {
            background-color: #00684A;
            color: #ffffff;
            font-weight: bold;
            text-align: left;
            padding: 6px 8px;
            text-transform: uppercase;
            border: 1px solid #00684A;
        }

        .data-table td {
            padding: 5px 8px;
            border: 1px solid #cbd5e1;
        }

        .data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .status-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 7.5pt;
            text-align: center;
        }

        .status-aktif { background-color: #dcfce7; color: #166534; border: 1px solid #86efac; }
        .status-nonaktif { background-color: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }

        @media print {
            .no-print { display: none !important; }
        }

        .print-bar {
            background-color: #0f172a;
            color: #ffffff;
            padding: 8px 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 14px;
            border-radius: 4px;
        }

        .btn-print {
            background-color: #00684A;
            color: #ffffff;
            border: none;
            padding: 6px 14px;
            font-weight: bold;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
</head>
<body>

    @if(isset($autoPrint) && $autoPrint)
    <div class="no-print print-bar">
        <span>📄 Rekapitulasi Data UMKM Siap Cetak / Export PDF</span>
        <button onclick="window.print()" class="btn-print">🖨️ Cetak / Save PDF</button>
    </div>
    @endif

    <div class="kop-surat">
        <h3>Pemerintah Kabupaten Bandung Barat</h3>
        <h1>Dinas Koperasi dan Usaha Mikro Kecil Menengah</h1>
        <p>Komplek Perkantoran Pemkab Bandung Barat, Jl. Raya Padalarang - Cisarua Km. 2, Ngamprah</p>
    </div>

    <div class="report-header">
        <div class="report-title">
            <h2>Laporan Rekapitulasi Spasial Data UMKM</h2>
        </div>
        <div class="meta-info">
            Total Record: <strong>{{ $umkmList->count() }} Unit</strong> | Dicetak: {{ $generatedAt }}
        </div>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 20%;">Nama UMKM</th>
                <th style="width: 14%;">Pemilik</th>
                <th style="width: 13%;">Kategori</th>
                <th style="width: 14%;">Kecamatan</th>
                <th style="width: 20%;">Alamat</th>
                <th style="width: 8%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($umkmList as $index => $item)
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td><strong>{{ $item->nama_umkm }}</strong></td>
                <td>{{ $item->nama_pemilik ?? $item->pemilik ?? '-' }}</td>
                <td>{{ $item->kategori?->nama_kategori ?? '-' }}</td>
                <td>{{ $item->kecamatan?->nama_kecamatan ?? '-' }}</td>
                <td>{{ $item->alamat_lengkap ?? $item->alamat ?? '-' }}</td>
                <td style="text-align: center;">
                    <span class="status-badge {{ ($item->status_operasional ?? 'aktif') === 'aktif' ? 'status-aktif' : 'status-nonaktif' }}">
                        {{ strtoupper($item->status_operasional ?? 'AKTIF') }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align: center; color: #94a3b8;">Tidak ada data UMKM yang sesuai dengan filter.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

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
