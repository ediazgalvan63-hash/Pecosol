@echo off
REM Script para iniciar el servidor chatbot de forma rápida sin bloqueos

cd /d "%~dp0"

echo [*] Iniciando servidor Pecosol Chatbot...
echo [*] Puerto: 8000
echo [*] URL: http://127.0.0.1:8000

REM Instalar dependencias si es necesario
echo [*] Verificando dependencias...
python -m pip install -q fastapi uvicorn mysql-connector-python google-generativeai openai python-dotenv python-multipart aiofiles 2>nul

REM Iniciar servidor con uvicorn
echo [✓] Iniciando uvicorn...
python -m uvicorn main:app --host 127.0.0.1 --port 8000 --reload

pause
