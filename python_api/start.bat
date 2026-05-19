@echo off
cd /d "%~dp0"
set APP_BASE_URL=http://localhost/pecosol
echo ====================================
echo   Pecosol - FastAPI Chatbot Server
echo ====================================
echo.
echo Directorio actual: %CD%
echo.

REM Verificar archivo .env
if not exist ".env" (
    echo [ADVERTENCIA] No existe archivo .env
    echo Copia .env.example a .env y configura tus variables
    echo.
    copy .env.example .env
    echo Archivo .env creado. EDITA el archivo y agrega tu OPENAI_API_KEY
    echo.
    pause
)

REM Encontrar Python en .venv o en PATH
set PYTHON_CMD=
if exist "%~dp0..\.venv\Scripts\python.exe" (
    set PYTHON_CMD="%~dp0..\.venv\Scripts\python.exe"
) else (
    python --version >nul 2>&1
    if errorlevel 1 (
        if exist "%SYSTEMROOT%\py.exe" (
            set PYTHON_CMD="%SYSTEMROOT%\py.exe -3"
        )
    ) else (
        set PYTHON_CMD=python
    )
)
if not defined PYTHON_CMD (
    echo [ERROR] Python no encontrado. Instala Python 3.12, 3.13 o 3.14 y/o crea el entorno .venv.
    pause
    exit /b 1
)

REM Verificar versión de Python compatible
for /f "tokens=2 delims= " %%A in ('%PYTHON_CMD% --version 2^>^&1') do set PY_VERSION=%%A
for /f "tokens=1-3 delims=." %%A in ("%PY_VERSION%") do (
    set PY_MAJOR=%%A
    set PY_MINOR=%%B
)
if "%PY_MAJOR%" NEQ "3" (
    echo [ERROR] Se requiere Python 3.12, 3.13 o 3.14. Se detectó Python %PY_VERSION%.
    pause
    exit /b 1
)
if %PY_MINOR% GEQ 15 (
    echo [ERROR] Python %PY_VERSION% no es compatible. Usa Python 3.12, 3.13 o 3.14.
    pause
    exit /b 1
)

REM Iniciar el servidor
echo [*] Iniciando servidor FastAPI en http://127.0.0.1:8000
echo [*] Documentacion disponible en http://127.0.0.1:8000/docs
echo.
echo Presiona Ctrl+C para detener el servidor
echo.

%PYTHON_CMD% -m uvicorn main:app --host 127.0.0.1 --port 8000 --reload

pause
