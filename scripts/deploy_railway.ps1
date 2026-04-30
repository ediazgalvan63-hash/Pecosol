param(
  [Parameter(Mandatory=$true)][string]$WebServiceId,
  [Parameter(Mandatory=$true)][string]$ChatbotServiceId,
  [Parameter(Mandatory=$false)][string]$SqlDumpPath = "pecosol_db.sql"
)

$ErrorActionPreference = "Stop"

function Assert-Command($name) {
  if (-not (Get-Command $name -ErrorAction SilentlyContinue)) {
    throw "No se encontro el comando '$name'. Instala la herramienta antes de continuar."
  }
}

Assert-Command "railway"
Assert-Command "mysql"

Write-Host "==> Verificando login en Railway..."
railway whoami | Out-Null

Write-Host "==> Desplegando servicio WEB..."
railway service $WebServiceId
railway up --detach

Write-Host "==> Desplegando servicio CHATBOT..."
railway service $ChatbotServiceId
railway up --detach

Write-Host "==> Para importar base de datos, ejecuta:"
Write-Host "mysql -h <MYSQLHOST> -P <MYSQLPORT> -u <MYSQLUSER> -p <MYSQLDATABASE> < $SqlDumpPath"
Write-Host "==> Luego valida:"
Write-Host "1) /health del chatbot"
Write-Host "2) Login en web"
Write-Host "3) Chatbot en dashboard"
