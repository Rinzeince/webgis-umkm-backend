# ML Service Quick Start Guide

## Prerequisites

- Python 3.11 or higher
- pip (Python package manager)

## Quick Setup (3 Steps)

### Step 1: Run Setup Script

**Windows (PowerShell):**
```powershell
cd ml-service
.\setup-venv.ps1
```

**Linux/macOS:**
```bash
cd ml-service
chmod +x setup-venv.sh
./setup-venv.sh
```

### Step 2: Verify Installation

```bash
python verify-setup.py
```

You should see:
```
✓ All dependencies are installed correctly!
```

### Step 3: Test the Script

```bash
python semma_kmeans_umkm_kbb.py test_dataset_snapshot.json ./output
```

## Manual Setup (Alternative)

If the setup scripts don't work, follow these steps manually:

```bash
# 1. Create virtual environment
python3 -m venv venv

# 2. Activate virtual environment
# Windows PowerShell:
.\venv\Scripts\Activate.ps1
# Windows CMD:
venv\Scripts\activate.bat
# Linux/macOS:
source venv/bin/activate

# 3. Upgrade pip
python -m pip install --upgrade pip

# 4. Install dependencies
pip install -r requirements.txt

# 5. Verify installation
python verify-setup.py
```

## Dependencies Installed

- **scikit-learn** ≥1.3.0 - K-Means clustering, StandardScaler
- **pandas** ≥2.0.0 - Data manipulation
- **numpy** ≥1.24.0 - Numerical operations
- **matplotlib** ≥3.7.0 - Graph generation
- **seaborn** ≥0.12.0 - Statistical visualization

## Troubleshooting

### "Python is not recognized"
- Install Python from https://www.python.org/downloads/
- Make sure to check "Add Python to PATH" during installation

### "Permission denied" on Linux/macOS
```bash
chmod +x setup-venv.sh
```

### "Execution policy" error on Windows
```powershell
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
```

### Dependency conflicts
```bash
# Create a fresh virtual environment
rm -rf venv  # or: Remove-Item -Recurse -Force venv
python3 -m venv venv
# Then follow setup steps again
```

## Next Steps

After setup is complete:

1. The virtual environment is ready for Laravel integration
2. Laravel will invoke the script via `Process::run()`
3. The script will be called from Queue jobs with a 10-minute timeout

See `README.md` for detailed documentation.
