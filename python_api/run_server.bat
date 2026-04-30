@echo off
setlocal enabledelayedexpansion

REM Script para arrancar el servidor FastAPI Chatbot
REM Cambiar al directorio del script
cd /d "%~dp0.."

REM Activar el entorno virtual
call .venv\Scripts\activate.bat

REM Cambiar al directorio de python_api
cd python_api

REM Iniciar uvicorn
python -m uvicorn main:app --host 127.0.0.1 --port 8000 --reload

REM Pausa para ver errores si los hay
pause
