@php
    $getClusterColor = function ($clusterId) {
        return match ((int) $clusterId) {
            0 => 'danger',
            1 => 'warning',
            2 => 'success',
            3 => 'info',
            default => 'purple',
        };
    };

    $getSectorColor = function ($sectorName) {
        $name = is_string($sectorName) ? trim($sectorName) : '';
        if (str_contains($name, 'Makanan'))
            return 'success';
        if (str_contains($name, 'Kerajinan'))
            return 'warning';
        if (str_contains($name, 'Fashion'))
            return 'purple';
        if (str_contains($name, 'Jasa'))
            return 'info';
        return 'gray';
    };

    $c0Count = $hasilClusters->where('label_cluster', 0)->count();
    $c1Count = $hasilClusters->where('label_cluster', 1)->count();
    $c2Count = $hasilClusters->where('label_cluster', 2)->count();
@endphp

<x-filament-panels::page>
    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
        {{-- Header Control Bar --}}
        <x-filament::section>
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem;">
                    <div>
                        <h2 style="font-size: 1.25rem; font-weight: 800; letter-spacing: -0.025em;" class="text-gray-900 dark:text-white">
                            Peta Analisis & Klasterisasi Spasial K-Means
                        </h2>
                        <p style="font-size: 0.875rem; margin-top: 0.25rem;" class="text-gray-500 dark:text-gray-400">
                            Evaluasi model spasial SEMMA K-Means untuk pemetaan sektor UMKM Kabupaten Bandung Barat.
                        </p>
                    </div>

                    <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.75rem;">
                        {{-- Batch Historical Selector --}}
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <label for="batch_selector" style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; white-space: nowrap;" class="text-gray-500 dark:text-gray-400">
                                Batch Analisis:
                            </label>
                            <x-filament::input.wrapper>
                                <x-filament::input.select
                                    id="batch_selector"
                                    wire:model.live="selectedAnalisisId"
                                >
                                    @forelse ($allBatches as $batch)
                                        <option value="{{ $batch->id_analisis }}">
                                            Batch #{{ $batch->id_analisis }}
                                            ({{ $batch->created_at->translatedFormat('d M Y H:i') }})
                                            — K={{ $batch->k_optimal }}
                                            @if ($batch->is_published) ✅ PUBLISHED @endif
                                        </option>
                                    @empty
                                        <option value="">Belum Ada Batch Analisis</option>
                                    @endforelse
                                </x-filament::input.select>
                            </x-filament::input.wrapper>
                        </div>

                        {{-- Dataset Sync Badge --}}
                        @if ($perluAnalisis)
                            <x-filament::badge color="warning" icon="heroicon-o-exclamation-triangle" size="md">
                                Perlu Analisis Ulang
                            </x-filament::badge>
                        @else
                            <x-filament::badge color="success" icon="heroicon-o-check-circle" size="md">
                                Dataset Sinkron
                            </x-filament::badge>
                        @endif

                        {{-- Published Status Badge --}}
                        @if ($publishedAnalisis)
                            <x-filament::badge
                                color="{{ $selectedAnalisis && $selectedAnalisis->id_analisis === $publishedAnalisis->id_analisis ? 'success' : 'gray' }}"
                                icon="heroicon-o-globe-alt"
                                size="md"
                            >
                                @if ($selectedAnalisis && $selectedAnalisis->id_analisis === $publishedAnalisis->id_analisis)
                                    ✅ Batch Ini Aktif di API
                                @else
                                    API: Batch #{{ $publishedAnalisis->id_analisis }}
                                @endif
                            </x-filament::badge>
                        @else
                            <x-filament::badge color="danger" icon="heroicon-o-x-circle" size="md">
                                Belum Ada Batch Published
                            </x-filament::badge>
                        @endif
                    </div>
                </div>
            </div>
        </x-filament::section>

        {{-- 4 Stat Cards Fitur A (Model K-Means Summary Cards) --}}
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem;">
            {{-- Card 1: Status & Model K-Means --}}
            <x-filament::section>
                <div style="display: flex; flex-direction: column; justify-content: space-between; height: 100%;">
                    <div>
                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;" class="text-gray-500 dark:text-gray-400">
                                Model & Batch Analisis
                            </span>
                            @if ($selectedAnalisis?->is_published)
                                <x-filament::badge color="success" size="sm">Published</x-filament::badge>
                            @else
                                <x-filament::badge color="gray" size="sm">Batch #{{ $selectedAnalisis?->id_analisis ?? '-' }}</x-filament::badge>
                            @endif
                        </div>
                        <span style="font-size: 1.75rem; font-weight: 900; margin-top: 0.5rem; display: block;" class="text-emerald-600 dark:text-emerald-400">
                            K = {{ $selectedAnalisis?->k_optimal ?? 3 }} Klaster
                        </span>
                    </div>
                    <p style="font-size: 0.75rem; margin-top: 0.75rem; padding-top: 0.75rem; border-top: 1px solid rgba(156, 163, 175, 0.2);" class="text-gray-500 dark:text-gray-400">
                        Silhouette: <b>{{ $selectedAnalisis?->nilai_silhouette !== null ? number_format($selectedAnalisis->nilai_silhouette, 4) : '-' }}</b> | DBI: <b>{{ $selectedAnalisis?->nilai_dbi !== null ? number_format($selectedAnalisis->nilai_dbi, 4) : '-' }}</b>
                    </p>
                </div>
            </x-filament::section>

            {{-- Card 2: Klaster Tinggi (Sentra Utama) --}}
            <x-filament::section>
                <div style="display: flex; flex-direction: column; justify-content: space-between; height: 100%;">
                    <div>
                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;" class="text-gray-500 dark:text-gray-400">
                                Klaster Tinggi (Sentra)
                            </span>
                            <x-filament::badge color="success" size="sm">Tinggi</x-filament::badge>
                        </div>
                        <span style="font-size: 1.875rem; font-weight: 900; margin-top: 0.5rem; display: block;" class="text-emerald-600 dark:text-emerald-400">
                            {{ $c2Count }} Kecamatan
                        </span>
                    </div>
                    <p style="font-size: 0.75rem; margin-top: 0.75rem; padding-top: 0.75rem; border-top: 1px solid rgba(156, 163, 175, 0.2);" class="text-gray-500 dark:text-gray-400">
                        Wilayah pusat pertumbuhan & konsentrasi UMKM terbesar.
                    </p>
                </div>
            </x-filament::section>

            {{-- Card 3: Klaster Sedang --}}
            <x-filament::section>
                <div style="display: flex; flex-direction: column; justify-content: space-between; height: 100%;">
                    <div>
                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;" class="text-gray-500 dark:text-gray-400">
                                Klaster Sedang
                            </span>
                            <x-filament::badge color="warning" size="sm">Sedang</x-filament::badge>
                        </div>
                        <span style="font-size: 1.875rem; font-weight: 900; margin-top: 0.5rem; display: block;" class="text-amber-500 dark:text-amber-400">
                            {{ $c1Count }} Kecamatan
                        </span>
                    </div>
                    <p style="font-size: 0.75rem; margin-top: 0.75rem; padding-top: 0.75rem; border-top: 1px solid rgba(156, 163, 175, 0.2);" class="text-gray-500 dark:text-gray-400">
                        Wilayah dengan pertumbuhan UMKM skala menengah.
                    </p>
                </div>
            </x-filament::section>

            {{-- Card 4: Klaster Rendah (Pembinaan) --}}
            <x-filament::section>
                <div style="display: flex; flex-direction: column; justify-content: space-between; height: 100%;">
                    <div>
                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;" class="text-gray-500 dark:text-gray-400">
                                Klaster Rendah (Pembinaan)
                            </span>
                            <x-filament::badge color="danger" size="sm">Intervensi</x-filament::badge>
                        </div>
                        <span style="font-size: 1.875rem; font-weight: 900; margin-top: 0.5rem; display: block;" class="text-rose-600 dark:text-rose-400">
                            {{ $c0Count }} Kecamatan
                        </span>
                    </div>
                    <p style="font-size: 0.75rem; margin-top: 0.75rem; padding-top: 0.75rem; border-top: 1px solid rgba(156, 163, 175, 0.2);" class="text-gray-500 dark:text-gray-400">
                        Fokus lokasi prioritas program bantuan & pembinaan dinas.
                    </p>
                </div>
            </x-filament::section>
        </div>

        {{-- Native Filament Tabs Navigation --}}
        <x-filament::tabs>
            <x-filament::tabs.item
                :active="$activeTab === 'visualisasi'"
                wire:click="setTab('visualisasi')"
                icon="heroicon-o-photo"
            >
                Visualisasi Grafik Model
            </x-filament::tabs.item>

            <x-filament::tabs.item
                :active="$activeTab === 'centroid'"
                wire:click="setTab('centroid')"
                icon="heroicon-o-chart-bar"
            >
                Profil Centroid Klaster ({{ $centroids->count() }})
            </x-filament::tabs.item>

            <x-filament::tabs.item
                :active="$activeTab === 'hasil'"
                wire:click="setTab('hasil')"
                icon="heroicon-o-map"
            >
                Sebaran 16 Kecamatan ({{ $hasilClusters->count() }})
            </x-filament::tabs.item>

            <x-filament::tabs.item
                :active="$activeTab === 'snapshot'"
                wire:click="setTab('snapshot')"
                icon="heroicon-o-document-text"
            >
                Snapshot Dataset & Log
            </x-filament::tabs.item>
        </x-filament::tabs>

        {{-- TAB 1: Visualisasi Grafik Model (PNG Output) --}}
        @if ($activeTab === 'visualisasi')
            <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); gap: 1.5rem;">
                    {{-- Elbow Plot Card --}}
                    <x-filament::section>
                        <x-slot name="heading">1. Plot Elbow Method (Penentuan K Optimal)</x-slot>
                        <x-slot name="description">Grafik penurunan WCSS untuk menentukan sikut penurunan inertia.</x-slot>
                        <div style="border-radius: 0.75rem; overflow: hidden; padding: 0.5rem; margin-top: 0.75rem;" class="bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
                            <img src="{{ $graficUrls['elbow.png'] ?? '/storage/analysis/elbow.png' }}?v={{ time() }}" alt="Elbow Method Plot" style="width: 100%; height: auto; border-radius: 0.5rem; object-fit: contain;" />
                        </div>
                    </x-filament::section>

                    {{-- Silhouette Plot Card --}}
                    <x-filament::section>
                        <x-slot name="heading">2. Plot Silhouette Score per Nilai K</x-slot>
                        <x-slot name="description">Perbandingan skor koefisien Silhouette untuk mengonfirmasi pilihan K optimal.</x-slot>
                        <div style="border-radius: 0.75rem; overflow: hidden; padding: 0.5rem; margin-top: 0.75rem;" class="bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
                            <img src="{{ $graficUrls['silhouette.png'] ?? '/storage/analysis/silhouette.png' }}?v={{ time() }}" alt="Silhouette Score Plot" style="width: 100%; height: auto; border-radius: 0.5rem; object-fit: contain;" />
                        </div>
                    </x-filament::section>
                </div>

                {{-- Scatter PCA 2D Card --}}
                <x-filament::section>
                    <x-slot name="heading">3. Proyeksi Spasial Scatter Plot K-Means (PCA 2D)</x-slot>
                    <x-slot name="description">Sebaran 16 kecamatan Kabupaten Bandung Barat dalam ruang komponen utama PCA 2D lengkap dengan batas klaster.</x-slot>
                    <div style="border-radius: 0.75rem; overflow: hidden; padding: 0.75rem; margin-top: 0.75rem; display: flex; justify-content: center;" class="bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
                        <img src="{{ $graficUrls['scatter_cluster.png'] ?? '/storage/analysis/scatter_cluster.png' }}?v={{ time() }}" alt="Scatter PCA Plot" style="max-width: 900px; width: 100%; height: auto; border-radius: 0.5rem; object-fit: contain;" />
                    </div>
                </x-filament::section>
            </div>
        @endif

        {{-- TAB 2: Profil Centroid Klaster --}}
        @if ($activeTab === 'centroid')
            <x-filament::section>
                <x-slot name="heading">Pusat Klaster & Karakteristik Fitur (Centroid)</x-slot>
                <x-slot name="description">Profil persentase sektor UMKM serta statistik demografi spasial untuk setiap klaster.</x-slot>

                <div style="overflow-x: auto; margin-top: 1rem; border-radius: 0.75rem;" class="border border-gray-200 dark:border-gray-700">
                    <table style="width: 100%; text-align: left; border-collapse: collapse; font-size: 0.8125rem;">
                        <thead class="bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                            <tr>
                                <th style="padding: 0.75rem 1rem; white-space: nowrap; min-width: 110px; font-weight: 700; text-transform: uppercase; font-size: 0.75rem; border-bottom: 2px solid rgba(156, 163, 175, 0.3);">Label</th>
                                <th style="padding: 0.75rem 1rem; min-width: 240px; font-weight: 700; text-transform: uppercase; font-size: 0.75rem; border-bottom: 2px solid rgba(156, 163, 175, 0.3);">Interpretasi Sektor</th>
                                <th style="padding: 0.75rem 1rem; white-space: nowrap; min-width: 180px; font-weight: 700; text-transform: uppercase; font-size: 0.75rem; border-bottom: 2px solid rgba(156, 163, 175, 0.3);">Sektor Dominan</th>
                                <th style="padding: 0.75rem 1rem; white-space: nowrap; font-weight: 700; text-transform: uppercase; font-size: 0.75rem; border-bottom: 2px solid rgba(156, 163, 175, 0.3);">% Makanan</th>
                                <th style="padding: 0.75rem 1rem; white-space: nowrap; font-weight: 700; text-transform: uppercase; font-size: 0.75rem; border-bottom: 2px solid rgba(156, 163, 175, 0.3);">% Kerajinan</th>
                                <th style="padding: 0.75rem 1rem; white-space: nowrap; font-weight: 700; text-transform: uppercase; font-size: 0.75rem; border-bottom: 2px solid rgba(156, 163, 175, 0.3);">% Fashion</th>
                                <th style="padding: 0.75rem 1rem; white-space: nowrap; font-weight: 700; text-transform: uppercase; font-size: 0.75rem; border-bottom: 2px solid rgba(156, 163, 175, 0.3);">% Jasa</th>
                                <th style="padding: 0.75rem 1rem; white-space: nowrap; font-weight: 700; text-transform: uppercase; font-size: 0.75rem; border-bottom: 2px solid rgba(156, 163, 175, 0.3);">% Lainnya</th>
                                <th style="padding: 0.75rem 1rem; white-space: nowrap; font-weight: 700; text-transform: uppercase; font-size: 0.75rem; border-bottom: 2px solid rgba(156, 163, 175, 0.3);">Kepadatan (jiwa/km²)</th>
                                <th style="padding: 0.75rem 1rem; white-space: nowrap; font-weight: 700; text-transform: uppercase; font-size: 0.75rem; border-bottom: 2px solid rgba(156, 163, 175, 0.3);">Jarak Ngamprah (km)</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-800">
                            @forelse ($centroids as $c)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/60 transition-colors">
                                    <td style="padding: 0.75rem 1rem; white-space: nowrap; font-weight: 700;">
                                        <x-filament::badge :color="$getClusterColor($c->label_cluster)" size="md" style="white-space: nowrap;">
                                            Klaster {{ $c->label_cluster }}
                                        </x-filament::badge>
                                    </td>
                                    <td style="padding: 0.75rem 1rem; font-weight: 600;" class="text-gray-900 dark:text-white">
                                        {{ $c->interpretasi ?? '-' }}
                                    </td>
                                    <td style="padding: 0.75rem 1rem; white-space: nowrap;">
                                        @if (!empty($c->sektor_dominan))
                                            <div style="display: flex; flex-wrap: nowrap; gap: 0.375rem; align-items: center;">
                                                @foreach ($c->sektor_dominan as $dom)
                                                    <x-filament::badge :color="$getSectorColor($dom)" size="sm" style="white-space: nowrap;">
                                                        {{ $dom }}
                                                    </x-filament::badge>
                                                @endforeach
                                            </div>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td style="padding: 0.75rem 1rem; white-space: nowrap;">
                                        <x-filament::badge color="success" size="sm" style="white-space: nowrap;">
                                            {{ number_format($c->nilai_fitur['f_prop_makanan'] ?? 0, 1) }}%
                                        </x-filament::badge>
                                    </td>
                                    <td style="padding: 0.75rem 1rem; white-space: nowrap;">
                                        <x-filament::badge color="warning" size="sm" style="white-space: nowrap;">
                                            {{ number_format($c->nilai_fitur['f_prop_kerajinan'] ?? 0, 1) }}%
                                        </x-filament::badge>
                                    </td>
                                    <td style="padding: 0.75rem 1rem; white-space: nowrap;">
                                        <x-filament::badge color="purple" size="sm" style="white-space: nowrap;">
                                            {{ number_format($c->nilai_fitur['f_prop_fashion'] ?? 0, 1) }}%
                                        </x-filament::badge>
                                    </td>
                                    <td style="padding: 0.75rem 1rem; white-space: nowrap;">
                                        <x-filament::badge color="info" size="sm" style="white-space: nowrap;">
                                            {{ number_format($c->nilai_fitur['f_prop_jasa'] ?? 0, 1) }}%
                                        </x-filament::badge>
                                    </td>
                                    <td style="padding: 0.75rem 1rem; white-space: nowrap;">
                                        <x-filament::badge color="gray" size="sm" style="white-space: nowrap;">
                                            {{ number_format($c->nilai_fitur['f_prop_lainnya'] ?? 0, 1) }}%
                                        </x-filament::badge>
                                    </td>
                                    <td style="padding: 0.75rem 1rem; font-family: monospace; white-space: nowrap;" class="text-gray-900 dark:text-white">
                                        {{ number_format($c->nilai_fitur['f_kepadatan'] ?? 0, 0) }}
                                    </td>
                                    <td style="padding: 0.75rem 1rem; font-family: monospace; white-space: nowrap;" class="text-gray-900 dark:text-white">
                                        {{ number_format($c->nilai_fitur['f_jarak'] ?? 0, 1) }} km
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" style="padding: 1.5rem; text-align: center;" class="text-gray-400">Belum ada data centroid klaster.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-filament::section>
        @endif

        {{-- TAB 3: Hasil Klasterisasi 16 Kecamatan --}}
        @if ($activeTab === 'hasil')
            <x-filament::section>
                <x-slot name="heading">Hasil Klasterisasi 16 Kecamatan KBB</x-slot>
                <x-slot name="description">Penugasan klaster K-Means dan urutan sektor prioritas untuk tiap kecamatan.</x-slot>

                <div style="overflow-x: auto; margin-top: 1rem; border-radius: 0.75rem;" class="border border-gray-200 dark:border-gray-700">
                    <table style="width: 100%; text-align: left; border-collapse: collapse; font-size: 0.8125rem;">
                        <thead class="bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                            <tr>
                                <th style="padding: 0.75rem 1rem; font-weight: 700; text-transform: uppercase; font-size: 0.75rem; border-bottom: 2px solid rgba(156, 163, 175, 0.3); white-space: nowrap; min-width: 140px;">Kecamatan</th>
                                <th style="padding: 0.75rem 1rem; font-weight: 700; text-transform: uppercase; font-size: 0.75rem; border-bottom: 2px solid rgba(156, 163, 175, 0.3); white-space: nowrap; min-width: 110px;">Klaster</th>
                                <th style="padding: 0.75rem 1rem; font-weight: 700; text-transform: uppercase; font-size: 0.75rem; border-bottom: 2px solid rgba(156, 163, 175, 0.3); min-width: 250px;">Interpretasi Profil</th>
                                <th style="padding: 0.75rem 1rem; font-weight: 700; text-transform: uppercase; font-size: 0.75rem; border-bottom: 2px solid rgba(156, 163, 175, 0.3); white-space: nowrap; min-width: 120px;">Top 1 Sektor</th>
                                <th style="padding: 0.75rem 1rem; font-weight: 700; text-transform: uppercase; font-size: 0.75rem; border-bottom: 2px solid rgba(156, 163, 175, 0.3); white-space: nowrap; min-width: 120px;">Top 2 Sektor</th>
                                <th style="padding: 0.75rem 1rem; font-weight: 700; text-transform: uppercase; font-size: 0.75rem; border-bottom: 2px solid rgba(156, 163, 175, 0.3); white-space: nowrap; min-width: 130px;">Sektor Terendah</th>
                                <th style="padding: 0.75rem 1rem; font-weight: 700; text-transform: uppercase; font-size: 0.75rem; border-bottom: 2px solid rgba(156, 163, 175, 0.3); white-space: nowrap; min-width: 100px;">Flag Data</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-800">
                            @forelse ($hasilClusters as $hc)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/60 transition-colors">
                                    <td style="padding: 0.75rem 1rem; font-weight: 800; white-space: nowrap;" class="text-gray-900 dark:text-white">
                                        {{ $hc->kecamatan?->nama_kecamatan ?? "Kecamatan #{$hc->id_kecamatan}" }}
                                    </td>
                                    <td style="padding: 0.75rem 1rem; white-space: nowrap;">
                                        <x-filament::badge :color="$getClusterColor($hc->label_cluster)" size="md" style="white-space: nowrap;">
                                            Klaster {{ $hc->label_cluster }}
                                        </x-filament::badge>
                                    </td>
                                    <td style="padding: 0.75rem 1rem; font-weight: 600;" class="text-gray-800 dark:text-gray-200">
                                        {{ $hc->interpretasi ?? '-' }}
                                    </td>
                                    <td style="padding: 0.75rem 1rem; white-space: nowrap;">
                                        @if ($hc->sektor_top1)
                                            <x-filament::badge :color="$getSectorColor($hc->sektor_top1)" size="sm" style="white-space: nowrap;">
                                                {{ $hc->sektor_top1 }}
                                            </x-filament::badge>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td style="padding: 0.75rem 1rem; white-space: nowrap;">
                                        @if ($hc->sektor_top2)
                                            <x-filament::badge :color="$getSectorColor($hc->sektor_top2)" size="sm" style="white-space: nowrap;">
                                                {{ $hc->sektor_top2 }}
                                            </x-filament::badge>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td style="padding: 0.75rem 1rem; white-space: nowrap;">
                                        @if ($hc->sektor_bottom1)
                                            <x-filament::badge :color="$getSectorColor($hc->sektor_bottom1)" size="sm" style="white-space: nowrap;">
                                                {{ $hc->sektor_bottom1 }}
                                            </x-filament::badge>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td style="padding: 0.75rem 1rem; white-space: nowrap;">
                                        @if ($hc->flag_imputasi === 'OK')
                                            <x-filament::badge color="success" size="sm">OK</x-filament::badge>
                                        @else
                                            <x-filament::badge color="warning" size="sm">VALIDASI</x-filament::badge>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" style="padding: 1.5rem; text-align: center;" class="text-gray-400">Belum ada data hasil klastering.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-filament::section>
        @endif

        {{-- TAB 4: Snapshot Dataset & Log Teknis --}}
        @if ($activeTab === 'snapshot')
            <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                {{-- Technical Error Trace Card --}}
                <x-filament::section>
                    <x-slot name="heading">Log Eksekusi & Status Technical Trace</x-slot>
                    <div style="padding: 1rem; border-radius: 0.75rem; font-family: monospace; font-size: 0.75rem; overflow-x: auto;" class="bg-gray-900 text-gray-100">
                        @if ($selectedAnalisis?->error_log)
                            <pre style="color: #f87171; white-space: pre-wrap;">{{ $selectedAnalisis->error_log }}</pre>
                        @else
                            <span style="color: #34d399;">✅ Job selesai dengan sukses (Exit Code 0). Tidak ada error teknis.</span>
                        @endif
                    </div>
                </x-filament::section>

                {{-- JSON Snapshot Viewer Card --}}
                <x-filament::section>
                    <x-slot name="heading">JSON Snapshot Input (16 Kecamatan)</x-slot>
                    <x-slot name="description">Dataset snapshot yang digunakan sebagai input saat batch analisis ini dijalankan.</x-slot>
                    <div style="padding: 1rem; border-radius: 0.75rem; font-family: monospace; font-size: 0.75rem; overflow-x: auto; max-height: 450px;" class="bg-gray-900 text-gray-100">
                        <pre style="white-space: pre-wrap;">{{ json_encode($selectedAnalisis?->dataset_snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                </x-filament::section>
            </div>
        @endif
    </div>
</x-filament-panels::page>
