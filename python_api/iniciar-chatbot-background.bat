@echo off
REM =====================================================
REM Pecosol - Iniciar Chatbot en Segundo Plano
REM =====================================================
REM Este archivo inicia el servidor Python sin bloquear la terminal

setlocal enabledelayedexpansion

REM Obtener directorio del script
set SCRIPT_DIR=%~dp0

REM Cambiar a directorio python_api
cd /d "%SCRIPT_DIR%"

REM Verificar si Python está instalado
set PYTHON_CMD=
if exist "%~dp0..\.venv\Scripts\pythonw.exe" (
    set PYTHON_CMD="%~dp0..\.venv\Scripts\pythonw.exe"
) else (
    where pythonw >nul 2>nul
    if %ERRORLEVEL% EQU 0 (
        set PYTHON_CMD=pythonw.exe
    ) else if exist "%SYSTEMROOT%\pyw.exe" (
        set PYTHON_CMD="%SYSTEMROOT%\pyw.exe"
    )
)

if not defined PYTHON_CMD (
    echo [ERROR] Pythonw no encontrado. Instala Python 3.12, 3.13 o 3.14 y/o crea el entorno .venv.
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

REM Verificar si main.py existe
if not exist "main.py" (
    echo [ERROR] main.py no encontrado en: %CD%
    pause
    exit /b 1
)

REM Verificar si el puerto 8000 está siendo usado
echo Verificando puerto 8000...
netstat -ano | findstr ":8000" >nul
if %ERRORLEVEL% EQU 0 (
    echo [OK] Servidor ya está corriendo en puerto 8000
    echo.
    echo Abre en tu navegador: http://127.0.0.1:8000
    echo.
    pause
    exit /b 0
)

REM Iniciar servidor en background usando VBS
echo Iniciando servidor chatbot...
start "" /min %PYTHON_CMD% main.py

REM Esperar a que el servidor inicie
timeout /t 3 /nobreak

REM Verificar que el servidor respondió
echo Verificando servidor...
curl -s http://127.0.0.1:8000 >nul 2>&1
if %ERRORLEVEL% EQU 0 (
    echo [OK] Servidor chatbot iniciado exitosamente!
    echo.
    echo URL: http://127.0.0.1:8000
    echo.
    echo El servidor está corriendo en segundo plano.
    echo Puedes cerrar esta ventana.
) else (
    echo [ADVERTENCIA] El servidor está iniciando...
    echo Espera 5 segundos y recarga tu navegador
)

echo.
pause
