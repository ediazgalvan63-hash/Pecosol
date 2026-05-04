#!/usr/bin/env python
import sys
try:
    from fastapi import FastAPI
    from uvicorn import run
    from pydantic import BaseModel
    print("✓ All dependencies imported successfully!")
    print(f"✓ Python version: {sys.version}")
except Exception as e:
    print(f"✗ Error: {e}")
    sys.exit(1)
