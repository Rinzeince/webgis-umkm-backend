<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dokumentasi REST API v1.0 — SIGAP UMKM Kabupaten Bandung Barat</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800|jet-brains-mono:400,500,600&display=swap" rel="stylesheet" />
    <style>
        :root {
            --primary: #00684A;
            --primary-light: #34d399;
            --primary-dark: #004d36;
            --bg-body: #090d16;
            --bg-sidebar: #0f172a;
            --bg-card: #1e293b;
            --bg-code: #0b1329;
            --border-color: #334155;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --method-get: #10b981;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            line-height: 1.5;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Top Navigation Header */
        .top-nav {
            background-color: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border-color);
            position: sticky;
            top: 0;
            z-index: 50;
            padding: 0.85rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .brand-logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            color: #ffffff;
        }

        .brand-icon {
            background-color: var(--primary);
            color: #ffffff;
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 1.1rem;
            box-shadow: 0 0 12px rgba(0, 104, 74, 0.5);
        }

        .brand-text h1 {
            font-size: 1.1rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            line-height: 1.2;
        }

        .brand-text span {
            font-size: 0.725rem;
            color: var(--text-muted);
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .nav-btn {
            background-color: #334155;
            color: #f8fafc;
            font-size: 0.8rem;
            font-weight: 600;
            padding: 0.45rem 0.85rem;
            border-radius: 6px;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .nav-btn:hover {
            background-color: #475569;
        }

        .nav-btn-primary {
            background-color: var(--primary);
            color: #ffffff;
        }

        .nav-btn-primary:hover {
            background-color: var(--primary-dark);
        }

        /* Layout Container */
        .app-container {
            display: flex;
            flex: 1;
        }

        /* Sidebar Navigation */
        .sidebar {
            width: 280px;
            background-color: var(--bg-sidebar);
            border-right: 1px solid var(--border-color);
            padding: 1.5rem 1rem;
            position: sticky;
            top: 57px;
            height: calc(100vh - 57px);
            overflow-y: auto;
        }

        .sidebar-group-title {
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--text-muted);
            margin: 1.25rem 0 0.5rem 0.5rem;
        }

        .sidebar-group-title:first-child {
            margin-top: 0;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 0.65rem;
            border-radius: 6px;
            color: #cbd5e1;
            font-size: 0.825rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.15s ease;
        }

        .sidebar-link:hover {
            background-color: rgba(255, 255, 255, 0.06);
            color: #ffffff;
        }

        .sidebar-link.active {
            background-color: rgba(0, 104, 74, 0.25);
            color: var(--primary-light);
            font-weight: 700;
            border-left: 3px solid var(--primary-light);
        }

        .method-badge-sm {
            font-size: 0.65rem;
            font-weight: 800;
            padding: 0.1rem 0.35rem;
            border-radius: 4px;
            background-color: rgba(16, 185, 129, 0.2);
            color: #34d399;
        }

        /* Main Content Area */
        .main-content {
            flex: 1;
            padding: 2.5rem;
            max-width: 1100px;
        }

        .hero-banner {
            background: linear-gradient(135deg, rgba(0, 104, 74, 0.25) 0%, rgba(15, 23, 42, 0.9) 100%);
            border: 1px solid rgba(52, 211, 153, 0.3);
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2.5rem;
        }

        .hero-banner h2 {
            font-size: 1.6rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            color: #ffffff;
        }

        .hero-banner p {
            color: var(--text-muted);
            font-size: 0.925rem;
            margin-bottom: 1.25rem;
        }

        .base-url-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            background-color: var(--bg-code);
            border: 1px solid var(--border-color);
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.85rem;
            color: #38bdf8;
        }

        /* Endpoint Card */
        .endpoint-card {
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            margin-bottom: 2.5rem;
            overflow: hidden;
            scroll-margin-top: 80px;
        }

        .endpoint-header {
            padding: 1.25rem 1.5rem;
            background-color: rgba(15, 23, 42, 0.6);
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .endpoint-title-group {
            display: flex;
            align-items: center;
            gap: 0.85rem;
        }

        .method-badge {
            background-color: #10b981;
            color: #042f2e;
            font-weight: 800;
            font-size: 0.8rem;
            padding: 0.25rem 0.65rem;
            border-radius: 6px;
            text-transform: uppercase;
        }

        .endpoint-path {
            font-family: 'JetBrains Mono', monospace;
            font-size: 1rem;
            font-weight: 700;
            color: #ffffff;
        }

        .endpoint-desc {
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        .endpoint-body {
            padding: 1.5rem;
        }

        .section-title {
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--primary-light);
            margin-bottom: 0.75rem;
        }

        /* Params Table */
        .params-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.825rem;
            margin-bottom: 1.5rem;
        }

        .params-table th {
            text-align: left;
            padding: 0.5rem 0.75rem;
            background-color: var(--bg-sidebar);
            color: var(--text-muted);
            font-weight: 700;
            border-bottom: 1px solid var(--border-color);
        }

        .params-table td {
            padding: 0.65rem 0.75rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            color: #cbd5e1;
        }

        .param-name {
            font-family: 'JetBrains Mono', monospace;
            font-weight: 600;
            color: #f472b6;
        }

        .param-type {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.75rem;
            color: #38bdf8;
        }

        .param-required {
            color: #f87171;
            font-size: 0.7rem;
            font-weight: 700;
        }

        .param-optional {
            color: #94a3b8;
            font-size: 0.7rem;
        }

        /* Playground / Request Box */
        .playground-box {
            background-color: var(--bg-code);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            overflow: hidden;
        }

        .playground-header {
            background-color: rgba(15, 23, 42, 0.9);
            padding: 0.5rem 1rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .playground-tabs {
            display: flex;
            gap: 0.5rem;
        }

        .tab-btn {
            background: none;
            border: none;
            color: var(--text-muted);
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.3rem 0.6rem;
            border-radius: 4px;
            cursor: pointer;
        }

        .tab-btn.active {
            background-color: var(--primary);
            color: #ffffff;
        }

        .btn-test {
            background-color: #0284c7;
            color: #ffffff;
            border: none;
            padding: 0.35rem 0.85rem;
            font-size: 0.75rem;
            font-weight: 700;
            border-radius: 4px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            transition: background 0.2s ease;
        }

        .btn-test:hover {
            background-color: #0369a1;
        }

        pre {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.8rem;
            padding: 1rem;
            overflow-x: auto;
            color: #e2e8f0;
            max-height: 350px;
        }

        .json-key { color: #f472b6; }
        .json-string { color: #a5f3fc; }
        .json-number { color: #fde047; }
        .json-boolean { color: #c084fc; }

        @media (max-width: 768px) {
            .app-container { flex-direction: column; }
            .sidebar { width: 100%; height: auto; position: static; }
            .main-content { padding: 1.25rem; }
        }
    </style>
</head>
<body>

    <!-- TOP NAVIGATION BAR -->
    <header class="top-nav">
        <a href="/api/v1/docs" class="brand-logo">
            <div class="brand-icon">S</div>
            <div class="brand-text">
                <h1>SIGAP UMKM KBB</h1>
                <span>Public REST API Documentation v1.0</span>
            </div>
        </a>

        <div class="nav-links">
            <a href="/" class="nav-btn">🏠 Portal Utama</a>
            <a href="/admin" class="nav-btn nav-btn-primary">🔑 Dashboard Admin</a>
        </div>
    </header>

    <div class="app-container">
        <!-- SIDEBAR NAVIGATION -->
        <aside class="sidebar">
            <div class="sidebar-group-title">Ringkasan Utama</div>
            <a href="#overview" class="sidebar-link active">
                📌 Pengenalan API
            </a>

            <div class="sidebar-group-title">1. Data Spasial Kecamatan</div>
            <a href="#ep-kecamatan-index" class="sidebar-link">
                <span class="method-badge-sm">GET</span> /kecamatan
            </a>
            <a href="#ep-kecamatan-show" class="sidebar-link">
                <span class="method-badge-sm">GET</span> /kecamatan/{id}
            </a>

            <div class="sidebar-group-title">2. Analisis K-Means (ML)</div>
            <a href="#ep-analisis-latest" class="sidebar-link">
                <span class="method-badge-sm">GET</span> /analisis/latest
            </a>
            <a href="#ep-cluster-index" class="sidebar-link">
                <span class="method-badge-sm">GET</span> /cluster
            </a>
            <a href="#ep-centroid-index" class="sidebar-link">
                <span class="method-badge-sm">GET</span> /centroid
            </a>

            <div class="sidebar-group-title">3. Data Spasial UMKM</div>
            <a href="#ep-umkm-index" class="sidebar-link">
                <span class="method-badge-sm">GET</span> /umkm
            </a>
            <a href="#ep-umkm-search" class="sidebar-link">
                <span class="method-badge-sm">GET</span> /umkm/search
            </a>
            <a href="#ep-umkm-show" class="sidebar-link">
                <span class="method-badge-sm">GET</span> /umkm/{id}
            </a>

            <div class="sidebar-group-title">4. Master Kategori UMKM</div>
            <a href="#ep-kategori-index" class="sidebar-link">
                <span class="method-badge-sm">GET</span> /kategori-umkm
            </a>

            <div class="sidebar-group-title">5. Artikel Edukasi Usaha</div>
            <a href="#ep-artikel-index" class="sidebar-link">
                <span class="method-badge-sm">GET</span> /artikel
            </a>
            <a href="#ep-artikel-show" class="sidebar-link">
                <span class="method-badge-sm">GET</span> /artikel/{slug}
            </a>

            <div class="sidebar-group-title">6. Agregat & Statistik</div>
            <a href="#ep-statistik-index" class="sidebar-link">
                <span class="method-badge-sm">GET</span> /statistik
            </a>
        </aside>

        <!-- MAIN CONTENT AREA -->
        <main class="main-content">
            <!-- HERO OVERVIEW BANNER -->
            <section id="overview" class="hero-banner">
                <h2>Spesifikasi RESTful API v1.0</h2>
                <p>Dokumentasi resmi layanan API publik untuk konsumsi peta interaktif Leaflet.js React Frontend, integrasi aplikasi mobile, dan pengujian sistem skripsi WebGIS KBB.</p>

                <div class="base-url-pill">
                    <span style="color: var(--text-muted);">Base URL:</span>
                    <strong id="api-base-url">{{ url('/api/v1') }}</strong>
                    <button onclick="copyBaseUrl()" id="btn-copy-base" title="Salin Base URL" style="background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.4); cursor: pointer; color: #34d399; margin-left: 8px; padding: 3px 8px; border-radius: 6px; font-size: 0.75rem; font-weight: 600; transition: all 0.2s;">
                        📋 Salin
                    </button>
                </div>
            </section>

            <!-- ENDPOINT 1: GET /kecamatan -->
            <article id="ep-kecamatan-index" class="endpoint-card">
                <div class="endpoint-header">
                    <div class="endpoint-title-group">
                        <span class="method-badge">GET</span>
                        <span class="endpoint-path">/api/v1/kecamatan</span>
                    </div>
                    <span class="endpoint-desc">Listing 16 Kecamatan KBB & Poligon Spasial GeoJSON</span>
                </div>
                <div class="endpoint-body">
                    <div class="section-title">Query Parameters</div>
                    <p style="font-size: 0.825rem; color: var(--text-muted); margin-bottom: 1rem;">Tidak membutuhkan parameter wajib. Mengembalikan array 16 data kecamatan lengkap dengan batas peta GeoJSON.</p>

                    <div class="section-title">Uji Coba Endpoint (Live Response)</div>
                    <div class="playground-box">
                        <div class="playground-header">
                            <div class="playground-tabs">
                                <button class="tab-btn active">JSON Response</button>
                            </div>
                            <button onclick="testEndpoint('/api/v1/kecamatan', 'res-kecamatan-index')" class="btn-test">
                                ▶️ Jalankan Request Live
                            </button>
                        </div>
                        <pre id="res-kecamatan-index">Klik tombol "Jalankan Request Live" untuk mengambil data aktual dari API...</pre>
                    </div>
                </div>
            </article>

            <!-- ENDPOINT 2: GET /kecamatan/{id} -->
            <article id="ep-kecamatan-show" class="endpoint-card">
                <div class="endpoint-header">
                    <div class="endpoint-title-group">
                        <span class="method-badge">GET</span>
                        <span class="endpoint-path">/api/v1/kecamatan/{id}</span>
                    </div>
                    <span class="endpoint-desc">Detail Spasial & Profil Demografi Kecamatan</span>
                </div>
                <div class="endpoint-body">
                    <div class="section-title">Path Parameters</div>
                    <table class="params-table">
                        <thead>
                            <tr>
                                <th>Parameter</th>
                                <th>Tipe</th>
                                <th>Status</th>
                                <th>Deskripsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="param-name">id</td>
                                <td class="param-type">integer</td>
                                <td><span class="param-required">Wajib</span></td>
                                <td>ID unik kecamatan (contoh: <code>1</code> untuk Lembang, <code>6</code> untuk Ngamprah).</td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="section-title">Uji Coba Endpoint (Live Response)</div>
                    <div class="playground-box">
                        <div class="playground-header">
                            <div class="playground-tabs">
                                <button class="tab-btn active">JSON Response</button>
                            </div>
                            <button onclick="testEndpoint('/api/v1/kecamatan/1', 'res-kecamatan-show')" class="btn-test">
                                ▶️ Test GET /kecamatan/1
                            </button>
                        </div>
                        <pre id="res-kecamatan-show">Klik tombol di atas untuk melihat detail Kecamatan Lembang...</pre>
                    </div>
                </div>
            </article>

            <!-- ENDPOINT 3: GET /analisis/latest -->
            <article id="ep-analisis-latest" class="endpoint-card">
                <div class="endpoint-header">
                    <div class="endpoint-title-group">
                        <span class="method-badge">GET</span>
                        <span class="endpoint-path">/api/v1/analisis/latest</span>
                    </div>
                    <span class="endpoint-desc">Metadata Batch K-Means Terpublikasi Aktif</span>
                </div>
                <div class="endpoint-body">
                    <div class="section-title">Deskripsi</div>
                    <p style="font-size: 0.825rem; color: var(--text-muted); margin-bottom: 1rem;">Mengembalikan metadata batch analisis K-Means yang sedang dipublikasikan di WebGIS publik (K optimal, Silhouette score, DBI score, tanggal analisis).</p>

                    <div class="playground-box">
                        <div class="playground-header">
                            <div class="playground-tabs"><button class="tab-btn active">JSON Response</button></div>
                            <button onclick="testEndpoint('/api/v1/analisis/latest', 'res-analisis-latest')" class="btn-test">▶️ Test GET /analisis/latest</button>
                        </div>
                        <pre id="res-analisis-latest">Klik tombol untuk mengambil metadata batch terpublikasi...</pre>
                    </div>
                </div>
            </article>

            <!-- ENDPOINT 4: GET /cluster -->
            <article id="ep-cluster-index" class="endpoint-card">
                <div class="endpoint-header">
                    <div class="endpoint-title-group">
                        <span class="method-badge">GET</span>
                        <span class="endpoint-path">/api/v1/cluster</span>
                    </div>
                    <span class="endpoint-desc">Pemetaan Klaster 16 Kecamatan & Profil Sektor</span>
                </div>
                <div class="endpoint-body">
                    <div class="section-title">Deskripsi</div>
                    <p style="font-size: 0.825rem; color: var(--text-muted); margin-bottom: 1rem;">Mengembalikan hasil penugasan klaster K-Means untuk 16 kecamatan Kabupaten Bandung Barat beserta profil sektor dominan.</p>

                    <div class="playground-box">
                        <div class="playground-header">
                            <div class="playground-tabs"><button class="tab-btn active">JSON Response</button></div>
                            <button onclick="testEndpoint('/api/v1/cluster', 'res-cluster-index')" class="btn-test">▶️ Test GET /cluster</button>
                        </div>
                        <pre id="res-cluster-index">Klik tombol untuk mengambil data pemetaan 16 klaster kecamatan...</pre>
                    </div>
                </div>
            </article>

            <!-- ENDPOINT 5: GET /centroid -->
            <article id="ep-centroid-index" class="endpoint-card">
                <div class="endpoint-header">
                    <div class="endpoint-title-group">
                        <span class="method-badge">GET</span>
                        <span class="endpoint-path">/api/v1/centroid</span>
                    </div>
                    <span class="endpoint-desc">Profil Pusat Centroid & Nilai Fitur BPS</span>
                </div>
                <div class="endpoint-body">
                    <div class="playground-box">
                        <div class="playground-header">
                            <div class="playground-tabs"><button class="tab-btn active">JSON Response</button></div>
                            <button onclick="testEndpoint('/api/v1/centroid', 'res-centroid-index')" class="btn-test">▶️ Test GET /centroid</button>
                        </div>
                        <pre id="res-centroid-index">Klik tombol untuk mengambil statistik fitur centroid...</pre>
                    </div>
                </div>
            </article>

            <!-- ENDPOINT 6: GET /umkm -->
            <article id="ep-umkm-index" class="endpoint-card">
                <div class="endpoint-header">
                    <div class="endpoint-title-group">
                        <span class="method-badge">GET</span>
                        <span class="endpoint-path">/api/v1/umkm</span>
                    </div>
                    <span class="endpoint-desc">Listing Titik Spasial UMKM (Filter & Paginasi)</span>
                </div>
                <div class="endpoint-body">
                    <div class="section-title">Query Parameters (Optional)</div>
                    <table class="params-table">
                        <thead>
                            <tr>
                                <th>Parameter</th>
                                <th>Tipe</th>
                                <th>Status</th>
                                <th>Deskripsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="param-name">kecamatan_id</td>
                                <td class="param-type">integer</td>
                                <td><span class="param-optional">Opsional</span></td>
                                <td>Filter titik UMKM berdasarkan ID Kecamatan (misal: <code>1</code>).</td>
                            </tr>
                            <tr>
                                <td class="param-name">kategori_id</td>
                                <td class="param-type">integer</td>
                                <td><span class="param-optional">Opsional</span></td>
                                <td>Filter berdasarkan ID Kategori Usaha (misal: <code>1</code> untuk Makanan).</td>
                            </tr>
                            <tr>
                                <td class="param-name">status</td>
                                <td class="param-type">string</td>
                                <td><span class="param-optional">Opsional</span></td>
                                <td>Filter status operasional (<code>aktif</code> / <code>nonaktif</code>).</td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="playground-box">
                        <div class="playground-header">
                            <div class="playground-tabs"><button class="tab-btn active">JSON Response</button></div>
                            <button onclick="testEndpoint('/api/v1/umkm?kecamatan_id=1', 'res-umkm-index')" class="btn-test">▶️ Test GET /umkm?kecamatan_id=1</button>
                        </div>
                        <pre id="res-umkm-index">Klik tombol untuk mengambil data titik UMKM di Lembang...</pre>
                    </div>
                </div>
            </article>

            <!-- ENDPOINT 7: GET /umkm/search -->
            <article id="ep-umkm-search" class="endpoint-card">
                <div class="endpoint-header">
                    <div class="endpoint-title-group">
                        <span class="method-badge">GET</span>
                        <span class="endpoint-path">/api/v1/umkm/search</span>
                    </div>
                    <span class="endpoint-desc">Pencarian Spasial Cepat UMKM</span>
                </div>
                <div class="endpoint-body">
                    <div class="section-title">Query Parameters</div>
                    <table class="params-table">
                        <thead>
                            <tr>
                                <th>Parameter</th>
                                <th>Tipe</th>
                                <th>Status</th>
                                <th>Deskripsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="param-name">q</td>
                                <td class="param-type">string</td>
                                <td><span class="param-required">Wajib</span></td>
                                <td>Kata kunci nama UMKM, nama pemilik, atau alamat.</td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="playground-box">
                        <div class="playground-header">
                            <div class="playground-tabs"><button class="tab-btn active">JSON Response</button></div>
                            <button onclick="testEndpoint('/api/v1/umkm/search?q=kopi', 'res-umkm-search')" class="btn-test">▶️ Test GET /umkm/search?q=kopi</button>
                        </div>
                        <pre id="res-umkm-search">Klik tombol untuk mencari UMKM dengan kata kunci "kopi"...</pre>
                    </div>
                </div>
            </article>

            <!-- ENDPOINT 8: GET /kategori-umkm -->
            <article id="ep-kategori-index" class="endpoint-card">
                <div class="endpoint-header">
                    <div class="endpoint-title-group">
                        <span class="method-badge">GET</span>
                        <span class="endpoint-path">/api/v1/kategori-umkm</span>
                    </div>
                    <span class="endpoint-desc">Listing Master Kategori UMKM & Warna Marker</span>
                </div>
                <div class="endpoint-body">
                    <div class="playground-box">
                        <div class="playground-header">
                            <div class="playground-tabs"><button class="tab-btn active">JSON Response</button></div>
                            <button onclick="testEndpoint('/api/v1/kategori-umkm', 'res-kategori-index')" class="btn-test">▶️ Test GET /kategori-umkm</button>
                        </div>
                        <pre id="res-kategori-index">Klik tombol untuk mengambil master kategori UMKM...</pre>
                    </div>
                </div>
            </article>

            <!-- ENDPOINT 9: GET /artikel -->
            <article id="ep-artikel-index" class="endpoint-card">
                <div class="endpoint-header">
                    <div class="endpoint-title-group">
                        <span class="method-badge">GET</span>
                        <span class="endpoint-path">/api/v1/artikel</span>
                    </div>
                    <span class="endpoint-desc">Listing Artikel Edukasi & Berita UMKM</span>
                </div>
                <div class="endpoint-body">
                    <div class="playground-box">
                        <div class="playground-header">
                            <div class="playground-tabs"><button class="tab-btn active">JSON Response</button></div>
                            <button onclick="testEndpoint('/api/v1/artikel', 'res-artikel-index')" class="btn-test">▶️ Test GET /artikel</button>
                        </div>
                        <pre id="res-artikel-index">Klik tombol untuk melihat daftar artikel publikasi...</pre>
                    </div>
                </div>
            </article>

            <!-- ENDPOINT 10: GET /statistik -->
            <article id="ep-statistik-index" class="endpoint-card">
                <div class="endpoint-header">
                    <div class="endpoint-title-group">
                        <span class="method-badge">GET</span>
                        <span class="endpoint-path">/api/v1/statistik</span>
                    </div>
                    <span class="endpoint-desc">Ringkasan Agregat Total UMKM, Kategori, & Kecamatan</span>
                </div>
                <div class="endpoint-body">
                    <div class="playground-box">
                        <div class="playground-header">
                            <div class="playground-tabs"><button class="tab-btn active">JSON Response</button></div>
                            <button onclick="testEndpoint('/api/v1/statistik', 'res-statistik-index')" class="btn-test">▶️ Test GET /statistik</button>
                        </div>
                        <pre id="res-statistik-index">Klik tombol untuk mengambil ringkasan statistik agregat...</pre>
                    </div>
                </div>
            </article>

        </main>
    </div>

    <!-- LIVE API TEST SCRIPT -->
    <script>
        function copyBaseUrl() {
            const url = document.getElementById('api-base-url').innerText;
            navigator.clipboard.writeText(url).then(() => {
                const btn = document.getElementById('btn-copy-base');
                btn.innerText = '✅ Tersalin!';
                setTimeout(() => { btn.innerText = '📋 Salin'; }, 2000);
            });
        }

        async function testEndpoint(endpoint, elementId) {
            const pre = document.getElementById(elementId);
            const fullUrl = `${window.location.origin}${endpoint}`;
            pre.innerHTML = `<div style="margin-bottom: 0.5rem; color: #38bdf8;">⏳ Mengirim GET HTTP Request ke <code>${fullUrl}</code>...</div>`;

            const startTime = performance.now();
            try {
                const response = await fetch(endpoint);
                const latency = Math.round(performance.now() - startTime);
                const isSuccess = response.ok;
                const statusBadge = isSuccess 
                    ? `<span style="background: #065f46; color: #34d399; padding: 2px 6px; border-radius: 4px; font-weight: 700;">Status: ${response.status} ${response.statusText || 'OK'}</span>`
                    : `<span style="background: #7f1d1d; color: #f87171; padding: 2px 6px; border-radius: 4px; font-weight: 700;">Status: ${response.status} ${response.statusText}</span>`;
                
                const data = await response.json();
                const headerInfo = `<div style="display: flex; gap: 12px; align-items: center; margin-bottom: 0.75rem; font-size: 0.75rem; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 0.5rem;">` +
                    `${statusBadge}` +
                    `<span style="color: #94a3b8;">Waktu: <strong>${latency} ms</strong></span>` +
                    `<span style="color: #64748b; font-family: monospace;">Target: ${fullUrl}</span>` +
                    `</div>`;

                pre.innerHTML = headerInfo + formatJsonHighlight(data);
            } catch (error) {
                const latency = Math.round(performance.now() - startTime);
                pre.innerHTML = `<div style="color: #f87171; margin-bottom: 0.5rem;">❌ Gagal mengambil data API (${latency} ms): ${error.message}</div>` +
                    `<div style="color: #64748b; font-size: 0.75rem;">Target: ${fullUrl}</div>`;
            }
        }

        function formatJsonHighlight(json) {
            if (typeof json !== 'string') {
                json = JSON.stringify(json, null, 2);
            }
            json = json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            return json.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g, function (match) {
                let cls = 'json-number';
                if (/^"/.test(match)) {
                    if (/:$/.test(match)) {
                        cls = 'json-key';
                    } else {
                        cls = 'json-string';
                    }
                } else if (/true|false/.test(match)) {
                    cls = 'json-boolean';
                } else if (/null/.test(match)) {
                    cls = 'json-boolean';
                }
                return '<span class="' + cls + '">' + match + '</span>';
            });
        }

        // Active sidebar link scrolling listener
        window.addEventListener('DOMContentLoaded', () => {
            const links = document.querySelectorAll('.sidebar-link');
            links.forEach(link => {
                link.addEventListener('click', function() {
                    links.forEach(l => l.classList.remove('active'));
                    this.classList.add('active');
                });
            });
        });
    </script>
</body>
</html>
