# PowerShell Script: Deploy Chatbot a Railway
# 
# Uso:
#   .\scripts\deploy_chatbot_to_railway.ps1 -WebServiceUrl "https://tu-dominio-web.railway.app"
#
# O modo interactivo:
#   .\scripts\deploy_chatbot_to_railway.ps1

param(
    [string]$WebServiceUrl = "",
    [string]$OpenAIApiKey = "",
    [string]$RailwayToken = ""
)

# Colores
$colors = @{
    Reset   = "`e[0m"
    Green   = "`e[32m"
    Red     = "`e[31m"
    Yellow  = "`e[33m"
    Blue    = "`e[34m"
    Cyan    = "`e[36m"
}

function Write-Status {
    param([string]$message, [string]$status = "INFO")
    
    $color = switch ($status) {
        "SUCCESS" { $colors.Green }
        "ERROR" { $colors.Red }
        "WARNING" { $colors.Yellow }
        "INFO" { $colors.Cyan }
        default { $colors.Reset }
    }
    
    Write-Host "$color[$status]$($colors.Reset) $message"
}

Clear-Host
Write-Host "$($colors.Blue)╔════════════════════════════════════════════╗$($colors.Reset)"
Write-Host "$($colors.Blue)║   Chatbot IA - Deploy a Railway Helper      ║$($colors.Reset)"
Write-Host "$($colors.Blue)╚════════════════════════════════════════════╝$($colors.Reset)"
Write-Host ""

# Verificar si está en la raíz del proyecto
if (-not (Test-Path "python_api/Dockerfile")) {
    Write-Status "Error: No se encontró python_api/Dockerfile. Ejecuta este script desde la raíz del proyecto." "ERROR"
    exit 1
}

Write-Status "✓ Proyecto Pecosol detectado" "SUCCESS"
Write-Host ""

# 1. Obtener URLs necesarias
if (-not $WebServiceUrl) {
    Write-Host "$($colors.Yellow)CONFIGURACIÓN REQUERIDA:$($colors.Reset)"
    Write-Host ""
    Write-Host "Necesitamos los datos de tu servicio en Railway:"
    Write-Host ""
    
    $WebServiceUrl = Read-Host "Ingresa tu URL de servicio web (ej: https://pecosol-web.railway.app)"
    if (-not $WebServiceUrl -or $WebServiceUrl -eq "") {
        Write-Status "URL de servicio web no proporcionada" "ERROR"
        exit 1
    }
}

# 2. Verificar formato de URL
$WebServiceUrl = $WebServiceUrl.TrimEnd('/')
if (-not ($WebServiceUrl -match "^https?://")) {
    $WebServiceUrl = "https://$WebServiceUrl"
}

Write-Status "Servicio Web: $WebServiceUrl" "INFO"
Write-Host ""

# 3. Variables para el servicio del chatbot
Write-Host "$($colors.Yellow)CONFIGURACIÓN DEL SERVICIO CHATBOT:$($colors.Reset)"
Write-Host ""

# Generar nombre sugerido del chatbot
$ChatbotUrl = $WebServiceUrl -replace "-web", "-chatbot"
$ChatbotUrl = Read-Host "URL del servicio chatbot (Enter para [$ChatbotUrl])"
if ($ChatbotUrl -eq "") {
    $ChatbotUrl = $WebServiceUrl -replace "-web", "-chatbot"
}

$ChatbotUrl = $ChatbotUrl.TrimEnd('/')
if (-not ($ChatbotUrl -match "^https?://")) {
    $ChatbotUrl = "https://$ChatbotUrl"
}

Write-Status "Servicio Chatbot: $ChatbotUrl" "INFO"
Write-Host ""

# 4. OPENAI_API_KEY
if (-not $OpenAIApiKey) {
    Write-Host "Tu OpenAI API Key (puede dejarse vacío si usas Gemini):"
    $OpenAIApiKey = Read-Host "OPENAI_API_KEY"
}

if ($OpenAIApiKey) {
    Write-Status "API Key de OpenAI configurada (${($OpenAIApiKey.Substring(0,10))}...)" "INFO"
} else {
    Write-Status "⚠ Sin OpenAI API Key. Asegúrate de configurarla en Railway." "WARNING"
}

Write-Host ""
Write-Host "$($colors.Blue)═══════════════════════════════════════════$($colors.Reset)"
Write-Host ""
Write-Host "$($colors.Yellow)PRÓXIMOS PASOS EN RAILWAY:$($colors.Reset)"
Write-Host ""
Write-Host "1. Ve a tu proyecto en railway.app"
Write-Host ""
Write-Host "2. En el servicio 'pecosol-web', agrega esta variable:"
Write-Host "   $($colors.Cyan)CHATBOT_API_URL$($colors.Reset) = $ChatbotUrl/api/chat"
Write-Host ""
Write-Host "3. En el servicio 'pecosol-chatbot' (o créalo), agrega:"
Write-Host "   • $($colors.Cyan)CHATBOT_ALLOWED_ORIGINS$($colors.Reset) = $WebServiceUrl"
Write-Host "   • $($colors.Cyan)API_HOST$($colors.Reset) = 0.0.0.0"
Write-Host "   • $($colors.Cyan)API_RELOAD$($colors.Reset) = false"
Write-Host "   • $($colors.Cyan)OPENAI_API_KEY$($colors.Reset) = $OpenAIApiKey"
Write-Host "   • Plus: DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASSWORD"
Write-Host ""
Write-Host "4. Usa el Dockerfile: $($colors.Cyan)python_api/Dockerfile$($colors.Reset)"
Write-Host ""
Write-Host "5. Deploy y espera a que termine"
Write-Host ""
Write-Host "6. Verifica que funciona:"
Write-Host "   $($colors.Cyan)$ChatbotUrl/health$($colors.Reset)"
Write-Host ""
Write-Host "7. Prueba el chatbot en tu web:"
Write-Host "   $($colors.Cyan)$WebServiceUrl$($colors.Reset)"
Write-Host ""

Write-Host ""
Write-Host "📌 $($colors.Yellow)COMANDO PARA COPIAR (variables de entorno):$($colors.Reset)"
Write-Host ""
Write-Host @"
Servicio: pecosol-chatbot
CHATBOT_ALLOWED_ORIGINS=$WebServiceUrl
API_HOST=0.0.0.0
API_RELOAD=false
OPENAI_API_KEY=$OpenAIApiKey
OPENAI_MODEL=gpt-4o-mini
"@

Write-Host ""
Write-Host "Servicio: pecosol-web"
Write-Host "CHATBOT_API_URL=$ChatbotUrl/api/chat"
Write-Host ""

Write-Status "Guarda estas URLs en un lugar seguro" "INFO"
Write-Host ""

# 7. Ofrecer guardar en archivo
$saveToFile = Read-Host "¿Deseas guardar estas variables en un archivo .env local? (s/n)"
if ($saveToFile -eq "s") {
    $envContent = @"
# Chatbot Configuration for Railway
WEB_URL=$WebServiceUrl
CHATBOT_URL=$ChatbotUrl
CHATBOT_ALLOWED_ORIGINS=$WebServiceUrl
OPENAI_API_KEY=$OpenAIApiKey
API_HOST=0.0.0.0
API_RELOAD=false
OPENAI_MODEL=gpt-4o-mini

# Copy above values to Railway Dashboard
"@
    
    $envContent | Out-File ".env.railway.local" -Encoding UTF8
    Write-Status "Variables guardadas en: .env.railway.local" "SUCCESS"
}

Write-Host ""
Write-Status "¡Listo! Ahora configura en Railway siguiendo los pasos anteriores" "SUCCESS"
