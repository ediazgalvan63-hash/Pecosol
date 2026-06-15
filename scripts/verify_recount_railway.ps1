#!/usr/bin/env pwsh
# 
# verify_recount_railway.ps1
# 
# Script para verificar si el reconteo en Railway coincide con LOCAL
# Uso: .\scripts\verify_recount_railway.ps1
#

Write-Host "`n" -ForegroundColor Cyan
Write-Host "╔════════════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║ VERIFICACIÓN DE RECONTEO: LOCAL vs RAILWAY                ║" -ForegroundColor Cyan
Write-Host "╚════════════════════════════════════════════════════════════╝" -ForegroundColor Cyan
Write-Host "`n"

# ============================================================
# PASO 1: Mostrar estado LOCAL
# ============================================================
Write-Host "📍 ESTADO LOCAL (Confirmado)" -ForegroundColor Green
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Gray
Write-Host "  • Productos totales:      10"
Write-Host "  • Stock total:            196"
Write-Host "  • Reconteos registrados:  6"
Write-Host "  • Últimos reconteos:      08-06-2026"
Write-Host "  • Base de datos:          pecosol_db"
Write-Host "`n"

# ============================================================
# PASO 2: Solicitar credenciales de Railway
# ============================================================
Write-Host "🚂 CREDENCIALES DE RAILWAY" -ForegroundColor Cyan
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Gray
Write-Host "Para comparar, necesito tus credenciales de Railway:" -ForegroundColor Yellow
Write-Host "Las encuentras en: railway.app → Tu proyecto → MySQL → Variables o Connect`n"

$railwayHost = Read-Host "  DB_HOST (ej: mysql-production.railway.internal)"
$railwayUser = Read-Host "  DB_USER (usualmente 'root')"
$railwayPassword = Read-Host -AsSecureString "  DB_PASSWORD"
$railwayDatabase = Read-Host "  DB_DATABASE (ej: railway)"
$railwayPort = Read-Host "  DB_PORT (default: 3306)"

if ([string]::IsNullOrWhiteSpace($railwayPort)) { $railwayPort = "3306" }

# Convertir contraseña de SecureString a String
$BSTR = [System.Runtime.InteropServices.Marshal]::SecureStringToBSTR($railwayPassword)
$railwayPasswordPlain = [System.Runtime.InteropServices.Marshal]::PtrToStringAuto($BSTR)

Write-Host "`n"

# ============================================================
# PASO 3: Verificar conexión a Railway
# ============================================================
Write-Host "🔍 VERIFICANDO CONEXIÓN A RAILWAY..." -ForegroundColor Cyan
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Gray

$mysqlPath = "mysql"
$mysqlAvailable = $null -ne (Get-Command mysql -ErrorAction SilentlyContinue)

if (-not $mysqlAvailable) {
    Write-Host "  ❌ ERROR: 'mysql' no encontrado en PATH" -ForegroundColor Red
    Write-Host "  Debes instalar MySQL CLI o usa phpMyAdmin de Railway" -ForegroundColor Yellow
    Write-Host "  Para instalar MySQL CLI en Windows:" -ForegroundColor Gray
    Write-Host "    1. Descarga: https://dev.mysql.com/downloads/mysql/" -ForegroundColor Gray
    Write-Host "    2. O usa Chocolatey: choco install mysql-cli" -ForegroundColor Gray
    exit 1
}

# Intentar conexión
$testCmd = @"
mysql -h "$railwayHost" -u "$railwayUser" -p"$railwayPasswordPlain" -P $railwayPort "$railwayDatabase" -e "SELECT 1;" 2>&1
"@

try {
    $result = Invoke-Expression $testCmd -ErrorAction SilentlyContinue
    if ($LASTEXITCODE -eq 0) {
        Write-Host "  ✓ Conexión exitosa a Railway" -ForegroundColor Green
    } else {
        Write-Host "  ❌ No se pudo conectar a Railway" -ForegroundColor Red
        Write-Host "  Verifica:" -ForegroundColor Yellow
        Write-Host "    - Host y puerto correctos" -ForegroundColor Gray
        Write-Host "    - Usuario y contraseña válidos" -ForegroundColor Gray
        Write-Host "    - Firewall permite la conexión" -ForegroundColor Gray
        exit 1
    }
} catch {
    Write-Host "  ❌ Error: $_" -ForegroundColor Red
    exit 1
}

Write-Host "`n"

# ============================================================
# PASO 4: Obtener datos de Railway
# ============================================================
Write-Host "📊 OBTENIENDO DATOS DE RAILWAY..." -ForegroundColor Cyan
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Gray

$sqlQuery = @"
SELECT 'productos' as tipo, COUNT(*) as valor FROM products
UNION ALL
SELECT 'reconteos', COUNT(*) FROM stock_movements WHERE notes LIKE '%reconteo%'
UNION ALL
SELECT 'stock_total', COALESCE(SUM(stock), 0) FROM products;
"@

# Guardar query en temp
$tempFile = [System.IO.Path]::GetTempFileName()
Set-Content -Path $tempFile -Value $sqlQuery -Encoding UTF8

$railwayData = & mysql -h "$railwayHost" -u "$railwayUser" -p"$railwayPasswordPlain" -P $railwayPort "$railwayDatabase" -N -e $sqlQuery 2>&1

Remove-Item -Path $tempFile -Force

if ($LASTEXITCODE -ne 0) {
    Write-Host "  ❌ Error ejecutando query en Railway" -ForegroundColor Red
    Write-Host "  Respuesta: $railwayData" -ForegroundColor Yellow
    exit 1
}

# Parsear resultados
$lines = $railwayData -split "`n" | Where-Object { $_ -match '\S' }
$productsRailway = 0
$reconteosRailway = 0
$stockRailway = 0

foreach ($line in $lines) {
    $parts = $line -split '\t' | Where-Object { $_ -match '\S' }
    if ($parts.Count -ge 2) {
        $tipo = $parts[0].Trim()
        $valor = $parts[1].Trim()
        
        if ($tipo -eq "productos") { $productsRailway = [int]$valor }
        elseif ($tipo -eq "reconteos") { $reconteosRailway = [int]$valor }
        elseif ($tipo -eq "stock_total") { $stockRailway = [int]$valor }
    }
}

Write-Host "`n"

# ============================================================
# PASO 5: Comparación
# ============================================================
Write-Host "⚖️  COMPARACIÓN: LOCAL vs RAILWAY" -ForegroundColor Cyan
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Gray

$metrics = @(
    @{ name = "Productos"; local = 10; railway = $productsRailway },
    @{ name = "Reconteos"; local = 6; railway = $reconteosRailway },
    @{ name = "Stock Total"; local = 196; railway = $stockRailway }
)

$allMatch = $true

foreach ($metric in $metrics) {
    $match = $metric.local -eq $metric.railway
    $status = if ($match) { "✓ COINCIDEN" } else { "✗ DIFERENTES" }
    $color = if ($match) { "Green" } else { "Red" }
    
    Write-Host "  $($metric.name)" -ForegroundColor Cyan
    Write-Host "    Local:   $($metric.local)" -ForegroundColor Gray
    Write-Host "    Railway: $($metric.railway)" -ForegroundColor Gray
    Write-Host "    Estado:  $status" -ForegroundColor $color
    Write-Host ""
    
    if (-not $match) { $allMatch = $false }
}

Write-Host "`n"

# ============================================================
# PASO 6: Recomendaciones
# ============================================================
if ($allMatch) {
    Write-Host "✅ ¡PERFECTO! El reconteo está sincronizado en Railway" -ForegroundColor Green
    Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Green
    Write-Host "  Los datos coinciden entre LOCAL y RAILWAY"
    Write-Host "  El módulo de reconteo funciona igual en ambos lados ✓"
    Write-Host ""
    Write-Host "Prueba accediendo a:" -ForegroundColor Green
    Write-Host "  https://tu-railway-url/index.php?controller=admin&action=inventoryRecountForm" -ForegroundColor Cyan
} else {
    Write-Host "⚠️  DIFERENCIAS DETECTADAS - Sincronización necesaria" -ForegroundColor Yellow
    Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "OPCIÓN 1: Sincronizar vía mysqldump (recomendado)" -ForegroundColor Cyan
    Write-Host "  En Windows PowerShell:" -ForegroundColor Gray
    Write-Host "    # Exportar de LOCAL:" -ForegroundColor Gray
    Write-Host "    mysqldump -u root -p pecosol_db products stock_movements > backup.sql" -ForegroundColor Gray
    Write-Host ""
    Write-Host "    # Importar a RAILWAY:" -ForegroundColor Gray
    Write-Host "    mysql -h $railwayHost -u $railwayUser -p'$railwayPasswordPlain' -P $railwayPort $railwayDatabase < backup.sql" -ForegroundColor Gray
    Write-Host ""
    Write-Host "OPCIÓN 2: Usar phpMyAdmin o UI de Railway" -ForegroundColor Cyan
    Write-Host "  1. Abre https://railway.app" -ForegroundColor Gray
    Write-Host "  2. Ve a tu MySQL" -ForegroundColor Gray
    Write-Host "  3. Copia los datos manualmente o usa import" -ForegroundColor Gray
}

Write-Host "`n"
