# ML Service - Python K-Means Clustering

This directory contains the Python machine learning service for UMKM distribution analysis using K-Means clustering with SEMMA methodology.

## Setup Instructions

### 1. Create Virtual Environment

Create an isolated Python virtual environment to avoid dependency conflicts:

```bash
cd ml-service
python3 -m venv venv
```

### 2. Activate Virtual Environment

**On Windows (PowerShell):**
```powershell
.\venv\Scripts\Activate.ps1
```

**On Windows (Command Prompt):**
```cmd
venv\Scripts\activate.bat
```

**On Linux/macOS:**
```bash
source venv/bin/activate
```

### 3. Install Dependencies

Install all required Python packages:

```bash
pip install -r requirements.txt
```

### 4. Verify Installation

Check that all packages are installed correctly:

```bash
python -c "import sklearn, pandas, numpy, matplotlib, seaborn; print('All dependencies installed successfully!')"
```

## Dependencies

- **scikit-learn>=1.3.0** - Machine learning algorithms (K-Means, StandardScaler)
- **pandas>=2.0.0** - Data manipulation and analysis
- **numpy>=1.24.0** - Numerical computing
- **matplotlib>=3.7.0** - Data visualization (graphs)
- **seaborn>=0.12.0** - Statistical data visualization

## Usage

The main script `semma_kmeans_umkm_kbb.py` is invoked by Laravel via the Queue system:

```bash
python3 semma_kmeans_umkm_kbb.py <input_json_path> <output_directory>
```

Example:
```bash
python3 semma_kmeans_umkm_kbb.py ../storage/app/ml/input/dataset_snapshot.json ../storage/app/ml/output
```

## Laravel Integration

Laravel invokes the Python script using the Process facade:

```php
Process::path(base_path('ml-service'))
    ->env(['PATH' => base_path('ml-service/venv/bin') . ':' . getenv('PATH')])
    ->timeout(600)
    ->run([
        'python3',
        'semma_kmeans_umkm_kbb.py',
        $inputPath,
        $outputPath
    ]);
```

## Files

- `semma_kmeans_umkm_kbb.py` - Main SEMMA K-Means clustering script
- `requirements.txt` - Python dependencies specification
- `test_dataset_snapshot.json` - Sample input for testing
- `*.json` / `*.csv` / `*.png` - Output files from analysis runs

## Notes

- The virtual environment directory `venv/` is excluded from version control (see .gitignore)
- Always activate the virtual environment before running the script manually
- The script is designed to be invoked by Laravel Queue jobs with a 10-minute timeout
- All output files are written to the directory specified as the second command-line argument
