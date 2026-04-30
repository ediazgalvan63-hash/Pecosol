# =====================================================
# Pecosol - Iniciar Chatbot Automáticamente
# =====================================================
# Script para iniciar el servidor Python en segundo plano
# Ejecutar como: powershell -ExecutionPolicy Bypass -File AutoStart-Chatbot.ps1

# Obtener directorio del script
$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
Set-Location $scriptDir

# Verificar si el servidor ya está corriendo
$processCheck = Get-Process python -ErrorAction SilentlyContinue | Where-Object { $_.MainWindowTitle -like "*FastAPI*" -or $_.Path -like "*python*" }

if ($processCheck) {
    Write-Host "✓ El servidor chatbot ya está activo" -ForegroundColor Green
    exit 0
}

# Verificar que exista main.py
if (-not (Test-Path "main.py")) {
    Write-Host "✗ Error: No se encontró main.py en $scriptDir" -ForegroundColor Red
    exit 1
}

# Iniciar servidor en segundo plano
Write-Host "Iniciando servidor chatbot..." -ForegroundColor Cyan
$null = Start-Process powershell -ArgumentList "-NoExit -Command `"cd '$scriptDir'; python main.py`"" -WindowStyle Minimized -PassThru

# Esperar a que el servidor esté listo
Start-Sleep -Seconds 3

# Verificar que el servidor respondió
$maxAttempts = 10
$attempt = 0
$serverReady = $false

while ($attempt -lt $maxAttempts) {
    try {
        $response = Invoke-WebRequest -Uri "http://127.0.0.1:8000" -UseBasicParsing -TimeoutSec 2 -ErrorAction Stop
        $serverReady = $true
        Write-Host "✓ Servidor chatbot iniciado en http://127.0.0.1:8000" -ForegroundColor Green
        break
    } catch {
        $attempt++
        if ($attempt -lt $maxAttempts) {
            Start-Sleep -Seconds 1
        }
    }
}

if (-not $serverReady) {
    Write-Host "⚠ Nota: El servidor está iniciando. Espera unos segundos antes de usar el chatbot." -ForegroundColor Yellow
}

exit 0
