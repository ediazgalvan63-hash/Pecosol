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
where python >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo [ERROR] Python no está instalado o no está en el PATH
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
start "" /min pythonw.exe main.py

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
