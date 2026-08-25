#!/usr/bin/env python3
"""
Verification script to check if all Python dependencies are installed correctly.
Usage: python verify-setup.py
"""

import sys

def check_import(module_name, package_name=None):
    """Try to import a module and report status."""
    package_name = package_name or module_name
    try:
        __import__(module_name)
        print(f"✓ {package_name:20} - Installed")
        return True
    except ImportError as e:
        print(f"✗ {package_name:20} - NOT FOUND")
        print(f"  Error: {e}")
        return False

def check_versions():
    """Check and display versions of key packages."""
    print("\nPackage Versions:")
    print("-" * 50)
    
    try:
        import sklearn
        print(f"  scikit-learn: {sklearn.__version__}")
    except:
        pass
    
    try:
        import pandas as pd
        print(f"  pandas:       {pd.__version__}")
    except:
        pass
    
    try:
        import numpy as np
        print(f"  numpy:        {np.__version__}")
    except:
        pass
    
    try:
        import matplotlib
        print(f"  matplotlib:   {matplotlib.__version__}")
    except:
        pass
    
    try:
        import seaborn as sns
        print(f"  seaborn:      {sns.__version__}")
    except:
        pass

def main():
    print("=" * 50)
    print("Python Environment Verification")
    print("=" * 50)
    print(f"\nPython Version: {sys.version}")
    print(f"Python Path: {sys.executable}")
    print("\nChecking Dependencies:")
    print("-" * 50)
    
    all_ok = True
    
    # Core ML libraries
    all_ok &= check_import("sklearn", "scikit-learn")
    all_ok &= check_import("pandas", "pandas")
    all_ok &= check_import("numpy", "numpy")
    
    # Visualization
    all_ok &= check_import("matplotlib", "matplotlib")
    all_ok &= check_import("seaborn", "seaborn")
    
    # Sub-modules used in the script
    print("\nChecking Sub-modules:")
    print("-" * 50)
    all_ok &= check_import("sklearn.preprocessing", "StandardScaler")
    all_ok &= check_import("sklearn.cluster", "KMeans")
    all_ok &= check_import("sklearn.metrics", "Metrics")
    all_ok &= check_import("sklearn.decomposition", "PCA")
    all_ok &= check_import("matplotlib.pyplot", "pyplot")
    
    # Display versions
    check_versions()
    
    # Final result
    print("\n" + "=" * 50)
    if all_ok:
        print("✓ All dependencies are installed correctly!")
        print("=" * 50)
        print("\nYou can now run the ML script:")
        print("  python semma_kmeans_umkm_kbb.py <input.json> <output_dir>")
        return 0
    else:
        print("✗ Some dependencies are missing!")
        print("=" * 50)
        print("\nPlease run:")
        print("  pip install -r requirements.txt")
        return 1

if __name__ == "__main__":
    sys.exit(main())
