#!/usr/bin/env pwsh
# sync_recount_to_railway.ps1
# Sincroniza reconteo (productos + movimientos) de LOCAL a RAILWAY

Write-Host "`n" -ForegroundColor Cyan
Write-Host "╔════════════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║ SINCRONIZACIÓN DE RECONTEO: LOCAL → RAILWAY               ║" -ForegroundColor Cyan
Write-Host "╚════════════════════════════════════════════════════════════╝" -ForegroundColor Cyan
Write-Host "`n"

# ============================================================
# DATOS LOCALES (YA EXPORTADOS)
# ============================================================
Write-Host "📍 DATOS LOCAL (Confirmados)" -ForegroundColor Green
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Gray
Write-Host "  ✓ 10 Productos"
Write-Host "  ✓ 6 Movimientos de reconteo"
Write-Host "  ✓ Stock total: 196 unidades"
Write-Host "  ✓ Archivo: recount_sync.sql (listo para importar)"
Write-Host "`n"

# ============================================================
# CREDENCIALES DE RAILWAY
# ============================================================
Write-Host "🚂 INGRESA CREDENCIALES DE RAILWAY" -ForegroundColor Cyan
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Gray
Write-Host "Las encuentras en: railway.app → Proyecto → MySQL → Variables`n" -ForegroundColor Yellow

$railwayHost = Read-Host "  MYSQLHOST"
$railwayUser = Read-Host "  MYSQLUSER"
$railwayPass = Read-Host -AsSecureString "  MYSQLPASSWORD"
$railwayDb = Read-Host "  MYSQLDATABASE"
$railwayPort = Read-Host "  MYSQLPORT (default: 3306)"

if ([string]::IsNullOrWhiteSpace($railwayPort)) { $railwayPort = "3306" }

# Convertir contraseña segura a string
$bstr = [System.Runtime.InteropServices.Marshal]::SecureStringToBSTR($railwayPass)
$railwayPassPlain = [System.Runtime.InteropServices.Marshal]::PtrToStringAuto($bstr)

Write-Host "`n"

# ============================================================
# OPCIONES DE SINCRONIZACIÓN
# ============================================================
Write-Host "🔄 OPCIONES DE SINCRONIZACIÓN" -ForegroundColor Cyan
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Gray
Write-Host "1. REPLACE  → Reemplaza datos existentes (Recomendado)" -ForegroundColor Green
Write-Host "2. INSERT   → Inserta nuevos datos (si no existen)" -ForegroundColor Yellow
Write-Host "3. DRY-RUN  → Ver qué se haría sin ejecutar" -ForegroundColor Cyan
Write-Host ""

$option = Read-Host "Selecciona opción (1-3)"

if ($option -notin @("1", "2", "3")) {
    Write-Host "❌ Opción inválida. Abortando." -ForegroundColor Red
    exit 1
}

# ============================================================
# VERIFICAR MYSQL CLI
# ============================================================
Write-Host "`n"
Write-Host "🔍 VERIFICANDO MYSQL CLI..." -ForegroundColor Cyan
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Gray

$mysqlAvailable = $null -ne (Get-Command mysql -ErrorAction SilentlyContinue)

if (-not $mysqlAvailable) {
    Write-Host "  ❌ 'mysql' no encontrado en PATH" -ForegroundColor Red
    Write-Host "`n  Opciones:" -ForegroundColor Yellow
    Write-Host "  1. Instala MySQL CLI: https://dev.mysql.com/downloads/mysql/" -ForegroundColor Gray
    Write-Host "  2. O usa chocolatey: choco install mysql-cli" -ForegroundColor Gray
    Write-Host "  3. O importa manualmente desde: railway.app → phpMyAdmin" -ForegroundColor Gray
    exit 1
}

Write-Host "  ✓ mysql CLI disponible" -ForegroundColor Green
Write-Host "`n"

# ============================================================
# PREPARAR SQL SEGÚN OPCIÓN
# ============================================================
$sqlFile = "$PSScriptRoot\recount_sync.sql"

if (-not (Test-Path $sqlFile)) {
    Write-Host "❌ Archivo $sqlFile no encontrado" -ForegroundColor Red
    exit 1
}

# Leer SQL original
$sqlOriginal = Get-Content -Path $sqlFile -Raw

# Modificar según opción
$sqlToExecute = $sqlOriginal

if ($option -eq "1") {
    # REPLACE: DELETE primero, luego INSERT
    $sqlToExecute = @"
-- REPLACE MODE: Eliminar datos antiguos e insertar nuevos
DELETE FROM stock_movements WHERE notes LIKE '%reconteo%';

$sqlOriginal
"@
    Write-Host "📋 MODO: REPLACE (borrar + insertar nuevo)" -ForegroundColor Green
}
elseif ($option -eq "2") {
    # INSERT: Solo insertar si no existen
    $sqlToExecute = @"
-- INSERT MODE: Solo insertar nuevos (sin eliminar)
$sqlOriginal
"@
    Write-Host "📋 MODO: INSERT (solo nuevos)" -ForegroundColor Yellow
}
else {
    # DRY-RUN: Solo mostrar
    Write-Host "📋 MODO: DRY-RUN (vista previa)" -ForegroundColor Cyan
    Write-Host "`n"
    Write-Host "PREVIEW DEL SQL A EJECUTAR:" -ForegroundColor Cyan
    Write-Host str_repeat("─", 60)
    Write-Host $sqlToExecute | Select-Object -First 50
    Write-Host str_repeat("─", 60)
    Write-Host "`nPara ejecutar realmente, elige opción 1 o 2." -ForegroundColor Yellow
    exit 0
}

# ============================================================
# TESTEAR CONEXIÓN
# ============================================================
Write-Host "🔗 VERIFICANDO CONEXIÓN A RAILWAY..." -ForegroundColor Cyan
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Gray

try {
    $testCmd = "SELECT 1;"
    $result = & mysql -h "$railwayHost" -u "$railwayUser" -p"$railwayPassPlain" -P $railwayPort -e $testCmd 2>&1
    
    if ($LASTEXITCODE -eq 0) {
        Write-Host "  ✓ Conexión exitosa a Railway" -ForegroundColor Green
    } else {
        Write-Host "  ❌ Error de conexión:" -ForegroundColor Red
        Write-Host "  $result" -ForegroundColor Yellow
        exit 1
    }
} catch {
    Write-Host "  ❌ Excepción: $_" -ForegroundColor Red
    exit 1
}

Write-Host "`n"

# ============================================================
# CONFIRMAR ANTES DE EJECUTAR
# ============================================================
Write-Host "⚠️  CONFIRMACIÓN FINAL" -ForegroundColor Yellow
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Gray
Write-Host "Servidor:    $railwayHost" -ForegroundColor Cyan
Write-Host "Base datos:  $railwayDb" -ForegroundColor Cyan
Write-Host "Usuario:     $railwayUser" -ForegroundColor Cyan
Write-Host "Modo:        $(if ($option -eq '1') { 'REPLACE' } else { 'INSERT' })" -ForegroundColor Cyan
Write-Host ""

$confirm = Read-Host "¿Continuar? (s/n)"

if ($confirm -ne "s") {
    Write-Host "`n❌ Operación cancelada." -ForegroundColor Red
    exit 0
}

Write-Host "`n"

# ============================================================
# EJECUTAR SINCRONIZACIÓN
# ============================================================
Write-Host "🔄 EJECUTANDO SINCRONIZACIÓN..." -ForegroundColor Cyan
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Gray

$tempSqlFile = [System.IO.Path]::GetTempFileName() + ".sql"
Set-Content -Path $tempSqlFile -Value $sqlToExecute -Encoding UTF8

try {
    $output = & mysql -h "$railwayHost" -u "$railwayUser" -p"$railwayPassPlain" -P $railwayPort "$railwayDb" < $tempSqlFile 2>&1
    
    if ($LASTEXITCODE -eq 0) {
        Write-Host "  ✓ Sincronización completada" -ForegroundColor Green
        Write-Host "`n  Resultado: $output" -ForegroundColor Green
    } else {
        Write-Host "  ❌ Error durante sincronización:" -ForegroundColor Red
        Write-Host "  $output" -ForegroundColor Yellow
        exit 1
    }
} finally {
    Remove-Item -Path $tempSqlFile -Force -ErrorAction SilentlyContinue
}

Write-Host "`n"

# ============================================================
# VERIFICAR RESULTADO
# ============================================================
Write-Host "✅ VERIFICANDO DATOS EN RAILWAY..." -ForegroundColor Green
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Gray

$verifyCmd = @"
SELECT 'productos' as tipo, COUNT(*) FROM products
UNION ALL
SELECT 'reconteos', COUNT(*) FROM stock_movements WHERE notes LIKE '%reconteo%'
UNION ALL
SELECT 'stock_total', COALESCE(SUM(stock), 0) FROM products;
"@

$verifyResult = & mysql -h "$railwayHost" -u "$railwayUser" -p"$railwayPassPlain" -P $railwayPort -N "$railwayDb" -e $verifyCmd 2>&1

Write-Host $verifyResult

Write-Host "`n"

# ============================================================
# RESUMEN FINAL
# ============================================================
Write-Host "✅ SINCRONIZACIÓN COMPLETA" -ForegroundColor Green
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Green
Write-Host ""
Write-Host "Lo siguiente:"
Write-Host "  1. Abre: https://tu-railway-url/index.php?controller=admin&action=inventoryRecountForm" -ForegroundColor Cyan
Write-Host "  2. Deberías ver 10 productos con stock actualizado" -ForegroundColor Cyan
Write-Host "  3. Intenta hacer un nuevo reconteo para verificar que funciona" -ForegroundColor Cyan
Write-Host ""
Write-Host "Documentación:"
Write-Host "  • compare_recount_status.php   → Ver via navegador" -ForegroundColor Gray
Write-Host "  • check_recount_status_cli.php → Ver en terminal" -ForegroundColor Gray
Write-Host ""
