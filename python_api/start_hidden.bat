@echo off
REM =====================================================
REM Pecosol - Iniciar Chatbot en segundo plano sin ventana
REM =====================================================
REM Este archivo ejecuta el servidor Python de forma invisible usando VBScript.

cd /d "%~dp0"

if not exist "AutoStart-Chatbot.vbs" (
    echo [ERROR] No se encontro AutoStart-Chatbot.vbs en: %CD%
    pause
    exit /b 1
)

REM Ejecutar el script VBS que inicia el servidor sin mostrar ventana
wscript.exe "%~dp0AutoStart-Chatbot.vbs"

echo [OK] El servidor se esta iniciando en segundo plano.
echo Abre tu navegador en: http://127.0.0.1:8000
exit /b 0
