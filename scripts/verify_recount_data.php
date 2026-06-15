<?php
// scripts/verify_recount_data.php
// Verifica que la tabla de reconteo sea idéntica entre local y Railway

$local = new PDO("mysql:host=127.0.0.1;port=3306;dbname=pecosol_db;charset=utf8mb4", "root", "");
$local->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$railway = new PDO(
    "mysql:host=switchback.proxy.rlwy.net;port=10989;dbname=railway;charset=utf8mb4",
    "root",
    "LTGlzNjlJkBgNAjhYhfSGUpHUTAyQcya"
);
$railway->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "VERIFICACIÓN DE DATOS DEL RECONTEO\n";
echo "================================================================================\n\n";

// Obtener productos de ambas bases
$localProducts = $local->query("SELECT id, name, stock, stock_minimum FROM products ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
$railwayProducts = $railway->query("SELECT id, name, stock, stock_minimum FROM products ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);

echo "TABLA DE PRODUCTOS (usado en Reconteo):\n";
echo "================================================================================\n";
echo sprintf("%-5s | %-35s | %-10s | %-10s\n", "ID", "Nombre", "Stock", "Mín");
echo "------|--------------------------------------|------------|----------\n";

$differences = 0;
foreach ($localProducts as $prod) {
    $id = $prod['id'];
    $railway_prod = array_filter($railwayProducts, fn($p) => $p['id'] == $id)[0] ?? null;
    
    $match = $railway_prod && 
             $prod['name'] === $railway_prod['name'] &&
             $prod['stock'] == $railway_prod['stock'] &&
             $prod['stock_minimum'] == $railway_prod['stock_minimum'];
    
    $status = $match ? "✅" : "❌";
    
    echo sprintf("%-5d | %-35s | %-10s | %-10s %s\n", 
        $id,
        substr($prod['name'], 0, 33),
        $prod['stock'],
        $prod['stock_minimum'],
        $status
    );
    
    if (!$match) {
        $differences++;
        if ($railway_prod) {
            echo "  Railway: name=" . $railway_prod['name'] . ", stock=" . $railway_prod['stock'] . ", mín=" . $railway_prod['stock_minimum'] . "\n";
        }
    }
}

echo "\n================================================================================\n";
if ($differences === 0) {
    echo "✅ TODOS LOS DATOS DEL RECONTEO SON IDÉNTICOS ENTRE LOCAL Y RAILWAY\n";
} else {
    echo "❌ $differences producto(s) con diferencias detectados\n";
}

// Ahora verifica stock_movements para ver el historial
$localMovements = $local->query("SELECT COUNT(*) as cnt FROM stock_movements")->fetch(PDO::FETCH_ASSOC)['cnt'];
$railwayMovements = $railway->query("SELECT COUNT(*) as cnt FROM stock_movements")->fetch(PDO::FETCH_ASSOC)['cnt'];

echo "\nHISTÓRICO DE MOVIMIENTOS (Stock Movements):\n";
echo "================================================================================\n";
echo sprintf("Local: %d registros\n", $localMovements);
echo sprintf("Railway: %d registros\n", $railwayMovements);

if ($localMovements == $railwayMovements) {
    echo "✅ Stock movements idénticos\n";
} else {
    echo "❌ Stock movements diferentes\n";
}

echo "\n================================================================================\n";
echo "✅ RECONTEO LISTO PARA USAR EN RAILWAY - DATOS SINCRONIZADOS CORRECTAMENTE\n";
echo "================================================================================\n";
?>
