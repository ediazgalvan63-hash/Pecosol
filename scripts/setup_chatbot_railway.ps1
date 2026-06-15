#!/usr/bin/env powershell
# Configurador del Chatbot Railway - Pecosol

# Colors for output
$colors = @{
    Green  = "`e[32m"
    Red    = "`e[31m"
    Yellow = "`e[33m"
    Cyan   = "`e[36m"
    Blue   = "`e[34m"
    Reset  = "`e[0m"
}

Write-Host ""
Write-Host "$($colors.Blue)========================================================$($colors.Reset)"
Write-Host "$($colors.Cyan)  CONFIGURAR CHATBOT EN RAILWAY$($colors.Reset)"
Write-Host "$($colors.Blue)========================================================$($colors.Reset)"
Write-Host ""

Write-Host "Este script te guiara a traves de la configuracion del chatbot"
Write-Host "para que funcione igual que en local."
Write-Host ""

# Paso 1: URL de la web
Write-Host "$($colors.Yellow)PASO 1: URL de tu web en Railway$($colors.Reset)"
Write-Host "Ejemplo: https://pecosol-web.railway.app"
$WebUrl = Read-Host "Ingresa tu URL"

if ($WebUrl -eq "") {
    $WebUrl = "https://pecosol-web.railway.app"
}

$WebUrl = $WebUrl.TrimEnd('/').TrimEnd('/api/chat')
if (-not ($WebUrl -match "^https?://")) {
    $WebUrl = "https://$WebUrl"
}

Write-Host "[INFO] Web URL: $WebUrl"
Write-Host ""

# Paso 2: URL del chatbot
Write-Host "$($colors.Yellow)PASO 2: URL del servicio chatbot$($colors.Reset)"
$ChatbotUrl = $WebUrl -replace "-web", "-chatbot"
Write-Host "Sugerencia: $ChatbotUrl"
$input = Read-Host "Presiona Enter para aceptar o ingresa otra URL"

if ($input -ne "") {
    $ChatbotUrl = $input
}

$ChatbotUrl = $ChatbotUrl.TrimEnd('/')
if (-not ($ChatbotUrl -match "^https?://")) {
    $ChatbotUrl = "https://$ChatbotUrl"
}

Write-Host "[INFO] Chatbot URL: $ChatbotUrl"
Write-Host ""

# Construir URL del API
$ChatbotApiUrl = "$ChatbotUrl/api/chat"

Write-Host "$($colors.Blue)========================================================$($colors.Reset)"
Write-Host "$($colors.Green)RESUMEN$($colors.Reset)"
Write-Host "$($colors.Blue)========================================================$($colors.Reset)"
Write-Host ""
Write-Host "Variable a crear en Railway:"
Write-Host ""
Write-Host "  Nombre:  $($colors.Cyan)CHATBOT_API_URL$($colors.Reset)"
Write-Host "  Valor:   $($colors.Cyan)$ChatbotApiUrl$($colors.Reset)"
Write-Host ""

# Confirmar
$confirm = Read-Host "Es correcto? (s/n)"
if ($confirm -ne "s") {
    Write-Host "[ERROR] Operacion cancelada"
    exit 1
}

Write-Host ""
Write-Host "$($colors.Blue)========================================================$($colors.Reset)"
Write-Host "$($colors.Yellow)PASOS A SEGUIR EN RAILWAY$($colors.Reset)"
Write-Host "$($colors.Blue)========================================================$($colors.Reset)"
Write-Host ""

Write-Host "1. Abre https://railway.app en tu navegador"
Write-Host ""
Write-Host "2. Selecciona tu proyecto Pecosol"
Write-Host ""
Write-Host "3. Haz clic en el servicio: $($colors.Cyan)pecosol-web$($colors.Reset)"
Write-Host ""
Write-Host "4. Ve a la pestana: $($colors.Yellow)Variables$($colors.Reset) (Variables)"
Write-Host ""
Write-Host "5. Busca o crea esta variable:"
Write-Host "   $($colors.Green)CHATBOT_API_URL$($colors.Reset)"
Write-Host ""
Write-Host "6. Asigna este valor:"
Write-Host "   $($colors.Cyan)$ChatbotApiUrl$($colors.Reset)"
Write-Host ""
Write-Host "7. Haz clic en: $($colors.Green)Save Variables$($colors.Reset)"
Write-Host ""
Write-Host "8. Espera a que el servicio haga Deploy (1-2 minutos)"
Write-Host ""

# Guardar en archivo local
Write-Host "$($colors.Yellow)¿Guardar en archivo local para referencia?$($colors.Reset)"
$saveToFile = Read-Host "(s/n)"

if ($saveToFile -eq "s") {
    $envContent = @"
# Chatbot Configuration for Railway
# Copia estos valores a tu proyecto en Railway

[SERVICIO: pecosol-web]
CHATBOT_API_URL=$ChatbotApiUrl

[SERVICIO: pecosol-chatbot]
DB_HOST=mysql.railway.internal
DB_PORT=3306
DB_NAME=railway
DB_USER=root
DB_PASSWORD=TU_PASSWORD

OPENAI_API_KEY=sk-TU_KEY
OPENAI_MODEL=gpt-4o-mini

API_HOST=0.0.0.0
API_PORT=8000
API_RELOAD=false

CHATBOT_ALLOWED_ORIGINS=$WebUrl
"@
    
    $envPath = ".env.chatbot-railway"
    $envContent | Out-File $envPath -Encoding UTF8 -Force
    Write-Host "[SUCCESS] Guardado en: $envPath"
    Write-Host ""
}

Write-Host "$($colors.Blue)========================================================$($colors.Reset)"
Write-Host "$($colors.Green)VERIFICACION$($colors.Reset)"
Write-Host "$($colors.Blue)========================================================$($colors.Reset)"
Write-Host ""

Write-Host "Despues de guardar en Railway, verifica el chatbot:"
Write-Host ""
Write-Host "1. Abre en tu navegador:"
Write-Host "   $($colors.Cyan)$WebUrl/chatbot_diagnostic.php$($colors.Reset)"
Write-Host ""
Write-Host "2. Deberias ver: $($colors.Green)READY (Accesible)$($colors.Reset)"
Write-Host ""
Write-Host "3. Si no funciona, revisa los logs del servicio chatbot en Railway"
Write-Host ""

Write-Host "$($colors.Blue)========================================================$($colors.Reset)"
Write-Host "$($colors.Yellow)PRUEBA FINAL$($colors.Reset)"
Write-Host "$($colors.Blue)========================================================$($colors.Reset)"
Write-Host ""

Write-Host "Si todo esta correcto:"
Write-Host "1. Abre tu web: $($colors.Cyan)$WebUrl$($colors.Reset)"
Write-Host "2. Haz clic en el boton robot (abajo a la derecha)"
Write-Host "3. Pregunta: Cuantos productos tenemos?"
Write-Host "4. El chatbot debe responder con datos reales"
Write-Host ""

Write-Host "$($colors.Green)[SUCCESS] Listo! Sigue los pasos en Railway.$($colors.Reset)"
Write-Host ""
