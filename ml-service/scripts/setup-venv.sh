#!/bin/bash
# Shell script to set up Python virtual environment for ML Service
# Usage: ./setup-venv.sh

echo "========================================"
echo "WebGIS UMKM ML Service - Setup Script"
echo "========================================"
echo ""

# Check if Python 3 is installed
echo "Checking Python installation..."
if ! command -v python3 &> /dev/null; then
    echo "ERROR: Python 3 is not installed or not in PATH"
    echo "Please install Python 3.11+ from your package manager"
    exit 1
fi
python3 --version
echo "✓ Python 3 found"

# Create virtual environment
echo ""
echo "Creating virtual environment..."
if [ -d "venv" ]; then
    echo "Virtual environment already exists. Skipping creation."
else
    python3 -m venv venv
    if [ $? -ne 0 ]; then
        echo "ERROR: Failed to create virtual environment"
        exit 1
    fi
    echo "✓ Virtual environment created successfully!"
fi

# Activate virtual environment
echo ""
echo "Activating virtual environment..."
source venv/bin/activate

# Upgrade pip
echo ""
echo "Upgrading pip..."
python -m pip install --upgrade pip

# Install dependencies
echo ""
echo "Installing dependencies from requirements.txt..."
pip install -r requirements.txt
if [ $? -ne 0 ]; then
    echo "ERROR: Failed to install dependencies"
    exit 1
fi

# Verify installation
echo ""
echo "Verifying installation..."
python -c "import sklearn, pandas, numpy, matplotlib, seaborn; print('All dependencies installed successfully!')"
if [ $? -ne 0 ]; then
    echo "ERROR: Dependency verification failed"
    exit 1
fi

echo ""
echo "========================================"
echo "✓ Setup completed successfully!"
echo "========================================"
echo ""
echo "To activate the virtual environment in the future, run:"
echo "  source venv/bin/activate"
echo ""
echo "To test the ML script, run:"
echo "  python semma_kmeans_umkm_kbb.py test_dataset_snapshot.json ./output"
echo ""
