#!/usr/bin/env powershell
<#
.SYNOPSIS
    Configura automáticamente el chatbot de Railway
    
.DESCRIPTION
    Este script facilita la configuración del chatbot en Railway
    asegurando que CHATBOT_API_URL esté correctamente definida.

.EXAMPLE
    .\scripts\fix_chatbot_railway.ps1
    
.NOTES
    Requiere acceso a Railway CLI o información manual
#>

# Colores para output
$colors = @{
    Green  = "`e[32m"
    Red    = "`e[31m"
    Yellow = "`e[33m"
    Cyan   = "`e[36m"
    Blue   = "`e[34m"
    Reset  = "`e[0m"
}

function Write-Status {
    param([string]$Message, [string]$Type = "INFO")
    $color = switch ($Type) {
        "SUCCESS" { $colors.Green }
        "ERROR" { $colors.Red }
        "WARNING" { $colors.Yellow }
        "INFO" { $colors.Cyan }
        default { $colors.Reset }
    }
    Write-Host "[$Type] $color$Message$($colors.Reset)"
}

function Write-Header {
    param([string]$Title)
    Write-Host ""
    Write-Host "$($colors.Blue)═══════════════════════════════════════════$($colors.Reset)"
    Write-Host "$($colors.Yellow)$Title$($colors.Reset)"
    Write-Host "$($colors.Blue)═══════════════════════════════════════════$($colors.Reset)"
    Write-Host ""
}

# ========================================
# INICIO DEL SCRIPT
# ========================================

Write-Header "🤖 CONFIGURAR CHATBOT EN RAILWAY"

Write-Host "Este script te ayudará a configurar el chatbot en Railway"
Write-Host "para que funcione igual que en local."
Write-Host ""

# Obtener información del usuario
Write-Host "$($colors.Yellow)📋 INFORMACIÓN REQUERIDA:$($colors.Reset)"
Write-Host ""

$WebUrl = Read-Host "URL de tu web en Railway (ej: https://pecosol-web.railway.app)"
$WebUrl = $WebUrl.TrimEnd('/').TrimEnd('/api/chat')

if (-not ($WebUrl -match "^https?://")) {
    $WebUrl = "https://$WebUrl"
}

Write-Status "Web URL: $WebUrl" "INFO"

# Generar URL del chatbot
$ChatbotUrl = $WebUrl -replace "-web$", "-chatbot"
$ChatbotUrl = Read-Host "URL del servicio chatbot (Enter para [$ChatbotUrl])"

if ($ChatbotUrl -eq "") {
    $ChatbotUrl = $WebUrl -replace "-web$", "-chatbot"
}

$ChatbotUrl = $ChatbotUrl.TrimEnd('/')
if (-not ($ChatbotUrl -match "^https?://")) {
    $ChatbotUrl = "https://$ChatbotUrl"
}

Write-Status "Chatbot URL: $ChatbotUrl" "INFO"

# Construir la URL del API
$ChatbotApiUrl = "$ChatbotUrl/api/chat"

Write-Host ""
Write-Header "📝 RESUMEN DE CONFIGURACIÓN"

Write-Host "Tu archivo de configuración será:"
Write-Host ""
Write-Host "  Nombre variable:  $($colors.Cyan)CHATBOT_API_URL$($colors.Reset)"
Write-Host "  Valor:            $($colors.Cyan)$ChatbotApiUrl$($colors.Reset)"
Write-Host ""

# Confirmar
$confirm = Read-Host "¿Es correcto? (s/n)"
if ($confirm -ne "s") {
    Write-Status "Operación cancelada" "WARNING"
    exit 0
}

# ========================================
# PASOS A SEGUIR EN RAILWAY
# ========================================

Write-Header "🚀 PASOS A SEGUIR EN RAILWAY"

Write-Host "1. Abre https://railway.app en tu navegador"
Write-Host ""
Write-Host "2. Selecciona tu proyecto Pecosol"
Write-Host ""
Write-Host "3. Haz clic en el servicio: $($colors.Cyan)pecosol-web$($colors.Reset)"
Write-Host ""
Write-Host "4. Ve a la pestaña: $($colors.Yellow)Variables$($colors.Reset)"
Write-Host ""
Write-Host "5. Busca o crea esta variable:"
Write-Host "   $($colors.Green)CHATBOT_API_URL$($colors.Reset)"
Write-Host ""
Write-Host "6. Asigna este valor:"
Write-Host "   $($colors.Cyan)$ChatbotApiUrl$($colors.Reset)"
Write-Host ""
Write-Host "7. Haz clic en: $($colors.Green)Save Variables$($colors.Reset)"
Write-Host ""
Write-Host "8. Espera a que el servicio haga $($colors.Yellow)Deploy$($colors.Reset) (1-2 minutos)"
Write-Host ""

# Guardar en archivo local
$saveToFile = Read-Host "¿Guardar estas variables en un archivo local para referencia? (s/n)"
if ($saveToFile -eq "s") {
    $envContent = @"
# Chatbot Configuration for Railway
# Copia estos valores a tu proyecto en Railway

[SERVICIO: pecosol-web]
CHATBOT_API_URL=$ChatbotApiUrl

[SERVICIO: pecosol-chatbot]
# Asegúrate que tenga estas variables (iguales a pecosol-web):
DB_HOST=mysql.railway.internal
DB_PORT=3306
DB_NAME=railway
DB_USER=root
DB_PASSWORD=<TU_PASSWORD>

# API de IA
OPENAI_API_KEY=sk-<TU_KEY>
OPENAI_MODEL=gpt-4o-mini

# FastAPI
API_HOST=0.0.0.0
API_PORT=8000
API_RELOAD=false

# CORS - Importante!
CHATBOT_ALLOWED_ORIGINS=$WebUrl
"@
    
    $envPath = ".env.chatbot-railway"
    $envContent | Out-File $envPath -Encoding UTF8
    Write-Status "Configuración guardada en: $envPath" "SUCCESS"
    Write-Host ""
}

Write-Header "✅ VERIFICACIÓN"

Write-Host "Después de guardar en Railway, verifica el chatbot:"
Write-Host ""
Write-Host "1. Abre en tu navegador:"
Write-Host "   $($colors.Cyan)$WebUrl/verify_chatbot.php$($colors.Reset)"
Write-Host ""
Write-Host "2. Deberías ver:"
Write-Host "   $($colors.Green)✅ READY$($colors.Reset)"
Write-Host ""
Write-Host "3. Si no funciona, revisa los logs del servicio chatbot:"
Write-Host "   Railway → tu proyecto → pecosol-chatbot → Logs"
Write-Host ""

Write-Header "🧪 PRUEBA FINAL"

Write-Host "Si todo está correcto:"
Write-Host "1. Abre tu web: $($colors.Cyan)$WebUrl$($colors.Reset)"
Write-Host "2. Haz clic en el botón 🤖 (abajo a la derecha)"
Write-Host "3. Pregunta: 'Cuantos productos tenemos?'"
Write-Host "4. El chatbot debe responder con datos reales"
Write-Host ""

Write-Status "Configuracion completada. Ahora configura en Railway!" "SUCCESS"
