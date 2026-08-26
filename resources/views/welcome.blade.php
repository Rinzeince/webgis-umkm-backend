<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SIGAP UMKM KBB — Backend Service API & Dashboard</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
    <style>
        :root {
            --primary: #00684A;
            --primary-dark: #004d36;
            --bg-dark: #090d16;
            --card-dark: #111827;
            --text-muted: #9ca3af;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-dark);
            color: #ffffff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        .card {
            background-color: var(--card-dark);
            border: 1px solid #1f2937;
            border-radius: 1rem;
            max-width: 540px;
            width: 100%;
            padding: 2.5rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5), 0 8px 10px -6px rgba(0, 0, 0, 0.5);
            text-align: center;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background-color: rgba(0, 104, 74, 0.2);
            color: #34d399;
            border: 1px solid rgba(52, 211, 153, 0.3);
            font-size: 0.75rem;
            font-weight: 700;
            padding: 0.35rem 0.85rem;
            border-radius: 9999px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 1.5rem;
        }

        .pulse-dot {
            width: 8px;
            height: 8px;
            background-color: #34d399;
            border-radius: 50%;
            box-shadow: 0 0 10px #34d399;
        }

        h1 {
            font-size: 1.75rem;
            font-weight: 800;
            letter-spacing: -0.025em;
            margin-bottom: 0.75rem;
        }

        p {
            color: var(--text-muted);
            font-size: 0.925rem;
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        .btn-group {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .btn-subgroup {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        @media (min-width: 480px) {
            .btn-subgroup {
                flex-direction: row;
            }
        }

        .btn {
            flex: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.85rem 1.25rem;
            font-size: 0.875rem;
            font-weight: 600;
            border-radius: 0.5rem;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .btn-primary {
            background-color: var(--primary);
            color: #ffffff;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            box-shadow: 0 4px 12px rgba(0, 104, 74, 0.4);
        }

        .btn-info {
            background-color: #1e293b;
            color: #38bdf8;
            border: 1px solid rgba(56, 189, 248, 0.25);
        }

        .btn-info:hover {
            background-color: rgba(56, 189, 248, 0.12);
            border-color: #38bdf8;
            color: #7dd3fc;
        }

        .btn-secondary {
            background-color: #1f2937;
            color: #e5e7eb;
            border: 1px solid #374151;
        }

        .btn-secondary:hover {
            background-color: #374151;
            color: #ffffff;
        }

        .footer-note {
            margin-top: 2rem;
            font-size: 0.75rem;
            color: #6b7280;
        }
    </style>
</head>

<body>
    <div class="card">
        <!-- <div class="badge">
            <span class="pulse-dot"></span>
            Backend API & GIS Engine Active
        </div> -->

        <h1>SIGAP UMKM KBB</h1>
        <p>Sistem Informasi Geografis Pemetaan & Analysis Clustering K-Means Sektor UMKM Kabupaten Bandung Barat.</p>

        <div class="btn-group">
            <div class="btn-subgroup">
                <a href="/admin" class="btn btn-primary">
                    🔑 Panel Admin Dashboard
                </a>
                <a href="{{ env('FRONTEND_URL', 'https://webgis-umkm-frontend.vercel.app') }}" target="_blank"
                    class="btn btn-secondary">
                    🌐 WebGIS Frontend
                </a>
            </div>
            <a href="{{ route('api.docs') }}" class="btn btn-info">
                📖 API Documentation
            </a>
        </div>

        <div class="footer-note">
            RESTful API Service Version 1.0 (Laravel {{ app()->version() }})
        </div>
    </div>
</body>

</html>