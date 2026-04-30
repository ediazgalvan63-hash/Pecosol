@echo off
REM =====================================================
REM Pecosol - Iniciar Servidor Chatbot (FastAPI)
REM =====================================================
REM Este archivo inicia el servidor Python necesario para que el chatbot funcione

setlocal enabledelayedexpansion

REM Obtener el directorio del script (python_api)
set SCRIPT_DIR=%~dp0

REM Cambiar al directorio de python_api
cd /d "%SCRIPT_DIR%"

echo.
echo ============================================
echo  Pecosol - Asistente IA (FastAPI Server)
echo ============================================
echo.
echo Directorio: %CD%
echo.

REM Verificar que exista .env
if not exist ".env" (
    echo [!] ADVERTENCIA: Archivo .env no encontrado
    echo.
    echo Creando .env desde plantilla...
    if exist ".env.example" (
        copy ".env.example" ".env" >nul
        echo [+] Archivo .env creado exitosamente
    ) else (
        echo [-] No se encontr. .env.example
        pause
        exit /b 1
    )
    echo.
    echo IMPORTANTE: Debes editar el archivo .env y configurar:
    echo   - DB_HOST, DB_NAME, DB_USER, DB_PASSWORD
    echo   - OPENAI_API_KEY (obtener en https://platform.openai.com)
    echo.
    echo Pulsa cualquier tecla cuando hayas configurado .env...
    pause
)

REM Verificar que exista Python
python --version >nul 2>&1
if errorlevel 1 (
    REM Intentar ubicaciones comunes de Python
    set PYTHON_EXE=
    
    if exist "C:\Program Files\Python311\python.exe" (
        set PYTHON_EXE="C:\Program Files\Python311\python.exe"
    ) else if exist "C:\Program Files\Python312\python.exe" (
        set PYTHON_EXE="C:\Program Files\Python312\python.exe"
    ) else if exist "C:\Users\%USERNAME%\AppData\Local\Programs\Python\Python311\python.exe" (
        set PYTHON_EXE="C:\Users\%USERNAME%\AppData\Local\Programs\Python\Python311\python.exe"
    ) else if exist "C:\Users\%USERNAME%\AppData\Local\Programs\Python\Python312\python.exe" (
        set PYTHON_EXE="C:\Users\%USERNAME%\AppData\Local\Programs\Python\Python312\python.exe"
    ) else if exist "C:\Users\%USERNAME%\AppData\Local\Microsoft\WindowsApps\python.exe" (
        set PYTHON_EXE="C:\Users\%USERNAME%\AppData\Local\Microsoft\WindowsApps\python.exe"
    )
    
    if defined PYTHON_EXE (
        echo [+] Python encontrado en: !PYTHON_EXE!
        REM Usar Python encontrado para el resto del script
        set PYTHON_CMD=!PYTHON_EXE!
    ) else (
        echo [-] ERROR: Python no esta instalado o no esta en PATH
        echo.
        echo Soluciones:
        echo 1. Instala Python desde https://www.python.org
        echo 2. Asegúrate de marcar "Add Python to PATH" durante la instalación
        echo 3. Reinicia esta ventana despues de instalar Python
        echo.
        pause
        exit /b 1
    )
) else (
    set PYTHON_CMD=python
)

REM Verificar que exista requirements.txt
if not exist "requirements.txt" (
    echo [-] ERROR: No se encontro requirements.txt
    pause
    exit /b 1
)

REM Instalar dependencias si es necesario
echo.
echo [*] Verificando dependencias de Python...
%PYTHON_CMD% -m pip install -q -r requirements.txt >nul 2>&1

if errorlevel 1 (
    echo [!] Instalando dependencias (primera vez)...
    %PYTHON_CMD% -m pip install -r requirements.txt
    if errorlevel 1 (
        echo [-] ERROR: No se pudieron instalar las dependencias
        pause
        exit /b 1
    )
)

REM Iniciar el servidor
echo.
echo [+] Iniciando servidor FastAPI...
echo.
echo    URL: http://127.0.0.1:8000
echo    Documentacion: http://127.0.0.1:8000/docs
echo.
echo Presiona Ctrl+C para detener el servidor
echo.
echo ============================================
echo.

%PYTHON_CMD% -m uvicorn main:app --host 127.0.0.1 --port 8000 --reload

echo.
echo Servidor detenido.
pause
