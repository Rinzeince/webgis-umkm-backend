# reorganize-ml-service.ps1
# Script untuk reorganisasi folder ml-service

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Reorganisasi Folder ML Service" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan

# 1. Buat struktur folder baru
Write-Host "`n[1/5] Membuat struktur folder..." -ForegroundColor Yellow
$folders = @("src", "data", "output", "docs", "scripts")
foreach ($folder in $folders) {
    if (-Not (Test-Path $folder)) {
        New-Item -ItemType Directory -Path $folder | Out-Null
        Write-Host "  ✓ Created: $folder/" -ForegroundColor Green
    } else {
        Write-Host "  ✓ Exists: $folder/" -ForegroundColor Gray
    }
}

# 2. Pindahkan script Python ke src/
Write-Host "`n[2/5] Memindahkan script Python ke src/..." -ForegroundColor Yellow
$pythonFiles = @("analisis_data_umkm_v5.py")
foreach ($file in $pythonFiles) {
    if (Test-Path $file) {
        Move-Item -Path $file -Destination "src\" -Force
        Write-Host "  ✓ Moved: $file → src/" -ForegroundColor Green
    }
}

# 3. Pindahkan data input ke data/
Write-Host "`n[3/5] Memindahkan data input ke data/..." -ForegroundColor Yellow
$dataFiles = @("dataset.csv", "test_dataset_snapshot.json")
foreach ($file in $dataFiles) {
    if (Test-Path $file) {
        Move-Item -Path $file -Destination "data\" -Force
        Write-Host "  ✓ Moved: $file → data/" -ForegroundColor Green
    }
}

# 4. Pindahkan output files ke output/
Write-Host "`n[4/5] Memindahkan output files ke output/..." -ForegroundColor Yellow
$outputFiles = @(
    "centroid_output.json",
    "hasil_cluster_output.json",
    "metadata_output.json",
    "hasil_klasterisasi_umkm_kbb.csv"
)
foreach ($file in $outputFiles) {
    if (Test-Path $file) {
        Move-Item -Path $file -Destination "output\" -Force
        Write-Host "  ✓ Moved: $file → output/" -ForegroundColor Green
    }
}

# 5. Pindahkan dokumentasi ke docs/
Write-Host "`n[5/5] Memindahkan dokumentasi ke docs/..." -ForegroundColor Yellow
$docFiles = @("README.md", "QUICKSTART.md")
foreach ($file in $docFiles) {
    if (Test-Path $file) {
        Move-Item -Path $file -Destination "docs\" -Force
        Write-Host "  ✓ Moved: $file → docs/" -ForegroundColor Green
    }
}

# 6. Pindahkan setup scripts ke scripts/
Write-Host "`n[6/6] Memindahkan setup scripts ke scripts/..." -ForegroundColor Yellow
$scriptFiles = @("setup-venv.ps1", "setup-venv.sh", "verify-setup.py")
foreach ($file in $scriptFiles) {
    if (Test-Path $file) {
        Move-Item -Path $file -Destination "scripts\" -Force
        Write-Host "  ✓ Moved: $file → scripts/" -ForegroundColor Green
    }
}

Write-Host "`n========================================" -ForegroundColor Green
Write-Host "✓ Reorganisasi selesai!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host "`nStruktur folder baru:" -ForegroundColor Cyan
tree /F /A
