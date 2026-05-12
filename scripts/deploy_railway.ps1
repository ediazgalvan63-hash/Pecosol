param(
  [Parameter(Mandatory=$true)][string]$WebServiceId,
  [Parameter(Mandatory=$true)][string]$ChatbotServiceId,
  [Parameter(Mandatory=$false)][string]$CommitMessage = "Deploy local changes to Railway",
  [Parameter(Mandatory=$false)][switch]$SkipGit,
  [Parameter(Mandatory=$false)][string]$SqlDumpPath = "pecosol_db.sql"
)

$ErrorActionPreference = "Stop"

function Assert-Command($name) {
  if (-not (Get-Command $name -ErrorAction SilentlyContinue)) {
    throw "No se encontro el comando '$name'. Instala la herramienta antes de continuar."
  }
}

Assert-Command "railway"
if (-not $SkipGit) { Assert-Command "git" }

if (-not $SkipGit) {
  Write-Host "==> Verificando repositorio Git..."
  $gitStatus = git status --porcelain
  if ($LASTEXITCODE -ne 0) {
    throw "Error al ejecutar git status. Asegúrate de tener Git instalado y configurado."
  }

  if ($gitStatus) {
    Write-Host "==> Cambios locales detectados. Agregando al commit..."
    git add --all
    git commit -m $CommitMessage 2>$null

    if ($LASTEXITCODE -eq 0) {
      Write-Host "==> Commit creado con éxito."
    } else {
      Write-Host "==> No se generó un nuevo commit. Es posible que los cambios ya estén indexados o no haya nada nuevo."
    }

    Write-Host "==> Enviando cambios a origin..."
    git push origin HEAD
    if ($LASTEXITCODE -ne 0) {
      throw "Error al enviar los cambios a origin. Revisa tu configuración de Git y la conexión de red."
    }
  } else {
    Write-Host "==> No hay cambios locales pendientes."
  }
}

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
